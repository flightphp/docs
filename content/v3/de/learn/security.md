# Sicherheit

## Überblick

Sicherheit ist ein großes Thema, wenn es um Webanwendungen geht. Sie möchten sicherstellen, dass Ihre Anwendung sicher ist und die Daten Ihrer Benutzer geschützt sind. Flight bietet eine Reihe von Funktionen, die Ihnen helfen, Ihre Webanwendungen abzusichern.

Das offizielle [Skelett](https://github.com/flightphp/skeleton) enthält ebenfalls eine dedizierte **`SECURITY.md`**-Datei und Middleware für Sicherheits-Header, damit [KI-Codierungstools](/learn/ai) (und Menschen) einen bewussten Ort für Geheimnisse, Header und XSS-/SQL-Regeln haben – getrennt vom allgemeinen Codierungsstil in `AGENTS.md`.

## Verständnis

Es gibt eine Reihe häufiger Sicherheitsbedrohungen, die Sie beim Erstellen von Webanwendungen beachten sollten. Zu den häufigsten Bedrohungen gehören:
- Cross-Site-Request-Forgery (CSRF)
- Cross-Site-Scripting (XSS)
- SQL-Injection
- Cross-Origin-Resource-Sharing (CORS)

[Templates](/docs/templates) helfen bei XSS, indem sie die Ausgabe standardmäßig escapen (Twig und Latte tun dies; nutzen Sie diesen Vorteil). [Sessions](/awesome-plugins/session) können bei CSRF helfen, indem sie ein CSRF-Token in der Benutzersitzung speichern, wie unten beschrieben. Die Verwendung vorbereiteter Anweisungen mit PDO – oder Helfern auf [SimplePdo](/docs/simple-pdo) – hilft, SQL-Injection zu verhindern. CORS kann mit einem einfachen Hook vor dem Aufruf von `Flight::start()` behandelt werden.

Alle diese Methoden arbeiten zusammen, um Ihre Webanwendungen sicher zu halten. Es sollte immer im Vordergrund stehen, Sicherheits-Praktiken zu lernen und zu verstehen. Bitten Sie einen KI-Assistenten nicht, "CSP zu deaktivieren" oder Header zu schwächen, nur um eine Seite zum Laden zu bringen, ohne den Kompromiss zu verstehen.

## Grundlegende Verwendung

### Header

HTTP-Header sind eine der einfachsten Möglichkeiten, Ihre Webanwendungen abzusichern. Sie können Header verwenden, um Clickjacking, XSS und andere Angriffe zu verhindern. Es gibt mehrere Möglichkeiten, diese Header zu Ihrer Anwendung hinzuzufügen.

Zwei großartige Websites, um die Sicherheit Ihrer Header zu überprüfen, sind [securityheaders.com](https://securityheaders.com/) und [observatory.mozilla.org](https://observatory.mozilla.org/). Nachdem Sie den untenstehenden Code eingerichtet haben, können Sie mit diesen beiden Websites einfach überprüfen, ob Ihre Header funktionieren.

Das Skelett enthält `App\Middleware\SecurityHeadersMiddleware` (CSP mit einem Nonce pro Anfrage, Frame-Optionen, HSTS und mehr). Bevorzugen Sie das Bewusste Erweitern davon, anstatt Header zu deaktivieren.

#### Manuelles Hinzufügen

Sie können diese Header manuell mit der `header`-Methode des `Flight\Response`-Objekts hinzufügen.
```php
// Setzen Sie den X-Frame-Options-Header, um Clickjacking zu verhindern
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Setzen Sie den Content-Security-Policy-Header, um XSS zu verhindern
// Hinweis: Dieser Header kann sehr komplex werden, schauen Sie sich daher
// Beispiele im Internet für Ihre Anwendung an
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Setzen Sie den X-XSS-Protection-Header, um XSS zu verhindern
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Setzen Sie den X-Content-Type-Options-Header, um MIME-Sniffing zu verhindern
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Setzen Sie den Referrer-Policy-Header, um die Menge der übermittelten Referrer-Informationen zu steuern
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Setzen Sie den Strict-Transport-Security-Header, um HTTPS zu erzwingen
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Setzen Sie den Permissions-Policy-Header, um zu steuern, welche Funktionen und APIs verwendet werden können
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Diese können am Anfang Ihrer `routes.php`- oder `index.php`-Dateien hinzugefügt werden.

#### Als Filter hinzufügen

Sie können sie auch in einem Filter/Hook wie folgt hinzufügen:

```php
// Fügen Sie die Header in einem Filter hinzu
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

Sie können sie auch als Middleware-Klasse hinzufügen, die die größte Flexibilität bietet, auf welche Routen diese angewendet werden. Im Allgemeinen sollten diese Header auf alle HTML- und API-Antworten angewendet werden.

Skelett-typischer Pfad und Namespace (**Ordner-Schreibweise passt zu `App\Middleware`**):

```php
// app/Middleware/SecurityHeadersMiddleware.php

namespace App\Middleware;

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
		// Bevorzugen Sie ein CSP-Nonce aus dem Bootstrap, wenn Sie Inline-Skripte haben (das Skelett setzt csp_nonce)
		$nonce = $this->app->get('csp_nonce');
		$csp = $nonce
			? "default-src 'self'; script-src 'self' 'nonce-{$nonce}'; style-src 'self' 'nonce-{$nonce}'"
			: "default-src 'self'";

		$response->header('X-Frame-Options', 'SAMEORIGIN');
		$response->header('Content-Security-Policy', $csp);
		$response->header('X-XSS-Protection', '1; mode=block');
		$response->header('X-Content-Type-Options', 'nosniff');
		$response->header('Referrer-Policy', 'no-referrer-when-downgrade');
		$response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		$response->header('Permissions-Policy', 'geolocation=()');
	}
}

// app/config/routes.php — leere Zeichenkette Gruppe = globale Middleware für alle Routen
use App\Middleware\SecurityHeadersMiddleware;
use flight\net\Router;

$router->group('', function (Router $router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// weitere Routen
}, [SecurityHeadersMiddleware::class]);
```

Ältere Projekte verwenden möglicherweise weiterhin `app/middlewares` und `app\middlewares`; das funktioniert, wenn die Ordner übereinstimmen. Neue Skelett-Apps verwenden **`app/Middleware/`** und **`App\Middleware`**. Siehe [Autoloading](/docs/autoloading).

### Cross-Site-Request-Forgery (CSRF)

Cross-Site-Request-Forgery (CSRF) ist eine Art von Angriff, bei dem eine bösartige Website den Browser eines Benutzers dazu bringen kann, eine Anfrage an Ihre Website zu senden. Dies kann verwendet werden, um Aktionen auf Ihrer Website ohne das Wissen des Benutzers auszuführen. Flight bietet keinen eingebauten CSRF-Schutzmechanismus, aber Sie können problemlos Ihren eigenen mit Middleware implementieren.

#### Einrichtung

Zuerst müssen Sie ein CSRF-Token generieren und in der Benutzersitzung speichern. Sie können dieses Token dann in Ihren Formularen verwenden und beim Absenden des Formulars überprüfen. Wir verwenden das [flightphp/session](/awesome-plugins/session)-Plugin zur Verwaltung der Sitzungen.

```php
// Generieren Sie ein CSRF-Token und speichern Sie es in der Benutzersitzung
// (vorausgesetzt, Sie haben ein Sitzungsobjekt erstellt und an Flight angehängt)
// Weitere Informationen finden Sie in der Sitzungsdokumentation
Flight::register('session', flight\Session::class);

// Sie müssen nur ein Token pro Sitzung generieren (damit es über mehrere Tabs 
// und Anfragen des gleichen Benutzers funktioniert)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### Mit Standard-PHP-Flight-Template

```html
<!-- Verwenden Sie das CSRF-Token in Ihrem Formular -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- Weitere Formularfelder -->
</form>
```

##### Mit Twig (Skelett-Standard)

Registrieren Sie eine Twig-Funktion oder übergeben Sie das Token an jede Formularansicht. Minimales Beispiel mit einer globalen Variable und einem Formularfeld:

```php
// Beim Konfigurieren von Twig (z.B. in services.php)
$twig->addGlobal('csrf_token', $app->session()->get('csrf_token'));
```

```html
{# app/views/form.twig #}
<form method="post">
	<input type="hidden" name="csrf_token" value="{{ csrf_token }}">
	{# Weitere Felder #}
</form>
```

##### Mit Latte

Sie können auch eine benutzerdefinierte Funktion einrichten, die das CSRF-Token in Ihren Latte-Templates ausgibt.

```php
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// andere Konfigurationen...

	// Setzen Sie eine benutzerdefinierte Funktion, um das CSRF-Token auszugeben
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
	<!-- Weitere Formularfelder -->
</form>
```

#### CSRF-Token prüfen

Sie können das CSRF-Token mit mehreren Methoden überprüfen.

##### Middleware

```php
// app/Middleware/CsrfMiddleware.php

namespace App\Middleware;

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

// routes.php
use App\Middleware\CsrfMiddleware;

$router->group('', function ($router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// weitere Routen
}, [CsrfMiddleware::class]);
```

##### Ereignisfilter

```php
// Diese Middleware prüft, ob die Anfrage eine POST-Anfrage ist, und wenn ja, prüft sie, ob das CSRF-Token gültig ist
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// Erfassen Sie das CSRF-Token aus den Formularwerten
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Ungültiges CSRF-Token');
			// oder für eine JSON-Antwort
			Flight::jsonHalt(['error' => 'Ungültiges CSRF-Token'], 403);
		}
	}
});
```

### Cross-Site-Scripting (XSS)

Cross-Site-Scripting (XSS) ist eine Art von Angriff, bei dem eine bösartige Formulareingabe Code in Ihre Website einschleusen kann. Die meisten dieser Möglichkeiten stammen von Formularwerten, die Ihre Endbenutzer ausfüllen. Sie sollten **nie** der Ausgabe Ihrer Benutzer vertrauen! Gehen Sie immer davon aus, dass alle die besten Hacker der Welt sind. Sie können bösartiges JavaScript oder HTML in Ihre Seite einfügen. Dieser Code kann verwendet werden, um Informationen von Ihren Benutzern zu stehlen oder Aktionen auf Ihrer Website auszuführen. Mit der View-Klasse von Flight oder einer Template-Engine wie [Twig](/awesome-plugins/twig) oder [Latte](/awesome-plugins/latte), können Sie die Ausgabe einfach escapen, um XSS-Angriffe zu verhindern.

```php
// Nehmen wir an, der Benutzer ist schlau und versucht das als Namen zu verwenden
$name = '<script>alert("XSS")</script>';

