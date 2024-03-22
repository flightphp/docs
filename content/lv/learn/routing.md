# Maršrutēšana

> **Piezīme:** Vai vēlaties uzzināt vairāk par maršrutēšanu? Apskatiet ["kāpēc izvēlēties ietvaru?"](/learn/why-frameworks) lapu, lai iegūtu detalizētāku skaidrojumu.

Vienkārša maršrutēšana Flight ietvarā tiek veikta, saskaņojot URL paraugu ar atpakaļizsaucējfunkciju vai klases un metodes masīvu.

```php
Flight::route('/', function(){
    echo 'sveika pasaule!';
});
```

> Maršruti tiek saskaņoti tajā secībā, kādā tie ir definēti. Pirmajam maršrutam, kas saskan ar pieprasījumu, tiks izpildīts.

### Atpakaļizsaucēji/Funkcijas
Atpakaļizsaucējs var būt jebkura objekta, kas ir izsaukams. Tātad jūs varat izmantot parastu funkciju:

```php
function sveiki(){
    echo 'sveika pasaule!';
}

Flight::route('/', 'sveiki');
```

### Klases
Jūs varat izmantot arī klases statisko metodi:

```php
class Sveiciens {
    public static function sveiki() {
        echo 'sveika pasaule!';
    }
}

Flight::route('/', [ 'Sveiciens','sveiki' ]);
```

Vai izveidojot objektu vispirms un tad izsaucot metodi:

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

Flight::route('/', [ $sveiciens, 'sveiki' ]);
// Jūs varat to darīt arī bez objekta izveidošanas pirmkārt
// Piezīme: Konstruktorā netiks ievietoti argumenti
Flight::route('/', [ 'Sveiciens', 'sveiki' ]);
```

#### Atkarību ievietošana, izmantojot DIC (Dependency Injection Container)
Ja vēlaties izmantot atkarību ievietošanu, izmantojot konteineru (PSR-11, PHP-DI, Dice, u.c.), vienīgā veida maršruti, kur tas ir pieejams, ir vai nu tieši izveidot objektu pats un izmantot konteineri, lai izveidotu savu objektu, vai arī varat izmantot virknes, lai definētu klasi un metodi, ko izsaukt. Varat doties uz [Atkarību ievietošana](/learn/extending) lapu, lai iegūtu vairāk informācijas.

Šeit ir ātrs piemērs:

```php

use flight\database\PdoWrapper;

// Sveiciens.php
class Sveiciens
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;
	}

	public function sveiki(int $id) {
		// darīt kaut ko ar $this->pdoWrapper
		$vards = $this->pdoWrapper->fetchField("SELECT vards FROM lietotaji WHERE id = ?", [ $id ]);
		echo "Sveiki, pasaule! Mani sauc {$vards}!";
	}
}

// index.php

// Iestatiet konteineru ar visiem nepieciešamajiem parametriem
// Skatiet Atkarību ievietošanas lapu, lai iegūtu plašāku informāciju par PSR-11
$dice = new \Dice\Dice();

// Nedrīkst aizmirst pārvietot mainīgo saglabāšanai ar '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Reģistrēt konteineru apstrādes pasūtījumu
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Maršruti kā parasti
Flight::route('/sveiki/@id', [ 'Sveiciens', 'sveiki' ]);
// vai
Flight::route('/sveiki/@id', 'Sveiciens->sveiki');
// vai
Flight::route('/sveiki/@id', 'Sveiciens::sveiki');

Flight::start();
```

## Metodes Maršrutēšana

Pēc noklusējuma maršruta paraugi tiek saskaņoti pret visiem pieprasījuma metodēm. Jūs varat reaģēt uz konkrētām metodēm, novietojot identifikatoru pirms URL.

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

Jūs varat arī pievienot vairākas metodes vienai atpakaļizsaucējfunkcijai, izmantojot `|` atdalītāju:

```php
Flight::route('GET|POST /', function () {
  echo 'Es saņēmu vai nu GET vai POST pieprasījumu.';
});
```

Turklāt jūs varat iegūt Maršrutētāja objektu, kuram ir dažas palīgmetodes, ko varat izmantot:

```php

$router = Flight::router();

