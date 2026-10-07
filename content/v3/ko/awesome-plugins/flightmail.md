# FlightMail

> **서드파티 플러그인** - [Ryan Stubbs](https://ryanstubbs.co.uk)가 유지 관리합니다 ([ryanstubbs/flightmail](https://github.com/ryanstubbs/flightmail), MIT 라이선스). Flight 핵심이 아니므로 문제는 [GitHub 저장소](https://github.com/ryanstubbs/flightmail/issues)에 보고해 주세요.

[ryanstubbs/flightmail](https://github.com/ryanstubbs/flightmail)을 사용하면 Flight 앱에서 골치 아프지 않게 이메일을 보낼 수 있습니다. **Symfony Mailer**(PHP에서 가장 검증된 메일 라이브러리)를 래핑하고 Flight의 일부처럼 느껴지게 만듭니다. 설치 한 줄, 전송을 위한 유창한 체인 한 줄:

```php
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('You did it!')
    ->text('Your first email is on its way.')
    ->send();
```

## 기능

- **모든 프로바이더, 각각 한 줄.** SMTP, Postmark, Sendgrid, Mailgun, Amazon SES, Brevo 등은 간단한 DSN 문자열로 작동합니다.
- **여러 프로바이더를 동시에 사용.** 트랜잭션 메일은 Postmark로, 뉴스레터는 자체 SMTP로 - 메시지마다 선택하세요.
- **원하면 템플릿.** Twig 또는 Latte로 본문 렌더링. 템플릿이 필요 없나요? 문자열만 전달하고 추가 설치할 필요 없습니다.
- **전송 시 폴리싱.** 선택적 CSS 인라인 및 HTML에서 자동으로 파생된 일반 텍스트 부분, 사용할 때만 설치하는 라이브러리로 구동됩니다.
- **지루할 정도로 견고함.** 지연 연결, 조용히 삼켜지는 대신 명확한 오류, 그리고 사용자 지정이 필요하면 모든 것을 교체할 수 있습니다.

## 요구 사항

| 항목           | 버전                                  |
| -------------- | -------------------------------------- |
| PHP            | 8.2 이상                               |
| Flight PHP     | core ^3.15                             |
| Symfony Mailer | ^7.2 또는 ^8.0 (자동 설치 됨)         |

## 설치

```bash
composer require ryanstubbs/flightmail
```

일반 텍스트 및 HTML 이메일 전송에는 이것이 전부입니다. 템플릿 렌더링은 선택 사항입니다. 사용할 경우에만 엔진을 추가하세요:

```bash
composer require twig/twig      # .twig 템플릿용
composer require latte/latte    # .latte 템플릿용
```

두 가지 추가 선택 라이브러리는 [아래](#styling-html-and-generating-text-parts)에서 다루는 전송 시 개선 기능을 지원합니다:

```bash
composer require pelago/emogrifier         # CSS 인라인용 ("inline_css")
composer require league/html-to-markdown   # Markdown 텍스트 부분용 ("text_from_html")
```

이들은 모두 함께 설치할 수 있습니다. FlightMail은 구성에 따라 올바른 것을 선택합니다.

## 첫 번째 이메일

부트스트랩(경로를 정의하는 곳)에 다음을 추가하세요:

```php
<?php
require 'vendor/autoload.php';

use ryanstubbs\FlightMail\MailPlugin;

// Tell FlightMail where to send mail from and through.
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

[Flight PHP 스켈레톤](https://github.com/flightphp/skeleton)을 사용하시나요? 대신 인스턴스 스타일로 `app/config/services.php`에 등록하세요:

```php
use ryanstubbs\FlightMail\MailPlugin;

MailPlugin::register($app, [
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'from' => 'no-reply@example.com',
]);
```

두 스타일 모두 동일한 메일러를 노출합니다: `Flight::mail()`과 `$app->mail()`은 서로 바꿔 사용할 수 있습니다.

> **로컬에서 테스트하시나요?** 프로젝트가 [DDEV](https://ddev.com)에서 실행되는 경우 DSN을 `smtp://127.0.0.1:1025`로 지정하고 `http://<project>.ddev.site:8025`에서 Mailpit으로 캡처된 모든 이메일을 읽으세요. 메시지가 컴퓨터 밖으로 나가지 않습니다.

## 이메일 보내기

### 일반 문자열 (템플릿 엔진 불필요)

`->text()` 및 `->html()`은 원시 문자열을 받으며 다른 설치가 필요 없습니다:

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

### Twig 템플릿

```php
// welcome.html.twig 내용: 안녕하세요 {{ name }}, 가입해 주셔서 감사합니다!
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.html.twig', ['name' => 'Ryan'])
    ->send();
```

### Latte 템플릿

같은 방식, `.latte` 확장자:

```php
// welcome.latte 내용: 안녕하세요 {$name}, 가입해 주셔서 감사합니다!
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.latte', ['name' => 'Ryan'])
    ->send();
```

### HTML + 일반 텍스트 함께

전달성 측면에서 모범 사례 - 메일 클라이언트에 두 버전을 모두 제공하세요:

```php
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.html.twig', ['name' => 'Ryan'])     // 리치 버전
    ->textTemplate('welcome.txt.twig', ['name' => 'Ryan'])  // 대체 버전
    ->send();
```

템플릿에 대해 알아 두면 좋은 몇 가지 사항:

- 전송 시점에 **지연** 렌더링됩니다 - 지금 작성하고 나중에 렌더링합니다.
- 엔진은 확장자로 선택됩니다: `.twig` → Twig, `.latte` → Latte, 그 외에는 구성된 기본값(`renderer` 옵션)이 사용됩니다.
- 명시적인 `->html()` 또는 `->text()` 본문은 항상 템플릿보다 우선하므로 기본 템플릿을 설정하고 메시지별로 재정의할 수 있습니다.

## HTML 스타일링 및 텍스트 부분 생성

전송 시 개선 기능 두 가지, 기본적으로 모두 꺼져 있으며 원할 때만 설치하는 라이브러리로 구동됩니다:

| 기능                           | 설치                         | 구성 키           |
| ----------------------------- | ---------------------------- | ---------------- |
| CSS 인라인                    | `pelago/emogrifier`          | `inline_css`     |
| HTML에서 텍스트 부분 생성     | `league/html-to-markdown`    | `text_from_html` |

### HTML 이메일에 CSS 인라인

Gmail과 대부분의 웹메일 클라이언트는 `<style>` 블록을 제거합니다. 인라인 `style=""` 속성만 안정적으로 인식하는 스타일링입니다. 손으로 작성하는 것은 고통스럽습니다. [Emogrifier](https://github.com/MyIntervals/emogrifier)가 전송 시 처리하도록 하세요:

```bash
composer require pelago/emogrifier
```

```php
MailPlugin::install([
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'inline_css' => true,
]);
```

이 기능을 켜면 모든 HTML 본문이 전송 직전에 CSS를 인라인으로 받습니다(템플릿 또는 `->html()`에서 온 경우 모두). `<style>p { color: red; }</style><p>Hi</p>`와 같은 메시지는 `<p style="color: red;">Hi</p>`로 나갑니다.

각 템플릿에 반복하지 않고 모든 이메일에 공유 스타일(브랜드 색상, 리셋)을 주입하려면 규칙을 직접 전달하거나 스타일시트 파일을 지정하세요:

```php
'inline_css' => ['css_file' => __DIR__ . '/mail-styles/base.css'],
// or
'inline_css' => ['css' => '.button { background: #0a84ff; color: #fff; }'],
```

메시지별 제어:

```php
$message->inlineCss();          // force inlining for this one message
$message->withoutInlineCss();   // skip it even when globally enabled
```

### HTML에서 텍스트 부분 생성

모범 사례는 HTML과 일반 텍스트 버전을 함께 보내는 것이지만, 둘 다 작성하는 것은 번거롭습니다. FlightMail은 최종 HTML에서 텍스트 부분을 자동으로 파생할 수 있습니다. 기본 변환에는 추가 의존성이 필요 없습니다. 변환기가 Symfony Mime에 포함되어 있기 때문입니다:

```php
MailPlugin::install([
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'text_from_html' => true,       // Markdown when possible, plain otherwise
]);
```

모드:

- `true` 또는 `'auto'` - `league/html-to-markdown`이 설치되어 있으면 Markdown 출력, 그렇지 않으면 간단한 태그 제거.
- `'markdown'` - Markdown 강제 (`composer require league/html-to-markdown`; 제목은 `==`가 되고 링크는 `[text](url)`, 굵게는 `**bold**`).
- `'plain'` - 항상 태그를 제거하며 추가 패키지 없이 작동합니다.

생성은 렌더링 및 CSS 인라인 후에, 그리고 메시지에 HTML 본문은 있지만 텍스트 본문이 없는 경우에만 실행됩니다. 명시적인 `->text()` 또는 `->textTemplate()`이 항상 우선합니다. 메시지별 재정의는 인라인과 동일합니다:

```php
$message->textFromHtml('plain');    // force tag-stripping for this one
$message->withoutTextFromHtml();    // HTML-only email
```

설치되지 않은 라이브러리의 모드를 활성화하면 실행해야 할 정확한 `composer require`를 알려주는 명확한 오류가 표시됩니다. 조용히 성능이 저하되지 않습니다.

## 프로바이더 선택

프로바이더는 DSN 문자열을 통해 연결됩니다. 브리지 패키지를 설치하고 DSN을 `dsns`에 붙여넣으면 끝입니다.

| 프로바이더            | 설치                                         | DSN 예시                                    |
| --------------------- | -------------------------------------------- | -------------------------------------------- |
| SMTP                  | 내장                                         | `smtp://user:pass@host:587`                  |
| Sendmail              | 내장                                         | `sendmail://default`                         |
| Dev/null (메일 삭제)  | 내장                                         | `null://null`                                |
| Postmark              | `composer require symfony/postmark-mailer`   | `postmark+api://KEY@api.postmarkapp.com`     |
| Sendgrid              | `composer require symfony/sendgrid-mailer`   | `sendgrid+api://KEY@default`                 |
| Mailgun               | `composer require symfony/mailgun-mailer`    | `mailgun+https://KEY:DOMAIN@api.mailgun.net` |
| Amazon SES            | `composer require symfony/amazon-mailer`     | `ses+https://KEY:SECRET@default`             |
| Brevo                 | `composer require symfony/brevo-mailer`      | `brevo+api://KEY@default`                    |
| MailerSend            | `composer require symfony/mailersend-mailer` | `mailersend+api://KEY@default`               |

전체 목록은 [Symfony Mailer 문서](https://symfony.com/doc/current/mailer.html)에 있습니다. 거기에 문서화된 것은 모두 변경 없이 여기서 작동합니다.

### 여러 프로바이더 동시에

각 전송(transport)에 이름을 지정한 다음 메시지별로 선택하세요:

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
// ->transport() 호출이 없으면 "dsns"의 첫 번째 키가 사용됩니다 (여기서는 "transactional").
Flight::mail()->compose()->to('...')->text('receipt')->send();

// 명시적으로 다른 경로를 선택합니다.
Flight::mail()->compose()->to('...')->text('newsletter')->transport('bulk')->send();
```

## 구성 참조

`dsns`를 제외한 모든 것은 선택 사항입니다.

```php
MailPlugin::install([
    // REQUIRED - transport name => Symfony DSN.
    // The first entry is used when a message doesn't name one.
    'dsns' => [
        'default' => 'smtp://user:pass@localhost:1025',
    ],

    // Transport used when a message has no explicit ->transport() and
    // you don't want the first key. Must exist in "dsns".
    'default_transport' => 'default',

    // Global sender. String, Symfony Address, or ['email' => 'Name'].
    // Applied only when a message doesn't set its own ->from().
    'from' => ['no-reply@example.com' => 'My App'],

    // Default template engine: 'twig', 'latte', or a custom name.
    // Only consulted for templates whose extension isn't a registered renderer.
    'renderer' => 'twig',

    // Where templates live, searched in order; plus an optional cache dir.
    'templates' => [
        'paths' => [__DIR__ . '/mail-templates'],
        'cache' => __DIR__ . '/cache/mail',
    ],

    // Extra options passed straight to Twig\Environment.
    'twig' => ['options' => ['strict_variables' => true]],

    // Tweak the Latte engine at boot: fn(Latte\Engine $engine): void.
    'latte' => ['setup' => static fn (Latte\Engine $e) => $e->addExtension(new MyExtension())],

    // Send-time body enhancements (see "Styling HTML and generating text parts").
    'inline_css' => true,           // or ['css' => '...', 'css_file' => '...']
    'text_from_html' => true,       // or 'plain' / 'markdown'

    // Custom DSN schemes, custom renderers, pre-send hooks (see below).
    'transport_factories' => [],
    'renderers' => [],
    'hooks' => [],

    // Optional plumbing handed to every transport.
    'event_dispatcher' => $dispatcher,  // Symfony MessageEvents
    'logger' => $psr3Logger,
]);
```

## 더 나아가기

아래의 모든 것은 선택 사항입니다. 기본값은 대부분의 앱에 충분합니다.

### 사용자 지정 DSN 스킴 추가

Symfony의 `TransportFactoryInterface`를 구현하고 등록하면 내장된 것과 똑같이 작동하는 사용자 지정 스킴을 사용할 수 있습니다:

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
        // ... build a transport that talks to your carrier
    }
}

$plugin = MailPlugin::install(['dsns' => ['carrier' => 'mycarrier://key']]);
$plugin->addTransportFactory(new MyCarrierFactory());
```

### 사용자 지정 템플릿 렌더러 추가

템플릿 이름과 매개변수를 문자열로 바꾸는 모든 것이 적합합니다:

```php
use ryanstubbs\FlightMail\MailPlugin;
use ryanstubbs\FlightMail\Render\RendererInterface;

$plugin = MailPlugin::install($config);

$plugin->addRenderer('markdown', fn (array $config): RendererInterface =>
    new MarkdownMailRenderer($config['templates']['paths'] ?? [])
);
```

```php
// .markdown으로 끝나는 템플릿은 이제 자동으로 이를 사용합니다:
Flight::mail()->compose()->to('...')->template('welcome.markdown', ['name' => 'Ryan'])->send();
```

### 전송 직전에 무언가 실행

훅은 완성된 메시지를 받습니다 - 렌더링 후, 기본값 적용 후, 전송 직전입니다:

```php
$plugin->addHook(function (ryanstubbs\FlightMail\Message $message): void {
    $message->getHeaders()->addTextHeader('X-Mailer', 'MyApp/1.0');
});
```

### 이벤트 및 로깅

Symfony 이벤트 디스패처 및/또는 PSR-3 로거를 넘겨주면 모든 전송이 이를 사용합니다:

```php
$plugin->eventDispatcher($dispatcher); // 각 전송 전에 MessageEvent를 수신합니다
$plugin->logger($logger);              // 전송 수준 로그
```

## API 치트 시트

```php
// 설정
MailPlugin::install($config)             // 전역 Flight 앱에 등록
MailPlugin::register($app, $config)      // 특정 Engine에 등록
$mailer = Flight::mail();                // 공유 Mailer 인스턴스

// 메시지 작성
$mailer->compose(): Message
$message->to(...)->from(...)->subject(...)   // 표준 Symfony Mime 메서드
$message->text(string)                       // 일반 문자열 본문
$message->html(string)                       // HTML 문자열 본문
$message->template($name, $params)           // 템플릿의 HTML 본문
$message->htmlTemplate($name, $params)       // template()의 별칭
$message->textTemplate($name, $params)       // 템플릿의 텍스트 본문
$message->inlineCss() / ->withoutInlineCss() // 메시지별 CSS 인라인
$message->textFromHtml($mode)                // 자동 텍스트 부분: true/'auto'/'plain'/'markdown'/false
$message->withoutTextFromHtml()              // HTML 전용 이메일
$message->transport($name)                   // 이름이 지정된 DSN을 통한 라우팅
$message->send(): ?SentMessage               // 렌더링 + 전송

// 메일러 자체에 대해
$mailer->send($message): ?SentMessage        // $message->send()의 명시적 대안
$mailer->render($template, $params): string  // 전송하지 않고 렌더링
$mailer->addHook(callable): static           // fn(Message $message): void
$mailer->transports(): TransportManager      // get() / has() / names()
$mailer->renderers(): RendererFactory        // create() / has() / add()
```

`Message`가 `Symfony\Component\Mime\Email`을 확장하므로 이미 알고 있는 모든 Symfony 메서드(`attach()`, `embed()`, `priority()`, `replyTo()`)가 별도 설정 없이 작동합니다.

## 문제 해결

**"No mail DSNs configured"**
플러그인을 등록하기 전에 `Flight::mail()`을 호출했거나 구성 배열에 `dsns`가 포함되지 않았습니다. 이 오류는 의도적입니다. FlightMail은 메일을 조용히 버리는 대신 어디로 보낼지 추측하지 않습니다.

**"Unknown mail template renderer ..."**
엔진이 설치되지 않은 템플릿을 사용했습니다. `composer require twig/twig` 또는 `composer require latte/latte`로 해결하거나 확장자 이름을 딴 사용자 지정 렌더러를 등록하세요.

**"Unknown mail transport ..."**
`->transport('name')`(또는 `default_transport`)이 `dsns`의 어떤 키와도 일치하지 않습니다. 철자를 확인하세요. 오류가 구성된 이름을 나열합니다.

**메일이 도착하지 않습니다**
`dsns`를 `null://null`로 지정하여 나머지 코드가 작동하는지 확인한 다음 실제 DSN으로 다시 전환하세요. DDEV에서는 `smtp://127.0.0.1:1025`를 사용하고 포트 8025의 Mailpit에서 메시지를 확인하세요.

---

버그 보고, 풀 리퀘스트 및 전체 소스는 [GitHub 저장소](https://github.com/ryanstubbs/flightmail)를 방문하세요.