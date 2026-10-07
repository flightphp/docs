# Konfiguration

## Überblick

Flight bietet eine einfache Möglichkeit, verschiedene Aspekte des Frameworks an die Anforderungen Ihrer Anwendung anzupassen. Einige sind standardmäßig gesetzt, aber Sie können sie bei Bedarf überschreiben. Sie können auch eigene Variablen setzen, die in Ihrer gesamten Anwendung verwendet werden.

Klare, mehrschichtige Konfiguration (Datei-Standardwerte + Umgebungsgeheimnisse) hilft auch [KI-Codierungswerkzeugen](/learn/ai): Agenten lernen einen Ort für Literale und einen Ort für Geheimnisse kennen, anstatt `$_ENV`-Zugriffe in Controllern zu erfinden.

## Verständnis

Sie können bestimmte Verhaltensweisen von Flight anpassen, indem Sie Konfigurationswerte über die `set`-Methode setzen.

```php
Flight::set('flight.log_errors', true);
```

In einer strukturierten App (einschließlich des [Skeleton](https://github.com/flightphp/skeleton)) laden Sie normalerweise die Projekteinstellungen aus `app/config/config.php` und wenden dann die relevanten Schlüssel auf die Engine an (z. B. `flight.base_url`, `flight.views.path`). Sie können auch ein kleines Konfigurationsobjekt in Controller injizieren, anstatt überall Globale zu lesen – freundlicher für Tests und für Agenten, die `AGENTS.md` folgen.

## Grundlegende Verwendung

### Flight-Konfigurationsoptionen

Die folgende Liste enthält alle verfügbaren Konfigurationseinstellungen:

- **flight.base_url** `?string` - Überschreibt die Basis-URL der Anfrage, wenn Flight in einem Unterverzeichnis läuft. (Standard: null)
- **flight.case_sensitive** `bool` - Case-sensitive URL-Zuordnung. (Standard: false)
- **flight.handle_errors** `bool` - Flight erlauben, alle Fehler intern zu behandeln. (Standard: true)
  - Wenn Sie möchten, dass Flight Fehler anstelle des standardmäßigen PHP-Verhaltens behandelt, muss dies true sein.
  - Wenn Sie [Tracy](/awesome-plugins/tracy) installiert haben, sollten Sie dies auf false setzen, damit Tracy Fehler behandeln kann.
  - Wenn Sie das [APM](/awesome-plugins/apm)-Plugin installiert haben, sollten Sie dies auf true setzen, damit das APM die Fehler protokollieren kann.
- **flight.log_errors** `bool` - Protokolliert Fehler in der Fehlerprotokolldatei des Webservers. (Standard: false)
  - Wenn Sie [Tracy](/awesome-plugins/tracy) installiert haben, protokolliert Tracy Fehler basierend auf den Tracy-Konfigurationen, nicht auf dieser Konfiguration.
- **flight.debug** `bool` - Gibt bei einem Fehler detaillierte Fehlerinformationen (Ausnahmemeldung, Code und Stack-Trace) im Browser aus. (Standard: false)
  - **Aktivieren Sie dies niemals in der Produktion** – es gibt interne Anwendungsdetails preis. Verwenden Sie es nur für die lokale Entwicklung oder das Staging.
  - Wenn `false` gesetzt ist, wird stattdessen ein generischer `500 Internal Server Error` angezeigt. Kombinieren Sie dies mit `flight.log_errors`, um Fehler serverseitig zu erfassen.
- **flight.allow_method_override** `bool` - Erlaubt das Überschreiben der HTTP-Methode über den `X-HTTP-Method-Override`-Request-Header oder ein `_method`-Feld im POST-Body. (Standard: true)
  - **Es wird empfohlen, dies auf `false` zu setzen** für Anwendungen, die kein HTML-Formular-basiertes Methoden-Spoofing benötigen, da verhindert wird, dass Clients über ein Standard-POST-Formular `DELETE`- oder `PUT`-Anfragen fälschen.
  - Weitere Details finden Sie unter [Security](/learn/security#flight-configuration-hardening).
- **flight.views.path** `string` - Verzeichnis, das die View-Template-Dateien enthält. (Standard: ./views)
- **flight.views.extension** `string` - Dateierweiterung der View-Templates. (Standard: `.php`; das offizielle Skeleton setzt dies bei Verwendung von Twig auf `.twig`)
- **flight.views.restrict_to_path** `bool` - Wenn `true`, akzeptiert die native `View` von Flight nur Template-Dateien, die innerhalb von `flight.views.path` aufgelöst werden. (Standard: `false`). **Aktivieren Sie dies** für Apps, die native Views verwenden. Siehe [Security](/learn/security#flightviewsrestrict_to_path).
- **flight.content_length** `bool` - Setzt den `Content-Length`-Header. (Standard: true)
  - Wenn Sie [Tracy](/awesome-plugins/tracy) verwenden, muss dies auf false gesetzt werden, damit Tracy korrekt rendern kann.
- **flight.v2.output_buffering** `bool` - Legacy-Output-Buffering verwenden. Siehe [Migration zu v3](migrating-to-v3). (Standard: false)

### Loader-Konfiguration

Zusätzlich gibt es eine weitere Konfigurationseinstellung für den Loader. Diese ermöglicht es Ihnen, Klassen mit `_` im Klassennamen automatisch zu laden.

```php
// Aktiviert das Laden von Klassen mit Unterstrichen
// Standardmäßig true
Loader::$v2ClassLoading = false;
```

Denken Sie daran, dass [Autoloading](/learn/autoloading) auch von der **Groß-/Kleinschreibung der Ordner** abhängt, die Ihren Namespaces entspricht – insbesondere beim `App\` + `app/Controller/`-Layout des Skeleton.

### Projektkonfiguration und `.env` (Skeleton-Muster)

Der Flight-Kern erfordert keine `.env`-Dateien. Viele Apps verwenden nur ein PHP-Konfigurationsarray. Das offizielle Skeleton schichtet die Konfiguration, sodass Geheimnisse außerhalb von Git bleiben, während Runway **literale** Konfiguration sicher neu schreiben kann:

1. **`.env` / echte Umgebung** — Geheimnisse und Bereitstellungs-Überschreibungen (in Git ignoriert).
2. **`app/config/config.php`** — literale PHP-Array-Standardwerte (kopiert aus `config_sample.php`). Bevorzugen Sie **keine** `$_ENV[...]`-Ausdrücke in dieser Datei: Tools wie `runway config:set` könnten sie als statische Werte neu schreiben und Geheimnisse in die Datei einbacken.
3. **Zusammenführung beim Bootstrap** — Umgebung gewinnt für zugeordnete Schlüssel; Anwendungscode liest ein Konfigurationsobjekt oder `$app->get()`, nicht `$_ENV` in Controllern.

Beispielstruktur von `config_sample.php` / `config.php` (vereinfacht):

```php
<?php
// Nur Literale – Geheimnisse gehören für den Skeleton-Workflow in .env
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

Diese Aufteilung ist bewusst für [KI-freundliche Projekte](/learn/ai) gedacht: Anweisungen können sagen „Standardwerte in `config.php`, Geheimnisse in `.env`, injizieren Sie Config / Engine – erfinden Sie niemals Umgebungszugriffe in einem Controller.“ Bestehende Apps können `.env` vollständig ignorieren und eine einzige Konfigurationsdatei behalten.

### Variablen

Flight ermöglicht es Ihnen, Variablen zu speichern, damit sie überall in Ihrer Anwendung verwendet werden können.

```php
// Speichern Sie Ihre Variable
Flight::set('id', 123);

// An anderer Stelle in Ihrer Anwendung
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

> **Hinweis:** Nur weil Sie eine Variable setzen können, heißt das nicht, dass Sie es tun sollten. Nutzen Sie diese Funktion sparsam. Der Grund dafür ist, dass alles, was hier gespeichert wird, zu einer globalen Variable wird. Globale Variablen sind schlecht, weil sie von überall in Ihrer Anwendung geändert werden können, was das Aufspüren von Fehlern erschwert. Außerdem kann dies Dinge wie [Unit-Tests](/guides/unit-testing) verkomplizieren. Bevorzugen Sie Konstruktor-Injektion (wie im Skeleton + Dice-Setup) für Dienste und Konfiguration, die Controller benötigen.

### Fehler und Ausnahmen

Alle Fehler und Ausnahmen werden von Flight abgefangen und an die `error`-Methode übergeben, wenn `flight.handle_errors` auf true gesetzt ist.

Das Standardverhalten besteht darin, eine generische `HTTP 500 Internal Server Error`-Antwort mit einigen Fehlerinformationen zu senden.

Sie können dieses Verhalten für Ihre eigenen Anforderungen [überschreiben](/learn/extending):

```php
Flight::map('error', function (Throwable $error) {
  // Fehler behandeln
  echo $error->getTraceAsString();
});
```

Standardmäßig werden Fehler nicht auf dem Webserver protokolliert. Sie können dies aktivieren, indem Sie die Konfiguration ändern:

```php
Flight::set('flight.log_errors', true);
```

#### 404 Nicht gefunden

Wenn eine URL nicht gefunden werden kann, ruft Flight die `notFound`-Methode auf. Das Standardverhalten besteht darin, eine `HTTP 404 Not Found`-Antwort mit einer einfachen Meldung zu senden.

Sie können dieses Verhalten für Ihre eigenen Anforderungen [überschreiben](/learn/extending):

```php
Flight::map('notFound', function () {
  // Nicht gefunden behandeln
});
```

## Siehe auch

- [Installation](/install) - Skeleton-Konfiguration, `.env` und Bootstrap-Layout.
- [Autoloading](/learn/autoloading) - Namespaces und Ordner-Groß-/Kleinschreibung.
- [Flight erweitern](/learn/extending) - So erweitern und passen Sie die Kernfunktionalität von Flight an.
- [Unit-Tests](/guides/unit-testing) - So schreiben Sie Unit-Tests für Ihre Flight-Anwendung.
- [KI & Entwicklererfahrung](/learn/ai) - `AGENTS.md` und konsistente Projektanweisungen.
- [Tracy](/awesome-plugins/tracy) - Ein Plugin für erweiterte Fehlerbehandlung und Debugging.
- [Tracy-Erweiterungen](/awesome-plugins/tracy_extensions) - Erweiterungen zur Integration von Tracy mit Flight.
- [APM](/awesome-plugins/apm) - Ein Plugin für Anwendungsleistungsüberwachung und Fehlerverfolgung.
- [Security](/learn/security) - Härtungsflags und Geheimnisbehandlung.

## Fehlerbehebung

- Wenn Sie Probleme haben, alle Werte Ihrer Konfiguration herauszufinden, können Sie `var_dump(Flight::get());` ausführen.
- Wenn Runway oder Bereitstellungswerkzeuge `config.php` neu geschrieben haben, bestätigen Sie, dass keine Geheimnisse eingecheckt wurden – bewahren Sie sie bei Verwendung des Skeleton-Musters in `.env` oder der echten Umgebung auf.

## Änderungsprotokoll

- Dokumentation – `flight.views.restrict_to_path` neben den View-Pfadeinstellungen erwähnt.
- Dokumentation – Skeleton-Konfiguration / `.env`-Schichtung und Twig-View-Erweiterungsstandard für neue Projekte dokumentiert.
- v3.18.1 – Konfigurationsoptionen `flight.debug` und `flight.allow_method_override` hinzugefügt.
- v3.5.0 – Konfiguration für `flight.v2.output_buffering` hinzugefügt, um das Legacy-Output-Buffering-Verhalten zu unterstützen.
- v2.0 – Kernkonfigurationen hinzugefügt.