# Requests

Flight encapsulates the HTTP request into a single object, which can be
accessed by doing:

```php
$request = Flight::request();
```

## Typical Use Cases

When you are working with a request in a web application, typically you'll
want to pull out a header, or a `$_GET` or `$_POST` parameter, or maybe
even the raw request body. Flight provides a simple interface to do all of
these things.

Here's an example getting a query string parameter:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "You are searching for: $keyword";
	// query a database or something else with the $keyword
});
```

Here's an example of maybe a form with a POST method:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "You submitted: $name, $email";
	// save to a database or something else with the $name and $email
});
```

## Request Object Properties

The request object provides the following properties:

- **body** - The raw HTTP request body
- **url** - The URL being requested
- **base** - The parent subdirectory of the URL
- **method** - The request method (GET, POST, PUT, DELETE)
- **referrer** - The referrer URL
- **ip** - IP address of the client
- **ajax** - Whether the request is an AJAX request
- **scheme** - The server protocol (http, https)
- **user_agent** - Browser information
- **type** - The content type
- **length** - The content length
- **query** - Query string parameters
- **data** - Post data or JSON data
- **cookies** - Cookie data
- **files** - Uploaded files
- **secure** - Whether the connection is secure
- **accept** - HTTP accept parameters
- **proxy_ip** - Proxy IP address of the client. Scans the `$_SERVER` array for `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` in that order.
- **host** - The request host name

You can access the `query`, `data`, `cookies`, and `files` properties
as arrays or objects.

So, to get a query string parameter, you can do:

```php
$id = Flight::request()->query['id'];
```

Or you can do:

```php
$id = Flight::request()->query->id;
```

## RAW Request Body

To get the raw HTTP request body, for example when dealing with PUT requests,
you can do:

```php
$body = Flight::request()->getBody();
```

## JSON Input

If you send a request with the type `application/json` and the data `{"id": 123}`
it will be available from the `data` property:

```php
$id = Flight::request()->data->id;
```

## `$_GET`

You can access the `$_GET` array via the `query` property:

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

You can access the `$_POST` array via the `data` property:

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

You can access the `$_COOKIE` array via the `cookies` property:

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

There is a shortcut available to access the `$_SERVER` array via the `getVar()` method:

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## Accessing Uploaded Files via `$_FILES`

You can access uploaded files via the `files` property:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## Processing File Uploads (v3.12.0)

You can process file uploads using the framework with some helper methods. It basically 
boils down to pulling the file data from the request, and moving it to a new location.

```php
Flight::route('POST /upload', function(){
	// If you had an input field like <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

If you have multiple files uploaded, you can loop through them:

```php
Flight::route('POST /upload', function(){
	// If you had an input field like <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Security Note:** Always validate and sanitize user input, especially when dealing with file uploads. Always validate the type of extensions you'll allow to be uploaded, but you should also validate the "magic bytes" of the file to ensure it's actually the type of file the user claims it is. There are [articles](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [and](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [libraries](https://github.com/RikudouSage/MimeTypeDetector) available to help with this.

## Request Headers

You can access request headers using the `getHeader()` or `getHeaders()` method:

```php

// Maybe you need Authorization header
$host = Flight::request()->getHeader('Authorization');
// or
$host = Flight::request()->header('Authorization');

// If you need to grab all headers
$headers = Flight::request()->getHeaders();
// or
$headers = Flight::request()->headers();
```

## Request Body

You can access the raw request body using the `getBody()` method:

```php
$body = Flight::request()->getBody();
```

## Request Method

You can access the request method using the `method` property or the `getMethod()` method:

```php
$method = Flight::request()->method; // actually calls getMethod()
$method = Flight::request()->getMethod();
```

**Note:** The `getMethod()` method first pulls the method from `$_SERVER['REQUEST_METHOD']`, then it can be overwritten 
by `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` if it exists or `$_REQUEST['_method']` if it exists.

## Request URLs

There are a couple helper methods to piece together parts of a URL for your convenience.

### Full URL

You can access the full request URL using the `getFullUrl()` method:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### Base URL

You can access the base URL using the `getBaseUrl()` method:

```php
$url = Flight::request()->getBaseUrl();
// Notice, no trailing slash.
// https://example.com
```

## Query Parsing

You can pass a URL to the `parseQuery()` method to parse the query string into an associative array:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```