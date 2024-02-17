# v3로 이전하기

대부분의 경우 역호환성이 유지되었지만, v2에서 v3로 이전할 때 알아야 할 몇 가지 변경 사항이 있습니다.

## 출력 버퍼링

[출력 버퍼링](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php)은 PHP 스크립트에서 생성된 출력이 클라이언트에 전송되기 전에 버퍼(내부 PHP에)에 저장되는 과정을 말합니다. 이를 통해 클라이언트에 전송되기 전에 출력물을 수정할 수 있습니다.

MVC 응용 프로그램에서 Controller는 "관리자"이며 뷰의 작업을 관리합니다. Controller 외부(또는 Flight의 경우 때때로 익명 함수에서) 생성된 출력은 MVC 패턴을 깨뜨립니다. 이 변경 사항은 MVC 패턴과 더 일치하도록하며 더 예측 가능하고 사용하기 쉬운 프레임워크로 만드는 것입니다.

v2에서 출력 버퍼링은 일반적으로 자체 출력 버퍼를 닫지 않았기 때문에 [유닛 테스트](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) 및 [스트리밍](https://github.com/flightphp/core/issues/413)이 더 어려워졌습니다. 대부분의 사용자에게는이 변경이 실제로 영향을 미치지 않을 수 있습니다. 그러나 콜러블 및 컨트롤러 외부에서 콘텐츠를 출력하는 경우(예: 훅에서) 문제가 발생할 수 있습니다. 훅에서 콘텐츠를 출력하고 프레임워크가 실제로 실행되기 전에 이전에 작동했을 수 있지만, 앞으로는 작동하지 않을 것입니다.

### 문제가 발생할 수 있는 위치
```php
// index.php
require 'vendor/autoload.php';

// 예시일 뿐입니다
define('START_TIME', microtime(true));

function hello() {
	echo 'Hello World'; // 여기는 정상
}

Flight::map('hello', 'hello'); // 여기는 정상
Flight::after('hello', function(){
	// 이것은 실제로 정상적으로 작동합니다
	echo '<p>This Hello World phrase was brought to you by the letter "H"</p>';
});

Flight::before('start', function(){
	// 이와 같은 것들은 오류를 일으킬 것입니다
	echo '<html><head><title>My Page</title></head><body>';
});

Flight::route('/', function(){
	// 이것은 실제로 정상
	echo 'Hello World';

	// 이것도 아주 잘 됩니다
	Flight::hello();
});

Flight::after('start', function(){
	// 이것은 오류를 일으킵니다
	echo '<div>Your page loaded in '.(microtime(true) - START_TIME).' seconds</div></body></html>';
});
```

### v2 렌더링 동작 켜기

기존 코드를 재작성하지 않고 v3와 작동하도록 만들 수 있습니까? 네, 가능합니다! `flight.v2.output_buffering` 구성 옵션을 `true`로 설정하여 v2 렌더링 동작을 활성화할 수 있습니다. 이를 통해 이전 렌더링 동작을 계속 사용할 수 있지만, 추후 수정하는 것이 권장됩니다. 프레임워크의 v4에서는 이 옵션이 제거될 것입니다.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// 이제는 정상으로 작동합니다
	echo '<html><head><title>My Page</title></head><body>';
});

// 더 많은 코드
```  