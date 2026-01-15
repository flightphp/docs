# SimplePdo PDO Helper Class

## Overview

The `SimplePdo` class in Flight is a modern, feature-rich helper for working with databases using PDO. It extends `PdoWrapper` and adds convenient helper methods for common database operations like `insert()`, `update()`, `delete()`, and transactions. It simplifies database tasks, returns results as [Collections](/learn/collections) for easy access, and supports query logging and application performance monitoring (APM) for advanced use cases.

## Understanding

The `SimplePdo` class is designed to make working with databases in PHP much easier. Instead of juggling prepared statements, fetch modes, and verbose SQL operations, you get clean, simple methods for common tasks. Every row is returned as a Collection, so you can use both array notation (`$row['name']`) and object notation (`$row->name`).

This class is a superset of `PdoWrapper`, meaning it includes all the functionality of `PdoWrapper` plus additional helper methods that make your code cleaner and more maintainable. If you're currently using `PdoWrapper`, upgrading to `SimplePdo` is straightforward since it extends `PdoWrapper`.

You can register `SimplePdo` as a shared service in Flight, and then use it anywhere in your app via `Flight::db()`.

## Basic Usage

### Registering SimplePdo

First, register the `SimplePdo` class with Flight:

```php
Flight::register('db', \flight\database\SimplePdo::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
]);
```

> **NOTE**
>
> If you don't specify `PDO::ATTR_DEFAULT_FETCH_MODE`, `SimplePdo` will automatically set it to `PDO::FETCH_ASSOC` for you.

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

`function fetchRow(string $sql, array $params = []): ?Collection`

Get a single row as a Collection (array/object access):

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// or
echo $user->name;
```

> **TIP**
>
> `SimplePdo` automatically adds `LIMIT 1` to `fetchRow()` queries if it's not already present, making your queries more efficient.

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

#### `fetchColumn()`

`function fetchColumn(string $sql, array $params = []): array`

Fetch a single column as an array:

```php
$ids = Flight::db()->fetchColumn("SELECT id FROM users WHERE active = ?", [1]);
// Returns: [1, 2, 3, 4, 5]
```

#### `fetchPairs()`

`function fetchPairs(string $sql, array $params = []): array`

Fetch results as key-value pairs (first column as key, second as value):

```php
$userNames = Flight::db()->fetchPairs("SELECT id, name FROM users");
// Returns: [1 => 'John', 2 => 'Jane', 3 => 'Bob']
```

### Using `IN()` Placeholders

You can use a single `?` in an `IN()` clause and pass an array:

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
```

## Helper Methods

One of the main advantages of `SimplePdo` over `PdoWrapper` is the addition of convenient helper methods for common database operations.

### `insert()`

`function insert(string $table, array $data): string`

Insert one or more rows and return the last insert ID.

**Single insert:**

```php
$id = Flight::db()->insert('users', [
    'name' => 'John',
    'email' => 'john@example.com'
]);
```

**Bulk insert:**

```php
$id = Flight::db()->insert('users', [
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com'],
]);
```

### `update()`

`function update(string $table, array $data, string $where, array $whereParams = []): int`

Update rows and return the number of affected rows:

```php
$affected = Flight::db()->update(
    'users',
    ['name' => 'Jane', 'email' => 'jane@example.com'],
    'id = ?',
    [1]
);
```

> **NOTE**
>
> SQLite's `rowCount()` returns the number of rows where data actually changed. If you update a row with the same values it already has, `rowCount()` will return 0. This differs from MySQL's behavior when using `PDO::MYSQL_ATTR_FOUND_ROWS`.

### `delete()`

`function delete(string $table, string $where, array $whereParams = []): int`

Delete rows and return the number of deleted rows:

```php
$deleted = Flight::db()->delete('users', 'id = ?', [1]);
```

### `transaction()`

`function transaction(callable $callback): mixed`

Execute a callback within a transaction. The transaction automatically commits on success or rolls back on error:

```php
$result = Flight::db()->transaction(function($db) {
    $db->insert('users', ['name' => 'John']);
    $db->insert('logs', ['action' => 'user_created']);
    return $db->lastInsertId();
});
```

If any exception is thrown within the callback, the transaction is automatically rolled back and the exception is re-thrown.

## Advanced Usage

### Query Logging & APM

If you want to track query performance, enable APM tracking when registering:

```php
Flight::register('db', \flight\database\SimplePdo::class, [
    'mysql:host=localhost;dbname=cool_db_name',
    'user',
    'pass',
    [/* PDO options */],
    [
        'trackApmQueries' => true,
        'maxQueryMetrics' => 1000
    ]
]);
```

After running queries, you can log them manually, but the APM will log them automatically if enabled:

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

    // Get a single column
    $ids = Flight::db()->fetchColumn('SELECT id FROM users');

    // Get key-value pairs
    $userNames = Flight::db()->fetchPairs('SELECT id, name FROM users');

    // Special IN() syntax
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);

    // Insert a new user
    $id = Flight::db()->insert('users', [
        'name' => 'Bob',
        'email' => 'bob@example.com'
    ]);

    // Bulk insert users
    Flight::db()->insert('users', [
        ['name' => 'Bob', 'email' => 'bob@example.com'],
        ['name' => 'Jane', 'email' => 'jane@example.com']
    ]);

    // Update a user
    $affected = Flight::db()->update('users', ['name' => 'Bob'], 'id = ?', [123]);

    // Delete a user
    $deleted = Flight::db()->delete('users', 'id = ?', [123]);

    // Use a transaction
    $result = Flight::db()->transaction(function($db) {
        $db->insert('users', ['name' => 'John', 'email' => 'john@example.com']);
        $db->insert('audit_log', ['action' => 'user_created']);
        return $db->lastInsertId();
    });
});
```

## Migrating from PdoWrapper

If you're currently using `PdoWrapper`, migrating to `SimplePdo` is straightforward:

1. **Update your registration:**
   ```php
   // Old
   Flight::register('db', \flight\database\PdoWrapper::class, [ /* ... */ ]);
   
   // New
   Flight::register('db', \flight\database\SimplePdo::class, [ /* ... */ ]);
   ```

2. **All existing `PdoWrapper` methods work in `SimplePdo`** - There are no breaking changes. Your existing code will continue to work.

3. **Optionally use the new helper methods** - Start using `insert()`, `update()`, `delete()`, and `transaction()` to simplify your code.

## See Also

- [Collections](/learn/collections) - Learn how to use the Collection class for easy data access.
- [PdoWrapper](/learn/pdo-wrapper) - The legacy PDO helper class (deprecated).

## Troubleshooting

- If you get an error about database connection, check your DSN, username, password, and options.
- All rows are returned as Collections—if you need a plain array, use `$collection->getData()`.
- For `IN (?)` queries, make sure to pass an array.
- If you're experiencing memory issues with query logging in long-running processes, adjust the `maxQueryMetrics` option.

## Changelog

- v3.18.0 - Initial release of SimplePdo with helper methods for insert, update, delete, and transactions.
