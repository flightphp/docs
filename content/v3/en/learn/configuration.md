# Configuration

## Overview 

Flight provides a simple way to configure various aspects of the framework to suit your application's needs. Some are set by default, but you can override them as needed. You can also set your own variables to be used throughout your application.

Clear, layered config (file defaults + environment secrets) also helps [AI coding tools](/learn/ai): agents learn one place for literals and one place for secrets, instead of inventing `$_ENV` reads inside controllers.

## Understanding

You can customize certain behaviors of Flight by setting configuration values
through the `set` method.

```php
Flight::set('flight.log_errors', true);
```

In a structured app (including the [skeleton](https://github.com/flightphp/skeleton)), you typically load project settings from `app/config/config.php` and then apply relevant keys onto the Engine (for example `flight.base_url`, `flight.views.path`). You can also inject a small config object into controllers instead of reading globals everywhere—friendlier for tests and for agents following `AGENTS.md`.

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
- **flight.debug** `bool` - Output detailed error information (exception message, code, and stack trace) in the browser when an error occurs. (default: false)
  - **Never enable this in production** — it leaks internal application details. Use it only for local development or staging.
  - When `false`, a generic `500 Internal Server Error` is shown instead. Pair with `flight.log_errors` to capture errors server-side.
- **flight.allow_method_override** `bool` - Allow the HTTP method to be overridden via the `X-HTTP-Method-Override` request header or a `_method` field in the POST body. (default: true)
  - **Setting this to `false` is recommended** for applications that do not need HTML-form-based method spoofing, as it prevents clients from forging `DELETE` or `PUT` requests through a standard POST form.
  - See [Security](/learn/security#flight-configuration-hardening) for more details.
- **flight.views.path** `string` - Directory containing view template files. (default: ./views)
- **flight.views.extension** `string` - View template file extension. (default: `.php`; the official skeleton sets this to `.twig` when using Twig)
- **View::$restrictToPath** `bool` - Not a `Flight::set()` key. Property on the view engine: when `true`, `render()` / `fetch()` / `exists()` only accept files that resolve inside `flight.views.path`. (default: `false`). **Turn this on** for apps that use Flight's native views. See [Security](/learn/security#viewrestricttopath).
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

Remember that [autoloading](/learn/autoloading) also depends on **folder case** matching your namespaces—especially with the skeleton's `App\` + `app/Controller/` layout.

### Project config and `.env` (skeleton pattern)

Flight core does not require `.env` files. Many apps only use a PHP config array. The official skeleton layers configuration so secrets stay out of git while Runway can still rewrite **literal** config safely:

1. **`.env` / real environment** — secrets and deploy overrides (gitignored).
2. **`app/config/config.php`** — literal PHP array defaults (copied from `config_sample.php`). Prefer **no** `$_ENV[...]` expressions inside this file: tools like `runway config:set` may rewrite it as static values and could bake secrets into the file.
3. **Merge at bootstrap** — env wins for mapped keys; app code reads a config object or `$app->get()`, not `$_ENV` in controllers.

Example shape of `config_sample.php` / `config.php` (simplified):

```php
<?php
// Literals only — secrets belong in .env for the skeleton workflow
return [
	'app' => [
		'env' => 'development',
		'debug' => true,
		'base_url' => '/',
		'timezone' => 'UTC',
	],
	'database' => [
		'driver' => 'sqlite', // or mysql, or '' to disable
		'host' => 'localhost',
		'dbname' => '',
		'user' => '',
		'password' => '',
		'file_path' => __DIR__ . '/../../database.sqlite',
	],
	// ...
];
```

```bash
# .env.example → .env (skeleton)
APP_ENV=development
APP_DEBUG=true
FLIGHT_BASE_URL=/
DB_DRIVER=sqlite
# DB_PASSWORD=...
```

This split is deliberate for [AI-friendly projects](/learn/ai): instructions can say “defaults in `config.php`, secrets in `.env`, inject Config / Engine—never invent env access in a controller.” Existing apps can ignore `.env` entirely and keep a single config file.

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

> **Note:** Just because you can set a variable doesn't mean you should. Use this feature sparingly. The reason why is that anything stored in here becomes a global variable. Global variables are bad because they can be changed from anywhere in your application, making it hard to track down bugs. Additionally this can complicate things like [unit testing](/guides/unit-testing). Prefer constructor injection (as in the skeleton + Dice setup) for services and config that controllers need.

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
- [Installation](/install) - Skeleton config, `.env`, and bootstrap layout.
- [Autoloading](/learn/autoloading) - Namespaces and folder case.
- [Extending Flight](/learn/extending) - How to extend and customize Flight's core functionality.
- [Unit Testing](/guides/unit-testing) - How to write unit tests for your Flight application.
- [AI & Developer Experience](/learn/ai) - `AGENTS.md` and consistent project instructions.
- [Tracy](/awesome-plugins/tracy) - A plugin for advanced error handling and debugging.
- [Tracy Extensions](/awesome-plugins/tracy_extensions) - Extensions for integrating Tracy with Flight.
- [APM](/awesome-plugins/apm) - A plugin for application performance monitoring and error tracking.
- [Security](/learn/security) - Hardening flags and secret handling.

## Troubleshooting
- If you are having problems finding out all the values of your configuration, you can do `var_dump(Flight::get());`
- If Runway or deploy tooling rewrote `config.php`, confirm secrets were not committed—keep them in `.env` or the real environment when using the skeleton pattern.

## Changelog
- Docs – Noted `View::$restrictToPath` next to view path settings.
- Docs – Document skeleton-style config / `.env` layering and Twig view extension default for new projects.
- v3.18.1 - Added `flight.debug` and `flight.allow_method_override` configuration options.
- v3.5.0 - Added configuration for `flight.v2.output_buffering` to support legacy output buffering behavior.
- v2.0 - Core configurations added.
