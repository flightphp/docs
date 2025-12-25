# Runway

Runway adalah aplikasi CLI yang membantu Anda mengelola aplikasi Flight Anda. Ini dapat menghasilkan controller, menampilkan semua rute, dan banyak lagi. Ini didasarkan pada pustaka [adhocore/php-cli](https://github.com/adhocore/php-cli) yang luar biasa.

Klik [di sini](https://github.com/flightphp/runway) untuk melihat kode.

## Instalasi

Instal dengan composer.

```bash
composer require flightphp/runway
```

## Konfigurasi Dasar

Pada pertama kali Anda menjalankan Runway, itu akan menjalankan Anda melalui proses pengaturan dan membuat file konfigurasi `.runway.json` di root proyek Anda. File ini akan berisi beberapa konfigurasi yang diperlukan agar Runway bekerja dengan benar.

## Penggunaan

Runway memiliki sejumlah perintah yang dapat Anda gunakan untuk mengelola aplikasi Flight Anda. Ada dua cara mudah untuk menggunakan Runway.

1. Jika Anda menggunakan proyek skeleton, Anda dapat menjalankan `php runway [command]` dari root proyek Anda.
1. Jika Anda menggunakan Runway sebagai paket yang diinstal melalui composer, Anda dapat menjalankan `vendor/bin/runway [command]` dari root proyek Anda.

Untuk perintah apa pun, Anda dapat memberikan flag `--help` untuk mendapatkan informasi lebih lanjut tentang cara menggunakan perintah tersebut.

```bash
php runway routes --help
```

Berikut adalah beberapa contoh:

### Menghasilkan Controller

Berdasarkan konfigurasi di file `.runway.json` Anda, lokasi default akan menghasilkan controller untuk Anda di direktori `app/controllers/`.

```bash
php runway make:controller MyController
```

### Menghasilkan Model Active Record

Berdasarkan konfigurasi di file `.runway.json` Anda, lokasi default akan menghasilkan controller untuk Anda di direktori `app/records/`.

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
 * // you could also add relationships here once you define them in the $relations array
 * @property CompanyRecord $company Example of a relationship
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Set the relationships for the model
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [];

    /**
     * Constructor
     * @param mixed $databaseConnection The connection to the database
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

Jika Anda ingin hanya melihat rute tertentu, Anda dapat memberikan flag untuk memfilter rute.

```bash
# Display only GET routes
php runway routes --get

# Display only POST routes
php runway routes --post

# etc.
```

## Menyesuaikan Runway

Jika Anda membuat paket untuk Flight, atau ingin menambahkan perintah kustom Anda sendiri ke dalam proyek Anda, Anda dapat melakukannya dengan membuat direktori `src/commands/`, `flight/commands/`, `app/commands/`, atau `commands/` untuk proyek/paket Anda. Jika Anda membutuhkan penyesuaian lebih lanjut, lihat bagian di bawah tentang Konfigurasi.

Untuk membuat perintah, Anda cukup memperluas kelas `AbstractBaseCommand`, dan menerapkan setidaknya metode `__construct` dan metode `execute`.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * Construct
     *
     * @param array<string,mixed> $config JSON config from .runway-config.json
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Create an example for the documentation', $config);
        $this->argument('<funny-gif>', 'The name of the funny gif');
    }

	/**
     * Executes the function
     *
     * @return void
     */
    public function execute()
    {
        $io = $this->app()->io();

		$io->info('Creating example...');

		// Do something here

		$io->ok('Example created!');
	}
}
```

Lihat [Dokumentasi adhocore/php-cli](https://github.com/adhocore/php-cli) untuk informasi lebih lanjut tentang cara membangun perintah kustom Anda sendiri ke dalam aplikasi Flight!

### Konfigurasi

Jika Anda perlu menyesuaikan konfigurasi untuk Runway, Anda dapat membuat file `.runway-config.json` di root proyek Anda. Berikut adalah beberapa konfigurasi tambahan yang dapat Anda atur:

```js
{

	// This is where your application directory is located
	"app_root": "app/",

	// This is the directory where your root index file is located
	"index_root": "public/",

	// These are the paths to the roots of other projects
	"root_paths": [
		"/home/user/different-project",
		"/var/www/another-project"
	],

	// Base paths most likely don't need to be configured, but it's here if you want it
	"base_paths": {
		"/includes/libs/vendor", // if you have a really unique path for your vendor directory or something
	},

	// Final paths are locations within a project to search for the command files
	"final_paths": {
		"src/diff-path/commands",
		"app/module/admin/commands",
	},

	// If you want to just add the full path, go right ahead (absolute or relative to project root)
	"paths": [
		"/home/user/different-project/src/diff-path/commands",
		"/var/www/another-project/app/module/admin/commands",
		"app/my-unique-commands"
	]
}
```