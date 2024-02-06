## Pieprasījumi

Flight ietver HTTP pieprasījumu vienā objektā, kuram var piekļūt:

```php
$request = Flight::request();
```

Pieprasījuma objekts nodrošina šādas īpašības:

- **url** - Pieprasītais URL
- **base** - URL vecākais apakškatalogs
- **method** - Pieprasījuma metode (GET, POST, PUT, DELETE)
- **referrer** - Norādītāja URL
- **ip** - Klienta IP adrese
- **ajax** - Vai pieprasījums ir AJAX pieprasījums
- **scheme** - Servera protokols (http, https)
- **user_agent** - Pārlūka informācija
- **type** - Satura tips
- **length** - Satura garums
- **query** - Vaicājuma rindas parametri
- **data** - Post datu vai JSON datu
- **cookies** - Sīkdatņu dati
- **files** - Augšupielādētie faili
- **secure** - Vai savienojums ir drošs
- **accept** - HTTP akcepta parametri
- **proxy_ip** - Klienta starpnieka IP adrese
- **host** - Pieprasījuma resursdatora nosaukums

Jūs varat piekļūt `query`, `data`, `cookies` un `files` īpašībām kā masīviem vai objektiem.

Tāpēc, lai iegūtu vaicājuma rindas parametru, varat darīt šādi:

```php
$id = Flight::request()->query['id'];
```

Vai arī varat darīt šādi:

```php
$id = Flight::request()->query->id;
```

## RAW Pieprasījuma Ķermenis

Lai iegūtu neapstrādātu HTTP pieprasījuma ķermeni, piemēram, kad darbojaties ar PUT pieprasījumiem, jūs varat darīt šādi:

```php
$body = Flight::request()->getBody();
```

## JSON Ievade

Ja nosūtāt pieprasījumu ar tipu `application/json` un datu `{"id": 123}`, tas būs pieejams no `data` īpašības:

```php
$id = Flight::request()->data->id;
```