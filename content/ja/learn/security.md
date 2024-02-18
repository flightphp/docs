# セキュリティ

Webアプリケーションに関連するセキュリティは重要です。アプリケーションが安全であり、ユーザーのデータが安全であることを確認したいです。Flight には、Webアプリケーションのセキュリティを強化するための機能がいくつか用意されています。

## ヘッダー

HTTPヘッダーはWebアプリケーションを保護するための簡単な方法の1つです。これらのヘッダーを使用して、クリックジャッキング、XSS、およびその他の攻撃を防ぐことができます。これらのヘッダーをアプリケーションに追加する方法はいくつかあります。

### 手動で追加

`Flight\Response` オブジェクトの `header` メソッドを使用してこれらのヘッダーを手動で追加できます。
```php
// クリックジャッキングを防ぐために X-Frame-Options ヘッダーを設定
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// XSSを防ぐために Content-Security-Policy ヘッダーを設定
// 注: このヘッダーは非常に複雑になる可能性があるため、
// アプリケーション向けのインターネット上の例を参照する必要があります
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// XSSを防ぐために X-XSS-Protection ヘッダーを設定
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// MIMEスニッフィングを防ぐために X-Content-Type-Options ヘッダーを設定
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// リファラーポリシーを設定して送信されるリファラー情報の制御
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// HTTPSを強制するために Strict-Transport-Security ヘッダーを設定
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
```

これらは `bootstrap.php` または `index.php` ファイルの先頭に追加できます。

### フィルターとして追加

次のようにフィルター/フックに追加することもできます:

```php
// フィルターでヘッダーを追加
Flight::before('start', function() {
	Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
	Flight::response()->header("Content-Security-Policy", "default-src 'self'");
	Flight::response()->header('X-XSS-Protection', '1; mode=block');
	Flight::response()->header('X-Content-Type-Options', 'nosniff');
	Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
	Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
});
```

### ミドルウェアとして追加

ミドルウェアクラスとして追加することもできます。これはコードをきれいに整理する良い方法です。

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
	}
}

// ルートがある index.php またはその他の場所
// ご存知のように、この空の文字列グループは
// すべてのルートに対するグローバルミドルウェアとして機能します。もちろん、同じことを実行し、
// これを特定のルートにのみ追加することもできます。
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// さらに他のルート
}, [ new SecurityHeadersMiddleware() ]);
```


## クロスサイトリクエストフォージェリ（CSRF）

クロスサイトリクエストフォージェリ（CSRF）は、悪意のあるウェブサイトがユーザーのブラウザを使用してあなたのウェブサイトにリクエストを送信する攻撃の一種です。これにより、ユーザーの知識を得ることなく、あなたのウェブサイト上でアクションを実行できます。Flight は組込みの CSRF 保護メカニズムを提供していませんが、ミドルウェアを使用して独自の保護を簡単に実装できます。

### 設定

まず、CSRF トークンを生成し、ユーザーのセッションに保存する必要があります。その後、このトークンをフォームで使用し、フォームが送信される際にチェックできます。

```php
// CSRF トークンを生成し、ユーザーのセッションに保存
// (Flight にセッションオブジェクトを作成し、アタッチしていると仮定)
// セッションごとに1つのトークンを生成するだけで十分です（複数のタブやリクエストに対して機能します）
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

#### Latte の使用

Latte テンプレートで CSRF トークンを出力するカスタム関数を設定することもできます。

```php
// CSRF トークンを出力するカスタム関数を設定
// 注: View はビューエンジンとして Latte が設定されています
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

簡単で簡潔ですね？

### CSRF トークンのチェック

イベントフィルターを使用して CSRF トークンをチェックできます:

```php
// このミドルウェアはリクエストが POST リクエストかどうかを確認し、もしそうであれば、CSRF トークンが有効かどうかをチェックします
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// フォームの値から csrf トークンを取得
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
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

// ルートがある index.php またはその他の場所
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// さらに他のルート
}, [ new CsrfMiddleware() ]);
```


## クロスサイトスクリプティング（XSS）

クロスサイトスクリプティング（XSS）は、悪意のあるウェブサイトがコードをあなたのウェブサイトに挿入する攻撃の種類です。これらの機会のほとんどは、エンドユーザーが入力するフォームの値から来ます。ユーザーの出力を **決して** 信頼してはいけません！常に、彼らが世界で最高のハッカーだと仮定してください。彼らは、あなたのページに悪意のある JavaScript または HTML を挿入できます。このコードは、ユーザーから情報を盗んだり、あなたのウェブサイト上でアクションを実行したりするために使用される可能性があります。Flight の view クラスを使用すると、XSS 攻撃を防ぐために出力を簡単にエスケープできます。

```php
// ユーザーが賢明で、これを名前として使用しようとすると仮定しましょう
$name = '<script>alert("XSS")</script>';

// この出力はエスケープされます
Flight::view()->set('name', $name);
// これは出力されます: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// View クラスとして Latte を使用している場合、これも自動的にエスケープされます。
Flight::view()->render('template', ['name' => $name]);
```

## SQLインジェクション

SQLインジェクションは、悪意のあるユーザーがSQLコードをデータベースに挿入できる攻撃の一種です。これは、データベースから情報を盗んだり、データベースでアクションを実行したりするために使用できます。再び、ユーザーからの入力を **決して** 信頼してはいけません！常に、彼らがあなたを脅かしていると仮定してください。`PDO` オブジェクト内でプリペアドステートメントを使用すると SQL インジェクションを防げます。

```php
// Flight::db() を PDO オブジェクトとして登録していると仮定
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// PdoWrapper クラスを使用して、1行で簡単に行うことができます
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// 同じことを、? プレースホルダを使用して PDO オブジェクトで行うこともできます
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// 決してこんなことをしないことを約束してください...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// なぜなら、$username = "' OR 1=1; -- "; だった場合、どうなるでしょうか？
// クエリが次のようになります
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// 奇妙に見えますが、このクエリは有効で、実際に機能します。実際、
// これは、すべてのユーザーを返す非常に一般的なSQLインジェクション攻撃です。

## CORS

クロスオリジンリソース共有（CORS）は、ウェブページの多くのリソース（フォント、JavaScript など）が、そのリソースの元のドメイン外の別のドメインから要求される仕組みです。Flight には組み込みの機能がないですが、これは CSRF に類似したミドルウェアやイベントフィルターを使用して簡単に処理できます。

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
		// ここで許可するホストをカスタマイズしてください。
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

// ルートがある index.php またはその他の場所
Flight::route('/users', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(new CorsMiddleware());
```

## 結論

セキュリティは重要です。Webアプリケーションを安全にすることが重要です。Flight はWebアプリケーションをセキュアにするための機能を多数提供していますが、常に警戒心を持ち、ユーザーのデータを安全に保つためにできる限りのことを行うことが重要です。常に最悪のセナリオが完了しました。