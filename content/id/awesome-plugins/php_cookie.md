# Cookies

[overclokk/cookie](https://github.com/overclokk/cookie) adalah perpustakaan sederhana untuk mengelola cookie dalam aplikasi Anda.

## Instalasi

Instalasi sangat sederhana dengan composer.

```bash
composer require overclokk/cookie
```

## Penggunaan

Penggunaan semudah mendaftarkan metode baru pada kelas Flight.

```php
use Overclokk\Cookie\Cookie;

/*
 * Set di file bootstrap atau public/index.php Anda
 */

Flight::register('cookie', Cookie::class);

/**
 * ExampleController.php
 */

class ExampleController {
	public function login() {
		// Set sebuah cookie

		// Anda ingin ini menjadi false agar Anda mendapatkan instance baru
		// gunakan komentar di bawah jika Anda ingin autocomplete
		/** @var \Overclokk\Cookie\Cookie $cookie */
		$cookie = Flight::cookie(false);
		$cookie->set(
			'stay_logged_in', // nama cookie
			'1', // nilai yang ingin Anda atur
			86400, // jumlah detik cookie harus bertahan
			'/', // jalur yang akan tersedia untuk cookie
			'example.com', // domain yang akan tersedia untuk cookie
			true, // cookie hanya akan ditransmisikan melalui koneksi HTTPS yang aman
			true // cookie hanya akan tersedia melalui protokol HTTP
		);

		// opsional, jika Anda ingin mempertahankan nilai default
		// dan memiliki cara cepat untuk mengatur cookie untuk waktu yang lama
		$cookie->forever('stay_logged_in', '1');
	}

	public function home() {
		// Periksa apakah Anda memiliki cookie
		if (Flight::cookie()->has('stay_logged_in')) {
			// tempatkan mereka di area dasbor misalnya.
			Flight::redirect('/dashboard');
		}
	}
}