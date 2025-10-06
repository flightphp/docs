# Pieprasījumi

## Pārskats

Flight ieslēdz HTTP pieprasījumu vienā objektā, ko var piekļūt, veicot:

```php
$request = Flight::request();
```

## Saprašana

HTTP pieprasījumi ir viens no galvenajiem HTTP dzīves cikla aspektiem, ko jāizprot. Lietotājs veic darbību tīmekļa pārlūkprogrammā vai HTTP klientā, un tie nosūta virkni galvenes, ķermeņa, URL utt. uz jūsu projektu. Jūs varat uztvert šīs galvenes (pārlūkprogrammas valoda, kādu kompresijas veidu tās var apstrādāt, lietotāja aģents utt.) un uztvert ķermeni un URL, kas nosūtīts uz jūsu Flight lietojumprogrammu. Šie pieprasījumi ir būtiski, lai jūsu lietojumprogramma saprastu, ko darīt tālāk.

## Pamata Izmantošana

PHP ir vairākas superglobālās mainīgās, tostarp `$_GET`, `$_POST`, `$_REQUEST`, `$_SERVER`, `$_FILES` un `$_COOKIE`. Flight abstraktē šīs prom praktiskajās [Kolekcijās](/learn/collections). Jūs varat piekļūt `query`, `data`, `cookies` un `files` īpašībām kā masīviem vai objektiem.

> **Piezīme:** Ir ** ĻOTI** atgrūstami izmantot šīs superglobālās mainīgās savā projektā, un tām jāatsaucas caur `request()` objektu.

> **Piezīme:** Nav pieejama abstrakcija `$_ENV`.

### `$_GET`

Jūs varat piekļūt `$_GET` masīvam caur `query` īpašību:

```php
// GET /search?keyword=something
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	// or
	$keyword = Flight::request()->query->keyword;
	echo "You are searching for: $keyword";
	// query a database or something else with the $keyword
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
	echo "You submitted: $name, $email";
	// save to a database or something else with the $name and $email
});
```

### `$_COOKIE`

Jūs varat piekļūt `$_COOKIE` masīvam caur `cookies` īpašību:

```php
Flight::route('GET /login', function(){
	$savedLogin = Flight::request()->cookies['myLoginCookie'];
	// or
	$savedLogin = Flight::request()->cookies->myLoginCookie;
	// check if it's really saved or not and if it is auto log them in
	if($savedLogin) {
		Flight::redirect('/dashboard');
		return;
	}
});
```

Lai iegūtu palīdzību par jaunu sīkfailu vērtību iestatīšanu, skatiet [overclokk/cookie](/awesome-plugins/php-cookie)

### `$_SERVER`

Ir pieejams saīsinājums, lai piekļūtu `$_SERVER` masīvam caur `getVar()` metodi:

```php

$host = Flight::request()->getVar('HTTP_HOST');
```

### `$_FILES`

Jūs varat piekļūt augšupielādētajām failiem caur `files` īpašību:

```php
// raw access to $_FILES property. See below for recommended approach
$uploadedFile = Flight::request()->files['myFile']; 
// or
$uploadedFile = Flight::request()->files->myFile;
```

Skatiet [Uploaded File Handler](/learn/uploaded-file) vairāk informācijas.

#### Failu Augšupielādes Apstrāde

_v3.12.0_

Jūs varat apstrādāt failu augšupielādi, izmantojot ietvaru ar dažām palīgapstrādes metodēm. Tas būtībā samazinās līdz faila datu iegūšanai no pieprasījuma un to pārvietošanai uz jaunu atrašanās vietu.

