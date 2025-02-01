# Routing

> **Catatan:** Ingin memahami lebih lanjut tentang routing? Periksa halaman ["mengapa sebuah framework?"](/learn/why-frameworks) untuk penjelasan yang lebih mendalam.

Routing dasar di Flight dilakukan dengan mencocokkan pola URL dengan fungsi callback atau sebuah array dari sebuah kelas dan metode.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Rute dicocokkan dalam urutan mereka didefinisikan. Rute pertama yang mencocokkan permintaan akan dipanggil.

### Callback/Fungsi
Callback dapat berupa objek apa pun yang dapat dipanggil. Jadi Anda dapat menggunakan fungsi biasa:

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### Kelas
Anda juga dapat menggunakan metode statis dari sebuah kelas:

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

Atau dengan membuat objek terlebih dahulu dan kemudian memanggil metode:

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
// Anda juga dapat melakukan ini tanpa membuat objek terlebih dahulu
// Catatan: Tidak ada argumen yang akan disuntikkan ke konstruktor
Flight::route('/', [ 'Greeting', 'hello' ]);
// Selain itu, Anda dapat menggunakan sintaks lebih pendek ini
Flight::route('/', 'Greeting->hello');
// atau
Flight::route('/', Greeting::class.'->hello');
```

#### Dependency Injection melalui DIC (Dependency Injection Container)
Jika Anda ingin menggunakan dependency injection melalui sebuah container (PSR-11, PHP-DI, Dice, dll), satu-satunya jenis rute yang tersedia adalah langsung membuat objek sendiri dan menggunakan container untuk membuat objek Anda atau Anda dapat menggunakan string untuk mendefinisikan kelas dan metode yang akan dipanggil. Anda dapat pergi ke halaman [Dependency Injection](/learn/extending) untuk informasi lebih lanjut. 

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
		echo "Hello, world! Nama saya adalah {$name}!";
	}
}

// index.php

// Siapkan container dengan parameter apa pun yang Anda butuhkan
// Lihat halaman Dependency Injection untuk informasi lebih lanjut tentang PSR-11
$dice = new \Dice\Dice();

// Jangan lupa untuk menetapkan kembali variabel dengan '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Daftarkan pengendali container
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

## Metode Routing

Secara default, pola rute dicocokkan dengan semua metode permintaan. Anda dapat merespons metode tertentu dengan menempatkan pengenal sebelum URL.

```php
Flight::route('GET /', function () {
  echo 'Saya menerima permintaan GET.';
});

Flight::route('POST /', function () {
  echo 'Saya menerima permintaan POST.';
});

// Anda tidak dapat menggunakan Flight::get() untuk rute karena itu adalah metode 
//    untuk mendapatkan variabel, tidak membuat rute.
// Flight::post('/', function() { /* kode */ });
// Flight::patch('/', function() { /* kode */ });
// Flight::put('/', function() { /* kode */ });
// Flight::delete('/', function() { /* kode */ });
```

Anda juga dapat memetakan beberapa metode ke satu callback dengan menggunakan pemisah `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'Saya menerima baik permintaan GET atau POST.';
});
```

Selain itu, Anda dapat mengambil objek Router yang memiliki beberapa metode pembantu untuk Anda gunakan:

```php

$router = Flight::router();

// memetakan semua metode
$router->map('/', function() {
	echo 'hello world!';
});

