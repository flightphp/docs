# セキュリティ

Webアプリケーションに関するセキュリティは重要です。アプリケーションが安全であり、ユーザーのデータが安全であることを確認したいと思います。Flight は、Webアプリケーションを保護するための機能をいくつか提供しています。

## ヘッダー

HTTPヘッダーはWebアプリケーションを保護する最も簡単な方法の1つです。ヘッダーを使用して、クリックジャッキング、XSS、およびその他の攻撃を防ぐことができます。これらのヘッダーをアプリケーションに追加する方法はいくつかあります。

セキュリティヘッダーを確認するための2つの優れたウェブサイトは、[securityheaders.com](https://securityheaders.com/) と [observatory.mozilla.org](https://observatory.mozilla.org/) です。

### 手動で追加

`Flight\Response` オブジェクトの `header` メソッドを使用してこれらのヘッダーを手動で追加できます。
```php
// クリックジャッキングを防ぐために X-Frame-Options ヘッダーを設定
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// XSSを防ぐために Content-Security-Policy ヘッダーを設定
// 注：このヘッダーは非常に複雑になる可能性があるため、
// アプリケーション向けのインターネット上の例を参照することが望ましい
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// XSS を防ぐために X-XSS-Protection ヘッダーを設定
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// MIME スニッフィングを防ぐために X-Content-Type-Options ヘッダーを設定
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// リファラーポリシーを設定して、送信されるリファラー情報の量を制御
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// HTTPS を強制するために Strict-Transport-Security ヘッダーを設定
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// 使用できる機能やAPIを制御するために Permissions-Policy ヘッダーを設定
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

これらは `bootstrap.php` または `index.php` ファイルの先頭に追加できます。

### フィルターとして追加

次のようなフィルター/フックでこれらを追加することもできます:

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

これらをミドルウェアクラスとして追加することもできます。これはコードを清潔で整理された状態に保つ方法です。

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
// この空の文字列グループは、すべてのルートのためのグローバルミドルウェアとして機能します。
// もちろん、同じようにして特定のルートにのみ追加することもできます。
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 各種ルート
}, [ new SecurityHeadersMiddleware() ]);
```


## クロスサイトリクエストフォージェリ（CSRF）

クロスサイトリクエストフォージェリ（CSRF）は、悪意のあるWebサイトがユーザーのブラウザに対してあなたのWebサイトにリクエストを送信させる攻撃のタイプです。これにより、ユーザーの知識を持たずにWebサイト上でアクションを実行することができます。Flight は組込みの CSRF 保護機構を提供していませんが、ミドルウェアを使用して簡単に独自の保護を実装できます。

### 設定

まず、CSRF トークンを生成してユーザーのセッションに保存する必要があります。フォームでこのトークンを使用し、フォームが送信されたときに確認できます。

```php
// CSRF トークンを生成してユーザーのセッションに保存
// (Flight が作成されたセッションオブジェクトを想定しています)
// セッションごとに単一のトークンを生成する必要があります（同じユーザーの複数のタブとリクエストで機能するためです）
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- フォームで CSRF トークンを使用 -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- その他のフォームフィールド -->
</form>
```

#### Latte を使用する

Latte テンプレートで CSRF トークンを出力するカスタム関数を設定できます。

```php
// CSRF トークンを出力するカスタム関数を設定
// 注：View はビューエンジンとして Latte が構成されています
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

そして、Latte テンプレートで `csrf()` 関数を使用して CSRF トークンを出力できます。

```html
<form method="post">
	{csrf()}
	<!-- その他のフォームフィールド -->
</form>
```

短くて簡単ですね？

### CSRF トークンのチェック

イベントフィルターを使用して CSRF トークンをチェックできます:

```php
// このミドルウェアは、リクエストが POST リクエストかどうかをチェックし、POST リクエストの場合は CSRF トークンが有効かどうかをチェックします
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// フォーム値から csrf トークンを取得
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
		}
	}
});
```

またはミドルウェアクラスを使用することもできます:

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

// index.php またはルートがある場所
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// その他のルート
}, [ new CsrfMiddleware() ]);
```

## クロスサイトスクリプティング（XSS）

クロスサイトスクリプティング（XSS）は、悪意のあるWebサイトがコードをあなたのWebサイトに注入できる攻撃のタイプです。これらの機会のほとんどは、エンドユーザーが入力するフォーム値から来ます。ユーザーからの出力を**決して**信頼しないでください！常に彼ら全員が世界で最も優れたハッカーであると仮定してください。彼らはあなたのページに悪意のあるJavaScriptやHTMLを注入することができます。このコードはユーザーから情報を盗むか、またはあなたのウェブサイト上でアクションを実行するために使用されるかもしれません。Flight のビュークラスを使用すると、XSS攻撃を防ぐために簡単に出力をエスケープできます。

```php
// ユーザーが賢いと想定して、これを名前に使用しようとしているとしましょう
$name = '<script>alert("XSS")</script>';

// これは出力をエスケープします
Flight::view()->set('name', $name);
// このように出力されます：&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Latte をビュークラスとして使用している場合、これも自動的にエスケープされます。
Flight::view()->render('template', ['name' => $name]);
```

## SQLインジェクション

SQLインジェクションは、悪意のあるユーザーがSQLコードをデータベースに注入できる攻撃のタイプです。これは、データベースから情報を盗むか、データベースでアクションを実行するために使用できます。再び、ユーザーからの入力を**決して**信頼しないでください！常に彼らがあなたを攻撃しようとしていると仮定してください。`PDO` オブジェクト内でプリペアドステートメントを使用することで、SQLインジェクションを防ぐことができます。

```php
// Flight::db() が PDO オブジェクトとして登録されていると仮定して
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// PdoWrapper クラスを使用して、これを1行で簡単に行うことができます
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// 同様のことを、PDO オブジェクトで ? プレースホルダを使用して行うこともできます
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// 決してこんなことをしないで約束してください...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// もし $username = "' OR 1=1; -- "; の場合はどうなるでしょうか？
// クエリが構築されると次のようになります
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// 奇妙に見えるかもしれませんが、これは動作する有効なクエリです。実際、
// これは全てのユーザーを返す非常に一般的なSQLインジェクション攻撃です。
```

## CORS

クロスオリジンリソース共有（CORS）は、ウェブページ上の多くのリソース（フォント、JavaScriptなど）が、元のリソースが存在するドメインの外部ドメインからリクエストされるメカニズムです。Flight には組込みの機能はありませんが、`Flight::start()` メソッドが呼び出される前にフックを実行するためのツールが簡単に利用できます。

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
		// ここで許可するホストをカスタマイズしてください。
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

// index.php またはルートがある場所## 結論

セキュリティは重要で、Webアプリケーションを安全にすることが重要です。Flight は、Webアプリケーションを保護するための機能をいくつか提供していますが、常に用心深く、ユーザーのデータを安全に保つためにできる限りのことを行っていることを確認することが重要です。常に最悪の状況を想定し、ユーザーからの入力を信頼しないでください。出力をエスケープし、SQLインジェクションを防ぐためにプリペアドステートメントを使用してください。CSRF および CORS 攻撃からルートを保護するためにミドルウェアを常に使用してください。これらすべてを行うことで、安全なWebアプリケーションの構築への道のりは確実になります。