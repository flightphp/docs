Tracy Flight Panel Extensions
=====

Ini adalah sekumpulan ekstensi untuk membuat kerja dengan Flight lebih kaya.

- Flight - Analisis semua variabel Flight.
- Database - Analisis semua query yang telah dijalankan pada halaman (jika Anda menginisialisasi koneksi database dengan benar).
- Request - Analisis semua variabel `$_SERVER` dan periksa semua payload global (`$_GET`, `$_POST`, `$_FILES`).
- Session - Analisis semua variabel `$_SESSION` jika sesi aktif.

Ini adalah Panel

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Dan setiap panel menampilkan informasi yang sangat membantu tentang aplikasi Anda!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Klik [here](https://github.com/flightphp/tracy-extensions) untuk melihat kode.

Installation
-------
Jalankan `composer require flightphp/tracy-extensions --dev` dan Anda siap!

Configuration
-------
Tidak banyak konfigurasi yang perlu Anda lakukan untuk memulai ini. Anda perlu menginisialisasi debugger Tracy sebelum menggunakan ini [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// kode bootstrap
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Anda mungkin perlu menentukan lingkungan Anda dengan Debugger::enable(Debugger::DEVELOPMENT)

// jika Anda menggunakan koneksi database dalam aplikasi Anda, ada 
// wrapper PDO yang diperlukan untuk digunakan HANYA DALAM PENGEMBANGAN (bukan produksi tolong!)
// Ini memiliki parameter yang sama seperti koneksi PDO reguler
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// atau jika Anda menambahkan ini ke framework Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// sekarang setiap kali Anda membuat query, itu akan menangkap waktu, query, dan parameter

// Ini menghubungkan titik-titik
if(Debugger::$showBar === true) {
	// Ini perlu menjadi false atau Tracy tidak bisa benar-benar merender :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// lebih kode

Flight::start();
```

## Additional Configuration

### Session Data
Jika Anda memiliki handler sesi khusus (seperti ghostff/session), Anda bisa meneruskan array data sesi ke Tracy dan itu akan secara otomatis mengeluarkan untuk Anda. Anda meneruskannya dengan kunci `session_data` dalam parameter kedua dari konstruktor `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;
// atau gunakan flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Ini perlu menjadi false atau Tracy tidak bisa benar-benar merender :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// rute dan hal-hal lainnya...

Flight::start();
```

### Latte

Jika Anda memiliki Latte terinstal dalam proyek Anda, Anda bisa menggunakan panel Latte untuk menganalisis template Anda. Anda bisa meneruskan instance Latte ke konstruktor `TracyExtensionLoader` dengan kunci `latte` dalam parameter kedua.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// ini adalah tempat Anda menambahkan Panel Latte ke Tracy
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// Ini perlu menjadi false atau Tracy tidak bisa benar-benar merender :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
```