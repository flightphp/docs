# Maršrutēšana

## Pārskats
Maršrutēšana Flight PHP kartē URL modeļus uz atsauces funkcijām vai klases metodēm, ļaujot ātri un vienkārši apstrādāt pieprasījumus. Tā ir izstrādāta ar minimālu slodzi, iesācējam draudzīgu lietošanu un paplašināmību bez ārējām atkarībām.

## Saprašana
Maršrutēšana ir galvenais mehānisms, kas savieno HTTP pieprasījumus ar jūsu lietojumprogrammas loģiku Flight. Definējot maršrutus, jūs norīdat, kā dažādi URL izraisa specifisku kodu, vai nu caur funkcijām, klases metodēm vai kontrolera darbībām. Flight maršrutēšanas sistēma ir elastīga, atbalstot pamata modeļus, nosauktus parametrus, regulārās izteiksmes un papildu funkcijas, piemēram, atkarību injekciju un resursu maršrutēšanu. Šī pieeja uztur jūsu kodu organizētu un viegli uzturamu, vienlaikus paliekot ātri un vienkāršai iesācējiem un paplašināmai pieredzējušiem lietotājiem.

> **Piezīme:** Vēlaties saprast vairāk par maršrutēšanu? Apskatiet ["kāpēc ietvars?"](/learn/why-frameworks) lapu plašākam skaidrojumam.

## Pamata lietošana

### Vienkārša maršruta definēšana
Pamata maršrutēšana Flight tiek veikta, saskaņojot URL modeli ar atsauces funkciju vai klases un metodes masīvu.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Maršruti tiek saskaņoti tajā secībā, kādā tie ir definēti. Pirmais maršruts, kas atbilst pieprasījumam, tiks izsaukts.

### Funkciju lietošana kā atsauces
Atsauce var būt jebkurš aizmirsts objekts. Tātad jūs varat izmantot regulāru funkciju:

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### Klases un metodes lietošana kā kontrolieris
Jūs varat izmantot klases metodi (statisku vai nē):

```php
class GreetingController {
    public function hello() {
        echo 'hello world!';
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

Vai arī, vispirms izveidojot objektu un tad izsaucot metodi:

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
        echo "Hello, {$this->name}!";
    }
}

// index.php
$app = Flight::app();
$greeting = new GreetingController($app);

Flight::route('/', [ $greeting, 'hello' ]);
```

> **Piezīme:** Pēc noklusējuma, kad kontrolieris tiek izsaukts ietvarā, `flight\Engine` klase vienmēr tiek injicēta, ja vien jūs neprecizējat caur [atkarību injekcijas konteineru](/learn/dependency-injection-container)

### Metodes specifiska maršrutēšana

Pēc noklusējuma maršrutu modeļi tiek saskaņoti pret visām pieprasījuma metodēm. Jūs varat reaģēt uz specifiskām metodēm, novietojot identifikatoru pirms URL.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// You cannot use Flight::get() for routes as that is a method 
//    to get variables, not create a route.
Flight::post('/', function() { /* code */ });
Flight::patch('/', function() { /* code */ });
Flight::put('/', function() { /* code */ });
Flight::delete('/', function() { /* code */ });
```

Jūs varat arī kartēt vairākas metodes uz vienu atsauces funkciju, izmantojot `|` atdalītāju:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

### Maršrutētāja objekta lietošana

Papildus jūs varat iegūt Maršrutētāja objektu, kuram ir daži palīginstrumenti jums:

```php

$router = Flight::router();

// maps all methods just like Flight::route()
$router->map('/', function() {
	echo 'hello world!';
});

