# Routing

## Gambaran Umum
Routing di Flight PHP memetakan pola URL ke fungsi callback atau metode kelas, memungkinkan penanganan permintaan yang cepat dan sederhana. Ini dirancang untuk overhead minimal, penggunaan yang ramah pemula, dan kemampuan ekstensi tanpa ketergantungan eksternal.

## Pemahaman
Routing adalah mekanisme inti yang menghubungkan permintaan HTTP ke logika aplikasi Anda di Flight. Dengan mendefinisikan rute, Anda menentukan bagaimana URL yang berbeda memicu kode spesifik, baik melalui fungsi, metode kelas, atau aksi pengontrol. Sistem routing Flight fleksibel, mendukung pola dasar, parameter bernama, ekspresi reguler, dan fitur lanjutan seperti injeksi dependensi dan routing sumber daya. Pendekatan ini menjaga kode Anda tetap terorganisir dan mudah dipelihara, sambil tetap cepat dan sederhana untuk pemula serta dapat diekstensikan untuk pengguna lanjutan.

> **Catatan:** Ingin memahami lebih lanjut tentang routing? Lihat halaman ["why a framework?"](/learn/why-frameworks) untuk penjelasan yang lebih mendalam.

## Penggunaan Dasar

### Mendefinisikan Rute Sederhana
Routing dasar di Flight dilakukan dengan mencocokkan pola URL dengan fungsi callback atau array dari kelas dan metode.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Rute dicocokkan sesuai urutan yang didefinisikan. Rute pertama yang cocok dengan permintaan akan dipanggil.

### Menggunakan Fungsi sebagai Callback
Callback bisa berupa objek apa pun yang dapat dipanggil. Jadi Anda bisa menggunakan fungsi biasa:

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### Menggunakan Kelas dan Metode sebagai Pengontrol
Anda juga bisa menggunakan metode (statis atau tidak) dari kelas:

```php
class GreetingController {
    public function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', [ 'GreetingController','hello' ]);
// or
Flight::route('/', [ GreetingController::class, 'hello' ]); // preferred method
// or
Flight::route('/', [ 'GreetingController::hello' ]);
// or 
Flight::route('/', [ 'GreetingController->hello' ]);
```

Atau dengan membuat objek terlebih dahulu lalu memanggil metodenya:

```php
use flight\Engine;

// GreetingController.php
class GreetingController
{
	protected Engine $app
    public function __construct(Engine $app) {
		$this->app = $app;
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hello, {$this->name}!";
    }
}

// index.php
$app = Flight::app();
$greeting = new GreetingController($app);

Flight::route('/', [ $greeting, 'hello' ]);
```

> **Catatan:** Secara default, ketika pengontrol dipanggil dalam kerangka kerja, kelas `flight\Engine` selalu diinjeksi kecuali Anda menentukannya melalui [container injeksi dependensi](/learn/dependency-injection-container)

### Routing Spesifik Metode

Secara default, pola rute dicocokkan dengan semua metode permintaan. Anda bisa merespons metode spesifik dengan menempatkan pengenal sebelum URL.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// You cannot use Flight::get() for routes as that is a method 
//    to get variables, not create a route.
Flight::post('/', function() { /* code */ });
Flight::patch('/', function() { /* code */ });
Flight::put('/', function() { /* code */ });
Flight::delete('/', function() { /* code */ });
```

Anda juga bisa memetakan beberapa metode ke satu callback dengan menggunakan pemisah `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

### Penanganan Khusus untuk Permintaan HEAD dan OPTIONS

Flight menyediakan penanganan bawaan untuk permintaan HTTP `HEAD` dan `OPTIONS`:

#### Permintaan HEAD

- **Permintaan HEAD** diperlakukan seperti permintaan `GET`, tetapi Flight secara otomatis menghapus isi respons sebelum mengirimkannya ke klien.
- Ini berarti Anda bisa mendefinisikan rute untuk `GET`, dan permintaan HEAD ke URL yang sama akan mengembalikan hanya header (tanpa konten), sesuai standar HTTP.

```php
Flight::route('GET /info', function() {
    echo 'This is some info!';
});
// A HEAD request to /info will return the same headers, but no body.
```

#### Permintaan OPTIONS

Permintaan `OPTIONS` ditangani secara otomatis oleh Flight untuk rute apa pun yang didefinisikan.
- Ketika permintaan OPTIONS diterima, Flight merespons dengan status `204 No Content` dan header `Allow` yang mencantumkan semua metode HTTP yang didukung untuk rute tersebut.
- Anda tidak perlu mendefinisikan rute terpisah untuk OPTIONS.

