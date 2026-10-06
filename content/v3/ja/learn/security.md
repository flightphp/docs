# セキュリティ

## 概要

Webアプリケーションにおいて、セキュリティは非常に重要です。アプリケーションを安全に保ち、ユーザーのデータを守るようにしましょう。Flight は、Webアプリケーションを保護するための多くの機能を提供します。

公式の [skeleton](https://github.com/flightphp/skeleton) には専用の **`SECURITY.md`** とセキュリティヘッダーミドルウェアが同梱されており、[AIコーディングツール](/learn/ai)（および人間）が、シークレット、ヘッダー、XSS/SQLルールについて、`AGENTS.md` の一般的なコーディングスタイルとは別に、意図的に一箇所にまとめられるようになっています。

## 理解

Webアプリケーションを構築する際に注意すべき一般的なセキュリティ脅威は数多くあります。最も一般的な脅威には次のようなものがあります。
- クロスサイトリクエストフォージェリ (CSRF)
- クロスサイトスクリプティング (XSS)
- SQLインジェクション
- クロスオリジンリソース共有 (CORS)

[Templates](/learn/templates) は、デフォルトで出力をエスケープすることでXSSを防ぐのに役立ちます（TwigとLatteはこれを実行します。その利点を活用してください）。[Sessions](/awesome-plugins/session) は、後述のようにユーザーのセッションにCSRFトークンを保存することでCSRFに役立ちます。PDOでプリペアドステートメントを使用するか、[SimplePdo](/learn/simple-pdo) のヘルパーを使用すると、SQLインジェクションを防ぐことができます。CORSは、`Flight::start()` が呼び出される前に簡単なフックで処理できます。

これらの方法はすべて連携して、Webアプリケーションを安全に保つのに役立ちます。セキュリティのベストプラクティスを学び理解することを常に最優先にしてください。トレードオフを理解せずに、ページを読み込むためだけにAIアシスタントに「CSPを無効にする」やヘッダーを弱めるよう依頼してはいけません。

## 基本的な使い方

### ヘッダー

HTTPヘッダーは、Webアプリケーションを保護する最も簡単な方法の1つです。ヘッダーを使用して、クリックジャッキング、XSS、その他の攻撃を防ぐことができます。これらのヘッダーをアプリケーションに追加する方法はいくつかあります。

ヘッダーのセキュリティを確認するための優れたWebサイトは、[securityheaders.com](https://securityheaders.com/) と [observatory.mozilla.org](https://observatory.mozilla.org/) です。以下のコードを設定すると、これらの2つのWebサイトでヘッダーが機能していることを簡単に確認できます。

スケルトンには `App\Middleware\SecurityHeadersMiddleware`（リクエストごとのnonce付きCSP、フレームオプション、HSTSなど）が含まれています。ヘッダーをオフにするのではなく、意図的にこれを拡張することをお勧めします。

#### 手動で追加する

`Flight\Response` オブジェクトの `header` メソッドを使用して、これらのヘッダーを手動で追加できます。
```php
// クリックジャッキングを防ぐために X-Frame-Options ヘッダーを設定
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// XSS を防ぐために Content-Security-Policy ヘッダーを設定
// 注: このヘッダーは非常に複雑になる可能性があるため、
//  アプリケーションに応じた例をインターネットで参照してください。
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// XSS を防ぐために X-XSS-Protection ヘッダーを設定
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// MIMEスニッフィングを防ぐために X-Content-Type-Options ヘッダーを設定
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// リファラー情報の送信量を制御するために Referrer-Policy ヘッダーを設定
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// HTTPS を強制するために Strict-Transport-Security ヘッダーを設定
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// 使用できる機能とAPIを制御するために Permissions-Policy ヘッダーを設定
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

これらは `routes.php` または `index.php` ファイルの先頭に追加できます。

#### フィルタとして追加する

次のようなフィルタ/フックで追加することもできます:

```php
// フィルタ内でヘッダーを追加
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

#### ミドルウェアとして追加する

ミドルウェアクラスとして追加することもできます。これにより、適用するルートに対して最大の柔軟性が得られます。一般に、これらのヘッダーはすべてのHTMLおよびAPIレスポンスに適用する必要があります。

スケルトンスタイルのパスと名前空間（**フォルダーの大文字小文字は `App\Middleware` と一致します**）:

```php
// app/Middleware/SecurityHeadersMiddleware.php

namespace App\Middleware;

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
		// インラインスクリプトがある場合はブートストラップのCSP nonceを優先する（スケルトンは csp_nonce を設定）
		$nonce = $this->app->get('csp_nonce');
		$csp = $nonce
			? "default-src 'self'; script-src 'self' 'nonce-{$nonce}'; style-src 'self' 'nonce-{$nonce}'"
			: "default-src 'self'";

		$response->header('X-Frame-Options', 'SAMEORIGIN');
		$response->header('Content-Security-Policy', $csp);
		$response->header('X-XSS-Protection', '1; mode=block');
		$response->header('X-Content-Type-Options', 'nosniff');
		$response->header('Referrer-Policy', 'no-referrer-when-downgrade');
		$response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		$response->header('Permissions-Policy', 'geolocation=()');
	}
}

// app/config/routes.php — 空文字列グループ = すべてのルートに対するグローバルミドルウェア
use App\Middleware\SecurityHeadersMiddleware;
use flight\net\Router;

$router->group('', function (Router $router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// その他のルート
}, [SecurityHeadersMiddleware::class]);
```

古いプロジェクトでは引き続き `app/middlewares` と `app\middlewares` を使用している場合があります。フォルダーが一致していれば機能します。新しいスケルトンアプリでは **`app/Middleware/`** と **`App\Middleware`** を使用します。[Autoloading](/learn/autoloading) を参照してください。

### クロスサイトリクエストフォージェリ (CSRF)

クロスサイトリクエストフォージェリ (CSRF) は、悪意のあるWebサイトがユーザーのブラウザにあなたのWebサイトへのリクエストを送信させる攻撃の一種です。これは、ユーザーの知らないうちにあなたのWebサイトで操作を実行するために使用される可能性があります。Flight にはCSRF保護の組み込みメカニズムはありませんが、ミドルウェアを使用して簡単に独自のメカニズムを実装できます。

#### セットアップ

まず、CSRFトークンを生成してユーザーのセッションに保存する必要があります。その後、このトークンをフォームで使用し、フォームが送信されたときにチェックできます。セッションの管理には [flightphp/session](/awesome-plugins/session) プラグインを使用します。

```php
// CSRF トークンを生成し、ユーザーのセッションに保存する
// （セッションオブジェクトを作成し、Flight にアタッチしていると仮定）
// 詳細についてはセッションドキュメントを参照
Flight::register('session', flight\Session::class);

// セッションごとに1つのトークンを生成するだけで十分です（同じユーザーの
// 複数のタブやリクエスト間で機能します）
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### デフォルトのPHP Flight テンプレートを使用する

```html
<!-- フォームで CSRF トークンを使用 -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- その他のフォームフィールド -->
</form>
```

##### Twigを使用する（スケルトンのデフォルト）

Twig関数を登録するか、トークンをすべてのフォームビューに渡します。グローバル + フォームフィールドを使用した最小限の例:

```php
// Twig の設定時（例: services.php）
$twig->addGlobal('csrf_token', $app->session()->get('csrf_token'));
```

```html
{# app/views/form.twig #}
<form method="post">
	<input type="hidden" name="csrf_token" value="{{ csrf_token }}">
	{# その他のフィールド #}
</form>
```

##### Latteを使用する

LatteテンプレートでCSRFトークンを出力するカスタム関数を設定することもできます。

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// その他の設定...

	// CSRF トークンを出力するカスタム関数を設定
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

これで、Latteテンプレート内で `csrf()` 関数を使用してCSRFトークンを出力できます。

```html
<form method="post">
	{csrf()}
	<!-- その他のフォームフィールド -->
</form>
```

#### CSRFトークンをチェックする

CSRFトークンはいくつかの方法でチェックできます。

##### ミドルウェア

```php
// app/Middleware/CsrfMiddleware.php

namespace App\Middleware;

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

// routes.php
use App\Middleware\CsrfMiddleware;

$router->group('', function ($router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// その他のルート
}, [CsrfMiddleware::class]);
```

##### イベントフィルタ

```php
// このミドルウェアは、リクエストがPOSTリクエストかどうかをチェックし、POSTの場合は CSRF トークンが有効かどうかをチェックします
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// フォーム値から csrf トークンを取得
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

クロスサイトスクリプティング (XSS) は、悪意のあるフォーム入力をあなたのWebサイトにコードを注入できる攻撃の一種です。これらの機会のほとんどは、エンドユーザーが入力するフォーム値から発生します。ユーザーからの出力を**決して信頼してはいけません**。常に、すべてのユーザーが最高のハッカーであると想定してください。彼らは悪意のあるJavaScriptやHTMLをあなたのページに注入する可能性があります。このコードは、ユーザーから情報を盗んだり、あなたのWebサイトで操作を実行したりするために使用される可能性があります。Flightのビュークラスや [Twig](/awesome-plugins/twig) や [Latte](/awesome-plugins/latte) のようなテンプレートエンジンを使用すると、出力を簡単にエスケープしてXSS攻撃を防ぐことができます。

```php
// ユーザーが賢く、これを自分の名前として使おうとしていると仮定します
$name = '<script>alert("XSS")</script>';

// これにより出力がエスケープされます
Flight::view()->set('name', $name);
// これは次のように出力されます: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Twig（スケルトンのデフォルト）と Latte はデフォルトで自動エスケープされます。素のPHP echo よりもこれらを優先してください。
Flight::render('template', ['name' => $name]);
// Twig: {{ name }}  → エスケープ済み
// コンテンツが完全に信頼できない限り、|raw / エスケープなし出力は避けてください
```

### SQLインジェクション

SQLインジェクションは、悪意のあるユーザーがデータベースにSQLコードを注入できる攻撃の一種です。これは、データベースから情報を盗んだり、データベース上で操作を実行したりするために使用される可能性があります。繰り返しますが、ユーザーからの入力を**決して信頼してはいけません**！常に彼らが血に飢えていると想定してください。プリペアドステートメントを使用してください。[SimplePdo](/learn/simple-pdo) のヘルパーはこれをデフォルトのパスにします。

```php
// Flight::db() が SimplePdo として登録されていると仮定します（またはコントローラーで SimplePdo を注入します）
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// SimplePdo（推奨）— バインドパラメータを使用したワンライナー
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// 同じ考え方で ? プレースホルダーを使用
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

スケルトンスタイルのコントローラーでは、テストとAI生成コードの一貫性を保つために、`Flight::db()` よりもコンストラクターインジェクションで `SimplePdo` を注入することをお勧めします（[DIC](/learn/dependency-injection-container)）。

#### 安全でない例

以下は、なぜSQLプリペアドステートメントを使用して、以下のような無邪気な例から保護するのかという理由です:

```php
// エンドユーザーがWebフォームに記入します。
// フォームの値として、ハッカーは次のようなものを入力します:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// クエリ構築後、次のようになります
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// 奇妙に見えますが、これは機能する有効なクエリです。実際、
// これはすべてのユーザーを返す非常に一般的なSQLインジェクション攻撃です。

var_dump($users); // これにより、特定の1つのユーザー名だけでなく、データベース内のすべてのユーザーがダンプされます
```

### シークレットと設定

- シークレットは、コミットされた `config.php` サンプルではなく、**`.env`**（または実際の環境）に置きます。
- スケルトンのルール: `config.php` にはリテラルのデフォルト値を置き、ブートストラップで環境変数をマージします。コントローラー内で `$_ENV` を読み取らないでください。代わりに設定を注入します。[Configuration](/learn/configuration) を参照してください。
- APIキー、DBパスワード、セッション暗号化キーをコミットしないでください。AIツールに **`SECURITY.md`** を指し示して、安全でないショートカットを発明しないようにしてください。

### JSONPコールバックの検証

Flightの `Flight::jsonp()` メソッドを使用する場合、Flight がJSONPコールバックパラメータ名を厳格な許可リストの正規表現（`/^[A-Za-z_$][\w$.]{0,127}$/`）に対して検証することに注意してください。このパターンに一致しないコールバック名は Flight が例外をスローし、悪意のあるコールバック値を介した任意のJavaScriptの注入を防ぎます。

この検証は組み込まれており、追加の設定は不要ですが、JSONPエンドポイントから予期しないエラーが発生した場合のデバッグ時に知っておく価値があります。

### CORS

クロスオリジンリソース共有 (CORS) は、ウェブページ上の多くのリソース（フォント、JavaScript など）を、そのリソースが発信されたドメイン以外の別のドメインからリクエストできるようにするメカニズムです。Flight には組み込みの機能はありませんが、`Flight::start()` メソッドが呼び出される前に実行されるフックで簡単に処理できます。

```php
// app/Utils/CorsUtil.php  (スケルトン: PascalCaseのUtilsフォルダ → App\Utils)

namespace App\Utils;

use flight\Engine;

class CorsUtil
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function set(array $params = []): void
	{
		$request = $this->app->request();
		$response = $this->app->response();
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
		// 許可するホストをここでカスタマイズします。
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		$request = $this->app->request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $allowed, true) === true) {
			$response = $this->app->response();
			$response->header("Access-Control-Allow-Origin", $request->getVar('HTTP_ORIGIN'));
		}
	}
}

// bootstrap / routes — 開始前に実行
$app = Flight::app();
$cors = new \App\Utils\CorsUtil($app);
$app->before('start', [ $cors, 'set' ]);
```

### Flight 設定の強化

Flight は、セキュリティに直接影響するいくつかのエンジン設定を公開しています。これらを正しく設定することは、アプリケーションを強化する最も簡単な方法の1つです。

#### `flight.allow_method_override`

デフォルトでは、Flight はクライアントが `X-HTTP-Method-Override` ヘッダーまたは POST ボディの `_method` フィールドを使用してリクエストのHTTPメソッドを上書きできるようにします。これは `GET`/`POST` しか送信できないHTMLフォームには便利ですが、想定していない場合は危険です。攻撃者は通常のフォームを介して `DELETE` や `PUT` リクエストを偽造する可能性があります。

アプリケーションがこの動作に依存していない場合（たとえば、任意のHTTP動詞を送信できるモダンクライアントやJavaScriptフロントエンドが利用するAPIを構築している場合）、これを無効にする必要があります:

```php
// index.php またはブートストラップファイル内、Flight::start() の前に
Flight::set('flight.allow_method_override', false);
```

デフォルト値は後方互換性のために `true` ですが、オーバーライド機能を明示的に必要としないアプリケーションでは **`false` に設定することを強くお勧めします**。

#### `flight.debug`

Flight には、未処理の例外が発生したときに、詳細なエラー情報（例外メッセージ、コード、完全なスタックトレース）をブラウザに表示するかどうかを制御する `flight.debug` 設定があります。デフォルトは `false` で、一般的な `500 Internal Server Error` メッセージのみが表示され、内部の詳細はクライアントに漏れません。

本番サーバーではこれを有効にしないでください。ローカルまたはステージング環境でのみ使用してください:

```php
// ローカル開発のみに安全 — 本番では決して使用しないでください
Flight::set('flight.debug', true);
```

`flight.debug` が `false`（デフォルト）の場合でも、`flight.log_errors` を有効にすることでエラーを捕捉できます:

```php
// クライアントに公開せずにサーバー側でエラーをログに記録
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
```

#### `flight.views.restrict_to_path`

Flight 組み込みの `View` クラスは、絶対パスから、または `flight.views.path` の外に上がる相対名（例: `../`）からテンプレートを喜んでインクルードします。これは、意図的にフォルダー間でテンプレートを共有するアプリには意図的なものですが、テンプレート名が信頼できない入力に由来する場合は、パストラバーサルのリスクにもなります。

`flight.views.restrict_to_path` はデフォルトで **オフ** であり、既存のアプリはそのまま動作します。文書化された理由がない限り、オンにしてください:

```php
// index.php またはブートストラップファイル内、Flight::start() の前に
Flight::set('flight.views.restrict_to_path', true);
```

エンジンはビューが作成されるときにこの設定を `View::$restrictToPath` に適用します（`flight.views.path` と `flight.views.extension` と同じパターン）。これをオンにすると:

- `render()` と `fetch()` は、実際のパスが設定されたビューディレクトリ内にあるファイルのみを含みます（外部を指すシンボリックリンクも拒否されます）。
- `exists()` は、それらの同じパスに対して例外をスローする代わりに `false` を返します。
- `getTemplate()` 自体は変更されません — これまでどおりパスを返します。
- ブロックされたファイルは `Template file is outside the views path.` をスローします。存在しないファイルは、これまでどおり `Template file not found: ...` メッセージをスローします。

Twig や Latte を独自のファイルシステムローダーでビューディレクトリに向けて使用する場合、それらのエンジンはすでにそのルートにテンプレートを制限しています。それでも、`Flight::view()->render()` / `fetch()` を呼び出すコードが同じ保護を受けられるように、Flight ネイティブの `View` でこれをオンにしてください。公式の [skeleton](https://github.com/flightphp/skeleton) はブートストラップでこれを有効にします。

#### 推奨される本番設定

```php
// index.php またはアプリ設定/ブートストラップから適用
Flight::set('flight.allow_method_override', false);
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
Flight::set('flight.views.restrict_to_path', true);
```

### エラーハンドリング

本番環境では、攻撃者に情報を漏らさないように、機密性の高いエラー詳細を非表示にします。本番環境では、`display_errors` を `0` に設定し、エラーを表示する代わりにログに記録します。

```php
// bootstrap.php または index.php 内

// これを app/config/config.php に追加
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // エラー表示を無効化
    ini_set('log_errors', 1);     // 代わりにエラーをログに記録
    ini_set('error_log', '/path/to/error.log');
}

