# Paplašināšana

Flight ir izstrādāts kā paplašināms rīku komplekts. Rīku komplekts nāk ar komplektu
noklusējuma metožu un komponentu, bet ļauj jums pievienot savas metodes,
reģistrēt savas klases vai pat aizvietot esošās klases un metodes.

Ja meklējat DIC (atkarību injekcijas konteineru), dodieties uz
[Atkarību injekcijas konteinera](dependency-injection-container) lapu.

## Metožu kartēšana

Lai kartētu savu vienkāršo pielāgoto metodi, izmantojiet `map` funkciju:

```php
// Kartējiet savu metodi
Flight::map('hello', function (string $name) {
  echo "sveiks $name!";
});

// Izsauciet savu pielāgoto metodi
Flight::hello('Bob');
```

Lai gan ir iespējams veidot vienkāršas pielāgotas metodes, ieteicams vienkārši izveidot
standarta funkcijas PHP. Tam ir automātiska pabeigšana IDE un tas ir vieglāk lasāms.
Atbilstošais iepriekš minētā koda variants būtu:

```php
function hello(string $name) {
  echo "sveiks $name!";
}

hello('Bob');
```

Tas tiek izmantots vairāk, kad nepieciešams nodot mainīgos savā metodē, lai iegūtu gaidīto
vērtību. Izmantojot `register()` metodi zemāk, tas ir vairāk paredzēts konfigurācijas
nodošanai un pēc tam jūsu iepriekš konfigurētās klases izsaukšanai.

## Klases reģistrācija

Lai reģistrētu savu klasi un konfigurētu to, izmantojiet `register` funkciju:

```php
// Reģistrējiet savu klasi
Flight::register('user', User::class);

// Iegūstiet instanci no savas klases
$user = Flight::user();
```

Reģistrācijas metode arī ļauj jums nodot parametrus savai klases
konstruktora metodei. Tādējādi, kad ielādējat savu pielāgoto klasi, tā tiks iepriekš
initializēta. Jūs varat definēt konstruktora parametrus, nododot papildu masīvu.
Šeit ir piemērs, kā ielādēt datubāzes savienojumu:

```php
// Reģistrējiet klasi ar konstruktora parametriem
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Iegūstiet instanci no savas klases
// Tas izveidos objektu ar definētajiem parametriem
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// un, ja jums tas būs nepieciešams vēlāk savā kodā, vienkārši izsauciet to pašu metodi vēlreiz
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Ja jūs nododat papildu atgriezeniskās saites parametru, tas tiks izpildīts uzreiz
pēc klases konstrukcijas. Tas ļauj veikt jebkādas sagatavošanas procedūras jūsu
jaunajam objektam. Atgriezeniskās saites funkcijai ir viens parametrs, jauna objekta instance.

```php
// Atgriezeniskā saite tiks nodota objektam, kas tika konstruēts
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Noklusējuma iestatījumos, katru reizi, kad ielādējat savu klasi, jūs saņemsiet kopēju instanci.
Lai iegūtu jaunu klases instanci, vienkārši nododiet `false` kā parametru:

```php
// Kopīga klases instance
$shared = Flight::db();

// Jauna klases instance
$new = Flight::db(false);
```

Ņemiet vērā, ka kartētām metodēm ir priekšroka pār reģistrētām klasēm. Ja jūs
deklarējat abus, izmantojot to pašu nosaukumu, tiks izsaukta tikai kartētā metode.

## Žurnālfailu veidošana

Flight nav iebūvēta žurnālfailu sistēma, tomēr ir ļoti viegli
izmantot žurnālfailu bibliotēku ar Flight. Šeit ir piemērs, izmantojot Monolog
bibliotēku:

```php
// index.php vai bootstrap.php

// Reģistrējiet žurnālu ar Flight
Flight::register('log', Monolog\Logger::class, ['name'], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

Tagad, kad tas ir reģistrēts, jūs to varat izmantot savā lietotnē:

```php
// Jūsu kontrolierī vai maršrutā
Flight::log()->warning('Tas ir brīdinājuma ziņojums');
```

Tas ierakstīs ziņojumu žurnālfailā, kuru jūs norādījāt. Ko darīt, ja vēlaties ierakstīt kaut ko, kad
notiek kļūda? Jūs varat izmantot `error` metodi:

```php
// Jūsu kontrolierī vai maršrutā

Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// Rādīt savu pielāgoto kļūdas lapu
	include 'errors/500.html';
});
```

Jūs arī varētu izveidot pamata APM (pieteikumu veiktspējas uzraudzības) sistēmu
izmantojot `before` un `after` metodes:

```php
// Jūsu bootstrap failā

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('Pieprasījums '.Flight::request()->url.' ilga ' . round($end - $start, 4) . ' sekundes');

	// Jūs varētu arī pievienot savus pieprasījuma vai atbildes galvenes
	// lai tās reģistrētu (esiet uzmanīgi, jo tas varētu būt
	// daudz datu, ja jums ir daudz pieprasījumu)
	Flight::log()->info('Pieprasījuma galvenes: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('Atbildes galvenes: ' . json_encode(Flight::response()->headers));
});
```

## Rīku komplekta metožu aizvietošana

Flight ļauj jums mainīt tā noklusējuma funkcionalitāti, lai tā atbilstu jūsu vajadzībām,
neizmainot nekādu kodu. Jūs varat skatīt visas metodes, kuras varat aizvietot [šeit](/learn/api).

Piemēram, kad Flight nevar atbilstoši maršrutam savienot URL, tas izsauc `notFound`
metodi, kas sūta vispārēju `HTTP 404` atbildi. Jūs varat aizvietot šo uzvedību
izmantojot `map` metodi:

```php
Flight::map('notFound', function() {
  // Rādīt pielāgotu 404 lapu
  include 'errors/404.html';
});
```

Flight arī ļauj jums aizvietot rīku komplekta kodolkomponentus.
Piemēram, jūs varat aizvietot noklusējuma maršrutētāja klasi ar savu pielāgoto klasi:

```php
// Reģistrējiet savu pielāgoto klasi
Flight::register('router', MyRouter::class);

// Kad Flight ielādē maršrutētāja instanci, tā ielādēs jūsu klasi
$myrouter = Flight::router();
```

Tomēr, rīku komplekta metodes, piemēram, `map` un `register`, nevar tikt aizvietotas. Jūs
saņemsiet kļūdu, ja mēģināsiet to izdarīt.