// atkārto visus metodus
$router->map('/', function() {
	echo 'sveika pasaule!';
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

## Regulārās izteiksmes

Jūs varat izmantot regulārās izteiksmes savos maršrutos:

```php
Flight::route('/lietotajs/[0-9]+', function () {
  // Tas saskanēs ar /lietotajs/1234
});
```

Kaut arī šī metode ir pieejama, rekomendējams izmantot nosauktos parametrus vai
nosauktos parametrus ar regulārām izteiksmēm, jo tie ir lasāmāki un vieglāk uzturami.

## Nosauktie Parametri

Jūs varat norādīt nosauktus parametrus savos maršrutos, kas tiks nodoti
jūsu atpakaļizsaucējfonkcijai.

```php
Flight::route('/@vards/@id', function (string $vards, string $id) {
  echo "sveiki, $vards ($id)!";
});
```

Jūs varat iekļaut arī regulāras izteiksmes savos nosauktajos parametros, izmantojot
`:` atdalītāju:

```php
Flight::route('/@vards/@id:[0-9]{3}', function (string $vards, string $id) {
  // Tas saskanēs ar /bobs/123
  // Bet nesaskanēs ar /bobs/12345
});
```

> **Piezīme:** Nesavienojam atbilstošos regex grupas `()` ar nosauktajiem parametriem. :'\(

## Neobligātie Parametri

Jūs varat norādīt nosauktos parametrus, kas ir neobligāti saskaņošanai, ietverot
segmentus iekavās.

```php
Flight::route(
  '/blogs(/@gads(/@menesis(/@diena)))',
  function(?string $gads, ?string $menesis, ?string $diena) {
    // Tas saskanēs ar šādiem URL:
    // /blogs/2012/12/10
    // /blogs/2012/12
    // /blogs/2012
    // /blogs
  }
);
```

Jebkuri neobligāti parametri, kas nesaskan, tiks nodoti kā `NULL`.

## Aizstādītāji

Saskanēšana tiek veikta tikai individuāliem URL segmentiem. Ja vēlaties saskanēt ar vairākiem
segmentiem, varat izmantot `*` aizstādītāju.

```php
Flight::route('/blogs/*', function () {
  // Tas saskanēs ar /blogs/2000/02/01
});
```

Lai saskanētu visas pieprasījumus ar vienu atpakaļizsaucējfonkciju, varat:

```php
Flight::route('*', function () {
  // Darīt kaut ko
});
```

## Pāreja

Jūs varat nodot izpildi nākamajam saskanētajam maršrutam, atgriežot `true`
no jūsu atpakaļizsaucējfunkcijas.

```php
Flight::route('/lietotajs/@vards', function (string $vards) {
  // Pārbaudiet nosacījumu
  if ($vards !== "Jānis") {
    // Turpiniet uz nākamo maršrutu
    return true;
  }
});

Flight::route('/lietotajs/*', function () {
  // Tas tiks izsaukts
});
```

## Maršrutu Piešķiršana

Jūs varat piešķirt aliāsu maršrutam, lai URL varētu dinamiski ģenerēt vēlāk jūsu kodā (piemēram, šablonam).

```php
Flight::route('/lietotaji/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotaja_skats');

// vēlāk jūsu kodā
Flight::getUrl('lietotaja_skats', [ 'id' => 5 ]); // atgriezīs '/lietotaji/5'
```

Tas ir īpaši noderīgi, ja jūsu URL mainās. Šajā piemērā, lecam uz gadījumu, ka lietotāji tiek pārvietots uz `/admin/lietotaji/@id` vietā.
Ar aliāšu izmantošanu jūs nevajadzēs mainīt vietu, kur atsauces alianses, jo alianse tagad atgriezīs `/admin/lietotaji/5` kā minēts iepriekšējā piemērā.

Maršruta aliāse darbojas arī grupās:

```php
Flight::group('/lietotaji', function() {
    Flight::route('/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotaja_skats');
});


// vēlāk jūsu kodā
Flight::getUrl('lietotaja_skats', [ 'id' => 5 ]); // atgriezīs '/lietotaji/5'
```

## Maršruta Informācija

Ja vēlaties pārbaudīt saskanēto maršruta informāciju, jūs varat pieprasīt, lai maršrutas objekts tiktu nodots jūsu atpakaļizsaucējfunkcijai, padodot `true` kā trešo parametru maršruta metodē. Maršruta objekts vienmēr tiks nodots kā pēdējais parametrs jūsu atpakaļizsaucējfunkcijai.

```php
Flight::route('/', function(\flight\net\Route $maršruts) {
  // Masīvs ar saskaņotajām HTTP metodēm
  $maršruts->metodes;

  // Masīvs ar nosauktajiem parametriem
  $maršruts->parametri;

  // Saskaņošanas regulārā izteiksme
  $maršruts->regex;

  // Iekļauj jebkurus '*' izmantotos URL paraugā
  $maršruts->lāpne;

  // Parāda URL ceļu....ja jums patiešām tas ir nepieciešams
  $maršruts->paraugs;

  // Parāda, kāds starpprogrammatūru ir piešķirts
  $maršruts->starpprogrammatūra;

  // Parāda piešķirto aliāsu šim maršrutam
  $maršruts->aliāsa;
}, true);
```

## Maršruta Grupēšana

Var gadīties, ka vēlaties grupēt saistītus maršrutus kopā (piemēram, `/api/v1`).
To var izdarīt, izmantojot `group` metodi:

```php
Flight::group('/api/v1', function () {
  Flight::route('/lietotaji', function () {
	// Saskanēs /api/v1/lietotaji
  });

  Flight::route('/ieraksti', function () {
	// Saskanēs /api/v1/ieraksti
  });
});
```

Pat varat ieienišanas grupas grupas:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() saņem mainīgos, tas neatrod maršrutu! Skatiet objekta kontekstu zemāk
	Flight::route('GET /lietotaji', function () {
	  // Saskan ar GET /api/v1/lietotaji
	});

	Flight::post('/ieraksti', function () {
	  // Saskan ar POST /api/v1/ieraksti
	});

	Flight::put('/ieraksti/1', function () {
	  // Saskan ar PUT /api/v1/ieraksti
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() saņem mainīgos, tas neatrod maršrutu! Skatiet objekta kontekstu zemāk
	Flight::route('GET /lietotaji', function () {
	  // Saskan ar GET /api/v2/lietotaji
	});
  });
});
```

### Grupēšana ar Objekta Kontekstu

Joprojām varat izmantot maršruta grupēšanu ar `Engine` objektu sekojošā veidā:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $maršrutētājs) {

  // izmantot $maršrutētājs mainīgo
  $maršrutētājs->get('/lietotaji', function () {
	// Saskan ar GET /api/v1/lietotaji
  });

  $maršrutētājs->post('/ieraksti', function () {
	// Saskan ar POST /api/v1/ieraksti
  });
});
```

