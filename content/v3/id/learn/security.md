# Keamanan

## Gambaran Umum

Keamanan adalah hal besar ketika berbicara tentang aplikasi web. Anda ingin memastikan bahwa aplikasi Anda aman dan data pengguna Anda 
aman. Flight menyediakan sejumlah fitur untuk membantu Anda mengamankan aplikasi web Anda.

## Pemahaman

Ada sejumlah ancaman keamanan umum yang harus Anda sadari saat membangun aplikasi web. Beberapa ancaman paling umum
termasuk:
- Cross Site Request Forgery (CSRF)
- Cross Site Scripting (XSS)
- SQL Injection
- Cross Origin Resource Sharing (CORS)

[Templates](/learn/templates) membantu dengan XSS dengan meng-escape output secara default sehingga Anda tidak perlu mengingat untuk melakukannya. [Sessions](/awesome-plugins/session) dapat membantu dengan CSRF dengan menyimpan token CSRF di sesi pengguna seperti yang diuraikan di bawah ini. Menggunakan prepared statements dengan PDO dapat membantu mencegah serangan SQL injection (atau menggunakan metode yang berguna di kelas [PdoWrapper](/learn/pdo-wrapper)). CORS dapat ditangani dengan hook sederhana sebelum `Flight::start()` dipanggil.

Semua metode ini bekerja sama untuk membantu menjaga aplikasi web Anda aman. Ini harus selalu menjadi yang terdepan di pikiran Anda untuk belajar dan memahami praktik terbaik keamanan.

## Penggunaan Dasar

### Header

Header HTTP adalah salah satu cara termudah untuk mengamankan aplikasi web Anda. Anda dapat menggunakan header untuk mencegah clickjacking, XSS, dan serangan lainnya. 
Ada beberapa cara yang dapat Anda gunakan untuk menambahkan header ini ke aplikasi Anda.

Dua situs web hebat untuk memeriksa keamanan header Anda adalah [securityheaders.com](https://securityheaders.com/) dan 
[observatory.mozilla.org](https://observatory.mozilla.org/). Setelah Anda menyiapkan kode di bawah ini, Anda dapat dengan mudah memverifikasi bahwa header Anda berfungsi dengan dua situs web tersebut.

#### Tambahkan Secara Manual

Anda dapat menambahkan header ini secara manual dengan menggunakan metode `header` pada objek `Flight\Response`.
```php
// Set header X-Frame-Options untuk mencegah clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Set header Content-Security-Policy untuk mencegah XSS
// Catatan: header ini bisa sangat kompleks, jadi Anda akan ingin
//  berkonsultasi dengan contoh di internet untuk aplikasi Anda
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Set header X-XSS-Protection untuk mencegah XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Set header X-Content-Type-Options untuk mencegah MIME sniffing
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Set header Referrer-Policy untuk mengontrol seberapa banyak informasi referrer yang dikirim
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Set header Strict-Transport-Security untuk memaksa HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Set header Permissions-Policy untuk mengontrol fitur dan API apa yang dapat digunakan
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Ini dapat ditambahkan di bagian atas file `routes.php` atau `index.php` Anda.

#### Tambahkan sebagai Filter

Anda juga dapat menambahkannya dalam filter/hook seperti berikut: 

```php
// Tambahkan header dalam filter
Flight::before('start', function() {
	Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
	Flight::response()->header("Content-Security-Policy", "default-src 'self'");
	Flight::response()->header('X-XSS-Protection', '1; mode=block');
	Flight::response()->header('X-Content-Type-Options', 'nosniff');
	Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
	Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
	Flight::response()->header('Permissions-Policy', 'geolocation=()');
});
```

#### Tambahkan sebagai Middleware

Anda juga dapat menambahkannya sebagai kelas middleware yang memberikan fleksibilitas terbesar untuk rute mana yang akan diterapkan ini. Secara umum, header ini harus diterapkan pada semua respons HTML dan API.

```php
// app/middlewares/SecurityHeadersMiddleware.php

namespace app\middlewares;

use flight\Engine;

class SecurityHeadersMiddleware
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function before(array $params): void
	{
		$response = $this->app->response();
		$response->header('X-Frame-Options', 'SAMEORIGIN');
		$response->header("Content-Security-Policy", "default-src 'self'");
		$response->header('X-XSS-Protection', '1; mode=block');
		$response->header('X-Content-Type-Options', 'nosniff');
		$response->header('Referrer-Policy', 'no-referrer-when-downgrade');
		$response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		$response->header('Permissions-Policy', 'geolocation=()');
	}
}

// index.php atau di mana pun Anda memiliki rute
// FYI, grup string kosong ini bertindak sebagai middleware global untuk
// semua rute. Tentu saja Anda bisa melakukan hal yang sama dan hanya menambahkan
// ini ke rute tertentu.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// lebih banyak rute
}, [ SecurityHeadersMiddleware::class ]);
```

### Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF) adalah jenis serangan di mana situs web berbahaya dapat membuat browser pengguna mengirim permintaan ke situs web Anda. 
Ini dapat digunakan untuk melakukan tindakan di situs web Anda tanpa sepengetahuan pengguna. Flight tidak menyediakan mekanisme perlindungan CSRF bawaan, 
tetapi Anda dapat dengan mudah mengimplementasikan sendiri dengan menggunakan middleware.

#### Penyiapan

Pertama, Anda perlu menghasilkan token CSRF dan menyimpannya di sesi pengguna. Kemudian Anda dapat menggunakan token ini di formulir Anda dan memeriksanya ketika 
formulir dikirim. Kami akan menggunakan plugin [flightphp/session](/awesome-plugins/session) untuk mengelola sesi.

```php
// Hasilkan token CSRF dan simpan di sesi pengguna
// (dengan asumsi Anda telah membuat objek sesi dan melampirkannya ke Flight)
// lihat dokumentasi sesi untuk informasi lebih lanjut
Flight::register('session', flight\Session::class);

