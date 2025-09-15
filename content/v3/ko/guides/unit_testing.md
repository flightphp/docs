# Flight PHP에서 PHPUnit를 사용한 단위 테스트

이 가이드는 [PHPUnit](https://phpunit.de/)를 사용한 Flight PHP의 단위 테스트를 소개하며, 초보자들이 단위 테스트가 왜 중요한지 이해하고 실무적으로 적용하는 데 초점을 맞춥니다. 우리는 *동작*을 테스트하는 데 중점을 두며, 이메일 보내기나 레코드 저장처럼 애플리케이션이 예상대로 작동하는지 확인합니다. 사소한 계산보다는 실질적인 동작에 초점을 맞추겠습니다. 간단한 [라우트 핸들러](/learn/routing)부터 시작해서 [컨트롤러](/learn/routing)로 진행하며, [의존성 주입](/learn/dependency-injection-container) (DI)과 타사 서비스 모킹을 포함합니다.

## 왜 단위 테스트를 해야 할까?

단위 테스트는 코드가 예상대로 작동하도록 보장하며, 프로덕션 환경에 버그가 도달하기 전에 포착합니다. 가벼운 라우팅과 유연성을 제공하는 Flight에서 특히 유용합니다. 솔로 개발자나 팀에게 단위 테스트는 안전망 역할을 하며, 예상되는 동작을 문서화하고 나중에 코드를 다시 볼 때 회귀 오류를 방지합니다. 또한 설계를 개선합니다: 테스트하기 어려운 코드는 과도하게 복잡하거나 밀접하게 결합된 클래스를 나타낼 수 있습니다.

단순한 예제(예: `x * y = z` 테스트)와 달리, 우리는 실제 세계의 동작에 초점을 맞춥니다. 예를 들어 입력 유효성 검사, 데이터 저장, 또는 이메일과 같은 동작을 다룹니다. 우리의 목표는 테스트를 접근하기 쉽고 의미 있게 만드는 것입니다.

## 일반적인 지침 원칙

1. **동작 테스트, 구현 테스트 아님**: 결과에 초점을 맞추세요(예: “이메일 전송” 또는 “레코드 저장”). 이는 리팩터링에 강건한 테스트를 만듭니다.
2. **`Flight::` 사용 중지**: Flight의 정적 메서드는 매우 편리하지만, 테스트를 어렵게 만듭니다. `$app = Flight::app();`에서 `$app` 변수를 사용하는 데 익숙해지세요. `$app`은 `Flight::`와 동일한 메서드를 가지고 있습니다. 여전히 `$app->route()` 또는 `$this->app->json()`을 컨트롤러에서 사용할 수 있습니다. 실제 Flight 라우터를 사용하려면 `$router = $app->router()`를 사용하고, `$router->get()`, `$router->post()`, `$router->group()` 등을 사용하세요. [Routing](/learn/routing) 참조.
3. **테스트를 빠르게 유지**: 빠른 테스트는 자주 실행되도록 장려합니다. 단위 테스트에서 데이터베이스 호출처럼 느린 작업을 피하세요. 테스트가 느리면 통합 테스트를 작성하고 있다는 신호입니다. 통합 테스트는 실제 데이터베이스, HTTP 호출, 이메일 전송 등을 포함하지만, 느리고 불안정할 수 있습니다(때때로 알려지지 않은 이유로 실패).
4. **설명적인 이름 사용**: 테스트 이름은 테스트되는 동작을 명확히 설명해야 합니다. 이는 가독성과 유지보수성을 높입니다.
5. **전역 변수 피하기**: `$app->set()`과 `$app->get()` 사용을 최소화하세요. 이는 전역 상태처럼 작동하여 매 테스트마다 모킹이 필요합니다. DI나 DI 컨테이너를 선호하세요([Dependency Injection Container](/learn/dependency-injection-container) 참조). `$app->map()` 메서드 사용도 기술적으로 “전역”이므로 피하세요. [flightphp/session](https://github.com/flightphp/session)과 같은 세션 라이브러리를 사용해 세션 객체를 모킹하세요. **절대** [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php)를 코드에서 직접 호출하지 마세요. 이는 전역 변수를 주입하여 테스트를 어렵게 만듭니다.
6. **의존성 주입 사용**: 컨트롤러에 의존성(예: [`PDO`](https://www.php.net/manual/en/class.pdo.php), 메일러)을 주입하여 로직을 분리하고 모킹을 단순화하세요. 의존성이 너무 많다면 [SOLID principles](https://en.wikipedia.org/wiki/SOLID)를 따르며 단일 책임을 가진 더 작은 클래스로 리팩터링하세요.
7. **타사 서비스 모킹**: 데이터베이스, HTTP 클라이언트(cURL), 또는 이메일 서비스를 모킹하여 외부 호출을 피하세요. 핵심 로직은 실행되도록 하되, 한두 계층까지만 테스트하세요. 예를 들어 앱이 문자 메시지를 보낸다면, 실제로 매 테스트마다 메시지를 보내지 마세요(비용이 발생하고 느려집니다). 대신 문자 서비스를 모킹하고 올바른 매개변수로 호출되었는지 확인하세요.
8. **높은 커버리지 목표, 완벽 아님**: 100% 라인 커버리지는 좋지만, 모든 것이 올바르게 테스트된다는 의미는 아닙니다([branch/path coverage in PHPUnit](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/)을 연구하세요). 중요한 동작(예: 사용자 등록, API 응답 및 실패 응답 포착)에 우선순위를 두세요.
9. **라우트에 컨트롤러 사용**: 라우트 정의에서 클로저 대신 컨트롤러를 사용하세요. `flight\Engine $app`은 기본적으로 생성자에서 모든 컨트롤러에 주입됩니다. 테스트에서 `$app = new Flight\Engine()`를 사용해 Flight를 인스턴스화하고 컨트롤러에 주입한 후 메서드를 직접 호출하세요(예: `$controller->register()`). [Extending Flight](/learn/extending) 및 [Routing](/learn/routing) 참조.
10. **모킹 스타일 선택 후 유지**: PHPUnit는 여러 모킹 스타일(예: prophecy, 내장 모킹)을 지원하거나 익명 클래스를 사용할 수 있습니다(코드 완성, 메서드 정의 변경 시 오류 등의 이점). 테스트 전체에서 일관되게 유지하세요. [PHPUnit Mock Objects](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles) 참조.
11. **테스트할 서브클래스의 메서드/속성에 `protected` 가시성 사용**: 이를 통해 서브클래스에서 재정의할 수 있으면서 공개적으로 만들지 않도록 합니다. 익명 클래스 모킹에 특히 유용합니다.

## PHPUnit 설정

먼저, Composer를 사용하여 [PHPUnit](https://phpunit.de/)를 Flight PHP 프로젝트에 설정하세요. 자세한 내용은 [PHPUnit Getting Started guide](https://phpunit.readthedocs.io/en/12.3/installation.html)를 참조하세요.

1. 프로젝트 디렉터리에서 다음 명령어를 실행하세요:
   ```bash
   // index.php 설정
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
			   $responseArray = ['status' => 'error', 'message' => '잘못된 이메일'];  // 이메일 유효성 검사 실패 시 오류 응답
		   } else {
			   $responseArray = ['status' => 'success', 'message' => '유효한 이메일'];  // 이메일 유효성 검사 성공 시 성공 응답
		   }

		   $this->app->json($responseArray);
	   }
   }
   ```

이제 테스트를 작성하면 `composer test`를 실행하여 라우트가 예상대로 작동하는지 확인하세요. Flight의 [requests](/learn/requests) 및 [responses](/learn/responses)에 대해 자세히 알아보세요.

## 테스트 가능한 컨트롤러를 위한 의존성 주입 사용

더 복잡한 시나리오에서는 [의존성 주입](/learn/dependency-injection-container) (DI)을 사용하여 컨트롤러를 테스트 가능하게 만드세요. Flight의 전역(예: `Flight::set()`, `Flight::map()`, `Flight::register()`)을 피하세요. 대신 Flight의 DI 컨테이너, [DICE](https://github.com/Level-2/Dice), [PHP-DI](https://php-di.org/) 또는 수동 DI를 사용하세요.

[`flight\database\PdoWrapper`](/awesome-plugins/pdo-wrapper)를 원시 PDO 대신 사용하세요. 이 래퍼는 모킹과 단위 테스트가 훨씬 쉽습니다!

사용자를 데이터베이스에 저장하고 환영 이메일을 보내는 컨트롤러 예제:

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
			// 단위 테스트에서 실행을 중지하도록 return 추가
			return $this->app->jsonHalt(['status' => 'error', 'message' => '잘못된 이메일']);
		}

		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);

		return $this->app->json(['status' => 'success', 'message' => '사용자 등록됨']);
    }
}
```

**주요 포인트**:
- 컨트롤러는 [`PdoWrapper`](/awesome-plugins/pdo-wrapper) 인스턴스와 `MailerInterface`(가상의 타사 이메일 서비스)에 의존합니다.
- 의존성은 생성자를 통해 주입되어 전역을 피합니다.

### 컨트롤러를 모킹하여 테스트

`UserController`의 동작을 테스트하세요: 이메일 유효성 검사, 데이터베이스 저장, 이메일 전송. 데이터베이스와 메일러를 모킹하여 컨트롤러를 격리합니다.

```php
// tests/UserControllerDICTest.php
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {

		// 때때로 모킹 스타일을 혼합해야 함
		// 여기서 PHPUnit의 내장 모킹을 PDOStatement에 사용
		$statementMock = $this->createMock(PDOStatement::class);
		$statementMock->method('execute')->willReturn(true);
		// PdoWrapper를 익명 클래스로 모킹
        $mockDb = new class($statementMock) extends PdoWrapper {
			protected $statementMock;
			public function __construct($statementMock) {
				$this->statementMock = $statementMock;
			}

			// 이 방식으로 모킹하면 실제 데이터베이스 호출이 발생하지 않음
			// 추가로 PDOStatement 모킹을 설정하여 실패 시뮬레이션 등을 할 수 있음
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
			// 빈 생성자로 부모 생성자 우회
			public function __construct() {}
            public function runQuery(string $sql, array $params = []): PDOStatement {
                throw new Exception('호출되지 않아야 함');
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                throw new Exception('호출되지 않아야 함');
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'invalid-email';

		// jsonHalt를 매핑하여 종료 방지
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
- `PdoWrapper`와 `MailerInterface`를 모킹하여 실제 데이터베이스 또는 이메일 호출을 피합니다.
- 테스트는 동작을 확인합니다: 유효한 이메일은 데이터베이스 삽입과 이메일 전송을 유발합니다; 유효하지 않은 이메일은 둘 다 건너뜁니다.
- 타사 의존성(예: `PdoWrapper`, `MailerInterface`)을 모킹합니다.

### 너무 많이 모킹

코드를 너무 많이 모킹하지 마세요. 아래 예제를 통해 왜 이것이 나쁠 수 있는지 설명하겠습니다. `UserController`를 사용해 `isEmailValid` 메서드(사용 `filter_var`)와 `registerUser` 메서드를 새로 추가합니다.

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
			// 단위 테스트에서 실행을 중지하도록 return 추가
			return $this->app->jsonHalt(['status' => 'error', 'message' => '잘못된 이메일']);
		}

		$this->registerUser($email);

		$this->app->json(['status' => 'success', 'message' => '사용자 등록됨']);
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

그리고 실제로 아무것도 테스트하지 않는 과도한 모킹 단위 테스트:

```php
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
		// 추가 의존성 주입 생략 (쉬움으로 간주)
        $controller = new class($app) extends UserControllerDICV2 {
			protected $app;
			// 생성자에서 의존성 우회
			public function __construct($app) {
				$this->app = $app;
			}

			// 강제로 유효하게 만듦
			protected function isEmailValid($email) {
				return true; // 항상 true 반환, 실제 유효성 검사 우회
			}

			// 실제 DB 및 메일러 호출 우회
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

축하합니다, 단위 테스트가 통과했습니다! 하지만 `isEmailValid` 또는 `registerUser`의 내부 작동을 변경하면 테스트가 여전히 통과합니다. 왜냐하면 모든 기능을 모킹했기 때문입니다. 아래와 같이 변경하면:

```php
// UserControllerDICV2.php
class UserControllerDICV2 {

	// ... 다른 메서드 ...

	protected function isEmailValid($email) {
		// 로직 변경
		$validEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
		// 이제 특정 도메인만 허용
		$validDomain = strpos($email, '@example.com') !== false; 
		return $validEmail && $validDomain;
	}
}
```

테스트를 실행해도 통과합니다! 하지만 동작을 테스트하지 않았기 때문에(일부 코드가 실제로 실행되지 않음), 프로덕션에서 버그가 발생할 수 있습니다. 테스트를 새 동작에 맞게 수정해야 합니다.

## 전체 예제

Flight PHP 프로젝트의 전체 단위 테스트 예제는 GitHub에서 확인할 수 있습니다: [n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide).
추가 가이드: [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) 및 [Troubleshooting](/learn/troubleshooting).

## 일반적인 함정

- **과도한 모킹**: 모든 의존성을 모킹하지 마세요; 일부 로직(예: 컨트롤러 유효성 검사)은 실제 동작을 테스트하도록 실행하세요. [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) 참조.
- **전역 상태**: PHP의 전역 변수(예: [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php), [`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php))를 과도하게 사용하면 테스트가 깨지기 쉽습니다. `Flight::`도 마찬가지입니다. 명시적으로 의존성을 전달하도록 리팩터링하세요.
- **복잡한 설정**: 테스트 설정이 복잡하면 클래스가 너무 많은 의존성이나 책임을 가지고 [SOLID principles](https://en.wikipedia.org/wiki/SOLID)를 위반할 수 있습니다.

## 단위 테스트로 확장

단위 테스트는 대형 프로젝트나 몇 달 후 코드 재방문 시 빛을 발합니다. 동작을 문서화하고 회귀 오류를 포착하여 앱 재학습 시간을 절약합니다. 솔로 개발자에게는 중요한 경로(예: 사용자 가입, 결제 처리)를 테스트하세요. 팀에게는 기여 시 일관된 동작을 보장합니다. [Why Frameworks?](/learn/why-frameworks)에서 프레임워크와 테스트의 이점에 대해 자세히 알아보세요.

Flight PHP 문서 저장소에 자신의 테스트 팁을 기여하세요!

_작성자 [n0nag0n](https://github.com/n0nag0n) 2025_