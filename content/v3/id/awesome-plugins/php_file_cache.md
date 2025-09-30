# flightphp/cache

Kelas penangkapan file PHP mandiri yang ringan, sederhana dan standalone, difork dari [Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache)

**Keunggulan** 
- Ringan, mandiri dan sederhana
- Semua kode dalam satu file - tidak ada driver yang tidak berguna.
- Aman - setiap file cache yang dihasilkan memiliki header PHP dengan die, membuat akses langsung tidak mungkin bahkan jika seseorang mengetahui jalur dan server Anda tidak dikonfigurasi dengan benar
- Didokumentasikan dengan baik dan diuji
- Menangani konkurensi dengan benar melalui flock
- Mendukung PHP 7.4+
- Gratis di bawah lisensi MIT

Situs dokumentasi ini menggunakan pustaka ini untuk menangkap setiap halaman!

Klik [di sini](https://github.com/flightphp/cache) untuk melihat kode.

## Instalasi

Instal melalui composer:

```bash
composer require flightphp/cache
```

## Penggunaan

Penggunaan cukup sederhana. Ini menyimpan file cache di direktori cache.

```php
use flight\Cache;

$app = Flight::app();

// Anda melewatkan direktori tempat cache akan disimpan ke dalam konstruktor
$app->register('cache', Cache::class, [ __DIR__ . '/../cache/' ], function(Cache $cache) {

	// Ini memastikan bahwa cache hanya digunakan saat dalam mode produksi
	// ENVIRONMENT adalah konstanta yang disetel dalam file bootstrap Anda atau di tempat lain dalam aplikasi Anda
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

### Mendapatkan Nilai Cache

Anda menggunakan metode `get()` untuk mendapatkan nilai cache. Jika Anda ingin metode kemudahan yang akan menyegarkan cache jika sudah kedaluwarsa, Anda bisa menggunakan `refreshIfExpired()`.

```php

// Dapatkan instance cache
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // return data to be cached
}, 10); // 10 detik

// atau
$data = $cache->get('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->set('simple-cache-test', $data, 10); // 10 detik
}
```

### Menyimpan Nilai Cache

Anda menggunakan metode `set()` untuk menyimpan nilai di cache.

```php
Flight::cache()->set('simple-cache-test', 'my cached data', 10); // 10 detik
```

### Menghapus Nilai Cache

Anda menggunakan metode `delete()` untuk menghapus nilai di cache.

```php
Flight::cache()->delete('simple-cache-test');
```

### Memeriksa Apakah Nilai Cache Ada

Anda menggunakan metode `exists()` untuk memeriksa apakah nilai ada di cache.

```php
if(Flight::cache()->exists('simple-cache-test')) {
	// lakukan sesuatu
}
```

### Membersihkan Cache
Anda menggunakan metode `flush()` untuk membersihkan seluruh cache.

```php
Flight::cache()->flush();
```

### Mengambil metadata dengan cache

Jika Anda ingin mengambil timestamp dan metadata lainnya tentang entri cache, pastikan Anda melewatkan `true` sebagai parameter yang benar.

```php
$data = $cache->refreshIfExpired("simple-cache-meta-test", function () {
    echo "Refreshing data!" . PHP_EOL;
    return date("H:i:s"); // return data to be cached
}, 10, true); // true = return with metadata
// atau
$data = $cache->get("simple-cache-meta-test", true); // true = return with metadata

/*
Contoh item cache yang diambil dengan metadata:
{
    "time":1511667506, <-- save unix timestamp
    "expire":10,       <-- expire time in seconds
    "data":"04:38:26", <-- unserialized data
    "permanent":false
}

Menggunakan metadata, kita bisa, misalnya, menghitung kapan item disimpan atau kapan kedaluwarsa
Kita juga bisa mengakses data itu sendiri dengan kunci "data"
*/

$expiresin = ($data["time"] + $data["expire"]) - time(); // get unix timestamp when data expires and subtract current timestamp from it
$cacheddate = $data["data"]; // we access the data itself with the "data" key

echo "Latest cache save: $cacheddate, expires in $expiresin seconds";
```

## Dokumentasi

Kunjungi [https://github.com/flightphp/cache](https://github.com/flightphp/cache) untuk melihat kode. Pastikan Anda melihat folder [examples](https://github.com/flightphp/cache/tree/master/examples) untuk cara tambahan menggunakan cache.