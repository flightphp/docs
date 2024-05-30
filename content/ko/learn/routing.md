## 라우팅

> **참고:** 라우팅에 대해 더 자세히 이해하고 싶으신가요? 깊은 설명을 보려면 ["왜 프레임워크를 사용해야 할까?"](/learn/why-frameworks) 페이지를 확인해보세요.

Flight에서의 기본 라우팅은 URL 패턴과 콜백 함수 또는 클래스와 메서드의 배열을 매칭하여 수행됩니다.

```php
Flight::route('/', function(){
    echo '안녕, 세계!';
});
```

> 라우트는 정의된 순서대로 매칭됩니다. 요청과 매치된 첫 번째 라우트가 호출됩니다.

### 콜백/함수
콜백은 호출 가능한 임의의 객체일 수 있습니다. 따라서 일반 함수를 사용할 수 있습니다.

```php
function hello(){
    echo '안녕, 세계!';
}

Flight::route('/', 'hello');
```

### 클래스
정적 메서드를 사용할 수도 있습니다.

```php
class Greeting {
    public static function hello() {
        echo '안녕, 세계!';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

또는 먼저 객체를 생성한 다음 메서드를 호출할 수도 있습니다.

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "안녕하세요, {$this->name}님!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// 객체를 먼저 생성하지 않고도 다음과 같이 할 수도 있습니다
// 참고: 생성자로 매개변수가 주입되지 않습니다
Flight::route('/', [ 'Greeting', 'hello' ]);
```

#### DIC (의존성 주입 컨테이너)를 통한 의존성 주입
의존성 주입을 사용하려면 (PSR-11, PHP-DI, Dice 등의) 컨테이너를 통해 의존성 주입을 사용하면 되는데,
객체를 직접 생성하고 컨테이너를 사용하여 객체를 만들거나 클래스와 메서드를 정의하는 문자열을 사용해야 합니다. 더 자세한 정보는 [의존성 주입](/learn/extending) 페이지를 참조하세요.

다음은 간단한 예제입니다.

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
		echo "안녕하세요! 제 이름은 {$name}입니다!";
	}
}

// index.php

// 필요한 매개변수로 컨테이너를 설정합니다
// PSR-11에 대한 더 자세한 정보는 의존성 주입 페이지를 확인하세요
$dice = new \Dice\Dice();

// 변수를 '$dice = '로 재할당하는 것을 잊지 마세요!
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

// 보통의 라우트와 마찬가지로
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// 또는
Flight::route('/hello/@id', 'Greeting->hello');
// 또는
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## 메소드 라우팅

기본적으로 라우트 패턴은 모든 요청 방법과 일치합니다. 특정 방법에 응답하려면 URL 앞에 식별자를 놓으세요.

```php
Flight::route('GET /', function () {
  echo 'GET 요청을 받았습니다.';
});

Flight::route('POST /', function () {
  echo 'POST 요청을 받았습니다.';
});

// 변수를 가져오는 메서드인 Flight::get()은 라우트를 설정하는 메서드가 아닙니다.
//    그래서 다음과 같이 사용할 수 없습니다.
// Flight::post('/', function() { /* 코드 */ });
// Flight::patch('/', function() { /* 코드 */ });
// Flight::put('/', function() { /* 코드 */ });
// Flight::delete('/', function() { /* 코드 */ });
```

`|` 구분자를 사용하여 하나의 콜백에 여러 방법을 매핑할 수도 있습니다.

```php
Flight::route('GET|POST /', function () {
  echo 'GET 또는 POST 요청을 받았습니다.';
});
```

또한 몇 가지 도우미 메서드를 사용할 수 있는 Router 객체를 가져올 수도 있습니다:

