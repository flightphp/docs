# 라우팅

> **참고:** 라우팅에 대해 더 알고 싶으신가요? 더 깊은 설명을 위해 ["왜 프레임워크를 사용해야 할까요?"](/learn/why-frameworks) 페이지를 확인해보세요.

Flight에서의 기본적인 라우팅은 URL 패턴을 콜백 함수나 클래스와 메소드의 배열과 일치시킴으로써 이루어집니다.

```php
Flight::route('/', function(){
    echo '안녕, 세상아!';
});
```

> 라우트는 정의된 순서대로 일치합니다. 요청과 일치하는 첫 번째 라우트가 호출됩니다.

### 콜백/함수
콜백은 호출 가능한 임의의 객체가 될 수 있습니다. 예를 들어 일반 함수를 사용할 수 있습니다.

```php
function hello(){
    echo '안녕, 세상아!';
}

Flight::route('/', 'hello');
```

### 클래스
정적 클래스 메소드 또한 사용할 수 있습니다.

```php
class Greeting {
    public static function hello() {
        echo '안녕, 세상아!';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

또한 객체를 먼저 생성한 다음 메소드를 호출할 수도 있습니다.

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = '홍길동';
    }

    public function hello() {
        echo "{$this->name}님, 안녕하세요!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// 객체를 먼저 생성하지 않고도 이 작업을 수행할 수도 있습니다.
// 참고: 생성자로 인수가 주입되지 않을 것임을 유념하세요
Flight::route('/', [ 'Greeting', 'hello' ]);
```

#### DIC (의존성 주입 컨테이너)를 통한 의존성 주입
컨테이너(PSR-11, PHP-DI, Dice 등)를 통해 의존성 주입을 사용하고 싶다면,
객체를 직접 생성하고 컨테이너를 사용하여 객체를 생성하는 라우트 유형이 유일합니다.
또는 클래스와 메소드를 호출하도록 문자열을 사용할 수도 있습니다. 더 많은 정보를 위해 [의존성 주입](/learn/extending) 페이지를 방문해보세요.

다음은 간단한 예제입니다:

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
// PSR-11에 대한 추가 정보는 Dependency Injection 페이지를 참조하세요
$dice = new \Dice\Dice();

// 변수를 '$dice = '로 다시 할당하는 것을 잊지 마세요!!!!
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

// 일반적인 라우트와 같이 사용합니다
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// 또는
Flight::route('/hello/@id', 'Greeting->hello');
// 또는
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## 메소드 라우팅

기본적으로, 라우트 패턴은 모든 요청 메소드와 일치합니다. 특정 메소드에 응답하기 위해 URL 앞에 식별자를 배치할 수 있습니다.

```php
Flight::route('GET /', function () {
  echo 'GET 요청을 받았습니다.';
});

Flight::route('POST /', function () {
  echo 'POST 요청을 받았습니다.';
});

// 변수를 가져오기 위한 메소드로 Flight::get()을 사용할 수 없습니다.
// 라우트를 생성하는 데 사용되는 메소드가 아니기 때문입니다.
// Flight::post('/', function() { /* 코드 */ });
// Flight::patch('/', function() { /* 코드 */ });
// Flight::put('/', function() { /* 코드 */ });
// Flight::delete('/', function() { /* 코드 */ });
```

`|` 구분자를 사용하여 여러 메소드를 하나의 콜백에 매핑할 수도 있습니다:

```php
Flight::route('GET|POST /', function () {
  echo 'GET 또는 POST 요청을 받았습니다.';
});
```

또한 일부 헬퍼 메소드를 사용할 수 있는 Router 객체를 가져올 수도 있습니다:

```php

$router = Flight::router();

// 모든 메소드에 매핑
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
  // 이는 /user/1234와 매치합니다.
});
```

이 방법은 사용할 수 있지만, 명명된 매개변수나 정규 표현식을 사용하는 것이 추천됩니다. 
이러한 방법은 더 가독성이 높고 유지보수가 쉽기 때문입니다.

## 명명된 매개변수

라우트에 전달되는 콜백 함수에 명명된 매개변수를 지정할 수 있습니다.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "안녕하세요, $name ($id)님!";
});
```

`:` 구분자를 사용하여 명명된 매개변수와 함께 정규 표현식을 사용할 수도 있습니다:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // 이는 /bob/123과 매치합니다.
  // 하지만 /bob/12345와는 매치하지 않습니다.
});
```

> **참고:** 명명된 매개변수와 정규 표현식을 사용하여 매개변수와 함께 `()`를 매치하는 것은 지원되지 않습니다. :'\(

## 선택적 매개변수