// GET request
$router->get('/users', function() {
	echo 'users';
});
$router->post('/users', 			function() { /* code */});
$router->put('/users/update/@id', 	function() { /* code */});
$router->delete('/users/@id', 		function() { /* code */});
$router->patch('/users/@id', 		function() { /* code */});
```

### Regulārās izteiksmes (Regex)
Jūs varat izmantot regulārās izteiksmes savos maršrutos:

```php
Flight::route('/user/[0-9]+', function () {
  // This will match /user/1234
});
```

Lai gan šī metode ir pieejama, ieteicams izmantot nosauktus parametrus vai nosauktus parametrus ar regulārajām izteiksmēm, jo tie ir lasāmāki un vieglāk uzturami.

### Nosaukti parametri
Jūs varat norādīt nosauktus parametrus savos maršrutos, kas tiks nodoti jūsu atsauces funkcijai. **Tas ir vairāk maršruta lasāmībai nekā jebkas cits. Lūdzu, skatiet sadaļu zemāk par svarīgu brīdinājumu.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

Jūs varat arī iekļaut regulārās izteiksmes ar saviem nosauktajiem parametriem, izmantojot `:` atdalītāju:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // This will match /bob/123
  // But will not match /bob/12345
});
```

> **Piezīme:** Saskaņošana regex grupām `()` ar pozīciju parametriem netiek atbalstīta. Piem.: `:'\(`

#### Svarīgs brīdinājums

Lai gan iepriekšējā piemērā šķiet, ka `@name` ir tieši saistīts ar mainīgo `$name`, tas nav. Parametru secība atsauces funkcijā nosaka, kas tam tiek nodots. Ja jūs mainītu parametru secību atsauces funkcijā, mainīgie tiktu mainīti arī. Šeit ir piemērs:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

Un ja jūs apmeklētu šādu URL: `/bob/123`, izvade būtu `hello, 123 (bob)!`. 
_Lūdzu, esiet uzmanīgi_, kad iestatāt savus maršrutus un atsauces funkcijas!

### Neobligāti parametri
Jūs varat norādīt nosauktus parametrus, kas ir neobligāti saskaņošanai, iekļaujot segmentus iekavās.

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

Jei neobligāti parametri, kas netiek saskaņoti, tiks nodoti kā `NULL`.

### Aizstājējzīmes maršrutēšana
Saskaņošana tiek veikta tikai uz atsevišķiem URL segmentiem. Ja vēlaties saskaņot vairākus segmentus, varat izmantot `*` aizstājējzīmi.

```php
Flight::route('/blog/*', function () {
  // This will match /blog/2000/02/01
});
```

Lai maršrutētu visus pieprasījumus uz vienu atsauces funkciju, varat darīt:

```php
Flight::route('*', function () {
  // Do something
});
```

### 404 Nav atrasts apstrādātājs

Pēc noklusējuma, ja URL nevar tikt atrasts, Flight nosūtīs `HTTP 404 Nav atrasts` atbildi, kas ir ļoti vienkārša un vienkārša.
Ja vēlaties pielāgotu 404 atbildi, varat [kartēt](/learn/extending) savu `notFound` metodi:

```php
Flight::map('notFound', function() {
	$url = Flight::request()->url;

	// You could also use Flight::render() with a custom template.
    $output = <<<HTML
		<h1>My Custom 404 Not Found</h1>
		<h3>The page you have requested {$url} could not be found.</h3>
		HTML;

	$this->response()
		->clearBody()
		->status(404)
		->write($output)
		->send();
});
```

## Papildu lietošana

### Atkarību injekcija maršrutos
Ja vēlaties izmantot atkarību injekciju caur konteineru (PSR-11, PHP-DI, Dice utt.), vienīgais maršrutu veids, kur tas ir pieejams, ir vai nu tieši izveidojot objektu pats un izmantojot konteineru, lai izveidotu savu objektu, vai arī izmantojot virknes, lai definētu klasi un metodi, ko izsaukt. Jūs varat apmeklēt [Atkarību injekcijas](/learn/dependency-injection-container) lapu vairāk informācijai. 

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
		echo "Hello, world! My name is {$name}!";
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

