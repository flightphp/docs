# Maršrutēšana

> **Piezīme:** Vai vēlaties uzzināt vairāk par maršrutēšanu? Apskati ["kāpēc izmantot ietvaru?"](/learn/why-frameworks) lapu, lai iegūtu detalizētāku skaidrojumu.

Pamata maršrutēšana Flight notiek, salīdzinot URL paraugu ar atgriezeniskās izsaukuma funkciju vai klases un metodes masīvu.

```php
Flight::route('/', function(){
    echo 'sveika, pasaule!';
});
```

Atgriezeniskā izsaukuma funkcija var būt jebkura izsaukāmais objekts. Tāpēc varat izmantot parastu funkciju:

```php
function sveiki(){
    echo 'sveika, pasaule!';
}

Flight::route('/', 'sveiki');
```

Vai arī klases metodi:

```php
class sveiciens {
    public static function sveiki() {
        echo 'sveika, pasaule!';
    }
}

Flight::route('/', array('sveiciens','sveiki'));
```

Vai objekta metodi:

```php

// Sveiciens.php
class sveiciens
{
    public function __construct() {
        $this->vards = 'Jānis Bērziņš';
    }

    public function sveiki() {
        echo "Sveiki, {$this->vards}!";
    }
}

// index.php
$sveiciens = new sveiciens();

Flight::route('/', array($sveiciens, 'sveiki'));
```

Maršruti tiek salīdzināti ar to secībā, kā tie ir definēti. Pirmajam piemērotajam maršrutam tiks izsaukts.

## Metodes Maršrutēšana

Pēc noklusējuma maršruta paraugi tiek salīdzināti ar visām pieprasījuma metodēm. Jūs varat reaģēt uz konkrētām metodēm, novietojot identifikatoru pirms URL.

```php
Flight::route('GET /', function () {
  echo 'Es saņēmu GET pieprasījumu.';
});

Flight::route('POST /', function () {
  echo 'Es saņēmu POST pieprasījumu.';
});
```

Jūs varat arī piešķirt vairākas metodes vienai funkcijai, izmantojot `|` atdalītāju:

```php
Flight::route('GET|POST /', function () {
  echo 'Es saņēmu gan GET, gan POST pieprasījumu.';
});
```

Papildus jūs varat iegūt Maršrutētāja objektu, kuram ir daži palīgmetodi, ko varat izmantot:

```php

$maršrutētājs = Flight::router();

// atbilst visām metodēm
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
  // Tas sakritīs ar /lietotājs/1234
});
```

Lai gan šī metode ir pieejama, ieteicams izmantot nosauktās parametrus vai
nosauktus parametrus ar regulārām izteiksmēm, jo tās ir lasāmākas un vieglāk uzturamas.

## Nosauktie Parametri

Jūs varat norādīt nosauktos parametrus savos maršrutos, kas tiks nodoti
jūsu atgriezeniskajai funkcijai.

```php
Flight::route('/@vards/@id', function (string $vards, string $id) {
  echo "sveiki, $vards ($id)!";
});
```

Jūs varat iekļaut regulārās izteiksmes ar savām nosauktajām parametriem, izmantojot
`:` atdalītāju:

```php
Flight::route('/@vards/@id:[0-9]{3}', function (string $vards, string $id) {
  // Tas sakritīs ar /bobs/123
  // Bet nesakritīs ar /bobs/12345
});
```

> **Piezīme:**  Nesakritības regex grupām `()` ar nosauktajiem parametriem nav atbalstītas. :'\(

## Neobligātie Parametri

Jūs varat norādīt neobligātus nosauktos parametrus, lai tos salīdzinātu, ietinot
segmentus iekavās.

```php
Flight::route(
  '/blogs(/@gads(/@mēnesis(/@diena)))',
  function(?string $gads, ?string $mēnesis, ?string $diena) {
    // Tas sakrīt ar šādiem URL:
    // /blogs/2012/12/10
    // /blogs/2012/12
    // /blogs/2012
    // /blogs
  }
);
```

Visi neobligātie parametri, kas nesakrīt, tiks nodoti kā `NULL`.

## Aizstājvārdi

Jūs varat piešķirt aizstājvārdu maršrutējumam, lai URL varētu dinamiski tikt ģenerēts vēlāk jūsu kodā (piemēram, kā šablons).

```php
Flight::route('/lietotāji/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotāja_skatījums');

// vēlāk jūsu kodā
Flight::getUrl('lietotāja_skatījums', [ 'id' => 5 ]); // atgriezīs '/lietotāji/5'
```

