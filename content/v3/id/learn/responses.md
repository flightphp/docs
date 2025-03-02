# Respons

Flight membantu menghasilkan sebagian header respons untuk Anda, tetapi Anda memiliki sebagian besar kontrol atas apa yang Anda kirim kembali kepada pengguna. Terkadang Anda dapat mengakses objek `Response` secara langsung, tetapi sebagian besar waktu Anda akan menggunakan instance `Flight` untuk mengirim respons.

## Mengirim Respons Dasar

Flight menggunakan ob_start() untuk menampung output. Ini berarti Anda dapat menggunakan `echo` atau `print` untuk mengirim respons kepada pengguna dan Flight akan menangkapnya dan mengirimkannya kembali kepada pengguna dengan header yang sesuai.

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

Sebagai alternatif, Anda dapat memanggil metode `write()` untuk menambahkan ke tubuh juga.

```php

// Ini akan mengirim "Hello, World!" ke browser pengguna
Flight::route('/', function() {
	// verbose, tetapi berhasil menyelesaikan pekerjaan kadang-kadang saat Anda membutuhkannya
	Flight::response()->write("Hello, World!");

	// jika Anda ingin mengambil tubuh yang telah Anda atur pada titik ini
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
		echo "Dilarang";
	}
});
```

Jika Anda ingin mendapatkan kode status saat ini, Anda dapat menggunakan metode `status` tanpa argumen:

```php
Flight::response()->status(); // 200
```

## Mengatur Tubuh Respons

Anda dapat mengatur tubuh respons dengan menggunakan metode `write`, namun, jika Anda echo atau print sesuatu, 
itu akan ditangkap dan dikirim sebagai tubuh respons melalui penampungan output.

```php
Flight::route('/', function() {
	Flight::response()->write("Hello, World!");
});

// sama dengan

Flight::route('/', function() {
	echo "Hello, World!";
});
```

### Menghapus Tubuh Respons

Jika Anda ingin menghapus tubuh respons, Anda dapat menggunakan metode `clearBody`:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### Menjalankan Callback pada Tubuh Respons

