# Security

## Overview

Security is a big deal when it comes to web applications. You want to make sure that your application is secure and that your users' data is 
safe. Flight provides a number of features to help you secure your web applications.

The official [skeleton](https://github.com/flightphp/skeleton) also ships a dedicated **`SECURITY.md`** and security-headers middleware so [AI coding tools](/learn/ai) (and humans) have one deliberate place for secrets, headers, and XSS/SQL rules—separate from general coding style in `AGENTS.md`.

## Understanding

There are a number of common security threats that you should be aware of when building web applications. Some of the most common threats
include:
- Cross Site Request Forgery (CSRF)
- Cross Site Scripting (XSS)
- SQL Injection
- Cross Origin Resource Sharing (CORS)

[Templates](/learn/templates) help with XSS by escaping output by default (Twig and Latte do this; use that advantage). [Sessions](/awesome-plugins/session) can help with CSRF by storing a CSRF token in the user's session as outlined below. Using prepared statements with PDO—or helpers on [SimplePdo](/learn/simple-pdo)—helps prevent SQL injection. CORS can be handled with a simple hook before `Flight::start()` is called.

All of these methods work together to help keep your web applications secure. It should always be at the forefront of your mind to learn and understand security best practices. Do not ask an AI assistant to "disable CSP" or weaken headers just to make a page load without understanding the tradeoff.

## Basic Usage

### Headers

HTTP headers are one of the easiest ways to secure your web applications. You can use headers to prevent clickjacking, XSS, and other attacks. 
There are several ways that you can add these headers to your application.

Two great websites to check for the security of your headers are [securityheaders.com](https://securityheaders.com/) and 
[observatory.mozilla.org](https://observatory.mozilla.org/). After you setup the below code, you can easily verify that your headers are working with those two websites.

The skeleton includes `App\Middleware\SecurityHeadersMiddleware` (CSP with a per-request nonce, frame options, HSTS, and more). Prefer extending that deliberately over turning headers off.

#### Add By Hand

You can manually add these headers by using the `header` method on the `Flight\Response` object.
```php
// Set the X-Frame-Options header to prevent clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Set the Content-Security-Policy header to prevent XSS
// Note: this header can get very complex, so you'll want
//  to consult examples on the internet for your application
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Set the X-XSS-Protection header to prevent XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Set the X-Content-Type-Options header to prevent MIME sniffing
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Set the Referrer-Policy header to control how much referrer information is sent
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Set the Strict-Transport-Security header to force HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Set the Permissions-Policy header to control what features and APIs can be used
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

These can be added at the top of your `routes.php` or `index.php` files.

#### Add as a Filter

You can also add them in a filter/hook like the following: 

```php
// Add the headers in a filter
Flight::before('start', function() {
	Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
	Flight::response()->header("Content-Security-Policy", "default-src 'self'");
	Flight::response()->header('X-XSS-Protection', '1; mode=block');
	Flight::response()->header('X-Content-Type-Options', 'nosniff');
	Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
	Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
	Flight::response()->header('Permissions-Policy', 'geolocation=()');
});
```

#### Add as a Middleware

You can also add them as a middleware class which provides the greatest flexibility for which routes to apply this to. In general, these headers should be applied to all HTML and API responses.

Skeleton-style path and namespace (**folder case matches `App\Middleware`**):

```php
// app/Middleware/SecurityHeadersMiddleware.php

namespace App\Middleware;

use flight\Engine;

class SecurityHeadersMiddleware
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function before(array $params): void
	{
		$response = $this->app->response();
		// Prefer a CSP nonce from bootstrap when you have inline scripts (skeleton sets csp_nonce)
		$nonce = $this->app->get('csp_nonce');
		$csp = $nonce
			? "default-src 'self'; script-src 'self' 'nonce-{$nonce}'; style-src 'self' 'nonce-{$nonce}'"
			: "default-src 'self'";

		$response->header('X-Frame-Options', 'SAMEORIGIN');
		$response->header('Content-Security-Policy', $csp);
		$response->header('X-XSS-Protection', '1; mode=block');
		$response->header('X-Content-Type-Options', 'nosniff');
		$response->header('Referrer-Policy', 'no-referrer-when-downgrade');
		$response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		$response->header('Permissions-Policy', 'geolocation=()');
	}
}

// app/config/routes.php — empty string group = global middleware for all routes
use App\Middleware\SecurityHeadersMiddleware;
use flight\net\Router;

$router->group('', function (Router $router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// more routes
}, [SecurityHeadersMiddleware::class]);
```

Older projects may still use `app/middlewares` and `app\middlewares`; that works if folders match. New skeleton apps use **`app/Middleware/`** and **`App\Middleware`**. See [Autoloading](/learn/autoloading).

### Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF) is a type of attack where a malicious website can make a user's browser send a request to your website. 
This can be used to perform actions on your website without the user's knowledge. Flight does not provide a built-in CSRF protection 
mechanism, but you can easily implement your own by using middleware.

#### Setup

First you need to generate a CSRF token and store it in the user's session. You can then use this token in your forms and check it when 
the form is submitted. We'll use the [flightphp/session](/awesome-plugins/session) plugin to manage sessions.

```php
// Generate a CSRF token and store it in the user's session
// (assuming you've created a session object at attached it to Flight)
// see the session documentation for more information
Flight::register('session', flight\Session::class);

// You only need to generate a single token per session (so it works 
// across multiple tabs and requests for the same user)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### Using the default PHP Flight Template

```html
<!-- Use the CSRF token in your form -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- other form fields -->
</form>
```

##### Using Twig (skeleton default)

Register a Twig function or pass the token into every form view. Minimal example with a global + form field:

```php
// When configuring Twig (e.g. services.php)
$twig->addGlobal('csrf_token', $app->session()->get('csrf_token'));
```

```html
{# app/views/form.twig #}
<form method="post">
	<input type="hidden" name="csrf_token" value="{{ csrf_token }}">
	{# other fields #}
</form>
```

##### Using Latte

You can also set a custom function to output the CSRF token in your Latte templates.

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// other configurations...

	// Set a custom function to output the CSRF token
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

And now in your Latte templates you can use the `csrf()` function to output the CSRF token.

```html
<form method="post">
	{csrf()}
	<!-- other form fields -->
</form>
```

#### Check the CSRF Token

You can check the CSRF token using several methods.

##### Middleware

```php
// app/Middleware/CsrfMiddleware.php

namespace App\Middleware;

use flight\Engine;

class CsrfMiddleware
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function before(array $params): void
	{
		if($this->app->request()->method == 'POST') {
			$token = $this->app->request()->data->csrf_token;
			if($token !== $this->app->session()->get('csrf_token')) {
				$this->app->halt(403, 'Invalid CSRF token');
			}
		}
	}
}

// routes.php
use App\Middleware\CsrfMiddleware;

$router->group('', function ($router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// more routes
}, [CsrfMiddleware::class]);
```

##### Event Filters

```php
// This middleware checks if the request is a POST request and if it is, it checks if the CSRF token is valid
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// capture the csrf token from the form values
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
			// or for a JSON response
			Flight::jsonHalt(['error' => 'Invalid CSRF token'], 403);
		}
	}
});
```

### Cross Site Scripting (XSS)

Cross Site Scripting (XSS) is a type of attack where a malicious form input can inject code into your website. Most of these opportunities come 
from form values that your end users will fill out. You should **never** trust output from your users! Always assume all of them are the 
best hackers in the world. They can inject malicious JavaScript or HTML into your page. This code can be used to steal information from your 
users or perform actions on your website. Using Flight's view class or a templating engine like [Twig](/awesome-plugins/twig) or [Latte](/awesome-plugins/latte), you can easily escape output to prevent XSS attacks.

```php
// Let's assume the user is clever as tries to use this as their name
$name = '<script>alert("XSS")</script>';

// This will escape the output
Flight::view()->set('name', $name);
// This will output: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Twig (skeleton default) and Latte auto-escape by default — prefer them over raw PHP echo
Flight::render('template', ['name' => $name]);
// Twig: {{ name }}  → escaped
// Avoid |raw / unescaped output unless the content is fully trusted
```

### SQL Injection

SQL Injection is a type of attack where a malicious user can inject SQL code into your database. This can be used to steal information 
from your database or perform actions on your database. Again you should **never** trust input from your users! Always assume they are 
out for blood. Use prepared statements—[SimplePdo](/learn/simple-pdo) helpers make this the default path.

```php
// Assuming you have Flight::db() registered as SimplePdo (or inject SimplePdo in the controller)
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// SimplePdo (preferred) — one-liners with bound parameters
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Same idea with ? placeholders
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

In skeleton-style controllers, prefer constructor injection of `SimplePdo` over `Flight::db()` so tests and AI-generated code stay consistent ([DIC](/learn/dependency-injection-container)).

#### Insecure Example

The below is why we use SQL prepared statements to protect from innocent examples like the below:

```php
// end user fills out a web form.
// for the value of the form, the hacker puts in something like this:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// After the query is build it looks like this
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// It looks strange, but it's a valid query that will work. In fact,
// it's a very common SQL injection attack that will return all users.

var_dump($users); // this will dump all users in the database, not just the one single username
```

### Secrets and configuration

- Put secrets in **`.env`** (or the real environment), not in committed `config.php` samples.
- Skeleton rule: literal defaults in `config.php`; merge env at bootstrap; **do not** read `$_ENV` inside controllers—inject config instead. See [Configuration](/learn/configuration).
- Never commit API keys, DB passwords, or session encryption keys. Point AI tools at **`SECURITY.md`** so they do not invent insecure shortcuts.

### JSONP Callback Validation

If you use Flight's `Flight::jsonp()` method, be aware that Flight validates the JSONP callback parameter name against a strict allowlist regex (`/^[A-Za-z_$][\w$.]{0,127}$/`). Any callback name that does not match this pattern will cause Flight to throw an exception, preventing injection of arbitrary JavaScript through a malicious callback value.

This validation is built in and requires no additional configuration, but it is worth knowing about when debugging unexpected errors from JSONP endpoints.

### CORS

Cross-Origin Resource Sharing (CORS) is a mechanism that allows many resources (e.g., fonts, JavaScript, etc.) on a web page to be 
requested from another domain outside the domain from which the resource originated. Flight does not have built in functionality, 
but this can easily be handled with a hook to run before the `Flight::start()` method is called.

```php
// app/Utils/CorsUtil.php  (skeleton: PascalCase Utils folder → App\Utils)

namespace App\Utils;

use flight\Engine;

class CorsUtil
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function set(array $params = []): void
	{
		$request = $this->app->request();
		$response = $this->app->response();
		if ($request->getVar('HTTP_ORIGIN') !== '') {
			$this->allowOrigins();
			$response->header('Access-Control-Allow-Credentials', 'true');
			$response->header('Access-Control-Max-Age', '86400');
		}

		if ($request->method === 'OPTIONS') {
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_METHOD') !== '') {
				$response->header(
					'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD'
				);
			}
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS') !== '') {
				$response->header(
					"Access-Control-Allow-Headers",
					$request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS')
				);
			}

			$response->status(200);
			$response->send();
			exit;
		}
	}

	private function allowOrigins(): void
	{
		// customize your allowed hosts here.
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		$request = $this->app->request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $allowed, true) === true) {
			$response = $this->app->response();
			$response->header("Access-Control-Allow-Origin", $request->getVar('HTTP_ORIGIN'));
		}
	}
}

