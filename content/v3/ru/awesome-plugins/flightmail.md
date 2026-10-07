# FlightMail

> **Сторонний плагин** — поддерживается [Ryan Stubbs](https://ryanstubbs.co.uk) ([ryanstubbs/flightmail](https://github.com/ryanstubbs/flightmail), лицензия MIT). Не является частью ядра Flight — сообщайте о проблемах в [его GitHub-репозитории](https://github.com/ryanstubbs/flightmail/issues).

[ryanstubbs/flightmail](https://github.com/ryanstubbs/flightmail) позволяет отправлять электронную почту из вашего Flight-приложения без лишней головной боли. Он оборачивает **Symfony Mailer** — самую проверенную почтовую библиотеку в PHP — и делает её частью Flight. Одна строка для установки, одна плавная цепочка вызовов для отправки:

```php
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('You did it!')
    ->text('Your first email is on its way.')
    ->send();
```

## Возможности

- **Любой провайдер, по одной строке на каждого.** SMTP, Postmark, Sendgrid, Mailgun, Amazon SES, Brevo и другие работают через простые DSN-строки.
- **Несколько провайдеров одновременно.** Транзакционные письма через Postmark, рассылки через свой SMTP — выбирайте для каждого сообщения.
- **Шаблоны, если хотите.** Рендерите тела писем с помощью Twig или Latte. Не нужны шаблоны? Просто передавайте строки и ничего дополнительно не устанавливайте.
- **Полировка при отправке.** Необязательное внедрение CSS и автоматическое создание текстовой части из вашего HTML, работающие на библиотеках, которые вы устанавливаете только при необходимости.
- **Скучно в хорошем смысле.** Ленивые подключения, понятные ошибки вместо молчаливо проглоченной почты, и всё можно заменить, если нужно что-то своё.

## Требования

| Что               | Версия                               |
| ----------------- | ------------------------------------ |
| PHP               | 8.2 или новее                        |
| Flight PHP        | ядро ^3.15                           |
| Symfony Mailer    | ^7.2 или ^8.0 (устанавливается автоматически) |

## Установка

```bash
composer require ryanstubbs/flightmail
```

Этого достаточно для отправки текстовых и HTML-писем. Рендеринг шаблонов подключается по желанию — добавляйте движок только если будете его использовать:

```bash
composer require twig/twig      # для шаблонов .twig
composer require latte/latte    # для шаблонов .latte
```

Ещё две необязательные библиотеки обеспечивают улучшения при отправке, описанные [ниже](#styling-html-and-generating-text-parts):

```bash
composer require pelago/emogrifier         # для внедрения CSS ("inline_css")
composer require league/html-to-markdown   # для текстовой части в Markdown ("text_from_html")
```

Все они могут быть установлены одновременно; FlightMail выбирает нужную на основе вашей конфигурации.

## Ваше первое письмо

Добавьте это в свой bootstrap (туда же, где определяете маршруты):

```php
<?php
require 'vendor/autoload.php';

use ryanstubbs\FlightMail\MailPlugin;

// Укажите FlightMail, откуда и через что отправлять почту.
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

Используете [скелет Flight PHP](https://github.com/flightphp/skeleton)? Зарегистрируйте в `app/config/services.php` в стиле экземпляра:

```php
use ryanstubbs\FlightMail\MailPlugin;

MailPlugin::register($app, [
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'from' => 'no-reply@example.com',
]);
```

Оба стиля открывают один и тот же почтовый объект: `Flight::mail()` и `$app->mail()` взаимозаменяемы.

> **Тестируете локально?** Если ваш проект работает в [DDEV](https://ddev.com), укажите DSN `smtp://127.0.0.1:1025` и просматривайте все перехваченные письма в Mailpit на `http://<project>.ddev.site:8025`. Ничего не покидает вашу машину.

## Отправка писем

### Простые строки (движок шаблонов не нужен)

`->text()` и `->html()` принимают обычные строки, и для них ничего дополнительно устанавливать не нужно:

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

### Шаблоны Twig

```php
// welcome.html.twig содержит: Hello {{ name }}, thanks for signing up!
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.html.twig', ['name' => 'Ryan'])
    ->send();
```

### Шаблоны Latte

Та же идея, расширение `.latte`:

```php
// welcome.latte содержит: Hello {$name}, thanks for signing up!
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.latte', ['name' => 'Ryan'])
    ->send();
```

### HTML + текст вместе

Лучшая практика для доставляемости — дайте почтовым клиентам обе версии:

```php
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.html.twig', ['name' => 'Ryan'])     // богатая версия
    ->textTemplate('welcome.txt.twig', ['name' => 'Ryan'])  // запасная версия
    ->send();
```

Пара важных моментов о шаблонах:

- Они рендерятся **лениво**, в момент отправки — составляйте сейчас, рендерите потом.
- Движок выбирается по расширению: `.twig` → Twig, `.latte` → Latte, всё остальное → ваш настроенный движок по умолчанию (опция `renderer`).
- Явное `->html()` или `->text()` всегда имеет приоритет над шаблоном, поэтому вы можете задать шаблон по умолчанию и переопределять его для конкретного сообщения.

## Стилизация HTML и создание текстовой части

Два необязательных улучшения при отправке, оба выключены по умолчанию и оба работают на библиотеках, которые вы устанавливаете только при необходимости:

| Функция                | Установка                  | Ключ конфигурации |
| ---------------------- | -------------------------- | ----------------- |
| Внедрение CSS          | `pelago/emogrifier`        | `inline_css`      |
| Текстовая часть из HTML| `league/html-to-markdown`  | `text_from_html`  |

### Внедрение CSS в ваше HTML-письмо

Gmail и большинство веб-почтовиков вырезают блоки `<style>` — только инлайновые атрибуты `style=""` они надёжно поддерживают. Писать их вручную — мучение; позвольте [Emogrifier](https://github.com/MyIntervals/emogrifier) сделать это во время отправки:

```bash
composer require pelago/emogrifier
```

```php
MailPlugin::install([
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'inline_css' => true,
]);
```

С этой настройкой каждое HTML-тело получает инлайновый CSS непосредственно перед отправкой — независимо от того, пришло ли оно из шаблона или из `->html()`. Сообщение вроде `<style>p { color: red; }</style><p>Hi</p>` уходит как `<p style="color: red;">Hi</p>`.

Чтобы добавить общие стили в каждое письмо (фирменные цвета, сбросы) без их повторения в каждом шаблоне, передайте правила напрямую или укажите файл таблицы стилей:

```php
'inline_css' => ['css_file' => __DIR__ . '/mail-styles/base.css'],
// или
'inline_css' => ['css' => '.button { background: #0a84ff; color: #fff; }'],
```

Управление на уровне конкретного сообщения:

```php
$message->inlineCss();          // принудительно внедрить CSS для этого сообщения
$message->withoutInlineCss();   // пропустить, даже если включено глобально
```

### Создание текстовой части из HTML

Лучшая практика — отправлять HTML и текстовую версию вместе, но писать обе утомительно. FlightMail может автоматически создать текстовую часть из итогового HTML — базовое преобразование не требует дополнительных зависимостей, так как конвертер поставляется с Symfony Mime:

```php
MailPlugin::install([
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'text_from_html' => true,       // Markdown, когда возможно, иначе простой текст
]);
```

Режимы:

- `true` или `'auto'` — вывод Markdown, если установлен `league/html-to-markdown`, иначе простое удаление тегов.
- `'markdown'` — принудительный Markdown (`composer require league/html-to-markdown`; заголовки становятся `==`, ссылки `[text](url)`, жирный `**bold**`).
- `'plain'` — всегда удалять теги; работает вообще без дополнительных пакетов.

Генерация выполняется после рендеринга и внедрения CSS и только когда у сообщения есть HTML-тело, но нет текстового — явное `->text()` или `->textTemplate()` всегда имеет приоритет. Переопределения на уровне сообщения зеркалят внедрение CSS:

```php
$message->textFromHtml('plain');    // принудительно удалить теги для этого сообщения
$message->withoutTextFromHtml();    // письмо только с HTML
```

Если включён режим, библиотека для которого не установлена, вы получите понятную ошибку с точной командой `composer require` — никакой тихой деградации.

## Выбор провайдера

Провайдеры подключаются через DSN-строки. Установите мостовой пакет, вставьте DSN в `dsns`, готово.

| Провайдер            | Установка                                     | Пример DSN                                   |
| -------------------- | --------------------------------------------- | -------------------------------------------- |
| SMTP                 | встроенный                                   | `smtp://user:pass@host:587`                  |
| Sendmail             | встроенный                                   | `sendmail://default`                         |
| Dev/null (отбрасывать почту) | встроенный                                   | `null://null`                                |
| Postmark             | `composer require symfony/postmark-mailer`   | `postmark+api://KEY@api.postmarkapp.com`     |
| Sendgrid             | `composer require symfony/sendgrid-mailer`   | `sendgrid+api://KEY@default`                 |
| Mailgun              | `composer require symfony/mailgun-mailer`    | `mailgun+https://KEY:DOMAIN@api.mailgun.net` |
| Amazon SES           | `composer require symfony/amazon-mailer`     | `ses+https://KEY:SECRET@default`             |
| Brevo                | `composer require symfony/brevo-mailer`      | `brevo+api://KEY@default`                    |
| MailerSend           | `composer require symfony/mailersend-mailer` | `mailersend+api://KEY@default`               |

Полный список — в [документации Symfony Mailer](https://symfony.com/doc/current/mailer.html) — всё, что там описано, работает здесь без изменений.

### Несколько провайдеров одновременно

Назовите каждый транспорт, затем выбирайте для каждого сообщения:

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
// Без вызова ->transport() используется первый ключ в "dsns" (здесь "transactional").
Flight::mail()->compose()->to('...')->text('receipt')->send();

// Явный выбор другого маршрута.
Flight::mail()->compose()->to('...')->text('newsletter')->transport('bulk')->send();
```

## Справочник по конфигурации

Всё необязательно, кроме `dsns`.

```php
MailPlugin::install([
    // ОБЯЗАТЕЛЬНО - имя транспорта => DSN Symfony.
    // Первая запись используется, когда сообщение не указывает транспорт.
    'dsns' => [
        'default' => 'smtp://user:pass@localhost:1025',
    ],

    // Транспорт, используемый, когда у сообщения нет явного ->transport() и
    // вы не хотите использовать первый ключ. Должен существовать в "dsns".
    'default_transport' => 'default',

    // Глобальный отправитель. Строка, адрес Symfony или ['email' => 'Имя'].
    // Применяется только когда сообщение не задаёт собственный ->from().
    'from' => ['no-reply@example.com' => 'My App'],

    // Движок шаблонов по умолчанию: 'twig', 'latte' или произвольное имя.
    // Используется только для шаблонов, чьё расширение не зарегистрировано как рендерер.
    'renderer' => 'twig',

    // Где лежат шаблоны, поиск по порядку; плюс необязательная папка кэша.
    'templates' => [
        'paths' => [__DIR__ . '/mail-templates'],
        'cache' => __DIR__ . '/cache/mail',
    ],

    // Дополнительные параметры, передаваемые напрямую в Twig\Environment.
    'twig' => ['options' => ['strict_variables' => true]],

    // Настройка движка Latte при запуске: fn(Latte\Engine $engine): void.
    'latte' => ['setup' => static fn (Latte\Engine $e) => $e->addExtension(new MyExtension())],

    // Улучшения тела при отправке (см. "Стилизация HTML и создание текстовой части").
    'inline_css' => true,           // или ['css' => '...', 'css_file' => '...']
    'text_from_html' => true,       // или 'plain' / 'markdown'

    // Пользовательские схемы DSN, пользовательские рендереры, хуки перед отправкой (см. ниже).
    'transport_factories' => [],
    'renderers' => [],
    'hooks' => [],

    // Необязательная инфраструктура для каждого транспорта.
    'event_dispatcher' => $dispatcher,  // события Symfony MessageEvents
    'logger' => $psr3Logger,
]);
```

## Дальнейшие возможности

Всё ниже необязательно. Настроек по умолчанию достаточно для большинства приложений.

### Добавление собственной схемы DSN

Реализуйте интерфейс Symfony `TransportFactoryInterface` и зарегистрируйте его — тогда ваша схема будет работать точно так же, как встроенная:

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
        // ... создайте транспорт, который общается с вашим перевозчиком
    }
}

$plugin = MailPlugin::install(['dsns' => ['carrier' => 'mycarrier://key']]);
$plugin->addTransportFactory(new MyCarrierFactory());
```

### Добавление собственного рендерера шаблонов

Подойдёт всё, что превращает имя шаблона плюс параметры в строку:

```php
use ryanstubbs\FlightMail\MailPlugin;
use ryanstubbs\FlightMail\Render\RendererInterface;

$plugin = MailPlugin::install($config);

$plugin->addRenderer('markdown', fn (array $config): RendererInterface =>
    new MarkdownMailRenderer($config['templates']['paths'] ?? [])
);
```

```php
// Шаблоны, заканчивающиеся на .markdown, теперь используются автоматически:
Flight::mail()->compose()->to('...')->template('welcome.markdown', ['name' => 'Ryan'])->send();
```

### Выполнение чего-либо непосредственно перед отправкой

Хуки получают готовое сообщение — после рендеринга, после значений по умолчанию, прямо перед отправкой:

```php
$plugin->addHook(function (ryanstubbs\FlightMail\Message $message): void {
    $message->getHeaders()->addTextHeader('X-Mailer', 'MyApp/1.0');
});
```

### События и журналирование

Передайте диспетчер событий Symfony и/или PSR-3 логгер — и каждый транспорт будет ими пользоваться:

```php
$plugin->eventDispatcher($dispatcher); // получает MessageEvent перед каждой отправкой
$plugin->logger($logger);              // журналы на уровне транспорта
```

## Шпаргалка по API

```php
// Настройка
MailPlugin::install($config)             // регистрация в глобальном приложении Flight
MailPlugin::register($app, $config)      // регистрация в конкретном экземпляре Engine
$mailer = Flight::mail();                // общий экземпляр Mailer

// Создание сообщений
$mailer->compose(): Message
$message->to(...)->from(...)->subject(...)   // стандартные методы Symfony Mime
$message->text(string)                       // тело из обычной строки
$message->html(string)                       // тело из HTML-строки
$message->template($name, $params)           // HTML-тело из шаблона
$message->htmlTemplate($name, $params)       // псевдоним template()
$message->textTemplate($name, $params)       // текстовое тело из шаблона
$message->inlineCss() / ->withoutInlineCss() // внедрение CSS для конкретного сообщения
$message->textFromHtml($mode)                // автотекстовая часть: true/'auto'/'plain'/'markdown'/false
$message->withoutTextFromHtml()              // письмо только с HTML
$message->transport($name)                   // маршрут через именованный DSN
$message->send(): ?SentMessage               // рендер + отправка

// На самом почтовике
$mailer->send($message): ?SentMessage        // явная альтернатива $message->send()
$mailer->render($template, $params): string  // рендер без отправки
$mailer->addHook(callable): static           // fn(Message $message): void
$mailer->transports(): TransportManager      // get() / has() / names()
$mailer->renderers(): RendererFactory        // create() / has() / add()
```

Поскольку `Message` расширяет `Symfony\Component\Mime\Email`, все уже знакомые вам методы Symfony — `attach()`, `embed()`, `priority()`, `replyTo()` — работают из коробки.

## Устранение неполадок

**«No mail DSNs configured»**
Вы вызвали `Flight::mail()` до регистрации плагина, или в массиве конфигурации не было `dsns`. Эта ошибка намеренная — FlightMail отказывается угадывать, куда должна идти ваша почта, вместо того чтобы молча её терять.

**«Unknown mail template renderer ...»**
Вы использовали шаблон, движок которого не установлен. Исправьте с помощью `composer require twig/twig` или `composer require latte/latte`, либо зарегистрируйте собственный рендерер с именем, соответствующим расширению.

**«Unknown mail transport ...»**
`->transport('name')` (или `default_transport`) не соответствует ни одному ключу в `dsns`. Проверьте написание — в ошибке перечислены настроенные имена.

**Почта не приходит**
Укажите `dsns` в `null://null`, чтобы убедиться, что остальной код работает, затем верните настоящий DSN. В DDEV используйте `smtp://127.0.0.1:1025` и просматривайте сообщения в Mailpit на порту 8025.

---

Об ошибках, запросах на изменения и полном исходном коде сообщайте в [GitHub-репозитории](https://github.com/ryanstubbs/flightmail).