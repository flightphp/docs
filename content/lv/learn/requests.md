# Pieprasījumi

Flight ietver HTTP pieprasījumu vienā objektā, uz kuru var piekļūt, izpildot:

```php
request = Flight::request();
```

Pieprasījuma objekts nodrošina šādas īpašības:

- **url** - Pieprasītais URL
- **base** - URL pamata apakškatalogs
- **method** - Pieprasījuma metode (GET, POST, PUT, DELETE)
- **referrer** - Pārvietotāja URL
- **ip** - Klienta IP adrese
- **ajax** - Vai pieprasījums ir AJAX pieprasījums
- **scheme** - Servera protokols (http, https)
- **user_agent** - Pārlūka informācija
- **type** - Saturs tipa
- **length** - Saturs garums
- **query** - Vaicājuma virknes parametri
- **data** - Post datu vai JSON datu
- **cookies** - Sīkdatņu dati
- **files** - Augšupielādētie faili
- **secure** - Vai savienojums ir drošs
- **accept** - HTTP pieņemšanas parametri
- **proxy_ip** - Proxy klienta IP adrese
- **host** - Pieprasījuma resursdatora nosaukums

Jūs varat piekļūt `query`, `data`, `cookies` un `files` īpašībām kā masīviem vai objektiem.

Tādēļ, lai iegūtu vaicājuma virknes parametru, jūs varat izdarīt:

```php
id = Flight::request()->query['id'];
```

Vai arī varat izdarīt:

```php
id = Flight::request()->query->id;
```

## NEAPSTRĀDĀTS Pieprasījuma Ķermenis

Lai iegūtu neapstrādātu HTTP pieprasījuma ķermeni, piemēram, darbojoties ar PUT pieprasījumiem,
jūs varat izdarīt:

```php
body = Flight::request()->getBody();
```

## JSON Ievade

Ja nosūtāt pieprasījumu ar tipu `application/json` un datiem `{"id": 123}`
tas būs pieejams no `data` īpašības:

```php
id = Flight::request()->data->id;
```