# Konfiguration

## Überblick

Flight bietet eine einfache Möglichkeit, verschiedene Aspekte des Frameworks an die Bedürfnisse Ihrer Anwendung anzupassen. Einige sind standardmäßig festgelegt, aber Sie können sie bei Bedarf überschreiben. Sie können auch eigene Variablen festlegen, die in Ihrer gesamten Anwendung verwendet werden können.

Eine klare, gestufte Konfiguration (Datei-Standardwerte + Umgebungsgeheimnisse) hilft auch [KI-Codierungstools](/learn/ai): Agenten lernen einen Ort für Literale und einen Ort für Geheimnisse kennen, anstatt `$_ENV`-Zugriffe in Controllern zu erfinden.

## Verständnis

Sie können bestimmte Verhaltensweisen von Flight anpassen, indem Sie Konfigurationswerte über die `set`-Methode festlegen.

```php
Flight::set('flight.log_errors', true);
```

In einer strukturierten Anwendung (einschließlich des [Skeletons](https://github.com/flightphp/skeleton)) laden Sie normalerweise Projekteinstellungen aus `app/config/config.php` und wenden dann relevante Schlüssel auf die Engine an (z. B. `flight.base_url`, `flight.views.path`). Sie können auch ein kleines Konfigurationsobjekt in Controller injizieren, anstatt überall Globale zu lesen – freundlicher für Tests und für Agenten, die `AGENTS.md` folgen.

## Grundlegende Verwendung

### Flight-Konfigurationsoptionen

Im Folgenden finden Sie eine Liste aller verfügbaren Konfigurationseinstellungen:

- **flight.base_url** `?string` - Überschreibt die Basis-URL der Anfrage, wenn Flight in einem Unterverzeichnis läuft. (Standard: null)
- **flight.case_sensitive** `bool` - Berücksichtigung der Groß-/Kleinschreibung bei der URL-Zuordnung. (Standard: false)
- **flight.handle_errors** `bool` - Ermöglicht Flight, alle Fehler intern zu behandeln. (Standard: true)
  - Wenn Flight die Fehler anstelle des standardmäßigen PHP-Verhaltens behandeln soll, muss dies auf true gesetzt sein.
  - Wenn Sie [Tracy](/awesome-plugins/tracy) installiert haben, sollten Sie dies auf false setzen, damit Tracy Fehler behandeln kann.
  - Wenn Sie das [APM](/awesome-plugins/apm)-Plugin installiert haben, sollten Sie dies auf true setzen, damit das APM die Fehler protokollieren kann.
- **flight.log_errors** `bool` - Fehler in die Fehlerprotokolldatei des Webservers schreiben. (Standard: false)
  - Wenn Sie [Tracy](/awesome-plugins/tracy) installiert haben, protokolliert Tracy Fehler basierend auf den Tracy-Konfigurationen, nicht auf dieser Konfiguration.
- **flight.debug** `bool` - Gibt detaillierte Fehlerinformationen (Ausnahmemeldung, Code und Stack-Trace) im Browser aus, wenn ein Fehler auftritt. (Standard: false)
  - **Aktivieren Sie dies niemals in der Produktion** – es gibt interne Anwendungsdetails preis. Verwenden Sie es nur für die lokale Entwicklung oder Staging.
  - Wenn `false`, wird stattdessen eine generische `500 Internal Server Error`-Antwort angezeigt. Kombinieren Sie dies mit `flight.log_errors`, um Fehler serverseitig zu erfassen.
- **flight.allow_method_override** `bool` - Ermöglicht das Überschreiben der HTTP-Methode über den `X-HTTP-Method-Override`-Anfrageheader oder ein `_method`-Feld im POST-Body. (Standard: true)
  - **Es wird empfohlen, dies auf `false` zu setzen** für Anwendungen, die kein auf HTML-Formularen basierendes Methodenspoofing benötigen, da es Clients daran hindert, `DELETE`- oder `PUT`-Anfragen über ein Standard-POST-Formular zu fälschen.
  - Siehe [Sicherheit](/learn/security#flight-configuration-hardening) für weitere Details.
- **flight.views.path** `string` - Verzeichnis, das View-Template-Dateien enthält. (Standard: ./views)
- **flight.views.extension** `string` - Dateierweiterung der View-Templates. (Standard: `.php`; das offizielle Skeleton setzt dies bei Verwendung von Twig auf `.twig`)
- **flight.views.restrict_to_path** `bool` - Wenn `true`, akzeptiert der native `View` von Flight nur Template-Dateien, die innerhalb von `flight.views.path` aufgelöst werden. (Standard: `false`). **Aktivieren Sie dies** für Anwendungen, die native Views verwenden. Siehe [Sicherheit](/learn/security#flightviewsrestrict_to_path).
- **flight.content_length** `bool` - Setzt den `Content-Length`-Header. (Standard: true)
  - Wenn Sie [Tracy](/awesome-plugins/tracy) verwenden, muss dies auf false gesetzt werden, damit Tracy korrekt rendern kann.
- **flight.v2.output_buffering** `bool` - Verwendet die veraltete Ausgabepufferung. Siehe [Migration zu v3](migrating-to-v3). (Standard: false)

### Loader-Konfiguration

Es gibt zusätzlich eine weitere Konfigurationseinstellung für den Loader. Diese ermöglicht es Ihnen, Klassen mit `_` im Klassennamen automatisch zu laden.

```php
// Klassenladung mit Unterstrichen aktivieren
// Standardmäßig true
Loader::$v2ClassLoading = false;
```

Denken Sie daran, dass [automatisches Laden](/learn/autoloading) auch von der **Groß-/Kleinschreibung der Ordner** abhängt, die mit Ihren Namespaces übereinstimmen muss – insbesondere mit dem `App\`- + `app/Controller/`-Layout des Skeletons.

### Projektkonfiguration und `.env` (Skeleton-Muster)

Der Flight-Kern erfordert keine `.env`-Dateien. Viele Anwendungen verwenden nur ein PHP-Konfigurationsarray. Das offizielle Skeleton schichtet die Konfiguration so, dass Geheimnisse nicht in Git gelangen, während Runway weiterhin **literale** Konfiguration sicher neu schreiben kann:

1. **`.env` / echte Umgebung** – Geheimnisse und Bereitstellungs-Overrides (gitignoriert).
2. **`app/config/config.php`** – literale PHP-Array-Standardwerte (kopiert aus `config_sample.php`). Bevorzugen Sie **keine** `$_ENV[...]`-Ausdrücke in dieser Datei: Tools wie `runway config:set` können sie als statische Werte neu schreiben und Geheimnisse in die Datei einbrennen.
3. **Zusammenführen beim Bootstrap** – Umgebungsvariablen haben Vorrang bei zugeordneten Schlüsseln; Anwendungscode liest ein Konfigurationsobjekt oder `$app->get()`, nicht `$_ENV` in Controllern.

Beispielstruktur von `config_sample.php` / `config.php` (vereinfacht):

```php
<?php
// Nur Literale – Geheimnisse gehören in .env für den Skeleton-Workflow
return [
	'app' => [
		'env' => 'development',
		'debug' => true,
		'base_url' => '/',
		'timezone' => 'UTC',
	],
	'database' => [
		'driver' => 'sqlite', // oder mysql, oder '' zum Deaktivieren
		'host' => 'localhost',
		'dbname' => '',
		'user' => '',
		'password' => '',
		'file_path' => __DIR__ . '/../../database.sqlite',
	],
	// ...
];
```

```bash
# .env.example → .env (Skeleton)
APP_ENV=development
APP_DEBUG=true
FLIGHT_BASE_URL=/
DB_DRIVER=sqlite
# DB_PASSWORD=...
```

Diese Trennung ist bewusst für [KI-freundliche Projekte](/learn/ai) gedacht: Anweisungen können besagen: „Standardwerte in `config.php`, Geheimnisse in `.env`, Config/Engine injizieren – niemals Env-Zugriffe in einem Controller erfinden.“ Bestehende Anwendungen können `.env` vollständig ignorieren und eine einzige Konfigurationsdatei verwenden.

### Variablen

Flight erlaubt es Ihnen, Variablen zu speichern, damit sie überall in Ihrer Anwendung verwendet werden können.

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

> **Hinweis:** Nur weil Sie eine Variable setzen können, bedeutet das nicht, dass Sie es tun sollten. Verwenden Sie diese Funktion sparsam. Der Grund dafür ist, dass alles, was hier gespeichert wird, eine globale Variable wird. Globale Variablen sind schlecht, weil sie von überall in Ihrer Anwendung geändert werden können, was es schwer macht, Fehler aufzuspüren. Außerdem kann dies Dinge wie [Unit-Tests](/guides/unit-testing) verkomplizieren. Bevorzugen Sie Konstruktor-Injektion (wie im Skeleton + Dice-Setup) für Services und Konfiguration, die Controller benötigen.

### Fehler und Ausnahmen

Alle Fehler und Ausnahmen werden von Flight abgefangen und an die `error`-Methode übergeben, wenn `flight.handle_errors` auf true gesetzt ist.

Das Standardverhalten besteht darin, eine generische `HTTP 500 Internal Server Error`-Antwort mit einigen Fehlerinformationen zu senden.

Sie können dieses Verhalten für Ihre eigenen Bedürfnisse [überschreiben](/learn/extending):

```php
Flight::map('error', function (Throwable $error) {
  // Fehler behandeln
  echo $error->getTraceAsString();
});
```

Standardmäßig werden Fehler nicht im Webserverprotokoll aufgezeichnet. Sie können dies aktivieren, indem Sie die Konfiguration ändern:

```php
Flight::set('flight.log_errors', true);
```

#### 404 Nicht gefunden

Wenn eine URL nicht gefunden werden kann, ruft Flight die `notFound`-Methode auf. Das Standardverhalten besteht darin, eine `HTTP 404 Not Found`-Antwort mit einer einfachen Meldung zu senden.

Sie können dieses Verhalten für Ihre eigenen Bedürfnisse [überschreiben](/learn/extending):

```php
Flight::map('notFound', function () {
  // Nicht gefunden behandeln
});
```

## Siehe auch
- [Installation](/install) – Skeleton-Konfiguration, `.env` und Bootstrap-Layout.
- [Automatisches Laden](/learn/autoloading) – Namespaces und Groß-/Kleinschreibung von Ordnern.
- [Flight erweitern](/learn/extending) – Wie Sie die Kernfunktionalität von Flight erweitern und anpassen.
- [Unit-Tests](/guides/unit-testing) – Wie Sie Unit-Tests für Ihre Flight-Anwendung schreiben.
- [KI & Entwicklererfahrung](/learn/ai) – `AGENTS.md` und konsistente Projektanweisungen.
- [Tracy](/awesome-plugins/tracy) – Ein Plugin für erweiterte Fehlerbehandlung und Debugging.
- [Tracy-Erweiterungen](/awesome-plugins/tracy_extensions) – Erweiterungen zur Integration von Tracy mit Flight.
- [APM](/awesome-plugins/apm) – Ein Plugin für Anwendungsleistungsüberwachung und Fehlerverfolgung.
- [Sicherheit](/learn/security) – Härtungs-Flags und Geheimnisbehandlung.

## Fehlerbehebung
- Wenn Sie Probleme haben, alle Werte Ihrer Konfiguration herauszufinden, können Sie `var_dump(Flight::get());` ausführen.
- Wenn Runway oder Bereitstellungstools `config.php` neu geschrieben haben, stellen Sie sicher, dass Geheimnisse nicht committet wurden – bewahren Sie sie in `.env` oder in der echten Umgebung auf, wenn Sie das Skeleton-Muster verwenden.

## Änderungsprotokoll
- Dokumentation – `flight.views.restrict_to_path` neben den View-Pfadeinstellungen notiert.
- Dokumentation – Skeleton-artige Konfigurations-/`.env`-Schichtung und Twig-View-Erweiterungsstandard für neue Projekte dokumentiert.
- v3.18.1 – Konfigurationsoptionen `flight.debug` und `flight.allow_method_override` hinzugefügt.
- v3.5.0 – Konfiguration für `flight.v2.output_buffering` hinzugefügt, um das veraltete Ausgabepufferungsverhalten zu unterstützen.
- v2.0 – Kernkonfigurationen hinzugefügt.