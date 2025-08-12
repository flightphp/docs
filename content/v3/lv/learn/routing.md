# Maršrutēšana

> **Piezīme:** Vai vēlaties uzzināt vairāk par maršrutēšanu? Apskatiet lapu ["why a framework?"](/learn/why-frameworks) plašākam paskaidrojumam.

Pamata maršrutēšana Flight tiek veikta, saskaņojot URL paraugu ar atsaukuma funkciju vai klases un metodes masīvu.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Maršruti tiek saskaņoti secībā, kādā tie ir definēti. Pirmais maršruts, kas atbilst pieprasījumam, tiks izsaukts.

### Atsaukumi/Funkcijas
Atsaukums var būt jebkurš objekts, kas ir izsaucams. Tātad jūs varat izmantot regulāru funkciju:

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

Vai arī vispirms izveidojot objektu un pēc tam izsaucot metodi:

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

#### Atkarību ievadīšana, izmantojot DIC (Atkarību ievadīšanas konteineru)
Ja vēlaties izmantot atkarību ievadīšanu, izmantojot konteineru (PSR-11, PHP-DI, Dice utt.), tad tas ir pieejams tikai maršrutiem, kurus izveidojat tieši paši vai izmantojot virknes, lai definētu klasi un metodi. Plašāku informāciju skatiet [Dependency Injection](/learn/extending) lapā.

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
		// izdariet kaut ko ar $this->pdoWrapper
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

## Metodes Maršrutēšana

Pēc noklusējuma maršrutu paraugi tiek saskaņoti ar visām pieprasījumu metodēm. Jūs varat atbildēt uz konkrētām metodēm, norādot identifikatoru pirms URL.

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

Jūs varat arī kartēt vairākas metodes uz vienu atsaukumu, izmantojot `|` atdalītāju:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

Turklāt jūs varat iegūt Router objektu, kuram ir daži palīgrīki, ko izmantot:

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
  // Tas atbildīs /user/1234
});
```

Kaut arī šī metode ir pieejama, ieteicams izmantot nosauktos parametrus vai nosauktos parametrus ar regulārām izteiksmēm, jo tie ir vieglāk lasāmi un uzturami.

## Nosauktie parametri

Jūs varat norādīt nosauktos parametrus savos maršrutos, kas tiks nodoti jūsu atsaukuma funkcijai. **Tas vairāk ir maršruta lasāmības dēļ. Lūdzu, skatiet sadaļu zemāk par svarīgu brīdinājumu.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

Jūs varat arī iekļaut regulārās izteiksmes ar nosauktajiem parametriem, izmantojot `:` atdalītāju:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Tas atbildīs /bob/123
  // Bet neatbildīs /bob/12345
});
```

> **Piezīme:** Atbalsts saskaņotu regex grupu `()` ar pozicionāliem parametriem nav pieejams. :'\(

### Svarīgs brīdinājums

Kaut arī piemērā augstāk šķiet, ka `@name` ir tieši saistīts ar mainīgo `$name`, tas nav. Parametru secība atsaukuma funkciijā nosaka, kas tiek nodots tai. Tātad, ja jūs mainītu parametru secību atsaukuma funkciijā, mainīgie arī tiktu mainīti. Šeit ir piemērs:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

Un, ja jūs apmeklētu šo URL: `/bob/123`, izeja būtu `hello, 123 (bob)!`. Lūdzu, esiet uzmanīgi, iestatot savus maršrutus un atsaukuma funkcijas.

## Izvēles parametri

Jūs varat norādīt nosauktos parametrus, kas ir izvēles saskaņošanai, ietinot segmentus iekavās.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Tas atbildēs šādiem URL:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Jebkuri izvēles parametri, kas netiek saskaņoti, tiks nodoti kā `NULL`.

## Wildcards

Saskaņošana tiek veikta tikai uz atsevišķiem URL segmentiem. Ja vēlaties saskaņot vairākus segmentus, varat izmantot `*` wildcard.

```php
Flight::route('/blog/*', function () {
  // Tas atbildēs /blog/2000/02/01
});
```

Lai maršrutētu visus pieprasījumus uz vienu atsaukumu, varat izdarīt:

```php
Flight::route('*', function () {
  // Izpildiet kaut ko
});
```

## Nododšana

Jūs varat nodot izpildi uz nākamo saskaņoto maršrutu, atgriežot `true` no sava atsaukuma funkcijas.

```php
Flight::route('/user/@name', function (string $name) {
  // Pārbaudiet kādu nosacījumu
  if ($name !== "Bob") {
    // Turpiniet uz nākamo maršrutu
    return true;
  }
});

Flight::route('/user/*', function () {
  // Tas tiks izsaukts
});
```

## Maršruta aliasing

Jūs varat piešķirt alias maršrutam, lai URL varētu dinamiski tikt ģenerēts vēlāk jūsu kodā (piemēram, veidnē).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// vēlāk kodā kaut kur
Flight::getUrl('user_view', [ 'id' => 5 ]); // atgriezīs '/users/5'
```

Tas ir īpaši noderīgi, ja jūsu URL gadās mainīties. Piemērā augstāk, pieņemsim, ka lietotāji tika pārvietoti uz `/admin/users/@id` vietā.
Ar aliasing vietā, jums nav jāmaina nekur, kur atsaucieties uz alias, jo alias tagad atgriezīs `/admin/users/5` kā piemērā.

Maršruta aliasing joprojām darbojas grupās:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// vēlāk kodā kaut kur
Flight::getUrl('user_view', [ 'id' => 5 ]); // atgriezīs '/users/5'
```

## Maršruta informācija

