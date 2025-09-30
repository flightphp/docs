# Atbildes

## Pārskats

Flight palīdz ģenerēt daļu no atbildes galvenes jums, bet jūs kontrolējat lielāko daļu no tā, ko nosūtāt atpakaļ lietotājam. Lielāko daļu laika jūs tieši piekļūsiet `response()` objektam, bet Flight piedāvā dažas palīgmēģenes, lai iestatītu dažas no atbildes galvenēm jums.

## Izpratne

Pēc tam, kad lietotājs nosūta savu [pieprasījumu](/learn/requests) jūsu lietojumprogrammai, jums jāģenerē pareiza atbilde viņiem. Viņi ir nosūtījuši jums informāciju, piemēram, valodu, kuru viņi dod priekšroku, vai viņi var apstrādāt noteiktus kompresijas veidus, viņu lietotāja aģentu utt., un pēc visu apstrādes ir laiks nosūtīt viņiem atpakaļ pareizu atbildi. Tas var būt galvenes iestatīšana, HTML vai JSON ķermeņa izvade viņiem vai novirzīšana uz lapu.

## Pamata lietošana

### Atbildes ķermeņa nosūtīšana

Flight izmanto `ob_start()` buferēšanai izvadi. Tas nozīmē, ka jūs varat izmantot `echo` vai `print`, lai nosūtītu atbildi lietotājam, un Flight to uztvers un nosūtīs atpakaļ lietotājam ar atbilstošajām galvenēm.

```php
// Tas nosūtīs "Sveiki, Pasaule!" uz lietotāja pārlūku
Flight::route('/', function() {
	echo "Sveiki, Pasaule!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Sveiki, Pasaule!
```

Kā alternatīvu, jūs varat izsaukt `write()` metodi, lai pievienotu ķermenim.

```php
// Tas nosūtīs "Sveiki, Pasaule!" uz lietotāja pārlūku
Flight::route('/', function() {
	// verbose, bet dažreiz dara darbu, kad tas ir vajadzīgs
	Flight::response()->write("Sveiki, Pasaule!");

	// ja vēlaties iegūt ķermeni, kuru esat iestatījis šajā brīdī
	// jūs varat to izdarīt šādi
	$body = Flight::response()->getBody();
});
```

### JSON

Flight nodrošina atbalstu JSON un JSONP atbilžu nosūtīšanai. Lai nosūtītu JSON atbildi, jūs nododiet datus, kas jākodē JSON:

```php
Flight::route('/@companyId/users', function(int $companyId) {
	// kaut kā izvilkt savus lietotājus no datubāzes, piemēram
	$users = Flight::db()->fetchAll("SELECT id, first_name, last_name FROM users WHERE company_id = ?", [ $companyId ]);

	Flight::json($users);
});
// [{"id":1,"first_name":"Bob","last_name":"Jones"}, /* vairāk lietotāju */ ]
```

> **Piezīme:** Pēc noklusējuma Flight nosūtīs `Content-Type: application/json` galveni ar atbildi. Tas arī izmantos karodziņus `JSON_THROW_ON_ERROR` un `JSON_UNESCAPED_SLASHES`, kodējot JSON.

#### JSON ar statusa kodu

Jūs varat arī nodot statusa kodu kā otro argumentu:

```php
Flight::json(['id' => 123], 201);
```

#### JSON ar skaistu izdruku

Jūs varat arī nodot argumentu pēdējā pozīcijā, lai iespējotu skaistu izdruku:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

#### Mainot JSON argumentu secību

`Flight::json()` ir ļoti novecojis metode, bet Flight mērķis ir saglabāt atpakaļsaderību projektiem. Patiesībā tas ir ļoti vienkārši, ja vēlaties atkārtoti izveidot argumentu secību, lai izmantotu vienkāršāku sintaksi, jūs varat vienkārši pārkartēt JSON metodi [kā jebkuru citu Flight metodi](/learn/extending):

```php
Flight::map('json', function($data, $code = 200, $options = 0) {

	// tagad jums nav jāizmanto `true, 'utf-8'` , izmantojot json() metodi!
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// Un tagad to var izmantot šādi
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

#### JSON un izpildes apturēšana

_v3.10.0_

Ja vēlaties nosūtīt JSON atbildi un apturēt izpildi, jūs varat izmantot `jsonHalt()` metodi.
Tas ir noderīgi gadījumos, kad jūs pārbaudāt varbūt kādu autorizācijas veidu un, ja lietotājs nav autorizēts, jūs varat nekavējoties nosūtīt JSON atbildi, notīrīt esošo ķermeņa saturu un apturēt izpildi.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Pārbaudiet, vai lietotājs ir autorizēts
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
		// nav izejas; vajadzīgs šeit.
	}

	// Turpiniet ar pārējo maršruta daļu
});
```

