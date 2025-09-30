# Unit Testing in Flight PHP mit PHPUnit

Dieser Leitfaden führt Unit Testing in Flight PHP mit [PHPUnit](https://phpunit.de/) ein, gerichtet an Anfänger, die verstehen möchten, *warum* Unit Testing wichtig ist und wie man es praktisch anwendet. Wir konzentrieren uns auf das Testen von *Verhalten* – das Sicherstellen, dass Ihre Anwendung das tut, was Sie erwarten, wie das Senden einer E-Mail oder das Speichern eines Datensatzes – anstelle von trivialen Berechnungen. Wir beginnen mit einem einfachen [Route Handler](/learn/routing) und gehen zu einem komplexeren [Controller](/learn/routing) über, unter Einbeziehung von [Dependency Injection](/learn/dependency-injection-container) (DI) und dem Mocken von Drittanbieter-Services.

## Warum Unit Tests?

Unit Testing stellt sicher, dass Ihr Code wie erwartet verhält, und fängt Bugs ab, bevor sie in die Produktion gelangen. Es ist besonders wertvoll in Flight, wo leichte Routing und Flexibilität zu komplexen Interaktionen führen können. Für Solo-Entwickler oder Teams dienen Unit Tests als Sicherheitsnetz, dokumentieren erwartetes Verhalten und verhindern Regressionen, wenn Sie später Code erneut betrachten. Sie verbessern auch das Design: Schwierig zu testender Code signalisiert oft übermäßig komplexe oder eng gekoppelte Klassen.

Im Gegensatz zu simplistischen Beispielen (z. B. Testen von `x * y = z`) konzentrieren wir uns auf reale Verhaltensweisen, wie die Validierung von Eingaben, das Speichern von Daten oder das Auslösen von Aktionen wie E-Mails. Unser Ziel ist es, Testing zugänglich und sinnvoll zu machen.

## Allgemeine Leitprinzipien

1. **Verhalten testen, nicht Implementierung**: Konzentrieren Sie sich auf Ergebnisse (z. B. „E-Mail gesendet“ oder „Datensatz gespeichert“) anstelle interner Details. Das macht Tests robust gegenüber Refactoring.
2. **Vermeiden Sie `Flight::`**: Flights statische Methoden sind bequem, machen Testing aber schwierig. Gewöhnen Sie sich daran, die `$app`-Variable aus `$app = Flight::app();` zu verwenden. `$app` hat alle Methoden, die `Flight::` hat. Sie können immer noch `$app->route()` oder `$this->app->json()` in Ihrem Controller usw. verwenden. Verwenden Sie auch den echten Flight-Router mit `$router = $app->router()` und dann können Sie `$router->get()`, `$router->post()`, `$router->group()` usw. nutzen. Siehe [Routing](/learn/routing).
3. **Halten Sie Tests schnell**: Schnelle Tests fördern häufige Ausführung. Vermeiden Sie langsame Operationen wie Datenbankaufrufe in Unit Tests. Wenn Sie einen langsamen Test haben, ist das ein Zeichen, dass Sie einen Integration Test schreiben, keinen Unit Test. Integration Tests beinhalten echte Datenbanken, echte HTTP-Aufrufe, echtes E-Mail-Versenden usw. Sie haben ihren Platz, sind aber langsam und können unzuverlässig sein, was bedeutet, dass sie manchmal aus unbekannten Gründen fehlschlagen. 
4. **Verwenden Sie beschreibende Namen**: Testnamen sollten das getestete Verhalten klar beschreiben. Das verbessert Lesbarkeit und Wartbarkeit.
5. **Vermeiden Sie Globals wie die Pest**: Minimieren Sie die Nutzung von `$app->set()` und `$app->get()`, da sie wie globaler Zustand wirken und in jedem Test Mocks erfordern. Bevorzugen Sie DI oder einen DI-Container (siehe [Dependency Injection Container](/learn/dependency-injection-container)). Sogar die Verwendung der Methode `$app->map()` ist technisch „global“ und sollte zugunsten von DI vermieden werden. Verwenden Sie eine Session-Bibliothek wie [flightphp/session](https://github.com/flightphp/session), damit Sie das Session-Objekt in Ihren Tests mocken können. **Rufen Sie** [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php) nicht direkt in Ihrem Code auf, da das eine globale Variable in Ihren Code injiziert und das Testing erschwert.
6. **Verwenden Sie Dependency Injection**: Injizieren Sie Abhängigkeiten (z. B. [`PDO`](https://www.php.net/manual/en/class.pdo.php), Mailer) in Controller, um Logik zu isolieren und das Mocken zu vereinfachen. Wenn eine Klasse zu viele Abhängigkeiten hat, überlegen Sie, sie in kleinere Klassen umzustrukturieren, die jeweils eine einzige Verantwortung haben und den [SOLID-Prinzipien](https://en.wikipedia.org/wiki/SOLID) folgen.
7. **Mocken Sie Drittanbieter-Services**: Mocken Sie Datenbanken, HTTP-Clients (cURL) oder E-Mail-Services, um externe Aufrufe zu vermeiden. Testen Sie eine oder zwei Ebenen tief, lassen Sie aber Ihre Kernlogik laufen. Zum Beispiel, wenn Ihre App eine SMS sendet, wollen Sie **NICHT** wirklich eine SMS bei jedem Testlauf senden, da die Kosten steigen (und es langsamer wird). Stattdessen mocken Sie den SMS-Service und überprüfen nur, ob Ihr Code den SMS-Service mit den richtigen Parametern aufgerufen hat.
8. **Streben Sie hohe Abdeckung an, nicht Perfektion**: 100% Zeilenabdeckung ist gut, bedeutet aber nicht, dass alles in Ihrem Code so getestet wird, wie es sein sollte (recherchieren Sie [Branch/Path Coverage in PHPUnit](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/)). Priorisieren Sie kritische Verhaltensweisen (z. B. Benutzerregistrierung, API-Antworten und das Erfassen fehlgeschlagener Antworten).
9. **Verwenden Sie Controller für Routes**: In Ihren Route-Definitionen verwenden Sie Controller, keine Closures. Die `flight\Engine $app` wird standardmäßig über den Konstruktor in jeden Controller injiziert. In Tests verwenden Sie `$app = new Flight\Engine()`, um Flight innerhalb eines Tests zu instanziieren, injizieren Sie es in Ihren Controller und rufen Methoden direkt auf (z. B. `$controller->register()`). Siehe [Extending Flight](/learn/extending) und [Routing](/learn/routing).
10. **Wählen Sie einen Mocking-Stil und halten Sie sich daran**: PHPUnit unterstützt mehrere Mocking-Stile (z. B. prophecy, eingebaute Mocks), oder Sie können anonyme Klassen verwenden, die eigene Vorteile haben wie Code-Vervollständigung, Brechen, wenn Sie die Methodendefinition ändern usw. Seien Sie einfach konsistent in Ihren Tests. Siehe [PHPUnit Mock Objects](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles).
11. **Verwenden Sie `protected` Sichtbarkeit für Methoden/Eigenschaften, die Sie in Subklassen testen möchten**: Das erlaubt es, sie in Test-Subklassen zu überschreiben, ohne sie public zu machen, was besonders nützlich für anonyme Klassen-Mocks ist.

## Einrichten von PHPUnit

Richten Sie zuerst [PHPUnit](https://phpunit.de/) in Ihrem Flight PHP-Projekt mit Composer ein, für einfaches Testing. Siehe den [PHPUnit Getting Started Guide](https://phpunit.readthedocs.io/en/12.3/installation.html) für mehr Details.

1. In Ihrem Projektverzeichnis ausführen:
   ```bash
   composer require --dev phpunit/phpunit
   ```
   Das installiert die neueste PHPUnit als Entwicklungsabhängigkeit.

2. Erstellen Sie ein `tests`-Verzeichnis in der Wurzel Ihres Projekts für Testdateien.

3. Fügen Sie ein Test-Skript zu `composer.json` für Bequemlichkeit hinzu:
   ```json
   // andere composer.json-Inhalte
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```

4. Erstellen Sie eine `phpunit.xml`-Datei in der Wurzel:
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

Nun können Sie, wenn Ihre Tests aufgebaut sind, `composer test` ausführen, um Tests auszuführen.

## Testen eines einfachen Route Handlers

Lassen Sie uns mit einer grundlegenden [Route](/learn/routing) beginnen, die die E-Mail-Eingabe eines Benutzers validiert. Wir testen ihr Verhalten: Rückgabe einer Erfolgsnachricht für gültige E-Mails und einer Fehlermeldung für ungültige. Für die E-Mail-Validierung verwenden wir [`filter_var`](https://www.php.net/manual/en/function.filter-var.php).

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
			$responseArray = ['status' => 'error', 'message' => 'Ungültige E-Mail'];
		} else {
			$responseArray = ['status' => 'success', 'message' => 'Gültige E-Mail'];
		}

		$this->app->json($responseArray);
	}
}
```

Um das zu testen, erstellen Sie eine Testdatei. Siehe [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) für mehr über die Strukturierung von Tests:

```php
// tests/UserControllerTest.php
use PHPUnit\Framework\TestCase;
use Flight;
use flight\Engine;

// Kommentar: Testklasse für UserController
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
        $this->assertEquals('Gültige E-Mail', $output['message']);
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
		$this->assertEquals('Ungültige E-Mail', $output['message']);
	}
}
```

**Wichtige Punkte**:
- Wir simulieren POST-Daten mit der Request-Klasse. Verwenden Sie keine Globals wie `$_POST`, `$_GET` usw., da das Testing komplizierter macht (Sie müssen diese Werte immer zurücksetzen, oder andere Tests könnten fehlschlagen).
- Alle Controller haben standardmäßig die `flight\Engine`-Instanz injiziert, auch ohne einen DIC-Container einzurichten. Das macht es viel einfacher, Controller direkt zu testen.
- Es gibt keine `Flight::`-Nutzung, was den Code einfacher testbar macht.
- Tests überprüfen Verhalten: Korrekter Status und Nachricht für gültige/ungültige E-Mails.

Führen Sie `composer test` aus, um zu überprüfen, ob die Route wie erwartet verhält. Für mehr über [Requests](/learn/requests) und [Responses](/learn/responses) in Flight siehe die relevanten Docs.

## Verwendung von Dependency Injection für testbare Controller

Für komplexere Szenarien verwenden Sie [Dependency Injection](/learn/dependency-injection-container) (DI), um Controller testbar zu machen. Vermeiden Sie Flights Globals (z. B. `Flight::set()`, `Flight::map()`, `Flight::register()`), da sie wie globaler Zustand wirken und Mocks für jeden Test erfordern. Stattdessen verwenden Sie Flights DI-Container, [DICE](https://github.com/Level-2/Dice), [PHP-DI](https://php-di.org/) oder manuelles DI.

Lassen Sie uns [`flight\database\PdoWrapper`](/learn/pdo-wrapper) anstelle von raw PDO verwenden. Dieser Wrapper ist viel einfacher zu mocken und für Unit Tests!

Hier ist ein Controller, der einen Benutzer in eine Datenbank speichert und eine Willkommens-E-Mail sendet:

```php
use flight\database\PdoWrapper;

// Kommentar: Controller mit DI für Datenbank und Mailer
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
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Ungültige E-Mail']);
		}

		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);

		return $this->app->json(['status' => 'success', 'message' => 'Benutzer registriert']);
    }
}
```

**Wichtige Punkte**:
- Der Controller hängt von einer [`PdoWrapper`](/learn/pdo-wrapper)-Instanz und einer `MailerInterface` ab (ein fingierter Drittanbieter-E-Mail-Service).
- Abhängigkeiten werden über den Konstruktor injiziert, Globals werden vermieden.

### Testen des Controllers mit Mocks

Nun testen wir das Verhalten des `UserController`: Validierung von E-Mails, Speichern in der Datenbank und Senden von E-Mails. Wir mocken die Datenbank und den Mailer, um den Controller zu isolieren.

```php
// tests/UserControllerDICTest.php
use PHPUnit\Framework\TestCase;

