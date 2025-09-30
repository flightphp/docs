# Sicherheit

## Überblick

Sicherheit ist ein großes Thema, wenn es um Webanwendungen geht. Sie wollen sicherstellen, dass Ihre Anwendung sicher ist und dass die Daten Ihrer Benutzer geschützt sind. Flight bietet eine Reihe von Funktionen, um Ihnen bei der Absicherung Ihrer Webanwendungen zu helfen.

## Verständnis

Es gibt eine Reihe gängiger Sicherheitsbedrohungen, auf die Sie achten sollten, wenn Sie Webanwendungen entwickeln. Einige der häufigsten Bedrohungen umfassen:
- Cross Site Request Forgery (CSRF)
- Cross Site Scripting (XSS)
- SQL Injection
- Cross Origin Resource Sharing (CORS)

[Templates](/learn/templates) helfen bei XSS, indem sie die Ausgabe standardmäßig escapen, sodass Sie sich nicht daran erinnern müssen, das zu tun. [Sessions](/awesome-plugins/session) können bei CSRF helfen, indem sie ein CSRF-Token in der Benutzersitzung speichern, wie unten beschrieben. Die Verwendung vorbereiteter Anweisungen mit PDO kann SQL-Injection-Angriffe verhindern (oder die bequemen Methoden in der [PdoWrapper](/learn/pdo-wrapper)-Klasse). CORS kann mit einem einfachen Hook gehandhabt werden, bevor `Flight::start()` aufgerufen wird.

All diese Methoden arbeiten zusammen, um Ihre Webanwendungen sicher zu halten. Es sollte immer im Vordergrund Ihres Geistes stehen, Sicherheitsbest Practices zu lernen und zu verstehen.

## Grundlegende Verwendung

### Header

HTTP-Header sind eine der einfachsten Möglichkeiten, Ihre Webanwendungen abzusichern. Sie können Header verwenden, um Clickjacking, XSS und andere Angriffe zu verhindern. Es gibt mehrere Möglichkeiten, diese Header zu Ihrer Anwendung hinzuzufügen.

Zwei großartige Websites, um die Sicherheit Ihrer Header zu überprüfen, sind [securityheaders.com](https://securityheaders.com/) und [observatory.mozilla.org](https://observatory.mozilla.org/). Nachdem Sie den unten stehenden Code eingerichtet haben, können Sie leicht überprüfen, ob Ihre Header mit diesen zwei Websites funktionieren.

#### Manuell hinzufügen

Sie können diese Header manuell hinzufügen, indem Sie die `header`-Methode auf dem `Flight\Response`-Objekt verwenden.
```php
// Setze den X-Frame-Options-Header, um Clickjacking zu verhindern
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Setze den Content-Security-Policy-Header, um XSS zu verhindern
// Hinweis: Dieser Header kann sehr komplex werden, daher möchten Sie
//  Beispiele im Internet für Ihre Anwendung konsultieren
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Setze den X-XSS-Protection-Header, um XSS zu verhindern
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Setze den X-Content-Type-Options-Header, um MIME-Sniffing zu verhindern
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Setze den Referrer-Policy-Header, um zu steuern, wie viel Referrer-Informationen gesendet werden
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Setze den Strict-Transport-Security-Header, um HTTPS zu erzwingen
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Setze den Permissions-Policy-Header, um zu steuern, welche Features und APIs verwendet werden können
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Diese können oben in Ihren `routes.php`- oder `index.php`-Dateien hinzugefügt werden.

#### Als Filter hinzufügen

Sie können sie auch in einem Filter/Hook wie dem Folgenden hinzufügen:

```php
// Füge die Header in einem Filter hinzu
Flight::before('start', function() {
	Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
	Flight::response()->header("Content-Security-Policy", "default-src 'self'");
	Flight::response()->header('X-XSS-Protection', '1; mode=block');
	Flight::response()->header('X-Content-Type-Options', 'nosniff');
	Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
	Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
	Flight::response()->header('Permissions-Policy', 'geolocation=()');
});
```

#### Als Middleware hinzufügen

Sie können sie auch als Middleware-Klasse hinzufügen, die die größte Flexibilität bietet, für welche Routen dies angewendet werden soll. Im Allgemeinen sollten diese Header auf alle HTML- und API-Antworten angewendet werden.

```php
// app/middlewares/SecurityHeadersMiddleware.php

namespace app\middlewares;

use flight\Engine;

class SecurityHeadersMiddleware
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function before(array $params): void
	{
		$response = $this->app->response();
		$response->header('X-Frame-Options', 'SAMEORIGIN');
		$response->header("Content-Security-Policy", "default-src 'self'");
		$response->header('X-XSS-Protection', '1; mode=block');
		$response->header('X-Content-Type-Options', 'nosniff');
		$response->header('Referrer-Policy', 'no-referrer-when-downgrade');
		$response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		$response->header('Permissions-Policy', 'geolocation=()');
	}
}

