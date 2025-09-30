# Pieprasījumi

## Pārskats

Flight ietver HTTP pieprasījumu vienā objektā, ko var piekļūt, veicot:

```php
$request = Flight::request();
```

## Saprašana

HTTP pieprasījumi ir viens no galvenajiem aspektiem, kas jāizprot par HTTP dzīves ciklu. Lietotājs veic darbību tīmekļa pārlūkprogrammā vai HTTP klientā, un viņi nosūta virkni galvenes, ķermeņa, URL utt. uz jūsu projektu. Jūs varat uztvert šīs galvenes (pārlūkprogrammas valodu, kādu kompresijas veidu viņi var apstrādāt, lietotāja aģentu utt.) un uztvert ķermeni un URL, kas nosūtīts uz jūsu Flight lietojumprogrammu. Šie pieprasījumi ir būtiski jūsu lietojumprogrammai, lai saprastu, ko darīt tālāk.

## Pamata izmantošana

PHP ir vairākas superglobālās mainīgās, tostarp `$_GET`, `$_POST`, `$_REQUEST`, `$_SERVER`, `$_FILES` un `$_COOKIE`. Flight abstraktē šīs prom uz ērtām [Kolekcijām](/learn/collections). Jūs varat piekļūt `query`, `data`, `cookies` un `files` īpašībām kā masīviem vai objektiem.

> **Piezīme:** Ir **ĻOTI** atvairīts izmantot šīs superglobālās mainīgās savā projektā, un tām jāatsaucas caur `request()` objektu.

> **Piezīme:** Nav pieejama abstrakcija `$_ENV`.

### `$_GET`

Jūs varat piekļūt `$_GET` masīvam caur `query` īpašību:

```php
// GET /search?keyword=something
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	// or
	$keyword = Flight::request()->query->keyword;
	echo "Jūs meklējat: $keyword";
	// vaicāt datubāzi vai kaut ko citu ar $keyword
});
```

### `$_POST`

Jūs varat piekļūt `$_POST` masīvam caur `data` īpašību:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	// or
	$name = Flight::request()->data->name;
	$email = Flight::request()->data->email;
	echo "Jūs iesniedzāt: $name, $email";
	// saglabāt datubāzē vai kaut ko citu ar $name un $email
});
```

### `$_COOKIE`

Jūs varat piekļūt `$_COOKIE` masīvam caur `cookies` īpašību:

```php
Flight::route('GET /login', function(){
	$savedLogin = Flight::request()->cookies['myLoginCookie'];
	// or
	$savedLogin = Flight::request()->cookies->myLoginCookie;
	// pārbaudīt, vai tas tiešām ir saglabāts vai nē, un ja tas ir, automātiski ielogoties
	if($savedLogin) {
		Flight::redirect('/dashboard');
		return;
	}
});
```

Lai iegūtu palīdzību ar jaunu sīkfailu vērtību iestatīšanu, skatiet [overclokk/cookie](/awesome-plugins/php-cookie)

### `$_SERVER`

Ir pieejams saīsinājums, lai piekļūtu `$_SERVER` masīvam caur `getVar()` metodi:

```php

