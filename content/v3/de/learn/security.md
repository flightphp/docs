# Sicherheit

## Übersicht

Sicherheit ist ein großes Thema bei Webanwendungen. Sie möchten sicherstellen, dass Ihre Anwendung sicher ist und die Daten Ihrer Benutzer geschützt sind. Flight bietet eine Reihe von Funktionen, die Ihnen helfen, Ihre Webanwendungen zu sichern.

Das offizielle [Skeleton](https://github.com/flightphp/skeleton) enthält außerdem eine dedizierte **`SECURITY.md`** sowie eine Middleware für Sicherheits-Header, sodass [KI-Codierungswerkzeuge](/learn/ai) (und Menschen) einen bewussten Ort für Geheimnisse, Header und XSS-/SQL-Regeln haben – getrennt vom allgemeinen Codierungsstil in `AGENTS.md`.

## Grundlegendes Verständnis

Es gibt eine Reihe häufiger Sicherheitsbedrohungen, die Sie beim Entwickeln von Webanwendungen kennen sollten. Zu den häufigsten Bedrohungen gehören:
- Cross Site Request Forgery (CSRF)
- Cross Site Scripting (XSS)
- SQL-Injection
- Cross Origin Resource Sharing (CORS)

[Templates](/learn/templates) helfen bei XSS, indem sie Ausgaben standardmäßig escapen (Twig und Latte tun dies; nutzen Sie diesen Vorteil). [Sessions](/awesome-plugins/session) können bei CSRF helfen, indem ein CSRF-Token in der Benutzersitzung gespeichert wird, wie unten beschrieben. Die Verwendung von Prepared Statements mit PDO – oder Helfern auf [SimplePdo](/learn/simple-pdo) – hilft, SQL-Injection zu verhindern. CORS kann mit einem einfachen Hook vor dem Aufruf von `Flight::start()` behandelt werden.

Alle diese Methoden arbeiten zusammen, um Ihre Webanwendungen sicher zu halten. Es sollte immer im Hinterkopf behalten werden, Sicherheits-Best-Practices zu lernen und zu verstehen. Fragen Sie keinen KI-Assistenten, "CSP zu deaktivieren" oder Header zu schwächen, nur um eine Seite laden zu lassen, ohne den Kompromiss zu verstehen.

## Grundlegende Verwendung

### Header

HTTP-Header sind einer der einfachsten Wege, Ihre Webanwendungen zu sichern. Sie können Header verwenden, um Clickjacking, XSS und andere Angriffe zu verhindern. Es gibt mehrere Möglichkeiten, diese Header zu Ihrer Anwendung hinzuzufügen.

Zwei großartige Websites, um die Sicherheit Ihrer Header zu überprüfen, sind [securityheaders.com](https://securityheaders.com/) und [observatory.mozilla.org](https://observatory.mozilla.org/). Nachdem Sie den folgenden Code eingerichtet haben, können Sie einfach mit diesen beiden Websites überprüfen, ob Ihre Header funktionieren.

Das Skeleton enthält `App\Middleware\SecurityHeadersMiddleware` (CSP mit einem Nonce pro Anfrage, Frame-Optionen, HSTS und mehr). Bevorzugen Sie, das bewusst zu erweitern, anstatt Header zu deaktivieren.

#### Manuell hinzufügen

Sie können diese Header manuell mit der `header`-Methode auf dem `Flight\Response`-Objekt hinzufügen.
```php
// Setzen Sie den X-Frame-Options-Header, um Clickjacking zu verhindern
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Setzen Sie den Content-Security-Policy-Header, um XSS zu verhindern
// Hinweis: Dieser Header kann sehr komplex werden, daher sollten Sie
//  Beispiele im Internet für Ihre Anwendung konsultieren
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Setzen Sie den X-XSS-Protection-Header, um XSS zu verhindern
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Setzen Sie den X-Content-Type-Options-Header, um MIME-Sniffing zu verhindern
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Setzen Sie den Referrer-Policy-Header, um zu steuern, wie viele Referrer-Informationen gesendet werden
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
// Header in einem Filter hinzufügen
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

Sie können sie auch als Middleware-Klasse hinzufügen, die die größte Flexibilität bietet, auf welche Routen dies angewendet wird. Im Allgemeinen sollten diese Header auf alle HTML- und API-Antworten angewendet werden.

Skeleton-Stil Pfad und Namespace (**Ordnernamen müssen zu `App\Middleware` passen**):

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
		// Bevorzugen Sie ein CSP-Nonce aus dem Bootstrap, wenn Sie Inline-Skripte haben (Skeleton setzt csp_nonce)
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

// app/config/routes.php – leere String-Gruppe = globale Middleware für alle Routen
use App\Middleware\SecurityHeadersMiddleware;
use flight\net\Router;

$router->group('', function (Router $router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// weitere Routen
}, [SecurityHeadersMiddleware::class]);
```

Ältere Projekte verwenden möglicherweise weiterhin `app/middlewares` und `app\middlewares`; das funktioniert, wenn die Ordner übereinstimmen. Neue Skeleton-Apps verwenden **`app/Middleware/`** und **`App\Middleware`**. Siehe [Autoloading](/learn/autoloading).

### Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF) ist eine Angriffsart, bei der eine bösartige Website den Browser eines Benutzers dazu bringen kann, eine Anfrage an Ihre Website zu senden. Dies kann verwendet werden, um Aktionen auf Ihrer Website ohne Wissen des Benutzers auszuführen. Flight bietet keinen eingebauten CSRF-Schutzmechanismus, aber Sie können leicht einen eigenen mit Middleware implementieren.

#### Einrichtung

Zuerst müssen Sie ein CSRF-Token generieren und in der Benutzersitzung speichern. Sie können dieses Token dann in Ihren Formularen verwenden und es überprüfen, wenn das Formular abgesendet wird. Wir verwenden das [flightphp/session](/awesome-plugins/session) Plugin zur Verwaltung von Sitzungen.

```php
// Generieren Sie ein CSRF-Token und speichern Sie es in der Benutzersitzung
// (angenommen, Sie haben ein Sitzungsobjekt erstellt und an Flight angehängt)
// siehe die Sitzungsdokumentation für weitere Informationen
Flight::register('session', flight\Session::class);

// Sie müssen nur ein einziges Token pro Sitzung generieren (damit es 
// über mehrere Tabs und Anfragen für denselben Benutzer funktioniert)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### Mit der Standard-PHP-Flight-Vorlage

```html
<!-- Verwenden Sie das CSRF-Token in Ihrem Formular -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- weitere Formularfelder -->
</form>
```

##### Mit Twig (Skeleton-Standard)

Registrieren Sie eine Twig-Funktion oder übergeben Sie das Token an jede Formularansicht. Minimales Beispiel mit einem Global und einem Formularfeld:

```php
// Bei der Konfiguration von Twig (z. B. services.php)
$twig->addGlobal('csrf_token', $app->session()->get('csrf_token'));
```

```html
{# app/views/form.twig #}
<form method="post">
	<input type="hidden" name="csrf_token" value="{{ csrf_token }}">
	{# weitere Felder #}
</form>
```

##### Mit Latte

Sie können auch eine benutzerdefinierte Funktion festlegen, um das CSRF-Token in Ihren Latte-Templates auszugeben.

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// weitere Konfigurationen...

	// Eine benutzerdefinierte Funktion festlegen, um das CSRF-Token auszugeben
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

Und jetzt können Sie in Ihren Latte-Templates die Funktion `csrf()` verwenden, um das CSRF-Token auszugeben.

```html
<form method="post">
	{csrf()}
	<!-- weitere Formularfelder -->
</form>
```

#### CSRF-Token überprüfen

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
				$this->app->halt(403, 'Invalid CSRF token');
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

##### Event-Filter

```php
// Diese Middleware prüft, ob die Anfrage eine POST-Anfrage ist, und wenn ja, ob das CSRF-Token gültig ist
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// das CSRF-Token aus den Formularwerten erfassen
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
			// oder für eine JSON-Antwort
			Flight::jsonHalt(['error' => 'Invalid CSRF token'], 403);
		}
	}
});
```

### Cross Site Scripting (XSS)

Cross Site Scripting (XSS) ist eine Angriffsart, bei der eine bösartige Formulareingabe Code in Ihre Website injizieren kann. Die meisten dieser Möglichkeiten stammen aus Formularwerten, die Ihre Endbenutzer ausfüllen. Sie sollten **niemals** der Ausgabe Ihrer Benutzer vertrauen! Gehen Sie immer davon aus, dass alle die besten Hacker der Welt sind. Sie können bösartiges JavaScript oder HTML in Ihre Seite injizieren. Dieser Code kann verwendet werden, um Informationen von Ihren Benutzern zu stehlen oder Aktionen auf Ihrer Website auszuführen. Mit der View-Klasse von Flight oder einer Template-Engine wie [Twig](/awesome-plugins/twig) oder [Latte](/awesome-plugins/latte) können Sie Ausgaben einfach escapen, um XSS-Angriffe zu verhindern.

```php
// Nehmen wir an, der Benutzer ist clever und versucht, dies als seinen Namen zu verwenden
$name = '<script>alert("XSS")</script>';