// Dies escaped die Ausgabe
Flight::view()->set('name', $name);
// Dies gibt aus: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Twig (Skelett-Standard) und Latte escapen standardmäßig automatisch – bevorzugen Sie diese gegenüber raw PHP echo
Flight::render('template', ['name' => $name]);
// Twig: {{ name }}  → escaped
// Vermeiden Sie |raw / unescaped Ausgabe, es sei denn, der Inhalt ist vollständig vertrauenswürdig
```

### SQL-Injection

SQL-Injection ist eine Art von Angriff, bei der ein bösartiger Benutzer SQL-Code in Ihre Datenbank einschleusen kann. Dies kann dazu verwendet werden, Informationen aus Ihrer Datenbank zu stehlen oder Aktionen an Ihrer Datenbank auszuführen. Auch hier sollten Sie **nie ** Eingaben von Benutzern vertrauen! Gehen Sie immer davon aus, dass sie auf Blut aus sind. Verwenden Sie vorbereitete Anweisungen – die [SimpleP](/docs/simple-pdo)-Helfer machen diesen Weg zum Standard.

```php
// Angenommen, Sie haben Flight::db() als SimplePdo registriert (oder injizieren SimplePdo in den Controller)
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// SimplePdo (bevorzugt) — Einzeiler mit gebundenen Parametern
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Gleiche Idee mit ? Platzhaltern
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

