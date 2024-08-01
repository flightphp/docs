# Atbildes

`Flight` palīdz jums ģenerēt daļu no atbildes galvenēm, bet lielāko daļu kontroles par to, ko nosūtat atpakaļ lietotājam, jūs turat. Dažreiz varat piekļūt ` Atbilde` objektam tieši, bet lielāko laiku izmantosiet `Flight` piemēru, lai nosūtītu atbildi.

## Nosūtīt pamata atbildi

`Flight` izmanto ob_start(), lai buferētu izvadi. Tas nozīmē, ka varat izmantot `echo` vai `print`, lai nosūtītu atbildi lietotājam, un `Flight` to uztvers, buferēs un nosūtīs atpakaļ lietotājam ar atbilstošajām galvenēm.

```php

// Tas nosūtīs "Sveika, pasaule!" uz lietotāja pārlūku
Flight::route('/', function() {
	echo "Sveika, pasaule!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Sveika, pasaule!
```

Kā alternatīvu varat izsaukt `write()` metodi, lai pievienotu arī ķermenim.

```php

// Tas nosūtīs "Sveika, pasaule!" uz lietotāja pārlūku
Flight::route('/', function() {
	// Ilgi, bet darbojas dažreiz, kad jums tas ir nepieciešams
	Flight::response()->write("Sveika, pasaule!");

	// ja vēlaties iegūt ķermeni, ko esat iestatījuši šajā punktā
	// varat to izdarīt šādi
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

## Iestatīt atbildes ķermeni

Jūs varat iestatīt atbildes ķermeni, izmantojot `write` metodi; tomēr, ja jūs izmantojat `echo` vai `print` jebko, 
tiks iegūts un nosūtīts atpakaļ kā atbildes ķermens, izmantojot izvades buferēšanu.

```php
Flight::route('/', function() {
	Flight::response()->write("Sveika, pasaule!");
});

// tas pats kā

Flight::route('/', function() {
	echo "Sveika, pasaule!";
});
```

### Notīrīt atbildes ķermeni

Ja vēlaties notīrīt atbildes ķermeni, jūs varat izmantot `clearBody` metodi:

```php
Flight::route('/', function() {
	if($kādaNosacījums) {
		Flight::response()->write("Sveika, pasaule!");
	} else {
		Flight::response()->clearBody();
	}
});
```