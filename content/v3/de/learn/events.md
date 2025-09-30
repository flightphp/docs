# Event Manager

_ab v3.15.0_

## Überblick

Events ermöglichen es Ihnen, benutzerdefiniertes Verhalten in Ihrer Anwendung zu registrieren und auszulösen. Mit der Ergänzung von `Flight::onEvent()` und `Flight::triggerEvent()` können Sie nun in Schlüssel-Momente des Lebenszyklus Ihrer App eingreifen oder eigene Events definieren (wie Benachrichtigungen und E-Mails), um Ihren Code modularer und erweiterbarer zu machen. Diese Methoden sind Teil der [mappbaren Methoden](/learn/extending) von Flight, was bedeutet, dass Sie ihr Verhalten nach Bedarf überschreiben können.

## Verständnis

Events erlauben es Ihnen, verschiedene Teile Ihrer Anwendung zu trennen, damit sie nicht zu stark voneinander abhängen. Diese Trennung – oft als **Entkopplung** bezeichnet – macht Ihren Code einfacher zu aktualisieren, zu erweitern oder zu debuggen. Anstatt alles in einem großen Block zu schreiben, können Sie Ihre Logik in kleinere, unabhängige Teile aufteilen, die auf spezifische Aktionen (Events) reagieren.

Stellen Sie sich vor, Sie bauen eine Blog-App:
- Wenn ein Benutzer einen Kommentar postet, möchten Sie möglicherweise:
  - Den Kommentar in der Datenbank speichern.
  - Eine E-Mail an den Blog-Besitzer senden.
  - Die Aktion für Sicherheitszwecke protokollieren.

Ohne Events würden Sie all das in eine Funktion packen. Mit Events können Sie es aufteilen: Ein Teil speichert den Kommentar, ein anderer löst ein Event wie `'comment.posted'` aus, und separate Listener handhaben die E-Mail und das Protokollieren. Das hält Ihren Code sauberer und ermöglicht es Ihnen, Funktionen (wie Benachrichtigungen) hinzuzufügen oder zu entfernen, ohne die Kernlogik zu berühren.

### Häufige Anwendungsfälle

In den meisten Fällen eignen sich Events für Dinge, die optional sind, aber nicht zwingend ein absoluter Kernteil Ihres Systems. Zum Beispiel sind die Folgenden gut zu haben, aber wenn sie aus irgendeinem Grund fehlschlagen, sollte Ihre Anwendung immer noch funktionieren:

- **Protokollierung**: Aktionen wie Logins oder Fehler protokollieren, ohne den Hauptcode zu überladen.
- **Benachrichtigungen**: E-Mails oder Warnungen senden, wenn etwas passiert.
- **Cache-Updates**: Caches aktualisieren oder andere Systeme über Änderungen benachrichtigen.

Angenommen jedoch, Sie haben eine „Passwort vergessen“-Funktion. Diese sollte Teil Ihrer Kernfunktionalität sein und kein Event, da wenn diese E-Mail nicht versendet wird, der Benutzer sein Passwort nicht zurücksetzen und Ihre Anwendung nicht nutzen kann.

## Grundlegende Verwendung

Das Event-System von Flight basiert auf zwei Hauptmethoden: `Flight::onEvent()` zum Registrieren von Event-Listenern und `Flight::triggerEvent()` zum Auslösen von Events. Hier ist, wie Sie sie verwenden können:

### Registrieren von Event-Listenern

Um auf ein Event zu hören, verwenden Sie `Flight::onEvent()`. Diese Methode ermöglicht es Ihnen, zu definieren, was passieren soll, wenn ein Event auftritt.

```php
Flight::onEvent(string $event, callable $callback): void
```

- `$event`: Ein Name für Ihr Event (z. B. `'user.login'`).
- `$callback`: Die Funktion, die ausgeführt wird, wenn das Event ausgelöst wird.

Sie „abonnieren“ ein Event, indem Sie Flight mitteilen, was es tun soll, wenn es passiert. Der Callback kann Argumente akzeptieren, die vom Event-Auslöser übergeben werden.

Das Event-System von Flight ist synchron, was bedeutet, dass jeder Event-Listener nacheinander ausgeführt wird. Wenn Sie ein Event auslösen, werden alle registrierten Listener für dieses Event vollständig ausgeführt, bevor Ihr Code fortfährt. Dies ist wichtig zu verstehen, da es sich von asynchronen Event-Systemen unterscheidet, bei denen Listener parallel oder zu einem späteren Zeitpunkt ausgeführt werden könnten.

