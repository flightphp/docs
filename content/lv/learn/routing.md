# Maršrutēšana

> **Piezīme:** Vai vēlaties uzzināt vairāk par maršrutēšanu? Apskatiet lapu [kāpēc izvēlēties ietvarus](/learn/why-frameworks), lai iegūtu pamata skaidrojumu.

Pamata maršrutēšana Flight tiek veikta, salīdzinot URL paraugu ar atgriezeniskās saites funkciju vai klases un metodes masīvu.

```php
Flight::route('/', function(){
    echo 'sveika, pasaule!';
});
```

Atgriezeniskā saite var būt jebkura objekta, kas ir izsaukams. Tāpēc varat izmantot parasto funkciju:

```php
function sveiki(){
    echo 'sveika, pasaule!';
}

Flight::route('/', 'sveiki');
```

Vai klases metodi:

```php
class Sveiciens {
    public static function sveiki() {
        echo 'sveika, pasaule!';
    }
}

Flight::route('/', array('Sveiciens','sveiki'));
```

Vai objekta metodi:

```php

// Sveiciens.php
class Sveiciens
{
    public function __construct() {
        $this->vards = 'Jānis Bērziņš';
    }

    public function sveiki() {
        echo "Sveiki, {$this->vards}!";
    }
}

// index.php
$sveiciens = new Sveiciens();

Flight::route('/', array($sveiciens, 'sveiki'));
```

Maršruti tiek salīdzināti tā, kā tie ir definēti. Pirmajam maršrutam, kurš atbilst pieprasījumam, tiks izsaukts.

## Metodes Maršrutēšana

Pēc noklusējuma maršruta paraugi tiek salīdzināti ar visām pieprasījuma metodēm. Jūs varat atbildēt
uz konkrētām metodēm, ievietojot identifikatoru pirms URL.

```php
Flight::route('GET /', function () {
  echo 'Es saņēmu GET pieprasījumu.';
});

Flight::route('POST /', function () {
  echo 'Es saņēmu POST pieprasījumu.';
});
```

Jūs varat arī atainot vairākas metodes vienai atgriezeniskajai saitei, izmantojot `|` atdalītāju:

```php
Flight::route('GET|POST /', function () {
  echo 'Es saņēmu GET vai POST pieprasījumu.';
});
```

Papildus jūs varat izmantot Router objektu, kuram ir daži palīgmetodi jums lietošanai:

```php

$router = Flight::router();

// ataino visus veidus
$router->map('/', function() {
	echo 'sveika, pasaule!';
});

// GET pieprasījums
$router->get('/lietotaji', function() {
	echo 'lietotāji';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## Regulāras Izteiksmes

Jūs varat izmantot regulārās izteiksmes savos maršrutos:

```php
Flight::route('/lietotajs/[0-9]+', function () {
  // Tas atbilstēs /lietotajs/1234
});
```

Neskatoties uz šo pieejamo metodi, ieteicams izmantot nosauktos parametrus vai
nosauktos parametrus ar regulārām izteiksmēm, jo tie ir lasāmāki un vieglāki uzturēšanai.

## Nosauktie Parametri

Jūs varat norādīt nosauktos parametrus savos maršrutos, kas tiks nodoti
jūsu atgriezeniskās saites funkcijai.

```php
Flight::route('/@vards/@id', function (string $vards, string $id) {
  echo "sveiki, $vards ($id)!";
});
```

Jūs varat iekļaut regulārās izteiksmes ar savām nosauktajām parametriem, izmantojot
`: ` atdalītāju:

```php
Flight::route('/@vards/@id:[0-9]{3}', function (string $vards, string $id) {
  // Tas atbilstēs /bobs/123
  // Bet neatbilstēs /bobs/12345
});
```

> **Piezīme:** Savienojot regulāros izteiksmes grupas `()` ar nosauktajiem parametriem netiek atbalstīts. :'\(

## Izvēles Parametri

Jūs varat norādīt nosauktos parametrus, kas ir izvēles varianti, lai saskanētu, ietveroši
segmentus iekavās.

```php
Flight::route(
  '/bloks(/@gads(/@menesis(/@diena)))',
  function(?string $gads, ?string $menesis, ?string $diena) {
    // Tas atbilst šādiem URL:
    // /bloks/2012/12/10
    // /bloks/2012/12
    // /bloks/2012
    // /bloks
  }
);
```

Jebkuri neuzskaņotie izvēles parametri tiks nodoti kā `NULL`.

## Aizsegi

Saskanēs tikai ar atsevišķiem URL segmentiem. Ja vēlaties saskanēt ar vairākiem
segmentiem, jūs varat izmantot `*` aizsegumu.

```php
Flight::route('/bloks/*', function () {
  // Tas atbilst /bloks/2000/02/01
});
```

Lai saskanētu visiem pieprasījumiem ar vienu atgriezenisko saiti, varat:

```php
Flight::route('*', function () {
  // Dariet kaut ko
});
```

## Iziet

Jūs varat nodot izpildi nākamajam saskanētajam maršrutam, atgriežot `true`
jūsu atgriezeniskās saites funkcijā.

```php
Flight::route('/lietotajs/@vards', function (string $vards) {
  // Pārbaudiet kādu nosacījumu
  if ($vards !== "Bobs") {
    // Turpiniet uz nākamo maršrutu
    return true;
  }
});

