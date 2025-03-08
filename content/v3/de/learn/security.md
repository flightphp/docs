# Sicherheit

Sicherheit ist ein großes Thema, wenn es um Webanwendungen geht. Sie möchten sicherstellen, dass Ihre Anwendung sicher ist und dass die Daten Ihrer Benutzer 
geschützt sind. Flight bietet eine Reihe von Funktionen, um Ihnen zu helfen, Ihre Webanwendungen abzusichern.

## Header

HTTP-Header sind eine der einfachsten Möglichkeiten, um Ihre Webanwendungen abzusichern. Sie können Header verwenden, um Clickjacking, XSS und andere Angriffe zu verhindern. 
Es gibt mehrere Möglichkeiten, wie Sie diese Header zu Ihrer Anwendung hinzufügen können.

Zwei großartige Websites, um die Sicherheit Ihrer Header zu überprüfen, sind [securityheaders.com](https://securityheaders.com/) und 
[observatory.mozilla.org](https://observatory.mozilla.org/).

### Manuell Hinzufügen

Sie können diese Header manuell hinzufügen, indem Sie die Methode `header` auf dem Objekt `Flight\Response` verwenden.
```php
// Setzen Sie den X-Frame-Options-Header, um Clickjacking zu verhindern
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Setzen Sie den Content-Security-Policy-Header, um XSS zu verhindern
// Hinweis: Dieser Header kann sehr komplex werden, daher sollten Sie
// sich Beispiele im Internet für Ihre Anwendung ansehen
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

Diese können am Anfang Ihrer `bootstrap.php` oder `index.php`-Dateien hinzugefügt werden.

### Als Filter Hinzufügen

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

### Als Middleware Hinzufügen

Sie können sie auch als Middleware-Klasse hinzufügen. Dies ist eine gute Möglichkeit, Ihren Code sauber und organisiert zu halten.

```php
// app/middleware/SecurityHeadersMiddleware.php

namespace app\middleware;

class SecurityHeadersMiddleware
{
	public function before(array $params): void
	{
		Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
		Flight::response()->header("Content-Security-Policy", "default-src 'self'");
		Flight::response()->header('X-XSS-Protection', '1; mode=block');
		Flight::response()->header('X-Content-Type-Options', 'nosniff');
		Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
		Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		Flight::response()->header('Permissions-Policy', 'geolocation=()');
	}
}

// index.php oder wo auch immer Sie Ihre Routen haben
// FYI, diese leere Zeichenfolge agiert als globale Middleware für
// alle Routen. Natürlich könnten Sie das Gleiche tun und es nur
// zu bestimmten Routen hinzufügen.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// weitere Routen
}, [ new SecurityHeadersMiddleware() ]);
```


## Cross-Site-Request- forgery (CSRF)

Cross-Site-Request-Forgery (CSRF) ist eine Art von Angriff, bei dem eine böswillige Website den Browser eines Benutzers dazu bringen kann, eine Anfrage an Ihre Website zu senden. 
Dies kann verwendet werden, um Aktionen auf Ihrer Website ohne das Wissen des Benutzers durchzuführen. Flight bietet keinen integrierten 
CSRF-Schutzmechanismus, aber Sie können ganz einfach Ihr eigenes durch die Verwendung von Middleware implementieren.

### Einrichtung

Zuerst müssen Sie ein CSRF-Token generieren und es in der Sitzung des Benutzers speichern. Sie können dann dieses Token in Ihren Formularen verwenden und überprüfen, wenn 
das Formular abgesendet wird.

```php
// Generieren Sie ein CSRF-Token und speichern Sie es in der Sitzung des Benutzers
// (vorausgesetzt, Sie haben ein Sitzungsobjekt erstellt und es an Flight angehängt)
// siehe die Sitzungsdokumentation für weitere Informationen
Flight::register('session', \Ghostff\Session\Session::class);