## Strīmošana

Tagad jūs varat strādāt ar atbildēm klientam, izmantojot `streamWithHeaders()` metodi. Tas ir noderīgi lielu failu sūtīšanai, ilgstošiem procesiem vai lielu atbildes ģenerēšanai. Maršruta strīmošana tiek apstrādāta nedaudz atšķirīgi nekā parasts maršruts.

> **Piezīme:** Strīmošanas atbildes ir pieejamas tikai tad, ja jums ir [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) iestatīts uz false.

```php
Flight::route('/strimo-lietotājus# Maršrutēšana

> **Piezīme:** Vai vēlaties uzzināt vairāk par maršrutēšanu? Apskatiet ["kāpēc izvēlēties ietvaru?"](/learn/why-frameworks) lapu, lai iegūtu detalizētāku skaidrojumu.

Vienkārša maršrutēšana Flight ietvarā tiek veikta, saskaņojot URL paraugu ar atpakaļizsaucējfunkciju vai klases un metodes masīvu.

```php
Flight::route('/', function(){
    echo 'sveika pasaule!';
});
```

> Maršruti tiek saskaņoti tajā secībā, kādā tie ir definēti. Pirmajam maršrutam, kas saskan ar pieprasījumu, tiks izpildīts.

### Atpakaļizsaucēji/Funkcijas
Atpakaļizsaucējs var būt jebkura objekta, kas ir izsaukams. Tātad jūs varat izmantot parastu funkciju:

```php
function sveiki(){
    echo 'sveika pasaule!';
}

Flight::route('/', 'sveiki');
```

### Klases
Jūs varat izmantot arī klases statisko metodi:

```php
class Sveiciens {
    public static function sveiki() {
        echo 'sveika pasaule!';
    }
}

