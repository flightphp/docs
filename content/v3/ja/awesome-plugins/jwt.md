# Firebase JWT - Flight 用の JSON Web Token 認証

JWT (JSON Web Tokens) は、アプリケーションとクライアント間の主張を表すコンパクトで URL セーフな方法です。サーバー側のセッションストレージが不要なステートレス API 認証に最適です！このガイドでは、[Firebase JWT](https://github.com/firebase/php-jwt) を Flight と統合して、セキュアなトークンベースの認証を行う方法を示します。

完全なドキュメントと詳細については、[Github リポジトリ](https://github.com/firebase/php-jwt) をご覧ください。

## JWT とは？

JSON Web Token は、3つの部分を含む文字列です：
1. **ヘッダー**: トークンに関するメタデータ (アルゴリズム、タイプ)
2. **ペイロード**: あなたのデータ (ユーザー ID、ロール、期限切れなど)
3. **シグネチャー**: 真正性を検証するための暗号署名

例の JWT: `eyJ0eXAiOiJKV1QiLCJhbGc...` (意味不明に見えますが、構造化されたデータです！)

### JWT を使用する理由は？

- **ステートレス**: サーバー側のセッションストレージが不要 — マイクロサービスや API に最適
- **スケーラブル**: セッションアフィニティの要件がないため、ロードバランサーとよく動作
- **クロスドメイン**: 異なるドメインやサービス間で使用可能
- **モバイルフレンドリー**: クッキーがうまく動作しないモバイルアプリに最適
- **標準化**: 業界標準のアプローチ (RFC 7519)

## インストール

Composer を使用してインストール：

```bash
composer require firebase/php-jwt
```

## 基本的な使用方法

JWT を作成して検証する簡単な例：

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// あなたの秘密鍵 (これをセキュアに保ってください！)
$secretKey = 'your-256-bit-secret-key-here-keep-it-safe';

// トークンを作成
$payload = [
    'user_id' => 123,
    'username' => 'johndoe',
    'role' => 'admin',
    'iat' => time(),              // 発行日時
    'exp' => time() + 3600        // 1時間後に期限切れ
];

$jwt = JWT::encode($payload, $secretKey, 'HS256');
echo "Token: " . $jwt;

// トークンを検証してデコード
try {
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
    echo "User ID: " . $decoded->user_id;
} catch (Exception $e) {
    echo "Invalid token: " . $e->getMessage();
}
```

## Flight 用の JWT ミドルウェア (推奨アプローチ)

Flight で JWT を使用する最も一般的で有用な方法は、**ミドルウェア**として API ルートを保護することです。以下は、完全で本番環境対応の例です：

### ステップ 1: JWT ミドルウェアクラスを作成

```php
// app/middleware/JwtMiddleware.php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use flight\Engine;

class JwtMiddleware {

    protected Engine $app;
    protected string $secretKey;

    public function __construct(Engine $app) {
        $this->app = $app;
        // 秘密鍵を app/config/config.php に保存し、ハードコードしないでください！
        $this->secretKey = $app->get('config')['jwt_secret'];
    }

    public function before(array $params) {
        $authHeader = $this->app->request()->getHeader('Authorization');

        // Authorization ヘッダーが存在するかチェック
        if (empty($authHeader)) {
            $this->app->jsonHalt(['error' => 'No authorization token provided'], 401);
        }

        // "Bearer <token>" 形式からトークンを抽出
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->app->jsonHalt(['error' => 'Invalid authorization format. Use: Bearer <token>'], 401);
        }

        $jwt = $matches[1];

        try {
            // トークンをデコードして検証
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
            
            // ルートハンドラーで使用するためにユーザー情報をリクエストに保存
            $this->app->request()->data->user = $decoded;
            
        } catch (ExpiredException $e) {
            $this->app->jsonHalt(['error' => 'Token has expired'], 401);
        } catch (SignatureInvalidException $e) {
            $this->app->jsonHalt(['error' => 'Invalid token signature'], 401);
        } catch (Exception $e) {
            $this->app->jsonHalt(['error' => 'Invalid token: ' . $e->getMessage()], 401);
        }
    }
}
```

### ステップ 2: 設定に JWT 秘密鍵を登録

```php
// app/config/config.php
return [
    'jwt_secret' => getenv('JWT_SECRET') ?: 'your-fallback-secret-for-development'
];

// app/config/bootstrap.php または index.php
// 設定をアプリに公開したい場合、この行を追加してください
$app->set('config', $config);
```

> **セキュリティノート**: 秘密鍵をハードコードしないでください！本番環境では環境変数を使用してください。

### ステップ 3: ミドルウェアでルートを保護

```php
// 単一のルートを保護
Flight::route('GET /api/user/profile', function() {
    $user = Flight::request()->data->user; // ミドルウェアによって設定
    Flight::json([
        'user_id' => $user->user_id,
        'username' => $user->username,
        'role' => $user->role
    ]);
})->addMiddleware( JwtMiddleware::class);

// ルートのグループ全体を保護 (より一般的！)
Flight::group('/api', function() {
    Flight::route('GET /users', function() { /* ... */ });
    Flight::route('GET /posts', function() { /* ... */ });
    Flight::route('POST /posts', function() { /* ... */ });
    Flight::route('DELETE /posts/@id', function($id) { /* ... */ });
}, [ JwtMiddleware::class ]); // このグループ内のすべてのルートが保護されます！
```

ミドルウェアの詳細については、[ミドルウェアドキュメント](/learn/middleware) を参照してください。

## 一般的なユースケース

### 1. ログインエンドポイント (トークン生成)

認証成功後に JWT を生成するルートを作成：

```php
Flight::route('POST /api/login', function() {
    $data = Flight::request()->data;
    $username = $data->username ?? '';
    $password = $data->password ?? '';

    // 認証情報を検証 (例 — 独自のロジックを使用！)
    $user = validateUserCredentials($username, $password);
    
    if (!$user) {
        Flight::jsonHalt(['error' => 'Invalid credentials'], 401);
    }

    // JWT を生成
    $secretKey = Flight::get('config')['jwt_secret'];
    $payload = [
        'user_id' => $user->id,
        'username' => $user->username,
        'role' => $user->role,
        'iat' => time(),
        'exp' => time() + (60 * 60) // 1時間後の期限切れ
    ];

    $jwt = JWT::encode($payload, $secretKey, 'HS256');

    Flight::json([
        'success' => true,
        'token' => $jwt,
        'expires_in' => 3600
    ]);
});

function validateUserCredentials($username, $password) {
    // ここにデータベース検索とパスワード検証を実装
    // 例:
    $db = Flight::db();
    $user = $db->fetchRow("SELECT * FROM users WHERE username = ?", [$username]);
    
    if ($user && password_verify($password, $user['password_hash'])) {
        return (object) [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ];
    }
    return null;
}
```

### 2. トークンリフレッシュフロー

長期間のセッションのためのリフレッシュトークンシステムを実装：

```php
Flight::route('POST /api/login', function() {
    // ... 認証情報を検証 ...

    $secretKey = Flight::get('config')['jwt_secret'];
    $refreshSecret = Flight::get('config')['jwt_refresh_secret'];
    
    // 短期間のアクセス トークン (15 分)
    $accessToken = JWT::encode([
        'user_id' => $user->id,
        'type' => 'access',
        'iat' => time(),
        'exp' => time() + (15 * 60)
    ], $secretKey, 'HS256');
    
    // 長期間のリフレッシュ トークン (7 日)
    $refreshToken = JWT::encode([
        'user_id' => $user->id,
        'type' => 'refresh',
        'iat' => time(),
        'exp' => time() + (7 * 24 * 60 * 60)
    ], $refreshSecret, 'HS256');
    
    Flight::json([
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken,
        'expires_in' => 900
    ]);
});

Flight::route('POST /api/refresh', function() {
    $refreshToken = Flight::request()->data->refresh_token ?? '';
    $refreshSecret = Flight::get('config')['jwt_refresh_secret'];
    
    try {
        $decoded = JWT::decode($refreshToken, new Key($refreshSecret, 'HS256'));
        
        // これがリフレッシュ トークンであることを検証
        if ($decoded->type !== 'refresh') {
            Flight::jsonHalt(['error' => 'Invalid token type'], 401);
        }
        
        // 新しいアクセス トークンを生成
        $secretKey = Flight::get('config')['jwt_secret'];
        $accessToken = JWT::encode([
            'user_id' => $decoded->user_id,
            'type' => 'access',
            'iat' => time(),
            'exp' => time() + (15 * 60)
        ], $secretKey, 'HS256');
        
        Flight::json([
            'access_token' => $accessToken,
            'expires_in' => 900
        ]);
        
    } catch (Exception $e) {
        Flight::jsonHalt(['error' => 'Invalid refresh token'], 401);
    }
});
```

### 3. ロールベースのアクセス制御

ミドルウェアを拡張してユーザー ロールをチェック：

```php
class JwtRoleMiddleware {
    
    protected Engine $app;
    protected array $allowedRoles;
    
    public function __construct(Engine $app, array $allowedRoles = []) {
        $this->app = $app;
        $this->allowedRoles = $allowedRoles;
    }
    
    public function before(array $params) {
        // JwtMiddleware が既に実行され、ユーザー データが設定されていると仮定
        $user = $this->app->request()->data->user ?? null;
        
        if (!$user) {
            $this->app->jsonHalt(['error' => 'Authentication required'], 401);
        }
        
        // ユーザーが必要なロールを持っているかチェック
        if (!empty($this->allowedRoles) && !in_array($user->role, $this->allowedRoles)) {
            $this->app->jsonHalt(['error' => 'Insufficient permissions'], 403);
        }
    }
}

// 使用例: 管理者限定ルート
Flight::route('DELETE /api/users/@id', function($id) {
    // ユーザー削除ロジック
})->addMiddleware([
    JwtMiddleware::class,
    new JwtRoleMiddleware(Flight::app(), ['admin'])
]);
```

### 4. ユーザーごとのレート制限付きパブリック API

セッションなしでユーザーを追跡してレート制限：

```php
class RateLimitMiddleware {
    
    public function before(array $params) {
        $user = Flight::request()->data->user ?? null;
        $userId = $user ? $user->user_id : Flight::request()->ip;
        
        $cacheKey = "rate_limit:$userId";
        // app/config/services.php でキャッシュ サービスを設定してください
        $requests = Flight::cache()->get($cacheKey, 0);
        
        if ($requests >= 100) { // 1時間あたり 100 リクエスト
            Flight::jsonHalt(['error' => 'Rate limit exceeded'], 429);
        }
        
        Flight::cache()->set($cacheKey, $requests + 1, 3600);
    }
}
```

## セキュリティのベストプラクティス

### 1. 強力な秘密鍵を使用

```php
// セキュアな秘密鍵を生成 (一度実行し、.env ファイルに保存)
$secretKey = base64_encode(random_bytes(32));
echo $secretKey; // これを .env ファイルに保存！
```

### 2. 秘密鍵を環境変数に保存

```php
// 秘密鍵をバージョン管理にコミットしないでください！
// .env ファイルと vlucas/phpdotenv などのライブラリを使用

// .env ファイル:
// JWT_SECRET=your-base64-encoded-secret-here
// JWT_REFRESH_SECRET=another-base64-encoded-secret-here

// アプリの設定ファイル app/config/config.php にも秘密鍵を保存可能
// ただし、設定ファイルはバージョン管理にコミットしないでください
// return [
//     'jwt_secret' => 'your-base64-encoded-secret-here',
//     'jwt_refresh_secret' => 'another-base64-encoded-secret-here',
// ];

// アプリ内:
// $secretKey = getenv('JWT_SECRET');
```

### 3. 適切な有効期限を設定

```php
// 良い習慣: 短期間のアクセス トークン
'exp' => time() + (15 * 60)  // 15 分

// リフレッシュ トークンの場合: 長い有効期限
'exp' => time() + (7 * 24 * 60 * 60)  // 7 日
```

### 4. 本番環境で HTTPS を使用

JWT は **常に** HTTPS を介して送信してください。本番環境でプレーン HTTP を使用しないでください！

### 5. トークン主張を検証

重要な主張を常に検証：

```php
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

// 期限切れのチェックはライブラリによって自動的に処理されます
// ただし、カスタム検証を追加可能:
if ($decoded->iat > time()) {
    throw new Exception('Token used before it was issued');
}

if (isset($decoded->nbf) && $decoded->nbf > time()) {
    throw new Exception('Token not yet valid');
}
```

### 6. ログアウトのためのトークンブラックリストを検討

追加のセキュリティのために、無効化されたトークンのブラックリストを維持：

```php
Flight::route('POST /api/logout', function() {
    $authHeader = Flight::request()->getHeader('Authorization');
    preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches);
    $jwt = $matches[1];
    
    // トークンの有効期限を抽出
    $decoded = Flight::request()->data->user;
    $ttl = $decoded->exp - time();
    
    // 有効期限までキャッシュ/Redis に保存
    Flight::cache()->set("blacklist:$jwt", true, $ttl);
    
    Flight::json(['message' => 'Successfully logged out']);
});

