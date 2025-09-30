# Respons

## Ikhtisar

Flight membantu menghasilkan sebagian header respons untuk Anda, tetapi Anda memegang sebagian besar kendali atas apa yang Anda kirim kembali ke pengguna. Sebagian besar waktu Anda akan mengakses objek `response()` secara langsung, tetapi Flight memiliki beberapa metode pembantu untuk mengatur sebagian header respons untuk Anda.

## Pemahaman

Setelah pengguna mengirimkan [permintaan](/learn/requests) mereka ke aplikasi Anda, Anda perlu menghasilkan respons yang tepat untuk mereka. Mereka telah mengirimkan informasi seperti bahasa yang mereka sukai, apakah mereka dapat menangani jenis kompresi tertentu, agen pengguna mereka, dll. dan setelah memproses semuanya, saatnya mengirimkan respons yang tepat kembali kepada mereka. Ini bisa berupa pengaturan header, mengeluarkan isi HTML atau JSON untuk mereka, atau mengarahkan mereka ke halaman.

## Penggunaan Dasar

### Mengirimkan Isi Respons

Flight menggunakan `ob_start()` untuk membuffer output. Ini berarti Anda dapat menggunakan `echo` atau `print` untuk mengirimkan respons ke pengguna dan Flight akan menangkapnya serta mengirimkannya kembali ke pengguna dengan header yang sesuai.

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

Sebagai alternatif, Anda dapat memanggil metode `write()` untuk menambahkan ke isi juga.

```php
// Ini akan mengirim "Hello, World!" ke browser pengguna
Flight::route('/', function() {
	// verbose, tapi kadang-kadang diperlukan saat Anda membutuhkannya
	Flight::response()->write("Hello, World!");

	// jika Anda ingin mengambil isi yang telah Anda atur pada titik ini
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
pengguna tidak diotorisasi, Anda dapat mengirim respons JSON segera, membersihkan isi badan
yang ada dan menghentikan eksekusi.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Periksa apakah pengguna diotorisasi
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
		// tidak ada exit; diperlukan di sini.
	}

	// Lanjutkan dengan sisa rute
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

	// Lanjutkan dengan sisa rute
});
```

### Membersihkan Isi Respons

Jika Anda ingin membersihkan isi respons, Anda dapat menggunakan metode `clearBody`:

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

### Menjalankan Callback pada Isi Respons