```php
// For a route defined as:
Flight::route('GET|POST /users', function() { /* ... */ });

// An OPTIONS request to /users will respond with:
//
// Status: 204 No Content
// Allow: GET, POST, HEAD, OPTIONS
```

### Menggunakan Objek Router

Selain itu, Anda bisa mengambil objek Router yang memiliki beberapa metode pembantu untuk digunakan:

```php

$router = Flight::router();

// maps all methods just like Flight::route()
$router->map('/', function() {
	echo 'hello world!';
});

// GET request
$router->get('/users', function() {
	echo 'users';
});
$router->post('/users', 			function() { /* code */});
$router->put('/users/update/@id', 	function() { /* code */});
$router->delete('/users/@id', 		function() { /* code */});
$router->patch('/users/@id', 		function() { /* code */});
```

### Ekspresi Regulasi (Regex)
Anda bisa menggunakan ekspresi reguler di rute Anda:

```php
Flight::route('/user/[0-9]+', function () {
  // This will match /user/1234
});
```

Meskipun metode ini tersedia, disarankan untuk menggunakan parameter bernama, atau parameter bernama dengan ekspresi reguler, karena lebih mudah dibaca dan dipelihara.

### Parameter Bernama
Anda bisa menentukan parameter bernama di rute Anda yang akan diteruskan ke fungsi callback Anda. **Ini lebih untuk keterbacaan rute daripada hal lain. Lihat bagian di bawah tentang peringatan penting.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

Anda juga bisa menyertakan ekspresi reguler dengan parameter bernama Anda dengan menggunakan pemisah `:`:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // This will match /bob/123
  // But will not match /bob/12345
});
```

> **Catatan:** Mencocokkan grup regex `()` dengan parameter posisional tidak didukung. Contoh: `:'\(`

#### Peringatan Penting

Meskipun dalam contoh di atas, tampaknya `@name` langsung terkait dengan variabel `$name`, sebenarnya bukan. Urutan parameter dalam fungsi callback yang menentukan apa yang diteruskan kepadanya. Jika Anda menukar urutan parameter dalam fungsi callback, variabel juga akan bertukar. Berikut contohnya:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

Dan jika Anda mengunjungi URL berikut: `/bob/123`, outputnya akan menjadi `hello, 123 (bob)!`. 
_Please be careful_ ketika Anda menyiapkan rute dan fungsi callback Anda!

### Parameter Opsional
Anda bisa menentukan parameter bernama yang opsional untuk pencocokan dengan membungkus segmen dalam tanda kurung.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // This will match the following URLS:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Parameter opsional apa pun yang tidak cocok akan diteruskan sebagai `NULL`.

### Routing Wildcard
Pencocokan hanya dilakukan pada segmen URL individu. Jika Anda ingin mencocokkan beberapa segmen, Anda bisa menggunakan wildcard `*`.

```php
Flight::route('/blog/*', function () {
  // This will match /blog/2000/02/01
});
```

Untuk merutekan semua permintaan ke satu callback, Anda bisa lakukan:

```php
Flight::route('*', function () {
  // Do something
});
```

### Penanganan 404 Tidak Ditemukan

Secara default, jika URL tidak ditemukan, Flight akan mengirim respons `HTTP 404 Not Found` yang sangat sederhana dan polos.
Jika Anda ingin respons 404 yang lebih disesuaikan, Anda bisa [memetakan](/learn/extending) metode `notFound` sendiri:

```php
Flight::map('notFound', function() {
	$url = Flight::request()->url;

	// You could also use Flight::render() with a custom template.
    $output = <<<HTML
		<h1>My Custom 404 Not Found</h1>
		<h3>The page you have requested {$url} could not be found.</h3>
		HTML;

	$this->response()
		->clearBody()
		->status(404)
		->write($output)
		->send();
});
```

### Penanganan Metode Tidak Ditemukan

Secara default, jika URL ditemukan tetapi metode tidak diizinkan, Flight akan mengirim respons `HTTP 405 Method Not Allowed` yang sangat sederhana dan polos (Contoh: Method Not Allowed. Allowed Methods are: GET, POST). Ini juga akan menyertakan header `Allow` dengan metode yang diizinkan untuk URL tersebut.

Jika Anda ingin respons 405 yang lebih disesuaikan, Anda bisa [memetakan](/learn/extending) metode `methodNotFound` sendiri:

