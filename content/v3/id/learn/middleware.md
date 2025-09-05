# Middleware Rute

Flight mendukung middleware rute dan grup middleware rute. Middleware adalah fungsi yang dieksekusi sebelum (atau setelah) callback rute. Ini adalah cara yang bagus untuk menambahkan pemeriksaan otentikasi API dalam kode Anda, atau untuk memvalidasi bahwa pengguna memiliki izin untuk mengakses rute.

## Middleware Dasar

Berikut adalah contoh dasar:

```php
// Jika Anda hanya menyediakan fungsi anonim, itu akan dieksekusi sebelum callback rute. 
// tidak ada fungsi middleware "setelah" kecuali untuk kelas (lihat di bawah)
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// Ini akan menghasilkan output "Middleware first! Here I am!"
```

Ada beberapa catatan penting tentang middleware yang harus Anda ketahui sebelum menggunakannya:
- Fungsi middleware dieksekusi dalam urutan mereka ditambahkan ke rute. Eksekusi mirip dengan bagaimana [Slim Framework handles this](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Befores dieksekusi dalam urutan yang ditambahkan, dan Afters dieksekusi dalam urutan terbalik.
- Jika fungsi middleware Anda mengembalikan false, semua eksekusi dihentikan dan error 403 Forbidden dilemparkan. Anda mungkin ingin menangani ini dengan lebih anggun menggunakan `Flight::redirect()` atau sesuatu yang serupa.
- Jika Anda memerlukan parameter dari rute Anda, parameter tersebut akan dikirimkan dalam satu array ke fungsi middleware Anda. (`function($params) { ... }` atau `public function before($params) {}`). Alasan untuk ini adalah Anda dapat menyusun parameter Anda menjadi kelompok dan dalam beberapa kelompok tersebut, parameter Anda mungkin muncul dalam urutan yang berbeda yang akan merusak fungsi middleware dengan merujuk pada parameter yang salah. Dengan cara ini, Anda dapat mengaksesnya berdasarkan nama daripada posisi.
- Jika Anda hanya meneruskan nama middleware, itu akan secara otomatis dieksekusi oleh [dependency injection container](dependency-injection-container) dan middleware akan dieksekusi dengan parameter yang dibutuhkannya. Jika Anda tidak memiliki dependency injection container yang terdaftar, itu akan meneruskan instance `flight\Engine` ke `__construct()`.

## Kelas Middleware

Middleware juga dapat didaftarkan sebagai kelas. Jika Anda memerlukan fungsionalitas "setelah", Anda **harus** menggunakan kelas.

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
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); // juga ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Ini akan menampilkan "Middleware first! Here I am! Middleware last!"
```

## Menangani Error Middleware

Katakanlah Anda memiliki middleware autentikasi dan Anda ingin mengarahkan pengguna ke halaman login jika mereka tidak terautentikasi. Anda memiliki beberapa opsi yang tersedia:

1. Anda dapat mengembalikan false dari fungsi middleware dan Flight akan secara otomatis mengembalikan error 403 Forbidden, tetapi tanpa kustomisasi.
1. Anda dapat mengarahkan pengguna ke halaman login menggunakan `Flight::redirect()`.
1. Anda dapat membuat error khusus dalam middleware dan menghentikan eksekusi rute.

### Contoh Dasar

Berikut adalah contoh sederhana return false;:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// karena itu benar, segala sesuatu hanya terus berlanjut
	}
}
```

### Contoh Pengarahan

Berikut adalah contoh mengarahkan pengguna ke halaman login:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### Contoh Error Khusus

Katakanlah Anda perlu melemparkan error JSON karena Anda sedang membangun API. Anda dapat melakukannya seperti ini:
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->headers['Authorization'];
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

## Mengelompokkan Middleware

Anda dapat menambahkan grup rute, dan kemudian setiap rute dalam grup tersebut akan memiliki middleware yang sama. Ini berguna jika Anda perlu mengelompokkan banyak rute dengan, katakanlah, middleware Auth untuk memeriksa kunci API di header.

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