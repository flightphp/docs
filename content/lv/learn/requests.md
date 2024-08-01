# Pieprasījumi

Lidojums ietver HTTP pieprasījumu vienīgajā objektā, uz kuru var piekļūt, veicot:

```php
$request = Flight::request();
```

## Tipiski Lietojumi

Strādājot ar pieprasījumu tīmekļa lietojumprogrammā, parasti jums būs jāizvelk galvenes vai `$_GET` vai `$_POST` parametrs, vai pat pati neapstrādātā pieprasījuma ķermenis. Lidojums nodrošina vienkāršu saskarni, lai veiktu šīs darbības.

Šeit ir piemērs, kā izgūt vaicājuma virknes parametru:

```php
Flight::route('/meklēt', function(){
	$atslēgas_vārds = Flight::request()->query['atslēgas_vārds'];
	echo "Jūs meklējat: $atslēgas_vārds";
	// veicot pieprasījumu datu bāzē vai kaut ko citu ar $atslēgas_vārds
});
```

Šeit ir piemērs, iespējams, formas ar POST metodi:

```php
Flight::route('POST /nosūtīt', function(){
	$vārds = Flight::request()->data['vārds'];
	$e-pasts = Flight::request()->data['e-pasts'];
	echo "Jūs iesniedzāt: $vārds, $e-pasts";
	// saglabāt datu bāzē vai kaut ko citu ar $vārds un $e-pasts
});
```

## Pieprasījuma Objekta Atribūti

Pieprasījuma objekts nodrošina šādas īpašības:

- **body** - Neapstrādāta HTTP pieprasījuma apmales zona
- **url** - Pieprasīto URL
- **base** - URL vecākdirektorija
- **metode** - Pieprasījuma metode (GET, POST, PUT, DELETE)
- **referrer** - Nosūtītāja URL
- **ip** - Klienta IP adrese
- **ajax** - Vai pieprasījums ir AJAX pieprasījums
- **scheme** - Servera protokols (http, https)
- **user_agent** - Pārlūkprogrammas informācija
- **type** - Satura tips
- **length** - Satura garums
- **query** - Vaicājuma virknes parametri
- **data** - Post datu vai JSON datu
- **cookies** - Čuku dati
- **files** - Augšupielādētie faili
- **drošs** - Vai savienojums ir drošs
- **accept** - HTTP pieņemamie parametri
- **proxy_ip** - Klienta starpnieka IP adrese. Skenē `$_SERVER` masīvu pēc `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` secībā.
- **host** - Pieprasījuma saimnieka nosaukums

Jūs varat piekļūt `vaicājuma`, `datu`, `čuku` un `failu` īpašībām kā masīviem vai objektiem.

Tātad, lai iegūtu vaicājuma virknes parametru varat veikt:

```php
$id = Flight::request()->query['id'];
```

Vai arī varat veikt:

```php
$id = Flight::request()->query->id;
```

## Neapstrādāta Pieprasījuma Apmales Zona

Lai iegūtu neapstrādāto HTTP pieprasījuma apmales zonu, piemēram, strādājot ar PUT pieprasījumiem, varat veikt:

```php
$body = Flight::request()->getBody();
```

## JSON Ievade

Ja nosūtāt pieprasījumu ar tipu `application/json` un datiem `{"id": 123}`, tas būs pieejams no `dati` īpašības:

```php
$id = Flight::request()->data->id;
```

## `$_GET`

Jūs varat piekļūt `$_GET` masīvam, izmantojot `vaicājuma` īpašumu:

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

Jūs varat piekļūt `$_POST` masīvam, izmantojot `datu` īpašumu:

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

Jūs varat piekļūt `$_COOKIE` masīvam, izmantojot `čuku` īpašumu:

```php
$manaČukuVērtība = Flight::request()->cookies['manaČukuNosaukums'];
```

## `$_SERVER`

Ir pieejams saīsinājums, lai piekļūtu `$_SERVER` masīvam, izmantojot `getVar()` metodi:

```php
$saimnieks = Flight::request()->getVar['HTTP_HOST'];
```

## Augšupielādētie Faili izmantojot `$_FILES`

Jūs varat piekļūt augšupielādētajiem failiem, izmantojot `faili` īpašumu:

```php
$augšupielādētaisFails = Flight::request()->files['mansFails'];
```

## Pieprasījuma Galvenes

Jūs varat piekļūt pieprasījuma galvenēm, izmantojot `getHeader()` vai `getHeaders()` metodi:

```php

// Varbūt jums ir nepieciešama Autorizācijas galvene
$saimnieks = Flight::request()->getHeader('Authorization');
// vai
$saimnieks = Flight::request()->header('Authorization');

// Ja jums ir jāatrod visi galvenes
$galvenes = Flight::request()->getHeaders();
// vai
$galvenes = Flight::request()->headers();
```

## Pieprasījuma Apmales Zona

Jūs varat piekļūt neapstrādātai pieprasījuma apmales zonai, izmantojot `getBody()` metodi:

```php
$body = Flight::request()->getBody();
```

## Pieprasījuma Metode

Jūs varat piekļūt pieprasījuma metodēm, izmantojot `metodi` īpašumu vai `getMethod()` metodi:

```php
$metode = Flight::request()->method; // faktiski izsauc getMethod()
$metode = Flight::request()->getMethod();
```

**Piezīme:** `getMethod()` metode vispirms iegūst metodi no `$_SERVER['REQUEST_METHOD']`, pēc tam to var pārrakstīt
ar `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']`, ja tāda pastāv vai `$_REQUEST['_method']`, ja tāda pastāv.

## Pieprasījuma URL

Ir dažas palīgmeklētāja metodes, lai kopt daļas no URL jums ērtībai.

### Pilns URL

Jūs varat piekļūt pilnam pieprasījuma URL, izmantojot `getFullUrl()` metodi:

```php
$url = Flight::request()->getFullUrl();
// https://piemērs.com/džens/some/path?foo=bar
```
### Bāzes URL

Jūs varat piekļūt bāzes URL, izmantojot `getBaseUrl()` metodi:

```php
$url = Flight::request()->getBaseUrl();
// Pievērsiet uzmanību, nav aizliegtās svītras.
// https://piemērs.com
```

## Vaicājuma Parsēšana

Jūs varat padot URL uz `parseQuery()` metodi, lai parsētu vaicājuma virkni asociatīvajā masīvā:

```php
vaicājums = Flight::request()->parseQuery('https://piemērs.com/džens/some/path?foo=bar');
// ['foo' => 'bar']
```