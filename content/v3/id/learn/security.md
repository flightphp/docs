# Keamanan

## Ikhtisar

Keamanan adalah hal yang sangat penting dalam aplikasi web. Anda ingin memastikan bahwa aplikasi Anda aman dan data pengguna Anda aman. Flight menyediakan sejumlah fitur untuk membantu Anda mengamankan aplikasi web Anda.

[Skeleton](https://github.com/flightphp/skeleton) resmi juga menyertakan **`SECURITY.md`** khusus dan middleware security-headers sehingga [alat coding AI](/learn/ai) (dan manusia) memiliki satu tempat yang disengaja untuk rahasia, header, dan aturan XSS/SQL—terpisah dari gaya coding umum di `AGENTS.md`.

## Pemahaman

Ada sejumlah ancaman keamanan umum yang harus Anda waspadai saat membangun aplikasi web. Beberapa ancaman paling umum meliputi:
- Cross Site Request Forgery (CSRF)
- Cross Site Scripting (XSS)
- SQL Injection
- Cross Origin Resource Sharing (CORS)

[Templat](/learn/templates) membantu mencegah XSS dengan melakukan escape output secara default (Twig dan Latte melakukan ini; manfaatkan keunggulan tersebut). [Sesi](/awesome-plugins/session) dapat membantu mencegah CSRF dengan menyimpan token CSRF di sesi pengguna seperti dijelaskan di bawah. Menggunakan prepared statement dengan PDO—atau helper pada [SimplePdo](/learn/simple-pdo)—membantu mencegah SQL injection. CORS dapat ditangani dengan hook sederhana sebelum `Flight::start()` dipanggil.

Semua metode ini bekerja sama untuk membantu menjaga aplikasi web Anda tetap aman. Penting untuk selalu mempelajari dan memahami praktik terbaik keamanan. Jangan meminta asisten AI untuk "menonaktifkan CSP" atau melemahkan header hanya agar halaman dapat dimuat tanpa memahami konsekuensinya.

## Penggunaan Dasar

### Header

HTTP header adalah salah satu cara termudah untuk mengamankan aplikasi web Anda. Anda dapat menggunakan header untuk mencegah clickjacking, XSS, dan serangan lainnya. Ada beberapa cara untuk menambahkan header ini ke aplikasi Anda.

Dua situs web yang bagus untuk memeriksa keamanan header Anda adalah [securityheaders.com](https://securityheaders.com/) dan [observatory.mozilla.org](https://observatory.mozilla.org/). Setelah Anda menyiapkan kode di bawah, Anda dapat dengan mudah memverifikasi bahwa header Anda berfungsi dengan kedua situs web tersebut.

Skeleton menyertakan `App\Middleware\SecurityHeadersMiddleware` (CSP dengan nonce per-permintaan, opsi frame, HSTS, dan lainnya). Lebih baik memperluas itu secara sengaja daripada mematikan header.

#### Tambahkan Secara Manual

Anda dapat menambahkan header ini secara manual dengan menggunakan metode `header` pada objek `Flight\Response`.
```php
// Setel header X-Frame-Options untuk mencegah clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Setel header Content-Security-Policy untuk mencegah XSS
// Catatan: header ini bisa sangat kompleks, jadi Anda sebaiknya
//  mencari contoh di internet untuk aplikasi Anda
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Setel header X-XSS-Protection untuk mencegah XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Setel header X-Content-Type-Options untuk mencegah MIME sniffing
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Setel header Referrer-Policy untuk mengontrol berapa banyak informasi referrer yang dikirim
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Setel header Strict-Transport-Security untuk memaksa HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Setel header Permissions-Policy untuk mengontrol fitur dan API apa yang dapat digunakan
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

Anda juga dapat menambahkannya sebagai kelas middleware yang memberikan fleksibilitas terbesar untuk rute mana yang akan diterapkan. Secara umum, header ini harus diterapkan pada semua respons HTML dan API.

Path dan namespace gaya skeleton (**huruf folder cocok dengan `App\Middleware`**):

```php
// app/Middleware/SecurityHeadersMiddleware.php

namespace App\Middleware;

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
		// Utamakan nonce CSP dari bootstrap saat Anda memiliki skrip inline (skeleton menyetel csp_nonce)
		$nonce = $this->app->get('csp_nonce');
		$csp = $nonce
			? "default-src 'self'; script-src 'self' 'nonce-{$nonce}'; style-src 'self' 'nonce-{$nonce}'"
			: "default-src 'self'";

		$response->header('X-Frame-Options', 'SAMEORIGIN');
		$response->header('Content-Security-Policy', $csp);
		$response->header('X-XSS-Protection', '1; mode=block');
		$response->header('X-Content-Type-Options', 'nosniff');
		$response->header('Referrer-Policy', 'no-referrer-when-downgrade');
		$response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		$response->header('Permissions-Policy', 'geolocation=()');
	}
}

