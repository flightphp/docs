# Belajar

Halaman ini adalah panduan untuk mempelajari Flight. Ini mencakup dasar-dasar kerangka kerja dan cara menggunakannya.

## <a name="routing"></a> Routing

Routing di Flight dilakukan dengan mencocokkan pola URL dengan fungsi callback.

``` php
Flight::route('/', function(){
    echo 'halo dunia!';
});
```

Callback dapat berupa objek yang dapat dipanggil. Jadi Anda dapat menggunakan fungsi biasa:

``` php
function hello(){
    echo 'halo dunia!';
}

Flight::route('/', 'hello');
```

Atau metode kelas:

``` php
class Greeting {
    public static function hello() {
        echo 'halo dunia!';
    }
}

Flight::route('/', array('Greeting','hello'));
```

Atau metode objek:

``` php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Halo, {$this->name}!";
    }
}

$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

Rute dicocokkan dalam urutan mereka didefinisikan. Rute pertama yang mencocokkan permintaan akan dipanggil.

### Method Routing

Secara default, pola rute dicocokkan dengan semua metode permintaan. Anda dapat merespons metode tertentu dengan menempatkan pengidentifikasi sebelum URL.

``` php
Flight::route('GET /', function(){
    echo 'Saya menerima permintaan GET.';
});

Flight::route('POST /', function(){
    echo 'Saya menerima permintaan POST.';
});
```

Anda juga dapat memetakan beberapa metode ke satu callback dengan menggunakan delimiter `|`:

``` php
Flight::route('GET|POST /', function(){
    echo 'Saya menerima baik permintaan GET maupun POST.';
});
```

### Ekspresi Reguler

Anda dapat menggunakan ekspresi reguler dalam rute Anda:

``` php
Flight::route('/user/[0-9]+', function(){
    // Ini akan mencocokkan /user/1234
});
```

### Parameter Bernama

Anda dapat menentukan parameter bernama dalam rute Anda yang akan diteruskan ke fungsi callback Anda.

``` php
Flight::route('/@name/@id', function($name, $id){
    echo "halo, $name ($id)!";
});
```

Anda juga dapat memasukkan ekspresi reguler dengan parameter bernama Anda dengan menggunakan delimiter `:`:

``` php
Flight::route('/@name/@id:[0-9]{3}', function($name, $id){
    // Ini akan mencocokkan /bob/123
    // Tapi tidak akan mencocokkan /bob/12345
});
```

### Parameter Opsional

Anda dapat menentukan parameter bernama yang opsional untuk dicocokkan dengan membungkus segmen dalam tanda kurung.

``` php
Flight::route('/blog(/@year(/@month(/@day)))', function($year, $month, $day){
    // Ini akan mencocokkan URL berikut:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
});
```

Parameter opsional yang tidak dicocokkan akan diteruskan sebagai NULL.

### Wildcards

Pencocokan hanya dilakukan pada segmen URL individu. Jika Anda ingin mencocokkan beberapa segmen, Anda dapat menggunakan wildcard `*`.

``` php
Flight::route('/blog/*', function(){
    // Ini akan mencocokkan /blog/2000/02/01
});
```

Untuk mengarahkan semua permintaan ke satu callback, Anda dapat melakukannya:

``` php
Flight::route('*', function(){
    // Lakukan sesuatu
});
```

### Passing

Anda dapat meneruskan eksekusi ke rute pencocokan berikutnya dengan mengembalikan `true` dari fungsi callback Anda.

``` php
Flight::route('/user/@name', function($name){
    // Periksa beberapa kondisi
    if ($name != "Bob") {
        // Lanjutkan ke rute berikutnya
        return true;
    }
});

Flight::route('/user/*', function(){
    // Ini akan dipanggil
});
```

### Informasi Rute

Jika Anda ingin memeriksa informasi rute yang dicocokkan, Anda dapat meminta objek rute untuk diteruskan ke callback Anda dengan meneruskan `true` sebagai parameter ketiga dalam metode rute. Objek rute akan selalu menjadi parameter terakhir yang diteruskan ke fungsi callback Anda.

``` php
Flight::route('/', function($route){
    // Array metode HTTP yang dicocokkan
    $route->methods;

    // Array parameter bernama
    $route->params;

    // Ekspresi reguler yang dicocokkan
    $route->regex;

    // Berisi konten dari setiap '*' yang digunakan dalam pola URL
    $route->splat;
}, true);
```
### Pengelompokan Rute

Mungkin ada saat-saat ketika Anda ingin mengelompokkan rute yang terkait bersama (seperti `/api/v1`).
Anda dapat melakukannya dengan menggunakan metode `group`:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Mencocokkan /api/v1/users
  });

  Flight::route('/posts', function () {
	// Mencocokkan /api/v1/posts
  });
});
```

