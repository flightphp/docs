# FlightMail

> **Plugin pihak ketiga** - dirawat oleh [Ryan Stubbs](https://ryanstubbs.co.uk) ([ryanstubbs/flightmail](https://github.com/ryanstubbs/flightmail), dilisensikan MIT). Bukan bagian dari inti Flight - harap laporkan masalah di [repositori GitHub-nya](https://github.com/ryanstubbs/flightmail/issues).

[ryanstubbs/flightmail](https://github.com/ryanstubbs/flightmail) memungkinkan Anda mengirim email dari aplikasi Flight tanpa pusing. Ia membungkus **Symfony Mailer** - pustaka email paling teruji di PHP - dan membuatnya terasa seperti bagian dari Flight. Satu baris untuk menginstal, satu rantai fluen untuk mengirim:

```php
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('You did it!')
    ->text('Your first email is on its way.')
    ->send();
```

## Fitur

- **Penyedia apa pun, satu baris saja.** SMTP, Postmark, Sendgrid, Mailgun, Amazon SES, Brevo, dan kawan-kawannya bekerja melalui string DSN sederhana.
- **Gunakan beberapa penyedia sekaligus.** Email transaksional melalui Postmark, buletin melalui SMTP Anda sendiri - pilih per pesan.
- **Templat jika Anda menginginkannya.** Render badan dengan Twig atau Latte. Tidak ingin templat? Cukup berikan string dan jangan menginstal apa pun ekstra.
- **Penyempurnaan saat pengiriman.** Inlining CSS opsional dan bagian teks otomatis yang diturunkan dari HTML, didukung oleh pustaka yang hanya Anda instal jika menggunakannya.
- **Membosankan dengan cara terbaik.** Koneksi lazy, error yang jelas alih-alih email ditelan diam-diam, dan semuanya bisa ditukar jika Anda membutuhkan sesuatu yang khusus.

## Persyaratan

| Apa                            | Versi                        |
| ------------------------------ | ---------------------------- |
| PHP                            | 8.2 atau lebih baru          |
| Flight PHP                     | inti ^3.15                   |
| Symfony Mailer                 | ^7.2 atau ^8.0 (terinstal otomatis) |

## Instalasi

```bash
composer require ryanstubbs/flightmail
```

Itu saja untuk mengirim email teks biasa dan HTML. Render templat bersifat opsional - tambahkan mesin hanya jika Anda akan menggunakannya:

```bash
composer require twig/twig      # untuk templat .twig
composer require latte/latte    # untuk templat .latte
```

Dua pustaka opsional lagi mendukung penyempurnaan saat pengiriman yang dibahas [di bawah](#styling-html-and-generating-text-parts):

```bash
composer require pelago/emogrifier         # untuk inlining CSS ("inline_css")
composer require league/html-to-markdown   # untuk bagian teks Markdown ("text_from_html")
```

Semua ini dapat diinstal berdampingan; FlightMail memilih yang tepat berdasarkan apa yang Anda konfigurasikan.

## Email pertama Anda

Tambahkan ini ke bootstrap Anda (tempat yang sama Anda mendefinisikan rute):

```php
<?php
require 'vendor/autoload.php';

use ryanstubbs\FlightMail\MailPlugin;

// Beri tahu FlightMail ke mana dan melalui apa email dikirim.
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

Menggunakan [kerangka Flight PHP](https://github.com/flightphp/skeleton)? Daftarkan di `app/config/services.php` dengan gaya instance sebagai gantinya:

```php
use ryanstubbs\FlightMail\MailPlugin;

MailPlugin::register($app, [
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'from' => 'no-reply@example.com',
]);
```

Kedua gaya mengekspos mailer yang sama: `Flight::mail()` dan `$app->mail()` dapat saling dipertukarkan.

> **Menguji secara lokal?** Jika proyek Anda berjalan di [DDEV](https://ddev.com), arahkan DSN ke `smtp://127.0.0.1:1025` dan baca setiap email yang ditangkap di Mailpit di `http://<project>.ddev.site:8025`. Tidak ada yang meninggalkan mesin Anda.

## Mengirim email

### String polos (tanpa mesin templat)

`->text()` dan `->html()` menerima string mentah dan tidak memerlukan instalasi tambahan apa pun:

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

### Templat Twig

```php
// welcome.html.twig berisi: Halo {{ name }}, terima kasih telah mendaftar!
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.html.twig', ['name' => 'Ryan'])
    ->send();
```

### Templat Latte

Ide yang sama, ekstensi `.latte`:

```php
// welcome.latte berisi: Halo {$name}, terima kasih telah mendaftar!
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.latte', ['name' => 'Ryan'])
    ->send();
```

### HTML + teks biasa bersamaan

Praktik terbaik untuk keterkiriman - berikan klien email kedua versi:

```php
Flight::mail()->compose()
    ->to('someone@example.com')
    ->subject('Welcome!')
    ->template('welcome.html.twig', ['name' => 'Ryan'])     // versi kaya
    ->textTemplate('welcome.txt.twig', ['name' => 'Ryan'])  // versi cadangan
    ->send();
```

Beberapa hal yang perlu diketahui tentang templat:

- Templat dirender secara **lazy**, saat pengiriman - buat sekarang, render nanti.
- Mesin dipilih berdasarkan ekstensi: `.twig` → Twig, `.latte` → Latte, lainnya → default yang Anda konfigurasikan (opsi `renderer`).
- Badan `->html()` atau `->text()` eksplisit selalu menang atas templat, jadi Anda dapat mengatur templat default dan menimpanya per pesan.

## Menata HTML dan menghasilkan bagian teks

Dua penyempurnaan saat pengiriman opsional, keduanya nonaktif secara default, dan keduanya didukung oleh pustaka yang hanya Anda instal jika menginginkannya:

| Fitur                  | Instal                      | Kunci konfigurasi |
| ---------------------- | --------------------------- | ----------------- |
| Inlining CSS           | `pelago/emogrifier`         | `inline_css`      |
| Bagian teks dari HTML  | `league/html-to-markdown`   | `text_from_html`  |

### Inline CSS ke dalam email HTML Anda

Gmail dan sebagian besar klien webmail menghapus blok `<style>` - atribut `style=""` inline adalah satu-satunya gaya yang mereka hormati dengan andal. Menulisnya secara manual sangat menyebalkan; biarkan [Emogrifier](https://github.com/MyIntervals/emogrifier) melakukannya saat pengiriman:

```bash
composer require pelago/emogrifier
```

```php
MailPlugin::install([
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'inline_css' => true,
]);
```

Dengan itu aktif, setiap badan HTML mendapatkan inlining CSS tepat sebelum dikirim - baik dari templat maupun `->html()`. Pesan seperti `<style>p { color: red; }</style><p>Hi</p>` akan terkirim sebagai `<p style="color: red;">Hi</p>`.

Untuk menyuntikkan gaya bersama ke setiap email (warna merek, reset) tanpa mengulanginya di setiap templat, berikan aturan secara langsung atau arahkan ke file stylesheet:

```php
'inline_css' => ['css_file' => __DIR__ . '/mail-styles/base.css'],
// atau
'inline_css' => ['css' => '.button { background: #0a84ff; color: #fff; }'],
```

Kontrol per pesan:

```php
$message->inlineCss();          // paksa inlining untuk pesan ini
$message->withoutInlineCss();   // lewati bahkan ketika diaktifkan secara global
```

### Hasilkan bagian teks dari HTML Anda

Praktik terbaik adalah mengirim versi HTML dan teks biasa bersamaan, tetapi menulis keduanya membosankan. FlightMail dapat menurunkan bagian teks dari HTML akhir secara otomatis - konversi dasar tidak memerlukan dependensi tambahan, karena konverter sudah disertakan dengan Symfony Mime:

```php
MailPlugin::install([
    'dsns' => ['default' => 'smtp://user:pass@localhost:1025'],
    'text_from_html' => true,       // Markdown jika memungkinkan, polos jika tidak
]);
```

Mode:

- `true` atau `'auto'` - output Markdown jika `league/html-to-markdown` terinstal, jika tidak, penghapusan tag sederhana.
- `'markdown'` - paksa Markdown (`composer require league/html-to-markdown`; heading menjadi `==`, tautan `[text](url)`, tebal `**bold**`).
- `'plain'` - selalu hapus tag; bekerja tanpa paket tambahan.

Generasi berjalan setelah rendering dan inlining CSS, dan hanya ketika pesan memiliki badan HTML tetapi tidak ada badan teks - `->text()` atau `->textTemplate()` eksplisit selalu menang. Penimpaan per pesan mencerminkan inlining:

```php
$message->textFromHtml('plain');    // paksa penghapusan tag untuk yang satu ini
$message->withoutTextFromHtml();    // email HTML saja
```

Jika Anda mengaktifkan mode yang pustakanya tidak terinstal, Anda akan mendapatkan error yang jelas menyebutkan `composer require` yang tepat untuk dijalankan - tidak pernah penurunan diam-diam.

## Memilih penyedia

Penyedia terhubung melalui string DSN. Instal paket bridge, tempel DSN ke `dsns`, selesai.

| Penyedia              | Instal                                        | Contoh DSN                                  |
| --------------------- | --------------------------------------------- | -------------------------------------------- |
| SMTP                  | bawaan                                        | `smtp://user:pass@host:587`                  |
| Sendmail              | bawaan                                        | `sendmail://default`                         |
| Dev/null (buang email)| bawaan                                        | `null://null`                                |
| Postmark              | `composer require symfony/postmark-mailer`    | `postmark+api://KEY@api.postmarkapp.com`     |
| Sendgrid              | `composer require symfony/sendgrid-mailer`    | `sendgrid+api://KEY@default`                 |
| Mailgun               | `composer require symfony/mailgun-mailer`     | `mailgun+https://KEY:DOMAIN@api.mailgun.net` |
| Amazon SES            | `composer require symfony/amazon-mailer`      | `ses+https://KEY:SECRET@default`             |
| Brevo                 | `composer require symfony/brevo-mailer`       | `brevo+api://KEY@default`                    |
| MailerSend            | `composer require symfony/mailersend-mailer`  | `mailersend+api://KEY@default`               |

Daftar lengkap ada di [dokumentasi Symfony Mailer](https://symfony.com/doc/current/mailer.html) - apa pun yang didokumentasikan di sana berfungsi di sini tanpa perubahan.

### Beberapa penyedia sekaligus

Beri nama setiap transport, lalu pilih per pesan:

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
// Tidak ada panggilan ->transport() = kunci pertama di "dsns" ("transactional" di sini).
Flight::mail()->compose()->to('...')->text('receipt')->send();

// Pilih rute lain secara eksplisit.
Flight::mail()->compose()->to('...')->text('newsletter')->transport('bulk')->send();
```

## Referensi konfigurasi

Semuanya opsional kecuali `dsns`.

```php
MailPlugin::install([
    // WAJIB - nama transport => DSN Symfony.
    // Entri pertama digunakan ketika pesan tidak menyebutkan satu pun.
    'dsns' => [
        'default' => 'smtp://user:pass@localhost:1025',
    ],

    // Transport yang digunakan ketika pesan tidak memiliki ->transport() eksplisit dan
    // Anda tidak menginginkan kunci pertama. Harus ada di "dsns".
    'default_transport' => 'default',

    // Pengirim global. String, Symfony Address, atau ['email' => 'Nama'].
    // Diterapkan hanya ketika pesan tidak mengatur ->from() sendiri.
    'from' => ['no-reply@example.com' => 'My App'],

    // Mesin templat default: 'twig', 'latte', atau nama kustom.
    // Hanya digunakan untuk templat yang ekstensinya bukan renderer terdaftar.
    'renderer' => 'twig',

    // Di mana templat berada, dicari secara berurutan; plus direktori cache opsional.
    'templates' => [
        'paths' => [__DIR__ . '/mail-templates'],
        'cache' => __DIR__ . '/cache/mail',
    ],

    // Opsi tambahan yang diteruskan langsung ke Twig\Environment.
    'twig' => ['options' => ['strict_variables' => true]],

    // Sesuaikan mesin Latte saat boot: fn(Latte\Engine $engine): void.
    'latte' => ['setup' => static fn (Latte\Engine $e) => $e->addExtension(new MyExtension())],

    // Peningkatan badan saat pengiriman (lihat "Menata HTML dan menghasilkan bagian teks").
    'inline_css' => true,           // atau ['css' => '...', 'css_file' => '...']
    'text_from_html' => true,       // atau 'plain' / 'markdown'

    // Skema DSN kustom, renderer kustom, hook sebelum kirim (lihat di bawah).
    'transport_factories' => [],
    'renderers' => [],
    'hooks' => [],

    // Perlengkapan opsional yang diberikan ke setiap transport.
    'event_dispatcher' => $dispatcher,  // Symfony MessageEvents
    'logger' => $psr3Logger,
]);
```

## Lebih lanjut

Semua di bawah ini opsional. Defaultnya mencakup sebagian besar aplikasi.

### Menambahkan skema DSN kustom

Implementasikan `TransportFactoryInterface` milik Symfony dan daftarkan - maka skema Anda sendiri bekerja persis seperti yang bawaan:

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
        // ... buat transport yang berkomunikasi dengan penyedia Anda
    }
}

$plugin = MailPlugin::install(['dsns' => ['carrier' => 'mycarrier://key']]);
$plugin->addTransportFactory(new MyCarrierFactory());
```

### Menambahkan renderer templat kustom

Apa pun yang mengubah nama templat plus parameter menjadi string memenuhi syarat:

```php
use ryanstubbs\FlightMail\MailPlugin;
use ryanstubbs\FlightMail\Render\RendererInterface;

$plugin = MailPlugin::install($config);

$plugin->addRenderer('markdown', fn (array $config): RendererInterface =>
    new MarkdownMailRenderer($config['templates']['paths'] ?? [])
);
```

```php
// Templat yang berakhiran .markdown sekarang menggunakannya secara otomatis:
Flight::mail()->compose()->to('...')->template('welcome.markdown', ['name' => 'Ryan'])->send();
```

### Menjalankan sesuatu tepat sebelum mengirim

Hook menerima pesan yang sudah selesai - setelah rendering, setelah default, tepat sebelum dikirim:

```php
$plugin->addHook(function (ryanstubbs\FlightMail\Message $message): void {
    $message->getHeaders()->addTextHeader('X-Mailer', 'MyApp/1.0');
});
```

### Event dan logging

Serahkan event dispatcher Symfony dan/atau logger PSR-3 dan setiap transport akan menggunakannya:

```php
$plugin->eventDispatcher($dispatcher); // menerima MessageEvent sebelum setiap pengiriman
$plugin->logger($logger);              // log tingkat transport
```

## Lembar contekan API

```php
// Penyiapan
MailPlugin::install($config)             // daftarkan pada aplikasi Flight global
MailPlugin::register($app, $config)      // daftarkan pada Engine tertentu
$mailer = Flight::mail();                // instance Mailer bersama

// Membuat pesan
$mailer->compose(): Message
$message->to(...)->from(...)->subject(...)   // metode Symfony Mime standar
$message->text(string)                       // badan string polos
$message->html(string)                       // badan string HTML
$message->template($name, $params)           // badan HTML dari templat
$message->htmlTemplate($name, $params)       // alias dari template()
$message->textTemplate($name, $params)       // badan teks dari templat
$message->inlineCss() / ->withoutInlineCss() // inlining CSS per pesan
$message->textFromHtml($mode)                // bagian teks otomatis: true/'auto'/'plain'/'markdown'/false
$message->withoutTextFromHtml()              // email HTML saja
$message->transport($name)                   // rute melalui DSN bernama
$message->send(): ?SentMessage               // render + kirim

// Pada mailer itu sendiri
$mailer->send($message): ?SentMessage        // alternatif eksplisit untuk $message->send()
$mailer->render($template, $params): string  // render tanpa mengirim
$mailer->addHook(callable): static           // fn(Message $message): void
$mailer->transports(): TransportManager      // get() / has() / names()
$mailer->renderers(): RendererFactory        // create() / has() / add()
```

Karena `Message` memperluas `Symfony\Component\Mime\Email`, setiap metode Symfony yang sudah Anda kenal - `attach()`, `embed()`, `priority()`, `replyTo()` - berfungsi langsung.

## Pemecahan masalah

**"No mail DSNs configured"**
Anda memanggil `Flight::mail()` sebelum mendaftarkan plugin, atau array konfigurasi tidak menyertakan `dsns`. Error ini disengaja - FlightMail menolak menebak ke mana email Anda harus pergi daripada diam-diam membuangnya.

**"Unknown mail template renderer ..."**
Anda menggunakan templat yang mesinnya tidak terinstal. Perbaiki dengan `composer require twig/twig` atau `composer require latte/latte`, atau daftarkan renderer kustom yang dinamai sesuai ekstensi.

**"Unknown mail transport ..."**
`->transport('name')` (atau `default_transport`) tidak cocok dengan kunci apa pun di `dsns`. Periksa ejaannya - error mencantumkan nama yang dikonfigurasi.

**Email tidak kunjung sampai**
Arahkan `dsns` ke `null://null` untuk memastikan kode Anda yang lain berfungsi, lalu kembali ke DSN asli. Di DDEV, gunakan `smtp://127.0.0.1:1025` dan periksa pesan di Mailpit pada port 8025.

---

Untuk laporan bug, pull request, dan sumber lengkap, kunjungi [repositori GitHub](https://github.com/ryanstubbs/flightmail).