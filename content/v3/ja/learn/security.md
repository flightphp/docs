# セキュリティ

## 概要

Webアプリケーションにおいてセキュリティは非常に重要です。アプリケーションが安全であり、ユーザーのデータが安全であることを確認したいはずです。FlightはWebアプリケーションを安全にするための多くの機能を提供します。

公式[スケルトン](https://github.com/flightphp/skeleton)には、専用の **`SECURITY.md`** とセキュリティヘッダーミドルウェアも同梱されているため、[AIコーディングツール](/learn/ai)（および人間）が、シークレット、ヘッダー、XSS/SQLルールを意図的に一元管理できる場所を持てます。これは `AGENTS.md` の一般的なコーディングスタイルとは別です。

## 理解

Webアプリケーションを構築する際に知っておくべき一般的なセキュリティ脅威がいくつかあります。最も一般的な脅威には次のものがあります:
- クロスサイトリクエストフォージェリ（CSRF）
- クロスサイトスクリプティング（XSS）
- SQLインジェクション
- クロスオリジンリソースシェアリング（CORS）

[テンプレート](/learn/templates)はデフォルトで出力をエスケープすることでXSS対策に役立ちます（TwigとLatteはこれを行います。この利点を活用してください）。[セッション](/awesome-plugins/session)は、以下で説明するようにユーザーのセッションにCSRFトークンを保存することでCSRF対策に役立ちます。PDOでプリペアドステートメントを使用するか、[SimplePdo](/learn/simple-pdo)のヘルパーを使用することで、SQLインジェクションを防ぐのに役立ちます。CORSは、`Flight::start()` が呼ばれる前の簡単なフックで処理できます。

これらの方法はすべて連携して、Webアプリケーションを安全に保つのに役立ちます。セキュリティのベストプラクティスを学び理解することを常に念頭に置いてください。AIアシスタントに、トレードオフを理解せずにページを読み込ませるためだけに「CSPを無効化」したりヘッダーを弱めたりするよう依頼しないでください。

## 基本的な使い方

### ヘッダー

HTTPヘッダーはWebアプリケーションを安全にする最も簡単な方法の1つです。ヘッダーを使用して、クリックジャッキング、XSS、その他の攻撃を防ぐことができます。これらのヘッダーをアプリケーションに追加する方法はいくつかあります。

ヘッダーのセキュリティを確認するのに優れたWebサイトが2つあります: [securityheaders.com](https://securityheaders.com/) と [observatory.mozilla.org](https://observatory.mozilla.org/) です。以下のコードを設定した後、これら2つのWebサイトでヘッダーが機能していることを簡単に確認できます。

スケルトンには `App\Middleware\SecurityHeadersMiddleware`（リクエストごとのnonce付きCSP、フレームオプション、HSTSなど）が含まれています。ヘッダーをオフにするのではなく、意図的にこれを拡張することを優先してください。

#### 手動で追加する

`Flight\Response` オブジェクトの `header` メソッドを使用して、これらのヘッダーを手動で追加できます。
```php
// X-Frame-Options ヘッダーを設定してクリックジャッキングを防ぐ
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Content-Security-Policy ヘッダーを設定してXSSを防ぐ
// 注意: このヘッダーは非常に複雑になる可能性があるため、
//  アプリケーションに合った例をインターネットで参照することをお勧めします
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// X-XSS-Protection ヘッダーを設定してXSSを防ぐ
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// X-Content-Type-Options ヘッダーを設定してMIMEスニッフィングを防ぐ
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Referrer-Policy ヘッダーを設定して送信されるリファラー情報の量を制御する
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Strict-Transport-Security ヘッダーを設定してHTTPSを強制する
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Permissions-Policy ヘッダーを設定して使用できる機能とAPIを制御する
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

これらは `routes.php` または `index.php` ファイルの先頭に追加できます。

#### フィルターとして追加する

次のようにフィルター/フックで追加することもできます:

```php
// フィルターでヘッダーを追加する
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

ミドルウェアクラスとして追加することもでき、どのルートに適用するかを最も柔軟に制御できます。一般に、これらのヘッダーはすべてのHTMLおよびAPIレスポンスに適用する必要があります。

スケルトン形式のパスと名前空間（**フォルダの大文字小文字は `App\Middleware` と一致**）:

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
		// インラインスクリプトがある場合は、ブートストラップからのCSP nonceを優先する（スケルトンは csp_nonce を設定する）
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

// app/config/routes.php — 空文字列のグループ = すべてのルートに対するグローバルミドルウェア
use App\Middleware\SecurityHeadersMiddleware;
use flight\net\Router;

$router->group('', function (Router $router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// その他のルート
}, [SecurityHeadersMiddleware::class]);
```

古いプロジェクトでは引き続き `app/middlewares` と `app\middlewares` を使用している場合があります。フォルダが一致していれば動作します。新しいスケルトンアプリでは **`app/Middleware/`** と **`App\Middleware`** を使用します。詳細は[オートローディング](/learn/autoloading)を参照してください。

### クロスサイトリクエストフォージェリ（CSRF）

クロスサイトリクエストフォージェリ（CSRF）は、悪意のあるWebサイトがユーザーのブラウザにあなたのWebサイトへリクエストを送信させる攻撃です。これにより、ユーザーが知らないうちにあなたのWebサイト上で操作を実行させられる可能性があります。Flightは組み込みのCSRF保護機構を提供していませんが、ミドルウェアを使用して独自に簡単に実装できます。

#### セットアップ

まず、CSRFトークンを生成し、ユーザーのセッションに保存する必要があります。その後、このトークンをフォームで使用し、フォーム送信時に検証できます。セッション管理には [flightphp/session](/awesome-plugins/session) プラグインを使用します。

```php
// CSRFトークンを生成し、ユーザーのセッションに保存する
// （セッションオブジェクトを作成してFlightにアタッチしていることを前提とします）
// 詳細はセッションのドキュメントを参照してください
Flight::register('session', flight\Session::class);

// トークンはセッションごとに1つだけ生成すれば十分です（これにより、
// 同じユーザーの複数のタブやリクエストにまたがって機能します）
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### デフォルトのPHP Flightテンプレートを使用する

```html
<!-- フォームでCSRFトークンを使用する -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- その他のフォームフィールド -->
</form>
```

##### Twigを使用する（スケルトンのデフォルト）

Twig関数を登録するか、すべてのフォームビューにトークンを渡します。グローバル + フォームフィールドの最小例:

```php
// Twigを設定するとき（例: services.php）
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

	// CSRFトークンを出力するカスタム関数を設定する
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

これで、Latteテンプレートで `csrf()` 関数を使用してCSRFトークンを出力できます。

```html
<form method="post">
	{csrf()}
	<!-- その他のフォームフィールド -->
</form>
```

#### CSRFトークンを確認する

CSRFトークンはいくつかの方法で確認できます。

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

##### イベントフィルター

```php
// このミドルウェアはリクエストがPOSTリクエストかどうかを確認し、そうであればCSRFトークンが有効かどうかを確認します
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// フォーム値からcsrfトークンを取得する
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
			// またはJSONレスポンスの場合
			Flight::jsonHalt(['error' => 'Invalid CSRF token'], 403);
		}
	}
});
```

### クロスサイトスクリプティング（XSS）

クロスサイトスクリプティング（XSS）は、悪意のあるフォーム入力があなたのWebサイトにコードを注入できる攻撃です。これらの機会のほとんどは、エンドユーザーが入力するフォーム値から生じます。ユーザーからの出力を**決して**信頼しないでください！常に、全員が世界最高のハッカーであると想定してください。彼らは悪意のあるJavaScriptやHTMLをあなたのページに注入できます。このコードは、ユーザーから情報を盗んだり、あなたのWebサイト上で操作を実行したりするために使用される可能性があります。Flightのビュークラスや、[Twig](/awesome-plugins/twig) や [Latte](/awesome-plugins/latte) のようなテンプレートエンジンを使用すると、出力を簡単にエスケープしてXSS攻撃を防ぐことができます。

```php
// ユーザーが自分の名前としてこれを使おうとするほど賢いと仮定します
$name = '<script>alert("XSS")</script>';

