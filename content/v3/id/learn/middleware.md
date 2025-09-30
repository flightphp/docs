# Middleware

## Ikhtisar

Flight mendukung middleware rute dan grup rute. Middleware adalah bagian dari aplikasi Anda di mana kode dieksekusi sebelum 
(atau setelah) callback rute. Ini adalah cara yang bagus untuk menambahkan pemeriksaan autentikasi API dalam kode Anda, atau untuk memvalidasi bahwa 
pengguna memiliki izin untuk mengakses rute.

## Pemahaman

Middleware dapat sangat menyederhanakan aplikasi Anda. Alih-alih pewarisan kelas abstrak yang kompleks atau override metode, middleware 
memungkinkan Anda mengontrol rute dengan menetapkan logika aplikasi kustom terhadapnya. Anda dapat membayangkan middleware seperti
sebuah sandwich. Anda memiliki roti di luar, dan kemudian lapisan topik seperti selada, tomat, daging dan keju. Kemudian bayangkan
seperti setiap permintaan adalah seperti menggigit sandwich di mana Anda makan lapisan luar terlebih dahulu dan bekerja menuju inti.

Berikut adalah visualisasi bagaimana middleware bekerja. Kemudian kami akan menunjukkan kepada Anda contoh praktis bagaimana ini berfungsi.

```text
Permintaan pengguna di URL /api ----> 
	Middleware->before() dieksekusi ----->
		Callable/method yang terpasang ke /api dieksekusi dan respons dihasilkan ------>
	Middleware->after() dieksekusi ----->
Pengguna menerima respons dari server
```

Dan berikut adalah contoh praktis:

```text
Pengguna menavigasi ke URL /dashboard
	LoggedInMiddleware->before() dieksekusi
		before() memeriksa sesi login yang valid
			jika ya lakukan tidak ada dan lanjutkan eksekusi
			jika tidak arahkan pengguna ke /login
				Callable/method yang terpasang ke /api dieksekusi dan respons dihasilkan
	LoggedInMiddleware->after() tidak memiliki apa pun yang didefinisikan sehingga membiarkan eksekusi berlanjut
Pengguna menerima HTML dashboard dari server
```

### Urutan Eksekusi

Fungsi middleware dieksekusi dalam urutan mereka ditambahkan ke rute. Eksekusi mirip dengan bagaimana [Slim Framework menangani ini](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).

Metode `before()` dieksekusi dalam urutan ditambahkan, dan metode `after()` dieksekusi dalam urutan terbalik.

Contoh: Middleware1->before(), Middleware2->before(), Middleware2->after(), Middleware1->after().

## Penggunaan Dasar

Anda dapat menggunakan middleware sebagai metode callable apa pun termasuk fungsi anonim atau kelas (direkomendasikan)

### Fungsi Anonim

Berikut adalah contoh sederhana:

```php
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// Ini akan menghasilkan "Middleware first! Here I am!"
```

> **Catatan:** Saat menggunakan fungsi anonim, satu-satunya metode yang diinterpretasikan adalah metode `before()`. Anda **tidak bisa** mendefinisikan perilaku `after()` dengan kelas anonim.

### Menggunakan Kelas

Middleware dapat (dan harus) didaftarkan sebagai kelas. Jika Anda membutuhkan fungsionalitas "after", Anda **harus** menggunakan kelas.

```php
class MyMiddleware {
	public function before($params) {
		echo 'Middleware first!';
	}

	public function after($params) {
		echo 'Middleware last!';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); 
// juga ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Ini akan menampilkan "Middleware first! Here I am! Middleware last!"
```

Anda juga hanya bisa mendefinisikan nama kelas middleware dan itu akan menginstansiasi kelas.

```php
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware(MyMiddleware::class); 
```

> **Catatan:** Jika Anda hanya memasukkan nama middleware, itu akan secara otomatis dieksekusi oleh [dependency injection container](dependency-injection-container) dan middleware akan dieksekusi dengan parameter yang dibutuhkan. Jika Anda tidak memiliki dependency injection container yang terdaftar, itu akan memasukkan instance `flight\Engine` ke dalam `__construct(Engine $app)` secara default.

### Menggunakan Rute dengan Parameter

