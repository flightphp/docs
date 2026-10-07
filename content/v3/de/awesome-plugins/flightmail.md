# FlightMail

> **Drittanbieter-Plugin** – gepflegt von [Ryan Stubbs](https://ryanstubbs.co.uk) ([ryanstubbs/flightmail](https://github.com/ryanstubbs/flightmail), MIT-lizenziert). Nicht Teil des Flight-Kerns – bitte melde Probleme im [GitHub-Repository](https://github.com/ryanstubbs/flightmail/issues).

[ryanstubbs/flightmail](https://github.com/ryanstubbs/flightmail) ermöglicht es dir, E-Mails aus deiner Flight-App ohne Kopfschmerzen zu versenden. Es kapselt **Symfony Mailer** – die am härtesten getestete Mail-Bibliothek in PHP – und lässt es sich wie ein Teil von Flight anfühlen. Eine Zeile zum Installieren, eine fließende Kette zum Senden:

```php
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('You did it!')
    ->text('Your first email is on its way.')
    ->send();
```

## Features

- **Beliebiger Anbieter, eine Zeile pro Anbieter.** SMTP, Postmark, Sendgrid, Mailgun, Amazon SES, Brevo und weitere funktionieren über einfache DSN-Strings.
- **Mehrere Anbieter gleichzeitig nutzen.** Transaktions-Mails über Postmark, Newsletter über dein eigenes SMTP – wähle pro Nachricht.
- **Vorlagen, wenn du sie möchtest.** Rendere Inhalte mit Twig oder Latte. Keine Vorlagen gewünscht? Einfach Strings übergeben und nichts Extra installieren.
- **Politur beim Senden.** Optionale CSS-Inline-Umwandlung und automatische Klartext-Teile aus deinem HTML, unterstützt von Bibliotheken, die du nur installierst, wenn du sie nutzt.
- **Langweilig im besten Sinne.** Träge Verbindungen, klare Fehler statt stillschweigend verschluckter Mails, und alles ist austauschbar, falls du etwas Eigenes brauchst.

## Anforderungen

| Was               | Version                                |
| ----------------- | -------------------------------------- |
| PHP               | 8.2 oder neuer                         |
| Flight PHP        | Kern ^3.15                             |
| Symfony Mailer    | ^7.2 oder ^8.0 (automatisch installiert) |

## Installation

```bash
composer require ryanstubbs/flightmail
```

Das wars für den Versand von Klartext- und HTML-E-Mails. Das Rendern von Vorlagen ist optional – füge nur eine Engine hinzu, wenn du sie nutzen willst:

```bash
composer require twig/twig      # für .twig-Vorlagen
composer require latte/latte    # für .latte-Vorlagen
```

Zwei weitere optionale Bibliotheken unterstützen die erweiterten Funktionen zum Sendezeitpunkt, die [unten](#styling-html-and-generating-text-parts) beschrieben werden:

```bash
composer require pelago/emogrifier         # für CSS-Inlining ("inline_css")
composer require league/html-to-markdown   # für Markdown-Textteile ("text_from_html")
```

Alle können nebeneinander installiert werden; FlightMail wählt anhand deiner Konfiguration die passende aus.

## Deine erste E-Mail

Füge dies zu deiner Bootstrap-Datei hinzu (am selben Ort, an dem du Routen definierst):

```php
<?php
require 'vendor/autoload.php';

use ryanstubbs\FlightMail\MailPlugin;

// Teile FlightMail mit, von welcher Adresse und über welchen Transport E-Mails gesendet werden.
MailPlugin::install([
    'dsns' => [
        'default' => 'smtp://user:pass@localhost:1025',
    ],
    'from' => 'no-reply@example.com',
]);

Flight::route('/signup', function () {
    Flight::mail()->compose()
        ->to('new-user@example.com')
        ->subject('Welcome aboard!')
        ->html('<h1>Welcome!</h1><p>We are glad you are here.</p>')
        ->send();
});

Flight::start();
```

Du nutzt das [Flight PHP-Skelett](https://github.com/flightphp/skeleton)? Registriere stattdessen in `app/config/services.php` im Instanz-Stil:

```php
use ryanstubbs\FlightMail\MailPlugin;

MailPlugin::register($app, [
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'from' => 'no-reply@example.com',
]);
```

Beide Stile stellen denselben Mailer bereit: `Flight::mail()` und `$app->mail()` sind austauschbar.

> **Lokal testen?** Falls dein Projekt in [DDEV](https://ddev.com) läuft, setze die DSN auf `smtp://127.0.0.1:1025` und lies jede erfasste E-Mail in Mailpit unter `http://<project>.ddev.site:8025`. Nichts verlässt deinen Rechner.

## E-Mails senden

### Klartext-Strings (keine Vorlagen-Engine nötig)

`->text()` und `->html()` akzeptieren rohe Strings und benötigen nichts weiter Installiertes:

```php
Flight::mail()->compose()
    ->to('ops@example.com')
    ->subject('Backup finished')
    ->text('Nightly backup completed in 42 minutes.')
    ->send();

Flight::mail()->compose()
    ->to('billing@example.com')
    ->subject('Invoice #123')
    ->html('<h1>Invoice #123</h1><p>Total due: $42.00</p>')
    ->send();
```

### Twig-Vorlagen

```php
// welcome.html.twig enthält: Hallo {{ name }}, danke für deine Anmeldung!
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.html.twig', ['name' => 'Ryan'])
    ->send();
```

### Latte-Vorlagen

Gleiche Idee, `.latte`-Erweiterung:

```php
// welcome.latte enthält: Hallo {$name}, danke für deine Anmeldung!
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.latte', ['name' => 'Ryan'])
    ->send();
```

### HTML + Klartext zusammen

Bewährte Praxis für die Zustellbarkeit – gib Mail-Clients beide Versionen:

```php
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.html.twig', ['name' => 'Ryan'])     // umfangreiche Version
    ->textTemplate('welcome.txt.twig', ['name' => 'Ryan'])  // Fallback-Version
    ->send();
```

Ein paar Dinge, die du über Vorlagen wissen solltest:

- Sie rendern **lazy** (träge) zum Sendezeitpunkt – jetzt verfassen, später rendern.
- Die Engine wird anhand der Erweiterung gewählt: `.twig` → Twig, `.latte` → Latte, alles andere → dein konfigurierter Standard (`renderer`-Option).
- Ein expliziter `->html()`- oder `->text()`-Body hat immer Vorrang vor einer Vorlage, du kannst also eine Standardvorlage festlegen und pro Nachricht überschreiben.

## HTML stylen und Textteile generieren

Zwei optionale Erweiterungen zum Sendezeitpunkt, beide standardmäßig deaktiviert und beide unterstützt von Bibliotheken, die du nur installierst, wenn du sie möchtest:

| Funktion                  | Installation                     | Konfigurationsschlüssel |
| ------------------------- | -------------------------------- | ----------------------- |
| CSS-Inline-Umwandlung     | `pelago/emogrifier`              | `inline_css`            |
| Textteil aus HTML         | `league/html-to-markdown`        | `text_from_html`        |

### CSS in deine HTML-E-Mail einfügen

Gmail und die meisten Webmail-Clients entfernen `<style>`-Blöcke – Inline-`style=""`-Attribute sind das einzige Styling, das sie zuverlässig beachten. Das von Hand zu schreiben ist mühselig; lass [Emogrifier](https://github.com/MyIntervals/emogrifier) das zum Sendezeitpunkt erledigen:

```bash
composer require pelago/emogrifier
```

```php
MailPlugin::install([
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'inline_css' => true,
]);
```

Ist das aktiviert, wird bei jedem HTML-Body das CSS direkt vor dem Senden inline eingefügt – egal ob aus einer Vorlage oder über `->html()`. Eine Nachricht wie `<style>p { color: red; }</style><p>Hi</p>` wird als `<p style="color: red;">Hi</p>` versendet.

Um gemeinsame Stile (Markenfarben, Resets) in jede E-Mail einzufügen, ohne sie in jeder Vorlage zu wiederholen, übergib Regeln direkt oder verweise auf eine Stylesheet-Datei:

```php
'inline_css' => ['css_file' => __DIR__ . '/mail-styles/base.css'],
// oder
'inline_css' => ['css' => '.button { background: #0a84ff; color: #fff; }'],
```

Steuerung pro Nachricht:

```php
$message->inlineCss();          // erzwingt das Inlining für diese eine Nachricht
$message->withoutInlineCss();   // überspringt es, auch wenn es global aktiviert ist
```

### Textteil aus deinem HTML generieren

Bewährte Praxis ist, eine HTML- und eine Klartext-Version zusammen zu senden, aber beides zu schreiben ist mühselig. FlightMail kann den Textteil automatisch aus dem endgültigen HTML ableiten – die einfache Konvertierung benötigt keine zusätzliche Abhängigkeit, da der Konverter mit Symfony Mime geliefert wird:

```php
MailPlugin::install([
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'text_from_html' => true,       // Markdown, wenn möglich, sonst Klartext
]);
```

Modi:

- `true` oder `'auto'` – Markdown-Ausgabe, wenn `league/html-to-markdown` installiert ist, andernfalls einfaches Entfernen von Tags.
- `'markdown'` – Markdown erzwingen (`composer require league/html-to-markdown`; Überschriften werden zu `==`, Links zu `[text](url)`, Fett zu **fett**).
- `'plain'` – entfernt immer Tags; funktioniert ohne zusätzliche Pakete.

Die Generierung läuft nach dem Rendern und dem CSS-Inlining und nur, wenn die Nachricht einen HTML-Body, aber keinen Text-Body hat – ein explizites `->text()` oder `->textTemplate()` hat immer Vorrang. Die Überschreibungen pro Nachricht spiegeln das Inlining:

```php
$message->textFromHtml('plain');    // erzwingt das Entfernen von Tags für diese eine
$message->withoutTextFromHtml();    // Nur-HTML-E-Mail
```

Wenn du einen Modus aktivierst, dessen Bibliothek nicht installiert ist, erhältst du eine klare Fehlermeldung, die das genaue `composer require` nennt – niemals stilles Verschlechtern.

## Einen Anbieter wählen

Anbieter werden über DSN-Strings angeschlossen. Installiere das Bridge-Paket, füge die DSN in `dsns` ein, fertig.

| Anbieter              | Installation                                 | DSN-Beispiel                                 |
| --------------------- | -------------------------------------------- | -------------------------------------------- |
| SMTP                  | integriert                                   | `smtp://user:pass@host:587`                  |
| Sendmail              | integriert                                   | `sendmail://default`                         |
| Dev/null (E-Mails verwerfen) | integriert                                   | `null://null`                                |
| Postmark              | `composer require symfony/postmark-mailer`   | `postmark+api://KEY@api.postmarkapp.com`     |
| Sendgrid              | `composer require symfony/sendgrid-mailer`   | `sendgrid+api://KEY@default`                 |
| Mailgun               | `composer require symfony/mailgun-mailer`    | `mailgun+https://KEY:DOMAIN@api.mailgun.net` |
| Amazon SES            | `composer require symfony/amazon-mailer`     | `ses+https://KEY:SECRET@default`             |
| Brevo                 | `composer require symfony/brevo-mailer`      | `brevo+api://KEY@default`                    |
| MailerSend            | `composer require symfony/mailersend-mailer` | `mailersend+api://KEY@default`               |

Die vollständige Liste findest du in der [Symfony-Mailer-Dokumentation](https://symfony.com/doc/current/mailer.html) – alles, was dort dokumentiert ist, funktioniert hier unverändert.

### Mehrere Anbieter gleichzeitig

Benenne jeden Transport und wähle dann pro Nachricht:

```php
MailPlugin::install([
    'dsns' => [
        'transactional' => 'postmark+api://KEY@api.postmarkapp.com',
        'bulk'          => 'smtp://user:pass@bulk.example.com:587',
    ],
    'from' => 'no-reply@example.com',
]);
```

```php
// Kein ->transport()-Aufruf = erster Schlüssel in "dsns" ("transactional" hier).
Flight::mail()->compose()->to('...')->text('receipt')->send();

// Explizit eine andere Route wählen.
Flight::mail()->compose()->to('...')->text('newsletter')->transport('bulk')->send();
```

## Konfigurationsreferenz

Alles ist optional außer `dsns`.

```php
MailPlugin::install([
    // ERFORDERLICH – Transportname => Symfony-DSN.
    // Der erste Eintrag wird verwendet, wenn eine Nachricht keinen benennt.
    'dsns' => [
        'default' => 'smtp://user:pass@localhost:1025',
    ],

    // Transport, der verwendet wird, wenn eine Nachricht kein explizites ->transport() hat
    // und du nicht den ersten Schlüssel möchtest. Muss in "dsns" existieren.
    'default_transport' => 'default',

    // Globaler Absender. String, Symfony-Adresse oder ['email' => 'Name'].
    // Wird nur angewendet, wenn eine Nachricht kein eigenes ->from() setzt.
    'from' => ['no-reply@example.com' => 'My App'],

    // Standard-Vorlagenengine: 'twig', 'latte' oder ein benutzerdefinierter Name.
    // Wird nur für Vorlagen herangezogen, deren Erweiterung kein registrierter Renderer ist.
    'renderer' => 'twig',

    // Wo Vorlagen liegen, in Reihenfolge durchsucht; plus optionales Cache-Verzeichnis.
    'templates' => [
        'paths' => [__DIR__ . '/mail-templates'],
        'cache' => __DIR__ . '/cache/mail',
    ],

    // Zusätzliche Optionen, die direkt an Twig\Environment übergeben werden.
    'twig' => ['options' => ['strict_variables' => true]],

    // Latte-Engine beim Start anpassen: fn(Latte\Engine $engine): void.
    'latte' => ['setup' => static fn (Latte\Engine $e) => $e->addExtension(new MyExtension())],

    // Body-Erweiterungen zum Sendezeitpunkt (siehe „HTML stylen und Textteile generieren").
    'inline_css' => true,           // oder ['css' => '...', 'css_file' => '...']
    'text_from_html' => true,       // oder 'plain' / 'markdown'

    // Benutzerdefinierte DSN-Schemata, benutzerdefinierte Renderer, Hooks vor dem Senden (siehe unten).
    'transport_factories' => [],
    'renderers' => [],
    'hooks' => [],

    // Optionale Verdrahtung, die jedem Transport übergeben wird.
    'event_dispatcher' => $dispatcher,  // Symfony MessageEvents
    'logger' => $psr3Logger,
]);
```

## Weiterführendes

Alles unten ist optional. Die Standardeinstellungen decken die meisten Apps ab.

### Ein benutzerdefiniertes DSN-Schema hinzufügen

Implementiere Symfonys `TransportFactoryInterface` und registriere sie – dann funktioniert dein eigenes Schema genau wie ein eingebautes:

```php
use ryanstubbs\FlightMail\MailPlugin;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportFactoryInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;

class MyCarrierFactory implements TransportFactoryInterface
{
    public function supports(Dsn $dsn): bool
    {
        return $dsn->getScheme() === 'mycarrier';
    }

    public function create(Dsn $dsn): TransportInterface
    {
        // ... baue einen Transport, der mit deinem Anbieter kommuniziert
    }
}

$plugin = MailPlugin::install(['dsns' => ['carrier' => 'mycarrier://key']]);
$plugin->addTransportFactory(new MyCarrierFactory());
```

### Einen benutzerdefinierten Vorlagen-Renderer hinzufügen

Alles, was einen Vorlagennamen plus Parameter in einen String verwandelt, kommt infrage:

```php
use ryanstubbs\FlightMail\MailPlugin;
use ryanstubbs\FlightMail\Render\RendererInterface;

$plugin = MailPlugin::install($config);

$plugin->addRenderer('markdown', fn (array $config): RendererInterface =>
    new MarkdownMailRenderer($config['templates']['paths'] ?? [])
);
```

```php
// Vorlagen, die auf .markdown enden, verwenden es jetzt automatisch:
Flight::mail()->compose()->to('...')->template('welcome.markdown', ['name' => 'Ryan'])->send();
```

### Direkt vor dem Senden etwas ausführen

Hooks empfangen die fertige Nachricht – nach dem Rendern, nach den Standardwerten, direkt vor der Übertragung:

```php
$plugin->addHook(function (ryanstubbs\FlightMail\Message $message): void {
    $message->getHeaders()->addTextHeader('X-Mailer', 'MyApp/1.0');
});
```

### Events und Logging

Übergib einen Symfony-Event-Dispatcher und/oder PSR-3-Logger, und jeder Transport wird sie verwenden:

```php
$plugin->eventDispatcher($dispatcher); // erhält vor jedem Senden ein MessageEvent
$plugin->logger($logger);              // Logs auf Transportebene
```

## API-Spickzettel

```php
// Einrichtung
MailPlugin::install($config)             // registriert in der globalen Flight-App
MailPlugin::register($app, $config)      // an einer bestimmten Engine registrieren
$mailer = Flight::mail();                // die gemeinsame Mailer-Instanz

// Nachrichten erstellen
$mailer->compose(): Message
$message->to(...)->from(...)->subject(...)   // Standard-Symfony-Mime-Methoden
$message->text(string)                       // Klartext-String-Body
$message->html(string)                       // HTML-String-Body
$message->template($name, $params)           // HTML-Body aus einer Vorlage
$message->htmlTemplate($name, $params)       // Alias von template()
$message->textTemplate($name, $params)       // Text-Body aus einer Vorlage
$message->inlineCss() / ->withoutInlineCss() // CSS-Inlining pro Nachricht
$message->textFromHtml($mode)                // automatischer Textteil: true/'auto'/'plain'/'markdown'/false
$message->withoutTextFromHtml()              // Nur-HTML-E-Mail
$message->transport($name)                   // über eine benannte DSN leiten
$message->send(): ?SentMessage               // rendern + senden

// Am Mailer selbst
$mailer->send($message): ?SentMessage        // explizite Alternative zu $message->send()
$mailer->render($template, $params): string  // rendern ohne zu senden
$mailer->addHook(callable): static           // fn(Message $message): void
$mailer->transports(): TransportManager      // get() / has() / names()
$mailer->renderers(): RendererFactory        // create() / has() / add()
```

Da `Message` von `Symfony\Component\Mime\Email` erbt, funktioniert jede Symfony-Methode, die du bereits kennst – `attach()`, `embed()`, `priority()`, `replyTo()` – sofort ohne weitere Einrichtung.

## Fehlerbehebung

**„Keine Mail-DSNs konfiguriert“**
Du hast `Flight::mail()` aufgerufen, bevor du das Plugin registriert hast, oder das Konfigurationsarray enthielt kein `dsns`. Dieser Fehler ist beabsichtigt – FlightMail weigert sich zu raten, wohin deine E-Mail gehen soll, statt sie stillschweigend zu verwerfen.

**„Unbekannter Mail-Vorlagen-Renderer ...“**
Du hast eine Vorlage verwendet, deren Engine nicht installiert ist. Behebe es mit `composer require twig/twig` oder `composer require latte/latte`, oder registriere einen benutzerdefinierten Renderer, der nach der Erweiterung benannt ist.

**„Unbekannter Mail-Transport ...“**
Ein `->transport('name')` (oder `default_transport`) entspricht keinem Schlüssel in `dsns`. Überprüfe die Schreibweise – die Fehlermeldung listet die konfigurierten Namen auf.

**E-Mails kommen nicht an**
Setze `dsns` auf `null://null`, um zu bestätigen, dass der Rest deines Codes funktioniert, und wechsle dann zurück zur echten DSN. In DDEV verwende `smtp://127.0.0.1:1025` und prüfe Nachrichten in Mailpit auf Port 8025.

---

Für Fehlerberichte, Pull-Requests und den vollständigen Quellcode besuche das [GitHub-Repository](https://github.com/ryanstubbs/flightmail).
