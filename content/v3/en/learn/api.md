# Framework API Methods

Flight is designed to be easy to use and understand. The following is the complete
set of methods for the framework. It consists of core methods, which are regular
static methods, and extensible methods, which are mapped methods that can be filtered
or overridden.

## Core Methods

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

## Extensible Methods

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
```

Any custom methods added with `map` and `register` can also be filtered. For examples on how to map these methods, see the [Extending Flight](/learn/extending) guide.
