# Konfiguration

Sie können bestimmte Verhaltensweisen von Flight anpassen, indem Sie Konfigurationswerte über die `set`-Methode setzen.

```php
Flight::set('flight.log_errors', true);
```

## Verfügbare Konfigurationseinstellungen

Folgend finden Sie eine Liste aller verfügbaren Konfigurationseinstellungen:

- **flight.base_url** - Überschreiben der Basis-URL der Anfrage. (Standard: null)
- **flight.case_sensitive** - Groß-/Kleinschreibung beachten bei URLs. (Standard: false)
- **flight.handle_errors** - Ermöglichen, dass Flight alle Fehler intern behandelt. (Standard: true)
- **flight.log_errors** - Fehler im Fehlerprotokoll der Webserver loggen. (Standard: false)
- **flight.views.path** - Verzeichnis, das Ansichtsvorlagendateien enthält. (Standard: ./views)
- **flight.views.extension** - Ansichtsvorlagendateierweiterung. (Standard: .php)

## Variablen

Flight ermöglicht es Ihnen, Variablen zu speichern, damit sie überall in Ihrer Anwendung verwendet werden können.

```php
// Ihre Variable speichern
Flight::set('id', 123);

// Anderswo in Ihrer Anwendung
$id = Flight::get('id');
```

Um zu überprüfen, ob eine Variable festgelegt wurde, können Sie Folgendes tun:

```php
if (Flight::has('id')) {
  // Etwas machen
}
```

Sie können eine Variable löschen, indem Sie Folgendes tun:

```php
// Löscht die id-Variable
Flight::clear('id');

// Löscht alle Variablen
Flight::clear();
```

Flight verwendet auch Variablen für Konfigurationszwecke.

```php
Flight::set('flight.log_errors', true);
```

## Fehlerbehandlung

### Fehler und Ausnahmen

Alle Fehler und Ausnahmen werden von Flight abgefangen und an die `error`-Methode weitergeleitet.
Das Standardverhalten besteht darin, eine generische `HTTP 500 Internal Server Error`-Antwort mit einigen Fehlerinformationen zu senden.

Sie können dieses Verhalten nach Ihren eigenen Bedürfnissen überschreiben:

```php
Flight::map('error', function (Throwable $error) {
  // Fehler behandeln
  echo $error->getTraceAsString();
});
```

Standardmäßig werden Fehler nicht im Webserver protokolliert. Sie können dies aktivieren, indem Sie die Konfiguration ändern:

```php
Flight::set('flight.log_errors', true);
```

### Nicht gefunden

Wenn eine URL nicht gefunden werden kann, ruft Flight die `notFound`-Methode auf. Das Standardverhalten besteht darin, eine `HTTP 404 Not Found`-Antwort mit einer einfachen Nachricht zu senden.

Sie können dieses Verhalten nach Ihren eigenen Bedürfnissen überschreiben:

```php
Flight::map('notFound', function () {
  // Nicht gefunden behandeln
});
```