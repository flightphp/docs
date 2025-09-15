# Atbildes

Flight palīdz ģenerēt daļu no atbildes galvenes jums, bet jūs kontrolējat lielāko daļu no tā, ko sūtāt atpakaļ lietotājam. Dažreiz jūs varat tieši piekļūt `Response` objektam, bet lielākoties jūs izmantosiet `Flight` instanci, lai nosūtītu atbildi.

## Sūtīšana pamata atbildes

Flight izmanto ob_start(), lai buferizētu izvadi. Tas nozīmē, ka jūs varat izmantot `echo` vai `print`, lai nosūtītu atbildi lietotājam, un Flight to uztvers un nosūtīs atpakaļ lietotājam ar atbilstošajām galvenēm.

```php
// Šis nosūtīs "Hello, World!" uz lietotāja pārlūku
Flight::route('/', function() {
	echo "Hello, World!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hello, World!
```

Kā alternatīvu, jūs varat izsaukt `write()` metodi, lai pievienotu ķermenim.

```php
// Šis nosūtīs "Hello, World!" uz lietotāja pārlūku
Flight::route('/', function() {
	// verbāli, bet dažreiz tas ir nepieciešams
	Flight::response()->write("Hello, World!");

	// ja jūs vēlaties izgūt ķermeni, ko esat iestatījis šajā brīdī
	// jūs varat to darīt šādi
	$body = Flight::response()->getBody();
});
```

## Statusa kodi

Jūs varat iestatīt atbildes statusa kodu, izmantojot `status` metodi:

```php
Flight::route('/@id', function($id) {
	if($id == 123) {
		Flight::response()->status(200);
		echo "Hello, World!";
	} else {
		Flight::response()->status(403);
		echo "Forbidden";
	}
});
```

Ja jūs vēlaties iegūt pašreizējo statusa kodu, jūs varat izmantot `status` metodi bez argumentiem:

```php
Flight::response()->status(); // 200
```

## Iestatīšana atbildes ķermenim

Jūs varat iestatīt atbildes ķermeni, izmantojot `write` metodi, tomēr, ja jūs echo vai print kaut ko, 
tas tiks uztverts un nosūtīts kā atbildes ķermenis caur izvades buferizēšanu.

```php
Flight::route('/', function() {
	Flight::response()->write("Hello, World!");
});

// tas pats kā

Flight::route('/', function() {
	echo "Hello, World!";
});
```

### Notīrīšana atbildes ķermenim

Ja jūs vēlaties notīrīt atbildes ķermeni, jūs varat izmantot `clearBody` metodi:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### Palaidiet atpakaļzvanu uz atbildes ķermeni

