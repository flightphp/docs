# セキュリティ

## 概要

ウェブアプリケーションにおいてセキュリティは重要な問題です。アプリケーションが安全であることを確認し、ユーザーのデータが安全であることを保証する必要があります。Flight は、ウェブアプリケーションを保護するためのいくつかの機能を提供します。

## 理解

ウェブアプリケーションを構築する際に注意すべき一般的なセキュリティ脅威がいくつかあります。最も一般的な脅威には以下が含まれます：
- クロスサイトリクエストフォージェリ (CSRF)
- クロスサイトスクリプティング (XSS)
- SQL インジェクション
- クロスオリジンリソースシェアリング (CORS)

[Templates](/learn/templates) は、デフォルトで出力をエスケープすることで XSS を防ぎます。これを覚えておく必要はありません。[Sessions](/awesome-plugins/session) は、以下の説明のようにユーザーのセッションに CSRF トークンを保存することで CSRF を防ぐのに役立ちます。PDO でプリペアドステートメントを使用すると SQL インジェクション攻撃を防げます（または [PdoWrapper](/learn/pdo-wrapper) クラスの便利なメソッドを使用）。CORS は、`Flight::start()` が呼び出される前のシンプルなフックで処理できます。

これらの方法はすべて連携してウェブアプリケーションを安全に保つのに役立ちます。常にセキュリティのベストプラクティスを学び、理解することが重要です。

## 基本的な使用方法

### ヘッダー

HTTP ヘッダーは、ウェブアプリケーションを保護する最も簡単な方法の一つです。ヘッダーを使用してクリックジャッキング、XSS、その他の攻撃を防げます。これらのヘッダーをアプリケーションに追加する方法がいくつかあります。

ヘッダーのセキュリティを確認するための優れたウェブサイトは [securityheaders.com](https://securityheaders.com/) と [observatory.mozilla.org](https://observatory.mozilla.org/) です。以下のコードを設定した後、これらのウェブサイトでヘッダーが動作しているかを簡単に確認できます。

#### 手動で追加

`Flight\Response` オブジェクトの `header` メソッドを使用して、これらのヘッダーを手動で追加できます。
```php
// クリックジャッキングを防ぐために X-Frame-Options ヘッダーを設定
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// XSS を防ぐために Content-Security-Policy ヘッダーを設定
// 注意: このヘッダーは非常に複雑になる可能性があるため、
//  アプリケーションに適したインターネット上の例を参照してください
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// XSS を防ぐために X-XSS-Protection ヘッダーを設定
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// MIME スニッフィングを防ぐために X-Content-Type-Options ヘッダーを設定
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// リファラー情報の送信量を制御するために Referrer-Policy ヘッダーを設定
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// HTTPS を強制するために Strict-Transport-Security ヘッダーを設定
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// 使用可能な機能と API を制御するために Permissions-Policy ヘッダーを設定
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

これらは `routes.php` または `index.php` ファイルの先頭に追加できます。

#### フィルターとして追加

以下のフィルター/フックで追加することもできます：

```php
// フィルターでヘッダーを追加
Flight::before('start', function() {
	Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
	Flight::response()->header("Content-Security-Policy", "default-src 'self'");
	Flight::response()->header('X-XSS-Protection', '1; mode=block');
	Flight::response()->header('X-Content-Type-Options', 'nosniff');
	Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
	Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
	Flight::response()->header('Permissions-Policy', 'geolocation=()');
});
```

#### ミドルウェアとして追加

どのルートに適用するかを最大限に柔軟に提供するミドルウェアクラスとして追加することもできます。一般的に、これらのヘッダーはすべての HTML および API レスポンスに適用されるべきです。

```php
// app/middlewares/SecurityHeadersMiddleware.php

namespace app\middlewares;

use flight\Engine;

class SecurityHeadersMiddleware
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function before(array $params): void
	{
		$response = $this->app->response();
		$response->header('X-Frame-Options', 'SAMEORIGIN');
		$response->header("Content-Security-Policy", "default-src 'self'");
		$response->header('X-XSS-Protection', '1; mode=block');
		$response->header('X-Content-Type-Options', 'nosniff');
		$response->header('Referrer-Policy', 'no-referrer-when-downgrade');
		$response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		$response->header('Permissions-Policy', 'geolocation=()');
	}
}

