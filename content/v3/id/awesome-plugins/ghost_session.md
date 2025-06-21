# Ghostff/Session

Manajer Sesi PHP (non-blocking, flash, segment, enkripsi sesi). Menggunakan PHP open_ssl untuk enkripsi/dekripsi data sesi opsional. Mendukung File, MySQL, Redis, dan Memcached.

Klik [di sini](https://github.com/Ghostff/Session) untuk melihat kode.

## Instalasi

Instal dengan composer.

```bash
composer require ghostff/session
```

## Konfigurasi Dasar

Anda tidak diharuskan untuk mengirimkan apa pun untuk menggunakan pengaturan default dengan sesi Anda. Anda dapat membaca tentang pengaturan lebih lanjut di [Github Readme](https://github.com/Ghostff/Session).

```php
use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// satu hal yang perlu diingat adalah bahwa Anda harus melakukan commit sesi pada setiap pemuatan halaman
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

	// setiap kali Anda menulis ke sesi, Anda harus melakukan commit secara sengaja.
	$session->commit();
});

// Periksa ini bisa ada di logika halaman terbatas, atau dibungkus dengan middleware.
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

## Contoh Lebih Kompleks

Berikut adalah contoh lebih kompleks tentang bagaimana Anda mungkin menggunakan ini.

```php
use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// atur jalur khusus ke file konfigurasi sesi Anda sebagai argumen pertama
// atau berikan array khusus
$app->register('session', Session::class, [ 
	[
		// jika Anda ingin menyimpan data sesi di database (bagus jika Anda ingin sesuatu seperti, "keluarkan saya dari semua perangkat" fungsionalitas)
		Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
		Session::CONFIG_ENCRYPT_DATA  => true,
		Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // silakan ubah ini menjadi sesuatu yang lain
		Session::CONFIG_AUTO_COMMIT   => true, // hanya lakukan ini jika diperlukan dan/atau sulit untuk commit() sesi Anda.
												// selain itu Anda bisa melakukan Flight::after('start', function() { Flight::session()->commit(); });
		Session::CONFIG_MYSQL_DS         => [
			'driver'    => 'mysql',             # Pengandar basis data untuk PDO dns misalnya (mysql:host=...;dbname=...)
			'host'      => '127.0.0.1',         # Host basis data
			'db_name'   => 'my_app_database',   # Nama basis data
			'db_table'  => 'sessions',          # Tabel basis data
			'db_user'   => 'root',              # Nama pengguna basis data
			'db_pass'   => '',                  # Kata sandi basis data
			'persistent_conn'=> false,          # Hindari biaya overhead dari membangun koneksi baru setiap kali skrip perlu berbicara ke basis data, menghasilkan aplikasi web yang lebih cepat. CARI BACKSIDE SENDIRI
		]
	] 
]);
```

## Bantuan! Data Sesi Saya Tidak Bertahan!

Apakah Anda mengatur data sesi dan itu tidak bertahan antara permintaan? Anda mungkin lupa untuk melakukan commit data sesi Anda. Anda bisa melakukan ini dengan memanggil `$session->commit()` setelah Anda mengatur data sesi Anda.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// lakukan logika login Anda di sini
	// validasi kata sandi, dll.

	// jika login berhasil
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// setiap kali Anda menulis ke sesi, Anda harus melakukan commit secara sengaja.
	$session->commit();
});
```

Cara lain untuk mengatasi ini adalah ketika Anda mengatur layanan sesi Anda, Anda harus mengatur `auto_commit` ke `true` dalam konfigurasi Anda. Ini akan secara otomatis melakukan commit data sesi Anda setelah setiap permintaan.

```php
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Selain itu, Anda bisa melakukan `Flight::after('start', function() { Flight::session()->commit(); });` untuk melakukan commit data sesi Anda setelah setiap permintaan.

## Dokumentasi

Kunjungi [Github Readme](https://github.com/Ghostff/Session) untuk dokumentasi lengkap. Opsi konfigurasi didokumentasikan dengan baik di [file default_config.php itu sendiri](https://github.com/Ghostff/Session/blob/master/src/default_config.php). Kode ini sederhana untuk dipahami jika Anda ingin menelusuri paket ini sendiri.