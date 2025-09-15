# Pieprasījumi

Flight encapsē HTTP pieprasījumu vienā objektā, ko var piekļūt, darot:

```php
$request = Flight::request();
```

## Tipiski lietošanas gadījumi

Kad jūs strādājat ar pieprasījumu tīmekļa lietotnē, parasti jūs vēlaties izvilkt galveni, vai `$_GET` vai `$_POST` parametru, vai varbūt pat izejošo pieprasījuma ķermeni. Flight nodrošina vienkāršu interfeisu, lai izdarītu visas šīs lietas.

Šeit ir piemērs, kā iegūt vaicājuma virknes parametru:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "You are searching for: $keyword";
	// vaicājiet datu bāzi vai kaut ko citu ar $keyword
});
```

Šeit ir piemērs, varbūt formai ar POST metodi:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "You submitted: $name, $email";
	// saglabājiet datu bāzē vai kaut ko citu ar $name un $email
});
```

## Pieprasījuma objekta īpašības

Pieprasījuma objekts nodrošina šādas īpašības:

- **body** - Neapstrādātais HTTP pieprasījuma ķermenis
- **url** - Pieprasītais URL
- **base** - URL vecāka apakšdirektorija
- **method** - Pieprasījuma metode (GET, POST, PUT, DELETE)
- **referrer** - Atsaucēja URL
- **ip** - Klienta IP adrese
- **ajax** - Vai pieprasījums ir AJAX pieprasījums
- **scheme** - Servera protokols (http, https)
- **user_agent** - Pārskata informācija
- **type** - Satura tips
- **length** - Satura garums
- **query** - Vaicājuma virknes parametri
- **data** - Post dati vai JSON dati
- **cookies** - Sīkdatņu dati
- **files** - Augšupielādētie faili
- **secure** - Vai savienojums ir drošs
- **accept** - HTTP pieņemšanas parametri
- **proxy_ip** - Proksija IP adrese klienta. Skenē `$_SERVER` masīvu pēc `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` šādā secībā.
- **host** - Pieprasījuma resursdators nosaukums
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

Ir īssavienojums, lai piekļūtu `$_SERVER` masīvam caur `getVar()` metodi:

```php
$host = Flight::request()->getVar('HTTP_HOST');
```

## Piekļuve augšupielādētajiem failiem caur `$_FILES`

Jūs varat piekļūt augšupielādētajiem failiem caur `files` īpašību:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## Failu augšupielādes apstrāde (v3.12.0)

Jūs varat apstrādāt failu augšupielādes, izmantojot ietvaru ar dažām palīgmētiņām. Tas galvenokārt samazina līdz faila datu izvilkšanai no pieprasījuma un pārvietošanai uz jaunu vietu.

```php
Flight::route('POST /upload', function(){
	// Ja jums bija ievades lauks kā <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Ja jums ir vairāki faili augšupielādēti, jūs varat tos iziet cauri:

```php
Flight::route('POST /upload', function(){
	// Ja jums bija ievades lauks kā <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Drošības piezīme:** Vienmēr validējiet un sanitizējiet lietotāja ievadi, īpaši, kad strādājat ar failu augšupielādēm. Vienmēr validējiet pieļaujamo paplašinājumu tipus, bet jums arī vajadzētu validēt "magic bytes" faila, lai nodrošinātu, ka tas patiešām ir tāds faila tips, ko lietotājs apgalvo. Ir [articles](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [and](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [libraries](https://github.com/RikudouSage/MimeTypeDetector) pieejamas, lai palīdzētu ar to.

## Pieprasījuma galvenes

Jūs varat piekļūt pieprasījuma galvenēm, izmantojot `getHeader()` vai `getHeaders()` metodi:

```php
// Varbūt jums vajadzīga Authorization galvene
$host = Flight::request()->getHeader('Authorization');
// vai
$host = Flight::request()->header('Authorization');

// Ja jums vajadzīgas visas galvenes
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

**Piezīme:** `getMethod()` metode vispirms izvelk metodi no `$_SERVER['REQUEST_METHOD']`, pēc tam to var pārdefinēt ar `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']`, ja tā eksistē, vai `$_REQUEST['_method']`, ja tā eksistē.

## Pieprasījuma URL

Ir daži palīgmētiņi, lai saliktu URL daļas jūsu ērtībai.

### Pilnais URL

Jūs varat piekļūt pilnajam pieprasījuma URL, izmantojot `getFullUrl()` metodi:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```

### Bāzes URL

Jūs varat piekļūt bāzes URL, izmantojot `getBaseUrl()` metodi:

```php
$url = Flight::request()->getBaseUrl();
// Pamaniet, bez gala slīpsvītras.
// https://example.com
```

## Vaicājuma parsēšana

Jūs varat nodot URL uz `parseQuery()` metodi, lai parsētu vaicājuma virkni asociatīvā masīvā:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```