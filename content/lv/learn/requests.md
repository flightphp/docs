# Pieprasījumi

Flight ietver HTTP pieprasījumu vienā objektā, kuram var piekļūt, veicot:

```php
$request = Flight::request();
```

Pieprasījuma objekts nodrošina šādas īpašības:

- **body** - Neapstrādātais HTTP pieprasījuma ķermenis
- **url** - Pieprasītais URL
- **base** - URL pamata apakškatalogs
- **method** - Pieprasījuma metode (GET, POST, PUT, DELETE)
- **referrer** - Norādītāja URL
- **ip** - Klienta IP adrese
- **ajax** - Vai pieprasījums ir AJAX pieprasījums
- **scheme** - Servera protokols (http, https)
- **user_agent** - Pārlūkprogrammas informācija
- **type** - Saturs tips
- **length** - Satura garums
- **query** - Pieprasījuma virknes parametri
- **data** - Post datu vai JSON datu
- **cookies** - Sīkdatņu dati
- **files** - Augšupielādētie faili
- **secure** - Vai savienojums ir drošs
- **accept** - HTTP pieņemšanas parametri
- **proxy_ip** - Klienta proxy IP adrese
- **host** - Pieprasījuma resursdatora nosaukums

Jūs varat piekļūt `query`, `data`, `cookies` un `files` īpašībām kā masīviem vai objektiem.

Tātad, lai iegūtu pieprasījuma virknes parametru, jūs varat izdarīt:

```php
$id = Flight::request()->query['id'];
```

Vai arī varat izdarīt:

```php
$id = Flight::request()->query->id;
```

## NEAPSTRĀDĀTS Pieprasījuma ķermenis

Lai iegūtu neapstrādātu HTTP pieprasījuma ķermeni, piemēram, darbojoties ar PUT pieprasījumiem,
jūs varat izdarīt:

```php
$body = Flight::request()->getBody();
```

## JSON Ievade

Ja nosūtāt pieprasījumu ar tipu `application/json` un datiem `{"id": 123}`
tas būs pieejams no `data` īpašības:

```php
$id = Flight::request()->data->id;
```

## Piekļuve `$_SERVER`

Ir ātrgaita pieejama, lai piekļūtu `$_SERVER` masīvam, izmantojot `getVar()` metodi:

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## Pieprasījuma galvenumu piekļuve

Jūs varat piekļūt pieprasījuma galvenumiem, izmantojot `getHeader()` vai `getHeaders()` metodi:

```php

// Varbūt jums nepieciešams Autentifikācijas galvenums
$host = Flight::request()->getHeader('Authorization');

// Ja vēlaties iegūt visus galvenumus
$headers = Flight::request()->getHeaders();
```