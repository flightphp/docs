# Extending

## Overview

Flight is designed to be an extensible framework. The framework comes with a set
of default methods and components, but it allows you to map your own methods,
register your own classes, or even override existing classes and methods.

## Understanding

There are 2 ways that you can extend the functionality of Flight:

1. Mapping Methods - This is used to create simple custom methods that you can call
   from anywhere in your application. These are typically used for utility functions
   that you want to be able to call from anywhere in your code. 
2. Registering Classes - This is used to register your own classes with Flight. This is
   typically used for classes that have dependencies or require configuration.

You can override existing framework methods as well to alter their default behavior to better
suite the needs of your project. 

> If you are looking for a DIC (Dependency Injection Container), hop over to the
[Dependency Injection Container](/learn/dependency-injection-container) page.

## Basic Usage

### Overriding Framework Methods

Flight allows you to override its default functionality to suit your own needs,
without having to modify any code. You can view all the methods you can override [below](#mappable-framework-methods).

For example, when Flight cannot match a URL to a route, it invokes the `notFound`
method which sends a generic `HTTP 404` response. You can override this behavior
by using the `map` method:

```php
Flight::map('notFound', function() {
  // Display custom 404 page
  include 'errors/404.html';
});
```

Flight also allows you to replace core components of the framework.
For example you can replace the default Router class with your own custom class:

```php
// create your custom Router class
class MyRouter extends \flight\net\Router {
	// override methods here
	// for example a shortcut for GET requests to remove
	// the pass route feature
	public function get($pattern, $callback, $alias = '') {
		return parent::get($pattern, $callback, false, $alias);
	}
}

// Register your custom class
Flight::register('router', MyRouter::class);

// When Flight loads the Router instance, it will load your class
$myRouter = Flight::router();
$myRouter->get('/hello', function() {
  echo "Hello World!";
}, 'hello_alias');
```

Framework methods like `map` and `register` however cannot be overridden. You will
get an error if you try to do so (again see [below](#mappable-framework-methods) for a list of methods).

### Mappable Framework Methods

The following is the complete set of methods for the framework. It consists of core methods, 
which are regular static methods, and extensible methods, which are mapped methods that can 
be filtered or overridden.

#### Core Methods

These methods are core to the framework and cannot be overridden.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Creates a custom framework method.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Registers a class to a framework method.
Flight::unregister(string $name) // Unregisters a class to a framework method.
Flight::before(string $name, callable $callback) // Adds a filter before a framework method.
Flight::after(string $name, callable $callback) // Adds a filter after a framework method.
Flight::path(string $path) // Adds a path for autoloading classes.
Flight::get(string $key) // Gets a variable set by Flight::set().
Flight::set(string $key, mixed $value) // Sets a variable within the Flight engine.
Flight::has(string $key) // Checks if a variable is set.
Flight::clear(array|string $key = []) // Clears a variable.
Flight::init() // Initializes the framework to its default settings.
Flight::app() // Gets the application object instance
Flight::request() // Gets the request object instance
Flight::response() // Gets the response object instance
Flight::router() // Gets the router object instance
Flight::view() // Gets the view object instance
```

#### Extensible Methods

```php
Flight::start() // Starts the framework.
Flight::stop() // Stops the framework and sends a response.
Flight::halt(int $code = 200, string $message = '') // Stop the framework with an optional status code and message.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Maps a URL pattern to a callback.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Maps a POST request URL pattern to a callback.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Maps a PUT request URL pattern to a callback.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Maps a PATCH request URL pattern to a callback.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Maps a DELETE request URL pattern to a callback.
Flight::group(string $pattern, callable $callback) // Creates grouping for urls, pattern must be a string.
Flight::getUrl(string $name, array $params = []) // Generates a URL based on a route alias.
Flight::redirect(string $url, int $code) // Redirects to another URL.
Flight::download(string $filePath) // Downloads a file.
Flight::render(string $file, array $data, ?string $key = null) // Renders a template file.
Flight::error(Throwable $error) // Sends an HTTP 500 response.
Flight::notFound() // Sends an HTTP 404 response.
Flight::etag(string $id, string $type = 'string') // Performs ETag HTTP caching.
Flight::lastModified(int $time) // Performs last modified HTTP caching.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sends a JSON response.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sends a JSONP response.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sends a JSON response and stops the framework.
Flight::onEvent(string $event, callable $callback) // Registers an event listener.
Flight::triggerEvent(string $event, ...$args) // Triggers an event.
```

Any custom methods added with `map` and `register` can also be filtered. For examples on how to filter these methods, see the [Filtering Methods](/learn/filtering) guide.

#### Extensible Framework Classes

There are several classes you can override functionality on by extending them and
registering your own class. These classes are:

```php
Flight::app() // Application class - extend the flight\Engine class
Flight::request() // Request class - extend the flight\net\Request class
Flight::response() // Response class - extend the flight\net\Response class
Flight::router() // Router class - extend the flight\net\Router class
Flight::view() // View class - extend the flight\template\View class
Flight::eventDispatcher() // Event Dispatcher class - extend the flight\core\Dispatcher class
```

### Mapping Custom Methods

To map your own simple custom method, you use the `map` function:

```php
// Map your method
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// Call your custom method
Flight::hello('Bob');
```

While it is possible to make simple custom methods, it is recommended to just create
standard functions in PHP. This has autocomplete in IDE's and is easier to read.
The equivalent of the above code would be:

```php
function hello(string $name) {
  echo "hello $name!";
}

hello('Bob');
```

This is used more when you need to pass variables into your method to get an expected
value. Using the `register()` method like below is more for passing in configuration
and then calling your pre-configured class.

### Registering Custom Classes

To register your own class and configure it, you use the `register` function. The benefit that this has over map() is that you can reuse the same class when you call this function (would be helpful with `Flight::db()` to share the same instance).

```php
// Register your class
Flight::register('user', User::class);

// Get an instance of your class
$user = Flight::user();
```

The register method also allows you to pass along parameters to your class
constructor. So when you load your custom class, it will come pre-initialized.
You can define the constructor parameters by passing in an additional array.
Here's an example of loading a database connection:

```php
// Register class with constructor parameters
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Get an instance of your class
// This will create an object with the defined parameters
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// and if you needed it later in your code, you just call the same method again
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

If you pass in an additional callback parameter, it will be executed immediately
after class construction. This allows you to perform any set up procedures for your
new object. The callback function takes one parameter, an instance of the new object.

```php
// The callback will be passed the object that was constructed
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

By default, every time you load your class you will get a shared instance.
To get a new instance of a class, simply pass in `false` as a parameter:

```php
// Shared instance of the class
$shared = Flight::db();

// New instance of the class
$new = Flight::db(false);
```

> **Note:** Keep in mind that mapped methods have precedence over registered classes. If you
declare both using the same name, only the mapped method will be invoked.

### Examples

Here are some examples of how you can extend Flight with functionality that's not built into core.

#### Logging

Flight does not have a built in logging system, however, it is really easy
to use a logging library with Flight. Here is an example using the Monolog
library:

```php
// services.php

// Register the logger with Flight
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

Now that it's registered, you can use it in your application:

```php
// In your controller or route
Flight::log()->warning('This is a warning message');
```

This will log a message to the log file you specified. What if you want to log something when an
error occurs? You can use the `error` method:

```php
// In your controller or route
Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// Display your custom error page
	include 'errors/500.html';
});
```

You also could create a basic APM (Application Performance Monitoring) system
using the `before` and `after` methods:

```php
// In your services.php file

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('Request '.Flight::request()->url.' took ' . round($end - $start, 4) . ' seconds');

	// You could also add your request or response headers
	// to log them as well (be careful as this would be a 
	// lot of data if you have a lot of requests)
	Flight::log()->info('Request Headers: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('Response Headers: ' . json_encode(Flight::response()->headers));
});
```

#### Caching

Flight does not have a built in caching system, however, it is really easy
to use a caching library with Flight. Here is an example using the
[PHP File Cache](/awesome-plugins/php_file_cache) library:

```php
// services.php

// Register the cache with Flight
Flight::register('cache', \flight\Cache::class, [ __DIR__ . '/../cache/' ], function(\flight\Cache $cache) {
    $cache->setDevMode(ENVIRONMENT === 'development');
});
```

Now that it's registered, you can use it in your application:

```php
// In your controller or route
$data = Flight::cache()->get('my_cache_key');
if (empty($data)) {
	// Do some processing to get the data
	$data = [ 'some' => 'data' ];
	Flight::cache()->set('my_cache_key', $data, 3600); // cache for 1 hour
}
```

#### Easy DIC Object Instantiation

If you are using a DIC (Dependency Injection Container) in your application,
you can use Flight to help you instantiate your objects. Here is an example using
the [Dice](https://github.com/level-2/Dice) library:

```php
// services.php

// create a new container
$container = new \Dice\Dice;
// don't forget to reassign it to itself like below!
$container = $container->addRule('PDO', [
	// shared means that the same object will be returned each time
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// now we can create a mappable method to create any object. 
Flight::map('make', function($class, $params = []) use ($container) {
	return $container->create($class, $params);
});

// This registers the container handler so Flight knows to use it for controllers/middleware
Flight::registerContainerHandler(function($class, $params) {
	Flight::make($class, $params);
});


// lets say we have the following sample class that takes a PDO object in the constructor
class EmailCron {
	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function send() {
		// code that sends an email
	}
}

// And finally you can create objects using dependency injection
$emailCron = Flight::make(EmailCron::class);
$emailCron->send();
```

Snazzy right?

## See Also
- [Dependency Injection Container](/learn/dependency-injection-container) - How to use a DIC with Flight.
- [File Cache](/awesome-plugins/php_file_cache) - Example of using a caching library with Flight.

## Troubleshooting
- Remember that mapped methods have precedence over registered classes. If you declare both using the same name, only the mapped method will be invoked.

## Changelog
- v2.0 - Initial Release.