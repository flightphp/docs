# 미들웨어

## 개요

Flight는 라우트와 그룹 라우트 미들웨어를 지원합니다. 미들웨어는 라우트 콜백 전에 (또는 후에) 코드가 실행되는 애플리케이션의 일부입니다. 이는 코드에 API 인증 검사를 추가하거나 사용자가 라우트에 접근할 권한이 있는지 확인하는 훌륭한 방법입니다.

## 이해

미들웨어는 앱을 크게 단순화할 수 있습니다. 복잡한 추상 클래스 상속이나 메서드 오버라이드 대신, 미들웨어를 사용하면 사용자 지정 앱 로직을 라우트에 할당하여 라우트를 제어할 수 있습니다. 미들웨어를 샌드위치처럼 생각할 수 있습니다. 바깥쪽에 빵이 있고, 양상추, 토마토, 고기, 치즈 같은 레이어가 있습니다. 각 요청이 샌드위치를 한 입 베어 물 때 바깥 레이어를 먼저 먹고 핵심으로 들어가는 것처럼 상상해 보세요.

미들웨어가 작동하는 시각적 예시입니다. 그 다음에 이 기능이 어떻게 작동하는지 실용적인 예를 보여드리겠습니다.

```text
사용자 요청 URL /api ----> 
	Middleware->before() 실행 ----->
		/api에 연결된 Callable/메서드 실행 및 응답 생성 ------>
	Middleware->after() 실행 ----->
사용자가 서버로부터 응답 받음
```

그리고 실용적인 예시입니다:

```text
사용자가 URL /dashboard로 이동
	LoggedInMiddleware->before() 실행
		before()가 유효한 로그인 세션 확인
			예: 아무것도 하지 않고 실행 계속
			아니오: 사용자를 /login으로 리다이렉트
				/api에 연결된 Callable/메서드 실행 및 응답 생성
	LoggedInMiddleware->after()가 정의되지 않아 실행 계속
사용자가 서버로부터 대시보드 HTML 받음
```

### 실행 순서

미들웨어 함수는 라우트에 추가된 순서대로 실행됩니다. 실행은 [Slim Framework가 이를 처리하는 방식](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)과 유사합니다.

`before()` 메서드는 추가된 순서대로 실행되고, `after()` 메서드는 역순으로 실행됩니다.

예: Middleware1->before(), Middleware2->before(), Middleware2->after(), Middleware1->after().

## 기본 사용법

미들웨어를 익명 함수나 클래스(권장) 같은 모든 호출 가능한 메서드로 사용할 수 있습니다.

### 익명 함수

간단한 예시입니다:

```php
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// 이는 "Middleware first! Here I am!"을 출력합니다.
```

> **주의:** 익명 함수를 사용할 때 해석되는 유일한 메서드는 `before()` 메서드입니다. 익명 클래스와 함께 `after()` 동작을 정의할 **수 없습니다**.

### 클래스 사용

미들웨어는 클래스(권장)로 등록할 수 있습니다. "after" 기능을 사용하려면 **반드시** 클래스를 사용해야 합니다.

```php
class MyMiddleware {
	public function before($params) {
		echo 'Middleware first!';
	}

	public function after($params) {
		echo 'Middleware last!';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); 
// 또는 ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// 이는 "Middleware first! Here I am! Middleware last!"를 표시합니다.
```

미들웨어 클래스 이름만 정의하고 클래스를 인스턴스화할 수도 있습니다.

```php
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware(MyMiddleware::class); 
```

> **주의:** 미들웨어 이름만 전달하면 [의존성 주입 컨테이너](dependency-injection-container)에 의해 자동으로 실행되며, 미들웨어는 필요한 매개변수로 실행됩니다. 의존성 주입 컨테이너가 등록되지 않은 경우, 기본적으로 `__construct(Engine $app)`에 `flight\Engine` 인스턴스를 전달합니다.

### 매개변수가 있는 라우트 사용

