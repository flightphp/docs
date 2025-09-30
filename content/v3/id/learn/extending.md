# Memperluas

## Gambaran Umum

Flight dirancang sebagai kerangka kerja yang dapat diperluas. Kerangka kerja ini dilengkapi dengan
seperangkat metode dan komponen default, tetapi memungkinkan Anda untuk memetakan metode Anda sendiri,
mendaftarkan kelas Anda sendiri, atau bahkan menimpa kelas dan metode yang ada.

## Pemahaman

Ada 2 cara yang dapat Anda gunakan untuk memperluas fungsionalitas Flight:

1. Pemetaan Metode - Ini digunakan untuk membuat metode kustom sederhana yang dapat Anda panggil
   dari mana saja di aplikasi Anda. Ini biasanya digunakan untuk fungsi utilitas
   yang ingin Anda panggil dari mana saja di kode Anda.
2. Pendaftaran Kelas - Ini digunakan untuk mendaftarkan kelas Anda sendiri dengan Flight. Ini
   biasanya digunakan untuk kelas yang memiliki dependensi atau memerlukan konfigurasi.

Anda juga dapat menimpa metode kerangka kerja yang ada untuk mengubah perilaku defaultnya agar lebih sesuai
dengan kebutuhan proyek Anda.

> Jika Anda mencari DIC (Dependency Injection Container), kunjungi halaman
[Dependency Injection Container](/learn/dependency-injection-container).

## Penggunaan Dasar

### Menimpa Metode Kerangka Kerja