#### Einfaches Beispiel
```php
Flight::onEvent('user.login', function ($username) {
    echo "Willkommen zurück, $username!";

	// Sie können eine E-Mail senden, wenn der Login von einem neuen Standort kommt
});
```
Hier, wenn das `'user.login'`-Event ausgelöst wird, begrüßt es den Benutzer namentlich und könnte auch Logik enthalten, um eine E-Mail zu senden, falls nötig.

> **Hinweis:** Der Callback kann eine Funktion, eine anonyme Funktion oder eine Methode aus einer Klasse sein.

### Auslösen von Events

Um ein Event auszulösen, verwenden Sie `Flight::triggerEvent()`. Dies weist Flight an, alle für dieses Event registrierten Listener auszuführen und dabei alle von Ihnen bereitgestellten Daten weiterzuleiten.

```php
Flight::triggerEvent(string $event, ...$args): void
```

- `$event`: Der Name des Events, das Sie auslösen (muss zu einem registrierten Event passen).
- `...$args`: Optionale Argumente, die an die Listener gesendet werden (kann jede Anzahl von Argumenten sein).

#### Einfaches Beispiel
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Dies löst das `'user.login'`-Event aus und sendet `'alice'` an den Listener, den wir zuvor definiert haben, was ausgibt: `Willkommen zurück, alice!`.

- Wenn keine Listener registriert sind, passiert nichts – Ihre App bricht nicht ab.
- Verwenden Sie den Spread-Operator (`...`), um mehrere Argumente flexibel zu übergeben.

### Stoppen von Events

Wenn ein Listener `false` zurückgibt, werden keine weiteren Listener für dieses Event ausgeführt. Dies ermöglicht es Ihnen, die Event-Kette basierend auf spezifischen Bedingungen zu stoppen. Denken Sie daran, dass die Reihenfolge der Listener wichtig ist, da der erste, der `false` zurückgibt, den Rest stoppt.

**Beispiel**:
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // Stoppt nachfolgende Listener
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // Dies wird nie gesendet
});
```

### Überschreiben von Event-Methoden

`Flight::onEvent()` und `Flight::triggerEvent()` können [erweitert](/learn/extending) werden, was bedeutet, dass Sie definieren können, wie sie funktionieren. Das ist großartig für fortgeschrittene Benutzer, die das Event-System anpassen möchten, z. B. durch Hinzufügen von Protokollierung oder Änderung der Event-Verteilung.

#### Beispiel: Anpassen von `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Protokolliere jede Event-Registrierung
    error_log("Neuer Event-Listener hinzugefügt für: $event");
    // Rufe das Standardverhalten auf (angenommen ein internes Event-System)
    Flight::_onEvent($event, $callback);
});
```
Jetzt wird jedes Mal, wenn Sie ein Event registrieren, protokolliert, bevor es fortgesetzt wird.

#### Warum überschreiben?
- Debugging oder Überwachung hinzufügen.
- Events in bestimmten Umgebungen einschränken (z. B. in Tests deaktivieren).
- Mit einer anderen Event-Bibliothek integrieren.

### Wo Events platzieren

Wenn Sie neu in den Event-Konzepten in Ihrem Projekt sind, fragen Sie sich vielleicht: *Wo registriere ich all diese Events in meiner App?* Die Einfachheit von Flight bedeutet, dass es keine strenge Regel gibt – Sie können sie überall platzieren, wo es für Ihr Projekt Sinn macht. Allerdings hilft es, sie organisiert zu halten, um Ihren Code zu pflegen, wenn Ihre App wächst. Hier sind einige praktische Optionen und Best Practices, angepasst an die leichte Natur von Flight:

#### Option 1: In Ihrer Haupt-`index.php`
Für kleine Apps oder schnelle Prototypen können Sie Events direkt in Ihrer `index.php`-Datei neben Ihren Routen registrieren. Das hält alles an einem Ort, was in Ordnung ist, wenn Einfachheit Ihre Priorität ist.

```php
require 'vendor/autoload.php';

// Events registrieren
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
});

// Routen definieren
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **Vorteile**: Einfach, keine extra Dateien, super für kleine Projekte.
- **Nachteile**: Kann unübersichtlich werden, wenn Ihre App mit mehr Events und Routen wächst.

