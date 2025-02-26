# Respons

Flight membantu menghasilkan sebagian dari header respon untuk Anda, tetapi Anda memegang sebagian besar kendali atas apa yang Anda kirim kembali kepada pengguna. Terkadang Anda dapat mengakses objek `Response` secara langsung, tetapi sebagian besar waktu Anda akan menggunakan instance `Flight` untuk mengirim respon.

## Mengirim Respon Dasar

Flight menggunakan ob_start() untuk membuffer output. Ini berarti Anda dapat menggunakan `echo` atau `print` untuk mengirim respon kepada pengguna dan Flight akan menangkapnya dan mengirimkannya kembali kepada pengguna dengan header yang sesuai.

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

Sebagai alternatif, Anda dapat memanggil metode `write()` untuk menambah isi juga.

```php

// Ini akan mengirim "Hello, World!" ke browser pengguna
Flight::route('/', function() {
	// verbose, tetapi terkadang menyelesaikan pekerjaan saat Anda membutuhkannya
	Flight::response()->write("Hello, World!");

	// jika Anda ingin mengambil isi yang telah Anda atur pada titik ini
	// Anda dapat melakukannya seperti ini
	$body = Flight::response()->getBody();
});
```

## Kode Status

Anda dapat mengatur kode status dari respon dengan menggunakan metode `status`:

```php
Flight::route('/@id', function($id) {
	if($id == 123) {
		Flight::response()->status(200);
		echo "Hello, World!";
	} else {
		Flight::response()->status(403);
		echo "Terlarang";
	}
});
```

Jika Anda ingin mendapatkan kode status saat ini, Anda dapat menggunakan metode `status` tanpa argumen:

```php
Flight::response()->status(); // 200
```

## Mengatur Isi Respon

Anda dapat mengatur isi respon dengan menggunakan metode `write`, namun, jika Anda menggunakan echo atau print apa pun, 
itu akan ditangkap dan dikirim sebagai isi respon melalui buffering output.

```php
Flight::route('/', function() {
	Flight::response()->write("Hello, World!");
});

// sama dengan

Flight::route('/', function() {
	echo "Hello, World!";
});
```

### Menghapus Isi Respon

Jika Anda ingin menghapus isi respon, Anda dapat menggunakan metode `clearBody`:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### Menjalankan Callback pada Isi Respon

Anda dapat menjalankan callback pada isi respon dengan menggunakan metode `addResponseBodyCallback`:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Ini akan gzip semua respon untuk rute mana pun
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Anda dapat menambahkan beberapa callback dan mereka akan dijalankan dalam urutan mereka ditambahkan. Karena ini dapat menerima [callable](https://www.php.net/manual/en/language.types.callable.php), itu dapat menerima array kelas `[ $class, 'method' ]`, satu closure `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, atau nama fungsi `'minify'` jika Anda memiliki fungsi untuk memperkecil kode html Anda misalnya.

**Catatan:** Callback rute tidak akan berfungsi jika Anda menggunakan opsi konfigurasi `flight.v2.output_buffering`.

### Callback Rute Spesifik

Jika Anda ingin ini hanya berlaku untuk rute tertentu, Anda bisa menambahkan callback di dalam rute itu sendiri:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Ini akan gzip hanya respon untuk rute ini
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
		// memperkecil isi dengan cara tertentu
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

## Mengatur Header Respon

Anda dapat mengatur header seperti jenis konten dari respon dengan menggunakan metode `header`:

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

Flight menyediakan dukungan untuk mengirim respon JSON dan JSONP. Untuk mengirim respon JSON Anda
mengirimkan beberapa data untuk di-encode JSON:

```php
Flight::json(['id' => 123]);
```

> **Catatan:** Secara default, Flight akan mengirim header `Content-Type: application/json` dengan respon. Ini juga akan menggunakan konstanta `JSON_THROW_ON_ERROR` dan `JSON_UNESCAPED_SLASHES` saat mengencode JSON.

### JSON dengan Kode Status

Anda juga dapat mengirimkan kode status sebagai argumen kedua:

```php
Flight::json(['id' => 123], 201);
```

### JSON dengan Pretty Print

Anda juga dapat mengirimkan argumen ke posisi terakhir untuk mengaktifkan pretty printing:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Jika Anda mengubah opsi yang dikirimkan ke `Flight::json()` dan ingin sintaks yang lebih sederhana, Anda dapat 
hanya memetakan metode JSON:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// Dan sekarang dapat digunakan seperti ini
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON dan Hentikan Eksekusi (v3.10.0)

Jika Anda ingin mengirim respon JSON dan menghentikan eksekusi, Anda dapat menggunakan metode `jsonHalt`.
Ini berguna untuk kasus-kasus di mana Anda memeriksa mungkin beberapa jenis otorisasi dan jika
pengguna tidak diizinkan, Anda dapat segera mengirim respon JSON, menghapus konten isi yang ada
dan menghentikan eksekusi.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Periksa apakah pengguna diizinkan
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Tidak Diizinkan'], 401);
	}

	// Lanjutkan dengan sisa rute
});
```

Sebelum v3.10.0, Anda harus melakukan sesuatu seperti ini:

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Periksa apakah pengguna diizinkan
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Tidak Diizinkan']));
	}

	// Lanjutkan dengan sisa rute
});
```