Šis ir īpaši noderīgi, ja jūsu URL gadījumā mainās. Iepriekš minētajā piemērā, ja lietotāji tiek pārvietoti uz `/admin/lietotāji/@id` vietā.
Izmantojot aizstājvārdus, jums nav jāmaina visi vietas, kur atsauktos uz aizstājvārdu, jo aizstājvārds tagad atgriezīs `/admin/lietotāji/5` kā augstāk minētajā
piemērā.

Maršruta aizstājvārdi joprojām darbojas grupās arī:

```php
Flight::group('/lietotāji', function() {
    Flight::route('/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotāja_skatījums');
});


// vēlāk jūsu kodā
Flight::getUrl('lietotāja_skatījums', [ 'id' => 5 ]); // atgriezīs '/lietotāji/5'
```

## Maršruta Informācija

Ja vēlaties pārbaudīt atbilstošā maršruta informāciju, jūs varat pieprasīt maršruta
objektu nodot jūsu atgriezeniskajai funkcijai, padodot `true` kā trešo parametru
maršruta metodē. Maršruta objekts vienmēr tiks nodots ka pēdējais parametrs jūsu
atgriezeniskajai funkcijai.

```php
Flight::route('/', function(\flight\net\Route $maršruts) {
  // Masīvs ar atbilstošajām HTTP metodēm
  $maršruts->metodes;

  // Nosauktie parametri masīvā
  $maršruts->parametri;

  // Atbilstošā regulārā izteiksme
  $maršruts->regex;

  // Saturs jebkuriem '*' izmantotajiem URL paraugiem
  $maršruts->splat;

  // Parāda URL ceļu.... ja tiešām tas ir nepieciešams
  $maršruts->paraugs;

  // Parāda, kās starpciņas ir piešķirtas šim
  $maršruts->middleware;

  // Parāda aizstājvārdu, kas piešķirts šim maršrutam
  $maršruts->aizstāv;
}, true);
```

## Maršruta Grupēšana

Var gadīties, ka vēlaties grupēt saistītos maršrutus kopā (piemēram, `/api/v1`).
To var izdarīt, izmantojot `group` metodi:

```php
Flight::group('/api/v1', function () {
  Flight::route('/lietotāji', function () {
	// Sakrīt ar /api/v1/lietotāji
  });

  Flight::route('/ziņas', function () {
	// Sakrīt ar /api/v1/ziņas
  });
});
```

Jūs pat varat iekļaut grupas grupas:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() iegūst mainīgos, tas nenosaka maršrutu! Skatiet objekta kontekstu zemāk
	Flight::route('GET /lietotāji', function () {
	  // Sakrīt ar GET /api/v1/lietotāji
	});

	Flight::post('/ziņas', function () {
	  // Sakrīt ar POST /api/v1/ziņas
	});

	Flight::put('/ziņas/1', function () {
	  // Sakrīt ar PUT /api/v1/ziņas
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() iegūst mainīgos, tas nenosaka maršrutu! Skatiet objekta kontekstu zemāk
	Flight::route('GET /lietotāji', function () {
	  // Sakrīt ar GET /api/v2/lietotāji
	});
  });
});
```

### Grupēšana ar Objekta Kontekstu

Joprojām varat izmantot maršruta grupēšanu ar `Engine` objektu šādā veidā:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $maršrutētājs) {

  // lietojam $maršrutētāj mainīgo
  $maršrutētājs->get('/lietotāji', function () {
	// Sakrīt ar GET /api/v1/lietotāji
  });

  $maršrutētājs->post('/ziņas', function () {
	// Sakrīt ar POST /api/v1/ziņas
  });
});
```

## Strūklināšana

Tagad jūs varat strūklināt atbildes klientam, izmantojot `streamWithHeaders()` metodi. 
Tas ir noderīgi, lai nosūtītu lielas failus, ilgi darbojošies procesus vai ģenerētu lielas atbildes. 
Maršruta strūklināšana tiek apstrādāta nedaudz atšķirīgi nekā parasts maršruts.

> **Piezīme:** Strūklināta atbildes ir pieejama tikai tad, ja jums ir iestatīts[`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) uz false.

```php
Flight::route('/plūdveida-lietotāji', function() {

	// kā vien izvelc savus datus, vienkārši kā piemērs...
	$lietotaji_stmt = Flight::db()->query("SELECT id, vards, uzvārds FROM lietotaji");

	echo '{';
	$lietotāja_skaita = count($lietotaji);
	while($lietotājs = $lietotaji_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($lietotājs);
		if(--$lietotāja_skaita > 0) {
			echo ',';
		}

		// Nepieciešams nosūtīt datus klientam
		ob_flush();
	}
	echo '}';

// Tā jūs iestatīsiet galvenes pirms sākat strūklināt.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	// fakultatīvs statusa kods, noklusējuma vērtība ir 200
	'statuss' => 200
]);
```