# Konfiguration

Sie können bestimmte Verhaltensweisen von Flight anpassen, indem Sie Konfigurationswerte über die `set` Methode setzen.

```php
Flight::set('flight.log_errors', true);
```

## Verfügbare Konfigurationseinstellungen

Folgend finden Sie eine Liste aller verfügbaren Konfigurationseinstellungen:

- **flight.base_url** `?string` - Überschreibt die Basis-URL der Anfrage. (Standard: null)
- **flight.case_sensitive** `bool` - Groß-/Kleinschreibung beachten bei URLs. (Standard: false)
- **flight.handle_errors** `bool` - Erlaubt Flight, alle Fehler intern zu behandeln. (Standard: true)
- **flight.log_errors** `bool` - Fehler ins Fehlerprotokoll der Webserver loggen. (Standard: false)
- **flight.views.path** `string` - Verzeichnis, das Ansichtsvorlagen enthält. (Standard: ./views)
- **flight.views.extension** `string` - Dateierweiterung der Ansichtsvorlagendatei. (Standard: .php)
- **flight.content_length** `bool` - Setzt den `Content-Length` Header. (Standard: true)
- **flight.v2.output_buffering** `bool` - Verwendung der veralteten Ausgabepufferung. Siehe [Migration zu v3](migrating-to-v3). (Standard: false)

## Variablen

Flight erlaubt es Ihnen, Variablen zu speichern, damit sie überall in Ihrer Anwendung verwendet werden können.

```php
// Speichern Sie Ihre Variable
Flight::set('id', 123);

// Anderswo in Ihrer Anwendung
$id = Flight::get('id');
```

Um zu überprüfen, ob eine Variable gesetzt wurde, können Sie Folgendes tun:

```php
if (Flight::has('id')) {
  // Etwas tun
}
```

Sie können eine Variable löschen, indem Sie Folgendes tun:

```php
// Löscht die id Variable
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

Alle Fehler und Ausnahmen werden von Flight abgefangen und an die `error` Methode übergeben. Das Standardverhalten besteht darin, eine generische `HTTP 500 Internal Server Error` Antwort mit einigen Fehlerinformationen zu senden.

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

Wenn eine URL nicht gefunden werden kann, ruft Flight die `notFound` Methode auf. Das Standardverhalten besteht darin, eine `HTTP 404 Not Found` Antwort mit einer einfachen Nachricht zu senden.

Sie können dieses Verhalten nach Ihren eigenen Bedürfnissen überschreiben:

```php
Flight::map('notFound', function () {
  // Nicht gefunden behandeln
});
```