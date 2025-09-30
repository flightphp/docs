# 라우팅

## 개요
Flight PHP의 라우팅은 URL 패턴을 콜백 함수나 클래스 메서드에 매핑하여 빠르고 간단한 요청 처리를 가능하게 합니다. 이는 최소한의 오버헤드, 초보자 친화적인 사용법, 그리고 외부 종속성 없이 확장성을 위해 설계되었습니다.

## 이해하기
라우팅은 Flight에서 HTTP 요청을 애플리케이션 로직에 연결하는 핵심 메커니즘입니다. 라우트를 정의함으로써 다양한 URL이 함수, 클래스 메서드, 또는 컨트롤러 액션을 통해 특정 코드를 트리거하는 방식을 지정합니다. Flight의 라우팅 시스템은 유연하며, 기본 패턴, 명명된 매개변수, 정규 표현식, 그리고 의존성 주입 및 리소스 라우팅과 같은 고급 기능을 지원합니다. 이 접근 방식은 코드를 체계적으로 유지하고 유지보수를 쉽게 하며, 초보자에게는 빠르고 간단하며 고급 사용자에게는 확장 가능합니다.

> **참고:** 라우팅에 대해 더 이해하고 싶으신가요? ["왜 프레임워크인가?](/learn/why-frameworks)" 페이지를 확인하여 더 자세한 설명을 보세요.

## 기본 사용법

### 간단한 라우트 정의
Flight의 기본 라우팅은 URL 패턴을 콜백 함수나 클래스와 메서드의 배열로 매칭하여 수행됩니다.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> 라우트는 정의된 순서대로 매칭됩니다. 요청과 일치하는 첫 번째 라우트가 호출됩니다.

### 콜백으로 함수 사용
콜백은 호출 가능한 모든 객체가 될 수 있습니다. 따라서 일반 함수를 사용할 수 있습니다:

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### 컨트롤러로 클래스와 메서드 사용
클래스의 메서드(정적 또는 비정적)를 사용할 수도 있습니다:

```php
class GreetingController {
    public function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', [ 'GreetingController','hello' ]);
// 또는
Flight::route('/', [ GreetingController::class, 'hello' ]); // 권장 방법
// 또는
Flight::route('/', [ 'GreetingController::hello' ]);
// 또는 
Flight::route('/', [ 'GreetingController->hello' ]);
```

또는 먼저 객체를 생성한 후 메서드를 호출할 수 있습니다:

```php
use flight\Engine;

// GreetingController.php
class GreetingController
{
	protected Engine $app
    public function __construct(Engine $app) {
		$this->app = $app;
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hello, {$this->name}!";
    }
}

// index.php
$app = Flight::app();
$greeting = new GreetingController($app);

Flight::route('/', [ $greeting, 'hello' ]);
```

> **참고:** 프레임워크 내에서 컨트롤러가 호출될 때 기본적으로 `flight\Engine` 클래스가 항상 주입됩니다. [의존성 주입 컨테이너](/learn/dependency-injection-container)를 통해 지정하지 않는 한.

### 메서드별 라우팅

기본적으로 라우트 패턴은 모든 요청 메서드에 대해 매칭됩니다. URL 앞에 식별자를 배치하여 특정 메서드에 응답할 수 있습니다.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// Flight::get()은 라우트를 생성하는 메서드가 아니라 변수를 가져오는 메서드이므로 라우트에 사용할 수 없습니다.
Flight::post('/', function() { /* code */ });
Flight::patch('/', function() { /* code */ });
Flight::put('/', function() { /* code */ });
Flight::delete('/', function() { /* code */ });
```

단일 콜백에 여러 메서드를 매핑하려면 `|` 구분 기호를 사용할 수 있습니다:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

### 라우터 객체 사용

또한 라우터 객체를 가져와서 헬퍼 메서드를 사용할 수 있습니다:

```php

$router = Flight::router();

// Flight::route()처럼 모든 메서드를 매핑합니다.
$router->map('/', function() {
	echo 'hello world!';
});

// GET 요청
$router->get('/users', function() {
	echo 'users';
});
$router->post('/users', 			function() { /* code */});
$router->put('/users/update/@id', 	function() { /* code */});
$router->delete('/users/@id', 		function() { /* code */});
$router->patch('/users/@id', 		function() { /* code */});
```

### 정규 표현식 (Regex)
라우트에서 정규 표현식을 사용할 수 있습니다:

```php
Flight::route('/user/[0-9]+', function () {
  // 이는 /user/1234와 일치합니다.
});
```

이 방법이 사용 가능하지만, 명명된 매개변수 또는 정규 표현식을 포함한 명명된 매개변수를 사용하는 것이 더 읽기 쉽고 유지보수가 쉽기 때문에 권장됩니다.

### 명명된 매개변수
라우트에서 명명된 매개변수를 지정할 수 있으며, 이는 콜백 함수로 전달됩니다. **이는 라우트의 가독성을 위한 것이지 다른 것이 아닙니다. 아래의 중요한 주의사항 섹션을 참조하세요.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

`:` 구분 기호를 사용하여 명명된 매개변수에 정규 표현식을 포함할 수도 있습니다:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // 이는 /bob/123과 일치합니다.
  // 하지만 /bob/12345와는 일치하지 않습니다.
});
```

> **참고:** 위치 매개변수와 함께 regex 그룹 `()` 매칭은 지원되지 않습니다. 예: `:'\(`

#### 중요한 주의사항

위 예에서 `@name`이 변수 `$name`에 직접 연결된 것처럼 보이지만, 그렇지 않습니다. 콜백 함수의 매개변수 순서가 전달되는 것을 결정합니다. 콜백 함수의 매개변수 순서를 변경하면 변수도 변경됩니다. 다음은 예입니다:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

다음 URL로 이동하면: `/bob/123`, 출력은 `hello, 123 (bob)!`가 됩니다. 
라우트와 콜백 함수를 설정할 때 **주의하세요**!

### 선택적 매개변수
세그먼트를 괄호로 감싸서 매칭에 선택적인 명명된 매개변수를 지정할 수 있습니다.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 이는 다음 URL과 일치합니다:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

매칭되지 않은 선택적 매개변수는 `NULL`로 전달됩니다.

### 와일드카드 라우팅
매칭은 개별 URL 세그먼트에만 수행됩니다. 여러 세그먼트를 매칭하려면 `*` 와일드카드를 사용할 수 있습니다.

```php
Flight::route('/blog/*', function () {
  // 이는 /blog/2000/02/01과 일치합니다.
});
```

모든 요청을 단일 콜백으로 라우팅하려면 다음을 할 수 있습니다:

```php
Flight::route('*', function () {
  // 무언가 수행
});
```

### 404 Not Found 핸들러

기본적으로 URL을 찾을 수 없으면 Flight는 매우 간단하고 평범한 `HTTP 404 Not Found` 응답을 보냅니다.
더 사용자 지정된 404 응답을 원한다면 [map](/learn/extending)으로 자신의 `notFound` 메서드를 매핑할 수 있습니다:

```php
Flight::map('notFound', function() {
	$url = Flight::request()->url;

	// 사용자 지정 템플릿과 함께 Flight::render()를 사용할 수도 있습니다.
    $output = <<<HTML
		<h1>My Custom 404 Not Found</h1>
		<h3>The page you have requested {$url} could not be found.</h3>
		HTML;

	$this->response()
		->clearBody()
		->status(404)
		->write($output)
		->send();
});
```

## 고급 사용법

### 라우트의 의존성 주입
컨테이너(PSR-11, PHP-DI, Dice 등)를 통해 의존성 주입을 사용하려면, 직접 객체를 생성하고 컨테이너를 사용하여 객체를 생성하거나 문자열로 클래스와 메서드를 정의하여 호출하는 라우트 유형만 사용할 수 있습니다. 더 많은 정보는 [의존성 주입](/learn/dependency-injection-container) 페이지를 참조하세요. 

다음은 간단한 예입니다:

```php

use flight\database\PdoWrapper;

// Greeting.php
class Greeting
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;
	}

	public function hello(int $id) {
		// $this->pdoWrapper로 무언가 수행
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";
	}
}

// index.php

// 필요한 매개변수로 컨테이너 설정
// PSR-11에 대한 자세한 정보는 의존성 주입 페이지를 참조하세요.
$dice = new \Dice\Dice();

// '$dice = '로 변수를 재할당하는 것을 잊지 마세요!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// 컨테이너 핸들러 등록
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// 일반적으로 라우트
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// 또는
Flight::route('/hello/@id', 'Greeting->hello');
// 또는
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

### 다음 라우트로 실행 전달
<span class="badge bg-warning">사용 중단됨</span>
콜백 함수에서 `true`를 반환하여 다음 매칭 라우트로 실행을 전달할 수 있습니다.

```php
Flight::route('/user/@name', function (string $name) {
  // 조건 확인
  if ($name !== "Bob") {
    // 다음 라우트로 계속
    return true;
  }
});

Flight::route('/user/*', function () {
  // 이는 호출됩니다.
});
```

이제 [미들웨어](/learn/middleware)를 사용하여 이와 같은 복잡한 사용 사례를 처리하는 것이 권장됩니다.

### 라우트 별칭
라우트에 별칭을 할당하면, 나중에 앱에서 동적으로 해당 별칭을 호출하여 코드에서 나중에 생성할 수 있습니다(예: HTML 템플릿의 링크 또는 리디렉션 URL 생성).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
// 또는 
Flight::route('/users/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');

// 코드 어딘가에서 나중에
class UserController {
	public function update() {

		// 사용자 저장 코드...
		$id = $user['id']; // 예: 5

		$redirectUrl = Flight::getUrl('user_view', [ 'id' => $id ]); // '/users/5' 반환
		Flight::redirect($redirectUrl);
	}
}

```

URL이 변경될 경우 특히 유용합니다. 위 예에서 사용자가 `/admin/users/@id`로 이동했다고 가정해 보세요.
라우트에 별칭이 있으면 코드에서 모든 이전 URL을 찾고 변경할 필요가 없으며, 별칭은 이제 `/admin/users/5`를 반환합니다.

그룹에서도 라우트 별칭이 작동합니다:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
	// 또는
	Flight::route('/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');
});
```

### 라우트 정보 검사
매칭된 라우트 정보를 검사하려면 2가지 방법이 있습니다:

1. `Flight::router()` 객체의 `executedRoute` 속성을 사용할 수 있습니다.
2. 라우트 메서드의 세 번째 매개변수로 `true`를 전달하여 콜백으로 라우트 객체를 전달받을 수 있습니다. 라우트 객체는 항상 콜백 함수로 전달되는 마지막 매개변수가 됩니다.

#### `executedRoute`
```php
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;
  // $route로 무언가 수행
  // 매칭된 HTTP 메서드 배열
  $route->methods;

  // 명명된 매개변수 배열
  $route->params;

  // 매칭 정규 표현식
  $route->regex;

  // URL 패턴에서 사용된 '*'의 내용을 포함
  $route->splat;

  // URL 경로를 보여줍니다....정말 필요하다면
  $route->pattern;

  // 이 라우트에 할당된 미들웨어를 보여줍니다
  $route->middleware;

  // 이 라우트에 할당된 별칭을 보여줍니다
  $route->alias;
});
```

> **참고:** `executedRoute` 속성은 라우트가 실행된 후에만 설정됩니다. 라우트가 실행되기 전에 접근하려 하면 `NULL`입니다. [미들웨어](/learn/middleware)에서도 executedRoute를 사용할 수 있습니다!

#### 라우트 정의에 `true` 전달
```php
Flight::route('/', function(\flight\net\Route $route) {
  // 매칭된 HTTP 메서드 배열
  $route->methods;

  // 명명된 매개변수 배열
  $route->params;

  // 매칭 정규 표현식
  $route->regex;

  // URL 패턴에서 사용된 '*'의 내용을 포함
  $route->splat;

  // URL 경로를 보여줍니다....정말 필요하다면
  $route->pattern;

  // 이 라우트에 할당된 미들웨어를 보여줍니다
  $route->middleware;

  // 이 라우트에 할당된 별칭을 보여줍니다
  $route->alias;
}, true);// <-- 이 true 매개변수가 그 일을 합니다.
```

### 라우트 그룹화 및 미들웨어
관련 라우트를 함께 그룹화해야 할 때가 있을 수 있습니다(예: `/api/v1`).
`group` 메서드를 사용하여 이를 수행할 수 있습니다:

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

그룹의 그룹을 중첩할 수도 있습니다:

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

#### 객체 컨텍스트와 그룹화

다음 방식으로 `Engine` 객체와 함께 라우트 그룹화를 여전히 사용할 수 있습니다:

```php
$app = Flight::app();

