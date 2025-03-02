# Middleware Rute

Flight mendukung middleware rute dan group rute. Middleware adalah fungsi yang dijalankan sebelum (atau setelah) callback rute. Ini adalah cara yang bagus untuk menambahkan pemeriksaan otentikasi API dalam kode Anda, atau untuk memvalidasi bahwa pengguna memiliki izin untuk mengakses rute tersebut.

## Middleware Dasar

Berikut adalah contoh dasar:

```php
// Jika Anda hanya menyediakan fungsi anonim, itu akan dieksekusi sebelum callback rute. 
// tidak ada fungsi middleware "setelah" kecuali untuk kelas (lihat di bawah)
Flight::route('/path', function() { echo ' Di sini saya!'; })->addMiddleware(function() {
	echo 'Middleware pertama!';
});

Flight::start();

// Ini akan menghasilkan "Middleware pertama! Di sini saya!"
```

Ada beberapa catatan yang sangat penting tentang middleware yang harus Anda ketahui sebelum menggunakannya:
- Fungsi middleware dieksekusi dalam urutan mereka ditambahkan ke rute. Eksekusi mirip dengan bagaimana [Slim Framework menangani ini](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Sebelum dieksekusi dalam urutan ditambahkan, dan Setelah dieksekusi dalam urutan terbalik.
- Jika fungsi middleware Anda mengembalikan false, semua eksekusi dihentikan dan kesalahan 403 Forbidden akan dibuang. Anda mungkin ingin menangani ini secara lebih halus dengan `Flight::redirect()` atau sesuatu yang serupa.
- Jika Anda perlu parameter dari rute Anda, mereka akan diteruskan dalam satu array ke fungsi middleware Anda. (`function($params) { ... }` atau `public function before($params) {}`). Alasan untuk ini adalah Anda dapat menyusun parameter Anda dalam kelompok dan dalam beberapa kelompok tersebut, parameter Anda mungkin sebenarnya muncul dalam urutan yang berbeda yang akan merusak fungsi middleware dengan merujuk pada parameter yang salah. Dengan cara ini, Anda dapat mengaksesnya berdasarkan nama alih-alih posisi.
- Jika Anda hanya memberikan nama middleware, itu akan secara otomatis dieksekusi oleh [kontainer injeksi ketergantungan](dependency-injection-container) dan middleware akan dieksekusi dengan parameter yang dibutuhkannya. Jika Anda tidak memiliki kontainer injeksi ketergantungan yang terdaftar, itu akan meneruskan instance `flight\Engine` ke `__construct()`.

## Kelas Middleware

Middleware dapat terdaftar sebagai kelas juga. Jika Anda perlu fungsi "setelah", Anda **harus** menggunakan kelas.

```php
class MyMiddleware {
	public function before($params) {
		echo 'Middleware pertama!';
	}

	public function after($params) {
		echo 'Middleware terakhir!';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/path', function() { echo ' Di sini saya! '; })->addMiddleware($MyMiddleware); // juga ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Ini akan menampilkan "Middleware pertama! Di sini saya! Middleware terakhir!"
```

## Menangani Kesalahan Middleware

Misalkan Anda memiliki middleware otentikasi dan Anda ingin mengalihkan pengguna ke halaman login jika mereka tidak 
terautentikasi. Anda memiliki beberapa opsi di tangan Anda:

1. Anda dapat mengembalikan false dari fungsi middleware dan Flight akan secara otomatis mengembalikan kesalahan 403 Forbidden, tetapi tidak ada kustomisasi.
1. Anda dapat mengalihkan pengguna ke halaman login menggunakan `Flight::redirect()`.
1. Anda dapat membuat kesalahan kustom dalam middleware dan menghentikan eksekusi rute.

### Contoh Dasar

Ini adalah contoh sederhana return false;:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// karena ini benar, semuanya akan terus berjalan
	}
}
```

### Contoh Redirect

Ini adalah contoh mengalihkan pengguna ke halaman login:
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

### Contoh Kesalahan Kustom

Misalkan Anda perlu membuang kesalahan JSON karena Anda sedang membangun API. Anda dapat melakukannya seperti ini:
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->headers['Authorization'];
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'Anda harus masuk untuk mengakses halaman ini.'], 403);
			// atau
			Flight::json(['error' => 'Anda harus masuk untuk mengakses halaman ini.'], 403);
			exit;
			// atau
			Flight::halt(403, json_encode(['error' => 'Anda harus masuk untuk mengakses halaman ini.']));
		}
	}
}
```

## Pengelompokan Middleware

Anda dapat menambahkan grup rute, dan kemudian setiap rute dalam grup itu akan memiliki middleware yang sama juga. Ini 
berguna jika Anda perlu mengelompokkan sejumlah rute dengan misalnya middleware Auth untuk memeriksa kunci API di header.

```php

// ditambahkan di akhir metode grup
Flight::group('/api', function() {

	// Rute yang "kosong" ini sebenarnya akan mencocokkan /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// Ini akan mencocokkan /api/users
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Ini akan mencocokkan /api/users/1234
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
}, [ new ApiAuthMiddleware() ]);
```