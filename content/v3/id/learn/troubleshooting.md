# Pemecahan Masalah

Halaman ini akan membantu Anda memecahkan masalah umum yang mungkin Anda temui saat menggunakan Flight.

## Masalah Umum

### 404 Tidak Ditemukan atau Perilaku Rute Tak Terduga

Jika Anda melihat kesalahan 404 Tidak Ditemukan (tetapi Anda bersumpah bahwa itu benar-benar ada dan bukan kesalahan ketik) ini sebenarnya bisa menjadi masalah 
dengan Anda yang mengembalikan nilai di titik akhir rute Anda alih-alih hanya mencetaknya. Alasan untuk ini adalah disengaja tetapi bisa mengganggu beberapa pengembang.

```php

Flight::route('/hello', function(){
	// Ini mungkin menyebabkan kesalahan 404 Tidak Ditemukan
	return 'Hello World';
});

// Apa yang mungkin Anda inginkan
Flight::route('/hello', function(){
	echo 'Hello World';
});

```

Alasan untuk ini adalah karena mekanisme khusus yang dibangun ke dalam router yang menangani keluaran pengembalian sebagai sinyal untuk "melanjutkan ke rute berikutnya". 
Anda dapat melihat perilaku ini didokumentasikan di bagian [Routing](/learn/routing#passing).

### Kelas Tidak Ditemukan (autoloading tidak bekerja)

Ada beberapa alasan mengapa ini tidak terjadi. Di bawah ini adalah beberapa contoh tetapi pastikan Anda juga memeriksa bagian [autoloading](/learn/autoloading).

#### Nama File yang Salah
Yang paling umum adalah bahwa nama kelas tidak cocok dengan nama file.

Jika Anda memiliki kelas bernama `MyClass` maka file tersebut harus dinamai `MyClass.php`. Jika Anda memiliki kelas bernama `MyClass` dan file tersebut dinamai `myclass.php`
maka autoloader tidak akan dapat menemukannya.

#### Namespace yang Salah
Jika Anda menggunakan namespace, maka namespace harus cocok dengan struktur direktori.

```php
// kode

// jika MyController Anda berada di direktori app/controllers dan memiliki namespace
// ini tidak akan berfungsi.
Flight::route('/hello', 'MyController->hello');

// Anda perlu memilih salah satu dari opsi ini
Flight::route('/hello', 'app\controllers\MyController->hello');
// atau jika Anda memiliki pernyataan use di atas

use app\controllers\MyController;

Flight::route('/hello', [ MyController::class, 'hello' ]);
// juga bisa ditulis
Flight::route('/hello', MyController::class.'->hello');
// juga...
Flight::route('/hello', [ 'app\controllers\MyController', 'hello' ]);
```

#### `path()` tidak didefinisikan

Dalam aplikasi kerangka kerja, ini didefinisikan di dalam file `config.php`, tetapi agar kelas Anda dapat ditemukan, Anda perlu memastikan bahwa metode `path()`
didefinisikan (kemungkinan ke root direktori Anda) sebelum Anda mencoba menggunakannya.

```php

// Tambahkan path ke autoloader
Flight::path(__DIR__.'/../');

```