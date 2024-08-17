# FlightPHP/Права доступа

Это модуль разрешений, который можно использовать в ваших проектах, если у вас есть несколько ролей в вашем приложении, и каждая роль имеет немного разную функциональность. Этот модуль позволяет определить разрешения для каждой роли, а затем проверить, имеет ли текущий пользователь разрешение на доступ к определенной странице или выполнение определенного действия.

Нажмите [сюда](https://github.com/flightphp/permissions) для репозитория на GitHub.

Установка
-------
Запустите `composer require flightphp/permissions` и вы готовы к работе!

Использование
-------
Сначала вам нужно настроить ваши разрешения, затем сообщить вашему приложению, что означают эти разрешения. В конечном итоге вы проверите ваши разрешения с помощью `$Permissions->has()`, `->can()` или `is()`. `has()` и `can()` имеют одинаковую функциональность, но названы по-разному, чтобы сделать ваш код более читаемым.

## Базовый пример

Давайте предположим, что у вас есть функция в вашем приложении, которая проверяет, вошел ли пользователь в систему. Вы можете создать объект разрешений следующим образом:

```php
// index.php
require 'vendor/autoload.php';

// некоторый код

// затем у вас вероятно есть что-то, что говорит вам, какая текущая роль у человека
// скорее всего у вас есть что-то, откуда вы извлекаете текущую роль
// из переменной сеанса, которая определяет это
// после входа в систему у кого-то должна быть роль 'guest' или 'public'.
$current_role = 'admin';

// настройка разрешений
$permission = new \flight\Permission($current_role);
$permission->defineRule('loggedIn', function($current_role) {
	return $current_role !== 'guest';
});

// Вам вероятно захочется сохранить этот объект где-то в Flight
Flight::set('permission', $permission);
```

Затем в контроллере где-то вы можете иметь что-то вроде этого.

```php
<?php

// некоторый контроллер
class SomeController {
	public function someAction() {
		$permission = Flight::get('permission');
		if ($permission->has('loggedIn')) {
			// сделать что-то
		} else {
			// сделать что-то другое
		}
	}
}
```

Вы также можете использовать это для отслеживания, есть ли у них разрешение на выполнение определенного действия в вашем приложении.
Например, если у вас есть способ, как пользователи могут взаимодействовать с публикацией в вашем программном обеспечении, вы можете
проверить, имеют ли они разрешение на выполнение определенных действий.

```php
$current_role = 'admin';

// настройка разрешений
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

Затем где-то в контроллере...

```php
class PostController {
	public function create() {
		$permission = Flight::get('permission');
		if ($permission->can('post.create')) {
			// сделать что-то
		} else {
			// сделать что-то еще
		}
	}
}
```

## Внедрение зависимостей
Вы можете внедрять зависимости в замыкание, которое определяет разрешения. Это полезно, если у вас есть какой-то переключатель, идентификатор или любая другая точка данных, которую вы хотите проверить. То же самое работает для вызовов вида Class->Method, за исключением того, что аргументы определяются в методе.

### Замыкания

```php
$Permission->defineRule('order', function(string $current_role, MyDependency $MyDependency = null) {
	// ... код
});

// в вашем файле контроллера
public function createOrder() {
	$MyDependency = Flight::myDependency();
	$permission = Flight::get('permission');
	if ($permission->can('order.create', $MyDependency)) {
		// сделать что-то
	} else {
		// сделать что-то еще
	}
}
```

### Классы

```php
namespace MyApp;

class Permissions {

	public function order(string $current_role, MyDependency $MyDependency = null) {
		// ... код
	}
}
```

## Сокращение для установки разрешений с использованием классов
Вы также можете использовать классы для определения ваших разрешений. Это полезно, если у вас много разрешений, и вы хотите, чтобы ваш код был чистым. Вы можете сделать что-то вроде этого:
```php
<?php

// код инициализации
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRule('order', 'MyApp\Permissions->order');

// myapp/Permissions.php
namespace MyApp;

class Permissions {

	public function order(string $current_role, int $user_id) {
		// Предположим, что вы это настроили заранее
		/** @var \flight\database\PdoWrapper $db */
		$db = Flight::db();
		$allowed_permissions = [ 'read' ]; // каждый может просматривать заказ
		if($current_role === 'manager') {
			$allowed_permissions[] = 'create'; // менеджеры могут создавать заказы
		}
		$some_special_toggle_from_db = $db->fetchField('SELECT some_special_toggle FROM settings WHERE id = ?', [ $user_id ]);
		if($some_special_toggle_from_db) {
			$allowed_permissions[] = 'update'; // если у пользователя есть особый переключатель, он может обновлять заказы
		}
		if($current_role === 'admin') {
			$allowed_permissions[] = 'delete'; // администраторы могут удалять заказы
		}
		return $allowed_permissions;
	}
}
```
Здесь примечательно то, что есть также сокращение, которое можно использовать (которое также может быть кешировано!!!), где вы просто говорите классу разрешений сопоставить все методы в классе в разрешения. Поэтому, если у вас есть метод с именем `order()` и метод с именем `company()`, они будут автоматически сопоставлены, и вы сможете просто выполнить `$Permissions->has('order.read')` или `$Permissions->has('company.read')`, и это сработает. Определение этого очень сложно, так что держитесь здесь со мной. Просто вам нужно сделать это:

Создайте класс разрешений, которые вы хотите сгруппировать вместе.
```php
class MyPermissions {
	public function order(string $current_role, int $order_id = 0): array {
		// код определения разрешений
		return $permissions_array;
	}

	public function company(string $current_role, int $company_id): array {
		// код определения разрешений
		return $permissions_array;
	}
}
```

Затем сделайте разрешения обнаруживаемыми с использованием этой библиотеки.

```php
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class);
Flight::set('permissions', $Permissions);
```

Наконец, вызовите разрешение в вашей кодовой базе, чтобы проверить, разрешено ли пользователю выполнение заданного разрешения.

```php
class SomeController {
	public function createOrder() {
		if(Flight::get('permissions')->can('order.create') === false) {
			die('Вы не можете создать заказ. Извините!');
		}
	}
}
```

### Кеширование

Для включения кэширования, см. простую [библиотеку wruczak/phpfilecache](https://docs.flightphp.com/awesome-plugins/php-file-cache). Пример включения приведен ниже.
```php

// этот $app может быть частью вашего кода, или
// вы можете просто передать null, и он извлечет из Flight::app() в конструкторе
$app = Flight::app();

// Теперь для этого принимается файловое кэширование. Другие могут легко
// быть добавлены в будущем. 
$Cache = new Wruczek\PhpFileCache\PhpFileCache;

$Permissions = new \flight\Permission($current_role, $app, $Cache);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class, 3600); // 3600 - это сколько секунд кэшировать это. Оставьте это, чтобы не использовать кэширование
```

И впереди!