Pirms v3.10.0 jums būtu jādarītu kaut kas šāds:

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Pārbaudiet, vai lietotājs ir autorizēts
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// Turpiniet ar pārējo maršruta daļu
});
```

### Ķermeņa atbildes notīrīšana

Ja vēlaties notīrīt atbildes ķermeni, jūs varat izmantot `clearBody` metodi:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Sveiki, Pasaule!");
	} else {
		Flight::response()->clearBody();
	}
});
```

Iepriekš minētais lietošanas gadījums, iespējams, nav izplatīts, tomēr tas varētu būt izplatītāks, ja tas tiktu izmantots [starpprogrammatūrā](/learn/middleware).

### Izpildes atbilde uz atbildes ķermeni

Jūs varat izpildīt atbildi uz atbildes ķermeni, izmantojot `addResponseBodyCallback` metodi:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Tas sasniegs visas atbildes ar gzip jebkuram maršrutam
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Jūs varat pievienot vairākas atbildes, un tās tiks izpildītas secībā, kādā tās tika pievienotas. Tā kā tas var pieņemt jebkuru [izsaucamu](https://www.php.net/manual/en/language.types.callable.php), tas var pieņemt klases masīvu `[ $class, 'method' ]`, aizvēršanu `$strReplace = function($body) { str_replace('hi', 'there', $body); };` vai funkcijas nosaukumu `'minify'`, ja jums ir funkcija, lai samazinātu jūsu html kodu, piemēram.

**Piezīme:** Maršruta atbildes nedarbosies, ja izmantojat `flight.v2.output_buffering` konfigurācijas opciju.

#### Īpašs maršruta atbilde

Ja vēlaties, lai tas attiektos tikai uz noteiktu maršrutu, jūs varat pievienot atbildi pašā maršrutā:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Tas sasniegs ar gzip tikai šo maršrutu atbildi
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

#### Starpprogrammatūras opcija

Jūs varat arī izmantot [starpprogrammatūru](/learn/middleware), lai piemērotu atbildi visiem maršrutiem, izmantojot starpprogrammatūru:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// Šeit piemērojiet atbildi uz response() objektu.
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// kaut kā samaziniet ķermeni
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

### Statusa kodi

Jūs varat iestatīt atbildes statusa kodu, izmantojot `status` metodi:

```php
Flight::route('/@id', function($id) {
	if($id == 123) {
		Flight::response()->status(200);
		echo "Sveiki, Pasaule!";
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

### Atbildes galvenes iestatīšana

Jūs varat iestatīt galveni, piemēram, atbildes satura veidu, izmantojot `header` metodi:

```php
// Tas nosūtīs "Sveiki, Pasaule!" uz lietotāja pārlūku kā vienkāršu tekstu
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// vai
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Sveiki, Pasaule!";
});
```

### Novirzīšana

Jūs varat novirzīt pašreizējo pieprasījumu, izmantojot `redirect()` metodi un nododot jaunu URL:

```php
Flight::route('/login', function() {
	$username = Flight::request()->data->username;
	$password = Flight::request()->data->password;
	$passwordConfirm = Flight::request()->data->password_confirm;

	if($password !== $passwordConfirm) {
		Flight::redirect('/new/location');
		return; // tas ir nepieciešams, lai funkcija zemāk neizpildītos
	}

	// pievienojiet jauno lietotāju...
	Flight::db()->runQuery("INSERT INTO users ....");
	Flight::redirect('/admin/dashboard');
});
```

> **Piezīme:** Pēc noklusējuma Flight nosūta HTTP 303 ("See Other") statusa kodu. Jūs varat izvēles kārtā iestatīt pielāgotu kodu:

```php
Flight::redirect('/new/location', 301); // pastāvīgs
```

### Maršruta izpildes apturēšana

Jūs varat apturēt ietvaru un nekavējoties iziet jebkurā punktā, izsaucot `halt` metodi:

```php
Flight::halt();
```

Jūs varat arī norādīt izvēles `HTTP` statusa kodu un ziņojumu:

```php
Flight::halt(200, 'Drīz atgriezīšos...');
```

Izsaucot `halt`, tas atmetos jebkuru atbildes saturu līdz tam punktam un aptur visus izpildi. 
Ja vēlaties apturēt ietvaru un izvadīt pašreizējo atbildi, izmantojiet `stop` metodi:

```php
Flight::stop($httpStatusCode = null);
```

> **Piezīme:** `Flight::stop()` ir dažas dīvainas uzvedības, piemēram, tas izvadīs atbildi, bet turpinās izpildīt jūsu skriptu, kas var nebūt tas, ko jūs vēlaties. Jūs varat izmantot `exit` vai `return` pēc `Flight::stop()` izsaukšanas, lai novērstu turpmāku izpildi, bet parasti iesaka izmantot `Flight::halt()`. 

Tas saglabās galvenes atslēgu un vērtību atbildes objektā. Pieprasījuma dzīves cikla beigās tas izveidos galvenes un nosūtīs atbildi.

## Uzlabota lietošana

### Galvenes nosūtīšana nekavējoties

Var būt gadījumi, kad jums jāizdara kaut kas pielāgots ar galveni, un jums jānosūta galvene tajā pašā koda rindā, ar kuru jūs strādājat. Ja jūs iestatāt [straumētu maršrutu](/learn/routing), tas ir tas, kas jums būtu vajadzīgs. To var sasniegt, izmantojot `response()->setRealHeader()`.

```php
Flight::route('/', function() {
	Flight::response()->setRealHeader('Content-Type: text/plain');
	echo 'Straumēta atbilde...';
	sleep(5);
	echo 'Gatavs!';
})->stream();
```

### JSONP

JSONP pieprasījumiem jūs varat izvēles kārtā nodot vaicājuma parametra nosaukumu, ko izmantojat, lai definētu savu atbilde funkciju:

```php
Flight::jsonp(['id' => 123], 'q');
```

Tātad, veicot GET pieprasījumu, izmantojot `?q=my_func`, jums vajadzētu saņemt izvadi:

```javascript
my_func({"id":123});
```

Ja nenododat vaicājuma parametra nosaukumu, tas pēc noklusējuma būs `jsonp`.

> **Piezīme:** Ja jūs joprojām izmantojat JSONP pieprasījumus 2025. gadā un vēlāk, ielēkiet čatā un pastāstiet mums, kāpēc! Mēs mīlam dzirdēt dažus labus kaujas/briesmu stāstus!

### Atbildes datu notīrīšana

Jūs varat notīrīt atbildes ķermeni un galvenes, izmantojot `clear()` metodi. Tas notīrīs jebkuras galvenes, kas piešķirtas atbildei, notīrīs atbildes ķermeni un iestatīs statusa kodu uz `200`.

```php
Flight::response()->clear();
```

#### Tikai atbildes ķermeņa notīrīšana

Ja vēlaties notīrīt tikai atbildes ķermeni, jūs varat izmantot `clearBody()` metodi:

```php
// Tas joprojām saglabās jebkuras galvenes, kas iestatītas uz response() objektu.
Flight::response()->clearBody();
```

### HTTP kešošana

Flight nodrošina iebūvētu atbalstu HTTP līmeņa kešošanai. Ja kešošanas nosacījums ir izpildīts, Flight atgriezīs HTTP `304 Not Modified` atbildi. Nākamreiz, kad klients pieprasa to pašu resursu, viņi tiks aicināti izmantot savu lokāli kešoto versiju.

#### Maršruta līmeņa kešošana

Ja vēlaties kešot visu savu atbildi, jūs varat izmantot `cache()` metodi un nodot laiku kešošanai.

```php

