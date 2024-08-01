# 라우팅

> **참고:** 라우팅에 대해 더 알고 싶으신가요? 보다 자세한 설명을 보려면 ["왜 프레임워크를 사용해야 하는가?"](/learn/why-frameworks) 페이지를 확인해주세요.

Flight에서의 기본 라우팅은 URL 패턴과 콜백 함수 또는 클래스와 메소드의 배열과 일치시켜 수행됩니다.

```php
Flight::route('/', function(){
    echo '안녕 세상아!';
});
```

> 라우트는 정의된 순서대로 일치시킵니다. 요청과 일치하는 첫 번째 라우트가 호출됩니다.

### 콜백/함수
콜백은 호출 가능한 임의의 객체일 수 있습니다. 따라서 일반 함수를 사용할 수 있습니다:

```php
function hello() {
    echo '안녕 세상아!';
}

Flight::route('/', 'hello');
```

### 클래스
클래스의 정적 메소드를 사용할 수도 있습니다:

```php
class Greeting {
    public static function hello() {
        echo '안녕 세상아!';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

또는 먼저 객체를 생성한 다음 메소드를 호출할 수 있습니다:

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
// 객체를 생성하지 않고도 이 작업을 수행할 수 있습니다
// 참고: 생성자에는 인수가 주입되지 않을 것입니다
Flight::route('/', [ 'Greeting', 'hello' ]);
// 추가로 이 짧은 구문을 사용할 수도 있습니다
Flight::route('/', 'Greeting->hello');
// 또는
Flight::route('/', Greeting::class.'->hello');
```

#### DIC (Dependency Injection Container)를 통한 의존성 주입
컨테이너(PRS-11, PHP-DI, Dice 등)를 통해 의존성 주입을 하려면,
직접 객체를 생성하고 컨테이너를 사용하여 객체를 생성하거나 클래스와 메소드를 정의하는 문자열을 사용하는 라우트 유형만 사용할 수 있습니다.
더 많은 정보를 보려면 [의존성 주입](/learn/extending) 페이지로 이동하세요.

다음은 간단한 예시입니다:

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
		// $this->pdoWrapper로 뭔가 수행
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "안녕하세요, 세상아! 제 이름은 {$name}입니다!";
	}
}

// index.php

// 필요한 매개변수로 컨테이너를 설정
// 더 자세한 내용은 의존성 주입 페이지를 참조하세요
$dice = new \Dice\Dice();

// 변수를 '$dice = '로 재할당하는 것을 잊지 마세요!!!!
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

// 일반적으로 라우트 지정
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// 또는
Flight::route('/hello/@id', 'Greeting->hello');
// 또는
Flight::route('/hello/@id', Greeting::class.'->hello');

Flight::start();
```

## 메소드 라우팅

기본적으로 라우트 패턴은 모든 요청 메소드와 일치합니다. 특정 메소드에 응답하려면 URL 앞에 식별자를 배치하면 됩니다.

```php
Flight::route('GET /', function () {
  echo 'GET 요청을 받았습니다.';
});

Flight::route('POST /', function () {
  echo 'POST 요청을 받았습니다.';
});

// 라우트를 만들기 위해 Flight::get()을 사용할 수 없습니다
//    변수를 가져오는 메소드이지 라우트를 만드는 메소드가 아닙니다.
// Flight::post('/', function() { /* 코드 */ });
// Flight::patch('/', function() { /* 코드 */ });
// Flight::put('/', function() { /* 코드 */ });
// Flight::delete('/', function() { /* 코드 */ });
```

`|` 구분자를 사용하여 여러 메소드를 단일 콜백에 매핑할 수도 있습니다:

```php
Flight::route('GET|POST /', function () {
  echo 'GET 또는 POST 요청을 받았습니다.';
});
```

또한 유용한 몇 가지 도우미 메소드가 있는 Router 객체를 가져올 수도 있습니다:

```php

$router = Flight::router();

// 모든 메소드에 대해 매핑
$router->map('/', function() {
	echo '안녕 세상아!';
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
  // /user/1234에 일치합니다.
});
```

이 방법이 사용 가능하지만, 명명된 매개변수 또는 정규 표현식과 결합된 명명된 매개변수를 사용하는 것이 권장됩니다. 이러한 방법이 더 가독성이 높고 유지보수가 쉽습니다.

## 명명된 매개변수

콜백 함수로 전달될 명명된 매개변수를 지정할 수 있습니다.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "$name님, 안녕하세요 ($id)!";
});
```

