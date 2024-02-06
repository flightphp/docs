# Ghostff/Session

PHP-Sitzungsmanager (nicht blockierend, Flash, Segment, Sitzungsverschlüsselung). Verwendet PHP open_ssl zur optionalen Verschlüsselung/Entschlüsselung von Sitzungsdaten. Unterstützt Datei, MySQL, Redis und Memcached.

## Installation

Installieren Sie es mit Composer.

```bash
composer require ghostff/session
```

## Grundkonfiguration

Sie müssen nichts übergeben, um die Standardeinstellungen für Ihre Sitzung zu verwenden. Weitere Einstellungen finden Sie im [Github-Readme](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// Eines ist zu beachten: Sie müssen Ihre Sitzung bei jedem Seitenaufruf übernehmen
// oder Sie müssen auto_commit in Ihrer Konfiguration ausführen.
```

## Einfaches Beispiel

Hier ist ein einfaches Beispiel, wie Sie dies verwenden könnten.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// Führen Sie hier Ihre Anmelde-Logik aus
	// Passwort überprüfen, etc.

	// Wenn die Anmeldung erfolgreich ist
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// Jedes Mal, wenn Sie in die Sitzung schreiben, müssen Sie sie absichtlich übernehmen.
	$session->commit();
});

// Diese Überprüfung könnte in der Logik der eingeschränkten Seite stehen oder mit Middleware umhüllt sein.
Flight::route('/einige-eingeschränkte-seite', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// Führen Sie hier Ihre Logik zur eingeschränkten Seite aus
});

// Die Middleware-Version
Flight::route('/einige-eingeschränkte-seite', function() {
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
$app->register('session', Session::class, [ 'pfad/zur/sitzungskonfig.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// oder Sie können Konfigurationsoptionen manuell überschreiben
		$session->updateConfiguration([
			// Wenn Sie Ihre Sitzungsdaten in einer Datenbank speichern möchten (gut, wenn Sie eine Funktion wie "Abmelden auf allen Geräten" möchten)
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'mein-super-S3CR3T-salz'), // Bitte ändern Sie dies, um etwas anderes zu sein
			Session::CONFIG_AUTO_COMMIT   => true, // Tun Sie dies nur, wenn es erforderlich ist und/oder es schwer ist, Ihre Sitzung mit commit() zu bestätigen.
												// Zusätzlich könnten Sie Flight::after('start', function() { Flight::session()->commit(); }); machen
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Datenbanktreiber für PDO-DNS z.B. (mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Datenbank-Host
				'db_name'   => 'meine_app_datenbank',   # Datenbankname
				'db_table'  => 'sitzungen',          # Datenbanktabelle
				'db_user'   => 'root',              # Datenbankbenutzername
				'db_pass'   => '',                  # Datenbankpasswort
				'persistent_conn'=> false,          # Vermeiden Sie den Overhead, jedes Mal eine neue Verbindung herzustellen, wenn ein Skript mit einer Datenbank sprechen muss, was zu einer schnelleren Webanwendung führt. SUCHEN SIE DIE RÜCKSEITE SELBST
			]
		]);
	}
);
```

## Dokumentation

Besuchen Sie das [Github-Readme](https://github.com/Ghostff/Session) für die vollständige Dokumentation. Die Konfigurationsoptionen sind [im default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) selbst gut dokumentiert. Der Code ist einfach zu verstehen, wenn Sie dieses Paket selbst durchsehen möchten.