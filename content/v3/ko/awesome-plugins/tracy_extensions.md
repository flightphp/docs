Tracy Flight 패널 확장
=====

이것은 Flight 작업을 조금 더 풍부하게 만들기 위한 확장 세트입니다.

- Flight - 모든 Flight 변수를 분석합니다.
- Database - 페이지에서 실행된 모든 쿼리를 분석합니다(데이터베이스 연결을 올바르게 시작한 경우).
- Request - 모든 `$_SERVER` 변수를 분석하고 모든 글로벌 페이로드(`$_GET`, `$_POST`, `$_FILES`)를 검사합니다.
- Session - 세션이 활성화된 경우 모든 `$_SESSION` 변수를 분석합니다.

이것은 패널입니다.

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

각 패널은 애플리케이션에 대한 매우 유용한 정보를 표시합니다!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

[여기](https://github.com/flightphp/tracy-extensions)를 클릭하여 코드를 보세요.

설치
-------
`composer require flightphp/tracy-extensions --dev`을 실행하면 바로 시작할 수 있습니다!

구성
-------
시작하려면 거의 구성할 필요가 없습니다. 이것을 사용하기 전에 Tracy 디버거를 시작해야 합니다 [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// 부트스트랩 코드
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// 환경을 지정해야 할 수 있습니다 Debugger::enable(Debugger::DEVELOPMENT)으로

// 앱에서 데이터베이스 연결을 사용하는 경우,
// 개발에서만 사용하도록 필요한 PDO 래퍼(프로덕션에서는 사용하지 마세요!)
// 일반 PDO 연결과 동일한 매개변수를 가집니다
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// 또는 Flight 프레임워크에 연결하는 경우
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// 이제 쿼리를 실행할 때마다 시간, 쿼리 및 매개변수를 캡처합니다

// 이것이 연결을 만듭니다
if(Debugger::$showBar === true) {
	// 이것이 false여야 합니다. 아니면 Tracy가 렌더링할 수 없어요 :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// 더 많은 코드

Flight::start();
```

## 추가 구성

### 세션 데이터
커스텀 세션 핸들러(예: ghostff/session)를 사용하는 경우, Tracy에 세션 데이터 배열을 전달하면 자동으로 출력됩니다. `TracyExtensionLoader` 생성자의 두 번째 매개변수에서 `session_data` 키로 전달합니다.

```php

use Ghostff\Session\Session;
// 또는 flight\Session를 사용하세요;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// 이것이 false여야 합니다. 아니면 Tracy가 렌더링할 수 없어요 :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// 라우트 및 다른 것들...

Flight::start();
```

### Latte

프로젝트에 Latte를 설치한 경우, Latte 패널을 사용하여 템플릿을 분석할 수 있습니다. Latte 인스턴스를 `TracyExtensionLoader` 생성자의 두 번째 매개변수에서 `latte` 키로 전달합니다.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// 여기서 Latte 패널을 Tracy에 추가합니다
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// 이것이 false여야 합니다. 아니면 Tracy가 렌더링할 수 없어요 :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
```