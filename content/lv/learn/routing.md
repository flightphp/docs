# Maršrutēšana

> **Piezīme:** Vai vēlaties uzzināt vairāk par maršrutēšanu? Apmeklējiet ["kāpēc izmantot ietvaru?"](/learn/why-frameworks) lapu, lai iegūtu padziļinātu paskaidrojumu.

Pamata maršrutēšana Flight ietvarā tiek veikta, saskaņojot URL paraugu ar atzvanas funkciju vai klases un metodes masīvu.

```php
Flight::route('/', function(){
    echo 'sveika, pasaule!';
});
```

> Maršruti tiek saskaņoti tādā secībā, kādā tie ir definēti. Pirmajam saskaņotajam maršrutam tiks izsaukts.

### Atzvanas/Funkcijas
Atzvana var būt jebkura uzgalvota objekts. Tātad jūs varat izmantot parastu funkciju:

```php
function sveiki() {
    echo 'sveika, pasaule!';
}

Flight::route('/', 'sveiki');
```

### Klases
Jūs varat izmantot arī klases statisko metodi:

```php
class Sveiciens {
    public static function sveika() {
        echo 'sveika, pasaule!';
    }
}

Flight::route('/', [ 'Sveiciens','sveika' ]);
```

Vai arī, izveidojot objektu pirmo reizi un pēc tam izsaukot metodi:

```php

// Sveiciens.php
class Sveiciens
{
    public function __construct() {
        $this->vards = 'Jānis Bērziņš';
    }

    public function sveika() {
        echo "Sveiki, {$this->vards}!";
    }
}

// index.php
$sveiciens = new Sveiciens();

Flight::route('/', [ $sveiciens, 'sveika' ]);
// Jūs varat to darīt arī bez objekta izveides pirmo reizi
// Piezīme: Neviens arguments netiks ievietots konstruktorā
Flight::route('/', [ 'Sveiciens', 'sveika' ]);
// Turklāt jūs varat izmantot šo īsāko sintaksi
Flight::route('/', 'Sveiciens->sveika');
// vai
Flight::route('/', Sveiciens::class.'->sveika');
```

#### Atkarības ielikšana, izmantojot DIC (Dependency Injection Container)
Ja vēlaties izmantot atkarību ielikšanu, izmantojot konteineru (PSR-11, PHP-DI, Dice, utt.), vienīgais maršrutu veids, kur tas ir pieejams, ir vai nu tieši izveidojot objektu pats
un, izmantojot konteineru, lai izveidotu objektu, vai izmantojot virknes, lai definētu klasi un metodi, lai to izsauktu. Varat doties uz [Atkarību ielikšanas](/learn/extending) lapu, lai iegūtu
vairāk informācijas.

Šeit ir ātrs piemērs:

```php

lietojiet flight\database\PdoWrapper;

// Sveiciens.php
class Sveiciens
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;
	}

	public function sveika(int $id) {
		// izdarīt kaut ko ar $this->pdoWrapper
		$vards = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Sveiki, pasaule! Mani sauc {$vards}!";
	}
}

// index.php

// Iestatiet konteineru ar nepieciešamajām parametriem
// Skatiet Atkarību ielikšanas lapu, lai iegūtu vairāk informācijas par PSR-11
$dice = new \Dice\Dice();

// Neaizmirstiet pārkārtot mainīgo ar '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'parole'
	]
]);

// Reģistrēt konteinera apstrādātāju
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Maršruti kā parasti
Flight::route('/sveiki/@id', [ 'Sveiciens', 'sveika' ]);
// vai
Flight::route('/sveiki/@id', 'Sveiciens->sveika');
// vai
Flight::route('/sveiki/@id', 'Sveiciens::sveika');

Flight::start();
```

## Metodes Maršrutēšana

Pēc noklusējuma maršruta paraugi tiek saskaņoti ar visiem pieprasījuma metodēm. Varat reaģēt
uz konkrētām metodēm, ievietojot identifikatoru pirms URL.

```php
Flight::route('GET /', function () {
  echo 'Es saņēmu GET pieprasījumu.';
});

Flight::route('POST /', function () {
  echo 'Es saņēmu POST pieprasījumu.';
});

// Jūs nevarat izmantot Flight::get() maršrutiem, jo tas ir metode
//    lai iegūtu mainīgos, nevis izveidotu maršrutu.
// Flight::post('/', function() { /* kods */ });
// Flight::patch('/', function() { /* kods */ });
// Flight::put('/', function() { /* kods */ });
// Flight::delete('/', function() { /* kods */ });
```

