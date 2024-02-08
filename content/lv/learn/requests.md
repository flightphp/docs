# Pieprasījumi

Flight ietver HTTP pieprasījumu vienā objektā, uz kuru var piekļūt, veicot:

```php
$request = Flight::request();
```

Pieprasījuma objekts nodrošina šādas īpašības:

- **body** - Siets HTTP pieprasījuma ķermenis
- **url** - Pieprasītais URL
- **base** - URL pamata apakškatalogs
- **method** - Pieprasījuma metode (GET, POST, PUT, DELETE)
- **referrer** - Ieteicamais URL
- **ip** - Klienta IP adrese
- **ajax** - Vai pieprasījums ir AJAX pieprasījums
- **scheme** - Servera protokols (http, https)
- **user_agent** - Pārlūka informācija
- **type** - Saturs tipa
- **length** - Saturs garums
- **query** - Meklēšanas virknes parametri
- **data** - Post datu vai JSON datu
- **cookies** - Sīkdatņu dati
- **files** - Augšupielādētie faili
- **secure** - Vai savienojums ir drošs
- **accept** - HTTP pieņemšanas parametri
- **proxy_ip** - Klienta pilnvara IP adrese
- **host** - Pieprasījuma resursa nosaukums

Jūs varat piekļūt `query`, `data`, `cookies` un `files` īpašībām kā masīviem vai objektiem.

Tātad, lai iegūtu meklēšanas virknes parametru, jūs varat veikt:

```php
$id = Flight::request()->query['id'];
```

Vai arī jūs varat veikt:

```php
$id = Flight::request()->query->id;
```

## RAW Pieprasījuma Ķermenis

Lai iegūtu sieta HTTP pieprasījuma ķermeni, piemēram, darbojoties ar PUT pieprasījumiem, jūs varat veikt:

```php
$body = Flight::request()->getBody();
```

## JSON ievade

Ja nosūtāt pieprasījumu ar tipu `application/json` un datu `{"id": 123}`, tas būs pieejams no īpašības `data`:

```php
$id = Flight::request()->data->id;
```

## Piekļuve `$_SERVER`

Ir pieejams saīsne, lai piekļūtu `$_SERVER` masīvam, izmantojot metodi `getVar()`:

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## Pieprasījuma Galvenēm Piekļuves

Jūs varat piekļūt pieprasījuma galvenēm, izmantojot metodi `getHeader()` vai `getHeaders()`:

```php

// Iespējams, jums ir nepieciešama Autentifikācijas galvene
$host = Flight::request()->getHeader('Authorization');

// Ja ir nepieciešams iegūt visas galvenes
$headers = Flight::request()->getHeaders();
```