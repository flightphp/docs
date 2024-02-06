Tracy Flight Panel Extensions
=====

Flight를 더 풍부하게 활용할 수 있는 확장 세트입니다.

- Flight - 모든 Flight 변수를 분석합니다.
- Database - 페이지에서 실행된 모든 쿼리를 분석합니다 (데이터베이스 연결을 올바르게 초기화한 경우)
- Request - 모든 `$_SERVER` 변수를 분석하고 전역 페이로드를 검사합니다 (`$_GET`, `$_POST`, `$_FILES`)
- Session - 세션이 활성화된 경우 모든 `$_SESSION` 변수를 분석합니다.

이것은 패널입니다

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

그리고 각 패널은 응용 프로그램에 대한 매우 유용한 정보를 표시합니다!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

설치
-------
`composer require flightphp/tracy-extensions --dev`를 실행하면 됩니다.

구성
-------
시작하기 위해 할 구성은 매우 적습니다. 이를 시작하기 전에 Tracy 디버거를 초기화해야 합니다. [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// 부트스트랩 코드
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT)으로 환경을 지정해야 할 수도 있습니다.

// 앱에서 데이터베이스 연결을 사용하는 경우
// 개발 단계에서만 사용해야 하는 필수 PDO 래퍼가 있습니다 (프로덕션에서는 사용하지 마십시오!)
// 일반 PDO 연결과 동일한 매개변수를 가지고 있습니다.
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// 또는 Flight 프레임워크에 이것을 첨부하는 경우
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// 이제 쿼리를 실행할 때마다 시간, 쿼리 및 매개변수를 포착할 것입니다.

// 이것이 점을 연결합니다
if(Debugger::$showBar === true) {
	new TracyExtensionLoader(Flight::app());
}

// 더 많은 코드

Flight::start();
```