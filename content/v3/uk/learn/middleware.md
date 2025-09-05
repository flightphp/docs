# Middleware маршруту

Flight підтримує middleware для маршрутів і груп маршрутів. Middleware — це функція, яка виконується перед (або після) зворотного виклику маршруту. Це чудовий спосіб додати перевірки автентифікації API у вашому коді, або перевірити, чи має користувач дозвіл на доступ до маршруту.

## Основне middleware

Ось базовий приклад:

```php
// Якщо ви постачаєте лише анонімну функцію, вона буде виконана перед зворотним викликом маршруту. 
// там немає функцій "after" middleware, крім класів (див. нижче)
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// Це виведе "Middleware first! Here I am!"
```

Є деякі дуже важливі нотатки про middleware, про які ви повинні знати перед їх використанням:
- Функції middleware виконуються в порядку, в якому вони додаються до маршруту. Виконання подібне до того, як [Slim Framework handles this](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Befores виконуються в порядку додавання, а Afters — у зворотному порядку.
- Якщо ваша функція middleware повертає false, все виконання зупиняється і викидається помилка 403 Forbidden. Ви, ймовірно, захочете обробити це більш елегантно за допомогою `Flight::redirect()` або подібного.
- Якщо вам потрібні параметри з вашого маршруту, вони будуть передані у вигляді єдиного масиву до вашої функції middleware. (`function($params) { ... }` або `public function before($params) {}`). Причина в тому, що ви можете структурувати свої параметри в групи, і в деяких з цих груп ваші параметри можуть з'явитися в іншому порядку, що зламає функцію middleware через посилання на неправильний параметр. Таким чином, ви можете доступатися до них за іменем, а не за позицією.
- Якщо ви передаєте лише ім'я middleware, воно автоматично виконуватиметься за допомогою [dependency injection container](dependency-injection-container) і middleware буде виконано з параметрами, які йому потрібні. Якщо у вас не зареєстровано контейнер ін'єкції залежностей, він передасть екземпляр `flight\Engine` у `__construct()`.

## Класи middleware

Middleware також можна реєструвати як клас. Якщо вам потрібна функціональність "after", ви **повинні** використовувати клас.

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

## Обробка помилок middleware

Припустимо, у вас є middleware для автентифікації і ви хочете перенаправити користувача на сторінку логіну, якщо він не аутентифікований. У вас є кілька варіантів:

1. Ви можете повернути false з функції middleware і Flight автоматично поверне помилку 403 Forbidden, але без налаштувань.
1. Ви можете перенаправити користувача на сторінку логіну за допомогою `Flight::redirect()`.
1. Ви можете створити власну помилку в middleware і зупинити виконання маршруту.

### Базовий приклад

Ось простий приклад з return false;:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// оскільки це true, все просто продовжується
	}
}
```

### Приклад перенаправлення

Ось приклад перенаправлення користувача на сторінку логіну:
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

Припустимо, вам потрібно викинути JSON-помилку, бо ви будуєте API. Ви можете зробити це так:
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

## Групування middleware

Ви можете додати групу маршрутів, і тоді кожний маршрут в цій групі матиме те саме middleware. Це корисно, якщо вам потрібно згрупувати кілька маршрутів, наприклад, за middleware Auth для перевірки API-ключа в заголовку.

```php

// додано в кінці методу group
Flight::group('/api', function() {

	// Цей "порожній" маршрут фактично відповідатиме /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// Це відповідатиме /api/users
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Це відповідатиме /api/users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

Якщо ви хочете застосувати глобальне middleware до всіх ваших маршрутів, ви можете додати "порожню" групу:

```php

// додано в кінці методу group
Flight::group('', function() {

	// Це все ще /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// І це все ще /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // або [ new ApiAuthMiddleware() ], те саме
```