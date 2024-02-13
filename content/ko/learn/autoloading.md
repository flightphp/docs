# 오토로딩

오토로딩은 PHP에서 클래스를 로드할 디렉토리 또는 디렉토리를 지정하는 개념입니다. 클래스를 로드하는 데 `require` 또는 `include`를 사용하는 것보다 훨씬 유익합니다. 또한, Composer 패키지를 사용하기 위한 필수 조건이기도 합니다.

기본적으로 `Flight` 클래스는 Composer 덕분에 자동으로 오토로드됩니다. 그러나 직접 클래스를 오토로드하려면 `Flight::path` 메서드를 사용하여 클래스를 로드할 디렉토리를 지정할 수 있습니다.

## 기본 예시

다음과 같은 디렉토리 트리가 있다고 가정해 봅시다:

```text
# 예시 경로
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - 이 프로젝트의 컨트롤러를 포함합니다
│   ├── translations
│   ├── UTILS - 이 애플리케이션을 위한 클래스를 포함합니다 (이 예시에서는 대문자로 명시적으로 표시함)
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

이것이 이 문서 사이트와 같은 파일 구조임을 알아채셨을 것입니다.

다음과 같이 각 디렉토리를 지정할 수 있습니다:

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

// 모든 오토로드된 클래스는 파스칼 케이스로 권장됩니다 (각 단어를 대문자로, 공백 없이)
// 클래스 이름에 밑줄을 사용할 수 없다는 요구 사항이 있습니다
class MyController {

	public function index() {
		// 무언가 수행
	}
}
```

## 네임스페이스

네임스페이스가 있는 경우 실제로 이를 구현하는 것이 매우 쉬워집니다. 애플리케이션의 루트 디렉토리 (문서 루트 또는 `public/` 폴더가 아님)를 지정하기 위해 `Flight::path()` 메서드를 사용해야 합니다.

```php

/**
 * public/index.php
 */

// 오토로더에 경로 추가
Flight::path(__DIR__.'/../');
```

이제 컨트롤러가 다음과 같이 보일 것입니다. 아래 예시를 살펴보되 중요한 정보에 주의하십시오.

```php
/**
 * app/controllers/MyController.php
 */

// 네임스페이스 필수
// 네임스페이스는 디렉토리 구조와 동일합니다
// 네임스페이스는 디렉토리 구조와 동일한 케이스를 따라야 합니다
// 네임스페이스와 디렉토리에 언더스코어를 포함할 수 없습니다
namespace app\controllers;

// 모든 오토로드된 클래스는 파스칼 케이스로 권장됩니다 (각 단어를 대문자로, 공백 없이)
// 클래스 이름에 밑줄을 사용할 수 없다는 요구 사항이 있습니다
class MyController {

	public function index() {
		// 무언가 수행
	}
}
```

그리고 utils 디렉토리에 클래스를 오토로드하려면 아래와 같이 거의 동일한 작업을 수행하면 됩니다:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// 네임스페이스는 디렉토리 구조와 케이스와 일치해야 합니다 (위의 파일 트리에서 UTILS 디렉토리가 모두 대문자임에 주목)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// 무언가 수행
	}
}
```