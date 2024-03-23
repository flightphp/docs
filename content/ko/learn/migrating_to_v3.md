
# v3로 이전하기

거의 대부분 역 호환성이 유지되었지만, v2에서 v3로 이전할 때 알아야 할 몇 가지 변경 사항이 있습니다.

## 출력 버퍼링 동작 (3.5.0)

[출력 버퍼링](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php)은 PHP 스크립트에서 생성된 출력물이 클라이언트에 전송되기 전에 버퍼(내부 PHP)에 저장되는 과정입니다. 이를 통해 클라이언트에 전송되기 전에 출력물을 수정할 수 있습니다.

MVC 애플리케이션에서 Controller가 "관리자"이며 뷰가 하는 일을 관리합니다. 컨트롤러 바깥에서(또는 Flight의 경우에는 때로는 익명 함수 안에서) 생성된 출력물은 MVC 패턴을 깨뜨립니다. 이 변경은 MVC 패턴과 더 일치하도록 하고 프레임워크를 예측 가능하고 사용하기 쉽게 만드는 것입니다.

v2에서는 출력 버퍼링이 자체 출력 버퍼를 일관되게 닫지 않는 방식으로 처리되어 [유닛 테스트](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) 및 [스트리밍](https://github.com/flightphp/core/issues/413)이 더 어려워졌습니다. 대부분의 사용자에게는 이 변경이 실제로 영향을 미칠 수도 있고 그렇지 않을 수도 있습니다. 그러나 호출 가능한 함수 및 컨트롤러 바깥에서 콘텐츠를 echo하는 경우(예: 후크에서) 문제가 발생할 수 있습니다. 후크에서 콘텐츠를 echo하고 프레임워크가 실제로 실행되기 전에 이전에 작동했을 수도 있지만, 앞으로는 작동하지 않을 것입니다.

### 문제가 발생할 수 있는 경우
```php
// index.php
require 'vendor/autoload.php';

// 예시일 뿐입니다
define('START_TIME', microtime(true));

function hello() {
	echo 'Hello World';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// 이 부분은 실제로 괜찮습니다
	echo '<p>This Hello World phrase was brought to you by the letter "H"</p>';
});

Flight::before('start', function(){
	// 이런 것들은 오류를 발생시킵니다
	echo '<html><head><title>My Page</title></head><body>';
});

Flight::route('/', function(){
	// 이 부분은 실제로 괜찮습니다
	echo 'Hello World';

	// 이것도 아주 괜찮습니다
	Flight::hello();
});

Flight::after('start', function(){
	// 이것이 오류를 발생시킵니다
	echo '<div>Your page loaded in '.(microtime(true) - START_TIME).' seconds</div></body></html>';
});
```

### v2 렌더링 동작 켜기

기존 코드를 재작성하지 않고 v3에서 작동하도록 만들기 위해 이전 방법으로 유지할 수 있습니까? 네, 가능합니다! `flight.v2.output_buffering` 구성 옵션을 `true`로 설정하여 v2 렌더링 동작을 켤 수 있습니다. 이를 통해 기존의 렌더링 동작을 계속 사용할 수 있지만, 앞으로 수정하는 것이 좋습니다. 프레임워크의 v4에서는 이 부분이 제거될 것입니다.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// 이제 이 부분은 괜찮아질 것입니다
	echo '<html><head><title>My Page</title></head><body>';
});

// 더 많은 코드 
```

## 디스패처 변경 사항 (3.7.0)

`Dispatcher::invokeMethod()`, `Dispatcher::execute()` 등과 같이 `Dispatcher`의 정적 메서드를 직접 호출했다면 해당 메서드를 직접 호출하지 않도록 코드를 업데이트해야 합니다. `Dispatcher`는 더 객체 지향적으로 변환되어 DI(Dependency Injection) 컨테이너를 더 쉽게 사용할 수 있게 되었습니다. Dispatcher가 했던 것과 유사한 방식으로 메서드를 호출해야 하는 경우 `$result = $class->$method(...$params);` 또는 `call_user_func_array()`와 같은 것을 수동으로 사용할 수 있습니다.