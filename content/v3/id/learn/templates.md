# Tampilan HTML dan Template

## Ikhtisar

Flight menyediakan beberapa fungsionalitas templating HTML dasar secara bawaan. Templating adalah cara yang sangat efektif untuk memisahkan logika aplikasi Anda dari lapisan presentasi. Mesin khusus (Twig, Latte, dll.) juga memberikan [alat coding AI](/learn/ai) sintaks yang familier dan terbatas sehingga mereka cenderung tidak membuang logika bisnis ke dalam HTML Anda.

## Pemahaman

Ketika Anda membangun aplikasi, Anda kemungkinan akan memiliki HTML yang ingin Anda kirim kembali ke pengguna akhir. PHP sendiri adalah bahasa templating, tetapi sangat mudah untuk membungkus logika bisnis seperti panggilan basis data, panggilan API, dll. ke dalam file HTML Anda dan membuat pengujian serta decoupling menjadi proses yang sangat sulit. Dengan mendorong data ke dalam template dan membiarkan template merender dirinya sendiri, akan jauh lebih mudah untuk mendekouple dan melakukan unit test pada kode Anda. Anda akan berterima kasih kepada kami jika Anda menggunakan template!

## Penggunaan Dasar

Flight memungkinkan Anda untuk mengganti mesin tampilan bawaan cukup dengan memetakan `render` (atau mendaftarkan kelas view). Gulir ke bawah untuk Twig, Latte, Smarty, Blade, dan lainnya.

