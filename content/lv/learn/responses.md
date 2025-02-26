# Atbildes

Flight palīdz ģenerēt daļu no atbildes virsrakstiem jūsu vietā, taču lielāko kontroli par to, ko jūs nosūtāt atpakaļ lietotājam, ierakstāt jūs. Dažreiz varat piekļūt `Response` objektam tieši, bet lielāko daļu laika jūs izmantosiet `Flight` instanci, lai nosūtītu atbildi.

## Pamata atbildes nosūtīšana

Flight izmanto ob_start() izejas buferēšanai. Tas nozīmē, ka jūs varat izmantot `echo` vai `print`, lai nosūtītu atbildi lietotājam, un Flight to noķers un nosūtīs atpakaļ lietotājam ar atbilstošajiem virsrakstiem.

```php

// Tas nosūtīs "Sveiki, pasaule!" lietotāja pārlūkprogrammai
Flight::route('/', function() {
	echo "Sveiki, pasaule!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Sveiki, pasaule!
```

Alternatīvi, jūs varat izsaukt `write()` metodi, lai pievienotu korpusu.

```php

// Tas nosūtīs "Sveiki, pasaule!" lietotāja pārlūkprogrammai
Flight::route('/', function() {
	// izsmeļoši, bet dažreiz noderīgi, kad jums tas ir vajadzīgs
	Flight::response()->write("Sveiki, pasaule!");

	// ja vēlaties iegūt korpusu, ko esat iestatījis šajā brīdī
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
		echo "Sveiki, pasaule!";
	} else {
		Flight::response()->status(403);
		echo "Aizliegts";
	}
});
```

Ja vēlaties iegūt pašreizējo statusa kodu, jūs varat izmantot `status` metodi bez jebkādiem argumentiem:

```php
Flight::response()->status(); // 200
```

## Atbildes ķermeņa iestatīšana

Jūs varat iestatīt atbildes ķermeni, izmantojot `write` metodi, tomēr, ja jūs izsaucat 'echo' vai 'print', 
tas tiks noķerts un nosūtīts kā atbildes ķermenis caur izejas buferēšanu.

```php
Flight::route('/', function() {
	Flight::response()->write("Sveiki, pasaule!");
});

// tāpat kā

Flight::route('/', function() {
	echo "Sveiki, pasaule!";
});
```

### Atbildes ķermeņa notīrīšana

Ja vēlaties notīrīt atbildes ķermeni, varat izmantot `clearBody` metodi:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Sveiki, pasaule!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### Atsaukšanas palaidšana uz atbildes ķermeni

Jūs varat palaist atsaukšanu uz atbildes ķermeni, izmantojot `addResponseBodyCallback` metodi:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Tas gzipos visas atbildes jebkurai maršrutu
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Jūs varat pievienot vairākus atsaukšanas, un tie tiks izpildīti secībā, kādā tie tika pievienoti. Tā kā tas var pieņemt jebkuru [callable](https://www.php.net/manual/en/language.types.callable.php), tas var pieņemt klases masīvu `[ $class, 'method' ]`, slēgtu `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, vai funkcijas nosaukumu `'minify'`, ja jums ir funkcija, lai samazinātu jūsu html kodu piemēram.

**Piezīme:** Maršruta atsaukšanas nedarbosies, ja izmantojat `flight.v2.output_buffering` konfigurācijas opciju.

### Specifiska maršruta atsaukšana

Ja jūs gribētu, lai tas attiektos tikai uz konkrētu maršrutu, jūs varētu pievienot atsaukšanu pašā maršrutā:

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

### Middleware opcija

Jūs varat arī izmantot starpprogrammas, lai lietotu atsaukšanu uz visiem maršrutiem caur starpprogrammu:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// Pielietot atsaukšanu šeit uz response() objektu.
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

Jūs varat iestatīt virsrakstu, piemēram, atbildes satura tipu, izmantojot `header` metodi:

```php

// Tas nosūtīs "Sveiki, pasaule!" lietotāja pārlūkprogrammai vienkāršā tekstā
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// vai
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Sveiki, pasaule!";
});
```

## JSON

Flight nodrošina atbalstu JSON un JSONP atbildēm. Lai nosūtītu JSON atbildi, Jūs 
pārsūtāt datus, lai tiktu JSON kodēti:

```php
Flight::json(['id' => 123]);
```

> **Piezīme:** Pēc noklusējuma Flight nosūtīs `Content-Type: application/json` virsrakstu ar atbildi. Tas arī izmantos konstantus `JSON_THROW_ON_ERROR` un `JSON_UNESCAPED_SLASHES`, kad kodēs JSON.

### JSON ar statusa kodu

Jūs varat arī pārsūtīt statusa kodu kā otro argumentu:

```php
Flight::json(['id' => 123], 201);
```

### JSON ar skaistu izdrukāšanu

Jūs varat arī pārsūtīt argumentu pēdējā pozīcijā, lai iespējotu skaistu drukāšanu:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Ja jūs maināt opcijas, kas pārsūtītas uz `Flight::json()`, un vēlaties vienkāršāku sintaksi, 
jūs varat vienkārši pārdefinēt JSON metodi:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// Un tagad to var izmantot šādā veidā
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON un izpildes apstādināšana (v3.10.0)

Ja vēlaties nosūtīt JSON atbildi un apstāt izpildi, jūs varat izmantot `jsonHalt` metodi.
Tas ir noderīgi gadījumos, kad jūs pārbaudāt kādu autorizācijas veidu un, ja
lietotājs nav autorizēts, jūs varat nekavējoties nosūtīt JSON atbildi, notīrīt esošā ķermeņa saturu
un apstāt izpildi.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Pārbaudiet, vai lietotājs ir autorizēts
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Neautorizēts'], 401);
	}

	// Turpināt ar pārējām maršruta daļām
});
```

Pirms v3.10.0 jums vajadzēja darīt kaut ko tādu:

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Pārbaudiet, vai lietotājs ir autorizēts
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Neautorizēts']));
	}

	// Turpināt ar pārējām maršruta daļām
});
```

