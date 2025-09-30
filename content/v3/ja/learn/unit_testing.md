# ユニットテスト

## 概要

Flight でのユニットテストは、アプリケーションが期待通りに動作することを保証し、バグを早期に検出し、コードベースのメンテナンスを容易にします。Flight は、最も人気のある PHP テストフレームワークである [PHPUnit](https://phpunit.de/) とスムーズに動作するように設計されています。

## 理解

ユニットテストは、アプリケーションの小さな部分（コントローラーやサービスなど）を分離してその動作をチェックします。Flight では、これはルート、コントローラー、ロジックが異なる入力に対してどのように応答するかをテストすることを意味します—グローバル状態や実際の外部サービスに依存せずに。

主な原則:
- **実装ではなく動作をテスト:** コードが何をするかに焦点を当て、どうするかを気にしない。
- **グローバル状態を避ける:** `Flight::set()` や `Flight::get()` の代わりに依存性注入を使用。
- **外部サービスをモック:** データベースやメーラーなどのものをテストダブルで置き換え。
- **テストを高速で集中させる:** ユニットテストは実際のデータベースや API にアクセスしない。

## 基本的な使用方法

### PHPUnit のセットアップ

1. Composer で PHPUnit をインストール:
   ```bash
   composer require --dev phpunit/phpunit
   ```
2. プロジェクトのルートに `tests` ディレクトリを作成。
3. `composer.json` にテストスクリプトを追加:
   ```json
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```
4. `phpunit.xml` ファイルを作成:
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

これで `composer test` でテストを実行できます。

### シンプルなルートハンドラーのテスト

メールを検証するルートがあると仮定:

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
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->app->json(['status' => 'error', 'message' => 'Invalid email']);
        }
        return $this->app->json(['status' => 'success', 'message' => 'Valid email']);
    }
}
```

このコントローラーのシンプルなテスト:

```php
use PHPUnit\Framework\TestCase;
use flight\Engine;

class UserControllerTest extends TestCase {
    public function testValidEmailReturnsSuccess() {
        $app = new Engine();
        $app->request()->data->email = 'test@example.com';
        $controller = new UserController($app);
        $controller->register();
        $response = $app->response()->getBody();
        $output = json_decode($response, true);
        $this->assertEquals('success', $output['status']);
        $this->assertEquals('Valid email', $output['message']);
    }

    public function testInvalidEmailReturnsError() {
        $app = new Engine();
        $app->request()->data->email = 'invalid-email';
        $controller = new UserController($app);
        $controller->register();
        $response = $app->response()->getBody();
        $output = json_decode($response, true);
        $this->assertEquals('error', $output['status']);
        $this->assertEquals('Invalid email', $output['message']);
    }
}
```

**ヒント:**
- `$app->request()->data` を使用して POST データ をシミュレート。
- テストでは `Flight::` 静的メソッドを避け—`$app` インスタンスを使用。

### テスト可能なコントローラー向けの依存性注入の使用

コントローラーに依存性（データベースやメーラーなど）を注入して、テストで簡単にモックできるようにします:

```php
use flight\database\PdoWrapper;

class UserController {
    protected $app;
    protected $db;
    protected $mailer;
    public function __construct($app, $db, $mailer) {
        $this->app = $app;
        $this->db = $db;
        $this->mailer = $mailer;
    }
    public function register() {
        $email = $this->app->request()->data->email;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->app->json(['status' => 'error', 'message' => 'Invalid email']);
        }
        $this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
        $this->mailer->sendWelcome($email);
        return $this->app->json(['status' => 'success', 'message' => 'User registered']);
    }
}
```

モックを使ったテスト:

```php
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {
        $mockDb = $this->createMock(flight\database\PdoWrapper::class);
        $mockDb->method('runQuery')->willReturn(true);
        $mockMailer = new class {
            public $sentEmail = null;
            public function sendWelcome($email) { $this->sentEmail = $email; return true; }
        };
        $app = new flight\Engine();
        $app->request()->data->email = 'test@example.com';
        $controller = new UserController($app, $mockDb, $mockMailer);
        $controller->register();
        $response = $app->response()->getBody();
        $result = json_decode($response, true);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('User registered', $result['message']);
        $this->assertEquals('test@example.com', $mockMailer->sentEmail);
    }
}
```

## 高度な使用方法

- **モッキング:** PHPUnit のビルトインモックや匿名クラスを使用して依存性を置き換え。
- **コントローラーの直接テスト:** 新しい `Engine` でコントローラーをインスタンス化し、依存性をモック。
- **過度なモッキングを避ける:** 可能な限り実際のロジックを実行; 外部サービスのみモック。

## 関連項目

- [Unit Testing Guide](/guides/unit-testing) - ユニットテストのベストプラクティスに関する包括的なガイド。
- [Dependency Injection Container](/learn/dependency-injection-container) - DIC を使用して依存性を管理し、テスト可能性を向上させる方法。
- [Extending](/learn/extending) - 独自のヘルパー を追加したり、コアクラスをオーバーライドする方法。
- [PDO Wrapper](/learn/pdo-wrapper) - データベースインタラクションを簡素化し、テストでモックしやすくする。
- [Requests](/learn/requests) - Flight での HTTP リクエストの処理。
- [Responses](/learn/responses) - ユーザーにレスポンスを送信。
- [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) - SOLID 原則がユニットテストをどのように改善するかを学ぶ。

## トラブルシューティング

- コードとテストでグローバル状態（`Flight::set()`、`$_SESSION` など）を避ける。
- テストが遅い場合、インテグレーションテストを書いている可能性—外部サービスをモックしてユニットテストを高速に保つ。
- テストセットアップが複雑な場合、依存性注入を使用するようコードをリファクタリングを検討。

## 変更履歴

- v3.15.0 - 依存性注入とモッキングの例を追加。