Anda dapat menjalankan callback pada isi respons dengan menggunakan metode `addResponseBodyCallback`:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Ini akan mengompresi gzip semua respons untuk rute apa pun
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Anda dapat menambahkan beberapa callback dan mereka akan dijalankan dalam urutan mereka ditambahkan. Karena ini dapat menerima [callable](https://www.php.net/manual/en/language.types.callable.php) apa pun, ini dapat menerima array kelas `[ $class, 'method' ]`, closure `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, atau nama fungsi `'minify'` jika Anda memiliki fungsi untuk meminimalkan kode html Anda misalnya.

**Catatan:** Callback rute tidak akan berfungsi jika Anda menggunakan opsi konfigurasi `flight.v2.output_buffering`.

#### Callback Rute Spesifik

Jika Anda ingin ini hanya berlaku untuk rute spesifik, Anda dapat menambahkan callback di dalam rute itu sendiri:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Ini akan mengompresi gzip hanya respons untuk rute ini
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

#### Opsi Middleware

Anda juga dapat menggunakan [middleware](/learn/middleware) untuk menerapkan callback ke semua rute melalui middleware:

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
		// minimalkan isi dengan cara tertentu
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

### Pengalihan

Anda dapat mengalihkan permintaan saat ini dengan menggunakan metode `redirect()` dan meneruskan
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

### Menghentikan Eksekusi Rute

Anda dapat menghentikan framework dan langsung keluar pada titik mana pun dengan memanggil metode `halt`:

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

> **Catatan:** `Flight::stop()` memiliki perilaku aneh seperti itu akan mengeluarkan respons tetapi melanjutkan eksekusi skrip Anda yang mungkin bukan yang Anda inginkan. Anda dapat menggunakan `exit` atau `return` setelah memanggil `Flight::stop()` untuk mencegah eksekusi lebih lanjut, tetapi umumnya direkomendasikan untuk menggunakan `Flight::halt()`. 

Ini akan menyimpan kunci header dan nilai ke objek respons. Pada akhir siklus hidup permintaan
ini akan membangun header dan mengirim respons.

## Penggunaan Lanjutan

### Mengirim Header Segera

Mungkin ada saat-saat ketika Anda perlu melakukan sesuatu yang kustom dengan header dan Anda perlu mengirim header
pada baris kode yang sama yang sedang Anda kerjakan. Jika Anda mengatur [rute streaming](/learn/routing),
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

Jadi, saat melakukan permintaan GET menggunakan `?q=my_func`, Anda seharusnya menerima output:

```javascript
my_func({"id":123});
```

Jika Anda tidak meneruskan nama parameter query, itu akan default ke `jsonp`.

> **Catatan:** Jika Anda masih menggunakan permintaan JSONP pada tahun 2025 dan seterusnya, masuk ke chat dan beri tahu kami mengapa! Kami suka mendengar cerita pertempuran/horor yang bagus!

### Membersihkan Data Respons

Anda dapat membersihkan isi respons dan header dengan menggunakan metode `clear()`. Ini akan membersihkan
header apa pun yang ditugaskan ke respons, membersihkan isi respons, dan mengatur kode status ke `200`.

```php
Flight::response()->clear();
```

#### Membersihkan Hanya Isi Respons

Jika Anda hanya ingin membersihkan isi respons, Anda dapat menggunakan metode `clearBody()`:

```php
// Ini masih akan mempertahankan header apa pun yang diatur pada objek response().
Flight::response()->clearBody();
```

### Penyimpanan Cache HTTP

Flight menyediakan dukungan bawaan untuk penyimpanan cache tingkat HTTP. Jika kondisi caching
terpenuhi, Flight akan mengembalikan respons HTTP `304 Not Modified`. Lain kali klien
meminta sumber daya yang sama, mereka akan diminta untuk menggunakan versi cache lokal mereka.

#### Penyimpanan Cache Tingkat Rute

Jika Anda ingin menyimpan cache seluruh respons Anda, Anda dapat menggunakan metode `cache()` dan meneruskan waktu untuk cache.

```php

// Ini akan menyimpan cache respons selama 5 menit
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'This content will be cached.';
});

// Secara alternatif, Anda dapat menggunakan string yang akan Anda teruskan
// ke metode strtotime()
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'This content will be cached.';
});
```

### Last-Modified

Anda dapat menggunakan metode `lastModified` dan meneruskan timestamp UNIX untuk mengatur tanggal
dan waktu halaman terakhir dimodifikasi. Klien akan terus menggunakan cache mereka hingga
nilai terakhir dimodifikasi diubah.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'This content will be cached.';
});
```

### ETag

Penyimpanan cache `ETag` mirip dengan `Last-Modified`, kecuali Anda dapat menentukan id apa pun yang Anda
inginkan untuk sumber daya:

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
});
```

## Lihat Juga
- [Routing](/learn/routing) - Cara memetakan rute ke controller dan merender tampilan.
- [Requests](/learn/requests) - Memahami cara menangani permintaan masuk.
- [Middleware](/learn/middleware) - Menggunakan middleware dengan rute untuk autentikasi, logging, dll.
- [Mengapa Framework?](/learn/why-frameworks) - Memahami manfaat menggunakan framework seperti Flight.
- [Memperluas](/learn/extending) - Cara memperluas Flight dengan fungsionalitas Anda sendiri.

## Pemecahan Masalah
- Jika Anda mengalami masalah dengan pengalihan yang tidak berfungsi, pastikan Anda menambahkan `return;` ke metode tersebut.
- `stop()` dan `halt()` bukan hal yang sama. `halt()` akan menghentikan eksekusi segera, sementara `stop()` akan mengizinkan eksekusi berlanjut.

## Changelog
- v3.12.0 - Menambahkan metode pembantu downloadFile.
- v3.10.0 - Menambahkan `jsonHalt`.
- v1.0 - Rilis awal.