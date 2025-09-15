# 라우팅

> **참고:** 라우팅에 대해 더 이해하고 싶으신가요? ["why a framework?"](/learn/why-frameworks) 페이지를 확인하세요. 더 자세한 설명이 있습니다.

Flight의 기본 라우팅은 URL 패턴을 콜백 함수나 클래스와 메서드의 배열과 일치시키는 방식으로 수행됩니다.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> 경로는 정의된 순서대로 일치됩니다. 요청과 일치하는 첫 번째 경로가 호출됩니다.

### 콜백/함수
콜백은 호출 가능한 모든 객체일 수 있습니다. 그래서 일반 함수를 사용할 수 있습니다:

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### 클래스
클래스의 정적 메서드를 사용할 수도 있습니다:

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

객체를 먼저 생성한 다음 메서드를 호출하는 방식으로도 할 수 있습니다:

```php
// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hello, {$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// 객체를 먼저 생성하지 않고도 할 수 있습니다
// 참고: 생성자에 인수가 주입되지 않습니다
Flight::route('/', [ 'Greeting', 'hello' ]);
// 추가로 이 짧은 구문을 사용할 수 있습니다
Flight::route('/', 'Greeting->hello');
// 또는
Flight::route('/', Greeting::class.'->hello');
```

#### DIC(의존성 주입 컨테이너)를 통한 의존성 주입
컨테이너를 통해 의존성 주입을 사용하려면 (PSR-11, PHP-DI, Dice 등), 사용 가능한 경로는 객체를 직접 생성하거나 클래스와 메서드를 문자열로 정의하는 것입니다. 자세한 정보는 [Dependency Injection](/learn/extending) 페이지를 참조하세요.

간단한 예제:

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
		// $this->pdoWrapper를 사용하여 무언가를 수행합니다
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";
	}
}

// index.php

// 필요한 파라미터로 컨테이너를 설정합니다
// PSR-11에 대한 자세한 정보는 의존성 주입 페이지를 참조하세요
$dice = new \Dice\Dice();

// 변수를 재할당하는 것을 잊지 마세요 '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// 컨테이너 핸들러를 등록합니다
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// 일반적으로 경로를 정의합니다
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// 또는
Flight::route('/hello/@id', 'Greeting->hello');
// 또는
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## 메서드 라우팅

기본적으로 경로 패턴은 모든 요청 메서드에 대해 일치됩니다. URL 앞에 식별자를 추가하여 특정 메서드에 응답할 수 있습니다.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// Flight::get()은 변수를 가져오는 메서드이지 경로를 생성하는 것이 아닙니다
// Flight::post('/', function() { /* code */ });
// Flight::patch('/', function() { /* code */ });
// Flight::put('/', function() { /* code */ });
// Flight::delete('/', function() { /* code */ });
```

단일 콜백에 여러 메서드를 매핑하려면 `|` 구분자를 사용하세요:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

또한 Router 객체를 가져와서 몇 가지 도우미 메서드를 사용할 수 있습니다:

```php
$router = Flight::router();

// 모든 메서드에 매핑
$router->map('/', function() {
	echo 'hello world!';
});

// GET 요청
$router->get('/users', function() {
	echo 'users';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## 정규 표현식

경로에서 정규 표현식을 사용할 수 있습니다:

```php
Flight::route('/user/[0-9]+', function () {
  // 이는 /user/1234와 일치합니다
});
```

이 방법은 사용 가능하지만, 이름 붙은 파라미터나 이름 붙은 파라미터와 함께 정규 표현식을 사용하는 것이 더 읽기 쉽고 유지보수가 쉽습니다.

## 이름 붙인 파라미터

경로에서 이름 붙인 파라미터를 지정하면 콜백 함수에 전달됩니다. **이는 경로의 가독성을 위한 것입니다. 아래의 중요한 주의 사항을 참조하세요.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

이름 붙인 파라미터에 정규 표현식을 포함하려면 `:` 구분자를 사용하세요:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // 이는 /bob/123과 일치합니다
  // 하지만 /bob/12345와는 일치하지 않습니다
});
```

> **참고:** 위치 파라미터와 함께 정규 표현식 그룹 `()`을 일치시키는 것은 지원되지 않습니다. :'\(

### 중요한 주의 사항

위 예제에서 `@name`이 변수 `$name`에 직접 연결된 것처럼 보이지만, 실제로는 아닙니다. 콜백 함수의 파라미터 순서가 전달되는 것을 결정합니다. 그래서 콜백 함수의 파라미터 순서를 바꾸면 변수도 바뀝니다. 예제:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

다음 URL에 접근하면: `/bob/123`, 출력은 `hello, 123 (bob)!`가 됩니다. 경로와 콜백 함수를 설정할 때 주의하세요.

## 선택적 파라미터

세그먼트를 괄호로 감싸서 선택적 이름 붙인 파라미터를 지정할 수 있습니다.

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

일치되지 않은 선택적 파라미터는 `NULL`로 전달됩니다.

## 와일드카드

일치는 개별 URL 세그먼트에만 적용됩니다. 여러 세그먼트를 일치시키려면 `*` 와일드카드를 사용하세요.

```php
Flight::route('/blog/*', function () {
  // 이는 /blog/2000/02/01과 일치합니다
});
```

모든 요청을 단일 콜백으로 라우팅하려면:

```php
Flight::route('*', function () {
  // 무언가를 수행합니다
});
```

## 전달

콜백 함수에서 `true`를 반환하여 다음 일치하는 경로로 실행을 전달할 수 있습니다.

```php
Flight::route('/user/@name', function (string $name) {
  // 어떤 조건을 확인합니다
  if ($name !== "Bob") {
    // 다음 경로로 계속합니다
    return true;
  }
});

