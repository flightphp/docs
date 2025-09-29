# PdoWrapper PDO Helper Class

## Overview

The `PdoWrapper` class in Flight is a friendly helper for working with databases using PDO. It simplifies common database tasks, adds some handy methods for fetching results, and returns results as [Collections](/learn/collections) for easy access. It also supports query logging and application performance monitoring (APM) for advanced use cases.

## Understanding

Working with databases in PHP can be a bit verbose, especially when using PDO directly. `PdoWrapper` extends PDO and adds methods that make querying, fetching, and handling results much easier. Instead of juggling prepared statements and fetch modes, you get simple methods for common tasks, and every row is returned as a Collection, so you can use array or object notation.

You can register the `PdoWrapper` as a shared service in Flight, and then use it anywhere in your app via `Flight::db()`.

## Basic Usage

### Registering the PDO Helper

First, register the `PdoWrapper` class with Flight:

```php
Flight::register('db', \flight\database\PdoWrapper::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
]);
```

Now you can use `Flight::db()` anywhere to get your database connection.

### Running Queries

#### `runQuery()`

`function runQuery(string $sql, array $params = []): PDOStatement`

Use this for INSERTs, UPDATEs, or when you want to fetch results manually:

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM users WHERE status = ?", ['active']);
while ($row = $statement->fetch()) {
    // $row is an array
}
```

You can also use it for writes:

```php
$db->runQuery("INSERT INTO users (name) VALUES (?)", ['Alice']);
$db->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 1]);
```

#### `fetchField()`

`function fetchField(string $sql, array $params = []): mixed`

Get a single value from the database:

```php
$count = Flight::db()->fetchField("SELECT COUNT(*) FROM users WHERE status = ?", ['active']);
```

#### `fetchRow()`

`function fetchRow(string $sql, array $params = []): Collection`

Get a single row as a Collection (array/object access):

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// or
echo $user->name;
```

#### `fetchAll()`

`function fetchAll(string $sql, array $params = []): array<Collection>`

Get all rows as an array of Collections:

```php
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE status = ?", ['active']);
foreach ($users as $user) {
    echo $user['name'];
    // or
    echo $user->name;
}
```

### Using `IN()` Placeholders

You can use a single `?` in an `IN()` clause and pass an array or comma-separated string:

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
// or
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", ['1,2,3']);
```

## Advanced Usage

### Query Logging & APM

If you want to track query performance, enable APM tracking when registering:

```php
Flight::register('db', \flight\database\PdoWrapper::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [/* options */], true // last param enables APM
]);
```

After running queries, you can log them manually but the APM will log them automatically if enabled:

```php
Flight::db()->logQueries();
```

This will trigger an event (`flight.db.queries`) with connection and query metrics, which you can listen for using Flight's event system.

### Full Example

```php
Flight::route('/users', function () {
    // Get all users
    $users = Flight::db()->fetchAll('SELECT * FROM users');

    // Stream all users
    $statement = Flight::db()->runQuery('SELECT * FROM users');
    while ($user = $statement->fetch()) {
        echo $user['name'];
    }

    // Get a single user
    $user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

    // Get a single value
    $count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

    // Special IN() syntax
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', ['1,2,3,4,5']);

    // Insert a new user
    Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
    $insert_id = Flight::db()->lastInsertId();

    // Update a user
    Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

    // Delete a user
    Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

    // Get the number of affected rows
    $statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
    $affected_rows = $statement->rowCount();
});
```

## See Also

- [Collections](/learn/collections) - Learn how to use the Collection class for easy data access.

## Troubleshooting

- If you get an error about database connection, check your DSN, username, password, and options.
- All rows are returned as Collections—if you need a plain array, use `$collection->getData()`.
- For `IN (?)` queries, make sure to pass an array or comma-separated string.

## Changelog

- v3.2.0 - Initial release of PdoWrapper with basic query and fetch methods.