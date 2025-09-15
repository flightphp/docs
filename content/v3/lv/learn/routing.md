# Maršrutēšana

> **Piezīme:** Vai vēlaties uzzināt vairāk par maršrutēšanu? Apskatiet ["why a framework?"](/learn/why-frameworks) lapu, lai iegūtu padziļinātu skaidrojumu.

Pamata maršrutēšana Flight tiek veikta, saskaņojot URL paraugu ar atsaukuma funkciju vai klases un metodes masīvu.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Maršruti tiek saskaņoti secībā, kādā tie ir definēti. Pirmais maršruts, kas saskaņojas ar pieprasījumu, tiks izsaukts.

### Atsaukumi/Funkcijas
Atsaukums var būt jebkurš aicināms objekts. Tātad jūs varat izmantot regulāru funkciju:

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
// Sveiciena.php
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

#### Atkarību ievadīšana caur DIC (Atkarību ievades konteineru)
Ja vēlaties izmantot atkarību ievadi caur konteineru (PSR-11, PHP-DI, Dice utt.), vienīgais maršrutu veids, kur tas ir pieejams, ir vai nu tieši izveidojot objektu pats un izmantojot konteineru, lai izveidotu savu objektu, vai arī izmantojot virknes, lai definētu klasi un metodi, ko izsaukt. Jūs varat doties uz [Dependency Injection](/learn/extending) lapu, lai iegūtu vairāk informācijas.

Lūk, ātrs piemērs:

```php
use flight\database\PdoWrapper;

// Sveiciena.php
class Greeting
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;
	}

	public function hello(int $id) {
		// izdariet kaut ko ar $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";
	}
}

// index.php

// Izveidojiet konteineru ar nepieciešamajiem parametriem
// Skatiet Atkarību ievades lapu, lai iegūtu vairāk informācijas par PSR-11
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

## Metodes Maršrutēšana

Pēc noklusējuma maršrutu paraugi tiek saskaņoti ar visām pieprasījuma metodēm. Jūs varat reaģēt uz konkrētām metodēm, izvietojot identifikatoru pirms URL.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// Jūs nevarat izmantot Flight::get() maršrutiem, jo tas ir metode 
//    lai iegūtu mainīgos, nevis izveidotu maršrutu.
// Flight::post('/', function() { /* code */ });
// Flight::patch('/', function() { /* code */ });
// Flight::put('/', function() { /* code */ });
// Flight::delete('/', function() { /* code */ });
```

Jūs varat arī kartēt vairākas metodes uz vienu atsaukumu, izmantojot `|` atdalītāju:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

Turklāt jūs varat iegūt Router objektu, kuram ir daži palīgmetodes, ko izmantot:

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
  // Šis saskaņosies ar /user/1234
});
```

Lai gan šī metode ir pieejama, ieteicams izmantot nosauktos parametrus vai nosauktos parametrus ar regulārām izteiksmēm, jo tie ir lasāmāki un vieglāk uzturami.

## Nosauktie parametri

Jūs varat norādīt nosauktos parametrus savos maršrutos, kas tiks nodoti jūsu atsaukuma funkcijai. **Tas vairāk ir maršruta lasāmības dēļ nekā kaut kas cits. Lūdzu, skatiet sadaļu zemāk par svarīgu brīdinājumu.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

Jūs varat arī iekļaut regulārās izteiksmes ar jūsu nosauktajiem parametriem, izmantojot `:` atdalītāju:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Šis saskaņosies ar /bob/123
  // Bet nesaskaņosies ar /bob/12345
});
```

> **Piezīme:** Saskaņošana ar regex grupām `()` pozicionāliem parametriem netiek atbalstīta. :'\(

### Svarīgs brīdinājums

Kaut arī piemērā augstāk šķiet, ka `@name` ir tieši saistīts ar mainīgo `$name`, tas nav. Parametru secība atsaukuma funkcijā nosaka, kas tiek nodots tai. Tātad, ja jūs mainītu parametru secību atsaukuma funkcijā, mainīgie tiktu mainīti arī. Lūk, piemērs:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

Un ja jūs dotos uz šādu URL: `/bob/123`, izvade būtu `hello, 123 (bob)!`. 
Lūdzu, esiet uzmanīgi, kad iestatāt savus maršrutus un atsaukuma funkcijas.

## Izvēles parametri

Jūs varat norādīt nosauktos parametrus, kas ir izvēles saskaņošanai, ietinot segmentus iekavās.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Šis saskaņosies ar šādiem URL:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Jei izvēles parametri, kas nav saskaņoti, tiks nodoti kā `NULL`.

