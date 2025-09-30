# Routing

## Ikhtisar
Routing di Flight PHP memetakan pola URL ke fungsi callback atau metode kelas, memungkinkan penanganan permintaan yang cepat dan sederhana. Dirancang untuk overhead minimal, penggunaan ramah pemula, dan keterluangan tanpa ketergantungan eksternal.

## Pemahaman
Routing adalah mekanisme inti yang menghubungkan permintaan HTTP ke logika aplikasi Anda di Flight. Dengan mendefinisikan rute, Anda menentukan bagaimana URL yang berbeda memicu kode spesifik, baik melalui fungsi, metode kelas, atau tindakan controller. Sistem routing Flight fleksibel, mendukung pola dasar, parameter bernama, ekspresi reguler, dan fitur lanjutan seperti injeksi dependensi dan routing sumber daya. Pendekatan ini menjaga kode Anda terorganisir dan mudah dipelihara, sambil tetap cepat dan sederhana untuk pemula serta dapat dikembangkan untuk pengguna lanjutan.

> **Catatan:** Ingin memahami lebih lanjut tentang routing? Lihat halaman ["why a framework?"](/learn/why-frameworks) untuk penjelasan yang lebih mendalam.

## Penggunaan Dasar

### Mendefinisikan Rute Sederhana
Routing dasar di Flight dilakukan dengan mencocokkan pola URL dengan fungsi callback atau array dari kelas dan metode.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Rute dicocokkan sesuai urutan definisinya. Rute pertama yang cocok dengan permintaan akan dipanggil.

### Menggunakan Fungsi sebagai Callback
Callback bisa berupa objek apa pun yang dapat dipanggil. Jadi Anda bisa menggunakan fungsi biasa:

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### Menggunakan Kelas dan Metode sebagai Controller
Anda juga bisa menggunakan metode (statis atau tidak) dari kelas:

```php
class GreetingController {
    public function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', [ 'GreetingController','hello' ]);
// atau
Flight::route('/', [ GreetingController::class, 'hello' ]); // metode yang disarankan
// atau
Flight::route('/', [ 'GreetingController::hello' ]);
// atau 
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

> **Catatan:** Secara default, ketika controller dipanggil dalam framework, kelas `flight\Engine` selalu diinjeksikan kecuali Anda menentukannya melalui [container injeksi dependensi](/learn/dependency-injection-container)

### Routing Khusus Metode

Secara default, pola rute dicocokkan dengan semua metode permintaan. Anda bisa merespons metode spesifik dengan menempatkan pengenal sebelum URL.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// Anda tidak bisa menggunakan Flight::get() untuk rute karena itu adalah metode 
//    untuk mendapatkan variabel, bukan membuat rute.
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

### Menggunakan Objek Router

Selain itu, Anda bisa mengambil objek Router yang memiliki beberapa metode pembantu untuk digunakan:

```php

$router = Flight::router();

// memetakan semua metode seperti Flight::route()
$router->map('/', function() {
	echo 'hello world!';
});

// permintaan GET
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
  // Ini akan cocok dengan /user/1234
});
```

Meskipun metode ini tersedia, disarankan untuk menggunakan parameter bernama, atau parameter bernama dengan ekspresi reguler, karena lebih mudah dibaca dan dipelihara.

### Parameter Bernama
Anda bisa menentukan parameter bernama di rute Anda yang akan diteruskan ke fungsi callback Anda. **Ini lebih untuk keterbacaan rute daripada yang lain. Lihat bagian di bawah tentang peringatan penting.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

Anda juga bisa menyertakan ekspresi reguler dengan parameter bernama dengan menggunakan pemisah `:`:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Ini akan cocok dengan /bob/123
  // Tapi tidak akan cocok dengan /bob/12345
});
```

> **Catatan:** Pencocokan grup regex `()` dengan parameter posisional tidak didukung. Contoh: `:'\(`

#### Peringatan Penting

Meskipun dalam contoh di atas, tampaknya `@name` langsung terkait dengan variabel `$name`, sebenarnya tidak. Urutan parameter dalam fungsi callback yang menentukan apa yang diteruskan ke dalamnya. Jika Anda menukar urutan parameter dalam fungsi callback, variabel juga akan bertukar. Berikut contohnya:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

Dan jika Anda mengunjungi URL berikut: `/bob/123`, outputnya akan menjadi `hello, 123 (bob)!`. 
_Harap berhati-hati_ saat menyiapkan rute dan fungsi callback Anda!

### Parameter Opsional
Anda bisa menentukan parameter bernama yang opsional untuk pencocokan dengan membungkus segmen dalam tanda kurung.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Ini akan cocok dengan URL berikut:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Parameter opsional apa pun yang tidak cocok akan diteruskan sebagai `NULL`.