// Sie müssen nur ein einzelnes Token pro Sitzung generieren (damit es funktioniert 
// über mehrere Tabs und Anfragen für denselben Benutzer)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- Verwenden Sie das CSRF-Token in Ihrem Formular -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- andere Formularfelder -->
</form>
```

#### Mit Latte

Sie können auch eine benutzerdefinierte Funktion festlegen, um das CSRF-Token in Ihren Latte-Vorlagen auszugeben.

```php
// Setzen Sie eine benutzerdefinierte Funktion, um das CSRF-Token auszugeben
// Hinweis: Die Ansicht wurde mit Latte als Ansicht-Engine konfiguriert
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

Und jetzt in Ihren Latte-Vorlagen können Sie die Funktion `csrf()` verwenden, um das CSRF-Token auszugeben.

```html
<form method="post">
	{csrf()}
	<!-- andere Formularfelder -->
</form>
```

Kurz und einfach, richtig?

### Überprüfen des CSRF-Tokens

Sie können das CSRF-Token mithilfe von Ereignisfiltern überprüfen:

```php
// Diese Middleware prüft, ob die Anfrage eine POST-Anfrage ist, und wenn ja, ob das CSRF-Token gültig ist
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

Oder Sie können eine Middleware-Klasse verwenden:

```php
// app/middleware/CsrfMiddleware.php

namespace app\middleware;

class CsrfMiddleware
{
	public function before(array $params): void
	{
		if(Flight::request()->method == 'POST') {
			$token = Flight::request()->data->csrf_token;
			if($token !== Flight::session()->get('csrf_token')) {
				Flight::halt(403, 'Ungültiges CSRF-Token');
			}
		}
	}
}

// index.php oder wo auch immer Sie Ihre Routen haben
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// weitere Routen
}, [ new CsrfMiddleware() ]);
```

## Cross-Site-Scripting (XSS)

Cross-Site-Scripting (XSS) ist eine Art von Angriff, bei dem eine böswillige Website Code in Ihre Website injizieren kann. Die meisten dieser Möglichkeiten 
stammen von Formularwerten, die Ihre Endbenutzer ausfüllen werden. Sie sollten **nie** den Ausgang Ihrer Benutzer vertrauen! Immer davon ausgehen, dass alle von ihnen die 
besten Hacker der Welt sind. Sie können böswilliges JavaScript oder HTML in Ihre Seite injizieren. Dieser Code kann verwendet werden, um Informationen von Ihren 
Benutzern zu stehlen oder Aktionen auf Ihrer Website auszuführen. Mit der Ansichtsklasse von Flight können Sie Ausgaben leicht escapen, um XSS-Angriffe zu verhindern.

```php
// Angenommen, der Benutzer ist clever und versucht, dies als seinen Namen zu verwenden
$name = '<script>alert("XSS")</script>';

