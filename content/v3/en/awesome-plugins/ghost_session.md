# Ghostff/Session

PHP Session Manager (non-blocking, flash, segment, session encryption). Uses PHP open_ssl for optional encrypt/decryption of session data. Supports File, MySQL, Redis, and Memcached.

Click [here](https://github.com/Ghostff/Session) to view the code.

## Installation

Install with composer.

```bash
composer require ghostff/session
```

## Basic Configuration

You aren't required to pass anything in to use the default settings with your session. You can read about more settings in the [Github Readme](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// one thing to remember is that you must commit your session on each page load
// or you'll need to run auto_commit in your configuration. 
```

## Simple Example

Here's a simple example of how you might use this.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// do your login logic here
	// validate password, etc.

	// if the login is successful
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// any time you write to the session, you must commit it deliberately.
	$session->commit();
});

// This check could be in the restricted page logic, or wrapped with middleware.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// do your restricted page logic here
});

// the middleware version
Flight::route('/some-restricted-page', function() {
	// regular page logic
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## More Complex Example

Here's a more complex example of how you might use this.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// set a custom path to your session configuration file as the first arg
// or give it the custom array
$app->register('session', Session::class, [ 
	[
		// if you want to store your session data in a database (good if you want something like, "log me out of all devices" functionality)
		Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
		Session::CONFIG_ENCRYPT_DATA  => true,
		Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // please change this to be something else
		Session::CONFIG_AUTO_COMMIT   => true, // only do this if it requires it and/or it's hard to commit() your session.
												// additionally you could do Flight::after('start', function() { Flight::session()->commit(); });
		Session::CONFIG_MYSQL_DS         => [
			'driver'    => 'mysql',             # Database driver for PDO dns eg(mysql:host=...;dbname=...)
			'host'      => '127.0.0.1',         # Database host
			'db_name'   => 'my_app_database',   # Database name
			'db_table'  => 'sessions',          # Database table
			'db_user'   => 'root',              # Database username
			'db_pass'   => '',                  # Database password
			'persistent_conn'=> false,          # Avoid the overhead of establishing a new connection every time a script needs to talk to a database, resulting in a faster web application. FIND THE BACKSIDE YOURSELF
		]
	] 
]);
```

## Help! My Session Data is Not Persisting!

Are you setting your session data and it's not persisting between requests? You might have forgotten to commit your session data. You can do this by calling `$session->commit()` after you've set your session data.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// do your login logic here
	// validate password, etc.

	// if the login is successful
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// any time you write to the session, you must commit it deliberately.
	$session->commit();
});
```

The other way around this is when you setup your session service, you have to set `auto_commit` to `true` in your configuration. This will automatically commit your session data after each request.

```php

$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Additionally you could do `Flight::after('start', function() { Flight::session()->commit(); });` to commit your session data after each request.

## Documentation

Visit the [Github Readme](https://github.com/Ghostff/Session) for full documentation. The configuration options are [well documented in the default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) file itself. The code is simple to understand if you wanted to peruse this package yourself.
```