Flight::route('/', [ 'Sveiciens','sveiki' ]);
```

Vai izveidojot objektu vispirms un tad izsaucot metodi:

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

Flight::route('/', [ $sveiciens, 'sveiki' ]);
// Jūs varat to darīt arī bez objekta izveidošanas pirmkārt
// Piezīme: Konstruktorā netiks ievietoti argumenti
Flight::route('/', [ 'Sveiciens', 'sveiki' ]);
```

#### Atkarību ievietošana, izmantojot DIC (Dependency Injection Container)
Ja vēlaties izmantot atkarību ievietošanu, izmantojot konteineru (PSR-11, PHP-DI, Dice, u.c.), vienīgā veida maršruti, kur tas ir pieejams, ir vai nu tieši izveidot objektu pats un izmantot konteineri, lai izveidotu savu objektu vai varat izmantot virknes, lai definētu klasi un metodi, ko izsaukt. Varat doties uz [Atkarību ievietošana](/learn/extending) lapu, lai iegūtu vairāk informācijas.

Šeit ir ātrs piemērs:

```php

use flight\database\PdoWrapper;

// Sveiciens.php
class Sveiciens
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;
	}

	public function sveiki(int $id) {
		// darīt kaut ko ar $this->pdoWrapper
		$vards = $this->pdoWrapper->fetchField("SELECT vards FROM lietotaji WHERE id = ?", [ $id ]);
		echo "Sveiki, pasaule! Mani sauc {$vards}!";
	}
}

// index.php

// Iestatiet konteineru ar visiem nepieciešamajiem parametriem
// Skatiet Atkarību ievietošanas lapu, lai iegūtu plašāku informāciju par PSR-11
$dice = new \Dice\Dice();

// Nedrīkst aizmirst pārvietot mainīgo saglabāšanai ar '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Reģistrēt konteineru apstrādes pasūtījumu
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Maršruti kā parasti
Flight::route('/sveiki/@id', [ 'Sveiciens', 'sveiki' ]);
// vai
Flight::route('/sveiki/@id', 'Sveiciens->sveiki');
// vai
Flight::route('/sveiki/@id', 'Sveiciens::sveiki');

Flight::start();
```

## Metodes Maršrutēšana

Pēc noklusējuma maršruta paraugi tiek saskaņoti pret visiem pieprasījuma metodēm. Jūs varat reaģēt uz konkrētām metodēm, novietojot identifikatoru pirms URL.

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

Jūs varat arī pievienot vairākas metodes vienai atpakaļizsaucējfunkcijai, izmantojot `|` atdalītāju:

```php
Flight::route('GET|POST /', function () {
  echo 'Es saņēmu vai nu GET vai POST pieprasījumu.';
});
```

Turklāt jūs varat iegūt Maršrutētāja objektu, kuram ir dažas palīgmetodes, ko varat izmantot:

