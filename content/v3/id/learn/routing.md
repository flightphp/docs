# Perutean

> **Catatan:** Ingin memahami lebih lanjut tentang perutean? Periksa halaman ["why a framework?"](/learn/why-frameworks) untuk penjelasan yang lebih mendalam.

Perutean dasar di Flight dilakukan dengan mencocokkan pola URL dengan fungsi callback atau array dari kelas dan metode.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Rute dicocokkan dalam urutan mereka didefinisikan. Rute pertama yang cocok dengan permintaan akan dipanggil.

### Callbacks/Fungsi
Callback bisa menjadi objek apa saja yang dapat dipanggil. Jadi Anda bisa menggunakan fungsi reguler:

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### Kelas
Anda juga bisa menggunakan metode statis dari kelas:

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

Atau dengan membuat objek terlebih dahulu kemudian memanggil metodenya:

```php
// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hello, {$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// Anda juga bisa melakukannya tanpa membuat objek terlebih dahulu
// Catatan: Tidak ada argumen yang disuntikkan ke konstruktor
Flight::route('/', [ 'Greeting', 'hello' ]);
// Selain itu, Anda bisa menggunakan sintaks yang lebih pendek
Flight::route('/', 'Greeting->hello');
// atau
Flight::route('/', Greeting::class.'->hello');
```

#### Injeksi Ketergantungan melalui DIC (Container Injeksi Ketergantungan)
Jika Anda ingin menggunakan injeksi ketergantungan melalui container (PSR-11, PHP-DI, Dice, dll), jenis rute yang tersedia hanyalah dengan membuat objek langsung sendiri dan menggunakan container untuk membuat objek Anda atau menggunakan string untuk mendefinisikan kelas dan metode yang dipanggil. Anda bisa pergi ke halaman [Dependency Injection](/learn/extending) untuk informasi lebih lanjut.

Berikut adalah contoh cepat:

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

// Daftarkan penangan container
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

## Perutean Metode

Secara default, pola rute dicocokkan dengan semua metode permintaan. Anda bisa merespons metode spesifik dengan menempatkan pengidentifikasi sebelum URL.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// Anda tidak bisa menggunakan Flight::get() untuk rute karena itu adalah metode 
//    untuk mendapatkan variabel, bukan membuat rute.
// Flight::post('/', function() { /* code */ });
// Flight::patch('/', function() { /* code */ });
// Flight::put('/', function() { /* code */ });
// Flight::delete('/', function() { /* code */ });
```

Anda juga bisa memetakan beberapa metode ke callback tunggal dengan menggunakan delimiter `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

Selain itu, Anda bisa mengambil objek Router yang memiliki beberapa metode bantu untuk digunakan:

```php
$router = Flight::router();

// memetakan semua metode
$router->map('/', function() {
	echo 'hello world!';
});

// Permintaan GET
$router->get('/users', function() {
	echo 'users';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## Ekspresi Reguler

Anda bisa menggunakan ekspresi reguler dalam rute Anda:

```php
Flight::route('/user/[0-9]+', function () {
  // Ini akan mencocokkan /user/1234
});
```

Meskipun metode ini tersedia, disarankan untuk menggunakan parameter bernama, atau parameter bernama dengan ekspresi reguler, karena lebih mudah dibaca dan dipertahankan.

## Parameter Bernama

Anda bisa menentukan parameter bernama dalam rute Anda yang akan dikirimkan ke fungsi callback Anda. **Ini lebih untuk keterbacaan rute daripada apa pun. Lihat bagian di bawah tentang caveat penting.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

Anda juga bisa menyertakan ekspresi reguler dengan parameter bernama dengan menggunakan delimiter `:`:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Ini akan mencocokkan /bob/123
  // Tapi tidak akan mencocokkan /bob/12345
});
```

> **Catatan:** Kelompok regex pencocokan `()` dengan parameter posisional tidak didukung. :'\(

### Caveat Penting

Meskipun dalam contoh di atas, seolah-olah `@name` langsung terikat ke variabel `$name`, itu tidak. Urutan parameter dalam fungsi callback adalah yang menentukan apa yang dikirimkan kepadanya. Jadi jika Anda mengganti urutan parameter dalam fungsi callback, variabel juga akan berganti. Berikut adalah contoh:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