## Visi elementi

Saskaņošana tiek veikta tikai uz atsevišķiem URL segmentiem. Ja vēlaties saskaņot vairākus segmentus, jūs varat izmantot `*` vispārīgo simbolu.

```php
Flight::route('/blog/*', function () {
  // Šis saskaņosies ar /blog/2000/02/01
});
```

Lai maršrutētu visus pieprasījumus uz vienu atsaukumu, jūs varat izdarīt:

```php
Flight::route('*', function () {
  // Izdariet kaut ko
});
```

## Pārnešana

Jūs varat nodot izpildi uz nākamo saskaņojošo maršrutu, atgriežot `true` no jūsu atsaukuma funkcijas.

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

## Maršrutu aliasi

Jūs varat piešķirt aliasu maršrutam, lai URL varētu dinamiski tikt ģenerēts vēlāk jūsu kodā (piemēram, šablonā).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// vēlāk kodā kaut kur
Flight::getUrl('user_view', [ 'id' => 5 ]); // atgriezīs '/users/5'
```

Tas ir īpaši noderīgi, ja jūsu URL gadās mainīties. Piemērā augstāk, pieņemsim, ka lietotāji tika pārvietoti uz `/admin/users/@id` vietā.
Ar aliasiem vietā, jums nav jāmaina nekur, kur jūs atsaucaties uz aliasu, jo alias tagad atgriezīs `/admin/users/5` kā piemērā.

Maršrutu aliasi joprojām darbojas grupās:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// vēlāk kodā kaut kur
Flight::getUrl('user_view', [ 'id' => 5 ]); // atgriezīs '/users/5'
```

## Maršrutu informācija

Ja vēlaties pārbaudīt saskaņoto maršrutu informāciju, ir 2 veidi, kā to izdarīt.
Jūs varat izmantot `executedRoute` īpašumu vai pieprasīt, lai maršruta objekts tiktu nodots jūsu atsaukumam, pievienojot `true` kā trešo parametru maršruta metodē. Maršruta objekts vienmēr būs pēdējais parametrs, kas nodots jūsu atsaukuma funkcijai.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Masīvs ar HTTP metodēm, kas saskaņotas
  $route->methods;

  // Masīvs ar nosauktajiem parametriem
  $route->params;

  // Saskaņotā regulārā izteiksme
  $route->regex;

  // Satur jebkādu '*' saturu, kas izmantots URL paraugā
  $route->splat;

  // Rāda URL ceļu....ja jums tas tiešām vajadzīgs
  $route->pattern;

  // Rāda, kāds starplikums ir piešķirts šim
  $route->middleware;

  // Rāda aliasu, kas piešķirts šim maršrutam
  $route->alias;
}, true);
```

Vai, ja vēlaties pārbaudīt pēdējo izpildīto maršrutu, jūs varat izdarīt:

```php
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;
  // Izdariet kaut ko ar $route
  // Masīvs ar HTTP metodēm, kas saskaņotas
  $route->methods;

  // Masīvs ar nosauktajiem parametriem
  $route->params;

  // Saskaņotā regulārā izteiksme
  $route->regex;

  // Satur jebkādu '*' saturu, kas izmantots URL paraugā
  $route->splat;

  // Rāda URL ceļu....ja jums tas tiešām vajadzīgs
  $route->pattern;

  // Rāda, kāds starplikums ir piešķirts šim
  $route->middleware;

  // Rāda aliasu, kas piešķirts šim maršrutam
  $route->alias;
});
```

> **Piezīme:** `executedRoute` īpašums tiks iestatīts tikai pēc maršruta izpildes. Ja mēģināt piekļūt tam pirms maršruta izpildes, tas būs `NULL`. Jūs varat izmantot executedRoute arī starplikumos!

## Maršrutu grupēšana

Var būt reizes, kad vēlaties grupēt saistītos maršrutus kopā (piemēram, `/api/v1`).
Jūs varat izdarīt to, izmantojot `group` metodi:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Saskaņojas ar /api/v1/users
  });

  Flight::route('/posts', function () {
	// Saskaņojas ar /api/v1/posts
  });
});
```

