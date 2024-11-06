# Routing

> **Note:** Want to understand more about routing? Check out the ["why a framework?"](/learn/why-frameworks) page for a more in-depth explanation.

Basic routing in Flight is done by matching a URL pattern with a callback function or an array of a class and method.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Routes are matched in the order they are defined. The first route to match a request will be invoked.

### Callbacks/Functions
The callback can be any object that is callable. So you can use a regular function:

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### Classes
You can use a static method of a class as well:

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

Or by creating an object first and then calling the method:

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hello, {$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// You also can do this without creating the object first
// Note: No args will be injected into the constructor
Flight::route('/', [ 'Greeting', 'hello' ]);
// Additionally you can use this shorter syntax
Flight::route('/', 'Greeting->hello');
// or
Flight::route('/', Greeting::class.'->hello');
```

#### Dependency Injection via DIC (Dependency Injection Container)
If you want to use dependency injection via a container (PSR-11, PHP-DI, Dice, etc), the
only type of routes where that is available is either directly creating the object yourself
and using the container to create your object or you can use strings to defined the class and
method to call. You can go to the [Dependency Injection](/learn/extending) page for 
more information. 

Here's a quick example:

```php

use flight\database\PdoWrapper;

// Greeting.php
class Greeting
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;
	}

	public function hello(int $id) {
		// do something with $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";
	}
}

// index.php

// Setup the container with whatever params you need
// See the Dependency Injection page for more information on PSR-11
$dice = new \Dice\Dice();

// Don't forget to reassign the variable with '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Register the container handler
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Routes like normal
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// or
Flight::route('/hello/@id', 'Greeting->hello');
// or
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## Method Routing

By default, route patterns are matched against all request methods. You can respond
to specific methods by placing an identifier before the URL.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// You cannot use Flight::get() for routes as that is a method 
//    to get variables, not create a route.
// Flight::post('/', function() { /* code */ });
// Flight::patch('/', function() { /* code */ });
// Flight::put('/', function() { /* code */ });
// Flight::delete('/', function() { /* code */ });
```

You can also map multiple methods to a single callback by using a `|` delimiter:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

Additionally you can grab the Router object which has some helper methods for you to use:

```php

$router = Flight::router();

// maps all methods
$router->map('/', function() {
	echo 'hello world!';
});

// GET request
$router->get('/users', function() {
	echo 'users';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## Regular Expressions

You can use regular expressions in your routes:

```php
Flight::route('/user/[0-9]+', function () {
  // This will match /user/1234
});
```

Although this method is available, it is recommended to use named parameters, or 
named parameters with regular expressions, as they are more readable and easier to maintain.

## Named Parameters

You can specify named parameters in your routes which will be passed along to
your callback function.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

You can also include regular expressions with your named parameters by using
the `:` delimiter:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // This will match /bob/123
  // But will not match /bob/12345
});
```

> **Note:** Matching regex groups `()` with named parameters isn't supported. :'\(

## Optional Parameters

You can specify named parameters that are optional for matching by wrapping
segments in parentheses.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // This will match the following URLS:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Any optional parameters that are not matched will be passed in as `NULL`.

## Wildcards

Matching is only done on individual URL segments. If you want to match multiple
segments you can use the `*` wildcard.

```php
Flight::route('/blog/*', function () {
  // This will match /blog/2000/02/01
});
```

To route all requests to a single callback, you can do:

```php
Flight::route('*', function () {
  // Do something
});
```

## Passing

You can pass execution on to the next matching route by returning `true` from
your callback function.

```php
Flight::route('/user/@name', function (string $name) {
  // Check some condition
  if ($name !== "Bob") {
    // Continue to next route
    return true;
  }
});

Flight::route('/user/*', function () {
  // This will get called
});
```

## Route Aliasing

You can assign an alias to a route, so that the URL can dynamically be generated later in your code (like a template for instance).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// later in code somewhere
Flight::getUrl('user_view', [ 'id' => 5 ]); // will return '/users/5'
```

This is especially helpful if your URL happens to change. In the above example, lets say that users was moved to `/admin/users/@id` instead.
With aliasing in place, you don't have to change anywhere you reference the alias because the alias will now return `/admin/users/5` like in the 
example above.

