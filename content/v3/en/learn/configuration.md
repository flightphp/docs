# Configuration

## Overview 

Flight provides a simple way to configure various aspects of the framework to suit your application's needs. Some are set by default, but you can override them as needed. You can also set your own variables to be used throughout your application.

## Understanding

You can customize certain behaviors of Flight by setting configuration values
through the `set` method.

```php
Flight::set('flight.log_errors', true);
```

In the `app/config/config.php` file, you can see all the default configuration variables available to you.

## Basic Usage

### Flight Configuration Options

The following is a list of all the available configuration settings:

- **flight.base_url** `?string` - Override the base url of the request if Flight is running in a subdirectory. (default: null)
- **flight.case_sensitive** `bool` - Case sensitive matching for URLs. (default: false)
- **flight.handle_errors** `bool` - Allow Flight to handle all errors internally. (default: true)
  - If you want Flight to handle errors instead of the default PHP behavior, this needs to be true.
  - If you have [Tracy](/awesome-plugins/tracy) installed, you want to set this to false so Tracy can handle errors.
  - If you have the [APM](/awesome-plugins/apm) plugin installed, you want to set this to true so the APM can log the errors.
- **flight.log_errors** `bool` - Log errors to the web server's error log file. (default: false)
  - If you have [Tracy](/awesome-plugins/tracy) installed, Tracy will log errors based on Tracy configurations, not this configuration.
- **flight.views.path** `string` - Directory containing view template files. (default: ./views)
- **flight.views.extension** `string` - View template file extension. (default: .php)
- **flight.content_length** `bool` - Set the `Content-Length` header. (default: true)
  - If you are using [Tracy](/awesome-plugins/tracy), this needs to be set to false so Tracy can render properly.
- **flight.v2.output_buffering** `bool` - Use legacy output buffering. See [migrating to v3](migrating-to-v3). (default: false)

### Loader Configuration

There is additionally another configuration setting for the loader. This will allow you 
to autoload classes with `_` in the class name.

```php
// Enable class loading with underscores
// Defaulted to true
Loader::$v2ClassLoading = false;
```

### Variables

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

> **Note:** Just because you can set a variable doesn't mean you should. Use this feature sparingly. The reason why is that anything stored in here becomes a global variable. Global variables are bad because they can be changed from anywhere in your application, making it hard to track down bugs. Additionally this can complicate things like [unit testing](/guides/unit-testing).

### Errors and Exceptions

All errors and exceptions are caught by Flight and passed to the `error` method.
if `flight.handle_errors` is set to true.

The default behavior is to send a generic `HTTP 500 Internal Server Error`
response with some error information.

You can [override](/learn/extending) this behavior for your own needs:

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

#### 404 Not Found

When a URL can't be found, Flight calls the `notFound` method. The default
behavior is to send an `HTTP 404 Not Found` response with a simple message.

You can [override](/learn/extending) this behavior for your own needs:

```php
Flight::map('notFound', function () {
  // Handle not found
});
```

## See Also
- [Extending Flight](/learn/extending) - How to extend and customize Flight's core functionality.
- [Unit Testing](/guides/unit-testing) - How to write unit tests for your Flight application.
- [Tracy](/awesome-plugins/tracy) - A plugin for advanced error handling and debugging.
- [Tracy Extensions](/awesome-plugins/tracy_extensions) - Extensions for integrating Tracy with Flight.
- [APM](/awesome-plugins/apm) - A plugin for application performance monitoring and error tracking.

## Troubleshooting
- If you are having problems finding out all the values of your configuration, you can do `var_dump(Flight::get());`

## Changelog
- v3.5.0 - Added configuration for `flight.v2.output_buffering` to support legacy output buffering behavior.
- v2.0 - Core configurations added.