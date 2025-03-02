# FlightPHP/Permissions

Це модуль дозволів, який можна використовувати у ваших проектах, якщо у вас є кілька ролей у вашому додатку, і кожна роль має трохи відмінну функціональність. Цей модуль дозволяє вам визначити дозволи для кожної ролі, а потім перевірити, чи має поточний користувач дозвіл на доступ до певної сторінки або виконання певної дії.

Клікніть [тут](https://github.com/flightphp/permissions), щоб перейти до репозиторію на GitHub.

Встановлення
-------
Запустіть `composer require flightphp/permissions`, і ви на правильному шляху!

Використання
-------
Спочатку вам потрібно налаштувати ваші дозволи, потім ви скажете вашому додатку, що означають ці дозволи. Врешті-решт, ви будете перевіряти ваші дозволи за допомогою `$Permissions->has()`, `->can()`, або `is()`. `has()` і `can()` мають однакову функціональність, але називаються по-різному, щоб зробити ваш код читабельнішим.

## Основний приклад

Припустимо, що у вас є функція у вашому додатку, яка перевіряє, чи ввійшов користувач. Ви можете створити об'єкт дозволу таким чином:

```php
// index.php
require 'vendor/autoload.php';

// деякий код 

// потім, напевно, у вас є щось, що говорить вам, яка поточна роль особи
// ймовірно, у вас є щось, де ви витягуєте поточну роль
// з змінної сесії, яка це визначає
// після того, як хтось увійде, інакше у них буде роль 'гостя' або 'публічна'.
$current_role = 'admin';

// налаштування дозволів
$permission = new \flight\Permission($current_role);
$permission->defineRule('loggedIn', function($current_role) {
	return $current_role !== 'guest';
});

// Ви, напевно, захотите зберегти цей об'єкт у Flight десь 
Flight::set('permission', $permission);
```

Потім у контролері десь ви можете мати щось на зразок цього.

```php
<?php

// якийсь контролер
class SomeController {
	public function someAction() {
		$permission = Flight::get('permission');
		if ($permission->has('loggedIn')) {
			// зробити щось
		} else {
			// зробити щось інше
		}
	}
}
```

Ви також можете використовувати це, щоб відстежувати, чи мають вони дозвіл робити щось у вашому додатку. Наприклад, якщо у вас є спосіб, яким користувачі можуть взаємодіяти з публікацією у вашому програмному забезпеченні, ви можете перевірити, чи мають вони дозвіл на виконання певних дій.

```php
$current_role = 'admin';

// налаштування дозволів
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

Потім у контролері десь...

```php
class PostController {
	public function create() {
		$permission = Flight::get('permission');
		if ($permission->can('post.create')) {
			// зробити щось
		} else {
			// зробити щось інше
		}
	}
}
```

## Впровадження залежностей
Ви можете впроваджувати залежності у замикання, яке визначає дозволи. Це корисно, якщо у вас є якийсь перемикач, id, або будь-яка інша точка даних, з якою ви хочете перевірити. Те ж саме стосується викликів типу Клас->Метод, за винятком того, що ви визначаєте аргументи в методі.

### Замикання

```php
$Permission->defineRule('order', function(string $current_role, MyDependency $MyDependency = null) {
	// ... код
});

// у вашому файлі контролера
public function createOrder() {
	$MyDependency = Flight::myDependency();
	$permission = Flight::get('permission');
	if ($permission->can('order.create', $MyDependency)) {
		// зробити щось
	} else {
		// зробити щось інше
	}
}
```

### Класи

```php
namespace MyApp;

class Permissions {

	public function order(string $current_role, MyDependency $MyDependency = null) {
		// ... код
	}
}
```

## Швидкий спосіб встановлення дозволів за допомогою класів
Ви також можете використовувати класи для визначення ваших дозволів. Це корисно, якщо у вас багато дозволів, і ви хочете зберегти ваш код чистим. Ви можете зробити щось на зразок цього:
```php
<?php

// код завантаження
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRule('order', 'MyApp\Permissions->order');

// myapp/Permissions.php
namespace MyApp;

class Permissions {

	public function order(string $current_role, int $user_id) {
		// Припускаємо, що ви налаштували це раніше
		/** @var \flight\database\PdoWrapper $db */
		$db = Flight::db();
		$allowed_permissions = [ 'read' ]; // всі можуть переглядати замовлення
		if($current_role === 'manager') {
			$allowed_permissions[] = 'create'; // менеджери можуть створювати замовлення
		}
		$some_special_toggle_from_db = $db->fetchField('SELECT some_special_toggle FROM settings WHERE id = ?', [ $user_id ]);
		if($some_special_toggle_from_db) {
			$allowed_permissions[] = 'update'; // якщо у користувача є спеціальний перемикач, вони можуть оновлювати замовлення
		}
		if($current_role === 'admin') {
			$allowed_permissions[] = 'delete'; // адміністратори можуть видаляти замовлення
		}
		return $allowed_permissions;
	}
}
```
Завдяки цьому є також швидкий спосіб, який ви можете використовувати (який також може бути кешованим!!!), де ви просто говорите класу дозволів відобразити всі методи в класі в дозволи. Отже, якщо у вас є метод з назвою `order()` і метод з назвою `company()`, ці методи будуть автоматично відображені, так що ви зможете просто запустити `$Permissions->has('order.read')` або `$Permissions->has('company.read')`, і це спрацює. Визначити це дуже складно, тому залишайтеся зі мною тут. Вам просто потрібно зробити це:

Створіть клас дозволів, які ви хочете об'єднати разом.
```php
class MyPermissions {
	public function order(string $current_role, int $order_id = 0): array {
		// код для визначення дозволів
		return $permissions_array;
	}

	public function company(string $current_role, int $company_id): array {
		// код для визначення дозволів
		return $permissions_array;
	}
}
```

Потім зробіть дозволи видимими за допомогою цієї бібліотеки.

```php
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class);
Flight::set('permissions', $Permissions);
```

Нарешті, викликайте дозвіл у вашій кодовій базі, щоб перевірити, чи дозволено користувачу виконати даний дозвіл.

```php
class SomeController {
	public function createOrder() {
		if(Flight::get('permissions')->can('order.create') === false) {
			die('Ви не можете створити замовлення. Вибачте!');
		}
	}
}
```

### Кешування

Щоб увімкнути кешування, ознайомтеся з простим [wruczak/phpfilecache](https://docs.flightphp.com/awesome-plugins/php-file-cache) бібліотекою. Приклад увімкнення цього наведено нижче.
```php

// цей $app може бути частиною вашого коду, або
// ви можете просто передати null, і це
// витягне з Flight::app() у конструкторі
$app = Flight::app();

// Поки що він приймає це як кеш файлів. Інші можна легко
// додати в майбутньому. 
$Cache = new Wruczek\PhpFileCache\PhpFileCache;

$Permissions = new \flight\Permission($current_role, $app, $Cache);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class, 3600); // 3600 - це скільки секунд кешувати це. Залиште це, щоб не використовувати кешування
```

І вперед!