```php

$router = Flight::router();

// 모든 방법 매핑
$router->map('/', function() {
	echo '안녕, 세계!';
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

라우트에서 정규 표현식을 사용할 수 있습니다.

```php
Flight::route('/user/[0-9]+', function () {
  // /user/1234에 매치됩니다.
});
```

이 방법은 가능하지만, 명명된 매개변수 또는 정규식을 사용하는 것이 더 가독성이 좋고 유지보수가 쉽다는 것을 염두에 두세요.

## 명명된 매개변수

콜백 함수에 전달되는 매개변수로 명명된 매개변수를 지정할 수 있습니다.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "$name님, 안녕하세요 ($id)!";
});
```

`: ` 구분자를 사용하여 명명된 매개변수와 함께 정규 표현식을 사용할 수도 있습니다.

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // /bob/123은 매치되지만
  // /bob/12345는 매치되지 않습니다.
});
```

> **참고:** 명명된 매개변수와 정규식 그룹 `()`을 매칭하는 것은 지원되지 않습니다. :'\(

## 선택적 매개변수

매칭을 위한 선택적 매개변수를 명시할 수 있습니다.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 다음 URL에 매치됩니다:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

매칭되지 않은 선택적 매개변수는 `NULL`로 전달됩니다.

## 와일드카드

매칭은 개별 URL 세그먼트에 대해서만 수행됩니다. 여러 세그먼트를 매치하려면 `*` 와일드카드를 사용할 수 있습니다.

```php
Flight::route('/blog/*', function () {
  // /blog/2000/02/01에 매치됩니다.
});
```

모든 요청을 단일 콜백으로 라우팅하려면 다음을 수행하면 됩니다:

```php
Flight::route('*', function () {
  // 무언가 수행
});
```

## 패싱

콜백 함수에서 `true`를 반환하여 다음 매칭 라우트로 실행을 전달할 수 있습니다.

```php
Flight::route('/user/@name', function (string $name) {
  // 어떤 조건을 확인합니다
  if ($name !== "Bob") {
    // 다음 라우트로 계속
    return true;
  }
});

Flight::route('/user/*', function () {
  // 여기가 호출됩니다
});
```

## 라우트에 별칭 지정

라우트에 별칭을 지정하여 나중에 코드에서 동적으로 URL을 생성할 수 있습니다(예: 템플릿과 같이).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// 나중에 코드의 어딘가에서
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환합니다.
```

URL이 변경될 경우 특히 유용합니다. 위 예시에선 사용자가 `/admin/users/@id`로 이동했다고 가정해보세요.
별칭을 사용하면 별칭을 참조하는 모든 곳을 변경할 필요가 없습니다. 별칭은 이제 `/admin/users/5`와 같이 반환됩니다.

그룹에서도 별칭을 사용할 수 있습니다:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// 나중에 코드의 어딘가에서
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환합니다.
```

## 라우트 정보

매칭된 라우트 정보를 검사하려면, 세 번째 매개변수에 `true`를 전달하여 라우트 객체를 콜백 함수에 전달하도록 요청할 수 있습니다. 라우트 객체는 항상 콜백 함수에 전달되는 마지막 매개변수입니다.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // HTTP 방법 배열과 매치
  $route->methods;

  // 명명된 매개변수 배열
  $route->params;

  // 매칭된 정규식
  $route->regex;

  // URL 패턴에서 사용된 '*'의 내용
  $route->splat;

  // URL 경로 표시... 정말 필요한 경우에만
  $route->pattern;

  // 이 검사를 위한 미들웨어
  $route->middleware;

  // 이 라우트에 할당된 별칭
  $route->alias;
}, true);
```

## 라우트 그룹화

때로는 관련된 라우트를 그룹화하고 싶을 수 있습니다(예: `/api/v1`와 같이). 이를 위해 `group` 메서드를 사용할 수 있습니다:

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

그룹 안에 그룹을 중첩할 수도 있습니다:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get()은 변수를 가져옵니다. 라우트를 설정하지 않습니다!
	// 아래에 있는 객체 컨텍스트를 확인하세요
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

	// Flight::get()은 변수를 가져옵니다. 라우트를 설정하지 않습니다!
	Flight::route('GET /users', function () {
	  // GET /api/v2/users와 일치합니다
	});
  });
});
```

### 객체 컨텍스트를 사용한 그룹

다음과 같이 `Engine` 객체를 사용하여 여전히 라우트 그룹을 사용할 수 있습니다:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // $router 변수를 사용하세요.
  $router->get('/users', function () {
	// GET /api/v1/users와 일치합니다
  });

  $router->post('/posts', function () {
	// POST /api/v1/posts와 일치합니다
  });
});
```

