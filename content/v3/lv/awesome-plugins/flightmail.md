# FlightMail

> **Trešās puses spraudnis** — uztur [Ryan Stubbs](https://ryanstubbs.co.uk) ([ryanstubbs/flightmail](https://github.com/ryanstubbs/flightmail), MIT licence). Nav daļa no Flight kodola — lūdzu ziņojiet par problēmām [tā GitHub repozitorijā](https://github.com/ryanstubbs/flightmail/issues).

[ryanstubbs/flightmail](https://github.com/ryanstubbs/flightmail) ļauj sūtīt e-pastus no jūsu Flight lietotnes bez liekām galvassāpēm. Tas aptver **Symfony Mailer** — visvairāk pārbaudīto e-pasta bibliotēku PHP — un padara to par dabisku Flight daļu. Viena rindiņa instalēšanai, viena plūstoša ķēde sūtīšanai:

```php
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('You did it!')
    ->text('Your first email is on its way.')
    ->send();
```

## Funkcijas

- **Jebkurš pakalpojumu sniedzējs — katra ar vienu rindiņu.** SMTP, Postmark, Sendgrid, Mailgun, Amazon SES, Brevo un citi visi darbojas ar vienkāršām DSN virknēm.
- **Izmantojiet vairākus pakalpojumu sniedzējus vienlaikus.** Transakciju e-pasti caur Postmark, jaunumi caur savu SMTP — izvēlieties katram ziņojumam atsevišķi.
- **Veidnes, ja vēlaties.** Renderējiet saturu ar Twig vai Latte. Negribat veidnes? Vienkārši padodiet virknes un neko papildu neinstalējiet.
- **Noslīpējums sūtīšanas brīdī.** Neobligāta CSS iekļaušana un automātiskas teksta daļas, kas iegūtas no jūsu HTML, ko nodrošina bibliotēkas, kuras instalējat tikai tad, ja tās lietojat.
- **Garlaicīgs vislabākajā nozīmē.** Slinki savienojumi, skaidras kļūdas tā vietā, lai klusu norītu e-pastus, un visu var nomainīt, ja nepieciešams kaut kas pielāgots.

## Prasības

| Komponents     | Versija                             |
| -------------- | ----------------------------------- |
| PHP            | 8.2 vai jaunāka                     |
| Flight PHP     | kodols ^3.15                        |
| Symfony Mailer | ^7.2 vai ^8.0 (instalēts automātiski) |

## Instalēšana

```bash
composer require ryanstubbs/flightmail
```

Ar to pietiek, lai sūtītu vienkārša teksta un HTML e-pastus. Veidņu renderēšana nav obligāta — pievienojiet dzinēju tikai tad, ja to izmantosiet:

```bash
composer require twig/twig      # .twig veidnēm
composer require latte/latte    # .latte veidnēm
```

Vēl divas neobligātas bibliotēkas nodrošina sūtīšanas laika uzlabojumus, kas aprakstīti [tālāk](#styling-html-and-generating-text-parts):

```bash
composer require pelago/emogrifier         # CSS iekļaušanai ("inline_css")
composer require league/html-to-markdown   # Markdown teksta daļām ("text_from_html")
```

Visas šīs bibliotēkas var instalēt blakus; FlightMail izvēlas pareizo atbilstoši jūsu konfigurācijai.

## Jūsu pirmais e-pasts

Pievienojiet to savam bootstrap kodam (tur, kur definējat maršrutus):

```php
<?php
require 'vendor/autoload.php';

use ryanstubbs\FlightMail\MailPlugin;

// Pastāstiet FlightMail, no kurienes un caur ko sūtīt e-pastus.
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

Izmantojat [Flight PHP skeletu](https://github.com/flightphp/skeleton)? Reģistrējiet `app/config/services.php` failā, izmantojot instances stilu:

```php
use ryanstubbs\FlightMail\MailPlugin;

MailPlugin::register($app, [
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'from' => 'no-reply@example.com',
]);
```

Abi stili nodrošina to pašu sūtītāju: `Flight::mail()` un `$app->mail()` ir savstarpēji aizvietojami.

> **Testējat lokāli?** Ja jūsu projekts darbojas [DDEV](https://ddev.com) vidē, norādiet DSN uz `smtp://127.0.0.1:1025` un lasiet visus notvertos e-pastus Mailpit saskarnē `http://<project>.ddev.site:8025`. Nekas neatstāj jūsu datoru.

## E-pasta sūtīšana

### Vienkāršas virknes (nav nepieciešams veidņu dzinējs)

`->text()` un `->html()` pieņem neapstrādātas virknes un neprasa neko citu instalētu:

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

### Twig veidnes

```php
// welcome.html.twig satur: Hello {{ name }}, paldies, ka reģistrējāties!
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.html.twig', ['name' => 'Ryan'])
    ->send();
```

### Latte veidnes

```php
// welcome.latte satur: Hello {$name}, paldies, ka reģistrējāties!
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.latte', ['name' => 'Ryan'])
    ->send();
```

### HTML un vienkāršs teksts kopā

Labākā prakse piegādājamībai — sniedziet e-pasta klientiem abas versijas:

```php
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.html.twig', ['name' => 'Ryan'])     // bagātā versija
    ->textTemplate('welcome.txt.twig', ['name' => 'Ryan'])  // rezerves versija
    ->send();
```

Dažas lietas, ko ir vērts zināt par veidnēm:

- Tās renderējas **slinki**, sūtīšanas brīdī — komponējiet tagad, renderējiet vēlāk.
- Dzinējs tiek izvēlēts pēc paplašinājuma: `.twig` → Twig, `.latte` → Latte, jebkas cits → jūsu konfigurētais noklusējums (`renderer` opcija).
- Skaidri norādīts `->html()` vai `->text()` saturs vienmēr prevalē pār veidni, tāpēc varat iestatīt noklusējuma veidni un pārrakstīt to katram ziņojumam atsevišķi.

## HTML noformēšana un teksta daļu ģenerēšana

Divi neobligāti sūtīšanas laika uzlabojumi, abi pēc noklusējuma izslēgti, un abus nodrošina bibliotēkas, kuras instalējat tikai tad, ja to vēlaties:

| Funkcija             | Instalācija                 | Konfigurācijas atslēga |
| -------------------- | --------------------------- | ---------------------- |
| CSS iekļaušana       | `pelago/emogrifier`         | `inline_css`           |
| Teksta daļa no HTML  | `league/html-to-markdown`   | `text_from_html`       |

### Iekļaujiet CSS savā HTML e-pastā

Gmail un lielākā daļa tīmekļa e-pasta klientu noņem `<style>` blokus — iekļautie `style=""` atribūti ir vienīgais noformējums, ko tie ticami atbalsta. Rakstīt tos ar roku ir nepatīkami; ļaujiet [Emogrifier](https://github.com/MyIntervals/emogrifier) to paveikt sūtīšanas laikā:

```bash
composer require pelago/emogrifier
```

```php
MailPlugin::install([
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'inline_css' => true,
]);
```

Kad tas ir ieslēgts, katram HTML saturam CSS tiek iekļauts tieši pirms sūtīšanas — neatkarīgi no tā, vai tas nācis no veidnes vai `->html()`. Ziņojums, piemēram, `<style>p { color: red; }</style><p>Hi</p>`, tiek nosūtīts kā `<p style="color: red;">Hi</p>`.

Lai ievietotu kopīgus stilus katrā e-pastā (zīmola krāsas, atiestatīšanas), neatkārtojot tos katrā veidnē, padodiet noteikumus tieši vai norādiet uz stila failu:

```php
'inline_css' => ['css_file' => __DIR__ . '/mail-styles/base.css'],
// vai
'inline_css' => ['css' => '.button { background: #0a84ff; color: #fff; }'],
```

Kontrole katram ziņojumam:

```php
$message->inlineCss();          // piespiedu iekļaušana šim vienam ziņojumam
$message->withoutInlineCss();   // izlaist, pat ja globāli iespējots
```

### Ģenerējiet teksta daļu no sava HTML

Labākā prakse ir sūtīt HTML un vienkāršā teksta versiju kopā, taču abu rakstīšana ir apnicīga. FlightMail var automātiski iegūt teksta daļu no galīgā HTML — pamata konversijai nav nepieciešama papildu atkarība, jo pārveidotājs ir iekļauts Symfony Mime:

```php
MailPlugin::install([
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'text_from_html' => true,       // Markdown, ja iespējams, citādi vienkāršs teksts
]);
```

Režīmi:

- `true` vai `'auto'` — Markdown izvade, ja `league/html-to-markdown` ir instalēts, citādi vienkārša tagu noņemšana.
- `'markdown'` — piespiedu Markdown (`composer require league/html-to-markdown`; virsraksti kļūst par `==`, saites `[text](url)`, treknraksts `**bold**`).
- `'plain'` — vienmēr noņem tagus; darbojas bez papildu pakotnēm.

Ģenerēšana notiek pēc renderēšanas un CSS iekļaušanas, un tikai tad, ja ziņojumam ir HTML saturs, bet nav teksta satura — skaidri norādīts `->text()` vai `->textTemplate()` vienmēr prevalē. Pārrakstīšana katram ziņojumam atspoguļo iekļaušanu:

```php
$message->textFromHtml('plain');    // piespiedu tagu noņemšana šim vienam
$message->withoutTextFromHtml();    // tikai HTML e-pasts
```

Ja iespējojat režīmu, kura bibliotēka nav instalēta, saņemsiet skaidru kļūdu, kas nosauc precīzu `composer require` komandu — nekādas klusas degradācijas.

## Pakalpojumu sniedzēja izvēle

Pakalpojumu sniedzēji tiek pievienoti, izmantojot DSN virknes. Instalējiet savienojuma pakotni, ielīmējiet DSN `dsns` laukā, gatavs.

| Sniedzējs                    | Instalācija                                  | DSN piemērs                                  |
| ---------------------------- | -------------------------------------------- | -------------------------------------------- |
| SMTP                         | iebūvēts                                     | `smtp://user:pass@host:587`                  |
| Sendmail                     | iebūvēts                                     | `sendmail://default`                         |
| Dev/null (e-pastu izmešana)  | iebūvēts                                     | `null://null`                                |
| Postmark                     | `composer require symfony/postmark-mailer`   | `postmark+api://KEY@api.postmarkapp.com`     |
| Sendgrid                     | `composer require symfony/sendgrid-mailer`   | `sendgrid+api://KEY@default`                 |
| Mailgun                      | `composer require symfony/mailgun-mailer`    | `mailgun+https://KEY:DOMAIN@api.mailgun.net` |
| Amazon SES                   | `composer require symfony/amazon-mailer`     | `ses+https://KEY:SECRET@default`             |
| Brevo                        | `composer require symfony/brevo-mailer`      | `brevo+api://KEY@default`                    |
| MailerSend                   | `composer require symfony/mailersend-mailer` | `mailersend+api://KEY@default`               |

Pilns saraksts ir [Symfony Mailer dokumentācijā](https://symfony.com/doc/current/mailer.html) — viss, kas tur dokumentēts, šeit darbojas nemainīts.

### Vairāki pakalpojumu sniedzēji vienlaikus

Nosauciet katru transportu un pēc tam izvēlieties katram ziņojumam:

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
// Bez ->transport() izsaukuma tiek izmantota pirmā "dsns" atslēga ("transactional" šeit).
Flight::mail()->compose()->to('...')->text('receipt')->send();

// Skaidri izvēlieties citu maršrutu.
Flight::mail()->compose()->to('...')->text('newsletter')->transport('bulk')->send();
```

## Konfigurācijas atsauce

Viss nav obligāts, izņemot `dsns`.

```php
MailPlugin::install([
    // OBLIGĀTI - transporta nosaukums => Symfony DSN.
    // Pirmais ieraksts tiek izmantots, ja ziņojums nenorāda konkrētu.
    'dsns' => [
        'default' => 'smtp://user:pass@localhost:1025',
    ],

    // Transports, ko izmanto, ja ziņojumam nav tieša ->transport() izsaukuma un
    // nevēlaties izmantot pirmo atslēgu. Jābūt definētam "dsns".
    'default_transport' => 'default',

    // Globālais sūtītājs. String, Symfony Address vai ['email' => 'Name'].
    // Piemērots tikai tad, ja ziņojums nenosaka savu ->from().
    'from' => ['no-reply@example.com' => 'My App'],

    // Noklusējuma veidņu dzinējs: 'twig', 'latte' vai pielāgots nosaukums.
    // Tiek izmantots tikai veidnēm, kuru paplašinājums nav reģistrēts renderētājs.
    'renderer' => 'twig',

    // Kur atrodas veidnes, tiek meklētas secīgi; plus neobligāta kešatmiņas mape.
    'templates' => [
        'paths' => [__DIR__ . '/mail-templates'],
        'cache' => __DIR__ . '/cache/mail',
    ],

    // Papildu opcijas, kas nodotas tieši Twig\Environment.
    'twig' => ['options' => ['strict_variables' => true]],

    // Pielāgojiet Latte dzinēju starta brīdī: fn(Latte\Engine $engine): void.
    'latte' => ['setup' => static fn (Latte\Engine $e) => $e->addExtension(new MyExtension())],

    // Sūtīšanas laika satura uzlabojumi (skatīt "HTML noformēšana un teksta daļu ģenerēšana").
    'inline_css' => true,           // vai ['css' => '...', 'css_file' => '...']
    'text_from_html' => true,       // vai 'plain' / 'markdown'

    // Pielāgotas DSN shēmas, pielāgoti renderētāji, pirms-sūtīšanas āķi (skatīt tālāk).
    'transport_factories' => [],
    'renderers' => [],
    'hooks' => [],

    // Neobligātā infrastruktūra katram transportam.
    'event_dispatcher' => $dispatcher,  // Symfony MessageEvents
    'logger' => $psr3Logger,
]);
```

## Tālākas iespējas

Viss zemāk ir neobligāts. Noklusējumi aptver lielāko daļu lietotņu.

### Pievienojiet pielāgotu DSN shēmu

Ieviesiet Symfony `TransportFactoryInterface` un reģistrējiet to — tad jūsu pašu shēma darbojas tieši tāpat kā iebūvēta:

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
        // ... izveidojiet transportu, kas sazinās ar jūsu pakalpojumu sniedzēju
    }
}

$plugin = MailPlugin::install(['dsns' => ['carrier' => 'mycarrier://key']]);
$plugin->addTransportFactory(new MyCarrierFactory());
```

### Pievienojiet pielāgotu veidņu renderētāju

Jebkas, kas pārvērš veidnes nosaukumu un parametrus virknē, ir derīgs:

```php
use ryanstubbs\FlightMail\MailPlugin;
use ryanstubbs\FlightMail\Render\RendererInterface;

$plugin = MailPlugin::install($config);

$plugin->addRenderer('markdown', fn (array $config): RendererInterface =>
    new MarkdownMailRenderer($config['templates']['paths'] ?? [])
);
```

```php
// Veidnes, kas beidzas ar .markdown, tagad to izmanto automātiski:
Flight::mail()->compose()->to('...')->template('welcome.markdown', ['name' => 'Ryan'])->send();
```

### Izpildiet kaut ko tieši pirms sūtīšanas

Āķi saņem pabeigto ziņojumu — pēc renderēšanas, pēc noklusējumiem, tieši pirms nosūtīšanas:

```php
$plugin->addHook(function (ryanstubbs\FlightMail\Message $message): void {
    $message->getHeaders()->addTextHeader('X-Mailer', 'MyApp/1.0');
});
```

### Notikumi un žurnālēšana

Nododiet Symfony notikumu izplatītāju un/vai PSR-3 žurnālētāju, un katrs transports tos izmantos:

```php
$plugin->eventDispatcher($dispatcher); // saņem MessageEvent pirms katras sūtīšanas
$plugin->logger($logger);              // transporta līmeņa žurnāli
```

## API īsa atsauce

```php
// Iestatīšana
MailPlugin::install($config)             // reģistrē globālajā Flight lietotnē
MailPlugin::register($app, $config)      // reģistrē konkrētam Engine
$mailer = Flight::mail();                // koplietotā Mailer instance

// Ziņojumu veidošana
$mailer->compose(): Message
$message->to(...)->from(...)->subject(...)   // standarta Symfony Mime metodes
$message->text(string)                       // vienkārša teksta virknes saturs
$message->html(string)                       // HTML virknes saturs
$message->template($name, $params)           // HTML saturs no veidnes
$message->htmlTemplate($name, $params)       // template() sinonīms
$message->textTemplate($name, $params)       // teksta saturs no veidnes
$message->inlineCss() / ->withoutInlineCss() // CSS iekļaušana katram ziņojumam
$message->textFromHtml($mode)                // automātiskā teksta daļa: true/'auto'/'plain'/'markdown'/false
$message->withoutTextFromHtml()              // tikai HTML e-pasts
$message->transport($name)                   // maršrutēt caur nosauktu DSN
$message->send(): ?SentMessage               // renderēt + sūtīt

// Uz paša sūtītāja
$mailer->send($message): ?SentMessage        // tieša alternatīva $message->send()
$mailer->render($template, $params): string  // renderēt bez sūtīšanas
$mailer->addHook(callable): static           // fn(Message $message): void
$mailer->transports(): TransportManager      // get() / has() / names()
$mailer->renderers(): RendererFactory        // create() / has() / add()
```

Tā kā `Message` paplašina `Symfony\Component\Mime\Email`, katra Symfony metode, ko jūs jau zināt — `attach()`, `embed()`, `priority()`, `replyTo()` — darbojas bez papildu iestatīšanas.

## Problēmu novēršana

**"No mail DSNs configured"**

Jūs izsaucāt `Flight::mail()` pirms spraudņa reģistrēšanas, vai konfigurācijas masīvā nebija iekļauts `dsns`. Šī kļūda ir apzināta — FlightMail atsakās uzminēt, kur jūsu e-pastiem būtu jādodas, nevis klusu tos izmet.

**"Unknown mail template renderer ..."**

Jūs izmantojāt veidni, kuras dzinējs nav instalēts. Labojiet ar `composer require twig/twig` vai `composer require latte/latte`, vai reģistrējiet pielāgotu renderētāju, kas nosaukts pēc paplašinājuma.

**"Unknown mail transport ..."**

`->transport('name')` (vai `default_transport`) neatbilst nevienai `dsns` atslēgai. Pārbaudiet pareizrakstību — kļūda uzskaita konfigurētos nosaukumus.

**E-pasts nenonāk**

Norādiet `dsns` uz `null://null`, lai pārliecinātos, ka pārējais jūsu kods darbojas, pēc tam pārslēdzieties atpakaļ uz īsto DSN. DDEV vidē izmantojiet `smtp://127.0.0.1:1025` un pārbaudiet ziņojumus Mailpit 8025. portā.

---

Lai ziņotu par kļūdām, iesniegtu pull requestus un skatītu pilnu avota kodu, apmeklējiet [GitHub repozitoriju](https://github.com/ryanstubbs/flightmail).