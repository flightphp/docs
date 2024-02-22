# v3로 이전하기

대부분의 경우 하위 호환성이 유지되었지만, v2에서 v3로 마이그레이션할 때 알아야 할 몇 가지 변경 사항이 있습니다.

## 출력 버퍼링

[출력 버퍼링](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php)은 PHP 스크립트에서 생성된 출력물이 클라이언트로 전송되기 전에 PHP 내부의 버퍼에 저장되는 프로세스입니다. 이를 통해 클라이언트로 전송되기 전에 출력물을 수정할 수 있습니다.

MVC 애플리케이션에서 컨트롤러는 "관리자"이며 뷰의 동작을 관리합니다. 컨트롤러 외부(또는 Flight의 경우 때로는 익명 함수)에서 생성된 출력물은 MVC 패턴을 깨뜨립니다. 이 변경은 MVC 패턴과 더 일치하도록 하고 프레임워크를 더 예측 가능하고 사용하기 쉽게 만들기 위한 것입니다.

v2에서 출력 버퍼링은 자체 출력 버퍼를 일관되게 닫지 않고 처리되어 [유닛 테스트](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) 및 [스트리밍](https://github.com/flightphp/core/issues/413)이 더 어려워졌습니다. 대부분의 사용자에게는이 변경이 실제로 영향을 미치지 않을 수 있습니다. 그러나 호출 가능 함수와 컨트롤러 외부에서 콘텐츠를 에코하는 경우(예: 후크에서) 문제가 발생할 수 있습니다. 후크에서 콘텐츠를 에코하고 프레임워크가 실제로 실행되기 전에 이전에 작동했을 수 있지만 앞으로는 작동하지 않을 것입니다.

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

### v2 렌더링 동작 활성화

v3로 변경하지 않고 이전 코드를 그대로 유지하고 싶으신가요? 네, 가능합니다! `flight.v2.output_buffering` 설정 옵션을 `true`로 설정하여 v2 렌더링 동작을 활성화할 수 있습니다. 이렇게 하면 이전 렌더링 동작을 계속 사용할 수 있지만 앞으로 고쳐야 하는 것이 권장됩니다. 프레임워크의 v4에서는 이 기능이 제거될 것입니다.

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