Anda bahkan dapat menempelkan kelompok-kelompok kelompok:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() mendapatkan variabel, itu tidak mengatur rute! Lihat konteks objek di bawah
	Flight::route('GET /users', function () {
	  // Mencocokkan GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Mencocokkan POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Mencocokkan PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() mendapatkan variabel, itu tidak mengatur rute! Lihat konteks objek di bawah
	Flight::route('GET /users', function () {
	  // Mencocokkan GET /api/v2/users
	});
  });
});
```

#### Pengelompokan dengan Konteks Objek

Anda masih dapat menggunakan pengelompokan rute dengan objek `Engine` dengan cara berikut:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {
  $router->get('/users', function () {
	// Mencocokkan GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Mencocokkan POST /api/v1/posts
  });
});
```

### Alihkan Rute

Anda dapat memberikan alias pada sebuah rute, sehingga URL dapat dihasilkan secara dinamis nanti dalam kode Anda (seperti template misalnya).

```php
Flight::route('/users/@id', function($id) { echo 'pengguna:'.$id; }, false, 'user_view');

// nanti dalam kode di suatu tempat
Flight::getUrl('user_view', [ 'id' => 5 ]); // akan mengembalikan '/users/5'
```

Ini sangat membantu jika URL Anda kebetulan berubah. Dalam contoh di atas, katakanlah bahwa pengguna dipindahkan ke `/admin/users/@id` sebagai gantinya.
Dengan penamaan alias ini, Anda tidak perlu mengubah di mana pun Anda merujuk alias tersebut karena alias sekarang akan mengembalikan `/admin/users/5` seperti dalam contoh di atas.

Pengalihan rute juga berfungsi dalam kelompok:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'pengguna:'.$id; }, false, 'user_view');
});


// nanti dalam kode di suatu tempat
Flight::getUrl('user_view', [ 'id' => 5 ]); // akan mengembalikan '/users/5'
```

## <a name="extending"></a> Memperluas

Flight dirancang agar menjadi kerangka kerja yang dapat diperluas. Kerangka kerja ini dilengkapi dengan sekumpulan metode dan komponen default, tetapi memungkinkan Anda untuk memetakan metode Anda sendiri, mendaftarkan kelas Anda sendiri, atau bahkan mengganti kelas dan metode yang sudah ada.

### Memetakan Metode

Untuk memetakan metode kustom Anda sendiri, Anda menggunakan fungsi `map`:

``` php
// Pemetakan metode Anda
Flight::map('hello', function($name){
    echo "halo $name!";
});

// Panggil metode kustom Anda
Flight::hello('Bob');
```

### Mendaftar Kelas

Untuk mendaftarkan kelas Anda sendiri, Anda menggunakan fungsi `register`:

``` php
// Daftarkan kelas Anda
Flight::register('user', 'User');

// Dapatkan instansi dari kelas Anda
$user = Flight::user();
```

Metode register juga memungkinkan Anda untuk meneruskan parameter ke konstruktor kelas Anda. Jadi ketika Anda memuat kelas kustom Anda, itu akan datang dengan inisialisasi awal.
Anda dapat mendefinisikan parameter konstruktor dengan meneruskan array tambahan.
Berikut adalah contoh memuat koneksi basis data:

``` php
// Daftarkan kelas dengan parameter konstruktor
Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=test','user','pass'));