$host = Flight::request()->getVar('HTTP_HOST');
```

### `$_FILES`

Jūs varat piekļūt augšupielādētiem failiem caur `files` īpašību:

```php
// raw access to $_FILES property. See below for recommended approach
$uploadedFile = Flight::request()->files['myFile']; 
// or
$uploadedFile = Flight::request()->files->myFile;
```

Skatiet [Uploaded File Handler](/learn/uploaded-file) vairāk informācijas.

#### Failu augšupielādes apstrāde

_v3.12.0_

Jūs varat apstrādāt failu augšupielādi, izmantojot ietvaru ar dažām palīgmēģīnēm. Tas būtībā samazinās līdz faila datu izvilkšanai no pieprasījuma un tā pārvietošanai uz jaunu atrašanās vietu.

```php
Flight::route('POST /upload', function(){
	// If you had an input field like <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Ja jums ir vairāki augšupielādēti faili, jūs varat tos iterēt:

```php
Flight::route('POST /upload', function(){
	// If you had an input field like <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Drošības piezīme:** Vienmēr validējiet un sanitizējiet lietotāja ievadi, īpaši, kad darbojaties ar failu augšupielādi. Vienmēr validējiet paplašinājumu tipus, kurus atļausit augšupielādēt, bet jums arī vajadzētu validēt faila "burvju baitus", lai nodrošinātu, ka tas tiešām ir tāds faila tips, kādu lietotājs apgalvo. Ir pieejami [raksti](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [un](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [bibliotēkas](https://github.com/RikudouSage/MimeTypeDetector), lai palīdzētu ar to.

### Pieprasījuma ķermenis

Lai iegūtu neapstrādātu HTTP pieprasījuma ķermeni, piemēram, kad darbojaties ar POST/PUT pieprasījumiem,
jūs varat to darīt:

```php
Flight::route('POST /users/xml', function(){
	$xmlBody = Flight::request()->getBody();
	// do something with the XML that was sent.
});
```

### JSON ķermenis

Ja jūs saņemat pieprasījumu ar satura tipu `application/json` un piemēra datiem `{"id": 123}`
tas būs pieejams no `data` īpašības:

```php
$id = Flight::request()->data->id;
```

### Pieprasījuma galvenes

Jūs varat piekļūt pieprasījuma galvenēm, izmantojot `getHeader()` vai `getHeaders()` metodi:

```php

// Maybe you need Authorization header
$host = Flight::request()->getHeader('Authorization');
// or
$host = Flight::request()->header('Authorization');

// If you need to grab all headers
$headers = Flight::request()->getHeaders();
// or
$headers = Flight::request()->headers();
```

### Pieprasījuma metode

Jūs varat piekļūt pieprasījuma metodei, izmantojot `method` īpašību vai `getMethod()` metodi:

```php
$method = Flight::request()->method; // actually populated by getMethod()
$method = Flight::request()->getMethod();
```

**Piezīme:** `getMethod()` metode vispirms izvelk metodi no `$_SERVER['REQUEST_METHOD']`, tad to var pārrakstīt 
`$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']`, ja tas pastāv, vai `$_REQUEST['_method']`, ja tas pastāv.

## Pieprasījuma objekta īpašības

Pieprasījuma objekts nodrošina šādas īpašības:

- **body** - Neapstrādāts HTTP pieprasījuma ķermenis
- **url** - Pieprasītais URL
- **base** - URL vecāka apakšdirektorija
- **method** - Pieprasījuma metode (GET, POST, PUT, DELETE)
- **referrer** - Atsauce URL
- **ip** - Klienta IP adrese
- **ajax** - Vai pieprasījums ir AJAX pieprasījums
- **scheme** - Servera protokols (http, https)
- **user_agent** - Pārlūkprogrammas informācija
- **type** - Satura tips
- **length** - Satura garums
- **query** - Vaicājuma virknes parametri
- **data** - POST dati vai JSON dati
- **cookies** - Sīkfailu dati
- **files** - Augšupielādētie faili
- **secure** - Vai savienojums ir drošs
- **accept** - HTTP pieņemšanas parametri
- **proxy_ip** - Klienta proxy IP adrese. Skenē `$_SERVER` masīvu pēc `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` tajā secībā.
- **host** - Pieprasījuma resursa nosaukums
- **servername** - SERVER_NAME no `$_SERVER`

## URL palīgmēģinātājas

Ir dažas palīgmēģinātājas, lai saliktu URL daļas jūsu ērtībai.

### Pilns URL

Jūs varat piekļūt pilnam pieprasījuma URL, izmantojot `getFullUrl()` metodi:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```

### Bāzes URL

Jūs varat piekļūt bāzes URL, izmantojot `getBaseUrl()` metodi:

```php
// http://example.com/path/to/something/cool?query=yes+thanks
$url = Flight::request()->getBaseUrl();
// https://example.com
// Notice, no trailing slash.
```

## Vaicājuma parsēšana

Jūs varat nodot URL uz `parseQuery()` metodi, lai parsētu vaicājuma virkni asociatīvā masīvā:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```

## Skatīt arī
- [Routing](/learn/routing) - Skatiet, kā kartēt maršrutus uz kontrolieriem un renderēt skatus.
- [Responses](/learn/responses) - Kā pielāgot HTTP atbildes.
- [Why a Framework?](/learn/why-frameworks) - Kā pieprasījumi iederas lielajā attēlā.
- [Collections](/learn/collections) - Darbs ar datu kolekcijām.
- [Uploaded File Handler](/learn/uploaded-file) - Failu augšupielādes apstrāde.

## Traucējummeklēšana
- `request()->ip` un `request()->proxy_ip` var atšķirties, ja jūsu tīmekļa serveris ir aiz proxy, slodzes līdzsvara utt. 

## Izmaiņu žurnāls
- v3.12.0 - Pievienota iespēja apstrādāt failu augšupielādi caur pieprasījuma objektu.
- v1.0 - Sākotnējā izlaišana.