## 스트리밍

`streamWithHeaders()` 메서드를 사용하여 클라이언트에 응답을 스트리밍할 수 있습니다.
큰 파일을 보내거나 긴 실행 프로세스를 처리하거나 큰 응답을 생성하는 데 유용합니다.
라우트를 스트리밍하는 방법은 일반 라우트와 조금 다릅니다.

> **참고:** [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering)가 `false`로 설정되어 있는 경우에만 스트리밍 응답을 사용할 수 있습니다.

### 수동 헤더로 스트리밍

라우트에서 `stream()` 메서드를 사용하여 클라이언트에 응답을 스트리밍할 수 있습니다. 이렇게 할 경우 클라이언트에 어떤 것이든 출력하기 전에 모든 헤더를 설정해야 합니다.
이는 `header()` php 함수나 `Flight::response()->setRealHeader()` 메서드를 사용하여 수행됩니다.

```php
Flight::route('/@filename', function($filename) {

	// 경로를 정리할 것입니다.
	$fileNameSafe = basename($filename);

	// 라우트 실행 후에 추가로 설정해야할 헤더가 있다면 여기에 추가하세요
	// 출력하기 전에 정의되어야하며
	// 모든 것이 header() 함수의 직접적인 호출 또는
	// Flight::response()->setRealHeader()의 호출이어야 합니다
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// 또는
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// 에러 처리 등
	if(empty($fileData)) {
		Flight::halt(404, '파일을 찾을 수 없습니다');
	}

	// 원한다면 컨텐츠 길이를 수동으로 설정할 수 있습니다
	header('Content-Length: '.filesize($filename));

	// 데이터를 클라이언트로 스트리밍합니다
	echo $fileData;

// 여기가 중요한 부분입니다
})->stream();
```

### 헤더와 함께 스트리밍

`streamWithHeaders()` 메서드를 사용하여 스트리밍을 시작하기 전에 헤더를 설정할 수도 있습니다.

```php
Flight::route('/stream-users', function() {

	// 여기에 원하는 추가 헤더를 추가할 수 있습니다
	// header() 또는 Flight::response()->setRealHeader()를 사용해야 합니다

	// 데이터를 추출하는 방법은 무엇이든 가능합니다. 여기에는 예시만 있습니다...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count## 라우팅

> **참고:** 라우팅에 대한 더 자세한 내용을 이해하고 싶다면 ["왜 프레임워크를 사용해야 하는가?"]( /learn/why-frameworks ) 페이지를 확인하십시오.

Flight에서의 기본 라우팅은 URL 패턴을 콜백 함수 또는 클래스와 메서드의 배열과 매치하여 실행됩니다.

```php
Flight::route('/', function(){
    echo '안녕, 세계!';
});
```

> 라우트는 정의된 순서대로 매치됩니다. 요청을 매치하는 첫 번째 라우트가 호출됩니다.

### 콜백/함수

콜백은 호출 가능한 아무 객체나 될 수 있습니다. 따라서 보통 함수를 사용할 수 있습니다.

```php
function hello(){
    echo '안녕, 세계!';
}

