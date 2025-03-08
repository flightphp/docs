# Keamanan

Keamanan adalah hal yang penting ketika berhubungan dengan aplikasi web. Anda ingin memastikan bahwa aplikasi Anda aman dan data pengguna Anda 
terlindungi. Flight menyediakan sejumlah fitur untuk membantu Anda mengamankan aplikasi web Anda.

## Header

Header HTTP adalah salah satu cara termudah untuk mengamankan aplikasi web Anda. Anda dapat menggunakan header untuk mencegah clickjacking, XSS, dan serangan lainnya. 
Ada beberapa cara yang dapat Anda lakukan untuk menambahkan header ini ke aplikasi Anda.

Dua situs web yang bagus untuk memeriksa keamanan header Anda adalah [securityheaders.com](https://securityheaders.com/) dan 
[observatory.mozilla.org](https://observatory.mozilla.org/).

### Tambah Secara Manual

Anda dapat menambahkan header ini secara manual dengan menggunakan metode `header` pada objek `Flight\Response`.
```php
// Atur header X-Frame-Options untuk mencegah clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Atur header Content-Security-Policy untuk mencegah XSS
// Catatan: header ini bisa menjadi sangat kompleks, jadi Anda akan
//  ingin berkonsultasi dengan contoh di internet untuk aplikasi Anda
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Atur header X-XSS-Protection untuk mencegah XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Atur header X-Content-Type-Options untuk mencegah sniffing MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Atur header Referrer-Policy untuk mengontrol seberapa banyak informasi referer yang dikirim
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Atur header Strict-Transport-Security untuk memaksakan HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Atur header Permissions-Policy untuk mengontrol fitur dan API apa yang dapat digunakan
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Header ini dapat ditambahkan di atas file `bootstrap.php` atau `index.php` Anda.

### Tambah sebagai Filter

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

### Tambah sebagai Middleware

Anda juga dapat menambahkannya sebagai kelas middleware. Ini adalah cara yang baik untuk menjaga kode Anda tetap bersih dan terorganisir.

```php
// app/middleware/SecurityHeadersMiddleware.php

namespace app\middleware;

class SecurityHeadersMiddleware
{
	public function before(array $params): void
	{
		Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
		Flight::response()->header("Content-Security-Policy", "default-src 'self'");
		Flight::response()->header('X-XSS-Protection', '1; mode=block');
		Flight::response()->header('X-Content-Type-Options', 'nosniff');
		Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
		Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		Flight::response()->header('Permissions-Policy', 'geolocation=()');
	}
}

// index.php atau di mana pun Anda memiliki rute Anda
// FYI, grup string kosong ini bertindak sebagai middleware global untuk
// semua rute. Tentu saja Anda dapat melakukan hal yang sama dan hanya menambah
// ini hanya di rute tertentu.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// lebih banyak rute
}, [ new SecurityHeadersMiddleware() ]);
```


## Serangan Permintaan Lintas Situs (CSRF)

Serangan Permintaan Lintas Situs (CSRF) adalah jenis serangan di mana situs web jahat dapat membuat browser pengguna mengirim permintaan ke situs web Anda. 
Ini dapat digunakan untuk melakukan tindakan di situs web Anda tanpa sepengetahuan pengguna. Flight tidak menyediakan mekanisme perlindungan CSRF 
bawaan, tetapi Anda dapat dengan mudah mengimplementasikan sendiri menggunakan middleware.

### Setup

Pertama Anda perlu menghasilkan token CSRF dan menyimpannya di sesi pengguna. Anda kemudian dapat menggunakan token ini dalam formulir Anda dan memeriksanya saat 
formulir diserahkan.

```php
// Menghasilkan token CSRF dan menyimpannya di sesi pengguna
// (asumsikan Anda telah membuat objek sesi dan mengaitkannya dengan Flight)
// lihat dokumentasi sesi untuk informasi lebih lanjut
Flight::register('session', \Ghostff\Session\Session::class);

// Anda hanya perlu menghasilkan satu token per sesi (agar berfungsi 
// di beberapa tab dan permintaan untuk pengguna yang sama)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- Gunakan token CSRF di formulir Anda -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- field formulir lainnya -->
</form>
```

#### Menggunakan Latte

Anda juga dapat mengatur fungsi kustom untuk menampilkan token CSRF dalam template Latte Anda.

```php
// Atur fungsi kustom untuk menampilkan token CSRF
// Catatan: View telah dikonfigurasi dengan Latte sebagai mesin tampilan
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

Dan sekarang di template Latte Anda, Anda dapat menggunakan fungsi `csrf()` untuk menampilkan token CSRF.

```html
<form method="post">
	{csrf()}
	<!-- field formulir lainnya -->
</form>
```

Singkat dan sederhana, bukan?

### Periksa Token CSRF

Anda dapat memeriksa token CSRF menggunakan filter peristiwa:

```php
// Middleware ini memeriksa apakah permintaan adalah permintaan POST dan jika iya, memeriksa apakah token CSRF valid
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// tangkap token csrf dari nilai formulir
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Token CSRF tidak valid');
			// atau untuk respons JSON
			Flight::jsonHalt(['error' => 'Token CSRF tidak valid'], 403);
		}
	}
});
```

Atau Anda dapat menggunakan kelas middleware:

```php
// app/middleware/CsrfMiddleware.php

namespace app\middleware;

class CsrfMiddleware
{
	public function before(array $params): void
	{
		if(Flight::request()->method == 'POST') {
			$token = Flight::request()->data->csrf_token;
			if($token !== Flight::session()->get('csrf_token')) {
				Flight::halt(403, 'Token CSRF tidak valid');
			}
		}
	}
}

// index.php atau di mana pun Anda memiliki rute Anda
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// lebih banyak rute
}, [ new CsrfMiddleware() ]);
```