Jūs pat varat ligzdot grupas:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() iegūst mainīgos, tas nekādē maršrutu! Skatiet objekta kontekstu zemāk
	Flight::route('GET /users', function () {
	  // Saskaņojas ar GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Saskaņojas ar POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Saskaņojas ar PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() iegūst mainīgos, tas nekādē maršrutu! Skatiet objekta kontekstu zemāk
	Flight::route('GET /users', function () {
	  // Saskaņojas ar GET /api/v2/users
	});
  });
});
```

### Grupēšana ar Objekta kontekstu

Jūs joprojām varat izmantot maršrutu grupēšanu ar `Engine` objektu šādā veidā:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // izmantot $router mainīgo
  $router->get('/users', function () {
	// Saskaņojas ar GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Saskaņojas ar POST /api/v1/posts
  });
});
```

### Grupēšana ar Starplikumu

Jūs varat arī piešķirt starplikumu grupai maršrutu:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Saskaņojas ar /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // vai [ new MyAuthMiddleware() ], ja vēlaties izmantot instanci
```

Skatiet vairāk detaļu [group middleware](/learn/middleware#grouping-middleware) lapā.

## Resursu maršrutēšana

Jūs varat izveidot maršrutu kopu resursam, izmantojot `resource` metodi. Tas izveidos maršrutu kopu resursam, kas atbilst RESTful konvencijām.

Lai izveidotu resursu, izdariet sekojošo:

```php
Flight::resource('/users', UsersController::class);
```

Un kas notiks fonā, tas izveidos sekojošos maršrutus:

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

> **Piezīme**: Jūs varat skatīt nesen pievienotos maršrutus ar `runway`, izpildot `php runway routes`.

### Pielāgošana Resursu maršrutiem

Ir daži varianti, lai konfigurētu resursu maršrutus.

#### Alias Bāze

Jūs varat konfigurēt `aliasBase`. Pēc noklusējuma alias ir pēdējā URL daļa, kas norādīta.
Piemēram, `/users/` rezultētos ar `aliasBase` kā `users`. Kad šie maršruti tiek izveidoti, alias ir `users.index`, `users.create` utt. Ja vēlaties mainīt alias, iestatiet `aliasBase` uz vērtību, ko vēlaties.

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

Tie ir balto un melno sarakstu opcijas, lai norādītu, kurus maršrutus vēlaties izveidot.

#### Starplikums

Jūs varat arī norādīt starplikumu, kas jāizpilda katrā no maršrutiem, ko izveido `resource` metode.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## Straumēšana

Jūs tagad varat straumēt atbildes klientam, izmantojot `streamWithHeaders()` metodi. 
Tas ir noderīgi, lai sūtītu lielas failus, ilgstošus procesus vai ģenerētu lielas atbildes. 
Straumēšana maršrutā tiek apstrādāta nedaudz savādāk nekā parastais maršruts.

> **Piezīme:** Straumētas atbildes ir pieejamas tikai, ja jums ir [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) iestatīts uz false.

### Straumēšana ar Manuāliem Headeriem

Jūs varat straumēt atbildi klientam, izmantojot `stream()` metodi maršrutā. Ja jūs to darāt, jums jāiestata visas metodes ar roku pirms kaut ko izvadat klientam.
Tas tiek darīts ar `header()` php funkciju vai `Flight::response()->setRealHeader()` metodi.

```php
Flight::route('/@filename', function($filename) {

	// acīmredzami jūs sanitizētu ceļu un tā tālāk.
	$fileNameSafe = basename($filename);

	// Ja jums ir papildu headeri, ko iestatīt šeit pēc maršruta izpildes
	// jums tie jādefinē pirms kaut kas tiek izvadīts.
	// Tie visi jābūt raw izsaukumam uz header() funkciju vai 
	// izsaukumam uz Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// vai
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$filePath = '/some/path/to/files/'.$fileNameSafe;

	if (!is_readable($filePath)) {
		Flight::halt(404, 'File not found');
	}

	// manuāli iestatiet satura garumu, ja vēlaties
	header('Content-Length: '.filesize($filePath));

	// Straumējiet failu klientam, kamēr tas tiek lasīts
	readfile($filePath);

// Šī ir maģiskā rinda šeit
})->stream();
```

### Straumēšana ar Headeriem

Jūs varat arī izmantot `streamWithHeaders()` metodi, lai iestatītu headerus pirms sākat straumēt.

```php
Flight::route('/stream-users', function() {

	// jūs varat pievienot jebkādus papildu headerus, ko vēlaties šeit
	// jūs vienkārši jāizmanto header() vai Flight::response()->setRealHeader()

	// tomēr, kā jūs velkat datus, tikai kā piemērs...
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

// Šis ir veids, kā jūs iestatīsit headerus pirms sākat straumēt.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// izvēles statusa kods, pēc noklusējuma 200
	'status' => 200
]);
```