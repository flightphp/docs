# Atbildes

Flight palīdz ģenerēt daļu no atbildes galvenēm jums, bet jūs turat lielāko kontroli pār to, ko nosūtāt atpakaļ lietotājam. Dažreiz jūs varat piekļūt `Response` objektam tieši, bet lielāko laiku jūs izmantosiet `Flight` instanci, lai nosūtītu atbildi.

## Nosūtīt Pamata Atbildi

Flight izmanto `ob_start()` lai buferētu izvadi. Tas nozīmē, ka jūs varat izmantot `echo` vai `print`, lai nosūtītu atbildi lietotājam un Flight to uztverēs un atsūtīs atpakaļ lietotājam ar atbilstošajām galvenēm.

```php

// Tas nosūtīs "Sveika, Pasaule!" uz lietotāja pārlūku
Flight::route('/', function() {
	echo "Sveika, Pasaule!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Sveika, Pasaule!
```

Kā alternatīvu, jūs varat izsaukt `write()` metodi, lai pievienotu arī ķermenim.

```php

// Tas nosūtīs "Sveika, Pasaule!" uz lietotāja pārlūku
Flight::route('/', function() {
	// izsmeļ, bet darbojas dažreiz, kad tas ir nepieciešams
	Flight::response()->write("Sveika, Pasaule!");

	// ja vēlaties iegūt ķermeni, ko esat uzstādījis šajā punktā
	// varat izdarīt to šādi
	$body = Flight::response()->getBody();
});
```

## Statusa Kodi

Jūs varat iestatīt atbildes statusa kodu, izmantojot `status` metodi:

```php
Flight::route('/@id', function($id) {
	if($id == 123) {
		Flight::response()->status(200);
		echo "Sveika, Pasaule!";
	} else {
		Flight::response()->status(403);
		echo "Liegs piekļuvi";
	}
});
```

Ja vēlaties iegūt pašreizējo statusa kodu, varat izmantot `status` metodi bez argumentiem:

```php
Flight::response()->status(); // 200
```

## Izpildīt Atsauksmi uz Atbildes Ķermeni

Jūs varat izpildīt atsauksmi uz atbildes ķermeni, izmantojot `addResponseBodyCallback` metodi:

```php
Flight::route('/lietotāji', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Tas saspiest visus atbilžu ceļus jebkuram maršrutam
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Jūs varat pievienot vairākas atsauksmes, un tās tiks izpildītas secībā, kādā tās tika pievienotas. Tā kā tas var pieņemt jebkuru [izsaucamo](https://www.php.net/manual/en/language.types.callable.php), tas var pieņemt klases masīvu `[ $class, 'method' ]`, slēgumu `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, vai funkcijas nosaukumu `'minify'`, ja jums būtu funkcija, lai samazinātu savu html kodu, piemēram.

**Piezīme:** Maršrutu atsauksmes nedarbosies, ja izmantojat `flight.v2.output_buffering` konfigurācijas opciju.

### Konkrēta Maršruta Atsauksme

Ja vēlaties, lai tas attiektos tikai uz konkrētu maršrutu, jūs varētu pievienot atsauksmi maršrutā pati:

```php
Flight::route('/lietotāji', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Tiks saspiesta tikai atbilde šim maršrutam
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### Starpaprīkojuma Opcija

Jūs arī varat izmantot vidējumu, lai piemērotu atsauksmi visiem maršrutiem, izmantojot vidēji:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		Flight::response()->addResponseBodyCallback(function($body) {
			// Tā ir 
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// sarīkot ķermeni
		return $body;
	}
}

// index.php
Flight::group('/lietotāji', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

## Iestatīt Atbildes Galveni

Jūs varat iestatīt galveni, piemēram, atbildes veidu, izmantojot `header` metodi:

```php