Route aliasing still works in groups as well:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// later in code somewhere
Flight::getUrl('user_view', [ 'id' => 5 ]); // will return '/users/5'
```

## Route Info

If you want to inspect the matching route information, you can request for the route
object to be passed to your callback by passing in `true` as the third parameter in
the route method. The route object will always be the last parameter passed to your
callback function.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array of HTTP methods matched against
  $route->methods;

  // Array of named parameters
  $route->params;

  // Matching regular expression
  $route->regex;

  // Contains the contents of any '*' used in the URL pattern
  $route->splat;

  // Shows the url path....if you really need it
  $route->pattern;

  // Shows what middleware is assigned to this
  $route->middleware;

  // Shows the alias assigned to this route
  $route->alias;
}, true);
```

## Route Grouping

There may be times when you want to group related routes together (such as `/api/v1`).
You can do this by using the `group` method:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Matches /api/v1/users
  });

  Flight::route('/posts', function () {
	// Matches /api/v1/posts
  });
});
```

You can even nest groups of groups:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() gets variables, it doesn't set a route! See object context below
	Flight::route('GET /users', function () {
	  // Matches GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Matches POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Matches PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() gets variables, it doesn't set a route! See object context below
	Flight::route('GET /users', function () {
	  // Matches GET /api/v2/users
	});
  });
});
```

### Grouping with Object Context

You can still use route grouping with the `Engine` object in the following way:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // user the $router variable
  $router->get('/users', function () {
	// Matches GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Matches POST /api/v1/posts
  });
});
```

## Resource Routing

You can create a set of routes for a resource using the `resource` method. This will create
a set of routes for a resource that follows the RESTful conventions.

To create a resource, do the following:

```php
Flight::resource('/users', UsersController::class);
```

And what will happen in the background is it will create the following routes:

```php
[
      'index' => 'GET ',
      'create' => 'GET /create',
      'store' => 'POST ',
      'show' => 'GET /@id',
      'edit' => 'GET /@id/edit',
      'update' => 'PUT /@id',
      'destroy' => 'DELETE /@id'
]
```

And your controller will look like this:

```php
class UsersController
{
    public function index(): void
    {
    }

    public function show(string $id): void
    {
    }

    public function create(): void
    {
    }

    public function store(): void
    {
    }

    public function edit(string $id): void
    {
    }

    public function update(string $id): void
    {
    }

    public function destroy(string $id): void
    {
    }
}
```

> **Note**: You can view the newly added routes with `runway` by running `php runway routes`.

### Customizing Resource Routes

There are a few options to configure the resource routes.

#### Alias Base

You can configure the `aliasBase`. By default the alias is the last part of the URL specified.
For example `/users/` would result in an `aliasBase` of `users`. When these routes are created,
the aliases are `users.index`, `users.create`, etc. If you want to change the alias, set the `aliasBase`
to the value you want.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Only and Except

You can also specify which routes you want to create by using the `only` and `except` options.

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

These are basically whitelisting and blacklisting options so you can specify which routes you want to create.

#### Middleware

You can also specify middleware to be run on each of the routes created by the `resource` method.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## Streaming

You can now stream responses to the client using the `streamWithHeaders()` method. 
This is useful for sending large files, long running processes, or generating large responses. 
Streaming a route is handled a little differently than a regular route.

> **Note:** Streaming responses is only available if you have [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) set to false.

### Stream with Manual Headers

You can stream a response to the client by using the `stream()` method on a route. If you 
do this, you must set all the methods by hand before you output anything to the client.
This is done with the `header()` php function or the `Flight::response()->setRealHeader()` method.

```php
Flight::route('/@filename', function($filename) {

	// obviously you would sanitize the path and whatnot.
	$fileNameSafe = basename($filename);

	// If you have additional headers to set here after the route has executed
	// you must define them before anything is echoed out.
	// They must all be a raw call to the header() function or 
	// a call to Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// or
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// Error catching and whatnot
	if(empty($fileData)) {
		Flight::halt(404, 'File not found');
	}

	// manually set the content length if you'd like
	header('Content-Length: '.filesize($filename));

	// Stream the data to the client
	echo $fileData;

// This is the magic line here
})->stream();
```

### Stream with Headers

You can also use the `streamWithHeaders()` method to set the headers before you start streaming.

```php
Flight::route('/stream-users', function() {

	// you can add any additional headers you want here
	// you just must use header() or Flight::response()->setRealHeader()

	// however you pull your data, just as an example...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// This is required to send the data to the client
		ob_flush();
	}
	echo '}';

// This is how you'll set the headers before you start streaming.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// optional status code, defaults to 200
	'status' => 200
]);
```