Flight::route('/', 'hello');
```

### 클래스

정적 메서드를 사용할 수도 있습니다.

```php
class Greeting {
    public static function hello() {
        echo '안녕, 세계!';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

또는 먼저 객체를 생성한 다음 메서드를 호출할 수도 있습니다.

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "안녕하세요, {$this->name}님!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// 객체를 먼저 생성하지 않고도 다음과 같이 할 수도 있습니다
// 참고: 생성자에 매개변수가 주입되지 않습니다
Flight::route('/', [ 'Greeting', 'hello' ]);
```

#### DIC(의존성 주입 컨테이너)를 통한 의존성 주입

의존성 주입을 사용하려면 컨테이너를 통해 객체를 만들거나 클래스와 메서드를 정의하는 문자열을 사용해야 합니다.
더 많은 정보를 원하시면 [의존성 주입](/learn/extending) 페이지를 참조하십시오.

다음은 간단한 예제입니다.

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
		echo "안녕하세요, 세상아! 제 이름은 {$name}입니다!";
	}
}

// index.php

// 필요한 매개변수로 컨테이너를 설정합니다
// 더 많은 정보를 보려면 의존성 주입 페이지를 참조하십시오
$dice = new \Dice\Dice();

// 반드시 변수를 '$dice = '로 재할당하세요!!
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

// 보통의 라우트와 마찬가지로
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// 또는
Flight::route('/hello/@id', 'Greeting->hello');
// 또는
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## 메서드 라우팅

기본적으로 라우트 패턴은 모든 요청 방법과 일치합니다. 특정 방법에 응답하려면 URL 앞에 식별자를 놓으세요.

```php
Flight::route('GET /', function () {
  echo 'GET 요청을 받았습니다.';
});

Flight::route('POST /', function () {
  echo 'POST 요청을 받았습니다.';
});

// 변수를 가져오는 메서드인 Flight::get()은 라우트를 만들지 않습니다.
// Flight::post('/', function() { /* 코드 */ });
// Flight::patch('/', function() { /* 코드 */ });
// Flight::put('/', function() { /* 코드 */ });
// Flight::delete('/', function() { /* 코드 */ });
```

`|` 구분자를 사용하여 여러 방법을 하나의 콜백에 매핑할 수도 있습니다.

```php
Flight::route('GET|POST /', function () {
  echo 'GET 또는 POST 요청을 받았습니다.';
});
```

또한 일부 도우미 메서드를 사용할 수 있는 Router 객체를 얻을 수도 있습니다:

```php

$router = Flight::router();

// 모든 방법 매핑
$router->map('/', function() {
	echo '안녕하세요, 세계!';
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

## 정규식

라우트에서 정규식을 사용할 수 있습니다.

```php
Flight::route('/user/[0-9]+', function () {
  // /user/1234와 일치합니다.
});
```

이 방법이 가능하지만, 명명된 매개변수 또는 정규식을 사용하면 더 읽기 쉽고 유지보수가 쉬워집니다.

## 명명된 매개변수

라우트에서 콜백 함수로 전달되는 매개변수로 명명된 매개변수를 지정할 수 있습니다.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "$name님, 안녕하세요 ($id)!";
});
```

`:` 구분자를 사용하여 명명된 매개변수와 정규식을 사용한 매개변수를 포함할 수 있습니다.

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // /bob/123에 일치하지만
  // /bob/12345에는 일치하지 않습니다.
});
```

> **참고:** 명명된 매개변수와 정규식 그룹 `()`를 사용한 매치는 지원되지 않습니다. :'\(

## 선택적 매개변수

매칭을 위한 선택적 매개변수를 명시할 수 있습니다.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 다음 URL에 일치합니다:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

일치하지 않은 선택적 매개변수는 `NULL`로 전달됩니다.

## 와일드카드

매칭은 각 URL 세그먼트별로만 수행됩니다. 여러 세그먼트를 매치하려면 `*` 와일드카드를 사용하십시오.

```php
Flight::route('/blog/*', function () {
  // /blog/2000/02/01에 일치합니다.
});
```

모든 요청을 단일 콜백으로 라우팅하려면 다음을 수행하십시오:

```php
Flight::route('*', function () {
  // 무언가를 수행
});
```

## 통과

콜백 함수에서 `true`를 반환하여 다음 매치되는 라우트로 실행을 전달할 수 있습니다.

```php
Flight::route('/user/@name', function (string $name) {
  // 어떤 조건을 확인합니다
  if ($name !== "Bob") {
    // 다음 라우트로 계속합니다
    return true;
  }
});

Flight::route('/user/*', function () {
  // 이것이 호출됩니다
});
```

## 라우트 별칭

라우트에 별칭을 할당하여 URL을 나중에 동적으로 생성할 수 있습니다(예: 템플릿에서).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// 나중에 코드의 어딘가에서
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환합니다.
```

특히 URL이 변경되는 경우 도움이 됩니다. 위의 예에서 `/admin/users/@id`로 이동한다고 상상해보세요.
별칭을 사용하면 별칭을 참조하는 모든 곳을 변경할 필요가 없습니다. 별칭은 이제 `/admin/users/5`와 같이 반환됩니다.

라우트 별칭은 그룹에서도 작동합니다:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// 나중에 코드의 어딘가에서
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환합니다.
```

## 라우트 정보

일치하는 라우트 정보를 검사하려면 `true`로 세 번째 매개변수로 전달하여 라우트 객체를 콜백 함수에 전달할 수 있습니다. 라우트 객체는 항상 콜백 함수에 전달되는 마지막 매개변수입니다.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 일치하는 HTTP 방법 배열
  $route->methods;

  // 명명된 매개변수 배열
  $route->params;

  // 일치하는 정규식
  $route->regex;

  // URL 패턴에 사용된 '*'의 내용
  $route->splat;

  // URL 경로 표시... 실제로 필요한 경우에만
  $route->pattern;

  // 이 라우트에 할당된 미들웨어
  $route->middleware;

  // 이 라우트에 할당된 별칭
  $route->alias;
}, true);
```

## 라우트 그룹화

때로는 관련된 라우트를 그룹화하려는 경우가 있습니다(예: `/api/v1`).
이를 위해 `group` 메서드를 사용할 수 있습니다:

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

그룹 안에 그룹을 중첩할 수도 있습니다:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get()은 변수를 가져옵니다. 라우트를 설정하지 않습니다!
	// 객체 컨텍스트에 관해 아래를 참조하세요
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

	// Flight::get()은 변수를 가져옵니다. 라우트를 설정하지 않습니다!
	Flight::route('GET /users', function () {
	  // GET /api/v2/users와 일치합니다
	});
  });
});
```