#### Option 2: Eine separate `events.php`-Datei
Für eine etwas größere App ziehen Sie in Erwägung, Event-Registrierungen in eine dedizierte Datei wie `app/config/events.php` zu verschieben. Schließen Sie diese Datei in Ihrer `index.php` vor Ihren Routen ein. Das ahmt nach, wie Routen oft in `app/config/routes.php` in Flight-Projekten organisiert werden.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "Email sent to $email: Welcome, $name!";
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **Vorteile**: Hält `index.php` auf Routing fokussiert, organisiert Events logisch, einfach zu finden und zu bearbeiten.
- **Nachteile**: Fügt eine kleine Struktur hinzu, die für sehr kleine Apps übertrieben wirken könnte.

#### Option 3: Nahe dem Auslöseort
Ein anderer Ansatz ist, Events nahe dem Ort zu registrieren, an dem sie ausgelöst werden, z. B. in einem Controller oder Routen-Definition. Das funktioniert gut, wenn ein Event spezifisch für einen Teil Ihrer App ist.

```php
Flight::route('/signup', function () {
    // Event hier registrieren
    Flight::onEvent('user.registered', function ($email) {
        echo "Welcome email sent to $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Signed up!";
});
```
- **Vorteile**: Hält verwandten Code zusammen, gut für isolierte Features.
- **Nachteile**: Verteilt Event-Registrierungen, was es schwieriger macht, alle Events auf einen Blick zu sehen; Risiko von Duplikaten, wenn nicht vorsichtig.

#### Best Practice für Flight
- **Einfach starten**: Für winzige Apps platzieren Sie Events in `index.php`. Es ist schnell und passt zu Flights Minimalismus.
- **Intelligent wachsen**: Wenn Ihre App expandiert (z. B. mehr als 5-10 Events), verwenden Sie eine `app/config/events.php`-Datei. Es ist ein natürlicher Schritt, wie die Organisation von Routen, und hält Ihren Code ordentlich, ohne komplexe Frameworks hinzuzufügen.
- **Über-Engineering vermeiden**: Erstellen Sie keine vollständige „Event-Manager“-Klasse oder -Verzeichnis, es sei denn, Ihre App wird riesig – Flight lebt von Einfachheit, also halten Sie es leichtgewichtig.

#### Tipp: Nach Zweck gruppieren
In `events.php` gruppieren Sie verwandte Events (z. B. alle benutzerbezogenen Events zusammen) mit Kommentaren für Klarheit:

```php
// app/config/events.php
// User Events
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in");
});
Flight::onEvent('user.registered', function ($email) {
    echo "Welcome to $email!";
});

// Page Events
Flight::onEvent('page.updated', function ($pageId) {
    Flight::cache()->delete("page_$pageId");
});
```

Diese Struktur skaliert gut und bleibt anfängerfreundlich.

### Beispiele aus der Praxis

Lassen Sie uns einige reale Szenarien durchgehen, um zu zeigen, wie Events funktionieren und warum sie hilfreich sind.

#### Beispiel 1: Protokollieren eines Benutzer-Logins
```php
// Schritt 1: Einen Listener registrieren
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username logged in at $time");
});

// Schritt 2: Es in Ihrer App auslösen
Flight::route('/login', function () {
    $username = 'bob'; // Stellen Sie sich vor, das kommt aus einem Formular
    Flight::triggerEvent('user.login', $username);
    echo "Hi, $username!";
});
```
**Warum nützlich**: Der Login-Code muss nichts über Protokollierung wissen – er löst nur das Event aus. Sie können später mehr Listener hinzufügen (z. B. eine Willkommens-E-Mail senden), ohne die Route zu ändern.

#### Beispiel 2: Benachrichtigen über neue Benutzer
```php
// Listener für neue Registrierungen
Flight::onEvent('user.registered', function ($email, $name) {
    // Simuliere das Senden einer E-Mail
    echo "Email sent to $email: Welcome, $name!";
});

// Auslösen, wenn jemand registriert
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Thanks for signing up!";
});
```
**Warum nützlich**: Die Registrierungslogik konzentriert sich auf die Erstellung des Benutzers, während das Event Benachrichtigungen handhabt. Sie könnten später mehr Listener hinzufügen (z. B. die Registrierung protokollieren).

#### Beispiel 3: Cache leeren
```php
// Listener zum Leeren eines Caches
Flight::onEvent('page.updated', function ($pageId) {
	// Wenn Sie das flightphp/cache-Plugin verwenden
    Flight::cache()->delete("page_$pageId");
    echo "Cache cleared for page $pageId.";
});

// Auslösen, wenn eine Seite bearbeitet wird
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Stellen Sie sich vor, wir haben die Seite aktualisiert
    Flight::triggerEvent('page.updated', $pageId);
    echo "Page $pageId updated.";
});
```
**Warum nützlich**: Der Bearbeitungscode kümmert sich nicht um Caching – er signalisiert nur die Aktualisierung. Andere Teile der App können entsprechend reagieren.

