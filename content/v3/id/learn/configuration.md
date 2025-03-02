# Konfigurasi

Anda dapat menyesuaikan perilaku tertentu dari Flight dengan menetapkan nilai konfigurasi melalui metode `set`.

```php
Flight::set('flight.log_errors', true);
```

## Pengaturan Konfigurasi Tersedia

Berikut adalah daftar semua pengaturan konfigurasi yang tersedia:

- **flight.base_url** `?string` - Gantilah URL dasar dari permintaan. (default: null)
- **flight.case_sensitive** `bool` - Pencocokan sensitif terhadap huruf untuk URL. (default: false)
- **flight.handle_errors** `bool` - Izinkan Flight menangani semua kesalahan secara internal. (default: true)
- **flight.log_errors** `bool` - Catat kesalahan ke file log kesalahan server web. (default: false)
- **flight.views.path** `string` - Direktori yang berisi file template tampilan. (default: ./views)
- **flight.views.extension** `string` - Ekstensi file template tampilan. (default: .php)
- **flight.content_length** `bool` - Atur header `Content-Length`. (default: true)
- **flight.v2.output_buffering** `bool` - Gunakan buffering output tradisional. Lihat [migrasi ke v3](migrating-to-v3). (default: false)

## Konfigurasi Loader

Selain itu, ada pengaturan konfigurasi lain untuk loader. Ini akan memungkinkan Anda 
untuk memuat kelas secara otomatis dengan `_` dalam nama kelas.

```php
// Aktifkan pemuatan kelas dengan garis bawah
// Defaultnya adalah true
Loader::$v2ClassLoading = false;
```

## Variabel

Flight memungkinkan Anda untuk menyimpan variabel sehingga dapat digunakan di mana saja dalam aplikasi Anda.

```php
// Simpan variabel Anda
Flight::set('id', 123);

// Di tempat lain dalam aplikasi Anda
$id = Flight::get('id');
```
Untuk melihat apakah sebuah variabel telah disetel, Anda dapat melakukan:

```php
if (Flight::has('id')) {
  // Lakukan sesuatu
}
```

Anda dapat menghapus variabel dengan melakukan:

```php
// Menghapus variabel id
Flight::clear('id');

// Menghapus semua variabel
Flight::clear();
```

Flight juga menggunakan variabel untuk tujuan konfigurasi.

```php
Flight::set('flight.log_errors', true);
```

## Penanganan Kesalahan

### Kesalahan dan Pengecualian

Semua kesalahan dan pengecualian ditangkap oleh Flight dan diteruskan ke metode `error`.
Perilaku default adalah mengirimkan respons `HTTP 500 Internal Server Error`
dengan beberapa informasi kesalahan.

Anda dapat mengganti perilaku ini sesuai kebutuhan Anda:

```php
Flight::map('error', function (Throwable $error) {
  // Tangani kesalahan
  echo $error->getTraceAsString();
});
```

Secara default kesalahan tidak dicatat ke server web. Anda dapat mengaktifkan ini dengan
mengubah konfigurasi:

```php
Flight::set('flight.log_errors', true);
```

### Tidak Ditemukan

Ketika sebuah URL tidak dapat ditemukan, Flight memanggil metode `notFound`. Perilaku default
adalah mengirimkan respons `HTTP 404 Not Found` dengan pesan sederhana.

Anda dapat mengganti perilaku ini sesuai kebutuhan Anda:

```php
Flight::map('notFound', function () {
  // Tangani tidak ditemukan
});
```