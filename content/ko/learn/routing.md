# 라우팅

> **참고:** 라우팅에 대해 더 이해하고 싶으신가요? 더 자세한 설명을 보려면 ["왜 프레임워크를 사용해야 하나요?"](/learn/why-frameworks) 페이지를 확인하세요.

Flight에서의 기본 라우팅은 URL 패턴을 콜백 함수 또는 클래스 및 메소드의 배열과 일치시켜 수행됩니다.

```php
Flight::route('/', function(){
    echo '안녕, 세상아!';
});
```

콜백은 호출 가능한 모든 객체가 될 수 있습니다. 따라서 보통 함수를 사용할 수 있습니다:

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
        echo "안녕하세요, {$this->name}씨!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

라우트는 정의된 순서대로 일치됩니다. 요청에 맞는 첫 번째 라우트가 호출됩니다.

## 메소드 라우팅

기본적으로 라우트 패턴은 모든 요청 메소드에 대해 일치됩니다. 특정 메소드에 응답할 수 있도록 식별자를 URL 앞에 배치하여 지정할 수 있습니다.

```php
Flight::route('GET /', function () {
  echo 'GET 요청을 받았습니다.';
});

Flight::route('POST /', function () {
  echo 'POST 요청을 받았습니다.';
});
```

`|` 구분자를 사용하여 단일 콜백에 여러 메소드를 매핑할 수도 있습니다:

```php
Flight::route('GET|POST /', function () {
  echo 'GET 또는 POST 요청을 받았습니다.';
});
```

또한 사용할 수 있는 몇 가지 도우미 메소드가 있는 Router 객체를 가져올 수도 있습니다:

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

라우트에 정규 표현식을 사용할 수 있습니다:

```php
Flight::route('/user/[0-9]+', function () {
  // 이는 /user/1234와 일치합니다
});
```

이 방법은 가능하지만, 이름 있는 매개변수 또는 정규 표현식과 함께 이름 있는 매개변수를 사용하는 것이 쉽고 유지 관리하기 쉬우므로 권장됩니다.

## 이름 있는 매개변수

콜백 함수로 전달될 이름 있는 매개변수를 지정할 수 있습니다.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "$name님, 안녕하세요 ($id)!";
});
```

`:` 구분자를 사용하여 이름 있는 매개변수와 함께 정규 표현식을 포함할 수도 있습니다:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // 이는 /bob/123에 일치합니다
  // 하지만 /bob/12345에는 일치하지 않습니다
});
```

> **참고:** 이름 있는 매개변수와 일치하는 정규 표현식 그룹 `()`는 지원되지 않습니다. :'\(

## 선택적 매개변수

매칭에 선택적인 이름 있는 매개변수를 지정할 수 있습니다. 세그먼트를 괄호로 묶어 선택적으로 일치하도록 할 수 있습니다.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 다음 URL들과 일치할 것입니다:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

일치하지 않는 선택적 매개변수는 `NULL`로 전달됩니다.

## 와일드카드

일치는 개별 URL 세그먼트에 대해 수행됩니다. 여러 세그먼트에 일치시키려면 `*` 와일드카드를 사용할 수 있습니다.

```php
Flight::route('/blog/*', function () {
  // 이는 /blog/2000/02/01과 일치합니다
});
```

모든 요청을 하나의 콜백에 매핑하려면 다음과 같이 할 수 있습니다:

```php
Flight::route('*', function () {
  // 무언가를 수행합니다
});
```

## 전달

콜백 함수에서 `true`를 반환하여 다음 일치하는 라우트로 실행을 전달할 수 있습니다.

```php
Flight::route('/user/@name', function (string $name) {
  // 조건을 확인합니다
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

라우트에 별칭을 지정하여 URL을 동적으로 생성할 수 있으며 나중에 코드에서 사용할 수 있습니다 (예: 템플릿과 같은).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// 나중에 코드 어딘가에서
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환합니다
```

이것은 특히 URL이 변경될 때 유용합니다. 위의 예시에서 사용자가 `/admin/users/@id`로 이동했다고 가정해봅시다.
별칭을 사용하면 별칭을 참조하는 모든 곳을 변경할 필요가 없으며, 예시에서처럼 별칭은 이제 `/admin/users/5`를 반환합니다.

라우트 별칭은 그룹에서도 작동합니다:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});

// 나중에 코드 어딘가에서
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환합니다
```

## 라우트 정보

일치하는 라우트 정보를 검사하려면 라우트 메소드에서 세 번째 매개변수로 `true`를 전달하여 라우트 개체를 요청할 수 있습니다. 라우트 개체는 항상 콜백 함수에 전달되는 마지막 매개변수가 됩니다.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 일치하는 HTTP 메소드 배열
  $route->methods;

  // 이름 있는 매개변수 배열
  $route->params;

  // 일치하는 정규 표현식
  $route->regex;

  // URL 패턴에 사용된 '*'의 내용
  $route->splat;

  // URL 경로를 보여줍니다....실제로 필요한 경우에만 사용합니다
  $route->pattern;

  // 이에 할당된 미들웨어를 보여줍니다
  $route->middleware;

  // 이 라우트에 할당된 별칭을 표시합니다
  $route->alias;
}, true);
```

## 라우트 그루핑

관련된 라우트를 그룹화하려는 경우 (예: `/api/v1`과 같이), `group` 메소드를 사용할 수 있습니다:

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

그룹의 그룹을 중첩할 수도 있습니다:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get()은 변수를 가져옵니다, 라우트를 설정하는 것이 아닙니다! 아래의 객체 컨텍스트를 보세요
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

	// Flight::get()은 변수를 가져옵니다, 라우트를 설정하는 것이 아닙니다! 아래의 객체 컨텍스트를 보세요
	Flight::route('GET /users', function () {
	  // GET /api/v2/users와 일치합니다
	});
  });
});
```

### 객체 컨텍스트로 그룹화

다음과 같이 `Engine` 객체를 사용하여 라우트 그룹화를 사용할 수 있습니다:

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

## 스트리밍

이제 `streamWithHeaders()` 메소드를 사용하여 클라이언트에 응답을 스트리밍할 수 있습니다. 큰 파일을 보내거나 긴 실행 프로세스를 실행하거나 큰 응답을 생성할 때 유용합니다. 라우트 스트리밍은 일반적인 라우트와 약간 다르게 처리됩니다.

> **참고:** 응답 스트리밍 기능은 [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering)이 false로 설정된 경우에만 사용할 수 있습니다.

```php
Flight::route('/stream-users', function() {

	// 데이터 검색하는 방법은 다양하지만 간단한 예시로...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// 데이터를 클라이언트로 보내기 위해 필요합니다
		ob_flush();
	}
	echo '}';

// 스트리밍을 시작하기 전에 헤더를 설정하는 방법입니다
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	// 선택적 상태 코드, 기본값은 200
	'status' => 200
]);
```