Tracy Flight Panel 확장 기능
=====

이것은 Flight를 사용하는 것을 좀 더 풍부하게 만들기 위한 확장 기능 세트입니다.

- Flight - 모든 Flight 변수를 분석합니다.
- Database - 페이지에서 실행된 모든 쿼리를 분석합니다 (데이터베이스 연결을 올바르게 초기화한 경우)
- Request - 모든 `$_SERVER` 변수를 분석하고 모든 글로벌 payload를 조사합니다 (`$_GET`, `$_POST`, `$_FILES`)
- Session - 세션이 활성화된 경우 모든 `$_SESSION` 변수를 분석합니다.

이것이 패널입니다

![Flight 바](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

그리고 각 패널은 애플리케이션에 대한 매우 유용한 정보를 표시합니다!

![Flight 데이터](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight 데이터베이스](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight 요청](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

설치
-------
`composer require flightphp/tracy-extensions --dev`를 실행하면 됩니다!

구성
-------
시작하려면 아주 적은 구성이 필요합니다. 이를 시작하기 전에 Tracy 디버거를 초기화해야 합니다 [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// 부트스트랩 코드
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT)으로 환경을 지정해야 할 수도 있습니다

// 앱에서 데이터베이스 연결을 사용하는 경우
// 개발 중에만 사용해야 하는 필수 PDO 래퍼가 있습니다 (상용 제품에서는 사용하지 마십시오!)
// 일반 PDO 연결과 동일한 매개변수를 가지고 있습니다
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// 또는 Flight 프레임워크에 이를 첨부하는 경우
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// 이제 쿼리를 실행할 때마다 시간, 쿼리 및 매개변수가 캡처됩니다

// 이것이 점을 이어주는 것입니다
if(Debugger::$showBar === true) {
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// 더 많은 코드

Flight::start();
```

## 추가 구성

### 세션 데이터
사용자 지정 세션 핸들러(예: ghostff/session)가 있는 경우, Tracy에 어떤 배열의 세션 데이터를 전달하면 자동으로 출력됩니다. `TracyExtensionLoader` 생성자의 두 번째 매개변수에서 `session_data` 키로 전달합니다.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// 라우트 및 기타 작업...

Flight::start();
```

### 라테
프로젝트에 라테가 설치되어 있는 경우, 라테 패널을 사용하여 템플릿을 분석할 수 있습니다. `TracyExtensionLoader` 생성자의 두 번째 매개변수에서 `latte` 키로 LaTte 인스턴스를 전달할 수 있습니다.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
