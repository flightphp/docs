# Migrasi ke v3

Kompatibilitas ke belakang sebagian besar telah dipertahankan, tetapi ada beberapa perubahan yang harus Anda perhatikan saat 
migrasi dari v2 ke v3.

## Perilaku Buffering Output (3.5.0)

[Buffering output](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) adalah proses di mana output 
yang dihasilkan oleh skrip PHP disimpan dalam buffer (internal di PHP) sebelum dikirim ke klien. Ini memungkinkan Anda untuk memodifikasi 
output sebelum dikirim ke klien.

Dalam aplikasi MVC, Controller adalah "pengelola" dan mengatur apa yang dilakukan tampilan. Memiliki output yang dihasilkan di luar 
controller (atau dalam kasus Flight kadang-kadang fungsi anonim) merusak pola MVC. Perubahan ini bertujuan untuk lebih selaras 
dengan pola MVC dan untuk membuat framework lebih dapat diprediksi dan lebih mudah digunakan.

Di v2, buffering output ditangani sedemikian rupa sehingga tidak secara konsisten menutup buffer output sendiri dan ini membuat 
[unit testing](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) 
dan [streaming](https://github.com/flightphp/core/issues/413) menjadi lebih sulit. Untuk sebagian besar pengguna, perubahan ini mungkin tidak 
benar-benar memengaruhi Anda. Namun jika Anda mencetak konten di luar callable dan controller (misalnya dalam hook), Anda kemungkinan 
akan menghadapi masalah. Mencetak konten dalam hook, dan sebelum framework benar-benar mengeksekusi mungkin berfungsi di 
masa lalu, tetapi tidak akan berfungsi ke depan.

### Di mana Anda mungkin mengalami masalah
```php
// index.php
require 'vendor/autoload.php';

// hanya contoh
define('START_TIME', microtime(true));

function hello() {
	echo 'Hello World';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// ini sebenarnya baik-baik saja
	echo '<p>Frasa Hello World ini dibawakan kepada Anda oleh huruf "H"</p>';
});

Flight::before('start', function(){
	// hal-hal seperti ini akan menyebabkan kesalahan
	echo '<html><head><title>Halaman Saya</title></head><body>';
});

Flight::route('/', function(){
	// ini sebenarnya baik-baik saja
	echo 'Hello World';

	// Ini juga seharusnya baik-baik saja
	Flight::hello();
});

Flight::after('start', function(){
	// ini akan menyebabkan kesalahan
	echo '<div>Halaman Anda dimuat dalam '.(microtime(true) - START_TIME).' detik</div></body></html>';
});
```

### Mengaktifkan Perilaku Rendering v2

Apakah Anda masih bisa menjaga kode lama Anda seperti semula tanpa melakukan penulisan ulang untuk membuatnya berfungsi dengan v3? Ya, Anda bisa! Anda dapat mengaktifkan 
perilaku rendering v2 dengan mengatur opsi konfigurasi `flight.v2.output_buffering` menjadi `true`. Ini akan memungkinkan Anda untuk terus menggunakan 
perilaku rendering lama, tetapi disarankan untuk memperbaikinya ke depan. Di v4 dari framework, ini akan dihapus.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// Sekarang ini akan baik-baik saja
	echo '<html><head><title>Halaman Saya</title></head><body>';
});

// lebih banyak kode 
```

## Perubahan Dispatcher (3.7.0)

Jika Anda langsung memanggil metode statis untuk `Dispatcher` seperti `Dispatcher::invokeMethod()`, `Dispatcher::execute()`, dll. 
Anda perlu memperbarui kode Anda agar tidak langsung memanggil metode ini. `Dispatcher` telah diubah menjadi lebih berorientasi objek sehingga 
Container Penyuntikan Ketergantungan dapat digunakan dengan cara yang lebih mudah. Jika Anda perlu memanggil metode mirip seperti yang dilakukan Dispatcher, Anda 
dapat menggunakan sesuatu seperti `$result = $class->$method(...$params);` atau `call_user_func_array()` sebagai gantinya.

## Perubahan `halt()` `stop()` `redirect()` dan `error()` (3.10.0)

Perilaku default sebelum 3.10.0 adalah untuk menghapus baik header maupun body respons. Ini diubah hanya untuk menghapus body respons. 
Jika Anda perlu menghapus header juga, Anda bisa menggunakan `Flight::response()->clear()`.