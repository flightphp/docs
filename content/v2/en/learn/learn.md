# Learn

This page is a guide to learning Flight. It covers the basics of the framework and how to use it.

## <a name="routing"></a> Routing

Routing in Flight is done by matching a URL pattern with a callback function.

``` php
Flight::route('/', function(){
    echo 'hello world!';
});
```

The callback can be any object that is callable. So you can use a regular function:

``` php
function hello(){
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

Or a class method:

``` php
class Greeting {
    public static function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', array('Greeting','hello'));
```

Or an object method:

``` php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hello, {$this->name}!";
    }
}

$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

Routes are matched in the order they are defined. The first route to match a request will be invoked.

### Method Routing

By default, route patterns are matched against all request methods. You can respond to specific methods by placing an identifier before the URL.

``` php
Flight::route('GET /', function(){
    echo 'I received a GET request.';
});

Flight::route('POST /', function(){
    echo 'I received a POST request.';
});
```

You can also map multiple methods to a single callback by using a `|` delimiter:

``` php
Flight::route('GET|POST /', function(){
    echo 'I received either a GET or a POST request.';
});
```

### Regular Expressions

You can use regular expressions in your routes:

``` php
Flight::route('/user/[0-9]+', function(){
    // This will match /user/1234
});
```

### Named Parameters

You can specify named parameters in your routes which will be passed along to your callback function.

``` php
Flight::route('/@name/@id', function($name, $id){
    echo "hello, $name ($id)!";
});
```

You can also include regular expressions with your named parameters by using the `:` delimiter:

``` php
Flight::route('/@name/@id:[0-9]{3}', function($name, $id){
    // This will match /bob/123
    // But will not match /bob/12345
});
```

### Optional Parameters

You can specify named parameters that are optional for matching by wrapping segments in parentheses.

``` php
Flight::route('/blog(/@year(/@month(/@day)))', function($year, $month, $day){
    // This will match the following URLS:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
});
```

Any optional parameters that are not matched will be passed in as NULL.

### Wildcards

Matching is only done on individual URL segments. If you want to match multiple segments you can use the `*` wildcard.

``` php
Flight::route('/blog/*', function(){
    // This will match /blog/2000/02/01
});
```

To route all requests to a single callback, you can do:

``` php
Flight::route('*', function(){
    // Do something
});
```

### Passing

You can pass execution on to the next matching route by returning `true` from your callback function.

``` php
Flight::route('/user/@name', function($name){
    // Check some condition
    if ($name != "Bob") {
        // Continue to next route
        return true;
    }
});

Flight::route('/user/*', function(){
    // This will get called
});
```

### Route Info

If you want to inspect the matching route information, you can request for the route
object to be passed to your callback by passing in `true` as the third parameter in
the route method. The route object will always be the last parameter passed to your
callback function.

``` php
Flight::route('/', function($route){
    // Array of HTTP methods matched against
    $route->methods;

    // Array of named parameters
    $route->params;

    // Matching regular expression
    $route->regex;

    // Contains the contents of any '*' used in the URL pattern
    $route->splat;
}, true);
```
### Route Grouping

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

#### Grouping with Object Context

You can still use route grouping with the `Engine` object in the following way:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {
  $router->get('/users', function () {
	// Matches GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Matches POST /api/v1/posts
  });
});
```

### Route Aliasing

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

## <a name="extending"></a> Extending

Flight is designed to be an extensible framework. The framework comes with a set
of default methods and components, but it allows you to map your own methods,
register your own classes, or even override existing classes and methods.

### Mapping Methods

To map your own custom method, you use the `map` function:

``` php
// Map your method
Flight::map('hello', function($name){
    echo "hello $name!";
});

// Call your custom method
Flight::hello('Bob');
```

### Registering Classes

To register your own class, you use the `register` function:

``` php
// Register your class
Flight::register('user', 'User');

// Get an instance of your class
$user = Flight::user();
```

The register method also allows you to pass along parameters to your class
constructor. So when you load your custom class, it will come pre-initialized.
You can define the constructor parameters by passing in an additional array.
Here's an example of loading a database connection:

``` php
// Register class with constructor parameters
Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=test','user','pass'));

