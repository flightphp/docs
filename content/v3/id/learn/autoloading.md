# Autoloading

## Gambaran Umum

Autoloading adalah konsep di PHP di mana Anda menentukan direktori atau direktori untuk memuat kelas dari. Ini jauh lebih bermanfaat daripada menggunakan `require` atau `include` untuk memuat kelas. Ini juga merupakan persyaratan untuk menggunakan paket Composer.

## Pemahaman

Secara default, kelas `Flight` apa pun dimuat secara otomatis untuk Anda berkat composer. Namun, jika Anda ingin memuat kelas Anda sendiri secara otomatis, Anda dapat menggunakan metode `Flight::path()` untuk menentukan direktori untuk memuat kelas dari.

Menggunakan autoloader dapat membantu menyederhanakan kode Anda secara signifikan. Alih-alih memiliki file yang dimulai dengan berbagai pernyataan `include` atau `require` di bagian atas untuk menangkap semua kelas yang digunakan dalam file tersebut, Anda dapat secara dinamis memanggil kelas Anda dan mereka akan disertakan secara otomatis.

## Penggunaan Dasar

Misalkan kita memiliki pohon direktori seperti berikut:

```text
# Contoh path
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - berisi controller untuk proyek ini
│   ├── translations
│   ├── UTILS - berisi kelas untuk aplikasi ini saja (semua huruf kapital dengan sengaja untuk contoh nanti)
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

Anda mungkin telah memperhatikan bahwa ini adalah struktur file yang sama dengan situs dokumentasi ini.

Anda dapat menentukan setiap direktori untuk dimuat seperti ini:

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

// tidak diperlukan namespacing

// Semua kelas yang dimuat secara otomatis disarankan menggunakan Pascal Case (setiap kata dikapitalisasi, tanpa spasi)
class MyController {

	public function index() {
		// lakukan sesuatu
	}
}
```

## Namespaces

Jika Anda memiliki namespace, sebenarnya sangat mudah untuk mengimplementasikannya. Anda harus menggunakan metode `Flight::path()` untuk menentukan direktori root (bukan document root atau folder `public/`) dari aplikasi Anda.

```php

/**
 * public/index.php
 */

// Tambahkan path ke autoloader
Flight::path(__DIR__.'/../');
```

Sekarang ini adalah tampilan controller Anda. Lihat contoh di bawah ini, tetapi perhatikan komentar untuk informasi penting.

```php
/**
 * app/controllers/MyController.php
 */

// namespace diperlukan
// namespace sama dengan struktur direktori
// namespace harus mengikuti case yang sama dengan struktur direktori
// namespace dan direktori tidak boleh memiliki underscore (kecuali Loader::setV2ClassLoading(false) diatur)
namespace app\controllers;

// Semua kelas yang dimuat secara otomatis disarankan menggunakan Pascal Case (setiap kata dikapitalisasi, tanpa spasi)
// Mulai dari 3.7.2, Anda dapat menggunakan Pascal_Snake_Case untuk nama kelas Anda dengan menjalankan Loader::setV2ClassLoading(false);
class MyController {

	public function index() {
		// lakukan sesuatu
	}
}
```

Dan jika Anda ingin memuat kelas di direktori utils Anda secara otomatis, Anda akan melakukan hal yang sama secara dasar:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// namespace harus cocok dengan struktur direktori dan case (perhatikan direktori UTILS semua huruf kapital
//     seperti di pohon file di atas)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// lakukan sesuatu
	}
}
```

## Underscores di Nama Kelas

Mulai dari 3.7.2, Anda dapat menggunakan Pascal_Snake_Case untuk nama kelas Anda dengan menjalankan `Loader::setV2ClassLoading(false);`. 
Ini akan memungkinkan Anda menggunakan underscore di nama kelas Anda. 
Ini tidak disarankan, tetapi tersedia untuk mereka yang membutuhkannya.

```php
use flight\core\Loader;

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

// tidak diperlukan namespacing

class My_Controller {

	public function index() {
		// lakukan sesuatu
	}
}
```

## Lihat Juga
- [Routing](/learn/routing) - Cara memetakan rute ke controller dan merender views.
- [Mengapa Framework?](/learn/why-frameworks) - Memahami manfaat menggunakan framework seperti Flight.

## Pemecahan Masalah
- Jika Anda tidak dapat mengetahui mengapa kelas namespaced Anda tidak ditemukan, ingatlah untuk menggunakan `Flight::path()` ke direktori root di proyek Anda, bukan direktori `app/` atau `src/` atau setara.

### Kelas Tidak Ditemukan (autoloading tidak berfungsi)

Ada beberapa alasan mengapa ini bisa terjadi. Di bawah ini beberapa contoh tetapi pastikan Anda juga memeriksa bagian [autoloading](/learn/autoloading).

#### Nama File Salah
Yang paling umum adalah nama kelas tidak cocok dengan nama file.

Jika Anda memiliki kelas bernama `MyClass` maka file harus bernama `MyClass.php`. Jika Anda memiliki kelas bernama `MyClass` dan file bernama `myclass.php` 
maka autoloader tidak akan dapat menemukannya.

#### Namespace Salah
Jika Anda menggunakan namespace, maka namespace harus cocok dengan struktur direktori.

```php
// ...code...

// jika MyController Anda berada di direktori app/controllers dan bernamespace
// ini tidak akan berfungsi.
Flight::route('/hello', 'MyController->hello');

// Anda perlu memilih salah satu opsi ini
Flight::route('/hello', 'app\controllers\MyController->hello');
// atau jika Anda memiliki pernyataan use di atas

use app\controllers\MyController;

Flight::route('/hello', [ MyController::class, 'hello' ]);
// juga bisa ditulis
Flight::route('/hello', MyController::class.'->hello');
// juga...
Flight::route('/hello', [ 'app\controllers\MyController', 'hello' ]);
```

#### `path()` tidak didefinisikan

Di aplikasi skeleton, ini didefinisikan di dalam file `config.php`, tetapi agar kelas Anda ditemukan, Anda perlu memastikan bahwa metode `path()`
didefinisikan (mungkin ke root direktori Anda) sebelum Anda mencoba menggunakannya.

```php
// Tambahkan path ke autoloader
Flight::path(__DIR__.'/../');
```

## Changelog
- v3.7.2 - Anda dapat menggunakan Pascal_Snake_Case untuk nama kelas Anda dengan menjalankan `Loader::setV2ClassLoading(false);`
- v2.0 - Fungsi autoload ditambahkan.