// Tas nosūtīs "Sveika, Pasaule!" uz lietotāja pārlūku kā vienkāršu tekstu
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	echo "Sveika, Pasaule!";
});
```

## JSON

Flight nodrošina atbalstu JSON un JSONP atbilžu nosūtīšanai. Lai nosūtītu JSON atbildi, jums jānosūta dati, kas jāpārvērš JSON formātā:

```php
Flight::json(['id' => 123]);
```

### JSON ar Statusa Kodu

Jūs arī varat nosūtīt statusa kodu kā otro argumentu:

```php
Flight::json(['id' => 123], 201);
```

### JSON ar Skaistu Izdruku

Jūs arī varat nosūtīt argumentu uz pēdējo pozīciju, lai iespējotu skaistu izdrukāšanu:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Ja maināt opcijas, kas nosūtītas `Flight::json()` un vēlaties vienkāršāku sintaksi, vienkārši atkarto JSON metodi:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// Tagad to var izmantot šādi
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON un Apturēt Izpildi

Ja vēlaties nosūtīt JSON atbildi un apturēt izpildi, varat izmantot `jsonHalt` metodi.
Tas ir noderīgi gadījumos, kad pārbaudāt iespējamo autorizāciju un ja
lietotājam nav atļaujas, jūs varat uzreiz nosūtīt JSON atbildi un notīrīt esošo ķermeni
saturs un apturēt izpildi.

```php
Flight::route('/lietotāji', function() {
	$authorized = someAuthorizationCheck();
	// Pārbaudiet, vai lietotājam ir atļauts
	if($authorized === false) {
		Flight::jsonHalt(['kļūda' => 'Neatļauts'], 401);
	}

	// Turpiniet ar maršruta pārējo daļu
});
```

### JSONP

JSONP pieprasījumiem jūs varat izvēlēties padot vaicājuma parametra nosaukumu, ko
izmanto, lai definētu jūsu atsauces funkciju:

```php
Flight::jsonp(['id' => 123], 'q');
```

Tādējādi, veicot GET pieprasījumu, izmantojot `?q=my_func`, jums vajadzētu saņemt izvadi:

```javascript
my_func({"id":123});
```

Ja nepievienojat vaicājuma parametra nosaukumu, tas pēc noklusējuma būs `jsonp`.

## Pāradresēt uz citu URL

Jūs varat pāradresēt pašreizējo pieprasījumu, izmantojot `redirect()` metodi un padodot
jaunu URL:

```php
Flight::redirect('/jauns/atrašanās/vieta');
```

Pēc noklusējuma Flight nosūta HTTP 303 ("Redzēt citu") statusa kodu. Jūs varat pēc izvēles iestatīt
pielāgotu kodu:

```php
Flight::redirect('/jauns/atrašanās/vieta', 401);
```

## Apturēšana

Jūs varat apturēt ietvaru jebkurā punktā, izsaucot `halt` metodi:

```php
Flight::halt();
```

Jūs varat arī norādīt neobligātu `HTTP` statusa kodu un ziņojumu:

```php
Flight::halt(200, 'Atgriezies pēc brīža...');
```

Izsaukšana `halt` nodzēš jebkuru atbildes saturu līdz šim brīdim. Ja vēlaties apturēt
ietvaru un izvadīt pašreizējo atbildi, izmantojiet `stop` metodi:

```php
Flight::stop();
```

## HTTP Kešošana

Flight nodrošina iebūvētu atbalstu HTTP līmeņa kešošanai. Ja tiek izpildīta kešošanas nosacījumi
būs atbilstoši, Flight atgriezīs HTTP `304 Nav Modificēts` atbildi. Nākamreiz, kad
klients pieprasa to pašu resursu, viņiem tiks ieteikts izmantot lokāli
kešēto versiju.

### Maršruta Līmeņa Kešošana

Ja vēlaties kešot visu savu atbildi, jūs varat izmantot `cache()` metodi un nosūtīt laiku, lai kešotu.

```php

// Tas kešos atbildi uz 5 minūtēm
Flight::route('/jaunumi', function () {
  Flight::response()->cache(time() + 300);
  echo 'Šis saturs tiks kešots.';
});

// Kā alternatīvu, jūs varat izmantot virkni, kuru nosūtat
// uz strtotime() metodēm
Flight::route('/jaunumi', function () {
  Flight::response()->cache('+5 minūtes');
  echo 'Šis saturs tiks kešots.';
});
```

### Pēdējais Modificēts

Jūs varat izmantot `lastModified` metodi un nosūtīt UNIX laika zīmi, lai iestatītu datumu
un laiku, kad lapa pēdējo reizi tika modificēta. Klients turpinās izmantot savu kešu līdz
pēdējais modificēšanas laiks tiek mainīts.

```php
Flight::route('/jaunumi', function () {
  Flight::lastModified(1234567890);
  echo 'Šis saturs tiks kešots.';
});
```

### ETag

`ETag` kešošana ir līdzīga `Pēdējais Modificēts`, izņemot to, jūs varat norādīt jebkuru
gribēto identifikatoru resursam:

```php
Flight::route('/jaunumi', function () {
  Flight::etag('man-īpašais-id');
  echo 'Šis saturs tiks kešots.';
});
```

Jāatceras, ka `lastModified` vai `etag` izsaukšana gan iestata, gan pārbauda
keša vērtību. Ja keša vērtība starp pieprasījumiem ir vienāda, Flight uzreiz
nosūtīs `HTTP 304` atbildi un pārtrauks apstrādi.