# Events-System in Flight PHP (v3.15.0+)

Flight PHP führt ein leichtes und intuitives Events-System ein, das es Ihnen ermöglicht, benutzerdefinierte Events in Ihrer Anwendung zu registrieren und auszulösen. Mit den Methoden `Flight::onEvent()` und `Flight::triggerEvent()` können Sie sich nun an Schlüsselstellen des Lebenszyklus Ihrer App einhaken oder eigene Events definieren, um Ihren Code modularer und erweiterbarer zu gestalten. Diese Methoden gehören zu Flight’s **mappable methods**, was bedeutet, dass Sie ihr Verhalten überschreiben können, um es an Ihre Bedürfnisse anzupassen.

Dieser Leitfaden deckt alles ab, was Sie wissen müssen, um mit Events zu starten, einschließlich der Gründe, warum sie wertvoll sind, wie man sie verwendet, und praktische Beispiele, um Anfängern ihre Stärke zu verdeutlichen.

## Warum Events verwenden?

Events ermöglichen es, verschiedene Teile Ihrer Anwendung zu trennen, damit sie nicht zu stark voneinander abhängen. Diese Trennung – oft als **Entkopplung** bezeichnet – macht Ihren Code einfacher zu aktualisieren, zu erweitern oder zu debuggen. Statt alles in einem großen Block zu schreiben, können Sie Ihre Logik in kleinere, unabhängige Teile aufteilen, die auf bestimmte Aktionen (Events) reagieren.

Stellen Sie sich vor, Sie bauen eine Blog-App:
- Wenn ein Benutzer einen Kommentar veröffentlicht, möchten Sie möglicherweise:
  - Den Kommentar in der Datenbank speichern.
  - Eine E-Mail an den Blog-Besitzer senden.
  - Die Aktion für die Sicherheit protokollieren.

Ohne Events würden Sie all das in eine Funktion packen. Mit Events können Sie es aufteilen: Ein Teil speichert den Kommentar, ein anderer löst ein Event wie `'comment.posted'` aus, und separate Listener kümmern sich um die E-Mail und das Protokollieren. Das hält Ihren Code sauberer und ermöglicht es Ihnen, Funktionen (wie Benachrichtigungen) hinzuzufügen oder zu entfernen, ohne die Kernlogik zu berühren.

### Häufige Anwendungen
- **Protokollierung**: Aktionen wie Logins oder Fehler protokollieren, ohne den Hauptcode zu belasten.
- **Benachrichtigungen**: E-Mails oder Alerts senden, wenn etwas passiert.
- **Updates**: Caches aktualisieren oder andere Systeme über Änderungen benachrichtigen.

## Registrieren von Event-Listenern

Um auf ein Event zu hören, verwenden Sie `Flight::onEvent()`. Diese Methode lässt Sie definieren, was passieren soll, wenn ein Event auftritt.

### Syntax
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event`: Ein Name für Ihr Event (z. B. `'user.login'`).
- `$callback`: Die Funktion, die ausgeführt wird, wenn das Event ausgelöst wird. // Dieser Kommentar wurde übersetzt

### Wie es funktioniert
Sie "abonnieren" ein Event, indem Sie Flight mitteilen, was bei dessen Auftreten geschehen soll. Der Callback kann Argumente akzeptieren, die vom Event-Auslöser übergeben werden.

Flight's Events-System ist synchron, was bedeutet, dass jeder Event-Listener nacheinander ausgeführt wird. Wenn Sie ein Event auslösen, werden alle registrierten Listener für dieses Event vollständig ausgeführt, bevor Ihr Code fortfährt. Das ist wichtig zu verstehen, da es sich von asynchronen Events-Systemen unterscheidet, bei denen Listener parallel oder zu einem späteren Zeitpunkt ausgeführt werden könnten.

### Einfaches Beispiel
```php
Flight::onEvent('user.login', function ($username) {
    echo "Welcome back, $username!"; // Dieser Kommentar wurde übersetzt
});
```
Hier wird der Benutzer bei Auslösung des `'user.login'`-Events mit seinem Namen begrüßt.

### Wichtige Punkte
- Sie können mehrere Listener für dasselbe Event hinzufügen – sie werden in der Reihenfolge ausgeführt, in der Sie sie registriert haben.
- Der Callback kann eine Funktion, eine anonyme Funktion oder eine Methode aus einer Klasse sein.

## Auslösen von Events

Um ein Event auszulösen, verwenden Sie `Flight::triggerEvent()`. Das weist Flight an, alle Listener für dieses Event auszuführen und dabei alle bereitgestellten Daten zu übergeben.

### Syntax
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`: Der Name des Events, das Sie auslösen (muss mit einem registrierten Event übereinstimmen).
- `...$args`: Optionale Argumente, die an die Listener gesendet werden (kann eine beliebige Anzahl von Argumenten sein).