// これは出力をエスケープします
Flight::view()->set('name', $name);
// これは次を出力します: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Twig（スケルトンのデフォルト）とLatteはデフォルトで自動エスケープします — 生のPHP echoより優先してください
Flight::render('template', ['name' => $name]);
// Twig: {{ name }}  → エスケープされる
// コンテンツが完全に信頼できる場合を除き、|raw / エスケープされない出力は避けてください
```

### SQLインジェクション

SQLインジェクションは、悪意のあるユーザーがあなたのデータベースにSQLコードを注入できる攻撃です。これにより、データベースから情報を盗んだり、データベース上で操作を実行したりできます。繰り返しますが、ユーザーからの入力は**決して**信頼しないでください！常に、彼らが攻撃を企んでいるものと想定してください。プリペアドステートメントを使用してください。[SimplePdo](/learn/simple-pdo) のヘルパーはこれをデフォルトの方法にします。

```php
// Flight::db() が SimplePdo として登録されている（またはコントローラーに SimplePdo を注入している）と仮定します
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// SimplePdo（推奨）— バインドパラメーターを使ったワンライナー
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// ? プレースホルダーを使う場合も同じ考え方
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

スケルトン形式のコントローラーでは、テストとAI生成コードの一貫性を保つため、`Flight::db()` よりも `SimplePdo` のコンストラクタインジェクションを優先してください（[DIC](/learn/dependency-injection-container)）。

