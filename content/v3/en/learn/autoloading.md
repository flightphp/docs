# Autoloading

## Overview

Autoloading is a concept in PHP where you specify a directory or directories to load classes from. This is much more beneficial than using `require` or `include` to load classes. It is also a requirement for using Composer packages.

## Understanding

By default, any `Flight` class is autoloaded for you automatically thanks to composer. However, if you want to autoload your own classes, you can use the `Flight::path()` method to specify a directory to load classes from.

Using an autoloader can help simplify your code in a significant way. Instead of having files start with a myriad of `include` or `require` statements at the top to capture all classes that are used in that file, you can instead dynamically call your classes and they will be included automatically.

## Basic Usage

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
// namespaces and directories cannot have any underscores (unless Loader::setV2ClassLoading(false) is set)
namespace app\controllers;

// All autoloaded classes are recommended to be Pascal Case (each word capitalized, no spaces)
// As of 3.7.2, you can use Pascal_Snake_Case for your class names by running Loader::setV2ClassLoading(false);
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

## Underscores in Class Names

As of 3.7.2, you can use Pascal_Snake_Case for your class names by running `Loader::setV2ClassLoading(false);`. 
This will allow you to use underscores in your class names. 
This is not recommended, but it is available for those who need it.

```php
use flight\core\Loader;

/**
 * public/index.php
 */

// Add a path to the autoloader
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');
Loader::setV2ClassLoading(false);

/**
 * app/controllers/My_Controller.php
 */

// no namespacing required

class My_Controller {

	public function index() {
		// do something
	}
}
```

## See Also
- [Routing](/learn/routing) - How to map routes to controllers and render views.
- [Why a Framework?](/learn/why-frameworks) - Understanding the benefits of using a framework like Flight.

## Troubleshooting
- If you can't seem to figure out why your namespaced classes aren't being found, remember to use `Flight::path()` to the root directory in your project, not your `app/` or `src/` directory or equivalent.

### Class Not Found (autoloading not working)

There could be a couple reasons for this one not happening. Below are some examples but make sure you also check out the [autoloading](/learn/autoloading) section.

#### Incorrect File Name
The most common is that the class name doesn't match the file name.

If you have a class named `MyClass` then the file should be named `MyClass.php`. If you have a class named `MyClass` and the file is named `myclass.php` 
then the autoloader won't be able to find it.

#### Incorrect Namespace
If you are using namespaces, then the namespace should match the directory structure.

```php
// ...code...

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

## Changelog
- v3.7.2 - You can use Pascal_Snake_Case for your class names by running `Loader::setV2ClassLoading(false);`
- v2.0 - Autoload functionality added.