### JSONP

Untuk permintaan JSONP, Anda dapat secara opsional mengirimkan nama parameter kueri yang Anda
gunakan untuk mendefinisikan fungsi callback Anda:

```php
Flight::jsonp(['id' => 123], 'q');
```

Jadi, ketika melakukan permintaan GET menggunakan `?q=my_func`, Anda seharusnya menerima output:

```javascript
my_func({"id":123});
```

Jika Anda tidak mengirimkan nama parameter kueri, itu akan default ke `jsonp`.

## Pengalihan ke URL Lain

Anda dapat mengalihkan permintaan saat ini dengan menggunakan metode `redirect()` dan mengirimkan
URL baru:

```php
Flight::redirect('/new/location');
```

Secara default, Flight mengirim kode status HTTP 303 ("Melihat Lainnya"). Anda dapat mengatur kode khusus:

```php
Flight::redirect('/new/location', 401);
```

## Menghentikan

Anda dapat menghentikan framework pada titik mana pun dengan memanggil metode `halt`:

```php
Flight::halt();
```

Anda juga dapat menentukan kode status `HTTP` dan pesan opsional:

```php
Flight::halt(200, 'Segera kembali...');
```

Memanggil `halt` akan membuang konten respon apa pun hingga titik itu. Jika Anda ingin menghentikan
framework dan output respon saat ini, gunakan metode `stop`:

```php
Flight::stop();
```

## Menghapus Data Respon

Anda dapat menghapus isi respon dan header dengan menggunakan metode `clear()`. Ini akan menghapus
header yang diberikan ke respon, menghapus isi respon, dan mengatur kode status ke `200`.

```php
Flight::response()->clear();
```

### Menghapus Hanya Isi Respon

Jika Anda hanya ingin menghapus isi respon, Anda dapat menggunakan metode `clearBody()`:

```php
// Ini masih akan menjaga semua header yang disetel pada objek response().
Flight::response()->clearBody();
```

## Caching HTTP

Flight menyediakan dukungan built-in untuk caching level HTTP. Jika kondisi caching
terpenuhi, Flight akan mengembalikan respon HTTP `304 Not Modified`. Lain kali klien
meminta sumber daya yang sama, mereka akan diminta untuk menggunakan versi yang di-cache secara lokal.

### Caching Level Rute

Jika Anda ingin menyimpan cache seluruh respon, Anda dapat menggunakan metode `cache()` dan mengirimkan waktu untuk menyimpan cache.

```php

// Ini akan menyimpan cache respon selama 5 menit
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'Konten ini akan di-cache.';
});

// Sebagai alternatif, Anda dapat menggunakan string yang akan Anda kirim
// ke metode strtotime()
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'Konten ini akan di-cache.';
});
```

### Last-Modified

Anda dapat menggunakan metode `lastModified` dan mengirimkan timestamp UNIX untuk mengatur tanggal
dan waktu halaman terakhir dimodifikasi. Klien akan terus menggunakan cache mereka sampai
nilai terakhir yang dimodifikasi diubah.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'Konten ini akan di-cache.';
});
```

### ETag

Caching `ETag` mirip dengan `Last-Modified`, kecuali Anda dapat menentukan id apa pun
yang Anda inginkan untuk sumber daya:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'Konten ini akan di-cache.';
});
```

Perlu diingat bahwa memanggil baik `lastModified` atau `etag` akan mengatur dan memeriksa
nilai cache. Jika nilai cache sama antara permintaan, Flight akan segera
mengirim respon `HTTP 304` dan menghentikan pemrosesan.

## Mengunduh Berkas (v3.12.0)

Ada metode bantu untuk mengunduh berkas. Anda dapat menggunakan metode `download` dan mengirimkan jalur.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```