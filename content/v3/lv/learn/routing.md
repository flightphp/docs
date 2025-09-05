# Maršrutizēšana

> **Piezīme:** Vēlaties uzzināt vairāk par maršrutizēšanu? Apskatiet lapu ["why a framework?"](/learn/why-frameworks) plašākam paskaidrojumam.

Pamata maršrutizēšana Flight tiek veikta, saskaņojot URL paraugu ar atsaukuma funkciju vai klases un metodes masīvu.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
// Piezīme: Maršruti tiek saskaņoti secībā, kādā tie ir definēti. Pirmais maršruts, kas atbilst pieprasījumam, tiks izsaukts.
```

### Atsaukuma funkcijas/Funkcijas
Atsaukuma funkcija var būt jebkurš izsaucams objekts. Tātad jūs varat izmantot parastu funkciju:

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### Klases
Jūs varat izmantot arī klases statisko metodi:

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

Vai izveidojot objektu vispirms un pēc tam izsaucot metodi:

```php
// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hello, {$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// Jūs varat izdarīt to arī bez objekta izveidošanas vispirms
// Piezīme: Argumenti netiks ievadīti konstruktorā
Flight::route('/', [ 'Greeting', 'hello' ]);
// Turklāt jūs varat izmantot šo īsāko sintaksi
Flight::route('/', 'Greeting->hello');
// vai
Flight::route('/', Greeting::class.'->hello');
```

#### Atkarību ievadīšana caur DIC (Atkarību ievadīšanas konteineru)
Ja vēlaties izmantot atkarību ievadīšanu caur konteineru (PSR-11, PHP-DI, Dice utt.), tas ir pieejams tikai maršrutiem, kur jūs tieši izveidojat objektu pats vai izmantojat virknes, lai definētu klasi un metodi, ko izsaukt. Plašāku informāciju skatiet lapā [Dependency Injection](/learn/extending).

Lūk, ātrs piemērs:

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
		// Izpildiet kaut ko ar $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";
	}
}

// index.php

// Iestatiet konteineru ar nepieciešamajiem parametriem
// Skatiet Atkarību ievadīšanas lapu vairāk informācijas par PSR-11
$dice = new \Dice\Dice();

// Neaizmirstiet pārrakstīt mainīgo ar '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Reģistrējiet konteinera apstrādātāju
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Maršruti kā parasti
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// vai
Flight::route('/hello/@id', 'Greeting->hello');
// vai
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## Metodes maršrutizēšana

Pēc noklusējuma maršrutu paraugi tiek saskaņoti ar visiem pieprasījumu metodēm. Jūs varat atbildēt uz konkrētām metodēm, izvietojot identifikatoru pirms URL.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// Jūs nevarat izmantot Flight::get() maršrutiem, jo tas ir metode, lai iegūtu mainīgos, nevis izveidotu maršrutu.
// Flight::post('/', function() { /* code */ });
// Flight::patch('/', function() { /* code */ });
// Flight::put('/', function() { /* code */ });
// Flight::delete('/', function() { /* code */ });
```

Jūs varat arī kartēt vairākas metodes uz vienu atsaukuma funkciju, izmantojot `|` atdalītāju:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

Turklāt jūs varat iegūt Router objektu, kuram ir daži palīgmetodes jūsu izmantošanai:

```php
$router = Flight::router();

// kartē visas metodes
$router->map('/', function() {
	echo 'hello world!';
});

// GET pieprasījums
$router->get('/users', function() {
	echo 'users';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## Regulārās izteiksmes

Jūs varat izmantot regulārās izteiksmes savos maršrutos:

```php
Flight::route('/user/[0-9]+', function () {
  // Šis atbildīs /user/1234
});
```

Lai gan šī metode ir pieejama, ieteicams izmantot nosauktos parametrus vai nosauktos parametrus ar regulārajām izteiksmēm, jo tie ir lasāmāki un vieglāk uzturami.

## Nosauktie parametri

Jūs varat norādīt nosauktos parametrus savos maršrutos, kuri tiks nodoti jūsu atsaukuma funkcijai. **Tas galvenokārt ir maršruta lasāmības dēļ. Lūdzu, skatiet sadaļu zemāk par svarīgu brīdinājumu.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

Jūs varat arī iekļaut regulārās izteiksmes ar saviem nosauktajiem parametriem, izmantojot `:` atdalītāju:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Šis atbildīs /bob/123
  // Bet neatbildīs /bob/12345
});
```

> **Piezīme:** Saskaņošana ar regex grupām `()` pozicionāliem parametriem netiek atbalstīta. :'\(

### Svarīgs brīdinājums

Kaut arī piemērā augstāk šķiet, ka `@name` ir tieši saistīts ar mainīgo `$name`, tas nav. Parametru secība atsaukuma funkcijā nosaka, kas tam tiek nodots. Tātad, ja jūs mainītu parametru secību atsaukuma funkcijā, mainīgie tiktu mainīti. Lūk, piemērs:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

Un, ja jūs apmeklētu šādu URL: `/bob/123`, izvade būtu `hello, 123 (bob)!`. 
Lūdzu, esiet uzmanīgi, iestatot savus maršrutus un atsaukuma funkcijas.