### Izpildes nodošana nākamajam maršrutam
<span class="badge bg-warning">Novecojis</span>
Jūs varat nodot izpildi nākamajam saskaņotajam maršrutam, atgriežot `true` no jūsu atsauces funkcijas.

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

Tagad ieteicams izmantot [vidutīni](/learn/middleware), lai apstrādātu sarežģītus gadījumus kā šis.

### Maršruta segvārds
Piešķirot segvārdu maršrutam, jūs varat vēlāk dinamiski izsaukt šo segvārdu savā lietojumprogrammā, lai tas tiktu ģenerēts vēlāk jūsu kodā (piem.: saite HTML veidnē vai ģenerējot pāradresācijas URL).

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

Tas ir īpaši noderīgi, ja jūsu URL gadās mainīties. Iepriekšējā piemērā pieņemsim, ka lietotāji tika pārvietoti uz `/admin/users/@id` vietā.
Ar segvārdu vietā maršrutam jums vairs nav jāmeklē visi vecie URL jūsu kodā un jāmaina tie, jo segvārds tagad atgriezīs `/admin/users/5`, kā piemērā iepriekš.

Maršruta segvārdi joprojām darbojas grupās:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
	// or
	Flight::route('/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');
});
```

### Maršruta informācijas pārbaude
Ja vēlaties pārbaudīt saskaņoto maršruta informāciju, ir 2 veidi, kā to izdarīt:

1. Jūs varat izmantot `executedRoute` īpašību uz `Flight::router()` objekta.
2. Jūs varat pieprasīt, lai maršruta objekts tiktu nodots jūsu atsauces funkcijai, nododot `true` kā trešo parametru maršruta metodē. Maršruta objekts vienmēr būs pēdējais parametrs, kas nodots jūsu atsauces funkcijai.

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

> **Piezīme:** `executedRoute` īpašība tiks iestatīta tikai pēc tam, kad maršruts ir izpildīts. Ja mēģināsiet tam piekļūt pirms maršruta izpildes, tas būs `NULL`. Jūs varat izmantot executedRoute arī [vidutīnos](/learn/middleware)!

#### Ievadīt `true` maršruta definīcijā
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

### Maršrutu grupēšana un vidutīni
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

Jūs varat pat ligzdot grupu grupas:

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

#### Grupēšana ar objekta kontekstu

Jūs joprojām varat izmantot maršrutu grupēšanu ar `Engine` objektu šādā veidā:

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

#### Grupēšana ar vidutīniem

Jūs varat piešķirt vidutīnus grupai maršrutu:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Matches /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // or [ new MyAuthMiddleware() ] if you want to use an instance
```