// ルートまたはコントローラー内
// 制御されたエラーレスポンスには Flight::halt() を使用
Flight::halt(403, 'Access denied');
```

### 入力のサニタイズ

ユーザー入力を決して信頼しないでください。悪意のあるデータの侵入を防ぐために、処理前に [filter_var](https://www.php.net/manual/en/function.filter-var.php) を使用してサニタイズします。アプリコードでは、生の `$_GET` / `$_POST` ではなく、`$app->request()`（または `Flight::request()`）を介して入力を読み取ることをお勧めします。

```php

// $_POST['input'] と $_POST['email'] を含む $_POST リクエストを想定します

// 文字列入力をサニタイズ
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// メールアドレスをサニタイズ
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### パスワードのハッシュ化

PHPの組み込み関数である [password_hash](https://www.php.net/manual/en/function.password-hash.php) や [password_verify](https://www.php.net/manual/en/function.password-verify.php) を使用して、パスワードを安全に保存し、安全に検証します。パスワードを平文で保存したり、可逆的な方法で暗号化したりしてはいけません。ハッシュ化により、たとえデータベースが侵害されても、実際のパスワードは保護されたままになります。

```php
$password = Flight::request()->data->password;
// 保存時にパスワードをハッシュ化（例: 登録時）
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// パスワードを検証（例: ログイン時）
if (password_verify($password, $stored_hash)) {
    // パスワードが一致
}
```

### レート制限

キャッシュを使用してリクエストレートを制限することで、ブルートフォース攻撃やサービス拒否攻撃から保護します。

```php
// flightphp/cache がインストールされ、登録されていると仮定します
// フィルタ内で flightphp/cache を使用
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

## 関連情報

- [Sessions](/awesome-plugins/session) - ユーザーセッションを安全に管理する方法。
- [Templates](/learn/templates) - Twig/Latteの自動エスケープとXSS。
- [SimplePdo](/learn/simple-pdo) - プリペアドステートメントを使用したデータベースヘルパー。
- [PdoWrapper](/learn/pdo-wrapper) - 非推奨。新しいコードでは SimplePdo を使用してください。
- [Middleware](/learn/middleware) - セキュリティヘッダーの追加プロセスを簡素化するミドルウェアの使用方法。
- [Configuration](/learn/configuration) - `.env` とリテラル設定、本番フラグ。
- [AI & Developer Experience](/learn/ai) - セキュリティポリシーはエージェント用に `SECURITY.md` に保持します。
- [Responses](/learn/responses) - 安全なヘッダーを使用してHTTPレスポンスをカスタマイズする方法。
- [Requests](/learn/requests) - ユーザー入力を処理およびサニタイズする方法。
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - 入力サニタイズ用のPHP関数。
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - 安全なパスワードハッシュ化のためのPHP関数。
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - ハッシュ化されたパスワードを検証するためのPHP関数。

## トラブルシューティング

- Flight Frameworkのコンポーネントに関する問題のトラブルシューティング情報については、上記の「関連情報」セクションを参照してください。
- CSPがスクリプトをブロックする場合は、nonce（スケルトンパターン）を追加するか、特定のオリジンを許可リストに追加します。計画なしで `script-src *` を設定しないでください。

## 変更履歴

- Docs – スケルトンの `App\Middleware`、TwigのCSRF/XSSメモ、SimplePdo、シークレット/`.env`、AIフレンドリーなプロジェクト向けの `SECURITY.md`。
- Docs – Flight設定の強化の下に `flight.views.restrict_to_path` を文書化（ネイティブビューのオプトインパス封じ込め）。
- v3.18.1 - `flight.allow_method_override`、`flight.debug`、JSONPコールバック検証をカバーする「Flight 設定の強化」セクションを追加。
- v3.1.0 - CORS、エラーハンドリング、入力のサニタイズ、パスワードのハッシュ化、レート制限に関するセクションを追加。
- v2.0 - XSSを防ぐためにデフォルトビューのエスケープを追加。