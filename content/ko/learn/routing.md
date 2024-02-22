# 라우팅

> **참고:** 라우팅에 대해 더 알고 싶으신가요? 좀 더 깊이 있는 설명이 있는 ["왜 프레임워크?"](/learn/why-frameworks) 페이지를 확인해보세요.

Flight에서의 기본 라우팅은 URL 패턴을 콜백 함수나 클래스 및 메소드의 배열과 일치시킴으로써 이루어집니다.

```php
Flight::route('/', function(){
    echo '안녕, 세상!';
});
```

콜백은 호출 가능한 모든 객체가 될 수 있습니다. 그래서 일반 함수를 사용할 수 있습니다:

```php
function hello(){
    echo '안녕, 세상!';
}

Flight::route('/', 'hello');
```

또는 클래스 메소드:

```php
class Greeting {
    public static function hello() {
        echo '안녕, 세상!';
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
        echo "안녕하세요, {$this->name}님!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

라우트는 정의된 순서대로 일치합니다. 요청을 일치시키는 첫 번째 라우트가 호출됩니다.

## 메소드 라우팅

기본적으로 라우트 패턴은 모든 요청 메소드에 대해 일치됩니다. 특정 메소드에 응답하려면 URL 앞에 식별자를 놓습니다.

```php
Flight::route('GET /', function () {
  echo 'GET 요청을 받았습니다.';
});

Flight::route('POST /', function () {
  echo 'POST 요청을 받았습니다.';
});
```

`|` 분리 기호를 사용하여 하나의 콜백에 여러 메소드를 매핑할 수도 있습니다:

```php
Flight::route('GET|POST /', function () {
  echo 'GET 또는 POST 요청을 받았습니다.';
});
```

또한 사용할 수 있는 일부 도우미 메소드가 있는 Router 객체를 얻을 수도 있습니다:

```php

$router = Flight::router();

// 모든 메소드 매핑
$router->map('/', function() {
	echo '안녕, 세상!';
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

이 방법은 사용할 수 있지만 named parameter 또는 named parameter와 정규 표현식을 사용하는 것이 더 가독성이 좋고 유지보수가 쉽습니다.

## Named Parameters

콜백 함수로 전달될 명명된 매개 변수를 지정할 수 있습니다.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "안녕하세요, $name ($id)님!";
});
```

`:` 분리 기호를 사용하여 named parameter와 정규 표현식을 함께 사용할 수도 있습니다:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // 이는 /bob/123와 일치합니다
  // 하지만 /bob/12345와는 일치하지 않습니다
});
```

> **참고:** named parameter와 일치하는 정규식 그룹 `()`은 지원되지 않습니다. :'\(

## 선택적 매개 변수

일치 필요성이 선택적인 named parameter를 지정할 수 있습니다. 세그먼트를 괄호로 묶어 선택적으로 할 수 있습니다.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 다음과 같은 URL 일치:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

일치하지 않는 선택적 매개 변수는 `NULL`로 전달됩니다.

## 와일드카드

일치는 개별 URL 세그먼트에서만 이루어집니다. 여러 세그먼트를 일치시키려면 `*` 와일드카드를 사용할 수 있습니다.

```php
Flight::route('/blog/*', function () {
  // 이는 /blog/2000/02/01과 일치합니다.
});
```

모든 요청을 단일 콜백으로 라우팅하려면 다음과 같이 할 수 있습니다:

```php
Flight::route('*', function () {
  // 뭔가 작업하기
});
```

## 전달

콜백 함수에서 `true`를 반환함으로써 다음 일치하는 라우트로 실행을 전달할 수 있습니다.

```php
Flight::route('/user/@name', function (string $name) {
  // 어떤 조건을 확인
  if ($name !== "Bob") {
    // 다음 라우트로 이동
    return true;
  }
});

Flight::route('/user/*', function () {
  // 이 부분이 호출됩니다
});
```

## 라우트 별칭