### 객체 컨텍스트를 사용한 그룹

다음과 같이 `Engine` 객체를 사용하여 여전히 라우트 그룹을 사용할 수 있습니다:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // $router 변수를 사용하세요.
  $router->get('/users', function () {
	// GET /api/v1/users와 일치합니다
  });

  $router->post('/posts', function () {
	// POST /api/v1/posts와 일치합니다
  });
});
```

## 스트리밍

`streamWithHeaders()` 메서드를 사용하여 클라이언트에 응답을 스트리밍할 수 있습니다.
이는 대규모 파일을 보내거나 긴 실행 프로세스를 처리하거나 큰 응답을 생성하는 데 유용합니다.
스트리밍된 라우트는 일반 라우트와 약간 다르게 처리됩니다.

> **참고:** 스트리밍 응답을 사용하려면 [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering)가 `false`로 설정되어 있어야 합니다.

### 수동 헤더로 스트리밍

라우트에서 `stream()` 메서드를 사용하여 클라이언트에 응답을 스트리밍할 수 있습니다. 이 경우 모든 헤더를 설정한 후 클라이언트에 아무것도 출력하기 전에 일일히 설정해야 합니다.
이것은 `header()` php 함수나 `Flight::response()->setRealHeader()` 메서드를 사용하여 수행됩니다.

```php
Flight::route('/@filename', function($filename) {

	// 경로를 안전하게 정리합니다.
	$fileNameSafe = basename($filename);

	// 추가 헤더가 라우트 실행 후에 설정되어야하는 경우
	// 어떤 것이든 클라이언트로 데이터를 보내기 전에 일일이 정의해야합니다.
	// 이는 `header()` 함수의 직접 호출 또는
	// `Flight::response()->setRealHeader()` 호출로 수행해야합니다.
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// 또는
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// 에러 처리 및 기타 핸들링
	if(empty($fileData)) {
		Flight::halt(404, '파일을 찾을 수 없음');
	}

	// 수동으로 길이를 설정할 수도 있습니다.
	header('Content-Length: '.filesize($filename));

	// 데이터를 클라이언트로 스트리밍합니다
	echo $fileData;

// 여기가 매직 라인입니다.
})->stream();
```

### 헤더와 함께 스트리밍

`streamWithHeaders()` 메서드를 사용하여 스트리밍을 시작하기 전에 헤더를 설정할 수도 있습니다.

```php
Flight::route('/stream-users', function() {

	// 원하는 추가 헤더를 여기에서 추가할 수 있습니다
	// 그러나 header() 또는 Flight::response()->setRealHeader()를 사용해야합니다.
	// 데이터를 가져올 방식은 여기서는 간단히...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// 클라이언트로 데이터를 보내려면 필요합니다.
		ob_flush();
	}
	echo '}';

// 스트리밍을 시작하기 전에 헤더를 설정하는 방법입니다## 라우팅

> **참고:** 라우팅에 대해 더 자세히 이해하고 싶으신가요? 보다 깊은 설명을 원하시면 ["왜 프레임워크를 사용해야 하는가?"]( /learn/why-frameworks ) 페이지를 확인해주세요.

Flight에서의 기본 라우팅은 URL 패턴을 콜백 함수 또는 클래스와 메서드의 배열과 매치하여 작동합니다.

```php
Flight::route('/', function(){
    echo '안녕, 세계!';
});
```

> 라우트는 정의한 순서대로 매칭됩니다. 요청과 매치되는 첫 번째 라우트가 호출됩니다.

### 콜백/함수
콜백은 호출 가능한 객체일 수 있습니다. 따라서 보통 함수를 사용할 수 있습니다.

```php
function hello(){
    echo '안녕, 세계!';
}

Flight::route('/', 'hello');
```

### 클래스
정적 메서드를 사용할 수도 있습니다.

```php
class Greeting {
    public static function hello() {
        echo '안녕, 세계!';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

또는 객체를 먼저 생성한 다음 메서드를 호출할 수도 있습니다.

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "안녕하세요, {$this->name}님!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// 객체를 먼저 생성하지 않고도 다음과 같이 할 수도 있습니다
// 참고: 생성자에 매개변수가 주입되지 않습니다
Flight::route('/', [ 'Greeting', 'hello' ]);
```

#### DIC (의존성 주입 컨테이너)를 통한 의존성 주입
의존성 주입을 사용하기 위해 컨테이너를 통해 객체를 만들거나 클래스와 메서드를 문자열로 정의해야 합니다.
더 많은 정보는 의존성 주입 페이지를 참조하세요.

다음은 간단한 예제입니다.

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
		// $this->pdoWrapper로 작업 수행
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "안녕하세요! 제 이름은 {$name}입니다!";
	}
}

