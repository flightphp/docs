# Maršrutizācija

## Pārskats
Maršrutizācija Flight PHP kartē URL raksturlietas ar atgriezeniskajām funkcijām vai klases metodēm, ļaujot ātri un vienkārši apstrādāt pieprasījumus. Tā ir paredzēta minimālam overhead, iesācējam draudzīgai lietošanai un paplašināmībai bez ārējām atkarībām.

## Saprašana
Maršrutizācija ir kodola mehānisms, kas savieno HTTP pieprasījumus ar jūsu lietojumprogrammas loģiku Flight. Definējot maršrutus, jūs norādāt, kā dažādas URL izraisa specifisku kodu, vai nu caur funkcijām, klases metodēm vai kontroliera darbībām. Flight maršrutizācijas sistēma ir elastīga, atbalsta pamatraksturlietas, nosauktos parametrus, regulārās izteiksmes un papildu funkcijas, piemēram, atkarību injekciju un resursu maršrutizāciju. Šī pieeja uztur jūsu kodu organizētu un viegli uzturamu, vienlaikus paliekot ātra un vienkārša iesācējiem un paplašināma pieredzējušiem lietotājiem.

> **Piezīme:** Vēlaties saprast vairāk par maršrutizāciju? Apskatiet ["kāpēc ietvars?"](/learn/why-frameworks) lapu plašākam skaidrojumam.

## Pamata Lietošana

### Vienkārša Maršruta Definēšana
Pamata maršrutizācija Flight tiek veikta, saskaņojot URL raksturlietu ar atgriezenisko funkciju vai klases un metodes masīvu.

```php
Flight::route('/', function(){
    echo 'sveiks pasaule!';
});
```

> Maršruti tiek saskaņoti tajā secībā, kādā tie ir definēti. Pirmais maršruts, kas saskan ar pieprasījumu, tiks izsaukts.

### Funkciju Lietošana kā Atgriezeniskajām Funkcijām
Atgriezeniskā funkcija var būt jebkurš callable objekts. Tātad jūs varat izmantot parasto funkciju:

```php
function hello() {
    echo 'sveiks pasaule!';
}

Flight::route('/', 'hello');
```

### Klases un Metodes Lietošana kā Kontrolieris
Jūs varat izmantot klases metodi (statisku vai nē) kā:

```php
class GreetingController {
    public function hello() {
        echo 'sveiks pasaule!';
    }
}

Flight::route('/', [ 'GreetingController','hello' ]);
// or
Flight::route('/', [ GreetingController::class, 'hello' ]); // preferred method
// or
Flight::route('/', [ 'GreetingController::hello' ]);
// or 
Flight::route('/', [ 'GreetingController->hello' ]);
```

Vai arī izveidojot objektu pirms tam un pēc tam izsaucot metodi:

```php
use flight\Engine;

// GreetingController.php
class GreetingController
{
	protected Engine $app
    public function __construct(Engine $app) {
		$this->app = $app;
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Sveiks, {$this->name}!";
    }
}

// index.php
$app = Flight::app();
$greeting = new GreetingController($app);

Flight::route('/', [ $greeting, 'hello' ]);
```

> **Piezīme:** Pēc noklusējuma, kad kontrolieris tiek izsaukts ietvarā, flight\Engine klase vienmēr tiek injicēta, ja vien jūs neprecizējat caur [atkarību injekcijas konteineru](/learn/dependency-injection-container)

### Metodes Specifiska Maršrutizācija

Pēc noklusējuma maršruta raksturlietas tiek saskaņotas pret visām pieprasījuma metodēm. Jūs varat atbildēt uz specifiskām metodēm, novietojot identifikatoru pirms URL.

```php
Flight::route('GET /', function () {
  echo 'Es saņēmu GET pieprasījumu.';
});

Flight::route('POST /', function () {
  echo 'Es saņēmu POST pieprasījumu.';
});

// You cannot use Flight::get() for routes as that is a method 
//    to get variables, not create a route.
Flight::post('/', function() { /* code */ });
Flight::patch('/', function() { /* code */ });
Flight::put('/', function() { /* code */ });
Flight::delete('/', function() { /* code */ });
```