Anda dapat menjalankan callback pada tubuh respons dengan menggunakan metode `addResponseBodyCallback`:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Ini akan gzip semua respons untuk rute mana pun
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Anda dapat menambahkan beberapa callback dan mereka akan dijalankan dalam urutan mereka ditambahkan. Karena ini dapat menerima [callable](https://www.php.net/manual/en/language.types.callable.php), itu dapat menerima array kelas `[ $class, 'method' ]`, closure `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, atau nama fungsi `'minify'` jika Anda memiliki fungsi untuk meminify kode html Anda misalnya.

**Catatan:** Callback rute tidak akan berfungsi jika Anda menggunakan opsi konfigurasi `flight.v2.output_buffering`.

### Callback Rute Khusus

Jika Anda ingin ini hanya berlaku untuk rute tertentu, Anda dapat menambahkan callback di dalam rute itu sendiri:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Ini akan gzip hanya respons untuk rute ini
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
		// meminify tubuh entah bagaimana
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

Anda dapat mengatur header seperti jenis konten dari respons dengan menggunakan metode `header`:

```php

// Ini akan mengirim "Hello, World!" ke browser pengguna dalam teks biasa
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// atau
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight menyediakan dukungan untuk mengirim respons JSON dan JSONP. Untuk mengirim respons JSON Anda
mengirimkan beberapa data untuk di-JSON-kan:

```php
Flight::json(['id' => 123]);
```

> **Catatan:** Secara default, Flight akan mengirimkan header `Content-Type: application/json` bersamaan dengan respons. Ini juga akan menggunakan konstanta `JSON_THROW_ON_ERROR` dan `JSON_UNESCAPED_SLASHES` saat mengkodekan JSON.

### JSON dengan Kode Status

Anda juga dapat memasukkan kode status sebagai argumen kedua:

```php
Flight::json(['id' => 123], 201);
```

### JSON dengan Pretty Print

Anda juga dapat memasukkan argumen di posisi terakhir untuk mengaktifkan pencetakan yang indah:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Jika Anda mengubah opsi yang diteruskan ke `Flight::json()` dan ingin sintaks yang lebih sederhana, Anda dapat 
mengganti metode JSON:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// Dan sekarang bisa digunakan seperti ini
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON dan Hentikan Eksekusi (v3.10.0)

Jika Anda ingin mengirim respons JSON dan menghentikan eksekusi, Anda dapat menggunakan metode `jsonHalt`.
Ini berguna untuk kasus di mana Anda memeriksa mungkin beberapa jenis otorisasi dan jika
pengguna tidak diotorisasi, Anda bisa segera mengirim respons JSON, membersihkan konten tubuh yang ada
dan menghentikan eksekusi.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Periksa apakah pengguna diotorisasi
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
	}

	// Melanjutkan dengan sisa rute
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

	// Melanjutkan dengan sisa rute
});
```

### JSONP

Untuk permintaan JSONP, Anda dapat secara opsional memasukkan nama parameter kueri yang Anda
gunakan untuk mendefinisikan fungsi callback Anda:

```php
Flight::jsonp(['id' => 123], 'q');
```

Jadi, ketika melakukan permintaan GET menggunakan `?q=my_func`, Anda harus menerima output:

```javascript
my_func({"id":123});
```

Jika Anda tidak memasukkan nama parameter kueri, itu akan default ke `jsonp`.

## Mengalihkan ke URL lain

Anda dapat mengalihkan permintaan saat ini dengan menggunakan metode `redirect()` dan memasukkan
URL baru:

```php
Flight::redirect('/new/location');
```

Secara default, Flight mengirimkan status kode HTTP 303 ("Lihat Lain"). Anda juga dapat mengatur
kode kustom:

```php
Flight::redirect('/new/location', 401);
```

## Menghentikan

Anda dapat menghentikan framework kapan saja dengan memanggil metode `halt`:

```php
Flight::halt();
```

Anda juga dapat menentukan status kode dan pesan `HTTP` opsional:

```php
Flight::halt(200, 'Be right back...');
```

Memanggil `halt` akan membuang konten respons apapun hingga saat itu. Jika Anda ingin menghentikan
framework dan menampilkan respons saat ini, gunakan metode `stop`:

```php
Flight::stop();
```

## Menghapus Data Respons

Anda dapat menghapus tubuh dan header respons dengan menggunakan metode `clear()`. Ini akan menghapus
header apapun yang ditetapkan pada respons, menghapus tubuh respons, dan mengatur kode status menjadi `200`.

```php
Flight::response()->clear();
```

### Menghapus Hanya Tubuh Respons

Jika Anda hanya ingin menghapus tubuh respons, Anda dapat menggunakan metode `clearBody()`:

```php
// Ini masih akan mempertahankan header apapun yang diatur pada objek response().
Flight::response()->clearBody();
```

## Caching HTTP

Flight menyediakan dukungan bawaan untuk caching level HTTP. Jika kondisi caching
terpenuhi, Flight akan mengembalikan respons HTTP `304 Not Modified`. Waktu berikutnya klien
meminta sumber daya yang sama, mereka akan diminta untuk menggunakan versi lokal yang telah mereka
cache.

### Caching Level Rute

Jika Anda ingin menyimpan cache seluruh respons Anda, Anda dapat menggunakan metode `cache()` dan memasukkan waktu untuk disimpan dalam cache.

```php

// Ini akan menyimpan cache respons selama 5 menit
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'Konten ini akan disimpan dalam cache.';
});

// Atau, Anda dapat menggunakan string yang akan Anda kirim
// ke metode strtotime()
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'Konten ini akan disimpan dalam cache.';
});
```

### Last-Modified

Anda dapat menggunakan metode `lastModified` dan memasukkan timestamp UNIX untuk mengatur tanggal
dan waktu halaman terakhir dimodifikasi. Klien akan terus menggunakan cache mereka sampai
nilai terakhir dimodifikasi diubah.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'Konten ini akan disimpan dalam cache.';
});
```

### ETag

Caching `ETag` mirip dengan `Last-Modified`, kecuali Anda dapat menentukan id apa pun yang
Anda inginkan untuk sumber daya:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'Konten ini akan disimpan dalam cache.';
});
```

Perlu diingat bahwa memanggil `lastModified` atau `etag` akan mengatur dan memeriksa
nilai cache. Jika nilai cache sama antara permintaan, Flight akan segera
mengirim respons `HTTP 304` dan menghentikan pemrosesan.

## Mengunduh File (v3.12.0)

Ada metode pembantu untuk mengunduh file. Anda dapat menggunakan metode `download` dan memasukkan path.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```