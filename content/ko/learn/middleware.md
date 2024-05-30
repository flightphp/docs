```ko
# 라우트 미들웨어

Flight은 라우트 및 그룹 라우트 미들웨어를 지원합니다. 미들웨어는 라우트 콜백 앞이나 뒤에서 실행되는 함수입니다. 이는 코드에 API 인증 확인을 추가하거나 사용자가 라우트에 액세스할 수 있는 권한이 있는지 확인하는 좋은 방법입니다.

## 기본 미들웨어

다음은 기본 예제입니다:

```php
//익명 함수만 제공하는 경우, 라우트 콜백 전에 실행됩니다.
//클래스를 제외한 "after" 미들웨어 함수는 없습니다(아래 참조)
Flight::route('/경로', function() { echo '여기 있어요!'; })->addMiddleware(function() {
	echo '첫 번째 미들웨어!';
});

Flight::start();

//이것은 "첫 번째 미들웨어! 여기 있어요!"를 출력합니다.
```

미들웨어에 대해 사용하기 전에 알아야 할 몇 가지 중요한 사항이 있습니다.
- 미들웨어 함수는 라우트에 추가된 순서대로 실행됩니다. 실행은 [Slim 프레임워크가 처리하는 방식](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)과 유사합니다.
   - <i>Before</i>는 추가된 순서대로 실행되고, <i>After</i>는 역순으로 실행됩니다.
- 미들웨어 함수가 false를 반환하는 경우, 모든 실행이 중지되고 403 금지 오류가 발생합니다. 이를 더 세련되게 처리하려면 `Flight::redirect()` 또는 유사한 방법을 사용하는 것이 좋습니다.
- 라우트에서 매개변수가 필요한 경우, 라우트 미들웨어 함수에 단일 배열로 전달됩니다. (`function($params) { ... }` 또는 `public function before($params) {}`). 이것의 이유는 매개변수를 그룹으로 구성하고 이러한 그룹 중 일부에서 매개변수가 실제로 다른 순서로 나타날 수 있어서 잘못된 매개변수에 대한 미들웨어 함수가 망가지는 것을 방지하기 위함입니다. 이렇게 하면 위치가 아닌 이름으로 액세스할 수 있습니다.
- 미들웨어의 이름만 전달하면 [의존성 주입 컨테이너](dependency-injection-container)에서 자동으로 실행되고 필요한 매개변수로 미들웨어가 실행됩니다. 등록된 의존성 주입 컨테이너가 없는 경우 `__construct()`에 `flight\Engine` 인스턴스가 전달됩니다.

## 미들웨어 클래스

미들웨어는 클래스로도 등록할 수 있습니다. "After" 기능이 필요한 경우 반드시 클래스를 사용해야 합니다.

```php
class MyMiddleware {
	public function before($params) {
		echo '첫 번째 미들웨어!';
	}

	public function after($params) {
		echo '마지막 미들웨어!';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/경로', function() { echo '여기 있어요! '; })->addMiddleware($MyMiddleware); // 또는 ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

//이것은 "첫 번째 미들웨어! 여기 있어요! 마지막 미들웨어!"를 표시합니다.
```

## 미들웨어 오류 처리

인증 미들웨어가 있는 경우 사용자를 인증하지 않은 경우 로그인 페이지로 리디렉션하려면 사용할 수 있는 몇 가지 옵션이 있습니다:

1. 미들웨어 함수에서 false를 반환하여 Flight가 자동으로 403 금지 오류를 반환하도록 할 수 있지만 사용자 정의가 없습니다.
1. `Flight::redirect()`를 사용하여 사용자를 로그인 페이지로 리디렉션할 수 있습니다.
1. 미들웨어에서 사용자 지정 오류를 생성하고 라우트의 실행을 중지할 수 있습니다.

### 기본 예제

여기 간단한 false 반환 예제입니다:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// true인 경우 모든 것이 계속됩니다.
	}
}
```

### 리디렉트 예제

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

### 사용자 지정 오류 예제

API를 구축 중이므로 JSON 오류를 throw해야 하는 경우 다음과 같이 할 수 있습니다:
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->headers['Authorization'];
		if(empty($authorization)) {
			Flight::json(['error' => '이 페이지에 액세스하려면 로그인해야 합니다.'], 403);
			exit;
			// 또는
			Flight::halt(403, json_encode(['error' => '이 페이지에 액세스하려면 로그인해야 합니다.']);
		}
	}
}
```

## 미들웨어 그룹화

라우트 그룹을 추가하고 해당 그룹의 모든 라우트에 동일한 미들웨어를 추가할 수 있습니다. 이는 헤더에 있는 API 키를 확인하기 위해 Auth 미들웨어로 라우트를 그룹화해야 하는 경우에 유용합니다.

```php

//그룹 메소드의 끝에 추가됨
Flight::group('/api', function() {

	// "빈"으로 보이는이 라우트는 실제로 /api와 일치합니다.
	Flight::route('', function() { echo 'api'; }, false, 'api');
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

모든 라우트에 전역 미들웨어를 적용하려면 "빈" 그룹을 추가할 수 있습니다:

```php

//그룹 메소드의 끝에 추가됨
Flight::group('', function() {
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```