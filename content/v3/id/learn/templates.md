# Tampilan HTML dan Template

Flight menyediakan beberapa fungsi templating dasar secara default.

Flight memungkinkan Anda untuk menukar mesin tampilan default hanya dengan mendaftarkan kelas tampilan Anda sendiri. Gulir ke bawah untuk melihat contoh cara menggunakan Smarty, Latte, Blade, dan lainnya!

## Mesin Tampilan Bawaan

Untuk menampilkan template tampilan, panggil metode `render` dengan nama
file template dan data template opsional:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Data template yang Anda masukkan secara otomatis disuntikkan ke dalam template dan dapat
diacu seperti variabel lokal. File template hanyalah file PHP. Jika
konten dari file template `hello.php` adalah:

```php
Hello, <?= $name ?>!
```

Outputnya akan menjadi:

```
Hello, Bob!
```

Anda juga dapat menetapkan variabel tampilan secara manual dengan menggunakan metode set:

```php
Flight::view()->set('name', 'Bob');
```

Variabel `name` sekarang tersedia di semua tampilan Anda. Jadi Anda dapat dengan mudah melakukan:

```php
Flight::render('hello');
```

Perhatikan bahwa saat menentukan nama template di metode render, Anda dapat
meninggalkan ekstensi `.php`.

Secara default, Flight akan mencari direktori `views` untuk file template. Anda dapat
menetapkan jalur alternatif untuk template Anda dengan menetapkan konfigurasi berikut:

```php
Flight::set('flight.views.path', '/path/to/views');
```

### Layout

Adalah umum bagi situs web untuk memiliki satu file template layout dengan konten yang saling berganti. Untuk merender konten yang akan digunakan dalam layout, Anda dapat memasukkan parameter opsional ke dalam metode `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Tampilan Anda kemudian akan memiliki variabel yang disimpan yang disebut `headerContent` dan `bodyContent`. Anda kemudian dapat merender layout Anda dengan melakukan:

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

## Smarty

Ini adalah cara Anda menggunakan mesin template [Smarty](http://www.smarty.net/) untuk tampilan Anda:

```php
// Memuat pustaka Smarty
require './Smarty/libs/Smarty.class.php';

// Mendaftarkan Smarty sebagai kelas tampilan
// Juga masukkan fungsi callback untuk mengonfigurasi Smarty saat memuat
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

Untuk kelengkapan, Anda juga harus menimpa metode render default dari Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

## Latte

Ini adalah cara Anda menggunakan mesin template [Latte](https://latte.nette.org/) untuk tampilan Anda:

```php

// Mendaftarkan Latte sebagai kelas tampilan
// Juga masukkan fungsi callback untuk mengonfigurasi Latte saat memuat
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Di sinilah Latte akan menyimpan cache untuk template Anda agar lebih cepat
	// Salah satu hal menarik tentang Latte adalah bahwa ia secara otomatis menyegarkan
	// cache Anda ketika Anda melakukan perubahan pada template Anda!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Beritahu Latte di mana direktori root untuk tampilan Anda akan berada.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Dan selesai sehingga Anda dapat menggunakan Flight::render() dengan benar
Flight::map('render', function(string $template, array $data): void {
  // Ini seperti $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```

## Blade

Ini adalah cara Anda menggunakan mesin template [Blade](https://laravel.com/docs/8.x/blade) untuk tampilan Anda:

Pertama, Anda perlu menginstal pustaka BladeOne melalui Composer:

```bash
composer require eftec/bladeone
```

Kemudian, Anda dapat mengonfigurasi BladeOne sebagai kelas tampilan di Flight:

```php
<?php
// Memuat pustaka BladeOne
use eftec\bladeone\BladeOne;

// Mendaftarkan BladeOne sebagai kelas tampilan
// Juga masukkan fungsi callback untuk mengonfigurasi BladeOne saat memuat
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Menetapkan data template
Flight::view()->share('name', 'Bob');

// Menampilkan template
echo Flight::view()->run('hello', []);
```

Untuk kelengkapan, Anda juga harus menimpa metode render default dari Flight:

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

Dengan mengikuti langkah-langkah ini, Anda dapat mengintegrasikan mesin template Blade dengan Flight dan menggunakannya untuk merender tampilan Anda.