라우트에 별칭을 지정하여 URL을 나중에 동적으로 생성할 수 있습니다 (예: 템플릿).

```php
Flight::route('/users/@id', function($id) { echo '사용자:'.$id; }, false, 'user_view');

// 나중에 코드의 어딘가에서
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환합니다
```

URL이 변경된다면 특히 유용합니다. 위의 예제에서 사용자가 `/admin/users/@id`로 이동했다고 가정해봅시다.
별칭을 사용하면 참조하는 모든 위치를 변경할 필요가 없으므로, 이제 별칭이 `/admin/users/5`로 반환됩니다. 

그룹에서도 작동합니다:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo '사용자:'.$id; }, false, 'user_view');
});


// 나중에 코드의 어딘가에서
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환합니다
```

## 라우트 정보

일치하는 라우트 정보를 검토하려면 라우트 메소드에 세 번째 매개 변수로 `true`를 전달하여 콜백에 라우트 객체를 요청할 수 있습니다. 라우트 객체는 항상 콜백 함수로 전달되는 마지막 매개 변수입니다.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 일치하는 HTTP 메소드 배열
  $route->methods;

  // 명명된 매개 변수 배열
  $route->params;

  // 일치하는 정규식
  $route->regex;

  // URL 패턴에 사용된 '*'의 내용
  $route->splat;

  // URL 경로 표시... 필요한 경우
  $route->pattern;

  // 이에 할당된 미들웨어 표시
  $route->middleware;

  // 이 라우트에 할당된 별칭 표시
  $route->alias;
}, true);
```

## 라우트 그룹화

`/api/v1`과 같이 관련된 라우트를 그룹화할 필요가 있는 경우가 있습니다. 이 작업은 `group` 메소드를 사용하여 수행할 수 있습니다:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// /api/v1/users에 일치
  });

  Flight::route('/posts', function () {
	// /api/v1/posts에 일치
  });
});
```

그룹을 또 다른 그룹 내에 중첩할 수도 있습니다:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get()는 변수를 가져와요, 라우트를 설정하지 않아요! 아래의 객체 컨텍스트를 확인하세요
	Flight::route('GET /users', function () {
	  // GET /api/v1/users에 일치
	});

	Flight::post('/posts', function () {
	  // POST /api/v1/posts에 일치
	});

	Flight::put('/posts/1', function () {
	  // PUT /api/v1/posts에 일치
	});
  });
  Flight::group('/v2', function () {

	// Flight::get()는 변수를 가져와요, 라우트를 설정하지 않아요! 아래의 객체 컨텍스트를 확인하세요
	Flight::route('GET /users', function () {
	  // GET /api/v2/users에 일치
	});
  });
});
```

### 객체 컨텍스트와 그룹

다음과 같이 `Engine` 객체에서 라우트 그룹을 사용할 수 있습니다:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // $router 변수 사용
  $router->get('/users', function () {
	// GET /api/v1/users에 일치
  });

  $router->post('/posts', function () {
	// POST /api/v1/posts에 일치
  });
});
```

## 스트리밍

`streamWithHeaders()` 메소드를 사용하여 클라이언트에게 응답을 스트리밍할 수 있습니다.
큰 파일을 보내거나 긴 실행 프로세스를 실행하거나 대량의 응답을 생성하는 데 유용합니다.
라우트를 스트리밍하는 것은 일반적인 라우트보다 약간 다르게 처리됩니다.

> **참고:** [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering)가 false로 설정된 경우에만 스트리밍 응답을 사용할 수 있습니다.

```php
Flight::route('/stream-users', function() {

	// 내용을 어떻게 가져오든지, 예시일 뿐입니다...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// 데이터를 클라이언트에 보내려면 필요합니다
		ob_flush();
	}
	echo '}';

// 데이터를 스트리밍하기 전에 헤더를 설정하는 방법
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	// 선택적 상태 코드, 기본값은 200
	'status' => 200
]);
```