// Tas kešos atbildi uz 5 minūtēm
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'Šis saturs tiks kešots.';
});

// Alternatīvi, jūs varat izmantot virkni, ko nodotu
// strtotime() metodei
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'Šis saturs tiks kešots.';
});
```

### Pēdējā modificēšana

Jūs varat izmantot `lastModified` metodi un nodot UNIX laika zīmogu, lai iestatītu datumu un laiku, kad lapa tika pēdējo reizi modificēta. Klients turpinās izmantot savu kešu, līdz pēdējās modificēšanas vērtība tiek mainīta.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'Šis saturs tiks kešots.';
});
```

### ETag

`ETag` kešošana ir līdzīga `Last-Modified`, izņemot to, ka jūs varat norādīt jebkuru ID, ko vēlaties resursam:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'Šis saturs tiks kešots.';
});
```

Ņemiet vērā, ka gan `lastModified`, gan `etag` izsaukums gan iestatīs, gan pārbaudīs keša vērtību. Ja keša vērtība ir tāda pati starp pieprasījumiem, Flight nekavējoties nosūtīs `HTTP 304` atbildi un apturēs apstrādi.

### Faila lejupielāde

_v3.12.0_

Ir palīgmēģene, lai straumētu failu galapunktam lietotājam. Jūs varat izmantot `download` metodi un nodot ceļu.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```

## Skatīt arī
- [Maršrutēšana](/learn/routing) - Kā kartēt maršrutus uz kontrolieriem un renderēt skatus.
- [Pieprasījumi](/learn/requests) - Izpratne par to, kā apstrādāt ienākošos pieprasījumus.
- [Starpprogrammatūra](/learn/middleware) - Izmantojot starpprogrammatūru ar maršrutiem autentifikācijai, žurnālošanai utt.
- [Kāpēc ietvars?](/learn/why-frameworks) - Izpratne par ieguvumiem, izmantojot ietvaru kā Flight.
- [Paplašināšana](/learn/extending) - Kā paplašināt Flight ar savu funkcionalitāti.

## Traucējummeklēšana
- Ja jums ir problēmas ar novirzīšanām, kas nedarbojas, pārliecinieties, ka pievienojat `return;` metodē.
- `stop()` un `halt()` nav tas pats. `halt()` apturēs izpildi nekavējoties, bet `stop()` ļaus izpildei turpināties.

## Izmaiņu žurnāls
- v3.12.0 - Pievienota downloadFile palīgmēģene.
- v3.10.0 - Pievienots `jsonHalt`.
- v1.0 - Sākotnējais izdevums.