Jūs arī varat piesaistīt vairākas metodes vienai atzvanas funkcijai, izmantojot `|` atdalītāju:

```php
Flight::route('GET|POST /', function () {
  echo 'Es saņēmu gan GET, gan POST pieprasījumu.';
});
```

Turklāt jūs varat iegūt Maršruta objektu, kuram ir dažas palīgfunkcijas, ko izmantot:

```php

$maršrutētājs = Flight::router();

// atkartot visus veidus
$maršrutētājs->map('/', function() {
	echo 'sveika, pasaule!';
});

// Iegūt pieprasījumu GET
$maršrutētājs->get('/lietotāji', function() {
	echo 'lietotāji';
});
// $maršrutētājs->post();
// $maršrutētājs->put();
// $maršrutētājs->delete();
// $maršrutētājs->patch();
```

## Regulārās izteiksmes

Varat izmantot regulāros izteikumus savos maršrutos:

```php
Flight::route('/lietotājs/[0-9]+', function () {
  // Tas sakrīt ar /lietotājs/1234
});
```

Lai gan šī metode ir pieejama, ieteicams izmantot nosauktos parametrus vai
nosauktus parametrus ar regulārajām izteiksmēm, jo tie ir lasāmāki un vieglāk uzturami.

## Nosauktie Parametri

Varat norādīt nosauktos parametrus savos maršrutos, kas tiks nodoti uz
jūsu atzvana funkciju.

```php
Flight::route('/@vārds/@id', function (string $vārds, string $id) {
  echo "sveiki, $vārds ($id)!";
});
```

Jūs varat iekļaut arī regulārās izteiksmes savos nosauktajos parametros ar
izmantojot `:` atdalītāju:

```php
Flight::route('/@vārds/@id:[0-9]{3}', function (string $vārds, string $id) {
  // Tas sakrīt ar /bobs/123
  // Bet nesakrīs ar /bobs/12345
});
```

> **Piezīme:** Atbilstošs regex grupas `()` nosauktajos parametros netiek atbalstīts. :'\(

## Neobligātie Parametri

Jūs varat norādīt nosauktos parametrus, kas ir neobligāti saskaņošanai, aptverot
segmentus iekavās.

```php
Flight::route(
  '/blogs(/@gads(/@mēnesis(/@diena)))',
  function(?string $gads, ?string $mēnesis, ?string $diena) {
    // Tas sakrīt ar sekojošajiem URL:
    // /blogs/2012/12/10
    // /blogs/2012/12
    // /blogs/2012
    // /blogs
  }
);
```

Jebkuri neobligātie parametri, kas nesakrīt, tiks nodoti kā `NULL`.

## Vaļējie Zīmēmji

Saskanēšana tiek veikta tikai ar atsevišķiem URL segmentiem. Ja vēlaties saskanēt ar vairākiem
segmentiem, jūs varat izmantot `*` vaļējo zīmēmju.

```php
Flight::route('/blogs/*', function () {
  // Tas sakrīt ar /blogs/2000/02/01
});
```

Lai maršrutētu visus pieprasījumus uz vienu atzvanas funkciju, jūs varat:

```php
Flight::route('*', function () {
  // Darīt kaut ko
});
```

## Nodod

Jūs varat nodot izpildi nākamajam saskanētajam maršrutam, atgriežot `true`
no savas atzvanas funkcijas.

```php
Flight::route('/lietotājs/@vārds', function (string $vārds) {
  // Pārbaudiet kādu nosacījumu
  if ($vārds !== "Bobs") {
    // Turpiniet uz nākamo maršrutu
    return true;
  }
});

Flight::route('/lietotājs/*', function () {
  // Šis tiks izsaukts
});
```

## Maršruta Aliasēšana

Jūs varat piešķirt aliasu maršrutam, lai URL vēlāk varētu dinamiski ģenerēt jūsu kodā (piemēram, veidnē).

```php
Flight::route('/lietotāji/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotāja_skats');

// vēlāk kādā vietā kodā
Flight::getUrl('lietotāja_skats', [ 'id' => 5 ]); // atgriezīs '/lietotāji/5'
```

Šis ir īpaši noderīgi, ja jūsu URL mainās. Iepriekš minētajā piemērā teiksim, ka lietotāji tika pārvietoti uz `/admin/lietotāji/@id` vietā.
Ar aliasu palīdzību jums nav jāmaina neviens aliasa atsauces punkts, jo aliasa atsauces punkts tagad atgriezīs `/admin/lietotāji/5`, kā iepriekš
piemērā.

