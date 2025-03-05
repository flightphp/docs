# Tampilan HTML dan Template

Flight menyediakan beberapa fungsionalitas templating dasar secara default.

Flight memungkinkan Anda untuk mengganti mesin tampilan default hanya dengan mendaftarkan kelas tampilan Anda sendiri. Gulir ke bawah untuk melihat contoh cara menggunakan Smarty, Latte, Blade, dan lainnya!

## Mesin Tampilan Bawaan

Untuk menampilkan sebuah template tampilan, panggil metode `render` dengan nama file template dan data template opsional:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Data template yang Anda masukkan secara otomatis disuntikkan ke dalam template dan dapat dirujuk seperti variabel lokal. File template hanyalah file PHP. Jika konten dari file template `hello.php` adalah:

```php
Hello, <?= $name ?>!
```

Outputnya adalah:

```text
Hello, Bob!
```

Anda juga dapat secara manual mengatur variabel tampilan dengan menggunakan metode set:

```php
Flight::view()->set('name', 'Bob');
```

Variabel `name` kini tersedia di semua tampilan Anda. Jadi Anda dapat dengan mudah melakukan:

```php
Flight::render('hello');
```

Perhatikan bahwa saat menentukan nama template dalam metode render, Anda dapat menghilangkan ekstensi `.php`.

Secara default, Flight akan mencari direktori `views` untuk file template. Anda dapat menetapkan jalur alternatif untuk template Anda dengan mengatur konfigurasi berikut:

```php
Flight::set('flight.views.path', '/path/to/views');
```

### Tata Letak

Adalah hal yang umum untuk situs web memiliki satu file template tata letak dengan konten yang saling bertukar. Untuk merender konten yang digunakan dalam tata letak, Anda dapat memasukkan parameter opsional ke dalam metode `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Tampilan Anda kemudian akan memiliki variabel yang disimpan yang disebut `headerContent` dan `bodyContent`. Anda kemudian dapat merender tata letak Anda dengan melakukan:

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

## Smarty

Berikut cara menggunakan mesin template [Smarty](http://www.smarty.net/) untuk tampilan Anda:

```php
// Muat pustaka Smarty
require './Smarty/libs/Smarty.class.php';

// Daftarkan Smarty sebagai kelas tampilan
// Juga lewati fungsi callback untuk mengonfigurasi Smarty saat dimuat
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

Untuk kelengkapan, Anda juga harus menimpa metode render default Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

## Latte

Berikut cara menggunakan mesin template [Latte](https://latte.nette.org/) untuk tampilan Anda:

```php
// Daftarkan Latte sebagai kelas tampilan
// Juga lewati fungsi callback untuk mengonfigurasi Latte saat dimuat
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Di sinilah Latte akan menyimpan cache template Anda untuk mempercepat segalanya
	// Satu hal menarik tentang Latte adalah bahwa ia secara otomatis menyegarkan
	// cache saat Anda melakukan perubahan pada template Anda!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Beri tahu Latte di mana direktori akar untuk tampilan Anda akan berada.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Dan akhiri sehingga Anda dapat menggunakan Flight::render() dengan benar
Flight::map('render', function(string $template, array $data): void {
  // Ini seperti $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```

## Blade

Berikut cara menggunakan mesin template [Blade](https://laravel.com/docs/8.x/blade) untuk tampilan Anda:

Pertama, Anda perlu menginstal pustaka BladeOne melalui Composer:

```bash
composer require eftec/bladeone
```

Kemudian, Anda dapat mengonfigurasi BladeOne sebagai kelas tampilan di Flight:

```php
<?php
// Muat pustaka BladeOne
use eftec\bladeone\BladeOne;

// Daftarkan BladeOne sebagai kelas tampilan
// Juga lewati fungsi callback untuk mengonfigurasi BladeOne saat dimuat
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

Outputnya adalah:

```
Hello, Bob!
```

Dengan mengikuti langkah-langkah ini, Anda dapat mengintegrasikan mesin template Blade dengan Flight dan menggunakannya untuk merender tampilan Anda.