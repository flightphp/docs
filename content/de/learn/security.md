# Sicherheit

Sicherheit ist ein wichtiges Thema, wenn es um Webanwendungen geht. Sie möchten sicherstellen, dass Ihre Anwendung sicher ist und die Daten Ihrer Benutzer geschützt sind. Flight bietet eine Reihe von Funktionen, um Ihnen dabei zu helfen, Ihre Webanwendungen zu sichern.

## Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF) ist ein Angriffstyp, bei dem eine bösartige Website den Browser eines Benutzers dazu bringen kann, eine Anfrage an Ihre Website zu senden. Dies kann verwendet werden, um Aktionen auf Ihrer Website ohne das Wissen des Benutzers auszuführen. Flight bietet keinen integrierten CSRF-Schutzmechanismus, aber Sie können ganz einfach Ihren eigenen implementieren, indem Sie Middleware verwenden.

Zuerst müssen Sie ein CSRF-Token generieren und es in der Sitzung des Benutzers speichern. Sie können dieses Token dann in Ihren Formularen verwenden und überprüfen, wenn das Formular gesendet wird.

```php
// Generiere ein CSRF-Token und speichere es in der Sitzung des Benutzers
// (angenommen, Sie haben ein Session-Objekt erstellt und es an Flight angehängt)
Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
```

```html
<!-- Verwenden Sie das CSRF-Token in Ihrem Formular -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- andere Formularfelder -->
</form>
```

Und dann können Sie das CSRF-Token mit Ereignisfiltern überprüfen:

```php
// Diese Middleware überprüft, ob die Anfrage eine POST-Anfrage ist, und wenn ja, wird überprüft, ob das CSRF-Token gültig ist
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// hole das CSRF-Token aus den Formularwerten
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Ungültiges CSRF-Token');
		}
	}
});
```

## Cross Site Scripting (XSS)

Cross Site Scripting (XSS) ist ein Angriffstyp, bei dem eine bösartige Website Code in Ihre Website einschleusen kann. Die meisten dieser Möglichkeiten ergeben sich aus Formularwerten, die Ihre Endbenutzer ausfüllen. Vertrauen Sie **niemals** der Ausgabe Ihrer Benutzer! Gehen Sie immer davon aus, dass sie die besten Hacker der Welt sind. Sie können bösartiges JavaScript oder HTML in Ihre Seite einschleusen. Dieser Code kann verwendet werden, um Informationen von Ihren Benutzern zu stehlen oder Aktionen auf Ihrer Website auszuführen. Mit der View-Klasse von Flight können Sie die Ausgabe einfach escapen, um XSS-Angriffe zu verhindern.

```php

// Nehmen wir an, der Benutzer ist schlau und versucht, dies als seinen Namen zu verwenden
$name = '<script>alert("XSS")</script>';

// Dies wird die Ausgabe escapen
Flight::view()->set('name', $name);
// Dies wird ausgegeben: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Wenn Sie etwas wie Latte als Ihre View-Klasse registrieren, wird dies auch automatisch escapen.
Flight::view()->render('template', ['name' => $name]);
```

## SQL Injection

SQL Injection ist ein Angriffstyp, bei dem ein bösartiger Benutzer SQL-Code in Ihre Datenbank einschleusen kann. Dies kann verwendet werden, um Informationen aus Ihrer Datenbank zu stehlen oder Aktionen auf Ihrer Datenbank auszuführen. Auch hier sollten Sie **niemals** Eingaben von Ihren Benutzern vertrauen! Gehen Sie immer davon aus, dass sie es auf Ihre Daten abgesehen haben. Sie können vorbereitete Anweisungen in Ihren `PDO`-Objekten verwenden, um SQL-Injection zu verhindern.

```php

// Angenommen, Sie haben Flight::db() als Ihr PDO-Objekt registriert
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Wenn Sie die PdoWrapper-Klasse verwenden, kann dies einfach in einer Zeile erfolgen
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Sie können dasselbe mit einem PDO-Objekt mit ? Platzhaltern tun
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Versprechen Sie einfach, dass Sie niemals etwas Ähnliches wie dies tun werden...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// denn was, wenn $username = "' OR 1=1; -- "; Nachdem die Abfrage erstellt wurde, sieht sie so aus
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Es sieht seltsam aus, aber es ist eine gültige Abfrage, die funktioniert. Tatsächlich handelt es sich um einen sehr verbreiteten SQL-Injection-Angriff, der alle Benutzer zurückgibt.
```

## CORS

Cross-Origin Resource Sharing (CORS) ist ein Mechanismus, der es ermöglicht, dass viele Ressourcen (z. B. Schriftarten, JavaScript usw.) auf einer Webseite von einer anderen Domäne außerhalb der Domäne angefordert werden, aus der die Ressource stammt. Flight verfügt nicht über eine integrierte Funktionalität, aber dies kann problemlos mit Middleware oder Ereignisfiltern ähnlich wie bei CSRF gehandhabt werden.

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

// index.php oder wo auch immer Sie Ihre Routen haben
Flight::route('/benutzer', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(new CorsMiddleware());
```

## Fazit

Sicherheit ist wichtig, und es ist wichtig, sicherzustellen, dass Ihre Webanwendungen sicher sind. Flight bietet eine Reihe von Funktionen, um Ihnen dabei zu helfen, Ihre Webanwendungen zu sichern, aber es ist wichtig, immer wachsam zu sein und sicherzustellen, dass Sie alles tun, um die Daten Ihrer Benutzer sicher zu halten. Gehen Sie immer vom Schlimmsten aus und vertrauen Sie niemals den Eingaben Ihrer Benutzer. Escapen Sie immer Ausgaben und verwenden Sie vorbereitete Anweisungen, um SQL-Injektionen zu verhindern. Verwenden Sie immer Middleware, um Ihre Routen vor CSRF- und CORS-Angriffen zu schützen. Wenn Sie all dies tun, sind Sie auf dem besten Weg, sichere Webanwendungen zu entwickeln.