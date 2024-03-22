# Atkarību ievietošanas konteiners

## Ievads

Atkarību ievietošanas konteiners (DIC) ir spēcīgs rīks, kas ļauj jums pārvaldīt
jūsu lietojumprogrammas atkarības. Tas ir galvenais koncepts mūsdienu PHP ietvaros un
tiek izmantots, lai pārvaldītu objektu instancēšanu un konfigurāciju. Daži piemēri 
DIC bibliotēkas ir: [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), 
[PHP-DI](http://php-di.org/) un [league/container](https://container.thephpleague.com/).

DIC ir izteiksmīgs veids, kā teikt, ka tas ļauj jums izveidot un pārvaldīt savas klases vienkāršotā 
vietā. Tas ir noderīgi, kad jums ir nepieciešams padot vienādu objektu vairākām klasēm (piemēram, jūsu vadītājiem). 
Vienkāršs piemērs varētu palīdzēt labāk saprast.

## Pamata piemērs

Vecākā darbības veida varbūt izskatītos šādi:
```php

require 'vendor/autoload.php';

// klase, lai pārvaldītu lietotājus no datu bāzes
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

$User = new UserController(new PDO('mysql:host=localhost;dbname=test', 'user', 'pass'));
Flight::route('/user/@id', [ $UserController, 'view' ]);

Flight::start();
```

Var redzēt no iepriekšējās koda daļas, ka mēs izveidojam jaunu `PDO` objektu un nododam to
mūsu `UserController` klasei. Tas ir labi mazai lietojumprogrammai, bet, kad
jūsu lietojumprogramma izaug, jūs secināsiet, ka izveidojat to pašu `PDO` objektu vairākās
vietās. Šeit noder DIC.

Šeit ir tas pats piemērs, izmantojot DIC (izmantojot Dice):
```php

require 'vendor/autoload.php';

// tāda pati klase kā iepriekš. Nav mainījies nekas
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

// izveidot jaunu konteineri
$container = new \Dice\Dice;
// neaizmirstiet pārdefinēt to pašu sev kā zemāk!
$container = $container->addRule('PDO', [
	// shared nozīmē, ka katru reizi tiks atgriezts tas pats objekts
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// Tas reģistrē konteineru apstrādātāju, tāpēc Flight zina, kā to izmantot.
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// tagad mēs varam izmantot konteineri, lai izveidotu mūsu UserController
Flight::route('/user/@id', [ 'UserController', 'view' ]);
// vai arī alternatīvi varat definēt maršrutu šādi
Flight::route('/user/@id', 'UserController->view');
// vai
Flight::route('/user/@id', 'UserController::view');

Flight::start();
```

Es uzskatu, ka jūs varētu domāt, ka pie piemēra tika pievienots daudz papildu koda.
Burvība rodas tad, kad jums ir cita kontroliera, kuram nepieciešams `PDO` objekts.

```php

// Ja visiem jūsu kontrolieriem ir konstruktors, kuram nepieciešams PDO objekts
// katram zemāk esošajam maršrutam automātiski tiks veikta injekcija!!!
Flight::route('/uzņēmums/@id', 'CompanyController->view');
Flight::route('/organizācija/@id', 'OrganizationController->view');
Flight::route('/kategorija/@id', 'CategoryController->view');
Flight::route('/uzstādījumi', 'SettingsController->view');
```

Papildu bonusa ieguvums, izmantojot DIC, ir tas, ka vienības tests kļūst daudz vienkāršāks. Jūs varat
izveidot modeli objektu un to nodot savai klasei. Tas ir liels ieguvums, kad jūs
veicat testus savai lietotnei!

## PSR-11

Flight arī var izmantot jebkuru PSR-11 saderīgu konteineri. Tas nozīmē, ka jūs varat izmantot jebkuru
konteineri, kas īsteno PSR-11 interfeisu. Šeit ir piemērs, izmantojot League PSR-11 konteineri:

```php

require 'vendor/autoload.php';

// tāda pati UserController klase kā iepriekš

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

Lai arī tas var būt nedaudz izsmeļošāks nekā iepriekšējais Dice piemērs, tas tomēr
paveic uzdevumu ar tādām pašām priekšrocībām!

## Pielāgots DIC apstrādātājs

Jūs varat arī izveidot savu DIC apstrādātāju. Tas ir noderīgi, ja jums ir pielāgots
konteiners, ko vēlaties izmantot, kas nav PSR-11 (Dice). Apskatiet
[pamata piemēru](#basic-example), lai noskaidrotu, kā to izdarīt.

Papildus tam
ir dažas noderīgas noklusējuma vērtības, kas atvieglos jūsu dzīvi, izmantojot Flight.

### Dzinēja instance

Ja jūs izmantojat `Engine` instanci savos vadītājos/starpposmos, šeit ir
kā jūs to konfigurētu:

```php

// Kurš mūsu sākumfailā
$engine = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// Šeit jūs padodat instance
		Engine::class => $engine
	]
]);

$engine->registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// Tagad jūs varat izmantot Engine instanci savos vadītājos/starpposmos

class MansKontrolieris {
	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function index() {
		$this->app->render('index');
	}
}
```

### Pievienojot citus klases

Ja jums ir citas klases, ko vēlaties pievienot konteinerim, ar Dice tas ir vienkārši, jo tās automātiski atrisināsies ar konteineri. Šeit ir piemērs:

```php

$container = new \Dice\Dice;
// Ja jums nav jāietver kaut kas jūsu klasē
// jums nav nepieciešams neko definēt!
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

class ManaPielāgotāKlase {
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