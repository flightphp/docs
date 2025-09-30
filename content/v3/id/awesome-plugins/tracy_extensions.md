Tracy Flight Panel Extensions
=====

Ini adalah serangkaian ekstensi untuk membuat kerja dengan Flight menjadi sedikit lebih kaya.

- Flight - Analisis semua variabel Flight.
- Database - Analisis semua kueri yang telah dijalankan di halaman (jika Anda menginisialisasi koneksi database dengan benar)
- Request - Analisis semua variabel `$_SERVER` dan periksa semua payload global (`$_GET`, `$_POST`, `$_FILES`)
- Session - Analisis semua variabel `$_SESSION` jika sesi aktif.

Ini adalah Panel

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Dan setiap panel menampilkan informasi yang sangat membantu tentang aplikasi Anda!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Klik [di sini](https://github.com/flightphp/tracy-extensions) untuk melihat kode.

Installation
-------
Jalankan `composer require flightphp/tracy-extensions --dev` dan Anda siap melanjutkan!

Configuration
-------
Ada sangat sedikit konfigurasi yang perlu Anda lakukan untuk memulai ini. Anda perlu menginisialisasi debugger Tracy sebelum menggunakan ini [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// bootstrap code
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// You may need to specify your environment with Debugger::enable(Debugger::DEVELOPMENT)

// if you use database connections in your app, there is a 
// required PDO wrapper to use ONLY IN DEVELOPMENT (not production please!)
// It has the same parameters as a regular PDO connection
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// or if you attach this to the Flight framework
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// now whenever you make a query it will capture the time, query, and parameters

// This connects the dots
if(Debugger::$showBar === true) {
	// This needs to be false or Tracy can't actually render :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// more code

Flight::start();
```

## Additional Configuration

### Session Data
Jika Anda memiliki handler sesi kustom (seperti ghostff/session), Anda dapat mengirimkan array data sesi apa pun ke Tracy dan itu akan secara otomatis menampilkannya untuk Anda. Anda mengirimkannya dengan kunci `session_data` di parameter kedua dari konstruktor `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;
// or use flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// This needs to be false or Tracy can't actually render :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// routes and other things...

Flight::start();
```

### Latte

_PHP 8.1+ diperlukan untuk bagian ini._

Jika Anda memiliki Latte yang terinstal di proyek Anda, Tracy memiliki integrasi native dengan Latte untuk menganalisis template Anda. Anda cukup mendaftarkan ekstensi dengan instance Latte Anda.

```php

require 'vendor/autoload.php';

$app = Flight::app();

$app->map('render', function($template, $data, $block = null) {
	$latte = new Latte\Engine;

	// other configurations...

	// only add the extension if Tracy Debug Bar is enabled
	if(Debugger::$showBar === true) {
		// this is where you add the Latte Panel to Tracy
		$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
	}

	$latte->render($template, $data, $block);
});
```