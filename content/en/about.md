# What is Flight?

Flight is a fast, simple, extensible framework for PHP.
Flight enables you to quickly and easily build RESTful web applications.

```php
<?php

// if installed with composer
require 'vendor/autoload.php';
// or if installed manually by zip file
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::start();
```

[Learn more](learn)

## Skeleton App
You can also install a skeleton app. Go to [flightphp/skeleton](https://github.com/flightphp/skeleton) for instructions on how to get started!

# Community

We're on Matrix! Chat with us at [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Contributing

This website is hosted on [Github](https://github.com/flightphp/docs). If you notice an error, feel free to correct it and submit a pull request!
We try to keep up on things, but updates and language translations are welcome.

# Requirements

Flight requires PHP 7.4 or greater.

**Note:** PHP 7.4 is supported because at the current time of writing (2024) PHP 7.4 is the default version for some LTS Linux distributions. Forcing a move to PHP >8 would cause a lot of heartburn for those users. The framework also supports PHP >8.

# License

Flight is released under the [MIT](https://github.com/flightphp/core/blob/master/LICENSE) license.