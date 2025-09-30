# Konfigurasi

## Gambaran Umum 

Flight menyediakan cara sederhana untuk mengonfigurasi berbagai aspek framework agar sesuai dengan kebutuhan aplikasi Anda. Beberapa diatur secara default, tetapi Anda dapat menimpa pengaturan tersebut sesuai kebutuhan. Anda juga dapat mengatur variabel sendiri untuk digunakan di seluruh aplikasi Anda.

## Pemahaman

Anda dapat menyesuaikan perilaku tertentu dari Flight dengan mengatur nilai konfigurasi
melalui metode `set`.

```php
Flight::set('flight.log_errors', true);
```

Dalam file `app/config/config.php`, Anda dapat melihat semua variabel konfigurasi default yang tersedia untuk Anda.

## Penggunaan Dasar

### Opsi Konfigurasi Flight

Berikut adalah daftar semua pengaturan konfigurasi yang tersedia:

- **flight.base_url** `?string` - Timpa URL dasar dari permintaan jika Flight berjalan di subdirektori. (default: null)
- **flight.case_sensitive** `bool` - Pencocokan sensitif huruf besar-kecil untuk URL. (default: false)
- **flight.handle_errors** `bool` - Izinkan Flight untuk menangani semua kesalahan secara internal. (default: true)
  - Jika Anda ingin Flight menangani kesalahan alih-alih perilaku PHP default, ini perlu diatur ke true.
  - Jika Anda memiliki [Tracy](/awesome-plugins/tracy) yang terinstal, Anda ingin mengatur ini ke false agar Tracy dapat menangani kesalahan.
  - Jika Anda memiliki plugin [APM](/awesome-plugins/apm) yang terinstal, Anda ingin mengatur ini ke true agar APM dapat mencatat kesalahan.
- **flight.log_errors** `bool` - Catat kesalahan ke file log kesalahan server web. (default: false)
  - Jika Anda memiliki [Tracy](/awesome-plugins/tracy) yang terinstal, Tracy akan mencatat kesalahan berdasarkan konfigurasi Tracy, bukan konfigurasi ini.
- **flight.views.path** `string` - Direktori yang berisi file template tampilan. (default: ./views)
- **flight.views.extension** `string` - Ekstensi file template tampilan. (default: .php)
- **flight.content_length** `bool` - Atur header `Content-Length`. (default: true)
  - Jika Anda menggunakan [Tracy](/awesome-plugins/tracy), ini perlu diatur ke false agar Tracy dapat dirender dengan benar.
- **flight.v2.output_buffering** `bool` - Gunakan buffering output legacy. Lihat [migrating to v3](migrating-to-v3). (default: false)

### Konfigurasi Loader

Ada juga pengaturan konfigurasi lain untuk loader. Ini akan memungkinkan Anda 
untuk memuat kelas secara otomatis dengan `_` dalam nama kelas.

```php
// Aktifkan pemuatan kelas dengan underscore
// Defaulted to true
Loader::$v2ClassLoading = false;
```

### Variabel

Flight memungkinkan Anda menyimpan variabel agar dapat digunakan di mana saja dalam aplikasi Anda.

```php
// Simpan variabel Anda
Flight::set('id', 123);

// Di tempat lain dalam aplikasi Anda
$id = Flight::get('id');
```
Untuk melihat apakah variabel telah diatur, Anda dapat melakukan:

```php
if (Flight::has('id')) {
  // Lakukan sesuatu
}
```

Anda dapat menghapus variabel dengan melakukan:

```php
// Hapus variabel id
Flight::clear('id');

// Hapus semua variabel
Flight::clear();
```

> **Catatan:** Hanya karena Anda dapat mengatur variabel tidak berarti Anda harus melakukannya. Gunakan fitur ini secara hemat. Alasan mengapa adalah bahwa apa pun yang disimpan di sini menjadi variabel global. Variabel global buruk karena dapat diubah dari mana saja dalam aplikasi Anda, membuat sulit untuk melacak bug. Selain itu, ini dapat mempersulit hal-hal seperti [unit testing](/guides/unit-testing).

### Kesalahan dan Pengecualian

Semua kesalahan dan pengecualian ditangkap oleh Flight dan diteruskan ke metode `error`.
jika `flight.handle_errors` diatur ke true.

Perilaku default adalah mengirim respons `HTTP 500 Internal Server Error`
umum dengan beberapa informasi kesalahan.

Anda dapat [menimpa](/learn/extending) perilaku ini untuk kebutuhan Anda sendiri:

```php
Flight::map('error', function (Throwable $error) {
  // Tangani kesalahan
  echo $error->getTraceAsString();
});
```

Secara default, kesalahan tidak dicatat ke server web. Anda dapat mengaktifkan ini dengan
mengubah konfigurasi:

```php
Flight::set('flight.log_errors', true);
```

#### 404 Tidak Ditemukan

Ketika URL tidak dapat ditemukan, Flight memanggil metode `notFound`. Perilaku
default adalah mengirim respons `HTTP 404 Not Found` dengan pesan sederhana.

Anda dapat [menimpa](/learn/extending) perilaku ini untuk kebutuhan Anda sendiri:

```php
Flight::map('notFound', function () {
  // Tangani tidak ditemukan
});
```

## Lihat Juga
- [Extending Flight](/learn/extending) - Cara memperluas dan menyesuaikan fungsionalitas inti Flight.
- [Unit Testing](/guides/unit-testing) - Cara menulis unit test untuk aplikasi Flight Anda.
- [Tracy](/awesome-plugins/tracy) - Plugin untuk penanganan kesalahan lanjutan dan debugging.
- [Tracy Extensions](/awesome-plugins/tracy_extensions) - Ekstensi untuk mengintegrasikan Tracy dengan Flight.
- [APM](/awesome-plugins/apm) - Plugin untuk pemantauan kinerja aplikasi dan pelacakan kesalahan.

## Pemecahan Masalah
- Jika Anda mengalami masalah untuk mengetahui semua nilai konfigurasi Anda, Anda dapat melakukan `var_dump(Flight::get());`

## Changelog
- v3.5.0 - Ditambahkan konfigurasi untuk `flight.v2.output_buffering` untuk mendukung perilaku buffering output legacy.
- v2.0 - Konfigurasi inti ditambahkan.