### Einfaches Beispiel
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username); // Dieser Kommentar wurde übersetzt
```
Das löst das `'user.login'`-Event aus und sendet `'alice'` an den Listener, den wir zuvor definiert haben, was "Welcome back, alice!" ausgibt.

### Wichtige Punkte
- Wenn keine Listener registriert sind, passiert nichts – Ihre App bricht nicht ab.
- Verwenden Sie den Spread-Operator (`...`), um mehrere Argumente flexibel zu übergeben.

### Registrieren von Event-Listenern

...

**Stoppen weiterer Listener**: Wenn ein Listener `false` zurückgibt, werden keine zusätzlichen Listener für dieses Event ausgeführt. Das ermöglicht es Ihnen, die Event-Kette basierend auf bestimmten Bedingungen zu stoppen. Denken Sie daran, dass die Reihenfolge der Listener wichtig ist, da der erste, der `false` zurückgibt, den Rest stoppt.

**Beispiel**:
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) { // Dieser Kommentar wurde übersetzt
        logoutUser($username);
        return false; // Stoppt nachfolgende Listener
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // Dies wird nie gesendet
});
```

## Überschreiben von Event-Methoden

`Flight::onEvent()` und `Flight::triggerEvent()` können [extended](/learn/extending) werden, was bedeutet, dass Sie ihr Verhalten neu definieren können. Das ist ideal für erfahrene Benutzer, die das Events-System anpassen möchten, z. B. durch Hinzufügen von Protokollierung oder Änderung der Auslösung von Events.

### Beispiel: Anpassung von `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Jeden Event-Registrierung protokollieren
    error_log("New event listener added for: $event"); // Dieser Kommentar wurde übersetzt
    // Das Standardverhalten aufrufen (unter der Annahme eines internen Events-Systems)
    Flight::_onEvent($event, $callback);
});
```
Nun wird jede Event-Registrierung protokolliert, bevor es fortfährt.

### Warum überschreiben?
- Debugging oder Überwachung hinzufügen.
- Events in bestimmten Umgebungen einschränken (z. B. in Tests deaktivieren).
- Mit einer anderen Event-Bibliothek integrieren.

## Wo Events platzieren

Als Anfänger fragen Sie sich vielleicht: *Wo registriere ich all diese Events in meiner App?* Flight’s Einfachheit bedeutet, dass es keine strenge Regel gibt – Sie können sie dort platzieren, wo es für Ihr Projekt Sinn ergibt. Allerdings hilft eine gute Organisation, Ihren Code zu pflegen, wenn Ihre App wächst. Hier sind einige praktische Optionen und Best Practices, die auf Flight’s leichter Natur abgestimmt sind:

### Option 1: In Ihrer Haupt-`index.php`
Für kleine Apps oder schnelle Prototypen können Sie Events direkt in Ihrer `index.php`-Datei neben Ihren Routen registrieren. Das hält alles an einem Ort, was in Ordnung ist, wenn Einfachheit im Vordergrund steht.

```php
require 'vendor/autoload.php'; // Events registrieren

Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s')); // Dieser Kommentar wurde übersetzt
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

### Option 2: In einer separaten `events.php`-Datei
Für etwas größere Apps ziehen Sie in Betracht, die Event-Registrierungen in eine dedizierte Datei wie `app/config/events.php` zu verschieben. Integrieren Sie diese Datei in Ihrer `index.php` vor Ihren Routen. Das ähnelt der Organisation von Routen in `app/config/routes.php` in Flight-Projekten.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s')); // Dieser Kommentar wurde übersetzt
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "Email sent to $email: Welcome, $name!"; // Dieser Kommentar wurde übersetzt
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
- **Vorteile**: Halten `index.php` auf Routen fokussiert, organisieren Events logisch, einfach zu finden und zu bearbeiten.
- **Nachteile**: Fügt ein wenig Struktur hinzu, was für sehr kleine Apps übertrieben wirken könnte.

