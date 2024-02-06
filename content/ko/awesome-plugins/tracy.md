# 트레이시

트레이시는 플라이트와 함께 사용할 수 있는 놀라운 에러 핸들러입니다. 응용 프로그램을 디버그하는 데 도움이 되는 여러 패널이 있습니다. 또한 매우 쉽게 확장하여 자체 패널을 추가할 수 있습니다. 플라이트 팀은 [flightphp/tracy-extensions](https://github.com/flightphp/tracy-extensions) 플러그인을 사용하여 플라이트 프로젝트용 몇 가지 패널을 만들었습니다.

## 설치

컴포저로 설치합니다. Tracy는 프로덕션 에러 핸들링 구성 요소가 포함되어 있으므로 실제로 개발 버전없이 설치해야 합니다.

```bash
composer require tracy/tracy
```

## 기본 구성

시작하는 데 도움이 되는 일부 기본 구성 옵션이 있습니다. 자세한 내용은 [트레이시 문서](https://tracy.nette.org/en/configuring)에서 읽을 수 있습니다.

```php

require 'vendor/autoload.php';

use Tracy\Debugger;

// 트레이시 활성화
Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT) // 때로는 명시적으로 할 필요가 있습니다 (문제 해결::생산)
// Debugger::enable('23.75.345.200'); // IP 주소 배열을 제공할 수도 있습니다.

// 여기에 발생한 오류 및 예외가 기록됩니다. 이 디렉토리가 존재하고 쓰기 가능한지 확인하세요.
Debugger::$logDirectory = __DIR__ . '/../log/';
Debugger::$strictMode = true; // 모든 오류 표시
// Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // 사용되지 않은 공지 제외 모든 오류
if (Debugger::$showBar) {
    $app->set('flight.content_length', false); // 디버그 바가 표시되는 경우, 내용 길이를 플라이트가 설정할 수 없습니다.

	// 이것은 Tracy Extension for Flight에 특정한 내용입니다. 해당 내용을 포함했다면,
	// 그렇지 않다면 이 부분을 주석 처리하십시오.
	new TracyExtensionLoader($app);
}
```

## 유용한 팁

코드를 디버깅할 때 데이터를 출력하는 데 매우 유용한 함수가 있습니다.

- `bdump($var)` - 변수를 별도의 패널에 덤프합니다.
- `dumpe($var)` - 변수를 덤프한 후 즉시 종료합니다.