## Serangan Skrip Lintas Situs (XSS)

Serangan Skrip Lintas Situs (XSS) adalah jenis serangan di mana situs web jahat dapat menyuntikkan kode ke situs web Anda. Sebagian besar peluang ini datang 
dari nilai formulir yang akan diisi oleh pengguna akhir Anda. Anda **tidak pernah** mempercayai output dari pengguna Anda! Selalu anggap semua dari 
mereka adalah hacker terbaik di dunia. Mereka dapat menyuntikkan JavaScript atau HTML berbahaya ke halaman Anda. Kode ini dapat digunakan untuk mencuri informasi dari pengguna 
Anda atau melakukan tindakan di situs web Anda. Dengan menggunakan kelas view dari Flight, Anda dapat dengan mudah melarikan output untuk mencegah serangan XSS.

```php
// Mari kita anggap pengguna cerdas dan mencoba menggunakan ini sebagai nama mereka
$name = '<script>alert("XSS")</script>';

// Ini akan melarikan output
Flight::view()->set('name', $name);
// Ini akan menampilkan: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Jika Anda menggunakan sesuatu seperti Latte yang terdaftar sebagai kelas view Anda, ini juga akan otomatis melarikan ini.
Flight::view()->render('template', ['name' => $name]);
```

## SQL Injection

SQL Injection adalah jenis serangan di mana pengguna jahat dapat menyuntikkan kode SQL ke dalam database Anda. Ini dapat digunakan untuk mencuri informasi 
dari database Anda atau melakukan tindakan di database Anda. Sekali lagi Anda **tidak pernah** mempercayai input dari pengguna Anda! Selalu anggap mereka 
berniat buruk. Anda dapat menggunakan pernyataan terprepared dalam objek `PDO` Anda untuk mencegah SQL injection.

```php
// Menganggap Anda memiliki Flight::db() terdaftar sebagai objek PDO Anda
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Jika Anda menggunakan kelas PdoWrapper, ini dapat dengan mudah dilakukan dalam satu baris
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Anda dapat melakukan hal yang sama dengan objek PDO dengan placeholder ?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Janji Anda tidak akan pernah MELAKUKAN sesuatu seperti ini...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// karena bagaimana jika $username = "' OR 1=1; -- "; 
// Setelah kueri dibangun, terlihat seperti ini
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Ini terlihat aneh, tetapi ini adalah kueri yang valid yang akan berfungsi. 
// Faktanya,
// ini adalah serangan SQL injection yang sangat umum yang akan mengembalikan semua pengguna.
```

## CORS

Cross-Origin Resource Sharing (CORS) adalah mekanisme yang memungkinkan banyak sumber daya (misalnya, font, JavaScript, dll.) di halaman web untuk 
diminta dari domain lain di luar domain tempat sumber daya tersebut berasal. Flight tidak memiliki fungsi bawaan, 
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
		// sesuaikan host yang diizinkan Anda di sini.
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

// index.php atau di mana pun Anda memiliki rute Anda
$CorsUtil = new CorsUtil();

// Ini perlu dijalankan sebelum start dijalankan.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

## Penanganan Kesalahan
Sembunyikan detail kesalahan sensitif di produksi untuk menghindari kebocoran informasi kepada penyerang.

```php
// Di bootstrap.php atau index.php Anda

// di flightphp/skeleton, ini ada di app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Nonaktifkan tampilan error
    ini_set('log_errors', 1);     // Catat kesalahan sebagai gantinya
    ini_set('error_log', '/path/to/error.log');
}

// Di rute atau pengontrol Anda
// Gunakan Flight::halt() untuk respons kesalahan yang terkendali
Flight::halt(403, 'Akses ditolak');
```

## Sanitasi Input
Jangan pernah mempercayai input pengguna. Sanitasi sebelum memproses untuk mencegah data berbahaya masuk.

```php

// Menganggap permintaan $_POST dengan $_POST['input'] dan $_POST['email']

// Sanitasi input string
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Sanitasi email
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

## Hashing Password
Simpan password dengan aman dan verifikasi dengan aman menggunakan fungsi bawaan PHP.

```php
$password = Flight::request()->data->password;
// Hash password saat menyimpan (misalnya, saat pendaftaran)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Verifikasi password (misalnya, saat login)
if (password_verify($password, $stored_hash)) {
    // Password cocok
}
```

## Pembatasan Kecepatan
Lindungi terhadap serangan brute force dengan membatasi laju permintaan menggunakan cache.

```php
// Menganggap Anda telah menginstal dan mendaftarkan flightphp/cache
// Menggunakan flightphp/cache dalam middleware
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Terlalu banyak permintaan');
    }
    
    $cache->set($key, $attempts + 1, 60); // Reset setelah 60 detik
});
```

## Kesimpulan

Keamanan adalah hal yang penting dan penting untuk memastikan aplikasi web Anda aman. Flight menyediakan sejumlah fitur untuk membantu Anda 
mengamankan aplikasi web Anda, tetapi penting untuk selalu waspada dan memastikan Anda melakukan segala sesuatu yang Anda bisa untuk menjaga data pengguna Anda tetap aman. Selalu anggap yang terburuk dan jangan pernah mempercayai input dari pengguna Anda. Selalu melarikan output dan gunakan pernyataan terprepared untuk mencegah SQL 
injection. Selalu gunakan middleware untuk melindungi rute Anda dari serangan CSRF dan CORS. Jika Anda melakukan semua hal ini, Anda akan berada di jalur yang tepat untuk membangun aplikasi web yang aman.