```php
use flight\net\Route;

Flight::map('methodNotFound', function(Route $route) {
	$url = Flight::request()->url;
	$methods = implode(', ', $route->methods);

	// You could also use Flight::render() with a custom template.
	$output = <<<HTML
		<h1>My Custom 405 Method Not Allowed</h1>
		<h3>The method you have requested for {$url} is not allowed.</h3>
		<p>Allowed Methods are: {$methods}</p>
		HTML;

	$this->response()
		->clearBody()
		->status(405)
		->setHeader('Allow', $methods)
		->write($output)
		->send();
});
```

## Penggunaan Lanjutan

### Injeksi Dependensi di Rute
Jika Anda ingin menggunakan injeksi dependensi melalui container (PSR-11, PHP-DI, Dice, dll), satu-satunya jenis rute di mana itu tersedia adalah dengan membuat objek secara langsung sendiri dan menggunakan container untuk membuat objek Anda atau Anda bisa menggunakan string untuk mendefinisikan kelas dan metode yang akan dipanggil. Anda bisa pergi ke halaman [Dependency Injection](/learn/dependency-injection-container) untuk informasi lebih lanjut. 

Berikut contoh cepat:

```php

use flight\database\PdoWrapper;

// Greeting.php
class Greeting
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;
	}

	public function hello(int $id) {
		// do something with $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";
	}
}

// index.php

// Setup the container with whatever params you need
// See the Dependency Injection page for more information on PSR-11
$dice = new \Dice\Dice();

// Don't forget to reassign the variable with '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Register the container handler
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Routes like normal
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// or
Flight::route('/hello/@id', 'Greeting->hello');
// or
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

### Mengalihkan Eksekusi ke Rute Berikutnya
<span class="badge bg-warning">Deprecated</span>
Anda bisa mengalihkan eksekusi ke rute pencocokan berikutnya dengan mengembalikan `true` dari fungsi callback Anda.

```php
Flight::route('/user/@name', function (string $name) {
  // Check some condition
  if ($name !== "Bob") {
    // Continue to next route
    return true;
  }
});

Flight::route('/user/*', function () {
  // This will get called
});
```

Sekarang disarankan untuk menggunakan [middleware](/learn/middleware) untuk menangani kasus penggunaan kompleks seperti ini.

### Alias Rute
Dengan menetapkan alias ke rute, Anda bisa memanggil alias tersebut di aplikasi Anda secara dinamis untuk dihasilkan nanti di kode Anda (contoh: tautan di template HTML, atau menghasilkan URL redirect).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
// or 
Flight::route('/users/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');

// later in code somewhere
class UserController {
	public function update() {

		// code to save user...
		$id = $user['id']; // 5 for example

		$redirectUrl = Flight::getUrl('user_view', [ 'id' => $id ]); // will return '/users/5'
		Flight::redirect($redirectUrl);
	}
}

```

Ini sangat membantu jika URL Anda berubah. Dalam contoh di atas, katakanlah bahwa users dipindahkan ke `/admin/users/@id` sebagai gantinya.
Dengan aliasing di tempat untuk rute, Anda tidak lagi perlu mencari semua URL lama di kode Anda dan mengubahnya karena alias sekarang akan mengembalikan `/admin/users/5` seperti dalam contoh di atas.

Alias rute masih berfungsi dalam grup juga:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
	// or
	Flight::route('/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');
});
```

### Memeriksa Informasi Rute
Jika Anda ingin memeriksa informasi rute pencocokan, ada 2 cara Anda bisa lakukan ini:

1. Anda bisa menggunakan properti `executedRoute` pada objek `Flight::router()`.
2. Anda bisa meminta objek rute diteruskan ke callback Anda dengan meneruskan `true` sebagai parameter ketiga dalam metode rute. Objek rute akan selalu menjadi parameter terakhir yang diteruskan ke fungsi callback Anda.

#### `executedRoute`
```php
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;
  // Do something with $route
  // Array of HTTP methods matched against
  $route->methods;

  // Array of named parameters
  $route->params;

  // Matching regular expression
  $route->regex;

  // Contains the contents of any '*' used in the URL pattern
  $route->splat;

  // Shows the url path....if you really need it
  $route->pattern;

  // Shows what middleware is assigned to this
  $route->middleware;

  // Shows the alias assigned to this route
  $route->alias;
});
```

> **Catatan:** Properti `executedRoute` hanya akan ditetapkan setelah rute dieksekusi. Jika Anda mencoba mengaksesnya sebelum rute dieksekusi, itu akan menjadi `NULL`. Anda juga bisa menggunakan executedRoute di [middleware](/learn/middleware) juga!

#### Meneruskan `true` ke definisi rute
```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array of HTTP methods matched against
  $route->methods;

  // Array of named parameters
  $route->params;

  // Matching regular expression
  $route->regex;

  // Contains the contents of any '*' used in the URL pattern
  $route->splat;

  // Shows the url path....if you really need it
  $route->pattern;

  // Shows what middleware is assigned to this
  $route->middleware;

  // Shows the alias assigned to this route
  $route->alias;
}, true);// <-- This true parameter is what makes that happen
```

### Pengelompokan Rute dan Middleware
Mungkin ada saatnya Anda ingin mengelompokkan rute terkait bersama (seperti `/api/v1`).
Anda bisa lakukan ini dengan menggunakan metode `group`:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Matches /api/v1/users
  });

  Flight::route('/posts', function () {
	// Matches /api/v1/posts
  });
});
```

