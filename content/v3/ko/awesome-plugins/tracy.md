# 트레이시 (Tracy)

트레이시는 Flight와 함께 사용할 수 있는 놀라운 에러 핸들러입니다. 애플리케이션을 디버깅하는 데 도움이 되는 여러 패널을 갖고 있습니다. 또한 손쉽게 확장하여 사용자 정의 패널을 추가할 수 있습니다. Flight 팀은 [flightphp/tracy-extensions](https://github.com/flightphp/tracy-extensions) 플러그인을 통해 Flight 프로젝트를 위해 몇 가지 패널을 특별히 만들었습니다.

## 설치

컴포저로 설치합니다. 실제로 이것은 Tracy가 프로덕션 에러 핸들링 구성 요소와 함께 제공되므로 개발 버전 없이 설치하는 것이 좋습니다.

```bash
composer require tracy/tracy
```

## 기본 구성

시작하려면 몇 가지 기본 구성 옵션이 있습니다. 더 자세한 내용은 [Tracy 문서](https://tracy.nette.org/en/configuring)를 참조하십시오.

```php
require 'vendor/autoload.php';

use Tracy\Debugger;

// Tracy를 활성화합니다
Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT) // 경우에 따라 명시적으로 설정해야 할 수도 있습니다 (또한 Debugger::PRODUCTION)
// Debugger::enable('23.75.345.200'); // IP 주소의 배열을 제공할 수도 있습니다

// 여기에 오류와 예외가 기록됩니다. 이 디렉터리가 존재하고 쓰기 가능한지 확인하십시오.
Debugger::$logDirectory = __DIR__ . '/../log/';
Debugger::$strictMode = true; // 모든 오류 표시
// Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // 사용되지 않은 공지를 제외한 모든 오류
if (Debugger::$showBar) {
    $app->set('flight.content_length', false); // Debugger 표시줄이 표시되는 경우 Flight에서 내용 길이를 설정할 수 없습니다

	// 이것은 Tracy Extension for Flight에 특화된 사항이며, 해당 항목을 포함했을 경우만 활성화합니다
	// 그렇지 않으면 주석 처리하십시오.
	new TracyExtensionLoader($app);
}
```

## 유용한 팁

코드를 디버깅하는 경우 데이터를 출력하기 위한 매우 유용한 함수들이 있습니다.

- `bdump($var)` - 이는 변수를 별도의 패널에 트레이시 바에 덤프합니다.
- `dumpe($var)` - 이는 변수를 덤프하고 즉시 종료합니다.