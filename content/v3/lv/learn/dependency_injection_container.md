# Atkarību Injekcijas Konteiners

## Pārskats

Atkarību Injekcijas Konteiners (DIC) ir spēcīgs uzlabojums, kas ļauj jums pārvaldīt
jūsu lietojumprogrammas atkarības.

## Saprašana

Atkarību Injekcija (DI) ir galvenā koncepcija mūsdienu PHP ietvaros un tiek
izmantota, lai pārvaldītu objektu instantiāciju un konfigurāciju. Daži DIC
bibliotēku piemēri ir: [flightphp/container](https://github.com/flightphp/container), [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), 
[PHP-DI](http://php-di.org/), un [league/container](https://container.thephpleague.com/).

DIC ir izsmalcināts veids, kā ļaut jums izveidot un pārvaldīt savas klases centralizētā
vietā. Tas ir noderīgi, kad jums jānodod tas pats objekts uz
vairākām klasēm (piemēram, jūsu kontrolieriem vai starpprogrammatūras programmatūrai).

## Pamata Izmantošana

Vecais veids, kā darīt lietas, var izskatīties šādi:
```php

require 'vendor/autoload.php';

// klase, lai pārvaldītu lietotājus no datubāzes
class UserController {

	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function view(int $id) {
		$stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
		$stmt->execute(['id' => $id]);

		print_r($stmt->fetch());
	}
}

// jūsu routes.php failā

$db = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');

$UserController = new UserController($db);
Flight::route('/user/@id', [ $UserController, 'view' ]);
// citi UserController maršruti...

Flight::start();
```

No iepriekšējā koda var redzēt, ka mēs izveidojam jaunu `PDO` objektu un nododam to
mūsu `UserController` klasei. Tas ir labi mazai lietojumprogrammai, bet kad jūsu
lietojumprogramma aug, jūs atklāsiet, ka izveidojat vai nododat to pašu `PDO` 
objektu vairākās vietās. Tieši šeit DIC nāk palīgā.

Šeit ir tas pats piemērs, izmantojot DIC (izmantojot Dice):
```php

require 'vendor/autoload.php';

// tā pati klase kā iepriekš. Nekas nav mainījies
class UserController {

	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function view(int $id) {
		$stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
		$stmt->execute(['id' => $id]);

		print_r($stmt->fetch());
	}
}

// izveidojiet jaunu konteineru
$container = new \Dice\Dice;

// pievienojiet noteikumu, lai pastāstītu konteineram, kā izveidot PDO objektu
// neaizmirstiet to piešķirt atpakaļ sev, kā zemāk!
$container = $container->addRule('PDO', [
	// shared nozīmē, ka tas pats objekts tiks atgriezts katru reizi
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// Tas reģistrē konteinera apstrādātāju, lai Flight zinātu to izmantot.
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// tagad mēs varam izmantot konteineru, lai izveidotu mūsu UserController
Flight::route('/user/@id', [ UserController::class, 'view' ]);

Flight::start();
```

Es deru, ka jūs varētu domāt, ka piemēram ir pievienots daudz papildu koda.
Burvība nāk tad, kad jums ir cits kontrolieris, kam nepieciešams `PDO` objekts. 

```php

// Ja visiem jūsu kontrolieriem ir konstruktors, kam nepieciešams PDO objekts
// katrs no maršrutiem zemāk automātiski to saņems injicēts!!!
Flight::route('/company/@id', [ CompanyController::class, 'view' ]);
Flight::route('/organization/@id', [ OrganizationController::class, 'view' ]);
Flight::route('/category/@id', [ CategoryController::class, 'view' ]);
Flight::route('/settings', [ SettingsController::class, 'view' ]);
```

Papildu bonuss, izmantojot DIC, ir tas, ka vienības testēšana kļūst daudz vieglāka. Jūs varat
izveidot viltotu objektu un nodot to jūsu klasei. Tas ir liels ieguvums, kad jūs
rakstāt testus savai lietojumprogrammai!

### Centralizēta DIC apstrādātāja izveide

Jūs varat izveidot centralizētu DIC apstrādātāju savā servisu failā, paplašinot [/learn/extending] jūsu lietojumprogrammu. Šeit ir piemērs:

```php
// services.php

// izveidojiet jaunu konteineru
$container = new \Dice\Dice;
// neaizmirstiet to piešķirt atpakaļ sev, kā zemāk!
$container = $container->addRule('PDO', [
	// shared nozīmē, ka tas pats objekts tiks atgriezts katru reizi
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// tagad mēs varam izveidot kartējamu metodi, lai izveidotu jebkuru objektu. 
Flight::map('make', function($class, $params = []) use ($container) {
	return $container->create($class, $params);
});

// Tas reģistrē konteinera apstrādātāju, lai Flight zinātu to izmantot kontrolieriem/starpprogrammatūrai
Flight::registerContainerHandler(function($class, $params) {
	Flight::make($class, $params);
});


// pieņemsim, ka mums ir šāda parauga klase, kas prasa PDO objektu konstruktorā
class EmailCron {
	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function send() {
		// kods, kas nosūta e-pastu
	}
}

// Un beidzot jūs varat izveidot objektus, izmantojot atkarību injekciju
$emailCron = Flight::make(EmailCron::class);
$emailCron->send();
```

### `flightphp/container`

Flight ir spraudnis, kas nodrošina vienkāršu PSR-11 atbilstošu konteineru, ko jūs varat izmantot, lai apstrādātu
jūsu atkarību injekciju. Šeit ir ātrs piemērs, kā to izmantot:

```php

// index.php piemēram
require 'vendor/autoload.php';

use flight\Container;

$container = new Container;

$container->set(PDO::class, fn(): PDO => new PDO('sqlite::memory:'));

Flight::registerContainerHandler([$container, 'get']);

class TestController {
  private PDO $pdo;

  function __construct(PDO $pdo) {
    $this->pdo = $pdo;
  }

  function index() {
    var_dump($this->pdo);
	// pareizi izvadīs šo!
  }
}

Flight::route('GET /', [TestController::class, 'index']);

Flight::start();
```

#### Uzlabota `flightphp/container` Izmantošana

Jūs varat arī rekursīvi atrisināt atkarības. Šeit ir piemērs:

```php
<?php

require 'vendor/autoload.php';

use flight\Container;

class User {}

interface UserRepository {
  function find(int $id): ?User;
}

class PdoUserRepository implements UserRepository {
  private PDO $pdo;

  function __construct(PDO $pdo) {
    $this->pdo = $pdo;
  }

  function find(int $id): ?User {
    // Implementācija ...
    return null;
  }
}

$container = new Container;

$container->set(PDO::class, static fn(): PDO => new PDO('sqlite::memory:'));
$container->set(UserRepository::class, PdoUserRepository::class);

$userRepository = $container->get(UserRepository::class);
var_dump($userRepository);

/*
object(PdoUserRepository)#4 (1) {
  ["pdo":"PdoUserRepository":private]=>
  object(PDO)#3 (0) {
  }
}
 */
```

### DICE

Jūs varat arī izveidot savu DIC apstrādātāju. Tas ir noderīgi, ja jums ir pielāgots
konteiners, ko vēlaties izmantot, kas nav PSR-11 (Dice). Skatiet 
[pamata izmantošanu](#basic-usage) sadaļu, kā to izdarīt.

Turklāt ir
daži noderīgi noklusējumi, kas padarīs jūsu dzīvi vieglāku, izmantojot Flight.

#### Engine Instances

Ja jūs izmantojat `Engine` instanci savos kontrolieros/starpprogrammatūrā, šeit ir
kā jūs to konfigurētu:

```php

// Kur kur jūsu bootstrap failā
$engine = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// Šeit jūs nododāt instanci
		Engine::class => $engine
	]
]);

$engine->registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// Tagad jūs varat izmantot Engine instanci savos kontrolieros/starpprogrammatūrā

class MyController {
	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function index() {
		$this->app->render('index');
	}
}
```

#### Citu Klases Pievienošana

Ja jums ir citas klases, ko vēlaties pievienot konteineram, ar Dice tas ir viegli, jo tās automātiski atrisināsies ar konteineru. Šeit ir piemērs:

```php

$container = new \Dice\Dice;
// Ja jums nav jāinjekē atkarības savās klasēs
// jums nav jādefinē nekas!
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

class MyCustomClass {
	public function parseThing() {
		return 'thing';
	}
}

class UserController {

	protected MyCustomClass $MyCustomClass;

	public function __construct(MyCustomClass $MyCustomClass) {
		$this->MyCustomClass = $MyCustomClass;
	}

	public function index() {
		echo $this->MyCustomClass->parseThing();
	}
}

Flight::route('/user', 'UserController->index');
```

### PSR-11

Flight var izmantot jebkuru PSR-11 atbilstošu konteineru. Tas nozīmē, ka jūs varat izmantot jebkuru
konteineru, kas implementē PSR-11 interfeisu. Šeit ir piemērs, izmantojot League
PSR-11 konteineru:

```php

require 'vendor/autoload.php';

// tā pati UserController klase kā iepriekš

$container = new \League\Container\Container();
$container->add(UserController::class)->addArgument(PdoWrapper::class);
$container->add(PdoWrapper::class)
	->addArgument('mysql:host=localhost;dbname=test')
	->addArgument('user')
	->addArgument('pass');
Flight::registerContainerHandler($container);

Flight::route('/user', [ 'UserController', 'view' ]);

Flight::start();
```

Tas var būt nedaudz verboseāks nekā iepriekšējais Dice piemērs, tas joprojām
izpilda darbu ar tiem pašiem ieguvumiem!

## Skatīt Arī
- [Paplašināšana Flight](/learn/extending) - Uzziniet, kā jūs varat pievienot atkarību injekciju savām klasēm, paplašinot ietvaru.
- [Konfigurācija](/learn/configuration) - Uzziniet, kā konfigurēt Flight savai lietojumprogrammai.
- [Maršrutēšana](/learn/routing) - Uzziniet, kā definēt maršrutus savai lietojumprogrammai un kā atkarību injekcija darbojas ar kontrolieriem.
- [Starpprogrammatūra](/learn/middleware) - Uzziniet, kā izveidot starpprogrammatūru savai lietojumprogrammai un kā atkarību injekcija darbojas ar starpprogrammatūru.

## Traucējummeklēšana
- Ja jums ir problēmas ar savu konteineru, pārliecinieties, ka nododat pareizos klases nosaukumus konteineram.

## Izmaiņu Žurnāls
- v3.7.0 - Pievienota iespēja reģistrēt DIC apstrādātāju Flight.