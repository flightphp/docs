# Konfiguration

Sie können bestimmte Verhaltensweisen von Flight anpassen, indem Sie Konfigurationswerte über die `set` Methode festlegen.

```php
Flight::set('flight.log_errors', true);
```

Folgendes ist eine Liste aller verfügbaren Konfigurationseinstellungen:

- **flight.base_url** - Überschreiben der Basis-URL der Anfrage. (Standard: null)
- **flight.case_sensitive** - Groß- und Kleinschreibung für URLs beachten. (Standard: false)
- **flight.handle_errors** - Erlauben, dass Flight alle Fehler intern behandelt. (Standard: true)
- **flight.log_errors** - Fehler in die Fehlerprotokolldatei des Webservers aufzeichnen. (Standard: false)
- **flight.views.path** - Verzeichnis mit Ansichtsvorlagendateien. (Standard: ./views)
- **flight.views.extension** - Dateierweiterung für Ansichtsvorlagen. (Standard: .php)