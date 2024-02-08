# Atbildes

Flight palīdz ģenerēt daļu no atbilžu galvenēm, bet lielāko kontroli pār to, ko nosūtat atpakaļ lietotājam, jūs turat. Dažreiz varat piekļūt objektam `Response` tieši, bet lielāko laiku jūs izmantosiet `Flight` instanci, lai nosūtītu atbildi.

## Nosūtīt pamata atbildi

Flight izmanto `ob_start()`, lai buferētu izvadi. Tas nozīmē, ka jūs varat izmantot `echo` vai `print`, lai nosūtītu atbildi lietotājam, un Flight to uztverēs un atsūtīs atpakaļ lietotājam ar atbilstošajām galvenēm.

```php

// Tas nosūtīs "Sveika, pasaule!" uz lietotāja pārlūkprogrammu
Flight::route('/', function() {
	echo "Sveika, pasaule!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Sveika, pasaule!
```

Kā alternatīvu varat izsaukt metodi `write()`, lai pievienotu arī ķermenim.

```php

// Tas nosūtīs "Sveika, pasaule!" uz lietotāja pārlūkprogrammu
Flight::route('/', function() {
	// izsmeļošs, bet darbojās pa laikam, kad tas ir nepieciešams
	Flight::response()->write("Sveika, pasaule!");

	// ja vēlaties saņemt ķermeni, ko esat iestatījuši šajā punktā
	// to varat izdarīt šādi
	$body = Flight::response()->getBody();
});
```

## Statusa kodi

Atbildes statusa kodu varat iestatīt, izmantojot metodi `status`:

```php
Flight::route('/@id', function($id) {
	if($id == 123) {
		Flight::response()->status(200);
		echo "Sveika, pasaule!";
	} else {
		Flight::response()->status(403);
		echo "Aizliegts";
	}
});
```

Ja vēlaties iegūt pašreizējo statusa kodu, varat izmantot metodi `status` bez argumentiem:

```php
Flight::response()->status(); // 200
```

## Iestatot atbildes galveni

Jūs varat iestatīt galveni, piemēram, atbildes satura veidu, izmantojot metodi `header`:

```php

// Tas nosūtīs "Sveika, pasaule!" uz lietotāja pārlūkprogrammu vienkāršā tekstā
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	echo "Sveika, pasaule!";
});
```



## JSON

Flight nodrošina atbalstu JSON un JSONP atbilžu nosūtīšanai. Lai nosūtītu JSON atbildi, jums jāpadod dati, kas būtu jāpārkoda JSON formātā:

```php
Flight::json(['id' => 123]);
```

### JSONP

JSONP pieprasījumiem jūs varat neobligāti nosūtīt vaicājuma parametra nosaukumu, ko lietojat, lai definētu savu atsauces funkciju:

```php
Flight::jsonp(['id' => 123], 'q');
```

Tātad, veicot GET pieprasījumu, izmantojot `?q=my_func`, jums vajadzētu saņemt izvadi:

```javascript
my_func({"id":123});
```

Ja neesat nodod pārbaudes parametra nosaukumu, tas pēc noklusējuma būs `jsonp`.

## Novirzīt uz citu URL

Pašreizējo pieprasījumu varat novirzīt, izmantojot `redirect()` metodi un padodot
jaunu URL:

```php
Flight::redirect('/jauns/vietne');
```

Pēc noklusējuma Flight nosūta HTTP 303 ("Skatīt citu") statusa kodu. Pēc izvēles varat iestatīt pielāgotu kodu:

```php
Flight::redirect('/jauns/vietne', 401);
```

## Apturēt

Jūs varat apturēt pamatstruktūru jebkurā brīdī, izsaucot metodi `halt`:

```php
Flight::halt();
```

Jūs arī varat norādīt neobligātu `HTTP` statusa kodu un ziņojumu:

```php
Flight::halt(200, 'Atgriezies drīz...');
```

Izsaukums `halt` noraidīs jebkuru atbildes saturu līdz tam brīdim. Ja vēlaties apturēt
struktūru un izvadīt pašreizējo atbildi, izmantojiet metodi `stop`:

```php
Flight::stop();
```

## HTTP kešošana

Flight nodrošina iebūvētu atbalstu HTTP līmeņa kešošanai. Ja kešošanas nosacījums
ir izpildīts, Flight atgriezīs HTTP `304 Nav modificēts` atbildi. Nākamajā reizē
klients pieprasa to pašu resursu, viņiem tiks lūgts izmantot lokāli
kešoto versiju.

### Maršruta līmeņa kešošana

Ja vēlaties kešot visu atbildi, varat izmantot `cache()` metodi un nodot laiku kešošanai.

```php

// Tas kešos atbildi uz 5 minūtēm
Flight::route('/jaunumi', function () {
  Flight::cache(time() + 300);
  echo 'Šis saturs tiks kešots.';
});

// Alternatīvi, varat izmantot virkni, ko nodotu
// funkcijai strtotime()
Flight::route('/jaunumi', function () {
  Flight::cache('+5 minutes');
  echo 'Šis saturs tiks kešots.';
});
```

### Pēdējais mainījums

Varat izmantot metodi `lastModified` un nodot UNIX laika zīmogu, lai iestatītu datumu
un laiku, kad lapa pēdējoreiz tika modificēta. Klients turpinās izmantot savu kešatmiņu,
līdz pēdējais modificēšanas laiks tiek mainīts.

```php
Flight::route('/jaunumi', function () {
  Flight::lastModified(1234567890);
  echo 'Šis saturs tiks kešots.';
});
```

### ETag

`ETag` kešošana ir līdzīga `Pēdējam mainījumam`, izņemot to, ka varat norādīt jebkuru identifikatoru
resursam, ko vēlaties:

```php
Flight::route('/jaunumi', function () {
  Flight::etag('mans-unikālais-id');
  echo 'Šis saturs tiks kešots.';
});
```

Ņemiet vērā, ka izsaukums `lastModified` vai `etag` gan iestatīs, gan pārbaudīs
keša vērtību. Ja keša vērtība ir vienāda starp pieprasījumiem, Flight nekavējoties
nosūtīs `HTTP 304` atbildi un pārtrauks apstrādi.