매치되는 선택적 매개변수를 정의할 수 있습니다.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 다음 URL들과 매치됩니다:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

매치되지 않은 선택적 매개변수들은 `NULL`로 전달됩니다.

## 와일드카드

매치는 개별 URL 세그먼트에 대해서만 이루어집니다. 여러 세그먼트를 매치하려면 `*` 와일드카드를 사용할 수 있습니다.

```php
Flight::route('/blog/*', function () {
  // 이는 /blog/2000/02/01과 매치합니다.
});
```

모든 요청을 단일 콜백으로 라우트하려면 다음과 같이 할 수 있습니다:

```php
Flight::route('*', function () {
  // 무언가 수행
});
```

## 패싱

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
  // 이 코드가 실행됩니다
});
```

## 라우트 별칭

라우트에 별칭을 지정하여 URL을 동적으로 생성할 수 있습니다 (예: 템플릿과 같은 용도).

```php
Flight::route('/users/@id', function($id) { echo '사용자:'.$id; }, false, 'user_view');

// 나중에 코드 어딘가에
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'을 반환합니다
```

이것은 URL이 변경되어도 동작하며 특히 유용합니다. 예를 들어 위의 예에서 사용자가 `/admin/users/@id`로 이동했다고 가정해 봅시다.
별칭 사용을 하면 별칭을 참조하는 모든 곳을 수정할 필요가 없습니다. 별칭은 이제 `/admin/users/5`와 같이 반환될 것입니다.

그룹에서도 라우트 별칭을 사용할 수 있습니다:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// 나중에 코드 어딘가에
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'을 반환합니다
```

## 라우트 정보

일치하는 라우트 정보를 검사하려면 라우트 메소드의 세 번째 매개변수로 `true`를 전달하면 됩니다. 라우트 객체는 항상 콜백 함수에 전달되는 마지막 매개변수가 됩니다.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 일치하는 HTTP 메소드 배열
  $route->methods;

  // 명명된 매개변수 배열
  $route->params;

  // 일치하는 정규 표현식
  $route->regex;

  // URL 패턴에 사용된 '*'의 내용
  $route->splat;

  // URL 경로 표시....정말 필요한 경우
  $route->pattern;

  // 이 라우트에 할당된 미들웨어 표시
  $route->middleware;

  // 이 라우트에 할당된 별칭 표시
  $route->alias;
}, true);
```

## 라우트 그룹화

때로는 관련된 라우트들을 그룹화하고 싶을 수 있습니다(예: `/api/v1`과 같이). 이를 위해 `group` 메소드를 사용할 수 있습니다:

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

그룹 중첩도 가능합니다:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get()은 변수를 가져오는 메소드이며, 라우트를 설정하지 않습니다! 아래 객체 컨텍스트를 참조하세요
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

	// Flight::get()은 변수를 가져오는 메소드이며, 라우트를 설정하지 않습니다! 아래 객체 컨텍스트를 참조하세요
	Flight::route('GET /users', function () {
	  // GET /api/v2/users와 일치
	});
  });
});
```

### 객체 컨텍스트를 사용한 그룹화

`Engine` 객체와 함께 라우트 그룹화를 계속 사용할 수 있습니다:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // $router 변수를 사용합니다
  $router->get('/users', function () {
	// GET /api/v1/users와 일치
  });

  $router->post('/posts', function () {
	// POST /api/v1/posts와 일치
  });
});
```

## 스트리밍

`streamWithHeaders()` 메소드를 사용하여 이제 클라이언트에게 응답을 스트리밍할 수 있습니다. 
큰 파일을 전송하거나 오래 실행되는 프로세스 또는 큰 응답을 생성하는 데 유용합니다.
라우트 스트리밍은 일반 라우트와는 약간 다르게 처리됩니다.

> **참고:** 응답 스트리밍은 [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering)이 false로 설정되어 있어야만 가능합니다.

```php
Flight::route('/stream-users', function() {

	// 라우트가 실행된 이후 여기서 추가 헤더를 설정해야 한다면
	// echo 되기 전에 정의해야 합니다.
	// 모든 헤더는 header() 함수를 사용하거나
	// Flight::response()->setRealHeader() 함수를 사용해야 합니다.
	header('Content-Disposition: attachment; filename="users.json"');
	// 또는
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="users.json"');

	// 데이터를 가져오는 방식과 무관하게, 단순 예제로...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// 데이터를 클라이언트로 보내려면 여기가 필요합니다
		ob_flush();
	}
	echo '}';

// 스트리밍을 시작하기 전에 헤더를 설정하는 방법을 보여줍니다.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	// 옵션 상태 코드, 기본값은 200입니다
	'status' => 200
]);
```