// index.php またはルートがある場所
// FYI、この空の文字列グループはすべてのルートのグローバルミドルウェアとして機能します。
// もちろん、特定のルートにのみ追加することもできます。
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 他のルート
}, [ SecurityHeadersMiddleware::class ]);
```

### クロスサイトリクエストフォージェリ (CSRF)

クロスサイトリクエストフォージェリ (CSRF) は、悪意のあるウェブサイトがユーザーのブラウザからあなたのウェブサイトへのリクエストを送信できる攻撃の一種です。これにより、ユーザーの知識なしにウェブサイト上でアクションを実行できます。Flight はビルトインの CSRF 保護メカニズムを提供しませんが、ミドルウェアを使用して簡単に実装できます。

#### セットアップ

まず、CSRF トークンを生成し、ユーザーのセッションに保存する必要があります。その後、フォームでこのトークンを使用し、フォームが送信されたときにチェックします。セッションを管理するために [flightphp/session](/awesome-plugins/session) プラグインを使用します。

```php
// CSRF トークンを生成し、ユーザーのセッションに保存
// (Flight にセッションオブジェクトを作成してアタッチしたと仮定)
// 詳細はセッションドキュメントを参照
Flight::register('session', flight\Session::class);

// セッションごとに1つのトークンのみ生成（同じユーザーの複数のタブとリクエストで動作）
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### デフォルトの PHP Flight テンプレートを使用

```html
<!-- フォームで CSRF トークンを使用 -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- 他のフォームフィールド -->
</form>
```

##### Latte を使用

Latte テンプレートで CSRF トークンを出力するためのカスタム関数を設定することもできます。

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// 他の設定...

	// CSRF トークンを出力するためのカスタム関数を設定
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

これで Latte テンプレートで `csrf()` 関数を使用して CSRF トークンを出力できます。

```html
<form method="post">
	{csrf()}
	<!-- 他のフォームフィールド -->
</form>
```

#### CSRF トークンをチェック

CSRF トークンをチェックする方法がいくつかあります。

##### ミドルウェア

```php
// app/middlewares/CsrfMiddleware.php

namespace app\middleware;

use flight\Engine;

class CsrfMiddleware
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function before(array $params): void
	{
		if($this->app->request()->method == 'POST') {
			$token = $this->app->request()->data->csrf_token;
			if($token !== $this->app->session()->get('csrf_token')) {
				$this->app->halt(403, 'Invalid CSRF token');
			}
		}
	}
}

// index.php またはルートがある場所
use app\middlewares\CsrfMiddleware;

Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 他のルート
}, [ CsrfMiddleware::class ]);
```

##### イベントフィルター

```php
// このミドルウェアは、リクエストが POST リクエストかをチェックし、そうであれば CSRF トークンが有効かをチェックします
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// フォーム値から CSRF トークンをキャプチャ
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
			// または JSON レスポンスの場合
			Flight::jsonHalt(['error' => 'Invalid CSRF token'], 403);
		}
	}
});
```

### クロスサイトスクリプティング (XSS)

クロスサイトスクリプティング (XSS) は、悪意のあるフォーム入力がウェブサイトにコードを注入できる攻撃の一種です。これらの機会のほとんどは、エンドユーザーが記入するフォーム値から来ます。ユーザーの出力は **決して** 信頼しないでください！ すべてが世界最高のハッカーと仮定してください。彼らはページに悪意のある JavaScript や HTML を注入できます。このコードは、ユーザーの情報を盗んだり、ウェブサイト上でアクションを実行したりするために使用できます。Flight のビュークラスや [Latte](/awesome-plugins/latte) のような別のテンプレートエンジンを使用すると、出力をエスケープして XSS 攻撃を簡単に防げます。

```php
// ユーザーが賢く名前としてこれを使用しようとすると仮定
$name = '<script>alert("XSS")</script>';

// これが出力をエスケープします
Flight::view()->set('name', $name);
// 出力: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Latte をビュークラスとして登録した場合、これも自動的にエスケープされます
Flight::view()->render('template', ['name' => $name]);
```

### SQL インジェクション

SQL インジェクションは、悪意のあるユーザーがデータベースに SQL コードを注入できる攻撃の一種です。これにより、データベースから情報を盗んだり、データベース上でアクションを実行したりできます。再び、ユーザーの入力は **決して** 信頼しないでください！ 常に彼らが悪意を持っていると仮定してください。`PDO` オブジェクトでプリペアドステートメントを使用すると SQL インジェクションを防げます。

```php
// Flight::db() を PDO オブジェクトとして登録したと仮定
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// PdoWrapper クラスを使用する場合、1行で簡単に実行できます
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// ? プレースホルダー付きの PDO オブジェクトでも同じことができます
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

#### 非セキュアな例

以下は、SQL プリペアドステートメントを使用して以下のような無害な例から保護する理由です：

```php
// エンドユーザーがウェブフォームを記入。
// フォームの値に、ハッカーが以下のようなものを入力：
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// クエリが構築された後、以下のように見えます
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// 奇妙に見えますが、有効なクエリで動作します。実際、
// これはすべてのユーザーを返す非常に一般的な SQL インジェクション攻撃です。

