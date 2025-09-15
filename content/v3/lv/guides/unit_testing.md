# Vienības testēšana Flight PHP ar PHPUnit

Šis ceļvedis ievieš vienības testēšanu Flight PHP, izmantojot [PHPUnit](https://phpunit.de/), un tas ir domāts iesācējiem, kuri vēlas saprast, *kāpēc* vienības testēšana ir svarīga un kā to pielietot praksē. Mēs koncentrēsimies uz testēšanu *uzvedību* — nodrošinot, ka jūsu aplikācija dara to, ko jūs sagaidāt, piemēram, nosūta e-pastu vai saglabā ierakstu — nevis uz sīkām aprēķiniem. Mēs sāksim ar vienkāršu [maršrutētāju](/learn/routing) un progresēsim līdz sarežģītākam [kontrolierim](/learn/routing), iekļaujot [atkarību iesūkšanu](/learn/dependency-injection-container) (DI) un trešo pušu servisu mocking.

## Kāpēc veikt vienības testus?

Vienības testi nodrošina, ka jūsu kods uzvedas kā gaidīts, atklājot kļūdas pirms to nonākšanas produkcijā. Tas ir īpaši vērtīgi Flight, kur vieglais maršrutēšana un elastība var izraisīt sarežģītas mijiedarbības. Vienpersonas izstrādātājiem vai komandām vienības testi darbojas kā drošības tīkls, dokumentējot gaidīto uzvedību un novēršot regresijas, kad jūs atgriežaties pie koda vēlāk. Tie arī uzlabo dizainu: kodu, kas ir grūti testēt, bieži norāda uz pārlieku sarežģītām vai cieši saistītām klasēm.

Atšķirībā no vienkāršiem piemēriem (piemēram, testēšana `x * y = z`), mēs koncentrēsimies uz reālas pasaules uzvedību, piemēram, ievades validēšanu, datu saglabāšanu vai darbību izraisīšanu, piemēram, e-pastiem. Mūsu mērķis ir padarīt testēšanu pieejamu un jēgpilnu.

## Vispārīgi vadlīnijas principi

1. **Testējiet uzvedību, nevis īstenošanu**: Fokuss uz rezultātiem (piemēram, “e-pasts nosūtīts” vai “ieraksts saglabāts”), nevis iekšējām detaļām. Tas padara testus izturīgākus pret refaktoringu.
2. **Pārtrauciet izmantot `Flight::`**: Flight statiskās metodes ir ļoti ērtas, bet padara testēšanu grūtu. Jums vajadzētu pierast izmantot `$app` mainīgo no `$app = Flight::app();`. `$app` ir visas tās pašas metodes, kas `Flight::`. Jūs joprojām varēsiet izmantot `$app->route()` vai `$this->app->json()` savā kontrolierī utt. Jums arī vajadzētu izmantot īsto Flight maršrutētāju ar `$router = $app->router()` un tad jūs varat izmantot `$router->get()`, `$router->post()`, `$router->group()` utt. Skatiet [Maršrutēšana](/learn/routing).
3. **Turiet testus ātrus**: Ātri testi veicina biežu izpildi. Izvairieties no lēnām operācijām, piemēram, datu bāzes zvanu, vienības testos. Ja jums ir lēns tests, tas ir zīme, ka jūs rakstāt integrācijas testu, nevis vienības testu. Integrācijas testi ir tad, kad jūs iesaistāt reālas datu bāzes, reālus HTTP zvanus, reālu e-pastu sūtīšanu utt. Tiem ir sava vieta, bet tie ir lēni un var būt nestabili, nozīmē, ka tie dažreiz neizdodas nezināmu iemeslu dēļ. 
4. **Izmantojiet aprakstošus nosaukumus**: Testu nosaukumiem jāapraksta skaidri testētā uzvedība. Tas uzlabo lasāmību un uzturējamību.
5. **Izvairieties no globāliem kā no mēra**: Minimizējiet `$app->set()` un `$app->get()` izmantošanu, jo tie darbojas kā globāla stāvokļa, prasošs mockus katrā testā. Dodiet priekšroku DI vai DI konteineram (skatiet [Atkarību iesūkšanas konteineru](/learn/dependency-injection-container)). Pat izmantojot `$app->map()` metodi, tehniski ir "globāls" un tam vajadzētu izvairīties par labu DI. Izmantojiet sesijas bibliotēku, piemēram, [flightphp/session](https://github.com/flightphp/session), lai jūs varētu mockot sesijas objektu savos testos. **Neizsaucite** [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php) tieši savā kodā, jo tas ievada globālu mainīgo jūsu kodā, padarot to grūti testējamu.
6. **Izmantojiet atkarību iesūkšanu**: Iesūkiet atkarības (piemēram, [`PDO`](https://www.php.net/manual/en/class.pdo.php), sūtītāji) kontrolieros, lai izolētu loģiku un vienkāršotu mocking. Ja jums ir klase ar pārāk daudzām atkarībām, apsveriet to refaktorēt mazākās klasēs, kurām katrai ir viena atbildība, ievērojot [SOLID principus](https://en.wikipedia.org/wiki/SOLID).
7. **Mock trešo pušu servisi**: Mock datu bāzes, HTTP klientus (cURL) vai e-pasta servisi, lai izvairītos no ārējiem zvaniem. Testējiet vienu vai divus slāņus dziļi, bet ļaujiet jūsu kodola loģikai darboties. Piemēram, ja jūsu aplikācija sūta īsziņu, jūs **NE** vēlaties tiešām sūtīt īsziņu katru reizi, kad izpildāt testus, jo tieši maksa pieaugs (un tas būs lēnāks). Tā vietā, mock īsziņas servisu un tikai pārbaudiet, ka jūsu kods izsauca īsziņas servisu ar pareizajiem parametriem.
8. **Mērķējiet uz augstu pārklājumu, nevis pilnību**: 100% līniju pārklājums ir labs, bet tas nenozīmē, ka viss jūsu kodā ir testēts tā, kā vajadzētu (turpiniet un pētiet [zaru/ceļu pārklājumu PHPUnit](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/)). Prioritizējiet kritiskas uzvedības (piemēram, lietotāja reģistrāciju, API atbildes un neizdevušos atbilžu uztveršanu).
9. **Izmantojiet kontrolierus maršrutu definīcijām**: Savās maršrutu definīcijās izmantojiet kontrolierus, nevis slēgumus. `flight\Engine $app` tiek iesūkts katrā kontrolierī caur konstruktoru pēc noklusējuma. Testos izmantojiet `$app = new Flight\Engine()` lai izveidotu Flight iekš testu, iesūkiet to savā kontrolierī un izsaucite metodes tieši (piemēram, `$controller->register()`). Skatiet [Flight paplašināšana](/learn/extending) un [Maršrutēšana](/learn/routing).
10. **Izvēlieties mocking stilu un turiet pie tā**: PHPUnit atbalsta vairākus mocking stilus (piemēram, prophecy, iebūvētie mocki), vai jūs varat izmantot anonīmas klases, kurām ir savas priekšrocības, piemēram, koda pabeigšana, pārtraukšana, ja jūs mainiet metodes definīciju utt. Vienkārši esiet konsekventi visos testos. Skatiet [PHPUnit Mock Objekti](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles).
11. **Izmantojiet `protected` redzamību metodēm/īpašībām, kuras jūs vēlaties testēt apakšklasēs**: Tas ļauj jums tos pārdefinēt testu apakšklasēs, nepadarot tos publiskus, tas ir īpaši noderīgi anonīmo klases mockiem.

## Iestatīšana PHPUnit

Pirmkārt, iestatiet [PHPUnit](https://phpunit.de/) savā Flight PHP projektā, izmantojot Composer ērtai testēšanai. Skatiet [PHPUnit Sākuma rokasgrāmatu](https://phpunit.readthedocs.io/en/12.3/installation.html) vairāk detaļu.

1. Savā projekta direktorijā izpildiet:
   ```bash
   composer require --dev phpunit/phpunit
   ```
   Tas instalē jaunāko PHPUnit kā attīstības atkarību.

2. Izveidojiet `tests` direktoriju jūsu projekta saknē testu failiem.

3. Pievienojiet testu skriptu `composer.json` ērtībai:
   ```json
   // citi composer.json saturs
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```

4. Izveidojiet `phpunit.xml` failu saknē:
   ```xml
   <?xml version="1.0" encoding="UTF-8"?>
   <phpunit bootstrap="vendor/autoload.php">
       <testsuites>
           <testsuite name="Flight Tests">
               <directory>tests</directory>
           </testsuite>
       </testsuites>
   </phpunit>
   ```

Tagad, kad jūsu testi ir izveidoti, jūs varat izpildīt `composer test`, lai izpildītu testus.

## Testēšana vienkārša maršrutētāja

Sāksim ar pamata [maršrutu](/learn/routing), kas validē lietotāja e-pasta ievadi. Mēs testēsim tā uzvedību: atgriežot panākumu ziņojumu derīgiem e-pastiem un kļūdas ziņojumu nederīgiem. E-pasta validēšanai mēs izmantojam [`filter_var`](https://www.php.net/manual/en/function.filter-var.php).

```php
// index.php
$app->route('POST /register', [ UserController::class, 'register' ]);

// UserController.php
class UserController {
	protected $app;

	public function __construct(flight\Engine $app) {
		$this->app = $app;  // Simulēt POST datus
	}

	public function register() {
		$email = $this->app->request()->data->email;
		$responseArray = [];
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$responseArray = ['status' => 'error', 'message' => 'Nederīgs e-pasts'];
		} else {
			$responseArray = ['status' => 'success', 'message' => 'Derīgs e-pasts'];
		}

		$this->app->json($responseArray);
	}
}
```

Lai testētu to, izveidojiet testu failu. Skatiet [Vienības testēšana un SOLID principi](/learn/unit-testing-and-solid-principles) vairāk par testu strukturēšanu:

```php
// tests/UserControllerTest.php
use PHPUnit\Framework\TestCase;
use Flight;
use flight\Engine;

class UserControllerTest extends TestCase {

    public function testValidEmailReturnsSuccess() {
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'test@example.com'; // Simulēt POST datus
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
        $response = $app->response()->getBody();
		$output = json_decode($response, true);
        $this->assertEquals('success', $output['status']);
        $this->assertEquals('Valid email', $output['message']);
    }

    public function testInvalidEmailReturnsError() {
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'invalid-email'; // Simulēt POST datus
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
		$response = $app->response()->getBody();
		$output = json_decode($response, true);
		$this->assertEquals('error', $output['status']);
		$this->assertEquals('Invalid email', $output['message']);
	}
}
```

**Galvenie punkti**:
- Mēs simulējam POST datus, izmantojot pieprasījuma klasi. Neizmantojiet globālos, piemēram, `$_POST`, `$_GET` utt., jo tas padara testēšanu sarežģītāku (jums vienmēr jāatjaunina šīs vērtības vai citi testi varētu sabojāties).
- Visiem kontrolieriem pēc noklusējuma tiks iesūkts `flight\Engine` instances pat bez DI konteinera iestatīšanas. Tas padara kontrolieru testēšanu tieši vieglāku.
- Nav `Flight::` izmantošanas vispār, padarot kodu vieglāku testēšanai.
- Testi pārbauda uzvedību: pareizu statusu un ziņojumu derīgiem/nederīgiem e-pastiem.

Izpildiet `composer test`, lai pārbaudītu, ka maršruts uzvedas kā gaidīts. Vairāk par [pieprasījumiem](/learn/requests) un [atbildēm](/learn/responses) Flight, skatiet attiecīgos dokumentus.

## Izmantojot atkarību iesūkšanu testējamiem kontrolieriem

Sarežģītākos scenārijos izmantojiet [atkarību iesūkšanu](/learn/dependency-injection-container) (DI), lai padarītu kontrolierus testējamus. Izvairieties no Flight globāliem (piemēram, `Flight::set()`, `Flight::map()`, `Flight::register()`), jo tie darbojas kā globāla stāvokļa, prasošs mockus katrā testā. Tā vietā, izmantojiet Flight DI konteineru, [DICE](https://github.com/Level-2/Dice), [PHP-DI](https://php-di.org/) vai manuālu DI.

Izmantosim [`flight\database\PdoWrapper`](/awesome-plugins/pdo-wrapper) vietā raw PDO. Šis apvalks ir daudz vieglāk mockot un vienības testēt!

Lūk, kontrolieris, kas saglabā lietotāju datu bāzē un sūta laipnu e-pastu:

```php
use flight\database\PdoWrapper;

class UserController {
    protected $app;
    protected $db;
    protected $mailer;

    public function __construct(Engine $app, PdoWrapper $db, MailerInterface $mailer) {
        $this->app = $app;
        $this->db = $db;
        $this->mailer = $mailer;  // Simulēt e-pasta sūtīšanu
    }

    public function register() {
		$email = $this->app->request()->data->email;
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			// pievienojot return šeit palīdz vienības testēšanai pārtraukt izpildi
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Nederīgs e-pasts']);
		}

		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);

		return $this->app->json(['status' => 'success', 'message' => 'Lietotājs reģistrēts']);
    }
}
```

**Galvenie punkti**:
- Kontrolieris atkarīgs no [`PdoWrapper`](/awesome-plugins/pdo-wrapper) instances un `MailerInterface` (izdomāts trešās puses e-pasta serviss).
- Atkarības tiek iesūkts caur konstruktoru, izvairoties no globāliem.

### Testēšana kontrolieru ar mockiem

Tagad, testēsim `UserController` uzvedību: validējot e-pastus, saglabājot datu bāzē un sūtot e-pastus. Mēs mockojam datu bāzi un sūtītāju, lai izolētu kontrolieru.

```php
// tests/UserControllerDICTest.php
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {

		// Dažreiz maisot mocking stilus ir nepieciešams
		// Šeit mēs izmantojam PHPUnit iebūvēto mock priekš PDOStatement
		$statementMock = $this->createMock(PDOStatement::class);
		$statementMock->method('execute')->willReturn(true);
		// Izmantojot anonīmu klasi, lai mockotu PdoWrapper
        $mockDb = new class($statementMock) extends PdoWrapper {
			protected $statementMock;
			public function __construct($statementMock) {
				$this->statementMock = $statementMock;
			}

			// Kad mēs mockojam to šādā veidā, mēs patiesi neveicam datu bāzes zvanu.
			// Mēs varam tālāk iestatīt šo, lai mainītu PDOStatement mock, lai simulētu neveiksmes utt.
            public function runQuery(string $sql, array $params = []): PDOStatement {
                return $this->statementMock;
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                $this->sentEmail = $email;
                return true;	
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
        $controller = new UserControllerDIC($app, $mockDb, $mockMailer);
        $controller->register();
		$response = $app->response()->getBody();
		$result = json_decode($response, true);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('User registered', $result['message']);
        $this->assertEquals('test@example.com', $mockMailer->sentEmail);
    }

    public function testInvalidEmailSkipsSaveAndEmail() {
		 $mockDb = new class() extends PdoWrapper {
			// Tukšs konstruktors apejot vecāku konstruktoru
			public function __construct() {}
            public function runQuery(string $sql, array $params = []): PDOStatement {
                throw new Exception('Should not be called');
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                throw new Exception('Should not be called');
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'invalid-email';

		// Vajadzīgs kartēt jsonHalt, lai izvairītos no iziešanas
		$app->map('jsonHalt', function($data) use ($app) {
			$app->json($data, 400);
		});
        $controller = new UserControllerDIC($app, $mockDb, $mockMailer);
        $controller->register();
        $response = $app->response()->getBody();
        $result = json_decode($response, true);
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Invalid email', $result['message']);
    }
}
```

**Galvenie punkti**:
- Mēs mockojam `PdoWrapper` un `MailerInterface`, lai izvairītos no reāliem datu bāzes vai e-pasta zvaniem.
- Testi pārbauda uzvedību: derīgi e-pasti izraisa datu bāzes ievietošanu un e-pasta sūtīšanu; nederīgi e-pasti izlaiž abus.
- Mock trešās puses atkarības (piemēram, `PdoWrapper`, `MailerInterface`), ļaujot kontrolieru loģikai darboties.

### Mockošana pārāk daudz

Esiet uzmanīgs, lai nemockotu pārāk daudz no sava koda. Ļaujiet daļai loģikas (piemēram, kontrolieru validēšana) darboties, lai testētu reālu uzvedību. Apskatiet zemāk piemēru, kāpēc tas varētu būt slikti, izmantojot mūsu `UserController`. Mēs mainīsim to pārbaudi uz metodi, ko sauc `isEmailValid` (izmantojot `filter_var`) un citas jaunas pievienošanas uz atsevišķu metodi, ko sauc `registerUser`.

```php
use flight\database\PdoWrapper;
use flight\Engine;

// UserControllerDICV2.php
class UserControllerDICV2 {
	protected $app;
    protected $db;
    protected $mailer;

    public function __construct(Engine $app, PdoWrapper $db, MailerInterface $mailer) {
        $this->app = $app;
        $this->db = $db;
        $this->mailer = $mailer;
    }

    public function register() {
		$email = $this->app->request()->data->email;
		if (!$this->isEmailValid($email)) {
			// pievienojot return šeit palīdz vienības testēšanai pārtraukt izpildi
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Nederīgs e-pasts']);
		}

		$this->registerUser($email);

		$this->app->json(['status' => 'success', 'message' => 'Lietotājs reģistrēts']);
    }

	protected function isEmailValid($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;  // Validēt e-pastu
	}

	protected function registerUser($email) {
		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);
	}
}
```

Un tagad pārmērīgi mockotais vienības tests, kas patiesībā nekā netestē:

```php
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
		// mēs izlaižam papildu atkarību iesūkšanu šeit, jo tā ir "vieglā"
        $controller = new class($app) extends UserControllerDICV2 {
			protected $app;
			// Apejot atkarības konstruktorā
			public function __construct($app) {
				$this->app = $app;
			}

			// Mēs vienkārši piespiedīsim to būt derīgam.
			protected function isEmailValid($email) {
				return true; // Vienmēr atgriezt true, apejot reālu validēšanu
			}

			// Apejot patieso DB un sūtītāja zvanus
			protected function registerUser($email) {
				return false;
			}
		};
        $controller->register();
		$response = $app->response()->getBody();
		$result = json_decode($response, true);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('User registered', $result['message']);
    }
}
```

Hurā, mums ir vienības testi un tie izdodas! Bet gaidiet, ko tad, ja es patiesi mainu `isEmailValid` vai `registerUser` iekšējās darbības? Mani testi joprojām izdosies, jo es esmu mockojis visu funkcionalitāti. Apskatiet, ko es domāju.

```php
// UserControllerDICV2.php
class UserControllerDICV2 {

	// ... citas metodes ...

	protected function isEmailValid($email) {
		// Mainīta loģika
		$validEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
		// Tagad tam vajadzētu būt specifiskai domeenai
		$validDomain = strpos($email, '@example.com') !== false; 
		return $validEmail && $validDomain;
	}
}
```

Ja es izpildītu manus iepriekšējos vienības testus, tie joprojām izdodas! Bet, jo es nepārbaudīju uzvedību (patiesi ļaujot daļai koda darboties), man ir potenciāla kļūda, kas gaida produkcijā. Testam vajadzētu būt modificētam, lai ņemtu vērā jauno uzvedību, un arī pretēju gadījumu, kad uzvedība nav tāda, kā gaidīts.

## Pilns piemērs

Jūs varat atrast pilnu Flight PHP projekta piemēru ar vienības testiem GitHub: [n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide).
Vairāk rokasgrāmatu, skatiet [Vienības testēšana un SOLID principi](/learn/unit-testing-and-solid-principles) un [Problēmu novēršana](/learn/troubleshooting).

## Biežas bedres

- **Pārmērīga mocking**: Nemockojiet katru atkarību; ļaujiet daļai loģikas (piemēram, kontrolieru validēšana) darboties, lai testētu reālu uzvedību. Skatiet [Vienības testēšana un SOLID principi](/learn/unit-testing-and-solid-principles).
- **Globāla stāvoklis**: Izmantojot globālos PHP mainīgos (piemēram, [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php), [`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php)) intensīvi padara testus trauslus. Tas pats attiecas uz `Flight::`. Refaktorējiet, lai nodotu atkarības skaidri.
- **Sarežģīta iestatīšana**: Ja testu iestatīšana ir sarežģīta, jūsu klasei var būt pārāk daudz atkarību vai atbildību, pārkāpjot [SOLID principus](https://en.wikipedia.org/wiki/SOLID).

## Paplašināšana ar vienības testiem

Vienības testi spīd lielākos projektos vai kad atgriežaties pie koda pēc mēnešiem. Tie dokumentē uzvedību un atklāj regresijas, ietaupot jums no atkārtotas mācīšanās aplikācijas. Vienpersonas izstrādātājiem, testējiet kritiskos ceļus (piemēram, lietotāja reģistrāciju, maksājumu apstrādi). Komandām, testi nodrošina konsekventu uzvedību visā ieguldījumā. Skatiet [Kāpēc ietvarprogrammas?](/learn/why-frameworks) vairāk par priekšrocībām, izmantojot ietvarprogrammas un testus.

Ieguldiet savus testēšanas padomus Flight PHP dokumentācijas repozitorijā!

_Rakstījis [n0nag0n](https://github.com/n0nag0n) 2025_