// index.php

// 필요한 매개변수와 함께 컨테이너를 설정합니다
// PSR-11에 대한 자세한 정보는 의존성 주입 페이지를 참조하세요
$dice = new \Dice\Dice();

// 변수를 '$dice = '로 다시 할당하지 않아야 합니다!
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

// 일반적인 라우트와 같이
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// 또는
Flight::route('/hello/@id', 'Greeting->hello');
// 또는
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## 메서드 라우팅

기본적으로 라우트 패턴은 모든 요청 방법과 매치됩니다. 특정 방법에 응답하려면 URL 앞에 식별자를 추가하십시오.

```php
Flight::route('GET /', function () {
  echo 'GET 요청을 받았습니다.';
});

Flight::route('POST /', function () {
  echo 'POST 요청을 받았습니다.';
});

// 변수를 가져오는 메서드인 Flight::get()은 라우트를 설정하지 않습니다.
// Flight::post('/', function() { /* 코드 */ });
// Flight::patch('/', function() { /* 코드 */ });
// Flight::put('/', function() { /* 코드 */ });
// Flight::delete('/', function() { /* 코드 */ });
```

`|` 구분자를 사용하여 여러 방법을 단일 콜백으로 매핑할 수도 있습니다.

```php
Flight::route('GET|POST /', function () {
  echo 'GET 또는 POST 요청을 받았습니다.';
});
```

