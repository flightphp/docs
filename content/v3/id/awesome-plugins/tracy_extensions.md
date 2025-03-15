Tracy Flight Panel Extensions
=====

Ini adalah seperangkat ekstensi untuk membuat kerja dengan Flight sedikit lebih kaya.

- Flight - Analisis semua variabel Flight.
- Database - Analisis semua kueri yang telah dijalankan di halaman (jika Anda telah memulai koneksi database dengan benar)
- Request - Analisis semua variabel `$_SERVER` dan periksa semua payload global (`$_GET`, `$_POST`, `$_FILES`)
- Session - Analisis semua variabel `$_SESSION` jika sesi aktif.

Ini adalah Panel

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Dan setiap panel menampilkan informasi yang sangat berguna tentang aplikasi Anda!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Klik [di sini](https://github.com/flightphp/tracy-extensions) untuk melihat kodenya.

Instalasi
-------
Jalankan `composer require flightphp/tracy-extensions --dev` dan Anda sudah siap!

Konfigurasi
-------
Ada sangat sedikit konfigurasi yang perlu Anda lakukan untuk memulai ini. Anda perlu memulai debugger Tracy sebelum menggunakan ini [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// kode bootstrap
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Anda mungkin perlu menentukan lingkungan Anda dengan Debugger::enable(Debugger::DEVELOPMENT)

// jika Anda menggunakan koneksi database di aplikasi Anda, ada
// pembungkus PDO yang diperlukan untuk digunakan HANYA DI PENGEMBANGAN (bukan produksi, tolong!)
// Ini memiliki parameter yang sama dengan koneksi PDO biasa
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// atau jika Anda menambahkannya ke framework Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// sekarang setiap kali Anda melakukan kueri, itu akan menangkap waktu, kueri, dan parameter

// Ini menghubungkan titik-titik
if(Debugger::$showBar === true) {
	// Ini perlu false atau Tracy tidak dapat merender :)
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// kode lebih lanjut

Flight::start();
```

## Konfigurasi Tambahan

### Data Sesi
Jika Anda memiliki pengelola sesi kustom (seperti ghostff/session), Anda dapat mengirimkan array data sesi apa pun ke Tracy dan itu secara otomatis akan mengeluarkannya untuk Anda. Anda mengirimkannya dengan kunci `session_data` di parameter kedua dari konstruktor `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;
// atau gunakan flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Ini perlu false atau Tracy tidak dapat merender :)
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// rute dan hal-hal lainnya...

Flight::start();
```

### Latte

Jika Anda memiliki Latte terinstal di proyek Anda, Anda dapat menggunakan panel Latte untuk menganalisis template Anda. Anda dapat mengirimkan instance Latte ke konstruktor `TracyExtensionLoader` dengan kunci `latte` di parameter kedua.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// di sinilah Anda menambahkan Panel Latte ke Tracy
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// Ini perlu false atau Tracy tidak dapat merender :)
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
```