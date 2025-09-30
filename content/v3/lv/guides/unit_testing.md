# Vienības testēšana Flight PHP ar PHPUnit

Šis ceļvedis iepazīstina ar vienības testēšanu Flight PHP, izmantojot [PHPUnit](https://phpunit.de/), kas paredzēts iesācējiem, kuri vēlas saprast, *kāpēc* vienības testēšana ir svarīga un kā to praktiski pielietot. Mēs koncentrēsimies uz testēšanu *uzvedības* — nodrošinot, ka jūsu lietojumprogramma darbojas tā, kā jūs sagaidāt, piemēram, nosūtot e-pastu vai saglabājot ierakstu — nevis uz sīkumainiem aprēķiniem. Mēs sāksim ar vienkāršu [maršruta apstrādātāju](/learn/routing) un pakāpeniski pāriesim pie sarežģītāka [kontrolera](/learn/routing), iekļaujot [atkarību injekciju](/learn/dependency-injection-container) (DI) un trešo pušu servisu mockēšanu.

## Kāpēc veikt vienības testus?

Vienības testēšana nodrošina, ka jūsu kods uzvedas kā paredzēts, uztverot kļūdas, pirms tās nonāk ražošanā. Tas ir īpaši vērtīgi Flight, kur vieglā maršrutēšana un elastība var radīt sarežģītas mijiedarbības. Vienības testi darbojas kā drošības tīkls solo izstrādātājiem vai komandām, dokumentējot paredzēto uzvedību un novēršot regresijas, kad jūs vēlāk atgriežaties pie koda. Tie arī uzlabo dizainu: kodu, kas ir grūti testēt, bieži norāda uz pārmērīgi sarežģītām vai cieši saistītām klasēm.

Atšķirībā no vienkāršiem piemēriem (piemēram, testēšana `x * y = z`), mēs koncentrēsimies uz reālās pasaules uzvedībām, piemēram, ievades validēšanu, datu saglabāšanu vai darbību izraisīšanu, piemēram, e-pastus. Mūsu mērķis ir padarīt testēšanu pieejamu un jēgpilnu.

## Vispārīgi vadības principi

1. **Testējiet uzvedību, nevis realizāciju**: Koncentrējieties uz rezultātiem (piemēram, "e-pasts nosūtīts" vai "ieraksts saglabāts"), nevis uz iekšējām detaļām. Tas padara testus izturīgus pret refaktoringu.
2. **Pārtrauciet izmantot `Flight::`**: Flight statiskās metodes ir ārkārtīgi ērtas, bet apgrūtina testēšanu. Jums vajadzētu pierast izmantot `$app` mainīgo no `$app = Flight::app();`. `$app` satur visas tās pašas metodes, kas `Flight::`. Jūs joprojām varēsiet izmantot `$app->route()` vai `$this->app->json()` savā kontrolerī utt. Jums arī vajadzētu izmantot īsto Flight maršrutētāju ar `$router = $app->router()` un tad varēsiet izmantot `$router->get()`, `$router->post()`, `$router->group()` utt. Skatiet [Routing](/learn/routing).
3. **Uzturiet testus ātrus**: Ātri testi veicina biežu izpildi. Izvairieties no lēnām operācijām, piemēram, datubāzes izsaukumiem vienības testos. Ja jums ir lēns tests, tas ir zīme, ka jūs rakstāt integrācijas testu, nevis vienības testu. Integrācijas testi ir tad, kad jūs patiešām iesaistāt reālas datubāzes, reālus HTTP izsaukumus, reālu e-pasta sūtīšanu utt. Tiem ir sava vieta, bet tie ir lēni un var būt nestabili, kas nozīmē, ka tie dažreiz neizdodas nezināma iemesla dēļ.
4. **Izmantojiet aprakstošus nosaukumus**: Testu nosaukumiem jāapraksta skaidri testētā uzvedība. Tas uzlabo lasāmību un uzturējamību.
5. **Izvairieties no globāliem mainīgajiem kā no mēra**: Minimāli izmantojiet `$app->set()` un `$app->get()`, jo tie darbojas kā globālais stāvoklis, prasot mockus katrā testā. Dodiet priekšroku DI vai DI konteineram (skatiet [Dependency Injection Container](/learn/dependency-injection-container)). Pat izmantojot `$app->map()` metodi, tehniski ir "globāls" un jāizvairās no tā par labu DI. Izmantojiet sesijas bibliotēku, piemēram, [flightphp/session](https://github.com/flightphp/session), lai varētu mockēt sesijas objektu savos testos. **Neizsauciet** [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php) tieši savā kodā, jo tas ievada globālo mainīgo jūsu kodā, apgrūtinot testēšanu.
6. **Izmantojiet atkarību injekciju**: Injektējiet atkarības (piemēram, [`PDO`](https://www.php.net/manual/en/class.pdo.php), pasta sūtītājus) kontroleros, lai izolētu loģiku un atvieglotu mockēšanu. Ja jums ir klase ar pārāk daudzām atkarībām, apsvēriet tās refaktoringu mazākās klasēs, kurām katrai ir viena atbildība, ievērojot [SOLID principus](https://en.wikipedia.org/wiki/SOLID).
7. **Mockējiet trešo pušu servisus**: Mockējiet datubāzes, HTTP klientus (cURL) vai e-pasta servisus, lai izvairītos no ārējiem izsaukumiem. Testējiet vienu vai divus slāņus dziļi, bet ļaujiet jūsu kodola loģikai darboties. Piemēram, ja jūsu lietojumprogramma nosūta īsziņu, jūs **NEGRIBAT** patiešām sūtīt īsziņu katru reizi, kad palaižat testus, jo šie maksājumi sakopsies (un tas būs lēnāks). Tā vietā mockējiet īsziņas servisu un tikai pārbaudiet, ka jūsu kods izsauca īsziņas servisu ar pareizajiem parametriem.
8. **Mērķis uz augstu pārklājumu, nevis pilnību**: 100% rindu pārklājums ir labs, bet tas nenozīmē, ka viss jūsu kodā ir testēts tā, kā vajadzētu (droši vien pētiet [zaru/ceļa pārklājumu PHPUnit](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/)). Prioritizējiet kritiskās uzvedības (piemēram, lietotāja reģistrāciju, API atbildes un neizdevušos atbilžu uztveršanu).
9. **Izmantojiet kontrolerus maršrutiem**: Savos maršruta definīcijās izmantojiet kontrolerus, nevis aizvēršanas. `flight\Engine $app` pēc noklusējuma tiek injicēts katrā kontrolerā caur konstruktoru. Testos izmantojiet `$app = new Flight\Engine()`, lai inicializētu Flight testā, injicējiet to kontrolerī un izsauciet metodes tieši (piemēram, `$controller->register()`). Skatiet [Extending Flight](/learn/extending) un [Routing](/learn/routing).
10. **Izvēlieties mockēšanas stilu un turieties pie tā**: PHPUnit atbalsta vairākus mockēšanas stilus (piemēram, prophecy, iebūvēti mocki), vai varat izmantot anonīmas klases, kurām ir savas priekšrocības, piemēram, koda pabeigšana, salūšana, ja maināt metodes definīciju utt. Tikai esiet konsekventi visos savos testos. Skatiet [PHPUnit Mock Objects](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles).
11. **Izmantojiet `protected` redzamību metodēm/īpašībām, kuras vēlaties testēt apakšklasēs**: Tas ļauj jums tās pārrakstīt testu apakšklasēs, nepadarot tās publiskām, tas ir īpaši noderīgi anonīmu klašu mockiem.

## PHPUnit iestatīšana

Vispirms iestatiet [PHPUnit](https://phpunit.de/) savā Flight PHP projektā, izmantojot Composer vieglai testēšanai. Skatiet [PHPUnit Sākuma ceļvedi](https://phpunit.readthedocs.io/en/12.3/installation.html) sīkākiem detalizācijām.

1. Savā projektu direktorijā palaidiet:
   ```bash
   composer require --dev phpunit/phpunit
   ```
   Tas instalē jaunāko PHPUnit kā izstrādes atkarību.

2. Izveidojiet `tests` direktoriju jūsu projekta saknē testu failiem.

3. Pievienojiet testa skriptu `composer.json` ērtībai:
   ```json
   // cits composer.json saturs
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

Tagad, kad jūsu testi ir izveidoti, varat palaidiet `composer test`, lai izpildītu testus.

## Vienkārša maršruta apstrādātāja testēšana

Sāksim ar pamata [maršrutu](/learn/routing), kas validē lietotāja e-pasta ievadi. Mēs testēsim tā uzvedību: atgriežot veiksmīgu ziņu derīgiem e-pastiem un kļūdu nederīgiem. E-pasta validēšanai mēs izmantojam [`filter_var`](https://www.php.net/manual/en/function.filter-var.php).

```php
// index.php
$app->route('POST /register', [ UserController::class, 'register' ]);

// UserController.php
class UserController {
	protected $app;

	public function __construct(flight\Engine $app) {
		$this->app = $app;
	}

	public function register() {
		$email = $this->app->request()->data->email;
		$responseArray = [];
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$responseArray = ['status' => 'error', 'message' => 'Invalid email'];
		} else {
			$responseArray = ['status' => 'success', 'message' => 'Valid email'];
		}

		$this->app->json($responseArray);
	}
}
```

Lai to testētu, izveidojiet testa failu. Skatiet [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) par vairāk informācijas par testu struktūru:

```php
// tests/UserControllerTest.php
use PHPUnit\Framework\TestCase;
use Flight;
use flight\Engine;

class UserControllerTest extends TestCase {

    public function testValidEmailReturnsSuccess() {
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'test@example.com'; // Simulate POST data
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
		$request->data->email = 'invalid-email'; // Simulate POST data
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
- Mēs simulējam POST datus, izmantojot pieprasījuma klasi. Neizmantojiet globālos mainīgos, piemēram, `$_POST`, `$_GET` utt., jo tas padara testēšanu sarežģītāku (jums vienmēr jāatstata šīs vērtības, vai citādi citi testi var sabrukt).
- Visi kontroleri pēc noklusējuma saņems `flight\Engine` instanci, kas injicēta tajos pat bez DIC konteinera iestatīšanas. Tas ievērojami atvieglo kontroleru tiešo testēšanu.
- Nav nekādas `Flight::` izmantošanas vispār, padarot kodu vieglāku testēšanai.
- Testi pārbauda uzvedību: pareizu statusu un ziņu derīgiem/nederīgiem e-pastiem.

Palaidiet `composer test`, lai pārbaudītu, vai maršruts uzvedas kā paredzēts. Par vairāk informācijas par [pieprasījumiem](/learn/requests) un [atbildēm](/learn/responses) Flight, skatiet attiecīgos dokumentus.

## Atkarību injekcijas izmantošana testējamu kontroleru izveidošanai

Sarežģītākiem scenārijiem izmantojiet [atkarību injekciju](/learn/dependency-injection-container) (DI), lai padarītu kontrolerus testējamus. Izvairieties no Flight globāliem (piemēram, `Flight::set()`, `Flight::map()`, `Flight::register()`), jo tie darbojas kā globālais stāvoklis, prasot mockus katram testam. Tā vietā izmantojiet Flight DI konteineru, [DICE](https://github.com/Level-2/Dice), [PHP-DI](https://php-di.org/) vai manuālu DI.

Izmantojiet [`flight\database\PdoWrapper`](/learn/pdo-wrapper) nevis neapstrādātu PDO. Šis apvalks ir daudz vieglāk mockēt un veikt vienības testus!

Šeit ir kontroleris, kas saglabā lietotāju datubāzē un nosūta laipnu e-pastu:

```php
use flight\database\PdoWrapper;

class UserController {
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
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			// adding the return here helps unit testing to stop execution
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Invalid email']);
		}

		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);

		return $this->app->json(['status' => 'success', 'message' => 'User registered']);
    }
}
```

**Galvenie punkti**:
- Kontroleris ir atkarīgs no [`PdoWrapper`](/learn/pdo-wrapper) instances un `MailerInterface` (izdomāts trešās puses e-pasta serviss).
- Atkarības tiek injicētas caur konstruktoru, izvairoties no globāliem.

### Kontrolera testēšana ar mockiem

Tagad testēsim `UserController` uzvedību: e-pasta validēšanu, saglabāšanu datubāzē un e-pasta sūtīšanu. Mēs mockēsim datubāzi un pasta sūtītāju, lai izolētu kontroleri.

```php
// tests/UserControllerDICTest.php
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {

		// Sometimes mixing mocking styles is necessary
		// Here we use PHPUnit's built-in mock for PDOStatement
		$statementMock = $this->createMock(PDOStatement::class);
		$statementMock->method('execute')->willReturn(true);
		// Using an anonymous class to mock PdoWrapper
        $mockDb = new class($statementMock) extends PdoWrapper {
			protected $statementMock;
			public function __construct($statementMock) {
				$this->statementMock = $statementMock;
			}

			// When we mock it this way, we are not really making a database call.
			// We can further setup this to alter the PDOStatement mock to simulate failures, etc.
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
			// An empty constructor bypasses the parent constructor
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

		// Need to map jsonHalt to avoid exiting
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
- Mēs mockējam `PdoWrapper` un `MailerInterface`, lai izvairītos no reāliem datubāzes vai e-pasta izsaukumiem.
- Testi pārbauda uzvedību: derīgi e-pasti izraisa datubāzes ievietošanu un e-pasta sūtīšanu; nederīgi e-pasti izlaiž abus.
- Mockējiet trešās puses atkarības (piemēram, `PdoWrapper`, `MailerInterface`), ļaujot kontrolera loģikai darboties.

### Pārāk daudz mockēšana

Esiet uzmanīgi, lai nemockētu pārāk daudz sava koda. Ļaujiet man dot piemēru zemāk par to, kāpēc tas var būt sliktas lietas, izmantojot mūsu `UserController`. Mēs mainīsim šo pārbaudi uz metodi, ko sauc `isEmailValid` (izmantojot `filter_var`) un citas jaunas pievienojumus uz atsevišķu metodi, ko sauc `registerUser`.

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
			// adding the return here helps unit testing to stop execution
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Invalid email']);
		}

		$this->registerUser($email);

		$this->app->json(['status' => 'success', 'message' => 'User registered']);
    }

	protected function isEmailValid($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
	}

	protected function registerUser($email) {
		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);
	}
}
```

Un tagad pārāk mockētais vienības tests, kas patiesībā neko netestē:

```php
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
		// we are skipping the extra dependency injection here cause it's "easy"
        $controller = new class($app) extends UserControllerDICV2 {
			protected $app;
			// Bypass the deps in the construct
			public function __construct($app) {
				$this->app = $app;
			}

			// We'll just force this to be valid.
			protected function isEmailValid($email) {
				return true; // Always return true, bypassing real validation
			}

			// Bypass the actual DB and mailer calls
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

Hurā, mums ir vienības testi un tie iziet! Bet pagaidi, ko tad, ja es patiesībā mainu `isEmailValid` vai `registerUser` iekšējo darbību? Mani testi joprojām izies, jo esmu mockējis visu funkcionalitāti. Ļaujiet man parādīt, ko es domāju.

```php
// UserControllerDICV2.php
class UserControllerDICV2 {

	// ... other methods ...

	protected function isEmailValid($email) {
		// Changed logic
		$validEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
		// Now it should only have a specific domain
		$validDomain = strpos($email, '@example.com') !== false; 
		return $validEmail && $validDomain;
	}
}
```

Ja es palaižu savus iepriekšējos vienības testus, tie joprojām iziet! Bet tāpēc, ka es netestēju uzvedību (patiesībā neļāvu daļai koda darboties), esmu potenciāli ieprogrammējis kļūdu, kas gaida ražošanā. Tests vajadzētu modificēt, lai ņemtu vērā jauno uzvedību, un arī pretējo, kad uzvedība nav tā, ko mēs sagaidām.

## Pilns piemērs

Varat atrast pilnu Flight PHP projektu piemēru ar vienības testiem GitHub: [n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide).
Lai iegūtu dziļāku izpratni, skatiet [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles).

## Biežas kļūdas

- **Pārāk daudz mockēšana**: Nemockējiet katru atkarību; ļaujiet daļai loģikas (piemēram, kontrolera validēšanai) darboties, lai testētu reālo uzvedību. Skatiet [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles).
- **Globālais stāvoklis**: Izmantojot globālos PHP mainīgos (piemēram, [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php), [`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php)) intensīvi, testi kļūst trausli. Tas pats attiecas uz `Flight::`. Refaktorējiet, lai eksplíciti nodotu atkarības.
- **Sarežģīta iestatīšana**: Ja testa iestatīšana ir apgrūtinoša, jūsu klasei var būt pārāk daudz atkarību vai atbildību, kas pārkāpj [SOLID principus](/learn/unit-testing-and-solid-principles).

## Mērogošana ar vienības testiem

Vienības testi spīd lielākos projektos vai kad atgriežaties pie koda pēc mēnešiem. Tie dokumentē uzvedību un uztver regresijas, ietaupot laiku no atkārtotas mācīšanās jūsu lietojumprogrammai. Solo izstrādātājiem testējiet kritiskos ceļus (piemēram, lietotāja reģistrāciju, maksājumu apstrādi). Komandām testi nodrošina konsekventu uzvedību visās ieguldījumos. Skatiet [Why Frameworks?](/learn/why-frameworks) par vairāk priekšrocībām, izmantojot ietvarus un testus.

Iesaistiet savus testēšanas padomus Flight PHP dokumentācijas repozitorijā!

_Rakstījis [n0nag0n](https://github.com/n0nag0n) 2025_