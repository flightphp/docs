# Sicherheit

Sicherheit ist eine große Sache, wenn es um Webanwendungen geht. Sie möchten sicherstellen, dass Ihre Anwendung sicher ist und dass die Daten Ihrer Benutzer geschützt sind. Flight bietet eine Reihe von Funktionen, um Ihnen bei der Absicherung Ihrer Webanwendungen zu helfen.

## Header

HTTP-Header sind einer der einfachsten Wege, um Ihre Webanwendungen zu sichern. Sie können Header verwenden, um Clickjacking, XSS und andere Angriffe zu verhindern. Es gibt mehrere Möglichkeiten, wie Sie diese Header zu Ihrer Anwendung hinzufügen können.

Zwei großartige Websites, um die Sicherheit Ihrer Header zu überprüfen, sind [securityheaders.com](https://securityheaders.com/) und [observatory.mozilla.org](https://observatory.mozilla.org/).

### Manuell hinzufügen

Sie können diese Header manuell hinzufügen, indem Sie die `header`-Methode auf dem `Flight\Response`-Objekt verwenden.
```php
// Setzen des X-Frame-Options-Headers, um Clickjacking zu verhindern
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Setzen des Content-Security-Policy-Headers, um XSS zu verhindern
// Hinweis: Dieser Header kann sehr komplex werden, daher möchten Sie
// Beispiele im Internet für Ihre Anwendung konsultieren
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Setzen des X-XSS-Protection-Headers, um XSS zu verhindern
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Setzen des X-Content-Type-Options-Headers, um MIME-Schnüffeln zu verhindern
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Setzen des Referrer-Policy-Headers, um zu steuern, wie viele Referrer-Informationen gesendet werden
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Setzen des Strict-Transport-Security-Headers, um HTTPS zu erzwingen
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Setzen des Permissions-Policy-Headers, um zu steuern, welche Funktionen und APIs verwendet werden können
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Diese können am Anfang Ihrer `bootstrap.php` oder `index.php`-Dateien hinzugefügt werden.

### Als Filter hinzufügen

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

### Als Middleware hinzufügen

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

// index.php oder wo immer Sie Ihre Routen haben
// Übrigens fungiert diese leere Stringgruppe als globales Middleware für
// alle Routen. Natürlich könnten Sie dasselbe tun und es nur bestimmten Routen hinzufügen.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// weitere Routen
}, [ new SecurityHeadersMiddleware() ]);
```


## Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF) ist ein Angriffstyp, bei dem eine bösartige Website den Browser eines Benutzers dazu bringen kann, eine Anfrage an Ihre Website zu senden. Dies kann verwendet werden, um Aktionen auf Ihrer Website ohne das Wissen des Benutzers durchzuführen. Flight bietet keinen integrierten CSRF-Schutzmechanismus, aber Sie können ganz einfach Ihren eigenen implementieren, indem Sie Middleware verwenden.

### Einrichtung

Zunächst müssen Sie ein CSRF-Token generieren und es in der Sitzung des Benutzers speichern. Sie können dieses Token dann in Ihren Formularen verwenden und beim Absenden des Formulars überprüfen.

```php
// Generieren eines CSRF-Tokens und Speichern in der Benutzersitzung
// (vorausgesetzt, Sie haben ein Session-Objekt erstellt und es an Flight angehängt)
// Sie müssen nur ein einziges Token pro Sitzung generieren (damit es
// über mehrere Tabs und Anfragen hinweg für denselben Benutzer funktioniert)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- Verwendung des CSRF-Tokens in Ihrem Formular -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- andere Formularfelder -->
</form>
```

#### Verwendung von Latte

Sie können auch eine benutzerdefinierte Funktion festlegen, um das CSRF-Token in Ihren Latte-Templates auszugeben.

```php
// Festlegen einer benutzerdefinierten Funktion zum Ausgeben des CSRF-Tokens
// Hinweis: Die Ansicht wurde mit Latte als Ansichtsmaschine konfiguriert
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

Und jetzt können Sie in Ihren Latte-Templates die `csrf()`-Funktion verwenden, um das CSRF-Token auszugeben.

```html
<form method="post">
	{csrf()}
	<!-- andere Formularfelder -->
</form>
```

Kurz und bündig, nicht wahr?

### Überprüfen des CSRF-Tokens

Sie können das CSRF-Token mithilfe von Event-Filtern überprüfen:

```php
// Dieses Middleware überprüft, ob die Anfrage eine POST-Anfrage ist, und überprüft dann, ob das CSRF-Token gültig ist
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// Erfassen des CSRF-Tokens aus den Formularwerten
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Ungültiges CSRF-Token');
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

// index.php oder wo immer Sie Ihre Routen haben
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// weitere Routen
}, [ new CsrfMiddleware() ]);
```


## Cross Site Scripting (XSS)

Cross Site Scripting (XSS) ist ein Angriffstyp, bei dem eine bösartige Website Code in Ihre Website einschleusen kann. Die meisten dieser Möglichkeiten ergeben sich aus Formularwerten, die Ihre Endbenutzer ausfüllen werden. Sie sollten **niemals** auf die Ausgabe Ihrer Benutzer vertrauen! Gehen Sie immer davon aus, dass alle die besten Hacker der Welt sind. Sie können bösartiges JavaScript oder HTML in Ihre Seite einschleusen. Dieser Code kann verwendet werden, um Informationen von Ihren Benutzern zu stehlen oder Aktionen auf Ihrer Website durchzuführen. Mit der View-Klasse von Flight können Sie die Ausgabe einfach escapen, um XSS-Angriffe zu verhindern.

```php
// Angenommen, der Benutzer ist clever und versucht, dies als seinen Namen zu verwenden
$name = '<script>alert("XSS")</script>';

