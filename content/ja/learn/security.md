# セキュリティ

ウェブアプリケーションに関してセキュリティは非常に重要です。アプリケーションが安全であり、ユーザーのデータが保護されていることを確認したいです。 Flight はウェブアプリケーションをセキュアにするための機能を多数提供しています。

## ヘッダー

HTTPヘッダーはウェブアプリケーションをセキュアにするための最も簡単な方法の1つです。 ヘッダーを使用して、クリックジャッキング、XSS、その他の攻撃を防ぐことができます。 これらのヘッダーをアプリケーションに追加する方法はいくつかあります。

セキュリティヘッダーのセキュリティをチェックするための素晴らしいウェブサイトは[securityheaders.com](https://securityheaders.com/)と[observatory.mozilla.org](https://observatory.mozilla.org/)です。

### 手動で追加

`Flight\Response` オブジェクトの `header` メソッドを使用してこれらのヘッダーを手動で追加できます。
```php
// X-Frame-Options ヘッダーを設定してクリックジャッキングを防止
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// XSS を防ぐために Content-Security-Policy ヘッダーを設定
// 注：このヘッダーは非常に複雑になる可能性があるため、
//  アプリケーション用のインターネット上の例を参照する必要があります
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// XSS を防ぐために X-XSS-Protection ヘッダーを設定
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// MIME スニッフィングを防ぐために X-Content-Type-Options ヘッダーを設定
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// リファラーポリシーを設定して送信されるリファラー情報の制御を行う
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// HTTPS を強制するために Strict-Transport-Security ヘッダーを設定
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// 使用できる機能や API を制御するために Permissions-Policy ヘッダーを設定
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

これらは `bootstrap.php` または `index.php` ファイルの先頭に追加できます。

### フィルターとして追加

以下のようにフィルターやフックでこれらを追加することもできます:

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

これらをミドルウェアクラスとして追加することもできます。これはコードをきれいに整理し保持する良い方法です。

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

// index.php もしくはルートがあるファイル
// この空のグループはすべてのルートに対するグローバルミドルウェアとして機能します。
// もちろん、同じことをして、特定のルートにのみ追加することもできます。
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// もっとたくさんのルート
}, [ new SecurityHeadersMiddleware() ]);
```

## クロスサイトリクエストフォージェリ (CSRF)

クロスサイトリクエストフォージェリ (CSRF) は、悪意のあるウェブサイトがユーザーのブラウザからあなたのウェブサイトにリクエストを送信させる攻撃の一種です。 これにより、ユーザーの知識を知らずにあなたのウェブサイトでアクションを実行できます。 Flight は組み込みの CSRF 保護メカニズムを提供していませんが、ミドルウェアを使用して簡単に独自の保護を実装することができます。

### セットアップ

まず、CSRF トークンを生成してユーザーのセッションに保存する必要があります。 その後、このトークンをフォームで使用し、フォームが送信されたときに確認できます。

```php
// CSRF トークンを生成してユーザーのセッションに保存
// (Flight にセッションオブジェクトが作成されて、アタッチされていると仮定)
// セッションごとに 1 つのトークンだけを生成するだけで十分です（同じユーザーにとって複数のタブとリクエストで機能します）
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- フォーム内で CSRF トークンを使用 -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- 他のフォームフィールド -->
</form>
```

#### Latte の使用

カスタム関数を設定して、Latte テンプレートで CSRF トークンを出力することもできます。

```php
// カスタム関数を設定して CSRF トークンを出力
// 注意: ビューはビューエンジンとして Latte が設定されています
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

そして、Latte テンプレートでは `csrf()` 関数を使用して CSRF トークンを出力できます。

```html
<form method="post">
	{csrf()}
	<!-- 他のフォームフィールド -->
</form>
```

短くてシンプルですね?

### CSRF トークンの確認

イベントフィルターを使用して CSRF トークンを確認することができます:

```php
// このミドルウェアは、リクエストが POST リクエストであるかどうかを確認し、そうであれば CSRF トークンが有効かどうかをチェックします
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// フォームの値から csrf トークンをキャプチャ
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
		}
	}
});
```

あるいは、ミドルウェアクラスを使用することもできます:

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
				Flight::halt(403, 'Invalid CSRF token');
			}
		}
	}
}

// index.php もしくはルートがあるファイル
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// もっとたくさんのルート
}, [ new CsrfMiddleware() ]);
```