```php
Flight::route('POST /upload', function(){
	// If you had an input field like <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Ja jums ir vairāki augšupielādēti faili, jūs varat tos iziet cauri cilpai:

```php
Flight::route('POST /upload', function(){
	// If you had an input field like <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Drošības Piezīme:** Vienmēr validējiet un sanitizējiet lietotāja ievadi, īpaši, kad strādājat ar failu augšupielādi. Vienmēr validējiet paplašinājumu veidus, kurus atļausiet augšupielādēt, bet jums arī vajadzētu validēt faila "magic bytes", lai nodrošinātu, ka tas patiešām ir tāds faila veids, kādu lietotājs apgalvo. Ir pieejami [raksti](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [un](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [bibliotēkas](https://github.com/RikudouSage/MimeTypeDetector), lai palīdzētu ar to.

### Pieprasījuma Ķermenis

Lai iegūtu neapstrādātu HTTP pieprasījuma ķermeni, piemēram, strādājot ar POST/PUT pieprasījumiem,
jūs varat veikt:

```php
Flight::route('POST /users/xml', function(){
	$xmlBody = Flight::request()->getBody();
	// do something with the XML that was sent.
});
```

### JSON Ķermenis

Ja jūs saņemat pieprasījumu ar satura veidu `application/json` un piemēra datiem `{"id": 123}`
tas būs pieejams no `data` īpašības:

```php
$id = Flight::request()->data->id;
```

### Pieprasījuma Galvenes

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

### Pieprasījuma Metode

Jūs varat piekļūt pieprasījuma metodei, izmantojot `method` īpašību vai `getMethod()` metodi:

```php
$method = Flight::request()->method; // actually populated by getMethod()
$method = Flight::request()->getMethod();
```

**Piezīme:** `getMethod()` metode vispirms iegūst metodi no `$_SERVER['REQUEST_METHOD']`, tad to var pārspēt 
`$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']`, ja tā pastāv, vai `$_REQUEST['_method']`, ja tā pastāv.

## Pieprasījuma Objekta Īpašības

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
- **type** - Satura veids
- **length** - Satura garums
- **query** - Vaicājuma virknes parametri
- **data** - POST dati vai JSON dati
- **cookies** - Sīkfailu dati
- **files** - Augšupielādētie faili
- **secure** - Vai savienojums ir drošs
- **accept** - HTTP pieņemšanas parametri
- **proxy_ip** - Klienta proxy IP adrese. Skenē `$_SERVER` masīvu pēc `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` šādā secībā.
- **host** - Pieprasījuma saimnieka nosaukums
- **servername** - SERVER_NAME no `$_SERVER`

## Palīgapstrādes Metodes

Ir dažas palīgapstrādes metodes, lai saliktu URL daļas vai strādātu ar noteiktām galvenēm.

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

## Vaicājuma Parsēšana

Jūs varat nodot URL uz `parseQuery()` metodi, lai parsētu vaicājuma virkni asociatīvā masīvā:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```

## Saturu Pieņemšanas Veidu Sarunas

_v3.17.2_

Jūs varat izmantot `negotiateContentType()` metodi, lai noteiktu labāko satura veidu, ar kuru atbildēt, balstoties uz `Accept` galveni, ko nosūtījis klients.

```php

// Example Accept header: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8
// The below defines what you support.
$availableTypes = ['application/json', 'application/xml'];
$typeToServe = Flight::request()->negotiateContentType($availableTypes);
if ($typeToServe === 'application/json') {
	// Serve JSON response
} elseif ($typeToServe === 'application/xml') {
	// Serve XML response
} else {
	// Default to something else or throw an error
}
```

> **Piezīme:** Ja neviena no pieejamajām veidiem nav atrasta `Accept` galvenē, metode atgriezīs `null`. Ja nav definēta `Accept` galvene, metode atgriezīs pirmo veidu `$availableTypes` masīvā.

## Skatīt Arī
- [Routing](/learn/routing) - Skatiet, kā kartēt maršrutus uz kontrolieriem un renderēt skatus.
- [Responses](/learn/responses) - Kā pielāgot HTTP atbildes.
- [Why a Framework?](/learn/why-frameworks) - Kā pieprasījumi iekļaujas lielajā attēlā.
- [Collections](/learn/collections) - Darbs ar datu kolekcijām.
- [Uploaded File Handler](/learn/uploaded-file) - Failu augšupielādes apstrāde.

## Traucējummeklēšana
- `request()->ip` un `request()->proxy_ip` var atšķirties, ja jūsu tīmekļa serveris ir aiz proxy, slodzes balansētāja utt. 

## Izmaiņu Žurnāls
- v3.17.2 - Pievienota negotiateContentType()
- v3.12.0 - Pievienota spēja apstrādāt failu augšupielādi caur pieprasījuma objektu.
- v1.0 - Sākotnējais izdevums.