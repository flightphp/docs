# FlightPHP/权限

这是一个权限模块，如果您的应用程序具有多个角色，并且每个角色具有略有不同的功能，则可以在项目中使用该模块。此模块允许您为每个角色定义权限，然后检查当前用户是否具有访问特定页面或执行特定操作的权限。

安装
-------
运行`composer require flightphp/permissions`，然后你就可以开始了！

用法
-------
首先，您需要设置您的权限，然后告诉您的应用程序这些权限的含义。最终，您将使用`$Permissions->has()`，`->can()`或`is()`检查您的权限。`has()`和`can()`具有相同的功能，但命名不同以使您的代码更易读。

## 基本示例

假设您的应用程序中有一个功能，用于检查用户是否已登录。您可以像这样创建一个权限对象：

```php
// index.php
require 'vendor/autoload.php';

// 一些代码

// 然后您可能有某些告诉您当前角色是谁的东西
// 可能会有一些地方拉取当前角色的当前角色
// 从一个定义此内容的会话变量中
// 某人登录后，否则他们将有一个'guest'或'public'角色。
$current_role = 'admin';

// 设置权限
$permission = new \flight\Permission($current_role);
$permission->defineRule('loggedIn', function($current_role) {
	return $current_role !== 'guest';
});

// 您可能希望在Flight的某个地方持久化此对象
Flight::set('permission', $permission);
```

然后在某个控制器中，您可能会有以下内容。

```php
<?php

// 一些控制器
class SomeController {
	public function someAction() {
		$permission = Flight::get('permission');
		if ($permission->has('loggedIn')) {
			// 做某事
		} else {
			// 做其他事情
		}
	}
}
```

您还可以使用此来追踪他们是否有权限在您的应用程序中执行某些操作。
例如，如果您有一种用户可以与软件上的帖子交互的方式，您可以
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
			// 做其他事情
		}
	}
}
```

## 注入依赖项
您可以将依赖项注入到定义权限的闭包中。如果您有一些您想要根据检查的开关、ID或任何其他数据点，这很有用。类->方法类型的调用也适用，不同之处在于您在方法中定义参数。

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
		// 做其他事情
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

## 使用类快捷设置权限
您还可以使用类来定义您的权限。如果您有很多权限并且希望保持代码清晰，这很有用。您可以这样做：
```php
<?php

// 启动代码
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRule('order', 'MyApp\Permissions->order');

// myapp/Permissions.php
namespace MyApp;

class Permissions {

	public function order(string $current_role, int $user_id) {
		// 假设您事先设置了这个
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
有趣的地方是，您还可以使用一个快捷方式（也可以缓存!!!），只需告诉权限类将类中的所有方法映射到权限中。因此，如果您有一个命名为`order()`和一个命名为`company()`的方法，这些将自动映射，因此您只需运行`$Permissions->has('order.read')`或`$Permissions->has('company.read')`，它将起作用。定义这个非常困难，所以请跟随我。

创建要组合在一起的权限类。
```php
class MyPermissions {
	public function order(string $current_role, int $order_id = 0): array {
		// 确定权限的代码
		return $permissions_array;
	}

	public function company(string $current_role, int $company_id): array {
		// 确定权限的代码
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

### 缓存

要启用缓存，请参见简单的 [wruczak/phpfilecache](https://docs.flightphp.com/awesome-plugins/php-file-cache) 库。下面是启用此功能的示例。
```php

// 此$ app可以是您代码的一部分，也可以
// 您只需传递null，它将
// 在构造函数中从Flight::app()中获取
$app = Flight::app();

// 现在它接受此作为文件缓存。其他缓存可以很容易地
// 在将来添加。
$Cache = new Wruczek\PhpFileCache\PhpFileCache;

$Permissions = new \flight\Permission($current_role, $app, $Cache);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class, 3600); // 3600 是要将此缓存几秒钟。省略此项以不使用缓存
```

然后您就可以开始了！

