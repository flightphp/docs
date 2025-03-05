# Ereignissystem in Flight PHP (v3.15.0+)

Flight PHP führt ein leichtgewichtiges und intuitives Ereignissystem ein, mit dem Sie benutzerdefinierte Ereignisse in Ihrer Anwendung registrieren und auslösen können. Mit der Ergänzung von `Flight::onEvent()` und `Flight::triggerEvent()` können Sie nun in Schlüsselereignisse im Lebenszyklus Ihrer App eintauchen oder Ihre eigenen Ereignisse definieren, um Ihren Code modularer und erweiterbarer zu gestalten. Diese Methoden sind Teil von Flight's **mapbaren Methoden**, was bedeutet, dass Sie deren Verhalten nach Ihren Bedürfnissen anpassen können.

Dieser Leitfaden behandelt alles, was Sie wissen müssen, um mit Ereignissen zu beginnen, einschließlich ihrer Wertigkeit, ihrer Nutzung und praktischer Beispiele, um Anfängern ihre Leistungsfähigkeit zu verdeutlichen.

## Warum Ereignisse verwenden?

Ereignisse ermöglichen es Ihnen, verschiedene Teile Ihrer Anwendung zu trennen, sodass sie nicht zu stark voneinander abhängen. Diese Trennung—oft als **Entkopplung** bezeichnet—macht es einfacher, Ihren Code zu aktualisieren, zu erweitern oder zu debuggen. Anstatt alles in einem großen Block zu schreiben, können Sie Ihre Logik in kleinere, unabhängige Teile aufteilen, die auf spezifische Aktionen (Ereignisse) reagieren.

Stellen Sie sich vor, Sie bauen eine Blog-App:
- Wenn ein Benutzer einen Kommentar veröffentlicht, möchten Sie möglicherweise:
  - Den Kommentar in der Datenbank speichern.
  - Eine E-Mail an den Blog-Besitzer senden.
  - Die Aktion aus Sicherheitsgründen protokollieren.

Ohne Ereignisse würden Sie das alles in eine Funktion quetschen. Mit Ereignissen können Sie es aufteilen: Ein Teil speichert den Kommentar, ein anderer löst ein Ereignis wie `'comment.posted'` aus, und separate Listener kümmern sich um die E-Mail und das Protokollieren. Dadurch bleibt Ihr Code sauberer und Sie können Funktionen (wie Benachrichtigungen) hinzufügen oder entfernen, ohne die Kernlogik zu berühren.

### Häufige Anwendungen
- **Protokollierung**: Aktionen wie Anmeldungen oder Fehler protokollieren, ohne Ihren Hauptcode zu überladen.
- **Benachrichtigungen**: E-Mails oder Alarme senden, wenn etwas passiert.
- **Updates**: Caches aktualisieren oder andere Systeme über Änderungen informieren.

## Registrieren von Ereignis-Listenern

Um auf ein Ereignis zu hören, verwenden Sie `Flight::onEvent()`. Diese Methode ermöglicht es Ihnen zu definieren, was passieren soll, wenn ein Ereignis eintritt.

### Syntax
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event`: Ein Name für Ihr Ereignis (z.B. `'user.login'`).
- `$callback`: Die Funktion, die ausgeführt wird, wenn das Ereignis ausgelöst wird.

### Wie es funktioniert
Sie "abonnieren" ein Ereignis, indem Sie Flight mitteilen, was zu tun ist, wenn es eintritt. Der Callback kann Argumente akzeptieren, die vom Ereignisauslöser übergeben werden.

Das Ereignissystem von Flight ist synchron, was bedeutet, dass jeder Ereignis-Listener nacheinander ausgeführt wird, einer nach dem anderen. Wenn Sie ein Ereignis auslösen, laufen alle registrierten Listener für dieses Ereignis bis zur Vollständigkeit, bevor Ihr Code fortfährt. Dies ist wichtig zu verstehen, da es sich von asynchronen Ereignissystemen unterscheidet, bei denen Listener parallel oder zu einem späteren Zeitpunkt ausgeführt werden können.

### Einfaches Beispiel
```php
Flight::onEvent('user.login', function ($username) {
    echo "Willkommen zurück, $username!";
});
```
Hier wird bei Auslösung des Ereignisses `'user.login'` der Benutzer namentlich begrüßt.

### Wichtige Punkte
- Sie können mehrere Listener für dasselbe Ereignis hinzufügen—sie werden in der Reihenfolge ausgeführt, in der Sie sie registriert haben.
- Der Callback kann eine Funktion, eine anonyme Funktion oder eine Methode aus einer Klasse sein.

## Auslösen von Ereignissen

Um ein Ereignis auszulösen, verwenden Sie `Flight::triggerEvent()`. Dies teilt Flight mit, alle für dieses Ereignis registrierten Listener auszuführen und alle Daten, die Sie bereitstellen, weiterzuleiten.

### Syntax
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`: Der Ereignisname, den Sie auslösen (muss mit einem registrierten Ereignis übereinstimmen).
- `...$args`: Optionale Argumente, die an die Listener gesendet werden (können beliebig viele Argumente sein).