> **Default skeleton:** [flightphp/skeleton](https://github.com/flightphp/skeleton) resmi menggunakan **Twig saja** di bawah `app/views/` (`*.twig`). Controller memanggil `$this->app->render('welcome', $data)` (ekstensi opsional). Itu adalah pilihan aplikasi untuk proyek baru—bukan persyaratan inti Flight. Latte dan mesin lainnya tetap didukung penuh.

### Twig

<span class="badge bg-info">default skeleton</span>

[Twig](https://twig.symfony.com/) adalah mesin template yang fleksibel, cepat, dan aman yang digunakan oleh Symfony dan banyak proyek PHP lainnya. Alat coding AI cenderung mengenal Twig dengan sangat baik, dan secara bawaan ia melakukan auto-escape pada output untuk membantu melindungi dari XSS.

#### Instalasi

```bash
composer require twig/twig
```

(Sudah disertakan ketika Anda `composer create-project flightphp/skeleton`.)

#### Konfigurasi Dasar

Timpa metode `render` untuk menggunakan Twig sebagai pengganti renderer PHP bawaan:

```php
// timpa metode render untuk menggunakan Twig sebagai pengganti renderer PHP bawaan
Flight::map('render', function(string $template, array $data): void {
	$loader = new \Twig\Loader\FilesystemLoader(Flight::get('flight.views.path'));
	$twig = new \Twig\Environment($loader, [
		// Tempat Twig menyimpan template terkompilasi
		'cache' => __DIR__ . '/../cache/twig',
		'auto_reload' => true,
	]);

	// Izinkan "welcome" atau "welcome.twig"
	if (substr($template, -5) !== '.twig') {
		$template .= '.twig';
	}

	echo $twig->render($template, $data);
});
```

Pada skeleton, koneksi ini berada di `app/config/services.php` (lingkungan Twig bersama, jalur cache, global seperti `base_url` / CSP nonce). Utamakan injeksi `Engine` dan panggil `$app->render()` dari controller agar kode tetap [ramah AI dan pengujian](/learn/ai).

#### Menggunakan Twig di Flight

Sekarang setelah Anda dapat merender dengan Twig, Anda dapat melakukan sesuatu seperti ini:

```html
{# app/views/home.twig #}
<html>
  <head>
	<title>{% if title %}{{ title }} - {% endif %}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, {{ name }}!</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.twig', [
		'title' => 'Home Page',
		'name' => $name
	]);
});
```

Ketika Anda mengunjungi `/Bob` di browser Anda, outputnya adalah:

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

Contoh yang lebih lengkap tentang penggunaan Twig dengan layout ditunjukkan di bagian [plugin luar biasa](/awesome-plugins/twig) dari dokumentasi ini. Untuk metrik waktu render pada bar Tracy, lihat [panel Twig di Ekstensi Tracy](/awesome-plugins/tracy-extensions#twig-panel-optional).

Anda dapat mempelajari lebih lanjut tentang kemampuan lengkap Twig dengan membaca [dokumentasi resmi](https://twig.symfony.com/doc/3.x/).

### Latte

<span class="badge bg-secondary">alternatif hebat</span>

[Latte](https://latte.nette.org/) adalah mesin berfitur lengkap dengan sintaks mirip PHP. Ia masih menjadi pilihan yang sangat baik untuk aplikasi Flight; skeleton hanya menstandarkan Twig sebagai satu default bersama (terutama membantu ketika alat AI menghasilkan template).

#### Instalasi

```bash
composer require latte/latte
```

#### Konfigurasi Dasar

Ide utamanya adalah Anda menimpa metode `render` untuk menggunakan Latte sebagai pengganti renderer PHP bawaan.

```php
// timpa metode render untuk menggunakan latte sebagai pengganti renderer PHP bawaan
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Tempat latte secara khusus menyimpan cache-nya
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

Ketika Anda mengunjungi `/Bob` di browser Anda, outputnya adalah:

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

Contoh yang lebih kompleks tentang penggunaan Latte dengan layout ditunjukkan di bagian [plugin luar biasa](/awesome-plugins/latte) dari dokumentasi ini.

Anda dapat mempelajari lebih lanjut tentang kemampuan lengkap Latte termasuk kemampuan terjemahan dan bahasa dengan membaca [dokumentasi resmi](https://latte.nette.org/en/).

### Mesin Tampilan Bawaan

<span class="badge bg-warning">usang</span>

> **Catatan:** Meskipun ini masih merupakan fungsionalitas bawaan dan secara teknis masih berfungsi.

Untuk menampilkan template view, panggil metode `render` dengan nama file template dan data template opsional:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Data template yang Anda teruskan secara otomatis disuntikkan ke dalam template dan dapat direferensikan seperti variabel lokal. File template hanyalah file PHP. Jika isi file template `hello.php` adalah:

```php
Hello, <?= $name ?>!
```

Outputnya adalah:

```text
Hello, Bob!
```

Anda juga dapat mengatur variabel view secara manual dengan menggunakan metode `set`:

```php
Flight::view()->set('name', 'Bob');
```

Variabel `name` sekarang tersedia di semua view Anda. Jadi Anda cukup melakukan:

```php
Flight::render('hello');
```

Perhatikan bahwa saat menentukan nama template dalam metode render, Anda dapat menghilangkan ekstensi `.php`.

Secara bawaan, Flight akan mencari direktori `views` untuk file template. Anda dapat mengatur jalur alternatif untuk template Anda dengan mengatur konfigurasi berikut:

```php
Flight::set('flight.views.path', '/path/to/views');
```

Secara bawaan, `View` bawaan Flight juga akan menerima jalur template absolut, atau nama yang keluar dari direktori tersebut. Untuk sebagian besar aplikasi, Anda harus menguncinya:

```php
Flight::set('flight.views.restrict_to_path', true);
```

Itu menjaga `render()`, `fetch()`, dan `exists()` tetap di dalam `flight.views.path`. Secara bawaan ini nonaktif untuk kompatibilitas mundur. Lihat [Keamanan](/learn/security#flightviewsrestrict_to_path).

#### Tata Letak

Umum bagi situs web untuk memiliki satu file template tata letak dengan konten yang saling berganti. Untuk merender konten yang akan digunakan dalam tata letak, Anda dapat meneruskan parameter opsional ke metode `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

View Anda kemudian akan memiliki variabel tersimpan bernama `headerContent` dan `bodyContent`. Anda kemudian dapat merender tata letak dengan melakukan:

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

Outputnya adalah:

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

Berikut cara menggunakan mesin template [Smarty](http://www.smarty.net/) untuk view Anda:

```php
// Muat pustaka Smarty
require './Smarty/libs/Smarty.class.php';

// Daftarkan Smarty sebagai kelas view
// Juga berikan fungsi callback untuk mengonfigurasi Smarty saat dimuat
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Tetapkan data template
Flight::view()->assign('name', 'Bob');

// Tampilkan template
Flight::view()->display('hello.tpl');
```

Untuk kelengkapan, Anda juga harus menimpa metode render bawaan Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

Berikut cara menggunakan mesin template [Blade](https://laravel.com/docs/8.x/blade) untuk view Anda:

Pertama, Anda perlu menginstal pustaka BladeOne melalui Composer:

```bash
composer require eftec/bladeone
```

Kemudian, Anda dapat mengonfigurasi BladeOne sebagai kelas view di Flight:

```php
<?php
// Muat pustaka BladeOne
use eftec\bladeone\BladeOne;

// Daftarkan BladeOne sebagai kelas view
// Juga berikan fungsi callback untuk mengonfigurasi BladeOne saat dimuat
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Tetapkan data template
Flight::view()->share('name', 'Bob');

// Tampilkan template
echo Flight::view()->run('hello', []);
```

Untuk kelengkapan, Anda juga harus menimpa metode render bawaan Flight:

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

Outputnya adalah:

```
Hello, Bob!
```

## Lihat Juga
- [Instalasi](/install) - Tata letak skeleton (`app/views/*.twig`) untuk proyek baru.
- [Ekstensi](/learn/extending) - Cara menimpa metode `render` untuk menggunakan mesin template yang berbeda.
- [Routing](/learn/routing) - Cara memetakan rute ke controller dan merender view.
- [Respons](/learn/responses) - Cara menyesuaikan respons HTTP.
- [Keamanan](/learn/security) - Auto-escaping, XSS, dan `flight.views.restrict_to_path`.
- [AI & Pengalaman Pengembang](/learn/ai) - Mengapa satu default mesin view membantu agen coding.
- [Mengapa Framework?](/learn/why-frameworks) - Bagaimana template cocok dalam gambaran besar.

## Pemecahan Masalah
- Jika Anda memiliki redirect di middleware, tetapi aplikasi Anda tampaknya tidak mengalihkan, pastikan Anda menambahkan pernyataan `exit;` di middleware Anda.
- Jika Twig tidak dapat menemukan template, periksa `flight.views.path` dan pastikan file tersebut ada di bawah jalur tersebut dengan ekstensi yang diharapkan (skeleton: `app/views/`).

## Catatan Perubahan
- Dokumentasi – Mendokumentasikan `flight.views.restrict_to_path` untuk view PHP asli.
- Dokumentasi – Twig didokumentasikan sebagai default skeleton resmi; Latte tetap menjadi alternatif kelas satu.
- v2.0 - Rilis awal.