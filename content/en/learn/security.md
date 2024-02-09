# Security

Security is a big deal when it comes to web applications. You want to make sure that your application is secure and that your users' data is safe. Flight provides a number of features to help you secure your web applications.

## Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF) is a type of attack where a malicious website can make a user's browser send a request to your website. This can be used to perform actions on your website without the user's knowledge. Flight does not provide a built-in CSRF protection mechanism, but you can easily implement your own by using middleware.

First you need to generate a CSRF token and store it in the user's session. You can then use this token in your forms and check it when the form is submitted.

```php
// Generate a CSRF token and store it in the user's session
// (assuming you've created a session object at attached it to Flight)
Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
```

```html
<!-- Use the CSRF token in your form -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- other form fields -->
</form>
```

And then you can check the CSRF token using event filters:

```php
// This middleware checks if the request is a POST request and if it is, it checks if the CSRF token is valid
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// capture the csrf token from the form values
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
		}
	}
});
```

## Cross Site Scripting (XSS)

Cross Site Scripting (XSS) is a type of attack where a malicious website can inject code into your website. Most of these opportunities come from form values that your end users will fill out. You should **never** trust output from your users! Always assume all of them are the best hackers in the world. They can inject malicious JavaScript or HTML into your page. This code can be used to steal information from your users or perform actions on your website. Using Flight's view class, you can easily escape output to prevent XSS attacks.

```php

// Let's assume the user is clever as tries to use this as their name
$name = '<script>alert("XSS")</script>';

// This will escape the output
Flight::view()->set('name', $name);
// This will output: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// If you use something like Latte registered as your view class, it will also auto escape this.
Flight::view()->render('template', ['name' => $name]);
```

## SQL Injection

SQL Injection is a type of attack where a malicious user can inject SQL code into your database. This can be used to steal information from your database or perform actions on your database. Again you should **never** trust input from your users! Always assume they are out for blood. You can use prepared statements in your `PDO` objects will  prevent SQL injection.

```php

// Assuming you have Flight::db() registered as your PDO object
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// If you use the PdoWrapper class, this can easily be done in one line
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// You can do the same thing with a PDO object with ? placeholders
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Just promise you will never EVER do something like this...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// because what if $username = "' OR 1=1; -- "; After the query is build it looks
// like this
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// It looks strange, but it's a valid query that will work. In fact,
// it's a very common SQL injection attack that will return all users.
```

## CORS

Cross-Origin Resource Sharing (CORS) is a mechanism that allows many resources (e.g., fonts, JavaScript, etc.) on a web page to be requested from another domain outside the domain from which the resource originated. Flight does not have built in functionality but this can easily be handled with middleware of event filters similar to CSRF.

```php

// app/middleware/CorsMiddleware.php

namespace app\middleware;

class CorsMiddleware
{
	public function before(array $params): void
	{
		$response = Flight::response();
		if (isset($_SERVER['HTTP_ORIGIN'])) {
			$this->allowOrigins();
			$response->header('Access-Control-Allow-Credentials: true');
			$response->header('Access-Control-Max-Age: 86400');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
				$response->header(
					'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS'
				);
			}
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
				$response->header(
					"Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
				);
			}
			$response->send();
			exit(0);
		}
	}

	private function allowOrigins(): void
	{
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		if (in_array($_SERVER['HTTP_ORIGIN'], $allowed)) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		}
	}
}

// index.php or wherever you have your routes
Flight::route('/users', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(new CorsMiddleware());
```

## Conclusion

Security is a big deal and it's important to make sure your web applications are secure. Flight provides a number of features to help you secure your web applications, but it's important to always be vigilant and make sure you're doing everything you can to keep your users' data safe. Always assume the worst and never trust input from your users. Always escape output and use prepared statements to prevent SQL injection. Always use middleware to protect your routes from CSRF and CORS attacks. If you do all of these things, you'll be well on your way to building secure web applications.