### Einfaches Beispiel
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Dies löst das Ereignis `'user.login'` aus und sendet `'alice'` an den zuvor definierten Listener, der ausgeben wird: `Willkommen zurück, alice!`.

### Wichtige Punkte
- Wenn keine Listener registriert sind, passiert nichts—Ihre App wird nicht abstürzen.
- Verwenden Sie den Spread-Operator (`...`), um flexibel mehrere Argumente zu übergeben.

### Registrieren von Ereignis-Listenern

...

**Weitere Listener stoppen**:
Wenn ein Listener `false` zurückgibt, werden keine zusätzlichen Listener für dieses Ereignis ausgeführt. Dies ermöglicht es Ihnen, die Ereigniskette basierend auf bestimmten Bedingungen zu stoppen. Denken Sie daran, dass die Reihenfolge der Listener wichtig ist, da der erste, der `false` zurückgibt, den Rest am Ausführen hindert.

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

`Flight::onEvent()` und `Flight::triggerEvent()` sind verfügbar, um [erweitert](/learn/extending) zu werden, was bedeutet, dass Sie neu definieren können, wie sie funktionieren. Dies ist großartig für fortgeschrittene Benutzer, die das Ereignissystem anpassen möchten, z.B. durch Hinzufügen von Protokollierung oder Ändern der Art und Weise, wie Ereignisse ausgelöst werden.

### Beispiel: Anpassen von `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Protokolliere jede Ereignisregistrierung
    error_log("Neuer Ereignis-Listener hinzugefügt für: $event");
    // Rufe das Standardverhalten auf (vorausgesetzt, es gibt ein internes Ereignissystem)
    Flight::_onEvent($event, $callback);
});
```
Jetzt wird jedes Mal, wenn Sie ein Ereignis registrieren, dies protokolliert, bevor es fortfährt.

### Warum überschreiben?
- Debugging oder Überwachung hinzufügen.
- Ereignisse in bestimmten Umgebungen einschränken (z.B. in der Testumgebung deaktivieren).
- Mit einer anderen Ereignisbibliothek integrieren.

## Wo Sie Ihre Ereignisse platzieren sollten

Als Anfänger fragen Sie sich vielleicht: *Wo registriere ich all diese Ereignisse in meiner App?* Die Einfachheit von Flight bedeutet, dass es keine strenge Regel gibt—Sie können sie überall dort platzieren, wo es für Ihr Projekt sinnvoll ist. Allerdings hilft eine organisierte Struktur, Ihren Code zu pflegen, wenn Ihre App wächst. Hier sind einige praktische Optionen und Best Practices, die auf die leichte Natur von Flight zugeschnitten sind:

### Option 1: In Ihrem Haupt-`index.php`
Für kleine Apps oder schnelle Prototypen können Sie Ereignisse direkt in Ihrer `index.php`-Datei neben Ihren Routen registrieren. Dies hält alles an einem Ort, was in Ordnung ist, wenn Einfachheit Ihre Priorität ist.

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
    echo "Angemeldet!";
});

Flight::start();
```
- **Vorteile**: Einfach, keine zusätzlichen Dateien, ideal für kleine Projekte.
- **Nachteile**: Kann unübersichtlich werden, wenn Ihre App mit mehr Ereignissen und Routen wächst.

### Option 2: Eine separate `events.php`-Datei
Für eine etwas größere App sollten Sie in Betracht ziehen, die Ereignisregistrierungen in eine separate Datei wie `app/config/events.php` zu verschieben. Binden Sie diese Datei in Ihre `index.php` vor Ihren Routen ein. Dies ahmt nach, wie Routen oft in Flight-Projekten in `app/config/routes.php` organisiert sind.

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
    echo "Angemeldet!";
});

