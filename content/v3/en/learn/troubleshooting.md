# Troubleshooting

This page will help you troubleshoot common issues that you may run into when using Flight.

## Common Issues

### 404 Not Found or Unexpected Route Behavior

If you are seeing a 404 Not Found error (but you swear on your life that it's really there and it's not a typo) this actually could be a problem 
with you returning a value in your route endpoint instead of just echoing it. The reason for this is intentional but could sneak up on some developers.

```php

Flight::route('/hello', function(){
	// This might cause a 404 Not Found error
	return 'Hello World';
});

// What you probably want
Flight::route('/hello', function(){
	echo 'Hello World';
});

```

The reason for this is because of a special mechanism built into the router that handles the return output as a single to "go to the next route". 
You can see the behavior documented in the [Routing](/learn/routing#passing) section.

### Class Not Found (autoloading not working)

There could be a couple reasons for this one not happening. Below are some examples but make sure you also check out the [autoloading](/learn/autoloading) section.

#### Incorrect File Name
The most common is that the class name doesn't match the file name.

If you have a class named `MyClass` then the file should be named `MyClass.php`. If you have a class named `MyClass` and the file is named `myclass.php` 
then the autoloader won't be able to find it.

#### Incorrect Namespace
If you are using namespaces, then the namespace should match the directory structure.

```php
// code

// if your MyController is in the app/controllers directory and it's namespaced
// this will not work.
Flight::route('/hello', 'MyController->hello');

// you'll need to pick one of these options
Flight::route('/hello', 'app\controllers\MyController->hello');
// or if you have a use statement up top

use app\controllers\MyController;

Flight::route('/hello', [ MyController::class, 'hello' ]);
// also can be written
Flight::route('/hello', MyController::class.'->hello');
// also...
Flight::route('/hello', [ 'app\controllers\MyController', 'hello' ]);
```

#### `path()` not defined

In the skeleton app, this is defined inside the `config.php` file, but in order for your classes to be found, you need to make sure that the `path()`
method is defined (probably to the root of your directory) before you try to use it.

```php

// Add a path to the autoloader
Flight::path(__DIR__.'/../');

```