// index.php oder wo immer Sie Ihre Routen haben
// FYI: Diese leere String-Gruppe wirkt als globales Middleware für
// alle Routen. Natürlich könnten Sie dasselbe tun und dies nur zu
// spezifischen Routen hinzufügen.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// mehr Routen
}, [ SecurityHeadersMiddleware::class ]);
```

### Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF) ist ein Angriffstyp, bei dem eine bösartige Website den Browser eines Benutzers dazu bringen kann, eine Anfrage an Ihre Website zu senden. Dies kann verwendet werden, um Aktionen auf Ihrer Website ohne das Wissen des Benutzers auszuführen. Flight bietet keinen integrierten CSRF-Schutzmechanismus, aber Sie können Ihren eigenen leicht mit Middleware implementieren.

#### Einrichtung

Zuerst müssen Sie ein CSRF-Token generieren und es in der Benutzersitzung speichern. Sie können dann dieses Token in Ihren Formularen verwenden und es überprüfen, wenn das Formular abgeschickt wird. Wir verwenden das [flightphp/session](/awesome-plugins/session)-Plugin, um Sitzungen zu verwalten.

```php
// Generiere ein CSRF-Token und speichere es in der Benutzersitzung
// (angenommen, Sie haben ein Session-Objekt erstellt und es an Flight angehängt)
// siehe die Session-Dokumentation für weitere Informationen
Flight::register('session', flight\Session::class);

// Sie müssen nur ein einzelnes Token pro Sitzung generieren (damit es über
// mehrere Tabs und Anfragen für denselben Benutzer funktioniert)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### Verwendung der standardmäßigen PHP Flight Template

```html
<!-- Verwende das CSRF-Token in deinem Formular -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- andere Formularfelder -->
</form>
```

##### Verwendung von Latte

Sie können auch eine benutzerdefinierte Funktion setzen, um das CSRF-Token in Ihren Latte-Templates auszugeben.

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// andere Konfigurationen...

	// Setze eine benutzerdefinierte Funktion, um das CSRF-Token auszugeben
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

Und jetzt können Sie in Ihren Latte-Templates die `csrf()`-Funktion verwenden, um das CSRF-Token auszugeben.

```html
<form method="post">
	{csrf()}
	<!-- andere Formularfelder -->
</form>
```

#### CSRF-Token überprüfen

Sie können das CSRF-Token mit mehreren Methoden überprüfen.

##### Middleware

```php
// app/middlewares/CsrfMiddleware.php

namespace app\middleware;

use flight\Engine;

class CsrfMiddleware
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function before(array $params): void
	{
		if($this->app->request()->method == 'POST') {
			$token = $this->app->request()->data->csrf_token;
			if($token !== $this->app->session()->get('csrf_token')) {
				$this->app->halt(403, 'Ungültiges CSRF-Token');
			}
		}
	}
}

// index.php oder wo immer Sie Ihre Routen haben
use app\middlewares\CsrfMiddleware;

Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// mehr Routen
}, [ CsrfMiddleware::class ]);
```

##### Event-Filter

```php
// Dieses Middleware überprüft, ob die Anfrage eine POST-Anfrage ist und ob sie es ist, überprüft es, ob das CSRF-Token gültig ist
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// erfasse das CSRF-Token aus den Formularwerten
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Ungültiges CSRF-Token');
			// oder für eine JSON-Antwort
			Flight::jsonHalt(['error' => 'Ungültiges CSRF-Token'], 403);
		}
	}
});
```

### Cross Site Scripting (XSS)

