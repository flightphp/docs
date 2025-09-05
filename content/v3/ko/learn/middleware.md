# 경로 미들웨어

Flight은 경로 및 그룹 경로 미들웨어를 지원합니다. 미들웨어는 경로 콜백 전에 (또는 후에) 실행되는 함수입니다. 이는 코드에 API 인증 검사를 추가하거나 사용자가 경로에 접근할 권한이 있는지 확인하는 훌륭한 방법입니다.

## 기본 미들웨어

여기 기본 예제가 있습니다:

```php
// 만약 익명 함수만 제공하면, 라우트 콜백 전에 실행됩니다. 
// "after" 미들웨어 함수는 클래스만 가능합니다 (아래 참조)
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// 이 출력은 "Middleware first! Here I am!"입니다.
```

미들웨어에 대해 알아야 할 몇 가지 중요한 사항이 있습니다:
- 미들웨어 함수는 추가된 순서대로 실행됩니다. 실행 방식은 [Slim Framework handles this](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)와 유사합니다.
   - Before는 추가된 순서대로 실행되며, After는 역순으로 실행됩니다.
- 미들웨어 함수가 false를 반환하면 모든 실행이 중지되고 403 Forbidden 오류가 발생합니다. 아마도 `Flight::redirect()`와 같은 방식으로 더 세련되게 처리하고 싶을 것입니다.
- 경로의 매개변수가 필요하다면, 미들웨어 함수에 단일 배열로 전달됩니다. (`function($params) { ... }` 또는 `public function before($params) {}`). 이렇게 하는 이유는 매개변수를 그룹으로 구성할 수 있고, 그 그룹 중 일부에서 매개변수가 다른 순서로 나타날 수 있어서 미들웨어 함수가 잘못된 매개변수를 참조할 수 있기 때문입니다. 이 방법으로 위치 대신 이름으로 접근할 수 있습니다.
- 미들웨어 이름만 전달하면, [dependency injection container](dependency-injection-container)에 의해 자동으로 실행되며, 필요한 매개변수와 함께 실행됩니다. 의존성 주입 컨테이너가 등록되어 있지 않으면 `__construct()`에 `flight\Engine` 인스턴스를 전달합니다.

## 미들웨어 클래스

미들웨어는 클래스 형태로도 등록할 수 있습니다. "after" 기능이 필요하다면, **클래스를 사용해야** 합니다.

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
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); // 또는 ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// 이 출력은 "Middleware first! Here I am! Middleware last!"입니다.
```

## 미들웨어 오류 처리

인증 미들웨어가 있고, 사용자가 인증되지 않았다면 로그인 페이지로 리디렉션하고 싶다고 가정해 보겠습니다. 몇 가지 옵션이 있습니다:

1. 미들웨어 함수에서 false를 반환하면 Flight이 자동으로 403 Forbidden 오류를 반환하지만, 커스터마이징이 불가능합니다.
1. `Flight::redirect()`를 사용하여 사용자를 로그인 페이지로 리디렉션할 수 있습니다.
1. 미들웨어에서 사용자 정의 오류를 생성하고 경로 실행을 중지할 수 있습니다.

### 기본 예제

간단한 return false; 예제입니다:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// true이므로 모든 것이 계속 진행됩니다.
	}
}
```

### 리디렉션 예제

사용자를 로그인 페이지로 리디렉션하는 예제입니다:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### 사용자 정의 오류 예제

API를 구축 중이라면 JSON 오류를 발생시켜야 한다고 가정해 보겠습니다. 이렇게 할 수 있습니다:
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->headers['Authorization'];
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'You must be logged in to access this page.'], 403);
			// 또는
			Flight::json(['error' => 'You must be logged in to access this page.'], 403);
			exit;
			// 또는
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']));
		}
	}
}
```

## 그룹 미들웨어

경로 그룹을 추가하면, 그 그룹의 모든 경로에 동일한 미들웨어가 적용됩니다. 이는 헤더의 API 키를 확인하기 위해 여러 경로를 Auth 미들웨어로 그룹화하는 데 유용합니다.

```php
// 그룹 메서드의 끝에 추가됨
Flight::group('/api', function() {

	// 이 "empty" 경로는 /api와 일치합니다.
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// 이 경로는 /api/users와 일치합니다.
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// 이 경로는 /api/users/1234와 일치합니다.
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

모든 경로에 글로벌 미들웨어를 적용하려면, "empty" 그룹을 추가할 수 있습니다:

```php
// 그룹 메서드의 끝에 추가됨
Flight::group('', function() {

	// 여전히 /users입니다.
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// 그리고 여전히 /users/1234입니다.
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // 또는 [ new ApiAuthMiddleware() ], 동일합니다.
```