// JwtMiddleware に追加:
public function before(array $params) {
    // ... JWT を抽出 ...
    
    // ブラックリストをチェック
    if (Flight::cache()->get("blacklist:$jwt")) {
        $this->app->jsonHalt(['error' => 'Token has been revoked'], 401);
    }
    
    // ... トークンを検証 ...
}
```

## アルゴリズムと鍵の種類

Firebase JWT は複数のアルゴリズムをサポート：

### 対称アルゴリズム (HMAC)
- **HS256** (ほとんどのアプリで推奨): 単一の秘密鍵を使用
- **HS384**, **HS512**: より強力なバリエーション

```php
$jwt = JWT::encode($payload, $secretKey, 'HS256');
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### 非対称アルゴリズム (RSA/ECDSA)
- **RS256**, **RS384**, **RS512**: 公開/秘密鍵ペアを使用
- **ES256**, **ES384**, **ES512**: 楕円曲線バリエーション

```php
// 鍵を生成: openssl genrsa -out private.key 2048
// openssl rsa -in private.key -pubout -out public.key

$privateKey = file_get_contents('/path/to/private.key');
$publicKey = file_get_contents('/path/to/public.key');

// 秘密鍵でエンコード
$jwt = JWT::encode($payload, $privateKey, 'RS256');

// 公開鍵でデコード
$decoded = JWT::decode($jwt, new Key($publicKey, 'RS256'));
```

