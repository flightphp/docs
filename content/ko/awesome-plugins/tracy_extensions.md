Tracy Flight Panel Extensions
=====

Flight와 함께 작업하는 데 도움이 되는 확장 프로그램 세트입니다.

- Flight - Flight 변수를 모두 분석합니다.
- Database - 페이지에서 실행된 모든 쿼리를 분석합니다 (데이터베이스 연결을 올바르게 초기화한 경우)
- Request - 모든 `$_SERVER` 변수와 모든 전역 데이터 (`$_GET`, `$_POST`, `$_FILES`)를 분석합니다.
- Session - 세션이 활성화된 경우 모든 `$_SESSION` 변수를 분석합니다.

이것은 패널입니다

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

그리고 각 패널은 응용 프로그램에 대한 매우 유용한 정보를 표시합니다!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

설치
-------
`composer require flightphp/tracy-extensions --dev`를 실행하면 됩니다!

구성
-------
시작하기 위해 매우 적은 구성이 필요합니다. [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide)에서 이를 시작하기 전에 Tracy 디버거를 시작해야 합니다.

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// 부트스트랩 코드
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT)를 사용하여 환경을 지정할 수도 있습니다.

// 앱에서 데이터베이스 연결을 사용하는 경우, 
// 개발 환경에서만 사용해야 하는 필수 PDO 래퍼가 있습니다. (운영 환경에서는 사용하지 마십시오!)
// 일반적인 PDO 연결과 동일한 매개변수를 갖고 있습니다.
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// 또는 Flight 프레임워크에 이를 첨부하는 경우
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// 이제 쿼리를 실행할 때마다 시간, 쿼리 및 매개변수를 캡처합니다.

// 이것이 연결점입니다
if(Debugger::$showBar === true) {
	// false 여야 하며 그렇지 않으면 Tracy가 실제로 렌더링할 수 없습니다. :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// 더 많은 코드

Flight::start();
```

## 추가 구성

### 세션 데이터
사용자 지정 세션 핸들러(예: ghostff/session)가 있는 경우 Tracy에 세션 데이터 배열을 전달하여 자동으로 출력할 수 있습니다. `TracyExtensionLoader` 생성자의 두 번째 매개변수의 `session_data` 키로 전달합니다.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// false 여야 하며 그렇지 않으면 Tracy가 실제로 렌더링할 수 없습니다. :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// 라우트 및 기타 작업...

Flight::start();
```

### 라테
프로젝트에 라테(Latte)가 설치된 경우, 라테 패널을 사용하여 템플릿을 분석할 수 있습니다. `TracyExtensionLoader` 생성자의 두 번째 매개변수에서 `latte` 키로 라테 인스턴스를 전달할 수 있습니다.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// 이 곳에서 라테 패널을 Tracy에 추가합니다
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// false 여야 하며 그렇지 않으면 Tracy가 실제로 렌더링할 수 없습니다. :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
