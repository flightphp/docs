# 확장

## 개요

Flight는 확장 가능한 프레임워크로 설계되었습니다. 이 프레임워크는 기본 메서드와 구성 요소 세트를 제공하지만, 사용자의 자체 메서드를 매핑하거나, 자체 클래스 등록, 또는 기존 클래스와 메서드 재정의가 가능합니다.

## 이해

Flight의 기능을 확장하는 방법은 2가지가 있습니다:

1. 메서드 매핑 - 애플리케이션의 어디서나 호출할 수 있는 간단한 사용자 정의 메서드를 생성하는 데 사용됩니다. 이는 코드의 어디서나 호출할 수 있는 유틸리티 함수에 일반적으로 사용됩니다.
2. 클래스 등록 - Flight에 자체 클래스를 등록하는 데 사용됩니다. 이는 종속성이나 구성이 필요한 클래스에 일반적으로 사용됩니다.

기존 프레임워크 메서드를 재정의하여 프로젝트 요구사항에 더 잘 맞도록 기본 동작을 변경할 수도 있습니다.

> DIC(Dependency Injection Container)를 찾고 계신다면, [Dependency Injection Container](/learn/dependency-injection-container) 페이지로 이동하세요.

## 기본 사용법

### 프레임워크 메서드 재정의

