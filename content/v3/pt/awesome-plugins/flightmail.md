# FlightMail

> **Plugin de terceiros** - mantido por [Ryan Stubbs](https://ryanstubbs.co.uk) ([ryanstubbs/flightmail](https://github.com/ryanstubbs/flightmail), licenciado MIT). Não faz parte do núcleo do Flight - por favor, reporte problemas no [repositório GitHub](https://github.com/ryanstubbs/flightmail/issues).

[ryanstubbs/flightmail](https://github.com/ryanstubbs/flightmail) permite enviar e-mails da sua aplicação Flight sem dores de cabeça. Ele encapsula o **Symfony Mailer** - a biblioteca de e-mail mais testada em PHP - e faz com que pareça parte do Flight. Uma linha para instalar, uma cadeia fluente para enviar:

```php
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('You did it!')
    ->text('Your first email is on its way.')
    ->send();
```

## Funcionalidades

- **Qualquer provedor, uma linha cada.** SMTP, Postmark, Sendgrid, Mailgun, Amazon SES, Brevo e amigos funcionam através de strings DSN simples.
- **Use vários provedores ao mesmo tempo.** E-mails transacionais via Postmark, newsletters via seu próprio SMTP - escolha por mensagem.
- **Templates se você quiser.** Renderize corpos com Twig ou Latte. Não quer templates? Apenas passe strings e não instale nada extra.
- **Acabamento no envio.** Inlining de CSS opcional e partes de texto simples automáticas derivadas do seu HTML, alimentadas por bibliotecas que você só instala se usá-las.
- **Chato da melhor maneira.** Conexões preguiçosas, erros claros em vez de e-mails engolidos silenciosamente, e tudo é substituível se você precisar de algo personalizado.

## Requisitos

| O que          | Versão                                 |
| -------------- | -------------------------------------- |
| PHP            | 8.2 ou mais recente                    |
| Flight PHP     | core ^3.15                             |
| Symfony Mailer | ^7.2 ou ^8.0 (instalado automaticamente) |

## Instalação

```bash
composer require ryanstubbs/flightmail
```

É isso para enviar e-mails em texto simples e HTML. A renderização de templates é opcional - adicione um motor apenas se você for usá-lo:

```bash
composer require twig/twig      # para templates .twig
composer require latte/latte    # para templates .latte
```

Mais duas bibliotecas opcionais alimentam os aprimoramentos de envio cobertos [abaixo](#styling-html-and-generating-text-parts):

```bash
composer require pelago/emogrifier         # para inlining de CSS ("inline_css")
composer require league/html-to-markdown   # para partes de texto Markdown ("text_from_html")
```

Todas estas podem ser instaladas lado a lado; FlightMail escolhe a certa com base no que você configurar.

## Seu primeiro e-mail

Adicione isto ao seu bootstrap (o mesmo lugar onde você define rotas):

```php
<?php
require 'vendor/autoload.php';

use ryanstubbs\FlightMail\MailPlugin;

// Diga ao FlightMail de onde e por onde enviar e-mails.
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

Usando o [esqueleto do Flight PHP](https://github.com/flightphp/skeleton)? Registre em `app/config/services.php` com o estilo de instância em vez disso:

```php
use ryanstubbs\FlightMail\MailPlugin;

MailPlugin::register($app, [
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'from' => 'no-reply@example.com',
]);
```

Ambos os estilos expõem o mesmo mailer: `Flight::mail()` e `$app->mail()` são intercambiáveis.

> **Testando localmente?** Se seu projeto roda no [DDEV](https://ddev.com), aponte o DSN para `smtp://127.0.0.1:1025` e leia todos os e-mails capturados no Mailpit em `http://<project>.ddev.site:8025`. Nada sai da sua máquina.

## Enviando e-mail

### Strings simples (sem necessidade de motor de template)

`->text()` e `->html()` aceitam strings brutas e não precisam de mais nada instalado:

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

### Templates Twig

```php
// welcome.html.twig contém: Hello {{ name }}, obrigado por se inscrever!
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.html.twig', ['name' => 'Ryan'])
    ->send();
```

### Templates Latte

Mesma ideia, extensão `.latte`:

```php
// welcome.latte contém: Hello {$name}, obrigado por se inscrever!
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.latte', ['name' => 'Ryan'])
    ->send();
```

### HTML + texto simples juntos

Melhor prática para entregabilidade - forneça aos clientes de e-mail ambas as versões:

```php
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.html.twig', ['name' => 'Ryan'])     // versão rica
    ->textTemplate('welcome.txt.twig', ['name' => 'Ryan'])  // versão de fallback
    ->send();
```

Algumas coisas que vale a pena saber sobre templates:

- Eles são renderizados **preguiçosamente**, no momento do envio - componha agora, renderize depois.
- O motor é escolhido pela extensão: `.twig` → Twig, `.latte` → Latte, qualquer outra coisa → seu padrão configurado (opção `renderer`).
- Um corpo explícito `->html()` ou `->text()` sempre vence sobre um template, então você pode definir um template padrão e sobrescrevê-lo por mensagem.

## Estilizando HTML e gerando partes de texto

Dois aprimoramentos opcionais no envio, ambos desativados por padrão e ambos alimentados por bibliotecas que você só instala se quiser:

| Funcionalidade             | Instalação                   | Chave de configuração       |
| ------------------- | ------------------------- | ---------------- |
| Inlining de CSS        | `pelago/emogrifier`       | `inline_css`     |
| Parte de texto do HTML | `league/html-to-markdown` | `text_from_html` |

### Inline CSS no seu e-mail HTML

O Gmail e a maioria dos clientes de webmail removem blocos `<style>` - atributos `style=""` inline são a única estilização que eles honram de forma confiável. Escrevê-los à mão é miserável; deixe o [Emogrifier](https://github.com/MyIntervals/emogrifier) fazer isso no momento do envio:

```bash
composer require pelago/emogrifier
```

```php
MailPlugin::install([
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'inline_css' => true,
]);
```

Com isso ativado, todo corpo HTML tem seu CSS inline antes do envio - seja vindo de um template ou de `->html()`. Uma mensagem como `<style>p { color: red; }</style><p>Hi</p>` sai como `<p style="color: red;">Hi</p>`.

Para injetar estilos compartilhados em cada e-mail (cores da marca, resets) sem repeti-los em cada template, passe regras diretamente ou aponte para um arquivo de folha de estilo:

```php
'inline_css' => ['css_file' => __DIR__ . '/mail-styles/base.css'],
// ou
'inline_css' => ['css' => '.button { background: #0a84ff; color: #fff; }'],
```

Controle por mensagem:

```php
$message->inlineCss();          // força inlining para esta única mensagem
$message->withoutInlineCss();   // pula mesmo quando globalmente ativado
```

### Gere a parte de texto do seu HTML

A melhor prática é enviar uma versão HTML e uma em texto simples juntas, mas escrever ambas é tedioso. FlightMail pode derivar a parte de texto do HTML final automaticamente - a conversão básica não precisa de dependência extra, já que o conversor vem com o Symfony Mime:

```php
MailPlugin::install([
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'text_from_html' => true,       // Markdown quando possível, texto simples caso contrário
]);
```

Modos:

- `true` ou `'auto'` - Saída em Markdown se `league/html-to-markdown` estiver instalado, caso contrário, remoção simples de tags.
- `'markdown'` - força Markdown (`composer require league/html-to-markdown`; cabeçalhos se tornam `==`, links `[text](url)`, negrito `**bold**`).
- `'plain'` - sempre remove tags; funciona com zero pacotes extras.

A geração ocorre após a renderização e o inlining de CSS, e somente quando a mensagem tem um corpo HTML mas nenhum corpo de texto - um `->text()` ou `->textTemplate()` explícito sempre vence. Sobrescritas por mensagem espelham o inlining:

```php
$message->textFromHtml('plain');    // força remoção de tags para esta
$message->withoutTextFromHtml();    // e-mail somente HTML
```

Ative um modo cuja biblioteca não está instalada e você recebe um erro claro nomeando o exato `composer require` a executar - nunca degradação silenciosa.

## Escolhendo um provedor

Provedores se conectam através de strings DSN. Instale o pacote de ponte, cole o DSN em `dsns`, pronto.

| Provedor             | Instalação                                      | Exemplo de DSN                                  |
| -------------------- | -------------------------------------------- | -------------------------------------------- |
| SMTP                 | integrado                                     | `smtp://user:pass@host:587`                  |
| Sendmail             | integrado                                     | `sendmail://default`                         |
| Dev/null (descartar e-mail) | integrado                                     | `null://null`                                |
| Postmark             | `composer require symfony/postmark-mailer`   | `postmark+api://KEY@api.postmarkapp.com`     |
| Sendgrid             | `composer require symfony/sendgrid-mailer`   | `sendgrid+api://KEY@default`                 |
| Mailgun              | `composer require symfony/mailgun-mailer`    | `mailgun+https://KEY:DOMAIN@api.mailgun.net` |
| Amazon SES           | `composer require symfony/amazon-mailer`     | `ses+https://KEY:SECRET@default`             |
| Brevo                | `composer require symfony/brevo-mailer`      | `brevo+api://KEY@default`                    |
| MailerSend           | `composer require symfony/mailersend-mailer` | `mailersend+api://KEY@default`               |

A lista completa está na [documentação do Symfony Mailer](https://symfony.com/doc/current/mailer.html) - qualquer coisa documentada lá funciona aqui sem alterações.

### Vários provedores ao mesmo tempo

Nomeie cada transporte, depois escolha por mensagem:

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
// Sem chamada ->transport() = primeira chave em "dsns" ("transactional" aqui).
Flight::mail()->compose()->to('...')->text('receipt')->send();

// Opte por outra rota explicitamente.
Flight::mail()->compose()->to('...')->text('newsletter')->transport('bulk')->send();
```

## Referência de configuração

Tudo é opcional exceto `dsns`.

```php
MailPlugin::install([
    // OBRIGATÓRIO - nome do transporte => DSN do Symfony.
    // A primeira entrada é usada quando uma mensagem não especifica uma.
    'dsns' => [
        'default' => 'smtp://user:pass@localhost:1025',
    ],

    // Transporte usado quando uma mensagem não tem ->transport() explícito e
    // você não quer a primeira chave. Deve existir em "dsns".
    'default_transport' => 'default',

    // Remetente global. String, Symfony Address, ou ['email' => 'Nome'].
    // Aplicado apenas quando uma mensagem não define seu próprio ->from().
    'from' => ['no-reply@example.com' => 'My App'],

    // Motor de template padrão: 'twig', 'latte', ou um nome personalizado.
    // Consultado apenas para templates cuja extensão não é um renderizador registrado.
    'renderer' => 'twig',

    // Onde os templates ficam, pesquisados em ordem; mais um diretório de cache opcional.
    'templates' => [
        'paths' => [__DIR__ . '/mail-templates'],
        'cache' => __DIR__ . '/cache/mail',
    ],

    // Opções extras passadas diretamente para Twig\Environment.
    'twig' => ['options' => ['strict_variables' => true]],

    // Ajuste o motor Latte na inicialização: fn(Latte\Engine $engine): void.
    'latte' => ['setup' => static fn (Latte\Engine $e) => $e->addExtension(new MyExtension())],

    // Aprimoramentos de corpo no envio (veja "Estilizando HTML e gerando partes de texto").
    'inline_css' => true,           // ou ['css' => '...', 'css_file' => '...']
    'text_from_html' => true,       // ou 'plain' / 'markdown'

    // Esquemas DSN personalizados, renderizadores personalizados, hooks pré-envio (veja abaixo).
    'transport_factories' => [],
    'renderers' => [],
    'hooks' => [],

    // Encanamento opcional entregue a cada transporte.
    'event_dispatcher' => $dispatcher,  // Symfony MessageEvents
    'logger' => $psr3Logger,
]);
```

## Indo além

Tudo abaixo é opcional. Os padrões cobrem a maioria das aplicações.

### Adicione um esquema DSN personalizado

Implemente a `TransportFactoryInterface` do Symfony e registre-a - então seu próprio esquema funciona exatamente como um integrado:

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
        // ... construa um transporte que se comunica com sua operadora
    }
}

$plugin = MailPlugin::install(['dsns' => ['carrier' => 'mycarrier://key']]);
$plugin->addTransportFactory(new MyCarrierFactory());
```

### Adicione um renderizador de template personalizado

Qualquer coisa que transforme um nome de template mais parâmetros em uma string se qualifica:

```php
use ryanstubbs\FlightMail\MailPlugin;
use ryanstubbs\FlightMail\Render\RendererInterface;

$plugin = MailPlugin::install($config);

$plugin->addRenderer('markdown', fn (array $config): RendererInterface =>
    new MarkdownMailRenderer($config['templates']['paths'] ?? [])
);
```

```php
// Templates terminando em .markdown agora o usam automaticamente:
Flight::mail()->compose()->to('...')->template('welcome.markdown', ['name' => 'Ryan'])->send();
```

### Execute algo bem antes de enviar

Hooks recebem a mensagem finalizada - após a renderização, após os padrões, pouco antes do envio:

```php
$plugin->addHook(function (ryanstubbs\FlightMail\Message $message): void {
    $message->getHeaders()->addTextHeader('X-Mailer', 'MyApp/1.0');
});
```

### Eventos e logging

Entregue um dispatcher de eventos do Symfony e/ou logger PSR-3 e cada transporte os usará:

```php
$plugin->eventDispatcher($dispatcher); // recebe MessageEvent antes de cada envio
$plugin->logger($logger);              // logs em nível de transporte
```

## Folha de dicas da API

```php
// Configuração
MailPlugin::install($config)             // registra na aplicação Flight global
MailPlugin::register($app, $config)      // registra em um Engine específico
$mailer = Flight::mail();                // a instância compartilhada do Mailer

// Construindo mensagens
$mailer->compose(): Message
$message->to(...)->from(...)->subject(...)   // métodos padrão do Symfony Mime
$message->text(string)                       // corpo de string simples
$message->html(string)                       // corpo de string HTML
$message->template($name, $params)           // corpo HTML de um template
$message->htmlTemplate($name, $params)       // alias de template()
$message->textTemplate($name, $params)       // corpo de texto de um template
$message->inlineCss() / ->withoutInlineCss() // inlining de CSS por mensagem
$message->textFromHtml($mode)                // parte de texto automática: true/'auto'/'plain'/'markdown'/false
$message->withoutTextFromHtml()              // e-mail somente HTML
$message->transport($name)                   // rota via um DSN nomeado
$message->send(): ?SentMessage               // renderiza + envia

// No próprio mailer
$mailer->send($message): ?SentMessage        // alternativa explícita a $message->send()
$mailer->render($template, $params): string  // renderiza sem enviar
$mailer->addHook(callable): static           // fn(Message $message): void
$mailer->transports(): TransportManager      // get() / has() / names()
$mailer->renderers(): RendererFactory        // create() / has() / add()
```

Como `Message` estende `Symfony\Component\Mime\Email`, todo método Symfony que você já conhece - `attach()`, `embed()`, `priority()`, `replyTo()` - funciona imediatamente.

## Solução de problemas

**"No mail DSNs configured"**
Você chamou `Flight::mail()` antes de registrar o plugin, ou o array de configuração não incluía `dsns`. Este erro é deliberado - FlightMail se recusa a adivinhar para onde seu e-mail deve ir em vez de descartá-lo silenciosamente.

**"Unknown mail template renderer ..."**
Você usou um template cujo motor não está instalado. Corrija com `composer require twig/twig` ou `composer require latte/latte`, ou registre um renderizador personalizado com o nome da extensão.

**"Unknown mail transport ..."**
Um `->transport('name')` (ou `default_transport`) não corresponde a nenhuma chave em `dsns`. Verifique a ortografia - o erro lista os nomes configurados.

**O e-mail não está chegando**
Aponte `dsns` para `null://null` para confirmar que o resto do seu código funciona, depois volte para o DSN real. No DDEV, use `smtp://127.0.0.1:1025` e inspecione as mensagens no Mailpit na porta 8025.

---

Para relatórios de bugs, pull requests e o código-fonte completo, visite o [repositório GitHub](https://github.com/ryanstubbs/flightmail).