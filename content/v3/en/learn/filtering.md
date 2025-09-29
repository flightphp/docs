# Filtering

## Overview

Flight allows you to filter [mapped methods](/learn/extending) before and after they are called. 

## Understanding 
There are no predefined hooks you need to memorize. You can filter any of the default framework
methods as well as any custom methods that you've mapped.

A filter function looks like this:

```php
/**
 * @param array $params The parameters passed to the method being filtered.
 * @param string $output (v2 output buffering only) The output of the method being filtered.
 * @return bool Return true/void or don't return to continue the chain, false to break the chain.
 */
function (array &$params, string &$output): bool {
  // Filter code
}
```

Using the passed in variables you can manipulate the input parameters and/or the output.

You can have a filter run before a method by doing:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // Do something
});
```

You can have a filter run after a method by doing:

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // Do something
});
```

You can add as many filters as you want to any method. They will be called in the
order that they are declared.

Here's an example of the filtering process:

```php
// Map a custom method
Flight::map('hello', function (string $name) {
  return "Hello, $name!";
});

// Add a before filter
Flight::before('hello', function (array &$params, string &$output): bool {
  // Manipulate the parameter
  $params[0] = 'Fred';
  return true;
});

// Add an after filter
Flight::after('hello', function (array &$params, string &$output): bool {
  // Manipulate the output
  $output .= " Have a nice day!";
  return true;
});

// Invoke the custom method
echo Flight::hello('Bob');
```

This should display:

```
Hello Fred! Have a nice day!
```

If you have defined multiple filters, you can break the chain by returning `false`
in any of your filter functions:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'one';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'two';

  // This will end the chain
  return false;
});

// This will not get called
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'three';
  return true;
});
```

> **Note:** Core methods such as `map` and `register` cannot be filtered because they
are called directly and not invoked dynamically. See [Extending Flight](/learn/extending) for more information.

## See Also
- [Extending Flight](/learn/extending)

## Troubleshooting
- Make sure you are returning `false` from your filter functions if you want the chain to stop. If you don't return anything, the chain will continue.

## Changelog
- v2.0 - Initial Release.