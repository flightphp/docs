# Requests

Flight encapsulates the HTTP request into a single object, which can be
accessed by doing:

```php
$request = Flight::request();
```

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

## Uploaded Files via `$_FILES`

You can access uploaded files via the `files` property:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

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

**Note:** The `getMethod()` method first pulls the method from `$_SERVER['REQUEST_METHOD']`, then it can be overwritten by `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` if it exists or `$_REQUEST['_method']` if it exists.

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