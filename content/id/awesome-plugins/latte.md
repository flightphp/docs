# Latte

[Latte](https://latte.nette.org/en/guide) adalah mesin templating dengan fitur lengkap yang sangat mudah digunakan dan terasa lebih dekat dengan sintaks PHP dibandingkan Twig atau Smarty. Ini juga sangat mudah untuk diperluas dan menambahkan filter serta fungsi Anda sendiri.

## Instalasi

Instal dengan composer.

```bash
composer require latte/latte
```

## Konfigurasi Dasar

Ada beberapa opsi konfigurasi dasar untuk memulai. Anda dapat membaca lebih lanjut tentang mereka di [Dokumentasi Latte](https://latte.nette.org/en/guide).

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// Di sinilah Latte akan menyimpan cache untuk template Anda untuk mempercepat proses
	// Satu hal menarik tentang Latte adalah bahwa ia secara otomatis menyegarkan 
	// cache saat Anda membuat perubahan pada template Anda!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Beri tahu Latte di mana direktori root untuk tampilan Anda akan berada.
	// $app->get('flight.views.path') diatur di file config.php
	//   Anda juga bisa melakukan sesuatu seperti `__DIR__ . '/../views/'`
	$latte->setLoader(new \Latte\Loaders\FileLoader($app->get('flight.views.path')));
});
```

## Contoh Layout Sederhana

Ini adalah contoh sederhana dari file layout. Ini adalah file yang akan digunakan untuk membungkus semua tampilan Anda yang lain.

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="en">
	<head>
		<title>{$title ? $title . ' - '}Aplikasi Saya</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<header>
			<nav>
				<!-- elemen navigasi Anda di sini -->
			</nav>
		</header>
		<div id="content">
			<!-- Ini adalah keajaiban yang terjadi di sini -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Hak Cipta
		</div>
	</body>
</html>
```

Dan sekarang kita memiliki file Anda yang akan dirender di dalam blok konten tersebut:

```html
<!-- app/views/home.latte -->
<!-- Ini memberi tahu Latte bahwa file ini "di dalam" file layout.latte -->
{extends layout.latte}

<!-- Ini adalah konten yang akan dirender di dalam layout di dalam blok konten -->
{block content}
	<h1>Halaman Utama</h1>
	<p>Selamat datang di aplikasi saya!</p>
{/block}
```

Kemudian saat Anda pergi untuk merender ini di dalam fungsi atau kontroler Anda, Anda akan melakukan sesuatu seperti ini:

```php
// rute sederhana
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => 'Halaman Utama'
	]);
});

// atau jika Anda menggunakan kontroler
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::latte()->render('home.latte', [
			'title' => 'Halaman Utama'
		]);
	}
}
```

Lihat [Dokumentasi Latte](https://latte.nette.org/en/guide) untuk informasi lebih lanjut tentang cara menggunakan Latte untuk potensi penuh!