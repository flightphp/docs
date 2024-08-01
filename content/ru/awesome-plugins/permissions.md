# FlightPHP/Permissions

Это модуль разрешений, который можно использовать в ваших проектах, если у вас есть несколько ролей в вашем приложении, и каждая роль имеет немного различные функции. Этот модуль позволяет определять разрешения для каждой роли, а затем проверять, имеет ли текущий пользователь разрешение на доступ к определенной странице или выполнение определенного действия.

Установка
-------
Запустите `composer require flightphp/permissions` и вы готовы к работе!

Использование
-------
Сначала вам нужно настроить ваши разрешения, затем вы объясняете вашему приложению, что означают разрешения. В конечном итоге вы будете проверять свои разрешения с помощью `$Permissions->has()`, `->can()` или `is()`. `has()` и `can()` имеют одинаковое функциональное назначение, но названы по-разному, чтобы сделать ваш код более читаемым.

## Базовый пример

Допустим, у вас есть функция в вашем приложении, которая проверяет, вошел ли пользователь в систему. Вы можете создать объект разрешений следующим образом:

```php
// index.php
require 'vendor/autoload.php';

// некоторый код

// затем у вас вероятно есть что-то, что говорит вам, какова текущая роль у человека
// вероятно, у вас есть что-то, где вы извлекаете текущую роль
// из переменной сеанса, которая это определяет
// после того, как кто-то войдет в систему, в противном случае у них будет роль 'guest' или 'public'.
$current_role = 'admin';

// настройка разрешений
$permission = new \flight\Permission($current_role);
$permission->defineRule('loggedIn', function($current_role) {
	return $current_role !== 'guest';
});

// Вероятно, вам захочется сохранить этот объект где-то в Flight
Flight::set('permission', $permission);
```

Затем, где-то в контроллере, у вас может быть что-то подобное.

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

Вы также можете использовать это для отслеживания, имеют ли они разрешение на выполнение какого-либо действия в вашем приложении. Например, если у вас есть способ взаимодействия пользователей с размещением информации в вашем программном обеспечении, вы можете проверить, имеют ли они разрешение на выполнение определенных действий.

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

Затем, где-то в контроллере...

```php
class PostController {
	public function create() {
		$permission = Flight::get('permission');
		if ($permission->can('post.create')) {
			// делать что-то
		} else {
			// сделать что-то другое
		}
	}
}
```

## Внедрение зависимостей
Вы можете внедрять зависимости в замыкание, которое определяет разрешения. Это полезно, если у вас есть какой-то переключатель, идентификатор или любая другая точка данных, которую вы хотите проверить. То же самое работает для вызовов типа Class->Method, за исключением того, что аргументы определяются в методе.

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
		// сделать что-то другое
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

## Быстрый способ установки разрешений с использованием классов
Вы также можете использовать классы для определения ваших разрешений. Это полезно, если у вас много разрешений, и вы хотите сохранить свой код чистым. Вы можете сделать что-то подобное:

```php
<?php

// код инициализации
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRule('order', 'MyApp\Permissions->order');

// myapp/Permissions.php
namespace MyApp;

class Permissions {

	public function order(string $current_role, int $user_id) {
		// Предполагая, что вы настроили это заранее
		/** @var \flight\database\PdoWrapper $db */
		$db = Flight::db();
		$allowed_permissions = [ 'read' ]; // каждый может просматривать заказ
		if($current_role === 'manager') {
			$allowed_permissions[] = 'create'; // менеджеры могут создавать заказы
		}
		$some_special_toggle_from_db = $db->fetchField('SELECT some_special_toggle FROM settings WHERE id = ?', [ $user_id ]);
		if($some_special_toggle_from_db) {
			$allowed_permissions[] = 'update'; // если у пользователя есть специальный переключатель, он может обновлять заказы
		}
		if($current_role === 'admin') {
			$allowed_permissions[] = 'delete'; // администраторы могут удалять заказы
		}
		return $allowed_permissions;
	}
}
```

Интересно то, что существует также сокращение, которое можно использовать (которое также может быть кэшировано!!!), где вы просто говорите классу разрешений отображать все методы класса в разрешения. Так что если у вас есть метод с именем `order()` и метод с именем `company()`, они автоматически будут сопоставлены, и вы сможете просто запустить `$Permissions->has('order.read')` или `$Permissions->has('company.read')` и это будет работать. Определение этого довольно сложное, так что держитесь со мной здесь. Вам просто нужно сделать это:

Создайте класс разрешений, который вы хотите сгруппировать вместе.

```php
class MyPermissions {
	public function order(string $current_role, int $order_id = 0): array {
		// код для определения разрешений
		return $permissions_array;
	}

	public function company(string $current_role, int $company_id): array {
		// код для определения разрешений
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

Наконец, вызовите разрешение в вашем кодовой базе, чтобы проверить, разрешено ли пользователю выполнять определенное разрешение.

```php
class SomeController {
	public function createOrder() {
		if(Flight::get('permissions')->can('order.create') === false) {
			die('Вы не можете создать заказ. Извините!');
		}
	}
}
```

### Кэширование

Для включения кэширования просмотрите простую [библиотеку wruczak/phpfilecache](https://docs.flightphp.com/awesome-plugins/php-file-cache). Пример включения этого приведен ниже.

```php

// это $app может быть частью вашего кода, или
// вы можете просто передать null, и он получит
// из Flight::app() в конструкторе
$app = Flight::app();

// На данный момент он принимает это как файловый кэш. Другие легко
// могут быть добавлены в будущем.
$Cache = new Wruczek\PhpFileCache\PhpFileCache;

$Permissions = new \flight\Permission($current_role, $app, $Cache);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class, 3600); // 3600 - сколько секунд кэшировать это. Оставьте это, чтобы не использовать кэширование
```

И вперед!