Tracy Flight Panel Extensions
=====

Flight와 작업하는 것을 조금 더 풍부하게 만들기 위한 확장 패널 세트입니다.

- Flight - 모든 Flight 변수를 분석합니다.
- Database - 페이지에서 실행된 모든 쿼리를 분석합니다(데이터베이스 연결을 올바르게 초기화하는 경우).
- Request - 모든 `$_SERVER` 변수를 분석하고 전역 페이로드(`$_GET`, `$_POST`, `$_FILES`)를 조사합니다.
- Session - 세션이 활성화된 경우 모든 `$_SESSION` 변수를 분석합니다.

이것이 패널입니다

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

그리고 각 패널은 응용 프로그램에 대한 매우 유용한 정보를 표시합니다!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

설치
-------
`composer require flightphp/tracy-extensions --dev`을 실행하고 작업을 시작해보세요!

구성
-------
이를 시작하려면 수행해야할 구성이 매우 적습니다. 이를 사용하기 전에 Tracy 디버거를 초기화해야 합니다 [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// 부트스트랩 코드
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Debugger::DEVELOPMENT로 환경을 지정해야 할 수도 있습니다.

// 앱에서 데이터베이스 연결을 사용하는 경우, 개발 환경에서 사용해야하는 
// 필수 PDO 래퍼가 있습니다(제발 프로덕션에서는 사용하지 마세요!)
// 이것은 일반 PDO 연결과 동일한 매개변수를 갖습니다
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// 또는 Flight 프레임워크에 이를 첨부하는 경우
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// 이제 쿼리를 할 때마다 시간, 쿼리 및 매개변수를 캡처합니다

// 이것이 점을 이어줍니다
if(Debugger::$showBar === true) {
	new TracyExtensionLoader(Flight::app());
}

// 더 많은 코드

Flight::start();
```