Flight::route('/user/*', function () {
  // 이는 호출됩니다
});
```

## 경로 별칭

경로에 별칭을 할당하면 코드에서(예: 템플릿처럼) URL을 동적으로 생성할 수 있습니다.

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// 코드 어딘가에서 나중에
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환합니다
```

URL이 변경된 경우에 특히 유용합니다. 위 예제에서 users가 `/admin/users/@id`로 이동했다고 가정하면, 별칭을 사용하면 별칭이 이제 `/admin/users/5`를 반환하므로 별칭을 참조하는 곳을 변경할 필요가 없습니다.

그룹에서도 경로 별칭이 작동합니다:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// 코드 어딘가에서 나중에
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환합니다
```

## 경로 정보

일치하는 경로 정보를 검사하려면 2가지 방법이 있습니다. `executedRoute` 속성을 사용하거나 경로 메서드의 세 번째 파라미터에 `true`를 전달하여 경로 객체를 콜백에 전달할 수 있습니다. 경로 객체는 콜백 함수에 전달되는 마지막 파라미터입니다.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 일치된 HTTP 메서드 배열
  $route->methods;

  // 이름 붙인 파라미터 배열
  $route->params;

  // 일치 정규 표현식
  $route->regex;

  // URL 패턴의 '*' 내용 포함
  $route->splat;

  // URL 경로를 보여줍니다....정말 필요하다면
  $route->pattern;

  // 이 경로에 할당된 미들웨어를 보여줍니다
  $route->middleware;

  // 이 경로에 할당된 별칭을 보여줍니다
  $route->alias;
}, true);
```

또는 실행된 마지막 경로를 검사하려면:

```php
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;
  // $route로 무언가를 수행합니다
  // 일치된 HTTP 메서드 배열
  $route->methods;

  // 이름 붙인 파라미터 배열
  $route->params;

  // 일치 정규 표현식
  $route->regex;

  // URL 패턴의 '*' 내용 포함
  $route->splat;

  // URL 경로를 보여줍니다....정말 필요하다면
  $route->pattern;

  // 이 경로에 할당된 미들웨어를 보여줍니다
  $route->middleware;

  // 이 경로에 할당된 별칭을 보여줍니다
  $route->alias;
});
```

> **참고:** `executedRoute` 속성은 경로가 실행된 후에만 설정됩니다. 경로 실행 전에 접근하면 `NULL`이 됩니다. 미들웨어에서도 executedRoute를 사용할 수 있습니다!

## 경로 그룹화

관련된 경로를 그룹으로 묶고 싶을 때가 있습니다 (예: `/api/v1`). `group` 메서드를 사용하여 할 수 있습니다:

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

그룹을 중첩할 수도 있습니다:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get()은 변수를 가져오는 것이지 경로를 설정하는 것이 아닙니다! 아래 객체 컨텍스트를 참조하세요
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

	// Flight::get()은 변수를 가져오는 것이지 경로를 설정하는 것이 아닙니다! 아래 객체 컨텍스트를 참조하세요
	Flight::route('GET /users', function () {
	  // GET /api/v2/users와 일치합니다
	});
  });
});
```

### 객체 컨텍스트와의 그룹화

`Engine` 객체와 함께 경로 그룹화를 다음과 같이 사용할 수 있습니다:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // $router 변수를 사용하세요
  $router->get('/users', function () {
	// GET /api/v1/users와 일치합니다
  });

  $router->post('/posts', function () {
	// POST /api/v1/posts와 일치합니다
  });
});
```

