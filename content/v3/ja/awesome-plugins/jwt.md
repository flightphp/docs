# Firebase JWT - JSON Web Token 認証

JWT (JSON Web Tokens) は、アプリケーションとクライアント間で請求を表すためのコンパクトで URL セーフな方法です。サーバー側のセッションストレージが不要なステートレス API 認証に最適です！このガイドでは、[Firebase JWT](https://github.com/firebase/php-jwt) を Flight と統合して、セキュアなトークンベースの認証を行う方法を示します。

完全なドキュメントと詳細については、[Github リポジトリ](https://github.com/firebase/php-jwt) をご覧ください。

## JWT とは？

JSON Web Token は、3つの部分を含む文字列です：
1. **ヘッダー**: トークンに関するメタデータ（アルゴリズム、タイプ）
2. **ペイロード**: あなたのデータ（ユーザー ID、ロール、期限切れなど）
3. **署名**: 真正性を検証するための暗号署名

例の JWT: `eyJ0eXAiOiJKV1QiLCJhbGc...`（意味不明に見えますが、構造化されたデータです！）

### JWT を使用する理由は？

- **ステートレス**: サーバー側のセッションストレージが不要 — マイクロサービスや API に最適
- **スケーラブル**: セッション親和性要件がないため、ロードバランサーでうまく動作
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

// あなたのシークレットキー（これを安全に保ってください！）
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

## Flight 用の JWT ミドルウェア（推奨アプローチ）

Flight で JWT を使用する最も一般的で有用な方法は、API ルートを保護するための **ミドルウェア** としてです。以下は、完全で本番環境対応の例です：

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
        // シークレットキーを app/config/config.php に保存し、ハードコードしないでください！
        $this->secretKey = $app->get('config')['jwt_secret'];
    }

    public function before(array $params) {
        $authHeader = $this->app->request()->getHeader('Authorization');

        // Authorization ヘッダーの存在を確認
        if (empty($authHeader)) {
            $this->app->jsonHalt(['error' => '認証トークンが提供されていません'], 401);
        }

        // "Bearer <token>" 形式からトークンを抽出
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->app->jsonHalt(['error' => '無効な認証形式です。Bearer <token> を使用してください'], 401);
        }

        $jwt = $matches[1];

        try {
            // トークンをデコードして検証
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
            
            // ルートハンドラーで使用するためにユーザー情報をリクエストに保存
            $this->app->request()->data->user = $decoded;
            
        } catch (ExpiredException $e) {
            $this->app->jsonHalt(['error' => 'トークンの有効期限が切れています'], 401);
        } catch (SignatureInvalidException $e) {
            $this->app->jsonHalt(['error' => '無効なトークン署名'], 401);
        } catch (Exception $e) {
            $this->app->jsonHalt(['error' => '無効なトークン: ' . $e->getMessage()], 401);
        }
    }
}
```

### ステップ 2: 設定に JWT シークレットを登録

```php
// app/config/config.php
return [
    'jwt_secret' => getenv('JWT_SECRET') ?: 'your-fallback-secret-for-development'
];

// app/config/bootstrap.php または index.php
// 設定をアプリに公開したい場合は、この行を追加してください
$app->set('config', $config);
```

> **セキュリティノート**: シークレットキーをハードコードしないでください！本番環境では環境変数を使用してください。

### ステップ 3: ミドルウェアでルートを保護

```php
// 単一のルートを保護
Flight::route('GET /api/user/profile', function() {
    $user = Flight::request()->data->user; // ミドルウェアで設定
    Flight::json([
        'user_id' => $user->user_id,
        'username' => $user->username,
        'role' => $user->role
    ]);
})->addMiddleware(JwtMiddleware::class);

