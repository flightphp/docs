# セキュリティ

セキュリティはウェブアプリケーションにとって非常に重要です。アプリケーションが安全で、ユーザーのデータが守られていることを確認したいでしょう。Flightは、ウェブアプリケーションのセキュリティを確保するための多くの機能を提供しています。

## ヘッダー

HTTPヘッダーは、ウェブアプリケーションを保護する最も簡単な方法の1つです。ヘッダーを使用して、クリックジャッキング、XSS、およびその他の攻撃を防ぐことができます。これらのヘッダーをアプリケーションに追加する方法はいくつかあります。

ヘッダーのセキュリティを確認するための優れたウェブサイトは、[securityheaders.com](https://securityheaders.com/) と [observatory.mozilla.org](https://observatory.mozilla.org/) です。

### 手動で追加

`Flight\Response`オブジェクトの`header`メソッドを使用して、これらのヘッダーを手動で追加できます。
```php
// クリックジャッキングを防ぐためにX-Frame-Optionsヘッダーを設定
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// XSSを防ぐためにContent-Security-Policyヘッダーを設定
// 注意: このヘッダーは非常に複雑になることがあるので、
//  アプリケーションのためにインターネット上の例を参照することをお勧めします
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// XSSを防ぐためにX-XSS-Protectionヘッダーを設定
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// MIMEスニッフィングを防ぐためにX-Content-Type-Optionsヘッダーを設定
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// どれだけのリファラー情報が送信されるかを制御するためにReferrer-Policyヘッダーを設定
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// HTTPSを強制するためにStrict-Transport-Securityヘッダーを設定
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// 使用できる機能とAPIを制御するためにPermissions-Policyヘッダーを設定
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

これらは、`bootstrap.php`や`index.php`ファイルの先頭に追加できます。

### フィルターとして追加

次のようなフィルター/フックで追加することもできます：

```php
// フィルター内でヘッダーを追加
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

### ミドルウェアとして追加

ミドルウェアクラスとして追加することもできます。これは、コードをクリーンで整理する良い方法です。

```php
// app/middleware/SecurityHeadersMiddleware.php

namespace app\middleware;

class SecurityHeadersMiddleware
{
	public function before(array $params): void
	{
		Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
		Flight::response()->header("Content-Security-Policy", "default-src 'self'");
		Flight::response()->header('X-XSS-Protection', '1; mode=block');
		Flight::response()->header('X-Content-Type-Options', 'nosniff');
		Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
		Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		Flight::response()->header('Permissions-Policy', 'geolocation=()');
	}
}

// index.php またはルートがある場所
// FYI、この空の文字列グループはすべてのルートに対するグローバルミドルウェアとして機能します。
// もちろん、同じことをして特定のルートにのみ追加することもできます。
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// その他のルート
}, [ new SecurityHeadersMiddleware() ]);
```

## クロスサイトリクエストフォージェリ (CSRF)

クロスサイトリクエストフォージェリ (CSRF) は、悪意のあるウェブサイトがユーザーのブラウザを使用して、あなたのウェブサイトにリクエストを送信させる攻撃の一種です。これを使用して、ユーザーの知らないうちにあなたのウェブサイトでアクションを実行することができます。Flightは、組み込みのCSRF保護メカニズムを提供していませんが、ミドルウェアを使用して自分自身のものを簡単に実装できます。

### セットアップ

まず、CSRFトークンを生成し、ユーザーのセッションに保存する必要があります。その後、このトークンをフォームで使用し、フォームが送信されるときに確認します。

```php
// CSRFトークンを生成し、ユーザーのセッションに保存
// (Flightにセッションオブジェクトを作成してアタッチしたと仮定)
// 詳細についてはセッションのドキュメントを参照してください
Flight::register('session', \Ghostff\Session\Session::class);

// セッションあたり1つのトークンを生成する必要があります（複数のタブや同じユーザーのリクエストで機能するため）
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- フォームにCSRFトークンを使用 -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- 他のフォームフィールド -->
</form>
```

#### Latteを使用する

独自の関数を定義してCSRFトークンをLatteテンプレートに出力することもできます。

```php
// CSRFトークンを出力するカスタム関数を設定
// 注意: ViewはLatteをビューエンジンとして構成されています
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

これで、Latteテンプレート内で`csrf()`関数を使用してCSRFトークンを出力できます。

```html
<form method="post">
	{csrf()}
	<!-- 他のフォームフィールド -->
</form>
```

短くてシンプルですよね？

### CSRFトークンの確認

イベントフィルターを使用してCSRFトークンを確認できます：

```php
// このミドルウェアはリクエストがPOSTリクエストであるかをチェックし、
// それが正しければCSRFトークンが有効であるかを確認します
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// フォームの値からCSRFトークンを取得
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, '無効なCSRFトークン');
			// あるいはJSONレスポンスのために
			Flight::jsonHalt(['error' => '無効なCSRFトークン'], 403);
		}
	}
});
```

または、ミドルウェアクラスを使用することもできます：

```php
// app/middleware/CsrfMiddleware.php

namespace app\middleware;

