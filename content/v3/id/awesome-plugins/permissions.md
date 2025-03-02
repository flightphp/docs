# FlightPHP/Permissions

Ini adalah modul izin yang dapat digunakan dalam proyek Anda jika Anda memiliki beberapa peran di aplikasi Anda dan setiap peran memiliki sedikit fungsi yang berbeda. Modul ini memungkinkan Anda untuk mendefinisikan izin untuk setiap peran dan kemudian memeriksa apakah pengguna saat ini memiliki izin untuk mengakses halaman tertentu atau melakukan tindakan tertentu.

Klik [di sini](https://github.com/flightphp/permissions) untuk repositori di GitHub.

Instalasi
-------
Jalankan `composer require flightphp/permissions` dan Anda sudah siap!

Penggunaan
-------
Pertama, Anda perlu mengatur izin Anda, lalu Anda memberi tahu aplikasi Anda apa arti izin tersebut. Pada akhirnya, Anda akan memeriksa izin Anda dengan `$Permissions->has()`, `->can()`, atau `is()`. `has()` dan `can()` memiliki fungsionalitas yang sama, tetapi dinamai berbeda untuk membuat kode Anda lebih mudah dibaca.

## Contoh Dasar

Mari kita anggap Anda memiliki fitur dalam aplikasi Anda yang memeriksa apakah seorang pengguna sudah masuk. Anda dapat membuat objek izin seperti ini:

```php
// index.php
require 'vendor/autoload.php';

// beberapa kode

// lalu Anda mungkin memiliki sesuatu yang memberi tahu Anda siapa peran saat ini dari orang tersebut
// kemungkinan Anda memiliki sesuatu di mana Anda mengambil peran saat ini
// dari variabel sesi yang mendefinisikan ini
// setelah seseorang masuk, jika tidak, mereka akan memiliki peran 'tamu' atau 'publik'.
$current_role = 'admin';

// mengatur izin
$permission = new \flight\Permission($current_role);
$permission->defineRule('loggedIn', function($current_role) {
	return $current_role !== 'guest';
});

// Anda mungkin ingin menyimpan objek ini di Flight di suatu tempat
Flight::set('permission', $permission);
```

Kemudian di dalam controller di suatu tempat, Anda mungkin memiliki sesuatu seperti ini.

```php
<?php

// beberapa controller
class SomeController {
	public function someAction() {
		$permission = Flight::get('permission');
		if ($permission->has('loggedIn')) {
			// lakukan sesuatu
		} else {
			// lakukan sesuatu yang lain
		}
	}
}
```

Anda juga dapat menggunakan ini untuk melacak apakah mereka memiliki izin untuk melakukan sesuatu dalam aplikasi Anda.
Sebagai contoh, jika Anda memiliki cara bagi pengguna untuk berinteraksi dengan posting di perangkat lunak Anda, Anda dapat 
memeriksa apakah mereka memiliki izin untuk melakukan tindakan tertentu.

```php
$current_role = 'admin';

// mengatur izin
$permission = new \flight\Permission($current_role);
$permission->defineRule('post', function($current_role) {
	if($current_role === 'admin') {
		$permissions = ['create', 'read', 'update', 'delete'];
	} else if($current_role === 'editor') {
		$permissions = ['create', 'read', 'update'];
	} else if($current_role === 'author') {
		$permissions = ['create', 'read'];
	} else if($current_role === 'contributor') {
		$permissions = ['create'];
	} else {
		$permissions = [];
	}
	return $permissions;
});
Flight::set('permission', $permission);
```

Kemudian di dalam controller di suatu tempat...

```php
class PostController {
	public function create() {
		$permission = Flight::get('permission');
		if ($permission->can('post.create')) {
			// lakukan sesuatu
		} else {
			// lakukan sesuatu yang lain
		}
	}
}
```

## Menginjeksi ketergantungan
Anda dapat menginjeksi ketergantungan ke dalam closure yang mendefinisikan izin. Ini berguna jika Anda memiliki semacam toggle, id, atau titik data lain yang ingin Anda periksa. Hal ini juga berlaku untuk panggilan jenis Class->Method, kecuali Anda mendefinisikan argumen dalam metode tersebut.

### Closure

```php
$Permission->defineRule('order', function(string $current_role, MyDependency $MyDependency = null) {
	// ... kode
});

// di file controller Anda
public function createOrder() {
	$MyDependency = Flight::myDependency();
	$permission = Flight::get('permission');
	if ($permission->can('order.create', $MyDependency)) {
		// lakukan sesuatu
	} else {
		// lakukan sesuatu yang lain
	}
}
```

### Kelas

```php
namespace MyApp;

class Permissions {

	public function order(string $current_role, MyDependency $MyDependency = null) {
		// ... kode
	}
}
```

## Pintasan untuk mengatur izin dengan kelas
Anda juga dapat menggunakan kelas untuk mendefinisikan izin Anda. Ini berguna jika Anda memiliki banyak izin dan Anda ingin menjaga kode Anda tetap bersih. Anda bisa melakukan sesuatu seperti ini:
```php
<?php

// kode bootstrap
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRule('order', 'MyApp\Permissions->order');

// myapp/Permissions.php
namespace MyApp;

class Permissions {

	public function order(string $current_role, int $user_id) {
		// Mengasumsikan Anda telah mengatur ini sebelumnya
		/** @var \flight\database\PdoWrapper $db */
		$db = Flight::db();
		$allowed_permissions = [ 'read' ]; // semua orang dapat melihat sebuah pesanan
		if($current_role === 'manager') {
			$allowed_permissions[] = 'create'; // manajer dapat membuat pesanan
		}
		$some_special_toggle_from_db = $db->fetchField('SELECT some_special_toggle FROM settings WHERE id = ?', [ $user_id ]);
		if($some_special_toggle_from_db) {
			$allowed_permissions[] = 'update'; // jika pengguna memiliki toggle khusus, mereka dapat memperbarui pesanan
		}
		if($current_role === 'admin') {
			$allowed_permissions[] = 'delete'; // admin dapat menghapus pesanan
		}
		return $allowed_permissions;
	}
}
```
Bagian yang keren adalah bahwa ada juga pintasan yang dapat Anda gunakan (yang juga dapat dicache!!!) di mana Anda cukup memberi tahu kelas izin untuk memetakan semua metode dalam sebuah kelas ke dalam izin. Jadi jika Anda memiliki metode bernama `order()` dan metode bernama `company()`, ini akan secara otomatis dipetakan sehingga Anda cukup menjalankan `$Permissions->has('order.read')` atau `$Permissions->has('company.read')` dan itu akan berhasil. Mendefinisikan ini sangat sulit, jadi ikutlah bersama saya di sini. Anda hanya perlu melakukan ini:

Buat kelas izin yang ingin Anda kelompokkan bersama.
```php
class MyPermissions {
	public function order(string $current_role, int $order_id = 0): array {
		// kode untuk menentukan izin
		return $permissions_array;
	}

	public function company(string $current_role, int $company_id): array {
		// kode untuk menentukan izin
		return $permissions_array;
	}
}
```

Kemudian buat izin tersebut dapat ditemukan menggunakan pustaka ini.

```php
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class);
Flight::set('permissions', $Permissions);
```

Akhirnya, panggil izin di basis kode Anda untuk memeriksa apakah pengguna diizinkan untuk melakukan izin tertentu.

```php
class SomeController {
	public function createOrder() {
		if(Flight::get('permissions')->can('order.create') === false) {
			die('Anda tidak bisa membuat sebuah pesanan. Maaf!');
		}
	}
}
```

### Cache

Untuk mengaktifkan caching, lihat pustaka sederhana [wruczak/phpfilecache](https://docs.flightphp.com/awesome-plugins/php-file-cache). Contoh untuk mengaktifkannya ada di bawah ini.
```php

// $app ini bisa menjadi bagian dari kode Anda, atau
// Anda bisa langsung meneruskan null dan itu akan
// mengambil dari Flight::app() di dalam konstruktor
$app = Flight::app();

// Untuk saat ini, ini menerima sebagai cache file. Lainnya dapat dengan mudah
// ditambahkan di masa depan. 
$Cache = new Wruczek\PhpFileCache\PhpFileCache;

$Permissions = new \flight\Permission($current_role, $app, $Cache);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class, 3600); // 3600 adalah berapa detik untuk menyimpan cache ini. Biarkan ini kosong untuk tidak menggunakan caching
```

Dan Anda sudah siap!