// app/config/routes.php — grup string kosong = middleware global untuk semua rute
use App\Middleware\SecurityHeadersMiddleware;
use flight\net\Router;

$router->group('', function (Router $router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// rute lainnya
}, [SecurityHeadersMiddleware::class]);
```

Proyek lama mungkin masih menggunakan `app/middlewares` dan `app\middlewares`; itu berfungsi jika foldernya cocok. Aplikasi skeleton baru menggunakan **`app/Middleware/`** dan **`App\Middleware`**. Lihat [Autoloading](/learn/autoloading).

### Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF) adalah jenis serangan di mana situs web berbahaya dapat membuat browser pengguna mengirim permintaan ke situs web Anda. Ini dapat digunakan untuk melakukan tindakan di situs web Anda tanpa sepengetahuan pengguna. Flight tidak menyediakan mekanisme perlindungan CSRF bawaan, tetapi Anda dapat dengan mudah mengimplementasikannya sendiri menggunakan middleware.

#### Penyiapan

Pertama Anda perlu membuat token CSRF dan menyimpannya di sesi pengguna. Anda kemudian dapat menggunakan token ini di formulir Anda dan memeriksanya saat formulir dikirimkan. Kami akan menggunakan plugin [flightphp/session](/awesome-plugins/session) untuk mengelola sesi.

```php
// Buat token CSRF dan simpan di sesi pengguna
// (asumsikan Anda telah membuat objek sesi dan melampirkannya ke Flight)
// lihat dokumentasi sesi untuk informasi lebih lanjut
Flight::register('session', flight\Session::class);

// Anda hanya perlu membuat satu token per sesi (sehingga berfungsi
// di beberapa tab dan permintaan untuk pengguna yang sama)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### Menggunakan Templat PHP Flight Default

```html
<!-- Gunakan token CSRF di formulir Anda -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- bidang formulir lainnya -->
</form>
```

##### Menggunakan Twig (default skeleton)

Daftarkan fungsi Twig atau berikan token ke setiap tampilan formulir. Contoh minimal dengan global + bidang formulir:

```php
// Saat mengonfigurasi Twig (mis. services.php)
$twig->addGlobal('csrf_token', $app->session()->get('csrf_token'));
```

```html
{# app/views/form.twig #}
<form method="post">
	<input type="hidden" name="csrf_token" value="{{ csrf_token }}">
	{# bidang lainnya #}
</form>
```

##### Menggunakan Latte

Anda juga dapat mengatur fungsi kustom untuk menampilkan token CSRF di templat Latte Anda.

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// konfigurasi lainnya...

	// Setel fungsi kustom untuk menampilkan token CSRF
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

Dan sekarang di templat Latte Anda, Anda dapat menggunakan fungsi `csrf()` untuk menampilkan token CSRF.

```html
<form method="post">
	{csrf()}
	<!-- bidang formulir lainnya -->
</form>
```

#### Periksa Token CSRF

Anda dapat memeriksa token CSRF menggunakan beberapa metode.

##### Middleware

```php
// app/Middleware/CsrfMiddleware.php

namespace App\Middleware;

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

// routes.php
use App\Middleware\CsrfMiddleware;

$router->group('', function ($router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// rute lainnya
}, [CsrfMiddleware::class]);
```

##### Filter Event

```php
// Middleware ini memeriksa apakah permintaan adalah permintaan POST dan jika ya, memeriksa apakah token CSRF valid
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// ambil token csrf dari nilai formulir
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

Cross Site Scripting (XSS) adalah jenis serangan di mana input formulir berbahaya dapat menyuntikkan kode ke situs web Anda. Sebagian besar peluang ini berasal dari nilai formulir yang akan diisi oleh pengguna akhir Anda. Anda **tidak boleh** mempercayai output dari pengguna Anda! Selalu anggap mereka semua adalah hacker terbaik di dunia. Mereka dapat menyuntikkan JavaScript atau HTML berbahaya ke halaman Anda. Kode ini dapat digunakan untuk mencuri informasi dari pengguna Anda atau melakukan tindakan di situs web Anda. Menggunakan kelas view Flight atau mesin templat seperti [Twig](/awesome-plugins/twig) atau [Latte](/awesome-plugins/latte), Anda dapat dengan mudah melakukan escape output untuk mencegah serangan XSS.

```php
// Anggap saja pengguna cukup pintar dan mencoba menggunakan ini sebagai nama mereka
$name = '<script>alert("XSS")</script>';

