# Middleware

## Огляд

Flight підтримує middleware для маршрутів та груп маршрутів. Middleware — це частина вашого додатка, де код виконується перед 
(або після) виклику маршруту. Це чудовий спосіб додати перевірки автентифікації API у ваш код або перевірити, 
чи має користувач дозвіл на доступ до маршруту.

## Розуміння

Middleware може значно спростити ваш додаток. Замість складної спадкування абстрактних класів або перевизначення методів, middleware 
дозволяє контролювати ваші маршрути, призначаючи власну логіку додатка до них. Ви можете уявити middleware як 
сендвіч. У вас є хліб ззовні, а потім шари інгредієнтів, як-от салат, помідори, м'ясо та сир. Потім уявіть,
що кожен запит — це укус сендвіча, де ви спочатку їсте зовнішні шари та просуваєтеся до серцевини.

Ось візуалізація того, як працює middleware. Потім ми покажемо вам практичний приклад того, як це функціонує.

```text
User request at URL /api ----> 
	Middleware->before() executed ----->
		Callable/method attached to /api executed and response generated ------>
	Middleware->after() executed ----->
User receives response from server
```

І ось практичний приклад:

```text
User navigates to URL /dashboard
	LoggedInMiddleware->before() executes
		before() checks for valid logged in session
			if yes do nothing and continue execution
			if no redirect the user to /login
				Callable/method attached to /api executed and response generated
	LoggedInMiddleware->after() has nothing defined so it lets execution continue
User receives dashboard HTML from server
```

### Порядок виконання

Функції middleware виконуються в порядку, в якому вони додаються до маршруту. Виконання подібне до того, як [Slim Framework обробляє це](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).

Методи `before()` виконуються в порядку додавання, а методи `after()` виконуються у зворотному порядку.

Наприклад: Middleware1->before(), Middleware2->before(), Middleware2->after(), Middleware1->after().

## Базове використання

Ви можете використовувати middleware як будь-який викличний метод, включаючи анонімну функцію або клас (рекомендовано)

### Анонімна функція

Ось простий приклад:

```php
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// This will output "Middleware first! Here I am!"
```

> **Примітка:** При використанні анонімної функції інтерпретується лише метод `before()`. Ви **не можете** визначити поведінку `after()` з анонімним класом.

### Використання класів

Middleware можна (і слід) реєструвати як клас. Якщо вам потрібна функціональність "after", ви **повинні** використовувати клас.

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
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); 
// also ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// This will display "Middleware first! Here I am! Middleware last!"
```

Ви також можете просто визначити ім'я класу middleware, і він буде створений екземпляр.

```php
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware(MyMiddleware::class); 
```

> **Примітка:** Якщо ви передаєте лише ім'я middleware, воно автоматично буде виконане за допомогою [контейнера залежностей](dependency-injection-container), і middleware буде виконано з параметрами, які йому потрібні. Якщо у вас не зареєстровано контейнер залежностей, за замовчуванням буде передано екземпляр `flight\Engine` у `__construct(Engine $app)`.

### Використання маршрутів з параметрами

Якщо вам потрібні параметри з вашого маршруту, вони будуть передані як єдиний масив до вашої функції middleware. (`function($params) { ... }` або `public function before($params) { ... }`). Причина в тому, що ви можете структурувати параметри в групи, і в деяких з цих груп ваші параметри можуть з'являтися в іншому порядку, що зламає функцію middleware через звернення до неправильного параметра. Таким чином, ви можете звертатися до них за ім'ям, а не за позицією.

```php
use flight\Engine;

class RouteSecurityMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$clientId = $params['clientId'];

		// jobId may or may not be passed in
		$jobId = $params['jobId'] ?? 0;

		// maybe if there's no job ID, you don't need to lookup anything.
		if($jobId === 0) {
			return;
		}

		// perform a lookup of some kind in your database
		$isValid = !!$this->app->db()->fetchField("SELECT 1 FROM client_jobs WHERE client_id = ? AND job_id = ?", [ $clientId, $jobId ]);

		if($isValid !== true) {
			$this->app->halt(400, 'You are blocked, muahahaha!');
		}
	}
}

// routes.php
$router->group('/client/@clientId/job/@jobId', function(Router $router) {

	// This group below still gets the parent middleware
	// But the parameters are passed in one single array 
	// in the middleware.
	$router->group('/job/@jobId', function(Router $router) {
		$router->get('', [ JobController::class, 'view' ]);
		$router->put('', [ JobController::class, 'update' ]);
		$router->delete('', [ JobController::class, 'delete' ]);
		// more routes...
	});
}, [ RouteSecurityMiddleware::class ]);
```

### Групування маршрутів з middleware

Ви можете додати групу маршрутів, і кожен маршрут у цій групі матиме той самий middleware. Це 
корисно, якщо вам потрібно згрупувати багато маршрутів, наприклад, за допомогою middleware Auth для перевірки ключа API в заголовку.

```php