Maršruta aliasēšana joprojām darbojas arī grupās:

```php
Flight::group('/lietotāji', function() {
    Flight::route('/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotāja_skats');
});


// vēlāk kādā kodā
Flight::getUrl('lietotāja_skats', [ 'id' => 5 ]); // atgriezīs '/lietotāji/5'
```

## Maršruta Informācija

Ja vēlaties pārbaudīt saskanētā maršruta informāciju, jūs varat pieprasīt maršruta
objektu tikt nodotam uz jūsu atzvanas funkciju, padodot `true` kā trešo parametru
maršruta metodē. Maršruta objekts vienmēr būs pēdējais parametrs, ko nodod jūsu
atzvanas funkcijai.

```php
Flight::route('/', function(\flight\net\Route $maršruts) {
  // Metodes saskaņota ar HTTP metodi sarakstu
  $maršruts->metodes;

  // Nosauktie parametri masīva formātā
  $maršruts->parametri;

  // Saskanēšanas regulārā izteiksme
  $maršruts->regex;

  // Saturs jebkura atsevišķa '*' satura
  $maršruts->splat;

  // Parāda URL ceļu.... ja tiešām to jums vajag
  $maršruts->paraugs;

  // Parāda, kāds starpējais programmatūrpakotnei ir piešķirts
  $maršruts->middleware;

  // Parāda piešķirto aliasu šim maršrutam
  $maršruts->alias;
}, true);
```

## Maršruta Grupēšana

Var būt brīži, kad vēlaties grupēt saistītus maršrutus kopā (piemēram, `/api/v1`).
To var izdarīt, izmantojot `group` metodi:

```php
Flight::group('/api/v1', function () {
  Flight::route('/lietotāji', function () {
	// Saskanēs ar /api/v1/lietotāji
  });

  Flight::route('/raksti', function () {
	// Saskanēs ar /api/v1/raksti
  });
});
```

Pat varat iegult grupas grupās:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() iegūst mainīgos, tas nenoteiks maršrutu! Skatiet zemāk esošo objekta kontekstu
	Flight::route('GET /lietotāji', function () {
	  // Saskanēs ar GET /api/v1/lietotāji
	});

	Flight::post('/raksti', function () {
	  // Saskanēs ar POST /api/v1/raksti
	});

	Flight::put('/raksti/1', function () {
	  // Saskanēs ar PUT /api/v1/raksti
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() iegūst mainīgos, tas nenoteiks maršrutu! Skatiet zemāk esošo objekta kontekstu
	Flight::route('GET /lietotāji', function () {
	  // Saskanēs ar GET /api/v2/lietotāji
	});
  });
});
```

### Grupēšana ar Objektu Kontekstu

Joprojām varat izmantot maršruta grupēšanu ar `Engine` objektu sekojošajā veidā:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $maršrutētājs) {

  // lietojiet $maršrutētājs mainīgo
  $maršrutētājs->get('/lietotāji', function () {
	// Saskanēs ar GET /api/v1/lietotāji
  });

  $maršrutētājs->post('/raksti', function () {
	// Saskanēs ar POST /api/v1/raksti
  });
});
```

## Translācija

Tagad varat straumēt atbildes klientam, izmantojot `streamWithHeaders()` metodi. Tas ir noderīgi lielo failu sūtīšanai, ilgstošiem procesiem vai lielo atbilžu ģenerēšanai.
Maršrutēt straumēšana tiek## Route Grouping

There may be times when you want to group related routes together (such as `/api/v1`).
You can do this by using the `group` method:

```lv
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Atbilst /api/v1/users
  });

  Flight::route('/posts', function () {
	// Atbilst /api/v1/posts
  });
});
```

You can even nest groups of groups:

```lv
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() iegūst mainīgos, tas nenoteiks maršrutu! Skatiet objekta kontekstu zemāk
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

	// Flight::get() iegūst mainīgos, tas nenoteiks maršrutu! Skatiet objekta kontekstu zemāk
	Flight::route('GET /users', function () {
	  // Atbilst GET /api/v2/users
	});
  });
});
```

### Grouping with Object Context

You can still use route grouping with the `Engine` object in the following way:

```lv
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // lietojiet $router mainīgo
  $router->get('/users', function () {
	// Atbilst GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Atbilst POST /api/v1/posts
  });
});
```