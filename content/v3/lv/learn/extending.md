# Paplašināšana

## Pārskats

Flight ir izstrādāts kā paplašināms ietvars. Ietvars nāk ar kopu
noklusējuma metožu un komponentu, bet tas ļauj jums kartēt savas metodes,
reģistrēt savas klases vai pat pārrakstīt esošās klases un metodes.

## Saprašana

Ir 2 veidi, kā jūs varat paplašināt Flight funkcionalitāti:

1. Metožu kartēšana - Tas tiek izmantots, lai izveidotu vienkāršas pielāgotas metodes, kuras jūs varat izsaukt
   no jebkuras vietas savā lietojumprogrammā. Šīs parasti tiek izmantotas utilītas funkcijām,
   kuras jūs vēlaties varēt izsaukt no jebkuras vietas savā kodā. 
2. Klases reģistrēšana - Tas tiek izmantots, lai reģistrētu savas klases ar Flight. Šis
   parasti tiek izmantots klasēm, kurām ir atkarības vai nepieciešama konfigurācija.

Jūs varat arī pārrakstīt esošās ietvara metodes, lai mainītu to noklusējuma uzvedību, lai labāk
atbilstu jūsu projekta vajadzībām. 

> Ja jūs meklējat DIC (Atkarību injekcijas konteineru), pārejiet uz
[Dependency Injection Container](/learn/dependency-injection-container) lapu.

## Pamata lietošana

### Ietvara metožu pārrakstīšana