Dan jika Anda pergi ke URL berikut: `/bob/123`, outputnya akan menjadi `hello, 123 (bob)!`. Harap berhati-hati saat mengatur rute dan fungsi callback Anda.

## Parameter Opsional

Anda bisa menentukan parameter bernama yang opsional untuk pencocokan dengan membungkus segmen dalam tanda kurung.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Ini akan mencocokkan URL berikut:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Parameter opsional apa pun yang tidak cocok akan dikirimkan sebagai `NULL`.

## Wildcards

Pencocokan hanya dilakukan pada segmen URL individu. Jika Anda ingin mencocokkan beberapa segmen, Anda bisa menggunakan wildcard `*`.

```php
Flight::route('/blog/*', function () {
  // Ini akan mencocokkan /blog/2000/02/01
});
```

Untuk merute semua permintaan ke callback tunggal, Anda bisa melakukan:

```php
Flight::route('*', function () {
  // Lakukan sesuatu
});
```

## Passing

Anda bisa meneruskan eksekusi ke rute pencocokan berikutnya dengan mengembalikan `true` dari fungsi callback Anda.

```php
Flight::route('/user/@name', function (string $name) {
  // Periksa beberapa kondisi
  if ($name !== "Bob") {
    // Lanjutkan ke rute berikutnya
    return true;
  }
});

Flight::route('/user/*', function () {
  // Ini akan dipanggil
});
```

## Aliasing Rute

Anda bisa menetapkan alias ke rute, sehingga URL bisa dihasilkan secara dinamis nanti dalam kode Anda (seperti template misalnya).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// nanti dalam kode di suatu tempat
Flight::getUrl('user_view', [ 'id' => 5 ]); // akan mengembalikan '/users/5'
```

Ini sangat membantu jika URL Anda berubah. Dalam contoh di atas, katakanlah pengguna dipindahkan ke `/admin/users/@id` sebagai gantinya. Dengan aliasing, Anda tidak perlu mengubah di mana saja Anda merujuk alias karena alias sekarang akan mengembalikan `/admin/users/5` seperti dalam contoh di atas.

Aliasing rute masih berfungsi dalam grup juga:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// nanti dalam kode di suatu tempat
Flight::getUrl('user_view', [ 'id' => 5 ]); // akan mengembalikan '/users/5'
```

## Info Rute

Jika Anda ingin memeriksa informasi rute pencocokan, ada 2 cara Anda bisa melakukannya. Anda bisa menggunakan properti `executedRoute` atau Anda bisa meminta objek rute untuk dikirimkan ke callback Anda dengan meneruskan `true` sebagai parameter ketiga dalam metode rute. Objek rute akan selalu menjadi parameter terakhir yang dikirimkan ke fungsi callback Anda.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array metode HTTP yang dicocokkan
  $route->methods;

  // Array parameter bernama
  $route->params;

  // Ekspresi reguler pencocokan
  $route->regex;

  // Berisi isi dari '*' yang digunakan dalam pola URL
  $route->splat;

  // Menampilkan path URL....jika Anda benar-benar membutuhkannya
  $route->pattern;

  // Menampilkan middleware yang ditugaskan ke ini
  $route->middleware;

  // Menampilkan alias yang ditugaskan ke rute ini
  $route->alias;
}, true);
```

Atau jika Anda ingin memeriksa rute yang dieksekusi terakhir, Anda bisa melakukan:

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

  // Berisi isi dari '*' yang digunakan dalam pola URL
  $route->splat;

  // Menampilkan path URL....jika Anda benar-benar membutuhkannya
  $route->pattern;

  // Menampilkan middleware yang ditugaskan ke ini
  $route->middleware;

  // Menampilkan alias yang ditugaskan ke rute ini
  $route->alias;
});
```

> **Catatan:** Properti `executedRoute` hanya akan diatur setelah rute dieksekusi. Jika Anda mencoba mengaksesnya sebelum rute dieksekusi, itu akan menjadi `NULL`. Anda juga bisa menggunakan executedRoute dalam middleware juga!

## Pengelompokan Rute

Mungkin ada saatnya Anda ingin mengelompokkan rute terkait bersama (seperti `/api/v1`). Anda bisa melakukan ini dengan menggunakan metode `group`:

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

Anda bahkan bisa menyimpan grup dalam grup:

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

### Pengelompokan dengan Konteks Objek

