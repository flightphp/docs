## Tracy 체크 패널 확장

이것은 Flight와 함께 작업하는 것을 좀 더 풍부하게 만들기 위한 일련의 확장입니다.

- Flight - 모든 Flight 변수를 분석합니다.
- Database - 페이지에서 실행된 모든 쿼리를 분석합니다 (데이터베이스 연결을 올바르게 초기화했을 경우)
- Request - 모든 `$_SERVER` 변수를 분석하고 모든 전역 페이로드를 검토합니다 (`$_GET`, `$_POST`, `$_FILES`)
- Session - 세션이 활성화된 경우 모든 `$_SESSION` 변수를 분석합니다.

이것은 패널입니다

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

그리고 각 패널은 응용 프로그램에 대해 매우 유용한 정보를 표시합니다!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

[여기](https://github.com/flightphp/tracy-extensions)를 클릭하여 코드를 확인하세요.

설치
-------
`composer require flightphp/tracy-extensions --dev`를 실행하면 됩니다!

구성
-------
시작하려면 할 필요가 있는 구성은 매우 적습니다. 이를 사용하기 전에 Tracy 디버거를 초기화해야 합니다. [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide)를 사용하세요:

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// 부트스트랩 코드
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT)으로 환경을 지정할 필요가 있을 수 있습니다.

// 앱에서 데이터베이스 연결을 사용하는 경우
// 개발 환경에서만 사용하는 필수 PDO 래퍼가 있습니다 (제발 프로덕션에서는 사용하지 마세요!)
// 이것은 일반 PDO 연결과 동일한 매개변수를 갖습니다
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// 또는 Flight framework에 이를 연결할 경우
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// 이제 쿼리를 실행할 때마다 시간, 쿼리 및 매개변수를 캡쳐합니다

// 이는 다른 것들을 연결합니다
if(Debugger::$showBar === true) {
	// 이것은 false여야 Tracy가 실제로 렌더링하지 못합니다 :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// 더 많은 코드

Flight::start();
```

## 추가 구성

### 세션 데이터
사용자 지정 세션 핸들러(예: ghostff/session)가 있는 경우 Tracy에 어떤 세션 데이터 배열이든 전달하여 자동으로 출력할 수 있습니다. `TracyExtensionLoader` 생성자의 두 번째 매개변수에서 `session_data` 키로 전달합니다.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// 이것은 false여야 Tracy가 실제로 렌더링하지 못합니다 :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// 라우트 및 기타 사항...

Flight::start();
```

### Latte

프로젝트에 Latte가 설치되어 있는 경우 Latte 패널을 사용하여 템플릿을 분석할 수 있습니다. 두 번째 매개변수의 `latte` 키로 `TracyExtensionLoader` 생성자에 Latte 인스턴스를 전달할 수 있습니다.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// 이곳에 Latte 패널을 Tracy에 추가합니다
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// 이것은 false여야 Tracy가 실제로 렌더링하지 못합니다 :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