### Option 3: In der Nähe der Auslösung
Ein weiterer Ansatz ist, Events in der Nähe ihrer Auslösung zu registrieren, z. B. innerhalb eines Controllers oder einer Routendefinition. Das funktioniert gut, wenn ein Event auf einen bestimmten Teil Ihrer App beschränkt ist.

```php
Flight::route('/signup', function () {
    // Event hier registrieren
    Flight::onEvent('user.registered', function ($email) {
        echo "Welcome email sent to $email!"; // Dieser Kommentar wurde übersetzt
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Signed up!";
});
```
- **Vorteile**: Hält verwandten Code zusammen, gut für isolierte Features.
- **Nachteile**: Streut Event-Registrierungen, macht es schwieriger, alle Events auf einen Blick zu sehen; Risiko von Duplikaten, wenn nicht vorsichtig.

### Best Practices für Flight
- **Einfach starten**: Für kleine Apps Events in `index.php` platzieren. Es ist schnell und passt zu Flight’s Minimalismus.
- **Klug wachsen**: Wenn Ihre App wächst (z. B. mehr als 5-10 Events), eine `app/config/events.php`-Datei verwenden. Es ist ein natürlicher Schritt, ähnlich wie bei Routen, und hält Ihren Code ordentlich, ohne komplexe Frameworks hinzuzufügen.
- **Überengineering vermeiden**: Erstellen Sie keine vollständige „Event-Manager“-Klasse oder -Verzeichnis, es sei denn, Ihre App wird riesig – Flight lebt von der Einfachheit, halten Sie es leicht.

### Tipp: Nach Zweck gruppieren
In `events.php` gruppieren Sie verwandte Events (z. B. alle Benutzer-Events zusammen) mit Kommentaren für Klarheit:

```php
// app/config/events.php
// Benutzer-Events
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in"); // Dieser Kommentar wurde übersetzt
});
Flight::onEvent('user.registered', function ($email) {
    echo "Welcome to $email!"; // Dieser Kommentar wurde übersetzt
});

// Seite-Events
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // Session-Cache leeren, falls zutreffend
    echo "Cache cleared for page $pageId."; // Dieser Kommentar wurde übersetzt
});
```

Diese Struktur skaliert gut und bleibt für Anfänger freundlich.

## Beispiele für Anfänger

Lassen Sie uns einige reale Szenarien durchgehen, um zu zeigen, wie Events funktionieren und warum sie hilfreich sind.

### Beispiel 1: Protokollieren eines Benutzer-Logins
```php
// Schritt 1: Einen Listener registrieren
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username logged in at $time"); // Dieser Kommentar wurde übersetzt
});

// Schritt 2: Es in Ihrer App auslösen
Flight::route('/login', function () {
    $username = 'bob'; // Stellen Sie sich vor, das kommt aus einem Formular
    Flight::triggerEvent('user.login', $username);
    echo "Hi, $username!";
});
```
**Warum es nützlich ist**: Der Login-Code muss nichts über das Protokollieren wissen – er löst nur das Event aus. Später können Sie weitere Listener hinzufügen (z. B. eine Willkommens-E-Mail), ohne die Route zu ändern.

### Beispiel 2: Benachrichtigen über neue Benutzer
```php
// Listener für neue Registrierungen
Flight::onEvent('user.registered', function ($email, $name) {
    // Eine E-Mail simulieren
    echo "Email sent to $email: Welcome, $name!"; // Dieser Kommentar wurde übersetzt
});

// Es auslösen, wenn jemand registriert
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Thanks for signing up!";
});
```
**Warum es nützlich ist**: Die Signup-Logik konzentriert sich auf die Erstellung des Benutzers, während das Event die Benachrichtigungen handhabt. Sie könnten später weitere Listener hinzufügen (z. B. das Signup protokollieren).

