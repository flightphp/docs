# 라우팅

> **참고:** 라우팅에 대해 더 알아보고 싶으신가요? 더 심층적인 설명은 ["프레임워크가 필요한 이유?"](/learn/why-frameworks) 페이지를 확인하세요.

Flight의 기본 라우팅은 URL 패턴을 콜백 함수 또는 클래스와 메서드의 배열과 매치하여 이루어집니다.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> 경로는 정의된 순서대로 일치합니다. 요청과 일치하는 첫 번째 경로가 호출됩니다.

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

먼저 객체를 생성하고 메서드를 호출할 수도 있습니다:

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
// 참고: 생성자에는 매개변수가 주입되지 않습니다
Flight::route('/', [ 'Greeting', 'hello' ]);
// 또한 이 짧은 구문을 사용할 수 있습니다
Flight::route('/', 'Greeting->hello');
// 또는
Flight::route('/', Greeting::class.'->hello');
```

#### DIC(의존성 주입 컨테이너)를 통한 의존성 주입
컨테이너를 통한 의존성 주입(PSR-11, PHP-DI, Dice 등)을 사용하려면,
사용할 수 있는 경로의 유일한 유형은 객체를 직접 생성하고 컨테이너를 사용하여 객체를 생성하는 것 또는 
문자열을 사용하여 호출할 클래스와 메서드를 정의하는 것입니다. 
자세한 정보는 [의존성 주입](/learn/extending) 페이지를 참조하세요.

빠른 예시입니다:

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
		// $this->pdoWrapper로 작업을 수행합니다
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";
	}
}

// index.php

// 필요한 매개변수로 컨테이너를 설정합니다
// PSR-11에 대한 자세한 정보는 의존성 주입 페이지를 참조하세요
$dice = new \Dice\Dice();

// '$dice = '로 변수를 다시 할당하는 것을 잊지 마세요!!!!!
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

// 정상적으로 라우팅
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// 또는
Flight::route('/hello/@id', 'Greeting->hello');
// 또는
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## 메서드 라우팅

기본적으로, 라우트 패턴은 모든 요청 메서드에 대해 일치합니다. 특정 메서드에 응답하려면 URL 앞에 식별자를 배치해야 합니다.

```php
Flight::route('GET /', function () {
  echo 'GET 요청을 받았습니다.';
});

Flight::route('POST /', function () {
  echo 'POST 요청을 받았습니다.';
});

// Flight::get()을 경로에 사용할 수 없습니다. 이는 변수를 가져오는 메서드이지 라우트를 생성하는 것이 아닙니다.
// Flight::post('/', function() { /* 코드 */ });
// Flight::patch('/', function() { /* 코드 */ });
// Flight::put('/', function() { /* 코드 */ });
// Flight::delete('/', function() { /* 코드 */ });
```

여러 메서드를 단일 콜백에 매핑하려면 `|` 구분자를 사용할 수 있습니다:

```php
Flight::route('GET|POST /', function () {
  echo 'GET 또는 POST 요청을 받았습니다.';
});
```

또한 몇 가지 도우미 메서드가 있는 Router 객체를 가져올 수 있습니다:

```php

$router = Flight::router();

// 모든 메서드 매핑
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

라우트에 정규 표현식을 사용할 수 있습니다:

```php
Flight::route('/user/[0-9]+', function () {
  // /user/1234와 일치합니다
});
```

이 방법은 사용 가능하지만, 명명된 매개변수나 정규 표현식이 있는 명명된 매개변수를 사용하는 것이 더 읽기 쉽고 유지 관리하기 쉽습니다.

## 명명된 매개변수

라우트에서 명명된 매개변수를 지정할 수 있으며, 이는 콜백 함수에 전달됩니다. **이는 경로의 가독성을 위한 것입니다. 아래의 중요한 단락을 참조하세요.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "안녕, $name ($id)!";
});
```

명명된 매개변수와 정규 표현식을 함께 포함할 수 있으며, 그때는 `:` 구분자를 사용합니다:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // /bob/123와 일치합니다
  // 그러나 /bob/12345와는 일치하지 않습니다
});
```

> **참고:** 포지셔널 매개변수와 함께 정규 표현식 그룹 `()`는 지원되지 않습니다. :'\(

