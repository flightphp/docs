# Autoloading

Autoloading is a concept in PHP where you specific a directory or directories to load classes from. This is much more beneficial than using `require` or `include` to load classes. It is also a requirement for using Composer packages.

By default any `Flight` class is autoloaded for your automatically thanks to composer. However, if you want to autoload your own classes, you can use the `Flight::path` method to specify a directory to load classes from.

## Basic Example

Let's assume we have a directory tree like the following:

```text
# Example path
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - contains the controllers for this project
│   ├── translations
│   ├── UTILS - contains classes for just this application (this is all caps on purpose for an example later)
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

You may have noticed that this is the same file structure as this documentation site.

You can specify each directory to load from like this:

```php

/**
 * public/index.php
 */

// Add a path to the autoloader
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// no namespacing required

// All autoloaded classes are recommended to be Pascal Case (each word capitalized, no spaces)
// It is a requirement that you cannot have an underscore in your class name
class MyController {

	public function index() {
		// do something
	}
}
```

## Namespaces

If you do have namespaces, it actually becomes very easy to implement this. You should use the `Flight::path()` method to specify the root directory (not the document root or `public/` folder) of your application.

```php

/**
 * public/index.php
 */

// Add a path to the autoloader
Flight::path(__DIR__.'/../');
```

Now this is what your controller might look like. Look at the example below, but pay attention to the comments for important information.

```php
/**
 * app/controllers/MyController.php
 */

// namespaces are required
// namespaces are the same as the directory structure
// namespaces must follow the same case as the directory structure
// namespaces and directories cannot have any underscores
namespace app\controllers;

// All autoloaded classes are recommended to be Pascal Case (each word capitalized, no spaces)
// It is a requirement that you cannot have an underscore in your class name
class MyController {

	public function index() {
		// do something
	}
}
```

And if you wanted to autoload a class in your utils directory, you would do basically the same thing:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// namespace must match the directory structure and case (note the UTILS directory is all caps
//     like in the file tree above)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// do something
	}
}
```


