# Respons

Flight membantu menghasilkan bagian dari header respons untuk Anda, tetapi Anda memegang sebagian besar kendali atas apa yang Anda kirim kembali ke pengguna. Terkadang Anda dapat mengakses objek `Response` secara langsung, tetapi sebagian besar waktu Anda akan menggunakan instance `Flight` untuk mengirim respons.

## Mengirim Respons Dasar

Flight menggunakan ob_start() untuk mengbuffer keluaran. Ini berarti Anda dapat menggunakan `echo` atau `print` untuk mengirim respons ke pengguna dan Flight akan menangkapnya serta mengirimkannya kembali ke pengguna dengan header yang sesuai.

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

Sebagai alternatif, Anda dapat memanggil metode `write()` untuk menambahkan ke badan respons juga.

```php
// Ini akan mengirim "Hello, World!" ke browser pengguna
Flight::route('/', function() {
	// verbose, tetapi berguna kadang-kadang ketika Anda membutuhkannya
	Flight::response()->write("Hello, World!");

	// jika Anda ingin mengambil badan yang telah disetel pada titik ini
	// Anda dapat melakukannya seperti ini
	$body = Flight::response()->getBody();
});
```

## Kode Status

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

## Mengatur Badan Respons

Anda dapat mengatur badan respons dengan menggunakan metode `write`, namun jika Anda echo atau print apa pun, 
itu akan ditangkap dan dikirim sebagai badan respons melalui output buffering.

```php
Flight::route('/', function() {
	Flight::response()->write("Hello, World!");
});

// sama seperti

Flight::route('/', function() {
	echo "Hello, World!";
});
```

### Menghapus Badan Respons

Jika Anda ingin menghapus badan respons, Anda dapat menggunakan metode `clearBody`:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### Menjalankan Callback pada Badan Respons

