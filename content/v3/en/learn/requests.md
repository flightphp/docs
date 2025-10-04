# Requests

## Overview

Flight encapsulates the HTTP request into a single object, which can be
accessed by doing:

```php
$request = Flight::request();
```

## Understanding

HTTP requests are one of the core facets to understand about the HTTP lifecycle. A user performs an action on a web browser or an HTTP client, and they send a series of headers, body, URL, etc to your project. You can capture these headers (the language of the browser, what type of compression they can handle, the user agent, etc) and capture the body and URL that is sent to your Flight application. These requests are essential for your app to understand what to do next.

## Basic Usage

PHP has several super globals including `$_GET`, `$_POST`, `$_REQUEST`, `$_SERVER`, `$_FILES`, and `$_COOKIE`. Flight abstracts these away into handy [Collections](/learn/collections). You can access the `query`, `data`, `cookies`, and `files` properties as arrays or objects.

> **Note:** It is **HIGHLY** discouraged to use these super globals in your project and should be referenced through the `request()` object.

> **Note:** There is no abstraction available for `$_ENV`.

### `$_GET`

You can access the `$_GET` array via the `query` property:

```php
// GET /search?keyword=something
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	// or
	$keyword = Flight::request()->query->keyword;
	echo "You are searching for: $keyword";
	// query a database or something else with the $keyword
});
```

### `$_POST`

You can access the `$_POST` array via the `data` property:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	// or
	$name = Flight::request()->data->name;
	$email = Flight::request()->data->email;
	echo "You submitted: $name, $email";
	// save to a database or something else with the $name and $email
});
```

### `$_COOKIE`

You can access the `$_COOKIE` array via the `cookies` property:

```php
Flight::route('GET /login', function(){
	$savedLogin = Flight::request()->cookies['myLoginCookie'];
	// or
	$savedLogin = Flight::request()->cookies->myLoginCookie;
	// check if it's really saved or not and if it is auto log them in
	if($savedLogin) {
		Flight::redirect('/dashboard');
		return;
	}
});
```

For help on setting new cookie values, see [overclokk/cookie](/awesome-plugins/php-cookie)

### `$_SERVER`

There is a shortcut available to access the `$_SERVER` array via the `getVar()` method:

```php

$host = Flight::request()->getVar('HTTP_HOST');
```

### `$_FILES`

You can access uploaded files via the `files` property:

```php
// raw access to $_FILES property. See below for recommended approach
$uploadedFile = Flight::request()->files['myFile']; 
// or
$uploadedFile = Flight::request()->files->myFile;
```

See [Uploaded File Handler](/learn/uploaded-file) for more info.

#### Processing File Uploads

_v3.12.0_

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

### Request Body

To get the raw HTTP request body, for example when dealing with POST/PUT requests,
you can do:

```php
Flight::route('POST /users/xml', function(){
	$xmlBody = Flight::request()->getBody();
	// do something with the XML that was sent.
});
```

### JSON Body

If you receive a request with the content type `application/json` and the example data of `{"id": 123}`
it will be available from the `data` property:

```php
$id = Flight::request()->data->id;
```

### Request Headers

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

### Request Method

You can access the request method using the `method` property or the `getMethod()` method:

```php
$method = Flight::request()->method; // actually populated by getMethod()
$method = Flight::request()->getMethod();
```

**Note:** The `getMethod()` method first pulls the method from `$_SERVER['REQUEST_METHOD']`, then it can be overwritten 
by `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` if it exists or `$_REQUEST['_method']` if it exists.

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
- **servername** - The SERVER_NAME from `$_SERVER`

## Helper Methods

There are a couple helper methods to piece together parts of a URL, or deal with certain headers.

### Full URL

You can access the full request URL using the `getFullUrl()` method:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### Base URL

You can access the base URL using the `getBaseUrl()` method:

```php
// http://example.com/path/to/something/cool?query=yes+thanks
$url = Flight::request()->getBaseUrl();
// https://example.com
// Notice, no trailing slash.
```

## Query Parsing

You can pass a URL to the `parseQuery()` method to parse the query string into an associative array:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```

## Negotiate Content Accept Types

_v3.17.2_

You can use the `negotiateContentType()` method to determine the best content type to respond with based on the `Accept` header sent by the client.

```php

// Example Accept header: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8
// The below defines what you support.
$availableTypes = ['application/json', 'application/xml'];
$typeToServe = Flight::request()->negotiateContentType($availableTypes);
if ($typeToServe === 'application/json') {
	// Serve JSON response
} elseif ($typeToServe === 'application/xml') {
	// Serve XML response
} else {
	// Default to something else or throw an error
}
```

> **Note:** If none of the available types are found in the `Accept` header, the method will return `null`. If there is no `Accept` header defined, the method will return the first type in the `$availableTypes` array.

## See Also
- [Routing](/learn/routing) - See how to map routes to controllers and render views.
- [Responses](/learn/responses) - How to customize HTTP responses.
- [Why a Framework?](/learn/why-frameworks) - How requests fit into the big picture.
- [Collections](/learn/collections) - Working with collections of data.
- [Uploaded File Handler](/learn/uploaded-file) - Handling file uploads.

## Troubleshooting
- `request()->ip` and `request()->proxy_ip` can be different if your webserver is behind a proxy, load balancer, etc. 

## Changelog
- v3.17.2 - Added negotiateContentType()
- v3.12.0 - Added ability to handle file uploads through the request object.
- v1.0 - Initial release.