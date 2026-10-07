# FlightMail

> **第三方插件** - 由 [Ryan Stubbs](https://ryanstubbs.co.uk) 维护（[ryanstubbs/flightmail](https://github.com/ryanstubbs/flightmail)，MIT 许可）。不是 Flight 核心的一部分 - 请在[其 GitHub 仓库](https://github.com/ryanstubbs/flightmail/issues)上报告问题。

[ryanstubbs/flightmail](https://github.com/ryanstubbs/flightmail) 让您可以从 Flight 应用发送电子邮件，无需烦恼。它封装了 **Symfony Mailer** - PHP 中最经受过考验的邮件库 - 并让它感觉像是 Flight 的一部分。一行安装，一个流畅的链式调用即可发送：

```php
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('You did it!')
    ->text('Your first email is on its way.')
    ->send();
```

## 功能

- **任何提供商，每行一个。** SMTP、Postmark、Sendgrid、Mailgun、Amazon SES、Brevo 等都可以通过简单的 DSN 字符串工作。
- **同时使用多个提供商。** 通过 Postmark 发送交易邮件，通过您自己的 SMTP 发送新闻通讯 - 每条消息可选择。
- **如果需要模板，也可以使用。** 使用 Twig 或 Latte 渲染正文。不想要模板？只需传递字符串，无需安装任何额外内容。
- **发送时优化。** 可选的 CSS 内联和从 HTML 自动派生的纯文本部分，由您仅在使用时才安装的库提供支持。
- **以最好的方式保持简单。** 惰性连接，清晰的错误而不是静默吞掉邮件，如果您需要自定义，一切都可以替换。

## 要求

| 项目           | 版本                                |
| -------------- | -------------------------------------- |
| PHP            | 8.2 或更高版本                           |
| Flight PHP     | 核心 ^3.15                             |
| Symfony Mailer | ^7.2 或 ^8.0（自动安装） |

## 安装

```bash
composer require ryanstubbs/flightmail
```

这就是发送纯文本和 HTML 电子邮件的全部内容。模板渲染是可选的 - 仅在您要使用时才添加引擎：

```bash
composer require twig/twig      # 用于 .twig 模板
composer require latte/latte    # 用于 .latte 模板
```

另外两个可选库支持[下面](#styling-html-and-generating-text-parts)介绍的发送时增强功能：

```bash
composer require pelago/emogrifier         # 用于 CSS 内联（"inline_css"）
composer require league/html-to-markdown   # 用于 Markdown 文本部分（"text_from_html"）
```

所有这些都可以并排安装；FlightMail 根据您的配置选择正确的一个。

## 您的第一封电子邮件

将此添加到您的引导文件（定义路由的同一位置）：

```php
<?php
require 'vendor/autoload.php';

use ryanstubbs\FlightMail\MailPlugin;

// 告诉 FlightMail 从哪里以及通过什么发送邮件。
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

使用 [Flight PHP 骨架](https://github.com/flightphp/skeleton)？改为在 `app/config/services.php` 中使用实例样式注册：

```php
use ryanstubbs\FlightMail\MailPlugin;

MailPlugin::register($app, [
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'from' => 'no-reply@example.com',
]);
```

两种样式都暴露相同的邮件程序：`Flight::mail()` 和 `$app->mail()` 可以互换。

> **本地测试？** 如果您的项目在 [DDEV](https://ddev.com) 中运行，请将 DSN 指向 `smtp://127.0.0.1:1025`，并在 Mailpit 中查看捕获的每封电子邮件，地址为 `http://<project>.ddev.site:8025`。没有任何内容离开您的机器。

## 发送电子邮件

### 纯字符串（无需模板引擎）

`->text()` 和 `->html()` 接受原始字符串，无需安装其他任何内容：

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

### Twig 模板

```php
// welcome.html.twig 包含：Hello {{ name }}, 感谢注册！
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.html.twig', ['name' => 'Ryan'])
    ->send();
```

### Latte 模板

相同的思路，`.latte` 扩展名：

```php
// welcome.latte 包含：Hello {$name}, 感谢注册！
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.latte', ['name' => 'Ryan'])
    ->send();
```

### HTML + 纯文本一起

为了提高送达率的最佳实践 - 给邮件客户端两个版本：

```php
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.html.twig', ['name' => 'Ryan'])     // 富文本版本
    ->textTemplate('welcome.txt.twig', ['name' => 'Ryan'])  // 后备版本
    ->send();
```

关于模板，有几件事值得了解：

- 它们在发送时**延迟**渲染 - 现在撰写，稍后渲染。
- 引擎根据扩展名选择：`.twig` → Twig，`.latte` → Latte，其他任何内容 → 您配置的默认（`renderer` 选项）。
- 显式的 `->html()` 或 `->text()` 正文始终优先于模板，因此您可以设置默认模板并按消息覆盖它。

## 样式化 HTML 和生成文本部分

两个可选的发送时增强功能，默认都关闭，并且都由仅在您需要时才安装的库提供支持：

| 功能             | 安装                   | 配置键       |
| ------------------- | ------------------------- | ---------------- |
| CSS 内联        | `pelago/emogrifier`       | `inline_css`     |
| 从 HTML 生成文本部分 | `league/html-to-markdown` | `text_from_html` |

### 将 CSS 内联到您的 HTML 电子邮件中

Gmail 和大多数网络邮件客户端会剥离 `<style>` 块 - 内联 `style=""` 属性是它们唯一可靠支持的样式。手动编写这些很痛苦；让 [Emogrifier](https://github.com/MyIntervals/emogrifier) 在发送时完成：

```bash
composer require pelago/emogrifier
```

```php
MailPlugin::install([
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'inline_css' => true,
]);
```

开启后，每个 HTML 正文在发送前都会内联其 CSS - 无论它来自模板还是 `->html()`。像 `<style>p { color: red; }</style><p>Hi</p>` 这样的消息会以 `<p style="color: red;">Hi</p>` 发出。

要将共享样式注入每封电子邮件（品牌颜色、重置）而无需在每个模板中重复它们，请直接传递规则或指向样式表文件：

```php
'inline_css' => ['css_file' => __DIR__ . '/mail-styles/base.css'],
// 或者
'inline_css' => ['css' => '.button { background: #0a84ff; color: #fff; }'],
```

按消息控制：

```php
$message->inlineCss();          // 强制对此消息进行内联
$message->withoutInlineCss();   // 即使全局启用也跳过它
```

### 从 HTML 生成文本部分

最佳实践是同时发送 HTML 和纯文本版本，但编写两者很繁琐。FlightMail 可以自动从最终 HTML 派生文本部分 - 基本转换不需要额外依赖，因为转换器随 Symfony Mime 一起提供：

```php
MailPlugin::install([
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'text_from_html' => true,       // 尽可能使用 Markdown，否则使用纯文本
]);
```

模式：

- `true` 或 `'auto'` - 如果安装了 `league/html-to-markdown`，则输出 Markdown，否则进行简单的标签剥离。
- `'markdown'` - 强制 Markdown（`composer require league/html-to-markdown`；标题变为 `==`，链接 `[text](url)`，粗体 `**bold**`）。
- `'plain'` - 始终剥离标签；无需任何额外包即可工作。

生成在渲染和 CSS 内联之后运行，并且仅在消息具有 HTML 正文但没有文本正文时运行 - 显式的 `->text()` 或 `->textTemplate()` 始终优先。按消息覆盖与内联类似：

```php
$message->textFromHtml('plain');    // 强制对此消息进行标签剥离
$message->withoutTextFromHtml();    // 仅 HTML 电子邮件
```

启用一个未安装其库的模式，您会得到一个清晰的错误，指出要运行的确切 `composer require` - 从不静默降级。

## 选择提供商

提供商通过 DSN 字符串插入。安装桥接包，将 DSN 粘贴到 `dsns` 中，完成。

| 提供商             | 安装                                      | DSN 示例                                  |
| -------------------- | -------------------------------------------- | -------------------------------------------- |
| SMTP                 | 内置                                     | `smtp://user:pass@host:587`                  |
| Sendmail             | 内置                                     | `sendmail://default`                         |
| Dev/null (丢弃邮件) | 内置                                     | `null://null`                                |
| Postmark             | `composer require symfony/postmark-mailer`   | `postmark+api://KEY@api.postmarkapp.com`     |
| Sendgrid             | `composer require symfony/sendgrid-mailer`   | `sendgrid+api://KEY@default`                 |
| Mailgun              | `composer require symfony/mailgun-mailer`    | `mailgun+https://KEY:DOMAIN@api.mailgun.net` |
| Amazon SES           | `composer require symfony/amazon-mailer`     | `ses+https://KEY:SECRET@default`             |
| Brevo                | `composer require symfony/brevo-mailer`      | `brevo+api://KEY@default`                    |
| MailerSend           | `composer require symfony/mailersend-mailer` | `mailersend+api://KEY@default`               |

完整列表位于 [Symfony Mailer 文档](https://symfony.com/doc/current/mailer.html) - 那里记录的任何内容在这里都可以不变地工作。

### 同时使用多个提供商

为每个传输命名，然后按消息选择：

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
// 没有 ->transport() 调用 = 使用 "dsns" 中的第一个键（此处为 "transactional"）。
Flight::mail()->compose()->to('...')->text('receipt')->send();

// 显式选择另一条路由。
Flight::mail()->compose()->to('...')->text('newsletter')->transport('bulk')->send();
```

## 配置参考

除了 `dsns` 之外，一切都是可选的。

```php
MailPlugin::install([
    // 必需 - 传输名称 => Symfony DSN。
    // 当消息未指定传输时，使用第一个条目。
    'dsns' => [
        'default' => 'smtp://user:pass@localhost:1025',
    ],

    // 当消息没有显式 ->transport() 时使用的传输，并且
    // 您不想要第一个键。必须存在于 "dsns" 中。
    'default_transport' => 'default',

    // 全局发件人。字符串、Symfony Address 或 ['email' => 'Name']。
    // 仅在消息未设置自己的 ->from() 时应用。
    'from' => ['no-reply@example.com' => 'My App'],

    // 默认模板引擎：'twig'、'latte' 或自定义名称。
    // 仅对扩展名不是已注册渲染器的模板进行查询。
    'renderer' => 'twig',

    // 模板所在位置，按顺序搜索；以及可选的缓存目录。
    'templates' => [
        'paths' => [__DIR__ . '/mail-templates'],
        'cache' => __DIR__ . '/cache/mail',
    ],

    // 直接传递给 Twig\Environment 的额外选项。
    'twig' => ['options' => ['strict_variables' => true]],

    // 在启动时调整 Latte 引擎：fn(Latte\Engine $engine): void。
    'latte' => ['setup' => static fn (Latte\Engine $e) => $e->addExtension(new MyExtension())],

    // 发送时正文增强功能（参见“样式化 HTML 和生成文本部分”）。
    'inline_css' => true,           // 或者 ['css' => '...', 'css_file' => '...']
    'text_from_html' => true,       // 或者 'plain' / 'markdown'

    // 自定义 DSN 方案、自定义渲染器、发送前钩子（见下文）。
    'transport_factories' => [],
    'renderers' => [],
    'hooks' => [],

    // 传递给每个传输的可选管道。
    'event_dispatcher' => $dispatcher,  // Symfony MessageEvents
    'logger' => $psr3Logger,
]);
```

## 更进一步

下面所有内容都是可选的。默认值覆盖大多数应用。

### 添加自定义 DSN 方案

实现 Symfony 的 `TransportFactoryInterface` 并注册它 - 然后您自己的方案就像内置方案一样工作：

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
        // ... 构建一个与您的运营商通信的传输
    }
}

$plugin = MailPlugin::install(['dsns' => ['carrier' => 'mycarrier://key']]);
$plugin->addTransportFactory(new MyCarrierFactory());
```

### 添加自定义模板渲染器

任何将模板名称加参数转换为字符串的东西都符合条件：

```php
use ryanstubbs\FlightMail\MailPlugin;
use ryanstubbs\FlightMail\Render\RendererInterface;

$plugin = MailPlugin::install($config);

$plugin->addRenderer('markdown', fn (array $config): RendererInterface =>
    new MarkdownMailRenderer($config['templates']['paths'] ?? [])
);
```

```php
// 以 .markdown 结尾的模板现在会自动使用它：
Flight::mail()->compose()->to('...')->template('welcome.markdown', ['name' => 'Ryan'])->send();
```

### 在发送前运行某些操作

钩子接收完成的消息 - 在渲染之后、默认值之后、上线之前：

```php
$plugin->addHook(function (ryanstubbs\FlightMail\Message $message): void {
    $message->getHeaders()->addTextHeader('X-Mailer', 'MyApp/1.0');
});
```

### 事件和日志记录

移交一个 Symfony 事件调度器和/或 PSR-3 日志记录器，每个传输都会使用它们：

```php
$plugin->eventDispatcher($dispatcher); // 在每次发送前接收 MessageEvent
$plugin->logger($logger);              // 传输级别日志
```

## API 速查表

```php
// 设置
MailPlugin::install($config)             // 在全局 Flight 应用上注册
MailPlugin::register($app, $config)      // 在特定 Engine 上注册
$mailer = Flight::mail();                // 共享的 Mailer 实例

// 构建消息
$mailer->compose(): Message
$message->to(...)->from(...)->subject(...)   // 标准 Symfony Mime 方法
$message->text(string)                       // 纯字符串正文
$message->html(string)                       // HTML 字符串正文
$message->template($name, $params)           // 来自模板的 HTML 正文
$message->htmlTemplate($name, $params)       // template() 的别名
$message->textTemplate($name, $params)       // 来自模板的文本正文
$message->inlineCss() / ->withoutInlineCss() // 按消息进行 CSS 内联
$message->textFromHtml($mode)                // 自动文本部分：true/'auto'/'plain'/'markdown'/false
$message->withoutTextFromHtml()              // 仅 HTML 电子邮件
$message->transport($name)                   // 通过命名 DSN 路由
$message->send(): ?SentMessage               // 渲染 + 发送

// 在邮件程序本身上
$mailer->send($message): ?SentMessage        // $message->send() 的显式替代
$mailer->render($template, $params): string  // 渲染而不发送
$mailer->addHook(callable): static           // fn(Message $message): void
$mailer->transports(): TransportManager      // get() / has() / names()
$mailer->renderers(): RendererFactory        // create() / has() / add()
```

由于 `Message` 扩展了 `Symfony\Component\Mime\Email`，您已经知道的每个 Symfony 方法 - `attach()`、`embed()`、`priority()`、`replyTo()` - 都可以开箱即用。

## 故障排除

**"No mail DSNs configured"**
您在注册插件之前调用了 `Flight::mail()`，或者配置数组中没有包含 `dsns`。此错误是故意的 - FlightMail 拒绝猜测您的邮件应该去哪里，而不是静默丢弃它。

**"Unknown mail template renderer ..."**
您使用了引擎未安装的模板。使用 `composer require twig/twig` 或 `composer require latte/latte` 修复，或者注册一个以扩展名命名的自定义渲染器。

**"Unknown mail transport ..."**
`->transport('name')`（或 `default_transport`）与 `dsns` 中的任何键都不匹配。检查拼写 - 错误列出了配置的名称。

**邮件未到达**
将 `dsns` 指向 `null://null` 以确认您的其余代码正常工作，然后切换回真实的 DSN。在 DDEV 中，使用 `smtp://127.0.0.1:1025` 并在端口 8025 的 Mailpit 中检查消息。

---

如需错误报告、拉取请求和完整源代码，请访问 [GitHub 仓库](https://github.com/ryanstubbs/flightmail)。