#### 安全でない例

以下は、このような一見無害な例から保護するために、なぜSQLプリペアドステートメントを使用するのかを示すものです:

```php
// エンドユーザーがWebフォームに入力します。
// フォームの値として、ハッカーは次のようなものを入力します:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// クエリが構築されると、次のようになります
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// 奇妙に見えますが、これは動作する有効なクエリです。実際、
// これはすべてのユーザーを返す非常に一般的なSQLインジェクション攻撃です。

var_dump($users); // これは単一のユーザー名だけでなく、データベース内のすべてのユーザーをダンプします
```

### シークレットと設定

- シークレットは、コミットされた `config.php` のサンプルではなく、**`.env`**（または実際の環境）に配置してください。
- スケルトンのルール: `config.php` にはリテラルのデフォルト値を持たせ、ブートストラップ時に環境変数をマージし、コントローラー内では `$_ENV` を**読み取らず**、代わりに設定を注入してください。詳細は[設定](/learn/configuration)を参照してください。
- APIキー、DBパスワード、セッション暗号化キーを決してコミットしないでください。AIツールには **`SECURITY.md`** を参照させ、安全でない近道を考案しないようにしてください。

### JSONPコールバックの検証

Flightの `Flight::jsonp()` メソッドを使用する場合、FlightがJSONPコールバックのパラメーター名を厳格な許可リスト正規表現（`/^[A-Za-z_$][\w$.]{0,127}$/`）に対して検証することに注意してください。このパターンに一致しないコールバック名はFlightに例外をスローさせるため、悪意のあるコールバック値を通じた任意のJavaScriptの注入を防ぎます。

この検証は組み込みであり、追加の設定は不要ですが、JSONPエンドポイントからの予期しないエラーのデバッグ時に知っておく価値があります。

### CORS

クロスオリジンリソースシェアリング（CORS）は、Webページ上の多くのリソース（例: フォント、JavaScriptなど）を、そのリソースの配信元ドメイン外の別ドメインからリクエストできるようにする仕組みです。Flightには組み込み機能はありませんが、`Flight::start()` メソッドが呼ばれる前に実行するフックで簡単に処理できます。

```php
// app/Utils/CorsUtil.php  (スケルトン: PascalCase の Utils フォルダ → App\Utils)

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
		// ここで許可するホストをカスタマイズします。
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

// ブートストラップ / ルート — start の前に実行する
$app = Flight::app();
$cors = new \App\Utils\CorsUtil($app);
$app->before('start', [ $cors, 'set' ]);
```

### Flight設定の堅牢化

Flightは、セキュリティに直接影響するいくつかのエンジン設定を公開しています。これらを正しく設定することは、アプリケーションを堅牢化する最も簡単な方法の1つです。

#### `flight.allow_method_override`

