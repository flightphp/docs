# Konfiguration

Sie können bestimmte Verhaltensweisen von Flight anpassen, indem Sie Konfigurationswerte über die `set` Methode setzen.

```php
Flight::set('flight.log_errors', true);
```

Folgendes ist eine Liste aller verfügbaren Konfigurationseinstellungen:

- **flight.base_url** - Überschreiben Sie die Basis-URL der Anfrage. (Standard: null)
- **flight.case_sensitive** - Groß- und Kleinschreibung bei der URL-Übereinstimmung. (Standard: false)
- **flight.handle_errors** - Erlauben Sie Flight, alle Fehler intern zu behandeln. (Standard: true)
- **flight.log_errors** - Fehler in die Fehlerprotokolldatei des Webservers protokollieren. (Standard: false)
- **flight.views.path** - Verzeichnis, das Ansichtsvorlagendateien enthält. (Standard: ./views)
- **flight.views.extension** - Dateierweiterung für Ansichtsvorlagendateien. (Standard: .php)