Jūs varat arī kartēt vairākas metodes uz vienu atgriezenisko funkciju, izmantojot `|` atdalītāju:

```php
Flight::route('GET|POST /', function () {
  echo 'Es saņēmu vai nu GET, vai POST pieprasījumu.';
});
```

### Īpaša Apstrāde HEAD un OPTIONS Pieprasījumiem

Flight nodrošina iebūvētu apstrādi HEAD un OPTIONS HTTP pieprasījumiem:

#### HEAD Pieprasījumi

- **HEAD pieprasījumi** tiek apstrādāti tieši kā GET pieprasījumi, bet Flight automātiski noņem atbildes ķermeni pirms tā nosūtīšanas klientam.
- Tas nozīmē, ka jūs varat definēt maršrutu GET, un HEAD pieprasījumi uz to pašu URL atgriezīs tikai galvenes (bez satura), kā paredzēts HTTP standartos.

```php
Flight::route('GET /info', function() {
    echo 'Šī ir kāda informācija!';
});
// A HEAD request to /info will return the same headers, but no body.
```

#### OPTIONS Pieprasījumi

OPTIONS pieprasījumi tiek automātiski apstrādāti Flight jebkuram definētam maršrutam.
- Kad saņemts OPTIONS pieprasījums, Flight atbild ar `204 No Content` statusu un `Allow` galveni, kas uzskaita visas atbalstītās HTTP metodes tam maršrutam.
- Jums nav jādefinē atsevišķs maršruts OPTIONS.

```php
// For a route defined as:
Flight::route('GET|POST /users', function() { /* ... */ });

// An OPTIONS request to /users will respond with:
//
// Status: 204 No Content
// Allow: GET, POST, HEAD, OPTIONS
```

### Maršrutētāja Objekta Lietošana

Papildus jūs varat iegūt Maršrutētāja objektu, kuram ir daži palīgapstrādes metodes jūsu lietošanai:

```php

$router = Flight::router();

// maps all methods just like Flight::route()
$router->map('/', function() {
	echo 'sveiks pasaule!';
});

// GET request
$router->get('/users', function() {
	echo 'lietotāji';
});
$router->post('/users', 			function() { /* code */});
$router->put('/users/update/@id', 	function() { /* code */});
$router->delete('/users/@id', 		function() { /* code */});
$router->patch('/users/@id', 		function() { /* code */});
```

### Regulārās Izteiksmes (Regex)
Jūs varat izmantot regulārās izteiksmes savos maršrutos:

```php
Flight::route('/user/[0-9]+', function () {
  // This will match /user/1234
});
```

Lai gan šī metode ir pieejama, ieteicams izmantot nosauktos parametrus vai nosauktos parametrus ar regulārajām izteiksmēm, jo tie ir lasāmāki un vieglāk uzturami.

### Nosauktie Parametri
Jūs varat norādīt nosauktos parametrus savos maršrutos, kas tiks nodoti jūsu atgriezeniskajai funkcijai. **Tas ir vairāk maršruta lasāmībai nekā jebkas cits. Lūdzu, skatiet sadaļu zemāk par svarīgu brīdinājumu.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "sveiks, $name ($id)!";
});
```

Jūs varat arī iekļaut regulārās izteiksmes ar saviem nosauktajiem parametriem, izmantojot `:` atdalītāju:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // This will match /bob/123
  // But will not match /bob/12345
});
```

> **Piezīme:** Saskaņošanas regex grupas `()` ar pozicionālajiem parametriem nav atbalstītas. Piem: `:'\(`

#### Svarīgs Brīdinājums

