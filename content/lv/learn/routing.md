# Maršrutēšana

Maršrutēšana `Flight` tiek veikta, sakrāsojot URL paraugu ar atzvana funkciju.

```php
Flight::route('/', function(){
    echo 'sveika, pasaule!';
});
```

Atzvana funkcija var būt jebkāds objekts, ar kuru var veikt atzvanu. Tādēļ varat izmantot parasto funkciju:

```php
function sveiki(){
    echo 'sveika, pasaule!';
}

Flight::route('/', 'sveiki');
```

Vai arī klases metodi:

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
class Sveiciens
{
    public function __construct() {
        $this->vards = 'Jānis Bērziņš';
    }

    public function sveiki() {
        echo "Sveiki, {$this->vards}!";
    }
}

$sveiciens = new Sveiciens();

Flight::route('/', array($sveiciens, 'sveiki'));
```

Maršruti tiek sakrāsoti atbilstoši definētajai secībai. Pirmais maršruts, kas atbilst pieprasījumam, tiks izsaukts.

## Metodes Maršrutēšana

Pēc noklusējuma, maršruta paraugi tiek salīdzināti ar visām pieprasījuma metodēm. Varat reaģēt
uz konkrētām metodēm, ievietojot identifikatoru pirms URL.

```php
Flight::route('GET /', function () {
  echo 'Es saņēmu GET pieprasījumu.';
});

Flight::route('POST /', function () {
  echo 'Es saņēmu POST pieprasījumu.';
});
```

Varat arī piešķirt vairākas metodes vienai atzvana funkcijai, izmantojot `|` atdalītāju:

```php
Flight::route('GET|POST /', function () {
  echo 'Es saņēmu vai nu GET vai POST pieprasījumu.';
});
```

## Regulārās Izteiksmes

Varat izmantot regulārās izteiksmes savos maršrutos:

```php
Flight::route('/lietotājs/[0-9]+', function () {
  // Tas saskanēs ar /lietotājs/1234
});
```

## Nosauktie Parametri

Varat norādīt nosauktus parametrus savos maršrutos, kas tiks padoti
jūsu atzvana funkcijai.

```php
Flight::route('/@vards/@id', function (string $vards, string $id) {
  echo "sveiki, $vards ($id)!";
});
```

Varat iekļaut regulārās izteiksmes arī ar nosauktajiem parametriem, izmantojot
`:` atdalītāju:

```php
Flight::route('/@vards/@id:[0-9]{3}', function (string $vards, string $id) {
  // Tas saskanēs ar /bobs/123
  // Bet nesaskanēs ar /bobs/12345
});
```

Saskaņošana ar nosauktajiem parametriem lietojot regex grupas `()` netiek atbalstīta.

## Izejas Parametri

Varat norādīt nosauktos parametrus, kas ir neobligāti saskaņošanai, ietveroš
segmentus iekavās.

```php
Flight::route(
  '/blogs(/@gads(/@mēnesis(/@diena)))',
  function(?string $gads, ?string $mēnesis, ?string $diena) {
    // Tas saskanēs ar sekojošiem URL:
    // /blogs/2012/12/10
    // /blogs/2012/12
    // /blogs/2012
    // /blogs
  }
);
```

Jebkuri neobligāti parametri, kas nesaskanēs, tiks padoti kā NULL vērtības.

## Vaļēji Simboli

Saskaņošana tiek veikta tikai ar individuāliem URL segmentiem. Ja jums ir nepieciešams saskaņot vairākus
segmentus, varat izmantot `*` vaļējo simbolu.

```php
Flight::route('/blogs/*', function () {
  // Tas saskanēs ar /blogs/2000/02/01
});
```

Lai maršrutētu visus pieprasījumus uz vienu atzvana funkciju, varat:

```php
Flight::route('*', function () {
  // Izdarīt kaut ko
});
```

## Pāreja

Jūs varat pāreju nodot tālāk nākamajam saskanējušajam maršrutam, atgriežot `true` no
jūsu atzvana funkcijas.

```php
Flight::route('/lietotājs/@vards', function (string $vards) {
  // Pārbaudiet kādu nosacījumu
  if ($vards !== "Bobs") {
    // Turpināt uz nākamo maršrutu
    return true;
  }
});

Flight::route('/lietotājs/*', function () {
  // Tas tiks izsaukts
});
```

## Maršruta Informācija

Ja vēlaties pārbaudīt saskanējošā maršruta informāciju, jūs varat pieprasīt maršruta
objektu, kas tiks padots jūsu atzvana funkcijai, padodot `true` kā trešo parametru
maršruta metodei. Maršruta objekts vienmēr tiks nosūtīts kā pēdējais parameters jūsu
atzvana funkcijai.

```php
Flight::route('/', function(\flight\net\Route $maršruts) {
  // Masīvs ar saskanētajām HTTP metodēm
  $maršruts->metodes;

  // Masīvs ar nosauktajiem parametriem
  $maršruts->parametri;

  // Saskanētā regulārā izteiksme
  $maršruts->regex;

  // Satur jebkura `*` izmantota URL paraugā saturs
  $maršruts->splat;
}, true);
```

## Maršruta Grupēšana

Var gadīties, ka vēlaties grupēt saistītus maršrutus kopā (piemēram, `/api/v1`).
To var izdarīt, izmantojot `group` metodi:

```php
Flight::group('/api/v1', function () {
  Flight::route('/lietotāji', function () {
	// Saskan ar /api/v1/lietotāji
  });

  Flight::route('/ziņas', function () {
	// Saskan ar /api/v1/ziņas
  });
});
```

Pat variet iekšā grupas grupās:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() saņem mainīgos, tas nenosaka maršrutu! Skatīt objekta kontekstu zemāk
	Flight::route('GET /lietotāji', function () {
	  // Saskan ar GET /api/v1/lietotāji
	});

	Flight::post('/ziņas', function () {
	  // Saskan ar POST /api/v1/ziņas
	});

	Flight::put('/ziņas/1', function () {
	  // Saskan ar PUT /api/v1/ziņas
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() saņem mainīgos, tas nenosaka maršrutu! Skatīt objekta kontekstu zemāk
	Flight::route('GET /lietotāji', function () {
	  // Saskan ar GET /api/v2/lietotāji
	});
  });
});
```

