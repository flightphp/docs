# PdoWrapper PDO Helper Class

Flight comes with a helper class for PDO. It allows you to easily query your database
with all the prepared/execute/fetchAll() wackiness. It greatly simplifies how you can 
query your database. Each row result is returned as a Flight Collection class which
allows you to access your data via array syntax or object syntax.

## Registering the PDO Helper Class

```php
// Register the PDO helper class
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## Usage
This object extends PDO so all the normal PDO methods are available. The following methods are added to make querying the database easier:

### `runQuery(string $sql, array $params = []): PDOStatement`
Use this for INSERTS, UPDATES, or if you plan on using a SELECT in a while loop

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM table WHERE something = ?", [ $something ]);
while($row = $statement->fetch()) {
	// ...
}

// Or writing to the database
$db->runQuery("INSERT INTO table (name) VALUES (?)", [ $name ]);
$db->runQuery("UPDATE table SET name = ? WHERE id = ?", [ $name, $id ]);
```

### `fetchField(string $sql, array $params = []): mixed`
Pulls the first field from the query

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM table WHERE something = ?", [ $something ]);
```

### `fetchRow(string $sql, array $params = []): array`
Pulls one row from the query

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT id, name FROM table WHERE id = ?", [ $id ]);
echo $row['name'];
// or
echo $row->name;
```

### `fetchAll(string $sql, array $params = []): array`
Pulls all rows from the query

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT id, name FROM table WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	echo $row['name'];
	// or
	echo $row->name;
}
```

## Note with `IN()` syntax
This also has a helpful wrapper for `IN()` statements. You can simply pass a single question mark as a placeholder for `IN()` and then an array of values. Here is an example of what that might look like:

```php
$db = Flight::db();
$name = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT id, name FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## Full Example

```php
// Example route and how you would use this wrapper
Flight::route('/users', function () {
	// Get all users
	$users = Flight::db()->fetchAll('SELECT * FROM users');

	// Stream all users
	$statement = Flight::db()->runQuery('SELECT * FROM users');
	while ($user = $statement->fetch()) {
		echo $user['name'];
		// or echo $user->name;
	}

	// Get a single user
	$user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

	// Get a single value
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

	// Special IN() syntax to help out (make sure IN is in caps)
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
	// you could also do this
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [ '1,2,3,4,5']);

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