Lai gan iepriekšējā piemērā šķiet, ka `@name` ir tieši saistīts ar mainīgo `$name`, tas nav. Parametru secība atgriezeniskajā funkcijā nosaka, kas tam tiek nodots. Ja jūs mainītu parametru secību atgriezeniskajā funkcijā, mainīgie tiktu mainīti arī. Šeit ir piemērs:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "sveiks, $name ($id)!";
});
```

Un ja jūs apmeklētu šādu URL: `/bob/123`, izvade būtu `sveiks, 123 (bob)!`. 
_Lūdzu, esiet uzmanīgi_, kad iestatāt savus maršrutus un atgriezeniskās funkcijas!

### Neobligātie Parametri
Jūs varat norādīt nosauktos parametrus, kas ir neobligāti saskaņošanai, ietverot segmentus iekavās.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // This will match the following URLS:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Jei kuri neobligātie parametri netiek saskaņoti, tie tiks nodoti kā `NULL`.

### Wildcard Maršrutizācija
Saskaņošana tiek veikta tikai uz atsevišķiem URL segmentiem. Ja vēlaties saskaņot vairākus segmentus, varat izmantot `*` wildcard.

```php
Flight::route('/blog/*', function () {
  // This will match /blog/2000/02/01
});
```

Lai maršrutētu visus pieprasījumus uz vienu atgriezenisko funkciju, varat darīt:

```php
Flight::route('*', function () {
  // Do something
});
```

### 404 Nav Atrasts Apstrādātājs

Pēc noklusējuma, ja URL nevar atrast, Flight nosūtīs `HTTP 404 Not Found` atbildi, kas ir ļoti vienkārša un vienkārša.
Ja vēlaties pielāgotu 404 atbildi, varat [kartēt](/learn/extending) savu `notFound` metodi:

```php
Flight::map('notFound', function() {
	$url = Flight::request()->url;

	// You could also use Flight::render() with a custom template.
    $output = <<<HTML
		<h1>Mans Pielāgots 404 Nav Atrasts</h1>
		<h3>Lapa, kuru jūs pieprasījāt {$url}, netika atrasta.</h3>
		HTML;

	$this->response()
		->clearBody()
		->status(404)
		->write($output)
		->send();
});
```

### Metodes Nav Atrasts Apstrādātājs

Pēc noklusējuma, ja URL ir atrasts, bet metode nav atļauta, Flight nosūtīs `HTTP 405 Method Not Allowed` atbildi, kas ir ļoti vienkārša un vienkārša (Piem: Method Not Allowed. Allowed Methods are: GET, POST). Tā arī iekļaus `Allow` galveni ar atļautajām metodēm tam URL.

Ja vēlaties pielāgotu 405 atbildi, varat [kartēt](/learn/extending) savu `methodNotFound` metodi:

```php
use flight\net\Route;

Flight::map('methodNotFound', function(Route $route) {
	$url = Flight::request()->url;
	$methods = implode(', ', $route->methods);

	// You could also use Flight::render() with a custom template.
	$output = <<<HTML
		<h1>Mans Pielāgots 405 Metode Nav Atļauta</h1>
		<h3>Metode, kuru jūs pieprasījāt {$url}, nav atļauta.</h3>
		<p>Atļautās Metodes ir: {$methods}</p>
		HTML;

	$this->response()
		->clearBody()
		->status(405)
		->setHeader('Allow', $methods)
		->write($output)
		->send();
});
```

## Papildu Lietošana

### Atkarību Injekcija Maršrutos
Ja vēlaties izmantot atkarību injekciju caur konteineru (PSR-11, PHP-DI, Dice utt.), vienīgais maršrutu veids, kur tas ir pieejams, ir vai nu tieši izveidojot objektu pats un izmantojot konteineru, lai izveidotu jūsu objektu, vai arī varat izmantot virknes, lai definētu klasi un metodi, ko izsaukt. Jūs varat apmeklēt [Atkarību Injekcijas](/learn/dependency-injection-container) lapu vairāk informācijas.

Šeit ir ātrs piemērs:

```php

use flight\database\PdoWrapper;

// Greeting.php
class Greeting
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;
	}

	public function hello(int $id) {
		// do something with $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Sveiks, pasaule! Mans vārds ir {$name}!";
	}
}

// index.php

// Setup the container with whatever params you need
// See the Dependency Injection page for more information on PSR-11
$dice = new \Dice\Dice();

// Don't forget to reassign the variable with '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Register the container handler
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Routes like normal
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// or
Flight::route('/hello/@id', 'Greeting->hello');
// or
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