Anda masih bisa menggunakan pengelompokan rute dengan objek `Engine` dengan cara berikut:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // gunakan variabel $router
  $router->get('/users', function () {
	// Mencocokkan GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Mencocokkan POST /api/v1/posts
  });
});
```

### Pengelompokan dengan Middleware

Anda juga bisa menetapkan middleware ke grup rute:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Mencocokkan /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // atau [ new MyAuthMiddleware() ] jika Anda ingin menggunakan instance
```

Lihat detail lebih lanjut di halaman [group middleware](/learn/middleware#grouping-middleware).

## Perutean Sumber Daya

Anda bisa membuat satu set rute untuk sumber daya menggunakan metode `resource`. Ini akan membuat satu set rute untuk sumber daya yang mengikuti konvensi RESTful.

Untuk membuat sumber daya, lakukan berikut:

```php
Flight::resource('/users', UsersController::class);
```

Dan apa yang akan terjadi di latar belakang adalah itu akan membuat rute berikut:

```php
[
      'index' => 'GET ',
      'create' => 'GET /create',
      'store' => 'POST ',
      'show' => 'GET /@id',
      'edit' => 'GET /@id/edit',
      'update' => 'PUT /@id',
      'destroy' => 'DELETE /@id'
]
```

Dan controller Anda akan terlihat seperti ini:

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

### Menyesuaikan Rute Sumber Daya

Ada beberapa opsi untuk mengonfigurasi rute sumber daya.

#### Basis Alias

Anda bisa mengonfigurasi `aliasBase`. Secara default, alias adalah bagian terakhir dari URL yang ditentukan. Misalnya `/users/` akan menghasilkan `aliasBase` dari `users`. Saat rute ini dibuat, aliasnya adalah `users.index`, `users.create`, dll. Jika Anda ingin mengubah alias, atur `aliasBase` ke nilai yang Anda inginkan.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Only dan Except

Anda juga bisa menentukan rute mana yang ingin Anda buat dengan menggunakan opsi `only` dan `except`.

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Ini pada dasarnya adalah opsi whitelist dan blacklist sehingga Anda bisa menentukan rute mana yang ingin Anda buat.

#### Middleware

Anda juga bisa menentukan middleware untuk dijalankan pada setiap rute yang dibuat oleh metode `resource`.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## Streaming

Anda sekarang bisa mengalirkan respons ke klien menggunakan metode `streamWithHeaders()`. Ini berguna untuk mengirim file besar, proses yang berjalan lama, atau menghasilkan respons besar. Mengalirkan rute ditangani sedikit berbeda daripada rute reguler.

> **Catatan:** Respons streaming hanya tersedia jika Anda memiliki [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) diatur ke false.

### Stream dengan Header Manual

Anda bisa mengalirkan respons ke klien dengan menggunakan metode `stream()` pada rute. Jika Anda melakukannya, Anda harus mengatur semua metode secara manual sebelum mengeluarkan apa pun ke klien. Ini dilakukan dengan fungsi `header()` php atau metode `Flight::response()->setRealHeader()`.

```php
Flight::route('/@filename', function($filename) {

	// jelas Anda akan membersihkan path dan sebagainya.
	$fileNameSafe = basename($filename);

	// Jika Anda memiliki header tambahan untuk diatur di sini setelah rute dieksekusi
	// Anda harus mendefinisikannya sebelum apa pun yang dik echoing.
	// Mereka harus semuanya panggilan mentah ke fungsi header() atau 
	// panggilan ke Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// atau
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// Penangkapan error dan sebagainya
	if(empty($fileData)) {
		Flight::halt(404, 'File not found');
	}

	// atur panjang konten secara manual jika Anda mau
	header('Content-Length: '.filesize($filename));

	// Alirkan data ke klien
	echo $fileData;

// Ini adalah baris ajaib di sini
})->stream();
```

### Stream dengan Header

Anda juga bisa menggunakan metode `streamWithHeaders()` untuk mengatur header sebelum Anda mulai streaming.

```php
Flight::route('/stream-users', function() {

	// Anda bisa menambahkan header tambahan apa pun yang Anda inginkan di sini
	// Anda hanya harus menggunakan header() atau Flight::response()->setRealHeader()

	// bagaimanapun Anda menarik data, hanya sebagai contoh...
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

// Ini adalah cara Anda akan mengatur header sebelum mulai streaming.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// kode status opsional, default ke 200
	'status' => 200
]);
```