In Skelett-typischen Controllern bevorzugen Sie die Konstruktor-Injektion von `SimplePdo` gegenüber `Flight::db()`, damit Tests und KI-generierter Code konsistent bleiben ([DIC](/docs/dependency-injection-container)).

#### Unsicheres Beispiel

Das Folgende zeigt, warum wir SQL- vorbereitete Anweisungen verwenden, um vor harmlosen Beispielen wie diesem zu schützen:

```php
// Endbenutzer füllt ein Webformular aus.
// Für den Wert des Formulars gibt der Hacker so etwas ein:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// Nachdem die Abfrage erstellt wurde, sieht sie so aus
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// Es sieht seltsam aus, aber es ist eine gültige Abfrage, die funktionieren wird. Tatsächlich
// ist es eine sehr häufige SQL-Injection-Angriffstechnik, die alle Benutzer zurückgibt.

var_dump($users); // Dies gibt alle Benutzer in der Datenbank aus, nicht nur den einen Benutzernamen
```

### Secrets und Konfiguration

- Legen Sie Secrets in **`.env`** (oder in den echten Umgebungsvariablen) ab, nicht in kompaktierten `config.php`-Beispielen.
- Skelett-Regel: wörtliche Standardwerte in `config.php`; Umgebungsvariablen beim Bootstrap zusammenführen; **lesen Sie** `$_ENV` nicht in Controllern – injizieren Sie Konfiguration stattdessen. Siehe [Konfiguration](/docs/configuration).
- Committen Sie niemals API-Schlüssel, Datenbankpasswörter oder Sitzungsverschlüsselungsschlüssel. Verweisen Sie KI-Tools auf **`SECURITY.md`**, damit sie keine unsicheren Schnelllösungen erfinden.