// permintaan GET
$router->get('/users', function() {
	echo 'users';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## Ekspresi Reguler

Anda dapat menggunakan ekspresi reguler dalam rute Anda:

```php
Flight::route('/user/[0-9]+', function () {
  // Ini akan mencocokkan /user/1234
});
```

Meskipun metode ini tersedia, disarankan untuk menggunakan parameter bernama, atau
parameter bernama dengan ekspresi reguler, karena lebih mudah dibaca dan lebih mudah untuk dipelihara.

## Parameter Bernama

Anda dapat menentukan parameter bernama dalam rute Anda yang akan diteruskan ke
fungsi callback Anda. **Ini lebih untuk keterbacaan rute daripada yang lain
. Silakan lihat bagian di bawah tentang caveat penting.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

Anda juga dapat menyertakan ekspresi reguler dengan parameter bernama Anda menggunakan
pemisah `:`:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Ini akan mencocokkan /bob/123
  // Tetapi tidak akan mencocokkan /bob/12345
});
```

> **Catatan:** Mencocokkan grup regex `()` dengan parameter posisi tidak didukung. :'\(

### Caveat Penting

Meskipun dalam contoh di atas, tampaknya `@name` terikat langsung pada variabel `$name`, itu tidak. Urutan parameter dalam fungsi callback yang menentukan apa yang diteruskan ke dalamnya. Jadi jika Anda membalik urutan parameter dalam fungsi callback, variabel juga akan dibalik. Berikut adalah contohnya:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

Dan jika Anda mengunjungi URL berikut: `/bob/123`, hasilnya akan menjadi `hello, 123 (bob)!`. 
Silakan berhati-hati saat Anda menetapkan rute dan fungsi callback Anda.

## Parameter Opsional

Anda dapat menentukan parameter bernama yang bersifat opsional untuk pencocokan dengan membungkus
segmen dalam tanda kurung.

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

Parameter opsional yang tidak dicocokkan akan diteruskan sebagai `NULL`.

## Wildcards

Pencocokan hanya dilakukan pada segmen URL individu. Jika Anda ingin mencocokkan beberapa
segmen Anda dapat menggunakan wildcard `*`.

```php
Flight::route('/blog/*', function () {
  // Ini akan mencocokkan /blog/2000/02/01
});
```

Untuk merutekan semua permintaan ke satu callback, Anda dapat melakukan:

```php
Flight::route('*', function () {
  // Lakukan sesuatu
});
```

## Menyampaikan

Anda dapat meneruskan eksekusi ke rute berikutnya yang cocok dengan mengembalikan `true` dari
fungsi callback Anda.

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

Anda dapat menetapkan alias ke sebuah rute, sehingga URL dapat dibuat secara dinamis dilanjutkan dalam kode Anda (seperti template misalnya).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// nanti dalam kode di suatu tempat
Flight::getUrl('user_view', [ 'id' => 5 ]); // akan mengembalikan '/users/5'
```

Ini sangat membantu jika URL Anda kebetulan berubah. Dalam contoh di atas, katakanlah bahwa pengguna dipindahkan ke `/admin/users/@id` alih-alih.
Dengan aliasing di tempat, Anda tidak perlu mengubah di mana pun Anda merujuk alias karena alias sekarang akan mengembalikan `/admin/users/5` seperti dalam
contoh di atas.

Aliasing rute masih berfungsi dalam grup juga:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});

// nanti dalam kode di suatu tempat
Flight::getUrl('user_view', [ 'id' => 5 ]); // akan mengembalikan '/users/5'
```

## Informasi Rute

Jika Anda ingin memeriksa informasi rute yang cocok, Anda dapat meminta objek rute
untuk diteruskan ke fungsi callback Anda dengan meneruskan `true` sebagai parameter ketiga di metode rute. Objek rute akan selalu menjadi parameter terakhir yang diteruskan ke fungsi callback Anda.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array metode HTTP yang dicocokkan
  $route->methods;

  // Array parameter bernama
  $route->params;

  // Ekspresi reguler yang cocok
  $route->regex;

  // Berisi konten dari setiap '*' yang digunakan dalam pola URL
  $route->splat;

  // Menunjukkan jalur url....jika Anda benar-benar membutuhkannya
  $route->pattern;

  // Menunjukkan middleware apa yang ditugaskan untuk ini
  $route->middleware;

  // Menunjukkan alias yang ditugaskan untuk rute ini
  $route->alias;
}, true);
```

## Pengelompokan Rute

Mungkin ada waktu ketika Anda ingin mengelompokkan rute-rute terkait bersama (seperti `/api/v1`).
Anda dapat melakukan ini dengan menggunakan metode `group`:

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