// bootstrap / routes — run before start
$app = Flight::app();
$cors = new \App\Utils\CorsUtil($app);
$app->before('start', [ $cors, 'set' ]);
```

### Flight Configuration Hardening

Flight exposes several engine settings that have direct security implications. Setting these correctly is one of the easiest ways to harden your application.

#### `flight.allow_method_override`

By default, Flight allows clients to override the HTTP method of a request using either the `X-HTTP-Method-Override` header or a `_method` field in a POST body. While this is handy for HTML forms that can only send `GET`/`POST`, it can be dangerous if you are not expecting it — an attacker could forge `DELETE` or `PUT` requests through a regular form.

If your application does not rely on this behaviour (e.g. you are building an API consumed by modern clients or JavaScript frontends that can send any HTTP verb), you should disable it:

```php
// In your index.php or bootstrap file, before Flight::start()
Flight::set('flight.allow_method_override', false);
```

The default value is `true` for backwards compatibility, but **setting it to `false` is strongly recommended** for any application that does not explicitly need the override feature.

#### `flight.debug`

Flight has a `flight.debug` setting that controls whether detailed error information (exception message, code, and full stack trace) is rendered in the browser when an unhandled exception occurs. The default is `false`, which means only a generic `500 Internal Server Error` message is shown — no internal details are leaked to the client.

Never enable this on a production server. Use it only locally or in a staging environment:

```php
// Safe for local development only — NEVER in production
Flight::set('flight.debug', true);
```

When `flight.debug` is `false` (the default), you can still capture errors by enabling `flight.log_errors`:

```php
// Log errors server-side without exposing them to the client
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
```

#### `flight.views.restrict_to_path`

Flight's built-in `View` class will happily include a template from an absolute path, or from a relative name that climbs out of `flight.views.path` (for example with `../`). That is intentional for apps that deliberately share templates across folders, but it is also a path-traversal risk if a template name ever comes from untrusted input.

`flight.views.restrict_to_path` is **off by default** so existing apps keep working. Turn it on unless you have a documented reason not to:

```php
// In your index.php or bootstrap file, before Flight::start()
Flight::set('flight.views.restrict_to_path', true);
```

Engine applies that setting onto `View::$restrictToPath` when the view is created (same pattern as `flight.views.path` and `flight.views.extension`). With it on:

- `render()` and `fetch()` only include files whose real path sits inside the configured views directory (symlinks that point outside are rejected too).
- `exists()` returns `false` for those same paths instead of throwing.
- `getTemplate()` itself is unchanged — it still returns paths the way it always has.
- A blocked file throws `Template file is outside the views path.` A missing file still throws the existing `Template file not found: ...` message.

If you use Twig or Latte with their own filesystem loaders pointed at your views directory, those engines already contain templates to that root. Still turn this on for Flight's native `View` so any code that calls `Flight::view()->render()` / `fetch()` gets the same protection. The official [skeleton](https://github.com/flightphp/skeleton) enables it in bootstrap.

#### Recommended production configuration

```php
// index.php or applied from app config / bootstrap
Flight::set('flight.allow_method_override', false);
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
Flight::set('flight.views.restrict_to_path', true);
```

### Error Handling
Hide sensitive error details in production to avoid leaking info to attackers. On production, log errors instead of displaying them with `display_errors` set to `0`.

```php
// In your bootstrap.php or index.php

