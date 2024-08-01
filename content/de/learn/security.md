# Sicherheit

Sicherheit ist ein großes Thema, wenn es um Webanwendungen geht. Sie möchten sicherstellen, dass Ihre Anwendung sicher ist und dass die Daten Ihrer Benutzer geschützt sind. Flight bietet eine Reihe von Funktionen, um Ihnen bei der Sicherung Ihrer Webanwendungen zu helfen.

## Header

HTTP-Header sind eine der einfachsten Möglichkeiten, um Ihre Webanwendungen abzusichern. Sie können Header verwenden, um Clickjacking, XSS und andere Angriffe zu verhindern. Es gibt mehrere Möglichkeiten, wie Sie diese Header zu Ihrer Anwendung hinzufügen können.

Zwei großartige Websites, um die Sicherheit Ihrer Header zu überprüfen, sind [securityheaders.com](https://securityheaders.com/) und [observatory.mozilla.org](https://observatory.mozilla.org/).

### Manuell hinzufügen

Sie können diese Header manuell hinzufügen, indem Sie die `header`-Methode auf dem `Flight\Response`-Objekt verwenden.

```php
// Setzen des X-Frame-Options-Headers, um Clickjacking zu verhindern
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Setzen des Content-Security-Policy-Headers, um XSS zu verhindern
// Hinweis: Dieser Header kann sehr komplex werden, daher sollten Sie
//  Beispiele im Internet für Ihre Anwendung konsultieren
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Setzen des X-XSS-Protection-Headers, um XSS zu verhindern
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Setzen des X-Content-Type-Options-Headers, um MIME-Sniffing zu verhindern
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Setzen des Referrer-Policy-Headers, um zu steuern, wie viele Referrer-Informationen gesendet werden
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Setzen des Strict-Transport-Security-Headers, um HTTPS zu erzwingen
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Setzen des Permissions-Policy-Headers, um zu steuern, welche Funktionen und APIs verwendet werden können
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Diese können am Anfang Ihrer `bootstrap.php`- oder `index.php`-Dateien hinzugefügt werden.

### Als Filter hinzufügen

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

// index.php oder wo auch immer Sie Ihre Routen haben
// FYI, diese leere String-Gruppe fungiert als globales Middleware für
// alle Routen. Natürlich könnten Sie dasselbe tun und dies nur zu spezifischen Routen hinzufügen.
Flight::group('', function(Router $router) {
    $router->get('/users', [ 'BenutzerController', 'getBenutzer' ]);
    // mehr Routen
}, [ new SecurityHeadersMiddleware() ]);
```


## Cross-Site Request Forgery (CSRF)

Cross-Site Request Forgery (CSRF) ist eine Art von Angriff, bei dem eine bösartige Website den Browser eines Benutzers dazu bringen kann, eine Anfrage an Ihre Website zu senden. Dies kann verwendet werden, um Aktionen auf Ihrer Website ohne Wissen des Benutzers durchzuführen. Flight bietet keinen eingebauten CSRF-Schutzmechanismus, aber Sie können Ihren eigenen leicht implementieren, indem Sie Middleware verwenden.

### Einrichtung

Zunächst müssen Sie ein CSRF-Token generieren und es in der Sitzung des Benutzers speichern. Sie können dieses Token dann in Ihren Formularen verwenden und beim Absenden des Formulars überprüfen.

```php
// Generieren eines CSRF-Tokens und Speichern in der Sitzung des Benutzers
// (vorausgesetzt, Sie haben ein Sitzungsobjekt erstellt und es an Flight angehängt)
// siehe die Sitzungsdokumentation für weitere Informationen
Flight::register('session', \Ghostff\Session\Session::class);

// Sie müssen nur ein einziges Token pro Sitzung generieren (damit es funktioniert 
// über mehrere Registerkarten und Anfragen für denselben Benutzer)
if(Flight::session()->get('csrf_token') === null) {
    Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- Verwenden des CSRF-Token in Ihrem Formular -->
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
    <!-- andere Formularfelder -->
</form>
```

#### Verwendung von Latte

Sie können auch eine benutzerdefinierte Funktion festlegen, um das CSRF-Token in Ihren Latte-Vorlagen auszugeben.

```php
// Festlegen einer benutzerdefinierten Funktion zum Ausgeben des CSRF-Tokens
// Hinweis: Die Ansicht wurde mit Latte als Ansichtsmaschine konfiguriert
Flight::view()->addFunction('csrf', function() {
    $csrfToken = Flight::session()->get('csrf_token');
    return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

Und jetzt können Sie in Ihren Latte-Vorlagen die `csrf()`-Funktion verwenden, um das CSRF-Token auszugeben.

```html
<form method="post">
    {csrf()}
    <!-- andere Formularfelder -->
</form>
```

Kurz und knapp, richtig?

### Überprüfen des CSRF-Tokens

Sie können das CSRF-Token mithilfe von Event-Filtern überprüfen:

```php
// Dieses Middleware überprüft, ob die Anfrage eine POST-Anfrage ist, und falls ja, überprüft es, ob das CSRF-Token gültig ist
Flight::before('start', function() {
    if(Flight::request()->method == 'POST') {

        // Erfassen des CSRF-Tokens aus den Formularwerten
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
    $router->get('/users', [ 'BenutzerController', 'getBenutzer' ]);
    // mehr Routen
}, [ new CsrfMiddleware() ]);
```


## Cross-Site-Scripting (XSS)

Cross-Site-Scripting (XSS) ist eine Art von Angriff, bei dem eine bösartige Website Code in Ihre Website einschleusen kann. Die meisten dieser Möglichkeiten ergeben sich aus Formularwerten, die Ihre Endbenutzer ausfüllen. Sie sollten **niemals** Ausgaben Ihrer Benutzer vertrauen! Gehen Sie immer davon aus, dass alle die besten Hacker der Welt sind. Sie können bösartiges JavaScript oder HTML in Ihre Seite einschleusen. Dieser Code kann verwendet werden, um Informationen von Ihren Benutzern zu stehlen oder Aktionen auf Ihrer Website durchzuführen. Mit der View-Klasse von Flight können Sie Ausgaben einfach escapen, um XSS-Angriffe zu verhindern.

```php
// Nehmen wir an, der Benutzer ist clever und versucht, dies als ihren Namen zu verwenden
$name = '<script>alert("XSS")</script>';

// Dies wird die Ausgabe escapen
Flight::view()->set('name', $name);
// Dies wird ausgegeben: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Wenn Sie beispielsweise Latte als Ihre View-Klasse registriert haben, wird dies automatisch auch escapen.
Flight::view()->render('Vorlage', ['name' => $name]);
```

## SQL-Injection

SQL-Injection ist eine Art von Angriff, bei dem ein bösartiger Benutzer SQL-Code in Ihre Datenbank einschleusen kann. Dies kann verwendet werden, um Informationen aus Ihrer Datenbank zu stehlen oder Aktionen auf Ihrer Datenbank durchzuführen. Auch hier sollten Sie **niemals** Eingaben Ihrer Benutzer vertrauen! Gehen Sie immer davon aus, dass sie auf Blut aus sind. Sie können vorbereitete Anweisungen in Ihren `PDO`-Objekten verwenden, um SQL-Injections zu verhindern.

```php
// Nehmen wir an, Flight::db() ist als Ihr PDO-Objekt registriert
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Wenn Sie die PdoWrapper-Klasse verwenden, kann dies einfach in einer Zeile erledigt werden
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Sie können dasselbe mit einem PDO-Objekt mit ?-Platzhaltern tun
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Versprechen Sie einfach, dass Sie niemals EVER etwas wie dies tun werden...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// denn was ist, wenn $username = "' OR 1=1; -- ";
// Nachdem die Abfrage aufgebaut ist, sieht sie so aus
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Es sieht seltsam aus, aber es ist eine gültige Abfrage, die funktionieren wird. Tatsächlich
// ist es ein sehr verbreiteter SQL-Injectionsangriff, der alle Benutzer zurückgeben wird.
```

## CORS

Cross-Origin Resource Sharing (CORS) ist ein Mechanismus, der es ermöglicht, dass viele Ressourcen (z. B. Schriften, JavaScript usw.) auf einer Webseite von einer anderen Domain angefordert werden können, die sich außerhalb der Domain befindet, von der die Ressource stammt. Flight hat keine integrierte Funktionalität dafür, aber dies kann leicht über ein Hook gehandhabt werden, das vor dem Aufruf der `Flight::start()`-Methode ausgeführt wird.

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
        // passen Sie hier Ihre erlaubten Hosts an.
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

// Dies muss ausgeführt werden, bevor start ausgeführt wird.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

## Fazit

Sicherheit ist wichtig und es ist wichtig sicherzustellen, dass Ihre Webanwendungen sicher sind. Flight bietet eine Reihe von Funktionen, um Ihnen bei der Sicherung Ihrer Webanwendungen zu helfen, aber es ist wichtig, immer wachsam zu sein und sicherzustellen, dass Sie alles tun, um die Daten Ihrer Benutzer sicher aufzubewahren. Gehen Sie immer vom Schlimmsten aus und vertrauen Sie niemals den Eingaben Ihrer Benutzer. Escapen Sie immer die Ausgaben und verwenden Sie vorbereitete Anweisungen, um SQL-Injection zu verhindern. Verwenden Sie immer Middleware, um Ihre Routen vor CSRF- und CORS-Angriffen zu schützen. Wenn Sie all diese Dinge tun, sind Sie auf dem besten Weg, sichere Webanwendungen zu erstellen.