// Anda hanya perlu menghasilkan satu token per sesi (sehingga berfungsi 
// di berbagai tab dan permintaan untuk pengguna yang sama)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### Menggunakan Template Flight PHP Default

```html
<!-- Gunakan token CSRF di formulir Anda -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- field formulir lainnya -->
</form>
```

##### Menggunakan Latte

Anda juga dapat mengatur fungsi kustom untuk mengeluarkan token CSRF di template Latte Anda.

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// konfigurasi lainnya...

	// Set fungsi kustom untuk mengeluarkan token CSRF
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

Dan sekarang di template Latte Anda, Anda dapat menggunakan fungsi `csrf()` untuk mengeluarkan token CSRF.

```html
<form method="post">
	{csrf()}
	<!-- field formulir lainnya -->
</form>
```

#### Periksa Token CSRF

Anda dapat memeriksa token CSRF menggunakan beberapa metode.

##### Middleware

```php
// app/middlewares/CsrfMiddleware.php

namespace app\middleware;

use flight\Engine;

class CsrfMiddleware
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function before(array $params): void
	{
		if($this->app->request()->method == 'POST') {
			$token = $this->app->request()->data->csrf_token;
			if($token !== $this->app->session()->get('csrf_token')) {
				$this->app->halt(403, 'Invalid CSRF token');
			}
		}
	}
}

// index.php atau di mana pun Anda memiliki rute
use app\middlewares\CsrfMiddleware;

Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// lebih banyak rute
}, [ CsrfMiddleware::class ]);
```

##### Filter Event

```php
// Middleware ini memeriksa apakah permintaan adalah permintaan POST dan jika ya, memeriksa apakah token CSRF valid
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// tangkap token csrf dari nilai formulir
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
			// atau untuk respons JSON
			Flight::jsonHalt(['error' => 'Invalid CSRF token'], 403);
		}
	}
});
```

### Cross Site Scripting (XSS)

