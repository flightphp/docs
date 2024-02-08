# 라우팅

> **참고:** 라우팅에 대해 더 알고 싶으신가요? [왜 프레임워크](/learn/why-frameworks) 페이지를 확인하여 자세한 설명을 살펴보세요.

Flight에서의 기본 라우팅은 URL 패턴과 콜백 함수 또는 클래스와 메소드의 배열을 일치시킴으로써 이루어집니다.

```php
Flight::route('/', function(){
    echo '안녕, 세상아!';
});
```

콜백은 호출 가능한 임의의 객체가 될 수 있습니다. 그러므로 일반 함수를 사용할 수 있습니다:

```php
function hello(){
    echo '안녕, 세상아!';
}

Flight::route('/', 'hello');
```

또는 클래스 메소드:

```php
class Greeting {
    public static function hello() {
        echo '안녕, 세상아!';
    }
}

Flight::route('/', array('Greeting','hello'));
```

또는 객체 메소드:

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = '홍길동';
    }

    public function hello() {
        echo "안녕, {$this->name}님!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

라우트는 정의된 순서대로 일치시킵니다. 요청과 가장 먼저 일치하는 첫 번째 라우트가 호출됩니다.

## 메소드 라우팅

기본적으로, 라우트 패턴은 모든 요청 메소드에 대해 일치됩니다. 특정 메소드에 응답하려면 URL 앞에 식별자를 배치하세요.

```php
Flight::route('GET /', function () {
  echo 'GET 요청을 받았습니다.';
});

Flight::route('POST /', function () {
  echo 'POST 요청을 받았습니다.';
});
```

또한 `|` 구분자를 사용하여 단일 콜백에 여러 메소드를 매핑할 수 있습니다:

```php
Flight::route('GET|POST /', function () {
  echo 'GET 또는 POST 요청을 받았습니다.';
});
```

또한 일부 도우미 메소드를 사용할 수 있는 Router 객체를 가져올 수 있습니다:

```php

$router = Flight::router();

// 모든 메소드 매핑
$router->map('/', function() {
	echo '안녕, 세상아!';
});

// GET 요청
$router->get('/users', function() {
	echo '사용자들';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## 정규 표현식

라우트에서 정규 표현식을 사용할 수 있습니다:

```php
Flight::route('/user/[0-9]+', function () {
  // 이는 /user/1234와 일치합니다.
});
```

이 방법이 제공되지만, 이름이 지정된 매개변수 또는 정규 표현식을 사용하는 것이 더 읽기 쉽고 유지보수가 더 쉬워 권장됩니다.

## 이름이 지정된 매개변수

콜백 함수로 전달될 이름이 지정된 매개변수를 라우트에 지정할 수 있습니다.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "$name님, 안녕하세요 ($id)!";
});
```

`:` 구분자를 사용하여 이름이 지정된 매개변수에 정규 표현식을 포함할 수도 있습니다:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // 이는 /bob/123에 일치합니다.
  // 하지만 /bob/12345에는 일치하지 않습니다.
});
```

> **참고:** 이름이 지정된 매개변수와 일치하는 정규식 그룹 `()`은 지원되지 않습니다. :'\(

## 선택적 매개변수

일치에 선택적인 명명된 매개변수를 지정할 수 있습니다. 세그먼트를 괄호로 감싸서 선택적 매개변수를 지정하세요.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 다음 URL들과 일치합니다:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

일치하지 않는 선택적 매개변수는 `NULL`로 전달됩니다.

## 와일드카드

매치는 개별 URL 세그먼트에서만 이루어집니다. 여러 세그먼트를 일치시키려면 `*` 와일드카드를 사용할 수 있습니다.

```php
Flight::route('/blog/*', function () {
  // 이는 /blog/2000/02/01과 일치합니다.
});
```

모든 요청을 단일 콜백으로 라우팅하려면 다음과 같이 할 수 있습니다:

```php
Flight::route('*', function () {
  // 뭔가 실행
});
```

## 전달

콜백 함수에서 `true`를 반환하여 다음 일치하는 라우트로 실행을 전달할 수 있습니다.

```php
Flight::route('/user/@name', function (string $name) {
  // 어떤 조건 확인
  if ($name !== "Bob") {
    // 다음 라우트로 이동
    return true;
  }
});

Flight::route('/user/*', function () {
  // 이것이 호출됩니다
});
```

## 라우트 별칭

라우트에 별칭을 지정하여 나중에 코드에서 동적으로 URL을 생성할 수 있습니다 (예: 템플릿과 같은 곳에서).

```php
Flight::route('/users/@id', function($id) { echo '사용자:'.$id; }, false, 'user_view');

// 나중에 코드의 어딘가에서
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'을 반환
```

이는 URL이 변경되는 경우 특히 도움이 됩니다. 위의 예제에서 사용자가 `/admin/users/@id`로 이동했다고 가정해 보죠.
별칭을 사용하면 별칭을 참조하는 모든 곳을 변경할 필요가 없으므로 별칭이 이제 `/admin/users/5`와 같이 반환됩니다.

라우트 별칭은 그룹에서도 작동합니다:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// 나중에 코드의 어딘가에서
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환
```

## 라우트 정보

라우트 일치 정보를 검사하려면 라우트 메소드에서 세 번째 매개변수로 `true`를 전달하여 콜백에 라우트 객체를 요청할 수 있습니다. 라우트 객체는 항상 콜백 함수에 전달되는 마지막 매개변수가 됩니다.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 일치하는 HTTP 메소드의 배열
  $route->methods;

  // 이름이 지정된 매개변수의 배열
  $route->params;

  // 일치하는 정규 표현식
  $route->regex;

  // URL 패턴에서 사용된 '*'의 내용이 포함됨
  $route->splat;

  // URL 경로를 보여줌...실제로 필요한 경우에만
  $route->pattern;

  // 이에 할당된 미들웨어를 보여줌
  $route->middleware;

  // 이 라우트에 할당된 별칭을 보여줌
  $route->alias;
}, true);
```

## 라우트 그룹화

(예: `/api/v1`과 같은) 관련 있는 라우트를 함께 그룹화하고 싶을 때가 있습니다. 이 경우, `group` 메소드를 사용할 수 있습니다:

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

그룹을 중첩하여 그룹으로 그룹화할 수도 있습니다:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get()는 변수를 가져오는 것이며, 라우트를 설정하지는 않습니다! 아래의 객체 컨텍스트 참조
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

	// Flight::get()는 변수를 가져오는 것이며, 라우트를 설정하지는 않습니다! 아래의 객체 컨텍스트 참조
	Flight::route('GET /users', function () {
	  // GET /api/v2/users와 일치
	});
  });
});
```

### 객체 컨텍스트로 그룹화

다음과 같이 `Engine` 객체에서 라우트 그룹을 사용할 수 있습니다:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // $router 변수 사용
  $router->get('/users', function () {
	// GET /api/v1/users와 일치
  });

  $router->post('/posts', function () {
	// POST /api/v1/posts와 일치
  });
});
```