### Grupēšana ar Objekta Kontekstu

Joprojām varat izmantot maršruta grupēšanu ar `Engine` objektu šādā veidā:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $marsrutētājs) {
  $marsrutētājs->get('/lietotāji', function () {
	// Saskan ar GET /api/v1/lietotāji
  });

  $marsrutētājs->post('/ziņas', function () {
	// Saskan ar POST /api/v1/ziņas
  });
});
```

## Maršruta Nosaukšana

Varat piešķirt nosaukumu maršrutam, lai URL varētu dinamiski tikt ģenerēts vēlāk jūsu kodā (piemēram, kā šablons).

```php
Flight::route('/lietotāji/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotāja_apskats');

// vēlāk kādā vietā kodā
Flight::getUrl('lietotāja_apskats', [ 'id' => 5 ]); // atgriezīs '/lietotāji/5'
```

Šis ir īpaši noderīgs, ja jūsu URL gadījumā mainās. Iepriekš minētajā piemērā, pieņemsim, ka lietotāji tika pārcelti uz `/admin/lietotāji/@id` ietv...

Maršruta nosaukšana joprojām darbojas grupās arī:

```php
Flight::group('/lietotāji', function() {
    Flight::route('/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotāja_apskats');
});


// vēlāk kādā vietā kodā
Flight::getUrl('lietotāja_apskats', [ 'id' => 5 ]); // atgriezīs '/lietotāji/5'
```

## Maršruta Vidējaislāsme
`Flight` atbalsta maršruta un grupas maršruta vidējaislāsmi. Vidējaislis ir funkcija, kas tiek izpildīta pirms (vai pēc) maršruta atzvoņa funkcijas. Tas ir lielisks veids, kā pievienot API autentifikācijas pārbaudes jūsu kodā vai validēt, vai lietotājam ir tiesības piekļūt maršrutam.

Šeit ir pamata piemērs:

```php
// Ja jūs sniedzat tikai anonīmu funkciju, tā tiks izpildīta pirms maršruta atzvoņa funkcijas.
// nav "pēc" vidējaisļasmas funkcijas, izņemot klases (skatīt zemāk)
Flight::route('/ceļš', function() { echo ' Šeit esmu! '; })->addMiddleware(function() {
	echo 'Vidējaislis pirmais!';
});

Flight::start();

// Tas izvadīs "Vidējaislis pirmais! Šeit esmu!"
```

Pastāv daži ļoti svarīgi punkti par vidējaislāsmi, par kuriem jums ir jābūt informētam, pirms to izmantojat:
- Vidējaislislasmas funkcijas tiek izpildītas tā, kā tās ir pievienotas maršrutam. Izpildīšana ir līdzīga tam, kā to apstrādā [Slim Framework](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Pirms tiek izpildīti tā kā pievienoti, un pēc tam tiek izpildīti apgrieztā secībā.
- Ja jūsu vidējaislislasma funkcija atgriež `false`, visa izpildes process tiek apturēts un tiek izvadīta 403 Aizliegts kļūda. Varbūt vēlēsieties to apstrādāt eleganti ar `Flight::redirect()` vai kādu līdzīgu metodi.
- Ja jums ir nepieciešami parametri no jūsu maršruta, tie tiks padoti vienā masīvā jūsu vidējaislāsmas funkcijai. (`function($params) { ... }` vai `public function before($params) {}`). Iemesls tam ir tas, ka varat strukturēt savus parametrus grupās, un kādās no tām, jūsu parametri faktiski var parādīties citā secībā, kas pārkāptu vidējaislāsmas funkciju, atsaucoties uz nepareizo parametru. Šādā veidā, varat piekļūt tiem pēc vārda, nevis pozīcijas.

### Vidējaislasmu Klases

Vidējaislasmas var reģistrēt arī kā klasi. Ja jums nepieciešama "pēc" funkcionalitāte, jāizmanto klase.

```php
class ManaVidējaislisma {
	public function before($params) {
		echo 'Vidējaislisma pirmais!';
	}

	public function after($params) {
		echo 'Vidējaislisma pēdējais!';
	}
}

$ManaVidējaislisma = new ManaVidējaislisma();
Flight::route('/ceļš', function() { echo ' Šeit esmu! '; })->addMiddleware($ManaVidējaislisma); // vai arī ->addMiddleware([ $ManaVidējaislisma, $ManaVidējaislisma2 ]);

Flight::start();

// Tas parādīs "Vidējaislisma pirmais! Šeit esmu! Vidējaislisma pēdējais!"
```

### Vidējaislasmu Grupas

Varat pievienot maršruta grupu, un tad katram maršrutam šajā grupā būs arī vienāda vidējaislasmas funkcionalitāte. Tas ir noderīgi, ja jums ir nepieciešams grupēt virkni maršrutu pēc, piemēram, Autentifikācijas vidējaislasmu, lai pārbaudītu API atslēgu galvenē.

```php

// pievienots grupas metodei beigās
Flight::group('/api', function() {
    Flight::route('/lietotāji', function() { echo 'lietotāji'; }, false, 'lietotāji');
	Flight::route('/lietotāji/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotāja_apskats');
}, [ new ApiAuthVidējaislisma() ]);
```