# Installation

## **1\. Download the files.**

If you're using [Composer](https://getcomposer.org), you can run the following
command:

```bash
composer require flightphp/core
```

OR you can [download](https://github.com/flightphp/core/archive/master.zip)
them directly and extract them to your web directory.

## **2\. Configure your webserver.**

For *Apache*, edit your `.htaccess` file with the following:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Note**: If you need to use flight in a subdirectory add the line
> `RewriteBase /subdir/` just after `RewriteEngine On`.
> **Note**: If you want to protect all server files, like a db or env file.
> Put this in your `.htaccess` file:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

For *Nginx*, add the following to your server declaration:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```
## **3\. Create your `index.php` file.**

```php
<?php

// If you're using Composer, require the autoloader.
require 'vendor/autoload.php';
// if you're not using Composer, load the framework directly
// require 'flight/Flight.php';

// Then define a route and assign a function to handle the request.
Flight::route('/', function () {
  echo 'hello world!';
});

// Finally, start the framework.
Flight::start();
```