デフォルトでは、Flightはクライアントが `X-HTTP-Method-Override` ヘッダーまたはPOST本文の `_method` フィールドのいずれかを使用して、リクエストのHTTPメソッドを上書きできるようにします。これは `GET`/`POST` しか送信できないHTMLフォームには便利ですが、想定していない場合は危険です。攻撃者が通常のフォームを通じて `DELETE` や `PUT` リクエストを偽造する可能性があります。

アプリケーションがこの動作に依存していない場合（例: 任意のHTTP動詞を送信できるモダンなクライアントやJavaScriptフロントエンドが利用するAPIを構築している場合）、無効にする必要があります:

```php
// index.php またはブートストラップファイル内で、Flight::start() の前に
Flight::set('flight.allow_method_override', false);
```

デフォルト値は後方互換性のために `true` ですが、上書き機能を明示的に必要としないアプリケーションでは、**`false` に設定することを強く推奨します**。

#### `flight.debug`

Flightには `flight.debug` 設定があり、未処理の例外が発生したときに詳細なエラー情報（例外メッセージ、コード、完全なスタックトレース）をブラウザーに表示するかどうかを制御します。デフォルトは `false` で、汎用的な `500 Internal Server Error` メッセージのみが表示され、内部の詳細はクライアントに漏えいしません。

本番サーバーでは決して有効にしないでください。ローカルまたはステージング環境でのみ使用してください:

```php
// ローカル開発でのみ安全 — 本番環境では決して使用しない
Flight::set('flight.debug', true);
```

`flight.debug` が `false`（デフォルト）の場合でも、`flight.log_errors` を有効にすることでエラーを捕捉できます:

```php
// クライアントに公開せず、サーバー側でエラーをログに記録する
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
```

#### `flight.views.restrict_to_path`

Flightの組み込み `View` クラスは、絶対パスから、または `flight.views.path` の外へ遡る相対名（たとえば `../` を使用）からテンプレートを喜んで読み込みます。これは、フォルダー間でテンプレートを意図的に共有するアプリでは意図的な動作ですが、テンプレート名が信頼できない入力から来る場合にはパストラバーサルのリスクにもなります。

`flight.views.restrict_to_path` は既存アプリが動作し続けるように**デフォルトでオフ**です。文書化された無効にする理由がない限り、有効にしてください:

```php
// index.php またはブートストラップファイル内で、Flight::start() の前に
Flight::set('flight.views.restrict_to_path', true);
```

エンジンはビューの作成時にその設定を `View::$restrictToPath` に適用します（`flight.views.path` と `flight.views.extension` と同じパターンです）。有効にすると:

- `render()` と `fetch()` は、実パスが設定されたビューディレクトリ内にあるファイルのみをインクルードします（外側を指すシンボリックリンクも拒否されます）。
- `exists()` はそれらのパスに対して例外をスローする代わりに `false` を返します。
- `getTemplate()` 自体は変更されません。従来どおりの方法でパスを返します。
- ブロックされたファイルは `Template file is outside the views path.` をスローします。存在しないファイルは引き続き既存の `Template file not found: ...` メッセージをスローします。