// Ini akan melakukan escape output
Flight::view()->set('name', $name);
// Ini akan menghasilkan: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Twig (default skeleton) dan Latte otomatis melakukan escape secara default — utamakan keduanya daripada echo PHP mentah
Flight::render('template', ['name' => $name]);
// Twig: {{ name }}  → di-escape
// Hindari |raw / output tanpa escape kecuali konten sepenuhnya tepercaya
```

### SQL Injection

SQL Injection adalah jenis serangan di mana pengguna berbahaya dapat menyuntikkan kode SQL ke database Anda. Ini dapat digunakan untuk mencuri informasi dari database Anda atau melakukan tindakan pada database Anda. Sekali lagi Anda **tidak boleh** mempercayai input dari pengguna Anda! Selalu anggap mereka punya niat jahat. Gunakan prepared statement—helper [SimplePdo](/learn/simple-pdo) menjadikan ini jalur default.

```php
// Asumsikan Anda telah mendaftarkan Flight::db() sebagai SimplePdo (atau menyuntikkan SimplePdo di controller)
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// SimplePdo (disukai) — satu baris dengan parameter terikat
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Ide yang sama dengan placeholder ?
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

Dalam controller gaya skeleton, utamakan injeksi konstruktor `SimplePdo` daripada `Flight::db()` agar pengujian dan kode yang dihasilkan AI tetap konsisten ([DIC](/learn/dependency-injection-container)).

#### Contoh Tidak Aman

Di bawah ini adalah alasan mengapa kita menggunakan prepared statement SQL untuk melindungi dari contoh polos seperti di bawah:

```php
// pengguna akhir mengisi formulir web.
// untuk nilai formulir, hacker memasukkan sesuatu seperti ini:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// Setelah query dibangun, tampilannya seperti ini
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// Terlihat aneh, tapi itu query valid yang akan berfungsi. Faktanya,
// ini adalah serangan SQL injection yang sangat umum yang akan mengembalikan semua pengguna.

var_dump($users); // ini akan membuang semua pengguna di database, bukan hanya satu username
```

### Rahasia dan konfigurasi

- Letakkan rahasia di **`.env`** (atau environment sebenarnya), bukan di contoh `config.php` yang di-commit.
- Aturan skeleton: default literal di `config.php`; gabungkan env saat bootstrap; **jangan** baca `$_ENV` di dalam controller—inject konfigurasi sebagai gantinya. Lihat [Konfigurasi](/learn/configuration).
- Jangan pernah melakukan commit API key, password DB, atau kunci enkripsi sesi. Arahkan alat AI ke **`SECURITY.md`** agar mereka tidak menciptakan jalan pintas yang tidak aman.

### Validasi Callback JSONP

Jika Anda menggunakan metode `Flight::jsonp()`, ketahuilah bahwa Flight memvalidasi nama parameter callback JSONP terhadap regex allowlist yang ketat (`/^[A-Za-z_$][\w$.]{0,127}$/`). Nama callback apa pun yang tidak cocok dengan pola ini akan menyebabkan Flight melempar exception, mencegah injeksi JavaScript arbitrer melalui nilai callback berbahaya.

Validasi ini sudah bawaan dan tidak memerlukan konfigurasi tambahan, tetapi perlu diketahui saat men-debug error tak terduga dari endpoint JSONP.

### CORS

Cross-Origin Resource Sharing (CORS) adalah mekanisme yang memungkinkan banyak sumber daya (mis., font, JavaScript, dll.) pada halaman web diminta dari domain lain di luar domain asal sumber daya tersebut. Flight tidak memiliki fungsionalitas bawaan, tetapi ini dapat dengan mudah ditangani dengan hook yang dijalankan sebelum metode `Flight::start()` dipanggil.

```php
// app/Utils/CorsUtil.php  (skeleton: folder Utils PascalCase → App\Utils)

namespace App\Utils;

use flight\Engine;

class CorsUtil
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function set(array $params = []): void
	{
		$request = $this->app->request();
		$response = $this->app->response();
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

		$request = $this->app->request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $allowed, true) === true) {
			$response = $this->app->response();
			$response->header("Access-Control-Allow-Origin", $request->getVar('HTTP_ORIGIN'));
		}
	}
}

// bootstrap / routes — jalankan sebelum start
$app = Flight::app();
$cors = new \App\Utils\CorsUtil($app);
$app->before('start', [ $cors, 'set' ]);
```

