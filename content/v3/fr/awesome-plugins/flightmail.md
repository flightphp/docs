# FlightMail

> **Plugin tiers** - maintenu par [Ryan Stubbs](https://ryanstubbs.co.uk) ([ryanstubbs/flightmail](https://github.com/ryanstubbs/flightmail), sous licence MIT). Ne fait pas partie du noyau Flight - veuillez signaler les problèmes sur [son dépôt GitHub](https://github.com/ryanstubbs/flightmail/issues).

[ryanstubbs/flightmail](https://github.com/ryanstubbs/flightmail) vous permet d'envoyer des e-mails depuis votre application Flight sans les maux de tête. Il enveloppe **Symfony Mailer** - la bibliothèque de messagerie la plus éprouvée en PHP - et la fait ressembler à une partie de Flight. Une ligne pour installer, une chaîne fluide pour envoyer :

```php
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('You did it!')
    ->text('Your first email is on its way.')
    ->send();
```

## Fonctionnalités

- **N’importe quel fournisseur, une ligne chacun.** SMTP, Postmark, Sendgrid, Mailgun, Amazon SES, Brevo et d’autres fonctionnent tous via de simples chaînes DSN.
- **Utilisez plusieurs fournisseurs à la fois.** E-mails transactionnels via Postmark, newsletters via votre propre SMTP - choisissez par message.
- **Des modèles si vous le souhaitez.** Rendu des corps avec Twig ou Latte. Pas besoin de modèles ? Passez simplement des chaînes et n’installez rien de plus.
- **Finition à l’envoi.** Inlining CSS optionnel et parties texte brut automatiques dérivées de votre HTML, propulsés par des bibliothèques que vous n’installez que si vous les utilisez.
- **Simple au meilleur sens du terme.** Connexions paresseuses, erreurs claires au lieu d’e-mails avalés silencieusement, et tout est remplaçable si vous avez besoin de quelque chose de personnalisé.

## Prérequis

| Quoi            | Version                                |
| --------------- | -------------------------------------- |
| PHP             | 8.2 ou plus                            |
| Flight PHP      | core ^3.15                             |
| Symfony Mailer  | ^7.2 ou ^8.0 (installé automatiquement) |

## Installation

```bash
composer require ryanstubbs/flightmail
```

C'est tout pour l'envoi d'e-mails en texte brut et en HTML. Le rendu de modèles est facultatif - ajoutez un moteur uniquement si vous l'utiliserez :

```bash
composer require twig/twig      # pour les modèles .twig
composer require latte/latte    # pour les modèles .latte
```

Deux bibliothèques facultatives supplémentaires alimentent les améliorations à l'envoi décrites [ci-dessous](#styling-html-and-generating-text-parts) :

```bash
composer require pelago/emogrifier         # pour l'inlining CSS ("inline_css")
composer require league/html-to-markdown   # pour les parties texte Markdown ("text_from_html")
```

Toutes peuvent être installées côte à côte ; FlightMail choisit la bonne en fonction de votre configuration.

## Votre premier e-mail

Ajoutez ceci à votre bootstrap (là où vous définissez vos routes) :

```php
<?php
require 'vendor/autoload.php';

use ryanstubbs\FlightMail\MailPlugin;

// Dites à FlightMail d'où et par où envoyer les e-mails.
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

Vous utilisez le [squelette Flight PHP](https://github.com/flightphp/skeleton) ? Enregistrez-le dans `app/config/services.php` avec le style par instance à la place :

```php
use ryanstubbs\FlightMail\MailPlugin;

MailPlugin::register($app, [
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'from' => 'no-reply@example.com',
]);
```

Les deux styles exposent le même mailer : `Flight::mail()` et `$app->mail()` sont interchangeables.

> **Test en local ?** Si votre projet tourne dans [DDEV](https://ddev.com), pointez le DSN vers `smtp://127.0.0.1:1025` et lisez chaque e-mail capturé dans Mailpit à `http://<project>.ddev.site:8025`. Rien ne sort de votre machine.

## Envoi d'e-mails

### Chaînes simples (aucun moteur de modèles nécessaire)

`->text()` et `->html()` acceptent des chaînes brutes et n'ont besoin de rien d'autre installé :

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

### Modèles Twig

```php
// welcome.html.twig contient : Bonjour {{ name }}, merci de vous être inscrit !
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.html.twig', ['name' => 'Ryan'])
    ->send();
```

### Modèles Latte

Même idée, extension `.latte` :

```php
// welcome.latte contient : Bonjour {$name}, merci de vous être inscrit !
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.latte', ['name' => 'Ryan'])
    ->send();
```

### HTML + texte brut ensemble

Bonne pratique pour la délivrabilité - donnez aux clients de messagerie les deux versions :

```php
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.html.twig', ['name' => 'Ryan'])     // version riche
    ->textTemplate('welcome.txt.twig', ['name' => 'Ryan'])  // version de secours
    ->send();
```

Quelques choses à savoir sur les modèles :

- Ils sont rendus **paresseusement**, au moment de l'envoi - composez maintenant, rendu plus tard.
- Le moteur est choisi par extension : `.twig` → Twig, `.latte` → Latte, toute autre → votre défaut configuré (option `renderer`).
- Un corps explicite `->html()` ou `->text()` l'emporte toujours sur un modèle, vous pouvez donc définir un modèle par défaut et le remplacer pour chaque message.

## Mise en forme HTML et génération de parties texte

Deux améliorations facultatives à l'envoi, toutes deux désactivées par défaut et toutes deux propulsées par des bibliothèques que vous n'installez que si vous le souhaitez :

| Fonctionnalité      | Installation              | Clé de configuration |
| ------------------- | ------------------------- | -------------------- |
| Inlining CSS        | `pelago/emogrifier`       | `inline_css`         |
| Partie texte depuis le HTML | `league/html-to-markdown` | `text_from_html`   |

### Inliner le CSS dans votre e-mail HTML

Gmail et la plupart des clients de messagerie en ligne suppriment les blocs `<style>` - les attributs `style=""` en ligne sont les seuls styles qu'ils honorent de manière fiable. Les écrire à la main est pénible ; laissez [Emogrifier](https://github.com/MyIntervals/emogrifier) le faire au moment de l'envoi :

```bash
composer require pelago/emogrifier
```

```php
MailPlugin::install([
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'inline_css' => true,
]);
```

Avec cette option activée, chaque corps HTML voit son CSS inliné juste avant l'envoi - qu'il provienne d'un modèle ou de `->html()`. Un message comme `<style>p { color: red; }</style><p>Hi</p>` part en tant que `<p style="color: red;">Hi</p>`.

Pour injecter des styles partagés dans chaque e-mail (couleurs de marque, réinitialisations) sans les répéter dans chaque modèle, passez des règles directement ou pointez vers un fichier de feuille de style :

```php
'inline_css' => ['css_file' => __DIR__ . '/mail-styles/base.css'],
// ou
'inline_css' => ['css' => '.button { background: #0a84ff; color: #fff; }'],
```

Contrôle par message :

```php
$message->inlineCss();          // force l'inlining pour ce message uniquement
$message->withoutInlineCss();   // ignore même si activé globalement
```

### Générer la partie texte depuis votre HTML

La bonne pratique est d'envoyer ensemble une version HTML et une version texte brut, mais écrire les deux est fastidieux. FlightMail peut dériver automatiquement la partie texte du HTML final - la conversion de base ne nécessite aucune dépendance supplémentaire, car le convertisseur est fourni avec Symfony Mime :

```php
MailPlugin::install([
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'text_from_html' => true,       // Markdown quand possible, texte brut sinon
]);
```

Modes :

- `true` ou `'auto'` - sortie Markdown si `league/html-to-markdown` est installé, sinon simple suppression des balises.
- `'markdown'` - force Markdown (`composer require league/html-to-markdown` ; les titres deviennent `==`, les liens `[text](url)`, les gras `**bold**`).
- `'plain'` - supprime toujours les balises ; fonctionne sans aucun package supplémentaire.

La génération s'exécute après le rendu et l'inlining CSS, et uniquement lorsque le message a un corps HTML mais pas de corps texte - un `->text()` ou `->textTemplate()` explicite gagne toujours. Les remplacements par message reflètent l'inlining :

```php
$message->textFromHtml('plain');    // force la suppression des balises pour celui-ci
$message->withoutTextFromHtml();    // e-mail HTML uniquement
```

Si vous activez un mode dont la bibliothèque n'est pas installée, vous obtenez une erreur claire nommant la commande `composer require` exacte à exécuter - jamais de dégradation silencieuse.

## Choisir un fournisseur

Les fournisseurs se branchent via des chaînes DSN. Installez le package de pont, collez le DSN dans `dsns`, c'est tout.

| Fournisseur          | Installation                                 | Exemple de DSN                                |
| -------------------- | -------------------------------------------- | --------------------------------------------- |
| SMTP                 | intégré                                      | `smtp://user:pass@host:587`                   |
| Sendmail             | intégré                                      | `sendmail://default`                          |
| Dev/null (supprime les e-mails) | intégré                            | `null://null`                                 |
| Postmark             | `composer require symfony/postmark-mailer`   | `postmark+api://KEY@api.postmarkapp.com`      |
| Sendgrid             | `composer require symfony/sendgrid-mailer`   | `sendgrid+api://KEY@default`                  |
| Mailgun              | `composer require symfony/mailgun-mailer`    | `mailgun+https://KEY:DOMAIN@api.mailgun.net`  |
| Amazon SES           | `composer require symfony/amazon-mailer`     | `ses+https://KEY:SECRET@default`              |
| Brevo                | `composer require symfony/brevo-mailer`      | `brevo+api://KEY@default`                     |
| MailerSend           | `composer require symfony/mailersend-mailer` | `mailersend+api://KEY@default`                |

La liste complète se trouve dans la [documentation de Symfony Mailer](https://symfony.com/doc/current/mailer.html) - tout ce qui y est documenté fonctionne ici sans modification.

### Plusieurs fournisseurs à la fois

Nommez chaque transport, puis choisissez par message :

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
// Aucun appel ->transport() = première clé de "dsns" ("transactional" ici).
Flight::mail()->compose()->to('...')->text('receipt')->send();

// Choisissez explicitement une autre voie.
Flight::mail()->compose()->to('...')->text('newsletter')->transport('bulk')->send();
```

## Référence de configuration

Tout est facultatif sauf `dsns`.

```php
MailPlugin::install([
    // OBLIGATOIRE - nom du transport => DSN Symfony.
    // La première entrée est utilisée lorsqu'un message n'en nomme pas.
    'dsns' => [
        'default' => 'smtp://user:pass@localhost:1025',
    ],

    // Transport utilisé lorsqu'un message n'a pas de ->transport() explicite et
    // que vous ne voulez pas de la première clé. Doit exister dans "dsns".
    'default_transport' => 'default',

    // Expéditeur global. Chaîne, adresse Symfony, ou ['email' => 'Nom'].
    // Appliqué uniquement lorsqu'un message ne définit pas son propre ->from().
    'from' => ['no-reply@example.com' => 'My App'],

    // Moteur de modèles par défaut : 'twig', 'latte', ou un nom personnalisé.
    // Uniquement consulté pour les modèles dont l'extension n'est pas un moteur de rendu enregistré.
    'renderer' => 'twig',

    // Où vivent les modèles, recherchés dans l'ordre ; plus un dossier de cache optionnel.
    'templates' => [
        'paths' => [__DIR__ . '/mail-templates'],
        'cache' => __DIR__ . '/cache/mail',
    ],

    // Options supplémentaires passées directement à Twig\Environment.
    'twig' => ['options' => ['strict_variables' => true]],

    // Ajustez le moteur Latte au démarrage : fn(Latte\Engine $engine): void.
    'latte' => ['setup' => static fn (Latte\Engine $e) => $e->addExtension(new MyExtension())],

    // Améliorations du corps à l'envoi (voir « Mise en forme HTML et génération de parties texte »).
    'inline_css' => true,           // ou ['css' => '...', 'css_file' => '...']
    'text_from_html' => true,       // ou 'plain' / 'markdown'

    // Schémas DSN personnalisés, moteurs de rendu personnalisés, crochets avant envoi (voir ci-dessous).
    'transport_factories' => [],
    'renderers' => [],
    'hooks' => [],

    // Conduites optionnelles remises à chaque transport.
    'event_dispatcher' => $dispatcher,  // Événements Symfony MessageEvents
    'logger' => $psr3Logger,
]);
```

## Aller plus loin

Tout ce qui suit est facultatif. Les valeurs par défaut couvrent la plupart des applications.

### Ajouter un schéma DSN personnalisé

Implémentez `TransportFactoryInterface` de Symfony et enregistrez-la - votre propre schéma fonctionne alors exactement comme un schéma intégré :

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
        // ... construisez un transport qui parle à votre fournisseur
    }
}

$plugin = MailPlugin::install(['dsns' => ['carrier' => 'mycarrier://key']]);
$plugin->addTransportFactory(new MyCarrierFactory());
```

### Ajouter un moteur de rendu de modèles personnalisé

Tout ce qui transforme un nom de modèle plus des paramètres en une chaîne est éligible :

```php
use ryanstubbs\FlightMail\MailPlugin;
use ryanstubbs\FlightMail\Render\RendererInterface;

$plugin = MailPlugin::install($config);

$plugin->addRenderer('markdown', fn (array $config): RendererInterface =>
    new MarkdownMailRenderer($config['templates']['paths'] ?? [])
);
```

```php
// Les modèles se terminant par .markdown l'utilisent désormais automatiquement :
Flight::mail()->compose()->to('...')->template('welcome.markdown', ['name' => 'Ryan'])->send();
```

### Exécuter quelque chose juste avant l'envoi

Les crochets reçoivent le message terminé - après le rendu, après les valeurs par défaut, juste avant l'envoi :

```php
$plugin->addHook(function (ryanstubbs\FlightMail\Message $message): void {
    $message->getHeaders()->addTextHeader('X-Mailer', 'MyApp/1.0');
});
```

### Événements et journalisation

Fournissez un dispatcher d'événements Symfony et/ou un logger PSR-3 et chaque transport les utilisera :

```php
$plugin->eventDispatcher($dispatcher); // reçoit MessageEvent avant chaque envoi
$plugin->logger($logger);              // journaux au niveau du transport
```

## Aide-mémoire API

```php
// Configuration
MailPlugin::install($config)             // enregistre sur l'application Flight globale
MailPlugin::register($app, $config)      // enregistre sur un Engine spécifique
$mailer = Flight::mail();                // l'instance Mailer partagée

// Construction des messages
$mailer->compose(): Message
$message->to(...)->from(...)->subject(...)   // méthodes Symfony Mime standard
$message->text(string)                       // corps en chaîne simple
$message->html(string)                       // corps en chaîne HTML
$message->template($name, $params)           // corps HTML depuis un modèle
$message->htmlTemplate($name, $params)       // alias de template()
$message->textTemplate($name, $params)       // corps texte depuis un modèle
$message->inlineCss() / ->withoutInlineCss() // inlining CSS par message
$message->textFromHtml($mode)                // partie texte automatique : true/'auto'/'plain'/'markdown'/false
$message->withoutTextFromHtml()              // e-mail HTML uniquement
$message->transport($name)                   // route via un DSN nommé
$message->send(): ?SentMessage               // rendu + envoi

// Sur le mailer lui-même
$mailer->send($message): ?SentMessage        // alternative explicite à $message->send()
$mailer->render($template, $params): string  // rendu sans envoi
$mailer->addHook(callable): static           // fn(Message $message): void
$mailer->transports(): TransportManager      // get() / has() / names()
$mailer->renderers(): RendererFactory        // create() / has() / add()
```

Comme `Message` étend `Symfony\Component\Mime\Email`, chaque méthode Symfony que vous connaissez déjà - `attach()`, `embed()`, `priority()`, `replyTo()` - fonctionne directement.

## Dépannage

**« Aucun DSN de messagerie configuré »**
Vous avez appelé `Flight::mail()` avant d'enregistrer le plugin, ou le tableau de configuration ne contenait pas `dsns`. Cette erreur est délibérée - FlightMail refuse de deviner où votre courrier doit aller plutôt que de le laisser tomber silencieusement.

**« Moteur de rendu de modèle de messagerie inconnu ... »**
Vous avez utilisé un modèle dont le moteur n'est pas installé. Corrigez avec `composer require twig/twig` ou `composer require latte/latte`, ou enregistrez un moteur de rendu personnalisé nommé d'après l'extension.

**« Transport de messagerie inconnu ... »**
Un `->transport('name')` (ou `default_transport`) ne correspond à aucune clé dans `dsns`. Vérifiez l'orthographe - l'erreur liste les noms configurés.

**Les e-mails n'arrivent pas**
Pointez `dsns` vers `null://null` pour confirmer que le reste de votre code fonctionne, puis revenez au vrai DSN. Dans DDEV, utilisez `smtp://127.0.0.1:1025` et inspectez les messages dans Mailpit au port 8025.

---

Pour les rapports de bugs, les pull requests et le code source complet, visitez le [dépôt GitHub](https://github.com/ryanstubbs/flightmail).