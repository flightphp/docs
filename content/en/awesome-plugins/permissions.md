# FlightPHP/Permissions

This is a permissions module that can be used in your projects if you have multiple roles in your app and each role has a little bit different functionality. This module allows you to define permissions for each role and then check if the current user has the permission to access a certain page or perform a certain action. 

Click [here](https://github.com/flightphp/permissions) for the repository in GitHub.

Installation
-------
Run `composer require flightphp/permissions` and you're on your way!

Usage
-------
First you need to setup your permissions, then you tell the your app what the permissions mean. Ultimately you will check your permissions with `$Permissions->has()`, `->can()`, or `is()`. `has()` and `can()` have the same functionality, but are named differently to make your code more readable.

## Basic Example

Let's assume you have a feature in your application that checks if a user is logged in. You can create a permissions object like this:

```php
// index.php
require 'vendor/autoload.php';

// some code 

// then you probably have something that tells you who the current role is of the person
// likely you have something where you pull the current role
// from a session variable which defines this
// after someone logs in, otherwise they will have a 'guest' or 'public' role.
$current_role = 'admin';

// setup permissions
$permission = new \flight\Permission($current_role);
$permission->defineRule('loggedIn', function($current_role) {
	return $current_role !== 'guest';
});

// You'll probably want to persist this object in Flight somewhere
Flight::set('permission', $permission);
```

Then in a controller somewhere, you might have something like this.

```php
<?php

// some controller
class SomeController {
	public function someAction() {
		$permission = Flight::get('permission');
		if ($permission->has('loggedIn')) {
			// do something
		} else {
			// do something else
		}
	}
}
```

You can also use this to track if they have permission to do something in your application.
For instance, if your have a way that users can interact with posting on your software, you can 
check if they have permission to perform certain actions.

```php
$current_role = 'admin';

// setup permissions
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

Then in a controller somewhere...

```php
class PostController {
	public function create() {
		$permission = Flight::get('permission');
		if ($permission->can('post.create')) {
			// do something
		} else {
			// do something else
		}
	}
}
```

## Injecting dependencies
You can inject dependencies into the closure that defines the permissions. This is useful if you have some sort of toggle, id, or any other data point that you want to check against. The same works for Class->Method type calls, except you define the arguments in the method.

### Closures

```php
$Permission->defineRule('order', function(string $current_role, MyDependency $MyDependency = null) {
	// ... code
});

// in your controller file
public function createOrder() {
	$MyDependency = Flight::myDependency();
	$permission = Flight::get('permission');
	if ($permission->can('order.create', $MyDependency)) {
		// do something
	} else {
		// do something else
	}
}
```

### Classes

```php
namespace MyApp;

class Permissions {

	public function order(string $current_role, MyDependency $MyDependency = null) {
		// ... code
	}
}
```

## Shortcut to set permissions with classes
You can also use classes to define your permissions. This is useful if you have a lot of permissions and you want to keep your code clean. You can do something like this:
```php
<?php

// bootstrap code
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRule('order', 'MyApp\Permissions->order');

// myapp/Permissions.php
namespace MyApp;

class Permissions {

	public function order(string $current_role, int $user_id) {
		// Assuming you set this up beforehand
		/** @var \flight\database\PdoWrapper $db */
		$db = Flight::db();
		$allowed_permissions = [ 'read' ]; // everyone can view an order
		if($current_role === 'manager') {
			$allowed_permissions[] = 'create'; // managers can create orders
		}
		$some_special_toggle_from_db = $db->fetchField('SELECT some_special_toggle FROM settings WHERE id = ?', [ $user_id ]);
		if($some_special_toggle_from_db) {
			$allowed_permissions[] = 'update'; // if the user has a special toggle, they can update orders
		}
		if($current_role === 'admin') {
			$allowed_permissions[] = 'delete'; // admins can delete orders
		}
		return $allowed_permissions;
	}
}
```
The cool part is that there is also a shortcut that you can use (that can also be cached!!!) where you just tell the permissions class to map all methods in a class into permissions. So if you have a method named `order()` and a method named `company()`, these will automatically be mapped so you can just run `$Permissions->has('order.read')` or `$Permissions->has('company.read')` and it will work. Defining this is very difficult, so stay with me here. You just need to do this:

Create the class of permissions you want to group together.
```php
class MyPermissions {
	public function order(string $current_role, int $order_id = 0): array {
		// code to determine permissions
		return $permissions_array;
	}

	public function company(string $current_role, int $company_id): array {
		// code to determine permissions
		return $permissions_array;
	}
}
```

Then make the permissions discoverable using this library.

```php
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class);
Flight::set('permissions', $Permissions);
```

Finally, call the permission in your codebase to check if the user is allowed to perform a given permission.

```php
class SomeController {
	public function createOrder() {
		if(Flight::get('permissions')->can('order.create') === false) {
			die('You can\'t create an order. Sorry!');
		}
	}
}
```

### Caching

To enable caching, see the simple [wruczak/phpfilecache](https://docs.flightphp.com/awesome-plugins/php-file-cache) library. An example of enabling this is below.
```php

// this $app can be part of your code, or
// you can just pass null and it will
// pull from Flight::app() in the constructor
$app = Flight::app();

// For now it accepts this as a file cache. Others can easily
// be added in the future. 
$Cache = new Wruczek\PhpFileCache\PhpFileCache;

$Permissions = new \flight\Permission($current_role, $app, $Cache);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class, 3600); // 3600 is how many seconds to cache this for. Leave this off to not use caching
```

And away you go!

