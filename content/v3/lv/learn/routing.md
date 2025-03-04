# Maršrutizācija

> **Piezīme:** Vēlaties labāk izprast maršrutizāciju? Apmeklējiet ["kāpēc ietvars?"](/learn/why-frameworks) lapu, lai iegūtu padziļinātu skaidrojumu.

Vienkārša maršrutizācija Flight ietvarā tiek veikta, salīdzinot URL paraugu ar atgriešanas funkciju vai klases un metodes masīvu.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Maršruti tiek salīdzināti to definēšanas secībā. Pirmais maršruts, kas atbilst pieprasījumam, tiks izsaukts.

### Atgriezeniskās saites/Funkcijas
Atgriezeniskā funkcija var būt jebkurš izsaucams objekts. Tādējādi varat izmantot parastu funkciju:

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### Klases
Jūs varat izmantot arī statisko metodi no klases:

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

Vai arī, vispirms izveidojot objektu un pēc tam izsaucot metodi:

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
// Jūs varat to darīt arī bez objekta izveides iepriekš
// Piezīme: Nav argumentu, kas tiks ievietoti konstruktora iekšpusē
Flight::route('/', [ 'Greeting', 'hello' ]);
// Turklāt jūs varat izmantot šo īsāko sintaksi
Flight::route('/', 'Greeting->hello');
// vai
Flight::route('/', Greeting::class.'->hello');
```

#### Atkarību injekcija, izmantojot DIC (Atkarību injekcijas konteineru)
Ja vēlaties izmantot atkarību injekciju, izmantojot konteineru (PSR-11, PHP-DI, Dice utt.), vienīgais maršrutu veids, kur tas ir pieejams, ir vai nu tieši izveidot objektu paši un izmantot konteineru, lai izveidotu savu objektu, vai izmantot virknes, lai definētu klasi un metodi, kuru izsaukt. Jūs varat doties uz [Atkarību injekcijas](/learn/extending) lapu, lai iegūtu vairāk informācijas.

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
		// veic kaut ko ar $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";
	}
}

// index.php

// Iestatiet konteineru ar visiem parametriem, kas jums nepieciešami
// Skatiet Atkarību injekcijas lapu, lai iegūtu vairāk informācijas par PSR-11
$dice = new \Dice\Dice();

// Neaizmirstiet pārdēvēt mainīgo ar '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Reģistrējiet konteineru
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

## Metodes maršrutizācija

Noklusējuma iestatījumos maršrutu paraugi tiek salīdzināti ar visām pieprasījumu metodēm. Jūs varat reaģēt uz konkrētām metodēm, novietojot identifikatoru pirms URL.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// Jūs nevarat izmantot Flight::get() maršrutiem, jo tas ir metode 
//    mainīgo iegūšanai, nevis maršruta izveidei.
// Flight::post('/', function() { /* code */ });
// Flight::patch('/', function() { /* code */ });
// Flight::put('/', function() { /* code */ });
// Flight::delete('/', function() { /* code */ });
```

Jūs varat arī mapēt vairākas metodes uz vienu atgriezenisko funkciju, izmantojot `|` atdalītāju:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

Turklāt jūs varat iegūt Router objektu, kuram ir daži palīgmetodes, kuras varat izmantot:

```php

$router = Flight::router();

// mapē visas metodes
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
  // Šis atbilst /user/1234
});
```

Lai gan šī metode ir pieejama, ieteicams izmantot nosauktus parametrus vai nosauktus parametrus ar regulārām izteiksmēm, jo tie ir lasāmāki un vieglāk uzturami.

## Nosauktie parametri

Jūs varat norādīt nosauktos parametrus savos maršrutos, kas tiks nodoti jūsu atgriezeniskajai funkcijai. **Tas ir vairāk par maršruta lasāmību nekā kaut kas cits. Lūdzu, skatiet zemāk esošo sadaļu par svarīgu brīdinājumu.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

