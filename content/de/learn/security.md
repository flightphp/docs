# Sicherheit

Sicherheit ist ein großes Thema bei Webanwendungen. Du möchtest sicherstellen, dass deine Anwendung sicher ist und die Daten deiner Benutzer geschützt sind. Flight bietet eine Reihe von Funktionen, um dir bei der Sicherung deiner Webanwendungen zu helfen.

## Header

HTTP-Header sind eine der einfachsten Möglichkeiten, um deine Webanwendungen abzusichern. Du kannst Header verwenden, um Clickjacking, XSS und andere Angriffe zu verhindern. Es gibt mehrere Möglichkeiten, wie du diese Header zu deiner Anwendung hinzufügen kannst.

### Manuell hinzufügen

Du kannst diese Header manuell hinzufügen, indem du die Methode `header` auf dem `Flight\Response`-Objekt verwendest.

```php
// Setze den X-Frame-Options-Header, um Clickjacking zu verhindern
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Setze den Content-Security-Policy-Header, um XSS zu verhindern
// Hinweis: Dieser Header kann sehr komplex werden, daher solltest du
//  Beispiele im Internet für deine Anwendung konsultieren
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Setze den X-XSS-Protection-Header, um XSS zu verhindern
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Setze den X-Content-Type-Options-Header, um MIME-Sniffing zu verhindern
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Setze den Referrer-Policy-Header, um zu steuern, wie viele Referrer-Informationen gesendet werden
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Setze den Strict-Transport-Security-Header, um HTTPS zu erzwingen
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
```

Diese können am Anfang deiner `bootstrap.php` oder `index.php` Dateien hinzugefügt werden.

### Als Filter hinzufügen

Du kannst sie auch in einem Filter/Hook wie folgt hinzufügen:

```php
// Füge die Header in einem Filter hinzu
Flight::before('start', function() {
	Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
	Flight::response()->header("Content-Security-Policy", "default-src 'self'");
	Flight::response()->header('X-XSS-Protection', '1; mode=block');
	Flight::response()->header('X-Content-Type-Options', 'nosniff');
	Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
	Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
});
```

### Als Middleware hinzufügen

Du kannst sie auch als Middleware-Klasse hinzufügen. Dies ist eine gute Möglichkeit, deinen Code übersichtlich und organisiert zu halten.

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
	}
}

// index.php oder wo auch immer deine Routen sind
// Übrigens, diese leere Gruppe dient als globales Middleware für
// alle Routen. Natürlich könntest du dasselbe tun und es nur zu spezifischen Routen hinzufügen.
Flight::group('', function(Router $router) {
	$router->get('/benutzer', [ 'BenutzerController', 'getBenutzer' ]);
	// weitere Routen
}, [ new SecurityHeadersMiddleware() ]);
```


## Cross-Site-Request-Forgery (CSRF)

Cross-Site-Request-Forgery (CSRF) ist ein Angriffstyp, bei dem eine bösartige Website den Browser eines Benutzers dazu bringen kann, eine Anfrage an deine Website zu senden. Dies kann verwendet werden, um Aktionen auf deiner Website ohne Wissen des Benutzers auszuführen. Flight bietet keinen eingebauten CSRF-Schutzmechanismus, aber du kannst leicht deinen eigenen implementieren, indem du Middleware verwendest.

### Einrichtung

Zuerst musst du ein CSRF-Token generieren und es in der Sitzung des Benutzers speichern. Du kannst dann dieses Token in deinen Formularen verwenden und es überprüfen, wenn das Formular übermittelt wird.

```php
// Generiere ein CSRF-Token und speichere es in der Sitzung des Benutzers
// (unter der Annahme, dass du ein Sitzungsobjekt erstellt und an Flight angehängt hast)
// Du musst nur ein Token pro Sitzung generieren (damit es
// über mehrere Registerkarten und Anfragen für denselben Benutzer funktioniert)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- Verwende das CSRF-Token in deinem Formular -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- andere Formularfelder -->
</form>
```

#### Verwendung von Latte

Du kannst auch eine benutzerdefinierte Funktion einstellen, um das CSRF-Token in deinen Latte-Templates auszugeben.

```php
// Setze eine benutzerdefinierte Funktion, um das CSRF-Token auszugeben
// Hinweis: View wurde mit Latte als Anzeigemotor konfiguriert
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

Und jetzt kannst du in deinen Latte-Templates die `csrf()`-Funktion verwenden, um das CSRF-Token auszugeben.

```html
<form method="post">
	{csrf()}
	<!-- andere Formularfelder -->
</form>
```

Kurz und einfach, oder?

### Überprüfe das CSRF-Token

Du kannst das CSRF-Token mithilfe von Event-Filtern überprüfen:

```php
// Dieses Middleware überprüft, ob die Anfrage eine POST-Anfrage ist, und falls ja, ob das CSRF-Token gültig ist
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// Erfasse das CSRF-Token aus den Formularwerten
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Ungültiges CSRF-Token');
		}
	}
});
```

Oder du kannst eine Middleware-Klasse verwenden:

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

