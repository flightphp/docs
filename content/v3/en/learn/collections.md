# Collections

## Overview

The `Collection` class in Flight is a handy utility for managing sets of data. It lets you access and manipulate data using both array and object notation, making your code cleaner and more flexible.

## Understanding

A `Collection` is basically a wrapper around an array, but with some extra powers. You can use it like an array, loop over it, count its items, and even access items as if they were object properties. This is especially useful when you want to pass around structured data in your app, or when you want to make your code a bit more readable.

Collections implement several PHP interfaces:
- `ArrayAccess` (so you can use array syntax)
- `Iterator` (so you can loop with `foreach`)
- `Countable` (so you can use `count()`)
- `JsonSerializable` (so you can easily convert to JSON)

## Basic Usage

### Creating a Collection

You can create a collection by simply passing an array to its constructor:

```php
use flight\util\Collection;

$data = [
  'name' => 'Flight',
  'version' => 3,
  'features' => ['routing', 'views', 'extending']
];

$collection = new Collection($data);
```

### Accessing Items

You can access items using either array or object notation:

```php
// Array notation
echo $collection['name']; // Output: FlightPHP

// Object notation
echo $collection->version; // Output: 3
```

If you try to access a key that doesn't exist, you'll get `null` instead of an error.

### Setting Items

You can set items using either notation as well:

```php
// Array notation
$collection['author'] = 'Mike Cao';

// Object notation
$collection->license = 'MIT';
```

### Checking and Removing Items

Check if an item exists:

```php
if (isset($collection['name'])) {
  // Do something
}

if (isset($collection->version)) {
  // Do something
}
```

Remove an item:

```php
unset($collection['author']);
unset($collection->license);
```

### Iterating Over a Collection

Collections are iterable, so you can use them in a `foreach` loop:

```php
foreach ($collection as $key => $value) {
  echo "$key: $value\n";
}
```

### Counting Items

You can count the number of items in a collection:

```php
echo count($collection); // Output: 4
```

### Getting All Keys or Data

Get all keys:

```php
$keys = $collection->keys(); // ['name', 'version', 'features', 'license']
```

Get all data as an array:

```php
$data = $collection->getData();
```

### Clearing the Collection

Remove all items:

```php
$collection->clear();
```

### JSON Serialization

Collections can be easily converted to JSON:

```php
echo json_encode($collection);
// Output: {"name":"FlightPHP","version":3,"features":["routing","views","extending"],"license":"MIT"}
```

## Advanced Usage

You can replace the internal data array entirely if needed:

```php
$collection->setData(['foo' => 'bar']);
```

Collections are especially useful when you want to pass structured data between components, or when you want to provide a more object-oriented interface to array data.

## See Also

- [Requests](/learn/requests) - Learn how to handle HTTP requests and how collections can be used to manage request data.
- [PDO Wrapper](/learn/pdo-wrapper) - Learn how to use the PDO wrapper in Flight and how collections can be used to manage database results.

## Troubleshooting

- If you try to access a key that doesn't exist, you'll get `null` instead of an error.
- Remember that collections are not recursive: nested arrays are not automatically converted to collections.
- If you need to reset the collection, use `$collection->clear()` or `$collection->setData([])`.

## Changelog

- v3.0 - Improved type hints and PHP 8+ support.
- v1.0 - Initial release of the Collection class.
