# Jalur

Jalur adalah aplikasi CLI yang membantu Anda mengelola aplikasi Flight Anda. Ini dapat menghasilkan pengontrol, menampilkan semua rute, dan banyak lagi. Ini didasarkan pada pustaka [adhocore/php-cli](https://github.com/adhocore/php-cli) yang sangat baik.

Klik [di sini](https://github.com/flightphp/runway) untuk melihat kodenya.

## Instalasi

Instal dengan composer.

```bash
composer require flightphp/runway
```

## Konfigurasi Dasar

Kali pertama Anda menjalankan Jalur, itu akan memandu Anda melalui proses pengaturan dan membuat file konfigurasi `.runway.json` di akar proyek Anda. File ini akan berisi beberapa konfigurasi yang diperlukan agar Jalur berfungsi dengan baik.

## Penggunaan

Jalur memiliki sejumlah perintah yang dapat Anda gunakan untuk mengelola aplikasi Flight Anda. Ada dua cara mudah untuk menggunakan Jalur.

1. Jika Anda menggunakan proyek tulang, Anda dapat menjalankan `php runway [command]` dari akar proyek Anda.
1. Jika Anda menggunakan Jalur sebagai paket yang diinstal melalui composer, Anda dapat menjalankan `vendor/bin/runway [command]` dari akar proyek Anda.

Untuk setiap perintah, Anda dapat melewatkan bendera `--help` untuk mendapatkan informasi lebih lanjut tentang cara menggunakan perintah tersebut.

```bash
php runway routes --help
```

Berikut adalah beberapa contoh:

### Menghasilkan Pengontrol

Berdasarkan konfigurasi dalam file `.runway.json` Anda, lokasi default akan menghasilkan pengontrol untuk Anda di direktori `app/controllers/`.

```bash
php runway make:controller MyController
```

### Menghasilkan Model Rekaman Aktif

Berdasarkan konfigurasi dalam file `.runway.json` Anda, lokasi default akan menghasilkan pengontrol untuk Anda di direktori `app/records/`.

```bash
php runway make:record users
```

Jika misalnya Anda memiliki tabel `users` dengan skema berikut: `id`, `name`, `email`, `created_at`, `updated_at`, sebuah file yang mirip dengan yang berikut akan dibuat di file `app/records/UserRecord.php`:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Kelas ActiveRecord untuk tabel users.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // Anda juga bisa menambahkan hubungan di sini setelah Anda mendefinisikannya di array $relations
 * @property CompanyRecord $company Contoh hubungan
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Tetapkan hubungan untuk model
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [];

    /**
     * Konstruktor
     * @param mixed $databaseConnection Koneksi ke database
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

### Tampilkan Semua Rute

Ini akan menampilkan semua rute yang saat ini terdaftar dengan Flight.

```bash
php runway routes
```

Jika Anda ingin hanya melihat rute tertentu, Anda dapat melewatkan bendera untuk menyaring rute.

```bash
# Tampilkan hanya rute GET
php runway routes --get

# Tampilkan hanya rute POST
php runway routes --post

# dll.
```

## Menyesuaikan Jalur

Jika Anda membuat paket untuk Flight, atau ingin menambahkan perintah kustom Anda sendiri ke dalam proyek Anda, Anda dapat melakukannya dengan membuat direktori `src/commands/`, `flight/commands/`, `app/commands/`, atau `commands/` untuk proyek/paket Anda. Jika Anda memerlukan penyesuaian lebih lanjut, lihat bagian di bawah tentang Konfigurasi.

Untuk membuat perintah, Anda cukup memperluas kelas `AbstractBaseCommand`, dan menerapkan setidaknya metode `__construct` dan metode `execute`.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * Konstruktor
     *
     * @param array<string,mixed> $config Konfigurasi JSON dari .runway-config.json
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Buat contoh untuk dokumentasi', $config);
        $this->argument('<funny-gif>', 'Nama gif lucu');
    }

	/**
     * Menjalankan fungsi
     *
     * @return void
     */
    public function execute(string $controller)
    {
        $io = $this->app()->io();

		$io->info('Membuat contoh...');

		// Lakukan sesuatu di sini

		$io->ok('Contoh dibuat!');
	}
}
```

Lihat [adhocore/php-cli Documentation](https://github.com/adhocore/php-cli) untuk informasi lebih lanjut tentang cara membangun perintah kustom Anda sendiri ke dalam aplikasi Flight Anda!

### Konfigurasi

Jika Anda perlu menyesuaikan konfigurasi untuk Jalur, Anda dapat membuat file `.runway-config.json` di akar proyek Anda. Di bawah ini adalah beberapa konfigurasi tambahan yang dapat Anda tetapkan:

```js
{

	// Ini adalah tempat direktori aplikasi Anda berada
	"app_root": "app/",

	// Ini adalah direktori tempat file indeks akar Anda berada
	"index_root": "public/",

	// Ini adalah jalur ke akar proyek lainnya
	"root_paths": [
		"/home/user/different-project",
		"/var/www/another-project"
	],

	// Jalur dasar kemungkinan besar tidak perlu dikonfigurasi, tapi ada di sini jika Anda menginginkannya
	"base_paths": {
		"/includes/libs/vendor", // jika Anda memiliki jalur yang sangat unik untuk direktori vendor Anda atau sesuatu
	},

	// Jalur akhir adalah lokasi dalam proyek untuk mencari file perintah
	"final_paths": {
		"src/diff-path/commands",
		"app/module/admin/commands",
	},

	// Jika Anda ingin hanya menambahkan jalur lengkap, silakan saja (absolut atau relatif terhadap akar proyek)
	"paths": [
		"/home/user/different-project/src/diff-path/commands",
		"/var/www/another-project/app/module/admin/commands",
		"app/my-unique-commands"
	]
}
```