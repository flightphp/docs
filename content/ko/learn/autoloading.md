# 오토로딩

오토로딩은 PHP에서 클래스를 불러오기 위해 디렉토리를 지정하는 개념입니다. 이는 클래스를 불러오는 데 `require` 또는 `include`를 사용하는 것보다 훨씬 유익합니다. Composer 패키지를 사용하기 위한 요구 사항이기도 합니다.

기본적으로 `Flight` 클래스는 Composer를 통해 자동으로 로드됩니다. 그러나 사용자 정의 클래스를 자동으로 로드하려면 `Flight::path` 메서드를 사용하여 클래스를 불러올 디렉토리를 지정할 수 있습니다.

## 기본 예제

다음과 같이 디렉토리 트리가 있는 것으로 가정해 봅시다:

```text
# 예시 경로
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - 이 프로젝트의 컨트롤러를 포함합니다
│   ├── translations
│   ├── UTILS - 이 응용 프로그램을 위한 클래스를 포함합니다 (총 대문자로 작성된 예제에 일부러 사용함)
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

이 파일 구조가 이 문서 사이트의 파일 구조와 동일하다는 것을 알아채셨을 것입니다.

다음과 같이 각 디렉토리를 다음과 같이 지정할 수 있습니다:

```php

/**
 * public/index.php
 */

// 오토로더에 경로 추가
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// 네임스페이스 필요 없음

// 모든 오토로드된 클래스는 파스칼 케이스로 권장됩니다 (각 단어의 첫 글자가 대문자이고 공백이 없음)
// 버전 3.7.2에서 Loader::setV2ClassLoading(false);를 실행하여 클래스 이름에 파스칼_스네이크_케이스를 사용할 수 있습니다
class MyController {

	public function index() {
		// 무언가를 수행
	}
}
```

## 네임스페이스

네임스페이스가 있는 경우 이를 구현하는 것이 실제로 매우 쉬워집니다. `Flight::path()` 메서드를 사용하여 응용 프로그램의 루트 디렉토리 (문서 루트 또는 `public/` 폴더가 아니라)를 지정해야 합니다.

```php

/**
 * public/index.php
 */

// 오토로더에 경로 추가
Flight::path(__DIR__.'/../');
```

이제 컨트롤러가 다음과 같이 보일 것입니다. 아래의 예제를 보되 중요한 정보를 주석으로 확인해 주세요.

```php
/**
 * app/controllers/MyController.php
 */

// 네임스페이스가 필요합니다
// 네임스페이스는 디렉토리 구조와 동일해야 합니다
// 네임스페이스는 디렉토리 구조와 동일한 경우가 있어야 합니다
// 네임스페이스와 디렉토리에 밑줄을 사용할 수 없습니다 (Loader::setV2ClassLoading(false)가 설정되지 않는 한)
namespace app\controllers;

// 모든 오토로드된 클래스는 파스칼 케이스로 권장됩니다 (각 단어의 첫 글자가 대문자이고 공백이 없음)
// 버전 3.7.2에서 Loader::setV2ClassLoading(false);를 실행하여 클래스 이름에 파스칼_스네이크_케이스를 사용할 수 있습니다
class MyController {

	public function index() {
		// 무언가를 수행
	}
}
```

그리고 UTILS 디렉토리의 클래스를 자동으로 로드하려면 다음과 같이 할 수 있습니다:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// 네임스페이스는 디렉토리 구조와 케이스와 일치해야 합니다 (UTILS 디렉토리가 모두 대문자이며
//     위의 파일 트리와 동일함)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// 무언가를 수행
	}
}
```

## 클래스 이름에 밑줄 사용

버전 3.7.2에서 `Loader::setV2ClassLoading(false);`를 실행하여 클래스 이름에 밑줄을 사용할 수 있습니다. 이는 권장되지는 않지만 필요한 사용자들을 위해 제공됩니다.

```php

/**
 * public/index.php
 */

// 오토로더에 경로 추가
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');
Loader::setV2ClassLoading(false);

/**
 * app/controllers/My_Controller.php
 */

// 네임스페이스 필요 없음

class My_Controller {

	public function index() {
		// 무언가를 수행
	}
}
```