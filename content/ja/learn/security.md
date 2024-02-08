# セキュリティ

Webアプリケーションに関するセキュリティは非常に重要です。アプリケーションが安全であり、ユーザーのデータが安全であることを確認したいです。Flightでは、Webアプリケーションのセキュリティを確保するための機能がいくつか提供されています。

## CSRF（クロスサイトリクエストフォージェリ）

CSRF（クロスサイトリクエストフォージェリ）は、悪意のあるウェブサイトがユーザーのブラウザーにリクエストを送信させる攻撃の一種です。これにより、ユーザーの知識を得ることなく、あなたのウェブサイトでアクションを実行することができます。Flightには組み込みのCSRF保護機構は提供されていませんが、ミドルウェアを使用して独自の保護を簡単に実装できます。

以下は、イベントフィルターを使用してCSRF保護を実装する方法の例です：

```php

// このミドルウェアはリクエストがPOSTリクエストかどうかを確認し、そうである場合はCSRFトークンが有効かどうかを確認します
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// フォーム値からcsrfトークンを取得
		$token = Flight::request()->data->csrf_token;
		if($token != $_SESSION['csrf_token']) {
			Flight::halt(403, 'Invalid CSRF token');
		}
	}
});
```

## XSS（クロスサイトスクリプティング）

XSS（クロスサイトスクリプティング）は、悪意のあるウェブサイトがコードをあなたのウェブサイトに挿入する攻撃です。これらの機会の多くは、エンドユーザーが記入するフォームの値から来ます。ユーザーの出力を**絶対に**信頼してはいけません！常に彼ら全員が世界で最高のハッカーだと仮定してください。悪意のあるJavaScriptやHTMLをページに挿入することができます。このコードはユーザーから情報を盗んだり、ウェブサイトでアクションを実行するために使用される可能性があります。Flightのビュークラスを使用すると、XSS攻撃を防ぐために簡単に出力をエスケープすることができます。

```php

// ユーザーがこれを名前として使用しようとする場合を想定してみましょう
$name = '<script>alert("XSS")</script>';

// これによって出力がエスケープされます
Flight::view()->set('name', $name);
// これが出力されます: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// ビュークラスとして登録されたLatteのようなものを使用する場合、これも自動エスケープされます。
Flight::view()->render('template', ['name' => $name]);
```

## SQLインジェクション

SQLインジェクションは、悪意のあるユーザーがSQLコードをデータベースに挿入できる攻撃の一種です。これにより、データベースから情報を盗むか、データベース上でアクションを実行することができます。再び、ユーザー入力を**絶対に**信頼しないでください！常に彼らが血を求めていると仮定してください。`PDO`オブジェクトでプリペアドステートメントを使用すると、SQLインジェクションを防ぐことができます。

```php

// Flight::db()をPDOオブジェクトとして登録していると仮定します
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// PdoWrapperクラスを使用すると、これを1行で簡単に行うことができます
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// 同様のことがPDOオブジェクトと?プレースホルダーを使用して行うことができます
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// 決してこのようなことを行わないと約束してください...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}'");
// なぜなら、$username = "' OR 1=1;"; だった場合、クエリが構築されると次のようになります
// SELECT * FROM users WHERE username = '' OR 1=1;
// 奇妙に見えますが、これは機能する有効なクエリです。実際、
// これはすべてのユーザーを返す非常に一般的なSQLインジェクション攻撃です。
```

## CORS（クロスオリジンリソース共有）

クロスオリジンリソース共有（CORS）は、ウェブページ上の多くのリソース（たとえば、フォント、JavaScriptなど）がリソースが発信元のドメインの外部から要求されるメカニズムです。Flightには組み込みの機能はありませんが、これはCSRFと同様に、ミドルウェアまたはイベントフィルターを使用して簡単に処理できます。

```php

Flight::route('/users', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(function() {
	if (isset($_SERVER['HTTP_ORIGIN'])) {
		header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Max-Age: 86400');
	}

	if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
			header(
				'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS'
			);
		}
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
			header(
				"Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
			);
		}
		exit(0);
	}
});
```

## 結論

セキュリティは非常に重要であり、ウェブアプリケーションが安全であることを確認することが重要です。FlightにはWebアプリケーションのセキュリティを支援する機能がいくつか提供されていますが、常に用心し、ユーザーのデータを安全に保つためにできる限りの努力をしていることを確認することが重要です。常に最悪のことを想定し、ユーザーからの入力を信頼しないでください。出力をエスケープし、SQLインジェクションを防ぐためにプリペアドステートメントを使用してください。CSRFおよびCORS攻撃からルートを保護するために常にミドルウェアを使用してください。これらのすべてを実行すると、安全なウェブアプリケーションの構築に大きく近づくことができます。