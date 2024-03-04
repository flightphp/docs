# Maršrutēšana

> **Piezīme:** Vēlaties uzzināt vairāk par maršrutēšanu? Apskatiet ["kāpēc izmantot struktūru?"](/learn/why-frameworks) lapu, lai iegūtu detalizētāku izskaidrojumu.

Vienkārša maršrutēšana Filtā tiek veikta, salīdzinot URL paraugu ar atpakaļsaicināšanas funkciju vai klases un metodes masīvu.

```php
Flight::route('/', function(){
    echo 'sveika, pasaule!';
});
```

Atpakaļsaicinājums var būt jebkura objekta funkcija. Tāpēc varat izmantot parastu funkciju:

```php
function hello(){
    echo 'sveika, pasaule!';
}

Flight::route('/', 'hello');
```

Vai arī klases metodi:

```php
class Greeting {
    public static function hello() {
        echo 'sveika, pasaule!';
    }
}

Flight::route('/', array('Greeting','hello'));
```

Vai objekta metodi:

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'Jānis Bērziņš';
    }

    public function hello() {
        echo "Sveiki, {$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

Maršruti tiek salīdzināti tā, kā tie ir definēti. Pirmajam maršrutam, kas atbilst pieprasījumam, tiks izsaukts.

## Metodes Maršrutēšana

Pēc noklusējuma maršruta paraugi tiek salīdzināti ar visām pieprasījuma metodēm. Jūs varat reaģēt uz konkrētām metodēm, novietojot identifikatoru pirms URL.

```php
Flight::route('GET /', function () {
  echo 'Esmu saņēmis GET pieprasījumu.';
});

Flight::route('POST /', function () {
  echo 'Esmu saņēmis POST pieprasījumu.';
});
```

Jūs varat arī pievienot vairākas metodes vienai atpakaļsaicinājumam, izmantojot `|` atdalītāju:

```php
Flight::route('GET|POST /', function () {
  echo 'Esmu saņēmis vai nu GET, vai POST pieprasījumu.';
});
```

Papildus jūs varat paņemt Maršruta objektu, kuram ir dažas palīginstrumentu metodes, ko varat izmantot:

```php

$router = Flight::router();

// atbilst visām metodēm
$router->map('/', function() {
	echo 'sveika, pasaule!';
});

// GET pieprasījums
$router->get('/lietotāji', function() {
	echo 'lietotāji';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## Regulārās izteiksmes

Jūs varat izmantot regulārās izteiksmes savos maršrutos:

```php
Flight::route('/lietotājs/[0-9]+', function () {
  // Tas atbilstēs /lietotājs/1234
});
```

Kaut arī šī metode ir pieejama, ir ieteicams izmantot nosauktos parametrus vai
nosauktos parametrus ar regulārajām izteiksmēm, jo tie ir lasāmāki un vieglāk uzturami.

## Nosauktie Parametri

Jūs varat norādīt nosauktos parametrus savos maršrutos, kas tiks nodoti
jūsu atpakaļsaicinājuma funkcijai.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "sveiki, $name ($id)!";
});
```

Jūs varat iekļaut arī regulārās izteiksmes savos nosauktajos parametros, izmantojot
`:` atdalītāju:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Tas atbilstēs /bob/123
  // Bet neatbilstēs /bob/12345
});
```

> **Piezīme:** Sakritība ar nosauktajiem parametriem ar nosauktajām parametru grupām `()` nav atbalstīta. :'\(

## Neobligātie Parametri

Jūs varat norādīt nosauktos parametrus, kas ir neobligāti, lai atbilstu, ietverot
segmentus iekavās.

```php
Flight::route(
  '/blogs(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Tas atbilst šādiem URLS:
    // /blogs/2012/12/10
    // /blogs/2012/12
    // /blogs/2012
    // /blogs
  }
);
```

Visi neobligātie parametri, kas nav atbilstoši, tiks nodoti kā `NULL`.

## Vaļējiem Simboliem

Atbilstība tiek veikta tikai individuāliem URL segmentiem. Ja vēlaties atbilst vairākiem
segmentiem, varat izmantot `*` vaļējo simbolu.

```php
Flight::route('/blogs/*', function () {
  // Tas atbilstēs /blogs/2000/02/01
});
```

Lai novirzītu visus pieprasījumus uz vienu atpakaļsaicinājumu, varat:

```php
Flight::route('*', function () {
  // Ko darīt
});
```

## Pāreja

Izpildi var nodot nākamajam atbilstošajam maršrutam, atgriežot `true`
no jūsu atpakaļsaicinājuma funkcijas.

```php
Flight::route('/lietotājs/@name', function (string $name) {
  // Pārbaudiet kādu nosacījumu
  if ($name !== "Jānis") {
    // Turpināt nākamajam maršrutam
    return true;
  }
});