### Routing Wildcard
Pencocokan hanya dilakukan pada segmen URL individual. Jika Anda ingin mencocokkan beberapa segmen, Anda bisa menggunakan wildcard `*`.

```php
Flight::route('/blog/*', function () {
  // Ini akan cocok dengan /blog/2000/02/01
});
```

Untuk merutekan semua permintaan ke satu callback, Anda bisa lakukan:

```php
Flight::route('*', function () {
  // Lakukan sesuatu
});
```

### Penanganan 404 Tidak Ditemukan

Secara default, jika URL tidak ditemukan, Flight akan mengirim respons `HTTP 404 Not Found` yang sangat sederhana dan polos.
Jika Anda ingin respons 404 yang lebih disesuaikan, Anda bisa [memetakan](/learn/extending) metode `notFound` sendiri:

```php
Flight::map('notFound', function() {
	$url = Flight::request()->url;

	// Anda juga bisa menggunakan Flight::render() dengan template kustom.
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

## Penggunaan Lanjutan

### Injeksi Dependensi di Rute
Jika Anda ingin menggunakan injeksi dependensi melalui container (PSR-11, PHP-DI, Dice, dll), satu-satunya jenis rute yang tersedia adalah dengan membuat objek secara langsung sendiri dan menggunakan container untuk membuat objek Anda atau Anda bisa menggunakan string untuk mendefinisikan kelas dan metode yang akan dipanggil. Anda bisa kunjungi halaman [Dependency Injection](/learn/dependency-injection-container) untuk informasi lebih lanjut. 

Berikut contoh singkat:

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
		// lakukan sesuatu dengan $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";
	}
}

// index.php

// Siapkan container dengan parameter apa pun yang Anda butuhkan
// Lihat halaman Dependency Injection untuk informasi lebih lanjut tentang PSR-11
$dice = new \Dice\Dice();

// Jangan lupa untuk menugaskan ulang variabel dengan '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Daftarkan penanganan container
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Rute seperti biasa
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// atau
Flight::route('/hello/@id', 'Greeting->hello');
// atau
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

### Mengalihkan Eksekusi ke Rute Berikutnya
<span class="badge bg-warning">Deprecated</span>
Anda bisa mengalihkan eksekusi ke rute pencocokan berikutnya dengan mengembalikan `true` dari fungsi callback Anda.

```php
Flight::route('/user/@name', function (string $name) {
  // Periksa kondisi tertentu
  if ($name !== "Bob") {
    // Lanjutkan ke rute berikutnya
    return true;
  }
});

Flight::route('/user/*', function () {
  // Ini akan dipanggil
});
```

Sekarang disarankan untuk menggunakan [middleware](/learn/middleware) untuk menangani kasus penggunaan kompleks seperti ini.

### Alias Rute
Dengan menetapkan alias ke rute, Anda bisa memanggil alias tersebut di aplikasi Anda secara dinamis untuk dihasilkan nanti di kode Anda (contoh: tautan di template HTML, atau menghasilkan URL redirect).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
// atau 
Flight::route('/users/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');

// nanti di suatu tempat di kode
class UserController {
	public function update() {

		// kode untuk menyimpan pengguna...
		$id = $user['id']; // 5 misalnya

		$redirectUrl = Flight::getUrl('user_view', [ 'id' => $id ]); // akan mengembalikan '/users/5'
		Flight::redirect($redirectUrl);
	}
}

```

Ini sangat membantu jika URL Anda berubah. Dalam contoh di atas, katakanlah pengguna dipindahkan ke `/admin/users/@id` sebagai gantinya.
Dengan aliasing di tempat untuk rute, Anda tidak lagi perlu mencari semua URL lama di kode Anda dan mengubahnya karena alias sekarang akan mengembalikan `/admin/users/5` seperti dalam contoh di atas.