Flight ļauj jums pārrakstīt tā noklusējuma funkcionalitāti, lai tā atbilstu jūsu vajadzībām,
bez nepieciešamības modificēt jebkuru kodu. Jūs varat apskatīt visas metodes, kuras varat pārrakstīt [zemāk](#mappable-framework-methods).

Piemēram, kad Flight nevar saskaņot URL ar maršrutu, tas izsauc `notFound`
metodi, kas nosūta vispārīgu `HTTP 404` atbildi. Jūs varat pārrakstīt šo uzvedību,
izmantojot `map` metodi:

```php
Flight::map('notFound', function() {
  // Rādīt pielāgotu 404 lapu
  include 'errors/404.html';
});
```

Flight arī ļauj jums aizstāt ietvara kodola komponentus.
Piemēram, jūs varat aizstāt noklusējuma Router klasi ar savu pielāgotu klasi:

```php
// izveidot savu pielāgoto Router klasi
class MyRouter extends \flight\net\Router {
	// pārrakstīt metodes šeit
	// piemēram, saīsinājums GET pieprasījumiem, lai noņemtu
	// pass route funkciju
	public function get($pattern, $callback, $alias = '') {
		return parent::get($pattern, $callback, false, $alias);
	}
}

// Reģistrēt savu pielāgoto klasi
Flight::register('router', MyRouter::class);

// Kad Flight ielādē Router экземпlāru, tas ielādēs jūsu klasi
$myRouter = Flight::router();
$myRouter->get('/hello', function() {
  echo "Hello World!";
}, 'hello_alias');
```

Tomēr ietvara metodes, piemēram `map` un `register`, nevar tikt pārrakstītas. Jūs saņemsiet
kļūdu, ja mēģināsiet to izdarīt (atkal skatiet [zemāk](#mappable-framework-methods) metožu sarakstam).

### Kartējamās ietvara metodes

Turpmāk ir pilnīga metožu kopa ietvaram. Tā sastāv no kodola metodēm, 
kuras ir regulāras statiskas metodes, un paplašināmām metodēm, kuras ir kartētas metodes, kuras var 
filtrēt vai pārrakstīt.

#### Kodola metodes

Šīs metodes ir kodola ietvaram un nevar tikt pārrakstītas.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Izveido pielāgotu ietvara metodi.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Reģistrē klasi ietvara metodē.
Flight::unregister(string $name) // Izslēdz klasi no ietvara metodes.
Flight::before(string $name, callable $callback) // Pievieno filtru pirms ietvara metodes.
Flight::after(string $name, callable $callback) // Pievieno filtru pēc ietvara metodes.
Flight::path(string $path) // Pievieno ceļu automātiskai klases ielādei.
Flight::get(string $key) // Iegūst mainīgo, ko iestatījis Flight::set().
Flight::set(string $key, mixed $value) // Iestata mainīgo Flight dzinējā.
Flight::has(string $key) // Pārbauda, vai mainīgais ir iestatīts.
Flight::clear(array|string $key = []) // Notīra mainīgo.
Flight::init() // Inicializē ietvaru ar tā noklusējuma iestatījumiem.
Flight::app() // Iegūst lietojumprogrammas objekta экземпlāru
Flight::request() // Iegūst pieprasījuma objekta экземпlāru
Flight::response() // Iegūst atbildes objekta экземпlāru
Flight::router() // Iegūst maršrutētāja objekta экземпlāru
Flight::view() // Iegūst skata objekta экземпlāru
```

#### Paplašināmas metodes

```php
Flight::start() // Sāk ietvaru.
Flight::stop() // Aptur ietvaru un nosūta atbildi.
Flight::halt(int $code = 200, string $message = '') // Aptur ietvaru ar opcionālu statusa kodu un ziņu.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Kartē URL paraugu ar atsaukumu.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Kartē POST pieprasījuma URL paraugu ar atsaukumu.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Kartē PUT pieprasījuma URL paraugu ar atsaukumu.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Kartē PATCH pieprasījuma URL paraugu ar atsaukumu.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Kartē DELETE pieprasījuma URL paraugu ar atsaukumu.
Flight::group(string $pattern, callable $callback) // Izveido grupēšanu URL, paraugam jābūt virknei.
Flight::getUrl(string $name, array $params = []) // Ģenerē URL, balstoties uz maršruta aliasu.
Flight::redirect(string $url, int $code) // Pāradresē uz citu URL.
Flight::download(string $filePath) // Lejupielādē failu.
Flight::render(string $file, array $data, ?string $key = null) // Renderē veidnes failu.
Flight::error(Throwable $error) // Nosūta HTTP 500 atbildi.
Flight::notFound() // Nosūta HTTP 404 atbildi.
Flight::etag(string $id, string $type = 'string') // Veic ETag HTTP kešošanu.
Flight::lastModified(int $time) // Veic pēdējās modificēšanas HTTP kešošanu.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Nosūta JSON atbildi.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Nosūta JSONP atbildi.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Nosūta JSON atbildi un aptur ietvaru.
Flight::onEvent(string $event, callable $callback) // Reģistrē notikuma klausītāju.
Flight::triggerEvent(string $event, ...$args) // Izsauc notikumu.
```

Jebkuras pielāgotas metodes, kas pievienotas ar `map` un `register`, var arī tikt filtrētas. Piemēriem, kā filtrēt šīs metodes, skatiet [Filtering Methods](/learn/filtering) rokasgrāmatu.

#### Paplašināmas ietvara klases

Ir vairākas klases, kurām jūs varat pārrakstīt funkcionalitāti, paplašinot tās un
reģistrējot savu klasi. Šīs klases ir:

```php
Flight::app() // Lietojumprogrammas klase - paplašiniet flight\Engine klasi
Flight::request() // Pieprasījuma klase - paplašiniet flight\net\Request klasi
Flight::response() // Atbildes klase - paplašiniet flight\net\Response klasi
Flight::router() // Maršrutētāja klase - paplašiniet flight\net\Router klasi
Flight::view() // Skata klase - paplašiniet flight\template\View klasi
Flight::eventDispatcher() // Notikuma dispečera klase - paplašiniet flight\core\Dispatcher klasi
```

### Pielāgoto metožu kartēšana

Lai kartētu savu vienkāršo pielāgoto metodi, jūs izmantojat `map` funkciju:

```php
// Kartēt savu metodi
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// Izsaukt savu pielāgoto metodi
Flight::hello('Bob');
```

Lai gan ir iespējams izveidot vienkāršas pielāgotas metodes, ieteicams tikai izveidot
standarta funkcijas PHP. Tam ir autocomplēte IDE un tas ir vieglāk lasāms.
Iepriekš minētā koda ekvivalents būtu:

```php
function hello(string $name) {
  echo "hello $name!";
}

hello('Bob');
```

Tas tiek izmantots vairāk, kad jums jānodod mainīgie jūsu metodē, lai iegūtu gaidīto
vērtību. Izmantojot `register()` metodi kā zemāk, tas ir vairāk par konfigurācijas nodošanu
un tad izsaukt savu iepriekš konfigurēto klasi.

### Pielāto klases reģistrēšana

Lai reģistrētu savu klasi un konfigurētu to, jūs izmantojat `register` funkciju. Iepriekšējs ieguvums, ko tas sniedz pār map(), ir tas, ka jūs varat atkārtoti izmantot to pašu klasi, kad izsaucat šo funkciju (tas būtu noderīgi ar `Flight::db()`, lai dalītos tajā pašā экземпlārā).

```php
// Reģistrēt savu klasi
Flight::register('user', User::class);

// Iegūt klases экземпlāru
$user = Flight::user();
```

Reģistrēšanas metode arī ļauj jums nodot parametrus jūsu klases
konstruktoram. Tātad, kad jūs ielādējat savu pielāgoto klasi, tā nāks jau inicializēta.
Jūs varat definēt konstruktora parametrus, nododot papildu masīvu.
Šeit ir piemērs datubāzes savienojuma ielādei:

```php
// Reģistrēt klasi ar konstruktora parametriem
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Iegūt klases экземпlāru
// Tas izveidos objektu ar definētajiem parametriem
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// un ja jums tas būtu vajadzīgs vēlāk savā kodā, jūs tikai izsaucat to pašu metodi vēlreiz
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Ja jūs nododit papildu atsauces parametru, tas tiks izpildīts uzreiz
pēc klases konstrukcijas. Tas ļauj jums veikt jebkuru iestatīšanas procedūru jūsu
jaunajam objektam. Atsauces funkcija ņem vienu parametru, jauna objekta экземпlāru.

```php
// Atsauce tiks nodota konstrukcijas objekts
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Pēc noklusējuma katru reizi, kad jūs ielādējat savu klasi, jūs iegūsit kopīgu экземпlāru.
Lai iegūtu jaunu klases экземпlāru, vienkārši nododit `false` kā parametru:

```php
// Kopīgs klases экземпlārs
$shared = Flight::db();

// Jauns klases экземпlārs
$new = Flight::db(false);
```

> **Piezīme:** Ņemiet vērā, ka kartētās metodes ir priekšroka pār reģistrētajām klasēm. Ja jūs
deklarējat abas, izmantojot to pašu nosaukumu, tikai kartētā metode tiks izsaukta.

### Piemēri

Šeit ir daži piemēri, kā jūs varat paplašināt Flight ar funkcionalitāti, kas nav iebūvēta kodolā.

#### Žurnālveide

Flight nav iebūvēta žurnālveides sistēma, tomēr ir patiešām viegli
izmantot žurnālveides bibliotēku ar Flight. Šeit ir piemērs, izmantojot Monolog
bibliotēku:

```php
// services.php

// Reģistrēt žurnālu ar Flight
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

Tagad, kad tas ir reģistrēts, jūs varat to izmantot savā lietojumprogrammā:

```php
// Jūsu kontrolierī vai maršrutā
Flight::log()->warning('This is a warning message');
```

Tas ierakstīs ziņu norādītajā žurnāla failā. Ko tad, ja jūs vēlaties ierakstīt kaut ko, kad rodas
kļūda? Jūs varat izmantot `error` metodi:

```php
// Jūsu kontrolierī vai maršrutā
Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// Rādīt savu pielāgoto kļūdas lapu
	include 'errors/500.html';
});
```

Jūs arī varētu izveidot pamata APM (Lietojumprogrammas veiktspējas uzraudzību) sistēmu,
izmantojot `before` un `after` metodes:

```php
// Jūsu services.php failā

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('Request '.Flight::request()->url.' took ' . round($end - $start, 4) . ' seconds');

	// Jūs varētu arī pievienot savu pieprasījumu vai atbildes galvenes
	// lai ierakstītu tās (esiet uzmanīgs, jo tas būtu daudz datu, ja jums ir daudz pieprasījumu)
	Flight::log()->info('Request Headers: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('Response Headers: ' . json_encode(Flight::response()->headers));
});
```

#### Kešošana

Flight nav iebūvēta kešošanas sistēma, tomēr ir patiešām viegli
izmantot kešošanas bibliotēku ar Flight. Šeit ir piemērs, izmantojot
[PHP File Cache](/awesome-plugins/php_file_cache) bibliotēku:

```php
// services.php

// Reģistrēt kešu ar Flight
Flight::register('cache', \flight\Cache::class, [ __DIR__ . '/../cache/' ], function(\flight\Cache $cache) {
    $cache->setDevMode(ENVIRONMENT === 'development');
});
```

Tagad, kad tas ir reģistrēts, jūs varat to izmantot savā lietojumprogrammā:

```php
// Jūsu kontrolierī vai maršrutā
$data = Flight::cache()->get('my_cache_key');
if (empty($data)) {
	// Veikt kādu apstrādi, lai iegūtu datus
	$data = [ 'some' => 'data' ];
	Flight::cache()->set('my_cache_key', $data, 3600); // kešot uz 1 stundu
}
```

#### Viegla DIC objekta instantiācija

Ja jūs izmantojat DIC (Atkarību injekcijas konteineru) savā lietojumprogrammā,
jūs varat izmantot Flight, lai palīdzētu jums instantiēt savus objektus. Šeit ir piemērs, izmantojot
[Dice](https://github.com/level-2/Dice) bibliotēku:

```php
// services.php

// izveidot jaunu konteineru
$container = new \Dice\Dice;
// neaizmirstiet to atkārtoti piešķirt sev pašam kā zemāk!
$container = $container->addRule('PDO', [
	// shared nozīmē, ka tas pats objekts tiks atgriezts katru reizi
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// tagad mēs varam izveidot kartējamu metodi, lai izveidotu jebkuru objektu. 
Flight::map('make', function($class, $params = []) use ($container) {
	return $container->create($class, $params);
});

// Tas reģistrē konteinera apstrādātāju, lai Flight zinātu to izmantot kontrolieriem/vidējai slānim
Flight::registerContainerHandler(function($class, $params) {
	Flight::make($class, $params);
});


// pieņemsim, ka mums ir šāda parauga klase, kas ņem PDO objektu konstruktorā
class EmailCron {
	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function send() {
		// kods, kas nosūta e-pastu
	}
}

// Un beidzot jūs varat izveidot objektus, izmantojot atkarību injekciju
$emailCron = Flight::make(EmailCron::class);
$emailCron->send();
```

Eleganti, vai ne?

## Skatīt arī
- [Dependency Injection Container](/learn/dependency-injection-container) - Kā izmantot DIC ar Flight.
- [File Cache](/awesome-plugins/php_file_cache) - Piemērs kešošanas bibliotēkas izmantošanai ar Flight.

## Traucējummeklēšana
- Atcerieties, ka kartētām metodēm ir priekšroka pār reģistrētajām klasēm. Ja jūs deklarējat abas, izmantojot to pašu nosaukumu, tikai kartētā metode tiks izsaukta.

## Izmaiņu žurnāls
- v2.0 - Sākotnējā izlaišana.