# Paplašināšana

Flight ir veidota kā paplašināma ietvarstruktūra. Ietvarstruktūra nāk ar noteiktu noklusējuma metožu un komponentu kopu, taču tā ļauj jums kartot savas metodes, reģistrēt savas klases vai pat pārdefinēt esošās klases un metodes.

Ja meklējat DIC (atkarības injekcijas konteineru), aizejiet uz [Atkarības injekcijas konteineri](dependency-injection-container) lapu.

## Metožu kartēšana

Lai kartotu savu vienkāršo pielāgoto metodi, izmantojiet funkciju `map`:

```php
// Kartojiet savu metodi
Flight::map('hello', function (string $name) {
  echo "sveiki $name!";
});

// Izsauciet savu pielāgoto metodi
Flight::hello('Bob');
```

Lai gan ir iespējams izveidot vienkāršas pielāgotas metodes, ieteicams vienkārši izveidot standarta funkcijas PHP. Tam ir automātiska pabeigšana IDE un tas ir vieglāk lasāms. Iepriekšējā koda ekvivalents būtu:

```php
function hello(string $name) {
  echo "sveiki $name!";
}

hello('Bob');
```

Tas tiek izmantots vairāk, kad jums ir jānodod mainīgie jūsu metodei, lai iegūtu sagaidāmo vērtību. Izmantojot `register()` metodi, kā norādīts zemāk, tas attiecas uz konfigurācijas nodošanu un pēc tam jūsu iepriekš konfigurētās klases izsaukšanu.

## Klases reģistrācija

Lai reģistrētu savu klasi un to konfigurētu, izmantojiet funkciju `register`:

```php
// Reģistrējiet savu klasi
Flight::register('user', User::class);

// Iegūstiet sava objekta instance
$user = Flight::user();
```

Reģistrēšanas metode ļauj arī nodot parametrus jūsu klases konstruktoram. Tātad, kad jūs ielādējat savu pielāgoto klasi, tā būs iepriekš inicializēta. Jūs varat definēt konstruktora parametrus, nododot papildu masīvu. Šeit ir piemērs datu bāzes savienojuma ielādēšanai:

```php
// Reģistrējiet klasi ar konstruktora parametriem
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Iegūstiet sava objekta instance
// Tas izveidos objektu ar noteiktajiem parametriem
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// un, ja jums tas vēlāk vajadzēs savā kodā, jūs vienkārši izsaucat to pašu metodi
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Ja jūs nododat papildu atgriezeniskās saites parametru, tas tiks izpildīts tūlīt pēc klases konstrukcijas. Tas ļauj jums veikt jebkādas iestatīšanas procedūras jūsu jaunajam objektam. Atgriezeniskās saites funkcija pieņem vienu parametru, jaunā objekta instanci.

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

Pēc noklusējuma, katru reizi, kad jūs ielādējat savu klasi, jūs saņemsiet kopīgu instanci. Lai iegūtu jaunu klases instanci, vienkārši nododiet `false` kā parametrs:

```php
// Kopa instance no klases
$shared = Flight::db();

// Jauna instance no klases
$new = Flight::db(false);
```

Ņemiet vērā, ka kartotām metodēm ir prioritāte pār reģistrētajām klasēm. Ja jūs deklarējat abus, izmantojot to pašu nosaukumu, tiks izsaukta tikai kartotā metode.

## Pierakstīšana

Flight nav iebūvēta pierakstīšanas sistēma, tomēr ir ļoti viegli izmantot pierakstīšanas bibliotēku kopā ar Flight. Šeit ir piemērs, izmantojot Monolog bibliotēku:

```php
// index.php vai bootstrap.php

// Reģistrējiet ierakstītāju pie Flight
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

Tagad, kad tas ir reģistrēts, jūs varat to izmantot savā lietotnē:

```php
// Jūsu kontrollerī vai maršrutā
Flight::log()->warning('Šis ir brīdinājuma ziņojums');
```

Tas ierakstīs ziņojumu log failā, kuru norādījāt. Ko darīt, ja vēlaties ierakstīt kaut ko, kad notiek kļūda? Jūs varat izmantot `error` metodi:

```php
// Jūsu kontrollerī vai maršrutā

Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// Rādīt savu pielāgoto kļūdas lapu
	include 'errors/500.html';
});
```

Jūs arī varētu izveidot pamata APM (Lietojumprogrammu veiktspējas uzraudzības) sistēmu, izmantojot `before` un `after` metodes:

```php
// Jūsu bootstrap failā

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('Pieprasījums '.Flight::request()->url.' aizņēma ' . round($end - $start, 4) . ' sekundes');

	// Jūs varētu arī pievienot savus pieprasījuma vai atbildes galvenes,
	// lai tās arī ierakstītu (esiet uzmanīgs, jo tas varētu būt daudz 
	// datu, ja jums ir daudz pieprasījumu)
	Flight::log()->info('Pieprasījuma galvenes: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('Atbildes galvenes: ' . json_encode(Flight::response()->headers));
});
```

## Ietvara metožu pārrakstīšana

Flight ļauj jums pārrakstīt tā noklusējuma funkcionalitāti, lai tā atbilstu jūsu vajadzībām, neieguldot nevienā kodā. Jūs varat apskatīt visas metodes, kuras varat pārrakstīt [šeit](/learn/api).

Piemēram, kad Flight nespēj pieskaņot URL maršrutam, tā izsauc `notFound` metodi, kas nosūta vispārēju `HTTP 404` atbildi. Jūs varat pārrakstīt šo uzvedību, izmantojot `map` metodi:

```php
Flight::map('notFound', function() {
  // Rādīt pielāgotu 404 lapu
  include 'errors/404.html';
});
```

Flight arī ļauj jums aizstāt ietvara pamatkomponentus. Piemēram, jūs varat aizstāt noklusējuma maršrutētāja klasi ar savu pielāgoto klasi:

```php
// Reģistrējiet savu pielāgoto klasi
Flight::register('router', MyRouter::class);

// Kad Flight ielādē maršrutētāja instanci, tā ielādēs jūsu klasi
$myrouter = Flight::router();
```

Tomēr ietvara metodes, piemēram, `map` un `register`, nevar tikt pārrakstītas. Ja jūs mēģināsiet to izdarīt, jūs saņemsiet kļūdu.