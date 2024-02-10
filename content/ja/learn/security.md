# セキュリティ

セキュリティはウェブアプリケーションにとって重要です。あなたは自分のアプリケーションが安全であり、ユーザーのデータが安全であることを確認したいです。Flightは、ウェブアプリケーションをセキュアにするためのさまざまな機能を提供しています。

## クロスサイトリクエストフォージェリ（CSRF）

クロスサイトリクエストフォージェリ（CSRF）は、悪意のあるウェブサイトがユーザーのブラウザに対してあなたのウェブサイトにリクエストを送信させる攻撃の一種です。Flightには組み込みのCSRF保護メカニズムが提供されていませんが、ミドルウェアを使用することで独自の実装が簡単にできます。

最初にCSRFトークンを生成し、ユーザーのセッションに保存する必要があります。その後、このトークンをフォームで使用して、フォームが送信されたときに確認できます。

```php
// CSRFトークンを生成し、ユーザーのセッションに保存します
// （Flightにセッションオブジェクトが作成されてアタッチされていることを想定）
Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
```

```html
<!-- フォームでCSRFトークンを使用 -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- 他のフォームフィールド -->
</form>
```

そして、CSRFトークンをチェックするためのイベントフィルターを使用できます：

```php
// このミドルウェアはリクエストがPOSTリクエストかどうかを確認し、POSTリクエストの場合はCSRFトークンが有効かどうかを確認します
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// フォーム値からcsrfトークンを取得
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
		}
	}
});
```

## クロスサイトスクリプティング（XSS）

クロスサイトスクリプティング（XSS）は、悪意のあるウェブサイトがコードをあなたのウェブサイトに注入する攻撃の一種です。これらの機会のほとんどはユーザーが記入するフォーム値から来ます。ユーザーからの出力を**決して**信頼してはいけません！常に彼ら全員が世界で最も優れたハッカーであると仮定してください。彼らはあなたのページに不正なJavaScriptやHTMLを挿入することができます。このコードはユーザーから情報を盗んだり、あなたのウェブサイトでアクションを実行するために使用できます。Flightのビュークラスを使用すると、XSS攻撃を防ぐために簡単に出力をエスケープできます。

```php

// ユーザーが賢いとしてこれを名前として使用しようとすると仮定します
$name = '<script>alert("XSS")</script>';

// このように出力をエスケープします
Flight::view()->set('name', $name);
// これが出力されます: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// もし、あなたのビュークラスとして登録されたLatteなどを使用しているなら、これも自動的にエスケープされます。
Flight::view()->render('template', ['name' => $name]);
```

## SQLインジェクション

SQLインジェクションは悪意のあるユーザーがSQLコードをあなたのデータベースにインジェクトする攻撃の一種です。これはデータベースから情報を盗むか、データベースでアクションを実行するために使用できます。再度、ユーザーからの入力を**決して**信頼してはいけません！常に彼らが血を求めていると仮定してください。`PDO`オブジェクトでプリペアドステートメントを使用するとSQLインジェクションを防ぐことができます。

```php

// Flight::db()をPDOオブジェクトとして登録していると仮定します
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// PdoWrapperクラスを使用すると、これは簡単に1行で行うことができます
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// 同じことをPDOオブジェクトで?プレースホルダーを使用して行うことができます
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// 決してこんなことをしないでください...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// なぜなら、もし $username = "' OR 1=1; -- "; だったら、クエリが構築されると次のようになります
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// おかしな見え方をしていますが、これは正常に動作する有効なクエリです。実際には、
// これはすべてのユーザーを返す非常に一般的なSQLインジェクション攻撃です。
```

## CORS

クロスオリジンリソース共有（CORS）は、ウェブページ上で多くのリソース（フォント、JavaScriptなど）が、そのリソースが起源となるドメインの外部ドメインからリクエストされる仕組みです。Flightには組み込みの機能がありませんが、これはCSRFと同様にミドルウェアやイベントフィルターを使用して簡単に処理できます。

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

// index.phpまたはルートを持っている場所
Flight::route('/users', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(new CorsMiddleware());
```

## 結論

セキュリティは重要であり、ウェブアプリケーションをセキュアにすることが重要です。Flightは、ウェブアプリケーションをセキュアにするのを支援するさまざまな機能を提供していますが、常に警戒し、ユーザーのデータを安全に保つためにできる限りのことを行っていることを確認することが重要です。常に最悪の事態を想定し、ユーザーからの入力を信頼しないでください。出力をエスケープし、SQLインジェクションを防ぐためにプリペアドステートメントを使用してください。CSRFやCORS攻撃からルートを保護するためにミドルウェアを常に使用してください。これらすべてを行うことで、セキュアなウェブアプリケーションを構築する道を歩んでいることになります。