# Migrasi ke v3

Kompatibilitas mundur sebagian besar telah dipertahankan, tetapi ada beberapa perubahan yang harus Anda ketahui saat 
migrasi dari v2 ke v3. Ada beberapa perubahan yang bertentangan terlalu banyak dengan pola desain sehingga beberapa penyesuaian harus dilakukan.

## Perilaku Penyanggaan Output

_v3.5.0_

[Penyanggaan output](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) adalah proses di mana output 
yang dihasilkan oleh skrip PHP disimpan dalam penyangga (internal ke PHP) sebelum dikirim ke klien. Ini memungkinkan Anda untuk memodifikasi 
output sebelum dikirim ke klien.

Dalam aplikasi MVC, Controller adalah "manajer" dan mengelola apa yang dilakukan oleh view. Memiliki output yang dihasilkan di luar 
controller (atau dalam kasus Flight terkadang fungsi anonim) merusak pola MVC. Perubahan ini dilakukan untuk lebih selaras dengan 
pola MVC dan membuat framework lebih dapat diprediksi serta lebih mudah digunakan.

Di v2, penyanggaan output ditangani dengan cara yang tidak secara konsisten menutup penyangga outputnya sendiri, yang membuat 
[pengujian unit](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) 
dan [streaming](https://github.com/flightphp/core/issues/413) lebih sulit. Bagi sebagian besar pengguna, perubahan ini mungkin tidak 
memengaruhi Anda secara aktual. Namun, jika Anda mencetak konten di luar callable dan controller (misalnya dalam hook), kemungkinan 
Anda akan mengalami masalah. Mencetak konten dalam hook, dan sebelum framework benar-benar dieksekusi mungkin pernah berhasil 
di masa lalu, tetapi tidak akan berhasil ke depannya.

### Di Mana Anda Mungkin Mengalami Masalah
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
	// ini sebenarnya akan baik-baik saja
	echo '<p>Kalimat Hello World ini disajikan oleh huruf "H"</p>';
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

Bisakah Anda tetap mempertahankan kode lama Anda apa adanya tanpa melakukan penulisan ulang agar kompatibel dengan v3? Ya, Anda bisa! Anda dapat mengaktifkan 
perilaku rendering v2 dengan mengatur opsi konfigurasi `flight.v2.output_buffering` menjadi `true`. Ini akan memungkinkan Anda untuk terus 
menggunakan perilaku rendering lama, tetapi disarankan untuk memperbaikinya ke depannya. Di v4 dari framework, ini akan dihapus.

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

## Perubahan Dispatcher

_v3.7.0_

Jika Anda secara langsung memanggil metode statis untuk `Dispatcher` seperti `Dispatcher::invokeMethod()`, `Dispatcher::execute()`, dll. 
Anda perlu memperbarui kode Anda agar tidak secara langsung memanggil metode-metode ini. `Dispatcher` telah diubah menjadi lebih berorientasi objek sehingga 
Container Injeksi Dependensi dapat digunakan dengan lebih mudah. Jika Anda perlu memanggil metode mirip dengan cara Dispatcher, Anda 
dapat secara manual menggunakan sesuatu seperti `$result = $class->$method(...$params);` atau `call_user_func_array()` sebagai gantinya.

## Perubahan `halt()` `stop()` `redirect()` dan `error()`

_v3.10.0_

Perilaku default sebelum 3.10.0 adalah membersihkan baik header maupun body respons. Ini diubah menjadi hanya membersihkan body respons. 
Jika Anda perlu membersihkan header juga, Anda dapat menggunakan `Flight::response()->clear()`.