class CsrfMiddleware
{
	public function before(array $params): void
	{
		if(Flight::request()->method == 'POST') {
			$token = Flight::request()->data->csrf_token;
			if($token !== Flight::session()->get('csrf_token')) {
				Flight::halt(403, '無効なCSRFトークン');
			}
		}
	}
}

// index.php またはルートがある場所
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// その他のルート
}, [ new CsrfMiddleware() ]);
```

## クロスサイトスクリプティング (XSS)

クロスサイトスクリプティング (XSS) は、悪意のあるウェブサイトがあなたのウェブサイトにコードを挿入できるようになる攻撃の一種です。これらの機会のほとんどは、あなたのエンドユーザーが記入するフォームの値から来ます。ユーザーからの出力を**決して**信頼しないでください！彼らが世界最高のハッカーであると常に仮定してください。彼らはあなたのページに悪意のあるJavaScriptやHTMLを挿入できます。このコードは、ユーザーの情報を盗むために使用されたり、あなたのウェブサイトでアクションを実行したりできます。Flightのビュークラスを使用することで、出力を簡単にエスケープしてXSS攻撃を防ぐことができます。

```php
// ユーザーが賢いと仮定してこの名前を使用しようとした場合
$name = '<script>alert("XSS")</script>';

// これは出力をエスケープします
Flight::view()->set('name', $name);
// これは出力されます: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// もしあなたがビュークラスとして登録されたLatteのようなものを使用すると、
// それも自動的にエスケープされます。
Flight::view()->render('template', ['name' => $name]);
```

## SQLインジェクション

SQLインジェクションは、悪意のあるユーザーがデータベースにSQLコードを注入できるようになる攻撃の一種です。これを使用して、データベースから情報を盗んだり、データベースでアクションを実行したりできます。再び、ユーザーからの入力を**決して**信頼しないでください！彼らが血を求めていると常に仮定してください。`PDO`オブジェクトでプリペアドステートメントを使用することで、SQLインジェクションを防ぐことができます。

```php
// Flight::db()があなたのPDOオブジェクトとして登録されていると仮定
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// PdoWrapperクラスを使用すると、これを1行で簡単に行えます
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// ? プレースホルダーを使用するPDOオブジェクトでも同様のことができます
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// こんなことは絶対にしないと約束してください...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// だってもし$username = "' OR 1=1; -- "; だったらどうするのでしょうか？
// クエリが構築された後は、次のようになります
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// 不思議に見えますが、これは有効なクエリで機能します。実際、
// これはすべてのユーザーを返す非常に一般的なSQLインジェクション攻撃です。
```

## CORS

クロスオリジンリソース共有 (CORS) は、ウェブページ上の多くのリソース（フォント、JavaScriptなど）が、リソースの発信元ドメイン外の別のドメインからリクエストされることを可能にするメカニズムです。Flightは組み込み機能を提供していませんが、`Flight::start()`メソッドが呼び出される前に実行するフックで簡単に処理できます。

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
		// ここで許可するホストをカスタマイズします。
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

// これはstartが実行される前に実行される必要があります。
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

## エラーハンドリング
攻撃者に情報を漏らさないように、プロダクション環境では敏感なエラーの詳細を非表示にします。

```php
// bootstrap.php または index.php で

// flightphp/skeletonでは、これは app/config/config.php にあります
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // エラー表示を無効にする
    ini_set('log_errors', 1);     // エラーをログに記録する
    ini_set('error_log', '/path/to/error.log');
}

// ルートやコントローラー内で
// コントロールされたエラー応答には Flight::halt() を使用
Flight::halt(403, 'アクセス拒否');
```

## 入力のサニタイズ
ユーザー入力を信頼しないでください。悪意のあるデータが入り込まないように、処理の前にサニタイズします。

```php

// $_POST['input'] および $_POST['email'] を持つ$_POSTリクエストがあると仮定します

// 文字列入力をサニタイズ
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// メールをサニタイズ
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

## パスワードのハッシュ化
パスワードを安全に保存し、安全に確認します。PHPの組み込み関数を使用してください。

```php
$password = Flight::request()->data->password;
// パスワードを保存する際にハッシュ化します（例: 登録時）
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// パスワードを確認します（例: ログイン時）
if (password_verify($password, $stored_hash)) {
    // パスワードが一致します
}
```

## レート制限
キャッシュを使用してリクエスト率を制限し、ブルートフォース攻撃から保護します。

```php
// flightphp/cache がインストールされ、登録されていると仮定します
// ミドルウェア内で flightphp/cache を使用
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'リクエストが多すぎます');
    }
    
    $cache->set($key, $attempts + 1, 60); // 60秒後にリセット
});
```

## 結論

セキュリティは非常に重要であり、ウェブアプリケーションが安全であることを確認することが重要です。Flightはウェブアプリケーションを保護するための多くの機能を提供しますが、常に警戒し、ユーザーのデータを守るためにできることをすべて行うことが重要です。最悪の事態を常に想定し、ユーザーからの入力を決して信頼しないでください。出力は常にエスケープし、SQLインジェクションを防ぐためにプリペアドステートメントを使用してください。CSRFやCORS攻撃からルートを保護するために、常にミドルウェアを使用してください。これらすべてを実行すれば、安全なウェブアプリケーションを構築する道のりが開けるでしょう。