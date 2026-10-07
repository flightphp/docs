# FlightMail

> **Complemento de terceros**: mantenido por [Ryan Stubbs](https://ryanstubbs.co.uk) ([ryanstubbs/flightmail](https://github.com/ryanstubbs/flightmail), licencia MIT). No forma parte del núcleo de Flight; por favor, reporta los problemas en [su repositorio de GitHub](https://github.com/ryanstubbs/flightmail/issues).

[ryanstubbs/flightmail](https://github.com/ryanstubbs/flightmail) te permite enviar correos electrónicos desde tu aplicación Flight sin dolores de cabeza. Envuelve **Symfony Mailer**, la librería de correo más probada en PHP, y la hace sentir como parte de Flight. Una línea para instalar, una cadena fluida para enviar:

```php
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('You did it!')
    ->text('Your first email is on its way.')
    ->send();
```

## Características

- **Cualquier proveedor, una línea cada uno.** SMTP, Postmark, Sendgrid, Mailgun, Amazon SES, Brevo y otros funcionan mediante cadenas DSN simples.
- **Usa varios proveedores a la vez.** Correo transaccional a través de Postmark, boletines a través de tu propio SMTP: elige por mensaje.
- **Plantillas si las quieres.** Renderiza cuerpos con Twig o Latte. ¿No quieres plantillas? Pasa cadenas de texto y no instales nada extra.
- **Pulido en el envío.** Incrustación de CSS opcional y partes de texto plano automáticas derivadas de tu HTML, impulsadas por librerías que solo instalas si las usas.
- **Aburrido en el mejor sentido.** Conexiones diferidas, errores claros en lugar de correos descartados silenciosamente, y todo es intercambiable si necesitas algo personalizado.

## Requisitos

| Qué                | Versión                              |
| ------------------ | ------------------------------------ |
| PHP                | 8.2 o superior                      |
| Flight PHP         | núcleo ^3.15                         |
| Symfony Mailer     | ^7.2 o ^8.0 (se instala automáticamente) |

## Instalación

```bash
composer require ryanstubbs/flightmail
```

Eso es todo para enviar correos en texto plano y HTML. El renderizado de plantillas es opcional: añade un motor solo si lo vas a usar:

```bash
composer require twig/twig      # para plantillas .twig
composer require latte/latte    # para plantillas .latte
```

Dos librerías opcionales más impulsan las mejoras de envío cubiertas [más abajo](#styling-html-and-generating-text-parts):

```bash
composer require pelago/emogrifier         # para incrustar CSS ("inline_css")
composer require league/html-to-markdown   # para partes de texto Markdown ("text_from_html")
```

Todas ellas pueden instalarse juntas; FlightMail elige la correcta según lo que configures.

## Tu primer correo

Añade esto a tu bootstrap (el mismo lugar donde defines rutas):

```php
<?php
require 'vendor/autoload.php';

use ryanstubbs\FlightMail\MailPlugin;

// Dile a FlightMail desde dónde y a través de qué enviar correos.
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

¿Usas el [esqueleto de Flight PHP](https://github.com/flightphp/skeleton)? Regístralo en `app/config/services.php` con el estilo de instancia:

```php
use ryanstubbs\FlightMail\MailPlugin;

MailPlugin::register($app, [
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'from' => 'no-reply@example.com',
]);
```

Ambos estilos exponen el mismo mailer: `Flight::mail()` y `$app->mail()` son intercambiables.

> **¿Probando localmente?** Si tu proyecto se ejecuta en [DDEV](https://ddev.com), apunta el DSN a `smtp://127.0.0.1:1025` y lee cada correo capturado en Mailpit en `http://<proyecto>.ddev.site:8025`. Nada sale de tu máquina.

## Envío de correos

### Cadenas de texto planas (sin motor de plantillas)

`->text()` y `->html()` aceptan cadenas sin procesar y no necesitan nada más instalado:

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

### Plantillas Twig

```php
// welcome.html.twig contiene: Hello {{ name }}, gracias por registrarte
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.html.twig', ['name' => 'Ryan'])
    ->send();
```

### Plantillas Latte

Misma idea, extensión `.latte`:

```php
// welcome.latte contiene: Hello {$name}, gracias por registrarte
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.latte', ['name' => 'Ryan'])
    ->send();
```

### HTML + texto plano juntos

Buena práctica para la entregabilidad: da a los clientes de correo ambas versiones:

```php
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.html.twig', ['name' => 'Ryan'])     // versión enriquecida
    ->textTemplate('welcome.txt.twig', ['name' => 'Ryan'])  // versión de respaldo
    ->send();
```

Algunas cosas útiles sobre las plantillas:

- Se renderizan **de forma diferida**, en el momento del envío: compón ahora, renderiza después.
- El motor se elige por extensión: `.twig` → Twig, `.latte` → Latte, cualquier otra cosa → tu predeterminado configurado (opción `renderer`).
- Un cuerpo explícito `->html()` o `->text()` siempre gana sobre una plantilla, de modo que puedes definir una plantilla predeterminada y anularla por mensaje.

## Aplicar estilos al HTML y generar partes de texto

Dos mejoras opcionales en el envío, ambas desactivadas por defecto y ambas impulsadas por librerías que solo instalas si las deseas:

| Característica               | Instalación                 | Clave de configuración |
| ---------------------------- | --------------------------- | ---------------------- |
| Incrustación de CSS          | `pelago/emogrifier`         | `inline_css`           |
| Parte de texto desde HTML    | `league/html-to-markdown`   | `text_from_html`       |

### Incrustar CSS en tu correo HTML

Gmail y la mayoría de los clientes de correo web eliminan los bloques `<style>`: los atributos `style=""` en línea son el único estilo que honran de forma fiable. Escribirlos a mano es miserable; deja que [Emogrifier](https://github.com/MyIntervals/emogrifier) lo haga en el momento del envío:

```bash
composer require pelago/emogrifier
```

```php
MailPlugin::install([
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'inline_css' => true,
]);
```

Con esto activado, cada cuerpo HTML recibe su CSS incrustado justo antes de enviarse, ya venga de una plantilla o de `->html()`. Un mensaje como `<style>p { color: red; }</style><p>Hi</p>` sale como `<p style="color: red;">Hi</p>`.

Para inyectar estilos compartidos en todos los correos (colores de marca, resets) sin repetirlos en cada plantilla, pasa reglas directamente o apunta a un archivo de hoja de estilos:

```php
'inline_css' => ['css_file' => __DIR__ . '/mail-styles/base.css'],
// o
'inline_css' => ['css' => '.button { background: #0a84ff; color: #fff; }'],
```

Control por mensaje:

```php
$message->inlineCss();          // fuerza la incrustación solo para este mensaje
$message->withoutInlineCss();   // la omite incluso si está activada globalmente
```

### Generar la parte de texto desde tu HTML

La buena práctica es enviar una versión HTML y otra de texto plano juntas, pero escribir ambas es tedioso. FlightMail puede derivar automáticamente la parte de texto del HTML final; la conversión básica no necesita dependencias adicionales, porque el conversor viene con Symfony Mime:

```php
MailPlugin::install([
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'text_from_html' => true,       // Markdown cuando sea posible, si no, texto plano
]);
```

Modos:

- `true` o `'auto'`: salida Markdown si `league/html-to-markdown` está instalado, si no, simple eliminación de etiquetas.
- `'markdown'`: fuerza Markdown (`composer require league/html-to-markdown`; los encabezados se convierten en `==`, los enlaces `[texto](url)`, la negrita `**negrita**`).
- `'plain'`: siempre elimina etiquetas; funciona con cero paquetes adicionales.

La generación ocurre después del renderizado y de la incrustación de CSS, y solo cuando el mensaje tiene cuerpo HTML pero no cuerpo de texto: un `->text()` o `->textTemplate()` explícito siempre gana. Las anulaciones por mensaje reflejan la incrustación:

```php
$message->textFromHtml('plain');    // fuerza la eliminación de etiquetas solo para este
$message->withoutTextFromHtml();    // correo solo HTML
```

Si activas un modo cuya librería no está instalada, obtienes un error claro que indica el `composer require` exacto a ejecutar; nunca una degradación silenciosa.

## Elegir un proveedor

Los proveedores se conectan mediante cadenas DSN. Instala el paquete puente, pega el DSN en `dsns`, listo.

| Proveedor             | Instalación                                   | Ejemplo de DSN                                |
| --------------------- | --------------------------------------------- | --------------------------------------------- |
| SMTP                  | incluido                                      | `smtp://user:pass@host:587`                   |
| Sendmail              | incluido                                      | `sendmail://default`                          |
| Dev/null (descarta correos) | incluido                                      | `null://null`                                 |
| Postmark              | `composer require symfony/postmark-mailer`    | `postmark+api://KEY@api.postmarkapp.com`      |
| Sendgrid              | `composer require symfony/sendgrid-mailer`    | `sendgrid+api://KEY@default`                  |
| Mailgun               | `composer require symfony/mailgun-mailer`     | `mailgun+https://KEY:DOMAIN@api.mailgun.net`  |
| Amazon SES            | `composer require symfony/amazon-mailer`      | `ses+https://KEY:SECRET@default`              |
| Brevo                 | `composer require symfony/brevo-mailer`       | `brevo+api://KEY@default`                     |
| MailerSend            | `composer require symfony/mailersend-mailer`  | `mailersend+api://KEY@default`                |

La lista completa está en la [documentación de Symfony Mailer](https://symfony.com/doc/current/mailer.html); cualquier cosa documentada allí funciona aquí sin cambios.

### Varios proveedores a la vez

Nombra cada transporte y luego elige por mensaje:

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
// Sin llamada a ->transport() = primera clave en "dsns" ("transactional" aquí).
Flight::mail()->compose()->to('...')->text('receipt')->send();

// Opta explícitamente por otra ruta.
Flight::mail()->compose()->to('...')->text('newsletter')->transport('bulk')->send();
```

## Referencia de configuración

Todo es opcional excepto `dsns`.

```php
MailPlugin::install([
    // OBLIGATORIO: nombre del transporte => DSN de Symfony.
    // La primera entrada se usa cuando un mensaje no menciona ninguno.
    'dsns' => [
        'default' => 'smtp://user:pass@localhost:1025',
    ],

    // Transporte usado cuando un mensaje no tiene ->transport() explícito
    // y no quieres la primera clave. Debe existir en "dsns".
    'default_transport' => 'default',

    // Remitente global. Cadena, dirección Symfony o ['email' => 'Nombre'].
    // Se aplica solo cuando un mensaje no define su propio ->from().
    'from' => ['no-reply@example.com' => 'My App'],

    // Motor de plantillas predeterminado: 'twig', 'latte' o un nombre personalizado.
    // Solo se consulta para plantillas cuya extensión no es un renderizador registrado.
    'renderer' => 'twig',

    // Dónde viven las plantillas, buscadas en orden; más un directorio de caché opcional.
    'templates' => [
        'paths' => [__DIR__ . '/mail-templates'],
        'cache' => __DIR__ . '/cache/mail',
    ],

    // Opciones extra pasadas directamente a Twig\Environment.
    'twig' => ['options' => ['strict_variables' => true]],

    // Ajusta el motor Latte al arrancar: fn(Latte\Engine $engine): void.
    'latte' => ['setup' => static fn (Latte\Engine $e) => $e->addExtension(new MyExtension())],

    // Mejoras del cuerpo en el envío (ver "Aplicar estilos al HTML y generar partes de texto").
    'inline_css' => true,           // o ['css' => '...', 'css_file' => '...']
    'text_from_html' => true,       // o 'plain' / 'markdown'

    // Esquemas DSN personalizados, renderizadores personalizados, ganchos de pre-envío (ver más abajo).
    'transport_factories' => [],
    'renderers' => [],
    'hooks' => [],

    // Infraestructura opcional entregada a cada transporte.
    'event_dispatcher' => $dispatcher,  // Symfony MessageEvents
    'logger' => $psr3Logger,
]);
```

## Ir más allá

Todo lo siguiente es opcional. Los valores predeterminados cubren la mayoría de aplicaciones.

### Añadir un esquema DSN personalizado

Implementa la `TransportFactoryInterface` de Symfony y regístrala; entonces tu propio esquema funciona exactamente como uno integrado:

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
        // ... construye un transporte que hable con tu operador
    }
}

$plugin = MailPlugin::install(['dsns' => ['carrier' => 'mycarrier://key']]);
$plugin->addTransportFactory(new MyCarrierFactory());
```

### Añadir un renderizador de plantillas personalizado

Cualquier cosa que convierta un nombre de plantilla más parámetros en una cadena vale:

```php
use ryanstubbs\FlightMail\MailPlugin;
use ryanstubbs\FlightMail\Render\RendererInterface;

$plugin = MailPlugin::install($config);

$plugin->addRenderer('markdown', fn (array $config): RendererInterface =>
    new MarkdownMailRenderer($config['templates']['paths'] ?? [])
);
```

```php
// Las plantillas terminadas en .markdown ahora lo usan automáticamente:
Flight::mail()->compose()->to('...')->template('welcome.markdown', ['name' => 'Ryan'])->send();
```

### Ejecutar algo justo antes de enviar

Los ganchos reciben el mensaje terminado: después del renderizado, después de los valores predeterminados, justo antes del cable:

```php
$plugin->addHook(function (ryanstubbs\FlightMail\Message $message): void {
    $message->getHeaders()->addTextHeader('X-Mailer', 'MyApp/1.0');
});
```

### Eventos y registro

Entrega un despachador de eventos de Symfony y/o un logger PSR-3 y todos los transportes los usarán:

```php
$plugin->eventDispatcher($dispatcher); // recibe MessageEvent antes de cada envío
$plugin->logger($logger);              // registros a nivel de transporte
```

## Referencia rápida de la API

```php
// Configuración
MailPlugin::install($config)             // registra en la aplicación Flight global
MailPlugin::register($app, $config)      // registra en un Engine específico
$mailer = Flight::mail();                // la instancia compartida de Mailer

// Construcción de mensajes
$mailer->compose(): Message
$message->to(...)->from(...)->subject(...)   // métodos estándar de Symfony Mime
$message->text(string)                       // cuerpo de cadena de texto plano
$message->html(string)                       // cuerpo de cadena HTML
$message->template($name, $params)           // cuerpo HTML desde una plantilla
$message->htmlTemplate($name, $params)       // alias de template()
$message->textTemplate($name, $params)       // cuerpo de texto desde una plantilla
$message->inlineCss() / ->withoutInlineCss() // incrustación de CSS por mensaje
$message->textFromHtml($mode)                // parte de texto automática: true/'auto'/'plain'/'markdown'/false
$message->withoutTextFromHtml()              // correo solo HTML
$message->transport($name)                   // enruta mediante un DSN nombrado
$message->send(): ?SentMessage               // renderiza + envía

// Sobre el propio mailer
$mailer->send($message): ?SentMessage        // alternativa explícita a $message->send()
$mailer->render($template, $params): string  // renderiza sin enviar
$mailer->addHook(callable): static           // fn(Message $message): void
$mailer->transports(): TransportManager      // get() / has() / names()
$mailer->renderers(): RendererFactory        // create() / has() / add()
```

Dado que `Message` extiende `Symfony\Component\Mime\Email`, todos los métodos de Symfony que ya conoces, `attach()`, `embed()`, `priority()`, `replyTo()`, funcionan de serie.

## Solución de problemas

**"No mail DSNs configured"**
Llamaste a `Flight::mail()` antes de registrar el complemento, o la matriz de configuración no incluía `dsns`. Este error es deliberado: FlightMail se niega a adivinar a dónde debe ir tu correo en lugar de descartarlo silenciosamente.

**"Unknown mail template renderer ..."**
Usaste una plantilla cuyo motor no está instalado. Arrégialo con `composer require twig/twig` o `composer require latte/latte`, o registra un renderizador personalizado con el nombre de la extensión.

**"Unknown mail transport ..."**
Un `->transport('nombre')` (o `default_transport`) no coincide con ninguna clave en `dsns`. Revisa la ortografía: el error lista los nombres configurados.

**El correo no llega**
Apunta `dsns` a `null://null` para confirmar que el resto de tu código funciona, y luego vuelve al DSN real. En DDEV, usa `smtp://127.0.0.1:1025` e inspecciona los mensajes en Mailpit en el puerto 8025.

---

Para informes de errores, solicitudes de extracción y el código fuente completo, visita el [repositorio de GitHub](https://github.com/ryanstubbs/flightmail).