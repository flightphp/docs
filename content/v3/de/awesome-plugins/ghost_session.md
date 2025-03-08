# Ghostff/Session

PHP-Sitzungsmanager (nicht-blockierend, Flash, Segment, Sitzungsverschlüsselung). Verwendet PHP open_ssl für optionale Verschlüsselung/Entschlüsselung von Sitzungsdaten. Unterstützt Datei, MySQL, Redis und Memcached.

Klicken Sie [hier](https://github.com/Ghostff/Session), um den Code zu sehen.

## Installation

Installieren Sie mit Composer.

```bash
composer require ghostff/session
```

## Grundkonfiguration

Sie sind nicht verpflichtet, etwas zu übergeben, um die Standardeinstellungen mit Ihrer Sitzung zu verwenden. Sie können mehr über die Einstellungen im [Github Readme](https://github.com/Ghostff/Session) lesen.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// Eine Sache, die Sie sich merken müssen, ist, dass Sie Ihre Sitzung bei jedem Seitenaufruf bestätigen müssen
// oder Sie müssen auto_commit in Ihrer Konfiguration aktivieren. 
```

## Einfaches Beispiel

Hier ist ein einfaches Beispiel, wie Sie dies verwenden könnten.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// Führen Sie hier Ihre Anmelde-Logik durch
	// Passwort validieren, usw.

	// Wenn die Anmeldung erfolgreich ist
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// Jedes Mal, wenn Sie in die Sitzung schreiben, müssen Sie es absichtlich bestätigen.
	$session->commit();
});

// Diese Überprüfung könnte in der Logik der eingeschränkten Seite sein, oder mit Middleware umhüllt werden.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// Führen Sie hier Ihre Logik für die eingeschränkte Seite durch
});

// die Middleware-Version
Flight::route('/some-restricted-page', function() {
	// reguläre Seitenlogik
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

// Legen Sie einen benutzerdefinierten Pfad zu Ihrer Sitzungskonfigurationsdatei fest und geben Sie ihr eine zufällige Zeichenfolge für die Sitzungs-ID
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// oder Sie können manuell Konfigurationsoptionen überschreiben
		$session->updateConfiguration([
			// Wenn Sie Ihre Sitzungsdaten in einer Datenbank speichern möchten (gut, wenn Sie etwas wie "von allen Geräten abmelden" Funktionalität möchten)
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // Bitte ändern Sie dies in etwas anderes
			Session::CONFIG_AUTO_COMMIT   => true, // Tun Sie dies nur, wenn es erforderlich ist und/oder es schwierig ist, Ihre Sitzung zu committen.
												   // Zusätzlich könnten Sie Flight::after('start', function() { Flight::session()->commit(); }); tun
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Datenbanktreiber für PDO-DNS z.B. (mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Datenbankhost
				'db_name'   => 'my_app_database',   # Datenbankname
				'db_table'  => 'sessions',          # Datenbanktabelle
				'db_user'   => 'root',              # Datenbankbenutzername
				'db_pass'   => '',                  # Datenbankpasswort
				'persistent_conn'=> false,          # Verhindern Sie die Überlastung durch die Herstellung einer neuen Verbindung jedes Mal, wenn ein Skript mit einer Datenbank kommunizieren muss, was zu einer schnelleren Webanwendung führt. FINDEN SIE DIE RÜCKSEITE SELBST
			]
		]);
	}
);
```

## Hilfe! Meine Sitzungsdaten werden nicht gespeichert!

Setzen Sie Ihre Sitzungsdaten und sie werden nicht zwischen den Anforderungen gespeichert? Sie haben vielleicht vergessen, Ihre Sitzungsdaten zu bestätigen. Sie können dies tun, indem Sie `$session->commit()` aufrufen, nachdem Sie Ihre Sitzungsdaten gesetzt haben.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// Führen Sie hier Ihre Anmelde-Logik durch
	// Passwort validieren, usw.

	// Wenn die Anmeldung erfolgreich ist
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// Jedes Mal, wenn Sie in die Sitzung schreiben, müssen Sie es absichtlich bestätigen.
	$session->commit();
});
```

Eine andere Möglichkeit ist, wenn Sie Ihren Sitzungsdienst einrichten, müssen Sie `auto_commit` auf `true` in Ihrer Konfiguration setzen. Dadurch werden Ihre Sitzungsdaten automatisch nach jeder Anforderung bestätigt.

```php

$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Zusätzlich könnten Sie `Flight::after('start', function() { Flight::session()->commit(); });` tun, um Ihre Sitzungsdaten nach jeder Anfrage zu bestätigen.

## Dokumentation

Besuchen Sie das [Github Readme](https://github.com/Ghostff/Session) für die vollständige Dokumentation. Die Konfigurationsoptionen sind [gut dokumentiert in der default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) Datei selbst. Der Code ist einfach zu verstehen, wenn Sie dieses Paket selbst durchsehen möchten.