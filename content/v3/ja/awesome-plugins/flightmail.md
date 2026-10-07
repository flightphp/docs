# FlightMail

> **サードパーティプラグイン** - [Ryan Stubbs](https://ryanstubbs.co.uk) によってメンテナンスされています ([ryanstubbs/flightmail](https://github.com/ryanstubbs/flightmail)、MITライセンス)。Flight コアの一部ではありません - 問題は[GitHubリポジトリ](https://github.com/ryanstubbs/flightmail/issues)で報告してください。

[ryanstubbs/flightmail](https://github.com/ryanstubbs/flightmail) を使うと、Flight アプリからメールを悩みなく送信できます。これは、PHP で最も実績のあるメールライブラリである **Symfony Mailer** をラップし、あたかも Flight の一部であるかのように感じさせます。インストールは1行、送信は1つのフルーエントチェーンで完結します。

```php
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('You did it!')
    ->text('Your first email is on its way.')
    ->send();
```

## 特徴

- **プロバイダは何でも、それぞれ1行で。** SMTP、Postmark、Sendgrid、Mailgun、Amazon SES、Brevo などはすべて単純な DSN 文字列で動作します。
- **複数のプロバイダを同時に使用。** トランザクションメールは Postmark で、ニュースレターは独自の SMTP で - メッセージごとに選択できます。
- **必要ならテンプレート。** Twig や Latte で本文をレンダリング。テンプレートは不要? 文字列を渡すだけで、追加インストールは不要です。
- **送信時の仕上げ。** オプションの CSS インライン化と、HTML から自動生成されるプレーンテキスト部分。使用する場合にのみインストールするライブラリによって実現されます。
- **最高の意味で退屈。** 遅延接続、静かにメールを飲み込むのではなく明確なエラー、カスタムが必要な場合はすべて交換可能。

## 要件

| 項目           | バージョン                                |
| -------------- | -------------------------------------- |
| PHP            | 8.2 以降                           |
| Flight PHP     | コア ^3.15                             |
| Symfony Mailer | ^7.2 または ^8.0 (自動インストールされます) |

## インストール

```bash
composer require ryanstubbs/flightmail
```

プレーンテキストメールと HTML メールを送信するだけならこれで完了です。テンプレートレンダリングはオプトインです - 使用する場合にのみエンジンを追加してください:

```bash
composer require twig/twig      # .twig テンプレート用
composer require latte/latte    # .latte テンプレート用
```

さらに2つのオプションライブラリが、[後述](#styling-html-and-generating-text-parts)の送信時拡張を支えます:

```bash
composer require pelago/emogrifier         # CSS インライン化用 ("inline_css")
composer require league/html-to-markdown   # Markdown テキスト部分用 ("text_from_html")
```

これらはすべて並行してインストールできます。FlightMail は設定に基づいて適切なものを選択します。

## 最初のメール

このコードをブートストラップ（ルートを定義する場所と同じ場所）に追加してください:

```php
<?php
require 'vendor/autoload.php';

use ryanstubbs\FlightMail\MailPlugin;

// FlightMail にメールの送信元と送信手段を指示します。
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

[Flight PHP スケルトン](https://github.com/flightphp/skeleton) を使用していますか? 代わりにインスタンススタイルで `app/config/services.php` に登録してください:

```php
use ryanstubbs\FlightMail\MailPlugin;

MailPlugin::register($app, [
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'from' => 'no-reply@example.com',
]);
```

どちらのスタイルでも同じメーラーが公開されます: `Flight::mail()` と `$app->mail()` は相互に利用可能です。

> **ローカルでテストする場合?** プロジェクトが [DDEV](https://ddev.com) で動いているなら、DSN を `smtp://127.0.0.1:1025` に設定し、Mailpit で `http://<project>.ddev.site:8025` からすべてのキャプチャされたメールを読めます。あなたのマシンの外に出ることはありません。

## メールを送信する

### プレーン文字列（テンプレートエンジン不要）

`->text()` と `->html()` は生の文字列を受け取り、他に何もインストールする必要はありません:

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

### Twig テンプレート

```php
// welcome.html.twig の内容: こんにちは {{ name }}、登録ありがとうございます!
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.html.twig', ['name' => 'Ryan'])
    ->send();
```

### Latte テンプレート

同じ考え方で、拡張子は `.latte`:

```php
// welcome.latte の内容: こんにちは {$name}、登録ありがとうございます!
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.latte', ['name' => 'Ryan'])
    ->send();
```

### HTML とプレーンテキストを一緒に

配信性のベストプラクティス - メールクライアントに両方のバージョンを提供します:

```php
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.html.twig', ['name' => 'Ryan'])     // リッチ版
    ->textTemplate('welcome.txt.twig', ['name' => 'Ryan'])  // フォールバック版
    ->send();
```

テンプレートについて知っておくべき点がいくつかあります:

- テンプレートは**遅延**レンダリングされます - 送信時にレンダリングされます。今コンポーズし、後でレンダリングします。
- エンジンは拡張子で選択されます: `.twig` → Twig、`.latte` → Latte、その他 → 設定されたデフォルト（`renderer` オプション）。
- 明示的な `->html()` または `->text()` 本文は常にテンプレートよりも優先されるため、デフォルトのテンプレートを設定し、メッセージごとに上書きできます。

## HTML のスタイリングとテキスト部分の生成

2つのオプションの送信時拡張機能。どちらもデフォルトではオフで、必要に応じてインストールするライブラリによって動作します:

| 機能             | インストール                   | 設定キー       |
| ------------------- | ------------------------- | ---------------- |
| CSS インライン化        | `pelago/emogrifier`       | `inline_css`     |
| HTML からのテキスト部分 | `league/html-to-markdown` | `text_from_html` |

### HTML メールに CSS をインライン化する

Gmail やほとんどのウェブメールクライアントは `<style>` ブロックを削除します - インラインの `style=""` 属性だけが確実に尊重されるスタイリングです。それを手書きするのは苦痛です。[Emogrifier](https://github.com/MyIntervals/emogrifier) に送信時にやってもらいましょう:

```bash
composer require pelago/emogrifier
```

```php
MailPlugin::install([
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'inline_css' => true,
]);
```

これを有効にすると、テンプレート由来か `->html()` 由来かを問わず、すべての HTML 本文の CSS が送信直前にインライン化されます。`<style>p { color: red; }</style><p>Hi</p>` のようなメッセージは `<p style="color: red;">Hi</p>` として送信されます。

すべてのメールに共有スタイル（ブランドカラー、リセット）を各テンプレートで繰り返さずに注入するには、ルールを直接渡すか、スタイルシートファイルを指定します:

```php
'inline_css' => ['css_file' => __DIR__ . '/mail-styles/base.css'],
// または
'inline_css' => ['css' => '.button { background: #0a84ff; color: #fff; }'],
```

メッセージごとの制御:

```php
$message->inlineCss();          // このメッセージだけインライン化を強制
$message->withoutInlineCss();   // グローバルで有効でもスキップ
```

### HTML からテキスト部分を生成する

ベストプラクティスは HTML とプレーンテキストの両方を一緒に送ることですが、両方を書くのは面倒です。FlightMail は最終的な HTML からテキスト部分を自動的に導出できます - コンバータは Symfony Mime に同梱されているため、基本変換には追加の依存関係は不要です:

```php
MailPlugin::install([
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'text_from_html' => true,       // Markdown が利用可能なら Markdown、それ以外はプレーン
]);
```

モード:

- `true` または `'auto'` - `league/html-to-markdown` がインストールされていれば Markdown 出力、それ以外は単純なタグ除去。
- `'markdown'` - Markdown を強制（`composer require league/html-to-markdown`; 見出しは `==`、リンクは `[text](url)`、太字は `**bold**`）。
- `'plain'` - 常にタグを除去。追加パッケージは不要。

生成はレンダリングと CSS インライン化の後に実行され、メッセージに HTML 本文がありテキスト本文がない場合にのみ実行されます - 明示的な `->text()` または `->textTemplate()` が常に優先されます。メッセージごとの上書きはインライン化と同様です:

```php
$message->textFromHtml('plain');    // このメッセージだけタグ除去を強制
$message->withoutTextFromHtml();    // HTML のみのメール
```

ライブラリがインストールされていないモードを有効にすると、実行すべき正確な `composer require` を指定する明確なエラーが表示されます - 静かに劣化することはありません。

## プロバイダの選択

プロバイダは DSN 文字列で接続します。ブリッジパッケージをインストールし、`dsns` に DSN を貼り付けるだけです。

| プロバイダ             | インストール                                      | DSN 例                                  |
| -------------------- | -------------------------------------------- | -------------------------------------------- |
| SMTP                 | 組み込み                                     | `smtp://user:pass@host:587`                  |
| Sendmail             | 組み込み                                     | `sendmail://default`                         |
| Dev/null（メール破棄） | 組み込み                                     | `null://null`                                |
| Postmark             | `composer require symfony/postmark-mailer`   | `postmark+api://KEY@api.postmarkapp.com`     |
| Sendgrid             | `composer require symfony/sendgrid-mailer`   | `sendgrid+api://KEY@default`                 |
| Mailgun              | `composer require symfony/mailgun-mailer`    | `mailgun+https://KEY:DOMAIN@api.mailgun.net` |
| Amazon SES           | `composer require symfony/amazon-mailer`     | `ses+https://KEY:SECRET@default`             |
| Brevo                | `composer require symfony/brevo-mailer`      | `brevo+api://KEY@default`                    |
| MailerSend           | `composer require symfony/mailersend-mailer` | `mailersend+api://KEY@default`               |

完全なリストは [Symfony Mailer ドキュメント](https://symfony.com/doc/current/mailer.html) にあります - そこに記載されているものはすべてそのままここでも動作します。

### 複数のプロバイダを同時に使う

各トランスポートに名前を付け、メッセージごとに選択します:

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
// ->transport() を呼ばない場合 = "dsns" の最初のキー（ここでは "transactional"）。
Flight::mail()->compose()->to('...')->text('receipt')->send();

// 明示的に別の経路を選択します。
Flight::mail()->compose()->to('...')->text('newsletter')->transport('bulk')->send();
```

## 設定リファレンス

`dsns` 以外はすべてオプションです。

```php
MailPlugin::install([
    // 必須 - トランスポート名 => Symfony DSN。
    // メッセージが名前を指定しない場合、最初のエントリが使用されます。
    'dsns' => [
        'default' => 'smtp://user:pass@localhost:1025',
    ],

    // メッセージに明示的な ->transport() がなく、最初のキーを使いたくない場合に使用されるトランスポート。
    // "dsns" に存在する必要があります。
    'default_transport' => 'default',

    // グローバル送信者。文字列、Symfony Address、または ['email' => 'Name']。
    // メッセージが独自の ->from() を設定していない場合にのみ適用されます。
    'from' => ['no-reply@example.com' => 'My App'],

    // デフォルトのテンプレートエンジン: 'twig'、'latte'、またはカスタム名。
    // 拡張子が登録済みレンダラーではないテンプレートに対してのみ参照されます。
    'renderer' => 'twig',

    // テンプレートの場所。順に検索され、オプションのキャッシュディレクトリも指定できます。
    'templates' => [
        'paths' => [__DIR__ . '/mail-templates'],
        'cache' => __DIR__ . '/cache/mail',
    ],

    // Twig\Environment にそのまま渡される追加オプション。
    'twig' => ['options' => ['strict_variables' => true]],

    // 起動時に Latte エンジンを調整: fn(Latte\Engine $engine): void。
    'latte' => ['setup' => static fn (Latte\Engine $e) => $e->addExtension(new MyExtension())],

    // 送信時本文拡張（「HTML のスタイリングとテキスト部分の生成」を参照）。
    'inline_css' => true,           // または ['css' => '...', 'css_file' => '...']
    'text_from_html' => true,       // または 'plain' / 'markdown'

    // カスタム DSN スキーム、カスタムレンダラー、送信前フック（後述）。
    'transport_factories' => [],
    'renderers' => [],
    'hooks' => [],

    // すべてのトランスポートに渡されるオプションの配管。
    'event_dispatcher' => $dispatcher,  // Symfony MessageEvents
    'logger' => $psr3Logger,
]);
```

## さらに進む

以下はすべてオプションです。デフォルトでほとんどのアプリをカバーできます。

### カスタム DSN スキームを追加する

Symfony の `TransportFactoryInterface` を実装して登録すると、独自のスキームが組み込みのものとまったく同じように動作します:

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
        // ... あなたのキャリアと通信するトランスポートを構築します
    }
}

$plugin = MailPlugin::install(['dsns' => ['carrier' => 'mycarrier://key']]);
$plugin->addTransportFactory(new MyCarrierFactory());
```

### カスタムテンプレートレンダラーを追加する

テンプレート名とパラメータを文字列に変換するものなら何でも該当します:

```php
use ryanstubbs\FlightMail\MailPlugin;
use ryanstubbs\FlightMail\Render\RendererInterface;

$plugin = MailPlugin::install($config);

$plugin->addRenderer('markdown', fn (array $config): RendererInterface =>
    new MarkdownMailRenderer($config['templates']['paths'] ?? [])
);
```

```php
// .markdown で終わるテンプレートは自動的にこれを使用します:
Flight::mail()->compose()->to('...')->template('welcome.markdown', ['name' => 'Ryan'])->send();
```

### 送信直前に何かを実行する

フックは、レンダリング後、デフォルト適用後、実際の送信直前に、完成したメッセージを受け取ります:

```php
$plugin->addHook(function (ryanstubbs\FlightMail\Message $message): void {
    $message->getHeaders()->addTextHeader('X-Mailer', 'MyApp/1.0');
});
```

### イベントとロギング

Symfony のイベントディスパッチャーや PSR-3 ロガーを渡すと、すべてのトランスポートがそれらを使用します:

```php
$plugin->eventDispatcher($dispatcher); // 各送信前に MessageEvent を受け取ります
$plugin->logger($logger);              // トランスポートレベルのログ
```

## API チートシート

```php
// セットアップ
MailPlugin::install($config)             // グローバルな Flight アプリに登録
MailPlugin::register($app, $config)      // 特定の Engine に登録
$mailer = Flight::mail();                // 共有 Mailer インスタンス

// メッセージの構築
$mailer->compose(): Message
$message->to(...)->from(...)->subject(...)   // 標準の Symfony Mime メソッド
$message->text(string)                       // プレーン文字列の本文
$message->html(string)                       // HTML 文字列の本文
$message->template($name, $params)           // テンプレートからの HTML 本文
$message->htmlTemplate($name, $params)       // template() のエイリアス
$message->textTemplate($name, $params)       // テンプレートからのテキスト本文
$message->inlineCss() / ->withoutInlineCss() // メッセージごとの CSS インライン化
$message->textFromHtml($mode)                // 自動テキスト部分: true/'auto'/'plain'/'markdown'/false
$message->withoutTextFromHtml()              // HTML のみのメール
$message->transport($name)                   // 名前付き DSN によるルーティング
$message->send(): ?SentMessage               // レンダリング + 送信

// メーラー自体に関する操作
$mailer->send($message): ?SentMessage        // $message->send() の明示的な代替
$mailer->render($template, $params): string  // 送信せずにレンダリング
$mailer->addHook(callable): static           // fn(Message $message): void
$mailer->transports(): TransportManager      // get() / has() / names()
$mailer->renderers(): RendererFactory        // create() / has() / add()
```

`Message` は `Symfony\Component\Mime\Email` を継承しているため、`attach()`、`embed()`、`priority()`、`replyTo()` など、すでに知っている Symfony のメソッドはすべてそのまま動作します。

## トラブルシューティング

**"No mail DSNs configured"**
プラグインを登録する前に `Flight::mail()` を呼び出したか、設定配列に `dsns` が含まれていません。このエラーは意図的なものです - FlightMail はメールを静かに捨てるのではなく、送信先を推測することを拒否します。

**"Unknown mail template renderer ..."**
エンジンがインストールされていないテンプレートを使用しました。`composer require twig/twig` または `composer require latte/latte` で修正するか、拡張子にちなんだカスタムレンダラーを登録してください。

**"Unknown mail transport ..."**
`->transport('name')`（または `default_transport`）が `dsns` 内のどのキーとも一致しません。スペルを確認してください - エラーには設定済みの名前が一覧表示されます。

**Mail isn't arriving**
`dsns` を `null://null` に設定して、残りのコードが機能することを確認し、実際の DSN に戻してください。DDEV では `smtp://127.0.0.1:1025` を使用し、Mailpit のポート 8025 でメッセージを確認してください。

---

バグ報告、プルリクエスト、完全なソースコードについては、[GitHub リポジトリ](https://github.com/ryanstubbs/flightmail) を参照してください。