// ルートのグループ全体を保護（より一般的！）
Flight::group('/api', function() {
    Flight::route('GET /users', function() { /* ... */ });
    Flight::route('GET /posts', function() { /* ... */ });
    Flight::route('POST /posts', function() { /* ... */ });
    Flight::route('DELETE /posts/@id', function($id) { /* ... */ });
}, [ JwtMiddleware::class ]); // このグループ内のすべてのルートが保護されます！
```

ミドルウェアの詳細については、[ミドルウェアドキュメント](/learn/middleware) を参照してください。

## 一般的な使用事例

### 1. ログインエンドポイント（トークン生成）

認証成功後に JWT を生成するルートを作成：

```php
Flight::route('POST /api/login', function() {
    $data = Flight::request()->data;
    $username = $data->username ?? '';
    $password = $data->password ?? '';

    // 認証情報を検証（例 — 独自のロジックを使用！）
    $user = validateUserCredentials($username, $password);
    
    if (!$user) {
        Flight::jsonHalt(['error' => '無効な認証情報'], 401);
    }

    // JWT を生成
    $secretKey = Flight::get('config')['jwt_secret'];
    $payload = [
        'user_id' => $user->id,
        'username' => $user->username,
        'role' => $user->role,
        'iat' => time(),
        'exp' => time() + (60 * 60) // 1時間の有効期限
    ];

    $jwt = JWT::encode($payload, $secretKey, 'HS256');

    Flight::json([
        'success' => true,
        'token' => $jwt,
        'expires_in' => 3600
    ]);
});

function validateUserCredentials($username, $password) {
    // ここにデータベース検索とパスワード検証
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

### 2. トークン更新フロー

長期間のセッションのためのリフレッシュトークンシステムを実装：

```php
Flight::route('POST /api/login', function() {
    // ... 認証情報を検証 ...

    $secretKey = Flight::get('config')['jwt_secret'];
    $refreshSecret = Flight::get('config')['jwt_refresh_secret'];
    
    // 短期間のアクセストークン (15 分)
    $accessToken = JWT::encode([
        'user_id' => $user->id,
        'type' => 'access',
        'iat' => time(),
        'exp' => time() + (15 * 60)
    ], $secretKey, 'HS256');
    
    // 長期間のリフレッシュトークン (7 日)
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
        
        // これがリフレッシュトークンであることを検証
        if ($decoded->type !== 'refresh') {
            Flight::jsonHalt(['error' => '無効なトークンタイプ'], 401);
        }
        
        // 新しいアクセストークンを生成
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
        Flight::jsonHalt(['error' => '無効なリフレッシュトークン'], 401);
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
        // JwtMiddleware がすでに実行され、ユーザー データが設定されていると仮定
        $user = $this->app->request()->data->user ?? null;
        
        if (!$user) {
            $this->app->jsonHalt(['error' => '認証が必要です'], 401);
        }
        
        // ユーザーが必要なロールを持っているかをチェック
        if (!empty($this->allowedRoles) && !in_array($user->role, $this->allowedRoles)) {
            $this->app->jsonHalt(['error' => '権限が不足しています'], 403);
        }
    }
}

// 使用例: 管理者専用ルート
Flight::route('DELETE /api/users/@id', function($id) {
    // ユーザー削除ロジック
})->addMiddleware([
    JwtMiddleware::class,
    new JwtRoleMiddleware(Flight::app(), ['admin'])
]);
```

### 4. ユーザーごとのレート制限付きパブリック API

セッションなしで JWT を使用してユーザー を追跡し、レート制限：

```php
class RateLimitMiddleware {
    
    public function before(array $params) {
        $user = Flight::request()->data->user ?? null;
        $userId = $user ? $user->user_id : Flight::request()->ip;
        
        $cacheKey = "rate_limit:$userId";
        // app/config/services.php でキャッシュサービスを設定してください
        $requests = Flight::cache()->get($cacheKey, 0);
        
        if ($requests >= 100) { // 1時間あたり 100 リクエスト
            Flight::jsonHalt(['error' => 'レート制限を超えました'], 429);
        }
        
        Flight::cache()->set($cacheKey, $requests + 1, 3600);
    }
}
```

## セキュリティのベストプラクティス

### 1. 強力なシークレットキー を使用

```php
// セキュアなシークレットキーを生成（一度実行し、.env ファイルに保存）
$secretKey = base64_encode(random_bytes(32));
echo $secretKey; // これを .env ファイルに保存！
```

### 2. シークレットを環境変数に保存

```php
// シークレットをバージョン管理にコミットしないでください！
// .env ファイルと vlucas/phpdotenv などのライブラリを使用

// .env ファイル:
// JWT_SECRET=your-base64-encoded-secret-here
// JWT_REFRESH_SECRET=another-base64-encoded-secret-here

// app/config/config.php ファイルを使用してシークレットを保存することも可能
// 設定ファイルがバージョン管理にコミットされないことを確認
// return [
//     'jwt_secret' => 'your-base64-encoded-secret-here',
//     'jwt_refresh_secret' => 'another-base64-encoded-secret-here',
// ];

// アプリ内:
// $secretKey = getenv('JWT_SECRET');
```

### 3. 適切な有効期限を設定

```php
// 良い習慣: 短期間のアクセストークン
'exp' => time() + (15 * 60)  // 15 分

// リフレッシュトークンの場合: 長い有効期限
'exp' => time() + (7 * 24 * 60 * 60)  // 7 日
```

### 4. 本番環境で HTTPS を使用

JWT は **常に** HTTPS で送信されるべきです。本番環境ではプレーン HTTP でトークンを送信しないでください！

### 5. トークンクレームを検証

重要なクレームを常に検証：

```php
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

// 期限切れのチェックはライブラリで自動的に処理されます
// ただし、カスタム検証を追加可能:
if ($decoded->iat > time()) {
    throw new Exception('トークンが発行される前に使用されました');
}

if (isset($decoded->nbf) && $decoded->nbf > time()) {
    throw new Exception('トークンがまだ有効ではありません');
}
```

### 6. ログアウトのためのトークンブラックリストを検討

追加のセキュリティのため、無効化されたトークンのブラックリストを維持：

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
    
    Flight::json(['message' => '正常にログアウトしました']);
});

