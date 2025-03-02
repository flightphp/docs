# 문제 해결

이 페이지는 Flight를 사용할 때 마주칠 수 있는 일반적인 문제를 해결하는 데 도움이 될 것입니다.

## 일반적인 문제

### 404 Not Found 또는 예기치 않은 경로 동작

404 Not Found 오류를 보고 있다면(하지만 정말 있고 오타가 아니라고 맹세한다면) 실제로는 route 끝점에서 값을 반환하는 것보다 그냥 출력하는 것이 문제가 될 수 있습니다. 그 이유는 의도적이지만 개발자들에겐 뜻밖의 문제가 될 수 있습니다.

```php

Flight::route('/hello', function(){
	// 이것이 404 Not Found 오류를 일으킬 수 있음
	return 'Hello World';
});

// 아마 원하던 것
Flight::route('/hello', function(){
	echo 'Hello World';
});

```

이 이유는 라우터에 내장된 특별한 메커니즘 때문입니다. 이 메커니즘은 반환 값을 다음 route로 "이동"으로 처리합니다. 이 동작은 [Routing](/learn/routing#passing) 섹션에서 문서화된 대로 확인할 수 있습니다.

### 클래스를 찾을 수 없음 (자동로딩이 작동하지 않음)

이 문제가 발생하지 않는 이유는 몇 가지가 있을 수 있습니다. 아래에는 일부 예가 나와 있지만 반드시 [autoloading](/learn/autoloading) 섹션도 확인해야 합니다.

#### 잘못된 파일 이름
가장 일반적인 이유는 클래스 이름이 파일 이름과 일치하지 않는 경우입니다.

`MyClass`라는 클래스가 있다면 파일 이름은 `MyClass.php`이어야 합니다. `MyClass`라는 클래스가 있고 파일 이름이 `myclass.php`인 경우에는 오토로더가 해당 파일을 찾지 못할 것입니다.

#### 잘못된 네임스페이스
네임스페이스를 사용하는 경우 네임스페이스는 디렉토리 구조와 일치해야 합니다.

```php
// 코드

// 만약 MyController가 app/controllers 디렉토리에 있고 네임스페이스가 있는 경우
// 이 방식은 작동하지 않습니다.
Flight::route('/hello', 'MyController->hello');

// 다음 중 하나를 선택해야 합니다
Flight::route('/hello', 'app\controllers\MyController->hello');
// 또는 위쪽에 use 문이 있는 경우

use app\controllers\MyController;

Flight::route('/hello', [ MyController::class, 'hello' ]);
// 또한 다음과 같이 작성할 수 있음
Flight::route('/hello', MyController::class.'->hello');
// 그리고...
Flight::route('/hello', [ 'app\controllers\MyController', 'hello' ]);
```

#### `path()`가 정의되지 않음

스켈레톤 앱에서 이 기능은 `config.php` 파일 내에서 정의되어 있지만 클래스를 찾기 위해서는 `path()` 메소드가 정의되어 있는지 확인해야 합니다(아마도 디렉토리 루트에). 

```php

// 오토로더에 경로 추가
Flight::path(__DIR__.'/../');

```