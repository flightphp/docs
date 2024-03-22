# セキュリティ

Webアプリケーションに関するセキュリティは重要です。アプリケーションが安全であり、ユーザーデータが安全であることを確認したいと思います。Flightは、Webアプリケーションのセキュリティを強化するためのさまざまな機能を提供しています。

## ヘッダー

HTTPヘッダーは Web アプリケーションをセキュアにする最も簡単な方法の1つです。ヘッダーを使用して、クリックジャッキング、XSS、およびその他の攻撃を防ぐことができます。これらのヘッダーをアプリケーションに追加する方法はいくつかあります。

セキュリティヘッダーのセキュリティを確認するための2つの優れたウェブサイトは[securityheaders.com](https://securityheaders.com/)と[observatory.mozilla.org](https://observatory.mozilla.org/)です。

### 手動での追加

`Flight\Response` オブジェクトの `header` メソッドを使用してこれらのヘッダーを手動で追加できます。
```php
// クリックジャンキングを防ぐために X-Frame-Options ヘッダーを設定
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// XSS を防ぐために Content-Security-Policy ヘッダーを設定
// 注：このヘッダーは非常に複雑になる場合があるため、
// アプリケーション向けのインターネット上の例を参照してください
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// XSS を防ぐために X-XSS-Protection ヘッダーを設定
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// MIME スニッフィングを防ぐために X-Content-Type-Options ヘッダーを設定
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// リファラーポリシーを設定してリファラー情報の送信量を制御
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// HTTPS を強制するために Strict-Transport-Security ヘッダーを設定
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// 使用可能な機能や API を制御するために Permissions-Policy ヘッダーを設定
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

これらは `bootstrap.php` または `index.php` ファイルの先頭に追加することができます。

### フィルターとして追加

次のようなフィルター/フックに追加することもできます: 

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

### ミドルウェアとして追加

ミドルウェアクラスとしても追加できます。これは、コードをきれいで整理された状態に保つ良い方法です。

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
// 空の文字列グループは、すべてのルートに対するグローバルミドルウェアとして機能します。
// もちろん、同じことをしたり、特定のルートにのみこれを追加することもできます。
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// その他のルート
}, [ new SecurityHeadersMiddleware() ]);
```


## CSRF（クロスサイトリクエストフォージェリ）

CSRFは、悪意のあるウェブサイトがユーザーのブラウザにリクエストを送信させる攻撃の一種です。これにより、ユーザーの知識を持たずにあなたのウェブサイトでアクションを実行できるようになります。Flightには組み込みのCSRF保護メカニズムは提供されていませんが、ミドルウェアを使用して独自の保護を簡単に実装できます。

### 設定

まず、CSRFトークンを生成してユーザーセッションに格納する必要があります。その後、このトークンをフォームで使用し、フォームの送信時に確認できます。

```php
// CSRFトークンを生成してユーザーセッションに格納
// （Flightにセッションオブジェクトを作成・アタッチしたと仮定）
// セッションごとに1回のみトークンを生成する必要があります（複数のタブとリクエストに対応するため）
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- フォーム内でCSRFトークンを使用 -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- その他のフォームフィールド -->
</form>
```

#### Latteの使用

Latteテンプレート内でCSRFトークンを出力するカスタム関数を設定することもできます。

```php
// CSRFトークンを出力するカスタム関数を設定
// 注：ViewはビューエンジンとしてLatteが設定されています
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

そして、Latteテンプレートで `csrf()` 関数を使用してCSRFトークンを出力できます。

```html
<form method="post">
	{csrf()}
	<!-- その他のフォームフィールド -->
</form>
```

短くてシンプルですね？

### CSRFトークンのチェック

イベントフィルタを使用してCSRFトークンを検証できます:

```php
// このミドルウェアはリクエストがPOSTリクエストかどうかを確認し、
// POSTリクエストの場合、CSRFトークンが有効かどうかを確認します
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// フォームからcsrfトークンを取得
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
		}
	}
});
```

または、ミドルウェアクラスを使用することもできます:

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


## XSS（クロスサイトスクリプティング）

XSSは、悪意のあるウェブサイトがコードをあなたのウェブサイトに注入する攻撃の一種です。これらの機会のほとんどは、エンドユーザーが入力するフォームの値から来ます。ユーザーの出力を絶対に信頼しないでください! 常にすべてのユーザーが世界で最高のハッカーであると仮定してください。悪意のあるJavaScriptやHTMLをページに注入するために使用される可能性があります。Flightのviewクラスを使用すると、XSS攻撃を防ぐために出力を簡単にエスケープできます。

```php
// ユーザーが賢いとして、これを名前として使用しようとしていると仮定します
$name = '<script>alert("XSS")</script>';

// これは出力をエスケープします
Flight::view()->set('name', $name);
// これが出力されます: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Viewクラスとして登録されているLatteを使用する場合は、これも自動的にエスケープされます。
Flight::view()->render('template', ['name' => $name]);
```

## SQLインジェクション

SQLインジェクションは、悪意のあるユーザーがSQLコードをデータベースに注入できる攻撃の一種です。これは、データベースから情報を盗んだり、データベースでアクションを実行したりするために使用される可能性があります。再度、ユーザーからの入力を絶対に信頼しないでください! 常に彼らが徹底的に検証しているものと仮定してください。SQLインジェクションを防止するために `PDO` オブジェクトでプリペアドステートメントを使用できます。

```php
// Flight::db() をPDOオブジェクトとして登録していると仮定
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// PdoWrapperクラスを使用して、1行のみで簡単にできます
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// 同様のことをPDOオブジェクトで?プレースホルダを使用して行うこともできます
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// 決してこれをやらないと約束してください...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// なぜなら、$username = "' OR 1=1; -- ";の場合はどうなるでしょうか?
// クエリが構築された後、次のようになります
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// 奇妙に見えるかもしれませんが、これは動作する有効なクエリです。実際、
// これは、すべてのユーザーを返す非常に一般的なSQLインジェクション攻撃です。
```

## CORS

Cross-Origin Resource Sharing (CORS) は、Webページの多くのリソース（フォント、JavaScriptなど）が、リソースが元々起動したドメインの外部ドメインから要求されることを許可するメカニズムです。Flightには組み込み機能は存在しませんが、このようなCORS攻撃から保護するためのミドルウェアやイベントフィルタを使って簡単に処理できます。

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

// index.php またはルートがある場所
Flight::route('/users', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(new CorsMiddleware());
```

## 結論

セキュリティは重要であり、Webアプリケーションが安全であることを確認することが重要です。Flightは、Webアプリケーションをセキュアにするためのさまざまな機能を提供していますが、常に警戒して、