Alias rute masih berfungsi dalam grup juga:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
	// atau
	Flight::route('/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');
});
```

### Memeriksa Informasi Rute
Jika Anda ingin memeriksa informasi rute pencocokan, ada 2 cara Anda bisa lakukan ini:

1. Anda bisa menggunakan properti `executedRoute` pada objek `Flight::router()`.
2. Anda bisa meminta objek rute diteruskan ke callback Anda dengan meneruskan `true` sebagai parameter ketiga dalam metode rute. Objek rute selalu menjadi parameter terakhir yang diteruskan ke fungsi callback Anda.

#### `executedRoute`
```php
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;
  // Lakukan sesuatu dengan $route
  // Array metode HTTP yang dicocokkan
  $route->methods;

  // Array parameter bernama
  $route->params;

  // Ekspresi reguler pencocokan
  $route->regex;

  // Berisi isi dari '*' apa pun yang digunakan dalam pola URL
  $route->splat;

  // Menunjukkan jalur URL....jika Anda benar-benar membutuhkannya
  $route->pattern;

  // Menunjukkan middleware apa yang ditetapkan untuk ini
  $route->middleware;

  // Menunjukkan alias yang ditetapkan untuk rute ini
  $route->alias;
});
```

> **Catatan:** Properti `executedRoute` hanya akan ditetapkan setelah rute dieksekusi. Jika Anda mencoba mengaksesnya sebelum rute dieksekusi, itu akan menjadi `NULL`. Anda juga bisa menggunakan executedRoute di [middleware](/learn/middleware) juga!

#### Teruskan `true` ke definisi rute
```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array metode HTTP yang dicocokkan
  $route->methods;

  // Array parameter bernama
  $route->params;

  // Ekspresi reguler pencocokan
  $route->regex;

  // Berisi isi dari '*' apa pun yang digunakan dalam pola URL
  $route->splat;

  // Menunjukkan jalur URL....jika Anda benar-benar membutuhkannya
  $route->pattern;

  // Menunjukkan middleware apa yang ditetapkan untuk ini
  $route->middleware;

  // Menunjukkan alias yang ditetapkan untuk rute ini
  $route->alias;
}, true);// <-- Parameter true ini yang membuatnya terjadi
```

### Pengelompokan Rute dan Middleware
Mungkin ada saatnya Anda ingin mengelompokkan rute terkait bersama (seperti `/api/v1`).
Anda bisa lakukan ini dengan menggunakan metode `group`:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Cocok dengan /api/v1/users
  });

  Flight::route('/posts', function () {
	// Cocok dengan /api/v1/posts
  });
});
```

Anda bahkan bisa menumpuk grup dari grup:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() mendapatkan variabel, itu tidak menetapkan rute! Lihat konteks objek di bawah
	Flight::route('GET /users', function () {
	  // Cocok dengan GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Cocok dengan POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Cocok dengan PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() mendapatkan variabel, itu tidak menetapkan rute! Lihat konteks objek di bawah
	Flight::route('GET /users', function () {
	  // Cocok dengan GET /api/v2/users
	});
  });
});
```

#### Pengelompokan dengan Konteks Objek

Anda masih bisa menggunakan pengelompokan rute dengan objek `Engine` dengan cara berikut:

```php
$app = Flight::app();

$app->group('/api/v1', function (Router $router) {

  // gunakan variabel $router
  $router->get('/users', function () {
	// Cocok dengan GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Cocok dengan POST /api/v1/posts
  });
});
```

> **Catatan:** Ini adalah metode yang disarankan untuk mendefinisikan rute dan grup dengan objek `$router`.

#### Pengelompokan dengan Middleware

Anda juga bisa menetapkan middleware ke grup rute:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Cocok dengan /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // atau [ new MyAuthMiddleware() ] jika Anda ingin menggunakan instance
```

