# Konfiguration

Sie können bestimmte Verhaltensweisen von Flight anpassen, indem Sie Konfigurationswerte über die `set`-Methode festlegen.

```php
Flight::set('flight.log_errors', true);
```

## Verfügbare Konfigurationseinstellungen

Folgende Liste enthält alle verfügbaren Konfigurationseinstellungen:

- **flight.base_url** `?string` - Überschreibe die Basis-URL der Anfrage. (Standard: null)
- **flight.case_sensitive** `bool` - Groß-/Kleinschreibung bei URLs beachten. (Standard: false)
- **flight.handle_errors** `bool` - Ermögliche Flight, alle Fehler intern zu behandeln. (Standard: true)
- **flight.log_errors** `bool` - Fehler im Error-Log-Datei des Webservers protokollieren. (Standard: false)
- **flight.views.path** `string` - Verzeichnis, das Ansichtsvorlagendateien enthält. (Standard: ./views)
- **flight.views.extension** `string` - Dateierweiterung für Ansichtsvorlagen. (Standard: .php)
- **flight.content_length** `bool` - Setze den `Content-Length`-Header. (Standard: true)
- **flight.v2.output_buffering** `bool` - Verwende legacy Output-Pufferung. Siehe [Umstieg auf v3](migrating-to-v3). (Standard: false)

## Variablen

Flight ermöglicht es Ihnen, Variablen zu speichern, damit sie überall in Ihrer Anwendung genutzt werden können.

```php
// Speichere deine Variable
Flight::set('id', 123);

// Anderswo in deiner Anwendung
$id = Flight::get('id');
```

Um zu prüfen, ob eine Variable gesetzt wurde, können Sie Folgendes tun:

```php
if (Flight::has('id')) {
  // Etwas tun
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

Alle Fehler und Ausnahmen werden von Flight erfasst und an die `error`-Methode weitergeleitet. Das Standardverhalten besteht darin, eine allgemeine `HTTP 500 Internal Server Error`-Antwort mit einigen Fehlerinformationen zu senden.

Sie können dieses Verhalten nach Ihren eigenen Bedürfnissen überschreiben:

```php
Flight::map('error', function (Throwable $error) {
  // Fehler behandeln
  echo $error->getTraceAsString();
});
```

Standardmäßig werden Fehler nicht im Webserverprotokoll protokolliert. Sie können dies aktivieren, indem Sie die Konfiguration ändern:

```php
Flight::set('flight.log_errors', true);
```

### Nicht gefunden

Wenn eine URL nicht gefunden werden kann, ruft Flight die Methode `notFound` auf. Das Standardverhalten besteht darin, eine `HTTP 404 Not Found`-Antwort mit einer einfachen Meldung zu senden.

Sie können dieses Verhalten nach Ihren eigenen Bedürfnissen überschreiben:

```php
Flight::map('notFound', function () {
  // Nicht gefunden behandeln
});
```  