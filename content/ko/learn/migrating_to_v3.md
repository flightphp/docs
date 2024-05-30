# v3로 이전하기

백워드 호환성은 대부분 유지되었지만, v2에서 v3로 마이그레이션할 때 알아야 할 몇 가지 변경 사항이 있습니다.

## 출력 버퍼링 동작 (3.5.0)

[출력 버퍼링](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php)은 PHP 스크립트에 의해 생성된 출력물이 클라이언트에 전송되기 전에 PHP 내부 버퍼에 저장되는 프로세스입니다. 이를 통해 클라이언트에 전송되기 전에 출력물을 수정할 수 있습니다.

MVC 응용 프로그램에서 Controller는 "관리자"이며 뷰가 하는 일을 관리합니다. Controller 외부에서 출력물이 생성되는 것은 MVC 패턴을 깨뜨립니다. 이 변경 사항은 MVC 패턴에 더 부합하고 프레임워크를 더 예측 가능하고 사용하기 쉽게 만들기 위한 것입니다.

v2에서 출력 버퍼링은 자체 출력 버퍼를 일관되게 닫지 않았고 [단위 테스트](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42)와 [스트리밍](https://github.com/flightphp/core/issues/413)을 더 어렵게 만들었습니다. 대부분의 사용자에게는이 변경이 실제로 영향을 미치지 않을 수 있습니다. 그러나 당신이 호출 가능한 함수 및 컨트롤러 외부에서 콘텐츠를 echo하는 경우 (예: 후크에서), 문제가 발생할 수 있습니다. 후크에서 콘텐츠를 echo하고 프레임워크가 실제로 실행되기 전에 콘텐츠를 이전에 작동했을 수 있지만 앞으로는 작동하지 않을 것입니다.

### 문제가 발생할 수 있는 위치
```php
// index.php
require 'vendor/autoload.php';

// 간단한 예제
define('START_TIME', microtime(true));

function hello() {
	echo 'Hello World';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// 이것은 실제로 괜찮습니다
	echo '<p>This Hello World phrase was brought to you by the letter "H"</p>';
});

Flight::before('start', function(){
	// 이와 같은 것들은 오류를 일으킬 것입니다
	echo '<html><head><title>My Page</title></head><body>';
});

Flight::route('/', function(){
	// 이것은 실제로 문제가 없습니다
	echo 'Hello World';

	// 이것도 괜찮습니다
	Flight::hello();
});

Flight::after('start', function(){
	// 이것은 오류를 일으키겠져
	echo '<div>Your page loaded in '.(microtime(true) - START_TIME).' seconds</div></body></html>';
});
```

### v2 렌더링 동작 활성화

v3로 작동하도록 기존 코드를 완전히 다시 작성하지 않고도 기존 렌더링 동작을 유지할 수 있을까요? 네, 가능합니다! `flight.v2.output_buffering` 구성 옵션을 `true`로 설정하여 v2 렌더링 동작을 활성화할 수 있습니다. 이를 통해 기존 렌더링 동작을 계속 사용할 수 있지만 앞으로 수정하는 것이 권장됩니다. 프레임워크의 v4에서는이 기능이 제거될 것입니다.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// 이제 이것은 완벽히 동작합니다
	echo '<html><head><title>My Page</title></head><body>';
});

// 더 많은 코드
```

## Dispatcher 변경 사항 (3.7.0)

만약 `Dispatcher::invokeMethod()`, `Dispatcher::execute()` 등과 같이 `Dispatcher`의 정적 메소드를 직접 호출하고 있다면, 이러한 메소드를 직접 호출하지 않도록 코드를 업데이트해야 합니다. `Dispatcher`는 더 객체 지향적으로 변환되어 의존성 주입 컨테이너가 더 쉽게 사용될 수 있도록 되었습니다. Dispatcher와 유사한 메소드를 호출해야 하는 경우, 수동으로 `$result = $class->$method(...$params);` 또는 `call_user_func_array()`와 같은 방식을 사용할 수 있습니다.