// Dies escapet die Ausgabe
Flight::view()->set('name', $name);
// Dies gibt aus: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Twig (Skeleton-Standard) und Latte escapen standardmäßig automatisch – bevorzugen Sie diese gegenüber rohem PHP-Echo
Flight::render('template', ['name' => $name]);
// Twig: {{ name }}  → escaped
// Vermeiden Sie |raw / unescapte Ausgabe, es sei denn, der Inhalt ist vollständig vertrauenswürdig
```

### SQL-Injection

SQL-Injection ist eine Angriffsart, bei der ein bösartiger Benutzer SQL-Code in Ihre Datenbank injizieren kann. Dies kann verwendet werden, um Informationen aus Ihrer Datenbank zu stehlen oder Aktionen in Ihrer Datenbank auszuführen. Auch hier sollten Sie **niemals** der Eingabe Ihrer Benutzer vertrauen! Gehen Sie immer davon aus, dass sie auf Ärger aus sind. Verwenden Sie Prepared Statements – [SimplePdo](/learn/simple-pdo) Helfer machen dies zum Standardpfad.

```php
// Angenommen, Sie haben Flight::db() als SimplePdo registriert (oder injizieren SimplePdo im Controller)
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// SimplePdo (bevorzugt) – Einzeiler mit gebundenen Parametern
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Gleiche Idee mit ?-Platzhaltern
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

