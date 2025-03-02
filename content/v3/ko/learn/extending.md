# 확장

Flight는 확장 가능한 프레임워크로 설계되었습니다. 이 프레임워크는 기본 메서드 및 구성 요소 집합과 함께 제공되지만, 여러분이 자신의 메서드를 매핑하고, 자신의 클래스를 등록하거나, 기존 클래스와 메서드를 오버라이드할 수 있도록 허용합니다.

DIC(의존성 주입 컨테이너)를 찾고 계신다면 [Dependency Injection Container](dependency-injection-container) 페이지로 이동하세요.

## 메서드 매핑

자신만의 간단한 사용자 정의 메서드를 매핑하려면 `map` 함수를 사용합니다:

```php
// 메서드 매핑
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// 사용자 정의 메서드 호출
Flight::hello('Bob');
```

간단한 사용자 정의 메서드를 만드는 것이 가능하지만, PHP에서 표준 함수를 생성하는 것이 권장됩니다. 이는 IDE에서 자동 완성 기능을 제공하며 읽기 더 쉽습니다.
위 코드의 동등한 형태는 다음과 같습니다:

```php
function hello(string $name) {
  echo "hello $name!";
}

hello('Bob');
```

이는 예상 값을 얻기 위해 메서드에 변수를 전달해야 할 때 더 많이 사용됩니다. 아래의 `register()` 메서드를 사용하는 것은 주로 구성 데이터를 전달한 다음 미리 구성된 클래스를 호출하는 데 사용됩니다.

## 클래스 등록

자신의 클래스를 등록하고 구성하려면 `register` 함수를 사용합니다:

```php
// 클래스 등록
Flight::register('user', User::class);

// 클래스의 인스턴스 가져오기
$user = Flight::user();
```

register 메서드는 클래스 생성자에 매개변수를 전달할 수 있도록 허용합니다. 따라서 사용자 정의 클래스를 로드할 때, 생성자가 미리 초기화되어 올 것입니다.
추가 배열을 전달하여 생성자 매개변수를 정의할 수 있습니다.
데이터베이스 연결을 로드하는 예제는 다음과 같습니다:

```php
// 생성자 매개변수와 함께 클래스 등록
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// 클래스의 인스턴스 가져오기
// 이는 정의된 매개변수로 객체를 생성합니다
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// 나중에 코드에서 필요할 때는 동일한 메서드를 다시 호출하기만 하면 됩니다
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

추가 콜백 매개변수를 전달하면 클래스 생성 직후에 실행됩니다. 이렇게 하면 새 객체에 대한 설정 절차를 수행할 수 있습니다. 콜백 함수는 새 객체의 인스턴스라는 하나의 매개변수를 받습니다.

```php
// 콜백은 생성된 객체가 전달됩니다
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

기본적으로 클래스를 로드할 때마다 공유 인스턴스를 얻게 됩니다.
클래스의 새 인스턴스를 얻으려면 `false`를 매개변수로 전달하기만 하면 됩니다:

```php
// 클래스의 공유 인스턴스
$shared = Flight::db();

// 클래스의 새 인스턴스
$new = Flight::db(false);
```

매핑된 메서드는 등록된 클래스보다 우선합니다. 동일한 이름으로 두 가지를 선언하면, 매핑된 메서드만 호출됩니다.

## 로깅

Flight에는 내장된 로깅 시스템이 없지만, 로깅 라이브러리를 Flight와 함께 사용하는 것은 매우 쉽습니다. 여기 Monolog 라이브러리를 사용하는 예가 있습니다:

```php
// index.php 또는 bootstrap.php

// Flight에 로거 등록
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

이제 등록되었으므로 응용 프로그램에서 사용할 수 있습니다:

```php
// 컨트롤러 또는 경로에서
Flight::log()->warning('이것은 경고 메시지입니다');
```

이것은 지정한 로그 파일에 메시지를 기록합니다. 오류가 발생했을 때 무언가를 기록하고 싶다면 `error` 메서드를 사용할 수 있습니다:

```php
// 컨트롤러 또는 경로에서

Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// 사용자 정의 오류 페이지 표시
	include 'errors/500.html';
});
```

`before` 및 `after` 메서드를 사용하여 기본 APM(애플리케이션 성능 모니터링) 시스템을 만들 수도 있습니다:

```php
// 부트스트랩 파일에서

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('요청 '.Flight::request()->url.'는 ' . round($end - $start, 4) . ' 초 걸렸습니다');

	// 요청 또는 응답 헤더도 추가하여 기록할 수 있습니다
	// (많은 요청이 있을 경우 많은 데이터가 기록될 수 있으니 주의하십시오)
	Flight::log()->info('요청 헤더: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('응답 헤더: ' . json_encode(Flight::response()->headers));
});
```

## 프레임워크 메서드 오버라이드

Flight는 코드를 수정할 필요 없이 자신의 필요에 맞게 기본 기능을 오버라이드할 수 있도록 합니다. 여러분이 오버라이드할 수 있는 모든 메서드는 [여기](/learn/api)에서 확인할 수 있습니다.

예를 들어, Flight가 URL을 경로에 매핑할 수 없을 때, `notFound` 메서드를 호출하여 일반 `HTTP 404` 응답을 보냅니다. `map` 메서드를 사용하여 이 동작을 오버라이드할 수 있습니다:

```php
Flight::map('notFound', function() {
  // 사용자 정의 404 페이지 표시
  include 'errors/404.html';
});
```

Flight는 또한 프레임워크의 핵심 구성 요소를 교체할 수 있도록 합니다. 예를 들어, 기본 라우터 클래스를 사용자 정의 클래스로 교체할 수 있습니다:

```php
// 사용자 정의 클래스 등록
Flight::register('router', MyRouter::class);

// Flight가 라우터 인스턴스를 로드할 때, 여러분의 클래스를 로드합니다
$myrouter = Flight::router();
```

그러나 `map` 및 `register`와 같은 프레임워크 메서드는 오버라이드할 수 없습니다. 그렇게 시도하면 오류가 발생합니다.