// JwtMiddleware に追加:
public function before(array $params) {
    // ... JWT を抽出 ...
    
    // ブラックリストをチェック
    if (Flight::cache()->get("blacklist:$jwt")) {
        $this->app->jsonHalt(['error' => 'トークンが取り消されました'], 401);
    }
    
    // ... トークンを検証 ...
}
```

## アルゴリズムとキー タイプ

Firebase JWT は複数のアルゴリズムをサポート：

### 対称アルゴリズム (HMAC)
- **HS256** (ほとんどのアプリに推奨): 単一のシークレットキー を使用
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

> **RSA を使用するタイミング**: 検証のための公開鍵を配布する必要がある場合に RSA を使用（例: マイクロサービス、サードパーティ統合）。単一のアプリケーションの場合、HS256 がよりシンプルで十分です。

## トラブルシューティング

### "Expired token" エラー
トークンの `exp` クレームが過去です。新しいトークンを発行するか、トークン更新を実装してください。

### "Signature verification failed"
- エンコードに使用したものと異なるシークレットキー でデコードしています
- トークンが改ざんされています
- サーバー間のクロックスキュー（leeway バッファを追加）

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
- `JWT::urlsafeB64Encode(string $input)`: Base64 URL セーフエンコーディング
- `JWT::urlsafeB64Decode(string $input)`: Base64 URL セーフデコーディング
- `JWT::$leeway`: 検証のための時間 leeway を設定する静的プロパティ（秒単位）

## このライブラリを使用する理由は？

- **業界標準**: Firebase JWT は PHP 用の最も人気があり信頼されている JWT ライブラリ
- **積極的なメンテナンス**: Google/Firebase チームによるメンテナンス
- **セキュリティ重視**: 定期的な更新とセキュリティパッチ
- **シンプルな API**: 理解しやすく実装しやすい
- **よくドキュメント化**: 豊富なドキュメントとコミュニティサポート
- **柔軟**: 複数のアルゴリズムと構成可能なオプションをサポート

## 関連資料

- [Firebase JWT Github リポジトリ](https://github.com/firebase/php-jwt)
- [JWT.io](https://jwt.io/) - JWT をデバッグしてデコード
- [RFC 7519](https://tools.ietf.org/html/rfc7519) - 公式 JWT 仕様
- [Flight ミドルウェアドキュメント](/learn/middleware)
- [Flight セッションプラグイン](/awesome-plugins/session) - 従来のセッションベース認証用

## ライセンス

Firebase JWT ライブラリは BSD 3-Clause ライセンスの下でライセンスされています。詳細は [Github リポジトリ](https://github.com/firebase/php-jwt) を参照してください。