`:` 구분자를 사용하여 명명된 매개변수에 정규 표현식을 포함할 수 있습니다:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // /bob/123과 일치할 것입니다
  // 하지만 /bob/12345와는 일치하지 않을 것입니다
});
```

> **참고:** 명명된 매개변수와 일치하는 정규식 그룹 `()`는 지원되지 않습니다. :'\(

## 선택적 매개변수

매칭에 선택적인 명명된 매개변수를 지정할 수 있습니다. 
세그먼트를 괄호로 둘러싸서 선택적으로 일치시킨다.

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

매칭은 개별 URL 세그먼트에서만 수행됩니다. 여러 세그먼트를 일치시키려면 `*` 와일드카드를 사용할 수 있습니다.

```php
Flight::route('/blog/*', function () {
  // /blog/2000/02/01에 일치합니다.
});
```

모든 요청을 단일 콜백으로 라우팅하려면 다음과 같이 할 수 있습니다:

```php
Flight::route('*', function () {
  // 무언가 수행
});
```

## 전달

콜백 함수에서 `true`를 반환하여 다음 일치하는 라우트로 실행을 전달할 수 있습니다.

```php
Flight::route('/user/@name', function (string $name) {
  // 조건 검사
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

라우트에 별칭을 지정하여 URL을 나중에 동적으로 생성할 수 있습니다(예: 템플릿과 유사한 경우).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// 나중에 코드의 어딘가에서
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환합니다.
```

이는 URL이 변할 경우 특히 유용합니다. 위의 예에서는 사용자가 `/admin/users/@id`로 이동했다고 가정해봅시다.
별칭을 사용하면 별칭을 참조하는 모든 위치를 수정할 필요가 없습니다. 다음과 같이 별칭은 이제 `/admin/users/5`를 반환합니다.

그룹 내에서도 라우트 별칭을 사용할 수 있습니다:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// 나중에 코드의 어딘가에서
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환합니다.
```

## 라우트 정보

일치하는 라우트 정보를 검사하려면 세 번째 매개변수로 `true`를 전달하여 라우트 메서드에 라우트 객체를 요청할 수 있습니다. 라우트 객체는 항상 콜백 함수에 전달된 마지막 매개변수입니다.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // HTTP 메소드 목록에 일치
  $route->methods;

  // 명명된 매개변수 목록
  $route->params;

  // 일치하는 정규식
  $route->regex;

  // URL 패턴 내에서 사용된 '*'의 내용
  $route->splat;

  // url 경로 표시....필요한 경우
  $route->pattern;

  // 이에 할당된 미들웨어 표시
  $route->middleware;

  // 이 라우트에 할당된 별칭 표시
  $route->alias;
}, true);
```

## 라우트 그룹

때로는 관련 있는 라우트를 그룹화하려는 경우가 있을 수 있습니다(예: `/api/v1` 등).
이를 위해 `group` 메서드를 사용할 수 있습니다:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// /api/v1/users에 일치합니다
  });

  Flight::route('/posts', function () {
	// /api/v1/posts에 일치합니다
  });
});
```

또한 그룹 내부에 그룹을 중첩할 수 있습니다:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get()은 변수를 가져올 뿐이며 라우트를 설정하지 않습니다! 객체 컨텍스트 참조 참조
	Flight::route('GET /users', function () {
	  // GET /api/v1/users에 일치합니다
	});

	Flight::post('/posts', function () {
	  // POST /api/v1/posts에 일치합니다
	});

	Flight::put('/posts/1', function () {
	  // PUT /api/v1/posts에 일치합니다
	});
  });
  Flight::group('/v2', function () {

	// Flight::get()은 변수를 가져올 뿐이며 라우트를 설정하지 않습니다! 객체 컨텍스트 참조 참조
	Flight::route('GET /users', function () {
	  // GET /api/v2/users에 일치합니다
	});
  });
});
```

### 객체 컨텍스트를 사용한 그룹화

다음과 같이 `Engine` 객체를 사용하여 여전히 라우트 그룹화를 사용할 수 있습니다:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // $router 변수를 사용합니다.
  $router->get('/users', function () {
	// GET /api/v1/users에 일치합니다
  });

  $router->post('/posts', function () {
	// POST /api/v1/posts에 일치합니다
  });
});
```

## 스트리밍

`streamWithHeaders()` 메소드를 사용하여 클라이언트에 응답을 스트리밍할 수 있습니다.
이는 대용량 파일을 보내거나 긴 작동 프로세스를 수행하거나 큰 응답을 생성하는 데 유용합니다.
라우트 스트리밍은 일반 라우트와는 조금 다르게 처리됩니다.

> **참고:** 스트리밍 응답은 [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering)이 거짓으로 설정된 경우에만 사용할 수 있습니다.

### 수동 헤더와 함께 스트리밍

라우트에서 `stream()` 메소드를 사용하여 클라이언트에 응답을 스트리밍할 수 있습니다. 이렇게 하려면 클라이언트에 출력하기 전에 모든 메서드를 수동으로 설정해야 합니다.
이는 `header()` php 함수 또는 `Flight::response()->setRealHeader()` 메서드를 사용하여 수행됩니다.

```php
Flight::route('/@filename', function($filename) {

	// 경로를 이상하게 처리해야 합니다.
	$fileNameSafe = basename($filename);

	// 라우트 실행 후 여기에 추가적인 헤더를 설정해야 하는 경우
	// 모든 헤더는 echo가 되기 전에 정의되어야 합니다.
	// 모두 header() 함수로 직접 호출 또는
	// Flight::response()->setRealHeader() 메소드를 사용해야 합니다.
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// 또는
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// 에러 처리 및 기타 등등
	if(empty($fileData)) {
		Flight::halt(404, '파일을 찾을 수 없습니다');
	}

	// 원하는 경우 내용 길이를 수동으로 설정할 수 있습니다
	header('Content-Length: '.filesize($filename));

	// 데이터를 클라이언트로 스트리밍
	echo $fileData;

// 이 부분이 마법의 줄입니다
})->stream();
```

### 헤더와 함께 스트리밍

`streamWithHeaders()` 메소드를 사용하여 스트리밍을 시작하기 전에 헤더를 설정할 수도 있습니다.

```php
Flight::route('/stream-users', function() {

	//