### 중요한 경고

위의 예제에서 `@name`이 변수 `$name`에 직접 연결된 것처럼 보일 수 있지만 그렇지 않습니다. 콜백 함수의 매개변수 순서가 전달되는 내용을 결정합니다. 따라서 콜백 함수의 매개변수 순서를 바꾸면 변수도 스위칭됩니다. 여기 예시가 있습니다:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "안녕, $name ($id)!";
});
```

그리고 만약 다음 URL로 이동한다면: `/bob/123`, 출력은 `안녕, 123 (bob)!`이 됩니다. 
라우트를 설정할 때와 콜백 함수를 설정할 때 주의하세요.

## 선택적 매개변수

세그먼트를 괄호로 감싸 선택적으로 매칭되는 명명된 매개변수를 지정할 수 있습니다.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 다음 URL과 일치합니다:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

일치하지 않는 선택적 매개변수는 `NULL`로 전달됩니다.

## 와일드카드

일치 비교는 개별 URL 세그먼트에 대해서만 수행됩니다. 여러 세그먼트를 일치시키려면 `*` 와일드카드를 사용할 수 있습니다.

```php
Flight::route('/blog/*', function () {
  // /blog/2000/02/01와 일치합니다
});
```

모든 요청을 단일 콜백으로 라우팅하려면 다음과 같이 할 수 있습니다:

```php
Flight::route('*', function () {
  // 작업 수행
});
```

## 전달

콜백 함수에서 `true`를 반환하여 다음 일치하는 경로로 실행을 전달할 수 있습니다.

```php
Flight::route('/user/@name', function (string $name) {
  // 어떤 조건을 확인합니다
  if ($name !== "Bob") {
    // 다음 경로로 계속
    return true;
  }
});

Flight::route('/user/*', function () {
  // 여기서 호출됩니다
});
```

## 경로 별칭

경로에 별칭을 지정하여 나중에 코드에서 URL을 동적으로 생성할 수 있습니다(예: 템플릿).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// 코드의 나중에
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환합니다
```

URL이 변경될 경우 특히 유용합니다. 위의 예제에서 사용자가 `/admin/users/@id`로 이동되었다고 가정해 봅시다.
별칭이 설정되어 있으면 별칭이 `/admin/users/5`를 반환하기 때문에 별칭을 참조하는 모든 곳에서 변경할 필요가 없습니다.

경로 별칭은 그룹에서 여전히 작동합니다:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});

// 코드의 나중에
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환합니다
```

## 경로 정보

일치하는 경로 정보를 검사하고 싶다면 경로 메서드의 세 번째 매개변수로 `true`를 전달하여 경로 객체를 콜백으로 전달하도록 요청할 수 있습니다. 경로 객체는 항상 콜백 함수에 전달된 마지막 매개변수가 됩니다.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 일치한 HTTP 메서드의 배열
  $route->methods;

  // 명명된 매개변수의 배열
  $route->params;

  // 일치하는 정규 표현식
  $route->regex;

  // URL 패턴에 사용된 '*'의 내용을 포함
  $route->splat;

  // URL 경로를 표시....필요하다면
  $route->pattern;

  // 이 경로에 할당된 미들웨어 표시
  $route->middleware;

  // 이 경로에 할당된 별칭 표시
  $route->alias;
}, true);
```

## 경로 그룹화

관련된 경로를 함께 그룹화하고 싶을 때가 있을 것입니다(예: `/api/v1`).
`group` 메서드를 사용하여 이를 수행할 수 있습니다:

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
	// Flight::get()은 변수를 가져옵니다. 경로를 설정하지 않습니다! 아래의 객체 컨텍스트를 참조하세요
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

	// Flight::get()은 변수를 가져옵니다. 경로를 설정하지 않습니다! 아래의 객체 컨텍스트를 참조하세요
	Flight::route('GET /users', function () {
	  // GET /api/v2/users와 일치합니다
	});
  });
});
```

### 객체 컨텍스트로 그룹화

`Engine` 객체와 함께 경로 그룹화를 다음과 같이 사용할 수 있습니다:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // $router 변수를 사용합니다
  $router->get('/users', function () {
	// GET /api/v1/users와 일치합니다
  });

  $router->post('/posts', function () {
	// POST /api/v1/posts와 일치합니다
  });
});
```

