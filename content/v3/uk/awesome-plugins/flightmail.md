# FlightMail

> **Сторонній плагін** - підтримується [Ryan Stubbs](https://ryanstubbs.co.uk) ([ryanstubbs/flightmail](https://github.com/ryanstubbs/flightmail), ліцензія MIT). Не є частиною ядра Flight - повідомляйте про проблеми в [його репозиторії GitHub](https://github.com/ryanstubbs/flightmail/issues).

[ryanstubbs/flightmail](https://github.com/ryanstubbs/flightmail) дозволяє надсилати електронні листи з вашого застосунку Flight без зайвого клопоту. Він обгортає **Symfony Mailer** - найперевіренішу поштову бібліотеку в PHP - і робить її схожою на частину Flight. Один рядок для встановлення, один ланцюжок методів для надсилання:

```php
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('You did it!')
    ->text('Your first email is on its way.')
    ->send();
```

## Функції

- **Будь-який провайдер, по одному рядку на кожен.** SMTP, Postmark, Sendgrid, Mailgun, Amazon SES, Brevo та інші працюють через прості DSN-рядки.
- **Використовуйте кількох провайдерів одночасно.** Транзакційна пошта через Postmark, розсилки через ваш власний SMTP - вибирайте для кожного повідомлення.
- **Шаблони, якщо хочете.** Рендерте тіла за допомогою Twig або Latte. Не хочете шаблонів? Просто передавайте рядки й нічого додаткового не встановлюйте.
- **Оздоблення під час надсилання.** Необов'язкове вбудовування CSS і автоматичні текстові частини, отримані з вашого HTML, на основі бібліотек, які ви встановлюєте лише за потреби.
- **Нудний у найкращому сенсі.** Ліниві з'єднання, зрозумілі помилки замість тихо проковтнутої пошти, і все можна замінити, якщо потрібно щось власне.

## Вимоги

| Що             | Версія                                 |
| -------------- | -------------------------------------- |
| PHP            | 8.2 або новіша                         |
| Flight PHP     | ядро ^3.15                             |
| Symfony Mailer | ^7.2 або ^8.0 (встановлюється автоматично) |

## Встановлення

```bash
composer require ryanstubbs/flightmail
```

Це все для надсилання звичайних текстових і HTML-листів. Рендеринг шаблонів вмикається за бажанням - додайте рушій, лише якщо будете ним користуватися:

```bash
composer require twig/twig      # для шаблонів .twig
composer require latte/latte    # для шаблонів .latte
```

Ще дві необов'язкові бібліотеки забезпечують покращення під час надсилання, описані [нижче](#styling-html-and-generating-text-parts):

```bash
composer require pelago/emogrifier         # для вбудовування CSS ("inline_css")
composer require league/html-to-markdown   # для текстових частин Markdown ("text_from_html")
```

Усі їх можна встановити разом; FlightMail вибирає потрібну залежно від вашої конфігурації.

## Ваш перший лист

Додайте це до свого bootstrap (туди ж, де ви визначаєте маршрути):

```php
<?php
require 'vendor/autoload.php';

use ryanstubbs\FlightMail\MailPlugin;

// Повідомте FlightMail, звідки й через що надсилати пошту.
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

Використовуєте [скелет Flight PHP](https://github.com/flightphp/skeleton)? Зареєструйте в `app/config/services.php` у стилі з інстансом:

```php
use ryanstubbs\FlightMail\MailPlugin;

MailPlugin::register($app, [
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'from' => 'no-reply@example.com',
]);
```

Обидва стилі надають той самий mailer: `Flight::mail()` і `$app->mail()` взаємозамінні.

> **Тестуєте локально?** Якщо ваш проєкт працює в [DDEV](https://ddev.com), спрямуйте DSN на `smtp://127.0.0.1:1025` і читайте кожен перехоплений лист у Mailpit за адресою `http://<project>.ddev.site:8025`. Нічого не залишає вашу машину.

## Надсилання листів

### Звичайні рядки (рушій шаблонів не потрібен)

`->text()` і `->html()` приймають необроблені рядки й не потребують нічого додатково:

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

### Шаблони Twig

```php
// welcome.html.twig містить: Привіт {{ name }}, дякуємо за реєстрацію!
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.html.twig', ['name' => 'Ryan'])
    ->send();
```

### Шаблони Latte

Та сама ідея, розширення `.latte`:

```php
// welcome.latte містить: Привіт {$name}, дякуємо за реєстрацію!
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.latte', ['name' => 'Ryan'])
    ->send();
```

### HTML + звичайний текст разом

Найкраща практика для доставності - дайте поштовим клієнтам обидві версії:

```php
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.html.twig', ['name' => 'Ryan'])     // розширена версія
    ->textTemplate('welcome.txt.twig', ['name' => 'Ryan'])  // резервна версія
    ->send();
```

Кілька речей, які варто знати про шаблони:

- Вони рендеряться **ліниво**, під час надсилання - компонуйте зараз, рендерте пізніше.
- Рушій вибирається за розширенням: `.twig` → Twig, `.latte` → Latte, будь-що інше → ваш налаштований типовий (`renderer` опція).
- Явне тіло `->html()` або `->text()` завжди має перевагу над шаблоном, тож ви можете встановити типовий шаблон і перевизначити його для кожного повідомлення.

## Стилізація HTML і генерація текстових частин

Два необов'язкові покращення під час надсилання, обидва вимкнені за замовчуванням і обидва працюють на бібліотеках, які ви встановлюєте лише за бажанням:

| Функція             | Встановлення              | Ключ конфігурації |
| ------------------- | ------------------------- | ---------------- |
| Вбудовування CSS    | `pelago/emogrifier`       | `inline_css`     |
| Текстова частина з HTML | `league/html-to-markdown` | `text_from_html` |

### Вбудовування CSS у ваш HTML-лист

Gmail і більшість вебклієнтів вилучають блоки `<style>` - вбудовані атрибути `style=""` є єдиним стилізуванням, яке вони надійно враховують. Писати їх вручну жахливо; дозвольте [Emogrifier](https://github.com/MyIntervals/emogrifier) зробити це під час надсилання:

```bash
composer require pelago/emogrifier
```

```php
MailPlugin::install([
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'inline_css' => true,
]);
```

Коли це увімкнено, кожне HTML-тіло отримує вбудований CSS безпосередньо перед надсиланням - незалежно від того, чи воно походить із шаблону, чи з `->html()`. Повідомлення на кшталт `<style>p { color: red; }</style><p>Hi</p>` надсилається як `<p style="color: red;">Hi</p>`.

Щоб додати спільні стилі до кожного листа (кольори бренду, скидання) без повторення їх у кожному шаблоні, передайте правила безпосередньо або вкажіть файл таблиці стилів:

```php
'inline_css' => ['css_file' => __DIR__ . '/mail-styles/base.css'],
// або
'inline_css' => ['css' => '.button { background: #0a84ff; color: #fff; }'],
```

Керування для кожного повідомлення:

```php
$message->inlineCss();          // примусово вбудувати для цього одного листа
$message->withoutInlineCss();   // пропустити, навіть якщо глобально увімкнено
```

### Генеруйте текстову частину з вашого HTML

Найкраща практика - надсилати HTML- і звичайну текстову версію разом, але писати обидві втомливо. FlightMail може автоматично отримати текстову частину з фінального HTML - базове перетворення не потребує додаткових залежностей, оскільки конвертер постачається із Symfony Mime:

```php
MailPlugin::install([
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'text_from_html' => true,       // Markdown, коли можливо, інакше звичайний текст
]);
```

Режими:

- `true` або `'auto'` - виведення Markdown, якщо встановлено `league/html-to-markdown`, інакше просте видалення тегів.
- `'markdown'` - примусовий Markdown (`composer require league/html-to-markdown`; заголовки стають `==`, посилання `[text](url)`, жирний `**bold**`).
- `'plain'` - завжди видаляти теги; працює без жодних додаткових пакетів.

Генерація виконується після рендерингу та вбудовування CSS і лише тоді, коли повідомлення має HTML-тіло, але не має текстового тіла - явний `->text()` або `->textTemplate()` завжди має перевагу. Перевизначення для кожного повідомлення дзеркалять вбудовування:

```php
$message->textFromHtml('plain');    // примусово прибрати теги для цього одного
$message->withoutTextFromHtml();    // лише HTML-лист
```

Увімкніть режим, бібліотеку якого не встановлено, і ви отримаєте чітку помилку з точною командою `composer require`, яку потрібно виконати - жодної тихої деградації.

## Вибір провайдера

Провайдери підключаються через DSN-рядки. Встановіть пакет-міст, вставте DSN у `dsns` - готово.

| Провайдер             | Встановлення                                      | Приклад DSN                                  |
| -------------------- | -------------------------------------------- | -------------------------------------------- |
| SMTP                 | вбудовано                                     | `smtp://user:pass@host:587`                  |
| Sendmail             | вбудовано                                     | `sendmail://default`                         |
| Dev/null (відкидати пошту) | вбудовано                                     | `null://null`                                |
| Postmark             | `composer require symfony/postmark-mailer`   | `postmark+api://KEY@api.postmarkapp.com`     |
| Sendgrid             | `composer require symfony/sendgrid-mailer`   | `sendgrid+api://KEY@default`                 |
| Mailgun              | `composer require symfony/mailgun-mailer`    | `mailgun+https://KEY:DOMAIN@api.mailgun.net` |
| Amazon SES           | `composer require symfony/amazon-mailer`     | `ses+https://KEY:SECRET@default`             |
| Brevo                | `composer require symfony/brevo-mailer`      | `brevo+api://KEY@default`                    |
| MailerSend           | `composer require symfony/mailersend-mailer` | `mailersend+api://KEY@default`               |

Повний список міститься в [документації Symfony Mailer](https://symfony.com/doc/current/mailer.html) - усе, що там задокументовано, працює тут без змін.

### Кілька провайдерів одночасно

Назвіть кожен транспорт, потім вибирайте для кожного повідомлення:

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
// Без виклику ->transport() = перший ключ у "dsns" (тут "transactional").
Flight::mail()->compose()->to('...')->text('receipt')->send();

// Явно виберіть інший маршрут.
Flight::mail()->compose()->to('...')->text('newsletter')->transport('bulk')->send();
```

## Довідник конфігурації

Усе необов'язкове, крім `dsns`.

```php
MailPlugin::install([
    // ОБОВ'ЯЗКОВО - назва транспорту => Symfony DSN.
    // Перший запис використовується, коли повідомлення не називає жодного.
    'dsns' => [
        'default' => 'smtp://user:pass@localhost:1025',
    ],

    // Транспорт, який використовується, коли повідомлення не має явного ->transport() і
    // ви не хочете використовувати перший ключ. Має існувати в "dsns".
    'default_transport' => 'default',

    // Глобальний відправник. Рядок, Symfony Address або ['email' => 'Name'].
    // Застосовується лише коли повідомлення не встановлює власний ->from().
    'from' => ['no-reply@example.com' => 'My App'],

    // Типовий рушій шаблонів: 'twig', 'latte' або власна назва.
    // Використовується лише для шаблонів, розширення яких не належить зареєстрованому рендереру.
    'renderer' => 'twig',

    // Де лежать шаблони, пошук у порядку; плюс необов'язкова директорія кешу.
    'templates' => [
        'paths' => [__DIR__ . '/mail-templates'],
        'cache' => __DIR__ . '/cache/mail',
    ],

    // Додаткові опції, передані безпосередньо до Twig\Environment.
    'twig' => ['options' => ['strict_variables' => true]],

    // Налаштуйте рушій Latte під час запуску: fn(Latte\Engine $engine): void.
    'latte' => ['setup' => static fn (Latte\Engine $e) => $e->addExtension(new MyExtension())],

    // Покращення тіла під час надсилання (див. "Стилізація HTML і генерація текстових частин").
    'inline_css' => true,           // або ['css' => '...', 'css_file' => '...']
    'text_from_html' => true,       // або 'plain' / 'markdown'

    // Власні схеми DSN, власні рендерери, хуки перед надсиланням (див. нижче).
    'transport_factories' => [],
    'renderers' => [],
    'hooks' => [],

    // Необов'язкова інфраструктура, передана кожному транспорту.
    'event_dispatcher' => $dispatcher,  // події Symfony MessageEvents
    'logger' => $psr3Logger,
]);
```

## Далі

Усе нижче необов'язкове. Типові налаштування покривають більшість застосунків.

### Додати власну схему DSN

Реалізуйте Symfony `TransportFactoryInterface` і зареєструйте його - тоді ваша власна схема працюватиме точно так само, як вбудована:

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
        // ... створіть транспорт, який спілкується з вашим оператором
    }
}

$plugin = MailPlugin::install(['dsns' => ['carrier' => 'mycarrier://key']]);
$plugin->addTransportFactory(new MyCarrierFactory());
```

### Додати власний рендерер шаблонів

Підходить будь-що, що перетворює назву шаблону плюс параметри в рядок:

```php
use ryanstubbs\FlightMail\MailPlugin;
use ryanstubbs\FlightMail\Render\RendererInterface;

$plugin = MailPlugin::install($config);

$plugin->addRenderer('markdown', fn (array $config): RendererInterface =>
    new MarkdownMailRenderer($config['templates']['paths'] ?? [])
);
```

```php
// Шаблони із закінченням .markdown тепер використовують його автоматично:
Flight::mail()->compose()->to('...')->template('welcome.markdown', ['name' => 'Ryan'])->send();
```

### Запустити щось безпосередньо перед надсиланням

Хуки отримують завершене повідомлення - після рендерингу, після типових значень, безпосередньо перед відправленням:

```php
$plugin->addHook(function (ryanstubbs\FlightMail\Message $message): void {
    $message->getHeaders()->addTextHeader('X-Mailer', 'MyApp/1.0');
});
```

### Події та журналювання

Передайте Symfony event dispatcher та/або PSR-3 logger, і кожен транспорт використовуватиме їх:

```php
$plugin->eventDispatcher($dispatcher); // отримує MessageEvent перед кожним надсиланням
$plugin->logger($logger);              // журнали рівня транспорту
```

## Шпаргалка з API

```php
// Налаштування
MailPlugin::install($config)             // зареєструвати в глобальному застосунку Flight
MailPlugin::register($app, $config)      // зареєструвати в конкретному Engine
$mailer = Flight::mail();                // спільний екземпляр Mailer

// Побудова повідомлень
$mailer->compose(): Message
$message->to(...)->from(...)->subject(...)   // стандартні методи Symfony Mime
$message->text(string)                       // тіло звичайного рядка
$message->html(string)                       // тіло HTML-рядка
$message->template($name, $params)           // HTML-тіло з шаблону
$message->htmlTemplate($name, $params)       // псевдонім template()
$message->textTemplate($name, $params)       // текстове тіло з шаблону
$message->inlineCss() / ->withoutInlineCss() // вбудовування CSS для кожного повідомлення
$message->textFromHtml($mode)                // автотекстова частина: true/'auto'/'plain'/'markdown'/false
$message->withoutTextFromHtml()              // лише HTML-лист
$message->transport($name)                   // маршрутизація через названий DSN
$message->send(): ?SentMessage               // рендеринг + надсилання

// На самому mailer
$mailer->send($message): ?SentMessage        // явна альтернатива $message->send()
$mailer->render($template, $params): string  // рендеринг без надсилання
$mailer->addHook(callable): static           // fn(Message $message): void
$mailer->transports(): TransportManager      // get() / has() / names()
$mailer->renderers(): RendererFactory        // create() / has() / add()
```

Оскільки `Message` розширює `Symfony\Component\Mime\Email`, кожен метод Symfony, який ви вже знаєте - `attach()`, `embed()`, `priority()`, `replyTo()` - працює одразу.

## Усунення проблем

**"No mail DSNs configured"**
Ви викликали `Flight::mail()` до реєстрації плагіна, або масив конфігурації не містив `dsns`. Ця помилка навмисна - FlightMail відмовляється вгадувати, куди має йти ваша пошта, замість того щоб тихо її відкидати.

**"Unknown mail template renderer ..."**
Ви використали шаблон, рушій якого не встановлено. Виправте за допомогою `composer require twig/twig` або `composer require latte/latte`, або зареєструйте власний рендерер із назвою за розширенням.

**"Unknown mail transport ..."**
`->transport('name')` (або `default_transport`) не збігається з жодним ключем у `dsns`. Перевірте написання - помилка перелічує налаштовані назви.

**Пошта не надходить**
Спрямуйте `dsns` на `null://null`, щоб переконатися, що решта вашого коду працює, потім поверніться до справжнього DSN. У DDEV використовуйте `smtp://127.0.0.1:1025` і переглядайте повідомлення в Mailpit на порту 8025.

---

Щоб повідомити про помилки, надіслати pull request або отримати повний код, відвідайте [репозиторій GitHub](https://github.com/ryanstubbs/flightmail).