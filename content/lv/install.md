# Uzstādīšana

## Lejupielādēt failus

Ja izmantojat [Composer](https://getcomposer.org), jūs varat izpildīt sekojošo komandu:

```bash
composer require flightphp/core
```

VAI varat [lejupielādēt failus](https://github.com/flightphp/core/archive/master.zip)
 tieši un izvilkt tos savā tīmekļa katalogā.

## Konfigurēt savu tīmekļa serveri

### Apache

Lai Apache, rediģējiet savu `.htaccess` failu ar sekojošo:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Piezīme**: Ja jums jāizmanto lidojums apakškatalogā, pievienojiet līniju
> `RewriteBase /subdir/` tieši pēc `RewriteEngine On`.

> **Piezīme**: Ja vēlaties aizsargāt visus servera failus, piemēram, db vai env failu.
> Ievietojiet to savā `.htaccess` failā:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

Nginx gadījumā, pievienojiet sekojošo savai servera deklarācijai:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## Izveidojiet savu `index.php` failu

```php
<?php

// Ja izmantojat Composer, pieprasiet autoloāderi.
require 'vendor/autoload.php';
// ja neizmantojat Composer, ielādējiet frameworku tieši
// require 'flight/Flight.php';

// Pēc tam definējiet maršrutu un piešķiriet funkciju, lai apstrādātu pieprasījumu.
Flight::route('/', function () {
  echo 'sveika pasaule!';
});

// Beigās, startējiet ietvaru.
Flight::start();
```