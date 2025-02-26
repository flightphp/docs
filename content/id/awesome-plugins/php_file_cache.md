# flightphp/cache

Kelas caching dalam file PHP yang ringan, sederhana, dan mandiri

**Keuntungan**
- Ringan, mandiri, dan sederhana
- Semua kode dalam satu file - tanpa driver yang tidak berguna.
- Aman - setiap file cache yang dihasilkan memiliki header php dengan die, membuat akses langsung tidak mungkin meskipun seseorang mengetahui jalur dan server Anda tidak dikonfigurasi dengan baik
- Didokumentasikan dengan baik dan diuji
- Menangani konkruensi dengan benar melalui flock
- Mendukung PHP 7.4+
- Gratis di bawah lisensi MIT

Situs dokumentasi ini menggunakan pustaka ini untuk menyimpan cache setiap halaman!

Klik [di sini](https://github.com/flightphp/cache) untuk melihat kodenya.

## Instalasi

Instal melalui composer:

```bash
composer require flightphp/cache
```

## Penggunaan

Penggunaannya cukup sederhana. Ini menyimpan file cache di direktori cache.

```php
use flight\Cache;

$app = Flight::app();

// Anda mengoper direktori tempat cache akan disimpan ke dalam konstruktor
$app->register('cache', Cache::class, [ __DIR__ . '/../cache/' ], function(Cache $cache) {

	// Ini memastikan bahwa cache hanya digunakan saat dalam mode produksi
	// ENVIRONMENT adalah konstanta yang diatur dalam file bootstrap Anda atau di tempat lain dalam aplikasi Anda
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Kemudian Anda bisa menggunakannya dalam kode Anda seperti ini:

```php

// Dapatkan instance cache
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // mengembalikan data untuk disimpan di cache
}, 10); // 10 detik

// atau
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 detik
}
```

## Dokumentasi

Kunjungi [https://github.com/flightphp/cache](https://github.com/flightphp/cache) untuk dokumentasi lengkap dan pastikan Anda melihat folder [contoh](https://github.com/flightphp/cache/tree/master/examples).