# Respons

## Gambaran Umum

Flight membantu menghasilkan sebagian header respons untuk Anda, tetapi Anda memegang sebagian besar kendali atas apa yang Anda kirim kembali ke pengguna. Sebagian besar waktu Anda akan mengakses objek `response()` secara langsung, tetapi Flight memiliki beberapa metode pembantu untuk mengatur beberapa header respons untuk Anda.

## Pemahaman

Setelah pengguna mengirimkan [permintaan](/learn/requests) mereka ke aplikasi Anda, Anda perlu menghasilkan respons yang tepat untuk mereka. Mereka telah mengirimkan informasi seperti bahasa yang mereka sukai, apakah mereka dapat menangani jenis kompresi tertentu, agen pengguna mereka, dll., dan setelah memproses semuanya, saatnya mengirimkan respons yang tepat kembali kepada mereka. Ini bisa berupa pengaturan header, mengeluarkan body HTML atau JSON untuk mereka, atau mengarahkan mereka ke halaman.

## Penggunaan Dasar

### Mengirim Body Respons

Flight menggunakan `ob_start()` untuk membuffer output. Ini berarti Anda dapat menggunakan `echo` atau `print` untuk mengirim respons ke pengguna dan Flight akan menangkapnya dan mengirimkannya kembali ke pengguna dengan header yang sesuai.

```php
// Ini akan mengirim "Hello, World!" ke browser pengguna
Flight::route('/', function() {
	echo "Hello, World!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hello, World!
```

Sebagai alternatif, Anda dapat memanggil metode `write()` untuk menambahkan ke body juga.

```php
// Ini akan mengirim "Hello, World!" ke browser pengguna
Flight::route('/', function() {
	// verbose, tapi kadang-kadang diperlukan saat Anda membutuhkannya
	Flight::response()->write("Hello, World!");

	// jika Anda ingin mengambil body yang telah Anda atur pada titik ini
	// Anda bisa melakukannya seperti ini
	$body = Flight::response()->getBody();
});
```

### JSON

Flight menyediakan dukungan untuk mengirim respons JSON dan JSONP. Untuk mengirim respons JSON, Anda
meneruskan beberapa data yang akan dikodekan JSON:

```php
Flight::route('/@companyId/users', function(int $companyId) {
	// entah bagaimana ambil pengguna Anda dari database misalnya
	$users = Flight::db()->fetchAll("SELECT id, first_name, last_name FROM users WHERE company_id = ?", [ $companyId ]);

	Flight::json($users);
});
// [{"id":1,"first_name":"Bob","last_name":"Jones"}, /* more users */ ]
```

> **Catatan:** Secara default, Flight akan mengirim header `Content-Type: application/json` dengan respons. Ini juga akan menggunakan flag `JSON_THROW_ON_ERROR` dan `JSON_UNESCAPED_SLASHES` saat mengkodekan JSON.

#### JSON dengan Kode Status

Anda juga dapat meneruskan kode status sebagai argumen kedua:

```php
Flight::json(['id' => 123], 201);
```

#### JSON dengan Pretty Print

Anda juga dapat meneruskan argumen ke posisi terakhir untuk mengaktifkan pretty printing:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

#### Mengubah Urutan Argumen JSON

`Flight::json()` adalah metode yang sangat lama, tetapi tujuan Flight adalah mempertahankan kompatibilitas mundur
untuk proyek. Sebenarnya sangat sederhana jika Anda ingin mengubah urutan argumen untuk menggunakan sintaks yang lebih sederhana,
Anda hanya perlu memetakan ulang metode JSON [seperti metode Flight lainnya](/learn/extending):

```php
Flight::map('json', function($data, $code = 200, $options = 0) {

	// sekarang Anda tidak perlu `true, 'utf-8'` saat menggunakan metode json()!
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// Dan sekarang bisa digunakan seperti ini
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

#### JSON dan Menghentikan Eksekusi

_v3.10.0_

Jika Anda ingin mengirim respons JSON dan menghentikan eksekusi, Anda dapat menggunakan metode `jsonHalt()`.
Ini berguna untuk kasus di mana Anda memeriksa mungkin jenis otorisasi tertentu dan jika
pengguna tidak diotorisasi, Anda dapat mengirim respons JSON segera, membersihkan konten body
yang ada dan menghentikan eksekusi.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Periksa apakah pengguna diotorisasi
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
		// no exit; needed here.
	}

	// Lanjutkan dengan sisa route
});
```

Sebelum v3.10.0, Anda harus melakukan sesuatu seperti ini:

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Periksa apakah pengguna diotorisasi
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// Lanjutkan dengan sisa route
});
```

### Membersihkan Body Respons

Jika Anda ingin membersihkan body respons, Anda dapat menggunakan metode `clearBody`:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

Kasus penggunaan di atas mungkin tidak umum, namun bisa lebih umum jika ini digunakan dalam [middleware](/learn/middleware).

### Menjalankan Callback pada Body Respons

Anda dapat menjalankan callback pada body respons dengan menggunakan metode `addResponseBodyCallback`:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Ini akan mengompresi gzip semua respons untuk route apa pun
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Anda dapat menambahkan beberapa callback dan mereka akan dijalankan dalam urutan yang ditambahkan. Karena ini dapat menerima [callable](https://www.php.net/manual/en/language.types.callable.php) apa pun, ini dapat menerima array kelas `[ $class, 'method' ]`, closure `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, atau nama fungsi `'minify'` jika Anda memiliki fungsi untuk meminify kode html Anda misalnya.

**Catatan:** Callback route tidak akan bekerja jika Anda menggunakan opsi konfigurasi `flight.v2.output_buffering`.

#### Callback Route Spesifik

Jika Anda ingin ini hanya berlaku untuk route spesifik, Anda dapat menambahkan callback di route itu sendiri:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Ini akan mengompresi gzip hanya respons untuk route ini
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

#### Opsi Middleware

Anda juga dapat menggunakan [middleware](/learn/middleware) untuk menerapkan callback ke semua route melalui middleware:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// Terapkan callback di sini pada objek response().
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// minify body entah bagaimana
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

### Kode Status

Anda dapat mengatur kode status respons dengan menggunakan metode `status`:

```php
Flight::route('/@id', function($id) {
	if($id == 123) {
		Flight::response()->status(200);
		echo "Hello, World!";
	} else {
		Flight::response()->status(403);
		echo "Forbidden";
	}
});
```

Jika Anda ingin mendapatkan kode status saat ini, Anda dapat menggunakan metode `status` tanpa argumen apa pun:

```php
Flight::response()->status(); // 200
```

### Mengatur Header Respons

Anda dapat mengatur header seperti tipe konten respons dengan menggunakan metode `header`:

```php
// Ini akan mengirim "Hello, World!" ke browser pengguna dalam teks biasa
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// atau
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

### Redirect

Anda dapat mengarahkan ulang permintaan saat ini dengan menggunakan metode `redirect()` dan meneruskan
URL baru:

```php
Flight::route('/login', function() {
	$username = Flight::request()->data->username;
	$password = Flight::request()->data->password;
	$passwordConfirm = Flight::request()->data->password_confirm;

	if($password !== $passwordConfirm) {
		Flight::redirect('/new/location');
		return; // ini diperlukan agar fungsionalitas di bawah tidak dieksekusi
	}

	// tambahkan pengguna baru...
	Flight::db()->runQuery("INSERT INTO users ....");
	Flight::redirect('/admin/dashboard');
});
```

> **Catatan:** Secara default Flight mengirim kode status HTTP 303 ("See Other"). Anda dapat secara opsional mengatur kode
kustom:

```php
Flight::redirect('/new/location', 301); // permanen
```

### Menghentikan Eksekusi Route

Anda dapat menghentikan framework dan segera keluar pada titik mana pun dengan memanggil metode `halt`:

```php
Flight::halt();
```

Anda juga dapat menentukan kode status `HTTP` dan pesan opsional:

```php
Flight::halt(200, 'Be right back...');
```

Memanggil `halt` akan membuang konten respons apa pun hingga titik itu dan menghentikan semua eksekusi. 
Jika Anda ingin menghentikan framework dan mengeluarkan respons saat ini, gunakan metode `stop`:

```php
Flight::stop($httpStatusCode = null);
```

> **Catatan:** `Flight::stop()` memiliki perilaku aneh seperti itu akan mengeluarkan respons tetapi melanjutkan eksekusi skrip Anda yang mungkin bukan yang Anda inginkan. Anda dapat menggunakan `exit` atau `return` setelah memanggil `Flight::stop()` untuk mencegah eksekusi lebih lanjut, tetapi umumnya disarankan untuk menggunakan `Flight::halt()`. 

Ini akan menyimpan kunci dan nilai header ke objek respons. Pada akhir siklus hidup permintaan
ini akan membangun header dan mengirim respons.

## Penggunaan Lanjutan

### Mengirim Header Segera

Mungkin ada saat-saat ketika Anda perlu melakukan sesuatu yang kustom dengan header dan Anda perlu mengirim header
pada baris kode yang sama yang Anda kerjakan. Jika Anda mengatur [route yang di-stream](/learn/routing),
ini yang Anda butuhkan. Itu dapat dicapai melalui `response()->setRealHeader()`.

```php
Flight::route('/', function() {
	Flight::response()->setRealHeader('Content-Type: text/plain');
	echo 'Streaming response...';
	sleep(5);
	echo 'Done!';
})->stream();
```

### JSONP

Untuk permintaan JSONP, Anda dapat secara opsional meneruskan nama parameter query yang Anda
gunakan untuk mendefinisikan fungsi callback Anda:

```php
Flight::jsonp(['id' => 123], 'q');
```

Jadi, saat membuat permintaan GET menggunakan `?q=my_func`, Anda seharusnya menerima output:

```javascript
my_func({"id":123});
```

Jika Anda tidak meneruskan nama parameter query, itu akan default ke `jsonp`.

> **Catatan:** Jika Anda masih menggunakan permintaan JSONP pada 2025 dan seterusnya, lompat ke chat dan beri tahu kami mengapa! Kami suka mendengar cerita pertempuran/horor yang bagus!

### Membersihkan Data Respons

Anda dapat membersihkan body respons dan header dengan menggunakan metode `clear()`. Ini akan membersihkan
header apa pun yang ditetapkan ke respons, membersihkan body respons, dan mengatur kode status ke `200`.

```php
Flight::response()->clear();
```

#### Membersihkan Hanya Body Respons

Jika Anda hanya ingin membersihkan body respons, Anda dapat menggunakan metode `clearBody()`:

```php
// Ini masih akan mempertahankan header apa pun yang diatur pada objek response().
Flight::response()->clearBody();
```

### Penyimpanan Cache HTTP

Flight menyediakan dukungan bawaan untuk caching tingkat HTTP. Jika kondisi caching
terpenuhi, Flight akan mengembalikan respons HTTP `304 Not Modified`. Saat berikutnya klien
meminta sumber daya yang sama, mereka akan diminta untuk menggunakan versi cache lokal mereka.

#### Caching Tingkat Route

Jika Anda ingin menyimpan cache seluruh respons Anda, Anda dapat menggunakan metode `cache()` dan meneruskan waktu untuk cache.

```php

