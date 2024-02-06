
# 라우팅

Flight에서의 라우팅은 URL 패턴을 콜백 함수와 일치시켜 수행됩니다.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

콜백은 호출 가능한 모든 객체가 될 수 있습니다. 그래서 일반 함수를 사용할 수 있습니다:

```php
function hello(){
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

또는 클래스 메서드를 사용할 수 있습니다:

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', array('Greeting','hello'));
```

또는 객체 메서드를 사용할 수 있습니다:

```php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hello, {$this->name}!";
    }
}

$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

루트는 정의된 순서대로 일치합니다. 요청과 일치하는 첫 번째 루트가 호출됩니다.

## 메서드 라우팅

기본적으로 루트 패턴은 모든 요청 방법과 일치합니다. 특정 방법에 응답하려면 URL 앞에 식별자를 배치합니다.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});
```

`|` 구분자를 사용하여 단일 콜백에 여러 방법을 매핑할 수도 있습니다:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

## 정규 표현식

루트에 정규 표현식을 사용할 수 있습니다:

```php
Flight::route('/user/[0-9]+', function () {
  // 이것은 /user/1234와 일치합니다
});
```

## 명명된 매개변수

라우트에 전달될 명명된 매개변수를 지정할 수 있습니다.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

이름 있는 매개변수를 사용할 때 정규 표현식을 포함할 수도 있습니다. `:` 구분자 사용:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // 이것은 /bob/123과 일치합니다
  // 그러나 /bob/12345와는 일치하지 않습니다
});
```

이름 있는 매개변수와 일치하는 정규식 그룹 `()`은 지원되지 않습니다.

## 선택적 매개변수

일치시키는 데 선택적인 명명된 매개변수를 지정할 수 있습니다.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 다음 URL 일치:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

일치하지 않은 선택적 매개변수는 NULL로 전달됩니다.

## 와일드카드

일치는 개별 URL 세그먼트에서만 수행됩니다. 여러 세그먼트에 일치시키려면 `*` 와일드카드를 사용할 수 있습니다.

```php
Flight::route('/blog/*', function () {
  // 이것은 /blog/2000/02/01과 일치합니다
});
```

모든 요청을 단일 콜백으로 라우팅하려면 다음을 수행할 수 있습니다:

```php
Flight::route('*', function () {
  // 어떤 작업 수행
});
```

## 전달

콜백 함수에서 `true`를 반환하여 다음 일치하는 라우트로 실행을 전달할 수 있습니다.

```php
Flight::route('/user/@name', function (string $name) {
  // 일부 조건 확인
  if ($name !== "Bob") {
    // 다음 라우트로 이동
    return true;
  }
});

Flight::route('/user/*', function () {
  // 이것이 호출됩니다
});
```

## 라우트 정보

일치하는 라우트 정보를 검사하려면, 라우트 객체를 요청하여 라우트 메서드의 세 번째 매개변수로 `true`를 전달하면 됩니다. 라우트 객체는 항상 콜백 함수에 전달되는 마지막 매개변수가 됩니다.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 일치하는 HTTP 방법 배열
  $route->methods;

  // 명명된 매개변수 배열
  $route->params;

  // 일치하는 정규 표현식
  $route->regex;

  // URL 패턴에서 사용 된 '*' 내용이 포함됩니다
  $route->splat;
}, true);
```

## 라우트 그룹화

시기적으로 관련된 라우트를 그룹화하려는 경우(`/api/v1`과 같은) `group` 메서드를 사용할 수 있습니다:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// /api/v1/users와 일치
  });

  Flight::route('/posts', function () {
	// /api/v1/posts와 일치
  });
});
```

그룹안에 그룹을 중첩할 수도 있습니다:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get()은 변수를 가져옵니다. 라우트를 설정하지 않습니다! 아래 객체 컨텍스트 참조
	Flight::route('GET /users', function () {
	  // GET /api/v1/users와 일치
	});

	Flight::post('/posts', function () {
	  // POST /api/v1/posts와 일치
	});

	Flight::put('/posts/1', function () {
	  // PUT /api/v1/posts와 일치
	});
  });
  Flight::group('/v2', function () {

	// Flight::get()은 변수를 가져옵니다. 라우트를 설정하지 않습니다! 아래 객체 컨텍스트 참조
	Flight::route('GET /users', function () {
	  // GET /api/v2/users와 일치
	});
  });
});
```

