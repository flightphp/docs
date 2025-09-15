## Overview
Flight's request object provides a fast, simple, and extensible way to access all HTTP request data in your app. It centralizes everything you need for handling GET, POST, cookies, files, headers, and more—no fuss, no dependencies.

## Understanding
The request object in Flight acts as your gateway to all incoming HTTP data. Whether you're building a simple form handler or a complex API, it keeps things predictable and beginner-friendly. Flight wraps PHP's superglobals and server info into a single, easy-to-use interface, so you can focus on your app logic instead of plumbing.

## Basic Usage
### Accessing the Request Object
How to get the request object in any Flight route or callback.
```php
// Example: Get the request object
```

### Getting Query String Parameters (GET)
How to access query string parameters from the request.
```php
// Example: Get a query string parameter
```

### Getting POST Data
How to access POST data from forms or JSON.
```php
// Example: Get POST data
```

### Accessing Cookies
How to read cookies sent by the client.
```php
// Example: Get a cookie value
```

### Accessing Request Headers
How to read HTTP headers from the request.
```php
// Example: Get a header value
```

### Accessing Request Method
How to check the HTTP method (GET, POST, etc.).
```php
// Example: Get the request method
```

### Accessing Request URLs
How to get the full or base URL of the request.
```php
// Example: Get the request URL
```

### Parsing Query Strings
How to parse a query string into an array.
```php
// Example: Parse a query string
```

## Advanced Usage
### Handling JSON Input
How to work with JSON request bodies and access decoded data.
```php
// Example: Handle JSON input
```

### Accessing Raw Request Body
How to get the raw HTTP request body for advanced scenarios.
```php
// Example: Get raw request body
```

### Processing File Uploads
How to handle file uploads, move files, and validate them securely.
```php
// Example: Process file uploads
```

### Accessing Uploaded Files
How to access uploaded files and their metadata.
```php
// Example: Access uploaded files
```

### Proxy IP Handling
How to get the real client IP when behind proxies.
```php
// Example: Handle proxy IPs
```

### Method Override Precedence
How Flight determines the request method, including overrides.
```php
// Example: Method override handling
```

## See Also
- [Middleware](./middleware.md) (Flight docs)
- [PHP: $_SERVER](https://www.php.net/manual/en/reserved.variables.server.php)
- Placeholder for external plugin example (if applicable)

## Troubleshooting
- Common issue: Request data not available – Ensure you’re using the correct property (query, data, cookies, files).
- Common issue: File uploads not working – Check input field names and validate file types/extensions and magic bytes.
- Security caveat: Always validate and sanitize user input, especially file uploads.
- Method override caveat: Be aware of override precedence (HTTP_X_HTTP_METHOD_OVERRIDE, _method param).
- Proxy IP caveat: Understand how Flight scans for proxy IPs and configure your server accordingly.

## Changelog
- 3.12.0: Added file upload helpers (getUploadedFiles, moveTo)
- X.Y.Z: Placeholder for future changes