```php

$router = Flight::router();

// atkārto visus metodus
$router->map('/', function() {
	echo 'sveika pasaule!';
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

## Regulārās izteiksmes

Jūs varat izmantot regulārās izteiksmes savos maršrutos:

```php
Flight::route('/lietotajs/[0-9]+', function () {
  // Tas saskanēs ar /lietotajs/1234
});
```

Kaut arī šī metode ir pieejama, rekomendējams izmantot nosauktos parametrus vai
nosauktos parametrus ar regulārām izteiksmēm, jo tie ir lasāmāki un vieglāk uzturami.

## Nosauktie Parametri

Jūs varat norādīt nosauktus parametrus savos maršrutos, kas tiks nodoti
jūsu atpakaļizsaucējfonkcijai.

```php
Flight::route('/@vards/@id', function (string $vards, string $id) {
  echo "hello, $vards ($id)!";
});
```

Jūs varat iekļaut arī regulāras izteiksmes savos nosauktajos parametros, izmantojot
`:` atdalītāju:

```php
Flight::route('/@vards/@id:[0-9]{3}', function (string $vards, string $id) {
  // Tas saskanēs ar /bob/123
  // Bet nesaskanēs ar /bob/12345
});
```

> **Piezīme:** Nesavienojam atbilstošos regex grupas `()` ar nosauktajiem parametriem. :'\(

## Neobligātie Parametri

Jūs varat norādīt nosauktos parametrus, kas ir neobligāti saskaņošanai, ietverot
segmentus iekavās.

```php
Flight::route(
  '/blog(/@gads(/@menesis(/@diena)))',
  function(?string $gads, ?string $menesis, ?string $diena) {
    // Tas saskanēs ar šādiem URL:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Jebkuri neobligāti parametri, kas nesaskan, tiks nodoti kā `NULL`.

## Aizstādītāji

Saskanēšana tiek veikta tikai individuāliem URL segmentiem. Ja vēlaties saskanēt ar vairākiem
segmentiem, varat izmantot `*` aizstādītāju.

```php
Flight::route('/blog/*', function () {
  // Tas saskanēs ar /blog/2000/02/01
});
```

Lai saskanētu visas pieprasījumus ar vienu atpakaļizsaucējfonkciju, varat:

```php
Flight::route('*', function () {
  // Do something
});
```

## Pāreja

Jūs varat nodot izpildi nākamajam saskanētajam maršrutam, atgriežot `true`
no jūsu atpakaļizsaucējfunkcijas.

```php
Flight::route('/lietotajs/@vards', function (string $vards) {
  // Pārbaudiet nosacījumu
  if ($vards !== "Bob") {
    // Turpiniet uz nākamo maršrutu
    return true;
  }
});

Flight::route('/lietotajs/*', function () {
  // This will get called
});
```

## Maršruta Aliasing

Jūs varat piešķirt aliāsu maršrutam, lai URL varētu dinamiski ģenerēt vēlāk jūsu kodā (piemēram, šablonam).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// later in code somewhere
Flight::getUrl('user_view', [ 'id' => 5 ]); // will return '/users/5'
```

This is especially helpful if your URL happens to change. In the above example, lets say that users was moved to `/admin/users/@id` instead.
With aliasing in place, you don't have to change anywhere you reference the alias because the alias will now return `/admin/users/5` like in the 
example above.

Route aliasing still works in groups as well:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// later in code somewhere
Flight::getUrl('user_view', [ 'id' => 5 ]); // will return '/users/5'
```

## Route Info

If you want to inspect the matching route information, you can request for the route
object to be passed to your callback by passing in `true` as the third parameter in
the route method. The route object will always be the last parameter passed to your
callback function.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array of HTTP methods matched against
  $route->methods;

  // Array of named parameters
  $route->params;

  // Matching regular expression
  $route->regex;

  // Contains the contents of any '*' used in the URL pattern
  $route->splat;

  // Shows the url path....if you really need it
  $route->pattern;

  // Shows what middleware is assigned to this
  $route->middleware;

  // Shows the alias assigned to this route
  $route->alias;
}, true);
```

## Route Grouping

There may be times when you want to group related routes together (such as `/api/v1`).
You can do this by using the `group` method:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Matches /api/v1/users
  });

  Flight::route('/posts', function () {
	// Matches /api/v1/posts
  });
});
```

You can even nest groups of groups:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() gets variables, it doesn't set a route! See object context below
	Flight::route('GET /users', function () {
	  // Matches GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Matches POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Matches PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() gets variables, it doesn't set a route! See object context below
	Flight::route('GET /users', function () {
	  // Matches GET /api/v2/users
	});
  });
});
```

### Grouping with Object Context

You can still use route grouping with the `Engine` object in the following way:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // user the $router variable
  $router->get('/users', function () {
	// Matches GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Matches POST /api/v1/posts
  });
});
```

## Streaming

You can now stream responses to the client using the `streamWithHeaders()` method. 
This is useful for sending large files, long running processes, or generating large responses. 
Streaming a route is handled a little differently than a regular route.

> **Note:** Streaming responses is only available if you have [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) set to false.

```php
Flight::route('/stream-users', function() {

	// If you have additional headers to set here after the route has executed
	// you must define them before anything is echoed out.
	// They must all be a raw call to the header() function or 
	// a call to Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="users.json"');
	// or
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="users.json"');

	// however you pull your data, just as an example...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// This is required to send the data to the client
		ob_flush();
	}
	echo '}';

// This is how you'll set the headers before you start streaming.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	// optional status code, defaults to 200
	'status' => 200
]);
```