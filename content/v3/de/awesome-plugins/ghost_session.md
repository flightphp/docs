# Ghostff/Session

PHP-Sitzungsmanager (nicht blockierend, Flash, Segment, Sitzungsverschlüsselung). Verwendet PHP open_ssl für optionale Verschlüsselung/Entschlüsselung von Sitzungsdaten. Unterstützt File, MySQL, Redis und Memcached.

Klicken Sie [hier](https://github.com/Ghostff/Session), um den Code anzusehen.

## Installation

Installieren Sie mit Composer.

```bash
composer require ghostff/session
```

## Basic Configuration

Sie müssen nichts übergeben, um die Standardeinstellungen für Ihre Sitzung zu verwenden. Sie können mehr über Einstellungen in der [Github Readme](https://github.com/Ghostff/Session) nachlesen.

```php
use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// eine Sache, die man sich merken sollte, ist, dass Sie Ihre Sitzung bei jedem Seitenaufruf committen müssen
// oder Sie müssen auto_commit in Ihrer Konfiguration ausführen.
```

## Simple Example

Hier ist ein einfaches Beispiel, wie Sie das verwenden könnten.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// führen Sie hier Ihre Login-Logik aus
	// Passwort validieren usw.

	// wenn der Login erfolgreich ist
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// immer wenn Sie in die Sitzung schreiben, müssen Sie sie explizit committen.
	$session->commit();
});

// Diese Überprüfung könnte in der Logik der eingeschränkten Seite erfolgen oder mit Middleware umgeben sein.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// führen Sie hier Ihre Logik für die eingeschränkte Seite aus
});

// die Middleware-Version
Flight::route('/some-restricted-page', function() {
	// reguläre Seitenslogik
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## More Complex Example

Hier ist ein komplexeres Beispiel, wie Sie das verwenden könnten.

```php
use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// geben Sie als ersten Argument einen benutzerdefinierten Pfad zu Ihrer Sitzungskonfigurationsdatei an
// oder geben Sie das benutzerdefinierte Array
$app->register('session', Session::class, [ 
	[
		// wenn Sie Ihre Sitzungsdaten in einer Datenbank speichern möchten (gut für Funktionen wie "mich von allen Geräten abmelden")
		Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
		Session::CONFIG_ENCRYPT_DATA  => true,
		Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // bitte ändern Sie das zu etwas anderem
		Session::CONFIG_AUTO_COMMIT   => true, // tun Sie das nur, wenn es erforderlich ist und/oder es schwierig ist, commit() für Ihre Sitzung aufzurufen.
												// zusätzlich könnten Sie Flight::after('start', function() { Flight::session()->commit(); }); machen.
		Session::CONFIG_MYSQL_DS         => [
			'driver'    => 'mysql',             # Database driver for PDO dns eg(mysql:host=...;dbname=...)
			'host'      => '127.0.0.1',         # Database host
			'db_name'   => 'my_app_database',   # Database name
			'db_table'  => 'sessions',          # Database table
			'db_user'   => 'root',              # Database username
			'db_pass'   => '',                  # Database password
			'persistent_conn'=> false,          # Vermeiden Sie den Aufwand, eine neue Verbindung bei jedem Skriptaufruf herzustellen, was zu einer schnelleren Web-Anwendung führt. FINDEN SIE DIE NACHTEILE SELBST
		]
	] 
]);
```

## Help! My Session Data is Not Persisting!

Setzen Sie Ihre Sitzungsdaten und sie persistieren nicht zwischen Anfragen? Sie haben vielleicht vergessen, Ihre Sitzungsdaten zu committen. Sie können das tun, indem Sie `$session->commit()` aufrufen, nachdem Sie Ihre Sitzungsdaten gesetzt haben.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// führen Sie hier Ihre Login-Logik aus
	// Passwort validieren usw.

	// wenn der Login erfolgreich ist
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// immer wenn Sie in die Sitzung schreiben, müssen Sie sie explizit committen.
	$session->commit();
});
```

Die andere Möglichkeit, das zu umgehen, ist, wenn Sie Ihren Sitzungsdienst einrichten, `auto_commit` in Ihrer Konfiguration auf `true` setzen. Das wird Ihre Sitzungsdaten automatisch nach jeder Anfrage committen.

```php
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Zusätzlich könnten Sie `Flight::after('start', function() { Flight::session()->commit(); });` machen, um Ihre Sitzungsdaten nach jeder Anfrage zu committen.

## Documentation

Besuchen Sie die [Github Readme](https://github.com/Ghostff/Session) für die vollständige Dokumentation. Die Konfigurationsoptionen sind [gut dokumentiert in der default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php)-Datei selbst. Der Code ist einfach zu verstehen, wenn Sie dieses Paket selbst durchsehen möchten.