## 리소스 라우팅

`resource` 메서드를 사용하여 리소스에 대한 경로 세트를 생성할 수 있습니다. 이는 RESTful 규칙을 따르는 리소스에 대한 경로 세트를 생성합니다.

리소스를 생성하려면 다음을 수행하세요:

```php
Flight::resource('/users', UsersController::class);
```

그리고 백그라운드에서 다음 경로를 생성합니다:

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

그리고 당신의 컨트롤러는 다음과 같이 보일 것입니다:

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

> **참고**: `runway`를 실행하여 추가된 경로를 볼 수 있습니다 `php runway routes`.

### 리소스 경로 사용자 정의

리소스 경로를 구성할 수 있는 몇 가지 옵션이 있습니다.

#### 별칭 기본

`aliasBase`를 구성할 수 있습니다. 기본적으로 별칭은 지정된 URL의 마지막 부분입니다.
예를 들어 `/users/`는 `aliasBase`가 `users`가 됩니다. 이러한 경로가 생성되면,
별칭은 `users.index`, `users.create` 등으로 설정됩니다. 별칭을 변경하려면,
`aliasBase`를 원하는 값으로 설정합니다.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Only 및 Except

어떤 경로를 생성할지 `only` 및 `except` 옵션을 사용하여 지정할 수도 있습니다.

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

이들은 기본적으로 화이트리스트 및 블랙리스트 옵션으로, 생성할 경로를 지정할 수 있습니다.

#### 미들웨어

`resource` 메서드로 생성된 각 경로에서 실행할 미들웨어를 지정할 수 있습니다.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## 스트리밍

이제 `streamWithHeaders()` 메서드를 사용하여 클라이언트에 응답을 스트리밍할 수 있습니다. 
이는 큰 파일, 긴 실행 프로세스 또는 큰 응답을 생성하는 데 유용합니다. 
라우트를 스트리밍하는 것은 일반 라우트와 약간 다르게 처리됩니다.

> **참고:** 스트리밍 응답은 [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering)이 false로 설정된 경우에만 가능합니다.

### 수동 헤더로 스트리밍

라우트에서 `stream()` 메서드를 사용하여 클라이언트에 응답을 스트리밍할 수 있습니다. 
이 경우 출력하기 전에 모든 메서드를 수동으로 설정해야 합니다.
이는 `header()` PHP 함수 또는 `Flight::response()->setRealHeader()` 메서드를 사용하여 수행됩니다.

```php
Flight::route('/@filename', function($filename) {

	// 분명히 경로 및 기타를 정리해야 합니다.
	$fileNameSafe = basename($filename);

	// 추가 헤더를 여기서 설정해야 합니다. 경로가 실행된 후에
	// 출력하기 전에 정의해야 합니다.
	// 모두 원시 호출로 `header()` 함수 또는
	// `Flight::response()->setRealHeader()` 메서드를 사용할 수 있습니다.
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// 또는
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// 오류 처리 및 기타를 진행합니다
	if(empty($fileData)) {
		Flight::halt(404, '파일을 찾을 수 없습니다');
	}

	// 원한다면 수동으로 콘텐츠 길이를 설정합니다
	header('Content-Length: '.filesize($filename));

	// 데이터를 클라이언트로 스트리밍합니다
	echo $fileData;

// 이것이 매직 라인입니다
})->stream();
```

### 헤더와 함께 스트리밍

헤더를 설정한 후 스트리밍을 시작할 수 있는 `streamWithHeaders()` 메서드를 사용할 수도 있습니다.

```php
Flight::route('/stream-users', function() {

	// 여기서 추가 헤더를 추가할 수 있습니다
	// 반드시 header() 또는 Flight::response()->setRealHeader()를 사용해야 합니다

	// 데이터를 끌어오는 방법에 따라, 예시로...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// 데이터를 클라이언트에 전송하기 위해 필요합니다
		ob_flush();
	}
	echo '}';

// 스트리밍을 시작하기 전에 헤더를 설정하는 방법입니다.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// 선택적 상태 코드, 기본값은 200
	'status' => 200
]);
```