Anda dapat menjalankan callback pada badan respons dengan menggunakan metode `addResponseBodyCallback`:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Ini akan mengompresi semua respons untuk rute apa pun
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Anda dapat menambahkan beberapa callback dan它们 akan dijalankan dalam urutan mereka ditambahkan. Karena ini dapat menerima [callable](https://www.php.net/manual/en/language.types.callable.php) apa pun, itu dapat menerima array kelas `[ $class, 'method' ]`, closure `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, atau nama fungsi `'minify'` jika Anda memiliki fungsi untuk meminimalkan kode HTML Anda sebagai contoh.

**Catatan:** Callback rute tidak akan berfungsi jika Anda menggunakan opsi konfigurasi `flight.v2.output_buffering`.

### Callback untuk Rute Spesifik

Jika Anda ingin ini hanya berlaku untuk rute spesifik, Anda dapat menambahkan callback dalam rute itu sendiri:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Ini akan mengompresi hanya respons untuk rute ini
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### Opsi Middleware

Anda juga dapat menggunakan middleware untuk menerapkan callback ke semua rute melalui middleware:

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
		// minimalkan badan dengan cara tertentu
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

## Mengatur Header Respons

Anda dapat mengatur header seperti tipe konten respons dengan menggunakan metode `header`:

```php
// Ini akan mengirim "Hello, World!" ke browser pengguna dalam teks polos
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// atau
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight menyediakan dukungan untuk mengirim respons JSON dan JSONP. Untuk mengirim respons JSON, Anda 
melewatkan beberapa data untuk dikodekan JSON:

```php
Flight::json(['id' => 123]);
```

> **Catatan:** Secara default, Flight akan mengirim header `Content-Type: application/json` dengan respons. Itu juga akan menggunakan konstanta `JSON_THROW_ON_ERROR` dan `JSON_UNESCAPED_SLASHES` saat mengkodekan JSON.

### JSON dengan Kode Status

Anda juga dapat melewatkan kode status sebagai argumen kedua:

```php
Flight::json(['id' => 123], 201);
```

### JSON dengan Pretty Print

Anda juga dapat melewatkan argumen ke posisi terakhir untuk mengaktifkan pretty printing:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Jika Anda mengubah opsi yang dilewatkan ke `Flight::json()` dan ingin sintaks yang lebih sederhana, Anda dapat 
hanya remap metode JSON:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// Dan sekarang dapat digunakan seperti ini
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON dan Menghentikan Eksekusi (v3.10.0)

Jika Anda ingin mengirim respons JSON dan menghentikan eksekusi, Anda dapat menggunakan metode `jsonHalt()`.
Ini berguna untuk kasus di mana Anda memeriksa mungkin beberapa jenis otorisasi dan jika 
pengguna tidak diotorisasi, Anda dapat mengirim respons JSON segera, menghapus konten badan yang ada
dan menghentikan eksekusi.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Periksa jika pengguna diotorisasi
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
	}

	// Lanjutkan dengan sisanya dari rute
});
```

Sebelum v3.10.0, Anda harus melakukan sesuatu seperti ini:

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Periksa jika pengguna diotorisasi
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// Lanjutkan dengan sisanya dari rute
});
```

### JSONP

Untuk permintaan JSONP, Anda dapat secara opsional melewatkan nama parameter query yang Anda gunakan
untuk mendefinisikan fungsi callback:

```php
Flight::jsonp(['id' => 123], 'q');
```

Jadi, saat membuat permintaan GET menggunakan `?q=my_func`, Anda harus menerima keluaran:

```javascript
my_func({"id":123});
```

Jika Anda tidak melewatkan nama parameter query, itu akan default ke `jsonp`.

## Mengarahkan ke URL Lain

Anda dapat mengarahkan permintaan saat ini dengan menggunakan metode `redirect()` dan melewatkan
URL baru:

```php
Flight::redirect('/new/location');
```

Secara default, Flight mengirim kode status HTTP 303 ("See Other"). Anda dapat secara opsional mengatur kode
kustom:

```php
Flight::redirect('/new/location', 401);
```

## Menghentikan

Anda dapat menghentikan framework kapan saja dengan memanggil metode `halt`:

```php
Flight::halt();
```

Anda juga dapat menentukan kode status `HTTP` opsional dan pesan:

```php
Flight::halt(200, 'Be right back...');
```

Memanggil `halt` akan membuang konten respons apa pun hingga saat itu. Jika Anda ingin menghentikan
framework dan mengeluarkan respons saat ini, gunakan metode `stop`:

```php
Flight::stop($httpStatusCode = null);
```

> **Catatan:** `Flight::stop()` memiliki beberapa perilaku aneh seperti itu akan mengeluarkan respons tetapi melanjutkan mengeksekusi skrip Anda. Anda dapat menggunakan `exit` atau `return` setelah memanggil `Flight::stop()` untuk mencegah eksekusi lebih lanjut, tetapi umumnya disarankan untuk menggunakan `Flight::halt()`. 

## Menghapus Data Respons

Anda dapat menghapus badan respons dan header dengan menggunakan metode `clear()`. Ini akan menghapus
header apa pun yang ditugaskan ke respons, menghapus badan respons, dan mengatur kode status ke `200`.

```php
Flight::response()->clear();
```

### Menghapus Hanya Badan Respons

Jika Anda hanya ingin menghapus badan respons, Anda dapat menggunakan metode `clearBody()`:

```php
// Ini akan tetap menjaga header apa pun yang disetel pada objek response().
Flight::response()->clearBody();
```

## Penyimpanan Cache HTTP

Flight menyediakan dukungan bawaan untuk penyimpanan cache tingkat HTTP. Jika kondisi caching
terpenuhi, Flight akan mengembalikan respons HTTP `304 Not Modified`. Kali berikutnya klien 
meminta sumber daya yang sama, mereka akan diminta untuk menggunakan versi cache lokal mereka.

### Penyimpanan Cache pada Level Rute

Jika Anda ingin menyimpan cache seluruh respons, Anda dapat menggunakan metode `cache()` dan melewatkan waktu untuk menyimpan cache.

```php
// Ini akan menyimpan cache respons selama 5 menit
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'This content will be cached.';
});

// Atau, Anda dapat menggunakan string yang akan Anda lewatkan
// ke metode strtotime()
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'This content will be cached.';
});
```

### Last-Modified

Anda dapat menggunakan metode `lastModified` dan melewatkan stempel waktu UNIX untuk mengatur tanggal
dan waktu halaman terakhir dimodifikasi. Klien akan terus menggunakan cache mereka hingga
nilai terakhir dimodifikasi berubah.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'This content will be cached.';
});
```

### ETag

Penayangan cache `ETag` mirip dengan `Last-Modified`, kecuali Anda dapat menentukan ID apa pun yang Anda
inginkan untuk sumber daya:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'This content will be cached.';
});
```

Perlu diingat bahwa memanggil baik `lastModified` atau `etag` akan mengatur dan memeriksa
nilai cache. Jika nilai cache sama antara permintaan, Flight akan segera
mengirim respons `HTTP 304` dan menghentikan pemrosesan.

## Mengunduh File (v3.12.0)

Ada metode bantu untuk mengunduh file. Anda dapat menggunakan metode `download` dan melewatkan path.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```