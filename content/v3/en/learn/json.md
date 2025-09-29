# JSON Wrapper

## Overview

The `Json` class in Flight provides a simple, consistent way to encode and decode JSON data in your application. It wraps PHP's native JSON functions with better error handling and some helpful defaults, making it easier and safer to work with JSON.

## Understanding

Working with JSON is super common in modern PHP apps, especially when building APIs or handling AJAX requests. The `Json` class centralizes all your JSON encoding and decoding, so you don't have to worry about weird edge cases or cryptic errors from PHP's built-in functions.

Key features:
- Consistent error handling (throws exceptions on failure)
- Default options for encoding/decoding (like unescaped slashes)
- Utility methods for pretty printing and validation

## Basic Usage

### Encoding Data to JSON

To convert PHP data to a JSON string, use `Json::encode()`:

```php
use flight\util\Json;

$data = [
  'framework' => 'Flight',
  'version' => 3,
  'features' => ['routing', 'views', 'extending']
];

$json = Json::encode($data);
echo $json;
// Output: {"framework":"Flight","version":3,"features":["routing","views","extending"]}
```

If encoding fails, you'll get an exception with a helpful error message.

### Pretty Printing

Want your JSON to be human-readable? Use `prettyPrint()`:

```php
echo Json::prettyPrint($data);
/*
{
  "framework": "Flight",
  "version": 3,
  "features": [
    "routing",
    "views",
    "extending"
  ]
}
*/
```

### Decoding JSON Strings

To convert a JSON string back to PHP data, use `Json::decode()`:

```php
$json = '{"framework":"Flight","version":3}';
$data = Json::decode($json);
echo $data->framework; // Output: Flight
```

If you want an associative array instead of an object, pass `true` as the second argument:

```php
$data = Json::decode($json, true);
echo $data['framework']; // Output: Flight
```

If decoding fails, you'll get an exception with a clear error message.

### Validating JSON

Check if a string is valid JSON:

```php
if (Json::isValid($json)) {
  // It's valid!
} else {
  // Not valid JSON
}
```

### Getting the Last Error

If you want to check the last JSON error message (from native PHP functions):

```php
$error = Json::getLastError();
if ($error !== '') {
  echo "Last JSON error: $error";
}
```

## Advanced Usage

You can customize encoding and decoding options if you need more control (see [PHP's json_encode options](https://www.php.net/manual/en/json.constants.php)):

```php
// Encode with HEX_TAG option
$json = Json::encode($data, JSON_HEX_TAG);

// Decode with custom depth
$data = Json::decode($json, false, 1024);
```

## See Also

- [Collections](/learn/collections) - For working with structured data that can be easily converted to JSON.
- [Configuration](/learn/configuration) - How to configure your Flight app.
- [Extending](/learn/extending) - How to add your own utilities or override core classes.

## Troubleshooting

- If encoding or decoding fails, an exception is thrown—wrap your calls in try/catch if you want to handle errors gracefully.
- If you get unexpected results, check your data for circular references or non-UTF8 characters.
- Use `Json::isValid()` to check if a string is valid JSON before decoding.

## Changelog

- v3.16.0 - Added JSON wrapper utility class.