// add this to your app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Disable error display
    ini_set('log_errors', 1);     // Log errors instead
    ini_set('error_log', '/path/to/error.log');
}

// In your routes or controllers
// Use Flight::halt() for controlled error responses
Flight::halt(403, 'Access denied');
```

### Input Sanitization
Never trust user input. Sanitize it using [filter_var](https://www.php.net/manual/en/function.filter-var.php) before processing to prevent malicious data from sneaking in. Prefer reading input via `$app->request()` (or `Flight::request()`) rather than raw `$_GET` / `$_POST` in app code.

```php

// Lets assume a $_POST request with $_POST['input'] and $_POST['email']

// Sanitize a string input
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Sanitize an email
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### Password Hashing
Store passwords securely and verify them safely using PHP’s built-in functions like [password_hash](https://www.php.net/manual/en/function.password-hash.php) and [password_verify](https://www.php.net/manual/en/function.password-verify.php). Passwords should never be stored in plain text, nor should they be encrypted with reversible methods. Hashing ensures that even if your database is compromised, the actual passwords remain protected.

```php
$password = Flight::request()->data->password;
// Hash a password when storing (e.g., during registration)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Verify a password (e.g., during login)
if (password_verify($password, $stored_hash)) {
    // Password matches
}
```

### Rate Limiting
Protect against brute force attacks or denial-of-service attacks by limiting request rates with a cache.

```php
// Assuming you have flightphp/cache installed and registered
// Using flightphp/cache in a filter
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Too many requests');
    }
    
    $cache->set($key, $attempts + 1, 60); // Reset after 60 seconds
});
```

## See Also
- [Sessions](/awesome-plugins/session) - How to manage user sessions securely.
- [Templates](/learn/templates) - Twig/Latte auto-escape and XSS.
- [SimplePdo](/learn/simple-pdo) - Database helpers with prepared statements.
- [PdoWrapper](/learn/pdo-wrapper) - Deprecated; use SimplePdo for new code.
- [Middleware](/learn/middleware) - How to use middleware for simplifying the process of adding security headers.
- [Configuration](/learn/configuration) - `.env` vs literal config, production flags.
- [AI & Developer Experience](/learn/ai) - Keep security policy in `SECURITY.md` for agents.
- [Responses](/learn/responses) - How to customize HTTP responses with secure headers.
- [Requests](/learn/requests) - How to handle and sanitize user input.
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - PHP function for input sanitization.
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - PHP function for secure password hashing.
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - PHP function for verifying hashed passwords.

## Troubleshooting
- Refer to the "See Also" section above for troubleshooting information related to issues with components of the Flight Framework.
- If CSP blocks your scripts, add a nonce (skeleton pattern) or allowlist specific origins—do not set `script-src *` without a plan.

## Changelog
- Docs – Skeleton `App\Middleware`, Twig CSRF/XSS notes, SimplePdo, secrets/`.env`, and `SECURITY.md` for AI-friendly projects.
- Docs – Documented `flight.views.restrict_to_path` under Flight Configuration Hardening (opt-in path containment for native views).
- v3.18.1 - Added Flight Configuration Hardening section covering `flight.allow_method_override`, `flight.debug`, and JSONP callback validation.
- v3.1.0 - Added sections on CORS, Error Handling, Input Sanitization, Password Hashing, and Rate Limiting.
- v2.0 - Added escaping for default views to prevent XSS.