// Dies wird die Ausgabe escapen
Flight::view()->set('name', $name);
// Dies wird ausgeben: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Wenn Sie etwas wie Latte verwenden, das als Ihre Ansichtsklasse registriert ist, wird dies auch automatisch escaped.
Flight::view()->render('template', ['name' => $name]);
```

## SQL-Injektion

SQL-Injektion ist eine Art von Angriff, bei dem ein böswilliger Benutzer SQL-Code in Ihre Datenbank injizieren kann. Dies kann verwendet werden, um Informationen 
aus Ihrer Datenbank zu stehlen oder Aktionen in Ihrer Datenbank auszuführen. Auch hier sollten Sie **nie** Eingaben von Ihren Benutzern vertrauen! Immer davon ausgehen, dass sie 
es auf Blut abgesehen haben. Sie können vorbereitete Anweisungen in Ihren `PDO`-Objekten verwenden, um SQL-Injektionen zu verhindern.

```php
// Vorausgesetzt, Sie haben Flight::db() als Ihr PDO-Objekt registriert
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Wenn Sie die PdoWrapper-Klasse verwenden, kann dies leicht in einer Zeile erledigt werden
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Sie können dasselbe mit einem PDO-Objekt mit ? Platzhaltern tun
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Versprechen Sie einfach, dass Sie niemals so etwas tun werden...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// denn was ist, wenn $username = "' OR 1=1; -- "; 
// Nachdem die Abfrage erstellt wurde, sieht sie so aus
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Es sieht seltsam aus, aber es ist eine gültige Abfrage, die funktioniert. Tatsächlich,
// es ist ein sehr häufiger SQL-Injektionsangriff, der alle Benutzer zurückgibt.
```

## CORS

Cross-Origin Resource Sharing (CORS) ist ein Mechanismus, der es vielen Ressourcen (z. B. Schriftarten, JavaScript usw.) auf einer Webseite ermöglicht, 
von einer anderen Domain außerhalb der Domain angefordert zu werden, von der die Ressource stammt. Flight hat keine integrierte Funktionalität, 
aber dies kann leicht mit einem Hook erledigt werden, der vor dem Aufruf der Methode `Flight::start()` ausgeführt wird.

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
		// Passen Sie hier Ihre erlaubten Hosts an.
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

// index.php oder wo auch immer Sie Ihre Routen haben
$CorsUtil = new CorsUtil();

// Dies muss vor dem start ausgeführt werden.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

## Fehlermanagement
Verbergen Sie sensible Fehlermeldungen in der Produktion, um das Leaken von Informationen an Angreifer zu vermeiden.

```php
// In Ihrer bootstrap.php oder index.php

// im flightphp/skeleton, befindet sich dies in app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Fehleranzeige deaktivieren
    ini_set('log_errors', 1);     // Fehler stattdessen protokollieren
    ini_set('error_log', '/path/to/error.log');
}

// In Ihren Routen oder Controllern
// Verwenden Sie Flight::halt() für kontrollierte Fehlermeldungen
Flight::halt(403, 'Zugriff verweigert');
```

## Eingabesäuberung
Vertrauen Sie niemals Benutzereingaben. Säubern Sie sie, bevor Sie sie verarbeiten, um zu verhindern, dass böswillige Daten eindringen.

```php

// Angenommen, eine $_POST-Anfrage mit $_POST['input'] und $_POST['email']

// Bereinigen einer String-Eingabe
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Bereinigen einer E-Mail
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

## Passwort Hashing
Speichern Sie Passwörter sicher und überprüfen Sie sie sicher mit den integrierten Funktionen von PHP.

```php
$password = Flight::request()->data->password;
// Hashen Sie ein Passwort bei der Speicherung (z. B. während der Registrierung)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Überprüfen Sie ein Passwort (z. B. während des Logins)
if (password_verify($password, $stored_hash)) {
    // Passwort stimmt überein
}
```

## Rate Limiting
Schützen Sie sich vor Brute-Force-Angriffen, indem Sie die Anforderungsraten mit einem Cache begrenzen.

```php
// Vorausgesetzt, Sie haben flightphp/cache installiert und registriert
// Verwendung von flightphp/cache in einer Middleware
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Zu viele Anfragen');
    }
    
    $cache->set($key, $attempts + 1, 60); // Reset nach 60 Sekunden
});
```

## Fazit

Sicherheit ist ein großes Thema, und es ist wichtig, sicherzustellen, dass Ihre Webanwendungen sicher sind. Flight bietet eine Reihe von Funktionen, um Ihnen zu helfen, 
Ihre Webanwendungen abzusichern, aber es ist wichtig, immer wachsam zu sein und sicherzustellen, dass Sie alles tun, um die Daten Ihrer Benutzer zu schützen. 
Gehen Sie immer vom Schlimmsten aus und vertrauen Sie niemals Eingaben von Ihren Benutzern. Escapen Sie immer Ausgaben und verwenden Sie vorbereitete Anweisungen, um SQL-Injektionen zu verhindern. 
Verwenden Sie immer Middleware, um Ihre Routen vor CSRF- und CORS-Angriffen zu schützen. Wenn Sie all diese Dinge tun, sind Sie auf dem besten Weg, sichere Webanwendungen zu erstellen.