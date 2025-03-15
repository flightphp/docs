# Ereignissystem in Flight PHP (v3.15.0+)

Flight PHP führt ein leichtgewichtiges und intuitives Ereignissystem ein, mit dem Sie benutzerdefinierte Ereignisse in Ihrer Anwendung registrieren und auslösen können. Mit der Hinzufügung von `Flight::onEvent()` und `Flight::triggerEvent()` können Sie jetzt in wichtige Momente des Lebenszyklus Ihrer App eingreifen oder eigene Ereignisse definieren, um Ihren Code modularer und erweiterbar zu gestalten. Diese Methoden sind Teil von Flight's **mappable methods**, was bedeutet, dass Sie ihr Verhalten an Ihre Bedürfnisse anpassen können.

Dieser Leitfaden behandelt alles, was Sie wissen müssen, um mit Ereignissen zu beginnen, einschließlich warum sie wertvoll sind, wie man sie verwendet und praktische Beispiele, um Anfängern ihre Stärke näherzubringen.

## Warum Ereignisse verwenden?

Ereignisse ermöglichen es Ihnen, verschiedene Teile Ihrer Anwendung zu trennen, sodass sie nicht zu stark voneinander abhängen. Diese Trennung—oft als **Entkopplung** bezeichnet—macht Ihren Code einfacher zu aktualisieren, zu erweitern oder zu debuggen. Anstatt alles in einem großen Stück zu schreiben, können Sie Ihre Logik in kleinere, unabhängige Teile aufteilen, die auf spezifische Aktionen (Ereignisse) reagieren.

Stellen Sie sich vor, Sie bauen eine Blog-App:
- Wenn ein Benutzer einen Kommentar postet, möchten Sie vielleicht:
  - Den Kommentar in der Datenbank speichern.
  - Eine E-Mail an den Blogbesitzer senden.
  - Die Aktion aus Sicherheitsgründen protokollieren.

Ohne Ereignisse würden Sie all dies in eine Funktion quetschen. Mit Ereignissen können Sie es aufteilen: Ein Teil speichert den Kommentar, ein anderer löst ein Ereignis wie `'comment.posted'` aus, und separate Listener übernehmen die E-Mail und das Protokollieren. Dies hält Ihren Code sauberer und ermöglicht es Ihnen, Funktionen (wie Benachrichtigungen) hinzuzufügen oder zu entfernen, ohne die Kernlogik anzufassen.

### Häufige Verwendungen
- **Protokollierung**: Aktionen wie Anmeldungen oder Fehler protokollieren, ohne Ihren Hauptcode zu überladen.
- **Benachrichtigungen**: E-Mails oder Warnungen senden, wenn etwas passiert.
- **Aktualisierungen**: Caches aktualisieren oder andere Systeme über Änderungen informieren.

## Registrieren von Ereignis-Listenern

Um auf ein Ereignis zu hören, verwenden Sie `Flight::onEvent()`. Diese Methode ermöglicht es Ihnen, festzulegen, was passieren soll, wenn ein Ereignis auftritt.

### Syntax
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event`: Ein Name für Ihr Ereignis (z. B. `'user.login'`).
- `$callback`: Die Funktion, die ausgeführt werden soll, wenn das Ereignis ausgelöst wird.

### Wie es funktioniert
Sie "abonnieren" ein Ereignis, indem Sie Flight mitteilen, was zu tun ist, wenn es eintritt. Der Callback kann Argumente akzeptieren, die vom Ereignis-Trigger übergeben werden.

Das Ereignissystem von Flight ist synchron, was bedeutet, dass jeder Ereignis-Listener der Reihe nach ausgeführt wird, einer nach dem anderen. Wenn Sie ein Ereignis auslösen, werden alle registrierten Listener für dieses Ereignis zu Ende ausgeführt, bevor Ihr Code fortgesetzt wird. Dies ist wichtig zu verstehen, da es sich von asynchronen Ereignissystemen unterscheidet, bei denen Listener parallel oder zu einem späteren Zeitpunkt ausgeführt werden könnten.

### Einfaches Beispiel
```php
Flight::onEvent('user.login', function ($username) {
    echo "Willkommen zurück, $username!";
});
```
Hier, wenn das Ereignis `'user.login'` ausgelöst wird, wird der Benutzer namentlich begrüßt.

### Wichtige Punkte
- Sie können mehrere Listener für dasselbe Ereignis hinzufügen—sie werden in der Reihenfolge ausgeführt, in der Sie sie registriert haben.
- Der Callback kann eine Funktion, eine anonyme Funktion oder eine Methode aus einer Klasse sein.

## Auslösen von Ereignissen

Um ein Ereignis auszulösen, verwenden Sie `Flight::triggerEvent()`. Dies sagt Flight, dass alle für dieses Ereignis registrierten Listener ausgeführt werden sollen, und dabei alle Daten übergeben werden, die Sie bereitstellen.

### Syntax
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`: Der Name des Ereignisses, das Sie auslösen (muss mit einem registrierten Ereignis übereinstimmen).
- `...$args`: Optionale Argumente, die an die Listener gesendet werden sollen (kann beliebig viele Argumente sein).

