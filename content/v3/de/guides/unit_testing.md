# Unit Testing in Flight PHP mit PHPUnit

Dieser Leitfaden führt in das Unit Testing in Flight PHP mit [PHPUnit](https://phpunit.de/) ein, für Anfänger, die verstehen wollen, *warum* Unit Testing wichtig ist und wie man es praktisch anwendet. Wir konzentrieren uns auf das Testen von *Verhalten* – sicherzustellen, dass Ihre Anwendung das tut, was erwartet wird, wie das Versenden einer E-Mail oder das Speichern eines Datensatzes – anstatt triviale Berechnungen. Wir beginnen mit einem einfachen [route handler](/learn/routing) und gehen zu einem komplexeren [controller](/learn/routing) über, unter Einbeziehung von [dependency injection](/learn/dependency-injection-container) (DI) und dem Mocking von Drittanbieter-Diensten.

## Warum Unit Testen?

Unit Testing stellt sicher, dass Ihr Code so verhält, wie erwartet, und Fehlern vorbeugt, bevor sie in die Produktion gelangen. Es ist besonders wertvoll in Flight, wo leichte Routing und Flexibilität zu komplexen Interaktionen führen können. Für Solo-Entwickler oder Teams dienen Unit Tests als Sicherheitsnetz, dokumentieren erwartetes Verhalten und verhindern Regressionen, wenn Sie später auf den Code zurückgreifen. Sie verbessern auch das Design: Schwierig zu testender Code deutet oft auf übermäßig komplexe oder eng gekoppelte Klassen hin.

Im Gegensatz zu einfachen Beispielen (z. B. das Testen von `x * y = z`) konzentrieren wir uns auf reale Verhaltensweisen, wie die Validierung von Eingaben, das Speichern von Daten oder das Auslösen von Aktionen wie E-Mails. Unser Ziel ist es, das Testen zugänglich und sinnvoll zu machen.

## Allgemeine Leitlinien

1. **Testen Sie Verhalten, nicht Implementierung**: Konzentrieren Sie sich auf Ergebnisse (z. B. „E-Mail gesendet“ oder „Datensatz gespeichert“), anstatt auf interne Details. Das macht Tests robust gegen Refactorings.
2. **Stop using `Flight::`**: Flight’s statische Methoden sind sehr praktisch, machen aber das Testen schwierig. Gewöhnen Sie sich an die Verwendung der `$app`-Variable von `$app = Flight::app();`. `$app` hat alle gleichen Methoden wie `Flight::`. Sie können weiterhin `$app->route()` oder `$this->app->json()` in Ihrem Controller usw. verwenden. Verwenden Sie auch den echten Flight-Router mit `$router = $app->router()` und dann `$router->get()`, `$router->post()`, `$router->group()` usw. Siehe [Routing](/learn/routing).
3. **Halten Sie Tests schnell**: Schnelle Tests fördern eine häufige Ausführung. Vermeiden Sie langsame Operationen wie Datenbankaufrufe in Unit Tests. Wenn ein Test langsam ist, ist das ein Hinweis, dass Sie einen Integrationstest schreiben, nicht einen Unit Test. Integrationstests beinhalten echte Datenbanken, echte HTTP-Aufrufe, echtes E-Mail-Versenden usw. Sie haben ihren Platz, sind aber langsam und können fehleranfällig sein, d. h. sie versagen manchmal aus unbekannten Gründen.
4. **Verwenden Sie beschreibende Namen**: Testnamen sollten klar das getestete Verhalten beschreiben. Das verbessert Lesbarkeit und Wartbarkeit.
5. **Vermeiden Sie Globals wie die Pest**: Minimieren Sie die Verwendung von `$app->set()` und `$app->get()`, da sie wie globaler Zustand wirken und in jedem Test gemockt werden müssen. Ziehen Sie DI oder einen DI-Container vor (siehe [Dependency Injection Container](/learn/dependency-injection-container)). Sogar die Verwendung von `$app->map()` ist technisch ein „Global“ und sollte zugunsten von DI vermieden werden. Verwenden Sie eine Session-Bibliothek wie [flightphp/session](https://github.com/flightphp/session), damit Sie das Session-Objekt in Ihren Tests mocken können. **Do not** call [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php) direkt in Ihrem Code, da das eine globale Variable injiziert und das Testen erschwert.
6. **Verwenden Sie Dependency Injection**: Injizieren Sie Abhängigkeiten (z. B. [`PDO`](https://www.php.net/manual/en/class.pdo.php), Mailer) in Controller, um Logik zu isolieren und das Mocking zu vereinfachen. Wenn eine Klasse zu viele Abhängigkeiten hat, überlegen Sie, sie in kleinere Klassen umzustrukturieren, die jeweils eine einzelne Verantwortung haben, gemäß [SOLID-Prinzipien](https://en.wikipedia.org/wiki/SOLID).
7. **Mocken Sie Drittanbieter-Dienste**: Mocken Sie Datenbanken, HTTP-Clients (cURL) oder E-Mail-Dienste, um externe Aufrufe zu vermeiden. Testen Sie eine oder zwei Schichten tief, aber lassen Sie Ihre Kernlogik laufen. Zum Beispiel, wenn Ihre App eine Textnachricht sendet, möchten Sie **NICHT** wirklich eine Textnachricht bei jedem Testlauf senden, da das Kosten verursacht (und langsamer ist). Stattdessen mocken Sie den Textnachrichten-Dienst und überprüfen nur, ob Ihr Code den Dienst mit den richtigen Parametern aufgerufen hat.
8. **Zielen Sie auf hohe Abdeckung, nicht auf Perfektion**: 100% Zeilenabdeckung ist gut, bedeutet aber nicht unbedingt, dass alles im Code richtig getestet wird (recherchieren Sie [branch/path coverage in PHPUnit](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/)). Priorisieren Sie kritische Verhaltensweisen (z. B. Benutzerregistrierung, API-Antworten und das Erfassen fehlerhafter Antworten).
9. **Verwenden Sie Controller für Routes**: In Ihren Routinedefinitionen verwenden Sie Controller, nicht Closures. Die `flight\Engine $app` wird standardmäßig in jeden Controller über den Konstruktor injiziert. In Tests verwenden Sie `$app = new Flight\Engine()`, injizieren es in Ihren Controller und rufen Methoden direkt auf (z. B. `$controller->register()`). Siehe [Extending Flight](/learn/extending) und [Routing](/learn/routing).
10. **Wählen Sie einen Mocking-Stil und halten Sie ihn bei**: PHPUnit unterstützt mehrere Mocking-Stile (z. B. prophecy, eingebaute Mocks) oder Sie können anonyme Klassen verwenden, die Vorteile wie Code-Vervollständigung haben, oder wenn Sie die Methodendefinition ändern. Seien Sie einfach konsistent in Ihren Tests. Siehe [PHPUnit Mock Objects](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles).
11. **Verwenden Sie `protected` Sichtbarkeit für Methoden/Eigenschaften, die Sie in Unterklassen testen möchten**: Das ermöglicht es, sie in Test-Unterklassen zu überschreiben, ohne sie public zu machen, was besonders für anonyme Klass-Mocks nützlich ist.

## Einrichtung von PHPUnit

Richten Sie zuerst [PHPUnit](https://phpunit.de/) in Ihrem Flight PHP-Projekt mit Composer für einfache Tests ein. Siehe den [PHPUnit Getting Started guide](https://phpunit.readthedocs.io/en/12.3/installation.html) für mehr Details.

1. In Ihrem Projektverzeichnis ausführen:
   ```bash
   composer require --dev phpunit/phpunit
   ```
   Das installiert die neueste PHPUnit als Entwicklungsabhängigkeit.

2. Erstellen Sie ein `tests`-Verzeichnis in der Projektwurzel für Testdateien.

3. Fügen Sie ein Test-Skript zu `composer.json` für die Bequemlichkeit hinzu:
   ```json
   // other composer.json content
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

Jetzt können Sie `composer test` ausführen, um Tests auszuführen.

## Testen eines einfachen Route Handlers

Lassen Sie uns mit einer grundlegenden [route](/learn/routing) beginnen, die eine E-Mail-Eingabe eines Benutzers validiert. Wir testen ihr Verhalten: Eine Erfolgsmeldung für gültige E-Mails und einen Fehler für ungültige. Für die E-Mail-Validierung verwenden wir [`filter_var`](https://www.php.net/manual/en/function.filter-var.php).

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

Um das zu testen, erstellen Sie eine Testdatei. Siehe [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) für mehr über die Strukturierung von Tests:

```php
// tests/UserControllerTest.php
use PHPUnit\Framework\TestCase;
use Flight;
use flight\Engine;

class UserControllerTest extends TestCase {

    public function testValidEmailReturnsSuccess() {
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'test@example.com'; // POST-Daten simulieren
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
		$request->data->email = 'invalid-email'; // POST-Daten simulieren
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
		$response = $app->response()->getBody();
		$output = json_decode($response, true);
		$this->assertEquals('error', $output['status']);
		$this->assertEquals('Invalid email', $output['message']);
	}
}
```

**Wichtige Punkte**:
- Wir simulieren POST-Daten mit der Request-Klasse. Verwenden Sie keine Globals wie `$_POST`, `$_GET` usw., da das das Testen komplizierter macht (Sie müssen diese Werte immer zurücksetzen, sonst könnten andere Tests fehlschlagen).
- Alle Controller haben standardmäßig eine `flight\Engine`-Instanz injiziert, auch ohne einen DIC-Container einzurichten. Das macht es viel einfacher, Controller direkt zu testen.
- Es gibt keine `Flight::`-Verwendung, was den Code leichter testbar macht.
- Tests überprüfen Verhalten: Korrekten Status und Nachricht für gültige/ungültige E-Mails.

Führen Sie `composer test` aus, um zu überprüfen, ob die Route wie erwartet verhält. Für mehr über [requests](/learn/requests) und [responses](/learn/responses) in Flight, siehe die relevanten Docs.

## Verwenden von Dependency Injection für testbare Controller

Für komplexere Szenarien verwenden Sie [dependency injection](/learn/dependency-injection-container) (DI), um Controller testbar zu machen. Vermeiden Sie Flight’s Globals (z. B. `Flight::set()`, `Flight::map()`, `Flight::register()`), da sie wie globaler Zustand wirken und in jedem Test gemockt werden müssen. Verwenden Sie stattdessen Flight’s DI-Container, [DICE](https://github.com/Level-2/Dice), [PHP-DI](https://php-di.org/) oder manuelles DI.

Verwenden Sie [`flight\database\PdoWrapper`](/awesome-plugins/pdo-wrapper) anstelle von raw PDO. Dieser Wrapper ist viel einfacher zu mocken und für Unit Tests zu verwenden!

Hier ist ein Controller, der einen Benutzer in eine Datenbank speichert und eine Willkommens-E-Mail sendet:

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
			// Hinzufügen des Returns hier hilft beim Unit Testing, die Ausführung zu stoppen
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Invalid email']);
		}

		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);

		return $this->app->json(['status' => 'success', 'message' => 'User registered']);
    }
}
```

**Wichtige Punkte**:
- Der Controller hängt von einer [`PdoWrapper`](/awesome-plugins/pdo-wrapper)-Instanz und einer `MailerInterface` (einem vorgetäuschten Drittanbieter-E-Mail-Dienst) ab.
- Abhängigkeiten werden über den Konstruktor injiziert, um Globals zu vermeiden.

### Testen des Controllers mit Mocks

Nun testen wir das Verhalten von `UserController`: Validierung von E-Mails, Speichern in der Datenbank und Senden von E-Mails. Wir mocken die Datenbank und den Mailer, um den Controller zu isolieren.

```php
// tests/UserControllerDICTest.php
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {

		// Manchmal ist das Mischen von Mocking-Stilen notwendig
		// Hier verwenden wir PHPUnit's eingebaugenes Mock für PDOStatement
		$statementMock = $this->createMock(PDOStatement::class);
		$statementMock->method('execute')->willReturn(true);
		// Verwenden einer anonymen Klasse, um PdoWrapper zu mocken
        $mockDb = new class($statementMock) extends PdoWrapper {
			protected $statementMock;
			public function __construct($statementMock) {
				$this->statementMock = $statementMock;
			}

			// Wenn wir es so mocken, wird kein echter Datenbankaufruf gemacht.
			// Wir können das weiter einrichten, um das PDOStatement-Mock zu simulieren, z. B. Fehler
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
			// Ein leerer Konstruktor umgeht den Elternkonstruktor
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

		// jsonHalt muss gemappt werden, um das Beenden zu vermeiden
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

**Wichtige Punkte**:
- Wir mocken `PdoWrapper` und `MailerInterface`, um echte Datenbank- oder E-Mail-Aufrufe zu vermeiden.
- Tests überprüfen Verhalten: Gültige E-Mails lösen Datenbankeinträge und E-Mail-Versand aus; ungültige E-Mails überspringen beides.
- Mocken Sie Drittanbieter-Abhängigkeiten (z. B. `PdoWrapper`, `MailerInterface`), lassen Sie die Logik des Controllers laufen.

### Zu viel Mocking

Seien Sie vorsichtig, nicht zu viel zu mocken. Hier ein Beispiel, warum das problematisch sein könnte, anhand unseres `UserController`. Wir ändern die Überprüfung in eine Methode namens `isEmailValid` (mit `filter_var`) und die anderen neuen Ergänzungen in eine separate Methode namens `registerUser`.

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
			// Hinzufügen des Returns hier hilft beim Unit Testing, die Ausführung zu stoppen
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

Und nun der übermockte Unit Test, der eigentlich nichts testet:

```php
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
		// Wir überspringen die extra Dependency Injection hier, da es "einfach" ist
        $controller = new class($app) extends UserControllerDICV2 {
			protected $app;
			// Umgehe die Abhängigkeiten im Konstruktor
			public function __construct($app) {
				$this->app = $app;
			}

			// Wir erzwingen einfach, dass es gültig ist.
			protected function isEmailValid($email) {
				return true; // Immer true zurückgeben, um echte Validierung zu umgehen
			}

			// Umgehe die tatsächlichen DB- und Mailer-Aufrufe
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

Hurra, wir haben Unit Tests und sie bestehen! Aber warte, was, wenn ich die internen Abläufe von `isEmailValid` oder `registerUser` wirklich ändere? Die Tests bestehen immer noch, weil ich alle Funktionalität gemockt habe. Lassen Sie mich zeigen, was ich meine.

```php
// UserControllerDICV2.php
class UserControllerDICV2 {

	// ... other methods ...

	protected function isEmailValid($email) {
		// Geänderte Logik
		$validEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
		// Nun sollte es nur eine spezifische Domain haben
		$validDomain = strpos($email, '@example.com') !== false; 
		return $validEmail && $validDomain;
	}
}
```

Wenn ich meine obigen Unit Tests ausführe, bestehen sie immer noch! Aber weil ich nicht auf Verhalten getestet habe (und etwas Code laufen lasse), habe ich potenziell einen Bug in der Produktion. Der Test sollte angepasst werden, um das neue Verhalten zu berücksichtigen, und auch das Gegenteil, wenn das Verhalten nicht das ist, was wir erwarten.

## Vollständiges Beispiel

Ein vollständiges Beispiel eines Flight PHP-Projekts mit Unit Tests finden Sie auf GitHub: [n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide).
Für mehr Leitfäden, siehe [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) und [Troubleshooting](/learn/troubleshooting).

## Häufige Fallstricke

- **Über-Mocking**: Mocken Sie nicht jede Abhängigkeit; lassen Sie etwas Logik (z. B. Controller-Validierung) laufen, um reales Verhalten zu testen. Siehe [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles).
- **Globaler Zustand**: Die starke Verwendung globaler PHP-Variablen (z. B. [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php), [`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php)) macht Tests empfindlich. Das Gleiche gilt für `Flight::`. Refactorisieren Sie, um Abhängigkeiten explizit zu übergeben.
- **Komplizierte Einrichtung**: Wenn die Testeinrichtung umständlich ist, hat Ihre Klasse möglicherweise zu viele Abhängigkeiten oder Verantwortlichkeiten, die den [SOLID-Prinzipien](https://en.wikipedia.org/wiki/SOLID) widersprechen.

## Skalierung mit Unit Tests

Unit Tests leuchten in größeren Projekten oder wenn Sie nach Monaten auf Code zurückgreifen. Sie dokumentieren Verhalten und fangen Regressionen ab, was Sie davor schützt, Ihre App neu lernen zu müssen. Für Solo-Entwickler testen Sie kritische Pfade (z. B. Benutzeranmeldung, Zahlungsabwicklung). Für Teams stellen Tests ein konsistentes Verhalten über Beiträge hinweg sicher. Siehe [Why Frameworks?](/learn/why-frameworks) für mehr über die Vorteile von Frameworks und Tests.

Teilen Sie Ihre eigenen Testtipps zum Flight PHP-Dokumentationsrepository bei!

_Geschrieben von [n0nag0n](https://github.com/n0nag0n) 2025_