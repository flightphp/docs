# Maršrutēšana

> **Piezīme:** Vai vēlaties uzzināt vairāk par maršrutēšanu? Skatiet [kāpēc ietvaros](/learn/why-frameworks) lapu, lai iegūtu detalizētāku izskaidrojumu.

Pamata maršrutēšana Flight ir veikta, saskaņojot URL paraugus ar atpakaļsaukuma funkciju vai klases un metodes masīvu.

```php
Flight::route('/', function(){
    echo 'sveika, pasaule!';
});
```

Atpakaļsaukums var būt jebkura objekta metode, kas ir izsaukama. Tādēļ varat izmantot parasto funkciju:

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

Maršruti tiek saskaņoti ar to secību, kā tie ir definēti. Pirmais maršruts, kas saskan ar pieprasījumu, tiks izsaukts.

## Metodes Maršrutēšana

Pēc noklusējuma maršruta paraugi tiek saskanoti pret visām pieprasījuma metodēm. Jūs varat atbildēt uz konkrētām metodēm, novietojot identifikatoru pirms URL.

```php
Flight::route('GET /', function () {
  echo 'Es saņēmu GET pieprasījumu.';
});

Flight::route('POST /', function () {
  echo 'Es saņēmu POST pieprasījumu.';
});
```

Jūs varat arī pievienot vairākas metodes vienai atpakaļsaukuma funkcijai, izmantojot `|` dalītāju:

```php
Flight::route('GET|POST /', function () {
  echo 'Es saņēmu vai nu GET vai POST pieprasījumu.';
});
```

Papildus varat paņemt Maršrutētāja objektu, kuram ir dažas palīgmetodes, ko varat izmantot:

```php

$maršrutētājs = Flight::router();

// attēlo visas metodes
$maršrutētājs->map('/', function() {
	echo 'sveika, pasaule!';
});

// GET pieprasījums
$maršrutētājs->get('/lietotāji', function() {
	echo 'lietotāji';
});
// $maršrutētājs->post();
// $maršrutētājs->put();
// $maršrutētājs->delete();
// $maršrutētājs->patch();
```

## Regulārās Izteiksmes

Jūs varat izmantot regulārās izteiksmes savos maršrutos:

```php
Flight::route('/lietotājs/[0-9]+', function () {
  // Tas atbilstēs /lietotājs/1234
});
```

Kaut arī šī metode ir pieejama, ieteicams izmantot nosauktos parametrus vai
nosauktos parametrus ar regulārām izteiksmēm, jo tie ir vieglāk lasāmi un uzturami.

## Nosauktie Parametri

Jūs varat norādīt nosauktos parametrus savos maršrutos, kas tiks padoti
jūsu atpakaļsaukuma funkcijai.

```php
Flight::route('/@vards/@id', function (string $vards, string $id) {
  echo "sveiki, $vards ($id)!";
});
```

Jūs varat arī iekļaut regulārās izteiksmes ar savām nosauktajām parametriem, izmantojot
`:` dalītāju:

```php
Flight::route('/@vards/@id:[0-9]{3}', function (string $vards, string $id) {
  // Tas atbilstēs /bobs/123
  // Bet neatbilstēs /bobs/12345
});
```

> **Piezīme:** Savietošana ar regulārajām izteiksmēm `()` ar nosauktajiem parametriem nav atbalstīta. :'\(

## Neobligāti Parametri

Jūs varat norādīt neobligātos nosauktos parametrus, lai atbilstu, iesaišušot
segmentus iekavās.

```php
Flight::route(
  '/ziņas(/@gads(/@mēnesis(/@diena)))',
  function(?string $gads, ?string $mēnesis, ?string $diena) {
    // Tas atbilstēs šādiem URL:
    // /ziņas/2012/12/10
    // /ziņas/2012/12
    // /ziņas/2012
    // /ziņas
  }
);
```

Neesošie neobligātie parametri, kas nesaskanēs, tiks padoti kā `NULL`.

## Vaļaspriekšmeti

Saskanēšana tiek veikta tikai ar atsevišķiem URL segmentiem. Ja vēlaties saskanēt ar vairākiem
segmentiem, varat izmantot `*` vaļaspriekšmetu.

```php
Flight::route('/ziņas/*', function () {
  // Tas atbilstēs /ziņas/2000/02/01
});
```

Lai novirzītu visus pieprasījumus uz vienu atpakaļsaukuma funkciju, varat darīt šādi:

```php
Flight::route('*', function () {
  // Dariet kaut ko
});
```

## Pāreja

