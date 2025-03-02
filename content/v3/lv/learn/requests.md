# Pieprasījumi

Flight īsteno HTTP pieprasījumu kā vienu objektu, ko var
piekļūt, izpildot:

```php
$request = Flight::request();
```

## Tipiskie Lietojumi

Strādājot ar pieprasījumu tīmekļa lietotnē, parasti vēlēsities
izņemt galveni, vai `$_GET` vai `$_POST` parametru, vai pat
neapstrādāto pieprasījuma ķermeni. Flight nodrošina vienkāršu saskarni, lai veiktu visas
šīs darbības.

Šeit ir piemērs, kā iegūt vaicājuma virknes parametru:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "Jūs meklējat: $keyword";
	// vaicā datubāzei vai kaut kam citam ar $keyword
});
```

Šeit ir piemērs varbūt veidlapai ar POST metodi:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "Jūs iesniedzāt: $name, $email";
	// saglabājiet datubāzē vai kaut kam citam ar $name un $email
});
```

## Pieprasījuma Objekta Atribūti

Pieprasījuma objekts nodrošina šādus atribūtus:

- **body** - Neapstrādātais HTTP pieprasījuma ķermenis
- **url** - Pieprasītā URL
- **base** - URL vecāku apakšdirektorija
- **method** - Pieprasījuma metode (GET, POST, PUT, DELETE)
- **referrer** - Atsauces URL
- **ip** - Klienta IP adrese
- **ajax** - Vai pieprasījums ir AJAX pieprasījums
- **scheme** - Servera protokols (http, https)
- **user_agent** - Pārlūka informācija
- **type** - Satura tips
- **length** - Satura garums
- **query** - Vaicājuma virknes parametri
- **data** - POST dati vai JSON dati
- **cookies** - Sīkdatņu dati
- **files** - Augšupielādēti faili
- **secure** - Vai savienojums ir drošs
- **accept** - HTTP pieņemšanas parametri
- **proxy_ip** - Klienta prokša IP adrese. Skatās `$_SERVER` masīvā `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` tā secībā.
- **host** - Pieprasījuma saimniekdatora nosaukums

Jūs varat piekļūt `query`, `data`, `cookies` un `files` atribūtiem
kā masīviem vai objektiem.

Tātad, lai iegūtu vaicājuma virknes parametru, varat darīt:

```php
$id = Flight::request()->query['id'];
```

Vai arī varat darīt:

```php
$id = Flight::request()->query->id;
```

## NEAPSTRĀDĀTA PIEPRASĪJUMA ĶERMENIS

Lai iegūtu neapstrādāto HTTP pieprasījuma ķermeni, piemēram, strādājot ar PUT pieprasījumiem,
varat darīt:

```php
$body = Flight::request()->getBody();
```

## JSON Ievade

Ja jūs sūtāt pieprasījumu ar tipu `application/json` un datiem `{"id": 123}`
tas būs pieejams no `data` atribūta:

```php
$id = Flight::request()->data->id;
```

## `$_GET`

Jūs varat piekļūt `$_GET` masīvam, izmantojot `query` atribūtu:

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

Jūs varat piekļūt `$_POST` masīvam, izmantojot `data` atribūtu:

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

Jūs varat piekļūt `$_COOKIE` masīvam, izmantojot `cookies` atribūtu:

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

Ir pieejams īsceļš, lai piekļūtu `$_SERVER` masīvam, izmantojot `getVar()` metodi:

```php
$host = Flight::request()->getVar['HTTP_HOST'];
```

## Piekļuve Augšupielādētajiem Failiem, izmantojot `$_FILES`

Jūs varat piekļūt augšupielādētajiem failiem, izmantojot `files` atribūtu:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## Failu Augšupielādes Apstrāde

Jūs varat apstrādāt failu augšupielādes, izmantojot ietvaru ar dažām palīgdarbībām. Tas būtībā
samazinās līdz faila datu vilkšanai no pieprasījuma un pārvietošanai uz jaunu vietu.

```php
Flight::route('POST /upload', function(){
	// Ja jums bija ievades lauks, piemēram, <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Ja jums ir vairāki augšupielādēti faili, varat tos aplūkot:

```php
Flight::route('POST /upload', function(){
	// Ja jums bija ievades lauks, piemēram, <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Drošības Piezīme:** Vienmēr validējiet un sanitizējiet lietotāju ievadi, īpaši, strādājot ar failu augšupielādēm. Vienmēr validējiet pieļaujamo paplašinājumu tipus, ko ļausiet augšupielādēt, bet jums arī jāvalidē faila "burvju baiti", lai pārliecinātos, ka tas patiešām ir tāda tipu fails, kādu lietotājs apgalvo. Ir pieejami [raksti](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [un](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [bibliotēkas](https://github.com/RikudouSage/MimeTypeDetector), kuras var jums palīdzēt ar to.

## Pieprasījuma Galvenes

Jūs varat piekļūt pieprasījuma galvenēm, izmantojot `getHeader()` vai `getHeaders()` metodi:

```php
// Varbūt jums ir nepieciešama autorizācijas galvene
$host = Flight::request()->getHeader('Authorization');
// vai
$host = Flight::request()->header('Authorization');

// Ja jums nepieciešams iegūt visas galvenes
$headers = Flight::request()->getHeaders();
// vai
$headers = Flight::request()->headers();
```

## Pieprasījuma Ķermenis

Jūs varat piekļūt neapstrādātajam pieprasījuma ķermenim, izmantojot `getBody()` metodi:

```php
$body = Flight::request()->getBody();
```

## Pieprasījuma Metode

Jūs varat piekļūt pieprasījuma metodei, izmantojot `method` atribūtu vai `getMethod()` metodi:

```php
$method = Flight::request()->method; // faktiski zvana getMethod()
$method = Flight::request()->getMethod();
```

**Piezīme:** `getMethod()` metode vispirms ņem metodi no `$_SERVER['REQUEST_METHOD']`, pēc tam to var pārrakstīt 
ar `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']`, ja tas pastāv, vai `$_REQUEST['_method']`, ja tas pastāv.

## Pieprasījuma URL

Ir pieejamas dažas palīgmetodes, lai ērti saliktu URL daļas.

### Pilns URL

Jūs varat piekļūt pilnam pieprasījuma URL, izmantojot `getFullUrl()` metodi:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```

### Pamata URL

Jūs varat piekļūt pamata URL, izmantojot `getBaseUrl()` metodi:

```php
$url = Flight::request()->getBaseUrl();
// Pievērsiet uzmanību, nav beigu slīpsvītras.
// https://example.com
```

## Vaicājuma Analīze

Jūs varat nodot URL uz `parseQuery()` metodi, lai analizētu vaicājuma virkni asociatīvā masīvā:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```