또한 몇 가지 도우미 메서드가 있습니다. 이러한 Router 객체를 사용할 수 있습니다:

```php

$router = Flight::router();

// 모든 방법을 매핑
$router->map('/', function() {
	echo '안녕, 세계!';
});

// GET 요청
$router->get('/users', function() {
	echo '사용자';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## 정규식

라우트에서 정규식을 사용할 수 있습니다.

```php
Flight::route('/user/[0-9]+', function () {
  // /user/1234에 매치됩니다.
});
```

이 방법도 가능하지만, 명명된 매개변수 또는 정규식을 사용하는 것이 더 읽기 쉽고 유지보수가 쉽습니다.

## 명명된 매개변수

라우트에서 콜백 함수에 전달되는 매개변수로 명명된 매개변수를 지정할 수 있습니다.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "$name님, 안녕하세요 ($id)!";
});
```

`:` 구분자를 사용하여 명명된 매개변수와 정규식을 함께 사용할 수도 있습니다.

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // /bob/123에 매치되지만
  // /bob/12345에는 매치되지 않습니다.
});
```

> **참고:** 명명된 매개변수와 정규식 그룹 `()`와 함께 사용하는 방법은 지원되지 않습니다. :'\(

## 선택적 매개변수

매칭을 위한 선택적 매개변수를 지정할 수 있습니다.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 다음 URL에 매치됩니다:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

일치하지 않는 선택적 매개변수는 `NULL`로 전달됩니다.

## 와일드카드

매칭은 개별 URL 세그먼트에 대해만 수행됩니다. 여러 세그먼트를 매치하려면 `*` 와일드카드를 사용할 수 있습니다.

```php
Flight::route('/blog/*', function () {
  // /blog/2000/02/01에 매치됩니다.
});
```

모든 요청을 단일 콜백으로 라우팅하려면 다음을 수행하십시오:

```php
Flight::route('*', function () {
  // 무언가를 수행
});
```

## 패싱

콜백 함수에서 반환값으로 `true`를 사용하여 다음 매치 라우트로 실행을 전달할 수 있습니다.

```php
Flight::route('/user/@name', function (string $name) {
  // 어떤 조건을 확인
  if ($name !== "Bob") {
    // 다음 라우트로 이동
    return true;
  }
});

Flight::route('/user/*', function () {
  // 이 부분이 호출됨
});
```

## 라우트 별칭

라우트에 별칭을 할당하여 URL을 나중에 동적으로 생성할 수 있습니다(예: 템플릿 등에서).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// 나중에 코드의 어딘가에서
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환함
```
라우트가 변경될 경우 특히 유용합니다. 위의 예에서 사용자가 "/admin/users/@id"로 이동한다고 가정해보겠습니다.
여기서 별칭을 사용하면 별칭을 참조하는 모든 곳을 바꿀 필요가 없습니다. 그대로 사용하면 됩니다.
예를 들어 "/admin/users/5"로 반환됩니다.

라우트 별칭은 그룹에서도 작동합니다:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// 나중에 코드의 어딘가에서
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환함
```

## 라우트 정보

일치하는 라우트 정보를 검사하려면, 라우트 메서드에서 세 번째 매개변수로 `true`를 전달하여 라우트 객체를 콜백에 전달할 수 있습니다. 라우트 객체는 항상 콜백 함수의 마지막 매개변수로 전달됩니다.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 매치된 HTTP 방식 배열
  $route->methods;

  // 명명된 매개변수 배열
  $route->params;

  // 매칭된 정규식
  $route->regex;

  // URL 패턴에 사용된 '*'의 내용
  $route->splat;

  // URL 경로 표시... 필요한 경우에만 사용
  $route->pattern;

  // 할당된 미들웨어를 보여줍니다
  $route->middleware;

  // 할당된 별칭을 보여줍니다
  $route->alias;
}, true);
```

