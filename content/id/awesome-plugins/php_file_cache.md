# Wruczek/PHP-File-Cache

Kelas caching in-file PHP yang ringan, sederhana, dan mandiri

**Keuntungan** 
- Ringan, mandiri, dan sederhana
- Semua kode dalam satu file - tidak ada driver yang tidak berguna.
- Aman - setiap file cache yang dihasilkan memiliki header php dengan die, menjadikan akses langsung tidak mungkin meskipun seseorang mengetahui jalannya dan server Anda tidak dikonfigurasi dengan benar
- Didokumentasikan dengan baik dan diuji
- Menangani concurrency dengan benar melalui flock
- Mendukung PHP 5.4.0 - 7.1+
- Gratis di bawah lisensi MIT

Klik [di sini](https://github.com/Wruczek/PHP-File-Cache) untuk melihat kode.

## Instalasi

Instal melalui composer:

```bash
composer require wruczek/php-file-cache
```

## Penggunaan

Penggunaan cukup sederhana.

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// Anda mengirimkan direktori tempat cache akan disimpan ke dalam konstruktor
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// Ini memastikan bahwa cache hanya digunakan saat dalam mode produksi
	// ENVIRONMENT adalah sebuah konstanta yang diatur dalam file bootstrap Anda atau di tempat lain dalam aplikasi Anda
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Kemudian Anda dapat menggunakannya dalam kode Anda seperti ini:

```php

// Ambil instance cache
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // mengembalikan data untuk dicache
}, 10); // 10 detik

// atau
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 detik
}
```

## Dokumentasi

Kunjungi [https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache) untuk dokumentasi lengkap dan pastikan Anda melihat folder [contoh](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples).