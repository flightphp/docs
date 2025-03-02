# 플라이트PHP/권한

이것은 여러 역할이 있는 앱에서 사용할 수있는 권한 모듈입니다. 각 역할마다 약간 다른 기능이 있는 경우 사용할 수 있습니다. 이 모듈을 사용하면 각 역할에 대한 권한을 정의한 다음 현재 사용자가 특정 페이지에 액세스하거나 특정 작업을 수행할 수 있는 권한이 있는지 확인할 수 있습니다.

[여기](https://github.com/flightphp/permissions)를 클릭하여 GitHub의 저장소를 확인하십시오.

설치
-------
`composer require flightphp/permissions`를 실행하면 됩니다!

사용법
-------
먼저 권한을 설정해야하고, 앱에 권한이 무엇을 의미하는지 알려야합니다. 최종적으로 `$Permissions->has()`, `->can()`, 또는 `is()`를 사용하여 권한을 확인할 수 있습니다. `has()`와 `can()`은 기능적으로 동일하지만 코드를 더 읽기 쉽게 만들기 위해 이름을 다르게 지었습니다.

## 기본 예제

앱에서 사용자가 로그인되어 있는지 확인하는 기능이있는 경우를 가정해 보겠습니다. 다음과 같이 권한 개체를 생성할 수 있습니다.

```php
// index.php
require 'vendor/autoload.php';

// 일부 코드

// 그런 다음 현재 사용자의 현재 역할을 알려주는 코드가 있을 것입니다.
// 아마도 현재 역할을 가져오는 코드가 있을 것입니다.
// 현재 역할을 정의하는 세션 변수에서 현재 역할을 가져오는 경우가 일반적일 것입니다.
// 그렇게하면 누군가 로그인한 후가 아니면 'guest' 또는 'public' 역할이있을 것입니다.
$current_role = 'admin';

// 권한 설정
$permission = new \flight\Permission($current_role);
$permission->defineRule('loggedIn', function($current_role) {
	return $current_role !== 'guest';
});

// 이 개체를 Flight 어딘가에 지속적으로 저장하는 것이 좋습니다
Flight::set('permission', $permission);
```

그런 다음 컨트롤러 어딘가에 다음과 같은 것이있을 수 있습니다.

```php
<?php

// 일부 컨트롤러
class SomeController {
	public function someAction() {
		$permission = Flight::get('permission');
		if ($permission->has('loggedIn')) {
			// 어떤 작업 수행
		} else {
			// 다른 작업 수행
		}
	}
}
```

또한이를 사용하여 응용 프로그램에서 작업을 수행할 수 있는 권한이 있는지 추적할 수도 있습니다.
예를 들어, 소프트웨어에서 게시물 작성과 상호 작용할 수있는 방법이있는 경우 특정 작업을 수행할 수 있는지 확인할 수 있습니다.

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

그런 다음 컨트롤러 어딘가에...

```php
class PostController {
	public function create() {
		$permission = Flight::get('permission');
		if ($permission->can('post.create')) {
			// 어떤 작업 수행
		} else {
			// 다른 작업 수행
		}
	}
}
```

## 의존성 주입
권한을 정의하는 클로저에 의존성을 주입할 수 있습니다. 확인하려는 일부 토글, ID 또는 기타 데이터 지점이 있는 경우 유용합니다. Class->Method 유형 호출의 경우 메서드에서 인수를 정의해야합니다.

### 클로저

```php
$Permission->defineRule('order', function(string $current_role, MyDependency $MyDependency = null) {
	// ... 코드
});

// 컨트롤러 파일에서
public function createOrder() {
	$MyDependency = Flight::myDependency();
	$permission = Flight::get('permission');
	if ($permission->can('order.create', $MyDependency)) {
		// 어떤 작업 수행
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

## 클래스로 권한 설정하는 바로 가기
클래스를 사용하여 권한을 정의할 수도 있습니다. 많은 권한이있고 코드를 깔끔하게 유지하려는 경우 유용합니다. 다음과 같이 할 수 있습니다:
```php
<?php

// 부트스트랩 코드
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRule('order', 'MyApp\Permissions->order');

// myapp/Permissions.php
namespace MyApp;

class Permissions {

	public function order(string $current_role, int $user_id) {
		// 미리 설정한 것으로 가정합니다
		/** @var \flight\database\PdoWrapper $db */
		$db = Flight::db();
		$allowed_permissions = [ 'read' ]; // 누구나 주문을 볼 수 있음
		if($current_role === 'manager') {
			$allowed_permissions[] = 'create'; // 매니저는 주문을 만들 수 있음
		}
		$some_special_toggle_from_db = $db->fetchField('SELECT some_special_toggle FROM settings WHERE id = ?', [ $user_id ]);
		if($some_special_toggle_from_db) {
			$allowed_permissions[] = 'update'; // 사용자가 특별 토글을 가지고있으면 주문을 업데이트 할 수 있음
		}
		if($current_role === 'admin') {
			$allowed_permissions[] = 'delete'; // 관리자는 주문을 삭제 할 수 있음
		}
		return $allowed_permissions;
	}
}
```
멋진 부분은 클래스에 대해 모든 메서드를 권한에 매핑하는 바로 가기가 있으며 (캐시 가능함!!!), 이렇게하면 `$Permissions->has('order.read')` 또는 `$Permissions->has('company.read')`를 실행하여 작동됩니다. 이를 정의하는 것은 매우 어렵기 때문에 여기에 머무르십시오. 그냥 이렇게하면됩니다:

그룹화하려는 권한 클래스를 작성합니다.
```php
class MyPermissions {
	public function order(string $current_role, int $order_id = 0): array {
		// 권한을 확인하는 코드
		return $permissions_array;
	}

	public function company(string $current_role, int $company_id): array {
		// 권한을 확인하는 코드
		return $permissions_array;
	}
}
```

그런 다음이 라이브러리를 사용하여 권한을 발견할 수 있도록 합니다.

```php
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class);
Flight::set('permissions', $Permissions);
```

마지막으로 코드베이스에서 사용자가 특정 권한을 수행할 수 있는지 확인하려면 권한을 호출하십시오.

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

캐싱을 활성화하려면 간단한 [wruczak/phpfilecache](https://docs.flightphp.com/awesome-plugins/php-file-cache) 라이브러리를 참조하십시오. 아래에 캐싱을 활성화하는 예제가 있습니다.
```php

// 이 $app은 귀하의 코드의 일부일 수 있거나
// null을 전달하여 클래스 외부에서 가져올 수 있습니다.
$app = Flight::app();

// 현재는이 파일 캐시를 사용합니다. 나중에 다른 캐시를 쉽게 추가할 수 있습니다.
$Cache = new Wruczek\PhpFileCache\PhpFileCache;

$Permissions = new \flight\Permission($current_role, $app, $Cache);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class, 3600); // 3600은 캐시로 저장되는 시간(초)입니다. 캐시를 사용하지 않으려면 이것을 뺍니다
```