## Izvēles parametri

Jūs varat norādīt nosauktos parametrus, kas ir izvēles saskaņošanai, ietinot segmentus iekavās.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Šis atbildēs šādiem URL:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Visi izvēles parametri, kas netiek saskaņoti, tiks nodoti kā `NULL`.

## Universālie simboli

Saskaņošana tiek veikta tikai uz atsevišķiem URL segmentiem. Ja vēlaties saskaņot vairākus segmentus, jūs varat izmantot `*` universālo simbolu.

```php
Flight::route('/blog/*', function () {
  // Šis atbildēs /blog/2000/02/01
});
```

Lai maršrutizētu visus pieprasījumus uz vienu atsaukuma funkciju, jūs varat izdarīt:

```php
Flight::route('*', function () {
  // Izpildiet kaut ko
});
```

## Nododšana

Jūs varat nodot izpildi uz nākamo saskaņoto maršrutu, atgriežot `true` no jūsu atsaukuma funkcijas.

```php
Flight::route('/user/@name', function (string $name) {
  // Pārbaudiet kādu nosacījumu
  if ($name !== "Bob") {
    // Turpiniet uz nākamo maršrutu
    return true;
  }
});

Flight::route('/user/*', function () {
  // Šis tiks izsaukts
});
```

## Maršruta aizvietojums

Jūs varat piešķirt aizvietotāju maršrutam, lai URL varētu dinamiski tikt ģenerēts vēlāk jūsu kodā (piemēram, veidnē).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// vēlāk kodā kaut kur
Flight::getUrl('user_view', [ 'id' => 5 ]); // atgriezīs '/users/5'
```

Tas ir īpaši noderīgi, ja jūsu URL gadās mainīties. Piemērā augstāk, pieņemsim, ka lietotāji tika pārvietoti uz `/admin/users/@id` vietā.
Ar aizvietojumu vietā, jums nav jāmaina nekur, kur jūs atsaucaties uz aizvietotāju, jo aizvietotājs tagad atgriezīs `/admin/users/5`, kā piemērā.

Maršruta aizvietojums joprojām darbojas grupās:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// vēlāk kodā kaut kur
Flight::getUrl('user_view', [ 'id' => 5 ]); // atgriezīs '/users/5'
```

## Maršruta informācija

Ja vēlaties pārbaudīt saskaņoto maršruta informāciju, ir 2 veidi, kā to izdarīt.
Jūs varat izmantot `executedRoute` īpašību vai pieprasīt, lai maršruta objekts tiktu nodots jūsu atsaukuma funkcijai, pievienojot `true` kā trešo parametru maršruta metodē. Maršruta objekts vienmēr būs pēdējais parametrs, kas nodots jūsu atsaukuma funkcijai.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Masīvs ar HTTP metodēm, kas saskaņotas
  $route->methods;

  // Masīvs ar nosauktajiem parametriem
  $route->params;

  // Saskaņotā regulārā izteiksme
  $route->regex;

  // satur jebkādu '*' saturu, kas izmantots URL paraugā
  $route->splat;

  // Rāda URL ceļu....ja jums tas tiešām vajadzīgs
  $route->pattern;

  // Rāda, kāds vidware ir piešķirts šim
  $route->middleware;

  // Rāda aizvietotāju, kas piešķirts šim maršrutam
  $route->alias;
}, true);
```

Vai, ja vēlaties pārbaudīt pēdējo izpildīto maršrutu, jūs varat izdarīt:

```php
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;
  // Izpildiet kaut ko ar $route
  // Masīvs ar HTTP metodēm, kas saskaņotas
  $route->methods;

  // Masīvs ar nosauktajiem parametriem
  $route->params;

  // Saskaņotā regulārā izteiksme
  $route->regex;

  // satur jebkādu '*' saturu, kas izmantots URL paraugā
  $route->splat;

  // Rāda URL ceļu....ja jums tas tiešām vajadzīgs
  $route->pattern;

  // Rāda, kāds vidware ir piešķirts šim
  $route->middleware;

  // Rāda aizvietotāju, kas piešķirts šim maršrutam
  $route->alias;
});
```

> **Piezīme:** `executedRoute` īpašība tiks iestatīta tikai pēc maršruta izpildes. Ja mēģināt piekļūt tam pirms maršruta izpildes, tas būs `NULL`. Jūs varat arī izmantot executedRoute vidware!

## Maršruta grupēšana

Var būt reizes, kad vēlaties grupēt saistītos maršrutus kopā (piemēram, `/api/v1`).
Jūs varat to izdarīt, izmantojot `group` metodi:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Atbildīs /api/v1/users
  });

  Flight::route('/posts', function () {
	// Atbildīs /api/v1/posts
  });
});
```

