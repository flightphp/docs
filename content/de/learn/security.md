# Sicherheit

Sicherheit ist ein großes Thema, wenn es um Webanwendungen geht. Sie möchten sicherstellen, dass Ihre Anwendung sicher ist und dass die Daten Ihrer Benutzer sicher sind. Flight bietet eine Reihe von Funktionen, um Ihnen zu helfen, Ihre Webanwendungen abzusichern.

## Cross-Site-Request-Forgery (CSRF)

Cross-Site-Request-Forgery (CSRF) ist eine Art von Angriff, bei dem eine bösartige Website den Browser eines Benutzers dazu bringen kann, eine Anfrage an Ihre Website zu senden. Dies kann verwendet werden, um Aktionen auf Ihrer Website ohne das Wissen des Benutzers durchzuführen. Flight bietet keinen eingebauten CSRF-Schutzmechanismus, aber Sie können ganz einfach Ihren eigenen implementieren, indem Sie Middleware verwenden.

Hier ist ein Beispiel, wie Sie CSRF-Schutz mithilfe von Event-Filtern implementieren können:

```php

// Diese Middleware überprüft, ob die Anfrage eine POST-Anfrage ist und ob das CSRF-Token gültig ist
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// Erfassen Sie das CSRF-Token aus den Formulardaten
		$token = Flight::request()->data->csrf_token;
		if($token != $_SESSION['csrf_token']) {
			Flight::halt(403, 'Ungültiges CSRF-Token');
		}
	}
});
```

## Cross-Site-Scripting (XSS)

Cross-Site-Scripting (XSS) ist eine Art von Angriff, bei dem eine bösartige Website Code in Ihre Website einschleusen kann. Die meisten dieser Möglichkeiten ergeben sich aus Formularwerten, die Ihre Endbenutzer ausfüllen werden. Sie sollten **niemals** auf die Ausgaben Ihrer Benutzer vertrauen! Gehen Sie immer davon aus, dass alle die besten Hacker der Welt sind. Sie können bösartiges JavaScript oder HTML in Ihre Seite einschleusen. Dieser Code kann verwendet werden, um Informationen von Ihren Benutzern zu stehlen oder Aktionen auf Ihrer Website durchzuführen. Mit Hilfe der View-Klasse von Flight können Sie Ausgaben einfach maskieren, um XSS-Angriffe zu verhindern.

```php

// Angenommen, der Benutzer ist schlau und versucht, dies als seinen Namen zu verwenden
$name = '<script>alert("XSS")</script>';

// Das wird die Ausgabe maskieren
Flight::view()->set('name', $name);
// Dies wird ausgegeben: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Wenn Sie beispielsweise Latte als Ihre View-Klasse registriert haben, wird dies auch automatisch maskiert.
Flight::view()->render('template', ['name' => $name]);
```

## SQL-Injection

SQL-Injection ist eine Art von Angriff, bei dem ein bösartiger Benutzer SQL-Code in Ihre Datenbank einschleusen kann. Dies kann verwendet werden, um Informationen aus Ihrer Datenbank zu stehlen oder Aktionen auf Ihrer Datenbank durchzuführen. Gehen Sie auch hier **niemals** davon aus, dass die Eingaben Ihrer Benutzer vertrauenswürdig sind! Gehen Sie immer davon aus, dass sie es auf Ihr Blut abgesehen haben. Sie können vorbereitete Anweisungen in Ihren `PDO`-Objekten verwenden, um SQL-Injection zu verhindern.

```php

// Angenommen, Sie haben Flight::db() als Ihr PDO-Objekt registriert
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Wenn Sie die Klasse PdoWrapper verwenden, kann dies einfach in einer Zeile erledigt werden
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Sie können dasselbe mit einem PDO-Objekt mit ?-Platzhaltern tun
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Versprechen Sie einfach, niemals etwas wie dies zu tun...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}'");
// Denn was ist, wenn $username = "' OR 1=1;"; Nachdem die Abfrage erstellt wurde, sieht diese so aus
// SELECT * FROM users WHERE username = '' OR 1=1;
// Es sieht merkwürdig aus, aber es ist eine gültige Abfrage, die funktionieren wird. Tatsächlich
// ist es ein sehr häufiger SQL-Injektionsangriff, der alle Benutzer zurückgibt.
```

## CORS

Cross-Origin Resource Sharing (CORS) ist ein Mechanismus, der es ermöglicht, dass viele Ressourcen (z.B. Schriftarten, JavaScript usw.) auf einer Webseite von einer anderen Domäne außerhalb der Domäne, von der die Ressource stammt, angefordert werden können. Flight verfügt nicht über eine integrierte Funktionalität, aber dies kann leicht mit Middleware oder Event-Filtern ähnlich wie CSRF gehandhabt werden.

```php

Flight::route('/users', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(function() {
	if (isset($_SERVER['HTTP_ORIGIN'])) {
		header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Max-Age: 86400');
	}

	if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
			header(
				'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS'
			);
		}
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
			header(
				"Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
			);
		}
		exit(0);
	}
});
```

## Schlussfolgerung

Sicherheit ist wichtig, und es ist wichtig sicherzustellen, dass Ihre Webanwendungen sicher sind. Flight bietet eine Reihe von Funktionen, um Ihnen zu helfen, Ihre Webanwendungen abzusichern, aber es ist wichtig, immer wachsam zu sein und sicherzustellen, dass Sie alles tun, um die Daten Ihrer Benutzer sicher zu halten. Gehen Sie immer vom Schlimmsten aus und vertrauen Sie niemals den Eingaben Ihrer Benutzer. Maskieren Sie immer Ausgaben und verwenden Sie vorbereitete Anweisungen, um SQL-Injektionen zu verhindern. Verwenden Sie immer Middleware, um Ihre Routen vor CSRF- und CORS-Angriffen zu schützen. Wenn Sie all diese Dinge tun, sind Sie auf dem besten Weg, sichere Webanwendungen zu erstellen.