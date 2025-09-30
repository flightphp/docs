# Vienības testēšana

## Pārskats

Vienības testēšana Flight palīdz nodrošināt, ka jūsu lietojumprogramma darbojas kā paredzēts, agrīnā stadijā uztver kļūdas un padara jūsu koda bāzi vieglāk uzturamu. Flight ir izstrādāts, lai gludi darbotos ar [PHPUnit](https://phpunit.de/), populārāko PHP testēšanas ietvaru.

## Saprašana

Vienības testi pārbauda jūsu lietojumprogrammas mazo daļu uzvedību (piemēram, kontrolierus vai servisus) izolēti. Flight tas nozīmē testēšanu, kā jūsu maršruti, kontrolieri un loģika reaģē uz dažādām ievadēm — bez paļaušanās uz globālo stāvokli vai reāliem ārējiem servisiem.

Galvenie principi:
- **Testējiet uzvedību, nevis ieviešanu:** Koncentrējieties uz to, ko jūsu kods dara, nevis kā tas to dara.
- **Izvairieties no globālā stāvokļa:** Izmantojiet atkarību injekciju vietā `Flight::set()` vai `Flight::get()`.
- **Mock ārējos servisus:** Aizstājiet tādas lietas kā datubāzes vai pasta sūtītājus ar testa dubultiem.
- **Turiet testus ātrus un fokusētus:** Vienības testi nedrīkst pieskarties reālām datubāzēm vai API.

## Pamata izmantošana

### PHPUnit iestatīšana

1. Instalējiet PHPUnit ar Composer:
   ```bash
   composer require --dev phpunit/phpunit
   ```
2. Izveidojiet `tests` direktoriju jūsu projekta saknes mapē.
3. Pievienojiet testa skriptu jūsu `composer.json`:
   ```json
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```
4. Izveidojiet `phpunit.xml` failu:
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

Tagad jūs varat palaist savus testus ar `composer test`.

### Vienkārša maršruta apstrādātāja testēšana

Pieņemsim, ka jums ir maršruts, kas validē e-pastu:

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
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->app->json(['status' => 'error', 'message' => 'Invalid email']);
        }
        return $this->app->json(['status' => 'success', 'message' => 'Valid email']);
    }
}
```

Vienkāršs tests šim kontrolierim:

```php
use PHPUnit\Framework\TestCase;
use flight\Engine;

class UserControllerTest extends TestCase {
    public function testValidEmailReturnsSuccess() {
        $app = new Engine();
        $app->request()->data->email = 'test@example.com';
        $controller = new UserController($app);
        $controller->register();
        $response = $app->response()->getBody();
        $output = json_decode($response, true);
        $this->assertEquals('success', $output['status']);
        $this->assertEquals('Valid email', $output['message']);
    }

    public function testInvalidEmailReturnsError() {
        $app = new Engine();
        $app->request()->data->email = 'invalid-email';
        $controller = new UserController($app);
        $controller->register();
        $response = $app->response()->getBody();
        $output = json_decode($response, true);
        $this->assertEquals('error', $output['status']);
        $this->assertEquals('Invalid email', $output['message']);
    }
}
```

**Padomi:**
- Simulējiet POST datus, izmantojot `$app->request()->data`.
- Izvairieties no `Flight::` statiskām metodēm savos testos — izmantojiet `$app` экземпlāru.

### Atkarību injekcijas izmantošana testējamu kontrolieru gadījumā

Injicējiet atkarības (piemēram, datubāzi vai pasta sūtītāju) savos kontrolieros, lai tos būtu viegli mock testos:

```php
use flight\database\PdoWrapper;

class UserController {
    protected $app;
    protected $db;
    protected $mailer;
    public function __construct($app, $db, $mailer) {
        $this->app = $app;
        $this->db = $db;
        $this->mailer = $mailer;
    }
    public function register() {
        $email = $this->app->request()->data->email;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->app->json(['status' => 'error', 'message' => 'Invalid email']);
        }
        $this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
        $this->mailer->sendWelcome($email);
        return $this->app->json(['status' => 'success', 'message' => 'User registered']);
    }
}
```

Un tests ar mock:

```php
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {
        $mockDb = $this->createMock(flight\database\PdoWrapper::class);
        $mockDb->method('runQuery')->willReturn(true);
        $mockMailer = new class {
            public $sentEmail = null;
            public function sendWelcome($email) { $this->sentEmail = $email; return true; }
        };
        $app = new flight\Engine();
        $app->request()->data->email = 'test@example.com';
        $controller = new UserController($app, $mockDb, $mockMailer);
        $controller->register();
        $response = $app->response()->getBody();
        $result = json_decode($response, true);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('User registered', $result['message']);
        $this->assertEquals('test@example.com', $mockMailer->sentEmail);
    }
}
```

## Uzlabota izmantošana

- **Mocking:** Izmantojiet PHPUnit iebūvētos mock vai anonīmas klases, lai aizstātu atkarības.
- **Tieša kontrolieru testēšana:** Instantējiet kontrolierus ar jaunu `Engine` un mock atkarības.
- **Izvairieties no pārmērīga mocking:** Ļaujiet reālajai loģikai darboties, kur iespējams; mock tikai ārējos servisus.

## Skatīt arī

- [Unit Testing Guide](/guides/unit-testing) - Visaptverošs ceļvedis par vienības testēšanas labākajām praksēm.
- [Dependency Injection Container](/learn/dependency-injection-container) - Kā izmantot DIC, lai pārvaldītu atkarības un uzlabotu testējamību.
- [Extending](/learn/extending) - Kā pievienot savus palīglīdzekļus vai pārrakstīt kodola klases.
- [PDO Wrapper](/learn/pdo-wrapper) - Vienkāršo datubāzes mijiedarbību un ir vieglāk mock testos.
- [Requests](/learn/requests) - HTTP pieprasījumu apstrāde Flight.
- [Responses](/learn/responses) - Atbilžu sūtīšana lietotājiem.
- [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) - Uzziniet, kā SOLID principi var uzlabot jūsu vienības testus.

## Traucējumu novēršana

- Izvairieties no globālā stāvokļa (`Flight::set()`, `$_SESSION` utt.) izmantošanas savā kodā un testos.
- Ja jūsu testi ir lēni, iespējams, jūs rakstāt integrācijas testus — mock ārējos servisus, lai vienības testi paliktu ātri.
- Ja testa iestatīšana ir sarežģīta, apsveriet sava koda refaktoringu, lai izmantotu atkarību injekciju.

## Izmaiņu žurnāls

- v3.15.0 - Pievienoti piemēri atkarību injekcijai un mocking.