// Ini akan menyimpan cache respons selama 5 menit
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'This content will be cached.';
});

// Alternatifnya, Anda dapat menggunakan string yang akan Anda teruskan
// ke metode strtotime()
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'This content will be cached.';
});
```

### Last-Modified

Anda dapat menggunakan metode `lastModified` dan meneruskan timestamp UNIX untuk mengatur tanggal
dan waktu halaman terakhir dimodifikasi. Klien akan terus menggunakan cache mereka hingga
nilai last modified berubah.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'This content will be cached.';
});
```

### ETag

Caching `ETag` mirip dengan `Last-Modified`, kecuali Anda dapat menentukan id apa pun yang
Anda inginkan untuk sumber daya:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'This content will be cached.';
});
```

Ingatlah bahwa memanggil `lastModified` atau `etag` akan sama-sama mengatur dan memeriksa
nilai cache. Jika nilai cache sama antara permintaan, Flight akan segera
mengirim respons `HTTP 304` dan menghentikan pemrosesan.

### Mengunduh File

_v3.12.0_

Ada metode pembantu untuk streaming file ke pengguna akhir. Anda dapat menggunakan metode `download` dan meneruskan path.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
  // Mulai v3.17.1 Anda dapat menentukan nama file kustom untuk unduhan
  Flight::download('/path/to/file.txt', 'custom_name.txt');
});
```

## Lihat Juga
- [Routing](/learn/routing) - Cara memetakan route ke controller dan merender view.
- [Requests](/learn/requests) - Memahami cara menangani permintaan masuk.
- [Middleware](/learn/middleware) - Menggunakan middleware dengan route untuk autentikasi, logging, dll.
- [Mengapa Framework?](/learn/why-frameworks) - Memahami manfaat menggunakan framework seperti Flight.
- [Extending](/learn/extending) - Cara memperluas Flight dengan fungsionalitas Anda sendiri.

## Pemecahan Masalah
- Jika Anda mengalami masalah dengan redirect yang tidak bekerja, pastikan Anda menambahkan `return;` ke metode.
- `stop()` dan `halt()` bukan hal yang sama. `halt()` akan menghentikan eksekusi segera, sementara `stop()` akan memungkinkan eksekusi berlanjut.

## Changelog
- v3.17.1 - Menambahkan `$fileName` ke metode `downloadFile()`.
- v3.12.0 - Menambahkan metode pembantu downloadFile.
- v3.10.0 - Menambahkan `jsonHalt`.
- v1.0 - Rilis awal.