### Pengerasan Konfigurasi Flight

Flight mengekspos beberapa pengaturan engine yang memiliki implikasi keamanan langsung. Menyetel ini dengan benar adalah salah satu cara termudah untuk mengeraskan aplikasi Anda.

#### `flight.allow_method_override`

Secara default, Flight memungkinkan klien untuk menimpa metode HTTP dari permintaan menggunakan header `X-HTTP-Method-Override` atau bidang `_method` dalam body POST. Meskipun ini berguna untuk formulir HTML yang hanya dapat mengirim `GET`/`POST`, ini bisa berbahaya jika Anda tidak mengharapkannya — penyerang dapat memalsukan permintaan `DELETE` atau `PUT` melalui formulir biasa.

Jika aplikasi Anda tidak bergantung pada perilaku ini (mis. Anda membangun API yang digunakan oleh klien modern atau frontend JavaScript yang dapat mengirim verb HTTP apa pun), Anda harus menonaktifkannya:

```php
// Di index.php atau file bootstrap Anda, sebelum Flight::start()
Flight::set('flight.allow_method_override', false);
```

Nilai default adalah `true` untuk kompatibilitas mundur, tetapi **menyetelnya ke `false` sangat disarankan** untuk aplikasi apa pun yang tidak secara eksplisit membutuhkan fitur override.

#### `flight.debug`

Flight memiliki pengaturan `flight.debug` yang mengontrol apakah informasi error terperinci (pesan exception, kode, dan stack trace lengkap) ditampilkan di browser ketika terjadi exception yang tidak tertangani. Defaultnya adalah `false`, yang berarti hanya pesan `500 Internal Server Error` umum yang ditampilkan — tidak ada detail internal yang bocor ke klien.

Jangan pernah mengaktifkan ini di server produksi. Gunakan hanya secara lokal atau di lingkungan staging:

```php
// Aman hanya untuk pengembangan lokal — JANGAN di produksi
Flight::set('flight.debug', true);
```

Ketika `flight.debug` adalah `false` (default), Anda masih dapat menangkap error dengan mengaktifkan `flight.log_errors`:

```php
// Catat error di sisi server tanpa mengeksposnya ke klien
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
```

#### `flight.views.restrict_to_path`

Kelas `View` bawaan Flight akan dengan senang hati menyertakan templat dari path absolut, atau dari nama relatif yang keluar dari `flight.views.path` (misalnya dengan `../`). Itu disengaja untuk aplikasi yang sengaja berbagi templat antar folder, tetapi itu juga merupakan risiko path traversal jika nama templat pernah berasal dari input yang tidak tepercaya.

`flight.views.restrict_to_path` **mati secara default** sehingga aplikasi yang ada tetap berfungsi. Aktifkan kecuali Anda memiliki alasan terdokumentasi untuk tidak melakukannya:

```php
// Di index.php atau file bootstrap Anda, sebelum Flight::start()
Flight::set('flight.views.restrict_to_path', true);
```

Engine menerapkan pengaturan itu ke `View::$restrictToPath` saat view dibuat (pola yang sama dengan `flight.views.path` dan `flight.views.extension`). Dengan ini aktif:

- `render()` dan `fetch()` hanya menyertakan file yang path aslinya berada di dalam direktori views yang dikonfigurasi (symlink yang menunjuk ke luar juga ditolak).
- `exists()` mengembalikan `false` untuk path yang sama alih-alih melempar exception.
- `getTemplate()` sendiri tidak berubah — ia tetap mengembalikan path seperti biasanya.
- File yang diblokir melempar `Template file is outside the views path.` File yang hilang tetap melempar pesan `Template file not found: ...` yang sudah ada.