// Kommentar: Testklasse mit DI und Mocks
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
        $this->assertEquals('Benutzer registriert', $result['message']);
        $this->assertEquals('test@example.com', $mockMailer->sentEmail);
    }

    public function testInvalidEmailSkipsSaveAndEmail() {
		 $mockDb = new class() extends PdoWrapper {
			// An empty constructor bypasses the parent constructor
			public function __construct() {}
            public function runQuery(string $sql, array $params = []): PDOStatement {
                throw new Exception('Sollte nicht aufgerufen werden');
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                throw new Exception('Sollte nicht aufgerufen werden');
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
        $this->assertEquals('Ungültige E-Mail', $result['message']);
    }
}
```

**Wichtige Punkte**:
- Wir mocken `PdoWrapper` und `MailerInterface`, um echte Datenbank- oder E-Mail-Aufrufe zu vermeiden.
- Tests überprüfen Verhalten: Gültige E-Mails lösen Datenbank-Inserts und E-Mail-Versand aus; ungültige E-Mails überspringen beides.
- Mocken Sie Drittanbieter-Abhängigkeiten (z. B. `PdoWrapper`, `MailerInterface`) und lassen Sie die Logik des Controllers laufen.

### Zu viel Mocken

Seien Sie vorsichtig, nicht zu viel von Ihrem Code zu mocken. Lassen Sie mich ein Beispiel unten geben, warum das schlecht sein könnte, mit unserem `UserController`. Wir ändern diese Überprüfung in eine Methode namens `isEmailValid` (mit `filter_var`) und die anderen neuen Ergänzungen in eine separate Methode namens `registerUser`.

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
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Ungültige E-Mail']);
		}

		$this->registerUser($email);

		$this->app->json(['status' => 'success', 'message' => 'Benutzer registriert']);
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

Und nun der übermäßig gemockte Unit Test, der eigentlich nichts testet:

```php
use PHPUnit\Framework\TestCase;

// Kommentar: Übermäßig gemockter Test, der nichts überprüft
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
        $this->assertEquals('Benutzer registriert', $result['message']);
    }
}
```

Hurra, wir haben Unit Tests und sie laufen! Aber warte, was, wenn ich tatsächlich die internen Funktionen von `isEmailValid` oder `registerUser` ändere? Meine Tests laufen immer noch, weil ich alle Funktionalität gemockt habe. Lassen Sie mich zeigen, was ich meine.

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

Wenn ich meine obigen Unit Tests ausführe, laufen sie immer noch! Aber weil ich nicht für Verhalten getestet habe (tatsächlich etwas Code laufen lassen), habe ich potenziell einen Bug kodiert, der in der Produktion auftritt. Der Test sollte für das neue Verhalten angepasst werden und auch für den umgekehrten Fall, wenn das Verhalten nicht das ist, was wir erwarten.

## Vollständiges Beispiel

Sie finden ein vollständiges Beispiel eines Flight PHP-Projekts mit Unit Tests auf GitHub: [n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide).
Für tieferes Verständnis siehe [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles).

## Häufige Fallstricke

- **Über-Mocken**: Mocken Sie nicht jede Abhängigkeit; lassen Sie etwas Logik (z. B. Controller-Validierung) laufen, um echtes Verhalten zu testen. Siehe [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles).
- **Globaler Zustand**: Die starke Nutzung globaler PHP-Variablen (z. B. [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php), [`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php)) macht Tests spröde. Dasselbe gilt für `Flight::`. Refactoren Sie, um Abhängigkeiten explizit zu übergeben.
- **Komplexe Einrichtung**: Wenn die Testeinrichtung umständlich ist, hat Ihre Klasse möglicherweise zu viele Abhängigkeiten oder Verantwortlichkeiten, die den [SOLID-Prinzipien](/learn/unit-testing-and-solid-principles) verletzen.

## Skalieren mit Unit Tests

Unit Tests glänzen in größeren Projekten oder beim Wiederbesuch von Code nach Monaten. Sie dokumentieren Verhalten und fangen Regressionen ab, sparen Ihnen das erneute Lernen Ihrer App. Für Solo-Entwickler testen Sie kritische Pfade (z. B. Benutzeranmeldung, Zahlungsabwicklung). Für Teams stellen Tests konsistentes Verhalten über Beiträge hinweg sicher. Siehe [Why Frameworks?](/learn/why-frameworks) für mehr über die Vorteile von Frameworks und Tests.

Beitragen Sie Ihre eigenen Testing-Tipps zum Flight PHP-Dokumentations-Repository!

_Geschrieben von [n0nag0n](https://github.com/n0nag0n) 2025_