Flight는 코드를 수정하지 않고도 자체 요구사항에 맞게 기본 기능을 재정의할 수 있도록 허용합니다. 재정의할 수 있는 모든 메서드는 [아래](#mappable-framework-methods)에서 확인할 수 있습니다.

예를 들어, Flight가 URL을 라우트에 매칭할 수 없을 때 `notFound` 메서드를 호출하여 일반적인 `HTTP 404` 응답을 보냅니다. 이 동작을 `map` 메서드를 사용하여 재정의할 수 있습니다:

```php
Flight::map('notFound', function() {
  // 사용자 정의 404 페이지 표시
  include 'errors/404.html';
});
```

Flight는 또한 프레임워크의 핵심 구성 요소를 교체할 수 있도록 허용합니다.
예를 들어 기본 Router 클래스를 자체 사용자 정의 클래스로 교체할 수 있습니다:

```php
// 사용자 정의 Router 클래스 생성
class MyRouter extends \flight\net\Router {
	// 여기서 메서드 재정의
	// 예를 들어 GET 요청에 대한 단축으로
	// pass route 기능을 제거
	public function get($pattern, $callback, $alias = '') {
		return parent::get($pattern, $callback, false, $alias);
	}
}

// 사용자 정의 클래스 등록
Flight::register('router', MyRouter::class);

// Flight가 Router 인스턴스를 로드할 때 사용자 클래스 로드
$myRouter = Flight::router();
$myRouter->get('/hello', function() {
  echo "Hello World!";
}, 'hello_alias');
```

그러나 `map` 및 `register`와 같은 프레임워크 메서드는 재정의할 수 없습니다. 이를 시도하면 오류가 발생합니다 (다시 [아래](#mappable-framework-methods)에서 메서드 목록 확인).

### 매핑 가능한 프레임워크 메서드

아래는 프레임워크의 전체 메서드 세트입니다. 이는 일반 정적 메서드인 핵심 메서드와, 필터링되거나 재정의될 수 있는 매핑된 메서드인 확장 메서드로 구성됩니다.

#### 핵심 메서드

이 메서드들은 프레임워크의 핵심이며 재정의할 수 없습니다.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // 사용자 정의 프레임워크 메서드 생성.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // 프레임워크 메서드에 클래스 등록.
Flight::unregister(string $name) // 프레임워크 메서드에 등록된 클래스 등록 해제.
Flight::before(string $name, callable $callback) // 프레임워크 메서드 전에 필터 추가.
Flight::after(string $name, callable $callback) // 프레임워크 메서드 후에 필터 추가.
Flight::path(string $path) // 클래스 자동 로딩을 위한 경로 추가.
Flight::get(string $key) // Flight::set()으로 설정된 변수 가져오기.
Flight::set(string $key, mixed $value) // Flight 엔진 내 변수 설정.
Flight::has(string $key) // 변수가 설정되었는지 확인.
Flight::clear(array|string $key = []) // 변수 지우기.
Flight::init() // 프레임워크를 기본 설정으로 초기화.
Flight::app() // 애플리케이션 객체 인스턴스 가져오기
Flight::request() // 요청 객체 인스턴스 가져오기
Flight::response() // 응답 객체 인스턴스 가져오기
Flight::router() // 라우터 객체 인스턴스 가져오기
Flight::view() // 뷰 객체 인스턴스 가져오기
```

#### 확장 메서드

```php
Flight::start() // 프레임워크 시작.
Flight::stop() // 프레임워크 중지 및 응답 전송.
Flight::halt(int $code = 200, string $message = '') // 선택적 상태 코드와 메시지로 프레임워크 중지.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // URL 패턴을 콜백에 매핑.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // POST 요청 URL 패턴을 콜백에 매핑.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // PUT 요청 URL 패턴을 콜백에 매핑.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // PATCH 요청 URL 패턴을 콜백에 매핑.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // DELETE 요청 URL 패턴을 콜백에 매핑.
Flight::group(string $pattern, callable $callback) // URL 그룹화 생성, 패턴은 문자열이어야 함.
Flight::getUrl(string $name, array $params = []) // 라우트 별칭에 기반한 URL 생성.
Flight::redirect(string $url, int $code) // 다른 URL로 리디렉션.
Flight::download(string $filePath) // 파일 다운로드.
Flight::render(string $file, array $data, ?string $key = null) // 템플릿 파일 렌더링.
Flight::error(Throwable $error) // HTTP 500 응답 전송.
Flight::notFound() // HTTP 404 응답 전송.
Flight::etag(string $id, string $type = 'string') // ETag HTTP 캐싱 수행.
Flight::lastModified(int $time) // 마지막 수정 HTTP 캐싱 수행.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSON 응답 전송.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSONP 응답 전송.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSON 응답 전송 및 프레임워크 중지.
Flight::onEvent(string $event, callable $callback) // 이벤트 리스너 등록.
Flight::triggerEvent(string $event, ...$args) // 이벤트 트리거.
```

`map` 및 `register`로 추가된 사용자 정의 메서드는 필터링될 수도 있습니다. 이러한 메서드를 필터링하는 방법에 대한 예시는 [Filtering Methods](/learn/filtering) 가이드를 참조하세요.

#### 확장 가능한 프레임워크 클래스

기존 클래스를 확장하고 자체 클래스를 등록하여 기능을 재정의할 수 있는 여러 클래스가 있습니다. 이러한 클래스들은 다음과 같습니다:

```php
Flight::app() // 애플리케이션 클래스 - flight\Engine 클래스 확장
Flight::request() // 요청 클래스 - flight\net\Request 클래스 확장
Flight::response() // 응답 클래스 - flight\net\Response 클래스 확장
Flight::router() // 라우터 클래스 - flight\net\Router 클래스 확장
Flight::view() // 뷰 클래스 - flight\template\View 클래스 확장
Flight::eventDispatcher() // 이벤트 디스패처 클래스 - flight\core\Dispatcher 클래스 확장
```

### 사용자 정의 메서드 매핑

간단한 사용자 정의 메서드를 매핑하려면 `map` 함수를 사용합니다:

```php
// 메서드 매핑
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// 사용자 정의 메서드 호출
Flight::hello('Bob');
```

간단한 사용자 정의 메서드를 만드는 것은 가능하지만, PHP에서 표준 함수를 생성하는 것이 IDE의 자동 완성 기능이 있고 읽기 쉽기 때문에 권장됩니다.
위 코드의 등가물은 다음과 같습니다:

```php
function hello(string $name) {
  echo "hello $name!";
}

hello('Bob');
```

이것은 메서드에 변수를 전달하여 예상 값이 필요한 경우 더 많이 사용됩니다. 아래와 같이 `register()` 메서드를 사용하는 것은 구성 전달 후 사전 구성된 클래스를 호출하는 데 더 적합합니다.

### 사용자 정의 클래스 등록

자체 클래스를 등록하고 구성하려면 `register` 함수를 사용합니다. `map()`에 비해 이점은 이 함수를 호출할 때 동일한 클래스를 재사용할 수 있다는 것입니다 (예: `Flight::db()`로 동일한 인스턴스 공유에 유용).

```php
// 클래스 등록
Flight::register('user', User::class);

// 클래스 인스턴스 가져오기
$user = Flight::user();
```

register 메서드는 클래스 생성자에 매개변수를 전달할 수도 있습니다.
따라서 사용자 정의 클래스를 로드할 때 미리 초기화된 상태로 제공됩니다.
생성자 매개변수는 추가 배열을 전달하여 정의할 수 있습니다.
데이터베이스 연결을 로드하는 예시입니다:

```php
// 생성자 매개변수와 함께 클래스 등록
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// 클래스 인스턴스 가져오기
// 정의된 매개변수로 객체 생성
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// 코드의 나중에 필요하다면 동일한 메서드를 다시 호출
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

추가 콜백 매개변수를 전달하면 클래스 생성 직후 즉시 실행됩니다. 이는 새 객체에 대한 설정 절차를 수행할 수 있도록 합니다. 콜백 함수는 새 객체의 인스턴스를 하나의 매개변수로 받습니다.

```php
// 콜백은 생성된 객체를 전달받음
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

기본적으로 클래스를 로드할 때마다 공유 인스턴스를 받습니다.
클래스의 새 인스턴스를 얻으려면 매개변수로 `false`를 전달하면 됩니다:

```php
// 클래스의 공유 인스턴스
$shared = Flight::db();

// 클래스의 새 인스턴스
$new = Flight::db(false);
```

> **참고:** 매핑된 메서드가 등록된 클래스보다 우선순위가 높습니다. 동일한 이름으로 둘 다 선언하면 매핑된 메서드만 호출됩니다.

### 예시

Flight를 핵심에 내장되지 않은 기능으로 확장하는 방법에 대한 예시입니다.

#### 로깅

Flight에는 내장 로깅 시스템이 없지만, 로깅 라이브러리를 Flight와 함께 사용하는 것은 매우 쉽습니다. Monolog 라이브러리를 사용한 예시입니다:

```php
// services.php

// Flight에 로거 등록
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

이제 등록되었으므로 애플리케이션에서 사용할 수 있습니다:

```php
// 컨트롤러나 라우트에서
Flight::log()->warning('This is a warning message');
```

이것은 지정한 로그 파일에 메시지를 로깅합니다. 오류 발생 시 로그를 남기고 싶다면 `error` 메서드를 사용할 수 있습니다:

```php
// 컨트롤러나 라우트에서
Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// 사용자 정의 오류 페이지 표시
	include 'errors/500.html';
});
```

또한 `before` 및 `after` 메서드를 사용하여 기본 APM(Application Performance Monitoring) 시스템을 생성할 수 있습니다:

```php
// services.php 파일에서

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('Request '.Flight::request()->url.' took ' . round($end - $start, 4) . ' seconds');

	// 요청 또는 응답 헤더를 로그에 추가할 수도 있음
	// (많은 요청이 있으면 데이터가 많아지니 주의)
	Flight::log()->info('Request Headers: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('Response Headers: ' . json_encode(Flight::response()->headers));
});
```

#### 캐싱

Flight에는 내장 캐싱 시스템이 없지만, 캐싱 라이브러리를 Flight와 함께 사용하는 것은 매우 쉽습니다. [PHP File Cache](/awesome-plugins/php_file_cache) 라이브러리를 사용한 예시입니다:

```php
// services.php

// Flight에 캐시 등록
Flight::register('cache', \flight\Cache::class, [ __DIR__ . '/../cache/' ], function(\flight\Cache $cache) {
    $cache->setDevMode(ENVIRONMENT === 'development');
});
```

이제 등록되었으므로 애플리케이션에서 사용할 수 있습니다:

```php
// 컨트롤러나 라우트에서
$data = Flight::cache()->get('my_cache_key');
if (empty($data)) {
	// 데이터 처리 수행
	$data = [ 'some' => 'data' ];
	Flight::cache()->set('my_cache_key', $data, 3600); // 1시간 캐시
}
```

#### 간단한 DIC 객체 인스턴스화

애플리케이션에서 DIC(Dependency Injection Container)를 사용 중이라면, Flight를 사용하여 객체를 인스턴스화할 수 있습니다. [Dice](https://github.com/level-2/Dice) 라이브러리를 사용한 예시입니다:

```php
// services.php

// 새 컨테이너 생성
$container = new \Dice\Dice;
// 아래와 같이 자신에게 재할당하는 것을 잊지 마세요!
$container = $container->addRule('PDO', [
	// shared는 동일 객체가 매번 반환됨을 의미
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// 이제 모든 객체를 생성하는 매핑 가능한 메서드 생성 가능. 
Flight::map('make', function($class, $params = []) use ($container) {
	return $container->create($class, $params);
});

// 컨트롤러/미들웨어에 사용하도록 컨테이너 핸들러 등록
Flight::registerContainerHandler(function($class, $params) {
	Flight::make($class, $params);
});


// 생성자에서 PDO 객체를 받는 샘플 클래스라고 가정
class EmailCron {
	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function send() {
		// 이메일 전송 코드
	}
}

// 마지막으로 의존성 주입을 사용하여 객체 생성
$emailCron = Flight::make(EmailCron::class);
$emailCron->send();
```

멋지지 않나요?

## 관련 자료
- [Dependency Injection Container](/learn/dependency-injection-container) - Flight와 DIC 사용 방법.
- [File Cache](/awesome-plugins/php_file_cache) - Flight와 캐싱 라이브러리 사용 예시.

## 문제 해결
- 매핑된 메서드가 등록된 클래스보다 우선순위가 높습니다. 동일한 이름으로 둘 다 선언하면 매핑된 메서드만 호출됩니다.

## 변경 로그
- v2.0 - 초기 릴리스.