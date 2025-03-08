# FlightPHP Sitzung - Leichtgewichtiger dateibasiierter Sitzungs-Handler

Dies ist ein leichtgewichtiger, dateibasierter Sitzungs-Handler-Plugin für das [Flight PHP Framework](https://docs.flightphp.com/). Es bietet eine einfache, aber leistungsstarke Lösung zum Verwalten von Sitzungen, mit Funktionen wie nicht-blockierenden Sitzungslesungen, optionaler Verschlüsselung, Auto-Commit-Funktion und einem Testmodus für die Entwicklung. Sitzungsdaten werden in Dateien gespeichert, was es ideal für Anwendungen macht, die keine Datenbank benötigen.

Wenn Sie eine Datenbank verwenden möchten, sehen Sie sich das [ghostff/session](/awesome-plugins/ghost-session) Plugin an, das viele dieser gleichen Funktionen, aber mit einem Datenbank-Backend bietet.

Besuchen Sie das [Github-Repository](https://github.com/flightphp/session) für den vollständigen Quellcode und Details.

## Installation

Installieren Sie das Plugin über Composer:

```bash
composer require flightphp/session
```

## Grundlegende Verwendung

Hier ist ein einfaches Beispiel, wie man das `flightphp/session` Plugin in Ihrer Flight-Anwendung verwendet:

```php
require 'vendor/autoload.php';

use flight\Session;

$app = Flight::app();

// Registrieren Sie den Sitzungsdienst
$app->register('session', Session::class);

// Beispielroute mit Sitzungsverwendung
Flight::route('/login', function() {
    $session = Flight::session();
    $session->set('user_id', 123);
    $session->set('username', 'johndoe');
    $session->set('is_admin', false);

    echo $session->get('username'); // Gibt aus: johndoe
    echo $session->get('preferences', 'default_theme'); // Gibt aus: default_theme

    if ($session->get('user_id')) {
        Flight::json(['message' => 'Benutzer ist angemeldet!', 'user_id' => $session->get('user_id')]);
    }
});

Flight::route('/logout', function() {
    $session = Flight::session();
    $session->clear(); // Löscht alle Sitzungsdaten
    Flight::json(['message' => 'Erfolgreich abgemeldet']);
});

Flight::start();
```

### Wichtige Punkte
- **Nicht-Blockierend**: Verwendet `read_and_close` für den Sitzungsstart standardmäßig und verhindert so Sitzungsblockierungsprobleme.
- **Auto-Commit**: Standardmäßig aktiviert, sodass Änderungen automatisch beim Herunterfahren gespeichert werden, es sei denn, es wird deaktiviert.
- **Dateispeicherung**: Sitzungen werden im standardmäßigen System-Temp-Verzeichnis unter `/flight_sessions` gespeichert.

## Konfiguration

Sie können den Sitzungs-Handler anpassen, indem Sie ein Array von Optionen beim Registrieren übergeben:

```php
$app->register('session', Session::class, [
    'save_path' => '/custom/path/to/sessions',         // Verzeichnis für Sitzungsdateien
    'encryption_key' => 'a-secure-32-byte-key-here',   // Verschlüsselung aktivieren (32 Bytes empfohlen für AES-256-CBC)
    'auto_commit' => false,                            // Auto-Commit für manuelle Kontrolle deaktivieren
    'start_session' => true,                           // Sitzung automatisch starten (Standard: true)
    'test_mode' => false                               // Testmodus für die Entwicklung aktivieren
]);
```

### Konfigurationsoptionen
| Option            | Beschreibung                                      | Standardwert                     |
|-------------------|--------------------------------------------------|-----------------------------------|
| `save_path`       | Verzeichnis, in dem Sitzungsdateien gespeichert werden | `sys_get_temp_dir() . '/flight_sessions'` |
| `encryption_key`  | Schlüssel für die AES-256-CBC-Verschlüsselung (optional) | `null` (keine Verschlüsselung)   |
| `auto_commit`     | Automatisches Speichern von Sitzungsdaten beim Herunterfahren | `true`                            |
| `start_session`   | Sitzung automatisch starten                      | `true`                            |
| `test_mode`       | Im Testmodus ausführen, ohne PHP-Sitzungen zu beeinflussen | `false`                           |
| `test_session_id` | Benutzerdefinierte Sitzungs-ID für den Testmodus (optional) | Zufällig generiert, falls nicht festgelegt |

## Erweiterte Verwendung

### Manuelles Commit
Wenn Sie Auto-Commit deaktivieren, müssen Sie die Änderungen manuell committen:

```php
$app->register('session', Session::class, ['auto_commit' => false]);

Flight::route('/update', function() {
    $session = Flight::session();
    $session->set('key', 'value');
    $session->commit(); // Änderungen explizit speichern
});
```

### Sitzungssicherheit mit Verschlüsselung
Aktivieren Sie die Verschlüsselung für sensible Daten:

```php
$app->register('session', Session::class, [
    'encryption_key' => 'your-32-byte-secret-key-here'
]);

Flight::route('/secure', function() {
    $session = Flight::session();
    $session->set('credit_card', '4111-1111-1111-1111'); // Automatisch verschlüsselt
    echo $session->get('credit_card'); // Bei der Abfrage entschlüsselt
});
```

### Sitzungsregeneration
Regenerieren Sie die Sitzungs-ID zur Sicherheit (z.B. nach dem Login):

```php
Flight::route('/post-login', function() {
    $session = Flight::session();
    $session->regenerate(); // Neue ID, Daten behalten
    // ODER
    $session->regenerate(true); // Neue ID, alte Daten löschen
});
```

### Middleware-Beispiel
Schützen Sie Routen mit sitzungsbasierter Authentifizierung:

```php
Flight::route('/admin', function() {
    Flight::json(['message' => 'Willkommen im Admin-Panel']);
})->addMiddleware(function() {
    $session = Flight::session();
    if (!$session->get('is_admin')) {
        Flight::halt(403, 'Zugriff verweigert');
    }
});
```

Dies ist nur ein einfaches Beispiel, wie man dies in Middleware verwendet. Für ein umfassenderes Beispiel siehe die [Middleware](/learn/middleware) Dokumentation.

## Methoden

Die `Session` Klasse bietet diese Methoden:

- `set(string $key, $value)`: Speichert einen Wert in der Sitzung.
- `get(string $key, $default = null)`: Ruft einen Wert ab, mit einem optionalen Standardwert, falls der Schlüssel nicht existiert.
- `delete(string $key)`: Entfernt einen bestimmten Schlüssel aus der Sitzung.
- `clear()`: Löscht alle Sitzungsdaten.
- `commit()`: Speichert die aktuellen Sitzungsdaten im Dateisystem.
- `id()`: Gibt die aktuelle Sitzungs-ID zurück.
- `regenerate(bool $deleteOld = false)`: Regeneriert die Sitzungs-ID und löscht optional alte Daten.

Alle Methoden außer `get()` und `id()` geben die `Session`-Instanz für das Chaining zurück.

## Warum dieses Plugin verwenden?

- **Leichtgewichtig**: Keine externen Abhängigkeiten – nur Dateien.
- **Nicht-Blockierend**: Vermeidet Sitzungsblockierungen mit `read_and_close` standardmäßig.
- **Sicher**: Unterstützt AES-256-CBC-Verschlüsselung für sensible Daten.
- **Flexibel**: Auto-Commit, Testmodus und Optionen für manuelle Kontrolle.
- **Flight-Native**: Speziell für das Flight-Framework entwickelt.

## Technische Details

- **Speicherformat**: Sitzungsdateien sind mit `sess_` vorangestellt und im konfigurierten `save_path` gespeichert. Verschlüsselte Daten verwenden ein `E`-Präfix, Klartext verwendet `P`.
- **Verschlüsselung**: Verwendet AES-256-CBC mit einer zufälligen IV pro Sitzungsbeschriftung, wenn ein `encryption_key` bereitgestellt wird.
- **Garbage Collection**: Implementiert PHPs `SessionHandlerInterface::gc()`, um abgelaufene Sitzungen zu bereinigen.

## Mitwirken

Beiträge sind willkommen! Forken Sie das [Repository](https://github.com/flightphp/session), nehmen Sie Ihre Änderungen vor und senden Sie eine Pull-Anfrage. Melden Sie Fehler oder schlagen Sie Funktionen über den Github-Fehlerverfolger vor.

## Lizenz

Dieses Plugin ist unter der MIT-Lizenz lizenziert. Siehe das [Github-Repository](https://github.com/flightphp/session) für Details.