Cross Site Scripting (XSS) adalah jenis serangan di mana input formulir berbahaya dapat menyuntikkan kode ke situs web Anda. Sebagian besar peluang ini berasal 
dari nilai formulir yang akan diisi oleh pengguna akhir Anda. Anda **tidak pernah** boleh mempercayai output dari pengguna Anda! Selalu asumsikan semua mereka adalah 
hacker terbaik di dunia. Mereka dapat menyuntikkan JavaScript atau HTML berbahaya ke halaman Anda. Kode ini dapat digunakan untuk mencuri informasi dari 
pengguna Anda atau melakukan tindakan di situs web Anda. Dengan menggunakan kelas view Flight atau engine templating lain seperti [Latte](/awesome-plugins/latte), Anda dapat dengan mudah meng-escape output untuk mencegah serangan XSS.

```php
// Mari kita asumsikan pengguna pintar dan mencoba menggunakan ini sebagai nama mereka
$name = '<script>alert("XSS")</script>';

// Ini akan meng-escape output
Flight::view()->set('name', $name);
// Ini akan mengeluarkan: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Jika Anda menggunakan sesuatu seperti Latte yang terdaftar sebagai kelas view Anda, itu juga akan auto escape ini.
Flight::view()->render('template', ['name' => $name]);
```

### SQL Injection

SQL Injection adalah jenis serangan di mana pengguna berbahaya dapat menyuntikkan kode SQL ke database Anda. Ini dapat digunakan untuk mencuri informasi 
dari database Anda atau melakukan tindakan di database Anda. Sekali lagi Anda **tidak pernah** boleh mempercayai input dari pengguna Anda! Selalu asumsikan mereka 
mengincar darah. Anda dapat menggunakan prepared statements di objek `PDO` Anda akan mencegah SQL injection.

```php
// Dengan asumsi Anda memiliki Flight::db() yang terdaftar sebagai objek PDO Anda
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Jika Anda menggunakan kelas PdoWrapper, ini dapat dengan mudah dilakukan dalam satu baris
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Anda dapat melakukan hal yang sama dengan objek PDO dengan placeholder ?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

#### Contoh Tidak Aman

Di bawah ini adalah alasan mengapa kami menggunakan pernyataan prepared SQL untuk melindungi dari contoh tidak bersalah seperti di bawah ini:

```php
// pengguna akhir mengisi formulir web.
// untuk nilai formulir, hacker memasukkan sesuatu seperti ini:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// Setelah query dibangun, itu terlihat seperti ini
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// Terlihat aneh, tapi itu query yang valid yang akan berfungsi. Bahkan,
// itu adalah serangan SQL injection yang sangat umum yang akan mengembalikan semua pengguna.

var_dump($users); // ini akan membuang semua pengguna di database, bukan hanya satu nama pengguna tunggal
```

### CORS

Cross-Origin Resource Sharing (CORS) adalah mekanisme yang memungkinkan banyak sumber daya (misalnya, font, JavaScript, dll.) pada halaman web untuk diminta 
dari domain lain di luar domain tempat sumber daya berasal. Flight tidak memiliki fungsionalitas bawaan, 
tetapi ini dapat dengan mudah ditangani dengan hook untuk dijalankan sebelum metode `Flight::start()` dipanggil.

```php
// app/utils/CorsUtil.php

namespace app\utils;

class CorsUtil
{
	public function set(array $params): void
	{
		$request = Flight::request();
		$response = Flight::response();
		if ($request->getVar('HTTP_ORIGIN') !== '') {
			$this->allowOrigins();
			$response->header('Access-Control-Allow-Credentials', 'true');
			$response->header('Access-Control-Max-Age', '86400');
		}

		if ($request->method === 'OPTIONS') {
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_METHOD') !== '') {
				$response->header(
					'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD'
				);
			}
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS') !== '') {
				$response->header(
					"Access-Control-Allow-Headers",
					$request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS')
				);
			}

