# Konfigurasi

## Ringkasan

Flight menyediakan cara sederhana untuk mengonfigurasi berbagai aspek framework agar sesuai dengan kebutuhan aplikasi Anda. Beberapa disetel secara bawaan, tetapi Anda dapat menimpanya sesuai kebutuhan. Anda juga dapat menyetel variabel Anda sendiri untuk digunakan di seluruh aplikasi Anda.

Konfigurasi yang jelas dan berlapis (default file + secret environment) juga membantu [alat coding AI](/learn/ai): agen mempelajari satu tempat untuk literal dan satu tempat untuk secret, alih-alih mengarang pembacaan `$_ENV` di dalam controller.

## Pemahaman

Anda dapat menyesuaikan perilaku tertentu dari Flight dengan menyetel nilai konfigurasi melalui metode `set`.

```php
Flight::set('flight.log_errors', true);
```

Dalam aplikasi terstruktur (termasuk [skeleton](https://github.com/flightphp/skeleton)), Anda biasanya memuat pengaturan proyek dari `app/config/config.php` lalu menerapkan key yang relevan ke Engine (misalnya `flight.base_url`, `flight.views.path`). Anda juga dapat menyuntikkan objek config kecil ke controller alih-alih membaca global di mana-mana—lebih ramah untuk pengujian dan untuk agen yang mengikuti `AGENTS.md`.

## Penggunaan Dasar

### Opsi Konfigurasi Flight

Berikut adalah daftar semua pengaturan konfigurasi yang tersedia:

- **flight.base_url** `?string` - Menimpa URL dasar permintaan jika Flight berjalan di subdirektori. (bawaan: null)
- **flight.case_sensitive** `bool` - Pencocokan URL yang peka huruf besar/kecil. (bawaan: false)
- **flight.handle_errors** `bool` - Mengizinkan Flight menangani semua error secara internal. (bawaan: true)
  - Jika Anda ingin Flight menangani error alih-alih perilaku PHP bawaan, ini harus true.
  - Jika Anda memiliki [Tracy](/awesome-plugins/tracy) terpasang, Anda perlu menyetel ini ke false agar Tracy dapat menangani error.
  - Jika Anda memiliki plugin [APM](/awesome-plugins/apm) terpasang, Anda perlu menyetel ini ke true agar APM dapat mencatat error.
- **flight.log_errors** `bool` - Mencatat error ke file log error web server. (bawaan: false)
  - Jika Anda memiliki [Tracy](/awesome-plugins/tracy) terpasang, Tracy akan mencatat error berdasarkan konfigurasi Tracy, bukan konfigurasi ini.
- **flight.debug** `bool` - Menampilkan informasi error terperinci (pesan exception, kode, dan stack trace) di browser saat terjadi error. (bawaan: false)
  - **Jangan pernah mengaktifkan ini di production** — ini membocorkan detail internal aplikasi. Gunakan hanya untuk pengembangan lokal atau staging.
  - Saat `false`, yang ditampilkan adalah `500 Internal Server Error` generik. Pasangkan dengan `flight.log_errors` untuk menangkap error di sisi server.
- **flight.allow_method_override** `bool` - Mengizinkan metode HTTP ditimpa melalui header permintaan `X-HTTP-Method-Override` atau field `_method` di body POST. (bawaan: true)
  - **Disarankan menyetel ini ke `false`** untuk aplikasi yang tidak memerlukan spoofing metode berbasis formulir HTML, karena ini mencegah klien memalsukan permintaan `DELETE` atau `PUT` melalui formulir POST standar.
  - Lihat [Keamanan](/learn/security#flight-configuration-hardening) untuk detail lebih lanjut.
- **flight.views.path** `string` - Direktori yang berisi file template view. (bawaan: ./views)
- **flight.views.extension** `string` - Ekstensi file template view. (bawaan: `.php`; skeleton resmi menyetel ini ke `.twig` saat menggunakan Twig)
- **flight.views.restrict_to_path** `bool` - Saat `true`, `View` bawaan Flight hanya menerima file template yang berada di dalam `flight.views.path`. (bawaan: `false`). **Aktifkan ini** untuk aplikasi yang menggunakan view bawaan. Lihat [Keamanan](/learn/security#flightviewsrestrict_to_path).
- **flight.content_length** `bool` - Menyetel header `Content-Length`. (bawaan: true)
  - Jika Anda menggunakan [Tracy](/awesome-plugins/tracy), ini perlu disetel ke false agar Tracy dapat merender dengan benar.
- **flight.v2.output_buffering** `bool` - Menggunakan output buffering lama. Lihat [migrasi ke v3](migrating-to-v3). (bawaan: false)

### Konfigurasi Loader

Selain itu, ada pengaturan konfigurasi lain untuk loader. Ini memungkinkan Anda melakukan autoload class dengan `_` pada nama class.

```php
// Aktifkan pemuatan class dengan garis bawah
// Default-nya true
Loader::$v2ClassLoading = false;
```

Ingat bahwa [autoloading](/learn/autoloading) juga bergantung pada **huruf besar/kecil folder** yang cocok dengan namespace Anda—terutama dengan tata letak `App\` + `app/Controller/` pada skeleton.

### Konfigurasi proyek dan `.env` (pola skeleton)

Core Flight tidak mewajibkan file `.env`. Banyak aplikasi hanya menggunakan array konfigurasi PHP. Skeleton resmi melapisi konfigurasi agar secret tetap di luar git sementara Runway tetap dapat menulis ulang konfigurasi **literal** dengan aman:

1. **`.env` / lingkungan nyata** — secret dan override deployment (diabaikan git).
2. **`app/config/config.php`** — nilai bawaan array PHP literal (disalin dari `config_sample.php`). Sebaiknya **tidak ada** ekspresi `$_ENV[...]` di dalam file ini: alat seperti `runway config:set` dapat menulis ulang file ini sebagai nilai statis dan bisa menanamkan secret ke dalam file.
3. **Gabungkan saat bootstrap** — env menang untuk key yang dipetakan; kode aplikasi membaca objek config atau `$app->get()`, bukan `$_ENV` di controller.

Contoh bentuk `config_sample.php` / `config.php` (disederhanakan):

```php
<?php
// Hanya literal — secret berada di .env untuk alur kerja skeleton
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

Pemisahan ini sengaja dilakukan untuk [proyek ramah AI](/learn/ai): instruksi dapat menyatakan “nilai bawaan di `config.php`, secret di `.env`, inject Config / Engine—jangan pernah mengarang akses env di controller.” Aplikasi yang sudah ada dapat mengabaikan `.env` sepenuhnya dan tetap memakai satu file konfigurasi.

### Variabel

Flight memungkinkan Anda menyimpan variabel agar dapat digunakan di mana saja dalam aplikasi Anda.

```php
// Simpan variabel Anda
Flight::set('id', 123);

// Di tempat lain dalam aplikasi Anda
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

> **Catatan:** Hanya karena Anda dapat menyetel variabel bukan berarti Anda harus melakukannya. Gunakan fitur ini seperlunya. Alasannya adalah apa pun yang disimpan di sini menjadi variabel global. Variabel global buruk karena dapat diubah dari mana saja di aplikasi Anda, sehingga sulit melacak bug. Selain itu, ini dapat mempersulit hal-hal seperti [pengujian unit](/guides/unit-testing). Utamakan constructor injection (seperti pada setup skeleton + Dice) untuk service dan config yang dibutuhkan controller.

### Error dan Exception

Semua error dan exception ditangkap oleh Flight dan diteruskan ke metode `error` jika `flight.handle_errors` disetel ke true.

Perilaku bawaannya adalah mengirim respons `HTTP 500 Internal Server Error` generik dengan sejumlah informasi error.

Anda dapat [menimpa](/learn/extending) perilaku ini sesuai kebutuhan Anda:

```php
Flight::map('error', function (Throwable $error) {
  // Tangani error
  echo $error->getTraceAsString();
});
```

Secara bawaan, error tidak dicatat ke web server. Anda dapat mengaktifkannya dengan mengubah konfigurasi:

```php
Flight::set('flight.log_errors', true);
```

#### 404 Tidak Ditemukan

Saat URL tidak dapat ditemukan, Flight memanggil metode `notFound`. Perilaku bawaannya adalah mengirim respons `HTTP 404 Not Found` dengan pesan sederhana.

Anda dapat [menimpa](/learn/extending) perilaku ini sesuai kebutuhan Anda:

```php
Flight::map('notFound', function () {
  // Tangani tidak ditemukan
});
```

## Lihat Juga
- [Instalasi](/install) - Konfigurasi skeleton, `.env`, dan tata letak bootstrap.
- [Autoloading](/learn/autoloading) - Namespace dan huruf besar/kecil folder.
- [Memperluas Flight](/learn/extending) - Cara memperluas dan menyesuaikan fungsionalitas inti Flight.
- [Pengujian Unit](/guides/unit-testing) - Cara menulis unit test untuk aplikasi Flight Anda.
- [AI & Pengalaman Developer](/learn/ai) - `AGENTS.md` dan instruksi proyek yang konsisten.
- [Tracy](/awesome-plugins/tracy) - Plugin untuk penanganan error dan debugging tingkat lanjut.
- [Ekstensi Tracy](/awesome-plugins/tracy_extensions) - Ekstensi untuk mengintegrasikan Tracy dengan Flight.
- [APM](/awesome-plugins/apm) - Plugin untuk pemantauan performa aplikasi dan pelacakan error.
- [Keamanan](/learn/security) - Flag hardening dan penanganan secret.

## Pemecahan Masalah
- Jika Anda mengalami masalah saat mengetahui semua nilai konfigurasi Anda, Anda dapat menjalankan `var_dump(Flight::get());`
- Jika Runway atau alat deploy menulis ulang `config.php`, pastikan secret tidak ikut ter-commit—simpan secret di `.env` atau lingkungan nyata saat menggunakan pola skeleton.

## Catatan Perubahan
- Dokumentasi – Mencatat `flight.views.restrict_to_path` di samping pengaturan path view.
- Dokumentasi – Mendokumentasikan konfigurasi gaya skeleton / pelapisan `.env` dan default ekstensi view Twig untuk proyek baru.
- v3.18.1 - Menambahkan opsi konfigurasi `flight.debug` dan `flight.allow_method_override`.
- v3.5.0 - Menambahkan konfigurasi untuk `flight.v2.output_buffering` guna mendukung perilaku output buffering lama.
- v2.0 - Konfigurasi inti ditambahkan.