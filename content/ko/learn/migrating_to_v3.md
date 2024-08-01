# v3로 이전하기

대부분의 경우 하위 호환성이 유지되었지만, v2에서 v3로 이전할 때 알아야 할 몇 가지 변경 사항이 있습니다.

## 출력 버퍼링 동작 (3.5.0)

[출력 버퍼링](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php)은 PHP 스크립트에서 생성된 출력물이 클라이언트로 전송되기 전에 PHP 내부 버퍼에 저장되는 프로세스입니다. 이를 통해 클라이언트로 전송되기 전에 출력물을 수정할 수 있습니다.

MVC 애플리케이션에서 컨트롤러는 "매니저"이며 뷰의 동작을 관리합니다. 컨트롤러 외부에서 출력물이 생성되는 것(또는 Flight의 경우 때로는 익명 함수 내에)는 MVC 패턴을 깨뜨립니다. 이 변경은 MVC 패턴에 더 일치하도록 하고 프레임워크를 더 예측 가능하고 사용하기 쉽도록 만드는 것입니다.

v2에서 출력 버퍼링은 자체 출력 버퍼를 일관되게 닫지 않는 방식으로 처리되었으며, 이는 [단위 테스트](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42)와 [스트리밍](https://github.com/flightphp/core/issues/413)을 더 어렵게 만들었습니다. 대부분의 사용자에게는 이 변경이 실제로 영향을 미치지 않을 수 있습니다. 그러나 콜러블 및 컨트롤러 외부에서 콘텐츠를 에코하는 경우(예: 훅 내에서) 문제가 발생할 수 있습니다. 훅에서 콘텐츠를 에코하거나 프레임워크가 실제로 실행되기 전에 콘텐츠를 에코하는 것이 과거에는 작동했을 수 있지만 앞으로는 작동하지 않을 것입니다.

### 문제가 발생할 수 있는 곳
```php
// index.php
require 'vendor/autoload.php';

// 예시일 뿐
define('START_TIME', microtime(true));

function hello() {
	echo 'Hello World';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// 실제로 이것은 문제가 없을 것입니다
	echo '<p>This Hello World phrase was brought to you by the letter "H"</p>';
});

Flight::before('start', function(){
	// 이러한 것들은 오류를 일으킬 것입니다
	echo '<html><head><title>My Page</title></head><body>';
});

Flight::route('/', function(){
	// 실제로 이것은 괜찮습니다
	echo 'Hello World';

	// 이것도 아주 괜찮을 것입니다
	Flight::hello();
});

Flight::after('start', function(){
	// 이것은 오류를 일으킬 것입니다
	echo '<div>Your page loaded in '.(microtime(true) - START_TIME).' seconds</div></body></html>';
});
```

### v2 렌더링 동작 활성화

v3로 만들어진 코드를 다시 작성하지 않고 이전 방식을 계속 사용할 수 있을까요? 네, 가능합니다! `flight.v2.output_buffering` 구성 옵션을 `true`로 설정하여 v2 렌더링 동작을 활성화할 수 있습니다. 이를 통해 이전 렌더링 동작을 계속 사용할 수 있지만 앞으로 수정하는 것이 좋습니다. 프레임워크의 v4에서는 이것이 제거될 것입니다.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// 이제 이것은 아주 잘 작동할 것입니다
	echo '<html><head><title>My Page</title></head><body>';
});

// 더 많은 코드
```

## Dispatcher 변경 사항 (3.7.0)

`Dispatcher::invokeMethod()`, `Dispatcher::execute()` 등과 같은 `Dispatcher`의 정적 메소드를 직접 호출했다면 코드를 업데이트하여 이러한 메소드를 직접 호출하지 않아야 합니다. `Dispatcher`는 보다 객체 지향적으로 변환되어 의존성 주입 컨테이너를 보다 쉽게 사용할 수 있도록 변경되었습니다. Dispatcher처럼 메소드를 호출해야 하는 경우에는 수동으로 `$result = $class->$method(...$params);` 또는 `call_user_func_array()`와 같은 것을 사용할 수 있습니다.

## `halt()`, `stop()`, `redirect()`, `error()` 변경 사항 (3.10.0)

3.10.0 이전의 기본 동작은 헤더와 응답 본문을 모두 지우는 것이었습니다. 이것은 응답 본문만 지우도록 변경되었습니다. 헤더도 지우려면 `Flight::response()->clear()`를 사용할 수 있습니다.