Flight::route('/lietotājs/*', function () {
  // Tas tiks izsaukts
});
```

## Maršruta Aliasēšana

Jūs varat piešķirt aliasu maršrutai, lai URL varētu dinamiski tikt ģenerēta vēlāk jūsu kodā (piemēram, veidlapai).

```php
Flight::route('/lietotāji/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotāja_skats');

// vēlāk kādā kodā
Flight::getUrl('lietotāja_skats', [ 'id' => 5 ]); // atgriezīs '/lietotāji/5'
```

Šis ir īpaši noderīgs, ja jūsu URL mainās. Šajā piemērā, apstākļi tika pārvietoti uz `/admin/lietotāji/@id`.
Ar aliasēšanu vietas jums nav jāmaina visur, kur norādat aliasu, jo aliasējot tagad tiks atgriests `/admin/lietotāji/5`, tāpat kā
piemērā iepriekš.

Maršruta aliasēšana joprojām darbojas arī grupās:

```php
Flight::group('/lietotāji', function() {
    Flight::route('/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotāja_skats');
});


// vēlāk kādā kodā
Flight::getUrl('lietotāja_skats', [ 'id' => 5 ]); // atgriezīs '/lietotāji/5'
```

## Maršruta Informācija

Ja vēlaties pārbaudīt atbilstošo maršruta informāciju, jūs varat pieprasīt maršruta objektu tikt nodotam uz jūsu atpakaļsaicinājuma funkciju, ievadot `true` kā trešo parametru maršruta metodē. Maršruta objekts vienmēr būs pēdējais parametrs, kas tiek nodots jūsu atpakaļsaicinājuma funkcijai.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Līdzinātie HTTP metodi masīvs
  $route->methods;

  // Nosaukto parametru masīvs
  $route->params;

  // Atbilstošā regulārā izteiksme
  $route->regex;

  // satur jebkura '*' izmantotā URL parauga saturu
  $route->splat;

  // Rāda URL ceļu .... ja tiešām to vajag
  $route->pattern;

  // Rāda kāda starpā ir piešķirts šim
  $route->middleware;

  // Rāda aliasu, kas šim maršrutam ir piešķirts
  $route->alias;
}, true);
```

## Maršruta Grupēšana

Var būt laiki, kad vēlaties grupēt saistītos maršrutus kopā (piemēram, `/api/v1`).
To varat izdarīt, izmantojot `group` metodi:

```php
Flight::group('/api/v1', function () {
  Flight::route('/lietotāji', function () {
	// Atbilst /api/v1/lietotāji
  });

  Flight::route('/ziņas', function () {
	// Atbilst /api/v1/ziņas
  });
});
```

Pat variet iekšnest grupas grupas:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() iegūst mainīgos, tas nenosaka maršrutu! Skatīt objektu kontekstu zemāk
	Flight::route('GET /lietotāji', function () {
	  // Atbilst GET /api/v1/lietotāji
	});

	Flight::post('/ziņas', function () {
	  // Atbilst POST /api/v1/ziņas
	});

	Flight::put('/ziņas/1', function () {
	  // Atbilst PUT /api/v1/ziņas
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() iegūst mainīgos, tas nenosaka maršrutu! Skatīt objektu kontekstu zemāk
	Flight::route('GET /lietotāji', function () {
	  // Atbilst GET /api/v2/lietotāji
	});
  });
});
```

### Grupēšana ar objektu kontekstu

Joprojām varat izmantot maršruta grupēšanu ar `Engine` objektu šādā veidā:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // izmantojiet $router mainīgo
  $router->get('/lietotāji', function () {
	// Atbilst GET /api/v1/lietotāji
  });

  $router->post('/ziņas', function () {
	// Atbilst POST /api/v1/ziņas
  });
});
```

## Strumēšana

Tagad varat strādāt ar strūklošanu klientam izmantojot `streamWithHeaders()` metodi.
Tas ir noderīgi, lai sūtītu lielas datnes, ilgstošas procesus vai ģenerētu lielas atbildes.
Maršruta strūklošana tiek apstrādāta nedaudz atšķirīgi nekā parasts maršruts.

> **Piezīme:** Strumelēšanas atbildes ir pieejamas tikai tad, ja jums ir iestatīts [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) uz false.

```php
Flight::route('/strumeļo-lietotājus', function() {

	// kā jūs izvelkat savus datus, tikai kā piemēru...
	$lietotaji_pazinj = Flight::db()->query("SELECT id, vārds, uzvārds FROM lietotāji");

	echo '{';
	$lietotaju_skaits = count($lietotaji);
	while($lietotājs = $lietotaji_pazinj->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($lietotājs);
		if(--$lietotaju_skaits > 0) {
			echo ',';
		}

		// Šis ir nepieciešams, lai nosūtītu datus klientam
		ob_flush();
	}
	echo '}';

// Tā jums jāiestata galvenes pirms strūklošanas sākuma.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	// neobligāts statusa kods, pēc noklusējuma 200
	'stāvoklis' => 200
]);