# Instalācija

## Lejupielādēt failus.

Ja izmantojat [Composer](https://getcomposer.org), varat izpildīt sekojošo komandu:

```bash
composer require flightphp/core
```

VAI arī varat [lejupielādēt failus](https://github.com/flightphp/core/archive/master.zip) tieši un izvilkt tos savā tīmekļa direktorijā.

## Konfigurējiet savu tīmekļa serveri.

### Apache
Lai Apache, rediģējiet savu `.htaccess` failu ar šādu informāciju:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Piezīme**: Ja jums ir nepieciešams izmantot Flight apakškatalogu, pievienojiet rindu
> `RewriteBase /apakskatalogs/` tieši pēc `RewriteEngine On`.

> **Piezīme**: Ja vēlaties aizsargāt visus servera failus, piemēram, db vai env failu.
> Ievietojiet šo savā `.htaccess` failā:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

Lai Nginx, pievienojiet sekojošo savā servera deklarācijā:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## Izveidojiet savu `index.php` failu.

```php
<?php

// Ja izmantojat Composer, pieprasiet autovadītāju.
require 'vendor/autoload.php';
// ja neizmantojat Composer, ielādējiet pamatni tieši
// require 'flight/Flight.php';

// Tad definējiet maršrutu un piešķiriet funkciju, lai apstrādātu pieprasījumu.
Flight::route('/', function () {
  echo 'sveika pasaule!';
});

// Visbeidzot, startējiet pamatni.
Flight::start();
```  