Anda bahkan bisa menumpuk grup dari grup:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() gets variables, it doesn't set a route! See object context below
	Flight::route('GET /users', function () {
	  // Matches GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Matches POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Matches PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() gets variables, it doesn't set a route! See object context below
	Flight::route('GET /users', function () {
	  // Matches GET /api/v2/users
	});
  });
});
```

#### Pengelompokan dengan Konteks Objek

Anda masih bisa menggunakan pengelompokan rute dengan objek `Engine` dengan cara berikut:

```php
$app = Flight::app();

$app->group('/api/v1', function (Router $router) {

  // user the $router variable
  $router->get('/users', function () {
	// Matches GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Matches POST /api/v1/posts
  });
});
```

> **Catatan:** Ini adalah metode yang disukai untuk mendefinisikan rute dan grup dengan objek `$router`.

#### Pengelompokan dengan Middleware

Anda juga bisa menetapkan middleware ke grup rute:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Matches /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // or [ new MyAuthMiddleware() ] if you want to use an instance
```

Lihat detail lebih lanjut di halaman [group middleware](/learn/middleware#grouping-middleware).

### Routing Sumber Daya
Anda bisa membuat set rute untuk sumber daya menggunakan metode `resource`. Ini akan membuat set rute untuk sumber daya yang mengikuti konvensi RESTful.

Untuk membuat sumber daya, lakukan hal berikut:

```php
Flight::resource('/users', UsersController::class);
```

Dan yang akan terjadi di latar belakang adalah itu akan membuat rute berikut:

```php
[
      'index' => 'GET /users',
      'create' => 'GET /users/create',
      'store' => 'POST /users',
      'show' => 'GET /users/@id',
      'edit' => 'GET /users/@id/edit',
      'update' => 'PUT /users/@id',
      'destroy' => 'DELETE /users/@id'
]
```

Dan pengontrol Anda akan menggunakan metode berikut:

```php
class UsersController
{
    public function index(): void
    {
    }

    public function show(string $id): void
    {
    }

    public function create(): void
    {
    }

    public function store(): void
    {
    }

    public function edit(string $id): void
    {
    }

    public function update(string $id): void
    {
    }

    public function destroy(string $id): void
    {
    }
}
```

> **Catatan**: Anda bisa melihat rute yang baru ditambahkan dengan `runway` dengan menjalankan `php runway routes`.

#### Menyesuaikan Rute Sumber Daya

Ada beberapa opsi untuk mengonfigurasi rute sumber daya.

##### Alias Dasar

Anda bisa mengonfigurasi `aliasBase`. Secara default alias adalah bagian terakhir dari URL yang ditentukan.
Misalnya `/users/` akan menghasilkan `aliasBase` dari `users`. Ketika rute ini dibuat, aliasnya adalah `users.index`, `users.create`, dll. Jika Anda ingin mengubah alias, tetapkan `aliasBase` ke nilai yang Anda inginkan.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

##### Only dan Except

Anda juga bisa menentukan rute mana yang ingin Anda buat dengan menggunakan opsi `only` dan `except`.

```php
// Whitelist only these methods and blacklist the rest
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
// Blacklist only these methods and whitelist the rest
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Ini pada dasarnya opsi whitelisting dan blacklisting sehingga Anda bisa menentukan rute mana yang ingin Anda buat.

##### Middleware

Anda juga bisa menentukan middleware yang akan dijalankan pada setiap rute yang dibuat oleh metode `resource`.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

### Respons Streaming

Anda sekarang bisa melakukan streaming respons ke klien menggunakan `stream()` atau `streamWithHeaders()`. 
Ini berguna untuk mengirim file besar, proses jangka panjang, atau menghasilkan respons besar. 
Streaming rute ditangani sedikit berbeda daripada rute biasa.

> **Catatan:** Respons streaming hanya tersedia jika Anda memiliki [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) yang diatur ke `false`.

#### Stream dengan Header Manual

Anda bisa melakukan streaming respons ke klien dengan menggunakan metode `stream()` pada rute. Jika Anda 
melakukan ini, Anda harus menetapkan semua header secara manual sebelum Anda mengoutput apa pun ke klien.
Ini dilakukan dengan fungsi php `header()` atau metode `Flight::response()->setRealHeader()`.

```php
Flight::route('/@filename', function($filename) {

	$response = Flight::response();

	// obviously you would sanitize the path and whatnot.
	$fileNameSafe = basename($filename);

	// If you have additional headers to set here after the route has executed
	// you must define them before anything is echoed out.
	// They must all be a raw call to the header() function or 
	// a call to Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// or
	$response->setRealHeader('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');

	$filePath = '/some/path/to/files/'.$fileNameSafe;

	if (!is_readable($filePath)) {
		Flight::halt(404, 'File not found');
	}

	// manually set the content length if you'd like
	header('Content-Length: '.filesize($filePath));
	// or
	$response->setRealHeader('Content-Length: '.filesize($filePath));

	// Stream the file to the client as it's read
	readfile($filePath);

// This is the magic line here
})->stream();
```

#### Stream dengan Header

Anda juga bisa menggunakan metode `streamWithHeaders()` untuk menetapkan header sebelum Anda mulai streaming.

```php
Flight::route('/stream-users', function() {

	// you can add any additional headers you want here
	// you just must use header() or Flight::response()->setRealHeader()

	// however you pull your data, just as an example...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// This is required to send the data to the client
		ob_flush();
	}
	echo '}';

// This is how you'll set the headers before you start streaming.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// optional status code, defaults to 200
	'status' => 200
]);
```

## Lihat Juga
- [Middleware](/learn/middleware) - Menggunakan middleware dengan rute untuk autentikasi, logging, dll.
- [Dependency Injection](/learn/dependency-injection-container) - Menyederhanakan pembuatan dan pengelolaan objek di rute.
- [Why a Framework?](/learn/why-frameworks) - Memahami manfaat menggunakan kerangka kerja seperti Flight.
- [Extending](/learn/extending) - Cara memperluas Flight dengan fungsionalitas Anda sendiri termasuk metode `notFound`.
- [php.net: preg_match](https://www.php.net/manual/en/function.preg-match.php) - Fungsi PHP untuk pencocokan ekspresi reguler.

## Pemecahan Masalah
- Parameter rute dicocokkan berdasarkan urutan, bukan nama. Pastikan urutan parameter callback cocok dengan definisi rute.
- Menggunakan `Flight::get()` tidak mendefinisikan rute; gunakan `Flight::route('GET /...')` untuk routing atau konteks objek Router di grup (misalnya `$router->get(...)`).
- Properti executedRoute hanya ditetapkan setelah rute dieksekusi; itu NULL sebelum eksekusi.
- Streaming memerlukan fungsi buffering output Flight legacy dinonaktifkan (`flight.v2.output_buffering = false`).
- Untuk injeksi dependensi, hanya definisi rute tertentu yang mendukung instansiasi berbasis container.

### 404 Tidak Ditemukan atau Perilaku Rute Tak Terduga

Jika Anda melihat kesalahan 404 Tidak Ditemukan (tapi Anda bersumpah dengan hidup Anda bahwa itu benar-benar ada dan bukan kesalahan ketik) ini sebenarnya bisa menjadi masalah dengan Anda mengembalikan nilai di endpoint rute Anda daripada hanya mencetaknya. Alasan untuk ini disengaja tapi bisa menyelinap pada beberapa pengembang.

```php
Flight::route('/hello', function(){
	// This might cause a 404 Not Found error
	return 'Hello World';
});

// What you probably want
Flight::route('/hello', function(){
	echo 'Hello World';
});
```

Alasan untuk ini adalah karena mekanisme khusus yang dibangun ke dalam router yang menangani output return sebagai sinyal untuk "pergi ke rute berikutnya". 
Anda bisa melihat perilaku yang didokumentasikan di bagian [Routing](/learn/routing#passing).

## Changelog
- v3: Menambahkan routing sumber daya, alias rute, dan dukungan streaming, grup rute, dan dukungan middleware.
- v1: Sebagian besar fitur dasar tersedia.