## クロスサイトスクリプティング (XSS)

クロスサイトスクリプティング (XSS) は、悪意のあるウェブサイトがコードをあなたのウェブサイトに注入する攻撃の一種です。 ほとんどの機会は、エンドユーザーが入力するフォーム値から来ます。 ユーザーの出力からは**決して**信用しないでください！ 常にすべてのユーザーが世界で最高のハッカーであると仮定してください。 これにより、ユーザーから情報を盗むか、あなたのウェブサイトでアクションを実行できます。 Flight のビュークラスを使用すると、XSS 攻撃を防ぐために出力を簡単にエスケープできます。

```php
// ユーザーが賢いとして、これを名前として使用しようとしていると仮定します
$name = '<script>alert("XSS")</script>';

// これにより、出力がエスケープされます
Flight::view()->set('name', $name);
// これが出力されます: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// View クラスとして Latte を使用している場合、これも自動的にエスケープされます。
Flight::view()->render('template', ['name' => $name]);
```

## SQLインジェクション

SQLインジェクションは、悪意のあるユーザーが SQL コードをあなたのデータベースに注入する攻撃の一種です。 これにより、データベースから情報を盗んだり、データベースでアクションを実行したりすることができます。 もう一度言いますが、ユーザーからの入力を**決して**信頼しないでください！ 常に彼らが出血を望んでいると仮定してください。 `PDO` オブジェクト内でプリペアドステートメントを使用することにより、SQLインジェクションを防ぐことができます。

```php
// Flight::db() が PDO オブジェクトとして登録されていると仮定
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// PdoWrapper クラスを使用して、これは簡単に 1 行で行うことができます
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// 同じことを ? プレースホルダを使用して PDO オブジェクトで行うこともできます
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// 決してこれを行わないと約束してください...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// なぜなら、もし $username = "' OR 1=1; -- "; だった場合、
// クエリは次のようになります
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// これは奇妙に見えるかもしれませんが、有効なクエリで、実際には動作します。
// 実際、これはすべてのユーザーを返す非常に一般的な SQLインジェクション攻撃です。
```

## CORS

クロスオリジンリソース共有 (CORS) は、ウェブページの多くのリソース（フォント、JavaScript など）を、リソース元のドメインと異なる別のドメインからリクエストするメカニズムです。 Flight は組み込みの機能を持っていませんが、これはミドルウェアによって簡単に CSRF と同様のイベントフィルターを使用して処理することができます。

```php
// app/middleware/CorsMiddleware.php

namespace app\middleware;

class CorsMiddleware
{
	public function before(array $params): void
	{
		$response = Flight::response();
		if (isset($_SERVER['HTTP_ORIGIN'])) {
			$this->allowOrigins();
			$response->header('Access-Control-Allow-Credentials: true');
			$response->header('Access-Control-Max-Age: 86400');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
				$response->header(
					'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS'
				);
			}
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
				$response->header(
					"Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
				);
			}
			$response->send();
			exit(0);
		}
	}

	private function allowOrigins(): void
	{
		// ここで許可されるホストをカスタマイズしてください。
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		if (in_array($_SERVER['HTTP_ORIGIN'], $allowed)) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		}
	}
}

// index.php もしくはルートがあるファイル
Flight::route('/users', function() {
	```markdown
# セキュリティ