### JSONP-Callback-Validierung

Wenn Sie die `Flight::jsonp()`-Methode verwenden, beachten Sie, dass Flight den JSONP-Callback-Parameternamen mit einem strengen Allowlist-Regex validiert (`/^[A-Za-z_$][\w$.]{0,127}$/`). Jeder Callback-Name, der diesem Muster nicht entspricht, führt dazu, dass Flight eine Ausnahme wirft, was die Einschleusung von beliebigem JavaScript über einen bösartigen Callback-Wert verhindert.

Diese Validierung ist eingebaut und erfordert keine zusätzliche Konfiguration, ist aber beim Debuggen unerwarteter Fehler von JSONP-Endpunkten relevant.

### CORS

Cross-Origin Resource Sharing (CORS) ist ein Mechanismus, der es vielen Ressourcen (z.B. Schriften, JavaScript usw.) auf einer Webseite ermöglicht, von einer anderen Domain angefordert zu werden, die nicht die Ursprungsdomain der Ressource ist. Flight hat keine eingebaute Funktionalität, aber dies kann leicht mit einem Hook vor dem Aufruf der `Flight::start()`-Methode behandelt werden.

```php
// app/Utils/CorsUtil.php  (Skelett: PascalCase Utils-Ordner → App\Utils)

namespace App\Utils;

use flight\Engine;

class CorsUtil
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function set(array $params = []): void
	{
		$request = $this->app->request();
		$response = $this->app->response();
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
		// Passen Sie hier Ihre erlaubten Hosts an.
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		$request = $this->app->request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $allowed, true) === true) {
			$response = $this->app->response();
			$response->header("Access-Control-Allow-Origin", $request->getVar('HTTP_ORIGIN'));
		}
	}
}

// Bootstrap / Routen — Vor dem Start ausführen
$app = Flight::app();
$cors = new \App\Utils\CorsUtil($app);
$app->before('start', [ $cors, 'set' ]);
```

### Härtung der Flight-Konfiguration

Flight bietet mehrere Engine-Einstellungen, die direkte Sicherheitsauswirkungen haben. Die richtige Einstellung ist eine der einfachsten Möglichkeiten, Ihre Anwendung zu härten.

#### `flight.allow_method_override`

Standardmäßig erlaubt Flight Clients, die HTTP-Methode einer Anfrage mit dem `X-HTTP-Method-Override`-Header oder einem `_method`-Feld im POST-Body zu überschreiben. Dies ist praktisch für HTML-Formulare, die nur `GET`/`POST` senden können, kann aber gefährlich sein, wenn Sie es nicht erwarten – ein Angreifer könnte `DELETE`- oder `PUT`-Anfragen über ein normales Formular fälschen.

Wenn Ihre Anwendung nicht auf dieses Verhalten angewiesen ist (z.B. eine API, die von modernen Clients oder JavaScript-Frontends konsumiert wird, die jedes HTTP-Verb senden können), sollten Sie es deaktivieren:

```php
// In Ihrer index.php oder Bootstrap-Datei, vor Flight::start()
Flight::set('flight.allow_method_override', false);
```

Der Standardwert ist `true` für die Abwärtskompatibilität, aber **die Einstellung auf `false` wird für jede Anwendung, die die Override-Funktion nicht explizit benötigt, dringend empfohlen**.

