# Ghostff/Session

PHP Session-Manager (nicht blockierend, Flash, Segment, Sitzungsverschlüsselung). Verwendet PHP open_ssl zur optionalen Verschlüsselung/Entschlüsselung von Sitzungsdaten. Unterstützt Datei, MySQL, Redis und Memcached.

## Installation

Installieren mit Composer.

```bash
composer require ghostff/session
```

## Grundkonfiguration

Es ist nicht erforderlich, etwas zu übergeben, um die Standardeinstellungen mit Ihrer Sitzung zu verwenden. Weitere Einstellungen finden Sie in der [Github Readme](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// Eine Sache, die Sie sich merken müssen, ist, dass Sie Ihre Sitzung bei jedem Seitenaufruf commiten müssen
// oder Sie müssen auto_commit in Ihrer Konfiguration ausführen.
```

## Einfaches Beispiel

Hier ist ein einfaches Beispiel, wie Sie dies verwenden könnten.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// Führen Sie Ihre Anmelde-Logik hier aus
	// Passwort validieren, usw.

	// Wenn die Anmeldung erfolgreich ist
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// Jedes Mal, wenn Sie in die Sitzung schreiben, müssen Sie sie bewusst committen.
	$session->commit();
});

// Diese Überprüfung könnte in der Logik der eingeschränkten Seite sein oder mit Middleware umschlossen sein.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// Führen Sie Ihre Logik für eingeschränkte Seiten hier aus
});

// Die Middleware-Version
Flight::route('/some-restricted-page', function() {
	// Reguläre Seitenlogik
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## Komplexeres Beispiel

Hier ist ein komplexeres Beispiel, wie Sie dies verwenden könnten.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// Legen Sie einen benutzerdefinierten Pfad für Ihre Sitzungskonfigurationsdatei fest und geben Sie ihm eine zufällige Zeichenfolge für die Sitzungs-ID
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// oder Sie können Konfigurationsoptionen manuell überschreiben
		$session->updateConfiguration([
			// Wenn Sie Ihre Sitzungsdaten in einer Datenbank speichern möchten (gut, wenn Sie so etwas wie die Funktionalität "Melden Sie mich von allen Geräten ab" möchten)
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // Bitte ändern Sie diesen Wert in etwas anderes
			Session::CONFIG_AUTO_COMMIT   => true, // Tun Sie dies nur, wenn es erforderlich ist und/oder es schwierig ist, Ihre Sitzung zu commiten.
												// Zusätzlich könnten Sie Flight::after('start', function() { Flight::session()->commit(); }); ausführen.
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Datenbanktreiber für PDO-DNS z.B. (mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Datenbankhost
				'db_name'   => 'my_app_database',   # Datenbankname
				'db_table'  => 'sessions',          # Datenbanktabelle
				'db_user'   => 'root',              # Datenbankbenutzername
				'db_pass'   => '',                  # Datenbankpasswort
				'persistent_conn'=> false,          # Vermeiden Sie den Overhead beim Herstellen einer neuen Verbindung jedes Mal, wenn ein Skript mit einer Datenbank sprechen muss, was zu einer schnelleren Webanwendung führt. FINDEN SIE DIE RÜCKSEITE SELBST
			]
		]);
	}
);
```

## Dokumentation

Besuchen Sie die [Github Readme](https://github.com/Ghostff/Session) für die vollständige Dokumentation. Die Konfigurationsoptionen sind [im default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) gut dokumentiert. Der Code ist einfach zu verstehen, wenn Sie dieses Paket selbst durchgehen möchten.