ウェブアプリケーションに関してセキュリティは非常に重要です。アプリケーションが安全であり、ユーザーのデータが保護されていることを確認したいです。 Flight はウェブアプリケーションをセキュアにするための機能を多数提供しています。

## ヘッダー

HTTPヘッダーはウェブアプリケーションをセキュアにするための最も簡単な方法の1つです。 ヘッダーを使用して、クリックジャッキング、XSS、その他の攻撃を防ぐことができます。 これらのヘッダーをアプリケーションに追加する方法はいくつかあります。

セキュリティヘッダーのセキュリティをチェックするための素晴らしいウェブサイトは[securityheaders.com](https://securityheaders.com/)と[observatory.mozilla.org](https://observatory.mozilla.org/)です。

### 手動で追加

`Flight\Response` オブジェクトの `header` メソッドを使用してこれらのヘッダーを手動で追加できます。

```php
// X-Frame-Options ヘッダーを設定してクリックジャッキングを防止
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// XSS を防ぐために Content-Security-Policy ヘッダーを設定
// 注：このヘッダーは非常に複雑になる可能性があるため、
//  アプリケーション用のインターネット上の例を参照する必要があります
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// XSS を防ぐために X-XSS-Protection ヘッダーを設定
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// MIME スニッフィングを防ぐために X-Content-Type-Options ヘッダーを設定
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// リファラーポリシーを設定して送信されるリファラー情報の制御を行う
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// HTTPS を強制するために Strict-Transport-Security ヘッダーを設定
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// 使用できる機能や API を制御するために Permissions-Policy ヘッダーを設定
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

これらは `bootstrap.php` または `index.php` ファイルの先頭に追加できます。

### フィルターとして追加

以下のようにフィルターやフックでこれらを追加することもできます:

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

これらをミドルウェアクラスとして追加することもできます。これはコードをきれいに整理し保持する良い方法です。

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

// index.php もしくはルートがあるファイル
// この空のグループはすべてのルートに対するグローバルミドルウェアとして機能します。
// もちろん、同じことをして、特定のルートにのみ追加することもできます。
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// もっとたくさんのルート
}, [ new SecurityHeadersMiddleware() ]);
```

## クロスサイトリクエストフォージェリ (CSRF)

クロスサイトリクエストフォージェリ (CSRF) は、悪意のあるウェブサイトがユーザーのブラウザからあなたのウェブサイトにリクエストを送信させる攻撃の一種です。 これにより、ユーザーの知識を知らずにあなたのウェブサイトでアクションを実行できます。 Flight は組み込みの CSRF 保護メカニズムを提供していませんが、ミドルウェアを使用して簡単に独自の保護を実装することができます。

### セットアップ

まず、CSRF トークンを生成してユーザーのセッションに保存する必要があります。 その後、このトークンをフォームで使用し、フォームが送信されたときに確認できます。

```php
// CSRF トークンを生成してユーザーのセッションに保存
// (Flight にセッションオブジェクトが作成されて、アタッチされていると仮定)
// セッションごとに 1 つのトークンだけを生成するだけで十分です（同じユーザーにとって複数のタブとリクエストで機能します）
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- フォーム内で CSRF トークンを使用 -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- 他のフォームフィールド -->
</form>
```

#### Using Latte

カスタム関数を設定して、Latte テンプレートで CSRF トークンを出力することもできます。

```php
// Set a custom function to output the CSRF token
// Note: View has been configured with Latte as the view engine
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

そして、Latte テンプレートでは `csrf()` 関数を使用して CSRF トークンを出力できます。

```html
<form method="post">
	{csrf()}
	<!-- 他のフォームフィールド -->
</form>
```

短くてシンプルですね?

### Check the CSRF Token

You can check the CSRF token using event filters:

```php
// This middleware checks if the request is a POST request and if it is, it checks if the CSRF token is valid
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// capture the csrf token from the form values
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
		}
	}
});
```

Or you can use a middleware class:

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
				Flight::halt(403, 'Invalid CSRF token');
			}
		}
	}
}

// index.php or wherever you have your routes
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// more routes
}, [ new CsrfMiddleware() ]);
```

