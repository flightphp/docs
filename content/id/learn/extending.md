# Memperluas

Flight dirancang sebagai framework yang dapat diperluas. Framework ini datang dengan serangkaian metode dan komponen default, tetapi memungkinkan Anda untuk memetakan metode Anda sendiri, mendaftarkan kelas Anda sendiri, atau bahkan menimpa kelas dan metode yang sudah ada.

Jika Anda mencari DIC (Dependency Injection Container), silakan lihat halaman [Dependency Injection Container](dependency-injection-container).

## Memetakan Metode

Untuk memetakan metode kustom sederhana Anda sendiri, Anda menggunakan fungsi `map`:

```php
// Memetakan metode Anda
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// Memanggil metode kustom Anda
Flight::hello('Bob');
```

Sementara memungkinkan untuk membuat metode kustom sederhana, disarankan untuk hanya membuat fungsi standar di PHP. Ini memiliki autocomplete di IDE dan lebih mudah dibaca. Setara dari kode di atas adalah:

```php
function hello(string $name) {
  echo "hello $name!";
}

hello('Bob');
```

Ini lebih banyak digunakan ketika Anda perlu melewatkan variabel ke dalam metode Anda untuk mendapatkan nilai yang diharapkan. Menggunakan metode `register()` seperti di bawah ini lebih untuk memasukkan konfigurasi dan kemudian memanggil kelas yang telah dikonfigurasi sebelumnya.

## Mendaftarkan Kelas

Untuk mendaftarkan kelas Anda sendiri dan mengkonfigurasinya, Anda menggunakan fungsi `register`:

```php
// Mendaftarkan kelas Anda
Flight::register('user', User::class);

// Mendapatkan instance dari kelas Anda
$user = Flight::user();
```

Metode register juga memungkinkan Anda untuk meneruskan parameter ke konstruktor kelas Anda. Jadi ketika Anda memuat kelas kustom Anda, itu akan sudah terinisialisasi. Anda dapat mendefinisikan parameter konstruktor dengan meneruskan array tambahan. Berikut adalah contoh memuat koneksi database:

```php
// Mendaftarkan kelas dengan parameter konstruktor
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Mendapatkan instance dari kelas Anda
// Ini akan membuat objek dengan parameter yang sudah ditentukan
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// dan jika Anda membutuhkannya nanti dalam kode Anda, Anda cukup memanggil metode yang sama lagi
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Jika Anda meneruskan parameter callback tambahan, itu akan dieksekusi segera setelah konstruktor kelas. Ini memungkinkan Anda untuk melakukan prosedur pengaturan untuk objek baru Anda. Fungsi callback mengambil satu parameter, instance dari objek baru.

```php
// Callback akan menerima objek yang telah dikonstruksi
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Secara default, setiap kali Anda memuat kelas Anda, Anda akan mendapatkan instance yang dibagikan. Untuk mendapatkan instance baru dari kelas, cukup masukkan `false` sebagai parameter:

```php
// Instance bersama dari kelas
$shared = Flight::db();

// Instance baru dari kelas
$new = Flight::db(false);
```

Perlu diingat bahwa metode yang dipetakan memiliki prioritas lebih tinggi daripada kelas yang terdaftar. Jika Anda mendeklarasikan keduanya dengan nama yang sama, hanya metode yang dipetakan yang akan dieksekusi.

## Logging

Flight tidak memiliki sistem logging yang terintegrasi, namun, sangat mudah untuk menggunakan pustaka logging dengan Flight. Berikut adalah contoh menggunakan pustaka Monolog:

```php
// index.php atau bootstrap.php

// Mendaftarkan logger dengan Flight
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

Sekarang setelah terdaftar, Anda dapat menggunakannya dalam aplikasi Anda:

```php
// Di controller atau route Anda
Flight::log()->warning('Ini adalah pesan peringatan');
```

Ini akan mencatat pesan ke file log yang Anda tentukan. Bagaimana jika Anda ingin mencatat sesuatu ketika terjadi kesalahan? Anda dapat menggunakan metode `error`:

```php
// Di controller atau route Anda

Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// Tampilkan halaman kesalahan kustom Anda
	include 'errors/500.html';
});
```

Anda juga bisa membuat sistem APM (Application Performance Monitoring) dasar menggunakan metode `before` dan `after`:

```php
// Di file bootstrap Anda

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('Permintaan '.Flight::request()->url.' memakan waktu ' . round($end - $start, 4) . ' detik');

	// Anda juga bisa menambahkan header permintaan atau respons
	// untuk mencatatnya juga (hati-hati karena ini akan menjadi 
	// banyak data jika Anda memiliki banyak permintaan)
	Flight::log()->info('Header Permintaan: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('Header Respons: ' . json_encode(Flight::response()->headers));
});
```

## Menimpa Metode Framework

Flight memungkinkan Anda untuk menimpa fungsionalitas default-nya untuk memenuhi kebutuhan Anda sendiri, tanpa harus memodifikasi kode apa pun. Anda dapat melihat semua metode yang bisa Anda timpa [di sini](/learn/api).

Misalnya, ketika Flight tidak dapat mencocokkan URL ke rute, ia memanggil metode `notFound` yang mengirimkan respons `HTTP 404` generik. Anda dapat menimpa perilaku ini dengan menggunakan metode `map`:

```php
Flight::map('notFound', function() {
  // Tampilkan halaman 404 kustom
  include 'errors/404.html';
});
```

Flight juga memungkinkan Anda untuk mengganti komponen inti dari framework. Misalnya, Anda dapat mengganti kelas Router default dengan kelas kustom Anda sendiri:

```php
// Mendaftarkan kelas kustom Anda
Flight::register('router', MyRouter::class);

// Ketika Flight memuat instance Router, itu akan memuat kelas Anda
$myrouter = Flight::router();
```

Metode framework seperti `map` dan `register` tidak bisa ditimpa. Anda akan mendapatkan kesalahan jika mencoba melakukannya.