// added at the end of the group method
Flight::group('/api', function() {

	// This "empty" looking route will actually match /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// This will match /api/users
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// This will match /api/users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

Якщо ви хочете застосувати глобальний middleware до всіх ваших маршрутів, ви можете додати "порожню" групу:

```php

// added at the end of the group method
Flight::group('', function() {

	// This is still /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// And this is still /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // or [ new ApiAuthMiddleware() ], same thing
```

### Поширені випадки використання

#### Валідація ключа API
Якщо ви хочете захистити ваші маршрути `/api`, перевіряючи, чи правильний ключ API, ви можете легко обробити це за допомогою middleware.

```php
use flight\Engine;

class ApiMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}
	
	public function before(array $params) {
		$authorizationHeader = $this->app->request()->getHeader('Authorization');
		$apiKey = str_replace('Bearer ', '', $authorizationHeader);

		// do a lookup in your database for the api key
		$apiKeyHash = hash('sha256', $apiKey);
		$hasValidApiKey = !!$this->db()->fetchField("SELECT 1 FROM api_keys WHERE hash = ? AND valid_date >= NOW()", [ $apiKeyHash ]);

		if($hasValidApiKey !== true) {
			$this->app->jsonHalt(['error' => 'Invalid API Key']);
		}
	}
}

// routes.php
$router->group('/api', function(Router $router) {
	$router->get('/users', [ ApiController::class, 'getUsers' ]);
	$router->get('/companies', [ ApiController::class, 'getCompanies' ]);
	// more routes...
}, [ ApiMiddleware::class ]);
```

Тепер усі ваші API-маршрути захищені цим middleware валідації ключа API, яке ви налаштували! Якщо ви додасте більше маршрутів до групи роутера, вони миттєво отримають той самий захист!

#### Валідація входу в систему

Чи хочете ви захистити деякі маршрути, щоб вони були доступні лише користувачам, які увійшли в систему? Це легко досягти за допомогою middleware!

```php
use flight\Engine;

class LoggedInMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$session = $this->app->session();
		if($session->get('logged_in') !== true) {
			$this->app->redirect('/login');
			exit;
		}
	}
}

// routes.php
$router->group('/admin', function(Router $router) {
	$router->get('/dashboard', [ DashboardController::class, 'index' ]);
	$router->get('/clients', [ ClientController::class, 'index' ]);
	// more routes...
}, [ LoggedInMiddleware::class ]);
```

#### Валідація параметрів маршруту

Чи хочете ви захистити ваших користувачів від зміни значень в URL для доступу до даних, до яких вони не повинні мати доступ? Це можна вирішити за допомогою middleware!

```php
use flight\Engine;

class RouteSecurityMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$clientId = $params['clientId'];
		$jobId = $params['jobId'];

		// perform a lookup of some kind in your database
		$isValid = !!$this->app->db()->fetchField("SELECT 1 FROM client_jobs WHERE client_id = ? AND job_id = ?", [ $clientId, $jobId ]);

		if($isValid !== true) {
			$this->app->halt(400, 'You are blocked, muahahaha!');
		}
	}
}

// routes.php
$router->group('/client/@clientId/job/@jobId', function(Router $router) {
	$router->get('', [ JobController::class, 'view' ]);
	$router->put('', [ JobController::class, 'update' ]);
	$router->delete('', [ JobController::class, 'delete' ]);
	// more routes...
}, [ RouteSecurityMiddleware::class ]);
```

## Обробка виконання middleware

Припустимо, у вас є middleware автентифікації, і ви хочете перенаправити користувача на сторінку входу, якщо він не 
автентифікований. У вас є кілька варіантів:

1. Ви можете повернути false з функції middleware, і Flight автоматично поверне помилку 403 Forbidden, але без кастомізації.
1. Ви можете перенаправити користувача на сторінку входу за допомогою `Flight::redirect()`.
1. Ви можете створити власну помилку в middleware і зупинити виконання маршруту.

### Простий і прямий

Ось простий приклад `return false;`:

```php
class MyMiddleware {
	public function before($params) {
		$hasUserKey = Flight::session()->exists('user');
		if ($hasUserKey === false) {
			return false;
		}

		// since it's true, everything just keeps on going
	}
}
```

### Приклад перенаправлення

Ось приклад перенаправлення користувача на сторінку входу:
```php
class MyMiddleware {
	public function before($params) {
		$hasUserKey = Flight::session()->exists('user');
		if ($hasUserKey === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### Приклад власної помилки

Припустимо, вам потрібно кинути JSON-помилку, оскільки ви створюєте API. Ви можете зробити це так:
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->getHeader('Authorization');
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'You must be logged in to access this page.'], 403);
			// or
			Flight::json(['error' => 'You must be logged in to access this page.'], 403);
			exit;
			// or
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']);
		}
	}
}
```

## Дивіться також
- [Routing](/learn/routing) - How to map routes to controllers and render views.
- [Requests](/learn/requests) - Understanding how to handle incoming requests.
- [Responses](/learn/responses) - How to customize HTTP responses.
- [Dependency Injection](/learn/dependency-injection-container) - Simplifying object creation and management in routes.
- [Why a Framework?](/learn/why-frameworks) - Understanding the benefits of using a framework like Flight.
- [Middleware Execution Strategy Example](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)

## Вирішення проблем
- If you have a redirect in your middleware, but your app doesn't seem to be redirecting, make sure you add an `exit;` statement in your middleware.

## Changelog
- v3.1: Added support for middleware.