#### `flight.debug`

Flight hat eine Einstellung `flight.debug`, die steuert, ob detaillierte Fehlerinformationen (Ausnahmemeldung, Code und vollständiger Stack-Trace) im Browser gerendert werden, wenn eine unbehandelte Ausnahme auftritt. Der Standardwert ist `false`, d.h. nur eine generische `500 Internal Server Error`-Meldung wird angezeigt – keine internen Details werden an den Client weitergegeben.

Aktivieren Sie dies nie auf einem Produktionsserver. Verwenden Sie es nur lokal oder in einer Staging-Umgebung:

```php
// Nur für lokale Entwicklung sicher – NIEMALS in der Produktion
Flight::set('flight.debug', true);
```

Wenn `flight.debug` `false` ist (der Standard), können Sie Fehler weiterhin erfassen, indem Sie `flight.log_errors` aktivieren:

```php
// Fehler serverseitig protokollieren, ohne sie dem Client zu zeigen
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
```

#### `flight.views.restrict_to_path`

Die eingebaute `View`-Klasse von Flight wird gerne ein Template aus einem absoluten Pfad oder aus einem relativen Namen, der außerhalb von `flight.views.path` (z.B. mit `../`) liegt, einbinden. Das ist beabsichtigt für Apps, die absichtlich Templates über Ordner hinweg teilen, stellt aber auch ein Pfad-Traversal-Risiko dar, wenn ein Template-Name aus unzuverlässiger Eingabe stammt.

`flight.views.restrict_to_path` ist **standardmäßig deaktiviert**, damit bestehende Apps weiter funktionieren. Aktivieren Sie es, es sei denn, Sie haben einen dokumentierten Grund, dies nicht zu tun:

```php
// In Ihrer index.php oder Bootstrap-Datei, vor Flight::start()
Flight::set('flight.views.restrict_to_path', true);
```

Die Engine wendet diese Einstellung auf `View::$restrictToPath` an, wenn die View erstellt wird (gleiches Muster wie `flight.views.path` und `flight.views.extension`).Mit aktivierter Einstellung:

- `render()` und `fetch()` enthalten nur Dateien, deren realer Pfad innerhalb des konfigurierten View-Verzeichnisses liegt (Symlinks, die nach außen zeigen, werden ebenfalls abgelehnt).
- `exists()` gibt `false` für diese Pfade zurück, anstatt eine Ausnahme zu werfen.
- `getTemplate()` selbst bleibt unverändert – es gibt weiterhin Pfade so wie immer.
- Eine blockierte Datei wirft `Template file is outside the views path.` Eine fehlende Datei wirft weiterhin die bestehende Meldung `Template file not found: ...`.

