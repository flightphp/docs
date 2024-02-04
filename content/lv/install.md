# Uzstādīšana

## **1. Lejupielādējiet failus.**

Ja izmantojat [Composer](https://getcomposer.org), varat izpildīt sekojošo
komandu:

```bash
composer require flightphp/core
```

VAI arī varat [lejupielādēt](https://github.com/flightphp/core/archive/master.zip)
failus tieši un izpakot tos savā tīmekļa direktorijā.

## **2. Konfigurējiet savu tīmekļa serveri.**

*Apache* gadījumā rediģējiet savu `.htaccess` failu ar šādu saturu:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Piezīme**: Ja vēlaties izmantot "flight" apakšmapē, pievienojiet rindiņu
> `RewriteBase /apaksmape/` tieši pēc `RewriteEngine On`.
> **Piezīme**: Ja vēlaties aizsargāt visus servera failus, piemēram, db vai env failu.
> Ielieciet šo savā `.htaccess` failā:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

*Nginx* gadījumā pievienojiet sekojošo savā servera deklarācijā:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## **3. Izveidojiet savu `index.php` failu.**

```php
<?php

// Ja izmantojat Composer, pievienojiet autoloader.
require 'vendor/autoload.php';
// ja neizmantojat Composer, ielādējiet ietvaru tieši
// require 'flight/Flight.php';

// Tad definējiet maršrutu un piešķiriet funkciju, lai apstrādātu pieprasījumu.
Flight::route('/', function () {
  echo 'sveika pasaule!';
});

// Beigās startējiet ietvaru.
Flight::start();
```