TwigやLatteを、ビューディレクトリを指す独自のファイルシステムローダーとともに使用する場合、それらのエンジンはすでにそのルートにテンプレートを限定しています。それでも、`Flight::view()->render()` / `fetch()` を呼び出すコードが同じ保護を受けるように、Flightネイティブの `View` に対してこれを有効にしてください。公式[スケルトン](https://github.com/flightphp/skeleton)はブートストラップでこれを有効にしています。

#### 推奨される本番設定

```php
// index.php またはアプリ設定 / ブートストラップから適用する
Flight::set('flight.allow_method_override', false);
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
Flight::set('flight.views.restrict_to_path', true);
```

### エラーハンドリング

本番環境では、攻撃者に情報が漏えいするのを避けるため、機密性の高いエラー詳細を隠してください。本番環境では、`display_errors` を `0` に設定してエラーを表示する代わりに、エラーをログに記録してください。

```php
// bootstrap.php または index.php 内で

// app/config/config.php にこれを追加する
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // エラー表示を無効にする
    ini_set('log_errors', 1);     // 代わりにエラーをログに記録する
    ini_set('error_log', '/path/to/error.log');
}

// ルートまたはコントローラー内で
// 制御されたエラーレスポンスには Flight::halt() を使用する
Flight::halt(403, 'Access denied');
```

### 入力のサニタイズ

ユーザー入力を決して信頼しないでください。処理前に [filter_var](https://www.php.net/manual/en/function.filter-var.php) を使用してサニタイズし、悪意のあるデータが紛れ込まないようにしてください。アプリコードでは、生の `$_GET` / `$_POST` ではなく、`$app->request()`（または `Flight::request()`）を介して入力を読み取ることを優先してください。

```php

// $_POST['input'] と $_POST['email'] を含む $_POST リクエストを仮定します

// 文字列入力をサニタイズする
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// メールアドレスをサニタイズする
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### パスワードハッシュ

[password_hash](https://www.php.net/manual/en/function.password-hash.php) や [password_verify](https://www.php.net/manual/en/function.password-verify.php) のようなPHPの組み込み関数を使用して、パスワードを安全に保存し、安全に検証してください。パスワードは平文で保存してはならず、可逆的な方法で暗号化してもいけません。ハッシュ化により、データベースが侵害されたとしても実際のパスワードは保護されたままになります。

```php
$password = Flight::request()->data->password;
// 保存時にパスワードをハッシュ化する（例: 登録時）
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// パスワードを検証する（例: ログイン時）
if (password_verify($password, $stored_hash)) {
    // パスワードが一致する
}
```

### レート制限

キャッシュでリクエストレートを制限することで、ブルートフォース攻撃やサービス拒否攻撃から保護します。

```php
// flightphp/cache がインストールされ、登録されていると仮定します
// フィルターで flightphp/cache を使用する
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
- [セッション](/awesome-plugins/session) - ユーザーセッションを安全に管理する方法。
- [テンプレート](/learn/templates) - Twig/Latteの自動エスケープとXSS。
- [SimplePdo](/learn/simple-pdo) - プリペアドステートメントを使用したデータベースヘルパー。
- [PdoWrapper](/learn/pdo-wrapper) - 非推奨。新しいコードではSimplePdoを使用してください。
- [ミドルウェア](/learn/middleware) - セキュリティヘッダーの追加を簡単にするミドルウェアの使用方法。
- [設定](/learn/configuration) - `.env` とリテラル設定の比較、本番用フラグ。
- [AIと開発者エクスペリエンス](/learn/ai) - エージェント向けにセキュリティポリシーを `SECURITY.md` に保つ方法。
- [レスポンス](/learn/responses) - セキュアなヘッダーでHTTPレスポンスをカスタマイズする方法。
- [リクエスト](/learn/requests) - ユーザー入力を処理およびサニタイズする方法。
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - 入力サニタイズ用のPHP関数。
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - 安全なパスワードハッシュ用のPHP関数。
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - ハッシュ化されたパスワードを検証するためのPHP関数。

## トラブルシューティング
- Flight Frameworkのコンポーネントに関する問題のトラブルシューティング情報については、上記の「関連項目」セクションを参照してください。
- CSPがスクリプトをブロックする場合は、nonce（スケルトンのパターン）を追加するか、特定のオリジンを許可リストに追加してください。計画なしに `script-src *` を設定しないでください。

## 変更履歴
- ドキュメント – スケルトンの `App\Middleware`、TwigのCSRF/XSSに関する注意、SimplePdo、シークレット/`.env`、AIフレンドリーなプロジェクト向けの `SECURITY.md`。
- ドキュメント – Flight設定の堅牢化の下に `flight.views.restrict_to_path` を文書化（ネイティブビュー向けのオプトインのパス封じ込め）。
- v3.18.1 - `flight.allow_method_override`、`flight.debug`、JSONPコールバック検証を扱うFlight設定の堅牢化セクションを追加。
- v3.1.0 - CORS、エラーハンドリング、入力のサニタイズ、パスワードハッシュ、レート制限に関するセクションを追加。
- v2.0 - XSSを防ぐためにデフォルトビューのエスケープを追加。