Wenn Sie Twig oder Latte mit eigenen Dateisystem-Loadern, die auf Ihr View-Verzeichnis zeigen, verwenden, haben diese Engines bereits Templates, die auf diese Wurzel beschränkt sind. Aktivieren Sie dies trotzdem für die native `View` von Flight, damit jeder Code, der `Flight::view()->render()` / `fetch()` aufruft, den gleichen Schutz erhält. Das offizielle [Skelett](https://github.com/flightphp/skeleton) aktiviert es im Bootstrap.

#### Empfohlene Produktionskonfiguration

```php
// index.php oder aus der App-Konfiguration / Bootstrap angewendet
Flight::set('flight.allow_method_override', false);
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
Flight::set('flight.views.restrict_to_path', true);
```

### Fehlerbehandlung
Verbergen Sie sensible Fehlerdetails in der Produktion, um das Auslaufen von Informationen an Angreifer zu vermeiden. In der Produktion sollen Fehler protokolliert werden, anstatt sie mit `display_errors` auf `0` anzuzeigen.

```php
// In Ihrer bootstrap.php oder index.php

// fügen Sie dies zu Ihrer app/config/config.php hinzu
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Fehleranzeige deaktivieren
    ini_set('log_errors', 1);     // Fehler stattdessen protokollieren
    ini_set('error_log', '/path/to/error.log');
}

// In Ihren Routen oder Controllern
// Verwenden Sie Flight::halt() für kontrollierte Fehlerantworten
Flight::halt(403, 'Zugriff verweigert');
```

### Eingabebereinigung
Vertrauen Sie Benutzereingaben niemals. Bereinigen Sie sie mit [filter_var](https://www.php.net/manual/en/function.filter-var.php), bevor Sie sie verarbeiten, um bösartige Daten fernzuhalten. Bevorzugen Sie das Lesen von Eingaben über `$app->request()` (oder `Flight::request()`), anstatt rohe `$_GET` / `$_POST` im Anwendungscode zu verwenden.

```php

// Nehmen wir an, eine $_POST-Anfrage mit $_POST['input'] und $_POST['email']

// Bereinigen Sie eine Zeichenfolgeneingabe
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Bereinigen Sie eine E-Mail
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### Passwort-Hashing
Speichern Sie Passwörter sicher und verifizieren Sie sie sicher mit den in PHP integrierten Funktionen wie [password_hash](https://www.php.net/manual/en/function.password-hash.php) und [password_verify](https://www.php.net/manual/en/function.password-verify.php). Passwörter sollten niemals im Klartext gespeichert werden, noch sollten sie mit reversiblen Methoden verschlüsselt werden. Das Hashing stellt sicher, dass selbst wenn Ihre Datenbank kompromittiert wird, die tatsächlichen Passwörter geschützt bleiben.

```php
$password = Flight::request()->data->password;
// Ein Passwort beim Speichern hashen (z.B. bei der Registrierung)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Ein Passwort verifizieren (z.B. beim Login)
if (password_verify($password, $stored_hash)) {
    // Passwort stimmt überein
}
```

### Ratenbegrenzung
Schützen Sie sich vor Brute-Force-Angriffen oder Denial-of-Service-Angriffen, indem Sie die Anforderungsraten mit einem Cache begrenzen.

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
    
    $cache->set($key, $attempts + 1, 60); // Nach 60 Sekunden zurücksetzen
});
```

## Siehe auch
- [Sessions](/awesome-plugins/session) – Verwaltung von Benutzersitzungen sicher.
- [Templates](/docs/templates) – Twig/Latte automatisches Escaping und XSS.
- [SimplePd](/docs/simple-pdo) – Datenbank-Helfer mit vorbereiteten Anweisungen.
- [PdoWrapper](/docs/pdo-wrapper) – Veraltet; verwenden Sie SimplePdo für neuen Code.
- [Middleware](/docs/middleware) – Verwendung von Middleware, um das Hinzufügen von Sicherheitsheaders zu vereinfachen.
- [Konfiguration](/docs/configuration) – `.env` gegenüber wörtlicher Konfiguration, Produktionsflags.
- [KI & Entwicklererfahrung](/docs/ai) – Sicherheitsrichtlinie in `SECURITY.md` für Agenten platzieren.
- [Antworten](/docs/responses) – Anpassung von HTTP-Antworten mit sicheren Headern.
- [Anfragen](/docs/requests) – Handhabung und Bereinigung von Benutzereingaben.
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) – PHP-Funktion zur Eingabebereinigung.
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) – PHP-Funktion für sicheres Passwort-Hashing.
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) – PHP-Funktion zur Überprüfung gehashter Passwörter.

## Fehlerbehebung
- Beachten Sie den Abschnitt "Siehe auch" oben, um Informationen zur Fehlerbehebung bei Problemen mit Komponenten des Flight-Frameworks zu erhalten.
- Falls CSP Ihre Skripte blockiert, fügen Sie ein Nonce hinzu (Skelett-Muster) oder erlauben Sie bestimmte Ursprünge explizit – setzen Sie `script-src *` nicht ohne einen Plan.

## Changelog
- Dokumentation – Skelett `App\Middleware`, Twig-CSRF/XSS-Hinweise, SimplePd, Secrets/`.env` und `SECURITY.md` für KI-freundliche Projekte.
- Dokumentation – `flight.views.restrict_to_path` unter Härtung der Flight-Konfiguration dokumentiert (opt-in Pfadbegrenzung für native Views).
- v3.18.1 – Abschnitt "Härtung der Flight-Konfiguration" hinzugefügt mit `flight.allow_method_override`, `flight.debug` und JSONP-Callback-Validierung.
- v3.1.0 – Abschnitte zu CORS, Fehlerbehandlung, Eingabebereinigung, Passwort-Hashing und Ratenbegrenzung hinzugefügt.
- v2.0 – Escaping für standard Views hinzugefügt, um XSS zu verhindern.