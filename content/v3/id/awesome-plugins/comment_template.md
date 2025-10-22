# CommentTemplate

[CommentTemplate](https://github.com/KnifeLemon/CommentTemplate) adalah mesin template PHP yang kuat dengan kompilasi aset, pewarisan template, dan pemrosesan variabel. Ini menyediakan cara sederhana namun fleksibel untuk mengelola template dengan minifikasi CSS/JS bawaan dan caching.

## Fitur

- **Pewarisan Template**: Gunakan layout dan sertakan template lain
- **Kompilasi Aset**: Minifikasi CSS/JS otomatis dan caching
- **Pemrosesan Variabel**: Variabel template dengan filter dan perintah
- **Encoding Base64**: Aset inline sebagai data URI
- **Integrasi Framework Flight**: Integrasi opsional dengan framework PHP Flight

## Instalasi

Instal dengan composer.

```bash
composer require knifelemon/comment-template
```

## Konfigurasi Dasar

Ada beberapa opsi konfigurasi dasar untuk memulai. Anda dapat membaca lebih lanjut tentangnya di [CommentTemplate Repo](https://github.com/KnifeLemon/CommentTemplate).

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
    __DIR__ . '/public',    // publicPath - tempat aset akan disajikan
    __DIR__ . '/views',     // skinPath - tempat file template disimpan  
    'assets',               // assetPath - tempat aset yang dikompilasi akan disimpan
    '.php'                  // fileExtension - ekstensi file template
]);

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

### Konfigurasi Path

CommentTemplate mendukung path relatif dan absolut dengan resolusi cerdas:

#### Path Relatif
```php
<?php
$engine = new Engine();

// Semua path relatif terhadap directPath publik
$engine->setPublicPath(__DIR__ . '/public');
$engine->setSkinPath('templates');        // Akan di-resolve ke {publicPath}/templates
$engine->setAssetPath('compiled');        // Akan di-resolve ke {publicPath}/compiled

// Juga dapat menggunakan subdirektori
$engine->setSkinPath('views/templates');  
$engine->setAssetPath('assets/compiled'); 
```

#### Path Absolut
```php
<?php
$engine = new Engine();

// Path absolut lengkap
$engine->setSkinPath('/var/www/templates');
$engine->setAssetPath('/var/www/public/assets');

// Path Windows
$engine->setSkinPath('C:\xampp\htdocs\templates');
$engine->setAssetPath('C:\xampp\htdocs\public\assets');

// Path UNC (Windows network)
$engine->setSkinPath('\\server\share\templates');
$engine->setAssetPath('\\server\share\public\assets');
```

#### Path Campuran
```php
<?php
$engine = new Engine();
$engine->setPublicPath('/var/www/public');

// Campurkan path relatif dan absolut berdasarkan kebutuhan
$engine->setSkinPath('views');                    // Relatif: /var/www/public/views
$engine->setAssetPath('/tmp/compiled-assets');    // Absolut: /tmp/compiled-assets
```

#### Tips Praktis

**Pengembangan Lokal:**
```php
$engine->setPublicPath(__DIR__ . '/public');
$engine->setSkinPath('views');           // Mudah untuk pengembangan
$engine->setAssetPath('assets');         // Aset terorganisir dalam struktur proyek
```

**Produksi:**
```php
$engine->setPublicPath('/var/www/myapp/public');
$engine->setSkinPath('/var/www/myapp/templates');    // Path absolut untuk kontrol penuh
$engine->setAssetPath('/var/www/myapp/public/dist'); // Aset di direktori yang dapat diakses web
```

**Docker/Container:**
```php
$engine->setPublicPath('/app/public');
$engine->setSkinPath('/app/resources/views');
$engine->setAssetPath('/app/public/assets');
```

CommentTemplate secara otomatis mendeteksi jenis path dan menanganinya dengan benar, memberikan fleksibilitas maksimum untuk setup deployment yang berbeda.

## Direktif Template

### Pewarisan Layout

Gunakan layout untuk membuat struktur umum:

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

### Pengelolaan Aset

#### File CSS
```html
<!--@css(/css/styles.css)-->          <!-- Diminifikasi dan di-cache -->
<!--@cssSingle(/css/critical.css)-->  <!-- File tunggal, tidak diminifikasi -->
```

#### File JavaScript
CommentTemplate mendukung strategi pemuatan JavaScript yang berbeda:

```html
<!--@js(/js/script.js)-->             <!-- Diminifikasi, dimuat di bagian bawah -->
<!--@jsAsync(/js/analytics.js)-->     <!-- Diminifikasi, dimuat di bagian bawah dengan async -->
<!--@jsDefer(/js/utils.js)-->         <!-- Diminifikasi, dimuat di bagian bawah dengan defer -->
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
/* Di file CSS Anda */
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
/* Di file JS Anda */
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

<!-- Salin seluruh direktori (fonts, icons, dll.) -->
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
{$html|striptag}                     <!-- Hilangkan tag HTML -->
{$text|escape}                       <!-- Escape HTML -->
{$multiline|nl2br}                   <!-- Ubah baris baru menjadi <br> -->
{$html|br2nl}                        <!-- Ubah tag <br> menjadi baris baru -->
{$description|trim}                  <!-- Hilangkan spasi -->
{$subject|title}                     <!-- Ubah ke title case -->
```

#### Perintah Variabel
```html
{$title|default=Default Title}       <!-- Tetapkan nilai default -->
{$name|concat= (Admin)}              <!-- Gabungkan teks -->
```

#### Rantai Beberapa Filter
```html
{$content|striptag|trim|escape}      <!-- Rantai beberapa filter -->
```

### Komentar

Komentar template sepenuhnya dihapus dari output dan tidak muncul di HTML final:

```html
{* Ini adalah komentar template satu baris *}

{* 
   Ini adalah komentar template
   multi-baris yang mencakup
   beberapa baris
*}

<h1>{$title}</h1>
{* Komentar debugging: memeriksa apakah variabel title berfungsi *}
<p>{$content}</p>
```

**Catatan**: Komentar template `{* ... *}` berbeda dari komentar HTML `<!-- ... -->`. Komentar template dihapus selama pemrosesan dan tidak pernah sampai ke browser.

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