# Installation

### 1. Download the files.

If you're using [Composer](https://getcomposer.org/), you can run the following command:

```bash
composer require flightphp/core
```

OR you can [download](https://github.com/flightphp/core/archive/master.zip) them directly and extract them to your web directory.

### 2. Configure your webserver.

For _Apache_, edit your `.htaccess` file with the following:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

For _Nginx_, add the following to your server declaration:

```nginx
server {
    location / {
        try_files $uri $uri/ /index.php;
    }
}
```

### 3. Create your `index.php` file.

First include the framework.

```php
require 'flight/Flight.php';
```

If you're using Composer, run the autoloader instead.

```php
require 'vendor/autoload.php';
```

Then define a route and assign a function to handle the request.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

Finally, start the framework.

```php
Flight::start();
```