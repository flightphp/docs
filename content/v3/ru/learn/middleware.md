# Middleware

## Обзор

Flight поддерживает middleware для маршрутов и групп маршрутов. Middleware — это часть вашего приложения, где код выполняется до (или после) обратного вызова маршрута. Это отличный способ добавить проверки аутентификации API в ваш код или убедиться, что пользователь имеет разрешение на доступ к маршруту.

## Понимание

Middleware может значительно упростить ваше приложение. Вместо сложного наследования абстрактных классов или переопределения методов middleware позволяет контролировать маршруты, присваивая им вашу пользовательскую логику приложения. Вы можете думать о middleware как о сэндвиче. У вас хлеб снаружи, а затем слои ингредиентов, такие как салат, помидоры, мясо и сыр. Затем представьте, что каждый запрос похож на укус сэндвича, где вы сначала едите внешние слои и продвигаетесь к центру.

Вот визуализация того, как работает middleware. Затем мы покажем вам практический пример того, как это функционирует.

```text
Запрос пользователя по URL /api ----> 
	Middleware->before() выполняется ----->
		Вызываемый метод, прикреплённый к /api, выполняется, и ответ генерируется ------>
	Middleware->after() выполняется ----->
Пользователь получает ответ от сервера
```

А вот практический пример:

```text
Пользователь переходит по URL /dashboard
	LoggedInMiddleware->before() выполняется
		before() проверяет наличие действительной сессии входа
			если да, ничего не делать и продолжить выполнение
			если нет, перенаправить пользователя на /login
				Вызываемый метод, прикреплённый к /api, выполняется, и ответ генерируется
	LoggedInMiddleware->after() ничего не определено, поэтому позволяет выполнению продолжиться
Пользователь получает HTML дашборда от сервера
```

### Порядок выполнения

Функции middleware выполняются в порядке их добавления к маршруту. Выполнение аналогично тому, [как Slim Framework обрабатывает это](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).

Методы `before()` выполняются в порядке добавления, а методы `after()` — в обратном порядке.

Пример: Middleware1->before(), Middleware2->before(), Middleware2->after(), Middleware1->after().

## Базовое использование

Вы можете использовать middleware как любой вызываемый метод, включая анонимную функцию или класс (рекомендуется).

### Анонимная функция

Вот простой пример:

```php
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// Это выведет "Middleware first! Here I am!"
```

> **Примечание:** При использовании анонимной функции интерпретируется только метод `before()`. Вы **не можете** определить поведение `after()` с анонимным классом.

### Использование классов

Middleware можно (и следует) регистрировать как класс. Если вам нужна функциональность "after", вы **должны** использовать класс.

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
// также ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Это отобразит "Middleware first! Here I am! Middleware last!"
```

Вы также можете просто указать имя класса middleware, и он будет создан экземпляр.

```php
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware(MyMiddleware::class); 
```

> **Примечание:** Если вы передаёте только имя middleware, оно автоматически будет выполнено [контейнером внедрения зависимостей](dependency-injection-container), и middleware будет выполнен с параметрами, которые ему нужны. Если у вас не зарегистрирован контейнер внедрения зависимостей, по умолчанию будет передан экземпляр `flight\Engine` в `__construct(Engine $app)`.

### Использование маршрутов с параметрами

Если вам нужны параметры из маршрута, они будут переданы в виде единого массива в функцию middleware. (`function($params) { ... }` или `public function before($params) { ... }`). Причина в том, что вы можете структурировать параметры в группы, и в некоторых из этих групп параметры могут появляться в другом порядке, что нарушит функцию middleware при обращении к неправильному параметру. Таким образом, вы можете обращаться к ним по имени, а не по позиции.

```php
use flight\Engine;

class RouteSecurityMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$clientId = $params['clientId'];

		// jobId может быть передан или нет
		$jobId = $params['jobId'] ?? 0;

		// возможно, если нет ID задания, вам не нужно ничего искать.
		if($jobId === 0) {
			return;
		}

		// выполнить поиск какого-то рода в вашей базе данных
		$isValid = !!$this->app->db()->fetchField("SELECT 1 FROM client_jobs WHERE client_id = ? AND job_id = ?", [ $clientId, $jobId ]);

		if($isValid !== true) {
			$this->app->halt(400, 'You are blocked, muahahaha!');
		}
	}
}

