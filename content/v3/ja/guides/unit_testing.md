# Flight PHP でのユニットテストと PHPUnit

このガイドは、[PHPUnit](https://phpunit.de/) を使用した Flight PHP でのユニットテストの入門を扱います。ユニットテストがなぜ重要かを理解し、実践的に適用したい初心者向けです。テストの焦点は *動作* に置き、アプリケーションが期待通りに動作することを確認します。例えば、メール送信やレコード保存のようなものです。単純な計算ではなく、シンプルな [ルートハンドラー](/learn/routing) から始め、より複雑な [コントローラー](/learn/routing) に進み、[依存性注入](/learn/dependency-injection-container) (DI) とサードパーティサービスのモッキングを組み込みます。

## なぜユニットテストするのか？

ユニットテストは、コードが期待通りに動作することを保証し、本番環境にバグが到達する前に検出します。Flight では、軽量なルーティングと柔軟性が複雑な相互作用を引き起こすため、特に価値があります。ソロ開発者やチームにとって、ユニットテストは安全網として機能し、期待される動作を文書化し、後でコードを再訪した際の回帰を防ぎます。また、デザインを改善します：テストしにくいコードは、過度に複雑または密結合なクラスを示すことが多いです。

単純な例（例：`x * y = z` のテスト）とは異なり、現実世界の動作、例えば入力検証、データ保存、メールなどのアクションのトリガーに焦点を当てます。テストを親しみやすく意味のあるものにするのが目標です。

## 一般的なガイドライン

1. **実装ではなく動作をテストする**：結果（例：「メール送信済み」や「レコード保存済み」）に焦点を当て、内部詳細ではなく。これにより、リファクタリングに対してテストが頑健になります。
2. **Flight:: の使用をやめる**：Flight の静的メソッドは非常に便利ですが、テストを難しくします。`$app = Flight::app();` から `$app` 変数を使用する習慣を付けましょう。`$app` は `Flight::` と同じメソッドを持ちます。コントローラーなどで `$app->route()` や `$this->app->json()` を使用できます。また、実際の Flight ルーターを `$router = $app->router();` で使用し、`$router->get()`、`$router->post()`、`$router->group()` などを利用してください。[Routing](/learn/routing) を参照。
3. **テストを高速に保つ**：高速なテストは頻繁な実行を促します。ユニットテストではデータベース呼び出しのような遅い操作を避けます。テストが遅い場合、それは統合テストを書いているサインで、ユニットテストではありません。統合テストは実際のデータベース、HTTP 呼び出し、メール送信などを含みます。それらは有用ですが、遅く不安定で、未知の理由で失敗することがあります。
4. **記述的な名前を使用する**：テスト名はテストされる動作を明確に記述すべきです。これにより読みやすさと保守性が向上します。
5. **グローバル変数を避ける**：`$app->set()` や `$app->get()` の使用を最小限にし、それらはグローバル状態として機能し、すべてのテストでモックを必要とします。DI または DI コンテナ（[Dependency Injection Container](/learn/dependency-injection-container) を参照）を優先してください。`$app->map()` メソッドの使用も技術的には「グローバル」なので、DI を優先して避けます。[flightphp/session](https://github.com/flightphp/session) のようなセッションワイブラリを使用し、テストでセッションオブジェクトをモックできるようにします。コードで [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php) を直接呼び出さないでください。それはグローバル変数を注入し、テストを難しくします。
6. **依存性注入を使用する**：コントローラーに依存（例：[`PDO`](https://www.php.net/manual/en/class.pdo.php)、メーラー）を注入し、ロジックを分離してモッキングを簡素化します。依存が多すぎるクラスがある場合、[SOLID principles](https://en.wikipedia.org/wiki/SOLID) に従った単一責任の小さなクラスにリファクタリングを検討してください。
7. **サードパーティサービスをモックする**：データベース、HTTP クライアント（cURL）、メールサービスをモックし、外部呼び出しを避けます。1 つか 2 層深くテストし、コアロジックを実行します。例えば、アプリがテキストメッセージを送信する場合、テストごとに実際のメッセージを送信したくありません（料金がかかり、遅くなります）。代わりにテキストメッセージサービスをモックし、コードが正しいパラメータでサービスを呼び出したかを検証します。
8. **完全ではなく高いカバレッジを目指す**：100% 行カバレッジは良いですが、コードが正しくテストされていることを意味しません（[PHPUnit でのブランチ/パス カバレッジ](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/) を調べてください）。重要な動作（例：ユーザー登録、API レスポンス、失敗レスポンスのキャプチャ）を優先します。
9. **ルートにコントローラーを使用する**：ルート定義ではクロージャではなくコントローラーを使用します。`flight\Engine $app` はデフォルトでコンストラクタ経由ですべてのコントローラーに注入されます。テストでは `$app = new Flight\Engine();` で Flight をインスタンス化し、コントローラーに注入し、メソッドを直接呼び出します（例：`$controller->register()`）。[Extending Flight](/learn/extending) と [Routing](/learn/routing) を参照。
10. **モッキングスタイルを選んで一貫させる**：PHPUnit は複数のモッキングスタイル（例：prophecy、内蔵モック）をサポートします。匿名クラスもコード補完やメソッド定義変更時の破損などの利点があります。テスト全体で一貫してください。[PHPUnit Mock Objects](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles) を参照。
11. **サブクラスでテストしたいメソッド/プロパティに `protected` 可視性を付ける**：これにより、公開せずにテストサブクラスでオーバーライドできます。これは匿名クラスモックで特に有用です。

## PHPUnit のセットアップ

まず、Composer を使用して Flight PHP プロジェクトに [PHPUnit](https://phpunit.de/) をセットアップします。簡単なテストのために。[PHPUnit Getting Started guide](https://phpunit.readthedocs.io/en/12.3/installation.html) で詳細を確認してください。

1. プロジェクトディレクトリで実行：
   ```bash
   composer require --dev phpunit/phpunit
   ```
   これで最新の PHPUnit が開発依存としてインストールされます。

2. プロジェクトルートに `tests` ディレクトリを作成し、テストファイルを置きます。

3. `composer.json` にテストスクリプトを追加して便利に：
   ```json
   // other composer.json content
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```

4. ルートに `phpunit.xml` ファイルを作成：
   ```xml
   <?xml version="1.0" encoding="UTF-8"?>
   <phpunit bootstrap="vendor/autoload.php">
       <testsuites>
           <testsuite name="Flight Tests">
               <directory>tests</directory>
           </testsuite>
       </testsuites>
   </phpunit>
   ```

これでテストが構築されたら、`composer test` でテストを実行できます。

## シンプルなルートハンドラーのテスト

基本的な [ルート](/learn/routing) から始めましょう。ユーザーのメール入力を検証するものです。動作をテスト：有効なメールには成功メッセージ、無効なものにはエラーを返します。メール検証には [`filter_var`](https://www.php.net/manual/en/function.filter-var.php) を使用します。

```php
// index.php
$app->route('POST /register', [ UserController::class, 'register' ]);

// UserController.php
class UserController {
	protected $app;

	public function __construct(flight\Engine $app) {
		$this->app = $app;
	}

	public function register() {
		$email = $this->app->request()->data->email;
		$responseArray = [];
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$responseArray = ['status' => 'error', 'message' => 'Invalid email'];
		} else {
			$responseArray = ['status' => 'success', 'message' => 'Valid email'];
		}

		$this->app->json($responseArray);
	}
}
```

これをテストするために、テストファイルを作成します。テストの構造については [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) を参照：

```php
// tests/UserControllerTest.php
use PHPUnit\Framework\TestCase;
use Flight;
use flight\Engine;

class UserControllerTest extends TestCase {

    public function testValidEmailReturnsSuccess() {
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'test@example.com'; // Simulate POST data
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
        $response = $app->response()->getBody();
		$output = json_decode($response, true);
        $this->assertEquals('success', $output['status']);
        $this->assertEquals('Valid email', $output['message']);
    }

    public function testInvalidEmailReturnsError() {
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'invalid-email'; // Simulate POST data
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
		$response = $app->response()->getBody();
		$output = json_decode($response, true);
		$this->assertEquals('error', $output['status']);
		$this->assertEquals('Invalid email', $output['message']);
	}
}
```

**主なポイント**：
- リクエストクラスを使用して POST データをシミュレートします。`$_POST`、`$_GET` などのグローバルを使用しないでください。それらはテストを複雑にし、値をリセットしないと他のテストが失敗する可能性があります。
- すべてのコントローラーは DIC コンテナを設定せずに、デフォルトで `flight\Engine` インスタンスが注入されます。これによりコントローラーを直接テストしやすくなります。
- `Flight::` の使用が一切なく、コードをテストしやすくします。
- テストは動作を検証：有効/無効なメールに対する正しいステータスとメッセージ。

`composer test` を実行して、ルートが期待通りに動作することを確認してください。Flight の [requests](/learn/requests) と [responses](/learn/responses) については関連ドキュメントを参照。

## テスト可能なコントローラーへの依存性注入の使用

より複雑なシナリオでは、[依存性注入](/learn/dependency-injection-container) (DI) を使用してコントローラーをテスト可能にします。Flight のグローバル（例：`Flight::set()`、`Flight::map()`、`Flight::register()`）を避け、それらはグローバル状態として機能し、すべてのテストでモックを必要とします。代わりに、Flight の DI コンテナ、[DICE](https://github.com/Level-2/Dice)、[PHP-DI](https://php-di.org/)、または手動 DI を使用します。

生の PDO の代わりに [`flight\database\PdoWrapper`](/learn/pdo-wrapper) を使用しましょう。このラッパーはモックとユニットテストがはるかに簡単です！

データベースにユーザーを保存し、ウェルカムメールを送信するコントローラー：

```php
use flight\database\PdoWrapper;

class UserController {
    protected $app;
    protected $db;
    protected $mailer;

    public function __construct(Engine $app, PdoWrapper $db, MailerInterface $mailer) {
        $this->app = $app;
        $this->db = $db;
        $this->mailer = $mailer;
    }

    public function register() {
		$email = $this->app->request()->data->email;
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			// adding the return here helps unit testing to stop execution
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Invalid email']);
		}

		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);

		return $this->app->json(['status' => 'success', 'message' => 'User registered']);
    }
}
```

**主なポイント**：
- コントローラーは [`PdoWrapper`](/learn/pdo-wrapper) インスタンスと `MailerInterface` （架空のサードパーティメールサービス）に依存します。
- 依存はコンストラクタ経由で注入され、グローバルを使用しません。

### モックを使用したコントローラーのテスト

次に、`UserController` の動作をテスト：メール検証、データベース保存、メール送信。コントローラーを分離するためにデータベースとメーラーをモックします。

```php
// tests/UserControllerDICTest.php
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {

		// Sometimes mixing mocking styles is necessary
		// Here we use PHPUnit's built-in mock for PDOStatement
		$statementMock = $this->createMock(PDOStatement::class);
		$statementMock->method('execute')->willReturn(true);
		// Using an anonymous class to mock PdoWrapper
        $mockDb = new class($statementMock) extends PdoWrapper {
			protected $statementMock;
			public function __construct($statementMock) {
				$this->statementMock = $statementMock;
			}

			// When we mock it this way, we are not really making a database call.
			// We can further setup this to alter the PDOStatement mock to simulate failures, etc.
            public function runQuery(string $sql, array $params = []): PDOStatement {
                return $this->statementMock;
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                $this->sentEmail = $email;
                return true;	
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
        $controller = new UserControllerDIC($app, $mockDb, $mockMailer);
        $controller->register();
		$response = $app->response()->getBody();
		$result = json_decode($response, true);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('User registered', $result['message']);
        $this->assertEquals('test@example.com', $mockMailer->sentEmail);
    }

    public function testInvalidEmailSkipsSaveAndEmail() {
		 $mockDb = new class() extends PdoWrapper {
			// An empty constructor bypasses the parent constructor
			public function __construct() {}
            public function runQuery(string $sql, array $params = []): PDOStatement {
                throw new Exception('Should not be called');
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                throw new Exception('Should not be called');
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'invalid-email';

		// Need to map jsonHalt to avoid exiting
		$app->map('jsonHalt', function($data) use ($app) {
			$app->json($data, 400);
		});
        $controller = new UserControllerDIC($app, $mockDb, $mockMailer);
        $controller->register();
        $response = $app->response()->getBody();
        $result = json_decode($response, true);
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Invalid email', $result['message']);
    }
}
```

**主なポイント**：
- `PdoWrapper` と `MailerInterface` をモックして、実際のデータベースやメール呼び出しを避けます。
- テストは動作を検証：有効なメールはデータベース挿入とメール送信をトリガー、無効なメールは両方をスキップ。
- サードパーティ依存（例：`PdoWrapper`、`MailerInterface`）をモックし、コントローラーのロジックを実行します。

### 過度なモッキング

コードを過度にモックしないように注意してください。以下に、`UserController` を使用した例を示します。これが悪い理由です。チェックを `isEmailValid` メソッド（`filter_var` を使用）に変更し、他の新しい追加を `registerUser` という別メソッドにします。

```php
use flight\database\PdoWrapper;
use flight\Engine;

// UserControllerDICV2.php
class UserControllerDICV2 {
	protected $app;
    protected $db;
    protected $mailer;

    public function __construct(Engine $app, PdoWrapper $db, MailerInterface $mailer) {
        $this->app = $app;
        $this->db = $db;
        $this->mailer = $mailer;
    }

    public function register() {
		$email = $this->app->request()->data->email;
		if (!$this->isEmailValid($email)) {
			// adding the return here helps unit testing to stop execution
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Invalid email']);
		}

		$this->registerUser($email);

		$this->app->json(['status' => 'success', 'message' => 'User registered']);
    }

	protected function isEmailValid($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
	}

	protected function registerUser($email) {
		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);
	}
}
```

そして、何も実際にはテストしない過度にモックされたユニットテスト：

```php
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
		// we are skipping the extra dependency injection here cause it's "easy"
        $controller = new class($app) extends UserControllerDICV2 {
			protected $app;
			// Bypass the deps in the construct
			public function __construct($app) {
				$this->app = $app;
			}

			// We'll just force this to be valid.
			protected function isEmailValid($email) {
				return true; // Always return true, bypassing real validation
			}

			// Bypass the actual DB and mailer calls
			protected function registerUser($email) {
				return false;
			}
		};
        $controller->register();
		$response = $app->response()->getBody();
		$result = json_decode($response, true);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('User registered', $result['message']);
    }
}
```

おめでとう、ユニットテストがあり、パスしています！しかし、`isEmailValid` や `registerUser` の内部動作を変更したらどうなるでしょうか？テストはすべての機能性をモックしたため、まだパスします。それが何を意味するかを示します。

```php
// UserControllerDICV2.php
class UserControllerDICV2 {

	// ... other methods ...

	protected function isEmailValid($email) {
		// Changed logic
		$validEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
		// Now it should only have a specific domain
		$validDomain = strpos($email, '@example.com') !== false; 
		return $validEmail && $validDomain;
	}
}
```

上記のユニットテストを実行しても、まだパスします！しかし、動作をテストしていなかった（コードの一部を実行させなかった）ため、本番でバグが発生する可能性があります。テストは新しい動作を考慮して修正し、期待しない動作の反対も含めるべきです。

## 完全な例

Flight PHP プロジェクトのユニットテストの完全な例は GitHub で見つかります：[n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide)。
より深い理解のために、[Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) を参照。

## 一般的な落とし穴

- **過度なモッキング**：すべての依存をモックせず、一部のロジック（例：コントローラー検証）を実行して実際の動作をテストします。[Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) を参照。
- **グローバル状態**：グローバル PHP 変数（例：[`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php)、[`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php)）の多用はテストを脆弱にします。`Flight::` も同様です。依存を明示的に渡すようリファクタリング。
- **複雑なセットアップ**：テストセットアップが面倒な場合、クラスが [SOLID principles](/learn/unit-testing-and-solid-principles) に違反して依存や責任が多すぎる可能性があります。

## ユニットテストによるスケーリング

ユニットテストは大規模プロジェクトや数ヶ月後のコード再訪で輝きます。動作を文書化し、回帰を検出してアプリの再学習を防ぎます。ソロ開発者には重要なパス（例：ユーザー登録、支払い処理）をテスト。チームには貢献間の動作の一貫性を確保。フレームワークとテストの利点については [Why Frameworks?](/learn/why-frameworks) を参照。

Flight PHP ドキュメントリポジトリに自分のテストのヒントを貢献してください！

_Written by [n0nag0n](https://github.com/n0nag0n) 2025_