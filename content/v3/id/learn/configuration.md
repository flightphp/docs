# Konfigurasi

## Ringkasan

Flight menyediakan cara sederhana untuk mengonfigurasi berbagai aspek framework agar sesuai dengan kebutuhan aplikasi Anda. Beberapa disetel secara default, tetapi Anda dapat menimpanya sesuai kebutuhan. Anda juga dapat menyetel variabel Anda sendiri untuk digunakan di seluruh aplikasi Anda.

Konfigurasi yang jelas dan berlapis (default file + rahasia lingkungan) juga membantu [alat pengkodean AI](/learn/ai): agen mempelajari satu tempat untuk literal dan satu tempat untuk rahasia, alih-alih mengarang pembacaan `$_ENV` di dalam controller.

## Pemahaman

Anda dapat menyesuaikan perilaku tertentu Flight dengan menyetel nilai konfigurasi
melalui metode `set`.

```php
Flight::set('flight.log_errors', true);
```

Dalam aplikasi terstruktur (termasuk [skeleton](https://github.com/flightphp/skeleton)), Anda biasanya memuat pengaturan proyek dari `app/config/config.php` lalu menerapkan key yang relevan ke Engine (misalnya `flight.base_url`, `flight.views.path`). Anda juga dapat menyuntikkan objek config kecil ke controller alih-alih membaca global di mana-mana—lebih bersahabat untuk pengujian dan untuk agen yang mengikuti `AGENTS.md`.

## Penggunaan Dasar

### Opsi Konfigurasi Flight

Berikut adalah daftar semua pengaturan konfigurasi yang tersedia:

- **flight.base_url** `?string` - Timpa base url permintaan jika Flight berjalan di subdirektori. (default: null)
- **flight.case_sensitive** `bool` - Pencocokan peka huruf besar-kecil untuk URL. (default: false)
- **flight.handle_errors** `bool` - Izinkan Flight menangani semua error secara internal. (default: true)
  - Jika Anda ingin Flight menangani error alih-alih perilaku PHP default, ini harus true.
  - Jika Anda memiliki [Tracy](/awesome-plugins/tracy) terpasang, Anda ingin menyetel ini ke false agar Tracy dapat menangani error.
  - Jika Anda memiliki plugin [APM](/awesome-plugins/apm) terpasang, Anda ingin menyetel ini ke true agar APM dapat mencatat error.
- **flight.log_errors** `bool` - Catat error ke file log error web server. (default: false)
  - Jika Anda memiliki [Tracy](/awesome-plugins/tracy) terpasang, Tracy akan mencatat error berdasarkan konfigurasi Tracy, bukan konfigurasi ini.
- **flight.debug** `bool` - Menampilkan informasi error terperinci (pesan exception, kode, dan stack trace) di browser saat terjadi error. (default: false)
  - **Jangan pernah aktifkan ini di produksi** — ini membocorkan detail internal aplikasi. Gunakan hanya untuk pengembangan lokal atau staging.
  - Saat `false`, sebagai gantinya ditampilkan `500 Internal Server Error` generik. Padukan dengan `flight.log_errors` untuk menangkap error di sisi server.
- **flight.allow_method_override** `bool` - Izinkan metode HTTP ditimpa melalui header permintaan `X-HTTP-Method-Override` atau field `_method` di body POST. (default: true)
  - **Menyetel ini ke `false` direkomendasikan** untuk aplikasi yang tidak memerlukan pemalsuan metode berbasis formulir HTML, karena ini mencegah klien memalsukan permintaan `DELETE` atau `PUT` melalui formulir POST standar.
  - Lihat [Keamanan](/learn/security#flight-configuration-hardening) untuk detail selengkapnya.
- **flight.views.path** `string` - Direktori yang berisi file template view. (default: ./views)
- **flight.views.extension** `string` - Ekstensi file template view. (default: `.php`; skeleton resmi menyetel ini ke `.twig` saat menggunakan Twig)
- **flight.views.restrict_to_path** `bool` - Saat `true`, `View` native Flight hanya menerima file template yang terselesaikan di dalam `flight.views.path`. (default: `false`). **Aktifkan ini** untuk aplikasi yang menggunakan view native. Lihat [Keamanan](/learn/security#flightviewsrestrict_to_path).
- **flight.content_length** `bool` - Setel header `Content-Length`. (default: true)
  - Jika Anda menggunakan [Tracy](/awesome-plugins/tracy), ini harus disetel ke false agar Tracy dapat merender dengan benar.
- **flight.v2.output_buffering** `bool` - Gunakan output buffering lama. Lihat [migrasi ke v3](migrating-to-v3). (default: false)

### Konfigurasi Loader

Ada tambahan pengaturan konfigurasi lain untuk loader. Ini akan memungkinkan Anda
memuat kelas secara otomatis dengan `_` di nama kelas.

```php
// Aktifkan pemuatan kelas dengan garis bawah
// Defaultnya true
Loader::$v2ClassLoading = false;
```

Ingat bahwa [autoloading](/learn/autoloading) juga bergantung pada **kecocokan huruf besar-kecil folder** dengan namespace Anda—terutama dengan tata letak `App\` + `app/Controller/` milik skeleton.

### Konfigurasi proyek dan `.env` (pola skeleton)

Inti Flight tidak memerlukan file `.env`. Banyak aplikasi hanya menggunakan array konfigurasi PHP. Skeleton resmi melapisi konfigurasi sehingga rahasia tetap di luar git sementara Runway masih dapat menulis ulang konfigurasi **literal** dengan aman:

1. **`.env` / lingkungan nyata** — rahasia dan override deploy (diabaikan git).
2. **`app/config/config.php`** — default array PHP literal (disalin dari `config_sample.php`). Sebaiknya **tidak ada** ekspresi `$_ENV[...]` di dalam file ini: alat seperti `runway config:set` dapat menulis ulangnya sebagai nilai statis dan bisa menanamkan rahasia ke dalam file.
3. **Gabungkan saat bootstrap** — env menang untuk key yang dipetakan; kode aplikasi membaca objek config atau `$app->get()`, bukan `$_ENV` di controller.

Contoh bentuk `config_sample.php` / `config.php` (disederhanakan):

```php
<?php
// Hanya literal — rahasia sebaiknya berada di .env untuk alur kerja skeleton
return [
	'app' => [
		'env' => 'development',
		'debug' => true,
		'base_url' => '/',
		'timezone' => 'UTC',
	],
	'database' => [
		'driver' => 'sqlite', // atau mysql, atau '' untuk menonaktifkan
		'host' => 'localhost',
		'dbname' => '',
		'user' => '',
		'password' => '',
		'file_path' => __DIR__ . '/../../database.sqlite',
	],
	// ...
];
```

```bash
# .env.example → .env (skeleton)
APP_ENV=development
APP_DEBUG=true
FLIGHT_BASE_URL=/
DB_DRIVER=sqlite
# DB_PASSWORD=...
```

Pemisahan ini disengaja untuk [proyek yang ramah AI](/learn/ai): instruksi dapat mengatakan "default di `config.php`, rahasia di `.env`, suntikkan Config / Engine—jangan pernah mengarang akses env di controller." Aplikasi yang sudah ada dapat mengabaikan `.env` sepenuhnya dan menyimpan satu file konfigurasi.

### Variabel

Flight memungkinkan Anda menyimpan variabel sehingga dapat digunakan di mana saja di aplikasi Anda.

```php
// Simpan variabel Anda
Flight::set('id', 123);

// Di tempat lain di aplikasi Anda
$id = Flight::get('id');
```
Untuk melihat apakah suatu variabel telah disetel, Anda dapat melakukan:

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

> **Catatan:** Hanya karena Anda dapat menyetel variabel bukan berarti Anda harus melakukannya. Gunakan fitur ini secara hemat. Alasannya adalah apa pun yang disimpan di sini menjadi variabel global. Variabel global buruk karena dapat diubah dari mana saja di aplikasi Anda, sehingga sulit melacak bug. Selain itu, ini dapat mempersulit hal-hal seperti [unit testing](/guides/unit-testing). Lebih baik gunakan constructor injection (seperti pada setup skeleton + Dice) untuk layanan dan konfigurasi yang dibutuhkan controller.

### Error dan Exception

Semua error dan exception ditangkap oleh Flight dan diteruskan ke metode `error`.
Jika `flight.handle_errors` disetel ke true.

Perilaku default adalah mengirim respons `HTTP 500 Internal Server Error` generik
dengan beberapa informasi error.

Anda dapat [menimpa](/learn/extending) perilaku ini untuk kebutuhan Anda sendiri:

```php
Flight::map('error', function (Throwable $error) {
  // Tangani error
  echo $error->getTraceAsString();
});
```

Secara default error tidak dicatat ke web server. Anda dapat mengaktifkannya dengan
mengubah konfigurasi:

```php
Flight::set('flight.log_errors', true);
```

#### 404 Not Found

Saat URL tidak dapat ditemukan, Flight memanggil metode `notFound`. Perilaku default
adalah mengirim respons `HTTP 404 Not Found` dengan pesan sederhana.

Anda dapat [menimpa](/learn/extending) perilaku ini untuk kebutuhan Anda sendiri:

```php
Flight::map('notFound', function () {
  // Tangani tidak ditemukan
});
```

## Lihat Juga
- [Instalasi](/install) - Konfigurasi skeleton, `.env`, dan tata letak bootstrap.
- [Autoloading](/learn/autoloading) - Namespace dan huruf besar-kecil folder.
- [Memperluas Flight](/learn/extending) - Cara memperluas dan menyesuaikan fungsionalitas inti Flight.
- [Unit Testing](/guides/unit-testing) - Cara menulis unit test untuk aplikasi Flight Anda.
- [AI & Pengalaman Pengembang](/learn/ai) - `AGENTS.md` dan instruksi proyek yang konsisten.
- [Tracy](/awesome-plugins/tracy) - Plugin untuk penanganan error dan debugging lanjutan.
- [Ekstensi Tracy](/awesome-plugins/tracy_extensions) - Ekstensi untuk mengintegrasikan Tracy dengan Flight.
- [APM](/awesome-plugins/apm) - Plugin untuk pemantauan performa aplikasi dan pelacakan error.
- [Keamanan](/learn/security) - Flag hardening dan penanganan rahasia.

## Pemecahan Masalah
- Jika Anda mengalami masalah dalam mengetahui semua nilai konfigurasi Anda, Anda dapat melakukan `var_dump(Flight::get());`
- Jika Runway atau alat deploy menulis ulang `config.php`, pastikan rahasia tidak ter-commit—simpan di `.env` atau lingkungan nyata saat menggunakan pola skeleton.

## Catatan Perubahan
- Dokumentasi – Mencatat `flight.views.restrict_to_path` di samping pengaturan view path.
- Dokumentasi – Mendokumentasikan konfigurasi bergaya skeleton / pelapisan `.env` dan default ekstensi view Twig untuk proyek baru.
- v3.18.1 - Menambahkan opsi konfigurasi `flight.debug` dan `flight.allow_method_override`.
- v3.5.0 - Menambahkan konfigurasi untuk `flight.v2.output_buffering` guna mendukung perilaku output buffering lama.
- v2.0 - Konfigurasi inti ditambahkan.