In Skeleton-Controllern bevorzugen Sie die Konstruktor-Injektion von `SimplePdo` anstelle von `Flight::db()`, damit Tests und KI-generierter Code konsistent bleiben ([DIC](/learn/dependency-injection-container)).

#### Unsicheres Beispiel

Das Folgende zeigt, warum wir SQL-Prepared-Statements verwenden, um sich vor unschuldigen Beispielen wie diesem zu schützen:

```php
// Endbenutzer füllt ein Webformular aus.
// Als Wert des Formulars gibt der Hacker etwa Folgendes ein:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// Nachdem die Abfrage erstellt wurde, sieht sie so aus
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// Es sieht seltsam aus, ist aber eine gültige Abfrage, die funktioniert. Tatsächlich
// ist es ein sehr häufiger SQL-Injection-Angriff, der alle Benutzer zurückgibt.

var_dump($users); // dies gibt alle Benutzer in der Datenbank aus, nicht nur den einen Benutzernamen
```

### Geheimnisse und Konfiguration

- Legen Sie Geheimnisse in **`.env`** (oder der echten Umgebung) ab, nicht in eingecheckten `config.php`-Beispielen.
- Skeleton-Regel: wörtliche Standardwerte in `config.php`; env beim Bootstrap zusammenführen; **lesen Sie** `