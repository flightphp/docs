# FlightPHP/권한

앱 내에 여러 역할이 있고 각 역할마다 약간 다른 기능이 있는 경우에 사용할 수 있는 권한 모듈입니다. 이 모듈을 사용하면 각 역할에 대한 권한을 정의한 다음 현재 사용자가 특정 페이지에 액세스하거나 특정 작업을 수행할 수 있는지 확인할 수 있습니다.

설치
-------
`composer require flightphp/permissions`를 실행하고 시작하세요!

사용법
-------
먼저 권한을 설정해야 하며, 앱에 권한이 무엇을 의미하는지 알려주어야 합니다. 최종적으로 `$Permissions->has()`, `->can()`, 또는 `is()`를 사용하여 권한을 확인할 수 있습니다. `has()`와 `can()`은 기능이 동일하지만 코드를 보다 읽기 쉽게 만들기 위해 다르게 명명되었습니다.

## 기본 예제

앱 내에서 사용자가 로그인되어 있는지 확인하는 기능이 있다고 가정해 보겠습니다. 다음과 같이 권한 오브젝트를 생성할 수 있습니다:

```php
// index.php
require 'vendor/autoload.php';

// 어떤 코드

// 아마도 현재 역할을 나타내는 사람의 현재 역할이 무엇인지 알려주는 코드가 있을 것입니다.
// 아마도 세션 변수에서 현재 역할을 가져오는 코드가 있을 것입니다.
// 누군가 로그인한 후에 세션에 정의되므로 그렇지 않으면 'guest' 또는 'public' 역할을 가질 것입니다.
$current_role = 'admin';

// 권한 설정
$permission = new \flight\Permission($current_role);
$permission->defineRule('loggedIn', function($current_role) {
	return $current_role !== 'guest';
});

// 이 객체를 어딘가에 올바르게 유지하는 것이 좋습니다.
Flight::set('permission', $permission);
```

그런 다음, 컨트롤러 어딘가에 다음과 같은 코드가 있을 수 있습니다.

```php
<?php

// 어떤 컨트롤러
class SomeController {
	public function someAction() {
		$permission = Flight::get('permission');
		if ($permission->has('loggedIn')) {
			// 무언가 수행
		} else {
			// 다른 작업 수행
		}
	}
}
```

또한 이를 사용하여 앱에서 특정 작업을 수행할 수 있는지 확인할 수도 있습니다.
예를 들어, 사용자가 소프트웨어에서 게시물을 상호작용할 수 있는 방법이 있는 경우 특정 작업을 수행할 수 있는지 확인할 수 있습니다.

```php
$current_role = 'admin';

// 권한 설정
$permission = new \flight\Permission($current_role);
$permission->defineRule('post', function($current_role) {
	if($current_role === 'admin') {
		$permissions = ['create', 'read', 'update', 'delete'];
	} else if($current_role === 'editor') {
		$permissions = ['create', 'read', 'update'];
	} else if($current_role === 'author') {
		$permissions = ['create', 'read'];
	} else if($current_role === 'contributor') {
		$permissions = ['create'];
	} else {
		$permissions = [];
	}
	return $permissions;
});
Flight::set('permission', $permission);
```

그런 다음, 컨트롤러 어딘가에...

```php
class PostController {
	public function create() {
		$permission = Flight::get('permission');
		if ($permission->can('post.create')) {
			// 무언가 수행
		} else {
			// 다른 작업 수행
		}
	}
}
```

## 의존성 주입
권한을 정의하는 클로저에 의존성을 주입할 수 있습니다. 이는 토글, ID 또는 확인할 데이터 등이 있는 경우 유용합니다. Class->Method 타입의 호출에 대해서도 동일하게 작동하며, 메소드에서 인수를 정의합니다.

### 클로저

```php
$Permission->defineRule('order', function(string $current_role, MyDependency $MyDependency = null) {
	// ... 코드
});

// 컨트롤러 파일에
public function createOrder() {
	$MyDependency = Flight::myDependency();
	$permission = Flight::get('permission');
	if ($permission->can('order.create', $MyDependency)) {
		// 무언가 수행
	} else {
		// 다른 작업 수행
	}
}
```