Ja vēlaties pārbaudīt saskaņoto maršruta informāciju, jūs varat pieprasīt, lai maršruta objekts tiktu nodots jūsu atsaukumam, pievienojot `true` kā trešo parametru maršruta metodē. Maršruta objekts vienmēr būs pēdējais parametrs, kas nodots jūsu atsaukuma funkcijai.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Masīvs ar HTTP metodēm, kas saskaņotas
  $route->methods;

  // Masīvs ar nosauktajiem parametriem
  $route->params;

  // Saskaņotā regulārā izteiksme
  $route->regex;

  // Satur saturu no jebkuras '*' izmantotas URL paraugā
  $route->splat;

  // Rāda URL ceļu....ja jums tas tiešām vajadzīgs
  $route->pattern;

  // Rāda, kāds middleware ir piešķirts šim
  $route->middleware;

  // Rāda alias, kas piešķirts šim maršrutam
  $route->alias;
}, true);
```

## Maršruta grupēšana

Var būt gadījumi, kad vēlaties grupēt saistītos maršrutus kopā (piemēram, `/api/v1`).
Jūs varat izdarīt to, izmantojot `group` metodi:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Atbild /api/v1/users
  });

  Flight::route('/posts', function () {
	// Atbild /api/v1/posts
  });
});
```

Jūs pat varat ligzdot grupas grupās:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() iegūst mainīgos, tas nekurs izveido maršrutu! Skatiet objektu kontekstu zemāk
	Flight::route('GET /users', function () {
	  // Atbild GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Atbild POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Atbild PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() iegūst mainīgos, tas nekurs izveido maršrutu! Skatiet objektu kontekstu zemāk
	Flight::route('GET /users', function () {
	  // Atbild GET /api/v2/users
	});
  });
});
```

### Grupēšana ar Objektu Kontekstu

Jūs joprojām varat izmantot maršruta grupēšanu ar `Engine` objektu šādā veidā:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // izmantot $router mainīgo
  $router->get('/users', function () {
	// Atbild GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Atbild POST /api/v1/posts
  });
});
```

### Grupēšana ar Middleware

Jūs varat arī piešķirt middleware grupai maršrutu:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Atbild /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // vai [ new MyAuthMiddleware() ], ja vēlaties izmantot instanci
```

Skatiet vairāk detaļu [group middleware](/learn/middleware#grouping-middleware) lapā.

## Resursu Maršrutēšana

Jūs varat izveidot maršrutu kopu resursam, izmantojot `resource` metodi. Tas izveidos maršrutu kopu resursam, kas atbilst RESTful konvencijām.

Lai izveidotu resursu, izdariet šādi:

```php
Flight::resource('/users', UsersController::class);
```

Un fonā tas izveidos šādus maršrutus:

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

### Pielāgošana Resursu Maršrutiem

Ir daži iestatījumi, lai konfigurētu resursu maršrutus.

#### Alias Bāze

Jūs varat konfigurēt `aliasBase`. Pēc noklusējuma alias ir pēdējā URL daļa.
Piemēram, `/users/` rezultātā būtu `aliasBase` kā `users`. Kad šie maršruti tiek izveidoti,
alias ir `users.index`, `users.create` utt. Ja vēlaties mainīt alias, iestatiet `aliasBase`
uz vēlamo vērtību.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Tikai un Izņemot

Jūs varat norādīt, kurus maršrutus vēlaties izveidot, izmantojot `only` un `except` iestatījumus.

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Tie ir balstīti uz balto un melno sarakstu, lai norādītu, kurus maršrutus vēlaties izveidot.

#### Middleware

Jūs varat norādīt middleware, kas jāizpilda katram no maršrutiem, ko izveido `resource` metode.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## Streaming

Tagad jūs varat straumēt atbildes klientam, izmantojot `streamWithHeaders()` metodi. 
Tas ir noderīgi, lai sūtītu lielas failus, ilgstošus procesus vai ģenerētu lielas atbildes. 
Straumēšana maršrutā tiek apstrādāta nedaudz savādāk nekā regulārs maršruts.

> **Piezīme:** Straumēšanas atbildes ir pieejamas tikai, ja jums ir [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) iestatīts uz false.

### Straumēšana ar Manuāliem Headeriem

Jūs varat straumēt atbildi klientam, izmantojot `stream()` metodi uz maršrutu. Ja jūs to izdarāt, jums ir jāiestata visi headeri ar roku pirms kaut ko izvadat klientam.
Tas tiek darīts ar `header()` PHP funkciju vai `Flight::response()->setRealHeader()` metodi.

```php
Flight::route('/@filename', function($filename) {

	// acīmredzami jūs sanitizētu ceļu un tā tālāk.
	$fileNameSafe = basename($filename);

	// Ja jums ir papildu headeri, ko iestatīt šeit pēc maršruta izpildes
	// jums tie jādefinē pirms kaut kas tiek echoed.
	// Tie visi jāsauc kā raw call uz header() funkciju vai
	// call uz Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// vai
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// Kļūdu ķeršana un tā tālāk
	if(empty($fileData)) {
		Flight::halt(404, 'File not found');
	}

	// manuāli iestatiet content length, ja vēlaties
	header('Content-Length: '.filesize($filename));

	// Straumējiet datus klientam
	echo $fileData;

// Šī ir burvju rindiņa šeit
})->stream();
```

### Straumēšana ar Headeriem

Jūs varat arī izmantot `streamWithHeaders()` metodi, lai iestatītu headerus pirms straumēšanas sākuma.

```php
Flight::route('/stream-users', function() {

	// jūs varat pievienot jebkādus papildu headerus šeit
	// jūs vienkārši izmantojiet header() vai Flight::response()->setRealHeader()

	// tomēr jūs iegūstat datus, tikai kā piemērs...
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
	// izvēles status kods, pēc noklusējuma 200
	'status' => 200
]);
```