Flight::start();
```
- **Vorteile**: Hält `index.php` auf das Routing konzentriert, organisiert Ereignisse logisch, leicht zu finden und zu bearbeiten.
- **Nachteile**: Fügt ein klein wenig Struktur hinzu, was sich für sehr kleine Apps übertrieben anfühlen kann.

### Option 3: Nahe dem Ort, wo sie ausgelöst werden
Ein weiterer Ansatz besteht darin, Ereignisse in der Nähe von dort zu registrieren, wo sie ausgelöst werden, z. B. innerhalb eines Controllers oder einer Routen-Definition. Dies funktioniert gut, wenn ein Ereignis spezifisch für einen Teil Ihrer App ist.

```php
Flight::route('/signup', function () {
    // Ereignis hier registrieren
    Flight::onEvent('user.registered', function ($email) {
        echo "Willkommens-E-Mail gesendet an $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Angemeldet!";
});
```
- **Vorteile**: Hält verwandte Codes zusammen, gut für isolierte Funktionen.
- **Nachteile**: Verstreut Ereignisregistrierungen, was es schwieriger macht, alle Ereignisse auf einmal zu sehen; das Risiko doppelter Registrierungen, wenn man nicht vorsichtig ist.

### Best Practice für Flight
- **Einfach beginnen**: Für winzige Apps setzen Sie Ereignisse in `index.php`. Es ist schnell und passt zur Minimalismus von Flight.
- **Intelligent wachsen**: Wenn Ihre App wächst (z. B. mehr als 5-10 Ereignisse), verwenden Sie eine Datei `app/config/events.php`. Es ist ein natürlicher Schritt, ähnlich wie die Organisation von Routen, und hält Ihren Code ordentlich, ohne komplexe Frameworks hinzuzufügen.
- **Vermeiden Sie Überengineering**: Erstellen Sie keine vollwertige „Ereignis-Manager“-Klasse oder -Verzeichnis, es sei denn, Ihre App wird riesig—Flight gedeiht in Einfachheit, also halten Sie es leichtgewichtig.

### Tipp: Gruppieren nach Zweck
In `events.php` gruppieren Sie verwandte Ereignisse (z.B. alle benutzerbezogenen Ereignisse zusammen) mit Kommentaren zur Klarheit:

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

Diese Struktur skalierbar und bleibt für Anfänger freundlich.

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
    $username = 'bob'; // Angenommen, dies kommt von einem Formular
    Flight::triggerEvent('user.login', $username);
    echo "Hallo, $username!";
});
```
**Warum es nützlich ist**: Der Anmeldecode muss nichts über das Protokollieren wissen—er löst einfach das Ereignis aus. Später können Sie mehr Zuhörer hinzufügen (z.B. eine Willkommens-E-Mail senden), ohne die Route zu ändern.

### Beispiel 2: Benachrichtigen über neue Benutzer
```php
// Listener für neue Registrierungen
Flight::onEvent('user.registered', function ($email, $name) {
    // Simulation des Sendens einer E-Mail
    echo "E-Mail gesendet an $email: Willkommen, $name!";
});

// Auslösen, wenn jemand sich anmeldet
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Danke für Ihre Anmeldung!";
});
```
**Warum es nützlich ist**: Die Anmeldelogik konzentriert sich auf das Erstellen des Benutzers, während das Ereignis sich um die Benachrichtigungen kümmert. Später könnten Sie mehr Listener hinzufügen (z.B. das Protokollieren der Anmeldung).

### Beispiel 3: Leeren eines Caches
```php
// Listener zum Leeren eines Caches
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // Leeren des Sitzungs-caches, falls zutreffend
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

- **Ereignisse klar benennen**: Verwenden Sie spezifische Namen wie `'user.login'` oder `'page.updated'`, sodass klar nachvollzogen werden kann, was sie tun.
- **Listener einfach halten**: Langsame oder komplexe Aufgaben sollten nicht in Listenern stehen—halten Sie Ihre App schnell.
- **Testen Sie Ihre Ereignisse**: Lösen Sie sie manuell aus, um sicherzustellen, dass Listener wie erwartet funktionieren.
- **Verwenden Sie Ereignisse weise**: Sie sind großartig für die Entkopplung, aber zu viele können Ihren Code schwer nachvollziehbar machen—verwenden Sie sie, wenn es sinnvoll ist.

Das Ereignissystem in Flight PHP, mit `Flight::onEvent()` und `Flight::triggerEvent()`, bietet Ihnen eine einfache und dennoch leistungsstarke Möglichkeit, flexible Anwendungen zu erstellen. Indem Sie verschiedenen Teilen Ihrer App erlauben, über Ereignisse miteinander zu kommunizieren, können Sie Ihren Code organisiert, wiederverwendbar und leicht erweiterbar halten. Ob Sie Aktionen protokollieren, Benachrichtigungen senden oder Updates verwalten, Ereignisse helfen Ihnen, dies zu tun, ohne Ihre Logik zu verkomplizieren. Darüber hinaus haben Sie mit der Möglichkeit, diese Methoden zu überschreiben, die Freiheit, das System an Ihre Bedürfnisse anzupassen. Beginnen Sie klein mit einem einzelnen Ereignis und beobachten Sie, wie es die Struktur Ihrer App transformiert!

## Eingebaute Ereignisse

Flight PHP verfügt über einige eingebaute Ereignisse, die Sie verwenden können, um in den Lebenszyklus des Frameworks einzugreifen. Diese Ereignisse werden an bestimmten Punkten im Anfrage-/Antwortzyklus ausgelöst, sodass Sie benutzerdefinierte Logik ausführen können, wenn bestimmte Aktionen auftreten.

### Liste der eingebauten Ereignisse
- `flight.request.received`: Wird ausgelöst, wenn eine Anfrage empfangen, analysiert und verarbeitet wird.
- `flight.route.middleware.before`: Wird ausgelöst, nachdem das "before"-Middleware ausgeführt wurde.
- `flight.route.middleware.after`: Wird ausgelöst, nachdem das "after"-Middleware ausgeführt wurde.
- `flight.route.executed`: Wird ausgelöst, nachdem eine Route ausgeführt und verarbeitet wurde.
- `flight.response.sent`: Wird ausgelöst, nachdem eine Antwort an den Client gesendet wurde.