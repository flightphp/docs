# Atbildes

Flight palīdz ģenerēt daļu no atbildes virsrakstiem, bet jūs valdāt pārsvaru pār to, ko nosūtāt atpakaļ lietotājam. Dažreiz jūs varat tieši piekļūt `Response` objektam, bet lielāko daļu laika jūs izmantosit `Flight` instanci, lai nosūtītu atbildi.

## Pamata atbildes nosūtīšana

Flight izmanto ob_start(), lai buferētu izvadi. Tas nozīmē, ka jūs varat izmantot `echo` vai `print`, lai nosūtītu atbildi lietotājam, un Flight to sag捕arīs un nosūtīs atpakaļ lietotājam ar atbilstošajiem virsrakstiem.

```php

// Tas nosūtīs "Hello, World!" lietotāja pārlūkā
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

// Tas nosūtīs "Hello, World!" lietotāja pārlūkā
Flight::route('/', function() {
	// detalizēts, bet dažreiz izpilda darbu, kad tas jums nepieciešams
	Flight::response()->write("Hello, World!");

	// ja vēlaties iegūt ķermeni, ko esat iestatījis šajā punktā
	// jūs varat to izdarīt šādi
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
		echo "Aizliegts";
	}
});
```

Ja vēlaties iegūt pašreizējo statusa kodu, varat izmantot `status` metodi bez jebkādiem argumentiem:

```php
Flight::response()->status(); // 200
```

## Atbildes ķermeņa iestatīšana

Jūs varat iestatīt atbildes ķermeni, izmantojot `write` metodi, tomēr, ja jūs kaut ko echo vai print, 
tas tiks sag捕arīts un nosūtīts kā atbildes ķermenis caur izvades buferēšanu.

```php
Flight::route('/', function() {
	Flight::response()->write("Hello, World!");
});

// tas pats kā

Flight::route('/', function() {
	echo "Hello, World!";
});
```

### Atbildes ķermeņa notīrīšana

Ja vēlaties notīrīt atbildes ķermeni, jūs varat izmantot `clearBody` metodi:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### Atbildes ķermeņa atsauces izpildīšana

