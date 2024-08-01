# Konfiguration

Sie können bestimmte Verhaltensweisen von Flight anpassen, indem Sie Konfigurationswerte über die `set`-Methode festlegen.

```php
Flight::set('flight.log_errors', true);
```

## Verfügbare Konfigurationseinstellungen

Im Folgenden finden Sie eine Liste aller verfügbaren Konfigurationseinstellungen:

- **flight.base_url** `?string` - Überschreiben der Basis-URL der Anfrage. (Standard: null)
- **flight.case_sensitive** `bool` - Groß-/Kleinschreibung für URLs beachten. (Standard: false)
- **flight.handle_errors** `bool` - Erlauben, dass Flight alle Fehler intern behandelt. (Standard: true)
- **flight.log_errors** `bool` - Fehler in die Fehlerprotokolldatei des Webservers schreiben. (Standard: false)
- **flight.views.path** `string` - Verzeichnis mit Ansichtsvorlagendateien. (Standard: ./views)
- **flight.views.extension** `string` - Dateierweiterung für Ansichtsvorlagen. (Standard: .php)
- **flight.content_length** `bool` - Setzen des `Content-Length`-Headers. (Standard: true)
- **flight.v2.output_buffering** `bool` - Verwendung des veralteten Ausgabe-Pufferns. Siehe [Migration zu v3](migrating-to-v3). (Standard: false)

## Loader-Konfiguration

Zusätzlich gibt es eine weitere Konfigurationseinstellung für den Loader. Dies ermöglicht Ihnen das automatische Laden von Klassen mit `_` im Klassennamen.

```php
// Klassenladen mit Unterstrichen aktivieren
// Standardmäßig auf true gesetzt
Loader::$v2ClassLoading = false;
```

## Variablen

Flight ermöglicht es Ihnen, Variablen zu speichern, damit sie überall in Ihrer Anwendung verwendet werden können.

```php
// Speichern Sie Ihre Variable
Flight::set('id', 123);

// Anderswo in Ihrer Anwendung
$id = Flight::get('id');
```

Um festzustellen, ob eine Variable festgelegt wurde, können Sie Folgendes tun:

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

Alle Fehler und Ausnahmen werden von Flight abgefangen und an die `error`-Methode übergeben. Das Standardverhalten besteht darin, eine allgemeine `HTTP 500 Internal Server Error`-Antwort mit einigen Fehlerinformationen zu senden.

Sie können dieses Verhalten für Ihre eigenen Bedürfnisse überschreiben:

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

Sie können dieses Verhalten für Ihre eigenen Bedürfnisse überschreiben:

```php
Flight::map('notFound', function () {
  // Nicht gefunden behandeln
});
```