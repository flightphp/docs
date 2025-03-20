Tracy Flight 패널 확장
=====

이것은 Flight와 함께 작업하는 것을 조금 더 풍부하게 만드는 확장 세트입니다.

- Flight - 모든 Flight 변수를 분석합니다.
- Database - 페이지에서 실행된 모든 쿼리를 분석합니다 (데이터베이스 연결을 올바르게 시작한 경우).
- Request - 모든 `$_SERVER` 변수를 분석하고 모든 전역 페이로드(`$_GET`, `$_POST`, `$_FILES`)를 검사합니다.
- Session - 세션이 활성 상태인 경우 모든 `$_SESSION` 변수를 분석합니다.

이것이 패널입니다.

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

각 패널은 애플리케이션에 대한 매우 유용한 정보를 표시합니다!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

코드를 보려면 [여기](https://github.com/flightphp/tracy-extensions)를 클릭하세요.

설치
-------
`composer require flightphp/tracy-extensions --dev`를 실행하면 준비가 완료됩니다!

구성
-------
시작하려면 구성해야 할 사항이 거의 없습니다. 사용하기 전에 Tracy 디버거를 시작해야 합니다 [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// 부트스트랩 코드
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT)로 환경을 지정해야 할 수도 있습니다.

// 앱에서 데이터베이스 연결을 사용하는 경우, 
// 오직 개발 환경에서만 사용할 수 있도록 필수 PDO 래퍼가 필요합니다 (생산 환경에서는 사용하지 마세요!)
// 일반 PDO 연결과 동일한 매개변수를 가집니다.
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// 또는 이것을 Flight 프레임워크에 연결하면
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// 이제 쿼리를 만들 때마다 시간, 쿼리 및 매개변수를 캡처합니다.

// 이를 연결합니다.
if(Debugger::$showBar === true) {
	// 이 값은 false여야 합니다. 그렇지 않으면 Tracy가 실제로 렌더링할 수 없습니다 :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// 더 많은 코드

Flight::start();
```

## 추가 구성

### 세션 데이터
사용자 정의 세션 핸들러(예: ghostff/session)가 있는 경우, Tracy에 세션 데이터 배열을 전달하면 자동으로 출력됩니다. `TracyExtensionLoader` 생성자의 두 번째 매개변수에 `session_data` 키와 함께 전달합니다.

```php

use Ghostff\Session\Session;
// 또는 flight\Session을 사용하세요.

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// 이 값은 false여야 합니다. 그렇지 않으면 Tracy가 실제로 렌더링할 수 없습니다 :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// 라우트 및 기타 사항...

Flight::start();
```

### Latte

프로젝트에 Latte가 설치되어 있는 경우, Latte 패널을 사용하여 템플릿을 분석할 수 있습니다. 생성자의 두 번째 매개변수에 `latte` 키와 함께 Latte 인스턴스를 전달할 수 있습니다.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// 여기서 Latte 패널을 Tracy에 추가합니다.
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// 이 값은 false여야 합니다. 그렇지 않으면 Tracy가 실제로 렌더링할 수 없습니다 :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
```