// Dapatkan instansi dari kelas Anda
// Ini akan membuat objek dengan parameter yang ditentukan
//
//     new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();
```

Jika Anda meneruskan parameter callback tambahan, itu akan dieksekusi segera setelah konstruksi kelas. Ini memungkinkan Anda untuk melakukan prosedur pengaturan apa pun untuk objek baru Anda. Fungsi callback mengambil satu parameter, yaitu instansi objek baru.

``` php
// Callback akan diteruskan dengan objek yang dibangun
Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=test','user','pass'),
  function($db){
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Secara default, setiap kali Anda memuat kelas Anda, Anda akan mendapatkan instansi yang dibagikan.
Untuk mendapatkan instansi baru dari sebuah kelas, cukup meneruskan `false` sebagai parameter:

``` php
// Instansi yang dibagikan dari kelas
$shared = Flight::db();

// Instansi baru dari kelas
$new = Flight::db(false);
```

Perlu diingat bahwa metode yang dipetakan memiliki prioritas di atas kelas yang terdaftar. Jika Anda mendeklarasikan keduanya dengan nama yang sama, hanya metode yang dipetakan yang akan dipanggil.

## <a name="overriding"></a> Menimpa

Flight memungkinkan Anda untuk menimpa fungsionalitas defaultnya agar sesuai dengan kebutuhan Anda sendiri, tanpa harus memodifikasi kode apa pun.

Sebagai contoh, ketika Flight tidak dapat mencocokkan URL ke rute, itu memanggil metode `notFound` yang mengirimkan respons generik `HTTP 404`. Anda dapat menimpa perilaku ini dengan menggunakan metode `map`:

``` php
Flight::map('notFound', function(){
    // Tampilkan halaman 404 kustom
    include 'errors/404.html';
});
```

Flight juga memungkinkan Anda untuk mengganti komponen inti dari kerangka kerja. 
Sebagai contoh, Anda dapat mengganti kelas Router default dengan kelas kustom Anda sendiri:

``` php
// Daftarkan kelas kustom Anda
Flight::register('router', 'MyRouter');

// Saat Flight memuat instansi Router, itu akan memuat kelas Anda
$myrouter = Flight::router();
```

Namun, metode kerangka kerja seperti `map` dan `register` tidak dapat ditimpa. Anda akan mendapatkan kesalahan jika mencoba melakukannya.

## <a name="filtering"></a> Penyaringan

Flight memungkinkan Anda untuk menyaring metode sebelum dan sesudah mereka dipanggil. Tidak ada hook yang sudah ditentukan yang perlu Anda hafal. Anda dapat menyaring salah satu dari metode default kerangka kerja serta metode kustom yang telah Anda peta.

Fungsi filter terlihat seperti ini:

``` php
function(&$params, &$output) {
    // Kode filter
}
```

Dengan menggunakan variabel yang diteruskan, Anda dapat memanipulasi parameter input dan/atau output.

Anda dapat memiliki filter yang dijalankan sebelum metode dengan melakukan:

``` php
Flight::before('start', function(&$params, &$output){
    // Lakukan sesuatu
});
```

Anda dapat memiliki filter yang dijalankan setelah metode dengan melakukan:

``` php
Flight::after('start', function(&$params, &$output){
    // Lakukan sesuatu
});
```

Anda dapat menambahkan sebanyak mungkin filter yang Anda inginkan ke metode mana pun. Mereka akan dipanggil dalam urutan yang mereka deklarasikan.

Berikut adalah contoh dari proses penyaringan:

``` php
// Memetakan metode kustom
Flight::map('hello', function($name){
    return "Halo, $name!";
});

// Menambahkan filter sebelum
Flight::before('hello', function(&$params, &$output){
    // Manipulasi parameter
    $params[0] = 'Fred';
});

// Menambahkan filter setelah
Flight::after('hello', function(&$params, &$output){
    // Manipulasi output
    $output .= " Selamat tinggal!";
});

// Memanggil metode kustom
echo Flight::hello('Bob');
```

Ini seharusnya menampilkan:

``` html
Halo Fred! Selamat tinggal!
```

Jika Anda telah mendefinisikan beberapa filter, Anda dapat memutuskan rantai dengan mengembalikan `false` di salah satu fungsi filter Anda:

``` php
Flight::before('start', function(&$params, &$output){
    echo 'satu';
});

Flight::before('start', function(&$params, &$output){
    echo 'dua';

    // Ini akan mengakhiri rantai
    return false;
});

// Ini tidak akan dipanggil
Flight::before('start', function(&$params, &$output){
    echo 'tiga';
});
```

Perlu dicatat, metode inti seperti `map` dan `register` tidak dapat disaring karena mereka dipanggil langsung dan bukan dipanggil secara dinamis.

## <a name="variables"></a> Variabel

Flight memungkinkan Anda untuk menyimpan variabel agar dapat digunakan di mana saja dalam aplikasi Anda.

``` php
// Simpan variabel Anda
Flight::set('id', 123);

// Di tempat lain dalam aplikasi Anda
$id = Flight::get('id');
```
Untuk melihat apakah sebuah variabel telah diatur, Anda dapat melakukannya:

``` php
if (Flight::has('id')) {
     // Lakukan sesuatu
}
```

Anda dapat menghapus variabel dengan melakukan:

``` php
// Menghapus variabel id
Flight::clear('id');

// Menghapus semua variabel
Flight::clear();
```

Flight juga menggunakan variabel untuk keperluan konfigurasi.

``` php
Flight::set('flight.log_errors', true);
```

## <a name="views"></a> Tampilan

Flight menyediakan beberapa fungsionalitas templating dasar secara default. Untuk menampilkan template tampilan, panggil metode `render` dengan nama file template dan data template opsional:

``` php
Flight::render('hello.php', array('name' => 'Bob'));
```

Data template yang Anda berikan secara otomatis disuntikkan ke dalam template dan dapat menjadi referensi seperti variabel lokal. File template hanyalah file PHP. Jika konten dari file template `hello.php` adalah:

``` php
Halo, '<?php echo $name; ?>'!
```

Outputnya akan menjadi:

``` html
Halo, Bob!
```

Anda juga dapat mengatur variabel tampilan secara manual dengan menggunakan metode set:

``` php
Flight::view()->set('name', 'Bob');
```

Variabel `name` sekarang tersedia di seluruh tampilan Anda. Jadi Anda cukup melakukannya:

``` php
Flight::render('hello');
```

Perhatikan bahwa ketika menentukan nama template dalam metode render, Anda dapat menghilangkan ekstensi `.php`.

Secara default Flight akan mencari direktori `views` untuk file template. Anda dapat mengatur jalur alternatif untuk template Anda dengan mengatur konfigurasi berikut:

``` php
Flight::set('flight.views.path', '/path/to/views');
```

### Tata Letak

Umum bagi situs web untuk memiliki satu file template tata letak dengan konten yang berganti. Untuk merender konten yang akan digunakan dalam tata letak, Anda dapat meneruskan parameter opsional ke metode `render`.

``` php
Flight::render('header', array('heading' => 'Halo'), 'header_content');
Flight::render('body', array('body' => 'Dunia'), 'body_content');
```

Tampilan Anda kemudian akan memiliki variabel yang disimpan yang disebut `header_content` dan `body_content`. Anda kemudian dapat merender tata letak Anda dengan melakukan:

``` php
Flight::render('layout', array('title' => 'Halaman Utama'));
```

Jika file template terlihat seperti ini:

`header.php`:

``` php
<h1><?php echo $heading; ?></h1>
```

`body.php`:

``` php
<div><?php echo $body; ?></div>
```

`layout.php`:

``` php
<html>
<head>
<title><?php echo $title; ?></title>
</head>
<body>
<?php echo $header_content; ?>
<?php echo $body_content; ?>
</body>
</html>
```

Outputnya akan menjadi:

``` html
<html>
<head>
<title>Halaman Utama</title>
</head>
<body>
<h1>Halo</h1>
<div>Dunia</div>
</body>
</html>
```

### Tampilan Kustom

Flight memungkinkan Anda untuk mengganti mesin tampilan default hanya dengan mendaftarkan kelas tampilan Anda sendiri. Berikut adalah cara Anda akan menggunakan mesin template [Smarty](http://www.smarty.net/) untuk tampilan Anda:

``` php
// Memuat pustaka Smarty
require './Smarty/libs/Smarty.class.php';

// Mendaftarkan Smarty sebagai kelas tampilan
// Juga lewatkan fungsi callback untuk mengonfigurasi Smarty saat memuat
Flight::register('view', 'Smarty', array(), function($smarty){
    $smarty->template_dir = './templates/';
    $smarty->compile_dir = './templates_c/';
    $smarty->config_dir = './config/';
    $smarty->cache_dir = './cache/';
});

// Menetapkan data template
Flight::view()->assign('name', 'Bob');

// Menampilkan template
Flight::view()->display('hello.tpl');
```

Untuk kelengkapan, Anda juga harus menimpa metode render default Flight:

``` php
Flight::map('render', function($template, $data){
    Flight::view()->assign($data);
    Flight::view()->display($template);
});
```

## <a name="errorhandling"></a> Penanganan Kesalahan

### Kesalahan dan Eksepsi

Semua kesalahan dan eksepsi ditangkap oleh Flight dan diteruskan ke metode `error`. Perilaku default adalah mengirim respons generik `HTTP 500 Kesalahan Server Internal` dengan beberapa informasi kesalahan.

Anda dapat menimpa perilaku ini untuk kebutuhan Anda sendiri:

``` php
Flight::map('error', function(Exception $ex){
    // Tangani kesalahan
    echo $ex->getTraceAsString();
});
```

Secara default, kesalahan tidak dicatat ke server web. Anda dapat mengaktifkannya dengan mengubah konfigurasi:

``` php
Flight::set('flight.log_errors', true);
```

### Tidak Ditemukan

Ketika URL tidak dapat ditemukan, Flight memanggil metode `notFound`. Perilaku default adalah mengirim respons `HTTP 404 Tidak Ditemukan` dengan pesan sederhana.

Anda dapat menimpa perilaku ini untuk kebutuhan Anda sendiri:

``` php
Flight::map('notFound', function(){
    // Tangani tidak ditemukan
});
```

## <a name="redirects"></a> Pengalihan

Anda dapat mengalihkan permintaan saat ini dengan menggunakan metode `redirect` dan meneruskan ke URL baru:

``` php
Flight::redirect('/new/location');
```

Secara default, Flight mengirimkan kode status HTTP 303. Anda dapat secara opsional mengatur kode kustom:

``` php
Flight::redirect('/new/location', 401);
```

## <a name="requests"></a> Permintaan

Flight mengenkapsulasi permintaan HTTP ke dalam satu objek, yang dapat diakses dengan melakukannya:

``` php
$request = Flight::request();
```

Objek permintaan menyediakan properti berikut:

``` html
url - URL yang diminta
base - Subdirektori induk dari URL
method - Metode permintaan (GET, POST, PUT, DELETE)
referrer - URL referer
ip - Alamat IP klien
ajax - Apakah permintaan adalah permintaan AJAX
scheme - Protokol server (http, https)
user_agent - Informasi browser
type - Tipe konten
length - Panjang konten
query - Parameter string query
data - Data pos atau data JSON
cookies - Data cookie
files - File yang diunggah
secure - Apakah koneksi aman
accept - Parameter penerimaan HTTP
proxy_ip - Alamat IP proxy dari klien
```

Anda dapat mengakses properti `query`, `data`, `cookies`, dan `files` sebagai array atau objek.

Jadi, untuk mendapatkan parameter string query, Anda dapat melakukannya:

``` php
$id = Flight::request()->query['id'];
```

Atau Anda dapat melakukannya:

``` php
$id = Flight::request()->query->id;
```

### Body Permintaan RAW

Untuk mendapatkan body permintaan HTTP raw, misalnya ketika berurusan dengan permintaan PUT, Anda dapat melakukannya:

``` php
$body = Flight::request()->getBody();
```

### Input JSON

Jika Anda mengirim permintaan dengan tipe `application/json` dan data `{"id": 123}`, itu akan tersedia dari properti `data`:

``` php
$id = Flight::request()->data->id;
```

## <a name="stopping"></a> Penghentian

Anda dapat menghentikan kerangka kerja kapan saja dengan memanggil metode `halt`:

``` php
Flight::halt();
```

Anda juga dapat menentukan kode status `HTTP` dan pesan opsional:

``` php
Flight::halt(200, 'Segera kembali...');
```

Memanggil `halt` akan membuang konten respons sampai titik itu. Jika Anda ingin menghentikan kerangka kerja dan mengeluarkan respons saat ini, gunakan metode `stop`:

``` php
Flight::stop();
```

## <a name="httpcaching"></a> Cache HTTP

Flight menyediakan dukungan bawaan untuk caching level HTTP. Jika kondisi caching terpenuhi, Flight akan mengembalikan respons HTTP `304 Tidak Dimodifikasi`. Di lain waktu, klien yang meminta sumber daya yang sama akan diminta untuk menggunakan versi cache lokal mereka.

### Terakhir-Diubah

Anda dapat menggunakan metode `lastModified` dan meneruskan timestamp UNIX untuk mengatur tanggal dan waktu halaman terakhir dimodifikasi. Klien akan terus menggunakan cache mereka sampai nilai terakhir dimodifikasi diubah.

``` php
Flight::route('/news', function(){
    Flight::lastModified(1234567890);
    echo 'Konten ini akan di-cache.';
});
```

### ETag

Caching `ETag` mirip dengan `Last-Modified`, kecuali Anda dapat menentukan id apa pun yang Anda inginkan untuk sumber daya:

``` php
Flight::route('/news', function(){
    Flight::etag('my-unique-id');
    echo 'Konten ini akan di-cache.';
});
```

Perlu diingat bahwa memanggil baik `lastModified` atau `etag` akan mengatur dan memeriksa nilai cache. Jika nilai cache sama antara permintaan, Flight akan segera mengirim respons `HTTP 304` dan menghentikan proses.

## <a name="json"></a> JSON

Flight menyediakan dukungan untuk mengirim respons JSON dan JSONP. Untuk mengirim respons JSON, Anda meneruskan beberapa data untuk di-JSON encode:

``` php
Flight::json(array('id' => 123));
```

Untuk permintaan JSONP, Anda dapat secara opsional meneruskan nama parameter query yang Anda gunakan untuk mendefinisikan fungsi callback Anda:

``` php
Flight::jsonp(array('id' => 123), 'q');
```

Jadi, saat membuat permintaan GET menggunakan `?q=my_func`, Anda seharusnya menerima output:

``` json
my_func({"id":123});
```

Jika Anda tidak meneruskan nama parameter query, itu akan default ke `jsonp`.

## <a name="configuration"></a> Konfigurasi

Anda dapat menyesuaikan beberapa perilaku Flight dengan mengatur nilai konfigurasi melalui metode `set`.

``` php
Flight::set('flight.log_errors', true);
```

Berikut adalah daftar semua pengaturan konfigurasi yang tersedia:

``` html 
flight.base_url - Ganti base url dari permintaan. (default: null)
flight.case_sensitive - Pencocokan sensitif huruf untuk URL. (default: false)
flight.handle_errors - Izinkan Flight untuk menangani semua kesalahan secara internal. (default: true)
flight.log_errors - Catat kesalahan ke file log kesalahan server web. (default: false)
flight.views.path - Direktori yang berisi file template tampilan. (default: ./views)
flight.views.extension - Ekstensi file template tampilan. (default: .php)
```

## <a name="frameworkmethods"></a> Metode Kerangka Kerja

Flight dirancang untuk mudah digunakan dan dipahami. Berikut adalah set lengkap metode untuk kerangka kerja. Ini terdiri dari metode inti, yang merupakan metode statis biasa, dan metode yang dapat diperluas, yang merupakan metode yang dipetakan yang dapat difilter atau ditimpa.

### Metode Inti

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Membuat metode kerangka kerja kustom.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Mendaftarkan kelas ke metode kerangka kerja.
Flight::before(string $name, callable $callback) // Menambahkan filter sebelum metode kerangka kerja.
Flight::after(string $name, callable $callback) // Menambahkan filter setelah metode kerangka kerja.
Flight::path(string $path) // Menambahkan jalur untuk pemuatan otomatis kelas.
Flight::get(string $key) // Mengambil variabel.
Flight::set(string $key, mixed $value) // Mengatur variabel.
Flight::has(string $key) // Memeriksa apakah variabel telah diatur.
Flight::clear(array|string $key = []) // Menghapus variabel.
Flight::init() // Menginisialisasi kerangka kerja ke pengaturan defaultnya.
Flight::app() // Mendapatkan instansi objek aplikasi
```

### Metode yang Dapat Diperluas

```php
Flight::start() // Memulai kerangka kerja.
Flight::stop() // Menghentikan kerangka kerja dan mengirim respons.
Flight::halt(int $code = 200, string $message = '') // Hentikan kerangka kerja dengan kode status dan pesan opsional.
Flight::route(string $pattern, callable $callback, bool $pass_route = false) // Memetakan pola URL ke callback.
Flight::group(string $pattern, callable $callback) // Membuat pengelompokan untuk URL, pola harus berupa string.
Flight::redirect(string $url, int $code) // Mengalihkan ke URL lain.
Flight::render(string $file, array $data, ?string $key = null) // Merender file template.
Flight::error(Throwable $error) // Mengirim respons HTTP 500.
Flight::notFound() // Mengirim respons HTTP 404.
Flight::etag(string $id, string $type = 'string') // Melakukan caching HTTP ETag.
Flight::lastModified(int $time) // Melakukan caching HTTP terakhir dimodifikasi.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Mengirim respons JSON.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Mengirim respons JSONP.
```

Metode kustom apa pun yang ditambahkan dengan `map` dan `register` juga dapat difilter.


## <a name="frameworkinstance"></a> Instansi Kerangka Kerja

Alih-alih menjalankan Flight sebagai kelas statis global, Anda secara opsional dapat menjadikannya
sebagai instansi objek.

``` php
require 'flight/autoload.php';

use flight\Engine;

$app = new Engine();

$app->route('/', function(){
    echo 'halo dunia!';
});

$app->start();
```

Jadi, alih-alih memanggil metode statis, Anda akan memanggil metode instansi dengan nama yang sama
pada objek Engine.