### JSONP

JSONP pieprasījumiem jūs varat izvēles kārtībā pārsūtīt pieprasījuma parametra nosaukumu, ko izmantojat, lai definētu savu atsaukšanas funkciju:

```php
Flight::jsonp(['id' => 123], 'q');
```

Tādējādi, veicot GET pieprasījumu, izmantojot `?q=my_func`, jūs saņemsiet izeju:

```javascript
my_func({"id":123});
```

Ja jūs nenorādāt pieprasījuma parametra nosaukumu, tas noklusēts uz `jsonp`.

## Pāradresēt uz citu URL

Jūs varat pāradresēt pašreizējo pieprasījumu, izmantojot `redirect()` metodi un pārsūtot
jaunu URL:

```php
Flight::redirect('/new/location');
```

Pēc noklusējuma Flight nosūtīs HTTP 303 ("Redzēt citu") statusa kodu. Jūs varat izvēles kārtībā iestatīt
pielāgotu kodu:

```php
Flight::redirect('/new/location', 401);
```

## Apstāšanās

Jūs varat apstāties sistēmā jebkurā brīdī, izsaucot `halt` metodi:

```php
Flight::halt();
```

Jūs varat arī norādīt opciju `HTTP` statusa kodu un ziņojumu:

```php
Flight::halt(200, 'Drīz atgriezīsimies...');
```

Izsaucot `halt`, tiks atstumta jebkura atbildes saturs līdz šim brīdim. Ja vēlaties apstāties
sistēmā un izvadīt pašreizējo atbildi, izmantojiet `stop` metodi:

```php
Flight::stop();
```

## Atbildes datu notīrīšana

Jūs varat notīrīt atbildes ķermeni un virsrakstus, izmantojot `clear()` metodi. Tas notīrīs
jebkādus virsrakstus, kas piešķirti atbildei, notīrīs atbildes ķermeni un iestatīs statusa kodu uz `200`.

```php
Flight::response()->clear();
```

### Tikai atbildes ķermeņa notīrīšana

Ja jūs vēlaties tikai notīrīt atbildes ķermeni, varat izmantot `clearBody()` metodi:

```php
// Tas joprojām saglabās jebkurus virsrakstus, kas iestatīti uz response() objektu.
Flight::response()->clearBody();
```

## HTTP kešatmiņa

Flight nodrošina iebūvētu atbalstu HTTP līmeņa kešatmiņai. Ja kešatmiņas nosacījums 
tiek izpildīts, Flight atgriezīs HTTP `304 Not Modified` atbildi. Nākamreiz, kad 
klients pieprasīs to pašu resursu, viņiem tiks ieteikts izmantot savu lokāli
kešoto versiju.

### Maršruta līmeņa kešatmiņa

Ja vēlaties kešot visu savu atbildi, varat izmantot `cache()` metodi un pārsūtīt laiku kešatmiņai.

```php

// Tas kešos atbildi 5 minūtes
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'Šis saturs tiks kešots.';
});

// Alternatīvi, jūs varat izmantot virkni, ko pārsūtīsiet
// uz strtotime() metodi
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'Šis saturs tiks kešots.';
});
```

### Pēdējā modifikācija

Jūs varat izmantot `lastModified` metodi un pārsūtīt UNIX laika zīmogu, lai iestatītu datumu
un laiku, kad lapa tika pēdējoreiz modificēta. Klients turpinās izmantot savu kešu, līdz
pēdējā modifikētā vērtība tiek mainīta.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'Šis saturs tiks kešots.';
});
```

### ETag

`ETag` kešatmiņa ir līdzīga `Last-Modified`, izņemot to, ka jūs varat norādīt jebkuru ID, 
ko vēlaties resursam:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'Šis saturs tiks kešots.';
});
```

Ņemiet vērā, ka, izsaucot either `lastModified` vai `etag`, tiek iestatīta un pārbaudīta 
kešatmiņas vērtība. Ja kešatmiņas vērtība ir tāda pati starp pieprasījumiem, Flight 
nekavējoties nosūtīs `HTTP 304` atbildi un pārtrauks apstrādi.

## Faila lejupielāde (v3.12.0)

Ir palīgfunkcija, lai lejupielādētu failu. Jūs varat izmantot `download` metodi un pārsūtīt ceļu.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```