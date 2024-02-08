# Ghostff/Sitzung

PHP-Sitzungsmanager (nicht blockierend, Flash, Segment, Sitzungsverschlüsselung). Verwendet PHP open_ssl für die optionale Verschlüsselung/Entschlüsselung von Sitzungsdaten. Unterstützt Datei, MySQL, Redis und Memcached.

## Installation

Mit Composer installieren.

```bash
composer require ghostff/session
```

## Grundkonfiguration

Sie müssen nichts übergeben, um die Standardeinstellungen für Ihre Sitzung zu verwenden. Weitere Einstellungen finden Sie im [Github Readme](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// Eine Sache, die Sie beachten müssen, ist, dass Sie bei jedem Seitenaufruf Ihre Sitzung bestätigen müssen
// oder Sie müssen auto_commit in Ihrer Konfiguration ausführen.
```

## Einfaches Beispiel

Hier ist ein einfaches Beispiel, wie Sie dies verwenden könnten.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// Führen Sie hier Ihre Anmelde-Logik aus
	// Passwort validieren, usw.

	// Wenn die Anmeldung erfolgreich ist
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// Jedes Mal, wenn Sie in die Sitzung schreiben, müssen Sie es bewusst bestätigen.
	$session->commit();
});

// Diese Überprüfung könnte in der Logik der eingeschränkten Seite oder in Middleware eingebettet sein.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// Führen Sie hier Ihre Logik für die eingeschränkte Seite aus
});

// Die Middleware-Version
Flight::route('/some-restricted-page', function() {
	// Reguläre Seitelogik
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

// Legen Sie einen benutzerdefinierten Pfad für Ihre Sitzungskonfigurationsdatei fest und geben Sie eine zufällige Zeichenfolge für die Sitzungs-ID an
$app->register('session', Session::class, [ 'Pfad/zur/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
	// oder Sie können Konfigurationsoptionen manuell überschreiben
		$session->updateConfiguration([
			// Wenn Sie Ihre Sitzungsdaten in einer Datenbank speichern möchten (gut, wenn Sie etwas Ähnliches wie "Melden Sie mich von allen Geräten ab" möchten)
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'mein-super-GEH3IM3S-Salz'), // bitte ändern Sie dies in etwas anderes
			Session::CONFIG_AUTO_COMMIT   => true, // tun Sie dies nur, wenn es erforderlich ist und/oder es schwierig ist, Ihre Sitzung zu bestätigen.
												// zusätzlich könnten Sie Flight::after('start', function() { Flight::session()->commit(); }); ausführen
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Datenbanktreiber für PDO-DSN z.B. (mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Datenbank-Host
				'db_name'   => 'meine_app_datenbank',   # Datenbankname
				'db_table'  => 'sitzungen',          # Datenbanktabelle
				'db_user'   => 'root',              # Datenbankbenutzername
				'db_pass'   => '',                  # Datenbankpasswort
				'persistent_conn'=> false,          # Vermeiden Sie den Overhead beim Herstellen einer neuen Verbindung jedes Mal, wenn ein Skript mit einer Datenbank sprechen muss, was zu einer schnelleren Webanwendung führt. FINDEN SIE IHREN EIGENEN WEG
			]
		]);
	}
);
```

## Dokumentation

Besuchen Sie das [Github Readme](https://github.com/Ghostff/Session) für die vollständige Dokumentation. Die Konfigurationsoptionen sind [im default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) gut dokumentiert. Der Code ist einfach zu verstehen, wenn Sie dieses Paket selbst durchgehen möchten.