// Get an instance of your class
// This will create an object with the defined parameters
//
//     new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();
```

If you pass in an additional callback parameter, it will be executed immediately
after class construction. This allows you to perform any set up procedures for your
new object. The callback function takes one parameter, an instance of the new object.

``` php
// The callback will be passed the object that was constructed
Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=test','user','pass'),
  function($db){
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

By default, every time you load your class you will get a shared instance.
To get a new instance of a class, simply pass in `false` as a parameter:

``` php
// Shared instance of the class
$shared = Flight::db();

// New instance of the class
$new = Flight::db(false);
```

Keep in mind that mapped methods have precedence over registered classes. If you
declare both using the same name, only the mapped method will be invoked.

## <a name="overriding"></a> Overriding

Flight allows you to override its default functionality to suit your own needs,
without having to modify any code.

For example, when Flight cannot match a URL to a route, it invokes the `notFound`
method which sends a generic `HTTP 404` response. You can override this behavior
by using the `map` method:

``` php
Flight::map('notFound', function(){
    // Display custom 404 page
    include 'errors/404.html';
});
```

Flight also allows you to replace core components of the framework.
For example you can replace the default Router class with your own custom class:

``` php
// Register your custom class
Flight::register('router', 'MyRouter');

// When Flight loads the Router instance, it will load your class
$myrouter = Flight::router();
```

Framework methods like `map` and `register` however cannot be overridden. You will
get an error if you try to do so.

## <a name="filtering"></a> Filtering

Flight allows you to filter methods before and after they are called. There are no
predefined hooks you need to memorize. You can filter any of the default framework
methods as well as any custom methods that you've mapped.

A filter function looks like this:

``` php
function(&$params, &$output) {
    // Filter code
}
```

Using the passed in variables you can manipulate the input parameters and/or the output.

You can have a filter run before a method by doing:

``` php
Flight::before('start', function(&$params, &$output){
    // Do something
});
```

You can have a filter run after a method by doing:

``` php
Flight::after('start', function(&$params, &$output){
    // Do something
});
```

You can add as many filters as you want to any method. They will be called in the
order that they are declared.

Here's an example of the filtering process:

``` php
// Map a custom method
Flight::map('hello', function($name){
    return "Hello, $name!";
});

// Add a before filter
Flight::before('hello', function(&$params, &$output){
    // Manipulate the parameter
    $params[0] = 'Fred';
});

// Add an after filter
Flight::after('hello', function(&$params, &$output){
    // Manipulate the output
    $output .= " Have a nice day!";
});

// Invoke the custom method
echo Flight::hello('Bob');
```

This should display:

``` html
Hello Fred! Have a nice day!
```

If you have defined multiple filters, you can break the chain by returning `false`
in any of your filter functions:

``` php
Flight::before('start', function(&$params, &$output){
    echo 'one';
});

Flight::before('start', function(&$params, &$output){
    echo 'two';

    // This will end the chain
    return false;
});

// This will not get called
Flight::before('start', function(&$params, &$output){
    echo 'three';
});
```

Note, core methods such as `map` and `register` cannot be filtered because they
are called directly and not invoked dynamically.

## <a name="variables"></a> Variables

Flight allows you to save variables so that they can be used anywhere in your application.

``` php
// Save your variable
Flight::set('id', 123);

// Elsewhere in your application
$id = Flight::get('id');
```
To see if a variable has been set you can do:

``` php
if (Flight::has('id')) {
     // Do something
}
```

You can clear a variable by doing:

``` php
// Clears the id variable
Flight::clear('id');

// Clears all variables
Flight::clear();
```

Flight also uses variables for configuration purposes.

``` php
Flight::set('flight.log_errors', true);
```

## <a name="views"></a> Views

Flight provides some basic templating functionality by default. To display a view
template call the `render` method with the name of the template file and optional
template data:

``` php
Flight::render('hello.php', array('name' => 'Bob'));
```

The template data you pass in is automatically injected into the template and can
be reference like a local variable. Template files are simply PHP files. If the
content of the `hello.php` template file is:

``` php
Hello, '<?php echo $name; ?>'!
```

The output would be:

``` html
Hello, Bob!
```

You can also manually set view variables by using the set method:

``` php
Flight::view()->set('name', 'Bob');
```

The variable `name` is now available across all your views. So you can simply do:

``` php
Flight::render('hello');
```

Note that when specifying the name of the template in the render method, you can
leave out the `.php` extension.

By default Flight will look for a `views` directory for template files. You can
set an alternate path for your templates by setting the following config:

``` php
Flight::set('flight.views.path', '/path/to/views');
```

### Layouts

It is common for websites to have a single layout template file with interchanging
content. To render content to be used in a layout, you can pass in an optional
parameter to the `render` method.

``` php
Flight::render('header', array('heading' => 'Hello'), 'header_content');
Flight::render('body', array('body' => 'World'), 'body_content');
```

Your view will then have saved variables called `header_content` and `body_content`.
You can then render your layout by doing:

``` php
Flight::render('layout', array('title' => 'Home Page'));
```

If the template files looks like this:

`header.php`:

``` php
<h1><?php echo $heading; ?></h1>
```

`body.php`:

``` php
<div><?php echo $body; ?></div>
```

`layout.php`:

``` php
<html>
<head>
<title><?php echo $title; ?></title>
</head>
<body>
<?php echo $header_content; ?>
<?php echo $body_content; ?>
</body>
</html>
```

The output would be:

``` html
<html>
<head>
<title>Home Page</title>
</head>
<body>
<h1>Hello</h1>
<div>World</div>
</body>
</html>
```

### Custom Views

Flight allows you to swap out the default view engine simply by registering your
own view class. Here's how you would use the [Smarty](http://www.smarty.net/)
template engine for your views:

``` php
// Load Smarty library
require './Smarty/libs/Smarty.class.php';

// Register Smarty as the view class
// Also pass a callback function to configure Smarty on load
Flight::register('view', 'Smarty', array(), function($smarty){
    $smarty->template_dir = './templates/';
    $smarty->compile_dir = './templates_c/';
    $smarty->config_dir = './config/';
    $smarty->cache_dir = './cache/';
});

// Assign template data
Flight::view()->assign('name', 'Bob');

// Display the template
Flight::view()->display('hello.tpl');
```

For completeness, you should also override Flight's default render method:

``` php
Flight::map('render', function($template, $data){
    Flight::view()->assign($data);
    Flight::view()->display($template);
});
```

## <a name="errorhandling"></a> Error Handling

### Errors and Exceptions

All errors and exceptions are caught by Flight and passed to the `error` method.
The default behavior is to send a generic `HTTP 500 Internal Server Error`
response with some error information.

You can override this behavior for your own needs:

``` php
Flight::map('error', function(Exception $ex){
    // Handle error
    echo $ex->getTraceAsString();
});
```

By default errors are not logged to the web server. You can enable this by
changing the config:

``` php
Flight::set('flight.log_errors', true);
```

### Not Found

When a URL can't be found, Flight calls the `notFound` method. The default
behavior is to send an `HTTP 404 Not Found` response with a simple message.

You can override this behavior for your own needs:

``` php
Flight::map('notFound', function(){
    // Handle not found
});
```

## <a name="redirects"></a> Redirects

You can redirect the current request by using the `redirect` method and passing
in a new URL:

``` php
Flight::redirect('/new/location');
```

By default Flight sends a HTTP 303 status code. You can optionally set a
custom code:

``` php
Flight::redirect('/new/location', 401);
```

## <a name="requests"></a> Requests

Flight encapsulates the HTTP request into a single object, which can be
accessed by doing:

``` php
$request = Flight::request();
```

The request object provides the following properties:

``` html
url - The URL being requested
base - The parent subdirectory of the URL
method - The request method (GET, POST, PUT, DELETE)
referrer - The referrer URL
ip - IP address of the client
ajax - Whether the request is an AJAX request
scheme - The server protocol (http, https)
user_agent - Browser information
type - The content type
length - The content length
query - Query string parameters
data - Post data or JSON data
cookies - Cookie data
files - Uploaded files
secure - Whether the connection is secure
accept - HTTP accept parameters
proxy_ip - Proxy IP address of the client
```

You can access the `query`, `data`, `cookies`, and `files` properties
as arrays or objects.

So, to get a query string parameter, you can do:

``` php
$id = Flight::request()->query['id'];
```

Or you can do:

``` php
$id = Flight::request()->query->id;
```

### RAW Request Body

To get the raw HTTP request body, for example when dealing with PUT requests, you can do:

``` php
$body = Flight::request()->getBody();
```

### JSON Input

If you send a request with the type `application/json` and the data `{"id": 123}` it will be available
from the `data` property:

``` php
$id = Flight::request()->data->id;
```

## <a name="stopping"></a> Stopping

You can stop the framework at any point by calling the `halt` method:

``` php
Flight::halt();
```

You can also specify an optional `HTTP` status code and message:

``` php
Flight::halt(200, 'Be right back...');
```

Calling `halt` will discard any response content up to that point. If you want to stop
the framework and output the current response, use the `stop` method:

``` php
Flight::stop();
```

## <a name="httpcaching"></a> HTTP Caching

Flight provides built-in support for HTTP level caching. If the caching condition
is met, Flight will return an HTTP `304 Not Modified` response. The next time the
client requests the same resource, they will be prompted to use their locally
cached version.

### Last-Modified

You can use the `lastModified` method and pass in a UNIX timestamp to set the date
and time a page was last modified. The client will continue to use their cache until
the last modified value is changed.

``` php
Flight::route('/news', function(){
    Flight::lastModified(1234567890);
    echo 'This content will be cached.';
});
```

### ETag

`ETag` caching is similar to `Last-Modified`, except you can specify any id you
want for the resource:

``` php
Flight::route('/news', function(){
    Flight::etag('my-unique-id');
    echo 'This content will be cached.';
});
```

Keep in mind that calling either `lastModified` or `etag` will both set and check the
cache value. If the cache value is the same between requests, Flight will immediately
send an `HTTP 304` response and stop processing.

## <a name="json"></a> JSON

Flight provides support for sending JSON and JSONP responses. To send a JSON response you
pass some data to be JSON encoded:

``` php
Flight::json(array('id' => 123));
```

For JSONP requests you, can optionally pass in the query parameter name you are
using to define your callback function:

``` php
Flight::jsonp(array('id' => 123), 'q');
```

So, when making a GET request using `?q=my_func`, you should receive the output:

``` json
my_func({"id":123});
```

If you don't pass in a query parameter name it will default to `jsonp`.

## <a name="configuration"></a> Configuration

You can customize certain behaviors of Flight by setting configuration values
through the `set` method.

``` php
Flight::set('flight.log_errors', true);
```

The following is a list of all the available configuration settings:

``` html 
flight.base_url - Override the base url of the request. (default: null)
flight.case_sensitive - Case sensitive matching for URLs. (default: false)
flight.handle_errors - Allow Flight to handle all errors internally. (default: true)
flight.log_errors - Log errors to the web server's error log file. (default: false)
flight.views.path - Directory containing view template files. (default: ./views)
flight.views.extension - View template file extension. (default: .php)
```

## <a name="frameworkmethods"></a> Framework Methods

Flight is designed to be easy to use and understand. The following is the complete
set of methods for the framework. It consists of core methods, which are regular
static methods, and extensible methods, which are mapped methods that can be filtered
or overridden.

### Core Methods

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Creates a custom framework method.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Registers a class to a framework method.
Flight::before(string $name, callable $callback) // Adds a filter before a framework method.
Flight::after(string $name, callable $callback) // Adds a filter after a framework method.
Flight::path(string $path) // Adds a path for autoloading classes.
Flight::get(string $key) // Gets a variable.
Flight::set(string $key, mixed $value) // Sets a variable.
Flight::has(string $key) // Checks if a variable is set.
Flight::clear(array|string $key = []) // Clears a variable.
Flight::init() // Initializes the framework to its default settings.
Flight::app() // Gets the application object instance
```

### Extensible Methods

```php
Flight::start() // Starts the framework.
Flight::stop() // Stops the framework and sends a response.
Flight::halt(int $code = 200, string $message = '') // Stop the framework with an optional status code and message.
Flight::route(string $pattern, callable $callback, bool $pass_route = false) // Maps a URL pattern to a callback.
Flight::group(string $pattern, callable $callback) // Creates groupping for urls, pattern must be a string.
Flight::redirect(string $url, int $code) // Redirects to another URL.
Flight::render(string $file, array $data, ?string $key = null) // Renders a template file.
Flight::error(Throwable $error) // Sends an HTTP 500 response.
Flight::notFound() // Sends an HTTP 404 response.
Flight::etag(string $id, string $type = 'string') // Performs ETag HTTP caching.
Flight::lastModified(int $time) // Performs last modified HTTP caching.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sends a JSON response.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sends a JSONP response.
```

Any custom methods added with `map` and `register` can also be filtered.


## <a name="frameworkinstance"></a> Framework Instance

Instead of running Flight as a global static class, you can optionally run it
as an object instance.

``` php
require 'flight/autoload.php';

use flight\Engine;

$app = new Engine();

$app->route('/', function(){
    echo 'hello world!';
});

$app->start();
```

So instead of calling the static method, you would call the instance method with
the same name on the Engine object.
