# セキュリティ

セキュリティはWebアプリケーションに関連する重要な要素です。アプリケーションが安全であり、ユーザーのデータが保護されていることを確認したいですね。Flight はウェブアプリケーションをセキュアにするための機能をいくつか提供しています。

## ヘッダー

HTTPヘッダーはウェブアプリケーションをセキュアにする最も簡単な方法の一つです。ヘッダーを使用してクリックジャッキング、XSS、その他の攻撃を防ぐことができます。これらのヘッダーをアプリケーションに追加する方法にはいくつかの方法があります。

セキュリティヘッダーを確認するための優れたウェブサイトのいくつかは、[securityheaders.com](https://securityheaders.com/) と [observatory.mozilla.org](https://observatory.mozilla.org/) です。

### 手動で追加

`Flight\Response` オブジェクトの `header` メソッドを使用してこれらのヘッダーを手動で追加できます。
```php
// クリックジャッキングを防ぐために X-Frame-Options ヘッダーを設定する
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// XSS を防ぐために Content-Security-Policy ヘッダーを設定する
// 注: このヘッダーは非常に複雑になる可能性があるため、
//  アプリケーション用のインターネット上の例を確認することをお勧めします
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// XSS を防ぐために X-XSS-Protection ヘッダーを設定する
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// MIME スニッフィングを防ぐために X-Content-Type-Options ヘッダーを設定する
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// リファラーポリシー ヘッダーを設定して送信するリファラー情報の量を制御する
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// HTTPS を強制するために Strict-Transport-Security ヘッダーを設定する
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// 使用可能な機能やAPIを制御するために Permissions-Policy ヘッダーを設定する
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

これらは `bootstrap.php` または `index.php` ファイルの先頭に追加することができます。

### フィルターとして追加

以下のように、フィルター/フックにこれらを追加することもできます。

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

ミドルウェアクラスとしても追加できます。これはコードを清潔で整理された状態に保つのに適した方法です。

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
// この空のグループは、すべてのルートに対するグローバルミドルウェアとして機能します。もちろん、同じことをして、特定のルートにのみこれを追加することもできます。
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 他のルート
}, [ new SecurityHeadersMiddleware() ]);
```


## クロスサイトリクエストフォージェリ（CSRF）

クロスサイトリクエストフォージェリ（CSRF）は、悪意のあるウェブサイトがユーザーのブラウザを使用してあなたのウェブサイトにリクエストを送信する攻撃の一種です。これにより、ユーザーの知識を得ることなくウェブサイトでのアクションを実行することができます。Flight はビルトインのCSRF保護メカニズムを提供していませんが、ミドルウェアを使用して簡単に独自の実装ができます。

### 設定

まず、CSRFトークンを生成し、ユーザーのセッションに保存する必要があります。その後、このトークンをフォームで使用し、フォームが送信された際に確認できます。

```php
// CSRFトークンを生成し、ユーザーのセッションに保存します
// （Flight にセッションオブジェクトを作成してアタッチしていると仮定しています）
// 詳細はセッションのドキュメントを参照してください
Flight::register('session', \Ghostff\Session\Session::class);

// セッションごとに1つのトークンを生成するだけで十分です（同じユーザーの複数のタブとリクエストで機能するようになります）
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- フォーム内でCSRFトークンを使用 -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- 他のフォームフィールド -->
</form>
```

#### Latte を使用する

カスタム関数を設定して、Latteテンプレート内でCSRFトークンを出力することもできます。

```php
// CSRFトークンを出力するカスタム関数を設定
// 注: ビューはビューエンジンとしてLatteが設定されていると想定しています
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

そして、Latteテンプレートでは `csrf()` 関数を使用してCSRFトークンを出力できます。

```html
<form method="post">
	{csrf()}
	<!-- 他のフォームフィールド -->
</form>
```

簡単ですね？

### CSRFトークンの確認

イベントフィルターを使用してCSRFトークンを確認できます:

```php
// このミドルウェアはリクエストがPOSTリクエストかどうかを確認し、POSTリクエストの場合はCSRFトークンが有効かどうかを確認します
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// フォーム値からcsrfトークンを取得
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
			// または JSON応答用
			Flight::jsonHalt(['error' => 'Invalid CSRF token'], 403);
		}
	}
});
```

または、ミドルウェアクラスを使用できます:

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
	// 他のルート
}, [ new CsrfMiddleware() ]);
```


## クロスサイトスクリプティング（XSS）

クロスサイトスクリプティング（XSS）は、悪意のあるウェブサイトがあなたのウェブサイトにコードをインジェクションする攻撃の一種です。これらの機会のほとんどは、エンドユーザーが記入するフォーム値から来ます。ユーザーからの出力を**決して**信用しないでください！常にすべてのユーザーが世界で最高のハッカーであると仮定してください。彼らは悪意のあるJavaScriptやHTMLをページにインジェクションすることができます。このコードは、ユーザーから情報を盗むか、あなたのウェブサイトでアクションを実行するために使用される可能性があります。Flightのビュークラスを使用すると、XSS攻撃を防ぐために出力を簡単にエスケープできます。

```php
// ユーザーが賢いことを考えて、これを名前として使用しようとします
$name = '<script>alert("XSS")</script>';

// この部分は出力をエスケープします
Flight::view()->set('name', $name);
// これが出力されます：&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// ビュークラスとして設定されているLatteなどを使用している場合、これも自動的にエスケープされます。
Flight::view()->render('template', ['name' => $name]);
```

## SQLインジェクション

SQLインジェクションは、悪意のあるユーザーがSQLコードをデータベースにインジェクションできる攻撃の一種です。これを使用してデータベースから情報を盗むか、データベースでアクションを実行できます。再度、ユーザーからの入力を**決して**信頼しないでください！常に彼らが自衛に出ていると仮定してください。`PDO` オブジェクト内でプリペアステートメントを使用することで、SQLインジェクションを防げます。

```php
// Flight::db() をPDOオブジェクトとして登録していると仮定します
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// PdoWrapperクラスを使用して、これは簡単に1行で行うことができます
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// ？プレースホルダを使用してPDOオブジェクトで同じことができます
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// 決してこのようなことをしないでください...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// なぜならば $username = "' OR 1=1; -- "; の場合、どうなるでしょう...
// クエリが組み立てられると次のようになります
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// 奇妙に見えるかもしれませんが、これは動作する有効なクエリです。実際、
// これは非常に一般的なSQLインジェクション攻撃で、すべてのユーザーを返します。

```

## CORS

クロスオリジンリソース共有（CORS）は、ウェブページ上の多くのリソース（フォント、JavaScriptなど）を、リソースが起源となるドメイン以外のドメインからリクエストできるようにする仕組みです。Flight には組み込みの機能がありませんが、`Flight::start()` メソッドが呼び出される前にフックを実行するためのフックを簡単に設定できます。

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
		// ここで許可されるホストをカスタマイズしてください。
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

## 結論

セキュリティは重要な要素であり、Webアプリケーションが安全であることを確認することが重要です。Flight はウェブアプリケーションをセキュアにするためのいくつかの機能を提供していますが、常に用心深く、ユーザーのデータを安全に保つためにできる限りのことを行っていることを確認することが重要です。常に最悪を想定し、ユーザーからの入力を信頼せずに、出力をエスケープし、SQLインジェクションを防ぐためにプリペアドステートメントを使用してください。CSRFとCORS攻撃からルートを保護するためにミドルウェアを使用してください。これらのすべてを行うことで、安全なウェブアプリケーションの構築に向けて大きく前進することができます。