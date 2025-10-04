# Responses

## Overview

Flight helps generate part of the response headers for you, but you hold most of the control over what you send back to the user. Most of the time you'll access the `response()` object directly, but Flight has some helper methods to set some of the response headers for you.

## Understanding

After the user sends their [request](/learn/requests) request to your application, you need to generate a proper response for them. They have sent you information like the language the prefer, if they can handle certain types of compression, their user agent, etc and after processing everything it's time to send them back a proper response. This can be setting headers, outputting a body of HTML or JSON for them, or redirecting them to a page.  

## Basic Usage

### Sending a Response Body

Flight uses `ob_start()` to buffer the output. This means you can use `echo` or `print` to send a response to the user and Flight will capture it and send it back to the user with the appropriate headers.

```php

// This will send "Hello, World!" to the user's browser
Flight::route('/', function() {
	echo "Hello, World!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hello, World!
```

As an alternative, you can call the `write()` method to add to the body as well.

```php

// This will send "Hello, World!" to the user's browser
Flight::route('/', function() {
	// verbose, but gets the job sometimes when you need it
	Flight::response()->write("Hello, World!");

	// if you want to retrieve the body that you've set at this point
	// you can do so like this
	$body = Flight::response()->getBody();
});
```

### JSON

Flight provides support for sending JSON and JSONP responses. To send a JSON response you
pass some data to be JSON encoded:

```php
Flight::route('/@companyId/users', function(int $companyId) {
	// somehow pull out your users from a database for example
	$users = Flight::db()->fetchAll("SELECT id, first_name, last_name FROM users WHERE company_id = ?", [ $companyId ]);

	Flight::json($users);
});
// [{"id":1,"first_name":"Bob","last_name":"Jones"}, /* more users */ ]
```

> **Note:** By default, Flight will send a `Content-Type: application/json` header with the response. It will also use the flags `JSON_THROW_ON_ERROR` and `JSON_UNESCAPED_SLASHES` when encoding the JSON.

#### JSON with Status Code

You can also pass in a status code as the second argument:

```php
Flight::json(['id' => 123], 201);
```

#### JSON with Pretty Print

You can also pass in an argument to the last position to enable pretty printing:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

#### Changing JSON Argument Order

`Flight::json()` is a very legacy method, but the goal of Flight is to maintain backwards compatibility
for projects. It's actually very simple if you want to redo the order of the arguments to use a simpler 
syntax, you can just remap the JSON method [like any other Flight method](/learn/extending):

```php
Flight::map('json', function($data, $code = 200, $options = 0) {

	// now you don't have to `true, 'utf-8'` when using the json() method!
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// And now it can be used like this
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

#### JSON and Stopping Execution

_v3.10.0_

If you want to send a JSON response and stop execution, you can use the `jsonHalt()` method.
This is useful for cases where you are checking for maybe some type of authorization and if
the user is not authorized, you can send a JSON response immediately, clear the existing body
content and stop execution.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Check if the user is authorized
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
		// no exit; needed here.
	}

	// Continue with the rest of the route
});
```

Before v3.10.0, you would have to do something like this:

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Check if the user is authorized
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// Continue with the rest of the route
});
```

### Clearing a Response Body

If you want to clear the response body, you can use the `clearBody` method:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

The use case above is likely not common, however it could be more common if this was used in a [middleware](/learn/middleware).

### Running a Callback on the Response Body

You can run a callback on the response body by using the `addResponseBodyCallback` method:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// This will gzip all the responses for any route
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

You can add multiple callbacks and they will be run in the order they were added. Because this can accept any [callable](https://www.php.net/manual/en/language.types.callable.php), it can accept a class array `[ $class, 'method' ]`, a closure `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, or a function name `'minify'` if you had a function to minify your html code for example.

**Note:** Route callbacks will not work if you are using the `flight.v2.output_buffering` configuration option.

#### Specific Route Callback

If you wanted this to only apply to a specific route, you could add the callback in the route itself:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// This will gzip only the response for this route
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

#### Middleware Option

You can also use [middleware](/learn/middleware) to apply the callback to all routes via middleware:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// Apply the callback here on the response() object.
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// minify the body somehow
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

### Status Codes

You can set the status code of the response by using the `status` method:

```php
Flight::route('/@id', function($id) {
	if($id == 123) {
		Flight::response()->status(200);
		echo "Hello, World!";
	} else {
		Flight::response()->status(403);
		echo "Forbidden";
	}
});
```

If you want to get the current status code, you can use the `status` method without any arguments:

```php
Flight::response()->status(); // 200
```

### Setting a Response Header

You can set a header such as content type of the response by using the `header` method:

```php
// This will send "Hello, World!" to the user's browser in plain text
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// or
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

### Redirect

You can redirect the current request by using the `redirect()` method and passing
in a new URL:

```php
Flight::route('/login', function() {
	$username = Flight::request()->data->username;
	$password = Flight::request()->data->password;
	$passwordConfirm = Flight::request()->data->password_confirm;

	if($password !== $passwordConfirm) {
		Flight::redirect('/new/location');
		return; // this is necessary so functionality below doesn't execute
	}

	// add the new user...
	Flight::db()->runQuery("INSERT INTO users ....");
	Flight::redirect('/admin/dashboard');
});
```

> **Note:** By default Flight sends a HTTP 303 ("See Other") status code. You can optionally set a
custom code:

```php
Flight::redirect('/new/location', 301); // permanent
```

### Stopping Route Execution

You can stop the framework and immediately exit at any point by calling the `halt` method:

```php
Flight::halt();
```

You can also specify an optional `HTTP` status code and message:

```php
Flight::halt(200, 'Be right back...');
```

Calling `halt` will discard any response content up to that point and stop all execution. 
If you want to stop the framework and output the current response, use the `stop` method:

```php
Flight::stop($httpStatusCode = null);
```

> **Note:** `Flight::stop()` has some odd behavior such as it will output the response but continue executing your script which might not be what you are after. You can use `exit` or `return` after calling `Flight::stop()` to prevent further execution, but it is generally recommended to use `Flight::halt()`. 

This will save the header key and value to the response object. At the end of the request lifecycle
it will build the headers and send a response.

## Advanced Usage

### Sending a Header Immediately

There may be times when you need to do something custom with the header and you need to send the header
on that very line of code you're working with. If you are setting a [streamed route](/learn/routing),
this is what you would need. That is achievable through `response()->setRealHeader()`.

```php
Flight::route('/', function() {
	Flight::response()->setRealHeader('Content-Type: text/plain');
	echo 'Streaming response...';
	sleep(5);
	echo 'Done!';
})->stream();
```

### JSONP

For JSONP requests, you can optionally pass in the query parameter name you are
using to define your callback function:

```php
Flight::jsonp(['id' => 123], 'q');
```

So, when making a GET request using `?q=my_func`, you should receive the output:

```javascript
my_func({"id":123});
```

If you don't pass in a query parameter name it will default to `jsonp`.

> **Note:** If you are still using JSONP requests in 2025 and beyond, hop in the chat and tell us why! We love hearing some good battle/horror stories!

### Clearing Response Data

You can clear the response body and headers by using the `clear()` method. This will clear
any headers assigned to the response, clear the response body, and set the status code to `200`.

```php
Flight::response()->clear();
```

#### Clearing Response Body Only

If you only want to clear the response body, you can use the `clearBody()` method:

```php
// This will still keep any headers set on the response() object.
Flight::response()->clearBody();
```

### HTTP Caching

Flight provides built-in support for HTTP level caching. If the caching condition
is met, Flight will return an HTTP `304 Not Modified` response. The next time the
client requests the same resource, they will be prompted to use their locally
cached version.

#### Route Level Caching

If you want to cache your whole response, you can use the `cache()` method and pass in time to cache.

```php

// This will cache the response for 5 minutes
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'This content will be cached.';
});

// Alternatively, you can use a string that you would pass
// to the strtotime() method
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'This content will be cached.';
});
```

### Last-Modified

You can use the `lastModified` method and pass in a UNIX timestamp to set the date
and time a page was last modified. The client will continue to use their cache until
the last modified value is changed.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'This content will be cached.';
});
```

### ETag

`ETag` caching is similar to `Last-Modified`, except you can specify any id you
want for the resource:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'This content will be cached.';
});
```

Keep in mind that calling either `lastModified` or `etag` will both set and check the
cache value. If the cache value is the same between requests, Flight will immediately
send an `HTTP 304` response and stop processing.

### Download a File

_v3.12.0_

There is a helper method to stream a file to the end user. You can use the `download` method and pass in the path.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
  // As of v3.17.1 you can specify a custom filename for the download
  Flight::download('/path/to/file.txt', 'custom_name.txt');
});
```

## See Also
- [Routing](/learn/routing) - How to map routes to controllers and render views.
- [Requests](/learn/requests) - Understanding how to handle incoming requests.
- [Middleware](/learn/middleware) - Using middleware with routes for authentication, logging, etc.
- [Why a Framework?](/learn/why-frameworks) - Understanding the benefits of using a framework like Flight.
- [Extending](/learn/extending) - How to extend Flight with your own functionality.

## Troubleshooting
- If you're having trouble with redirects not working, make sure you add a `return;` to the method.
- `stop()` and `halt()` are not the same thing. `halt()` will stop execution immediately, while `stop()` will allow execution to continue.

## Changelog
- v3.17.1 - Added `$fileName` to `downloadFile()` method.
- v3.12.0 - Added downloadFile helper method.
- v3.10.0 - Added `jsonHalt`.
- v1.0 - Initial release.