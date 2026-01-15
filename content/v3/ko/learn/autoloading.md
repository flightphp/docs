# Autoloading

## 개요

Autoloading은 PHP에서 클래스들을 로드할 디렉토리 또는 디렉토리들을 지정하는 개념입니다. 이는 `require`나 `include`를 사용하여 클래스들을 로드하는 것보다 훨씬 유익합니다. 또한 Composer 패키지를 사용하는 데 필수적입니다.

## 이해

기본적으로 `Flight`의 모든 클래스는 Composer 덕분에 자동으로 autoload됩니다. 그러나 자신의 클래스들을 autoload하려면 `Flight::path()` 메서드를 사용하여 클래스들을 로드할 디렉토리를 지정할 수 있습니다.

Autoloader를 사용하면 코드를 상당히 단순화할 수 있습니다. 파일 상단에 사용되는 모든 클래스들을 캡처하기 위해 수많은 `include`나 `require` 문으로 시작하는 대신, 클래스들을 동적으로 호출하면 자동으로 포함됩니다.

## 기본 사용법

다음과 같은 디렉토리 트리가 있다고 가정해 보겠습니다:

```text
# 예시 경로
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - 이 프로젝트의 컨트롤러들을 포함
│   ├── translations
│   ├── UTILS - 이 애플리케이션 전용 클래스들을 포함 (나중에 예시를 위해 의도적으로 대문자)
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

이것이 이 문서 사이트의 파일 구조와 동일하다는 것을 눈치채셨을 수 있습니다.

각 디렉토리를 다음과 같이 지정하여 로드할 수 있습니다:

```php

/**
 * public/index.php
 */

// Autoloader에 경로 추가
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// 네임스페이싱 불필요

// 모든 autoload된 클래스들은 Pascal Case(각 단어의 첫 글자 대문자, 공백 없음)로 하는 것을 권장
class MyController {

	public function index() {
		// 무언가 수행
	}
}
```

## 네임스페이스

네임스페이스가 있는 경우, 이를 구현하는 것이 매우 쉽습니다. `Flight::path()` 메서드를 사용하여 애플리케이션의 루트 디렉토리(문서 루트나 `public/` 폴더가 아님)를 지정해야 합니다.

```php

/**
 * public/index.php
 */

// Autoloader에 경로 추가
Flight::path(__DIR__.'/../');
```

이제 컨트롤러가 어떻게 보일지 확인해 보세요. 아래 예시를 보시되, 중요한 정보가 있는 주석에 주의하세요.

```php
/**
 * app/controllers/MyController.php
 */

// 네임스페이스 필수
// 네임스페이스는 디렉토리 구조와 동일
// 네임스페이스는 디렉토리 구조와 동일한 대소문자를 따라야 함
// 네임스페이스와 디렉토리는 언더스코어를 가질 수 없음 (Loader::setV2ClassLoading(false)가 설정되지 않은 한)
namespace app\controllers;

// 모든 autoload된 클래스들은 Pascal Case(각 단어의 첫 글자 대문자, 공백 없음)로 하는 것을 권장
// 3.7.2 버전부터 Loader::setV2ClassLoading(false)를 실행하여 클래스 이름에 Pascal_Snake_Case를 사용할 수 있음
class MyController {

	public function index() {
		// 무언가 수행
	}
}
```

utils 디렉토리의 클래스를 autoload하려면 기본적으로 동일한 작업을 수행합니다:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// 네임스페이스는 디렉토리 구조와 대소문자를 일치시켜야 함 (위 파일 트리에서 UTILS 디렉토리가 대문자임을 유의
//     위 파일 트리와 같이)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// 무언가 수행
	}
}
```

## 클래스 이름의 언더스코어

3.7.2 버전부터 `Loader::setV2ClassLoading(false);`를 실행하여 클래스 이름에 Pascal_Snake_Case를 사용할 수 있습니다. 
이렇게 하면 클래스 이름에 언더스코어를 사용할 수 있습니다. 
이는 권장되지 않지만, 필요로 하는 사람들을 위해 제공됩니다.

```php
use flight\core\Loader;

/**
 * public/index.php
 */

// Autoloader에 경로 추가
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');
Loader::setV2ClassLoading(false);

/**
 * app/controllers/My_Controller.php
 */

// 네임스페이싱 불필요

class My_Controller {

	public function index() {
		// 무언가 수행
	}
}
```

## 관련 자료
- [Routing](/learn/routing) - 라우트를 컨트롤러에 매핑하고 뷰를 렌더링하는 방법.
- [Why a Framework?](/learn/why-frameworks) - Flight와 같은 프레임워크를 사용하는 이점 이해.

## 문제 해결
- 네임스페이스가 있는 클래스들이 발견되지 않는 이유를 파악할 수 없다면, 프로젝트의 루트 디렉토리에 `Flight::path()`를 사용해야 하며, `app/`나 `src/` 디렉토리나 이에 상응하는 것을 사용하지 말아야 한다는 점을 기억하세요.

### 클래스 발견되지 않음 (autoloading 작동 안 함)

이 문제가 발생하는 이유는 몇 가지가 있을 수 있습니다. 아래에 몇 가지 예시를 들었지만, [autoloading](/learn/autoloading) 섹션도 확인하세요.

#### 잘못된 파일 이름
가장 흔한 이유는 클래스 이름이 파일 이름과 일치하지 않는 것입니다.

`MyClass`라는 이름의 클래스가 있다면 파일 이름은 `MyClass.php`여야 합니다. `MyClass`라는 클래스 이름과 `myclass.php`라는 파일 이름이라면 autoloader가 이를 찾을 수 없습니다.

#### 잘못된 네임스페이스
네임스페이스를 사용하는 경우, 네임스페이스가 디렉토리 구조와 일치해야 합니다.

```php
// ...코드...

// MyController가 app/controllers 디렉토리에 있고 네임스페이스가 있는 경우
// 이는 작동하지 않습니다.
Flight::route('/hello', 'MyController->hello');

// 다음 옵션 중 하나를 선택해야 합니다
Flight::route('/hello', 'app\controllers\MyController->hello');
// 또는 상단에 use 문이 있는 경우

use app\controllers\MyController;

Flight::route('/hello', [ MyController::class, 'hello' ]);
// 다음과 같이 작성할 수도 있음
Flight::route('/hello', MyController::class.'->hello');
// 또는...
Flight::route('/hello', [ 'app\controllers\MyController', 'hello' ]);
```

#### `path()` 정의되지 않음

스켈레톤 앱에서는 `config.php` 파일 내부에 정의되어 있지만, 클래스들이 발견되도록 하려면 `path()` 메서드가 정의되어 있어야 합니다(아마도 디렉토리의 루트로). 사용 전에 이를 확인하세요.

```php
// Autoloader에 경로 추가
Flight::path(__DIR__.'/../');
```

## 변경 로그
- v3.7.2 - `Loader::setV2ClassLoading(false);`를 실행하여 클래스 이름에 Pascal_Snake_Case 사용 가능
- v2.0 - Autoload 기능 추가.