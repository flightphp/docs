# 라우트 미들웨어

Flight은 라우트 및 그룹 라우트 미들웨어를 지원합니다. 미들웨어는 라우트 콜백 전에 (또는 후에) 실행되는 함수입니다. API 인증 확인을 코드에 추가하거나 사용자가 해당 라우트에 액세스할 수 있는 권한이 있는지 확인하는 좋은 방법입니다.

## 기본 미들웨어

다음은 기본 예제입니다:

```php
// 익명 함수만 제공하면 라우트 콜백 전에 실행됩니다.
// "after" 미들웨어 함수 는 들어올 수 없다. (아래 참조)
Flight::route('/path', function() { echo '여기 있어요!'; })->addMiddleware(function() {
	echo '첫 번째 미들웨어!';
});

Flight::start();

// 이것은 "첫 번째 미들웨어! 여기 있어요!"를 출력합니다.
```

미들웨어에 대해 사용하기 전에 알아두어야 할 중요한 사항 몇 가지가 있습니다:
- 미들웨어 함수들은 라우트에 추가된 순서대로 실행됩니다. 실행 방식은 [Slim Framework에서 처리하는 방식](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)과 유사합니다.
   - Befores는 추가된 순서대로 실행되며, Afters는 역순으로 실행됩니다.
- 미들웨어 함수가 false를 반환하면 모든 실행이 중지되고 403 Forbidden 오류가 발생합니다. 이를 더 세련되게 처리하려면 `Flight::redirect()`나 유사한 방법을 사용하는 것이 좋습니다.
- 라우트에서 매개변수가 필요한 경우, 해당 매개변수는 단일 배열로 미들웨어 함수에 전달됩니다. (`function($params) { ... }` 또는 `public function before($params) {}`). 이는 매개변수를 그룹으로 구성할 수 있도록 하여 일부 그룹에서 매개변수가 실제로 다른 순서로 표시될 수 있기 때문에 미들웨어 함수가 잘못된 매개변수를 참조하여 작동하지 않게 하는 문제를 방지하기 위한 것입니다. 이렇게 하면 위치 대신 이름으로 액세스할 수 있습니다.

## 미들웨어 클래스

미들웨어는 클래스로도 등록할 수 있습니다. "after" 기능이 필요한 경우 반드시 클래스를 사용해야 합니다.

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
Flight::route('/path', function() { echo '여기 있어요! '; })->addMiddleware($MyMiddleware); // also ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// 이는 "첫 번째 미들웨어! 여기 있어요! 마지막 미들웨어!"를 표시합니다.
```

## 미들웨어 그룹화

라우트 그룹을 추가하면 해당 그룹의 모든 라우트에 동일한 미들웨어가 적용됩니다. 이는 헤더에서 API 키를 확인하기 위해 Auth 미들웨어로 라우트를 그룹화해야 하는 경우 유용합니다.

```php

// 그룹 메소드 끝에 추가
Flight::group('/api', function() {

	// 이 "빈" 모양의 라우트는 사실 /api와 일치합니다
	Flight::route('', function() { echo 'api'; }, false, 'api');
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

모든 라우트에 전역 미들웨어를 적용하려면 "빈" 그룹을 추가할 수 있습니다:

```php

// 그룹 메소드 끝에 추가
Flight::group('', function() {
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```