Jūs varat pārnest izpildi uz nākamo saskanēšanas maršrutu, atgriežot `true`
no savas atpakaļsaukuma funkcijas.

```php
Flight::route('/lietotājs/@vards', function (string $vards) {
  // Pārbaudiet kādu nosacījumu
  if ($vards !== "Bobs") {
    // Turpiniet uz nākamo maršrutu
    return true;
  }
});

Flight::route('/lietotājs/*', function () {
  // Tas tiks izsaukts
});
```

## Maršruta Apielēšana

Jūs varat piešķirt aliasu maršrutam, lai URL varētu dinamiski tikt ģenerēta vēlāk jūsu kodā (piemēram, kā šablons).

```php
Flight::route('/lietotāji/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotāja_skats');

// vēlāk kādā vietā kodā
Flight::getUrl('lietotāja_skats', [ 'id' => 5 ]); // atgriezīs '/lietotāji/5'
```

Šis ir īpaši noderīgi, ja jūsu URL gadījumā rodas izmaiņas. Iepriekš minētajā piemērā, teiksim, ka lietotāji tika pārvietoti uz `/admin/lietotāji/@id` vietā.
Ar aliasēšanu jums nav jāmaina visur, kur atsauktajā aliasēšanā, jo tas tagad atgriezīs `/admin/lietotāji/5` kā iepriekšējā piemērā.

Maršruta apielēšana strādā arī grupās:

```php
Flight::group('/lietotāji', function() {
    Flight::route('/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotāja_skats');
});


// vēlāk kādā vietā kodā
Flight::getUrl('lietotāja_skats', [ 'id' => 5 ]); // atgriezīs '/lietotāji/5'
```

## Maršruta Info

Ja vēlaties pārbaudīt saskanēšanas maršruta informāciju, jūs varat pieprasīt maršruta objektu, lai to padotu jūsu atpakaļsaukuma funkcijai, padodot `true` kā trešo parametru
maršruta metodē. Maršruta objekts vienmēr būs pēdējais parametrs, kas padots jūsu atpakaļsaukuma funkcijai.

```php
Flight::route('/', function(\flight\net\Route $maršruts) {
  // Masīvs ar HTTP metodēm, kas saskanētas
  $maršruts->methods;

  // Masīvs ar nosauktajiem parametriem
  $maršruts->params;

  // Saskaņošanas regulārā izteiksme
  $maršruts->regex;

  // Saturs jebkuriem '*' izmantotajiem URL paraugiem
  $maršruts->splat;

  // Parāda URL ceļu...ja jums tiešām tāds ir nepieciešams
  $maršruts->pattern;

  // Parāda, kādas starpposma programmas piešķirtas šim
  $maršruts->middleware;

  // Parāda piešķirto aliasu šim maršrutam
  $maršruts->alias;
}, true);
```

## Maršruta Grupēšana

Var gadīties, ka vēlaties grupēt saistītos maršrutus kopā (piemēram, `/api/v1`).
To varat izdarīt, izmantojot `group` metodi:

```php
Flight::group('/api/v1', function () {
  Flight::route('/lietotāji', function () {
	// Saskan ar /api/v1/lietotāji
  });

  Flight::route('/ieraksti', function () {
	// Saskan ar /api/v1/ieraksti
  });
});
```

Pat varat ienest grupas grupās:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() iegūst mainīgos, tas nenosaka maršrutu! Skatiet objekta kontekstu zemāk
	Flight::route('GET /lietotāji', function () {
	  // Saskan ar GET /api/v1/lietotāji
	});

	Flight::post('/ieraksti', function () {
	  // Saskan ar POST /api/v1/ieraksti
	});

	Flight::put('/ieraksti/1', function () {
	  // Saskan ar PUT /api/v1/ieraksti
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() iegūst mainīgos, tas nenosaka maršrutu! Skatiet objekta kontekstu zemāk
	Flight::route('GET /lietotāji', function () {
	  // Saskan ar GET /api/v2/lietotāji
	});
  });
});
```

### Grupēšana Ar Objekta Kontekstu

Jūs joprojām varat izmantot maršruta grupēšanu ar `Engine` objektu šādā veidā:

```php
$lietotne = new \flight\Engine();
$lietotne->group('/api/v1', function (Router $maršruts) {

  // izmantojam $maršruta mainīgo
  $maršruts->get('/lietotāji', function () {
	// Saskan ar GET /api/v1/lietotāji
  });

  $maršruts->post('/ieraksti', function () {
	// Saskan ar POST /api/v1/ieraksti
  });
});
```