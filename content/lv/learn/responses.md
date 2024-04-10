# Atbildes

Flight palīdz jums ģenerēt daļu no atbildes galvenēm, bet jums lielākoties ir kontrole pār to, ko nosūtāt lietotājam atpakaļ. Dažreiz jūs varat tieši piekļūt `Response` objektam, bet lielāko daļu laika jūs izmantosiet `Flight` gadījumu, lai nosūtītu atbildi.

## Nosūtīt pamata atbildi

Flight izmanto ob_start(), lai buferētu izvadi. Tas nozīmē, ka jūs varat izmantot `echo` vai `print`, lai nosūtītu atbildi lietotājam, un Flight to gūdīs un nosūtīs atpakaļ lietotājam ar atbilstošajiem galvenēm.

```php

// Tas nosūtīs "Sveika, pasaule!" lietotāja pārlūkam
Flight::route('/', function() {
	echo "Sveika, pasaule!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Sveika, pasaule!
```

Kā alternatīvu, jūs varat izsaukt `write()` metodi, lai pievienotu arī saturu.

```php

// Tas nosūtīs "Sveika, pasaule!" lietotāja pārlūkam
Flight::route('/', function() {
	// detalizēts, bet darbojas dažreiz, kad jums tas ir nepieciešams
	Flight::response()->write("Sveika, pasaule!");

	// ja vēlaties iegūt satura, ko esat iestatījis šajā punktā
	// to varat izdarīt šādi
	$body = Flight::response()->getBody();
});
```

## Statusa kodi

Jūs varat iestatīt atbildes statusa kodu, izmantojot `status` metodi:

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

Ja vēlaties iegūt pašreizējo statusa kodu, jūs varat izmantot `status` metodi bez argumentiem:

```php
Flight::response()->status(); // 200
```

## Iestatot atbildes galveni

Jūs varat iestatīt galveni, piemēram, atbildes satura tipu, izmantojot `header` metodi:

```php

// Tas nosūtīs "Sveika, pasaule!" lietotāja pārlūkam vienkāršā teksta formātā
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	echo "Sveika, pasaule!";
});
```



## JSON

Flight piedāvā atbalstu JSON un JSONP atbilžu nosūtīšanai. Lai nosūtītu JSON atbildi
jūs padodat datus, kas jāpārvērš par JSON:

```php
Flight::json(['id' => 123]);
```

### JSONP

JSONP pieprasījumiem, jūs varat pēc izvēles padot vaicājuma parametra nosaukumu
kurš tiek izmantots, lai definētu jūsu atsauces funkciju:

```php
Flight::jsonp(['id' => 123], 'q');
```

Tātad, veicot GET pieprasījumu, izmantojot `?q=my_func`, jums vajadzētu saņemt izvadi:

```javascript
my_func({"id":123});
```

Ja jūs nepadodat vaicājuma parametra nosaukumu, tas pēc noklusējuma būs `jsonp`.

## Pāradresēt uz citu URL

Jūs varat pāradresēt pašreizējo pieprasījumu, izmantojot `redirect()` metodi un padodot
jaunu URL:

```php
Flight::redirect('/jauns/atrašanās_vieta');
```

Pēc noklusējuma Flight nosūta HTTP 303 ("Redzēt citu") statusa kodu. Jūs varat pēc izvēles iestatīt
pielāgotu kodu:

```php
Flight::redirect('/jauns/atrašanās_vieta', 401);
```

## Apturēšana

Jūs varat apturēt pamata struktūru jebkurā brīdī, izsaucot `halt` metodi:

```php
Flight::halt();
```

Jūs varat arī norādīt neobligātu `HTTP` statusa kodu un ziņojumu:

```php
Flight::halt(200, 'Es atgriezšos drīz...');
```

Izsaukšana `halt` iznīcinās jebkuru atbildes saturu līdz tam punktam. Ja vēlaties apturēt
pamata struktūru un izvadīt pašreizējo atbildi, izmantojiet `stop` metodi:

```php
Flight::stop();
```

## HTTP kešošana

Flight iebūvēti atbalsta HTTP līmeņa kešošanai. Ja tiek izpildīta kešošanas nosacījums
Flight atgriezīs HTTP `304 Nav modificēts` atbildi. Nākamajā reizē, kad
klients pieprasa to pašu resursu, viņi tiks aicināti izmantot vietējo
saglabāto versiju.

### Ceļa līmeņa kešošana

Ja vēlaties kešot visu atbildi, jūs varat izmantot `cache()` metodi un padot laiku, ko saglabāt kešā.

```php

// Tas kešos atbildi uz 5 minūtēm
Flight::route('/jaunumi', function () {
  Flight::response()->cache(time() + 300);
  echo 'Šis saturs tiks kešots.';
});

// Kā alternatīvu, jūs varat izmantot tekstu, ko padodat
// funkcijai strtotime()
Flight::route('/jaunumi', function () {
  Flight::response()->cache('+5 minutes');
  echo 'Šis saturs tiks kešots.';
});
```

### Pēdējais mainījums

Jūs varat izmantot `lastModified` metodi un padot UNIX laika zīmi, lai iestatītu datumu
un laiku, kad lapa tika pēdējo reizi modificēta. Klients turpinās izmantot savu kešu līdz
pēdējais modificēšanas laika vērtība tiek mainīta.

```php
Flight::route('/jaunumi', function () {
  Flight::lastModified(1234567890);
  echo 'Šis saturs tiks kešots.';
});
```

### ETag

`ETag` kešošana ir līdzīga `Pēdējais modificējums`, izņemot to, ka jūs varat norādīt jebkuru id vienību
ko vēlaties resursam:

```php
Flight::route('/jaunumi', function () {
  Flight::etag('mans-unikālais-id');
  echo 'Šis saturs tiks kešots.';
});
```

Ņemiet vērā, ka izsaucot gan `lastModified`, gan `etag` gan iestatīs, gan pārbaudīs kešu
vērtību. Ja kešu vērtība ir vienāda starp pieprasījumiem, Flight nekavējoties
nosūtīs `HTTP 304` atbildi un apturēs apstrādi.