// Dies wird die Ausgabe escapen
Flight::view()->set('name', $name);
// Dies wird ausgegeben: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Wenn Sie etwas wie Latte als Ihre View-Klasse registriert haben, wird es dies auch automatisch escapen.
Flight::view()->render('template', ['name' => $name]);
```

## SQL-Injection

SQL-Injection ist ein Angriffstyp, bei dem ein bösartiger Benutzer SQL-Code in Ihre Datenbank einschleusen kann. Dies kann verwendet werden, um Informationen aus Ihrer Datenbank zu stehlen oder Aktionen auf Ihrer Datenbank durchzuführen. Auch hier sollten Sie **niemals** auf Eingaben Ihrer Benutzer vertrauen! Gehen Sie immer davon aus, dass sie auf Blut aus sind. Sie können vorbereitete Anweisungen in Ihren `PDO`-Objekten verwenden, um SQL-Injection zu verhindern.

```php
// Angenommen, dass Flight::db() als Ihr PDO-Objekt registriert ist
$anweisung = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$anweisung->execute([':username' => $username]);
$benutzer = $anweisung->fetchAll();

// Wenn Sie die PdoWrapper-Klasse verwenden, kann dies einfach in einer Zeile erledigt werden
$benutzer = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Sie können das Gleiche mit einem PDO-Objekt mit ?-Platzhaltern tun
$anweisung = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Versprechen Sie einfach, dass Sie niemals etwas wie dies tun werden...
$benutzer = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// denn was ist, wenn $benutzername = "' OR 1=1; -- ";
// Nachdem die Abfrage erstellt wurde, sieht sie so aus
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Es sieht seltsam aus, aber es ist eine gültige Abfrage, die funktionieren wird. Tatsächlich ist es ein sehr verbreiteter SQL-Injection-Angriff, der alle Benutzer zurückgibt.
```

## CORS

Cross-Origin Resource Sharing (CORS) ist ein Mechanismus, der es vielen Ressourcen (z. B. Schriften, JavaScript usw.) auf einer Webseite ermöglicht, von einer anderen Domain außerhalb der Domäne, von der die Ressource stammt, angefordert zu werden. Flight hat keine integrierte Funktionalität, aber dies kann einfach mit einem Hook behandelt werden, der vor dem Aufruf der `Flight::start()`-Methode ausgeführt wird.

```php
// app/utils/CorsUtil.php

namespace app\utils;

class CorsUtil
{
	public function set(array $params): void
	{
		$anfrage = Flight::request();
		$antwort = Flight::response();
		if ($anfrage->getVar('HTTP_ORIGIN') !== '') {
			$this->allowOrigins();
			$antwort->header('Access-Control-Allow-Credentials', 'true');
			$antwort->header('Access-Control-Max-Age', '86400');
		}

		if ($anfrage->method === 'OPTIONS') {
			if ($anfrage->getVar('HTTP_ACCESS_CONTROL_REQUEST_METHOD') !== '') {
				$antwort->header(
					'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD'
				);
			}
			if ($anfrage->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS') !== '') {
				$antwort->header(
					"Access-Control-Allow-Headers",
					$anfrage->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS')
				);
			}

			$antwort->status(200);
			$antwort->send();
			exit;
		}
	}

	private function allowOrigins(): void
	{
		// passen Sie Ihre erlaubten Hosts hier an.
		$erlaubt = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		$anfrage = Flight::request();

		if (in_array($anfrage->getVar('HTTP_ORIGIN'), $erlaubt, true) === true) {
			$antwort = Flight::response();
			$antwort->header("Access-Control-Allow-Origin", $anfrage->getVar('HTTP_ORIGIN'));
		}
	}
}

// index.php oder wo immer Sie Ihre Routen haben
$CorsUtil = new CorsUtil();
Flight::before('start', [ $CorsUtil, 'setupCors' ]);

```


## Fazit

Sicherheit ist wichtig und es ist wichtig sicherzustellen, dass Ihre Webanwendungen sicher sind. Flight bietet eine Reihe von Funktionen, um Ihnen bei der Absicherung Ihrer Webanwendungen zu helfen, aber es ist wichtig, immer wachsam zu sein und sicherzustellen, dass Sie alles tun, um die Daten Ihrer Benutzer sicher zu halten. Gehen Sie immer vom Schlimmsten aus und vertrauen Sie niemals den Eingaben Ihrer Benutzer. Escapen Sie immer die Ausgabe und verwenden Sie vorbereitete Anweisungen, um SQL-Injection zu verhindern. Verwenden Sie immer Middleware, um Ihre Routen vor CSRF- und CORS-Angriffen zu schützen. Wenn Sie all diese Dinge tun, sind Sie auf dem besten Weg, sichere Webanwendungen zu erstellen.