Skatiet vairāk detaļu [grupas vidutīnos](/learn/middleware#grouping-middleware) lapā.

### Resursu maršrutēšana
Jūs varat izveidot maršrutu kopu resursam, izmantojot `resource` metodi. Tas izveidos maršrutu kopu resursam, kas seko RESTful konvencijām.

Lai izveidotu resursu, dariet šādi:

```php
Flight::resource('/users', UsersController::class);
```

Un fonā tas izveidos šādus maršrutus:

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

#### Pielāgošana resursu maršrutiem

Ir dažas opcijas resursu maršrutu konfigurēšanai.

##### Alias bāze

Jūs varat konfigurēt `aliasBase`. Pēc noklusējuma alias ir pēdējā URL daļa, kas norādīta.
Piemēram, `/users/` rezultētos `aliasBase` kā `users`. Kad šie maršruti tiek izveidoti, alias ir `users.index`, `users.create` utt. Ja vēlaties mainīt alias, iestatiet `aliasBase` uz vēlamo vērtību.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

##### Tikai un Izņemot

Jūs varat norādīt, kurus maršrutus vēlaties izveidot, izmantojot `only` un `except` opcijas.

```php
// Whitelist only these methods and blacklist the rest
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
// Blacklist only these methods and whitelist the rest
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Šie ir balstīti uz balto sarakstu un melno sarakstu opcijām, lai norādītu, kurus maršrutus vēlaties izveidot.

##### Vidutīni

Jūs varat norādīt vidutīnus, kas jāizpilda katram no `resource` metodes izveidotajiem maršrutiem.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

### Plūsmas atbildes

Jūs tagad varat plūst atbildes klientam, izmantojot `stream()` vai `streamWithHeaders()`. 
Tas ir noderīgi lielu failu nosūtīšanai, garām procesiem vai lielu atbilžu ģenerēšanai. 
Maršruta plūsmošana tiek apstrādāta nedaudz savādāk nekā regulārs maršruts.

> **Piezīme:** Plūsmas atbildes ir pieejamas tikai tad, ja jums ir [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) iestatīts uz `false`.

#### Plūsma ar manuāliem galvenes

Jūs varat plūst atbildi klientam, izmantojot `stream()` metodi maršrutā. Ja dariet to, jums jāiestata visi galvenes ar roku, pirms izvadat jebko klientam.
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

#### Plūsma ar galvenēm

Jūs varat izmantot `streamWithHeaders()` metodi, lai iestatītu galvenes, pirms sākat plūst.

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

## Skatīt arī
- [Vidutīni](/learn/middleware) - Vidutīņu lietošana ar maršrutiem autentifikācijai, žurnālveidošanai utt.
- [Atkarību injekcija](/learn/dependency-injection-container) - Objektu izveides un pārvaldības vienkāršošana maršrutos.
- [Kāpēc ietvars?](/learn/why-frameworks) - Ietvara kā Flight izmantošanas priekšrocību saprašana.
- [Paplašināšana](/learn/extending) - Kā paplašināt Flight ar savu funkcionalitāti, ieskaitot `notFound` metodi.
- [php.net: preg_match](https://www.php.net/manual/en/function.preg-match.php) - PHP funkcija regulāro izteiksmju saskaņošanai.

## Traucējummeklēšana
- Maršruta parametri tiek saskaņoti pēc secības, nevis nosaukuma. Pārliecinieties, ka atsauces parametru secība atbilst maršruta definīcijai.
- `Flight::get()` lietošana nedefinē maršrutu; izmantojiet `Flight::route('GET /...')` maršrutēšanai vai Maršrutētāja objekta kontekstu grupās (piem. `$router->get(...)`).
- executedRoute īpašība tiek iestatīta tikai pēc maršruta izpildes; tā ir NULL pirms izpildes.
- Plūsmošanai ir jāatspējo mantojamā Flight izvades buferizācijas funkcionalitāte (`flight.v2.output_buffering = false`).
- Atkarību injekcijai tikai noteiktas maršruta definīcijas atbalsta konteineru balstītu instantiāciju.

### 404 Nav atrasts vai negaidīta maršruta uzvedība

Ja redzat 404 Nav atrasts kļūdu (bet esat pārliecināts uz savu dzīvi, ka tas tiešām ir tur un tas nav drukas kļūda), tas patiesībā varētu būt problēma ar jūsu atgriežamo vērtību jūsu maršruta galapunktā, nevis tikai izvadi. Iemesls tam ir tīšs, bet var uzbrukt dažiem izstrādātājiem.

```php

Flight::route('/hello', function(){
	// This might cause a 404 Not Found error
	return 'Hello World';
});

// What you probably want
Flight::route('/hello', function(){
	echo 'Hello World';
});

```

Iemesls tam ir īpašs mehānisms, kas iebūvēts maršrutētājā, kas apstrādā atgriežamo izvadi kā signālu "iet uz nākamo maršrutu". 
Jūs varat redzēt uzvedību, kas dokumentēta [Maršrutēšanas](/learn/routing#passing) sadaļā.

## Izmaiņu žurnāls
- v3: Pievienota resursu maršrutēšana, maršruta segvārdi un plūsmas atbalsts, maršruta grupas un vidutīņu atbalsts.
- v1: Liela daļa pamata funkciju pieejama.