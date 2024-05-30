# Ghostff/Session

PHP-Sitzungsmanager (nicht blockierend, Flash, Segment, Sitzungsverschlüsselung). Verwendet PHP open_ssl für die optionale Verschlüsselung/Entschlüsselung von Sitzungsdaten. Unterstützt Datei, MySQL, Redis und Memcached.

## Installation

Installiere es mit Composer.

```bash
composer require ghostff/session
```

## Grundkonfiguration

Sie müssen nichts übergeben, um die Standardeinstellungen für Ihre Sitzung zu verwenden. Weitere Einstellungen findest du in der [Github Readme](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// Etwas, an das man sich erinnern sollte, ist, dass Sie Ihre Sitzung bei jedem Laden der Seite bestätigen müssen
// oder Sie müssen auto_commit in Ihrer Konfiguration ausführen.
```

## Einfaches Beispiel

Hier ist ein einfaches Beispiel, wie du das verwenden könntest.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// Führen Sie hier Ihre Anmelde-Logik aus
	// Passwort validieren, usw.

	// Wenn die Anmeldung erfolgreich ist
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// Jedes Mal, wenn Sie auf die Sitzung schreiben, müssen Sie sie bewusst bestätigen.
	$session->commit();
});

// Diese Überprüfung könnte in der Logik der eingeschränkten Seite sein oder mit Middleware umgeben sein.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();
	
	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// Führen Sie hier Ihre Logik für eingeschränkte Seiten aus
});

// Die Version mit Middleware
Flight::route('/some-restricted-page', function() {
	// Normale Seitenlogik
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## Komplexeres Beispiel

Hier ist ein komplexeres Beispiel, wie du das verwenden könntest.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// Legen Sie einen benutzerdefinierten Pfad für Ihre Sitzungskonfigurationsdatei fest und geben Sie eine Zufallszeichenfolge für die Sitzungs-ID an
$app->register('session', Session::class, [ 'pfad/zur/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// oder Sie können manuell Konfigurationsoptionen überschreiben
		$session->updateConfiguration([
			// Wenn Sie Ihre Sitzungsdaten in einer Datenbank speichern möchten (gut, wenn Sie etwas wie "Melden Sie mich von allen Geräten ab" möchten)
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'mein-super-GEHEIME-salt'), // bitte ändern Sie dies in etwas anderes
			Session::CONFIG_AUTO_COMMIT   => true, // tun Sie dies nur, wenn es erforderlich ist oder es schwierig ist, Ihre Sitzung mit commit() zu bestätigen.
												   // zusätzlich könnten Sie Flight::after('start', function() { Flight::session()->commit(); }); ausführen
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Datenbanktreiber für PDO dns zB(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Datenbankhost
				'db_name'   => 'meine_app-datenbank',   # Datenbankname
				'db_table'  => 'sitzungen',          # Datenbanktabelle
				'db_user'   => 'root',              # Datenbankbenutzername
				'db_pass'   => '',                  # Datenbankpasswort
				'persistent_conn'=> false,          # Vermeiden Sie den Overhead beim Herstellen einer neuen Verbindung jedes Mal, wenn ein Skript mit einer Datenbank kommunizieren muss, was zu einer schnelleren Webanwendung führt. FINDEN SIE DIE RÜCKSEITE SELBST HERAUS
			]
		]);
	}
);
```

## Hilfe! Meine Sitzungsdaten bleiben nicht erhalten!

Hast du deine Sitzungsdaten gesetzt und sie bleiben nicht zwischen Anfragen erhalten? Du hast vielleicht vergessen, deine Sitzungsdaten zu bestätigen. Dies kannst du tun, indem du `$session->commit()` aufrufst, nachdem du deine Sitzungsdaten gesetzt hast.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// Führen Sie hier Ihre Anmelde-Logik aus
	// Passwort validieren, usw.

	// Wenn die Anmeldung erfolgreich ist
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// Jedes Mal, wenn Sie auf die Sitzung schreiben, müssen Sie sie bewusst bestätigen.
	$session->commit();
});
```

Der andere Weg dies zu umgehen ist, wenn Sie Ihren Sitzungsdienst einrichten, müssen Sie `auto_commit` auf `true` in Ihrer Konfiguration setzen. Dies wird automatisch Ihre Sitzungsdaten nach jeder Anfrage bestätigen.

```php

$app->register('session', Session::class, [ 'pfad/zur/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Zusätzlich könnten Sie `Flight::after('start', function() { Flight::session()->commit(); });` ausführen, um Ihre Sitzungsdaten nach jeder Anfrage zu bestätigen.

## Dokumentation

Besuche die [Github Readme](https://github.com/Ghostff/Session) für die vollständige Dokumentation. Die Konfigurationsoptionen sind [im default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) selbst gut dokumentiert. Der Code ist einfach zu verstehen, wenn du das Paket selbst durchsuchen möchtest.