Cross Site Scripting (XSS) ist ein Angriffstyp, bei dem eine bösartige Formulareingabe Code in Ihre Website injizieren kann. Die meisten dieser Möglichkeiten stammen von Formularwerten, die Ihre Endbenutzer ausfüllen werden. Sie sollten **niemals** der Ausgabe Ihrer Benutzer vertrauen! Nehmen Sie immer an, dass alle von ihnen die besten Hacker der Welt sind. Sie können bösartigen JavaScript- oder HTML-Code in Ihre Seite injizieren. Dieser Code kann verwendet werden, um Informationen von Ihren Benutzern zu stehlen oder Aktionen auf Ihrer Website auszuführen. Mit der View-Klasse von Flight oder einem anderen Templating-Engine wie [Latte](/awesome-plugins/latte) können Sie die Ausgabe leicht escapen, um XSS-Angriffe zu verhindern.

```php
// Nehmen wir an, der Benutzer ist clever und versucht, dies als seinen Namen zu verwenden
$name = '<script>alert("XSS")</script>';

// Dies wird die Ausgabe escapen
Flight::view()->set('name', $name);
// Dies wird ausgeben: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Wenn Sie etwas wie Latte als Ihre View-Klasse registriert haben, wird es dies auch automatisch escapen.
Flight::view()->render('template', ['name' => $name]);
```

### SQL Injection

SQL Injection ist ein Angriffstyp, bei dem ein bösartiger Benutzer SQL-Code in Ihre Datenbank injizieren kann. Dies kann verwendet werden, um Informationen aus Ihrer Datenbank zu stehlen oder Aktionen auf Ihrer Datenbank auszuführen. Wiederum sollten Sie **niemals** Eingaben von Ihren Benutzern vertrauen! Nehmen Sie immer an, dass sie blutrünstig sind. Die Verwendung vorbereiteter Anweisungen in Ihren `PDO`-Objekten wird SQL-Injection verhindern.

```php
// Angenommen, Sie haben Flight::db() als Ihr PDO-Objekt registriert
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Wenn Sie die PdoWrapper-Klasse verwenden, kann dies leicht in einer Zeile erledigt werden
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Sie können dasselbe mit einem PDO-Objekt mit ?-Platzhaltern tun
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

#### Unsicheres Beispiel

Das Folgende ist der Grund, warum wir SQL-vorbereitete Anweisungen verwenden, um vor unschuldigen Beispielen wie dem unten zu schützen:

```php
// Endbenutzer füllt ein Webformular aus.
// Für den Wert des Formulars gibt der Hacker etwas wie dies ein:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// Nachdem die Abfrage aufgebaut ist, sieht sie so aus
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// Es sieht seltsam aus, aber es ist eine gültige Abfrage, die funktioniert. Tatsächlich
// ist es ein sehr häufiger SQL-Injection-Angriff, der alle Benutzer zurückgibt.

var_dump($users); // dies wird alle Benutzer in der Datenbank ausgeben, nicht nur den einen einzelnen Benutzernamen
```

### CORS

Cross-Origin Resource Sharing (CORS) ist ein Mechanismus, der es vielen Ressourcen (z. B. Schriftarten, JavaScript usw.) auf einer Webseite ermöglicht, von einer anderen Domain außerhalb der Domain angefordert zu werden, von der die Ressource stammt. Flight hat keine integrierte Funktionalität, aber dies kann leicht mit einem Hook gehandhabt werden, der vor dem Aufruf der `Flight::start()`-Methode ausgeführt wird.

```php
// app/utils/CorsUtil.php

namespace app\utils;

class CorsUtil
{
	public function set(array $params): void
	{
		$request = Flight::request();
		$response = Flight::response();
		if ($request->getVar('HTTP_ORIGIN') !== '') {
			$this->allowOrigins();
			$response->header('Access-Control-Allow-Credentials', 'true');
			$response->header('Access-Control-Max-Age', '86400');
		}

		if ($request->method === 'OPTIONS') {
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_METHOD') !== '') {
				$response->header(
					'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD'
				);
			}
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS') !== '') {
				$response->header(
					"Access-Control-Allow-Headers",
					$request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS')
				);
			}

			$response->status(200);
			$response->send();
			exit;
		}
	}

	private function allowOrigins(): void
	{
		// Passen Sie Ihre erlaubten Hosts hier an.
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		$request = Flight::request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $allowed, true) === true) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin", $request->getVar('HTTP_ORIGIN'));
		}
	}
}

