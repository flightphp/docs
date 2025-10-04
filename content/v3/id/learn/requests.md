# Permintaan

## Gambaran Umum

Flight merangkum permintaan HTTP ke dalam satu objek, yang dapat diakses dengan melakukan:

```php
$request = Flight::request();
```

## Pemahaman

Permintaan HTTP adalah salah satu aspek inti yang perlu dipahami tentang siklus hidup HTTP. Pengguna melakukan aksi pada browser web atau klien HTTP, dan mereka mengirim serangkaian header, body, URL, dll ke proyek Anda. Anda dapat menangkap header ini (bahasa browser, jenis kompresi yang dapat ditangani, agen pengguna, dll) dan menangkap body serta URL yang dikirim ke aplikasi Flight Anda. Permintaan ini sangat penting bagi aplikasi Anda untuk memahami apa yang harus dilakukan selanjutnya.

## Penggunaan Dasar

PHP memiliki beberapa super global termasuk `$_GET`, `$_POST`, `$_REQUEST`, `$_SERVER`, `$_FILES`, dan `$_COOKIE`. Flight mengabstraksikan ini menjadi [Collections](/learn/collections) yang berguna. Anda dapat mengakses properti `query`, `data`, `cookies`, dan `files` sebagai array atau objek.

> **Catatan:** Sangat **TIDAK DISARANKAN** untuk menggunakan super global ini di proyek Anda dan seharusnya dirujuk melalui objek `request()`.

> **Catatan:** Tidak ada abstraksi yang tersedia untuk `$_ENV`.

### `$_GET`

Anda dapat mengakses array `$_GET` melalui properti `query`:

```php
// GET /search?keyword=something
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	// or
	$keyword = Flight::request()->query->keyword;
	echo "Anda sedang mencari: $keyword";
	// query database atau sesuatu yang lain dengan $keyword
});
```

### `$_POST`

Anda dapat mengakses array `$_POST` melalui properti `data`:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	// or
	$name = Flight::request()->data->name;
	$email = Flight::request()->data->email;
	echo "Anda mengirimkan: $name, $email";
	// simpan ke database atau sesuatu yang lain dengan $name dan $email
});
```

### `$_COOKIE`

Anda dapat mengakses array `$_COOKIE` melalui properti `cookies`:

```php
Flight::route('GET /login', function(){
	$savedLogin = Flight::request()->cookies['myLoginCookie'];
	// or
	$savedLogin = Flight::request()->cookies->myLoginCookie;
	// periksa apakah benar-benar disimpan atau tidak dan jika ya, login otomatis
	if($savedLogin) {
		Flight::redirect('/dashboard');
		return;
	}
});
```

Untuk bantuan tentang pengaturan nilai cookie baru, lihat [overclokk/cookie](/awesome-plugins/php-cookie)

### `$_SERVER`

Ada jalan pintas yang tersedia untuk mengakses array `$_SERVER` melalui metode `getVar()`:

```php