Jika Anda menggunakan Twig atau Latte dengan loader filesystem mereka sendiri yang diarahkan ke direktori views Anda, engine tersebut sudah membatasi templat ke root itu. Tetap aktifkan ini untuk `View` native Flight agar kode apa pun yang memanggil `Flight::view()->render()` / `fetch()` mendapatkan perlindungan yang sama. [Skeleton](https://github.com/flightphp/skeleton) resmi mengaktifkannya di bootstrap.

#### Konfigurasi produksi yang disarankan

```php
// index.php atau diterapkan dari konfigurasi aplikasi / bootstrap
Flight::set('flight.allow_method_override', false);
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
Flight::set('flight.views.restrict_to_path', true);
```

### Penanganan Error
Sembunyikan detail error sensitif di produksi untuk menghindari kebocoran info ke penyerang. Di produksi, catat error alih-alih menampilkannya dengan `display_errors` disetel ke `0`.

```php
// Di bootstrap.php atau index.php Anda

// tambahkan ini ke app/config/config.php Anda
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Nonaktifkan tampilan error
    ini_set('log_errors', 1);     // Catat error sebagai gantinya
    ini_set('error_log', '/path/to/error.log');
}

// Di rute atau controller Anda
// Gunakan Flight::halt() untuk respons error yang terkendali
Flight::halt(403, 'Access denied');
```

### Sanitasi Input
Jangan pernah mempercayai input pengguna. Sanitasi menggunakan [filter_var](https://www.php.net/manual/en/function.filter-var.php) sebelum diproses untuk mencegah data berbahaya menyelinap masuk. Utamakan membaca input melalui `$app->request()` (atau `Flight::request()`) daripada `$_GET` / `$_POST` mentah di kode aplikasi.

```php

// Anggap saja ada permintaan $_POST dengan $_POST['input'] dan $_POST['email']

// Sanitasi input string
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Sanitasi email
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### Hashing Password
Simpan password dengan aman dan verifikasi dengan aman menggunakan fungsi bawaan PHP seperti [password_hash](https://www.php.net/manual/en/function.password-hash.php) dan [password_verify](https://www.php.net/manual/en/function.password-verify.php). Password tidak boleh disimpan dalam teks biasa, juga tidak boleh dienkripsi dengan metode yang dapat dibalik. Hashing memastikan bahwa bahkan jika database Anda disusupi, password sebenarnya tetap terlindungi.

```php
$password = Flight::request()->data->password;
// Hash password saat menyimpan (mis., selama registrasi)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Verifikasi password (mis., selama login)
if (password_verify($password, $stored_hash)) {
    // Password cocok
}
```

### Pembatasan Laju
Lindungi dari serangan brute force atau serangan denial-of-service dengan membatasi laju permintaan menggunakan cache.

```php
// Asumsikan Anda telah menginstal dan mendaftarkan flightphp/cache
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
- [Sesi](/awesome-plugins/session) - Cara mengelola sesi pengguna dengan aman.
- [Templat](/learn/templates) - Auto-escape Twig/Latte dan XSS.
- [SimplePdo](/learn/simple-pdo) - Helper database dengan prepared statement.
- [PdoWrapper](/learn/pdo-wrapper) - Usang; gunakan SimplePdo untuk kode baru.
- [Middleware](/learn/middleware) - Cara menggunakan middleware untuk menyederhanakan proses penambahan header keamanan.
- [Konfigurasi](/learn/configuration) - `.env` vs konfigurasi literal, flag produksi.
- [AI & Pengalaman Pengembang](/learn/ai) - Simpan kebijakan keamanan di `SECURITY.md` untuk agen.
- [Respons](/learn/responses) - Cara menyesuaikan respons HTTP dengan header aman.
- [Permintaan](/learn/requests) - Cara menangani dan menyanitasi input pengguna.
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - Fungsi PHP untuk sanitasi input.
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - Fungsi PHP untuk hashing password yang aman.
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - Fungsi PHP untuk memverifikasi password yang di-hash.

## Pemecahan Masalah
- Lihat bagian "Lihat Juga" di atas untuk informasi pemecahan masalah terkait masalah dengan komponen Framework Flight.
- Jika CSP memblokir skrip Anda, tambahkan nonce (pola skeleton) atau allowlist origin tertentu—jangan setel `script-src *` tanpa rencana.

## Changelog
- Docs – Skeleton `App\Middleware`, catatan CSRF/XSS Twig, SimplePdo, rahasia/`.env`, dan `SECURITY.md` untuk proyek ramah AI.
- Docs – Mendokumentasikan `flight.views.restrict_to_path` di bawah Pengerasan Konfigurasi Flight (path containment opt-in untuk view native).
- v3.18.1 - Menambahkan bagian Pengerasan Konfigurasi Flight yang mencakup `flight.allow_method_override`, `flight.debug`, dan validasi callback JSONP.
- v3.1.0 - Menambahkan bagian tentang CORS, Penanganan Error, Sanitasi Input, Hashing Password, dan Pembatasan Laju.
- v2.0 - Menambahkan escaping untuk view default untuk mencegah XSS.