Flight memungkinkan Anda menimpa fungsionalitas defaultnya agar sesuai dengan kebutuhan Anda sendiri,
tanpa harus memodifikasi kode apa pun. Anda dapat melihat semua metode yang dapat ditimpa [di bawah](#mappable-framework-methods).

Misalnya, ketika Flight tidak dapat mencocokkan URL dengan rute, ia memanggil metode `notFound`
yang mengirim respons `HTTP 404` generik. Anda dapat menimpa perilaku ini
dengan menggunakan metode `map`:

```php
Flight::map('notFound', function() {
  // Tampilkan halaman 404 kustom
  include 'errors/404.html';
});
```

Flight juga memungkinkan Anda untuk mengganti komponen inti kerangka kerja.
Misalnya, Anda dapat mengganti kelas Router default dengan kelas kustom Anda sendiri:

```php
// buat kelas Router kustom Anda
class MyRouter extends \flight\net\Router {
	// timpa metode di sini
	// misalnya pintasan untuk permintaan GET untuk menghapus
	// fitur rute pass
	public function get($pattern, $callback, $alias = '') {
		return parent::get($pattern, $callback, false, $alias);
	}
}

// Daftarkan kelas kustom Anda
Flight::register('router', MyRouter::class);

// Saat Flight memuat instance Router, ia akan memuat kelas Anda
$myRouter = Flight::router();
$myRouter->get('/hello', function() {
  echo "Hello World!";
}, 'hello_alias');
```

Namun, metode kerangka kerja seperti `map` dan `register` tidak dapat ditimpa. Anda akan
mendapat kesalahan jika mencoba melakukannya (lihat lagi [di bawah](#mappable-framework-methods) untuk daftar metode).

### Metode Kerangka Kerja yang Dapat Dipetakan

Berikut adalah kumpulan lengkap metode untuk kerangka kerja. Ini terdiri dari metode inti,
yang merupakan metode statis biasa, dan metode yang dapat diperluas, yang merupakan metode yang dipetakan yang dapat 
difilter atau ditimpa.

#### Metode Inti

Metode ini adalah inti dari kerangka kerja dan tidak dapat ditimpa.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Membuat metode kerangka kerja kustom.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Mendaftarkan kelas ke metode kerangka kerja.
Flight::unregister(string $name) // Membatalkan pendaftaran kelas ke metode kerangka kerja.
Flight::before(string $name, callable $callback) // Menambahkan filter sebelum metode kerangka kerja.
Flight::after(string $name, callable $callback) // Menambahkan filter setelah metode kerangka kerja.
Flight::path(string $path) // Menambahkan jalur untuk autoloading kelas.
Flight::get(string $key) // Mendapatkan variabel yang ditetapkan oleh Flight::set().
Flight::set(string $key, mixed $value) // Mengatur variabel dalam mesin Flight.
Flight::has(string $key) // Memeriksa apakah variabel telah ditetapkan.
Flight::clear(array|string $key = []) // Membersihkan variabel.
Flight::init() // Menginisialisasi kerangka kerja ke pengaturan defaultnya.
Flight::app() // Mendapatkan instance objek aplikasi
Flight::request() // Mendapatkan instance objek permintaan
Flight::response() // Mendapatkan instance objek respons
Flight::router() // Mendapatkan instance objek router
Flight::view() // Mendapatkan instance objek tampilan
```

#### Metode yang Dapat Diperluas

```php
Flight::start() // Memulai kerangka kerja.
Flight::stop() // Menghentikan kerangka kerja dan mengirim respons.
Flight::halt(int $code = 200, string $message = '') // Menghentikan kerangka kerja dengan kode status dan pesan opsional.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Memetakan pola URL ke callback.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Memetakan pola URL permintaan POST ke callback.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Memetakan pola URL permintaan PUT ke callback.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Memetakan pola URL permintaan PATCH ke callback.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Memetakan pola URL permintaan DELETE ke callback.
Flight::group(string $pattern, callable $callback) // Membuat pengelompokan untuk url, pola harus berupa string.
Flight::getUrl(string $name, array $params = []) // Menghasilkan URL berdasarkan alias rute.
Flight::redirect(string $url, int $code) // Mengarahkan ke URL lain.
Flight::download(string $filePath) // Mengunduh file.
Flight::render(string $file, array $data, ?string $key = null) // Merender file template.
Flight::error(Throwable $error) // Mengirim respons HTTP 500.
Flight::notFound() // Mengirim respons HTTP 404.
Flight::etag(string $id, string $type = 'string') // Melakukan caching HTTP ETag.
Flight::lastModified(int $time) // Melakukan caching HTTP last modified.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Mengirim respons JSON.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Mengirim respons JSONP.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Mengirim respons JSON dan menghentikan kerangka kerja.
Flight::onEvent(string $event, callable $callback) // Mendaftarkan pendengar acara.
Flight::triggerEvent(string $event, ...$args) // Memicu acara.
```

Metode kustom apa pun yang ditambahkan dengan `map` dan `register` juga dapat difilter. Untuk contoh tentang cara memfilter metode ini, lihat panduan [Filtering Methods](/learn/filtering).

#### Kelas Kerangka Kerja yang Dapat Diperluas

Ada beberapa kelas yang dapat Anda timpa fungsionalitasnya dengan memperluasnya dan
mendaftarkan kelas Anda sendiri. Kelas-kelas ini adalah:

```php
Flight::app() // Kelas Aplikasi - perluas kelas flight\Engine
Flight::request() // Kelas Permintaan - perluas kelas flight\net\Request
Flight::response() // Kelas Respons - perluas kelas flight\net\Response
Flight::router() // Kelas Router - perluas kelas flight\net\Router
Flight::view() // Kelas Tampilan - perluas kelas flight\template\View
Flight::eventDispatcher() // Kelas Event Dispatcher - perluas kelas flight\core\Dispatcher
```

### Pemetaan Metode Kustom

Untuk memetakan metode kustom sederhana Anda sendiri, gunakan fungsi `map`:

```php
// Petakan metode Anda
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// Panggil metode kustom Anda
Flight::hello('Bob');
```

Meskipun mungkin untuk membuat metode kustom sederhana, disarankan untuk hanya membuat
fungsi standar di PHP. Ini memiliki autocomplete di IDE dan lebih mudah dibaca.
Setara dengan kode di atas adalah:

```php
function hello(string $name) {
  echo "hello $name!";
}

hello('Bob');
```

Ini digunakan lebih banyak ketika Anda perlu meneruskan variabel ke metode Anda untuk mendapatkan nilai
yang diharapkan. Menggunakan metode `register()` seperti di bawah ini lebih untuk meneruskan konfigurasi
dan kemudian memanggil kelas yang telah dikonfigurasi sebelumnya.

### Pendaftaran Kelas Kustom

Untuk mendaftarkan kelas Anda sendiri dan mengonfigurasinya, gunakan fungsi `register`. Keuntungan yang dimiliki ini dibandingkan map() adalah Anda dapat menggunakan kembali kelas yang sama ketika Anda memanggil fungsi ini (akan membantu dengan `Flight::db()` untuk berbagi instance yang sama).

```php
// Daftarkan kelas Anda
Flight::register('user', User::class);

// Dapatkan instance kelas Anda
$user = Flight::user();
```

Metode register juga memungkinkan Anda untuk meneruskan parameter ke konstruktor kelas Anda.
Jadi ketika Anda memuat kelas kustom Anda, ia akan datang sudah diinisialisasi.
Anda dapat mendefinisikan parameter konstruktor dengan meneruskan array tambahan.
Berikut adalah contoh memuat koneksi database:

```php
// Daftarkan kelas dengan parameter konstruktor
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Dapatkan instance kelas Anda
// Ini akan membuat objek dengan parameter yang didefinisikan
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// dan jika Anda membutuhkannya nanti di kode Anda, Anda hanya memanggil metode yang sama lagi
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Jika Anda meneruskan parameter callback tambahan, ia akan dieksekusi segera
setelah konstruksi kelas. Ini memungkinkan Anda untuk melakukan prosedur penyiapan apa pun untuk objek
baru Anda. Fungsi callback mengambil satu parameter, instance objek baru.

```php
// Callback akan diteruskan objek yang dibuat
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Secara default, setiap kali Anda memuat kelas Anda, Anda akan mendapatkan instance yang dibagikan.
Untuk mendapatkan instance baru dari kelas, cukup teruskan `false` sebagai parameter:

```php
// Instance kelas yang dibagikan
$shared = Flight::db();

// Instance kelas yang baru
$new = Flight::db(false);
```

> **Catatan:** Ingatlah bahwa metode yang dipetakan memiliki prioritas atas kelas yang terdaftar. Jika Anda
menyatakan keduanya menggunakan nama yang sama, hanya metode yang dipetakan yang akan dipanggil.

### Contoh

Berikut adalah beberapa contoh tentang bagaimana Anda dapat memperluas Flight dengan fungsionalitas yang tidak ada di inti.

#### Logging

Flight tidak memiliki sistem logging bawaan, namun, sangat mudah
untuk menggunakan perpustakaan logging dengan Flight. Berikut adalah contoh menggunakan
perpustakaan Monolog:

```php
// services.php

// Daftarkan logger dengan Flight
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

Sekarang setelah terdaftar, Anda dapat menggunakannya di aplikasi Anda:

```php
// Di controller atau rute Anda
Flight::log()->warning('This is a warning message');
```

Ini akan mencatat pesan ke file log yang Anda tentukan. Bagaimana jika Anda ingin mencatat sesuatu ketika terjadi
kesalahan? Anda dapat menggunakan metode `error`:

```php
// Di controller atau rute Anda
Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// Tampilkan halaman kesalahan kustom Anda
	include 'errors/500.html';
});
```

Anda juga dapat membuat sistem APM (Application Performance Monitoring) dasar
menggunakan metode `before` dan `after`:

```php
// Di file services.php Anda

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('Request '.Flight::request()->url.' took ' . round($end - $start, 4) . ' seconds');

	// Anda juga dapat menambahkan header permintaan atau respons Anda
	// untuk mencatatnya juga (berhati-hatilah karena ini akan menjadi banyak data 
	// jika Anda memiliki banyak permintaan)
	Flight::log()->info('Request Headers: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('Response Headers: ' . json_encode(Flight::response()->headers));
});
```

#### Caching

Flight tidak memiliki sistem caching bawaan, namun, sangat mudah
untuk menggunakan perpustakaan caching dengan Flight. Berikut adalah contoh menggunakan
[PHP File Cache](/awesome-plugins/php_file_cache) library:

```php
// services.php

// Daftarkan cache dengan Flight
Flight::register('cache', \flight\Cache::class, [ __DIR__ . '/../cache/' ], function(\flight\Cache $cache) {
    $cache->setDevMode(ENVIRONMENT === 'development');
});
```

Sekarang setelah terdaftar, Anda dapat menggunakannya di aplikasi Anda:

```php
// Di controller atau rute Anda
$data = Flight::cache()->get('my_cache_key');
if (empty($data)) {
	// Lakukan pemrosesan untuk mendapatkan data
	$data = [ 'some' => 'data' ];
	Flight::cache()->set('my_cache_key', $data, 3600); // cache untuk 1 jam
}
```

#### Instantiation Objek DIC yang Mudah

Jika Anda menggunakan DIC (Dependency Injection Container) di aplikasi Anda,
Anda dapat menggunakan Flight untuk membantu Anda menginisialisasi objek Anda. Berikut adalah contoh menggunakan
perpustakaan [Dice](https://github.com/level-2/Dice):

```php
// services.php

// buat container baru
$container = new \Dice\Dice;
// jangan lupa untuk menugaskan ulang ke dirinya sendiri seperti di bawah!
$container = $container->addRule('PDO', [
	// shared berarti bahwa objek yang sama akan dikembalikan setiap kali
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// sekarang kita dapat membuat metode yang dapat dipetakan untuk membuat objek apa pun. 
Flight::map('make', function($class, $params = []) use ($container) {
	return $container->create($class, $params);
});

// Ini mendaftarkan penangan container sehingga Flight tahu untuk menggunakannya untuk controller/middleware
Flight::registerContainerHandler(function($class, $params) {
	Flight::make($class, $params);
});


// katakanlah kita memiliki kelas sampel berikut yang mengambil objek PDO di konstruktor
class EmailCron {
	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function send() {
		// kode yang mengirim email
	}
}

// Dan akhirnya Anda dapat membuat objek menggunakan dependency injection
$emailCron = Flight::make(EmailCron::class);
$emailCron->send();
```

Keren, bukan?

## Lihat Juga
- [Dependency Injection Container](/learn/dependency-injection-container) - Cara menggunakan DIC dengan Flight.
- [File Cache](/awesome-plugins/php_file_cache) - Contoh menggunakan perpustakaan caching dengan Flight.

## Pemecahan Masalah
- Ingatlah bahwa metode yang dipetakan memiliki prioritas atas kelas yang terdaftar. Jika Anda menyatakan keduanya menggunakan nama yang sama, hanya metode yang dipetakan yang akan dipanggil.

## Changelog
- v2.0 - Rilis Awal.