### Best Practices

- **Events klar benennen**: Verwenden Sie spezifische Namen wie `'user.login'` oder `'page.updated'`, damit klar ist, was sie tun.
- **Listener einfach halten**: Legen Sie keine langsamen oder komplexen Aufgaben in Listener – halten Sie Ihre App schnell.
- **Ihre Events testen**: Lösen Sie sie manuell aus, um sicherzustellen, dass Listener wie erwartet funktionieren.
- **Events weise verwenden**: Sie sind großartig für Entkopplung, aber zu viele können Ihren Code schwer nachvollziehbar machen – verwenden Sie sie, wenn es Sinn macht.

Das Event-System in Flight PHP mit `Flight::onEvent()` und `Flight::triggerEvent()` bietet Ihnen eine einfache, aber leistungsstarke Möglichkeit, flexible Anwendungen zu bauen. Indem verschiedene Teile Ihrer App durch Events miteinander kommunizieren, können Sie Ihren Code organisiert, wiederverwendbar und einfach erweiterbar halten. Ob Sie Aktionen protokollieren, Benachrichtigungen senden oder Updates verwalten – Events helfen Ihnen dabei, ohne Ihre Logik zu verknüpfen. Und mit der Möglichkeit, diese Methoden zu überschreiben, haben Sie die Freiheit, das System an Ihre Bedürfnisse anzupassen. Starten Sie klein mit einem einzelnen Event und beobachten Sie, wie es die Struktur Ihrer App verändert!

### Eingebauten Events

Flight PHP kommt mit einigen eingebauten Events, die Sie verwenden können, um in den Lebenszyklus des Frameworks einzugreifen. Diese Events werden an spezifischen Punkten im Request/Response-Zyklus ausgelöst und ermöglichen es Ihnen, benutzerdefinierte Logik auszuführen, wenn bestimmte Aktionen auftreten.

#### Liste der eingebauten Events
- **flight.request.received**: `function(Request $request)` Wird ausgelöst, wenn eine Anfrage empfangen, geparst und verarbeitet wird.
- **flight.error**: `function(Throwable $exception)` Wird ausgelöst, wenn ein Fehler während des Request-Lebenszyklus auftritt.
- **flight.redirect**: `function(string $url, int $status_code)` Wird ausgelöst, wenn eine Weiterleitung initiiert wird.
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` Wird ausgelöst, wenn der Cache für einen spezifischen Schlüssel überprüft wird und ob es ein Treffer oder Fehlschlag war.
- **flight.middleware.before**: `function(Route $route)` Wird ausgelöst, nachdem das Before-Middleware ausgeführt wurde.
- **flight.middleware.after**: `function(Route $route)` Wird ausgelöst, nachdem das After-Middleware ausgeführt wurde.
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` Wird ausgelöst, nachdem ein beliebiges Middleware ausgeführt wurde.
- **flight.route.matched**: `function(Route $route)` Wird ausgelöst, wenn eine Route übereinstimmt, aber noch nicht ausgeführt wird.
- **flight.route.executed**: `function(Route $route, float $executionTime)` Wird ausgelöst, nachdem eine Route ausgeführt und verarbeitet wurde. `$executionTime` ist die Zeit, die es gedauert hat, die Route auszuführen (Controller aufrufen usw.).
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` Wird ausgelöst, nachdem eine View gerendert wurde. `$executionTime` ist die Zeit, die es gedauert hat, das Template zu rendern. **Hinweis: Wenn Sie die `render`-Methode überschreiben, müssen Sie dieses Event erneut auslösen.**
- **flight.response.sent**: `function(Response $response, float $executionTime)` Wird ausgelöst, nachdem eine Response an den Client gesendet wurde. `$executionTime` ist die Zeit, die es gedauert hat, die Response zu erstellen.

## Siehe auch
- [Erweitern von Flight](/learn/extending) - Wie Sie die Kernfunktionalität von Flight erweitern und anpassen.
- [Cache](/awesome-plugins/php_file_cache) - Beispiel für die Verwendung von Events, um Cache zu leeren, wenn eine Seite aktualisiert wird.

## Fehlerbehebung
- Wenn Ihre Event-Listener nicht aufgerufen werden, stellen Sie sicher, dass Sie sie vor dem Auslösen der Events registrieren. Die Reihenfolge der Registrierung ist wichtig.

## Änderungsprotokoll
- v3.15.0 - Events zu Flight hinzugefügt.