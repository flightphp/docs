# Atkarību injekcijas konteiners

## Ievads

Atkarību injekcijas konteiners (DIC) ir spēcīgs rīks, kas ļauj jums pārvaldīt
jūsu lietojumprogrammas atkarības. Tas ir galvenais koncepts mūsdienu PHP ietvaros un tiek
izmantots, lai pārvaldītu objektu instancēšanu un konfigurāciju. Veidi, kādi DIC 
bibliotēkas ir: [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), 
[PHP-DI](http://php-di.org/), un [league/container](https://container.thephpleague.com/).

DIC ir greznā veidā teikt, ka tas ļauj jums izveidot un pārvaldīt jūsu klases centrālizētā vietā.
Tas ir noderīgi, ja jums ir nepieciešams nodot to pašu objektu vairākām klasēm (piemēram, jūsu kontrolieriem). Viegls piemērs varētu palīdzēt šo padarīt skaidrāku.

## Pamata piemērs

Vecais veids, kā darīt lietas, varētu izskatīties šādi:
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

$User = new UserController(new PDO('mysql:host=localhost;dbname=test', 'lietotājvārds', 'parole'));
Flight::route('/lietotājs/@id', [ $UserController, 'view' ]);

Flight::start();
```

Jūs varat redzēt no augstāk minētā koda, ka mēs izveidojam jaunu `PDO` objektu un nododam to
mūsu `UserController` klasei. Tas ir labi mazai lietojumprogrammai, bet kad
jūsu lietojumprogramma aug, jūs atklāsiet, ka izveidojat to pašu `PDO` objektu vairākos
vietās. Tieši šeit noder DIC.

Šeit ir tas pats piemērs, izmantojot DIC (izmantojot Dice):
```php

require 'vendor/autoload.php';

// tāda pati klase kā iepriekš. Nav nekā mainījies
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
// neaizmirstiet atkārtoti piešķirt to sev tāpat kā zemāk!
$container = $container->addRule('PDO', [
	// shared nozīmē, ka tiks atgriezts tas pats objekts katru reizi
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'lietotājvārds', 'parole' ]
]);

// Tas reģistrē konteineru apstrādātāju, lai Flight zinātu, ka to izmantot.
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// tagad mēs varam izmantot konteineri, lai izveidotu mūsu UserController
Flight::route('/lietotājs/@id', [ 'UserController', 'view' ]);
// vai arī alternatīvi varat definēt maršrutu šādi
Flight::route('/lietotājs/@id', 'UserController->view');
// vai
Flight::route('/lietotājs/@id', 'UserController::view');

Flight::start();
```

Es uzdrīkotos iedomāties, ka jūs domājāt, ka piemēram tika pievienots daudz papildu koda.
Maģija rodas, kad jums ir cita kontroliera, kuram nepieciešams `PDO` objekts.

```php

// Ja visi jūsu kontrolieri ir konstruktoru, kuram nepieciešams PDO objekts
// katram zemāk esošajam maršrutam automātiski tiks injicēts !!!
Flight::route('/uzņēmums/@id', 'CompanyController->view');
Flight::route('/organizācija/@id', 'OrganizationController->view');
Flight::route('/kategorija/@id', 'CategoryController->view');
Flight::route('/uzstādījumi', 'SettingsController->view');
```

Pievienotais bonuss, izmantojot DIC, ir tas, ka vienības testēšana kļūst daudz vienkāršāka. Jūs varat
izveidot nepatiesu objektu un nodot to savai klasei. Tas ir milzīgs ieguvums, rakstot tests jūsu lietojumprogrammai!

## PSR-11

Flight var izmantot jebkuru PSR-11 atbilstošu konteineri. Tas nozīmē, ka jūs varat izmantot jebkuru
konteineri, kas īsteno PSR-11 interfeisu. Šeit ir piemērs, izmantojot League's
PSR-11 konteineri:

```php

require 'vendor/autoload.php';

// tāda pati UserController klase kā iepriekš

$container = new \League\Container\Container();
$container->add(UserController::class)->addArgument(PdoWrapper::class);
$container->add(PdoWrapper::class)
	->addArgument('mysql:host=localhost;dbname=test')
	->addArgument('lietotājvārds')
	->addArgument('parole');
Flight::registerContainerHandler($container);

Flight::route('/lietotājs', [ 'UserController', 'view' ]);

Flight::start();
```

Lai gan tas var būt nedaudz izsmeļošāks nekā iepriekšējais Dice piemērs, tas joprojām
izdara darbu ar tādām pašām priekšrocībām!

## Pielāgots DIC apstrādātājs

Jūs varat arī izveidot savu DIC apstrādātāju. Tas ir noderīgi, ja jums ir pielāgots
konteiners, ko jūs vēlaties izmantot, kas nav PSR-11 (Dice). Skatiet
[pamata piemēru](#basic-example), kā to izdarīt.

Papildus tam
ir dažas noderīgas noklusējuma vērtības, kas atvieglos jūsu dzīvi, izmantojot Flight.

### Dzinēja instances

Ja izmantojat `Engine` instanci savos kontrolieros/starpprogrammatūrā, šeit
ir, kā jūs to konfigurētu:

```php

// Kaut kur jūsu sākotnējās datnes
$dzinējs = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// Šeit jūs padodat instanci
		Engine::class => $dzinējs
	]
]);

$dzinējs->registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// Tagad jūs varat izmantot Dzinēja instanci savos kontrolieros/starpprogrammatūrā

class MyController {
	public function __construct(Engine $lietojumprogramma) {
		$this->lietojumprogramma = $lietojumprogramma;
	}

	public function index() {
		Šī->lietojumprogramma->render('indekss');
	}
}
```

### Pievienojot citus klases

Ja jums ir citas klases, ko vēlaties pievienot konteinerim, ar Dice tas ir viegli, jo tās automātiski tiks atrisinātas ar konteineri. Šeit ir piemērs:

```php

$container = new \Dice\Dice;
// Ja jums nav jāievēro nekāda savas klases
// jums nav nepieciešams neko definēt!
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

class MansPielāgotaisKlase {
	public function analizējietLietu() {
		atgriezt 'lieta';
	}
}

class UserController {

	protected MyCustomClass $MansPielāgotaisKlase;

	public function __construct(MyCustomClass $MansPielāgotaisKlase) {
		Šī->MansPielāgotaisKlase = $MansPielāgotaisKlase;
	}

	public function index() {
		echo $this->MansPielāgotaisKlase->parseThing();
	}
}

Flight::route('/lietotājs', 'UserController->index');
```