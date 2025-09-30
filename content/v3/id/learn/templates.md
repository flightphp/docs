# Tampilan HTML dan Template

## Gambaran Umum

Flight menyediakan fungsionalitas templating HTML dasar secara default. Templating adalah cara yang sangat efektif bagi Anda untuk memisahkan logika aplikasi dari lapisan presentasi Anda.

## Pemahaman

Ketika Anda membangun aplikasi, kemungkinan besar Anda akan memiliki HTML yang ingin dikirimkan kembali ke pengguna akhir. PHP dengan sendirinya adalah bahasa templating, tetapi _sangat_ mudah untuk membungkus logika bisnis seperti panggilan database, panggilan API, dll ke dalam file HTML Anda dan membuat pengujian serta pemisahan menjadi proses yang sangat sulit. Dengan mendorong data ke dalam template dan membiarkan template merender dirinya sendiri, menjadi jauh lebih mudah untuk memisahkan dan menguji unit kode Anda. Anda akan berterima kasih kepada kami jika Anda menggunakan template!

## Penggunaan Dasar

Flight memungkinkan Anda untuk menukar engine tampilan default hanya dengan mendaftarkan kelas tampilan Anda sendiri. Gulir ke bawah untuk melihat contoh cara menggunakan Smarty, Latte, Blade, dan lainnya!

### Latte

<span class="badge bg-info">direkomendasikan</span>

Berikut adalah cara Anda menggunakan engine template [Latte](https://latte.nette.org/)
untuk tampilan Anda.

#### Instalasi

```bash
composer require latte/latte
```

#### Konfigurasi Dasar

Ide utamanya adalah Anda menimpa metode `render` untuk menggunakan Latte alih-alih renderer PHP default.

```php
// overwrite the render method to use latte instead of the default PHP renderer
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Where latte specifically stores its cache
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### Menggunakan Latte di Flight

Sekarang setelah Anda dapat merender dengan Latte, Anda dapat melakukan sesuatu seperti ini:

```html
<!-- app/views/home.latte -->
<html>
  <head>
	<title>{$title ? $title . ' - '}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, {$name}!</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.latte', [
		'title' => 'Home Page',
		'name' => $name
	]);
});
```

Ketika Anda mengunjungi `/Bob` di browser Anda, outputnya akan menjadi:

```html
<html>
  <head>
	<title>Home Page - My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, Bob!</h1>
  </body>
</html>
```

#### Bacaan Lebih Lanjut

Contoh yang lebih kompleks tentang penggunaan Latte dengan tata letak ditunjukkan di bagian [awesome plugins](/awesome-plugins/latte) dari dokumentasi ini.

Anda dapat mempelajari lebih lanjut tentang kemampuan penuh Latte termasuk terjemahan dan kemampuan bahasa dengan membaca [dokumentasi resmi](https://latte.nette.org/en/).

### Engine Tampilan Built-in

<span class="badge bg-warning">deprecated</span>

> **Catatan:** Meskipun ini masih fungsionalitas default dan secara teknis masih berfungsi.

Untuk menampilkan template tampilan, panggil metode `render` dengan nama 
file template dan data template opsional:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Data template yang Anda berikan secara otomatis disuntikkan ke dalam template dan dapat
dirujuk seperti variabel lokal. File template hanyalah file PHP. Jika
isi file template `hello.php` adalah:

```php
Hello, <?= $name ?>!
```

Outputnya akan menjadi:

```text
Hello, Bob!
```

Anda juga dapat mengatur variabel tampilan secara manual dengan menggunakan metode set:

```php
Flight::view()->set('name', 'Bob');
```

Variabel `name` sekarang tersedia di seluruh tampilan Anda. Jadi Anda dapat dengan mudah melakukan:

```php
Flight::render('hello');
```

Perhatikan bahwa ketika menentukan nama template di metode render, Anda dapat
meninggalkan ekstensi `.php`.

Secara default Flight akan mencari direktori `views` untuk file template. Anda dapat
mengatur jalur alternatif untuk template Anda dengan mengatur konfigurasi berikut:

```php
Flight::set('flight.views.path', '/path/to/views');
```

#### Tata Letak

Umum bagi situs web untuk memiliki satu file template tata letak dengan konten
yang saling berganti. Untuk merender konten yang akan digunakan dalam tata letak, Anda dapat memberikan parameter opsional ke metode `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Tampilan Anda kemudian akan memiliki variabel yang disimpan bernama `headerContent` dan `bodyContent`.
Anda kemudian dapat merender tata letak Anda dengan melakukan:

```php
Flight::render('layout', ['title' => 'Home Page']);
```

Jika file template terlihat seperti ini:

`header.php`:

```php
<h1><?= $heading ?></h1>
```

`body.php`:

```php
<div><?= $body ?></div>
```

`layout.php`:

```php
<html>
  <head>
    <title><?= $title ?></title>
  </head>
  <body>
    <?= $headerContent ?>
    <?= $bodyContent ?>
  </body>
</html>
```

Outputnya akan menjadi:
```html
<html>
  <head>
    <title>Home Page</title>
  </head>
  <body>
    <h1>Hello</h1>
    <div>World</div>
  </body>
</html>
```

### Smarty

Berikut adalah cara Anda menggunakan engine template [Smarty](http://www.smarty.net/)
untuk tampilan Anda:

```php
// Load Smarty library
require './Smarty/libs/Smarty.class.php';

// Register Smarty as the view class
// Also pass a callback function to configure Smarty on load
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Assign template data
Flight::view()->assign('name', 'Bob');

// Display the template
Flight::view()->display('hello.tpl');
```

Untuk kelengkapan, Anda juga harus menimpa metode render default Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

Berikut adalah cara Anda menggunakan engine template [Blade](https://laravel.com/docs/8.x/blade) untuk tampilan Anda:

Pertama, Anda perlu menginstal pustaka BladeOne melalui Composer:

```bash
composer require eftec/bladeone
```

Kemudian, Anda dapat mengonfigurasi BladeOne sebagai kelas tampilan di Flight:

```php
<?php
// Load BladeOne library
use eftec\bladeone\BladeOne;

// Register BladeOne as the view class
// Also pass a callback function to configure BladeOne on load
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Assign template data
Flight::view()->share('name', 'Bob');

// Display the template
echo Flight::view()->run('hello', []);
```

Untuk kelengkapan, Anda juga harus menimpa metode render default Flight:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

Dalam contoh ini, file template hello.blade.php mungkin terlihat seperti ini:

```php
<?php
Hello, {{ $name }}!
```

Outputnya akan menjadi:

```
Hello, Bob!
```

## Lihat Juga
- [Extending](/learn/extending) - Cara menimpa metode `render` untuk menggunakan engine template yang berbeda.
- [Routing](/learn/routing) - Cara memetakan rute ke controller dan merender tampilan.
- [Responses](/learn/responses) - Cara menyesuaikan respons HTTP.
- [Why a Framework?](/learn/why-frameworks) - Bagaimana template cocok ke dalam gambaran besar.

## Pemecahan Masalah
- Jika Anda memiliki pengalihan di middleware Anda, tetapi aplikasi Anda sepertinya tidak mengalihkan, pastikan Anda menambahkan pernyataan `exit;` di middleware Anda.

## Changelog
- v2.0 - Rilis awal.