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
query your database. See [PdoWrapper](/awesome-plugins/pdo-wrapper) for more information.