라우트에서 매개변수가 필요하면, 미들웨어 함수에 단일 배열로 전달됩니다. (`function($params) { ... }` 또는 `public function before($params) { ... }`). 이렇게 하는 이유는 매개변수를 그룹으로 구조화할 수 있고, 일부 그룹에서 매개변수가 다른 순서로 나타날 수 있어 잘못된 매개변수를 참조하여 미들웨어 함수가 깨질 수 있기 때문입니다. 이 방법으로 위치 대신 이름으로 접근할 수 있습니다.

```php
use flight\Engine;

class RouteSecurityMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$clientId = $params['clientId'];

		// jobId가 전달될 수도 있고 안 될 수도 있음
		$jobId = $params['jobId'] ?? 0;

		// job ID가 없으면 조회할 필요가 없을 수 있음.
		if($jobId === 0) {
			return;
		}

		// 데이터베이스에서 일종의 조회 수행
		$isValid = !!$this->app->db()->fetchField("SELECT 1 FROM client_jobs WHERE client_id = ? AND job_id = ?", [ $clientId, $jobId ]);

		if($isValid !== true) {
			$this->app->halt(400, 'You are blocked, muahahaha!');
		}
	}
}

// routes.php
$router->group('/client/@clientId/job/@jobId', function(Router $router) {

	// 아래 그룹은 여전히 부모 미들웨어를 받음
	// 하지만 매개변수는 미들웨어에서 단일 배열로 전달됨
	$router->group('/job/@jobId', function(Router $router) {
		$router->get('', [ JobController::class, 'view' ]);
		$router->put('', [ JobController::class, 'update' ]);
		$router->delete('', [ JobController::class, 'delete' ]);
		// 더 많은 라우트...
	});
}, [ RouteSecurityMiddleware::class ]);
```

### 미들웨어를 사용한 라우트 그룹화

라우트 그룹을 추가하면 그 그룹의 모든 라우트가 동일한 미들웨어를 갖게 됩니다. 헤더의 API 키를 확인하는 Auth 미들웨어로 여러 라우트를 그룹화해야 할 때 유용합니다.

```php

// 그룹 메서드 끝에 추가
Flight::group('/api', function() {

	// 이 "빈" 라우트는 실제로 /api와 일치
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// 이는 /api/users와 일치
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// 이는 /api/users/1234와 일치
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

모든 라우트에 글로벌 미들웨어를 적용하려면 "빈" 그룹을 추가할 수 있습니다:

```php

// 그룹 메서드 끝에 추가
Flight::group('', function() {

	// 이는 여전히 /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// 그리고 이는 여전히 /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // 또는 [ new ApiAuthMiddleware() ], 동일
```

### 일반적인 사용 사례

#### API 키 검증
`/api` 라우트를 보호하고 API 키가 올바른지 확인하려면 미들웨어로 쉽게 처리할 수 있습니다.

```php
use flight\Engine;

class ApiMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}
	
	public function before(array $params) {
		$authorizationHeader = $this->app->request()->getHeader('Authorization');
		$apiKey = str_replace('Bearer ', '', $authorizationHeader);

		// 데이터베이스에서 api 키 조회
		$apiKeyHash = hash('sha256', $apiKey);
		$hasValidApiKey = !!$this->db()->fetchField("SELECT 1 FROM api_keys WHERE hash = ? AND valid_date >= NOW()", [ $apiKeyHash ]);

		if($hasValidApiKey !== true) {
			$this->app->jsonHalt(['error' => 'Invalid API Key']);
		}
	}
}

// routes.php
$router->group('/api', function(Router $router) {
	$router->get('/users', [ ApiController::class, 'getUsers' ]);
	$router->get('/companies', [ ApiController::class, 'getCompanies' ]);
	// 더 많은 라우트...
}, [ ApiMiddleware::class ]);
```

이제 모든 API 라우트가 설정한 API 키 검증 미들웨어로 보호됩니다! 라우터 그룹에 더 많은 라우트를 추가하면 즉시 동일한 보호를 받습니다!

#### 로그인 검증

로그인한 사용자만 접근할 수 있는 일부 라우트를 보호하려면? 미들웨어로 쉽게 달성할 수 있습니다!

```php
use flight\Engine;

class LoggedInMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$session = $this->app->session();
		if($session->get('logged_in') !== true) {
			$this->app->redirect('/login');
			exit;
		}
	}
}

// routes.php
$router->group('/admin', function(Router $router) {
	$router->get('/dashboard', [ DashboardController::class, 'index' ]);
	$router->get('/clients', [ ClientController::class, 'index' ]);
	// 더 많은 라우트...
}, [ LoggedInMiddleware::class ]);
```

#### 라우트 매개변수 검증

URL에서 값을 변경하여 접근하지 말아야 할 데이터에 접근하는 것을 사용자에게서 보호하려면? 미들웨어로 해결할 수 있습니다!

```php
use flight\Engine;

class RouteSecurityMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$clientId = $params['clientId'];
		$jobId = $params['jobId'];

		// 데이터베이스에서 일종의 조회 수행
		$isValid = !!$this->app->db()->fetchField("SELECT 1 FROM client_jobs WHERE client_id = ? AND job_id = ?", [ $clientId, $jobId ]);

		if($isValid !== true) {
			$this->app->halt(400, 'You are blocked, muahahaha!');
		}
	}
}

// routes.php
$router->group('/client/@clientId/job/@jobId', function(Router $router) {
	$router->get('', [ JobController::class, 'view' ]);
	$router->put('', [ JobController::class, 'update' ]);
	$router->delete('', [ JobController::class, 'delete' ]);
	// 더 많은 라우트...
}, [ RouteSecurityMiddleware::class ]);
```

## 미들웨어 실행 처리

인증 미들웨어가 있고 사용자가 인증되지 않으면 로그인 페이지로 리다이렉트하려고 가정해 보세요. 몇 가지 옵션이 있습니다:

1. 미들웨어 함수에서 false를 반환하면 Flight가 자동으로 403 Forbidden 오류를 반환하지만, 사용자 지정이 없습니다.
1. `Flight::redirect()`를 사용하여 사용자를 로그인 페이지로 리다이렉트할 수 있습니다.
1. 미들웨어 내에서 사용자 지정 오류를 생성하고 라우트 실행을 중단할 수 있습니다.

### 간단하고 직관적

간단한 `return false;` 예시입니다:

```php
class MyMiddleware {
	public function before($params) {
		$hasUserKey = Flight::session()->exists('user');
		if ($hasUserKey === false) {
			return false;
		}

		// true이므로 모든 것이 계속 진행됨
	}
}
```

### 리다이렉트 예시

사용자를 로그인 페이지로 리다이렉트하는 예시입니다:
```php
class MyMiddleware {
	public function before($params) {
		$hasUserKey = Flight::session()->exists('user');
		if ($hasUserKey === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### 사용자 지정 오류 예시

API를 구축 중이므로 JSON 오류를 발생시켜야 한다고 가정해 보세요. 이렇게 할 수 있습니다:
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->getHeader('Authorization');
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'You must be logged in to access this page.'], 403);
			// 또는
			Flight::json(['error' => 'You must be logged in to access this page.'], 403);
			exit;
			// 또는
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']);
		}
	}
}
```

## 관련 항목
- [라우팅](/learn/routing) - 라우트를 컨트롤러에 매핑하고 뷰를 렌더링하는 방법.
- [요청](/learn/requests) - 들어오는 요청 처리 방법 이해.
- [응답](/learn/responses) - HTTP 응답 사용자 지정 방법.
- [의존성 주입](/learn/dependency-injection-container) - 라우트에서 객체 생성 및 관리 단순화.
- [프레임워크를 왜 사용할까?](/learn/why-frameworks) - Flight 같은 프레임워크 사용 이점 이해.
- [미들웨어 실행 전략 예시](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)

## 문제 해결
- 미들웨어에 리다이렉트가 있지만 앱이 리다이렉트되지 않는 것 같으면, 미들웨어에 `exit;` 문을 추가했는지 확인하세요.

## 변경 로그
- v3.1: 미들웨어 지원 추가.