## 라우트 그룹화

때때로 관련된 라우트를 그룹화하고 싶을 수 있습니다(예: "/api/v1" 등).
이를 위해 `group` 메서드를 사용할 수 있습니다:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// "/api/v1/users"와 매치됩니다
  });

  Flight::route('/posts', function () {
	// "/api/v1/posts"와 매치됩니다
  });
});
```

그룹 안에 그룹을 중첩할 수도 있습니다:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get()은 변수를 얻는 역할을 하며, 라우트를 설정하지는 않습니다!
	Flight::route('GET /users', function () {
	  // GET "/api/v1/users"와 매치됩니다
	});

	Flight::post('/posts', function () {
	  // POST "/api/v1/posts"와 매치됩니다
	});

	Flight::put('/posts/1', function () {
	  // PUT "/api/v1/posts"와 매치됩니다
	});
  });
  Flight::group('/v2', function () {

	// Flight::get()은 변수를 얻는 역할을 하며, 라우트를 설정하지는 않습니다!
	Flight::route('GET /users', function () {
	  // GET "/api/v2/users"와 매치됩니다
	});
  });
});
```

### 객체 컨텍스트를 사용한 그룹

`Engine` 객체를 사용하여 여전히 라우트 그룹을 사용할 수 있습니다:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // $router 변수를 사용하세요.
  $router->get('/users', function () {
	// GET "/api/v1/users"와 매치됩니다
  });

  $router->post('/posts', function () {
	// POST "/api/v1/posts"와 매치됩니다
  });
});
```

## 스트리밍

`streamWithHeaders()` 메서드를 사용하여 클라이언트에 응답을 스트리밍할 수 있습니다.
이는 대형 파일을 보내거나 긴 실행 프로세스를 처리하거나 큰 응답을 생성하는 데 유용합니다.
스트리밍 라우트는 일반적인 라우트와는 약간 다르게 처리됩니다.

> **참고:** [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering)가 `false`로 설정되어 있는 경우에만 스트리밍 응답이 가능합니다.

### 헤더 수동 설정으로 스트리밍

라우트에서 `stream()` 메서드를 사용하여 클라이언트에 응답을 스트리밍할 수 있습니다. 이 경우 데이터를 보내기 전에 모든 헤더를 설정해야합니다.
이를 위해 `header()` php 함수나 `Flight::response()->setRealHeader()` 메서드를 사용해야합니다.

```php
Flight::route('/@filename', function($filename) {

	// 경로 정재에 주의해야합니다.
	$fileNameSafe = basename($filename);

	// 라우트 실행 후 추가로 설정할 헤더가 있다면 여기에 추가하세요
	// 클라이언트에 출력하기 전에 모든 것이 직접적인 header() 함수 호출이어야하거나
	// Flight::response()->setRealHeader() 메서드 호출이어야 합니다.
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// 또는
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// 누락된 파일 등에 대한 처리
	if(empty($fileData)) {
		Flight::halt(404, '파일을 찾을 수 없음');
	}

	// 수동으로 콘텐츠 길이를 설정하고 싶다면
	header('Content-Length: '.filesize($filename));

	// 데이터를 클라이언트로 스트리밍합니다
	echo $fileData;

// 이 라인이 매직입니다
})->stream();
```

### 헤더와 스트리밍

`streamWithHeaders()` 메서드를 사용하여 스트리밍을 시작하기 전에 헤더를 설정할 수 있습니다.

```php
Flight::route('/stream-users', function() {

	// 추가 헤더 설정이 필요한 경우 여기에 추가하십시오.
	// header() 또는 Flight::response()->setRealHeader()와 같은 방법을 사용해야합니다.

	// 데이터를 가져오는 방법에 따라 원하는대로 작성하세요.
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// 데이터를 클라이언트에 전송하려면 필요합니다
		ob_flush();
	}
	echo '}';

// 스트리밍 시작 전에 헤더를 설정하는 방