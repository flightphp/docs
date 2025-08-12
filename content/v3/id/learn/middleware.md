# Rute Perantara

Flight mendukung rute dan kelompok rute perantara. Perantara adalah fungsi yang dieksekusi sebelum (atau setelah) callback rute. 
Ini adalah cara yang bagus untuk menambahkan pemeriksaan otentikasi API dalam kode Anda, atau untuk memvalidasi bahwa pengguna memiliki izin 
untuk mengakses rute tersebut.

## Perantara Dasar

Berikut adalah contoh dasar:

```php
// Jika Anda hanya memberikan fungsi anonim, itu akan dieksekusi sebelum callback rute. 
// tidak ada fungsi perantara "setelah" kecuali untuk kelas (lihat di bawah)
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// Ini akan menghasilkan output "Middleware first! Here I am!"
```

Ada beberapa catatan penting tentang perantara yang harus Anda ketahui sebelum menggunakannya:
- Fungsi perantara dieksekusi sesuai urutan penambahan ke rute. Eksekusi mirip dengan cara [Slim Framework menanganinya](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Perantara sebelum dieksekusi sesuai urutan penambahan, dan perantara setelah dieksekusi dalam urutan terbalik.
- Jika fungsi perantara Anda mengembalikan false, semua eksekusi dihentikan dan error 403 Forbidden dilemparkan. Anda mungkin ingin menangani ini dengan lebih halus menggunakan `Flight::redirect()` atau sesuatu yang serupa.
- Jika Anda memerlukan parameter dari rute Anda, parameter tersebut akan dikirimkan dalam satu array ke fungsi perantara Anda. (`function($params) { ... }` atau `public function before($params) {}`). Alasan untuk ini adalah Anda dapat menyusun parameter ke dalam kelompok dan dalam beberapa kelompok tersebut, parameter Anda mungkin muncul dalam urutan yang berbeda yang dapat merusak fungsi perantara dengan merujuk pada parameter yang salah. Dengan cara ini, Anda dapat mengaksesnya berdasarkan nama daripada posisi.
- Jika Anda hanya meneruskan nama perantara, itu akan secara otomatis dieksekusi oleh [kontainer injeksi dependensi](dependency-injection-container) dan perantara akan dieksekusi dengan parameter yang dibutuhkan. Jika Anda tidak memiliki kontainer injeksi dependensi yang terdaftar, itu akan meneruskan instance `flight\Engine` ke `__construct()`.

## Kelas Perantara

Perantara juga dapat didaftarkan sebagai kelas. Jika Anda memerlukan fungsionalitas "setelah", Anda **harus** menggunakan kelas.

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

## Menangani Error Perantara

Katakanlah Anda memiliki perantara otentikasi dan Anda ingin mengarahkan pengguna ke halaman login jika mereka tidak 
terotentikasi. Anda memiliki beberapa opsi yang tersedia:

1. Anda dapat mengembalikan false dari fungsi perantara dan Flight akan secara otomatis mengembalikan error 403 Forbidden, tetapi tanpa penyesuaian.
1. Anda dapat mengarahkan pengguna ke halaman login menggunakan `Flight::redirect()`.
1. Anda dapat membuat error khusus dalam perantara dan menghentikan eksekusi rute.

### Contoh Dasar

Berikut adalah contoh sederhana return false;:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// karena itu benar, semuanya hanya terus berjalan
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
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']));
		}
	}
}
```

## Mengelompokkan Perantara

Anda dapat menambahkan kelompok rute, dan kemudian setiap rute dalam kelompok tersebut akan memiliki perantara yang sama. Ini berguna 
jika Anda perlu mengelompokkan banyak rute dengan, katakanlah, perantara Auth untuk memeriksa kunci API di header.

```php
// ditambahkan di akhir metode group
Flight::group('/api', function() {

	// Rute "kosong" ini sebenarnya akan cocok dengan /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// Ini akan cocok dengan /api/users
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Ini akan cocok dengan /api/users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

Jika Anda ingin menerapkan perantara global ke semua rute Anda, Anda dapat menambahkan kelompok "kosong":

```php
// ditambahkan di akhir metode group
Flight::group('', function() {

	// Ini tetap /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Dan ini tetap /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // atau [ new ApiAuthMiddleware() ], hal yang sama
```