Jika Anda membutuhkan parameter dari rute Anda, mereka akan diteruskan dalam satu array ke fungsi middleware Anda. (`function($params) { ... }` atau `public function before($params) { ... }`). Alasan untuk ini adalah bahwa Anda dapat menyusun parameter Anda menjadi grup dan dalam beberapa grup tersebut, parameter Anda mungkin muncul dalam urutan yang berbeda yang akan merusak fungsi middleware dengan merujuk ke parameter yang salah. Dengan cara ini, Anda dapat mengaksesnya berdasarkan nama bukan posisi.

```php
use flight\Engine;

class RouteSecurityMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$clientId = $params['clientId'];

		// jobId mungkin atau mungkin tidak diteruskan
		$jobId = $params['jobId'] ?? 0;

		// mungkin jika tidak ada ID pekerjaan, Anda tidak perlu mencari apa pun.
		if($jobId === 0) {
			return;
		}

		// lakukan pencarian semacamnya di database Anda
		$isValid = !!$this->app->db()->fetchField("SELECT 1 FROM client_jobs WHERE client_id = ? AND job_id = ?", [ $clientId, $jobId ]);

		if($isValid !== true) {
			$this->app->halt(400, 'You are blocked, muahahaha!');
		}
	}
}

// routes.php
$router->group('/client/@clientId/job/@jobId', function(Router $router) {

	// Grup ini di bawah masih mendapatkan middleware parent
	// Tapi parameter diteruskan dalam satu array tunggal 
	// di middleware.
	$router->group('/job/@jobId', function(Router $router) {
		$router->get('', [ JobController::class, 'view' ]);
		$router->put('', [ JobController::class, 'update' ]);
		$router->delete('', [ JobController::class, 'delete' ]);
		// lebih banyak rute...
	});
}, [ RouteSecurityMiddleware::class ]);
```

### Mengelompokkan Rute dengan Middleware

Anda dapat menambahkan grup rute, dan kemudian setiap rute dalam grup itu akan memiliki middleware yang sama juga. Ini 
berguna jika Anda perlu mengelompokkan banyak rute berdasarkan middleware Auth untuk memeriksa kunci API di header.

```php

// ditambahkan di akhir metode grup
Flight::group('/api', function() {

	// Rute "kosong" ini sebenarnya akan cocok dengan /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// Ini akan cocok dengan /api/users
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Ini akan cocok dengan /api/users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

Jika Anda ingin menerapkan middleware global ke semua rute Anda, Anda dapat menambahkan grup "kosong":

```php

// ditambahkan di akhir metode grup
Flight::group('', function() {

	// Ini masih /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Dan ini masih /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // atau [ new ApiAuthMiddleware() ], hal yang sama
```

### Kasus Penggunaan Umum

#### Validasi Kunci API
Jika Anda ingin melindungi rute `/api` Anda dengan memverifikasi kunci API yang benar, Anda dapat dengan mudah menanganinya dengan middleware.

```php
use flight\Engine;

class ApiMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}
	
	public function before(array $params) {
		$authorizationHeader = $this->app->request()->getHeader('Authorization');
		$apiKey = str_replace('Bearer ', '', $authorizationHeader);

		// lakukan pencarian di database Anda untuk kunci api
		$apiKeyHash = hash('sha256', $apiKey);
		$hasValidApiKey = !!$this->db()->fetchField("SELECT 1 FROM api_keys WHERE hash = ? AND valid_date >= NOW()", [ $apiKeyHash ]);

		if($hasValidApiKey !== true) {
			$this->app->jsonHalt(['error' => 'Invalid API Key']);
		}
	}
}

// routes.php
$router->group('/api', function(Router $router) {
	$router->get('/users', [ ApiController::class, 'getUsers' ]);
	$router->get('/companies', [ ApiController::class, 'getCompanies' ]);
	// lebih banyak rute...
}, [ ApiMiddleware::class ]);
```

Sekarang semua rute API Anda dilindungi oleh middleware validasi kunci API yang Anda siapkan! Jika Anda memasukkan lebih banyak rute ke dalam grup router, mereka akan langsung memiliki perlindungan yang sama!

#### Validasi Login

Apakah Anda ingin melindungi beberapa rute agar hanya tersedia untuk pengguna yang login? Itu dapat dengan mudah dicapai dengan middleware!

```php
use flight\Engine;

class LoggedInMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$session = $this->app->session();
		if($session->get('logged_in') !== true) {
			$this->app->redirect('/login');
			exit;
		}
	}
}

// routes.php
$router->group('/admin', function(Router $router) {
	$router->get('/dashboard', [ DashboardController::class, 'index' ]);
	$router->get('/clients', [ ClientController::class, 'index' ]);
	// lebih banyak rute...
}, [ LoggedInMiddleware::class ]);
```

#### Validasi Parameter Rute

Apakah Anda ingin melindungi pengguna Anda dari mengubah nilai di URL untuk mengakses data yang seharusnya tidak mereka akses? Itu dapat diselesaikan dengan middleware!

```php
use flight\Engine;

class RouteSecurityMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$clientId = $params['clientId'];
		$jobId = $params['jobId'];

		// lakukan pencarian semacamnya di database Anda
		$isValid = !!$this->app->db()->fetchField("SELECT 1 FROM client_jobs WHERE client_id = ? AND job_id = ?", [ $clientId, $jobId ]);

		if($isValid !== true) {
			$this->app->halt(400, 'You are blocked, muahahaha!');
		}
	}
}

// routes.php
$router->group('/client/@clientId/job/@jobId', function(Router $router) {
	$router->get('', [ JobController::class, 'view' ]);
	$router->put('', [ JobController::class, 'update' ]);
	$router->delete('', [ JobController::class, 'delete' ]);
	// lebih banyak rute...
}, [ RouteSecurityMiddleware::class ]);
```

## Menangani Eksekusi Middleware

Misalkan Anda memiliki middleware auth dan Anda ingin mengarahkan pengguna ke halaman login jika mereka tidak 
terautentikasi. Anda memiliki beberapa opsi yang tersedia:

1. Anda dapat mengembalikan false dari fungsi middleware dan Flight akan secara otomatis mengembalikan kesalahan 403 Forbidden, tapi tidak ada kustomisasi.
1. Anda dapat mengarahkan pengguna ke halaman login menggunakan `Flight::redirect()`.
1. Anda dapat membuat kesalahan kustom dalam middleware dan menghentikan eksekusi rute.

### Sederhana dan Langsung

Berikut adalah contoh sederhana `return false;` :

```php
class MyMiddleware {
	public function before($params) {
		$hasUserKey = Flight::session()->exists('user');
		if ($hasUserKey === false) {
			return false;
		}

		// karena itu benar, semuanya terus berjalan
	}
}
```

### Contoh Pengalihan

Berikut adalah contoh mengarahkan pengguna ke halaman login:
```php
class MyMiddleware {
	public function before($params) {
		$hasUserKey = Flight::session()->exists('user');
		if ($hasUserKey === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### Contoh Kesalahan Kustom

Misalkan Anda perlu melempar kesalahan JSON karena Anda membangun API. Anda dapat melakukannya seperti ini:
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->getHeader('Authorization');
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'You must be logged in to access this page.'], 403);
			// atau
			Flight::json(['error' => 'You must be logged in to access this page.'], 403);
			exit;
			// atau
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']);
		}
	}
}
```

## Lihat Juga
- [Routing](/learn/routing) - Cara memetakan rute ke controller dan merender tampilan.
- [Requests](/learn/requests) - Memahami cara menangani permintaan masuk.
- [Responses](/learn/responses) - Cara menyesuaikan respons HTTP.
- [Dependency Injection](/learn/dependency-injection-container) - Menyederhanakan pembuatan dan pengelolaan objek di rute.
- [Mengapa Framework?](/learn/why-frameworks) - Memahami manfaat menggunakan framework seperti Flight.
- [Contoh Strategi Eksekusi Middleware](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)

## Pemecahan Masalah
- Jika Anda memiliki pengalihan di middleware Anda, tapi aplikasi Anda tampaknya tidak mengalihkan, pastikan Anda menambahkan pernyataan `exit;` di middleware Anda.

## Changelog
- v3.1: Menambahkan dukungan untuk middleware.