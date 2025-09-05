# Pieprasījumi

Flight ietver HTTP pieprasījumu vienā objektā, ko var piekļūt, darot:

```php
$request = Flight::request();
```

## Tipiski lietošanas gadījumi

Kad strādājat ar pieprasījumu tīmekļa lietotnē, parasti vēlaties izvilkt galveni, vai `$_GET` vai `$_POST` parametru, vai varbūt pat izejošo pieprasījuma ķermeni. Flight nodrošina vienkāršu interfeisu, lai izdarītu visas šīs lietas.

Šeit ir piemērs, kā iegūt vaicājuma virknes parametru:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "You are searching for: $keyword";
	// vaicājiet datubāzi vai kaut ko citu ar $keyword
});
```

Šeit ir piemērs varbūt formas ar POST metodi:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "You submitted: $name, $email";
	// saglabājiet datubāzē vai kaut ko citu ar $name un $email
});
```

## Pieprasījuma objekta īpašības

Pieprasījuma objekts nodrošina šādas īpašības:

- **body** - Neapstrādātais HTTP pieprasījuma ķermenis
- **url** - Pieprasītais URL
- **base** - URL vecāku apakšdirektorija
- **method** - Pieprasījuma metode (GET, POST, PUT, DELETE)
- **referrer** - Atsaucēja URL
- **ip** - Klienta IP adrese
- **ajax** - Vai pieprasījums ir AJAX pieprasījums
- **scheme** - Servera protokols (http, https)
- **user_agent** - Pārdevēja informācija
- **type** - Satura tips
- **length** - Satura garums
- **query** - Vaicājuma virknes parametri
- **data** - Post dati vai JSON dati
- **cookies** - Sīkdatņu dati
- **files** - Augšupielādētie faili
- **secure** - Vai savienojums ir drošs
- **accept** - HTTP pieņemšanas parametri
- **proxy_ip** - Prokses IP adrese klienta. Skenē `$_SERVER` masīvu pēc `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` šādā secībā.
- **host** - Pieprasījuma resursdatora nosaukums
- **servername** - SERVER_NAME no `$_SERVER`

Jūs varat piekļūt `query`, `data`, `cookies` un `files` īpašībām kā masīviem vai objektiem.

Tātad, lai iegūtu vaicājuma virknes parametru, jūs varat darīt:

```php
$id = Flight::request()->query['id'];
```

Vai jūs varat darīt:

```php
$id = Flight::request()->query->id;
```

## Neapstrādātais pieprasījuma ķermenis

Lai iegūtu neapstrādāto HTTP pieprasījuma ķermeni, piemēram, kad strādājat ar PUT pieprasījumiem, jūs varat darīt:

```php
$body = Flight::request()->getBody();
```

## JSON ievade

Ja jūs sūtāt pieprasījumu ar tipu `application/json` un datiem `{"id": 123}`, tas būs pieejams no `data` īpašības:

```php
$id = Flight::request()->data->id;
```

## `$_GET`

Jūs varat piekļūt `$_GET` masīvam caur `query` īpašību:

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

Jūs varat piekļūt `$_POST` masīvam caur `data` īpašību:

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

Jūs varat piekļūt `$_COOKIE` masīvam caur `cookies` īpašību:

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

Ir īssaturs pieejams, lai piekļūtu `$_SERVER` masīvam caur `getVar()` metodi:

```php
$host = Flight::request()->getVar('HTTP_HOST');
```

## Piekļuve augšupielādētajiem failiem caur `$_FILES`

Jūs varat piekļūt augšupielādētajiem failiem caur `files` īpašību:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## Failu augšupielādes apstrāde (v3.12.0)

Jūs varat apstrādāt failu augšupielādes, izmantojot sistēmu ar dažām palīgmētiņām. Tas pamatā samazina uz failu datu izvilkšanu no pieprasījuma un to pārvietošanu uz jaunu vietu.

```php
Flight::route('POST /upload', function(){
	// Ja jums bija ievades lauks, piemēram, <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Ja jums ir vairāki faili augšupielādēti, jūs varat tos iziet cauri:

```php
Flight::route('POST /upload', function(){
	// Ja jums bija ievades lauks, piemēram, <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Drošības piezīme:** Vienmēr validējiet un sanitizējiet lietotāja ievadi, it īpaši, kad strādājat ar failu augšupielādēm. Vienmēr validējiet tipu paplašinājumus, ko jūs atļausiet augšupielādēt, bet jums arī vajadzētu validēt "magic bytes" faila, lai nodrošinātu, ka tas ir patiesi tāds faila tips, ko lietotājs apgalvo. Ir [raksti](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [un](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [bibliotēkas](https://github.com/RikudouSage/MimeTypeDetector) pieejamas, lai palīdzētu ar to.

## Pieprasījuma galvenes

Jūs varat piekļūt pieprasījuma galvenēm, izmantojot `getHeader()` vai `getHeaders()` metodi:

```php
// Varbūt jums vajadzīga Autorizācijas galvene
$host = Flight::request()->getHeader('Authorization');
// vai
$host = Flight::request()->header('Authorization');

// Ja jums vajadzīgs iegūt visas galvenes
$headers = Flight::request()->getHeaders();
// vai
$headers = Flight::request()->headers();
```

## Pieprasījuma ķermenis

Jūs varat piekļūt neapstrādātajam pieprasījuma ķermenim, izmantojot `getBody()` metodi:

```php
$body = Flight::request()->getBody();
```

## Pieprasījuma metode

Jūs varat piekļūt pieprasījuma metodei, izmantojot `method` īpašību vai `getMethod()` metodi:

```php
$method = Flight::request()->method; // faktiski izsauc getMethod()
$method = Flight::request()->getMethod();
```

**Piezīme:** `getMethod()` metode vispirms izvelk metodi no `$_SERVER['REQUEST_METHOD']`, tad to var pārrakstīt ar `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']`, ja tas eksistē, vai `$_REQUEST['_method']`, ja tas eksistē.

## Pieprasījuma URL

Ir daži palīgmētiņi, lai sastādītu URL daļas jūsu ērtībai.

### Pilns URL

Jūs varat piekļūt pilnam pieprasījuma URL, izmantojot `getFullUrl()` metodi:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```

### Bāzes URL

Jūs varat piekļūt bāzes URL, izmantojot `getBaseUrl()` metodi:

```php
$url = Flight::request()->getBaseUrl();
// Pamaniet, nav beigu slīpsvītras.
// https://example.com
```

## Vaicājuma parsēšana

Jūs varat nodot URL uz `parseQuery()` metodi, lai parsētu vaicājuma virkni uz asociatīvo masīvu:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```