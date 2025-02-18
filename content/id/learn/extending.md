# Memperpanjang

Flight dirancang untuk menjadi framework yang dapat diperluas. Framework ini dilengkapi dengan sejumlah metode dan komponen standar, tetapi memungkinkan Anda untuk memetakan metode Anda sendiri, mendaftar kelas Anda sendiri, atau bahkan menggantikan kelas dan metode yang ada.

Jika Anda mencari DIC (Dependency Injection Container), silakan menuju halaman
[Dependency Injection Container](dependency-injection-container).

## Memetakan Metode

Untuk memetakan metode kustom sederhana Anda sendiri, Anda menggunakan fungsi `map`:

```php
// Pemetaan metode Anda
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// Memanggil metode kustom Anda
Flight::hello('Bob');
```

Meskipun dimungkinkan untuk membuat metode kustom sederhana, disarankan untuk hanya membuat fungsi standar di PHP. Ini memiliki autocomplete di IDE dan lebih mudah dibaca. Padanan dari kode di atas adalah:

```php
function hello(string $name) {
  echo "hello $name!";
}

hello('Bob');
```

Ini lebih sering digunakan ketika Anda perlu melewatkan variabel ke dalam metode Anda untuk mendapatkan nilai yang diharapkan. Menggunakan metode `register()` seperti di bawah ini lebih untuk melewatkan konfigurasi dan kemudian memanggil kelas yang telah dipra-konfigurasi.

## Mendaftar Kelas

Untuk mendaftarkan kelas Anda sendiri dan mengonfigurasinya, Anda menggunakan fungsi `register`:

```php
// Mendaftar kelas Anda
Flight::register('user', User::class);

// Mendapatkan instance dari kelas Anda
$user = Flight::user();
```

Metode register juga memungkinkan Anda untuk melewatkan parameter ke konstruktor kelas Anda. Jadi ketika Anda memuat kelas kustom Anda, itu akan datang dengan pra-inisialisasi. Anda dapat mendefinisikan parameter konstruktor dengan melewatkan array tambahan. Berikut adalah contoh memuat koneksi database:

```php
// Mendaftar kelas dengan parameter konstruktor
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Mendapatkan instance dari kelas Anda
// Ini akan membuat objek dengan parameter yang telah ditentukan
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// dan jika Anda memerlukannya nanti dalam kode Anda, Anda hanya perlu memanggil metode yang sama sekali lagi
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Jika Anda melewatkan parameter callback tambahan, itu akan dieksekusi segera setelah konstruktor kelas. Ini memungkinkan Anda untuk melakukan prosedur persiapan untuk objek baru Anda. Fungsi callback mengambil satu parameter, sebuah instance dari objek baru.

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

Secara default, setiap kali Anda memuat kelas Anda, Anda akan mendapatkan instance bersama. Untuk mendapatkan instance baru dari kelas, cukup melewatkan `false` sebagai parameter:

```php
// Instance bersama dari kelas
$shared = Flight::db();

// Instance baru dari kelas
$new = Flight::db(false);
```

Ingat bahwa metode yang dipetakan memiliki prioritas lebih tinggi daripada kelas yang terdaftar. Jika Anda mendeklarasikan keduanya dengan nama yang sama, hanya metode yang dipetakan yang akan dipanggil.

## Mengganti Metode Framework

Flight memungkinkan Anda untuk mengganti fungsionalitas bawaannya sesuai kebutuhan Anda sendiri, tanpa harus memodifikasi kode apa pun. Anda dapat melihat semua metode yang dapat Anda ganti [di sini](/learn/api).

Sebagai contoh, ketika Flight tidak dapat mencocokkan URL dengan rute, ia memanggil metode `notFound` yang mengirimkan respons `HTTP 404` umum. Anda dapat mengganti perilaku ini dengan menggunakan metode `map`:

```php
Flight::map('notFound', function() {
  // Tampilkan halaman 404 kustom
  include 'errors/404.html';
});
```

Flight juga memungkinkan Anda untuk mengganti komponen inti dari framework. Sebagai contoh, Anda dapat mengganti kelas Router standar dengan kelas kustom Anda sendiri:

```php
// Mendaftar kelas kustom Anda
Flight::register('router', MyRouter::class);

// Ketika Flight memuat instance Router, itu akan memuat kelas Anda
$myrouter = Flight::router();
```

Namun, metode-framework seperti `map` dan `register` tidak dapat diganti. Anda akan mendapatkan kesalahan jika mencoba melakukannya.