var_dump($users); // これにより、データベース内のすべてのユーザーがダンプされ、単一のユーザー名だけではありません
```

### CORS

クロスオリジンリソースシェアリング (CORS) は、ウェブページ上の多くのリソース（例: フォント、JavaScript など）が、リソースが起源となったドメイン外の別のドメインからリクエストできるメカニズムです。Flight にはビルトインの機能はありませんが、`Flight::start()` メソッドが呼び出される前のフックで簡単に処理できます。

```php
// app/utils/CorsUtil.php

namespace app\utils;

class CorsUtil
{
	public function set(array $params): void
	{
		$request = Flight::request();
		$response = Flight::response();
		if ($request->getVar('HTTP_ORIGIN') !== '') {
			$this->allowOrigins();
			$response->header('Access-Control-Allow-Credentials', 'true');
			$response->header('Access-Control-Max-Age', '86400');
		}

		if ($request->method === 'OPTIONS') {
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_METHOD') !== '') {
				$response->header(
					'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD'
				);
			}
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS') !== '') {
				$response->header(
					"Access-Control-Allow-Headers",
					$request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS')
				);
			}

			$response->status(200);
			$response->send();
			exit;
		}
	}

	private function allowOrigins(): void
	{
		// 許可されたホストをここでカスタマイズ。
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		$request = Flight::request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $allowed, true) === true) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin", $request->getVar('HTTP_ORIGIN'));
		}
	}
}

// index.php またはルートがある場所
$CorsUtil = new CorsUtil();

// start が実行される前にこれを実行する必要があります。
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

### エラーハンドリング
プロダクションでは、攻撃者に情報を漏らさないよう、機密のエラー詳細を非表示にします。プロダクションでは、`display_errors` を `0` に設定してエラーを表示する代わりにログに記録します。

```php
// bootstrap.php または index.php で

// app/config/config.php にこれを追加
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // エラー表示を無効
    ini_set('log_errors', 1);     // エラーをログに記録
    ini_set('error_log', '/path/to/error.log');
}

// ルートまたはコントローラーで
// 制御されたエラーレスポンスのために Flight::halt() を使用
Flight::halt(403, 'Access denied');
```

### 入力サニタイズ
ユーザー入力を決して信頼しないでください。悪意のあるデータが忍び込まないよう、処理前に [filter_var](https://www.php.net/manual/en/function.filter-var.php) を使用してサニタイズします。

```php

// $_POST['input'] と $_POST['email'] 付きの $_POST リクエストを仮定

// 文字列入力をサニタイズ
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// メールをサニタイズ
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### パスワードハッシュ
PHP のビルトイン関数である [password_hash](https://www.php.net/manual/en/function.password-hash.php) と [password_verify](https://www.php.net/manual/en/function.password-verify.php) を使用して、パスワードを安全に保存し検証します。パスワードは平文で保存せず、可逆的な方法で暗号化もしないでください。ハッシュ化により、データベースが侵害されても実際のパスワードは保護されます。

```php
$password = Flight::request()->data->password;
// 保存時（例: 登録時）にパスワードをハッシュ
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// パスワードを検証（例: ログイン時）
if (password_verify($password, $stored_hash)) {
    // パスワードが一致
}
```

### レート制限
キャッシュを使用してリクエストレートを制限し、ブルートフォース攻撃やサービス拒否攻撃から保護します。

```php
// flightphp/cache をインストールして登録したと仮定
// フィルターで flightphp/cache を使用
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Too many requests');
    }
    
    $cache->set($key, $attempts + 1, 60); // 60秒後にリセット
});
```

## 関連項目
- [Sessions](/awesome-plugins/session) - ユーザーのセッションを安全に管理する方法。
- [Templates](/learn/templates) - 出力を自動エスケープして XSS を防ぐテンプレートの使用。
- [PDO Wrapper](/learn/pdo-wrapper) - プリペアドステートメント付きの簡略化されたデータベースインタラクション。
- [Middleware](/learn/middleware) - セキュリティヘッダーの追加プロセスを簡略化するためのミドルウェアの使用方法。
- [Responses](/learn/responses) - セキュアなヘッダー付きのカスタム HTTP レスポンス。
- [Requests](/learn/requests) - ユーザー入力を処理およびサニタイズする方法。
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - 入力サニタイズのための PHP 関数。
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - セキュアなパスワードハッシュのための PHP 関数。
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - ハッシュされたパスワードを検証するための PHP 関数。

## トラブルシューティング
- Flight Framework のコンポーネントに関する問題のトラブルシューティング情報については、上記の「関連項目」セクションを参照してください。

## 変更履歴
- v3.1.0 - CORS、エラーハンドリング、入力サニタイズ、パスワードハッシュ、レート制限に関するセクションを追加。
- v2.0 - XSS を防ぐためのデフォルトビューのエスケープを追加。