Flight::route('/lietotajs/*', function () {
  // Tas tiks izsaukts
});
```

## Maršruta Aliasing

Jūs varat piešķirt aizsegu maršrutam, tāpēc URL var dinamiski tikt ģenerēts vēlāk jūsu kodā (piemēram, veidnes gadījumā).

```php
Flight::route('/lietotaji/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotaja_skats');

// vēlāk kādā kodā
Flight::getUrl('lietotaja_skats', [ 'id' => 5 ]); // atgriezīs '/lietotaji/5'
```

Šis ir īpaši noderīgi, ja jūsu URL gadījumā mainās. Iepriekš minētajā piemērā, iedomāsimies, ka lietotāji tika pārvietoti uz `/admin/lietotaji/@id` vietā.
Pamatojoties uz aizsegumu, jums nav jāmaina vietās, kur referējat aizsegu, jo aizsegums tagad atgriezīs `/admin/lietotaji/5`, kā iepriekš minētajā
piemērā.

Maršruta aizsegšana joprojām darbojas arī grupās:

```php
Flight::group('/lietotaji', function() {
    Flight::route('/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotaja_skats');
});


// vēlāk kādā kodā
Flight::getUrl('lietotaja_skats', [ 'id' => 5 ]); // atgriezīs '/lietotaji/5'
```

## Maršruta Info

Ja vēlaties pārbaudīt saskanēto maršruta informāciju, jūs varat pieprasīt maršrutu
objekta nodot jūsu atgriezeniskajai funkcijai, nododot `true` kā trešo parametru
maršruta metodē. Maršruta objekts vienmēr būs pēdējais parametrs, kas nodots jūsu
atgriezeniskajai funkcijai.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Masīvs ar saskanētajām HTTP metodēm
  $route->methods;

  // Nosauktie parametru masīvs
  $route->params;

  // Saskanēšanas regulārā izteiksme
  $route->regex;

  // Iekļauj jebkādu '*' lietoto URL paraugā
  $route->splat;

  // Parāda URL ceļa veidlapu....ja tiešām to nepieciešams
  $route->pattern;

  // Parāda, kādas starpības ir piešķirtas šim
  $route->middleware;

  // Parāda aizsegumu, kas piešķirts šim maršrutam
  $route->alias;
}, true);
```

## Maršruta Grupēšana

Var būt brīži, kad vēlaties apkopot saistītos maršrutus kopā (piemēram, `/api/v1`).
To var izdarīt, izmantojot `group` metodi:

```php
Flight::group('/api/v1', function () {
  Flight::route('/lietotaji', function () {
	// Saskan ar /api/v1/lietotaji
  });

  Flight::route('/ziņas', function () {
	// Saskan ar /api/v1/ziņas
  });
});
```

Pat varat nested grupas grupās:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() iegūst mainīgos, tas nenosaka maršrutu! Skatīt objekta kontekstu zemāk
	Flight::route('GET /lietotaji', function () {
	  // Atbilst GET /api/v1/lietotaji
	});

	Flight::post('/ziņas', function () {
	  // Atbilst POST /api/v1/ziņas
	});

	Flight::put('/ziņas/1', function () {
	  // Atbilst PUT /api/v1/ziņas
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() iegūst mainīgos, tas nenosaka maršrutu! Skatīt objekta kontekstu zemāk
	Flight::route('GET /lietotaji', function () {
	  // Atbilst GET /api/v2/lietotaji
	});
  });
});
```

### Grupējot ar objekta kontekstu

Jūs joprojām varat izmantot maršruta grupēšanu ar `Engine` objektu šādā veidā:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // izmanto mainīgo $router
  $router->get('/lietotaji', function () {
	// Atbilst GET /api/v1/lietotaji
  });

  $router->post('/ziņas', function () {
	// Atbilst POST /api/v1/ziņas
  });
});
```