// index.php oder wo auch immer deine Routen sind
Flight::group('', function(Router $router) {
	$router->get('/benutzer', [ 'BenutzerController', 'getBenutzer' ]);
	// weitere Routen
}, [ new CsrfMiddleware() ]);
```


## Cross-Site-Scripting (XSS)

Cross-Site-Scripting (XSS) ist ein Angriffstyp, bei dem eine bösartige Website Code in deine Website einschleusen kann. Die meisten dieser Möglichkeiten ergeben sich aus Formularwerten, die deine Endbenutzer ausfüllen. Du solltest **niemals** die Ausgabe deiner Benutzer vertrauen! Gehe immer davon aus, dass sie die besten Hacker der Welt sind. Sie können bösartiges JavaScript oder HTML in deine Seite einschleusen. Dieser Code kann verwendet werden, um Informationen von deinen Benutzern zu stehlen oder Aktionen auf deiner Website auszuführen. Mit Hilfe der View-Klasse von Flight kannst du die Ausgabe einfach escapen, um XSS-Angriffe zu verhindern.

```php
// Nehmen wir an, der Benutzer ist schlau und versucht, dies als seinen Namen zu verwenden
$name = '<script>alert("XSS")</script>';

// Dies wird die Ausgabe escapen
Flight::view()->set('name', $name);
// Dies wird ausgegeben: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Wenn du etwas wie Latte als deine View-Klasse registriert hast, wird dies auch automatisch escapen.
Flight::view()->render('template', ['name' => $name]);
```

## SQL-Injection

SQL-Injection ist ein Angriffstyp, bei dem ein bösartiger Benutzer SQL-Code in deine Datenbank einschleusen kann. Dies kann verwendet werden, um Informationen aus deiner Datenbank zu stehlen oder Aktionen auf deiner Datenbank auszuführen. Auch hier solltest du **niemals** die Eingaben deiner Benutzer vertrauen! Gehe immer davon aus, dass sie es auf dein Bestes abgesehen haben. Du kannst vorbereitete Anweisungen in deinen `PDO`-Objekten verwenden, um SQL-Injection zu verhindern.

```php
// Angenommen, du hast Flight::db() als dein PDO-Objekt registriert
$anweisung = Flight::db()->prepare('SELECT * FROM benutzer WHERE benutzername = :benutzername');
$anweisung->execute([':benutzername' => $benutzername]);
$benutzer = $anweisung->fetchAll();

// Wenn du die PdoWrapper-Klasse verwendest, kann dies leicht in einer Zeile erledigt werden
$benutzer = Flight::db()->fetchAll('SELECT * FROM benutzer WHERE benutzername = :benutzername', [ 'benutzername' => $benutzername ]);

// Du kannst dasselbe mit einem PDO-Objekt mit ? Platzhaltern tun
$anweisung = Flight::db()->fetchAll('SELECT * FROM benutzer WHERE benutzername = ?', [ $benutzername ]);

// Versprich einfach, dass du niemals, niemals etwas wie dies tun wirst...
$benutzer = Flight::db()->fetchAll("SELECT * FROM benutzer WHERE benutzername = '{$benutzername}' LIMIT 5");
// denn was ist, wenn $benutzername = "' OR 1=1; -- "; 
// Nachdem die Abfrage erstellt ist, sieht sie so aus
// SELECT * FROM benutzer WHERE benutzername = '' OR 1=1; -- LIMIT 5
// Es sieht seltsam aus, aber es ist eine gültige Abfrage, die funktionieren wird. Tatsächlich
// handelt es sich um einen sehr häufigen SQL-Injektionsangriff, der alle Benutzer zurückgibt.
```

## CORS

Cross-Origin Resource Sharing (CORS) ist ein Mechanismus, der es ermöglicht, dass viele Ressourcen (z.B. Schriftarten, JavaScript usw.) auf einer Webseite von einer anderen Domain angefordert werden, die von der Domain abweicht, von der die Ressource stammt. Flight hat keine eingebaute Funktionalität dafür, aber dies kann leicht mit Middleware oder Event-Filtern behandelt werden, ähnlich wie CSRF.

```php
// app/middleware/CorsMiddleware.php

namespace app\middleware;

class CorsMiddleware
{
	public function before(array $params): void
	{
		$response = Flight::response();
		if (isset($_SERVER['HTTP_ORIGIN'])) {
			$this->allowOrigins();
			$response->header('Access-Control-Allow-Credentials: true');
			$response->header('Access-Control-Max-Age: 86400');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
				$response->header(
					'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS'
				);
			}
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
				$response->header(
					"Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
				);
			}
			$response->send();
			exit(0);
		}
	}

	private function allowOrigins(): void
	{
		// Passe deine erlaubten Hosts hier an.
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		if (in_array($_SERVER['HTTP_ORIGIN'], $allowed)) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		}
	}
}

// index.php oder wo auch immer deine Routen sind
Flight::route('/benutzer', function() {
	$benutzer = Flight::db()->fetchAll('SELECT * FROM benutzer');
	Flight::json($benutzer);
})->addMiddleware(new CorsMiddleware());
```

## Fazit

Sicherheit ist wichtig, und es ist wichtig sicherzustellen, dass deine Webanwendungen sicher sind. Flight bietet eine Reihe von Funktionen, um dir bei der Sicherung deiner Webanwendungen zu helfen, aber es ist wichtig, immer wachsam zu sein und sicherzustellen, dass du alles Mögliche tust, um die Daten deiner Benutzer sicher zu halten. Gehe immer vom Schlimmsten aus und vertraue niemals den Eingaben deiner Benutzer. Escape immer die Ausgabe und verwende vorbereitete Anweisungen, um SQL-Injection zu verhindern. Benutze immer Middleware, um deine Routen vor CSRF- und CORS-Angriffen zu schützen. Wenn du all diese Dinge tust, bist du auf dem besten Weg, sichere Webanwendungen zu erstellen.