Jūs varat arī iekļaut regulārās izteiksmes kopā ar saviem nosauktajiem parametriem, izmantojot `:` atdalītāju:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Šis atbilst /bob/123
  // Bet neatbilst /bob/12345
});
```

> **Piezīme:** Atbilstības regulārā izteiksmes grupām `()` ar pozicionālajiem parametriem nav atbalstīta. :'\(

### Svarīgs brīdinājums

Pats par sevi, piemēram, iepriekš minētajā piemērā, izskatās, ka `@name` ir tieši saistīts ar mainīgo `$name`, bet tā nav. Parametru secība atgriezeniskajā funkcijā nosaka, kas tam tiek nodots. Tātad, ja jūs mainītu parametru secību atgriezeniskajā funkcijā, mainīgie tiks arī mainīti. Šeit ir piemērs:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

Un, ja jūs dotos uz sekojošo URL: `/bob/123`, rezultāts būtu `hello, 123 (bob)!`. Lūdzu, esiet uzmanīgs, kad uzstādat savus maršrutus un atgriezeniskās funkcijas.

## Opciju parametri

Jūs varat norādīt nosauktos parametrus, kas ir izvēles, lai atbilstu, aptverot segmentos iekavās.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Šis atbilst šādām URL:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Visi izvēles parametri, kas netiek atbilsti, tiks nodoti kā `NULL`.

## Zvaigznītes

Atbilstība tiek veikta tikai uz atsevišķiem URL segmentiem. Ja jūs vēlaties atbilst vairākām segmentiem, varat izmantot `*` zvaigznīti.

```php
Flight::route('/blog/*', function () {
  // Šis atbilst /blog/2000/02/01
});
```

Lai novirzītu visus pieprasījumus uz vienu atgriezenisko funkciju, jūs varat to izdarīt:

```php
Flight::route('*', function () {
  // Veiciet kaut ko
});
```

## Pāreja

Jūs varat pāradresēt izpildi uz nākamo atbilstošo maršrutu, atgriežot `true` no savas atgriezeniskās funkcijas.

```php
Flight::route('/user/@name', function (string $name) {
  // Pārbaudiet dažus nosacījumus
  if ($name !== "Bob") {
    // Turpināt uz nākamo maršrutu
    return true;
  }
});

Flight::route('/user/*', function () {
  // Šī funkcija tiks izsaukta
});
```

## Maršruta aliasēšana

Jūs varat piešķirt alias maršrutam, lai URL varētu dinamiski izveidot vēlāk jūsu kodā (piemēram, veidnē).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// vēlāk kaut kur kodā
Flight::getUrl('user_view', [ 'id' => 5 ]); // atgriezīs '/users/5'
```

Tas ir īpaši noderīgi, ja jūsu URL nejauši mainās. Iepriekšējā piemērā, pieņemsim, ka "lietotāji" tika pārvietoti uz `/admin/users/@id` vietas. 
Ar aliasēšanu jūs nekur vairs nevajag mainīt, jo alias tagad atgriezīs `/admin/users/5` kā iepriekšējā piemērā.

Maršruta aliasēšana arī darbojas grupās:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});

// vēlāk kaut kur kodā
Flight::getUrl('user_view', [ 'id' => 5 ]); // atgriezīs '/users/5'
```

## Maršruta informācija

Ja vēlaties pārbaudīt atbilstošo maršruta informāciju, jūs varat pieprasīt, lai maršruta objekts tiktu nodots jūsu atgriezeniskajai funkcijai, pievienojot `true` kā trešo parametru maršruta metodē. Maršruta objekts vienmēr būs pēdējais parametra nodots jūsu atgriezeniskajai funkcijai.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // HTTP metožu masīvs, ar kurām tiek atbilstībā
  $route->methods;

  // Nosaukto parametru masīvs
  $route->params;

  // Atbilstošā regulārā izteiksme
  $route->regex;

  // Satur jebkuru '*' izmantotu URL paraugā
  $route->splat;

  // Rāda URL ceļu...ja jums tas tiešām ir nepieciešams
  $route->pattern;

  // Rāda, kāda starpniekprogrammatūra ir piešķirta šim
  $route->middleware;

  // Rāda, kāds alias piešķirts šim maršrutam
  $route->alias;
}, true);
```

## Maršruta grupēšana

Reizēm var būt vajadzība apvienot saistītos maršrutus kopā (piemēram, `/api/v1`). Jūs varat to izdarīt, izmantojot `group` metodi:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Atbilst /api/v1/users
  });

  Flight::route('/posts', function () {
	// Atbilst /api/v1/posts
  });
});
```

Jūs varat pat ligzdot grupas grupās:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() iegūst mainīgos, tas nenosaka maršrutu! Skatiet objekta kontekstu zemāk
	Flight::route('GET /users', function () {
	  // Atbilst GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Atbilst POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Atbilst PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {
	// Flight::get() iegūst mainīgos, tas nenosaka maršrutu! Skatiet objekta kontekstu zemāk
	Flight::route('GET /users', function () {
	  // Atbilst GET /api/v2/users
	});
  });
});
```

