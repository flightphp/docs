# Runway

Runway adalah aplikasi CLI yang membantu Anda mengelola aplikasi Flight Anda. Ini dapat menghasilkan controller, menampilkan semua rute, dan lainnya. Ini didasarkan pada pustaka [adhocore/php-cli](https://github.com/adhocore/php-cli) yang luar biasa.

Klik [di sini](https://github.com/flightphp/runway) untuk melihat kode.

## Instalasi

Instal dengan composer.

```bash
composer require flightphp/runway
```

## Konfigurasi Dasar

Pada pertama kali Anda menjalankan Runway, itu akan mencoba mencari konfigurasi `runway` di `app/config/config.php` melalui kunci `'runway'`.

```php
<?php
// app/config/config.php
return [
    'runway' => [
        'app_root' => 'app/',
		'public_root' => 'public/',
    ],
];
```

> **CATATAN** - Mulai dari **v1.2.0**, `.runway-config.json` sudah tidak digunakan lagi. Silakan migrasikan konfigurasi Anda ke `app/config/config.php`. Anda dapat melakukan ini dengan mudah menggunakan perintah `php runway config:migrate`.

### Deteksi Root Proyek

Runway cukup pintar untuk mendeteksi root proyek Anda, bahkan jika Anda menjalankannya dari subdirektori. Ini mencari indikator seperti `composer.json`, `.git`, atau `app/config/config.php` untuk menentukan di mana root proyek berada. Ini berarti Anda dapat menjalankan perintah Runway dari mana saja di proyek Anda! 

## Penggunaan

Runway memiliki sejumlah perintah yang dapat Anda gunakan untuk mengelola aplikasi Flight Anda. Ada dua cara mudah untuk menggunakan Runway.

1. Jika Anda menggunakan proyek skeleton, Anda dapat menjalankan `php runway [command]` dari root proyek Anda.
1. Jika Anda menggunakan Runway sebagai paket yang diinstal melalui composer, Anda dapat menjalankan `vendor/bin/runway [command]` dari root proyek Anda.

### Daftar Perintah

Anda dapat melihat daftar semua perintah yang tersedia dengan menjalankan perintah `php runway`.

```bash
php runway
```

### Bantuan Perintah

Untuk perintah apa pun, Anda dapat menambahkan flag `--help` untuk mendapatkan informasi lebih lanjut tentang cara menggunakan perintah tersebut.

```bash
php runway routes --help
```

Berikut adalah beberapa contoh:

### Menghasilkan Controller

Berdasarkan konfigurasi di `runway.app_root`, lokasi akan menghasilkan controller untuk Anda di direktori `app/controllers/`.

```bash
php runway make:controller MyController
```

### Menghasilkan Model Active Record

Pertama pastikan Anda telah menginstal plugin [Active Record](/awesome-plugins/active-record). Berdasarkan konfigurasi di `runway.app_root`, lokasi akan menghasilkan record untuk Anda di direktori `app/records/`.

```bash
php runway make:record users
```

Misalnya, jika Anda memiliki tabel `users` dengan skema berikut: `id`, `name`, `email`, `created_at`, `updated_at`, file serupa dengan yang berikut akan dibuat di file `app/records/UserRecord.php`:

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
 * // Anda juga bisa menambahkan relasi di sini setelah mendefinisikannya di array $relations
 * @property CompanyRecord $company Contoh relasi
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Atur relasi untuk model
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

### Menampilkan Semua Rute

Ini akan menampilkan semua rute yang saat ini terdaftar dengan Flight.

```bash
php runway routes
```

Jika Anda ingin hanya melihat rute tertentu, Anda dapat menambahkan flag untuk memfilter rute.

```bash
# Tampilkan hanya rute GET
php runway routes --get

# Tampilkan hanya rute POST
php runway routes --post

# dst.
```

## Menambahkan Perintah Kustom ke Runway

Jika Anda sedang membuat paket untuk Flight, atau ingin menambahkan perintah kustom sendiri ke proyek Anda, Anda dapat melakukannya dengan membuat direktori `src/commands/`, `flight/commands/`, `app/commands/`, atau `commands/` untuk proyek/paket Anda. Jika Anda membutuhkan penyesuaian lebih lanjut, lihat bagian di bawah tentang Konfigurasi.

Untuk membuat perintah, Anda cukup memperluas kelas `AbstractBaseCommand`, dan mengimplementasikan minimal metode `__construct` dan metode `execute`.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * Konstruktor
     *
     * @param array<string,mixed> $config Konfigurasi dari app/config/config.php
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Buat contoh untuk dokumentasi', $config);
        $this->argument('<funny-gif>', 'Nama dari gif lucu');
    }

	/**
     * Menjalankan fungsi
     *
     * @return void
     */
    public function execute()
    {
        $io = $this->app()->io();

		$io->info('Membuat contoh...');

		// Lakukan sesuatu di sini

		$io->ok('Contoh dibuat!');
	}
}
```

Lihat [Dokumentasi adhocore/php-cli](https://github.com/adhocore/php-cli) untuk informasi lebih lanjut tentang cara membangun perintah kustom Anda sendiri ke aplikasi Flight!

## Manajemen Konfigurasi

Karena konfigurasi telah dipindahkan ke `app/config/config.php` mulai dari `v1.2.0`, ada beberapa perintah bantu untuk mengelola konfigurasi.

### Migrasi Konfigurasi Lama

Jika Anda memiliki file `.runway-config.json` lama, Anda dapat dengan mudah memigrasikannya ke `app/config/config.php` dengan perintah berikut:

```bash
php runway config:migrate
```

### Mengatur Nilai Konfigurasi

Anda dapat mengatur nilai konfigurasi menggunakan perintah `config:set`. Ini berguna jika Anda ingin memperbarui nilai konfigurasi tanpa membuka file.

```bash
php runway config:set app_root "app/"
```

### Mendapatkan Nilai Konfigurasi

Anda dapat mendapatkan nilai konfigurasi menggunakan perintah `config:get`.

```bash
php runway config:get app_root
```

## Semua Konfigurasi Runway

Jika Anda perlu menyesuaikan konfigurasi untuk Runway, Anda dapat mengatur nilai-nilai ini di `app/config/config.php`. Berikut adalah beberapa konfigurasi tambahan yang dapat Anda atur:

```php
<?php
// app/config/config.php
return [
    // ... nilai konfigurasi lainnya ...

    'runway' => [
        // Ini adalah lokasi direktori aplikasi Anda
        'app_root' => 'app/',

        // Ini adalah direktori di mana file index root Anda berada
        'index_root' => 'public/',

        // Ini adalah path ke root proyek lainnya
        'root_paths' => [
            '/home/user/different-project',
            '/var/www/another-project'
        ],

        // Path dasar kemungkinan besar tidak perlu dikonfigurasi, tapi ini ada jika Anda menginginkannya
        'base_paths' => [
            '/includes/libs/vendor', // jika Anda memiliki path yang sangat unik untuk direktori vendor atau semacamnya
        ],

        // Path akhir adalah lokasi dalam proyek untuk mencari file perintah
        'final_paths' => [
            'src/diff-path/commands',
            'app/module/admin/commands',
        ],

        // Jika Anda ingin menambahkan path lengkap, silakan (absolut atau relatif terhadap root proyek)
        'paths' => [
            '/home/user/different-project/src/diff-path/commands',
            '/var/www/another-project/app/module/admin/commands',
            'app/my-unique-commands'
        ]
    ]
];
```

### Mengakses Konfigurasi

Jika Anda perlu mengakses nilai konfigurasi secara efektif, Anda dapat mengaksesnya melalui metode `__construct` atau metode `app()`. Penting juga untuk dicatat bahwa jika Anda memiliki file `app/config/services.php`, layanan tersebut juga akan tersedia untuk perintah Anda.

```php
public function execute()
{
    $io = $this->app()->io();
    
    // Akses konfigurasi
    $app_root = $this->config['runway']['app_root'];
    
    // Akses layanan seperti mungkin koneksi database
    $database = $this->config['database']
    
    // ...
}
```

## Wrapper Pembantu AI

Runway memiliki beberapa wrapper pembantu yang membuat lebih mudah bagi AI untuk menghasilkan perintah. Anda dapat menggunakan `addOption` dan `addArgument` dengan cara yang mirip dengan Symfony Console. Ini membantu jika Anda menggunakan alat AI untuk menghasilkan perintah Anda.

```php
public function __construct(array $config)
{
    parent::__construct('make:example', 'Buat contoh untuk dokumentasi', $config);
    
    // Argumen mode bersifat nullable dan defaultnya sepenuhnya opsional
    $this->addOption('name', 'Nama dari contoh', null);
}
```