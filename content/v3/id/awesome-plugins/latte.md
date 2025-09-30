# Latte

[Latte](https://latte.nette.org/en/guide) adalah mesin templating lengkap yang sangat mudah digunakan dan terasa lebih dekat dengan sintaks PHP daripada Twig atau Smarty. Ini juga sangat mudah untuk diperluas dan menambahkan filter serta fungsi Anda sendiri.

## Instalasi

Instal dengan composer.

```bash
composer require latte/latte
```

## Konfigurasi Dasar

Ada beberapa opsi konfigurasi dasar untuk memulai. Anda dapat membaca lebih lanjut tentangnya di [Dokumentasi Latte](https://latte.nette.org/en/guide).

```php

require 'vendor/autoload.php';

$app = Flight::app();

$app->map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Tempat di mana latte secara khusus menyimpan cache-nya
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

## Contoh Layout Sederhana

Berikut adalah contoh sederhana dari file layout. Ini adalah file yang akan digunakan untuk membungkus semua tampilan Anda yang lain.

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="en">
	<head>
		<title>{$title ? $title . ' - '}My App</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<header>
			<nav>
				<!-- elemen nav Anda di sini -->
			</nav>
		</header>
		<div id="content">
			<!-- Ini adalah keajaiban di sini -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Copyright
		</div>
	</body>
</html>
```

Dan sekarang kita punya file Anda yang akan dirender di dalam blok konten tersebut:

```html
<!-- app/views/home.latte -->
<!-- Ini memberi tahu Latte bahwa file ini "di dalam" file layout.latte -->
{extends layout.latte}

<!-- Ini adalah konten yang akan dirender di dalam layout di dalam blok konten -->
{block content}
	<h1>Halaman Beranda</h1>
	<p>Selamat datang di aplikasi saya!</p>
{/block}
```

Kemudian ketika Anda pergi untuk merender ini di dalam fungsi atau controller Anda, Anda akan melakukan sesuatu seperti ini:

```php
// rute sederhana
Flight::route('/', function () {
	Flight::render('home.latte', [
		'title' => 'Halaman Beranda'
	]);
});

// atau jika Anda menggunakan controller
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::render('home.latte', [
			'title' => 'Halaman Beranda'
		]);
	}
}
```

Lihat [Dokumentasi Latte](https://latte.nette.org/en/guide) untuk informasi lebih lanjut tentang cara menggunakan Latte secara maksimal!

## Debugging dengan Tracy

_PHP 8.1+ diperlukan untuk bagian ini._

Anda juga dapat menggunakan [Tracy](https://tracy.nette.org/en/) untuk membantu debugging file template Latte Anda langsung dari kotak! Jika Anda sudah menginstal Tracy, Anda perlu menambahkan ekstensi Latte ke Tracy.

```php
// services.php
use Tracy\Debugger;

$app->map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Tempat di mana latte secara khusus menyimpan cache-nya
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	// Ini hanya akan menambahkan ekstensi jika Bilah Debug Tracy diaktifkan
	if (Debugger::$showBar === true) {
		// ini adalah tempat Anda menambahkan Panel Latte ke Tracy
		$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
	}
	$latte->render($finalPath, $data, $block);
});
```