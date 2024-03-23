# Configuration

You can customize certain behaviors of Flight by setting configuration values
through the `set` method.

```php
Flight::set('flight.log_errors', true);
```

## Available Configuration Settings

The following is a list of all the available configuration settings:

- **flight.base_url** `?string` - Override the base url of the request. (default: null)
- **flight.case_sensitive** `bool` - Case sensitive matching for URLs. (default: false)
- **flight.handle_errors** `bool` - Allow Flight to handle all errors internally. (default: true)
- **flight.log_errors** `bool` - Log errors to the web server's error log file. (default: false)
- **flight.views.path** `string` - Directory containing view template files. (default: ./views)
- **flight.views.extension** `string` - View template file extension. (default: .php)
- **flight.content_length** `bool` - Set the `Content-Length` header. (default: true)
- **flight.v2.output_buffering** `bool` - Use legacy output buffering. See [migrating to v3](migrating-to-v3). (default: false)

## Variables

Flight allows you to save variables so that they can be used anywhere in your application.

```php
// Save your variable
Flight::set('id', 123);

// Elsewhere in your application
$id = Flight::get('id');
```
To see if a variable has been set you can do:

```php
if (Flight::has('id')) {
  // Do something
}
```

You can clear a variable by doing:

```php
// Clears the id variable
Flight::clear('id');

// Clears all variables
Flight::clear();
```

Flight also uses variables for configuration purposes.

```php
Flight::set('flight.log_errors', true);
```

## Error Handling

### Errors and Exceptions

All errors and exceptions are caught by Flight and passed to the `error` method.
The default behavior is to send a generic `HTTP 500 Internal Server Error`
response with some error information.

You can override this behavior for your own needs:

```php
Flight::map('error', function (Throwable $error) {
  // Handle error
  echo $error->getTraceAsString();
});
```

By default errors are not logged to the web server. You can enable this by
changing the config:

```php
Flight::set('flight.log_errors', true);
```

### Not Found

When a URL can't be found, Flight calls the `notFound` method. The default
behavior is to send an `HTTP 404 Not Found` response with a simple message.

You can override this behavior for your own needs:

```php
Flight::map('notFound', function () {
  // Handle not found
});
```