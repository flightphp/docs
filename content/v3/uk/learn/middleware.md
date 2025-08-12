# Середнє ПЗ для маршрутів

Flight підтримує середнє ПЗ для маршрутів і груп маршрутів. Середнє ПЗ — це функція, яка виконується перед (або після) зворотного виклику маршруту. Це чудовий спосіб додати перевірки автентифікації API у вашому коді або перевірити, чи має користувач дозвіл на доступ до маршруту.

## Основне середнє ПЗ

Ось базовий приклад:

```php
// Якщо ви надасте лише анонімну функцію, вона буде виконана перед зворотним викликом маршруту. 
// там немає функцій "after" середнього ПЗ, крім класів (див. нижче)
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// Це виведе "Middleware first! Here I am!"
```

Є деякі дуже важливі нотатки про середнє ПЗ, про які ви повинні знати перед використанням:
- Функції середнього ПЗ виконуються в порядку їх додавання до маршруту. Виконання подібне до того, як [Slim Framework обробляє це](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Befores виконуються в порядку додавання, а Afters — у зворотному порядку.
- Якщо ваша функція середнього ПЗ повертає false, все виконання зупиняється і викидається помилка 403 Заборонено. Ви, ймовірно, захочете обробити це більш елегантно за допомогою `Flight::redirect()` або подібного.
- Якщо вам потрібні параметри з вашого маршруту, вони будуть передані у вигляді єдиного масиву до вашої функції середнього ПЗ. (`function($params) { ... }` або `public function before($params) {}`). Причина в тому, що ви можете структурувати свої параметри в групи, і в деяких з цих груп ваші параметри можуть з'являтися в іншому порядку, що зламає функцію середнього ПЗ через посилання на неправильний параметр. Таким чином, ви можете доступатися до них за іменем, а не за позицією.
- Якщо ви передасте лише ім'я середнього ПЗ, воно автоматично буде виконано за допомогою [контейнера залежностей](dependency-injection-container), і середнє ПЗ буде виконано з параметрами, які йому потрібні. Якщо у вас не зареєстровано контейнер залежностей, буде передано екземпляр `flight\Engine` у `__construct()`.

## Класи середнього ПЗ

Середнє ПЗ можна зареєструвати як клас. Якщо вам потрібна функціональність "after", ви **повинні** використовувати клас.

```php
class MyMiddleware {
	public function before($params) {
		echo 'Middleware first!';
	}

	public function after($params) {
		echo 'Middleware last!';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); // також ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Це відобразить "Middleware first! Here I am! Middleware last!"
```

## Обробка помилок середнього ПЗ

Припустимо, у вас є середнє ПЗ для автентифікації, і ви хочете перенаправити користувача на сторінку входу, якщо він не аутентифікований. У вас є кілька варіантів:

1. Ви можете повернути false з функції середнього ПЗ, і Flight автоматично поверне помилку 403 Заборонено, але без налаштувань.
1. Ви можете перенаправити користувача на сторінку входу за допомогою `Flight::redirect()`.
1. Ви можете створити власну помилку в середньому ПЗ і зупинити виконання маршруту.

### Базовий приклад

Ось простий приклад повернення false:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// оскільки це правда, все просто продовжується
	}
}
```

### Приклад перенаправлення

Ось приклад перенаправлення користувача на сторінку входу:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### Приклад власної помилки

Припустимо, вам потрібно викинути помилку JSON, оскільки ви будуєте API. Ви можете зробити це так:
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->headers['Authorization'];
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'You must be logged in to access this page.'], 403);
			// або
			Flight::json(['error' => 'You must be logged in to access this page.'], 403);
			exit;
			// або
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']);
		}
	}
}
```

## Групування середнього ПЗ

Ви можете додати групу маршрутів, і тоді кожний маршрут у цій групі матиме те саме середнє ПЗ. Це корисно, якщо вам потрібно згрупувати кілька маршрутів, наприклад, за допомогою середнього ПЗ Auth для перевірки API-ключу в заголовку.

```php

// додано в кінці методу group
Flight::group('/api', function() {

	// Цей "порожній" маршрут фактично збігається з /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// Це збігається з /api/users
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Це збігається з /api/users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

Якщо ви хочете застосувати глобальне середнє ПЗ до всіх ваших маршрутів, ви можете додати "порожню" групу:

```php

// додано в кінці методу group
Flight::group('', function() {

	// Це все ще /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// І це все ще /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // або [ new ApiAuthMiddleware() ], те саме
```