# Konfiguration

## Überblick

Flight bietet eine einfache Möglichkeit, verschiedene Aspekte des Frameworks an die Bedürfnisse Ihrer Anwendung anzupassen. Einige werden standardmäßig festgelegt, aber Sie können sie bei Bedarf überschreiben. Sie können auch eigene Variablen festlegen, die in Ihrer gesamten Anwendung verwendet werden können.

## Verständnis

Sie können bestimmte Verhaltensweisen von Flight anpassen, indem Sie Konfigurationswerte über die `set`-Methode festlegen.

```php
Flight::set('flight.log_errors', true);
```

In der Datei `app/config/config.php` können Sie alle standardmäßigen Konfigurationsvariablen sehen, die Ihnen zur Verfügung stehen.

## Grundlegende Verwendung

### Flight-Konfigurationsoptionen

Die folgende Liste enthält alle verfügbaren Konfigurationseinstellungen:

- **flight.base_url** `?string` - Überschreibt die Basis-URL der Anfrage, wenn Flight in einem Unterverzeichnis läuft. (Standard: null)
- **flight.case_sensitive** `bool` - Groß-/Kleinschreibungssensible Übereinstimmung für URLs. (Standard: false)
- **flight.handle_errors** `bool` - Erlaubt Flight, alle Fehler intern zu behandeln. (Standard: true)
  - Wenn Sie möchten, dass Flight Fehler anstelle des standardmäßigen PHP-Verhaltens behandelt, muss dies auf true gesetzt werden.
  - Wenn Sie [Tracy](/awesome-plugins/tracy) installiert haben, sollten Sie dies auf false setzen, damit Tracy Fehler behandeln kann.
  - Wenn Sie das [APM](/awesome-plugins/apm)-Plugin installiert haben, sollten Sie dies auf true setzen, damit das APM die Fehler protokollieren kann.
- **flight.log_errors** `bool` - Fehler in die Fehlerprotokolldatei des Webservers protokollieren. (Standard: false)
  - Wenn Sie [Tracy](/awesome-plugins/tracy) installiert haben, protokolliert Tracy Fehler basierend auf den Tracy-Konfigurationen, nicht basierend auf dieser Konfiguration.
- **flight.views.path** `string` - Verzeichnis, das View-Template-Dateien enthält. (Standard: ./views)
- **flight.views.extension** `string` - Dateierweiterung für View-Template-Dateien. (Standard: .php)
- **flight.content_length** `bool` - Den `Content-Length`-Header setzen. (Standard: true)
  - Wenn Sie [Tracy](/awesome-plugins/tracy) verwenden, muss dies auf false gesetzt werden, damit Tracy korrekt gerendert werden kann.
- **flight.v2.output_buffering** `bool` - Legacy-Output-Buffering verwenden. Siehe [Migration zu v3](migrating-to-v3). (Standard: false)

### Loader-Konfiguration

Es gibt zusätzlich eine weitere Konfigurationseinstellung für den Loader. Dies ermöglicht es Ihnen, Klassen mit `_` im Klassennamen automatisch zu laden.

```php
// Aktiviere Klassenladen mit Unterstrichen
// Standardmäßig true
Loader::$v2ClassLoading = false;
```

### Variablen

Flight ermöglicht es Ihnen, Variablen zu speichern, damit sie überall in Ihrer Anwendung verwendet werden können.

```php
// Speichern Sie Ihre Variable
Flight::set('id', 123);

// An anderer Stelle in Ihrer Anwendung
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
// Löscht die id-Variable
Flight::clear('id');

// Löscht alle Variablen
Flight::clear();
```

> **Hinweis:** Nur weil Sie eine Variable setzen können, bedeutet das nicht, dass Sie es tun sollten. Verwenden Sie diese Funktion sparsam. Der Grund dafür ist, dass alles, was hier gespeichert wird, zu einer globalen Variable wird. Globale Variablen sind schlecht, weil sie von überall in Ihrer Anwendung geändert werden können, was es schwierig macht, Fehler zu finden. Zusätzlich kann dies Dinge wie [Unit-Testing](/guides/unit-testing) komplizieren.

### Fehler und Ausnahmen

Alle Fehler und Ausnahmen werden von Flight abgefangen und an die `error`-Methode weitergeleitet, wenn `flight.handle_errors` auf true gesetzt ist.

Das standardmäßige Verhalten ist das Senden einer generischen `HTTP 500 Internal Server Error`-Antwort mit einigen Fehlerinformationen.

Sie können [dieses Verhalten](/learn/extending) für Ihre eigenen Bedürfnisse überschreiben:

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

#### 404 Nicht gefunden

Wenn eine URL nicht gefunden werden kann, ruft Flight die `notFound`-Methode auf. Das standardmäßige Verhalten ist das Senden einer `HTTP 404 Not Found`-Antwort mit einer einfachen Nachricht.

Sie können [dieses Verhalten](/learn/extending) für Ihre eigenen Bedürfnisse überschreiben:

```php
Flight::map('notFound', function () {
  // Nicht gefunden behandeln
});
```

## Siehe auch
- [Flight erweitern](/learn/extending) - Wie Sie die Kernfunktionalität von Flight erweitern und anpassen können.
- [Unit-Testing](/guides/unit-testing) - Wie Sie Unit-Tests für Ihre Flight-Anwendung schreiben.
- [Tracy](/awesome-plugins/tracy) - Ein Plugin für erweiterte Fehlerbehandlung und Debugging.
- [Tracy-Erweiterungen](/awesome-plugins/tracy_extensions) - Erweiterungen zur Integration von Tracy mit Flight.
- [APM](/awesome-plugins/apm) - Ein Plugin für Anwendungsleistungsüberwachung und Fehlerverfolgung.

## Fehlerbehebung
- Wenn Sie Probleme haben, alle Werte Ihrer Konfiguration herauszufinden, können Sie `var_dump(Flight::get());` ausführen.

## Änderungsprotokoll
- v3.5.0 - Konfiguration für `flight.v2.output_buffering` hinzugefügt, um das Legacy-Output-Buffering-Verhalten zu unterstützen.
- v2.0 - Kernkonfigurationen hinzugefügt.