### Einfaches Beispiel
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Dies löst das Ereignis `'user.login'` aus und sendet `'alice'` an den zuvor definierten Listener, der Folgendes ausgeben wird: `Willkommen zurück, alice!`.

### Wichtige Punkte
- Wenn keine Listener registriert sind, passiert nichts—Ihre App wird nicht abstürzen.
- Verwenden Sie den Spread-Operator (`...`), um mehrere Argumente flexibel zu übergeben.

### Registrieren von Ereignis-Listenern

...

**Weitere Listener stoppen**:
Wenn ein Listener `false` zurückgibt, werden keine zusätzlichen Listener für dieses Ereignis ausgeführt. Dies ermöglicht es Ihnen, die Ereigniskette unter bestimmten Bedingungen zu stoppen. Denken Sie daran, die Reihenfolge der Listener ist wichtig, da der erste, der `false` zurückgibt, die Ausführung der restlichen Listener stoppt.

**Beispiel**:
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // Stoppt nachfolgende Listener
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // diese wird niemals gesendet
});
```

## Überschreiben von Ereignismethoden

`Flight::onEvent()` und `Flight::triggerEvent()` sind verfügbar, um [erweitert](/learn/extending) zu werden, was bedeutet, dass Sie neu definieren können, wie sie funktionieren. Dies ist großartig für fortgeschrittene Benutzer, die das Ereignissystem anpassen möchten, z. B. um Protokollierung hinzuzufügen oder zu ändern, wie Ereignisse versendet werden.

### Beispiel: Anpassen von `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Protokolliere jede Ereignisregistrierung
    error_log("Neuer Ereignislistener hinzugefügt für: $event");
    // Rufe das Standardverhalten auf (unter der Annahme, dass es ein internes Ereignissystem gibt)
    Flight::_onEvent($event, $callback);
});
```
Jetzt protokolliert es jedes Mal, wenn Sie ein Ereignis registrieren, bevor es fortfährt.

### Warum überschreiben?
- Fügen Sie Debugging oder Monitoring hinzu.
- Beschränken Sie Ereignisse in bestimmten Umgebungen (z. B. deaktivieren in der Testumgebung).
- Integrieren Sie eine andere Ereignisbibliothek.

## Wo Sie Ihre Ereignisse platzieren sollten

Als Anfänger fragen Sie sich vielleicht: *Wo registriere ich all diese Ereignisse in meiner App?* Die einfachen Regeln von Flight bedeuten, dass es kein strenges Regelwerk gibt—Sie können sie dort platzieren, wo es für Ihr Projekt sinnvoll ist. Dennoch hilft es, sie organisiert zu halten, damit Sie Ihren Code verwalten können, wenn Ihre App wächst. Hier sind einige praktische Optionen und Best Practices, angepasst an die Leichtigkeit von Flight:

### Option 1: In Ihrer Haupt-`index.php`
Für kleine Apps oder schnelle Prototypen können Sie Ereignisse direkt in Ihrer `index.php`-Datei zusammen mit Ihren Routen registrieren. Das hält alles an einem Ort, was in Ordnung ist, wenn Einfachheit Ihre Priorität ist.

```php
require 'vendor/autoload.php';

// Ereignisse registrieren
Flight::onEvent('user.login', function ($username) {
    error_log("$username hat sich um " . date('Y-m-d H:i:s') . " angemeldet");
});

// Routen definieren
Flight::route('/login', function () {
    $username = 'bob'; 
    Flight::triggerEvent('user.login', $username);
    echo "Eingeloggt!";
});