### Izpildes Nodošana Nākamajam Maršrutam
<span class="badge bg-warning">Deprecated</span>
Jūs varat nodot izpildi nākamajam saskanīgajam maršrutam, atgriežot `true` no jūsu atgriezeniskās funkcijas.

```php
Flight::route('/user/@name', function (string $name) {
  // Check some condition
  if ($name !== "Bob") {
    // Continue to next route
    return true;
  }
});

Flight::route('/user/*', function () {
  // This will get called
});
```

Tagad ieteicams izmantot [middleware](/learn/middleware), lai apstrādātu sarežģītus gadījumus kā šis.

### Maršruta Aliasēšana
Piešķirot aliasu maršrutam, jūs varat vēlāk dinamiski izsaukt šo aliasu savā lietojumprogrammā, lai tas tiktu ģenerēts vēlāk jūsu kodā (piem: saite HTML veidnē vai ģenerējot pāradresācijas URL).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
// or 
Flight::route('/users/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');

// later in code somewhere
class UserController {
	public function update() {

		// code to save user...
		$id = $user['id']; // 5 for example

		$redirectUrl = Flight::getUrl('user_view', [ 'id' => $id ]); // will return '/users/5'
		Flight::redirect($redirectUrl);
	}
}

```

Tas ir īpaši noderīgi, ja jūsu URL mainās. Iepriekšējā piemērā, pieņemsim, ka lietotāji tika pārvietoti uz `/admin/users/@id` vietā.
Ar aliasēšanu vietā maršrutam, jums vairs nav jāmeklē visi vecie URL jūsu kodā un jāmaina tie, jo alias tagad atgriezīs `/admin/users/5`, kā piemērā iepriekš.

Maršruta aliasēšana joprojām darbojas grupās:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
	// or
	Flight::route('/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');
});
```

### Maršruta Informācijas Pārbaude
Ja vēlaties pārbaudīt saskanīgo maršruta informāciju, ir 2 veidi, kā to izdarīt:

1. Jūs varat izmantot `executedRoute` īpašību uz `Flight::router()` objekta.
2. Jūs varat pieprasīt, lai maršruta objekts tiktu nodots jūsu atgriezeniskajai funkcijai, nododot `true` kā trešo parametru maršruta metodē. Maršruta objekts vienmēr būs pēdējais parametrs, kas nodots jūsu atgriezeniskajai funkcijai.

#### `executedRoute`
```php
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;
  // Do something with $route
  // Array of HTTP methods matched against
  $route->methods;

  // Array of named parameters
  $route->params;

  // Matching regular expression
  $route->regex;

  // Contains the contents of any '*' used in the URL pattern
  $route->splat;

  // Shows the url path....if you really need it
  $route->pattern;

  // Shows what middleware is assigned to this
  $route->middleware;

  // Shows the alias assigned to this route
  $route->alias;
});
```

> **Piezīme:** `executedRoute` īpašība tiks iestatīta tikai pēc tam, kad maršruts ir izpildīts. Ja mēģināsiet piekļūt tai pirms maršruta izpildes, tā būs `NULL`. Jūs varat izmantot executedRoute arī [middleware](/learn/middleware)!

#### Nodošana `true` maršruta definīcijā
```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array of HTTP methods matched against
  $route->methods;

  // Array of named parameters
  $route->params;

  // Matching regular expression
  $route->regex;

  // Contains the contents of any '*' used in the URL pattern
  $route->splat;

  // Shows the url path....if you really need it
  $route->pattern;

  // Shows what middleware is assigned to this
  $route->middleware;

  // Shows the alias assigned to this route
  $route->alias;
}, true);// <-- This true parameter is what makes that happen
```

### Maršruta Grupēšana un Middleware
Var būt gadījumi, kad vēlaties grupēt saistītus maršrutus kopā (piemēram, `/api/v1`).
Jūs varat to izdarīt, izmantojot `group` metodi:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Matches /api/v1/users
  });

  Flight::route('/posts', function () {
	// Matches /api/v1/posts
  });
});
```

Jūs pat varat ligzdot grupu grupas:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() gets variables, it doesn't set a route! See object context below
	Flight::route('GET /users', function () {
	  // Matches GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Matches POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Matches PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() gets variables, it doesn't set a route! See object context below
	Flight::route('GET /users', function () {
	  // Matches GET /api/v2/users
	});
  });
});
```

