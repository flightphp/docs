# FlightPHP Session - Leichtgewichtiger Dateibasierter Session-Handler

Dies ist ein leichtgewichtiger, dateibasisierter Session-Handler-Plugin für das [Flight PHP Framework](https://docs.flightphp.com/). Es bietet eine einfache, aber leistungsstarke Lösung zur Verwaltung von Sessions, mit Funktionen wie nicht blockierendem Lesen von Sessions, optionaler Verschlüsselung, Auto-Commit-Funktionalität und einem Testmodus für die Entwicklung. Session-Daten werden in Dateien gespeichert, was es ideal für Anwendungen macht, die keine Datenbank benötigen.

Falls du eine Datenbank verwenden möchtest, schaue dir das [ghostff/session](/awesome-plugins/ghost-session) Plugin an, das viele der gleichen Funktionen bietet, aber mit einer Datenbank-Backend.

Besuche das [Github-Repository](https://github.com/flightphp/session) für den vollständigen Quellcode und Details.

## Installation

Installiere das Plugin über Composer:

```bash
composer require flightphp/session
```

## Grundlegende Nutzung

Hier ist ein einfaches Beispiel, wie du das `flightphp/session`-Plugin in deiner Flight-Anwendung verwendest:

```php
require 'vendor/autoload.php';

use flight\Session;

$app = Flight::app();

// Registriere den Session-Dienst
$app->register('session', Session::class);

// Beispiel-Route mit Session-Nutzung
Flight::route('/login', function() {
    $session = Flight::session();
    $session->set('user_id', 123);
    $session->set('username', 'johndoe');
    $session->set('is_admin', false);

    echo $session->get('username'); // Gibt aus: johndoe
    echo $session->get('preferences', 'default_theme'); // Gibt aus: default_theme

    if ($session->get('user_id')) {
        Flight::json(['message' => 'User is logged in!', 'user_id' => $session->get('user_id')]);
    }
});

Flight::route('/logout', function() {
    $session = Flight::session();
    $session->clear(); // Löschung aller Session-Daten
    Flight::json(['message' => 'Logged out successfully']);
});

Flight::start();
```

### Wichtige Punkte
- **Nicht blockierend**: Verwende `read_and_close` standardmäßig, um Probleme mit Session-Sperrungen zu vermeiden.
- **Auto-Commit**: Standardmäßig aktiviert, sodass Änderungen automatisch beim Herunterfahren gespeichert werden, es sei denn, es wird deaktiviert.
- **Dateispeicherung**: Sessions werden standardmäßig im System-Temp-Verzeichnis unter `/flight_sessions` gespeichert.

## Konfiguration

Du kannst den Session-Handler anpassen, indem du ein Array von Optionen beim Registrieren übergeben:

```php
// Ja, es ist ein doppeltes Array :)
$app->register('session', Session::class, [ [
    'save_path' => '/custom/path/to/sessions',         // Verzeichnis für Session-Dateien
	'prefix' => 'myapp_',                              // Präfix für Session-Dateien
    'encryption_key' => 'a-secure-32-byte-key-here',   // Verschlüsselung aktivieren (32 Bytes empfohlen für AES-256-CBC)
    'auto_commit' => false,                            // Auto-Commit deaktivieren für manuelle Kontrolle
    'start_session' => true,                           // Session automatisch starten (Standard: true)
    'test_mode' => false,                              // Testmodus für die Entwicklung aktivieren
    'serialization' => 'json',                         // Serialisierungs-Methode: 'json' (Standard) oder 'php' (Legacy)
] ]);
```

### Konfigurationsoptionen
| Option            | Beschreibung                                      | Standardwert                     |
|-------------------|--------------------------------------------------|-----------------------------------|
| `save_path`       | Verzeichnis, in dem Session-Dateien gespeichert werden         | `sys_get_temp_dir() . '/flight_sessions'` |
| `prefix`          | Präfix für die gespeicherte Session-Datei                | `sess_`                           |
| `encryption_key`  | Schlüssel für AES-256-CBC-Verschlüsselung (optional)        | `null` (keine Verschlüsselung)            |
| `auto_commit`     | Automatische Speicherung von Session-Daten beim Herunterfahren               | `true`                            |
| `start_session`   | Session automatisch starten                  | `true`                            |
| `test_mode`       | Im Testmodus ausführen, ohne PHP-Sessions zu beeinflussen  | `false`                           |
| `test_session_id` | Benutzerdefinierte Session-ID für den Testmodus (optional)       | Zufällig generiert, wenn nicht gesetzt     |
| `serialization`   | Serialisierungs-Methode: 'json' (Standard, sicher) oder 'php' (Legacy, erlaubt Objekte) | `'json'` |

## Serialisierungsmodi

Standardmäßig verwendet diese Bibliothek **JSON-Serialisierung** für Session-Daten, was sicher ist und PHP-Objekt-Injektions-Schwachstellen verhindert. Wenn du PHP-Objekte in der Session speichern musst (nicht empfohlen für die meisten Apps), kannst du auf die Legacy-PHP-Serialisierung umschalten:

- `'serialization' => 'json'` (Standard):
  - Nur Arrays und Primitive sind in Session-Daten erlaubt.
  - Sicherer: Immun gegen PHP-Objekt-Injection.
  - Dateien werden mit `J` (einfaches JSON) oder `F` (verschlüsseltes JSON) präfixiert.
- `'serialization' => 'php'`:
  - Erlaubt das Speichern von PHP-Objekten (mit Vorsicht verwenden).
  - Dateien werden mit `P` (einfaches PHP-Serialisieren) oder `E` (verschlüsseltes PHP-Serialisieren) präfixiert.

**Hinweis:** Wenn du JSON-Serialisierung verwendest, wirft das Versuch, ein Objekt zu speichern, eine Ausnahme.

## Erweiterte Nutzung

### Manuelles Commit
Wenn du Auto-Commit deaktivierst, musst du Änderungen manuell speichern:

```php
$app->register('session', Session::class, ['auto_commit' => false]);

Flight::route('/update', function() {
    $session = Flight::session();
    $session->set('key', 'value');
    $session->commit(); // Änderungen explizit speichern
});
```

### Session-Sicherheit mit Verschlüsselung
Aktiviere Verschlüsselung für sensible Daten:

```php
$app->register('session', Session::class, [
    'encryption_key' => 'your-32-byte-secret-key-here'
]);

Flight::route('/secure', function() {
    $session = Flight::session();
    $session->set('credit_card', '4111-1111-1111-1111'); // Wird automatisch verschlüsselt
    echo $session->get('credit_card'); // Wird beim Abruf entschlüsselt
});
```

### Session-Regeneration
Regeneriere die Session-ID für Sicherheit (z. B. nach dem Login):

```php
Flight::route('/post-login', function() {
    $session = Flight::session();
    $session->regenerate(); // Neue ID, Daten behalten
    // ODER
    $session->regenerate(true); // Neue ID, alte Daten löschen
});
```

### Middleware-Beispiel
Schütze Routen mit sessionbasierter Authentifizierung:

```php
Flight::route('/admin', function() {
    Flight::json(['message' => 'Welcome to the admin panel']);
})->addMiddleware(function() {
    $session = Flight::session();
    if (!$session->get('is_admin')) {
        Flight::halt(403, 'Access denied');
    }
});
```

Dies ist nur ein einfaches Beispiel für die Nutzung in Middleware. Für ein detaillierteres Beispiel, siehe die [Middleware](/learn/middleware)-Dokumentation.

## Methoden

Die `Session`-Klasse bietet diese Methoden:

- `set(string $key, $value)`: Speichert einen Wert in der Session.
- `get(string $key, $default = null)`: Ruft einen Wert ab, mit einem optionalen Standardwert, falls der Schlüssel nicht existiert.
- `delete(string $key)`: Entfernt einen bestimmten Schlüssel aus der Session.
- `clear()`: Löscht alle Session-Daten, behält aber den gleichen Dateinamen für die Session.
- `commit()`: Speichert die aktuellen Session-Daten im Dateisystem.
- `id()`: Gibt die aktuelle Session-ID zurück.
- `regenerate(bool $deleteOldFile = false)`: Regeneriert die Session-ID inklusive Erstellen einer neuen Session-Datei, behält alle alten Daten und die alte Datei bleibt im System. Wenn `$deleteOldFile` `true` ist, wird die alte Session-Datei gelöscht.
- `destroy(string $id)`: Zerstört eine Session anhand der ID und löscht die Session-Datei aus dem System. Dies ist Teil des `SessionHandlerInterface` und `$id` ist erforderlich. Typische Nutzung wäre `$session->destroy($session->id())`.
- `getAll()` : Gibt alle Daten der aktuellen Session zurück.

Alle Methoden außer `get()` und `id()` geben die `Session`-Instanz für Kettenaufrufe zurück.

## Warum dieses Plugin verwenden?

- **Leichtgewichtig**: Keine externen Abhängigkeiten – nur Dateien.
- **Nicht blockierend**: Vermeidet Session-Sperrungen mit `read_and_close` standardmäßig.
- **Sicher**: Unterstützt AES-256-CBC-Verschlüsselung für sensible Daten.
- **Flexibel**: Optionen für Auto-Commit, Testmodus und manuelle Kontrolle.
- **Flight-Native**: Speziell für das Flight-Framework entwickelt.

## Technische Details

- **Speicherformat**: Session-Dateien werden mit `sess_` präfixiert und im konfigurierten `save_path` gespeichert. Dateiinhalts-Präfixe:
  - `J`: Einfaches JSON (Standard, keine Verschlüsselung)
  - `F`: Verschlüsseltes JSON (Standard mit Verschlüsselung)
  - `P`: Einfaches PHP-Serialisieren (Legacy, keine Verschlüsselung)
  - `E`: Verschlüsseltes PHP-Serialisieren (Legacy mit Verschlüsselung)
- **Verschlüsselung**: Verwende AES-256-CBC mit einem zufälligen IV pro Session-Schreibvorgang, wenn ein `encryption_key` angegeben ist. Verschlüsselung funktioniert für beide JSON- und PHP-Serialisierungsmodi.
- **Serialisierung**: JSON ist die Standard- und sicherste Methode. PHP-Serialisierung ist für Legacy/fortgeschrittene Nutzung verfügbar, ist aber weniger sicher.
- **Garbage Collection**: Implementiert PHP’s `SessionHandlerInterface::gc()`, um abgelaufene Sessions zu bereinigen.

## Beitrag

Beiträge sind willkommen! Forke das [Repository](https://github.com/flightphp/session), mache deine Änderungen und reiche einen Pull-Request ein. Melde Fehler oder schlage Features über den Github-Issue-Tracker vor.

## Lizenz

Dieses Plugin ist unter der MIT-Lizenz lizenziert. Siehe das [Github-Repository](https://github.com/flightphp/session) für Details.