Flight::start();
```
- **Vorteile**: Einfach, keine zusätzlichen Dateien, großartig für kleine Projekte.
- **Nachteile**: Kann chaotisch werden, wenn Ihre App mit mehr Ereignissen und Routen wächst.

### Option 2: Eine separate `events.php`-Datei
Für eine etwas größere App sollten Sie in Betracht ziehen, die Ereignisregistrierungen in einer speziellen Datei wie `app/config/events.php` zu verschieben. Binden Sie diese Datei in Ihre `index.php` ein, bevor Sie Ihre Routen definieren. Dies ahmt die Organisation von Routen in `app/config/routes.php` in Flight-Projekten nach.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username hat sich um " . date('Y-m-d H:i:s') . " angemeldet");
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "E-Mail gesendet an $email: Willkommen, $name!";
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob'; 
    Flight::triggerEvent('user.login', $username);
    echo "Eingeloggt!";
});

Flight::start();
```
- **Vorteile**: Hält `index.php` auf Routing konzentriert, organisiert Ereignisse logisch, leicht zu finden und zu bearbeiten.
- **Nachteile**: Fügt ein kleines bisschen Struktur hinzu, was sich für sehr kleine Apps übertrieben anfühlen könnte.

### Option 3: In der Nähe, wo sie ausgelöst werden
Ein anderer Ansatz besteht darin, Ereignisse dort zu registrieren, wo sie ausgelöst werden, z. B. in einem Controller oder einer Routenbeschreibung. Dies funktioniert gut, wenn ein Ereignis spezifisch für einen Teil Ihrer App ist.

```php
Flight::route('/signup', function () {
    // Ereignis hier registrieren
    Flight::onEvent('user.registered', function ($email) {
        echo "Willkommens-E-Mail an $email gesendet!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Angemeldet!";
});
```
- **Vorteile**: Hält verwandten Code zusammen, gut für isolierte Funktionen.
- **Nachteile**: Streut Ereignisregistrierungen, wodurch es schwieriger wird, alle Ereignisse auf einmal zu sehen; Risiken von doppelten Registrierungen, wenn man nicht vorsichtig ist.

### Best Practice für Flight
- **Beginnen Sie einfach**: Für winzige Apps, legen Sie Ereignisse in `index.php`. Es ist schnell und entspricht der Minimalismus von Flight.
- **Intelligent wachsen**: Wenn Ihre App wächst (z. B. mehr als 5-10 Ereignisse), verwenden Sie eine `app/config/events.php`-Datei. Es ist ein natürlicher nächster Schritt, ähnlich der Organisation von Routen, und hält Ihren Code aufgeräumt, ohne komplexe Frameworks hinzuzufügen.
- **Überengineering vermeiden**: Erstellen Sie keine vollwertige „Ereignismanager“-Klasse oder -Verzeichnis, es sei denn, Ihre App wird riesig—Flight gedeiht in Einfachheit, also halten Sie es leichtgewichtig.

### Tipp: Gruppierung nach Zweck
In der `events.php` sollten verwandte Ereignisse (z. B. alle benutzerbezogenen Ereignisse zusammen) mit Kommentaren zur Klarheit gruppiert werden:

```php
// app/config/events.php
// Benutzerereignisse
Flight::onEvent('user.login', function ($username) {
    error_log("$username hat sich angemeldet");
});
Flight::onEvent('user.registered', function ($email) {
    echo "Willkommen bei $email!";
});

// Seitenereignisse
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);
});
```

Diese Struktur skaliert gut und bleibt anfängerfreundlich.

## Beispiele für Anfänger

Lassen Sie uns einige reale Szenarien durchgehen, um zu zeigen, wie Ereignisse funktionieren und warum sie hilfreich sind.

### Beispiel 1: Protokollierung einer Benutzeranmeldung
```php
// Schritt 1: Listener registrieren
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username hat sich um $time angemeldet");
});

// Schritt 2: Auslösen in Ihrer App
Flight::route('/login', function () {
    $username = 'bob'; // Angenommen, dies stammt aus einem Formular
    Flight::triggerEvent('user.login', $username);
    echo "Hallo, $username!";
});
```
**Warum es nützlich ist**: Der Anmeldecode muss nicht über die Protokollierung Bescheid wissen—er löst einfach das Ereignis aus. Sie können später weitere Listener hinzufügen (z. B. eine Willkommens-E-Mail senden), ohne die Route zu ändern.

### Beispiel 2: Benachrichtigung über neue Benutzer
```php
// Listener für neue Registrierung
Flight::onEvent('user.registered', function ($email, $name) {
    // Simuliert das Senden einer E-Mail
    echo "E-Mail an $email gesendet: Willkommen, $name!";
});

// Auslösen, wenn sich jemand anmeldet
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Danke für Ihre Anmeldung!";
});
```
**Warum es nützlich ist**: Die Registrierungslogik konzentriert sich auf die Erstellung des Benutzers, während das Ereignis die Benachrichtigungen verarbeitet. Sie könnten später mehr Listener hinzufügen (z. B. die Anmeldung protokollieren).