Jūs varat palaist atpakaļzvanu uz atbildes ķermeni, izmantojot `addResponseBodyCallback` metodi:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Šis saspiest visus atbildes visiem maršrutiem ar gzip
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Jūs varat pievienot vairākas atpakaļzvani un tās tiks palaistas secībā, kādā tās tika pievienotas. Tā kā tas var pieņemt jebkuru [aicināmu](https://www.php.net/manual/en/language.types.callable.php), tas var pieņemt klases masīvu `[ $class, 'method' ]`, aizvērumu `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, vai funkcijas nosaukumu `'minify'`, ja jums ir funkcija, lai minimizētu jūsu html kodu, piemēram.

**Piezīme:** Maršrutu atpakaļzvani nedarbosies, ja jūs izmantojat `flight.v2.output_buffering` konfigurācijas opciju.

### Specifisks maršruta atpakaļzvans

Ja jūs vēlaties, lai tas attiektos tikai uz specifisku maršrutu, jūs varētu pievienot atpakaļzvanu pašā maršrutā:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Šis saspiest tikai šo maršrutu ar gzip
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### Starpprogrammatūras opcija

Jūs varat arī izmantot starpprogrammatūru, lai piemērotu atpakaļzvanu visiem maršrutiem caur starpprogrammatūru:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// Piemērojiet atpakaļzvanu šeit uz response() objektu.
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// kāds veids, kā minimizēt ķermeni
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

## Iestatīšana atbildes galvenei

Jūs varat iestatīt galveni, piemēram, satura tipu atbildei, izmantojot `header` metodi:

```php
// Šis nosūtīs "Hello, World!" uz lietotāja pārlūku kā vienkāršu tekstu
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// vai
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight nodrošina atbalstu JSON un JSONP atbildēm. Lai nosūtītu JSON atbildi, jūs
nododiet datus, kas jākodē JSON:

```php
Flight::json(['id' => 123]);
```

> **Piezīme:** Pēc noklusējuma, Flight nosūtīs `Content-Type: application/json` galveni ar atbildi. Tas arī izmantos konstantes `JSON_THROW_ON_ERROR` un `JSON_UNESCAPED_SLASHES`, kodējot JSON.

### JSON ar statusa kodu

Jūs varat arī nodot statusa kodu kā otro argumentu:

```php
Flight::json(['id' => 123], 201);
```

### JSON ar skaistu izdruku

Jūs varat arī nodot argumentu pēdējā pozīcijā, lai iespējotu skaistu izdruku:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Ja jūs maināt opcijas, kas nodotas uz `Flight::json()` un vēlaties vienkāršāku sintaksi, jūs varat 
pārkartēt JSON metodi:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// Un tagad to var izmantot šādi
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON un izpildes pārtraukšana (v3.10.0)

Ja jūs vēlaties nosūtīt JSON atbildi un pārtraukt izpildi, jūs varat izmantot `jsonHalt()` metodi.
Tas ir noderīgi gadījumos, kur jūs pārbaudāt, piemēram, autorizāciju un, ja lietotājs nav autorizēts, jūs varat nosūtīt JSON atbildi nekavējoties, notīrīt esošo ķermeņa saturu un pārtraukt izpildi.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Pārbaudiet, vai lietotājs ir autorizēts
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
	}

	// Turpiniet ar pārējo maršrutu
});
```

Pirms v3.10.0, jums būtu jādara kaut kas tāds:

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Pārbaudiet, vai lietotājs ir autorizēts
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// Turpiniet ar pārējo maršrutu
});
```

### JSONP

For JSONP pieprasījumiem jūs, varat izvēles kārtā nodot vaicājuma parametra nosaukumu, ko jūs izmantojat, lai definētu savu atpakaļzvanu funkciju:

```php
Flight::jsonp(['id' => 123], 'q');
```

Tātad, veicot GET pieprasījumu, izmantojot `?q=my_func`, jums vajadzētu saņemt izvadi:

```javascript
my_func({"id":123});
```

Ja jūs neesat nodevis vaicājuma parametra nosaukumu, tas pēc noklusējuma būs `jsonp`.

## Pārvirzīšana uz citu URL

Jūs varat pārvirzīt pašreizējo pieprasījumu, izmantojot `redirect()` metodi un nododot
jaunu URL:

```php
Flight::redirect('/new/location');
```

Pēc noklusējuma Flight nosūta HTTP 303 ("See Other") statusa kodu. Jūs varat izvēles kārtā iestatīt
pielāgotu kodu:

```php
Flight::redirect('/new/location', 401);
```

## Apturēšana

Jūs varat apturēt sistēmu jebkurā brīdī, izsaucot `halt` metodi:

```php
Flight::halt();
```

Jūs varat arī norādīt izvēles `HTTP` statusa kodu un ziņojumu:

```php
Flight::halt(200, 'Be right back...');
```

Izsaucot `halt` tiks atbrīvots jebkurš atbildes saturs līdz šim punktam. Ja jūs vēlaties apturēt
sistēmu un izvadīt pašreizējo atbildi, izmantojiet `stop` metodi:

```php
Flight::stop($httpStatusCode = null);
```

> **Piezīme:** `Flight::stop()` ir dažas dīvainas uzvedības, piemēram, tas izvadīs atbildi, bet turpinās izpildīt jūsu skriptu. Jūs varat izmantot `exit` vai `return` pēc izsaukuma `Flight::stop()`, lai novērstu turpmāku izpildi, bet parasti ieteicams izmantot `Flight::halt()`. 

## Notīrīšana atbildes datiem

Jūs varat notīrīt atbildes ķermeni un galvenes, izmantojot `clear()` metodi. Tas notīrīs
jebkuras galvenes, kas piešķirtas atbildei, notīrīs atbildes ķermeni un iestatīs statusa kodu uz `200`.

```php
Flight::response()->clear();
```

### Notīrīšana tikai atbildes ķermenim

Ja jūs vēlaties notīrīt tikai atbildes ķermeni, jūs varat izmantot `clearBody()` metodi:

```php
// Šis joprojām saglabās jebkuras galvenes, kas iestatītas uz response() objektu.
Flight::response()->clearBody();
```

## HTTP kešošana

Flight nodrošina iebūvētu atbalstu HTTP līmeņa kešošanai. Ja kešošanas nosacījums
ir izpildīts, Flight atgriezīs HTTP `304 Not Modified` atbildi. Nākamreiz, kad klients
pieprasa to pašu resursu, viņš tiks aicināts izmantot savu lokāli kešoto versiju.

### Maršruta līmeņa kešošana

Ja jūs vēlaties kešot visu atbildi, jūs varat izmantot `cache()` metodi un nodot laiku, kurā kešot.

```php
// Šis kešos atbildi 5 minūtes
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'This content will be cached.';
});

// Alternatīvi, jūs varat izmantot virkni, ko jūs nodotu
// uz strtotime() metodi
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'This content will be cached.';
});
```

### Last-Modified

Jūs varat izmantot `lastModified` metodi un nodot UNIX laika zīmogu, lai iestatītu datumu
un laiku, kad lapa tika pēdējo reizi modificēta. Klients turpinās izmantot savu kešu līdz
kamēr last modified vērtība ir mainījusies.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'This content will be cached.';
});
```

### ETag

`ETag` kešošana ir līdzīga `Last-Modified`, izņemot to, ka jūs varat norādīt jebkuru id, ko
vēlaties resursam:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'This content will be cached.';
});
```

Ņemiet vērā, ka izsaucot vai `lastModified` vai `etag` iestādīs un pārbaudīs
kešošanas vērtību. Ja kešošanas vērtība ir tā pati starp pieprasījumiem, Flight nekavējoties
nosūtīs `HTTP 304` atbildi un pārtrauks apstrādi.

## Lejupielāde faila (v3.12.0)

Ir palīgmetode, lai lejupielādētu failu. Jūs varat izmantot `download` metodi un nodot ceļu.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```