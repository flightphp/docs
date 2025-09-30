# v3로 마이그레이션

대부분의 경우 뒤로 호환성이 유지되었지만, v2에서 v3로 마이그레이션할 때 알아야 할 몇 가지 변경 사항이 있습니다. 설계 패턴과 너무 많이 충돌하는 변경 사항이 있어서 일부 조정이 필요했습니다.

## 출력 버퍼링 동작

_v3.5.0_

[출력 버퍼링](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php)은 PHP 스크립트에서 생성된 출력을 클라이언트로 보내기 전에 PHP 내부 버퍼에 저장하는 프로세스입니다. 이를 통해 클라이언트로 보내기 전에 출력을 수정할 수 있습니다.

MVC 애플리케이션에서 컨트롤러는 "관리자" 역할을 하며 뷰가 무엇을 하는지 관리합니다. 컨트롤러 외부(또는 Flight의 경우 때때로 익명 함수)에서 출력이 생성되는 것은 MVC 패턴을 깨뜨립니다. 이 변경은 MVC 패턴에 더 잘 맞추고 프레임워크를 더 예측 가능하고 사용하기 쉽게 만들기 위한 것입니다.

v2에서는 출력 버퍼링이 자체 출력 버퍼를 일관되게 닫지 않는 방식으로 처리되어 [단위 테스트](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42)와 [스트리밍](https://github.com/flightphp/core/issues/413)을 더 어렵게 만들었습니다. 대부분의 사용자에게 이 변경은 실제로 영향을 미치지 않을 수 있습니다. 그러나 콜러블과 컨트롤러 외부(예: 훅)에서 콘텐츠를 에코 출력하는 경우 문제가 발생할 가능성이 큽니다. 훅에서 콘텐츠를 에코 출력하거나 프레임워크가 실제로 실행되기 전에 에코 출력하는 것은 과거에는 작동했을 수 있지만 앞으로는 작동하지 않을 것입니다.

### 문제가 발생할 수 있는 곳
```php
// index.php
require 'vendor/autoload.php';

// just an example
define('START_TIME', microtime(true));

function hello() {
	echo 'Hello World';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// this will actually be fine
	echo '<p>This Hello World phrase was brought to you by the letter "H"</p>';
});

Flight::before('start', function(){
	// things like this will cause an error
	echo '<html><head><title>My Page</title></head><body>';
});

Flight::route('/', function(){
	// this is actually just fine
	echo 'Hello World';

	// This should be just fine as well
	Flight::hello();
});

Flight::after('start', function(){
	// this will cause an error
	echo '<div>Your page loaded in '.(microtime(true) - START_TIME).' seconds</div></body></html>';
});
```

### v2 렌더링 동작 켜기

기존 코드를 v3와 작동하도록 재작성하지 않고 그대로 유지할 수 있나요? 네, 가능합니다! `flight.v2.output_buffering` 구성 옵션을 `true`로 설정하여 v2 렌더링 동작을 켤 수 있습니다. 이는 기존 렌더링 동작을 계속 사용할 수 있게 해주지만, 앞으로는 이를 수정하는 것이 권장됩니다. 프레임워크의 v4에서는 이 기능이 제거될 것입니다.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// Now this will be just fine
	echo '<html><head><title>My Page</title></head><body>';
});

// more code 
```

## 디스패처 변경 사항

_v3.7.0_

`Dispatcher`의 정적 메서드를 직접 호출한 경우, 예를 들어 `Dispatcher::invokeMethod()`, `Dispatcher::execute()` 등이라면 코드를 업데이트하여 이러한 메서드를 직접 호출하지 않도록 해야 합니다. `Dispatcher`는 의존성 주입 컨테이너를 더 쉽게 사용할 수 있도록 더 객체 지향적으로 변환되었습니다. Dispatcher와 유사하게 메서드를 호출해야 한다면 `$result = $class->$method(...$params);`나 `call_user_func_array()`를 수동으로 사용할 수 있습니다.

## `halt()` `stop()` `redirect()` 및 `error()` 변경 사항

_v3.10.0_

3.10.0 이전의 기본 동작은 헤더와 응답 본문을 모두 지우는 것이었습니다. 이는 응답 본지만 지우도록 변경되었습니다. 헤더도 지워야 한다면 `Flight::response()->clear()`를 사용할 수 있습니다.