$host = Flight::request()->getVar('HTTP_HOST');
```

### `$_FILES`

Anda dapat mengakses file yang diunggah melalui properti `files`:

```php
// akses mentah ke properti $_FILES. Lihat di bawah untuk pendekatan yang direkomendasikan
$uploadedFile = Flight::request()->files['myFile']; 
// or
$uploadedFile = Flight::request()->files->myFile;
```

Lihat [Uploaded File Handler](/learn/uploaded-file) untuk info lebih lanjut.

#### Pemrosesan Unggahan File

_v3.12.0_

Anda dapat memproses unggahan file menggunakan framework dengan beberapa metode pembantu. Pada dasarnya, ini merangkum menarik data file dari permintaan, dan memindahkannya ke lokasi baru.

```php
Flight::route('POST /upload', function(){
	// Jika Anda memiliki field input seperti <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Jika Anda memiliki beberapa file yang diunggah, Anda dapat melooping melalui mereka:

```php
Flight::route('POST /upload', function(){
	// Jika Anda memiliki field input seperti <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Catatan Keamanan:** Selalu validasi dan sanitasi input pengguna, terutama saat berurusan dengan unggahan file. Selalu validasi jenis ekstensi yang akan diizinkan untuk diunggah, tetapi Anda juga harus memvalidasi "magic bytes" dari file untuk memastikan itu benar-benar jenis file yang diklaim pengguna. Ada [artikel](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [dan](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [library](https://github.com/RikudouSage/MimeTypeDetector) yang tersedia untuk membantu dengan ini.

### Body Permintaan

Untuk mendapatkan body permintaan HTTP mentah, misalnya saat berurusan dengan permintaan POST/PUT,
Anda dapat melakukan:

```php
Flight::route('POST /users/xml', function(){
	$xmlBody = Flight::request()->getBody();
	// lakukan sesuatu dengan XML yang dikirim.
});
```

### Body JSON

Jika Anda menerima permintaan dengan jenis konten `application/json` dan data contoh `{"id": 123}`
itu akan tersedia dari properti `data`:

```php
$id = Flight::request()->data->id;
```

### Header Permintaan

Anda dapat mengakses header permintaan menggunakan metode `getHeader()` atau `getHeaders()`:

```php

// Mungkin Anda membutuhkan header Authorization
$host = Flight::request()->getHeader('Authorization');
// or
$host = Flight::request()->header('Authorization');

// Jika Anda perlu mengambil semua header
$headers = Flight::request()->getHeaders();
// or
$headers = Flight::request()->headers();
```

### Metode Permintaan

Anda dapat mengakses metode permintaan menggunakan properti `method` atau metode `getMethod()`:

```php
$method = Flight::request()->method; // sebenarnya diisi oleh getMethod()
$method = Flight::request()->getMethod();
```

**Catatan:** Metode `getMethod()` pertama-tama menarik metode dari `$_SERVER['REQUEST_METHOD']`, kemudian dapat ditimpa 
oleh `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` jika ada atau `$_REQUEST['_method']` jika ada.

## Properti Objek Permintaan

Objek permintaan menyediakan properti berikut:

- **body** - Body permintaan HTTP mentah
- **url** - URL yang diminta
- **base** - Subdirektori induk dari URL
- **method** - Metode permintaan (GET, POST, PUT, DELETE)
- **referrer** - URL referrer
- **ip** - Alamat IP klien
- **ajax** - Apakah permintaan adalah permintaan AJAX
- **scheme** - Protokol server (http, https)
- **user_agent** - Informasi browser
- **type** - Jenis konten
- **length** - Panjang konten
- **query** - Parameter string query
- **data** - Data post atau data JSON
- **cookies** - Data cookie
- **files** - File yang diunggah
- **secure** - Apakah koneksi aman
- **accept** - Parameter accept HTTP
- **proxy_ip** - Alamat IP proxy klien. Memindai array `$_SERVER` untuk `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` dalam urutan itu.
- **host** - Nama host permintaan
- **servername** - SERVER_NAME dari `$_SERVER`

## Metode Pembantu

Ada beberapa metode pembantu untuk menyusun bagian dari URL, atau berurusan dengan header tertentu.

### URL Lengkap

Anda dapat mengakses URL permintaan lengkap menggunakan metode `getFullUrl()`:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```

### URL Dasar

Anda dapat mengakses URL dasar menggunakan metode `getBaseUrl()`:

```php
// http://example.com/path/to/something/cool?query=yes+thanks
$url = Flight::request()->getBaseUrl();
// https://example.com
// Perhatikan, tidak ada slash akhir.
```

## Parsing Query

Anda dapat meneruskan URL ke metode `parseQuery()` untuk mem-parsing string query menjadi array asosiatif:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```

## Negosiasi Jenis Konten Accept

_v3.17.2_

Anda dapat menggunakan metode `negotiateContentType()` untuk menentukan jenis konten terbaik untuk merespons berdasarkan header `Accept` yang dikirim oleh klien.

```php

// Contoh header Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8
// Yang di bawah ini mendefinisikan apa yang Anda dukung.
$availableTypes = ['application/json', 'application/xml'];
$typeToServe = Flight::request()->negotiateContentType($availableTypes);
if ($typeToServe === 'application/json') {
	// Layani respons JSON
} elseif ($typeToServe === 'application/xml') {
	// Layani respons XML
} else {
	// Default ke sesuatu yang lain atau lempar error
}
```

> **Catatan:** Jika tidak ada jenis yang tersedia yang ditemukan dalam header `Accept`, metode akan mengembalikan `null`. Jika tidak ada header `Accept` yang didefinisikan, metode akan mengembalikan jenis pertama dalam array `$availableTypes`.

## Lihat Juga
- [Routing](/learn/routing) - Lihat cara memetakan rute ke controller dan render tampilan.
- [Responses](/learn/responses) - Cara menyesuaikan respons HTTP.
- [Mengapa Framework?](/learn/why-frameworks) - Bagaimana permintaan cocok ke dalam gambaran besar.
- [Collections](/learn/collections) - Bekerja dengan kumpulan data.
- [Uploaded File Handler](/learn/uploaded-file) - Menangani unggahan file.

## Pemecahan Masalah
- `request()->ip` dan `request()->proxy_ip` bisa berbeda jika webserver Anda berada di belakang proxy, load balancer, dll. 

## Changelog
- v3.17.2 - Menambahkan negotiateContentType()
- v3.12.0 - Menambahkan kemampuan untuk menangani unggahan file melalui objek permintaan.
- v1.0 - Rilis awal.