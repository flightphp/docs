# Flight PHP での PHPUnit を使用したユニットテスト

このガイドは、[PHPUnit](https://phpunit.de/) を使用して Flight PHP でユニットテストを行う入門者向けのものです。*why* ユニットテストが重要かを理解し、実際的に適用する方法に焦点を当てます。計算のような単純なものではなく、*behavior*、つまりメールの送信やレコードの保存など、アプリケーションが期待通りに動作することを確認します。シンプルな [route handler](/learn/routing) から始め、[controller](/learn/routing) に進み、[dependency injection](/learn/dependency-injection-container) (DI) とサードパーティサービスのモックを組み込みます。

## なぜユニットテストを行うのか？

ユニットテストは、コードが期待通りに動作することを保証し、プロダクションにバグが到達するのを防ぎます。Flight の軽量なルーティングと柔軟性は複雑な相互作用を引き起こす可能性があるため、特に有用です。個人開発者やチームにとって、ユニットテストは期待される動作を文書化し、後でコードを再訪した際に回帰を防ぐ安全網となります。また、設計を改善します：テストしにくいコードは、過度に複雑または密結合のクラスを示していることが多いです。

単純な例（例: `x * y = z` のテスト）ではなく、現実世界の動作、例えば入力の検証、データの保存、またはメールのようなアクションのトリガーに焦点を当てます。私たちの目標は、テストを親しみやすく、有意義なものにします。

## 一般的な指導原則

1. **動作をテストする、実施をテストしない**: 結果（例: 「メールが送信された」または「レコードが保存された」）に焦点を当て、内部の詳細ではなくします。これにより、リファクタリングに対してテストを堅牢に保ちます。
2. **`Flight::` の使用をやめる**: Flight の静的メソッドは非常に便利ですが、テストを困難にします。`$app = Flight::app();` から得られる `$app` 変数を使用する習慣をつけてください。`$app` は `Flight::` と同じメソッドを持っています。コントローラーでは、`$app->route()` や `$this->app->json()` を引き続き使用できます。また、実際の Flight ルーターを使用するために `$router = $app->router()` を使用し、`$router->get()`、`$router->post()`、`$router->group()` などを行います。 [Routing](/learn/routing) を参照してください。
3. **テストを高速に保つ**: 高速なテストは頻繁な実行を促します。ユニットテストでデータベース呼び出しのような遅い操作を避けてください。テストが遅い場合、それは統合テストを書いているサインです。統合テストは実際のデータベース、HTTP 呼び出し、メール送信などを含みます。これらは有用ですが、遅く、不安定で、理由不明に失敗することがあります。
4. **記述的な名前を使用する**: テスト名はテストされる動作を明確に記述するべきです。これにより、読みやすさとメンテナビリティが向上します。
5. **グローバル変数を避ける**: `$app->set()` や `$app->get()` の使用を最小限にし、これらはグローバル状態として振る舞い、毎回のテストでモックが必要になります。DI または DI コンテナを優先してください（[Dependency Injection Container](/learn/dependency-injection-container) を参照）。`$app->map()` の使用も技術的に「グローバル」なので、DI に代えてください。 [flightphp/session](https://github.com/flightphp/session) などのセッションライブラリを使用し、テストでセッションオブジェクトをモックします。**Do not** [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php) を直接コードで呼び出さないでください。これはグローバル変数を注入し、テストを困難にします。
6. **依存性注入を使用する**: コントローラーに依存性（例: [`PDO`](https://www.php.net/manual/en/class.pdo.php)、メール送信者）を注入して、論理を分離し、モックを簡略化します。依存性が多すぎるクラスがある場合、[SOLID principles](https://en.wikipedia.org/wiki/SOLID) に従って単一責任を持つ小さなクラスにリファクタリングを検討してください。
7. **サードパーティサービスをモックする**: データベース、HTTP クライアント (cURL)、またはメールサービスをモックして外部呼び出しを避けます。コア論理を実行しつつ、1 つか 2 つの層だけをテストします。例えば、アプリケーションがテキストメッセージを送信する場合、テストごとに実際に送信したくありません（料金が積み上がり、遅くなります）。代わりに、テキストメッセージサービスをモックし、コードが正しいパラメータでサービスを呼び出したことを検証します。
8. **高いカバレッジを目指すが、完璧を求めない**: 100% 行カバレッジは良いですが、すべてが正しくテストされているわけではありません（[branch/path coverage in PHPUnit](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/) を調べてください）。重要な動作（例: ユーザー登録、API レスポンス、失敗したレスポンスのキャプチャ）を優先してください。
9. **ルートでコントローラーを使用する**: ルート定義でクロージャではなくコントローラーを使用してください。デフォルトで、`flight\Engine $app` はコンストラクタ経由ですべてのコントローラーに注入されます。テストでは、` $app = new Flight\Engine()` を使用して Flight をインスタンス化し、コントローラーに注入し、メソッドを直接呼び出します（例: `$controller->register()`）。 [Extending Flight](/learn/extending) と [Routing](/learn/routing) を参照してください。
10. **モッキングスタイルを選択し、堅持する**: PHPUnit は複数のモッキングスタイルをサポートします（例: prophecy、ビルトインのモック）、または匿名クラスを使用できます。これらはコード補完、メソッド定義の変更による破損などの利点があります。テスト全体で一貫性を保ってください。 [PHPUnit Mock Objects](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles) を参照してください。
11. **サブクラスでテストしたいメソッド/プロパティに `protected` -visibility を使用する**: これにより、パブリックにせずにテストサブクラスでオーバーライドできます。これは匿名クラスモックで特に有用です。

## PHPUnit の設定

まず、[PHPUnit](https://phpunit.de/) を Composer を使用して Flight PHP プロジェクトに設定します。詳細は [PHPUnit Getting Started guide](https://phpunit.readthedocs.io/en/12.3/installation.html) を参照してください。

1. プロジェクトディレクトリで実行します:
   ```bash
   composer require --dev phpunit/phpunit
   ```
   これで最新の PHPUnit を開発依存としてインストールします。

2. プロジェクトルートに `tests` ディレクトリを作成して、テストファイルを置きます。

3. 利便性のために `composer.json` にテストスクリプトを追加します:
   ```json
   // other composer.json content
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```

4. ルートに `phpunit.xml` ファイルを作成します:
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

テストが構築されたら、`composer test` を実行してテストを実行します。

## シンプルなルートハンドラーのテスト

基本的な [route](/learn/routing) から始め、ユーザーのメール入力の検証を行います。動作をテストします：有効なメールに対して成功メッセージを返し、無効なものに対してエラーを返します。メール検証には [`filter_var`](https://www.php.net/manual/en/function.filter-var.php) を使用します。

```php
// index.php ファイル
$app->route('POST /register', [ UserController::class, 'register' ]);

// UserController.php ファイル
class UserController {
	protected $app;

	public function __construct(flight\Engine $app) {
		$this->app = $app;
	}

	public function register() {
		$email = $this->app->request()->data->email;
		$responseArray = [];
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$responseArray = ['status' => 'error', 'message' => '無効なメール'];
		} else {
			$responseArray = ['status' => 'success', 'message' => '有効なメール'];
		}

		$this->app->json($responseArray);
	}
}
```

これをテストするために、テストファイルを作成します。 [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) でテストの構造化について詳しく知れます:

```php
// tests/UserControllerTest.php
use PHPUnit\Framework\TestCase;
use Flight;
use flight\Engine;

class UserControllerTest extends TestCase {

    public function testValidEmailReturnsSuccess() {
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'test@example.com'; // POST データのシミュレーション
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
        $response = $app->response()->getBody();
		$output = json_decode($response, true);
        $this->assertEquals('success', $output['status']);
        $this->assertEquals('有効なメール', $output['message']);
    }

    public function testInvalidEmailReturnsError() {
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'invalid-email'; // POST データのシミュレーション
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
		$response = $app->response()->getBody();
		$output = json_decode($response, true);
		$this->assertEquals('error', $output['status']);
		$this->assertEquals('無効なメール', $output['message']);
	}
}
```

**重要なポイント**:
- 要求クラスを使用して POST データのシミュレーションを行います。`$_POST`、`$_GET` などのグローバルを使用しないでください。これらはテストを複雑にします（値のリセットが必要で、他のテストが失敗する可能性があります）。
- すべてのコントローラーは、DI コンテナを設定せずにデフォルトで `flight\Engine` インスタンスが注入されます。これにより、コントローラーを直接テストしやすくなります。
- `Flight::` の使用が一切ないため、コードがテストしやすくなります。
- テストは動作を検証します：有効/無効なメールに対して正しいステータスとメッセージ。

`composer test` を実行して、ルートが期待通りに動作することを確認します。Flight の [requests](/learn/requests) と [responses](/learn/responses) については、関連ドキュメントを参照してください。

## テスト可能なコントローラーに対する依存性注入の使用

より複雑なシナリオでは、[dependency injection](/learn/dependency-injection-container) (DI) を使用してコントローラーをテスト可能にします。Flight のグローバル（例: `Flight::set()`、`Flight::map()`、`Flight::register()`）を避け、毎回のテストでモックが必要になります。代わりに、Flight の DI コンテナ、[DICE](https://github.com/Level-2/Dice)、[PHP-DI](https://php-di.org/)、または手動 DI を使用します。

[`flight\database\PdoWrapper`](/awesome-plugins/pdo-wrapper) を raw PDO の代わりに使用します。このラッパーはモックしやすく、ユニットテストが簡単です！

データベースにユーザーを保存し、ウェルカムメールを送信するコントローラーの例:

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
			// ユニットテストで実行を停止するのを助けるために return を追加
			return $this->app->jsonHalt(['status' => 'error', 'message' => '無効なメール']);
		}

		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);

		return $this->app->json(['status' => 'success', 'message' => 'ユーザーが登録されました']);
    }
}
```

**重要なポイント**:
- コントローラーは [`PdoWrapper`](/awesome-plugins/pdo-wrapper) インスタンスと `MailerInterface` (架空のサードパーティメールサービス) に依存します。
- 依存性はコンストラクタ経由で注入され、グローバルを使用しません。

### コントローラーのテストにモックを使用する

`UserController` の動作をテストします：メールの検証、データベースへの保存、メールの送信。データベースとメール送信者をモックしてコントローラーを分離します。

```php
// tests/UserControllerDICTest.php
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {

		// スタイルを混ぜる必要がある場合があります
		// ここでは PHPUnit のビルトインのモックで PDOStatement を使用
		$statementMock = $this->createMock(PDOStatement::class);
		$statementMock->method('execute')->willReturn(true);
		// PdoWrapper を匿名クラスでモック
        $mockDb = new class($statementMock) extends PdoWrapper {
			protected $statementMock;
			public function __construct($statementMock) {
				$this->statementMock = $statementMock;
			}

			// この方法でモックすると、実際のデータベース呼び出しは行われません。
			// PDOStatement モックをさらに設定して失敗をシミュレーションできます。
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
        $this->assertEquals('ユーザーが登録されました', $result['message']);
        $this->assertEquals('test@example.com', $mockMailer->sentEmail);
    }

    public function testInvalidEmailSkipsSaveAndEmail() {
		 $mockDb = new class() extends PdoWrapper {
			// 親コンストラクタをバイパスする空のコンストラクタ
			public function __construct() {}
            public function runQuery(string $sql, array $params = []): PDOStatement {
                throw new Exception('呼び出されるべきではありません');
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                throw new Exception('呼び出されるべきではありません');
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'invalid-email';

		// jsonHalt をマップして終了を避ける
		$app->map('jsonHalt', function($data) use ($app) {
			$app->json($data, 400);
		});
        $controller = new UserControllerDIC($app, $mockDb, $mockMailer);
        $controller->register();
        $response = $app->response()->getBody();
        $result = json_decode($response, true);
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('無効なメール', $result['message']);
    }
}
```

**重要なポイント**:
- `PdoWrapper` と `MailerInterface` をモックして、実際のデータベースやメール呼び出しを避けます。
- テストは動作を検証します：有効なメールはデータベースの挿入とメール送信をトリガーし、無効なメールは両方をスキップします。
- サードパーティの依存性（例: `PdoWrapper`、`MailerInterface`）をモックし、コントローラーの論理を実行します。

### 過度なモック

コードの多くをモックしないように注意してください。以下に、なぜ悪い例を示します。`UserController` を使用して、`isEmailValid`（`filter_var` を使用）と `registerUser` の新しいメソッドに変更します。

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
			// ユニットテストで実行を停止するのを助けるために return を追加
			return $this->app->jsonHalt(['status' => 'error', 'message' => '無効なメール']);
		}

		$this->registerUser($email);

		$this->app->json(['status' => 'success', 'message' => 'ユーザーが登録されました']);
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

そして、実際には何もテストしない過度なモックのユニットテスト:

```php
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
		// 追加の依存性注入をスキップするので簡単
        $controller = new class($app) extends UserControllerDICV2 {
			protected $app;
			// コンストラクタで依存をバイパス
			public function __construct($app) {
				$this->app = $app;
			}

			// 常に true を返すことで実際の検証をバイパス
			protected function isEmailValid($email) {
				return true;
			}

			// 実際の DB とメール送信呼び出しをバイパス
			protected function registerUser($email) {
				return false;
			}
		};
        $controller->register();
		$response = $app->response()->getBody();
		$result = json_decode($response, true);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('ユーザーが登録されました', $result['message']);
    }
}
```

ユニットテストが合格しました！しかし、`isEmailValid` や `registerUser` の内部動作を変更した場合、テストは依然として合格します。以下に示します。

```php
// UserControllerDICV2.php
class UserControllerDICV2 {

	// ... 他のメソッド ...

	protected function isEmailValid($email) {
		// 論理を変更
		$validEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
		// 今度は特定のドメインのみ
		$validDomain = strpos($email, '@example.com') !== false; 
		return $validEmail && $validDomain;
	}
}
```

テストを実行しても合格しますが、動作をテストしていない（一部のコードを実行させていない）ため、プロダクションでバグが発生する可能性があります。テストは新しい動作を考慮し、期待しない動作もテストするように修正してください。

## 完全な例

Flight PHP プロジェクトの完全なユニットテスト例は GitHub で見つかります: [n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide)。
詳細は [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) と [Troubleshooting](/learn/troubleshooting) を参照してください。

## 一般的な落とし穴

- **過度なモック**: すべての依存性をモックしないでください；一部の論理（例: コントローラーの検証）を実行して実際の動作をテストします。 [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) を参照してください。
- **グローバル状態**: PHP のグローバル変数（例: [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php)、[`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php)）や `Flight::` の多用はテストを脆くします。明示的に依存性を渡すようリファクタリングしてください。
- **複雑なセットアップ**: テストセットアップが面倒な場合、クラスに依存性や責任が多すぎる可能性があり、[SOLID principles](https://en.wikipedia.org/wiki/SOLID) に違反しているかもしれません。

## ユニットテストによるスケーリング

ユニットテストは大規模プロジェクトや数ヶ月後にコードを再訪する際に輝きます。動作を文書化し、回帰を検知してアプリの再学習を防ぎます。個人開発者には重要なパス（例: ユーザーサインアップ、支払い処理）をテストしてください。チームでは、貢献acrossで一貫した動作を確保します。 [Why Frameworks?](/learn/why-frameworks) でフレームワークとテストの利点について詳しく知れます。

Flight PHP ドキュメントリポジトリにあなたのテストチップを寄与してください！

_Written by [n0nag0n](https://github.com/n0nag0n) 2025_