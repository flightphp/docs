# Ghostff/Session

Manajer Sesi PHP (non-blocking, flash, segmen, enkripsi sesi). Menggunakan PHP open_ssl untuk enkripsi/dekripsi data sesi secara opsional. Mendukung File, MySQL, Redis, dan Memcached.

Klik [di sini](https://github.com/Ghostff/Session) untuk melihat kodenya.

## Instalasi

Install menggunakan composer.

```bash
composer require ghostff/session
```

## Konfigurasi Dasar

Anda tidak perlu meneruskan apapun untuk menggunakan pengaturan default dengan sesi Anda. Anda dapat membaca lebih banyak tentang pengaturan di [Github Readme](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// satu hal yang perlu diingat adalah bahwa Anda harus mengkomit sesi Anda pada setiap pemuatan halaman
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

	// setiap kali Anda menulis ke sesi, Anda harus mengkomitnya secara sengaja.
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
	// logika halaman reguler
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

// atur path kustom untuk file konfigurasi sesi Anda dan berikan string acak untuk id sesi
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// atau Anda dapat secara manual menimpa opsi konfigurasi
		$session->updateConfiguration([
			// jika Anda ingin menyimpan data sesi Anda dalam database (baik jika Anda ingin sesuatu seperti, "keluarkan saya dari semua perangkat" fungsionalitas)
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // silakan ganti ini menjadi sesuatu yang lain
			Session::CONFIG_AUTO_COMMIT   => true, // hanya lakukan ini jika memerlukannya dan/atau sulit untuk mengkomit sesi Anda.
												   // tambahan Anda bisa melakukan Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Driver database untuk dns PDO eg(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Host database
				'db_name'   => 'my_app_database',   # Nama database
				'db_table'  => 'sessions',          # Tabel database
				'db_user'   => 'root',              # Nama pengguna database
				'db_pass'   => '',                  # Kata sandi database
				'persistent_conn'=> false,          # Hindari biaya overhead untuk membuat koneksi baru setiap kali skrip perlu berbicara ke database, yang mengakibatkan aplikasi web yang lebih cepat. TEMUKAN BELAKANGNYA SENDIRI
			]
		]);
	}
);
```

## Bantuan! Data Sesi Saya Tidak Persisten!

Apakah Anda mengatur data sesi Anda dan tidak persisten antara permintaan? Anda mungkin lupa untuk mengkomit data sesi Anda. Anda dapat melakukan ini dengan memanggil `$session->commit()` setelah Anda mengatur data sesi Anda.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// lakukan logika login Anda di sini
	// validasi kata sandi, dll.

	// jika login berhasil
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// setiap kali Anda menulis ke sesi, Anda harus mengkomitnya secara sengaja.
	$session->commit();
});
```

Cara lain untuk mengatasi ini adalah ketika Anda mengatur layanan sesi Anda, Anda harus mengatur `auto_commit` menjadi `true` dalam konfigurasi Anda. Ini akan secara otomatis mengkomit data sesi Anda setelah setiap permintaan.

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

Kunjungi [Github Readme](https://github.com/Ghostff/Session) untuk dokumentasi lengkap. Opsi konfigurasi [didokumentasikan dengan baik dalam default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) file itu sendiri. Kodenya mudah untuk dipahami jika Anda ingin meneliti paket ini sendiri.