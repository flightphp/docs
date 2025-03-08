# Ghostff/Session

Pengelola Sesi PHP (non-blocking, flash, segment, enkripsi sesi). Menggunakan PHP open_ssl untuk enkripsi/dekripsi data sesi secara opsional. Mendukung File, MySQL, Redis, dan Memcached.

Klik [di sini](https://github.com/Ghostff/Session) untuk melihat kode.

## Instalasi

Instal dengan composer.

```bash
composer require ghostff/session
```

## Konfigurasi Dasar

Anda tidak perlu mengirimkan apa pun untuk menggunakan pengaturan default dengan sesi Anda. Anda bisa membaca lebih lanjut tentang pengaturan lainnya di [Github Readme](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// satu hal yang perlu diingat adalah bahwa Anda harus mengkomit sesi Anda di setiap muatan halaman
// atau Anda perlu menjalankan auto_commit dalam konfigurasi Anda. 
```

## Contoh Sederhana

Berikut adalah contoh sederhana tentang bagaimana Anda mungkin menggunakan ini.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// lakukan logika login Anda di sini
	// validasi kata sandi, dll.

	// jika login berhasil
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// setiap kali Anda menulis ke sesi, Anda harus mengkomitnya dengan sengaja.
	$session->commit();
});

// Pemeriksaan ini bisa ada dalam logika halaman terbatas, atau dibungkus dengan middleware.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// lakukan logika halaman terbatas Anda di sini
});

// versi middleware
Flight::route('/some-restricted-page', function() {
	// logika halaman biasa
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## Contoh yang Lebih Kompleks

Berikut adalah contoh yang lebih kompleks tentang bagaimana Anda mungkin menggunakan ini.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// setel jalur kustom ke file konfigurasi sesi Anda dan berikan string acak untuk id sesi
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// atau Anda dapat secara manual menimpa opsi konfigurasi
		$session->updateConfiguration([
			// jika Anda ingin menyimpan data sesi Anda dalam basis data (baik jika Anda menginginkan sesuatu seperti, "keluar dari semua perangkat")
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // silakan ubah ini menjadi sesuatu yang lain
			Session::CONFIG_AUTO_COMMIT   => true, // hanya lakukan ini jika memerlukannya dan/atau sulit untuk mengkomit sesi Anda.
												   // selain itu Anda bisa melakukan Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Driver basis data untuk dns PDO misal(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Host basis data
				'db_name'   => 'my_app_database',   # Nama basis data
				'db_table'  => 'sessions',          # Tabel basis data
				'db_user'   => 'root',              # Nama pengguna basis data
				'db_pass'   => '',                  # Kata sandi basis data
				'persistent_conn'=> false,          # Hindari overhead dari pembuatan koneksi baru setiap kali skrip perlu berbicara dengan basis data, yang menghasilkan aplikasi web yang lebih cepat. TEMUKAN BELAKANG SENDIRI
			]
		]);
	}
);
```

## Bantu! Data Sesi Saya Tidak Bertahan!

Apakah Anda mengatur data sesi Anda dan itu tidak bertahan antar permintaan? Anda mungkin telah lupa untuk mengkomit data sesi Anda. Anda dapat melakukannya dengan memanggil `$session->commit()` setelah Anda mengatur data sesi Anda.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// lakukan logika login Anda di sini
	// validasi kata sandi, dll.

	// jika login berhasil
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// setiap kali Anda menulis ke sesi, Anda harus mengkomitnya dengan sengaja.
	$session->commit();
});
```

Cara lain untuk mengatasi ini adalah ketika Anda mengatur layanan sesi Anda, Anda harus mengatur `auto_commit` ke `true` dalam konfigurasi Anda. Ini akan secara otomatis mengkomit data sesi Anda setelah setiap permintaan.

```php

$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Selain itu, Anda dapat melakukan `Flight::after('start', function() { Flight::session()->commit(); });` untuk mengkomit data sesi Anda setelah setiap permintaan.

## Dokumentasi

Kunjungi [Github Readme](https://github.com/Ghostff/Session) untuk dokumentasi lengkap. Opsi konfigurasi dijelaskan [secara rinci dalam default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) file itu sendiri. Kodenya mudah dipahami jika Anda ingin menelusuri paket ini sendiri.