### Beispiel 3: Einen Cache leeren
```php
// Listener zum Leeren eines Caches
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // Session-Cache leeren, falls zutreffend
    echo "Cache cleared for page $pageId."; // Dieser Kommentar wurde übersetzt
});

// Auslösen, wenn eine Seite bearbeitet wird
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Stellen Sie sich vor, wir haben die Seite aktualisiert
    Flight::triggerEvent('page.updated', $pageId);
    echo "Page $pageId updated.";
});
```
**Warum es nützlich ist**: Der Bearbeitungs-Code kümmert sich nicht um das Caching – er signalisiert nur die Aktualisierung. Andere Teile der App können entsprechend reagieren.

## Best Practices

- **Events klar benennen**: Verwenden Sie spezifische Namen wie `'user.login'` oder `'page.updated'`, damit klar ist, was sie tun.
- **Listener einfach halten**: Setzen Sie keine langsamen oder komplexen Aufgaben in Listener – halten Sie Ihre App schnell.
- **Events testen**: Lösen Sie sie manuell aus, um sicherzustellen, dass Listener wie erwartet funktionieren.
- **Events klug einsetzen**: Sie sind großartig für Entkopplung, aber zu viele können Ihren Code schwer verständlich machen – verwenden Sie sie, wenn es Sinn ergibt.

Das Events-System in Flight PHP, mit `Flight::onEvent()` und `Flight::triggerEvent()`, bietet Ihnen einen einfachen, aber leistungsstarken Weg, flexible Anwendungen zu bauen. Indem verschiedene Teile Ihrer App durch Events miteinander kommunizieren, können Sie Ihren Code organisiert, wiederverwendbar und einfach erweiterbar halten. Ob Sie Aktionen protokollieren, Benachrichtigungen senden oder Updates verwalten, Events helfen Ihnen dabei, ohne Ihre Logik zu verkomplizieren. Und mit der Möglichkeit, diese Methoden zu überschreiben, haben Sie die Freiheit, das System an Ihre Bedürfnisse anzupassen. Beginnen Sie klein mit einem einzelnen Event und beobachten Sie, wie es die Struktur Ihrer App verändert!

## Eingebauten Events

Flight PHP kommt mit ein paar eingebauten Events, die Sie nutzen können, um sich in den Lebenszyklus des Frameworks einzuhaken. Diese Events werden an spezifischen Punkten im Request/Response-Zyklus ausgelöst, sodass Sie benutzerdefinierte Logik ausführen können, wenn bestimmte Aktionen erfolgen.

### Liste der eingebauten Events
- **flight.request.received**: `function(Request $request)` Wird ausgelöst, wenn eine Anfrage empfangen, geparst und verarbeitet wird.
- **flight.error**: `function(Throwable $exception)` Wird ausgelöst, wenn ein Fehler während des Request-Lebenszyklus auftritt.
- **flight.redirect**: `function(string $url, int $status_code)` Wird ausgelöst, wenn eine Weiterleitung initiiert wird.
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` Wird ausgelöst, wenn der Cache für einen bestimmten Schlüssel überprüft wird und ob es einen Treffer oder Fehlschlag gab.
- **flight.middleware.before**: `function(Route $route)` Wird ausgelöst, nachdem das Before-Middleware ausgeführt wurde.
- **flight.middleware.after**: `function(Route $route)` Wird ausgelöst, nachdem das After-Middleware ausgeführt wurde.
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` Wird ausgelöst, nachdem ein Middleware ausgeführt wurde.
- **flight.route.matched**: `function(Route $route)` Wird ausgelöst, wenn eine Route übereinstimmt, aber noch nicht ausgeführt wird.
- **flight.route.executed**: `function(Route $route, float $executionTime)` Wird ausgelöst, nachdem eine Route ausgeführt und verarbeitet wurde. `$executionTime` ist die Zeit, die es dauerte, die Route auszuführen (z. B. den Controller aufzurufen).
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` Wird ausgelöst, nachdem eine View gerendert wurde. `$executionTime` ist die Zeit, die es dauerte, das Template zu rendern. **Hinweis: Wenn Sie die `render`-Methode überschreiben, müssen Sie dieses Event neu auslösen.**
- **flight.response.sent**: `function(Response $response, float $executionTime)` Wird ausgelöst, nachdem eine Response an den Client gesendet wurde. `$executionTime` ist die Zeit, die es dauerte, die Response aufzubauen.