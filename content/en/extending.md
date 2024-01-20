# Extending / Containers

Flight is designed to be an extensible framework. The framework comes with a set
of default methods and components, but it allows you to map your own methods,
register your own classes, or even override existing classes and methods.

## Mapping Methods

To map your own custom method, you use the `map` function:

```php
// Map your method
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// Call your custom method
Flight::hello('Bob');
```

## Registering Classes / Containerization

To register your own class, you use the `register` function:

```php
// Register your class
Flight::register('user', User::class);

// Get an instance of your class
$user = Flight::user();
```

The register method also allows you to pass along parameters to your class
constructor. So when you load your custom class, it will come pre-initialized.
You can define the constructor parameters by passing in an additional array.
Here's an example of loading a database connection:

```php
// Register class with constructor parameters
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Get an instance of your class
// This will create an object with the defined parameters
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();
```

If you pass in an additional callback parameter, it will be executed immediately
after class construction. This allows you to perform any set up procedures for your
new object. The callback function takes one parameter, an instance of the new object.

```php
// The callback will be passed the object that was constructed
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

By default, every time you load your class you will get a shared instance.
To get a new instance of a class, simply pass in `false` as a parameter:

```php
// Shared instance of the class
$shared = Flight::db();

// New instance of the class
$new = Flight::db(false);
```

Keep in mind that mapped methods have precedence over registered classes. If you
declare both using the same name, only the mapped method will be invoked.

## PDO Helper Class

Flight comes with a helper class for PDO. It allows you to easily query your database
with all the prepared/execute/fetchAll() wackiness. It greatly simplifies how you can 
query your database.

```php
// Register the PDO helper class
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);

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