// routes.php
$router->group('/client/@clientId/job/@jobId', function(Router $router) {

	// Эта группа ниже всё ещё получает middleware родителя
	// Но параметры передаются в одном единственном массиве 
	// в middleware.
	$router->group('/job/@jobId', function(Router $router) {
		$router->get('', [ JobController::class, 'view' ]);
		$router->put('', [ JobController::class, 'update' ]);
		$router->delete('', [ JobController::class, 'delete' ]);
		// больше маршрутов...
	});
}, [ RouteSecurityMiddleware::class ]);
```

### Группировка маршрутов с middleware

Вы можете добавить группу маршрутов, и каждый маршрут в этой группе будет иметь одинаковый middleware. Это полезно, если вам нужно сгруппировать множество маршрутов, например, с помощью middleware Auth для проверки API-ключа в заголовке.

```php

// добавлено в конце метода группы
Flight::group('/api', function() {

	// Этот "пустой" маршрут на самом деле соответствует /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// Это соответствует /api/users
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Это соответствует /api/users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

Если вы хотите применить глобальный middleware ко всем вашим маршрутам, вы можете добавить "пустую" группу:

```php

// добавлено в конце метода группы
Flight::group('', function() {

	// Это всё ещё /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// И это всё ещё /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // или [ new ApiAuthMiddleware() ], одно и то же
```

### Распространённые случаи использования

#### Валидация API-ключа
Если вы хотите защитить маршруты `/api`, проверяя, что API-ключ правильный, вы можете легко справиться с этим с помощью middleware.

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

		// выполнить поиск в вашей базе данных для API-ключа
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
	// больше маршрутов...
}, [ ApiMiddleware::class ]);
```

Теперь все ваши API-маршруты защищены этим middleware валидации API-ключа, который вы настроили! Если вы добавите больше маршрутов в группу роутера, они мгновенно получат ту же защиту!

#### Валидация входа в систему

Хотите ли вы защитить некоторые маршруты, чтобы они были доступны только пользователям, которые вошли в систему? Это легко достижимо с помощью middleware!

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
	// больше маршрутов...
}, [ LoggedInMiddleware::class ]);
```

#### Валидация параметров маршрута

Хотите ли вы защитить своих пользователей от изменения значений в URL для доступа к данным, к которым они не должны иметь доступ? Это можно решить с помощью middleware!

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

		// выполнить поиск какого-то рода в вашей базе данных
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
	// больше маршрутов...
}, [ RouteSecurityMiddleware::class ]);
```

## Обработка выполнения middleware

Предположим, у вас есть middleware аутентификации, и вы хотите перенаправить пользователя на страницу входа, если он не аутентифицирован. У вас есть несколько вариантов:

1. Вы можете вернуть false из функции middleware, и Flight автоматически вернёт ошибку 403 Forbidden, но без настройки.
1. Вы можете перенаправить пользователя на страницу входа с помощью `Flight::redirect()`.
1. Вы можете создать пользовательскую ошибку внутри middleware и остановить выполнение маршрута.

### Простой и прямолинейный

Вот простой пример `return false;` :

```php
class MyMiddleware {
	public function before($params) {
		$hasUserKey = Flight::session()->exists('user');
		if ($hasUserKey === false) {
			return false;
		}

		// поскольку это true, всё просто продолжается
	}
}
```

### Пример перенаправления

Вот пример перенаправления пользователя на страницу входа:
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

### Пример пользовательской ошибки

Предположим, вам нужно выбросить JSON-ошибку, потому что вы строите API. Вы можете сделать это так:
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->getHeader('Authorization');
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'You must be logged in to access this page.'], 403);
			// или
			Flight::json(['error' => 'You must be logged in to access this page.'], 403);
			exit;
			// или
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']);
		}
	}
}
```

## См. также
- [Маршрутизация](/learn/routing) - Как сопоставлять маршруты с контроллерами и рендерить представления.
- [Запросы](/learn/requests) - Понимание того, как обрабатывать входящие запросы.
- [Ответы](/learn/responses) - Как настраивать HTTP-ответы.
- [Внедрение зависимостей](/learn/dependency-injection-container) - Упрощение создания и управления объектами в маршрутах.
- [Почему фреймворк?](/learn/why-frameworks) - Понимание преимуществ использования фреймворка вроде Flight.
- [Пример стратегии выполнения middleware](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)

## Устранение неисправностей
- Если у вас есть перенаправление в middleware, но ваше приложение не перенаправляется, убедитесь, что вы добавили инструкцию `exit;` в middleware.

## Журнал изменений
- v3.1: Добавлена поддержка middleware.