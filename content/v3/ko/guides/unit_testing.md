# Flight PHP에서의 단위 테스트와 PHPUnit

이 가이드는 [PHPUnit](https://phpunit.de/)를 사용한 Flight PHP에서의 단위 테스트를 소개하며, 단위 테스트가 왜 중요한지 이해하고 실질적으로 적용하고 싶은 초보자를 대상으로 합니다. 우리는 *동작* 테스트에 중점을 두겠습니다—이메일 보내기나 레코드 저장과 같이 애플리케이션이 예상대로 작동하는지 확인하는 것—대신 사소한 계산에 초점을 맞춥니다. 간단한 [route handler](/learn/routing)부터 시작하여 더 복잡한 [controller](/learn/routing)로 진행하며, [dependency injection](/learn/dependency-injection-container) (DI)과 타사 서비스 모킹을 포함합니다.

## 왜 단위 테스트를 할까?

단위 테스트는 코드가 예상대로 동작하는지 확인하며, 프로덕션에 도달하기 전에 버그를 포착합니다. Flight에서 가벼운 라우팅과 유연성은 복잡한 상호작용으로 이어질 수 있어 특히 가치 있습니다. 솔로 개발자나 팀에게 단위 테스트는 안전망 역할을 하며, 예상 동작을 문서화하고 나중에 코드를 다시 볼 때 회귀를 방지합니다. 또한 설계를 개선합니다: 테스트하기 어려운 코드는 종종 과도하게 복잡하거나 긴밀하게 결합된 클래스를 나타냅니다.

단순한 예제(예: `x * y = z` 테스트)와 달리, 우리는 입력 유효성 검사, 데이터 저장, 이메일과 같은 실제 동작에 중점을 둡니다. 우리의 목표는 테스트를 접근하기 쉽고 의미 있게 만드는 것입니다.

## 일반 지침 원칙

1. **구현이 아닌 동작 테스트**: 내부 세부 사항이 아닌 결과(예: “이메일 발송” 또는 “레코드 저장”)에 중점을 두세요. 이는 리팩토링에 대한 테스트의 견고성을 만듭니다.
2. **Flight:: 사용 중지**: Flight의 정적 메서드는 매우 편리하지만 테스트를 어렵게 만듭니다. `$app = Flight::app();`에서 가져온 `$app` 변수를 사용하는 데 익숙해지세요. `$app`는 `Flight::`와 동일한 메서드를 모두 가지고 있습니다. 컨트롤러 등에서 여전히 `$app->route()` 또는 `$this->app->json()`을 사용할 수 있습니다. 또한 `$router = $app->router();`로 실제 Flight 라우터를 사용하고 `$router->get()`, `$router->post()`, `$router->group()` 등을 사용할 수 있습니다. [Routing](/learn/routing) 참조.
3. **테스트를 빠르게 유지**: 빠른 테스트는 빈번한 실행을 장려합니다. 단위 테스트에서 데이터베이스 호출과 같은 느린 작업을 피하세요. 테스트가 느리면 통합 테스트를 작성 중이라는 신호입니다. 통합 테스트는 실제 데이터베이스, 실제 HTTP 호출, 실제 이메일 발송 등을 포함합니다. 그들은 위치가 있지만 느리고 불안정할 수 있으며, 때때로 알려지지 않은 이유로 실패합니다.
4. **설명적인 이름 사용**: 테스트 이름은 테스트 중인 동작을 명확히 설명해야 합니다. 이는 가독성과 유지보수성을 향상시킵니다.
5. **전역 변수를 피하세요**: `$app->set()`과 `$app->get()` 사용을 최소화하세요. 이는 전역 상태처럼 작동하여 모든 테스트에서 모킹이 필요합니다. DI 또는 DI 컨테이너를 선호하세요( [Dependency Injection Container](/learn/dependency-injection-container) 참조). 심지어 `$app->map()` 메서드 사용도 기술적으로 "전역"이며 DI를 선호하여 피해야 합니다. [flightphp/session](https://github.com/flightphp/session)과 같은 세션 라이브러리를 사용하세요. 이렇게 하면 테스트에서 세션 객체를 모킹할 수 있습니다. 코드에서 [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php)을 직접 호출하지 마세요. 이는 전역 변수를 코드에 주입하여 테스트를 어렵게 만듭니다.
6. **Dependency Injection 사용**: 컨트롤러에 의존성(예: [`PDO`](https://www.php.net/manual/en/class.pdo.php), 메일러)을 주입하여 로직을 격리하고 모킹을 단순화하세요. 의존성이 너무 많은 클래스가 있으면 [SOLID principles](https://en.wikipedia.org/wiki/SOLID)을 따르는 단일 책임이 있는 더 작은 클래스로 리팩토링을 고려하세요.
7. **타사 서비스 모킹**: 데이터베이스, HTTP 클라이언트(cURL), 이메일 서비스를 모킹하여 외부 호출을 피하세요. 핵심 로직은 실행되도록 하되 1~2계층 깊이 테스트하세요. 예를 들어, 앱이 텍스트 메시지를 보낸다면 테스트 실행 시마다 실제로 텍스트 메시지를 보내지 마세요. 비용이 쌓이고 느려집니다. 대신 텍스트 메시지 서비스를 모킹하고 코드가 올바른 매개변수로 텍스트 메시지 서비스를 호출했는지 확인하세요.
8. **완벽이 아닌 높은 커버리지 목표**: 100% 라인 커버리지는 좋지만, 코드의 모든 것이 제대로 테스트되었다는 의미는 아닙니다( [PHPUnit에서의 branch/path coverage](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/)를 연구하세요). 중요한 동작(예: 사용자 등록, API 응답 및 실패 응답 포착)을 우선하세요.
9. **라우트에 컨트롤러 사용**: 라우트 정의에서 클로저가 아닌 컨트롤러를 사용하세요. 기본적으로 `flight\Engine $app`이 컨트롤러의 생성자를 통해 주입됩니다. 테스트에서 `$app = new Flight\Engine();`를 사용하여 테스트 내에서 Flight를 인스턴스화하고 컨트롤러에 주입한 후 메서드를 직접 호출하세요(예: `$controller->register()`). [Extending Flight](/learn/extending) 및 [Routing](/learn/routing) 참조.
10. **모킹 스타일 선택하고 일관되게 유지**: PHPUnit는 여러 모킹 스타일(예: prophecy, 내장 모킹)을 지원하며, 익명 클래스를 사용할 수도 있습니다. 이는 코드 완성, 메서드 정의 변경 시 깨짐 등의 이점이 있습니다. 테스트 전반에서 일관되게 하세요. [PHPUnit Mock Objects](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles) 참조.
11. **서브클래스에서 테스트할 메서드/속성에 `protected` 가시성 사용**: 이를 통해 공개하지 않고 테스트 서브클래스에서 재정의할 수 있으며, 익명 클래스 모킹에 특히 유용합니다.

## PHPUnit 설정

먼저 Composer를 사용하여 Flight PHP 프로젝트에 [PHPUnit](https://phpunit.de/)를 설정하세요. 자세한 내용은 [PHPUnit Getting Started guide](https://phpunit.readthedocs.io/en/12.3/installation.html)를 참조하세요.

1. 프로젝트 디렉토리에서 실행:
   ```bash
   composer require --dev phpunit/phpunit
   ```
   이는 최신 PHPUnit를 개발 의존성으로 설치합니다.

2. 테스트 파일을 위한 프로젝트 루트에 `tests` 디렉토리를 만듭니다.

3. 편의를 위해 `composer.json`에 테스트 스크립트를 추가:
   ```json
   // other composer.json content
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```

4. 루트에 `phpunit.xml` 파일 생성:
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

이제 테스트가 빌드되면 `composer test`를 실행하여 테스트를 실행할 수 있습니다.

## 간단한 라우트 핸들러 테스트

사용자 이메일 입력을 유효성 검사하는 기본 [route](/learn/routing)로 시작하겠습니다. 우리는 그 동작을 테스트합니다: 유효한 이메일에 성공 메시지 반환, 유효하지 않은 경우 오류 반환. 이메일 유효성 검사는 [`filter_var`](https://www.php.net/manual/en/function.filter-var.php)를 사용합니다.

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

이를 테스트하기 위해 테스트 파일을 만듭니다. 테스트 구조화에 대한 자세한 내용은 [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) 참조:

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

**주요 포인트**:
- 요청 클래스를 사용하여 POST 데이터를 시뮬레이션합니다. `$_POST`, `$_GET` 등의 전역을 사용하지 마세요. 이는 테스트를 더 복잡하게 만듭니다(항상 값을 재설정해야 하며 다른 테스트가 실패할 수 있습니다).
- 모든 컨트롤러는 DIC 컨테이너 없이도 기본적으로 `flight\Engine` 인스턴스가 주입됩니다. 이는 컨트롤러를 직접 테스트하기 쉽게 만듭니다.
- 전혀 `Flight::`를 사용하지 않아 코드가 테스트하기 쉬워집니다.
- 테스트는 동작을 확인합니다: 유효/유효하지 않은 이메일에 대한 올바른 상태와 메시지.

`composer test`를 실행하여 라우트가 예상대로 동작하는지 확인하세요. Flight에서의 [requests](/learn/requests)와 [responses](/learn/responses)에 대한 자세한 내용은 관련 문서를 참조하세요.

## 테스트 가능한 컨트롤러를 위한 Dependency Injection 사용

더 복잡한 시나리오를 위해 [dependency injection](/learn/dependency-injection-container) (DI)을 사용하여 컨트롤러를 테스트 가능하게 만듭니다. Flight의 전역(예: `Flight::set()`, `Flight::map()`, `Flight::register()`)을 피하세요. 이는 전역 상태처럼 작동하여 모든 테스트에서 모킹이 필요합니다. 대신 Flight의 DI 컨테이너, [DICE](https://github.com/Level-2/Dice), [PHP-DI](https://php-di.org/) 또는 수동 DI를 사용하세요.

원시 PDO 대신 [`flight\database\PdoWrapper`](/learn/pdo-wrapper)를 사용하겠습니다. 이 래퍼는 모킹과 단위 테스트가 훨씬 쉽습니다!

데이터베이스에 사용자 저장하고 환영 이메일 보내는 컨트롤러 예시:

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

**주요 포인트**:
- 컨트롤러는 [`PdoWrapper`](/learn/pdo-wrapper) 인스턴스와 `MailerInterface`(가상의 타사 이메일 서비스)에 의존합니다.
- 의존성은 생성자를 통해 주입되어 전역을 피합니다.

### 모킹으로 컨트롤러 테스트

이제 `UserController`의 동작을 테스트하겠습니다: 이메일 유효성 검사, 데이터베이스 저장, 이메일 발송. 데이터베이스와 메일러를 모킹하여 컨트롤러를 격리합니다.

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

**주요 포인트**:
- `PdoWrapper`와 `MailerInterface`를 모킹하여 실제 데이터베이스나 이메일 호출을 피합니다.
- 테스트는 동작을 확인합니다: 유효한 이메일은 데이터베이스 삽입과 이메일 발송을 트리거; 유효하지 않은 이메일은 둘 다 건너뜁니다.
- 타사 의존성(예: `PdoWrapper`, `MailerInterface`)을 모킹하고 컨트롤러의 로직은 실행되도록 합니다.

### 너무 많은 모킹

코드의 너무 많은 부분을 모킹하지 않도록 주의하세요. 아래에 `UserController`를 사용한 예를 들어 왜 이것이 나쁜지 설명하겠습니다. 우리는 그 검사를 `isEmailValid` 메서드( `filter_var` 사용)로 변경하고 다른 새로운 추가를 `registerUser`라는 별도의 메서드로 만듭니다.

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

이제 아무것도 실제로 테스트하지 않는 과도한 모킹 단위 테스트:

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

축하합니다. 단위 테스트가 있고 통과합니다! 하지만 `isEmailValid` 또는 `registerUser`의 내부 작동을 실제로 변경하면 어떨까요? 테스트는 여전히 통과할 것입니다. 왜냐하면 모든 기능을 모킹했기 때문입니다. 제가 의미하는 바를 보여드리겠습니다.

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

위 단위 테스트를 실행하면 여전히 통과합니다! 하지만 동작을 테스트하지 않았기 때문에(코드의 일부를 실제로 실행하지 않음), 프로덕션에서 발생할 잠재적 버그를 코딩했을 수 있습니다. 테스트는 새로운 동작을 고려하도록 수정되어야 하며, 예상과 다른 동작의 반대도 마찬가지입니다.

## 전체 예제

Flight PHP 프로젝트와 단위 테스트의 전체 예제를 GitHub에서 찾을 수 있습니다: [n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide).
더 깊은 이해를 위해 [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) 참조.

## 일반적인 함정

- **과도한 모킹**: 모든 의존성을 모킹하지 마세요; 실제 동작을 테스트하기 위해 일부 로직(예: 컨트롤러 유효성 검사)을 실행하세요. [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) 참조.
- **전역 상태**: 전역 PHP 변수(예: [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php), [`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php))를 많이 사용하면 테스트가 취약해집니다. `Flight::`도 마찬가지입니다. 의존성을 명시적으로 전달하도록 리팩토링하세요.
- **복잡한 설정**: 테스트 설정이 번거로우면 클래스가 너무 많은 의존성이나 책임을 가지고 [SOLID principles](/learn/unit-testing-and-solid-principles)를 위반할 수 있습니다.

## 단위 테스트로 확장

단위 테스트는 큰 프로젝트나 몇 달 후 코드 재방문 시 빛을 발합니다. 동작을 문서화하고 회귀를 포착하여 앱을 다시 배우는 데서 구합니다. 솔로 개발자에게는 중요한 경로(예: 사용자 가입, 결제 처리)를 테스트하세요. 팀에게는 기여 전반에서 일관된 동작을 보장합니다. 프레임워크와 테스트의 이점에 대한 자세한 내용은 [Why Frameworks?](/learn/why-frameworks) 참조.

Flight PHP 문서 저장소에 자신의 테스트 팁을 기여하세요!

_[n0nag0n](https://github.com/n0nag0n)에 의해 작성 2025_