Lihat detail lebih lanjut di halaman [group middleware](/learn/middleware#grouping-middleware).

### Routing Sumber Daya
Anda bisa membuat set rute untuk sumber daya menggunakan metode `resource`. Ini akan membuat set rute untuk sumber daya yang mengikuti konvensi RESTful.

Untuk membuat sumber daya, lakukan hal berikut:

```php
Flight::resource('/users', UsersController::class);
```

Dan yang akan terjadi di belakang adalah membuat rute berikut:

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

Dan controller Anda akan menggunakan metode berikut:

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

Anda bisa mengonfigurasi `aliasBase`. Secara default, alias adalah bagian terakhir dari URL yang ditentukan.
Misalnya `/users/` akan menghasilkan `aliasBase` dari `users`. Saat rute ini dibuat, aliasnya adalah `users.index`, `users.create`, dll. Jika Anda ingin mengubah alias, tetapkan `aliasBase` ke nilai yang Anda inginkan.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

##### Only dan Except

Anda juga bisa menentukan rute mana yang ingin Anda buat dengan menggunakan opsi `only` dan `except`.

```php
// Daftar putih hanya metode ini dan daftar hitam sisanya
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
// Daftar hitam hanya metode ini dan daftar putih sisanya
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Ini pada dasarnya opsi daftar putih dan daftar hitam sehingga Anda bisa menentukan rute mana yang ingin dibuat.

##### Middleware

Anda juga bisa menentukan middleware yang akan dijalankan pada setiap rute yang dibuat oleh metode `resource`.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

### Respons Streaming

Anda sekarang bisa streaming respons ke klien menggunakan `stream()` atau `streamWithHeaders()`. 
Ini berguna untuk mengirim file besar, proses jangka panjang, atau menghasilkan respons besar. 
Streaming rute ditangani sedikit berbeda daripada rute biasa.

> **Catatan:** Respons streaming hanya tersedia jika Anda memiliki [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) yang ditetapkan ke `false`.

#### Stream dengan Header Manual

Anda bisa streaming respons ke klien dengan menggunakan metode `stream()` pada rute. Jika Anda 
melakukannya, Anda harus menetapkan semua header secara manual sebelum Anda mengeluarkan apa pun ke klien.
Ini dilakukan dengan fungsi php `header()` atau metode `Flight::response()->setRealHeader()`.

```php
Flight::route('/@filename', function($filename) {

	$response = Flight::response();

	// jelas Anda akan membersihkan jalur dan sebagainya.
	$fileNameSafe = basename($filename);

	// Jika Anda memiliki header tambahan untuk ditetapkan di sini setelah rute dieksekusi
	// Anda harus mendefinisikannya sebelum apa pun diekstrak.
	// Semuanya harus panggilan mentah ke fungsi header() atau 
	// panggilan ke Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// atau
	$response->setRealHeader('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');

	$filePath = '/some/path/to/files/'.$fileNameSafe;

	if (!is_readable($filePath)) {
		Flight::halt(404, 'File not found');
	}

	// tetapkan panjang konten secara manual jika Anda suka
	header('Content-Length: '.filesize($filePath));
	// atau
	$response->setRealHeader('Content-Length: '.filesize($filePath));

	// Stream file ke klien saat dibaca
	readfile($filePath);

// Ini adalah baris ajaib di sini
})->stream();
```

#### Stream dengan Header

Anda juga bisa menggunakan metode `streamWithHeaders()` untuk menetapkan header sebelum Anda mulai streaming.

```php
Flight::route('/stream-users', function() {

	// Anda bisa menambahkan header tambahan apa pun yang Anda inginkan di sini
	// Anda hanya harus menggunakan header() atau Flight::response()->setRealHeader()

	// bagaimanapun Anda menarik data Anda, hanya sebagai contoh...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// Ini diperlukan untuk mengirim data ke klien
		ob_flush();
	}
	echo '}';

// Ini cara Anda menetapkan header sebelum Anda mulai streaming.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// kode status opsional, default ke 200
	'status' => 200
]);
```

## Lihat Juga
- [Middleware](/learn/middleware) - Menggunakan middleware dengan rute untuk autentikasi, logging, dll.
- [Dependency Injection](/learn/dependency-injection-container) - Menyederhanakan pembuatan dan pengelolaan objek di rute.
- [Why a Framework?](/learn/why-frameworks) - Memahami manfaat menggunakan framework seperti Flight.
- [Extending](/learn/extending) - Cara memperluas Flight dengan fungsionalitas sendiri termasuk metode `notFound`.
- [php.net: preg_match](https://www.php.net/manual/en/function.preg-match.php) - Fungsi PHP untuk pencocokan ekspresi reguler.

## Pemecahan Masalah
- Parameter rute dicocokkan berdasarkan urutan, bukan nama. Pastikan urutan parameter callback cocok dengan definisi rute.
- Menggunakan `Flight::get()` tidak mendefinisikan rute; gunakan `Flight::route('GET /...')` untuk routing atau konteks objek Router di grup (misalnya `$router->get(...)`).
- Properti executedRoute hanya ditetapkan setelah rute dieksekusi; itu NULL sebelum eksekusi.
- Streaming memerlukan fungsionalitas output buffering Flight lama dinonaktifkan (`flight.v2.output_buffering = false`).
- Untuk injeksi dependensi, hanya definisi rute tertentu yang mendukung instansiasi berbasis container.

### 404 Tidak Ditemukan atau Perilaku Rute Tak Terduga

Jika Anda melihat kesalahan 404 Tidak Ditemukan (tapi Anda bersumpah dengan nyawa Anda bahwa itu benar-benar ada dan bukan kesalahan ketik) ini sebenarnya bisa jadi masalah 
dengan Anda mengembalikan nilai di endpoint rute Anda daripada hanya mengekstraknya. Alasan untuk ini disengaja tapi bisa menyelinap pada beberapa pengembang.

```php

Flight::route('/hello', function(){
	// Ini mungkin menyebabkan kesalahan 404 Tidak Ditemukan
	return 'Hello World';
});

// Yang mungkin Anda inginkan
Flight::route('/hello', function(){
	echo 'Hello World';
});

```

Alasan untuk ini adalah karena mekanisme khusus yang dibangun ke dalam router yang menangani output return sebagai sinyal untuk "pergi ke rute berikutnya". 
Anda bisa melihat perilaku yang didokumentasikan di bagian [Routing](/learn/routing#passing).

## Changelog
- v3: Menambahkan routing sumber daya, alias rute, dan dukungan streaming, grup rute, dan dukungan middleware.
- v1: Mayoritas fitur dasar tersedia.