# Autoloading

Autoloading adalah konsep dalam PHP di mana Anda menentukan satu direktori atau beberapa direktori untuk memuat kelas. Ini jauh lebih menguntungkan daripada menggunakan `require` atau `include` untuk memuat kelas. Ini juga merupakan persyaratan untuk menggunakan paket Composer.

Secara default, setiap kelas `Flight` dimuat otomatis untuk Anda berkat composer. Namun, jika Anda ingin memuat otomatis kelas Anda sendiri, Anda dapat menggunakan metode `Flight::path()` untuk menentukan direktori yang akan memuat kelas.

## Contoh Dasar

Mari kita anggap kita memiliki pohon direktori seperti berikut:

```text
# Contoh path
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - berisi kontroler untuk proyek ini
│   ├── translations
│   ├── UTILS - berisi kelas untuk aplikasi ini saja (ini huruf kapital semua dengan tujuan sebagai contoh nanti)
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

Anda mungkin telah memperhatikan bahwa ini adalah struktur file yang sama dengan situs dokumentasi ini.

Anda dapat menentukan setiap direktori untuk dimuat dari seperti ini:

```php

/**
 * public/index.php
 */

// Tambahkan path ke autoloader
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// tidak ada namespace yang diperlukan

// Semua kelas yang dimuat otomatis disarankan untuk menggunakan Pascal Case (setiap kata dengan huruf kapital, tanpa spasi)
// Mulai dari 3.7.2, Anda dapat menggunakan Pascal_Snake_Case untuk nama kelas Anda dengan menjalankan Loader::setV2ClassLoading(false);
class MyController {

	public function index() {
		// lakukan sesuatu
	}
}
```

## Namespace

Jika Anda memiliki namespace, sebenarnya menjadi sangat mudah untuk menerapkan ini. Anda harus menggunakan metode `Flight::path()` untuk menentukan direktori root (bukan document root atau folder `public/`) aplikasi Anda.

```php

/**
 * public/index.php
 */

// Tambahkan path ke autoloader
Flight::path(__DIR__.'/../');
```

Sekarang inilah yang mungkin terlihat seperti kontroler Anda. Lihat contoh di bawah ini, tetapi perhatikan komentar untuk informasi penting.

```php
/**
 * app/controllers/MyController.php
 */

// namespace diperlukan
// namespace sama dengan struktur direktori
// namespace harus mengikuti huruf besar yang sama dengan struktur direktori
// namespace dan direktori tidak dapat memiliki garis bawah (kecuali Loader::setV2ClassLoading(false) diset)
namespace app\controllers;

// Semua kelas yang dimuat otomatis disarankan untuk menggunakan Pascal Case (setiap kata dengan huruf kapital, tanpa spasi)
// Mulai dari 3.7.2, Anda dapat menggunakan Pascal_Snake_Case untuk nama kelas Anda dengan menjalankan Loader::setV2ClassLoading(false);
class MyController {

	public function index() {
		// lakukan sesuatu
	}
}
```

Dan jika Anda ingin memuat otomatis kelas di direktori utilitas Anda, Anda akan melakukan hal yang hampir sama:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// namespace harus cocok dengan struktur direktori dan huruf besar (perhatikan direktori UTILS semua huruf kapital
//     seperti pada pohon file di atas)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// lakukan sesuatu
	}
}
```

## Garis Bawah dalam Nama Kelas

Mulai dari 3.7.2, Anda dapat menggunakan Pascal_Snake_Case untuk nama kelas Anda dengan menjalankan `Loader::setV2ClassLoading(false);`. 
Ini akan memungkinkan Anda untuk menggunakan garis bawah dalam nama kelas Anda. 
Ini tidak disarankan, tetapi tersedia untuk mereka yang membutuhkannya.

```php

/**
 * public/index.php
 */

// Tambahkan path ke autoloader
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');
Loader::setV2ClassLoading(false);

/**
 * app/controllers/My_Controller.php
 */

// tidak ada namespace yang diperlukan

class My_Controller {

	public function index() {
		// lakukan sesuatu
	}
}
```