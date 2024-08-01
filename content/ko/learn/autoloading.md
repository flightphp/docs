# 자동로딩

자동로딩은 PHP에서 클래스를 로드할 디렉토리 또는 여러 디렉토리를 지정하는 개념입니다. 이는 `require` 또는 `include`를 사용하여 클래스를 로드하는 것보다 훨씬 유익합니다. 또한 Composer 패키지를 사용하는 데 필요합니다.

기본적으로 `Flight` 클래스는 Composer를 통해 자동으로 자동로드됩니다. 그러나 자체 클래스를 자동으로로드하려면 `Flight::path()` 메서드를 사용하여 클래스를 로드할 디렉토리를 지정할 수 있습니다.

## 기본 예제

다음과 같은 디렉터리 트리가 있다고 가정해 봅시다:

```text
# 예제 경로
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - 이 프로젝트의 컨트롤러가 들어 있는 디렉토리
│   ├── translations
│   ├── UTILS - 이 애플리케이션을 위한 클래스가 들어 있는 디렉토리 (예를 들어 나중에 사용하기 위해 모두 대문자로 표시되어 있습니다)
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

이는 이 설명서 사이트의 파일 구조와 동일한 것을 알 수 있습니다.

다음과 같이 각 디렉토리를 로드할 수 있습니다:

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

// 네임스페이스가 필요하지 않음

// 모든 자동로드 클래스는 파스칼 케이스(각 단어의 첫 글자가 대문자이고 공백이 없는)로 권장합니다
// 3.7.2 버전부터 Loader::setV2ClassLoading(false);를 실행하여 클래스 이름에 Pascal_Snake_Case를 사용할 수 있습니다.
class MyController {

	public function index() {
		// 무언가 수행
	}
}
```

## 네임스페이스

네임스페이스가 있는 경우 이 구현이 매우 쉬워집니다. 애플리케이션의 루트 디렉토리(문서 루트 또는 `public/` 폴더가 아님)를 지정하기 위해 `Flight::path()` 메서드를 사용해야 합니다.

```php

/**
 * public/index.php
 */

// 오토로더에 경로 추가
Flight::path(__DIR__.'/../');
```

이제 컨트롤러가 이와 같이 보일 수 있습니다. 아래 예제를 확인하되 중요한 정보를 위해 주석을 주의깊게 읽어주십시오.

```php
/**
 * app/controllers/MyController.php
 */

// 네임스페이스가 필요합니다
// 네임스페이스는 디렉토리 구조와 같아야 합니다
// 네임스페이스는 디렉토리 구조와 동일한 케이스를 따라야 합니다
// 네임스페이스와 디렉토리에는 밑줄을 사용할 수 없습니다 (Loader::setV2ClassLoading(false)가 설정된 경우를 제외하고)
namespace app\controllers;

// 모든 자동로드 클래스는 파스칼 케이스로 권장됩니다
// 3.7.2 버전부터 Loader::setV2ClassLoading(false);를 실행하여 클래스 이름에 Pascal_Snake_Case를 사용할 수 있습니다.
class MyController {

	public function index() {
		// 무언가 수행
	}
}
```

그리고 utils 디렉토리에 있는 클래스를 자동으로로드하려면 기본적으로 다음을 수행하면 됩니다:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// 네임스페이스는 디렉토리 구조 및 케이스와 일치해야 합니다 (UTILS 디렉토리가 위의 파일 트리처럼 대문자로 되어 있음에 유의하세요)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// 무언가 수행
	}
}
```

## 클래스 이름에 밑줄 사용

3.7.2 버전부터 `Loader::setV2ClassLoading(false);`를 실행하여 클래스 이름에 밑줄을 사용할 수 있습니다. 
이를 통해 클래스 이름에 밑줄을 사용할 수 있지만 권장되지는 않지만 필요한 경우 사용할 수 있습니다.

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

// 네임스페이스가 필요하지 않음

class My_Controller {

	public function index() {
		// 무언가 수행
	}
}
```  