$app->group('/api/v1', function (Router $router) {

  // $router 변수를 사용
  $router->get('/users', function () {
	// GET /api/v1/users와 일치
  });

  $router->post('/posts', function () {
	// POST /api/v1/posts와 일치
  });
});
```

> **참고:** `$router` 객체를 사용한 라우트 및 그룹 정의의 권장 방법입니다.

#### 미들웨어와 그룹화

라우트 그룹에 미들웨어를 할당할 수도 있습니다:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// /api/v1/users와 일치
  });
}, [ MyAuthMiddleware::class ]); // 인스턴스를 사용하려면 [ new MyAuthMiddleware() ]
```

[그룹 미들웨어](/learn/middleware#grouping-middleware) 페이지에서 더 자세한 내용을 확인하세요.

### 리소스 라우팅
`resource` 메서드를 사용하여 리소스에 대한 라우트 세트를 생성할 수 있습니다. 이는 RESTful 규칙을 따르는 리소스에 대한 라우트 세트를 생성합니다.

리소스를 생성하려면 다음을 수행하세요:

```php
Flight::resource('/users', UsersController::class);
```

배경에서 다음 라우트가 생성됩니다:

```php
[
      'index' => 'GET /users',
      'create' => 'GET /users/create',
      'store' => 'POST /users',
      'show' => 'GET /users/@id',
      'edit' => 'GET /users/@id/edit',
      'update' => 'PUT /users/@id',
      'destroy' => 'DELETE /users/@id'
]
```

컨트롤러는 다음 메서드를 사용합니다:

```php
class UsersController
{
    public function index(): void
    {
    }

    public function show(string $id): void
    {
    }

    public function create(): void
    {
    }

    public function store(): void
    {
    }

    public function edit(string $id): void
    {
    }

    public function update(string $id): void
    {
    }

    public function destroy(string $id): void
    {
    }
}
```

> **참고**: 새로 추가된 라우트를 확인하려면 `php runway routes`를 실행하세요.

#### 리소스 라우트 사용자 지정

리소스 라우트를 구성할 몇 가지 옵션이 있습니다.

##### 별칭 베이스

`aliasBase`를 구성할 수 있습니다. 기본적으로 별칭은 지정된 URL의 마지막 부분입니다.
예를 들어 `/users/`는 `aliasBase`가 `users`가 됩니다. 이러한 라우트가 생성될 때 별칭은 `users.index`, `users.create` 등이 됩니다. 별칭을 변경하려면 `aliasBase`를 원하는 값으로 설정하세요.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

##### Only 및 Except

`only` 및 `except` 옵션을 사용하여 생성할 라우트를 지정할 수도 있습니다.

```php
// 이 메서드만 화이트리스트하고 나머지는 블랙리스트
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
// 이 메서드만 블랙리스트하고 나머지는 화이트리스트
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

이것은 라우트를 생성할지 지정할 수 있는 화이트리스트 및 블랙리스트 옵션입니다.

##### 미들웨어

`resource` 메서드가 생성한 각 라우트에 실행될 미들웨어를 지정할 수도 있습니다.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

### 스트리밍 응답

이제 `stream()` 또는 `streamWithHeaders()`를 사용하여 클라이언트에 응답을 스트리밍할 수 있습니다. 
이는 대용량 파일 전송, 장기 실행 프로세스, 또는 대용량 응답 생성에 유용합니다. 
라우트 스트리밍은 일반 라우트와 약간 다르게 처리됩니다.

> **참고:** 스트리밍 응답은 [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering)이 `false`로 설정된 경우에만 사용할 수 있습니다.

#### 수동 헤더와 스트림

라우트에서 `stream()` 메서드를 사용하여 클라이언트에 응답을 스트리밍할 수 있습니다. 
이 경우 클라이언트에 아무것도 출력하기 전에 모든 헤더를 수동으로 설정해야 합니다.
이는 `header()` PHP 함수나 `Flight::response()->setRealHeader()` 메서드로 수행됩니다.

```php
Flight::route('/@filename', function($filename) {

	$response = Flight::response();

	// 명백히 경로를 정화하고 그 밖의 것을 수행합니다.
	$fileNameSafe = basename($filename);

	// 라우트가 실행된 후 여기서 추가 헤더를 설정해야 한다면
	// 클라이언트에 에코되기 전에 정의해야 합니다.
	// 모두 header() 함수의 원시 호출이거나 
	// Flight::response()->setRealHeader() 호출이어야 합니다.
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// 또는
	$response->setRealHeader('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');

	$filePath = '/some/path/to/files/'.$fileNameSafe;

	if (!is_readable($filePath)) {
		Flight::halt(404, 'File not found');
	}

	// 원한다면 콘텐츠 길이를 수동으로 설정
	header('Content-Length: '.filesize($filePath));
	// 또는
	$response->setRealHeader('Content-Length: '.filesize($filePath));

	// 파일을 읽으면서 클라이언트에 스트리밍
	readfile($filePath);

// 여기서 마법의 줄입니다.
})->stream();
```

#### 헤더와 스트림

스트리밍을 시작하기 전에 헤더를 설정하려면 `streamWithHeaders()` 메서드를 사용할 수도 있습니다.

```php
Flight::route('/stream-users', function() {

	// 여기서 원하는 추가 헤더를 추가할 수 있습니다.
	// header() 또는 Flight::response()->setRealHeader()를 사용해야 합니다.

	// 데이터를 어떻게 가져오든, 예시로...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// 데이터를 클라이언트에 보내기 위해 필요합니다.
		ob_flush();
	}
	echo '}';

// 스트리밍을 시작하기 전에 헤더를 설정하는 방법입니다.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// 선택적 상태 코드, 기본값 200
	'status' => 200
]);
```

## 관련 자료
- [미들웨어](/learn/middleware) - 인증, 로깅 등에 라우트와 함께 미들웨어 사용.
- [의존성 주입](/learn/dependency-injection-container) - 라우트에서 객체 생성 및 관리를 단순화.
- [왜 프레임워크인가?](/learn/why-frameworks) - Flight와 같은 프레임워크 사용의 이점 이해.
- [확장](/learn/extending) - `notFound` 메서드를 포함한 Flight 확장 방법.
- [php.net: preg_match](https://www.php.net/manual/en/function.preg-match.php) - 정규 표현식 매칭을 위한 PHP 함수.

## 문제 해결
- 라우트 매개변수는 이름이 아닌 순서대로 매칭됩니다. 콜백 매개변수 순서가 라우트 정의와 일치하는지 확인하세요.
- `Flight::get()`은 라우트를 정의하지 않습니다; 라우팅에는 `Flight::route('GET /...')` 또는 그룹의 Router 객체 컨텍스트(예: `$router->get(...)`)를 사용하세요.
- executedRoute 속성은 라우트 실행 후에만 설정됩니다; 실행 전에 NULL입니다.
- 스트리밍은 레거시 Flight 출력 버퍼링 기능이 비활성화되어야 합니다(`flight.v2.output_buffering = false`).
- 의존성 주입의 경우, 특정 라우트 정의만 컨테이너 기반 인스턴스화를 지원합니다.

### 404 Not Found 또는 예상치 못한 라우트 동작

404 Not Found 오류를 보고 있지만(인생을 걸고 정말 거기 있고 오타가 아니라고 맹세하더라도) 이는 라우트 엔드포인트에서 값을 반환하는 대신 에코하는 문제일 수 있습니다. 이유는 의도적ですが 일부 개발자에게 숨어들 수 있습니다.

```php

Flight::route('/hello', function(){
	// 이는 404 Not Found 오류를 일으킬 수 있습니다.
	return 'Hello World';
});

// 아마 원하는 것
Flight::route('/hello', function(){
	echo 'Hello World';
});

```

이유는 라우터에 내장된 특수 메커니즘 때문으로, 반환 출력을 "다음 라우트로 이동" 신호로 처리합니다. 
동작은 [라우팅](/learn/routing#passing) 섹션에 문서화되어 있습니다.

## 변경 로그
- v3: 리소스 라우팅, 라우트 별칭, 스트리밍 지원, 라우트 그룹, 미들웨어 지원 추가.
- v1: 기본 기능의 대부분 사용 가능.