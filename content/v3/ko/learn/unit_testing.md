# 단위 테스트

## 개요

Flight의 단위 테스트는 애플리케이션이 예상대로 작동하는지 확인하고, 버그를 조기에 포착하며, 코드베이스를 유지보수하기 쉽게 만드는 데 도움이 됩니다. Flight는 가장 인기 있는 PHP 테스트 프레임워크인 [PHPUnit](https://phpunit.de/)와 원활하게 작동하도록 설계되었습니다.

## 이해

단위 테스트는 애플리케이션의 작은 부분(컨트롤러나 서비스 등)의 동작을 격리된 상태에서 확인합니다. Flight에서 이는 루트, 컨트롤러, 로직이 다양한 입력에 어떻게 응답하는지를 테스트하는 것을 의미합니다—전역 상태나 실제 외부 서비스에 의존하지 않고요.

주요 원칙:
- **구현이 아닌 동작을 테스트하세요:** 코드가 무엇을 하는지에 초점을 맞추고, 어떻게 하는지에 초점을 맞추지 마세요.
- **전역 상태 피하기:** `Flight::set()`이나 `Flight::get()` 대신 의존성 주입을 사용하세요.
- **외부 서비스 모킹:** 데이터베이스나 메일러 같은 것을 테스트 더블로 대체하세요.
- **테스트를 빠르고 집중적으로 유지하세요:** 단위 테스트는 실제 데이터베이스나 API를 호출하지 않아야 합니다.

## 기본 사용법

### PHPUnit 설정

1. Composer로 PHPUnit 설치:
   ```bash
   composer require --dev phpunit/phpunit
   ```
2. 프로젝트 루트에 `tests` 디렉토리 생성.
3. `composer.json`에 테스트 스크립트 추가:
   ```json
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```
4. `phpunit.xml` 파일 생성:
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

이제 `composer test`로 테스트를 실행할 수 있습니다.

### 간단한 루트 핸들러 테스트

이메일을 검증하는 루트가 있다고 가정해 보세요:

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

이 컨트롤러에 대한 간단한 테스트:

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

**팁:**
- `$app->request()->data`를 사용하여 POST 데이터를 시뮬레이션하세요.
- 테스트에서 `Flight::` 정적 메서드를 피하세요—`$app` 인스턴스를 사용하세요.

### 테스트 가능한 컨트롤러를 위한 의존성 주입 사용

컨트롤러에 데이터베이스나 메일러 같은 의존성을 주입하여 테스트에서 쉽게 모킹할 수 있게 만드세요:

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

모킹을 사용한 테스트:

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

## 고급 사용법

- **모킹:** PHPUnit의 내장 모킹이나 익명 클래스를 사용하여 의존성을 대체하세요.
- **컨트롤러 직접 테스트:** 새로운 `Engine`으로 컨트롤러를 인스턴스화하고 의존성을 모킹하세요.
- **과도한 모킹 피하기:** 가능한 한 실제 로직을 실행하세요; 외부 서비스만 모킹하세요.

## 관련 자료

- [Unit Testing Guide](/guides/unit-testing) - 단위 테스트 모범 사례에 대한 포괄적인 가이드.
- [Dependency Injection Container](/learn/dependency-injection-container) - DIC를 사용하여 의존성을 관리하고 테스트 가능성을 향상시키는 방법.
- [Extending](/learn/extending) - 자체 도우미를 추가하거나 코어 클래스를 재정의하는 방법.
- [PDO Wrapper](/learn/pdo-wrapper) - 데이터베이스 상호작용을 단순화하고 테스트에서 모킹하기 쉽게 만듦.
- [Requests](/learn/requests) - Flight에서 HTTP 요청 처리.
- [Responses](/learn/responses) - 사용자에게 응답 전송.
- [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) - SOLID 원칙이 단위 테스트를 어떻게 향상시키는지 배우세요.

## 문제 해결

- 코드와 테스트에서 전역 상태(`Flight::set()`, `$_SESSION` 등)를 피하세요.
- 테스트가 느리다면 통합 테스트를 작성 중일 수 있습니다—외부 서비스를 모킹하여 단위 테스트를 빠르게 유지하세요.
- 테스트 설정이 복잡하다면 의존성 주입을 사용하도록 코드를 리팩토링하세요.

## 변경 로그

- v3.15.0 - 의존성 주입과 모킹 예제 추가.