## クロスサイトスクリプティング (XSS)

クロスサイトスクリプティング (XSS) は、悪意のあるウェブサイトがコードをあなたのウェブサイトに注入する攻撃の一種です。 ほとんどの機会は、エンドユーザーが入力するフォーム値から来ます。 ユーザーの出力からは**決して**信用しないでください！ 常にすべてのユーザーが世界で最高のハッカーであると仮定してください。 これにより、ユーザーから情報を盗むか、あなたのウェブサイトでアクションを実行できます。 Flight のビュークラスを使用すると、XSS 攻撃を防ぐために出力を簡単にエスケープできます。

```php
// Let's assume the user is clever as tries to use this as their name
$name = '<script>alert("XSS")</script>';

// This will escape the output
Flight::view()->set('name', $name);
// This will output: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// If you use something like Latte registered as your view class, it will also auto escape this.
Flight::view()->render('template', ['name' => $name]);
```

## SQLインジェクション

SQLインジェクションは、悪意のあるユーザーが SQL コードをあなたのデータベースに注入する攻撃の一種です。 これにより、データベースから情報を盗んだり、データベースでアクションを実行したりすることができます。 もう一度言いますが、ユーザーからの入力を**決して**信頼しないでください！ 常に彼らが出血を望んでいると仮定してください。 `PDO` オブジェクト内でプリペアドステートメントを使用することにより、SQLインジェクションを防ぐことができます。

```php
// Assuming you have Flight::db() registered as your PDO object
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// If you use the PdoWrapper class, this can easily be done in one line
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// You can do the same thing with a PDO object with ? placeholders
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Just promise you will never EVER do something like this...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// because what if $username = "' OR 1=1; -- ";
// After the query is build it looks like this
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// It looks strange, but it's a valid query that will work. In fact,
// it's a very common SQL injection attack that will return all users.
```

## CORS

クロスオリジンリソース共有 (CORS) は、ウェブページの多くのリソース（フォント、JavaScript など）を、リソース元のドメインと異なる別のドメインからリクエストするメカニズムです。 Flight は組み込みの機能を持っていませんが、これはミドルウェアによって簡単に CSRF と同様のイベントフィルターを使用して処理することができます。

```php
// app/middleware/CorsMiddleware.php

namespace app\middleware;

class CorsMiddleware
{
	public function before(array $params): void
	{
		$response = Flight::response();
		if (isset($_SERVER['HTTP_ORIGIN'])) {
			$this->allowOrigins();
			$response->header('Access-Control-Allow-Credentials: true');
			$response->header('Access-Control-Max-Age: 86400');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
				$response->header(
					'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS'
				);
			}
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
				$response->header(
					"Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
				);
			}
			$response->send();
			exit(0);
		}
	}

	private function allowOrigins(): void
	{
		// customize your allowed hosts here.
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		if (in_array($_SERVER['HTTP_ORIGIN'], $allowed)) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		}
	}
}

// index.php もしくはルートがあるファイル
Flight::route('/users', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(new CorsMiddleware());
```

## 結論

セキュリティは非常に重要です。ウェブアプリケーションが安全であることを確認することが重要です。 Flight はウェブアプリケーションをセキュアにするための機能を多数提供していますが、常に注意深く、ユーザーのデータを安全に保つためにできる限りのことを行うことが重要です。 常に最悪の状況を想定して、ユーザーからの入力を決して信頼しないでください。 出力を必ずエスケープして、SQLインジェクションを防ぐためにプリペアドステートメントを使用してください。 CSRF や CORS の攻撃からルートを保護するために常にミドルウェアを使用してください。 これらのすべてを行うと、安全なウェブアプリケーションを構築するための手順が示されています。