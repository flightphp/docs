# Tampilan dan Template HTML

Flight menyediakan beberapa fungsionalitas templating dasar secara default. 

Jika Anda memerlukan kebutuhan templating yang lebih kompleks, lihat contoh Smarty dan Latte di bagian [Tampilan Kustom](#custom-views).

## Mesin Tampilan Default

Untuk menampilkan template tampilan, panggil metode `render` dengan nama 
file template dan data template opsional:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Data template yang Anda masukkan akan secara otomatis disuntikkan ke dalam template dan dapat
diacu seperti variabel lokal. File template hanyalah file PHP. Jika isi file template `hello.php` adalah:

```php
Hello, <?= $name ?>!
```

Outputnya akan menjadi:

```
Hello, Bob!
```

Anda juga dapat secara manual mengatur variabel tampilan dengan menggunakan metode set:

```php
Flight::view()->set('name', 'Bob');
```

Variabel `name` sekarang tersedia di semua tampilan Anda. Jadi Anda cukup melakukan:

```php
Flight::render('hello');
```

Perhatikan bahwa saat menentukan nama template dalam metode render, Anda dapat
mengabaikan ekstensi `.php`.

Secara default, Flight akan mencari direktori `views` untuk file template. Anda dapat
mengatur jalur alternatif untuk template Anda dengan menyetel konfigurasi berikut:

```php
Flight::set('flight.views.path', '/path/to/views');
```

### Layout

Umum bagi situs web untuk memiliki satu file template layout dengan konten yang berganti-ganti. Untuk merender konten yang akan digunakan dalam layout, Anda dapat memberikan parameter opsional pada metode `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Tampilan Anda kemudian akan memiliki variabel yang disimpan bernama `headerContent` dan `bodyContent`.
Anda kemudian dapat merender layout Anda dengan melakukan:

```php
Flight::render('layout', ['title' => 'Halaman Utama']);
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
    <title>Halaman Utama</title>
  </head>
  <body>
    <h1>Hello</h1>
    <div>World</div>
  </body>
</html>
```

## Mesin Tampilan Kustom

Flight memungkinkan Anda untuk mengganti mesin tampilan default hanya dengan mendaftarkan kelas tampilan Anda sendiri. 

### Smarty

Inilah cara Anda menggunakan mesin template [Smarty](http://www.smarty.net/)
untuk tampilan Anda:

```php
// Memuat pustaka Smarty
require './Smarty/libs/Smarty.class.php';

// Mendaftarkan Smarty sebagai kelas tampilan
// Juga berikan fungsi callback untuk mengonfigurasi Smarty saat dimuat
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Menetapkan data template
Flight::view()->assign('name', 'Bob');

// Menampilkan template
Flight::view()->display('hello.tpl');
```

Untuk kelengkapan, Anda juga harus menimpa metode render default Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

Inilah cara Anda menggunakan mesin template [Latte](https://latte.nette.org/)
untuk tampilan Anda:

```php

// Mendaftarkan Latte sebagai kelas tampilan
// Juga berikan fungsi callback untuk mengonfigurasi Latte saat dimuat
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Di sinilah Latte akan menyimpan cache template Anda untuk mempercepat segalanya
	// Satu hal yang menarik tentang Latte adalah bahwa ia secara otomatis menyegarkan
	// cache Anda saat Anda melakukan perubahan pada template Anda!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Beritahu Latte di mana direktori root untuk tampilan Anda.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Dan menyimpulkan sehingga Anda dapat menggunakan Flight::render() dengan benar
Flight::map('render', function(string $template, array $data): void {
  // Ini seperti $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```