#### Grupēšana ar Objekta Kontekstu

Jūs joprojām varat izmantot maršruta grupēšanu ar `Engine` objektu šādā veidā:

```php
$app = Flight::app();

$app->group('/api/v1', function (Router $router) {

  // user the $router variable
  $router->get('/users', function () {
	// Matches GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Matches POST /api/v1/posts
  });
});
```

> **Piezīme:** Šī ir ieteiktā metode maršrutu un grupu definēšanai ar `$router` objektu.

#### Grupēšana ar Middleware

Jūs varat arī piešķirt middleware grupai maršrutu:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Matches /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // or [ new MyAuthMiddleware() ] if you want to use an instance
```

Skatiet vairāk detaļu [grupas middleware](/learn/middleware#grouping-middleware) lapā.

### Resursu Maršrutizācija
Jūs varat izveidot maršrutu kopu resursam, izmantojot `resource` metodi. Tas izveidos maršrutu kopu resursam, kas seko RESTful konvencijām.

Lai izveidotu resursu, dariet šādu:

```php
Flight::resource('/users', UsersController::class);
```

Un kas notiks fonā, tas izveidos šādus maršrutus:

```php
[
      'index' => 'GET /users',
      'create' => 'GET /users/create',
      'store' => 'POST /users',
      'show' => 'GET /users/@id',
      'edit' => 'GET /users/@id/edit',
      'update' => 'PUT /users/@id',
      'destroy' => 'DELETE /users/@id'
]
```

Un jūsu kontrolieris izmantos šādas metodes:

```php
class UsersController
{
    public function index(): void
    {
    }

    public function show(string $id): void
    {
    }

    public function create(): void
    {
    }

    public function store(): void
    {
    }

    public function edit(string $id): void
    {
    }

    public function update(string $id): void
    {
    }

    public function destroy(string $id): void
    {
    }
}
```

> **Piezīme**: Jūs varat skatīt jaunos pievienotos maršrutus ar `runway`, palaižot `php runway routes`.

#### Pielāgošana Resursu Maršrutiem

Ir dažas opcijas resursu maršrutu konfigurēšanai.

##### Alias Bāze

Jūs varat konfigurēt `aliasBase`. Pēc noklusējuma alias ir pēdējā URL daļa, kas norādīta.
Piemēram, `/users/` rezultēs `aliasBase` kā `users`. Kad šie maršruti ir izveidoti, alias ir `users.index`, `users.create` utt. Ja vēlaties mainīt alias, iestatiet `aliasBase` uz vēlamo vērtību.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

##### Tikai un Izņemot

Jūs varat arī norādīt, kurus maršrutus vēlaties izveidot, izmantojot `only` un `except` opcijas.

```php
// Whitelist only these methods and blacklist the rest
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
// Blacklist only these methods and whitelist the rest
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Šie ir pamatā balstīti uz balto sarakstu un melno sarakstu opcijām, lai jūs varētu norādīt, kurus maršrutus vēlaties izveidot.

##### Middleware

Jūs varat arī norādīt middleware, kas jāizpilda katram no maršrutiem, ko izveido `resource` metode.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

### Plūsmas Atbildes

Jūs tagad varat plūst atbildes klientam, izmantojot `stream()` vai `streamWithHeaders()`. 
Tas ir noderīgi lielu failu nosūtīšanai, garām procesiem vai lielu atbilžu ģenerēšanai. 
Maršruta plūsmošana tiek apstrādāta nedaudz savādāk nekā parasts maršruts.

> **Piezīme:** Plūsmas atbildes ir pieejamas tikai tad, ja jums ir iestatīts [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) kā `false`.

#### Plūsma ar Manuālām Galvenēm

Jūs varat plūst atbildi klientam, izmantojot `stream()` metodi uz maršruta. Ja dariet to, jums jāiestata visas galvenes manuāli pirms izvades kaut ko klientam.
Tas tiek darīts ar `header()` php funkciju vai `Flight::response()->setRealHeader()` metodi.

