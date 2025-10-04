# Pieprasījumi

## Pārskats

Flight ietver HTTP pieprasījumu vienā objektā, ko var piekļūt, darot:

```php
$request = Flight::request();
```

## Saprašana

HTTP pieprasījumi ir viens no galvenajiem HTTP cikla aspektiem, ko saprast. Lietotājs veic darbību tīmekļa pārlūkprogrammā vai HTTP klientā, un viņi nosūta virkni galvenes, ķermeņa, URL utt. uz jūsu projektu. Jūs varat uztvert šīs galvenes (pārlūkprogrammas valoda, kādu veida kompresiju viņi var apstrādāt, lietotāja aģents utt.) un uztvert ķermeni un URL, kas nosūtīts uz jūsu Flight lietojumprogrammu. Šie pieprasījumi ir būtiski jūsu lietojumprogrammai, lai saprastu, ko darīt tālāk.

## Pamata Izmantošana

PHP ir vairākas superglobālas, tostarp `$_GET`, `$_POST`, `$_REQUEST`, `$_SERVER`, `$_FILES` un `$_COOKIE`. Flight abstraktē šīs prom praktiskos [Collections](/learn/collections). Jūs varat piekļūt `query`, `data`, `cookies` un `files` īpašībām kā masīviem vai objektiem.

> **Piezīme:** Ir ** ĻOTI** atdissludināts izmantot šīs superglobālas savā projektā, un tās jāatsaucas caur `request()` objektu.

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
	// pārbaudīt, vai tas tiešām ir saglabāts vai nē, un ja ir, automātiski pieteikties
	if($savedLogin) {
		Flight::redirect('/dashboard');
		return;
	}
});
```

Palīdzībai iestatīt jaunas cepumu vērtības skatiet [overclokk/cookie](/awesome-plugins/php-cookie)

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

#### Apstrāde Failu Augšupielādes

_v3.12.0_

Jūs varat apstrādāt failu augšupielādi, izmantojot ietvaru ar dažām palīglokalizācijām. Tas būtībā samazinās līdz failu datu izvilkšanai no pieprasījuma un pārvietošanai uz jaunu atrašanās vietu.

```php
Flight::route('POST /upload', function(){
	// Ja jums ir ievades lauks kā <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Ja jums ir vairāki augšupielādēti faili, jūs varat iterēt caur tiem:

```php
Flight::route('POST /upload', function(){
	// Ja jums ir ievades lauks kā <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Drošības Piezīme:** Vienmēr validējiet un sanitējiet lietotāja ievadi, īpaši, kad darbojaties ar failu augšupielādi. Vienmēr validējiet paplašinājumu tipus, kurus atļausiet augšupielādēt, bet jums arī jāvalidē faila "magic bytes", lai nodrošinātu, ka tas tiešām ir tāda tipa fails, kādu lietotājs apgalvo. Ir pieejami [raksti](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [un](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [bibliotēkas](https://github.com/RikudouSage/MimeTypeDetector), lai palīdzētu ar to.

### Pieprasījuma Ķermenis

Lai iegūtu neapstrādātu HTTP pieprasījuma ķermeni, piemēram, kad darbojaties ar POST/PUT pieprasījumiem,
jūs varat darīt:

```php
Flight::route('POST /users/xml', function(){
	$xmlBody = Flight::request()->getBody();
	// darīt kaut ko ar XML, kas tika nosūtīts.
});
```

### JSON Ķermenis

Ja jūs saņemat pieprasījumu ar satura tipu `application/json` un piemēra datiem `{"id": 123}`
tas būs pieejams no `data` īpašības:

```php
$id = Flight::request()->data->id;
```

### Pieprasījuma Galvenes

Jūs varat piekļūt pieprasījuma galvenēm, izmantojot `getHeader()` vai `getHeaders()` metodi:

```php

// Varbūt jums vajag Autorizācijas galveni
$host = Flight::request()->getHeader('Authorization');
// or
$host = Flight::request()->header('Authorization');

// Ja jums vajag paņemt visas galvenes
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

**Piezīme:** `getMethod()` metode vispirms izvelk metodi no `$_SERVER['REQUEST_METHOD']`, tad to var pārrakstīt 
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
- **type** - Satura tips
- **length** - Satura garums
- **query** - Vaicājuma virknes parametri
- **data** - POST dati vai JSON dati
- **cookies** - Cepumu dati
- **files** - Augšupielādēti faili
- **secure** - Vai savienojums ir drošs
- **accept** - HTTP pieņemšanas parametri
- **proxy_ip** - Proxy IP adrese klienta. Skenē `$_SERVER` masīvu pēc `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` tajā secībā.
- **host** - Pieprasījuma resursdatora nosaukums
- **servername** - SERVER_NAME no `$_SERVER`

## Palīglokas

Ir dažas palīglokas, lai saliktu URL daļas vai darbotos ar noteiktām galvenēm.

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
// Pievērsiet uzmanību, nav beigu slīpsvītras.
```

## Vaicājuma Parsēšana

Jūs varat nodot URL uz `parseQuery()` metodi, lai parsētu vaicājuma virkni asociatīvā masīvā:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```

## Sarunu Satura Pieņemšanas Tipi

_v3.17.2_

Jūs varat izmantot `negotiateContentType()` metodi, lai noteiktu labāko satura tipu, ar kuru atbildēt, balstoties uz `Accept` galveni, ko nosūtīja klients.

```php

// Piemēra Accept galvene: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8
// Zemāk definē, ko jūs atbalstāt.
$availableTypes = ['application/json', 'application/xml'];
$typeToServe = Flight::request()->negotiateContentType($availableTypes);
if ($typeToServe === 'application/json') {
	// Pasniegt JSON atbildi
} elseif ($typeToServe === 'application/xml') {
	// Pasniegt XML atbildi
} else {
	// Noklusējuma kaut ko citu vai mest kļūdu
}
```

> **Piezīme:** Ja neviens no pieejamajiem tipiem nav atrasts `Accept` galvenē, metode atgriezīs `null`. Ja nav definēta `Accept` galvene, metode atgriezīs pirmo tipu `$availableTypes` masīvā.

## Skatīt Arī
- [Routing](/learn/routing) - Skatiet, kā kartēt maršrutus uz kontrolieriem un renderēt skatus.
- [Responses](/learn/responses) - Kā pielāgot HTTP atbildes.
- [Why a Framework?](/learn/why-frameworks) - Kā pieprasījumi iekļaujas lielajā attēlā.
- [Collections](/learn/collections) - Darbs ar datu kolekcijām.
- [Uploaded File Handler](/learn/uploaded-file) - Failu augšupielādes apstrāde.

## Traucējummeklēšana
- `request()->ip` un `request()->proxy_ip` var atšķirties, ja jūsu tīmekļa serveris ir aiz proxy, slodzes līdzsvara utt. 

## Izmaiņu Žurnāls
- v3.17.2 - Pievienota negotiateContentType()
- v3.12.0 - Pievienota spēja apstrādāt failu augšupielādi caur pieprasījuma objektu.
- v1.0 - Sākotnējā izlaišana.