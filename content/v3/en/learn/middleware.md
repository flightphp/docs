# Middleware

## Overview

Flight supports route and group route middleware. Middleware is a part of your application where code is executed before 
(or after) the route callback. This is a great way to add API authentication checks in your code, or to validate that 
the user has permission to access the route.

## Understanding

Middleware can greatly simplify your app. Instead of complex abstract class inheritance or method overrides, middleware 
allows you to control your routes by assigning your custom app logic against them. You can think of middleware much like
a sandwich. You have bread on the outside, and then layers of topics like lettuce, tomatoes, meats and cheese. Then imagine
like each request is like taking a bit of the sandwich where you eat the outer layers first and work your way to the core.

Here is a visual of how middleware works. Then we'll show you a practical example of how this functions.

```text
User request at URL /api ----> 
	Middleware->before() executed ----->
		Callable/method attached to /api executed and response generated ------>
	Middleware->after() executed ----->
User receives response from server
```

And here's a practical example:

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

### Execution Order

Middleware functions are executed in the order they are added to the route. The execution is similar to how [Slim Framework handles this](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).

`before()` methods are executed in the order added, and `after()` methods are executed in reverse order.

Ex: Middleware1->before(), Middleware2->before(), Middleware2->after(), Middleware1->after().

## Basic Usage

You can use middleware as any callable method including an anonymous function or a class (recommended)

### Anonymous Function

Here's a simple example:

```php
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// This will output "Middleware first! Here I am!"
```

> **Note:** When using an anonymous function, the only method that is interpreted is a `before()` method. You **cannot** define `after()` behavior with an anonymous class.

### Using Classes

Middleware can (and should) be registered as a class. If you need the "after" functionality, you **must** use a class.

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

You also can just define the middleware class name and it will instantiate the class.

```php
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware(MyMiddleware::class); 
```

> **Note:** If you pass in just the name of the middleware, it will automatically be executed by the [dependency injection container](dependency-injection-container) and the middleware will be executed with the parameters it needs. If you don't have a dependency injection container registered, it will pass in the `flight\Engine` instance into the `__construct(Engine $app)` by default.

### Using Routes with Parameters

If you need parameters from your route, they will be passed in a single array to your middleware function. (`function($params) { ... }` or `public function before($params) { ... }`). The reason for this is that you can structure your parameters into groups and in some of those groups, your parameters may actually show up in a different order which would break the middleware function by referring to the wrong parameter. This way, you can access them by name instead of position.

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

### Grouping Routes with Middleware

You can add a route group, and then every route in that group will have the same middleware as well. This is 
useful if you need to group a bunch of routes by say an Auth middleware to check the API key in the header.

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

If you want to apply a global middleware to all your routes, you can add an "empty" group:

```php

// added at the end of the group method
Flight::group('', function() {

	// This is still /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// And this is still /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // or [ new ApiAuthMiddleware() ], same thing
```

### Common Use Cases

#### API Key Validation
If you wanted to protect your `/api` routes by verifying the API key is correct, you can easily handle that with middleware.

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

Now all your API routes are protected by this API key validation middleware you have setup! If you put more routes into the router group, they will instantly have the same protection!

#### Logged In Validation

Do you want to protect some routes from only being available to users who are logged in? That can easily be achieved with middleware!

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

#### Route Parameter Validation

Do you want to protect your users from changing values in the URL to access data that they shouldn't? That scan be solved with middleware!

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

## Handling Middleware Execution

Let's say you have an auth middleware and you want to redirect the user to a login page if they are not 
authenticated. You have a couple of options at your disposal:

1. You can return false from the middleware function and Flight will automatically return a 403 Forbidden error, but have no customization.
1. You can redirect the user to a login page using `Flight::redirect()`.
1. You can create a custom error within the middleware and halt execution of the route.

### Simple and Straightforward

Here is a simple `return false;` example:

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

### Redirect Example

Here is an example of redirecting the user to a login page:
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

### Custom Error Example

Let's say you need to throw a JSON error because you're building an API. You can do that like this:
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

## See Also
- [Routing](/learn/routing) - How to map routes to controllers and render views.
- [Requests](/learn/requests) - Understanding how to handle incoming requests.
- [Responses](/learn/responses) - How to customize HTTP responses.
- [Dependency Injection](/learn/dependency-injection-container) - Simplifying object creation and management in routes.
- [Why a Framework?](/learn/why-frameworks) - Understanding the benefits of using a framework like Flight.
- [Middleware Execution Strategy Example](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)

## Troubleshooting
- If you have a redirect in your middleware, but your app doesn't seem to be redirecting, make sure you add an `exit;` statement in your middleware.

## Changelog
- v3.1: Added support for middleware.
