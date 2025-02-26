# Extending

Flight is designed to be an extensible framework. The framework comes with a set
of default methods and components, but it allows you to map your own methods,
register your own classes, or even override existing classes and methods.

If you are looking for a DIC (Dependency Injection Container), hop over to the
[Dependency Injection Container](dependency-injection-container) page.

## Mapping Methods

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

## Registering Classes

To register your own class and configure it, you use the `register` function:

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

Keep in mind that mapped methods have precedence over registered classes. If you
declare both using the same name, only the mapped method will be invoked.

## Logging

Flight does not have a built in logging system, however, it is really easy
to use a logging library with Flight. Here is an example using the Monolog
library:

```php
// index.php or bootstrap.php

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
// In your bootstrap file

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

## Overriding Framework Methods

Flight allows you to override its default functionality to suit your own needs,
without having to modify any code. You can view all the methods you can override [here](/learn/api).

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
// Register your custom class
Flight::register('router', MyRouter::class);

// When Flight loads the Router instance, it will load your class
$myrouter = Flight::router();
```

Framework methods like `map` and `register` however cannot be overridden. You will
get an error if you try to do so.