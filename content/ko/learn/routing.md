# Routing

> **참고:** 라우팅에 대해 더 알고 싶다면, 더 자세한 설명을 보려면 [why frameworks](/learn/why-frameworks) 페이지를 확인하십시오.

Flight에서의 기본 라우팅은 URL 패턴과 콜백 함수 또는 클래스 및 메소드의 배열을 일치시킴으로써 이루어집니다.

```php
Flight::route('/', function(){
    echo '안녕, 세상아!';
});
```

콜백은 호출 가능한 모든 객체가 될 수 있습니다. 그래서 보통 기능을 사용할 수 있습니다:

```php
function hello(){
    echo '안녕, 세상아!';
}

Flight::route('/', 'hello');
```

또는 클래스 메소드를 사용할 수 있습니다:

```php
class Greeting {
    public static function hello() {
        echo '안녕, 세상아!';
    }
}

Flight::route('/', array('Greeting','hello'));
```

또는 객체 메소드를 사용할 수 있습니다:

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "안녕, {$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

라우트는 정의된 순서대로 일치시킵니다. 요청과 일치하는 첫 번째 경로가 호출됩니다.

## 메소드 라우팅

기본적으로 라우트 패턴은 모든 요청 메소드와 일치합니다. 특정 메소드에 응답할 수 있습니다.
URL 앞에 식별자를 배치하여 특정 메소드에 응답할 수 있습니다.

```php
Flight::route('GET /', function () {
  echo 'GET 요청이 도착했습니다.';
});

Flight::route('POST /', function () {
  echo 'POST 요청이 도착했습니다.';
});
```

`|` 구분자를 사용하여 단일 콜백에 여러 방법을 매핑할 수 있습니다:

```php
Flight::route('GET|POST /', function () {
  echo 'GET 또는 POST 요청을 받았습니다.';
});
```

또한 일부 도우미 방법이 있는 Router 객체를 가져올 수도 있습니다:

```php

$router = Flight::router();

// 모든 방법을 매핑합니다
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
  // /user/1234와 일치합니다.
});
```

이 방법은 가능하지만, 이름 지정된 매개변수 또는 정규 표현식이 포함된 이름 지정된 매개변수를 사용하는 것이 권장되며 
더 읽기 쉽고 유지보수하기 쉽습니다.

## 이름이 지정된 매개변수

콜백 함수로 전달할 이름이 지정된 매개변수를 지정할 수 있습니다.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "안녕, $name ($id)!";
});
```

이름이 지정된 매개변수와 함께 정규 표현식을 포함할 수도 있습니다
`:` 구분자를 사용하여:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // /bob/123을 일치하지만
  // /bob/12345를 일치하지 않습니다.
});
```

> **참고:** 이름이 지정된 매개변수와 함께 정규 표현식 그룹 `()`을 일치시키는 것은 지원되지 않습니다. :'\(

## 선택적 매개변수

매치에 선택적인 이름이 지정된 매개변수를 지정할 수 있습니다
괄호로 세그먼트를 래핑합니다.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 다음 URL을 일치시킬 것입니다.
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

일치하지 않는 선택적 매개변수는 `NULL`으로 전달됩니다.

## 와일드카드

매치는 개별 URL 세그먼트에 대해서만 수행됩니다. 여러 세그먼트를 매치하려면 `*` 와일드카드를 사용해야 합니다.

```php
Flight::route('/blog/*', function () {
  // /blog/2000/02/01와 일치합니다.
});
```

모든 요청을 하나의 콜백으로 라우팅하려면 다음과 같이 할 수 있습니다:

```php
Flight::route('*', function () {
  // 무언가를 실행합니다
});
```

## 전달

콜백 함수에서 `true`를 반환함으로써 다음 일치하는 경로로 실행을 전달할 수 있습니다.

```php
Flight::route('/user/@name', function (string $name) {
  // 어떤 조건을 확인합니다
  if ($name !== "Bob") {
    // 다음 경로로 이동합니다.
    return true;
  }
});

Flight::route('/user/*', function () {
  // 이것이 호출됩니다
});
```

## 경로 별칭

라우트에 별칭을 할당하여 URL이 후에 코드에서 동적으로 생성될 수 있도록 할 수 있습니다 (예: 템플릿).

```php
Flight::route('/users/@id', function($id) { echo '사용자:'.$id; }, false, 'user_view');

// 나중에 코드의 어딘가에서
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환합니다
```

이것은 특히 URL이 변경된 경우에 유용합니다. 위의 예에서, 사용자가 `/admin/users/@id`로 이동했다면
별칭을 사용하면 별칭을 참조하는 모든 위치를 변경할 필요가 없으므로 별칭이 `/admin/users/5`로 돌아가도록 합니다.

그룹에서도 별칭이 작동합니다:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// 나중에 코드의 어딘가에서
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환합니다
```

## 라우트 정보

일치하는 라우트 정보를 검사하려면 콜백에서 세 번째 매개변수로 `true`를 전달하여 라우트 객체를 요청할 수 있습니다. 
라우트 객체는 항상 콜백 함수로 전달된 마지막 매개변수가 됩니다.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 일치시킨 HTTP 메소드의 배열
  $route->methods;

  // 이름이 지정된 매개변수의 배열
  $route->params;

  // 일치하는 정규 표현식
  $route->regex;

  // URL 패턴에서 사용 된 '*'의 내용을 포함
  $route->splat;

  // URL 경로를 보여줍니다....필요한 경우에만
  $route->pattern;

  // 이에 할당된 미들웨어를 보여줍니다
  $route->middleware;

  // 이 라우트에 할당된 별칭을 보여줍니다
  $route->alias;
}, true);
```

## 라우트 그룹화

때로는 관련된 경로를 그룹화하고 싶을 수도 있습니다 (예: `/api/v1`와 같은). 
이것은 `group` 메소드를 사용하여 수행할 수 있습니다:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// /api/v1/users와 일치합니다
  });

  Flight::route('/posts', function () {
	// /api/v1/posts와 일치합니다
  });
});
```

그룹의 그룹을 중첩시킬 수도 있습니다:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get()은 변수를 가져옵니다, 경로를 설정하는 것이 아닙니다! 아래의 객체 컨텍스트를 참조하세요
	Flight::route('GET /users', function () {
	  // GET /api/v1/users와 일치합니다
	});

	Flight::post('/posts', function () {
	  // POST /api/v1/posts와 일치합니다
	});

	Flight::put('/posts/1', function () {
	  // PUT /api/v1/posts와 일치합니다
	});
  });
  Flight::group('/v2', function () {

	// Flight::get()은 변수를 가져옵니다, 경로를 설정하는 것이 아닙니다! 아래의 객체 컨텍스트를 참조하세요
	Flight::route('GET /users', function () {
	  // GET /api/v2/users와 일치합니다
	});
  });
});
```

### 객체 컨텍스트에 그룹 지정

다음과 같이 `Engine` 객체에서 라우트 그룹을 사용할 수 있습니다:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // $router 변수 사용
  $router->get('/users', function () {
	// GET /api/v1/users와 일치합니다
  });

  $router->post('/posts', function () {
	// POST /api/v1/posts와 일치합니다
  });
});
```