### Beispiel 3: Leeren eines Caches
```php
// Listener zum Löschen eines Caches
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // Clears session cache if applicable
    echo "Cache für Seite $pageId gelöscht.";
});

// Auslösen, wenn eine Seite bearbeitet wird
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Angenommen, wir haben die Seite aktualisiert
    Flight::triggerEvent('page.updated', $pageId);
    echo "Seite $pageId aktualisiert.";
});
```
**Warum es nützlich ist**: Der Bearbeitungscode kümmert sich nicht um das Caching—er signalisiert einfach das Update. Andere Teile der App können nach Bedarf reagieren.

## Best Practices

- **Ereignisse klar benennen**: Verwenden Sie spezifische Namen wie `'user.login'` oder `'page.updated'`, damit offensichtlich ist, was sie tun.
- **Halten Sie Listener einfach**: Legen Sie keine langsamen oder komplexen Aufgaben in Listenern—halten Sie Ihre App schnell.
- **Testen Sie Ihre Ereignisse**: Lösen Sie sie manuell aus, um sicherzustellen, dass die Listener wie erwartet funktionieren.
- **Verwenden Sie Ereignisse weise**: Sie sind großartig für die Entkopplung, aber zu viele können Ihren Code schwer zu folgen machen—verwenden Sie sie, wenn es sinnvoll ist.

Das Ereignissystem in Flight PHP, mit `Flight::onEvent()` und `Flight::triggerEvent()`, bietet Ihnen eine einfache, aber leistungsstarke Möglichkeit, flexible Anwendungen zu erstellen. Indem Sie verschiedenen Teilen Ihrer App ermöglichen, über Ereignisse miteinander zu kommunizieren, können Sie Ihren Code organisiert, wiederverwendbar und leicht erweiterbar halten. Egal, ob Sie Aktionen protokollieren, Benachrichtigungen senden oder Updates verwalten, Ereignisse helfen Ihnen dabei, ohne die Logik zu verknüpfen. Darüber hinaus haben Sie die Freiheit, diese Methoden zu überschreiben, um das System an Ihre Bedürfnisse anzupassen. Beginnen Sie klein mit einem einzigen Ereignis, und beobachten Sie, wie es die Struktur Ihrer App transformiert!

## Eingebaute Ereignisse

Flight PHP kommt mit einigen integrierten Ereignissen, die Sie verwenden können, um in den Lebenszyklus des Frameworks einzuhaken. Diese Ereignisse werden an bestimmten Punkten im Anfrage-/Antwortzyklus ausgelöst, sodass Sie benutzerdefinierte Logik ausführen können, wenn bestimmte Aktionen auftreten.

### Liste der eingebauten Ereignisse
- **flight.request.received**: `function(Request $request)` Wird ausgelöst, wenn eine Anfrage empfangen, geparst und verarbeitet wird.
- **flight.error**: `function(Throwable $exception)` Wird ausgelöst, wenn ein Fehler während des Lebenszyklus der Anfrage auftritt.
- **flight.redirect**: `function(string $url, int $status_code)` Wird ausgelöst, wenn eine Weiterleitung initiiert wird.
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` Wird ausgelöst, wenn der Cache für einen bestimmten Schlüssel überprüft wird und ob der Cache einen Treffer oder ein Fehlschlagen hat.
- **flight.middleware.before**: `function(Route $route)` Wird ausgelöst, nachdem die Middleware vor der Ausführung ausgeführt wurde.
- **flight.middleware.after**: `function(Route $route)` Wird ausgelöst, nachdem die Middleware nach der Ausführung ausgeführt wurde.
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` Wird ausgelöst, nachdem eine Middleware ausgeführt wurde.
- **flight.route.matched**: `function(Route $route)` Wird ausgelöst, wenn eine Route übereinstimmt, aber noch nicht ausgeführt wird.
- **flight.route.executed**: `function(Route $route, float $executionTime)` Wird ausgelöst, nachdem eine Route ausgeführt und verarbeitet wurde. `$executionTime` ist die Zeit, die benötigt wurde, um die Route auszuführen (Controller aufzurufen usw.).
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` Wird ausgelöst, nachdem eine Ansicht gerendert wurde. `$executionTime` ist die Zeit, die zum Rendern der Vorlage benötigt wurde. **Hinweis: Wenn Sie die Methode `render` überschreiben, müssen Sie dieses Ereignis erneut auslösen.**
- **flight.response.sent**: `function(Response $response, float $executionTime)` Wird ausgelöst, nachdem eine Antwort an den Client gesendet wurde. `$executionTime` ist die Zeit, die benötigt wurde, um die Antwort zu erstellen.