			$response->status(200);
			$response->send();
			exit;
		}
	}

	private function allowOrigins(): void
	{
		// sesuaikan host yang diizinkan di sini.
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		$request = Flight::request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $allowed, true) === true) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin", $request->getVar('HTTP_ORIGIN'));
		}
	}
}

// index.php atau di mana pun Anda memiliki rute
$CorsUtil = new CorsUtil();

// Ini perlu dijalankan sebelum start berjalan.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

### Penanganan Error
Sembunyikan detail error sensitif di produksi untuk menghindari kebocoran info ke penyerang. Di produksi, log error daripada menampilkannya dengan `display_errors` disetel ke `0`.

```php
// Di bootstrap.php atau index.php Anda

// tambahkan ini ke app/config/config.php Anda
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Nonaktifkan tampilan error
    ini_set('log_errors', 1);     // Log error sebagai gantinya
    ini_set('error_log', '/path/to/error.log');
}

// Di rute atau controller Anda
// Gunakan Flight::halt() untuk respons error yang terkendali
Flight::halt(403, 'Access denied');
```

### Sanitasi Input
Jangan pernah percaya input pengguna. Sanitasi itu menggunakan [filter_var](https://www.php.net/manual/en/function.filter-var.php) sebelum diproses untuk mencegah data berbahaya menyusup masuk.

```php

// Mari kita asumsikan permintaan $_POST dengan $_POST['input'] dan $_POST['email']

// Sanitasi input string
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Sanitasi email
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### Penyimpanan Hash Kata Sandi
Simpan kata sandi dengan aman dan verifikasi dengan aman menggunakan fungsi bawaan PHP seperti [password_hash](https://www.php.net/manual/en/function.password-hash.php) dan [password_verify](https://www.php.net/manual/en/function.password-verify.php). Kata sandi tidak boleh disimpan dalam teks biasa, juga tidak boleh dienkripsi dengan metode yang dapat dibalik. Hashing memastikan bahwa bahkan jika database Anda dikompromikan, kata sandi sebenarnya tetap terlindungi.

```php
$password = Flight::request()->data->password;
// Hash kata sandi saat menyimpan (misalnya, selama pendaftaran)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Verifikasi kata sandi (misalnya, selama login)
if (password_verify($password, $stored_hash)) {
    // Kata sandi cocok
}
```

### Pembatasan Laju
Lindungi dari serangan brute force atau serangan denial-of-service dengan membatasi laju permintaan menggunakan cache.

```php
// Dengan asumsi Anda memiliki flightphp/cache yang diinstal dan terdaftar
// Menggunakan flightphp/cache dalam filter
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Too many requests');
    }
    
    $cache->set($key, $attempts + 1, 60); // Reset setelah 60 detik
});
```

## Lihat Juga
- [Sessions](/awesome-plugins/session) - Cara mengelola sesi pengguna dengan aman.
- [Templates](/learn/templates) - Menggunakan template untuk auto-escape output dan mencegah XSS.
- [PDO Wrapper](/learn/pdo-wrapper) - Interaksi database yang disederhanakan dengan prepared statements.
- [Middleware](/learn/middleware) - Cara menggunakan middleware untuk menyederhanakan proses menambahkan header keamanan.
- [Responses](/learn/responses) - Cara menyesuaikan respons HTTP dengan header aman.
- [Requests](/learn/requests) - Cara menangani dan menyanitasi input pengguna.
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - Fungsi PHP untuk sanitasi input.
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - Fungsi PHP untuk hashing kata sandi aman.
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - Fungsi PHP untuk memverifikasi kata sandi yang di-hash.

## Pemecahan Masalah
- Lihat bagian "Lihat Juga" di atas untuk informasi pemecahan masalah terkait isu dengan komponen Framework Flight.

## Changelog
- v3.1.0 - Ditambahkan bagian tentang CORS, Penanganan Error, Sanitasi Input, Hashing Kata Sandi, dan Pembatasan Laju.
- v2.0 - Ditambahkan escaping untuk view default untuk mencegah XSS.