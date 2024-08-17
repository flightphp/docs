# FlightPHP/Permissions

这是一个权限模块，可以在您的项目中使用，如果您的应用程序中有多个角色，并且每个角色有稍微不同的功能。此模块允许您为每个角色定义权限，然后检查当前用户是否具有权限访问某个页面或执行某个操作。

单击[此处](https://github.com/flightphp/permissions)查看 GitHub 上的存储库。

安装
-------
运行 `composer require flightphp/permissions`，您就可以开始了！

用法
-------
首先，您需要设置权限，然后告诉您的应用程序这些权限的含义。最终，您将使用 `$Permissions->has()`、`->can()` 或 `is()` 检查权限。`has()` 和 `can()` 具有相同的功能，但命名不同，以使您的代码更易读。

## 基本示例

假设您的应用程序中有一个功能，用于检查用户是否已登录。您可以像这样创建一个权限对象：

```php
// index.php
require 'vendor/autoload.php';

// 一些代码

// 然后您可能有一些内容告诉您当前角色是谁
// 可能您有一些内容从会话变量中提取当前角色
// 以定义此内容
// 在某人登录后，否则他们将拥有 'guest' 或 'public' 角色。
$current_role = 'admin';

// 设置权限
$permission = new \flight\Permission($current_role);
$permission->defineRule('loggedIn', function($current_role) {
	return $current_role !== 'guest';
});

// 您可能希望在 Flight 中某处持久化此对象
Flight::set('permission', $permission);
```

然后在某个控制器中，您可能会有如下代码。

```php
<?php

// 某个控制器
class SomeController {
	public function someAction() {
		$permission = Flight::get('permission');
		if ($permission->has('loggedIn')) {
			// 做某事
		} else {
			// 做其他事
		}
	}
}
```

您还可以使用此功能跟踪用户在应用程序中是否具有执行某些操作的权限。
例如，如果您的应用程序允许用户与软件上的帖子进行交互，您可以
检查他们是否有权限执行某些操作。

```php
$current_role = 'admin';

// 设置权限
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

然后在某个控制器中...

```php
class PostController {
	public function create() {
		$permission = Flight::get('permission');
		if ($permission->can('post.create')) {
			// 做某事
		} else {
			// 做其他事
		}
	}
}
```

## 注入依赖项
您可以将依赖项注入定义权限的闭包中。如果您有某种切换、ID 或任何其他要检查的数据点，这很有用。对于 Class->Method 类型的调用同样适用，只是您需要在方法中定义参数。

### 闭包

```php
$Permission->defineRule('order', function(string $current_role, MyDependency $MyDependency = null) {
	// ... 代码
});

// 在您的控制器文件中
public function createOrder() {
	$MyDependency = Flight::myDependency();
	$permission = Flight::get('permission');
	if ($permission->can('order.create', $MyDependency)) {
		// 做某事
	} else {
		// 做其他事
	}
}
```

### 类

```php
namespace MyApp;

class Permissions {

	public function order(string $current_role, MyDependency $MyDependency = null) {
		// ... 代码
	}
}
```

## 使用类设置权限的快捷方式
您还可以使用类来定义您的权限。如果您有很多权限要保持代码整洁，这很有用。您可以这样做：
```php
<?php

// 启动代码
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRule('order', 'MyApp\Permissions->order');

// myapp/Permissions.php
namespace MyApp;

class Permissions {

	public function order(string $current_role, int $user_id) {
		// 假设您先前设置好了这一点
		/** @var \flight\database\PdoWrapper $db */
		$db = Flight::db();
		$allowed_permissions = [ 'read' ]; // 每个人都可以查看订单
		if($current_role === 'manager') {
			$allowed_permissions[] = 'create'; // 管理员可以创建订单
		}
		$some_special_toggle_from_db = $db->fetchField('SELECT some_special_toggle FROM settings WHERE id = ?', [ $user_id ]);
		if($some_special_toggle_from_db) {
			$allowed_permissions[] = 'update'; // 如果用户有特殊开关，他们可以更新订单
		}
		if($current_role === 'admin') {
			$allowed_permissions[] = 'delete'; // 管理员可以删除订单
		}
		return $allowed_permissions;
	}
}
```
很酷的部分是，还有一个简便方法可以使用（也可以被缓存！），您只需告诉权限类将类中的所有方法映射到权限中。因此，如果您有一个名为 `order()` 和一个名为 `company()` 的方法，这些将自动映射，因此您只需运行 `$Permissions->has('order.read')` 或 `$Permissions->has('company.read')` 即可正常工作。定义这些非常困难，所以请跟着我学习。您只需要执行以下操作：

创建要分组的权限类。
```php
class MyPermissions {
	public function order(string $current_role, int $order_id = 0): array {
		// 决定权限的代码
		return $permissions_array;
	}

	public function company(string $current_role, int $company_id): array {
		// 决定权限的代码
		return $permissions_array;
	}
}
```

然后使用此库使权限可发现。

```php
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class);
Flight::set('permissions', $Permissions);
```

最后，在您的代码库中调用权限以检查用户是否被允许执行给定的权限。

```php
class SomeController {
	public function createOrder() {
		if(Flight::get('permissions')->can('order.create') === false) {
			die('您无法创建订单。抱歉！');
		}
	}
}
```

### 缓存

要启用缓存，请参阅简单的 [wruczak/phpfilecache](https://docs.flightphp.com/awesome-plugins/php-file-cache) 库。以下是启用此功能的示例。
```php

// 此 $app 可以是您代码的一部分，
// 或者您可以只传递 null，并在构造函数中
// 从 Flight::app() 获取
$app = Flight::app();

// 现在它接受此文件缓存。将来可以轻松添加其他缓存。
$Cache = new Wruczek\PhpFileCache\PhpFileCache;

$Permissions = new \flight\Permission($current_role, $app, $Cache);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class, 3600); // 3600 是要将其缓存多少秒。如果不使用缓存，请勿包含此选项
```

然后开启吧！

