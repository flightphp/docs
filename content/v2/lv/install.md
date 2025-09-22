# Instalēšana

### 1. Lejupielādējiet failus.

Ja izmantojat [Composer](https://getcomposer.org), varat izpildīt šādu
komandu:

```bash
composer require flightphp/core
```

VAI varat [lejupielādēt](https://github.com/flightphp/core/archive/master.zip)
tos tieši un izvilkt tos savā tīmekļa direktorijā.

### 2. Konfigurējiet savu tīmekļa serveri.

*Apache* gadījumā rediģējiet savu `.htaccess` failu ar šādu:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Piezīme**: Ja jums ir nepieciešams izmantot Flight apakšdirektorijā, pievienojiet rindu
> `RewriteBase /subdir/` tūlīt aiz `RewriteEngine On`.
> **Piezīme**: Ja vēlaties aizsargāt visus servera failus, piemēram, db vai env failu.
> Ievietojiet to savā `.htaccess` failā:

```apache
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

*Nginx* gadījumā pievienojiet sekojošo savai servera deklarācijai:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

### 3. Izveidojiet savu `index.php` failu.

Vispirms iekļaujiet ietvaru.

```php
require 'flight/Flight.php';
```

Ja izmantojat Composer, palaidiet automātisku ielādi.

```php
require 'vendor/autoload.php';
```

Tad definējiet maršrutu un piešķiriet funkciju, kas apstrādā pieprasījumu.

```php
Flight::route('/', function () {
  echo 'hello world!';
});
```

Visbeidzot uzsāciet ietvaru.

```php
Flight::start();
```