Jūs varat izpildīt atsauci uz atbildes ķermeni, izmantojot `addResponseBodyCallback` metodi:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Tas gzipos visas atbildes par jebkuru maršrutu
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Jūs varat pievienot vairākas atsauces, un tās tiks izpildītas secībā, kādā tās tika pievienotas. Tā kā šī var pieņemt jebkuru [izsaucamu](https://www.php.net/manual/en/language.types.callable.php), tā var pieņemt klases masīvu `[ $class, 'method' ]`, slēgšanu `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, vai funkcijas nosaukumu `'minify'`, ja jums būtu funkcija, lai samazinātu jūsu html kodu piemēram.

**Piezīme:** Maršruta atsauces nedarbosies, ja jūs izmantojat `flight.v2.output_buffering` konfigurācijas opciju.

### Specifiska maršruta atsauce

Ja vēlaties, lai tas attiektos tikai uz konkrētu maršrutu, jūs varētu pievienot atsauci pašā maršrutā:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Tas gzipos tikai atbildi šim maršrutam
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### Vidēja opcija

Jūs varat arī izmantot vidējo, lai pielāgotu atsauci visiem maršrutiem, izmantojot vidēji:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// Pielietojiet atsauci šeit uz response() objektu.
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// kaut kā samazināt ķermeni
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

## Atbildes virsraksta iestatīšana

Jūs varat iestatīt virsrakstu, piemēram, atbildes satura veidu, izmantojot `header` metodi:

```php

// Tas nosūtīs "Hello, World!" lietotāja pārlūkā kā vienkāršu tekstu
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// vai
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight nodrošina atbalstu JSON un JSONP atbilžu nosūtīšanai. Lai nosūtītu JSON atbildi, jūs
pārsniedzat datus, kas jākodo JSON formātā:

```php
Flight::json(['id' => 123]);
```

> **Piezīme:** Pēc noklusējuma Flight nosūtīs `Content-Type: application/json` virsrakstu kopā ar atbildi. Tas arī izmantos konstantus `JSON_THROW_ON_ERROR` un `JSON_UNESCAPED_SLASHES`, kad kodē JSON.

### JSON ar statusa kodu

Jūs varat arī pārsūtīt statusa kodu kā otro argumentu:

```php
Flight::json(['id' => 123], 201);
```

### JSON ar skaistu izskatu

Jūs varat arī pārsūtīt argumentu pēdējā pozīcijā, lai iespējotu skaistu izskatu:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Ja jūs maināt opcijas, ko nododat `Flight::json()`, un vēlaties vienkāršāku sintaksi, jūs varat 
vienkārši pārsaukt JSON metodi:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
});

// Un tagad to var izmantot šādi
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON un izpildes apstādināšana (v3.10.0)

Ja vēlaties nosūtīt JSON atbildi un apstādināt izpildi, jūs varat izmantot `jsonHalt` metodi.
Tas ir noderīgi gadījumiem, kad jūs pārbaudāt varbūt kādu autorizāciju, un, ja
lietotājs nav autorizēts, jūs varat nekavējoties nosūtīt JSON atbildi, notīrīt esošo ķermeņa
saturs un apstādināt izpildi.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Pārbaudiet, vai lietotājs ir autorizēts
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Nav autorizēts'], 401);
	}

	// Turpiniet ar pārējo maršrutu
});
```

Pirms v3.10.0 jūs būtu jāveic kaut kas tāds:

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Pārbaudiet, vai lietotājs ir autorizēts
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Nav autorizēts']));
	}

	// Turpiniet ar pārējo maršrutu
});
```

### JSONP

JSONP pieprasījumiem jūs varat izvēles veidā pārsūtīt vaicājuma parametra nosaukumu,
ko izmantojat, lai noteiktu jūsu atsauces funkciju:

```php
Flight::jsonp(['id' => 123], 'q');
```

Tātad, veicot GET pieprasījumu, izmantojot `?q=my_func`, jums vajadzētu saņemt izeju:

```javascript
my_func({"id":123});
```

Ja jūs neizpildāt vaicājuma parametra nosaukumu, tas noklusējuma būs `jsonp`.

## Tomburgizzard uz citu URL

Jūs varat pārsūtīt pašreizējo pieprasījumu, izmantojot `redirect()` metodi un pārsūtot
jaunu URL:

```php
Flight::redirect('/new/location');
```

Pēc noklusējuma Flight nosūta HTTP 303 ("Redzēt citu") statusa kodu. Jūs varat izvēles veidā iestatīt
pielāgotu kodu:

```php
Flight::redirect('/new/location', 401);
```

## Apstāšanās

Jūs varat apstāties ietvarā jebkurā brīdī, izsaucot `halt` metodi:

```php
Flight::halt();
```

Jūs arī varat norādīt opcionalu `HTTP` statusa kodu un ziņojumu:

```php
Flight::halt(200, 'Atgriezīsimies drīz...');
```

Izsaucot `halt`, tiks izmests jebkurš atbildes saturs līdz tam punktam. Ja vēlaties apstāties
ietvarā un izvadīt pašreizējo atbildi, izmantojiet `stop` metodi:

```php
Flight::stop();
```

## Atbildes datu notīrīšana

Jūs varat notīrīt atbildes ķermeni un virsrakstus, izmantojot `clear()` metodi. Tas notīrīs
jebkurus virsrakstus, kas piešķirti atbildei, notīrīs atbildes ķermeni un iestatīs statusa kodu uz `200`.

```php
Flight::response()->clear();
```

### Tikai atbildes ķermeņa notīrīšana

Ja jūs vēlaties tikai notīrīt atbildes ķermeni, jūs varat izmantot `clearBody()` metodi:

```php
// Tas joprojām saglabās jebkurus virsrakstus, kas iestatīti uz response() objektu.
Flight::response()->clearBody();
```

## HTTP kešatmiņa

Flight nodrošina iebūvētu atbalstu HTTP līmeņa kešatmiņai. Ja kešatmiņas nosacījums
ir izpildīts, Flight nosūtīs HTTP `304 Not Modified` atbildi. Nākamreiz, kad
klients pieprasīs to pašu resursu, viņiem tiks lūgts izmantot viņu lokāli
kešoto versiju.

### Maršruta līmeņa kešatmiņa

Ja vēlaties kešot visu atbildi, jūs varat izmantot `cache()` metodi un pārsūtīt laiku kešot.

```php

// Tas kešos atbildi uz 5 minūtēm
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'Šis saturs tiks kešots.';
});

// Alternatīvi jūs varat izmantot virkni, ko jūs nodosiet
// uz strtotime() metodi
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'Šis saturs tiks kešots.';
});
```

### Pēdējais modificēts

Jūs varat izmantot `lastModified` metodi un pārsūtīt UNIX laika zīmogu, lai iestatītu datumu
un laiku, kad lapa tika pēdējoreiz modificēta. Klients turpinās izmantot savu kešu līdz
pēdējā modificētā vērtība tiks mainīta.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'Šis saturs tiks kešots.';
});
```

### ETag

`ETag` kešatmiņa ir līdzīga `Last-Modified`, izņemot to, ka jūs varat norādīt jebkuru id,
ko vēlaties resursam:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'Šis saturs tiks kešots.';
});
```

Paturiet prātā, ka izsaukot gan `lastModified`, gan `etag`, abi iestatīs un pārbaudīs
kešatmiņas vērtību. Ja kešatmiņas vērtība ir vienāda starp pieprasījumiem, Flight uzreiz
nosūtīs `HTTP 304` atbildi un apstādinās apstrādi.

## Faila lejupielāde (v3.12.0)

Ir palīgmetode, lai lejupielādētu failu. Jūs varat izmantot `download` metodi un pārsūtīt ceļu.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```