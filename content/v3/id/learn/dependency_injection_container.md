# Kontainer Penyuntikan Ketergantungan

## Pengenalan

Kontainer Penyuntikan Ketergantungan (DIC) adalah alat yang kuat yang memungkinkan Anda untuk mengelola
ketergantungan aplikasi Anda. Ini adalah konsep kunci dalam kerangka PHP modern dan digunakan
untuk mengelola instansiasi dan konfigurasi objek. Beberapa contoh pustaka DIC adalah: [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), 
[PHP-DI](http://php-di.org/), dan [league/container](https://container.thephpleague.com/).

DIC adalah cara mewah untuk mengatakan bahwa ia memungkinkan Anda untuk membuat dan mengelola kelas Anda di lokasi
terpusat. Ini berguna ketika Anda perlu mengoper objek yang sama ke beberapa kelas (seperti pengontrol Anda). Contoh sederhana mungkin membantu ini menjadi lebih jelas.

## Contoh Dasar

Cara lama dalam melakukan sesuatu mungkin terlihat seperti ini:
```php

require 'vendor/autoload.php';

// kelas untuk mengelola pengguna dari database
class UserController {

	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function view(int $id) {
		$stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
		$stmt->execute(['id' => $id]);

		print_r($stmt->fetch());
	}
}

$User = new UserController(new PDO('mysql:host=localhost;dbname=test', 'user', 'pass'));
Flight::route('/user/@id', [ $UserController, 'view' ]);

Flight::start();
```

Anda dapat melihat dari kode di atas bahwa kami membuat objek `PDO` baru dan mengoperasikan
ke kelas `UserController` kami. Ini baik untuk aplikasi kecil, tetapi seiring pertumbuhan aplikasi Anda,
Anda akan menemukan bahwa Anda membuat objek `PDO` yang sama di beberapa tempat. Inilah saatnya DIC berguna.

Berikut adalah contoh yang sama menggunakan DIC (menggunakan Dice):
```php

require 'vendor/autoload.php';

// kelas yang sama seperti di atas. Tidak ada yang berubah
class UserController {

	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function view(int $id) {
		$stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
		$stmt->execute(['id' => $id]);

		print_r($stmt->fetch());
	}
}

// buat kontainer baru
$container = new \Dice\Dice;
// jangan lupa untuk menetapkannya kembali ke dirinya sendiri seperti di bawah ini!
$container = $container->addRule('PDO', [
	// dibagikan berarti objek yang sama akan dikembalikan setiap kali
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// Ini mendaftarkan penangan kontainer sehingga Flight tahu untuk menggunakannya.
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// sekarang kita bisa menggunakan kontainer untuk membuat UserController kita
Flight::route('/user/@id', [ 'UserController', 'view' ]);
// atau alternatifnya, Anda dapat mendefinisikan rute seperti ini
Flight::route('/user/@id', 'UserController->view');
// atau
Flight::route('/user/@id', 'UserController::view');

Flight::start();
```

Saya yakin Anda mungkin berpikir bahwa banyak kode tambahan ditambahkan ke contoh ini.
Keajaiban datang ketika Anda memiliki pengontrol lain yang membutuhkan objek `PDO`. 

```php

// Jika semua pengontrol Anda memiliki konstruktor yang membutuhkan objek PDO
// masing-masing rute di bawah ini akan secara otomatis menginjeksikannya!!!
Flight::route('/company/@id', 'CompanyController->view');
Flight::route('/organization/@id', 'OrganizationController->view');
Flight::route('/category/@id', 'CategoryController->view');
Flight::route('/settings', 'SettingsController->view');
```

Bonus tambahan dari memanfaatkan DIC adalah bahwa pengujian unit menjadi jauh lebih mudah. Anda dapat
membuat objek tiruan dan mengoperasikannya ke kelas Anda. Ini adalah manfaat besar ketika Anda
menulis tes untuk aplikasi Anda!

## PSR-11

Flight juga dapat menggunakan kontainer yang sesuai dengan PSR-11. Ini berarti Anda dapat menggunakan
kontainer apa pun yang mengimplementasikan antarmuka PSR-11. Berikut adalah contoh menggunakan kontainer
PSR-11 dari League:

```php

require 'vendor/autoload.php';

// kelas UserController yang sama seperti di atas

$container = new \League\Container\Container();
$container->add(UserController::class)->addArgument(PdoWrapper::class);
$container->add(PdoWrapper::class)
	->addArgument('mysql:host=localhost;dbname=test')
	->addArgument('user')
	->addArgument('pass');
Flight::registerContainerHandler($container);

Flight::route('/user', [ 'UserController', 'view' ]);

Flight::start();
```

Ini mungkin sedikit lebih panjang daripada contoh Dice sebelumnya, tetapi tetap
menyelesaikan pekerjaan dengan keuntungan yang sama!

## Penangan DIC Kustom

Anda juga dapat membuat penangan DIC Anda sendiri. Ini berguna jika Anda memiliki kontainer kustom yang ingin Anda gunakan yang bukan PSR-11 (Dice). Lihat 
[contoh dasar](#basic-example) untuk cara melakukannya.

Selain itu, ada beberapa default yang berguna yang akan membuat hidup Anda lebih mudah saat menggunakan Flight.

### Instance Engine

Jika Anda menggunakan instance `Engine` di pengontrol/middleware Anda, inilah cara Anda mengkonfigurasinya:

```php

// Di suatu tempat di file bootstrap Anda
$engine = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// Ini adalah tempat Anda mengoper instance
		Engine::class => $engine
	]
]);

$engine->registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// Sekarang Anda dapat menggunakan instance Engine di pengontrol/middleware Anda

class MyController {
	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function index() {
		$this->app->render('index');
	}
}
```

### Menambahkan Kelas Lain

Jika Anda memiliki kelas lain yang ingin Anda tambahkan ke kontainer, dengan Dice sangat mudah karena mereka akan secara otomatis diselesaikan oleh kontainer. Berikut adalah contohnya:

```php

$container = new \Dice\Dice;
// Jika Anda tidak perlu menyuntikkan apa pun ke dalam kelas Anda
// Anda tidak perlu mendefinisikan apa pun!
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

class MyCustomClass {
	public function parseThing() {
		return 'thing';
	}
}

class UserController {

	protected MyCustomClass $MyCustomClass;

	public function __construct(MyCustomClass $MyCustomClass) {
		$this->MyCustomClass = $MyCustomClass;
	}

	public function index() {
		echo $this->MyCustomClass->parseThing();
	}
}

Flight::route('/user', 'UserController->index');
```