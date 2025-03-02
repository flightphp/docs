# Cookies

[overclokk/cookie](https://github.com/overclokk/cookie) is a simple library for managing cookies within your app.

## Installation

Installation is simple with composer.

```bash
composer require overclokk/cookie
```

## Usage

Usage is as simple as registering a new method on the Flight class.

```php
use Overclokk\Cookie\Cookie;

/*
 * Set in your bootstrap or public/index.php file
 */

Flight::register('cookie', Cookie::class);

/**
 * ExampleController.php
 */

class ExampleController {
	public function login() {
		// Set a cookie

		// you'll want this to be false so you get a new instance
		// use the below comment if you want autocomplete
		/** @var \Overclokk\Cookie\Cookie $cookie */
		$cookie = Flight::cookie(false);
		$cookie->set(
			'stay_logged_in', // name of the cookie
			'1', // the value you want to set it to
			86400, // number of seconds the cookie should last
			'/', // path that the cookie will be available to
			'example.com', // domain that the cookie will be available to
			true, // cookie will only be transmitted over a secure HTTPS connection
			true // cookie will only be available through the HTTP protocol
		);

		// optionally, if you want to keep the default values
		// and have a quick way to set a cookie for a long time
		$cookie->forever('stay_logged_in', '1');
	}

	public function home() {
		// Check if you have the cookie
		if (Flight::cookie()->has('stay_logged_in')) {
			// put them in the dashboard area for example.
			Flight::redirect('/dashboard');
		}
	}
}