Anda bahkan dapat menelurkan grup dari grup:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() mendapatkan variabel, itu tidak menetapkan rute! Lihat konteks objek di bawah
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

	// Flight::get() mendapatkan variabel, itu tidak menetapkan rute! Lihat konteks objek di bawah
	Flight::route('GET /users', function () {
	  // Mencocokkan GET /api/v2/users
	});
  });
});
```

### Pengelompokan dengan Konteks Objek

Anda masih dapat menggunakan pengelompokan rute dengan objek `Engine` dengan cara berikut:

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

## Resource Routing

Anda dapat membuat serangkaian rute untuk sebuah sumber daya menggunakan metode `resource`. Ini akan membuat
serangkaian rute untuk sumber daya yang mengikuti konvensi RESTful.

Untuk membuat sumber daya, lakukan hal berikut:

```php
Flight::resource('/users', UsersController::class);
```

Dan yang akan terjadi di latar belakang adalah ini akan membuat rute berikut:

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

> **Catatan**: Anda dapat melihat rute yang baru ditambahkan dengan `runway` dengan menjalankan `php runway routes`.

### Menyesuaikan Rute Sumber Daya

Ada beberapa opsi untuk mengonfigurasi rute sumber daya.

#### Alias Basis

Anda dapat mengonfigurasi `aliasBase`. Secara default, alias adalah bagian terakhir dari URL yang ditentukan.
Misalnya `/users/` akan menghasilkan `aliasBase` menjadi `users`. Ketika rute ini dibuat,
alias adalah `users.index`, `users.create`, dst. Jika Anda ingin mengubah alias, atur `aliasBase`
ke nilai yang Anda inginkan.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Hanya dan Kecuali

Anda juga dapat menentukan rute mana yang ingin Anda buat dengan menggunakan opsi `only` dan `except`.

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Ini pada dasarnya adalah opsi daftar putih dan daftar hitam sehingga Anda dapat menentukan rute mana yang ingin Anda buat.

#### Middleware

Anda juga dapat menentukan middleware yang akan dijalankan di setiap rute yang dibuat oleh metode `resource`.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## Streaming

Anda sekarang dapat melakukan streaming respons ke klien menggunakan metode `streamWithHeaders()`. 
Ini berguna untuk mengirim file besar, proses yang memakan waktu lama, atau menghasilkan respons besar. 
Streaming rute ditangani sedikit berbeda dari rute biasa.

> **Catatan:** Streaming respons hanya tersedia jika Anda memiliki [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) disetel ke false.

### Stream dengan Header Manual

Anda dapat melakukan streaming respons ke klien dengan menggunakan metode `stream()` pada sebuah rute. Jika Anda 
melakukan ini, Anda harus menetapkan semua metode secara manual sebelum Anda mengeluarkan apapun kepada klien.
Ini dilakukan dengan fungsi `header()` php atau metode `Flight::response()->setRealHeader()`.

```php
Flight::route('/@filename', function($filename) {

	// jelas Anda akan menyaring jalur dan lain-lain.
	$fileNameSafe = basename($filename);

	// Jika Anda memiliki header tambahan untuk diatur di sini setelah rute dieksekusi
	// Anda harus mendefinisikannya sebelum ada yang di-echo keluar.
	// Mereka semua harus merupakan panggilan mentah ke fungsi header() 
	// atau panggilan ke Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// atau
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// Penanganan kesalahan dan lain-lain
	if(empty($fileData)) {
		Flight::halt(404, 'File tidak ditemukan');
	}

	// set panjang konten secara manual jika Anda suka
	header('Content-Length: '.filesize($filename));

	// Streaming data ke klien
	echo $fileData;

// Ini adalah baris ajaib di sini
})->stream();
```

### Stream dengan Header

Anda juga dapat menggunakan metode `streamWithHeaders()` untuk mengatur header sebelum Anda mulai streaming.

```php
Flight::route('/stream-users', function() {

	// Anda dapat menambahkan header tambahan apa pun yang Anda inginkan di sini
	// Anda hanya harus menggunakan header() atau Flight::response()->setRealHeader()

	// namun cara Anda menarik data Anda, hanya sebagai contoh...
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

// Ini adalah bagaimana Anda mengatur header sebelum Anda mulai streaming.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// kode status opsional, default ke 200
	'status' => 200
]);
```