### 클래스

```php
namespace MyApp;

class Permissions {

	public function order(string $current_role, MyDependency $MyDependency = null) {
		// ... 코드
	}
}
```

## 클래스로 권한 설정에 대한 바로 가기
클래스를 사용하여 권한을 정의할 수도 있습니다. 많은 권한이 있는 경우 코드를 깔끔하게 유지하려면 유용합니다. 다음과 같이 수행할 수 있습니다:

```php
<?php

// 부트스트랩 코드
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRule('order', 'MyApp\Permissions->order');

// myapp/Permissions.php
namespace MyApp;

class Permissions {

	public function order(string $current_role, int $user_id) {
		// 미리 설정한 값을 가정합니다.
		/** @var \flight\database\PdoWrapper $db */
		$db = Flight::db();
		$allowed_permissions = [ 'read' ]; // 누구나 주문을 볼 수 있음
		if($current_role === 'manager') {
			$allowed_permissions[] = 'create'; // 매니저는 주문을 만들 수 있음
		}
		$some_special_toggle_from_db = $db->fetchField('SELECT some_special_toggle FROM settings WHERE id = ?', [ $user_id ]);
		if($some_special_toggle_from_db) {
			$allowed_permissions[] = 'update'; // 사용자에게 특별 토글이 있으면 주문을 업데이트할 수 있음
		}
		if($current_role === 'admin') {
			$allowed_permissions[] = 'delete'; // 관리자는 주문을 삭제할 수 있음
		}
		return $allowed_permissions;
	}
}
```
위의 방법은 권한을 자동으로 매핑할 수 있는 바로 가기도 제공합니다(캐시도 가능합니다!). 따라서 `order()`와 `company()`와 같은 이름의 메소드가 있고, `$Permissions->has('order.read')` 또는 `$Permissions->has('company.read')`와 같이 실행할 수 있습니다. 이를 정의하는 것은 매우 어렵습니다, 그러므로 이 부분을 주목하세요. 이렇게 수행하는 것이 좋습니다:

그룹화하고자 하는 권한 클래스를 생성합니다.
```php
class MyPermissions {
	public function order(string $current_role, int $order_id = 0): array {
		// 권한을 결정하는 코드
		return $permissions_array;
	}

	public function company(string $current_role, int $company_id): array {
		// 권한을 결정하는 코드
		return $permissions_array;
	}
}
```

그런 다음, 이 라이브러리를 사용하여 권한을 찾을 수 있도록 만듭니다.

```php
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class);
Flight::set('permissions', $Permissions);
```

마지막으로, 코드베이스에서 사용자가 지정된 권한을 수행할 수 있는지 확인하려면 권한을 호출하세요.

```php
class SomeController {
	public function createOrder() {
		if(Flight::get('permissions')->can('order.create') === false) {
			die('주문을 만들 수 없습니다. 죄송합니다!');
		}
	}
}
```

### 캐싱

캐싱을 활성화하려면 간단한 [wruczak/phpfilecache](https://docs.flightphp.com/awesome-plugins/php-file-cache) 라이브러리를 참조하십시오. 이를 활성화하는 예시는 다음과 같습니다.
```php

// 이 $app은 귀하의 코드의 일부가 될 수도 있으며
// null을 전달하고 생성자에서 Flight::app()을 가져올 수도 있습니다.
$app = Flight::app();

// 현재는 이 파일 캐시 설정을 수락합니다. 나중에 다른 것을 손쉽게
// 추가할 수 있습니다. 
$Cache = new Wruczek\PhpFileCache\PhpFileCache;

$Permissions = new \flight\Permission($current_role, $app, $Cache);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class, 3600); // 이것은 이를 얼마나 오랫동안 캐시할지를 나타냅니다. 캐싱을 사용하지 않으려면 이 부분을 제거하세요
```

그리고 시작하세요!