> **RSA を使用するタイミング**: 公開鍵を検証のために配布する必要がある場合 (例: マイクロサービス、サードパーティ統合) に RSA を使用。単一のアプリケーションの場合、HS256 がよりシンプルで十分です。

## トラブルシューティング

### "Expired token" エラー
トークンの `exp` 主張が過去です。新しいトークンを発行するか、トークンリフレッシュを実装してください。

### "Signature verification failed"
- エンコードに使用したものと異なる秘密鍵でデコードしている
- トークンが改ざんされている
- サーバー間のクロックスキュー (レウェイバッファを追加)

```php
use Firebase\JWT\JWT;

JWT::$leeway = 60; // 60 秒のクロックスキューを許可
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### リクエストでトークンが送信されない
クライアントが `Authorization` ヘッダーを送信していることを確認：

```javascript
// JavaScript の例
fetch('/api/users', {
    headers: {
        'Authorization': 'Bearer ' + token
    }
});
```

## メソッド

Firebase JWT ライブラリはこれらのコアメソッドを提供：

- `JWT::encode(array $payload, string $key, string $alg)`: ペイロードから JWT を作成
- `JWT::decode(string $jwt, Key $key)`: JWT をデコードして検証
- `JWT::urlsafeB64Encode(string $input)`: Base64 URL セーフエンコード
- `JWT::urlsafeB64Decode(string $input)`: Base64 URL セーフデコード
- `JWT::$leeway`: 検証のための時間レウェイを設定する静的プロパティ (秒単位)

## このライブラリを使用する理由は？

- **業界標準**: Firebase JWT は PHP で最も人気があり、広く信頼されている JWT ライブラリ
- **積極的なメンテナンス**: Google/Firebase チームによるメンテナンス
- **セキュリティ重視**: 定期的な更新とセキュリティパッチ
- **シンプルな API**: 理解しやすく実装しやすい
- **よくドキュメント化**: 広範なドキュメントとコミュニティサポート
- **柔軟**: 複数のアルゴリズムと設定可能なオプションをサポート

## 関連項目

- [Firebase JWT Github リポジトリ](https://github.com/firebase/php-jwt)
- [JWT.io](https://jwt.io/) - JWT をデバッグおよびデコード
- [RFC 7519](https://tools.ietf.org/html/rfc7519) - 公式 JWT 仕様
- [Flight ミドルウェアドキュメント](/learn/middleware)
- [Flight セッションプラグイン](/awesome-plugins/session) - 従来のセッションベース認証用

## ライセンス

Firebase JWT ライブラリは BSD 3-Clause ライセンスの下でライセンスされています。詳細は [Github リポジトリ](https://github.com/firebase/php-jwt) を参照してください。