Jūs pat varat ligzdot grupas:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() iegūst mainīgos, tas nekādē maršrutu! Skatiet objekta kontekstu zemāk
	Flight::route('GET /users', function () {
	  // Atbildīs GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Atbildīs POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Atbildīs PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() iegūst mainīgos, tas nekādē maršrutu! Skatiet objekta kontekstu zemāk
	Flight::route('GET /users', function () {
	  // Atbildīs GET /api/v2/users
	});
  });
});
```

### Grupēšana ar Objekta kontekstu

Jūs joprojām varat izmantot maršruta grupēšanu ar `Engine` objektu šādā veidā:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // izmantot $router mainīgo
  $router->get('/users', function () {
	// Atbildīs GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Atbildīs POST /api/v1/posts
  });
});
```

### Grupēšana ar Vidware

Jūs varat arī piešķirt vidware grupai maršrutu:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Atbildīs /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // vai [ new MyAuthMiddleware() ], ja vēlaties izmantot instanci
```

Skatiet vairāk detaļu lapā [group middleware](/learn/middleware#grouping-middleware).

## Resursu maršrutizēšana

Jūs varat izveidot maršrutu kopu resursam, izmantojot `resource` metodi. Tas izveidos maršrutu kopu resursam, kas atbilst RESTful konvencijām.

Lai izveidotu resursu, izdariet šādi:

```php
Flight::resource('/users', UsersController::class);
```

Un fonā notiks šādu maršrutu izveide:

```php
[
      'index' => 'GET ',
      'create' => 'GET /create',
      'store' => 'POST ',
      'show' => 'GET /@id',
      'edit' => 'GET /@id/edit',
      'update' => 'PUT /@id',
      'destroy' => 'DELETE /@id'
]
```

Un jūsu kontrolieris izskatīsies šādi:

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

> **Piezīme**: Jūs varat skatīt jaunpievienotos maršrutus ar `runway`, izpildot `php runway routes`.

### Pielāgošana Resursu Maršrutiem

Ir dažas opcijas, lai konfigurētu resursu maršrutus.

#### Alias Bāze

Jūs varat konfigurēt `aliasBase`. Pēc noklusējuma aizvietotājs ir pēdējā daļa no norādītā URL.
Piemēram, `/users/` rezultātā būtu `aliasBase` kā `users`. Kad šie maršruti tiek izveidoti, aizvietotāji ir `users.index`, `users.create` utt. Ja vēlaties mainīt aizvietotāju, iestatiet `aliasBase` uz vēlamo vērtību.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Tikai un Izņemot

Jūs varat arī norādīt, kurus maršrutus vēlaties izveidot, izmantojot `only` un `except` opcijas.

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Tie ir balto sarakstu un melno sarakstu opcijas, lai jūs varētu norādīt, kurus maršrutus vēlaties izveidot.

#### Vidware

Jūs varat arī norādīt vidware, kas jāizpilda katram no maršrutiem, ko izveido `resource` metode.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## Straumēšana

Tagad jūs varat straumēt atbildes klientam, izmantojot `streamWithHeaders()` metodi. 
Tas ir noderīgi, lai sūtītu lielas failus, ilgstošus procesus vai ģenerētu lielas atbildes. 
Straumēšana maršrutā tiek apstrādāta nedaudz savādāk nekā parasts maršruts.

> **Piezīme:** Straumēšanas atbildes ir pieejamas tikai, ja jums ir [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) iestatīts uz false.

### Straumēšana ar Manuāliem Headeriem

Jūs varat straumēt atbildi klientam, izmantojot `stream()` metodi maršrutā. Ja jūs to darāt, jums ir jāiestata visas metodes ar roku pirms kaut ko izvadat klientam.
Tas tiek darīts ar `header()` PHP funkciju vai `Flight::response()->setRealHeader()` metodi.

```php
Flight::route('/@filename', function($filename) {

	// acīmredzami jūs sanitizētu ceļu un tā tālāk.
	$fileNameSafe = basename($filename);

	// Ja jums ir papildu headeri, ko iestatīt šeit pēc maršruta izpildes
	// jums tie jādefinē pirms kaut kas tiek echo.
	// Tie visi ir jāizmanto kā raw call uz header() funkciju vai 
	// call uz Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// vai
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// Kļūdu noķeršana un tā tālāk
	if(empty($fileData)) {
		Flight::halt(404, 'File not found');
	}

	// manuāli iestatiet satura garumu, ja vēlaties
	header('Content-Length: '.filesize($filename));

	// Straumējiet datus klientam
	echo $fileData;

// Šī ir maģiskā rindiņa šeit
})->stream();
```

### Straumēšana ar Headeriem

Jūs varat arī izmantot `streamWithHeaders()` metodi, lai iestatītu headerus pirms straumēšanas sākuma.

```php
Flight::route('/stream-users', function() {

	// jūs varat pievienot jebkādus papildu headerus, ko vēlaties šeit
	// jūs vienkārši jāizmanto header() vai Flight::response()->setRealHeader()

	// tomēr jūs iegūstat savus datus, tikai kā piemērs...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// Tas ir nepieciešams, lai nosūtītu datus klientam
		ob_flush();
	}
	echo '}';

// Šis ir veids, kā iestatīt headerus pirms straumēšanas sākuma.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// opcional status kods, pēc noklusējuma 200
	'status' => 200
]);
```