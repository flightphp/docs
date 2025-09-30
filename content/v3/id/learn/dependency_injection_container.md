# Wadah Injeksi Ketergantungan

## Gambaran Umum

Wadah Injeksi Ketergantungan (DIC) adalah peningkatan kuat yang memungkinkan Anda untuk mengelola
ketergantungan aplikasi Anda.

## Pemahaman

Injeksi Ketergantungan (DI) adalah konsep kunci dalam framework PHP modern dan digunakan
untuk mengelola instansiasi dan konfigurasi objek. Beberapa contoh pustaka DIC
adalah: [flightphp/container](https://github.com/flightphp/container), [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), 
[PHP-DI](http://php-di.org/), dan [league/container](https://container.thephpleague.com/).

Sebuah DIC adalah cara mewah untuk memungkinkan Anda membuat dan mengelola kelas Anda di
lokasi terpusat. Ini berguna ketika Anda perlu meneruskan objek yang sama ke
beberapa kelas (seperti controller atau middleware Anda misalnya).

## Penggunaan Dasar

Cara lama melakukan hal-hal mungkin terlihat seperti ini:
```php

require 'vendor/autoload.php';

// class to manage users from the database
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

// in your routes.php file

$db = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');

$UserController = new UserController($db);
Flight::route('/user/@id', [ $UserController, 'view' ]);
// other UserController routes...

Flight::start();
```

Anda dapat melihat dari kode di atas bahwa kami membuat objek `PDO` baru dan meneruskannya
ke kelas `UserController` kami. Ini baik untuk aplikasi kecil, tetapi saat aplikasi
Anda berkembang, Anda akan menemukan bahwa Anda membuat atau meneruskan objek `PDO` yang sama
di beberapa tempat. Inilah di mana DIC sangat berguna.

Berikut adalah contoh yang sama menggunakan DIC (menggunakan Dice):
```php

require 'vendor/autoload.php';

// same class as above. Nothing changed
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

// create a new container
$container = new \Dice\Dice;

// add a rule to tell the container how to create a PDO object
// don't forget to reassign it to itself like below!
$container = $container->addRule('PDO', [
	// shared means that the same object will be returned each time
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// This registers the container handler so Flight knows to use it.
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// now we can use the container to create our UserController
Flight::route('/user/@id', [ UserController::class, 'view' ]);

Flight::start();
```

Saya yakin Anda mungkin berpikir bahwa ada banyak kode tambahan yang ditambahkan ke contoh ini.
Sihirnya muncul ketika Anda memiliki controller lain yang membutuhkan objek `PDO`.

```php

// If all your controllers have a constructor that needs a PDO object
// each of the routes below will automatically have it injected!!!
Flight::route('/company/@id', [ CompanyController::class, 'view' ]);
Flight::route('/organization/@id', [ OrganizationController::class, 'view' ]);
Flight::route('/category/@id', [ CategoryController::class, 'view' ]);
Flight::route('/settings', [ SettingsController::class, 'view' ]);
```

Bonus tambahan dari memanfaatkan DIC adalah bahwa pengujian unit menjadi jauh lebih mudah. Anda bisa
membuat objek mock dan meneruskannya ke kelas Anda. Ini adalah manfaat besar ketika Anda menulis
tes untuk aplikasi Anda!

### Membuat Penangan DIC Terpusat

Anda dapat membuat penangan DIC terpusat di file layanan Anda dengan [memperluas](/learn/extending) aplikasi Anda. Berikut adalah contoh:

```php
// services.php

// create a new container
$container = new \Dice\Dice;
// don't forget to reassign it to itself like below!
$container = $container->addRule('PDO', [
	// shared means that the same object will be returned each time
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// now we can create a mappable method to create any object. 
Flight::map('make', function($class, $params = []) use ($container) {
	return $container->create($class, $params);
});

// This registers the container handler so Flight knows to use it for controllers/middleware
Flight::registerContainerHandler(function($class, $params) {
	Flight::make($class, $params);
});


// lets say we have the following sample class that takes a PDO object in the constructor
class EmailCron {
	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function send() {
		// code that sends an email
	}
}

// And finally you can create objects using dependency injection
$emailCron = Flight::make(EmailCron::class);
$emailCron->send();
```

### `flightphp/container`

Flight memiliki plugin yang menyediakan wadah sederhana yang sesuai dengan PSR-11 yang dapat Anda gunakan untuk menangani
injeksi ketergantungan Anda. Berikut adalah contoh cepat tentang cara menggunakannya:

```php

// index.php for example
require 'vendor/autoload.php';

use flight\Container;

$container = new Container;

$container->set(PDO::class, fn(): PDO => new PDO('sqlite::memory:'));

Flight::registerContainerHandler([$container, 'get']);

class TestController {
  private PDO $pdo;

  function __construct(PDO $pdo) {
    $this->pdo = $pdo;
  }

  function index() {
    var_dump($this->pdo);
	// will output this correctly!
  }
}

Flight::route('GET /', [TestController::class, 'index']);

Flight::start();
```

#### Penggunaan Lanjutan flightphp/container

Anda juga dapat menyelesaikan ketergantungan secara rekursif. Berikut adalah contoh:

```php
<?php

require 'vendor/autoload.php';

use flight\Container;

class User {}

interface UserRepository {
  function find(int $id): ?User;
}

class PdoUserRepository implements UserRepository {
  private PDO $pdo;

  function __construct(PDO $pdo) {
    $this->pdo = $pdo;
  }

  function find(int $id): ?User {
    // Implementation ...
    return null;
  }
}

$container = new Container;

$container->set(PDO::class, static fn(): PDO => new PDO('sqlite::memory:'));
$container->set(UserRepository::class, PdoUserRepository::class);

$userRepository = $container->get(UserRepository::class);
var_dump($userRepository);

/*
object(PdoUserRepository)#4 (1) {
  ["pdo":"PdoUserRepository":private]=>
  object(PDO)#3 (0) {
  }
}
 */
```

### DICE

Anda juga dapat membuat penangan DIC sendiri. Ini berguna jika Anda memiliki wadah kustom
yang ingin Anda gunakan yang bukan PSR-11 (Dice). Lihat bagian
[penggunaan dasar](#basic-usage) untuk cara melakukannya.

Selain itu, ada
beberapa default yang membantu yang akan memudahkan hidup Anda saat menggunakan Flight.

#### Instance Engine

Jika Anda menggunakan instance `Engine` di controller/middleware Anda, berikut
cara Anda mengonfigurasinya:

```php

// Somewhere in your bootstrap file
$engine = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// This is where you pass in the instance
		Engine::class => $engine
	]
]);

$engine->registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// Now you can use the Engine instance in your controllers/middleware

class MyController {
	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function index() {
		$this->app->render('index');
	}
}
```

#### Menambahkan Kelas Lain

Jika Anda memiliki kelas lain yang ingin Anda tambahkan ke wadah, dengan Dice ini mudah karena mereka akan diselesaikan secara otomatis oleh wadah. Berikut adalah contoh:

```php

$container = new \Dice\Dice;
// If you don't need to inject any dependencies into your classes
// you don't need to define anything!
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

### PSR-11

Flight juga dapat menggunakan wadah apa pun yang sesuai dengan PSR-11. Ini berarti bahwa Anda dapat menggunakan wadah apa pun
yang mengimplementasikan antarmuka PSR-11. Berikut adalah contoh menggunakan wadah
PSR-11 League:

```php

require 'vendor/autoload.php';

// same UserController class as above

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

Ini bisa sedikit lebih verbose daripada contoh Dice sebelumnya, itu masih
mendapatkan pekerjaan selesai dengan manfaat yang sama!

## Lihat Juga
- [Memperluas Flight](/learn/extending) - Pelajari bagaimana Anda dapat menambahkan injeksi ketergantungan ke kelas Anda sendiri dengan memperluas framework.
- [Konfigurasi](/learn/configuration) - Pelajari bagaimana mengonfigurasi Flight untuk aplikasi Anda.
- [Rute](/learn/routing) - Pelajari bagaimana mendefinisikan rute untuk aplikasi Anda dan bagaimana injeksi ketergantungan bekerja dengan controller.
- [Middleware](/learn/middleware) - Pelajari bagaimana membuat middleware untuk aplikasi Anda dan bagaimana injeksi ketergantungan bekerja dengan middleware.

## Pemecahan Masalah
- Jika Anda mengalami masalah dengan wadah Anda, pastikan bahwa Anda meneruskan nama kelas yang benar ke wadah.

## Changelog
- v3.7.0 - Ditambahkan kemampuan untuk mendaftarkan penangan DIC ke Flight.