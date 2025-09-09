# Routing

## Overview
Routing in Flight PHP lets you map URLs to PHP callbacks, making it easy to build fast, simple, and extensible web applications. Flight’s routing is designed for minimal overhead and maximum flexibility, with zero configuration required.

## Understanding the Concept
Routing is the process of connecting incoming HTTP requests to specific PHP functions or methods. In Flight, you define routes by specifying a URL pattern and a callback. This keeps your code organized and lets you respond to different URLs and HTTP methods with ease. Flight’s routing system is beginner-friendly, but also powerful enough for advanced use cases like RESTful APIs, dependency injection, and streaming responses.

## Common Use Cases

### 1. Basic Route Definition
```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

### 2. Callback Types
You can use functions, static methods, or object methods:
```php
// Function
function hello() { echo 'hello world!'; }
Flight::route('/', 'hello');

// Static method
class Greeting {
    public static function hello() { echo 'hello world!'; }
}
Flight::route('/', [ 'Greeting', 'hello' ]);

// Object method
$greeting = new Greeting();
Flight::route('/', [ $greeting, 'hello' ]);
```

### 3. Method Routing
Specify HTTP methods by prefixing the pattern:
```php
Flight::route('GET /', function () { echo 'GET request'; });
Flight::route('POST /', function () { echo 'POST request'; });
Flight::route('GET|POST /', function () { echo 'GET or POST'; });
```

### 4. Named and Optional Parameters
```php
Flight::route('/@name/@id', function ($name, $id) {
  echo "hello, $name ($id)!";
});

Flight::route('/blog(/@year(/@month(/@day)))', function($year = null, $month = null, $day = null) {
  // Matches /blog, /blog/2012, /blog/2012/12, /blog/2012/12/10
});
```

### 5. Wildcard Routes
```php
Flight::route('/blog/*', function () {
  // Matches /blog/2000/02/01
});
Flight::route('*', function () {
  // Matches any request
});
```

### 6. Route Grouping
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

### 7. Route Aliasing
```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
// Later
Flight::getUrl('user_view', [ 'id' => 5 ]); // returns '/users/5'
```

## Advanced Use Cases

### 1. Resource Routing (RESTful Controllers)
```php
Flight::resource('/users', UsersController::class);
// Creates routes for index, create, store, show, edit, update, destroy
```
Customize with options:
```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

### 2. Dependency Injection via DIC
```php
use flight\database\PdoWrapper;
class Greeting {
    protected PdoWrapper $pdoWrapper;
    public function __construct(PdoWrapper $pdoWrapper) {
        $this->pdoWrapper = $pdoWrapper;
    }
    public function hello(int $id) {
        $name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
        echo "Hello, world! My name is {$name}!";
    }
}
// Setup container and register
$dice = new \Dice\Dice();
$dice = $dice->addRule('flight\database\PdoWrapper', [
    'shared' => true,
    'constructParams' => [ 'mysql:host=localhost;dbname=test', 'root', 'password' ]
]);
Flight::registerContainerHandler(function($class, $params) use ($dice) {
    return $dice->create($class, $params);
});
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
```

### 3. Streaming Responses
Manual headers:
```php
Flight::route('/@filename', function($filename) {
    $fileNameSafe = basename($filename);
    header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
    $fileData = file_get_contents('/some/path/'.$fileNameSafe);
    if(empty($fileData)) Flight::halt(404, 'File not found');
    header('Content-Length: '.filesize($filename));
    echo $fileData;
})->stream();
```
With headers:
```php
Flight::route('/stream-users', function() {
    $users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");
    echo '{';
    while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($user);
        ob_flush();
    }
    echo '}';
})->streamWithHeaders([
    'Content-Type' => 'application/json',
    'Content-Disposition' => 'attachment; filename="users.json"',
    'status' => 200
]);
```

### 4. Route Inspection
```php
Flight::route('/', function(\flight\net\Route $route) {
  // $route->methods, $route->params, $route->regex, $route->splat, $route->pattern, $route->middleware, $route->alias
}, true);
// Or
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;
  // Inspect $route
});
```

### 5. Router Object Usage
```php
$router = Flight::router();
$router->map('/', function() { echo 'hello world!'; });
$router->get('/users', function() { echo 'users'; });
```

### 6. Regular Expressions in Routes
```php
Flight::route('/user/[0-9]+', function () {
  // Matches /user/1234
});
Flight::route('/@name/@id:[0-9]{3}', function ($name, $id) {
  // Matches /bob/123
});
```

## Uncommon Use Cases

### 1. Passing Execution to Next Route
```php
Flight::route('/user/@name', function ($name) {
  if ($name !== "Bob") {
    return true; // Pass to next matching route
  }
});
Flight::route('/user/*', function () {
  // Will get called if above returns true
});
```

## Notes/Warnings
- Routes are matched in the order they are defined; first match wins.
- `Flight::get()` is for getting variables, not for defining routes.
- Named parameters are matched by order, not by name.
- Regex group matching with positional parameters is not supported.
- `executedRoute` is only set after a route executes.
- Streaming only works if output buffering is disabled (`flight.v2.output_buffering = false`).
- Route aliasing helps keep URLs flexible if you change patterns.

## See Also
- [Middleware](/learn/middleware)
- [Dependency Injection](/learn/extending)
- [Why Frameworks](/learn/why-frameworks)
- [Migrating to v3](/learn/migrating-to-v3)
- [preg_match on php.net](https://www.php.net/manual/en/function.preg-match.php)

## Troubleshooting
- Route not matching? Check the order, method, and pattern.
- Optional parameters not matched are passed as NULL.
- Streaming not working? Check output buffering setting.
- Route aliasing: update alias if you change the URL pattern.

## Changelog
- v3.x: Added streaming, resource routing, middleware grouping, output buffering changes.
- v2.x: Basic routing, grouping, aliasing, wildcards, named/optional parameters.