### 미들웨어와의 그룹화

경로 그룹에 미들웨어를 할당할 수도 있습니다:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// /api/v1/users와 일치합니다
  });
}, [ MyAuthMiddleware::class ]); // 또는 [ new MyAuthMiddleware() ]를 사용하려면 인스턴스를 사용하세요
```

자세한 내용은 [group middleware](/learn/middleware#grouping-middleware) 페이지를 참조하세요.

## 리소스 라우팅

`resource` 메서드를 사용하여 RESTful 규칙을 따르는 리소스에 대한 경로 세트를 생성할 수 있습니다.

리소스를 생성하려면:

```php
Flight::resource('/users', UsersController::class);
```

배경에서 다음 경로가 생성됩니다:

```php
[
      'index' => 'GET ',
      'create' => 'GET /create',
      'store' => 'POST ',
      'show' => 'GET /@id',
      'edit' => 'GET /@id/edit',
      'update' => 'PUT /@id',
      'destroy' => 'DELETE /@id'
]
```

컨트롤러는 다음과 같습니다:

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

> **참고**: 새로 추가된 경로를 `runway`로 확인하려면 `php runway routes`를 실행하세요.

### 리소스 경로 사용자 정의

리소스 경로를 구성할 수 있는 몇 가지 옵션이 있습니다.

#### 별칭 기본값

`aliasBase`를 구성할 수 있습니다. 기본적으로 별칭은 지정된 URL의 마지막 부분입니다. 예를 들어 `/users/`는 `aliasBase`가 `users`가 됩니다. 이러한 경로가 생성되면 별칭은 `users.index`, `users.create` 등이 됩니다. 별칭을 변경하려면 `aliasBase`를 원하는 값으로 설정하세요.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Only and Except

`only`와 `except` 옵션을 사용하여 생성할 경로를 지정할 수 있습니다.

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

이는 화이트리스트와 블랙리스트 옵션으로, 생성할 경로를 지정할 수 있습니다.

#### 미들웨어

`resource` 메서드가 생성하는 각 경로에 미들웨어를 지정할 수 있습니다.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## 스트리밍

`streamWithHeaders()` 메서드를 사용하여 클라이언트에 응답을 스트리밍할 수 있습니다. 이는 대용량 파일, 장기 실행 프로세스 또는 대용량 응답을 보내는 데 유용합니다. 스트리밍 경로는 일반 경로와 다르게 처리됩니다.

> **참고:** 스트리밍 응답은 [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering)가 false로 설정된 경우에만 사용 가능합니다.

### 수동 헤더와 스트리밍

경로의 `stream()` 메서드를 사용하여 클라이언트에 응답을 스트리밍할 수 있습니다. 이 경우 출력 전에 모든 헤더를 수동으로 설정해야 합니다. 이는 `header()` PHP 함수나 `Flight::response()->setRealHeader()` 메서드로 수행됩니다.

```php
Flight::route('/@filename', function($filename) {

	// 분명히 경로를 정리하고 등등을 해야 합니다.
	$fileNameSafe = basename($filename);

	// 경로가 실행된 후 추가 헤더를 설정하려면 출력 전에 정의해야 합니다.
	// 모두 header() 함수의 원시 호출이거나 Flight::response()->setRealHeader() 호출이어야 합니다.
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// 또는
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$filePath = '/some/path/to/files/'.$fileNameSafe;

	if (!is_readable($filePath)) {
		Flight::halt(404, 'File not found');
	}

	// 원하는 경우 콘텐츠 길이를 수동으로 설정합니다
	header('Content-Length: '.filesize($filePath));

	// 파일을 읽으면서 클라이언트에 스트리밍합니다
	readfile($filePath);

// 이게 마법의 줄입니다
})->stream();
```

### 헤더와 스트리밍

스트리밍을 시작하기 전에 헤더를 설정하려면 `streamWithHeaders()` 메서드를 사용하세요.

```php
Flight::route('/stream-users', function() {

	// 추가 헤더를 추가하려면 header() 또는 Flight::response()->setRealHeader()를 사용하세요

	// 데이터를 어떻게 가져오는지, 예를 들어...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// 데이터를 클라이언트에 보내기 위해 필요합니다
		ob_flush();
	}
	echo '}';

// 스트리밍을 시작하기 전에 헤더를 설정합니다.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// 선택적 상태 코드, 기본값은 200
	'status' => 200
]);
```