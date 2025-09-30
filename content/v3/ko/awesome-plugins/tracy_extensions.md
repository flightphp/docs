# Tracy Flight 패널 확장

이것은 Flight 작업을 더 풍부하게 만드는 확장 세트입니다.

- Flight - 모든 Flight 변수를 분석합니다.
- 데이터베이스 - 페이지에서 실행된 모든 쿼리를 분석합니다 (데이터베이스 연결을 올바르게 시작한 경우)
- 요청 - 모든 `$_SERVER` 변수를 분석하고 모든 전역 페이로드를 검사합니다 (`$_GET`, `$_POST`, `$_FILES`)
- 세션 - 세션이 활성 상태인 경우 모든 `$_SESSION` 변수를 분석합니다.

이것이 패널입니다

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

그리고 각 패널은 애플리케이션에 대한 매우 유용한 정보를 표시합니다!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

코드 보기 [여기](https://github.com/flightphp/tracy-extensions)를 클릭하세요.

## 설치
-------
`composer require flightphp/tracy-extensions --dev`를 실행하면 시작할 수 있습니다!

## 구성
-------
이것을 시작하는 데 필요한 구성은 거의 없습니다. 이 [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide)를 사용하기 전에 Tracy 디버거를 시작해야 합니다:

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// 부트스트랩 코드
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// 환경을 지정해야 할 수 있습니다: Debugger::enable(Debugger::DEVELOPMENT)

// 앱에서 데이터베이스 연결을 사용하는 경우, 
// 개발에서만 사용해야 하는 필수 PDO 래퍼가 있습니다 (프로덕션에서는 사용하지 마세요!)
// 일반 PDO 연결과 동일한 매개변수를 가집니다
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// 또는 Flight 프레임워크에 연결하는 경우
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// 이제 쿼리를 실행할 때마다 시간, 쿼리 및 매개변수를 캡처합니다

// 이것이 연결을 연결합니다
if(Debugger::$showBar === true) {
	// 이것이 false여야 Tracy가 실제로 렌더링할 수 있습니다 :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// 더 많은 코드

Flight::start();
```

## 추가 구성

### 세션 데이터
커스텀 세션 핸들러(예: ghostff/session)를 사용하는 경우, 세션 데이터를 Tracy에 배열로 전달할 수 있으며 자동으로 출력됩니다. `TracyExtensionLoader` 생성자의 두 번째 매개변수의 `session_data` 키로 전달합니다.

```php

use Ghostff\Session\Session;
// 또는 flight\Session 사용;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// 이것이 false여야 Tracy가 실제로 렌더링할 수 있습니다 :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// 라우트 및 기타 사항...

Flight::start();
```

### Latte

_이 섹션에는 PHP 8.1+가 필요합니다._

프로젝트에 Latte가 설치된 경우, Tracy는 템플릿을 분석하기 위해 Latte와 네이티브 통합을 제공합니다. Latte 인스턴스에 확장을 등록하기만 하면 됩니다.

```php

require 'vendor/autoload.php';

$app = Flight::app();

$app->map('render', function($template, $data, $block = null) {
	$latte = new Latte\Engine;

	// 기타 구성...

	// Tracy 디버그 바로만 확장을 추가합니다
	if(Debugger::$showBar === true) {
		// 여기에 Latte 패널을 Tracy에 추가합니다
		$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
	}

	$latte->render($template, $data, $block);
});
```