### Grupēšana ar objekta kontekstu

Jūs joprojām varat izmantot maršruta grupēšanu ar `Engine` objektu šādā veidā:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // izmantojiet $router mainīgo
  $router->get('/users', function () {
	// Atbilst GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Atbilst POST /api/v1/posts
  });
});
```

## Resursu maršrutizācija

Jūs varat izveidot maršrutu kopumu resursam, izmantojot `resource` metodi. Tas izveidos maršrutu kopumu resursam, kas seko RESTful konvencijām.

Lai izveidotu resursu, veiciet sekojošo:

```php
Flight::resource('/users', UsersController::class);
```

Un, kas notiks fonā, tas izveidos šādus maršrutus:

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

Un jūsu controllers izskatīsies šādi:

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

> **Piezīme**: Jūs varat apskatīt jaunpiešķirtos maršrutus ar `runway`, izpildot `php runway routes`.

### Resursu maršrutu pielāgošana

Ir dažas iespējas, lai konfigurētu resursu maršrutus.

#### Alias bāze

Jūs varat konfigurēt `aliasBase`. Noklusējuma gadījumā alias ir pēdējā URL daļa, kas norādīta. Piemēram, `/users/` radīs `aliasBase` vērtību `users`. Kad šie maršruti tiek izveidoti, alias ir `users.index`, `users.create` utt. Ja vēlaties mainīt alias, iestatiet `aliasBase` uz vērtību, kuru vēlaties.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Tikai un Izņemot

Jūs varat arī norādīt, kuri maršruti jums jāizveido, izmantojot `only` un `except` opcijas.

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Šīs ir pamata baltas un melnas sarakstu opcijas, lai jūs varētu norādīt, kuri maršruti jums jāizveido.

#### Starpniekprogrammatūru

Jūs varat arī norādīt starpniekprogrammatūru, kas jāveic katram maršrutam, ko izveido `resource` metode.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## Plūstoša informācija

Tagad jūs varat straumēt atbildes uz klientu, izmantojot `streamWithHeaders()` metodi. 
Tas ir noderīgi, lai nosūtītu lielus failus, ilgstošas darbības procesu vai radītu lielas atbildes. 
Maršruta straumēšana tiek apstrādāta nedaudz savādāk nekā parasts maršruts.

> **Piezīme:** Straumēšanas atbildes ir pieejamas tikai tad, ja jums ir [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) iestatīts uz nepatiesu.

### Straumēšana ar manuālām virsrakstiem

Jūs varat straumēt atbildi klientam, izmantojot `stream()` metodi uz maršruta. Ja jūs to darāt, jums jānosaka visas metodes pašiem, pirms dodat jebko uz klientu.
To var izdarīt ar `header()` php funkciju vai `Flight::response()->setRealHeader()` metodi.

```php
Flight::route('/@filename', function($filename) {

	// acīmredzot jums būtu jānosaka ceļš un tā tālāk.
	$fileNameSafe = basename($filename);

	// Ja jums ir papildu virsraksti, kas jānosaka šeit pēc maršruta izpildes
	// tie jādefinē pirms jebkādas izdrukas ārā.
	// Tie ir jābūt visi tieši aicinājumi uz header() funkciju vai 
	// izsaukumi uz Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// vai
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// Kļūdu noķeršana un tā tālāk
	if(empty($fileData)) {
		Flight::halt(404, 'Fails nav atrasts');
	}

	// manuāli nosakiet satura garumu, ja vēlaties
	header('Content-Length: '.filesize($filename));

	// Straumējiet datus uz klientu
	echo $fileData;

// Šī ir maģiskā rinda šeit
})->stream();
```

### Straumēšana ar virsrakstiem

Jūs varat izmantot arī `streamWithHeaders()` metodi, lai iestatītu virsrakstus, pirms sākat straumēšanu.

```php
Flight::route('/stream-users', function() {

	// jūs varat pievienot visu papildus virsrakstu, ko vēlaties šeit
	// jums tikai jāizmanto header() vai Flight::response()->setRealHeader()

	// tomēr kā jūs iegūstat savus datus, piemēram...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// Šis ir nepieciešams, lai nosūtītu datus uz klientu
		ob_flush();
	}
	echo '}';

// Šī ir tā, kā jūs iestatīsit virsrakstus pirms sākat straumēšanu.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// opcional statusa kods, noklusējums ir 200
	'status' => 200
]);
```