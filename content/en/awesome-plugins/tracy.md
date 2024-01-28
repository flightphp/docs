# Tracy

Tracy is an amazing error handler that can be used with Flight. It has a number of panels that can help you debug your application. It's also very easy to extend and add your own panels. The Flight Team has created a few panels specifically for Flight projects with the [flightphp/tracy-extensions](https://github.com/flightphp/tracy-extensions) plugin.

## Installation

Install with composer. And you will actually want to install this without the dev version as Tracy comes with a production error handling component.

```bash
composer require tracy/tracy
```

## Basic Configuration

There are some basic configuration options to get started. You can read more about them in the [Tracy Documentation](https://tracy.nette.org/en/configuring).

```php

require 'vendor/autoload.php';

use Tracy\Debugger;

// Enable Tracy
Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT) // sometimes you have to be explicit (also Debugger::PRODUCTION)
// Debugger::enable('23.75.345.200'); // you can also provide an array of IP addresses

// This where errors and exceptions will be logged. Make sure this directory exists and is writable.
Debugger::$logDirectory = __DIR__ . '/../log/';
Debugger::$strictMode = true; // display all errors
// Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // all errors except deprecated notices
if (Debugger::$showBar) {
    $app->set('flight.content_length', false); // if Debugger bar is visible, then content-length can not be set by Flight

	// This is specific to the Tracy Extension for Flight if you've included that
	// otherwise comment this out.
	new TracyExtensionLoader($app);
}
```