```php
Flight::route('/@filename', function($filename) {

	$response = Flight::response();

	// obviously you would sanitize the path and whatnot.
	$fileNameSafe = basename($filename);

	// If you have additional headers to set here after the route has executed
	// you must define them before anything is echoed out.
	// They must all be a raw call to the header() function or 
	// a call to Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// or
	$response->setRealHeader('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');

	$filePath = '/some/path/to/files/'.$fileNameSafe;

	if (!is_readable($filePath)) {
		Flight::halt(404, 'File not found');
	}

	// manually set the content length if you'd like
	header('Content-Length: '.filesize($filePath));
	// or
	$response->setRealHeader('Content-Length: '.filesize($filePath));

	// Stream the file to the client as it's read
	readfile($filePath);

// This is the magic line here
})->stream();
```

#### Plūsma ar Galvenēm

Jūs varat izmantot `streamWithHeaders()` metodi, lai iestatītu galvenes pirms plūsmošanas sākšanas.

```php
Flight::route('/stream-users', function() {

	// you can add any additional headers you want here
	// you just must use header() or Flight::response()->setRealHeader()

	// however you pull your data, just as an example...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// This is required to send the data to the client
		ob_flush();
	}
	echo '}';

// This is how you'll set the headers before you start streaming.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// optional status code, defaults to 200
	'status' => 200
]);
```

## Skatīt Arī
- [Middleware](/learn/middleware) - Middleware lietošana ar maršrutiem autentifikācijai, žurnālošanai utt.
- [Atkarību Injekcija](/learn/dependency-injection-container) - Objektu izveides un pārvaldības vienkāršošana maršrutos.
- [Kāpēc Ietvars?](/learn/why-frameworks) - Ietvara kā Flight izmantošanas priekšrocību saprašana.
- [Paplašināšana](/learn/extending) - Kā paplašināt Flight ar savu funkcionalitāti, ieskaitot `notFound` metodi.
- [php.net: preg_match](https://www.php.net/manual/en/function.preg-match.php) - PHP funkcija regulāro izteiksmju saskaņošanai.

## Traucējummeklēšana
- Maršruta parametri tiek saskaņoti pēc secības, nevis pēc nosaukuma. Pārliecinieties, ka atgriezeniskās funkcijas parametru secība atbilst maršruta definīcijai.
- `Flight::get()` lietošana nedefinē maršrutu; izmantojiet `Flight::route('GET /...')` maršrutizācijai vai Router objekta kontekstu grupās (piem. `$router->get(...)`).
- executedRoute īpašība tiek iestatīta tikai pēc maršruta izpildes; tā ir NULL pirms izpildes.
- Plūsmošanai nepieciešama legacy Flight izvades buferizācijas funkcionalitāte, kas ir atspējota (`flight.v2.output_buffering = false`).
- Atkarību injekcijai tikai noteiktas maršruta definīcijas atbalsta konteineru balstītu instancēšanu.

### 404 Nav Atrasts vai Negaidīta Maršruta Uzvedība

Ja redzat 404 Nav Atrasts kļūdu (bet jūs zvērāt uz savu dzīvi, ka tas tiešām ir tur un tas nav drukas kļūda), tas patiesībā varētu būt problēma ar vērtības atgriešanu jūsu maršruta galapunktā, nevis tikai to izsaukšanu. Iemesls tam ir tīms, bet var uzmest dažus izstrādātājus.

```php
Flight::route('/hello', function(){
	// This might cause a 404 Not Found error
	return 'Sveiks Pasaule';
});

// What you probably want
Flight::route('/hello', function(){
	echo 'Sveiks Pasaule';
});
```

Iemesls tam ir īpašs mehānisms, kas iebūvēts maršrutētājā, kas apstrādā atgriezenisko izvadi kā signālu "iet uz nākamo maršrutu". 
Jūs varat redzēt uzvedību, kas dokumentēta [Maršrutizācijas](/learn/routing#passing) sadaļā.

## Izmaiņu Žurnāls
- v3: Pievienota resursu maršrutizācija, maršruta aliasēšana un plūsmas atbalsts, maršruta grupas un middleware atbalsts.
- v1: Liela daļa pamata funkciju pieejamas.