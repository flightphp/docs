# Memperluas

Flight dirancang untuk menjadi kerangka kerja yang dapat diperluas. Kerangka kerja ini dilengkapi dengan serangkaian metode dan komponen default, tetapi memungkinkan Anda untuk memetakan metode Anda sendiri, mendaftarkan kelas Anda sendiri, atau bahkan mengganti kelas dan metode yang sudah ada.

Jika Anda mencari DIC (Dependency Injection Container), silakan kunjungi halaman [Dependency Injection Container](dependency-injection-container).

## Memetakan Metode

Untuk memetakan metode kustom sederhana Anda sendiri, Anda menggunakan fungsi `map`:

```php
// Pemetakan metode Anda
Flight::map('hello', function (string $name) {
  echo "halo $name!";
});

// Panggil metode kustom Anda
Flight::hello('Bob');
```

Meskipun mungkin untuk membuat metode kustom sederhana, disarankan untuk hanya membuat fungsi standar di PHP. Ini memiliki autocompletion di IDE dan lebih mudah dibaca. Padanan dari kode di atas adalah:

```php
function hello(string $name) {
  echo "halo $name!";
}

hello('Bob');
```

Ini lebih sering digunakan ketika Anda perlu mengoper variabel ke metode Anda untuk mendapatkan nilai yang diharapkan. Menggunakan metode `register()` seperti di bawah ini lebih untuk mengoper konfigurasi dan kemudian memanggil kelas yang sudah dikonfigurasi sebelumnya.

## Mendaftarkan Kelas

Untuk mendaftarkan kelas Anda sendiri dan mengkonfigurasinya, Anda menggunakan fungsi `register`:

```php
// Daftarkan kelas Anda
Flight::register('user', User::class);

// Ambil instance dari kelas Anda
$user = Flight::user();
```

Metode register juga memungkinkan Anda untuk mengoper parameter ke konstruktor kelas Anda. Jadi, ketika Anda memuat kelas kustom Anda, itu akan datang dengan inisialisasi awal. Anda dapat menentukan parameter konstruktor dengan mengoper array tambahan. Berikut adalah contoh memuat koneksi database:

```php
// Daftarkan kelas dengan parameter konstruktor
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Ambil instance dari kelas Anda
// Ini akan membuat objek dengan parameter yang ditentukan
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

Jika Anda mengoper parameter callback tambahan, itu akan dieksekusi segera setelah konstruktor kelas. Ini memungkinkan Anda untuk melakukan prosedur penyiapan untuk objek baru Anda. Fungsi callback mengambil satu parameter, sebuah instance dari objek baru.

```php
// Callback akan menerima objek yang telah dibangun
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Secara default, setiap kali Anda memuat kelas Anda, Anda akan mendapatkan instance yang dibagikan. Untuk mendapatkan instance baru dari kelas, cukup oper `false` sebagai parameter:

```php
// Instance bersama dari kelas
$shared = Flight::db();

// Instance baru dari kelas
$new = Flight::db(false);
```

Ingat bahwa metode yang dipetakan memiliki prioritas atas kelas yang terdaftar. Jika Anda mendeklarasikan keduanya menggunakan nama yang sama, hanya metode yang dipetakan yang akan dipanggil.

## Logging

Flight tidak memiliki sistem logging bawaan, namun, sangat mudah untuk menggunakan pustaka logging dengan Flight. Berikut adalah contoh menggunakan pustaka Monolog:

```php
// index.php atau bootstrap.php

// Daftarkan logger dengan Flight
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

Sekarang setelah terdaftar, Anda dapat menggunakannya di aplikasi Anda:

```php
// Di controller atau route Anda
Flight::log()->warning('Ini adalah pesan peringatan');
```

Ini akan mencatat pesan ke file log yang Anda tentukan. Bagaimana jika Anda ingin mencatat sesuatu saat terjadi kesalahan? Anda dapat menggunakan metode `error`:

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

	// Anda juga bisa menambahkan header permintaan atau respons Anda
	// untuk mencatatnya juga (hati-hati karena ini akan jadi
	// banyak data jika Anda memiliki banyak permintaan)
	Flight::log()->info('Header Permintaan: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('Header Respons: ' . json_encode(Flight::response()->headers));
});
```

## Mengganti Metode Framework

Flight memungkinkan Anda untuk mengganti fungsionalitas bawaannya agar sesuai dengan kebutuhan Anda, tanpa harus memodifikasi kode apa pun. Anda dapat melihat semua metode yang dapat Anda ganti [di sini](/learn/api).

Sebagai contoh, ketika Flight tidak dapat mencocokkan URL dengan rute, ia memanggil metode `notFound` yang mengirimkan tanggapan `HTTP 404` umum. Anda dapat mengganti perilaku ini dengan menggunakan metode `map`:

```php
Flight::map('notFound', function() {
  // Tampilkan halaman kustom 404
  include 'errors/404.html';
});
```

Flight juga memungkinkan Anda untuk mengganti komponen inti dari kerangka kerja. Sebagai contoh, Anda dapat mengganti kelas Router default dengan kelas kustom Anda sendiri:

```php
// Daftarkan kelas kustom Anda
Flight::register('router', MyRouter::class);

// Ketika Flight memuat instance Router, itu akan memuat kelas Anda
$myrouter = Flight::router();
```

Namun, metode kerangka kerja seperti `map` dan `register` tidak dapat diganti. Anda akan mendapatkan kesalahan jika Anda mencoba melakukannya.