## 객체 컨텍스트로 그룹화

다음과 같이 `Engine` 객체를 사용하여 라우트 그룹화를 계속 사용할 수 있습니다:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {
  $router->get('/users', function () {
	// GET /api/v1/users와 일치
  });

  $router->post('/posts', function () {
	// POST /api/v1/posts와 일치
  });
});
```

## 라우트 별칭

라우트에 별칭을 할당하여 URL을 나중에 동적으로 생성할 수 있습니다(예: 템플릿과 같은 경우).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// 코드의 나중 어딘가에서
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환합니다
```

위의 예제에서 매개변수가 변경되면 어떻게 되는지 살펴봅시다. `/admin/users/@id`로 이동했다고 가정합시다.
별칭이 있으면 별칭을 참조하는 모든 위치를 변경할 필요가 없습니다. 왜냐하면 별칭이 이제 `/admin/users/5`와 같이 반환되기 때문입니다.

라우트 별칭은 그룹에서도 작동합니다:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});

// 코드의 나중 어딘가에서
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환합니다
```

## 라우트 미들웨어
Flight는 라우트 및 그룹 라우트 미들웨어를 지원합니다. 미들웨어는 라우트 콜백 전(또는 후)에 실행되는 함수입니다. 이는 코드에 API 인증 검사를 추가하거나 사용자가 경로에 액세스할 권한이 있는지를 검증하는 좋은 방법입니다.

다음은 기본적인 예입니다:

```php
// 익명 함수만 제공하면 라우트 콜백 전에 실행됩니다.
// 클래스에 대한 "후" 미들웨어 함수는 없습니다(아래 참조)
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// 출력은 "Middleware first! Here I am!"이 됩니다
```

미들웨어에 대해 사용하기 전에 알아두어야 할 중요한 사항이 있습니다:
- 미들웨어 함수는 라우트에 추가된 순서대로 실행됩니다. 실행은 [Slim Framework에서 이를 처리하는 방법](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)과 유사합니다.
   - 이전 미들웨어는 추가된 순서대로 실행되며, 이후 미들웨어는 역순으로 실행됩니다.
- 만약 미들웨어 함수가 `false`를 반환하면 모든 실행이 중지되고 403 금지 오류가 발생합니다. 이것을 더 세련되게 처리하려면 `Flight::redirect()` 또는 비슷한 것을 사용하는 것이 좋습니다.
- 라우트에서 매개변수가 필요한 경우, 매개변수는 단일 배열로 미들웨어 함수에 전달됩니다(`function($params) { ... }` 또는 `public function before($params) {}`). 이것은 당신이 매개변수를 그룹으로 구조화할 수 있고, 일부 그룹에서는 실제로 규칙을 다르게 뒤바꿀 수 있기 때문에 미들웨어 함수를 깨뜨리는 매개변수를 잘못 사용하는 것 대신 이름으로 액세스할 수 있도록 되어 있습니다.

### 미들웨어 클래스

미들웨어를 클래스로 등록할 수도 있습니다. "후" 기능이 필요한 경우 클래스를 사용해야 합니다.

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
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); // also ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// 이것은 "Middleware first! Here I am! Middleware last!"를 표시합니다
```

### 미들웨어 그룹

라우트 그룹을 추가할 수도 있고, 그 그룹 내의 모든 라우트에 동일한 미들웨어를 추가할 수 있습니다. 이는 헤더의 API 키를 확인하는 Auth 미들웨어를 그룹화해야 하는 경우에 유용합니다.

```php

// 그룹 메서드의 끝에 추가
Flight::group('/api', function() {
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);