// index.php oder wo immer Sie Ihre Routen haben
$CorsUtil = new CorsUtil();

// Dies muss vor dem Ausführen von start ausgeführt werden.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

### Fehlerbehandlung
Verbergen Sie sensible Fehlerdetails in der Produktion, um das Austreten von Informationen an Angreifer zu vermeiden. In der Produktion protokollieren Sie Fehler anstatt sie anzuzeigen, mit `display_errors` auf `0` gesetzt.

```php
// In Ihrer bootstrap.php oder index.php

// Fügen Sie dies zu Ihrer app/config/config.php hinzu
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Deaktiviere Fehlanzeige
    ini_set('log_errors', 1);     // Protokolliere Fehler stattdessen
    ini_set('error_log', '/path/to/error.log');
}

// In Ihren Routen oder Controllern
// Verwenden Sie Flight::halt() für kontrollierte Fehlerantworten
Flight::halt(403, 'Zugriff verweigert');
```

### Eingabe-Sanitization
Vertrauen Sie niemals Benutzereingaben. Sanitieren Sie sie mit [filter_var](https://www.php.net/manual/en/function.filter-var.php), bevor Sie sie verarbeiten, um zu verhindern, dass bösartige Daten eindringen.

```php

// Nehmen wir an, eine $_POST-Anfrage mit $_POST['input'] und $_POST['email']

// Sanitisiere eine String-Eingabe
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Sanitisiere eine E-Mail
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### Passwort-Hashing
Speichern Sie Passwörter sicher und verifizieren Sie sie sicher mit PHPs integrierten Funktionen wie [password_hash](https://www.php.net/manual/en/function.password-hash.php) und [password_verify](https://www.php.net/manual/en/function.password-verify.php). Passwörter sollten niemals im Klartext gespeichert werden, noch sollten sie mit reversiblen Methoden verschlüsselt werden. Hashing stellt sicher, dass selbst wenn Ihre Datenbank kompromittiert wird, die tatsächlichen Passwörter geschützt bleiben.

```php
$password = Flight::request()->data->password;
// Hash ein Passwort beim Speichern (z. B. während der Registrierung)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Verifiziere ein Passwort (z. B. während des Logins)
if (password_verify($password, $stored_hash)) {
    // Passwort stimmt überein
}
```

### Ratenbegrenzung
Schützen Sie vor Brute-Force-Angriffen oder Denial-of-Service-Angriffen, indem Sie Anfrageraten mit einem Cache begrenzen.

```php
// Angenommen, Sie haben flightphp/cache installiert und registriert
// Verwendung von flightphp/cache in einem Filter
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Zu viele Anfragen');
    }
    
    $cache->set($key, $attempts + 1, 60); // Zurücksetzen nach 60 Sekunden
});
```

## Siehe auch
- [Sessions](/awesome-plugins/session) - Wie man Benutzersitzungen sicher verwaltet.
- [Templates](/learn/templates) - Verwendung von Templates, um Ausgabe automatisch zu escapen und XSS zu verhindern.
- [PDO Wrapper](/learn/pdo-wrapper) - Vereinfachte Datenbankinteraktionen mit vorbereiteten Anweisungen.
- [Middleware](/learn/middleware) - Wie man Middleware verwendet, um den Prozess des Hinzufügens von Sicherheits-Headern zu vereinfachen.
- [Responses](/learn/responses) - Wie man HTTP-Antworten mit sicheren Headern anpasst.
- [Requests](/learn/requests) - Wie man Benutzereingaben handhabt und sanitisiert.
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - PHP-Funktion für Eingabe-Sanitization.
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - PHP-Funktion für sicheres Passwort-Hashing.
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - PHP-Funktion zum Verifizieren gehasheter Passwörter.

## Fehlerbehebung
- Beziehen Sie sich auf den Abschnitt "Siehe auch" oben für Fehlerbehebungsinformationen im Zusammenhang mit Problemen mit Komponenten des Flight Frameworks.

## Änderungsprotokoll
- v3.1.0 - Hinzugefügte Abschnitte zu CORS, Fehlerbehandlung, Eingabe-Sanitization, Passwort-Hashing und Ratenbegrenzung.
- v2.0 - Hinzugefügtes Escaping für Standard-Views, um XSS zu verhindern.