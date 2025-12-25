# CommentTemplate

[CommentTemplate](https://github.com/KnifeLemon/CommentTemplate) adalah mesin template PHP yang kuat dengan kompilasi aset, pewarisan template, dan pemrosesan variabel. Ini menyediakan cara sederhana namun fleksibel untuk mengelola template dengan minifikasi CSS/JS dan caching bawaan.

## Fitur

- **Pewarisan Template**: Gunakan tata letak dan sertakan template lain
- **Kompilasi Aset**: Minifikasi dan caching CSS/JS otomatis
- **Pemrosesan Variabel**: Variabel template dengan filter dan perintah
- **Encoding Base64**: Aset inline sebagai data URI
- **Integrasi Framework Flight**: Integrasi opsional dengan framework PHP Flight

## Instalasi

Instal dengan composer.

```bash
composer require knifelemon/comment-template
```

## Konfigurasi Dasar

Ada beberapa opsi konfigurasi dasar untuk memulai. Anda dapat membaca lebih lanjut tentangnya di [Repo CommentTemplate](https://github.com/KnifeLemon/CommentTemplate).

### Metode 1: Menggunakan Fungsi Callback

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

$app->register('view', Engine::class, [], function (Engine $engine) use ($app) {
    // Direktori root (tempat index.php berada) - root dokumen aplikasi web Anda
    $engine->setPublicPath(__DIR__);
    
    // Direktori file template - mendukung path relatif dan absolut
    $engine->setSkinPath('views');             // Relatif terhadap path publik
    
    // Tempat aset yang dikompilasi akan disimpan - mendukung path relatif dan absolut
    $engine->setAssetPath('assets');           // Relatif terhadap path publik
    
    // Ekstensi file template
    $engine->setFileExtension('.php');
});

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

### Metode 2: Menggunakan Parameter Konstruktor

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

// __construct(string $publicPath = "", string $skinPath = "", string $assetPath = "", string $fileExtension = "")
$app->register('view', Engine::class, [
    __DIR__,                // publicPath - direktori root (tempat index.php berada)
    'views',                // skinPath - path template (mendukung relatif/absolut)
    'assets',               // assetPath - path aset yang dikompilasi (mendukung relatif/absolut)
    '.php'                  // fileExtension - ekstensi file template
]);

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

## Konfigurasi Path

CommentTemplate menyediakan penanganan path yang cerdas untuk path relatif dan absolut:

### Path Publik

**Path Publik** adalah direktori root aplikasi web Anda, biasanya tempat `index.php` berada. Ini adalah root dokumen yang disajikan oleh server web.

```php
// Contoh: jika index.php Anda berada di /var/www/html/myapp/index.php
$template->setPublicPath('/var/www/html/myapp');  // Direktori root

// Contoh Windows: jika index.php Anda berada di C:\xampp\htdocs\myapp\index.php
$template->setPublicPath('C:\\xampp\\htdocs\\myapp');
```

### Konfigurasi Path Template

Path template mendukung path relatif dan absolut:

```php
$template = new Engine();
$template->setPublicPath('/var/www/html/myapp');  // Direktori root (tempat index.php berada)

// Path relatif - otomatis digabungkan dengan path publik
$template->setSkinPath('views');           // → /var/www/html/myapp/views/
$template->setSkinPath('templates/pages'); // → /var/www/html/myapp/templates/pages/

// Path absolut - digunakan apa adanya (Unix/Linux)
$template->setSkinPath('/var/www/templates');      // → /var/www/templates/
$template->setSkinPath('/full/path/to/templates'); // → /full/path/to/templates/

// Path absolut Windows
$template->setSkinPath('C:\\www\\templates');     // → C:\www\templates\
$template->setSkinPath('D:/projects/templates');  // → D:/projects/templates/

// Path UNC (share jaringan Windows)
$template->setSkinPath('\\\\server\\share\\templates'); // → \\server\share\templates\
```

### Konfigurasi Path Aset

Path aset juga mendukung path relatif dan absolut:

```php
// Path relatif - otomatis digabungkan dengan path publik
$template->setAssetPath('assets');        // → /var/www/html/myapp/assets/
$template->setAssetPath('static/files');  // → /var/www/html/myapp/static/files/

// Path absolut - digunakan apa adanya (Unix/Linux)
$template->setAssetPath('/var/www/cdn');           // → /var/www/cdn/
$template->setAssetPath('/full/path/to/assets');   // → /full/path/to/assets/

// Path absolut Windows
$template->setAssetPath('C:\\www\\static');       // → C:\www\static\
$template->setAssetPath('D:/projects/assets');    // → D:/projects/assets/

// Path UNC (share jaringan Windows)
$template->setAssetPath('\\\\server\\share\\assets'); // → \\server\share\assets\
```

**Deteksi Path Cerdas:**

- **Path Relatif**: Tidak ada pemisah awal (`/`, `\`) atau huruf drive
- **Absolut Unix**: Dimulai dengan `/` (misalnya, `/var/www/assets`)
- **Absolut Windows**: Dimulai dengan huruf drive (misalnya, `C:\www`, `D:/assets`)
- **Path UNC**: Dimulai dengan `\\` (misalnya, `\\server\share`)

**Cara Kerjanya:**

- Semua path otomatis diselesaikan berdasarkan tipe (relatif vs absolut)
- Path relatif digabungkan dengan path publik
- `@css` dan `@js` membuat file yang diminifikasi di: `{resolvedAssetPath}/css/` atau `{resolvedAssetPath}/js/`
- `@asset` menyalin file tunggal ke: `{resolvedAssetPath}/{relativePath}`
- `@assetDir` menyalin direktori ke: `{resolvedAssetPath}/{relativePath}`
- Caching cerdas: file hanya disalin ketika sumber lebih baru daripada tujuan

## Integrasi Tracy Debugger

CommentTemplate menyertakan integrasi dengan [Tracy Debugger](https://tracy.nette.org/) untuk logging dan debugging pengembangan.

![Comment Template Tracy](https://raw.githubusercontent.com/KnifeLemon/CommentTemplate/refs/heads/master/tracy.jpeg)

### Instalasi

```bash
composer require tracy/tracy
```

### Penggunaan

```php
<?php
use KnifeLemon\CommentTemplate\Engine;
use Tracy\Debugger;

// Aktifkan Tracy (harus dipanggil sebelum output apa pun)
Debugger::enable(Debugger::DEVELOPMENT);
Flight::set('flight.content_length', false);

// Override template
$app->register('view', Engine::class, [], function (Engine $builder) use ($app) {
    $builder->setPublicPath($app->get('flight.views.topPath'));
    $builder->setAssetPath($app->get('flight.views.assetPath'));
    $builder->setSkinPath($app->get('flight.views.path'));
    $builder->setFileExtension($app->get('flight.views.extension'));
});
$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});

$app->start();
```

### Fitur Panel Debug

CommentTemplate menambahkan panel kustom ke debug bar Tracy dengan empat tab:

- **Overview**: Konfigurasi, metrik kinerja, dan penghitungan
- **Assets**: Detail kompilasi CSS/JS dengan rasio kompresi
- **Variables**: Nilai asli dan yang ditransformasi dengan filter yang diterapkan
- **Timeline**: Tampilan kronologis dari semua operasi template

### Apa yang Dicatat

- Rendering template (mulai/selesai, durasi, layout, impor)
- Kompilasi aset (file CSS/JS, ukuran, rasio kompresi)
- Pemrosesan variabel (nilai asli/yang ditransformasi, filter)
- Operasi aset (encoding base64, penyalinan file)
- Metrik kinerja (durasi, penggunaan memori)

**Catatan:** Tidak ada dampak kinerja ketika Tracy tidak diinstal atau dinonaktifkan.

Lihat [contoh lengkap yang berfungsi dengan Flight PHP](https://github.com/KnifeLemon/CommentTemplate/tree/master/examples/flightphp).

## Direktif Template

### Pewarisan Tata Letak

Gunakan tata letak untuk membuat struktur umum:

**layout/global_layout.php**:
```html
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
</head>
<body>
    <!--@contents-->
</body>
</html>
```

**view/page.php**:
```html
<!--@layout(layout/global_layout)-->
<h1>{$title}</h1>
<p>{$content}</p>
```

### Manajemen Aset

#### File CSS
```html
<!--@css(/css/styles.css)-->          <!-- Diminifikasi dan di-cache -->
<!--@cssSingle(/css/critical.css)-->  <!-- File tunggal, tidak diminifikasi -->
```

#### File JavaScript
CommentTemplate mendukung strategi pemuatan JavaScript yang berbeda:

```html
<!--@js(/js/script.js)-->             <!-- Diminifikasi, dimuat di bawah -->
<!--@jsAsync(/js/analytics.js)-->     <!-- Diminifikasi, dimuat di bawah dengan async -->
<!--@jsDefer(/js/utils.js)-->         <!-- Diminifikasi, dimuat di bawah dengan defer -->
<!--@jsTop(/js/critical.js)-->        <!-- Diminifikasi, dimuat di head -->
<!--@jsTopAsync(/js/tracking.js)-->   <!-- Diminifikasi, dimuat di head dengan async -->
<!--@jsTopDefer(/js/polyfill.js)-->   <!-- Diminifikasi, dimuat di head dengan defer -->
<!--@jsSingle(/js/widget.js)-->       <!-- File tunggal, tidak diminifikasi -->
<!--@jsSingleAsync(/js/ads.js)-->     <!-- File tunggal, tidak diminifikasi, async -->
<!--@jsSingleDefer(/js/social.js)-->  <!-- File tunggal, tidak diminifikasi, defer -->
```

#### Direktif Aset dalam File CSS/JS

CommentTemplate juga memproses direktif aset dalam file CSS dan JavaScript selama kompilasi:

**Contoh CSS:**
```css
/* Dalam file CSS Anda */
@font-face {
    font-family: 'CustomFont';
    src: url('<!--@asset(fonts/custom.woff2)-->') format('woff2');
}

.background-image {
    background: url('<!--@asset(images/bg.jpg)-->');
}

.inline-icon {
    background: url('<!--@base64(icons/star.svg)-->');
}
```

**Contoh JavaScript:**
```javascript
/* Dalam file JS Anda */
const fontUrl = '<!--@asset(fonts/custom.woff2)-->';
const imageData = '<!--@base64(images/icon.png)-->';
```

#### Encoding Base64
```html
<!--@base64(images/logo.png)-->       <!-- Inline sebagai data URI -->
```
** Contoh: **
```html
<!-- Inline gambar kecil sebagai data URI untuk pemuatan lebih cepat -->
<img src="<!--@base64(images/logo.png)-->" alt="Logo">
<div style="background-image: url('<!--@base64(icons/star.svg)-->');">
    Ikon kecil sebagai latar belakang
</div>
```

#### Penyalinan Aset
```html
<!--@asset(images/photo.jpg)-->       <!-- Salin aset tunggal ke direktori publik -->
<!--@assetDir(assets)-->              <!-- Salin seluruh direktori ke direktori publik -->
```
** Contoh: **
```html
<!-- Salin dan rujuk aset statis -->
<img src="<!--@asset(images/hero-banner.jpg)-->" alt="Hero Banner">
<a href="<!--@asset(documents/brochure.pdf)-->" download>Unduh Brosur</a>

<!-- Salin seluruh direktori (font, ikon, dll.) -->
<!--@assetDir(assets/fonts)-->
<!--@assetDir(assets/icons)-->
```

### Penyertaan Template
```html
<!--@import(components/header)-->     <!-- Sertakan template lain -->
```
** Contoh: **
```html
<!-- Sertakan komponen yang dapat digunakan kembali -->
<!--@import(components/header)-->

<main>
    <h1>Selamat datang di situs web kami</h1>
    <!--@import(components/sidebar)-->
    
    <div class="content">
        <p>Konten utama di sini...</p>
    </div>
</main>

<!--@import(components/footer)-->
```

### Pemrosesan Variabel

#### Variabel Dasar
```html
<h1>{$title}</h1>
<p>{$description}</p>
```

#### Filter Variabel
```html
{$title|upper}                       <!-- Ubah ke huruf besar -->
{$content|lower}                     <!-- Ubah ke huruf kecil -->
{$html|striptag}                     <!-- Hapus tag HTML -->
{$text|escape}                       <!-- Escape HTML -->
{$multiline|nl2br}                   <!-- Ubah baris baru menjadi <br> -->
{$html|br2nl}                        <!-- Ubah tag <br> menjadi baris baru -->
{$description|trim}                  <!-- Potong spasi -->
{$subject|title}                     <!-- Ubah ke title case -->
```

#### Perintah Variabel
```html
{$title|default=Default Title}       <!-- Atur nilai default -->
{$name|concat= (Admin)}              <!-- Gabungkan teks -->
```

#### Perintah Variabel
```html
{$content|striptag|trim|escape}      <!-- Rantai beberapa filter -->
```

### Komentar

Komentar template sepenuhnya dihapus dari output dan tidak akan muncul di HTML akhir:

```html
{* Ini adalah komentar template satu baris *}

{* 
   Ini adalah komentar 
   template multi-baris 
   yang meliputi beberapa baris
*}

<h1>{$title}</h1>
{* Komentar debug: memeriksa apakah variabel title berfungsi *}
<p>{$content}</p>
```

**Catatan**: Komentar template `{* ... *}` berbeda dari komentar HTML `<!-- ... -->`. Komentar template dihapus selama pemrosesan dan tidak pernah mencapai browser.

## Struktur Proyek Contoh

```
project/
├── source/
│   ├── layouts/
│   │   └── default.php
│   ├── components/
│   │   ├── header.php
│   │   └── footer.php
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   └── custom.css
│   ├── js/
│   │   ├── app.js
│   │   └── bootstrap.min.js
│   └── homepage.php
├── public/
│   └── assets/           # Aset yang dihasilkan
│       ├── css/
│       └── js/
└── vendor/
```