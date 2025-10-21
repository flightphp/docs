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
    // Tempat file template disimpan
    $engine->setTemplatesPath(__DIR__ . '/views');
    
    // Tempat aset publik akan disajikan
    $engine->setPublicPath(__DIR__ . '/public');
    
    // Tempat aset yang dikompilasi akan disimpan
    $engine->setAssetPath('assets');
    
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

#### Perintah Variabel
```html
{$content|striptag|trim|escape}      <!-- Rantai beberapa filter -->
```

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