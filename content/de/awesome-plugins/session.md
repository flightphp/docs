# Geist/Session

PHP-Sitzungsverwaltung (nicht blockierend, Flash, Segment, Sitzungsverschlüsselung). Verwendet PHP open_ssl zur optionalen Verschlüsselung/Entschlüsselung von Sitzungsdaten. Unterstützt Datei, MySQL, Redis und Memcached.

Klicken Sie [hier](https://github.com/Geist/Session), um den Code anzuzeigen.

## Installation

Installation mit Composer.

```bash
composer require geist/session
```

## Grundkonfiguration

Es ist nicht erforderlich, etwas zu übergeben, um die Standardeinstellungen für Ihre Sitzung zu verwenden. Weitere Einstellungen finden Sie im [Github-Readme](https://github.com/Geist/Session).

```php

Verwendung von Geist\Session\Session;

Erfordert 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Sitzung::class);

// Eine Sache, die Sie beachten müssen, ist, dass Sie bei jedem Seitenaufruf Ihre Sitzung bestätigen müssen
// oder Sie müssen auto_commit in Ihrer Konfiguration ausführen.
```

## Einfaches Beispiel

Hier ist ein einfaches Beispiel, wie Sie dies verwenden könnten.

```php
Flight::route('POST /login', function() {
	$sitzung = Flight::session();

	// Führen Sie hier Ihre Anmelde-Logik aus
	// Passwort überprüfen, usw.

	// Wenn die Anmeldung erfolgreich ist
	$sitzung->set('is_logged_in', true);
	$sitzung->set('user', $user);

	// Jedes Mal, wenn Sie in die Sitzung schreiben, müssen Sie sie bewusst bestätigen.
	$sitzung->commit();
});

// Diese Überprüfung könnte in der Logik der eingeschränkten Seite sein oder mit Middleware umhüllt sein.
Flight::route('/some-restricted-page', function() {
	$sitzung = Flight::session();

	if(!$sitzung->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// Führen Sie hier Ihre Logik für die eingeschränkte Seite aus
});

// die Middleware-Version
Flight::route('/some-restricted-page', function() {
	// normale Seitenlogik
})->addMiddleware(function() {
	$sitzung = Flight::session();

	if(!$sitzung->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## Komplexeres Beispiel

Hier ist ein komplexeres Beispiel, wie Sie dies verwenden könnten.

```php

Verwendung von Geist\Session\Session;

Erfordert 'vendor/autoload.php';

$app = Flight::app();

// Setzen Sie einen benutzerdefinierten Pfad zu Ihrer Sitzungskonfigurationsdatei und geben Sie ihm eine Zufalls Zeichenfolge für die Sitzungs-ID
$app->register('session', Sitzung::class, [ 'pfad/zur/sitzungskonfig.php', bin2hex(random_bytes(32)) ], function(Sitzung $sitzung) {
		// oder Sie können Konfigurationsoptionen manuell überschreiben
		$sitzung->updateConfiguration([
			// Wenn Sie Ihre Sitzungsdaten in einer Datenbank speichern möchten (gut, wenn Sie etwas wie "Abmelden von allen Geräten" Funktion benötigen)
			Sitzung::CONFIG_DRIVER        => Geist\Session\Drivers\MySql::class,
			Sitzung::CONFIG_ENCRYPT_DATA  => true,
			Sitzung::CONFIG_SALT_KEY      => hash('sha256', 'mein-super-GEH3IM-salz'), // bitte ändern Sie dies in etwas anderes
			Sitzung::CONFIG_AUTO_COMMIT   => true, // Nur machen Sie das, wenn es erforderlich ist und/oder schwer ist, Ihre Sitzung zu commiten.
												   // zusätzlich könnten Sie machen Flight::after('start', function() { Flight::session()->commit(); });;
			Sitzung::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Datenbanktreiber für PDO-DNS z.B. (mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Datenbank-Host
				'db_name'   => 'meine_app_datenbank',   # Datenbankname
				'db_table'  => 'sitzungen',          # Datenbanktabelle
				'db_user'   => 'root',              # Datenbankbenutzername
				'db_pass'   => '',                  # Datenbankpasswort
				'persistent_conn'=> false,          # Vermeiden Sie den Overhead beim Aufbau einer neuen Verbindung, wenn ein Skript mit einer Datenbank sprechen muss, was zu einer schnelleren Webanwendung führt. FINDEN SIE DIE RÜCKSEITE SELBST
			]
		]);
	}
);
```

## Hilfe! Meine Sitzungsdaten bleiben nicht bestehen!

Wenn Sie Ihre Sitzungsdaten festlegen und sie zwischen Anfragen nicht bestehen bleiben, haben Sie möglicherweise vergessen, Ihre Sitzungsdaten zu bestätigen. Sie können dies tun, indem Sie nach dem Festlegen Ihrer Sitzungsdaten `$sitzung->commit()` aufrufen.

```php
Flight::route('POST /login', function() {
	$sitzung = Flight::session();

	// Führen Sie hier Ihre Anmelde-Logik aus
	// Passwort überprüfen, usw.

	// Wenn die Anmeldung erfolgreich ist
	$sitzung->set('is_logged_in', true);
	$sitzung->set('user', $user);

	// Jedes Mal, wenn Sie in die Sitzung schreiben, müssen Sie sie bewusst bestätigen.
	$sitzung->commit();
});
```

Der andere Weg, dies zu umgehen, ist, wenn Sie Ihren Sitzungsdienst einrichten, müssen Sie `auto_commit` in Ihrer Konfiguration auf `true` setzen. Dadurch werden Ihre Sitzungsdaten automatisch nach jeder Anfrage bestätigt.

```php

$app->register('session', Sitzung::class, [ 'pfad/zur/sitzungskonfig.php', bin2hex(random_bytes(32)) ], function(Sitzung $sitzung) {
		$sitzung->updateConfiguration([
			Sitzung::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Zusätzlich könnten Sie `Flight::after('start', function() { Flight::session()->commit(); });` verwenden, um Ihre Sitzungsdaten nach jeder Anfrage zu bestätigen.

## Dokumentation

Besuchen Sie das [Github-Readme](https://github.com/Geist/Session) für die vollständige Dokumentation. Die Konfigurationsoptionen sind [im default_config.php](https://github.com/Geist/Session/blob/master/src/default_config.php) selbst gut dokumentiert. Der Code ist einfach zu verstehen, wenn Sie dieses Paket selbst durchgehen möchten.