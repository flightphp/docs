# Keamanan

Keamanan adalah hal yang sangat penting ketika datang ke aplikasi web. Anda ingin memastikan bahwa aplikasi Anda aman dan bahwa data pengguna Anda 
berada dalam keadaan aman. Flight menyediakan sejumlah fitur untuk membantu Anda mengamankan aplikasi web Anda.

## Header

Header HTTP adalah salah satu cara termudah untuk mengamankan aplikasi web Anda. Anda dapat menggunakan header untuk mencegah clickjacking, XSS, dan serangan lainnya. 
Ada beberapa cara yang dapat Anda lakukan untuk menambahkan header ini ke aplikasi Anda.

Dua situs web hebat untuk memeriksa keamanan header Anda adalah [securityheaders.com](https://securityheaders.com/) dan 
[observatory.mozilla.org](https://observatory.mozilla.org/).

### Tambahkan Secara Manual

Anda dapat menambahkan header ini secara manual dengan menggunakan metode `header` pada objek `Flight\Response`.
```php
// Setel header X-Frame-Options untuk mencegah clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Setel header Content-Security-Policy untuk mencegah XSS
// Catatan: header ini dapat menjadi sangat kompleks, jadi Anda ingin
// berkonsultasi dengan contoh di internet untuk aplikasi Anda
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Setel header X-XSS-Protection untuk mencegah XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Setel header X-Content-Type-Options untuk mencegah MIME sniffing
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Setel header Referrer-Policy untuk mengontrol seberapa banyak informasi referrer yang dikirim
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Setel header Strict-Transport-Security untuk memaksa HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Setel header Permissions-Policy untuk mengontrol fitur dan API mana yang dapat digunakan
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Header ini dapat ditambahkan di bagian atas file `bootstrap.php` atau `index.php` Anda.

### Tambahkan sebagai Filter

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

### Tambahkan sebagai Middleware

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
// semua rute. Tentu saja, Anda bisa melakukan hal yang sama dan hanya menambahkan
// ini hanya pada rute tertentu.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// lebih banyak rute
}, [ new SecurityHeadersMiddleware() ]);
```


## Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF) adalah jenis serangan di mana situs web jahat dapat membuat browser pengguna mengirim permintaan ke situs web Anda. 
Ini dapat digunakan untuk melakukan tindakan di situs web Anda tanpa sepengetahuan pengguna. Flight tidak menyediakan mekanisme perlindungan CSRF bawaan, 
tetapi Anda dapat dengan mudah mengimplementasikan sendiri dengan menggunakan middleware.

### Pengaturan

Pertama, Anda perlu menghasilkan token CSRF dan menyimpannya di sesi pengguna. Anda kemudian dapat menggunakan token ini di formulir Anda dan memeriksa saat 
formulir dikirimkan.

```php
// Hasilkan token CSRF dan simpan di sesi pengguna
// (mengasumsikan Anda telah membuat objek sesi dan melampirkannya ke Flight)
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

Anda juga dapat mengatur fungsi khusus untuk menampilkan token CSRF di template Latte Anda.

```php
// Atur fungsi khusus untuk menampilkan token CSRF
// Catatan: Tampilan telah dikonfigurasi dengan Latte sebagai mesin tampilan
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

Anda dapat memeriksa token CSRF menggunakan filter acara:

```php
// Middleware ini memeriksa apakah permintaan adalah permintaan POST dan jika ya, memeriksa apakah token CSRF valid
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// ambil token csrf dari nilai formulir
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Token CSRF tidak valid');
			// atau untuk respon JSON
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

// index.php atau di mana pun Anda memiliki rute
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// lebih banyak rute
}, [ new CsrfMiddleware() ]);
```

## Cross Site Scripting (XSS)

Cross Site Scripting (XSS) adalah jenis serangan di mana situs web jahat dapat menyisipkan kode ke situs web Anda. Sebagian besar peluang ini muncul 
dari nilai formulir yang akan diisi oleh pengguna akhir Anda. Anda **tidak boleh** pernah mempercayai output dari pengguna Anda! Selalu anggap semua dari mereka adalah 
pengacau terbaik di dunia. Mereka dapat menyisipkan JavaScript atau HTML berbahaya ke dalam halaman Anda. Kode ini dapat digunakan untuk mencuri informasi dari pengguna Anda 
atau melakukan tindakan di situs web Anda. Menggunakan kelas tampilan Flight, Anda dapat dengan mudah melarikan output untuk mencegah serangan XSS.

```php
// Mari kita anggap pengguna cukup cerdas dan mencoba menggunakan ini sebagai nama mereka
$name = '<script>alert("XSS")</script>';

// Ini akan melarikan output
Flight::view()->set('name', $name);
// Ini akan menghasilkan: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Jika Anda menggunakan sesuatu seperti Latte yang terdaftar sebagai kelas tampilan Anda, itu juga akan secara otomatis melarikan ini.
Flight::view()->render('template', ['name' => $name]);
```

## SQL Injection

SQL Injection adalah jenis serangan di mana pengguna jahat dapat menyisipkan kode SQL ke dalam database Anda. Ini dapat digunakan untuk mencuri informasi 
dari database Anda atau melakukan tindakan di database Anda. Sekali lagi, Anda **tidak boleh** mempercayai input dari pengguna Anda! Selalu anggap mereka 
mencari darah. Anda dapat menggunakan pernyataan yang dipersiapkan dalam objek `PDO` Anda untuk mencegah injeksi SQL.

```php
// Mengasumsikan Anda memiliki Flight::db() terdaftar sebagai objek PDO Anda
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Jika Anda menggunakan kelas PdoWrapper, ini dapat dengan mudah dilakukan dalam satu baris
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Anda dapat melakukan hal yang sama dengan objek PDO dengan placeholder ?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Hanya janjikan Anda tidak akan PERNAH melakukan sesuatu seperti ini...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// karena bagaimana jika $username = "' OR 1=1; -- "; 
// Setelah kueri dibangun, sepertinya ini
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Sepertinya aneh, tetapi itu adalah kueri yang valid yang akan berhasil. Faktanya,
// ini adalah serangan injeksi SQL yang sangat umum yang akan mengembalikan semua pengguna.
```

## CORS

Cross-Origin Resource Sharing (CORS) adalah mekanisme yang memungkinkan banyak sumber daya (misalnya, font, JavaScript, dll.) pada halaman web untuk 
diminta dari domain lain di luar domain dari mana sumber daya itu berasal. Flight tidak memiliki fungsi bawaan, 
tetapi ini dapat dengan mudah ditangani dengan hook yang dijalankan sebelum metode `Flight::start()` dipanggil.

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

// Ini perlu dijalankan sebelum mulai berjalan.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

## Kesimpulan

Keamanan adalah hal yang sangat penting dan penting untuk memastikan aplikasi web Anda aman. Flight menyediakan sejumlah fitur untuk membantu Anda 
mengamankan aplikasi web Anda, tetapi penting untuk selalu waspada dan memastikan Anda melakukan semua yang Anda bisa untuk menjaga data pengguna Anda 
aman. Selalu anggap yang terburuk dan jangan pernah mempercayai input dari pengguna Anda. Selalu melarikan output dan gunakan pernyataan yang dipersiapkan untuk mencegah 
injeksi SQL. Selalu gunakan middleware untuk melindungi rute Anda dari serangan CSRF dan CORS. Jika Anda melakukan semua hal ini, Anda akan berada di jalur yang baik untuk membangun aplikasi web yang aman.