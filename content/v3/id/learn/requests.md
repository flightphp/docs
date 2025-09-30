# Requests

## Overview

Flight merangkum permintaan HTTP ke dalam satu objek, yang dapat diakses dengan melakukan:

```php
$request = Flight::request();
```

## Understanding

Permintaan HTTP adalah salah satu aspek inti yang perlu dipahami tentang siklus hidup HTTP. Pengguna melakukan tindakan pada peramban web atau klien HTTP, dan mereka mengirim serangkaian header, body, URL, dll ke proyek Anda. Anda dapat menangkap header ini (bahasa peramban, jenis kompresi yang dapat ditangani, agen pengguna, dll) dan menangkap body serta URL yang dikirim ke aplikasi Flight Anda. Permintaan ini sangat penting agar aplikasi Anda memahami apa yang harus dilakukan selanjutnya.

## Basic Usage

PHP memiliki beberapa super globals termasuk `$_GET`, `$_POST`, `$_REQUEST`, `$_SERVER`, `$_FILES`, dan `$_COOKIE`. Flight mengabstraksikan ini menjadi [Collections](/learn/collections) yang berguna. Anda dapat mengakses properti `query`, `data`, `cookies`, dan `files` sebagai array atau objek.

> **Catatan:** Sangat **TIDAK DISARANKAN** menggunakan super globals ini dalam proyek Anda dan sebaiknya dirujuk melalui objek `request()`.

> **Catatan:** Tidak ada abstraksi yang tersedia untuk `$_ENV`.

### `$_GET`

Anda dapat mengakses array `$_GET` melalui properti `query`:

```php
// GET /search?keyword=something
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	// atau
	$keyword = Flight::request()->query->keyword;
	echo "Anda sedang mencari: $keyword";
	// query database atau hal lain dengan $keyword
});
```

### `$_POST`

Anda dapat mengakses array `$_POST` melalui properti `data`:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	// atau
	$name = Flight::request()->data->name;
	$email = Flight::request()->data->email;
	echo "Anda mengirimkan: $name, $email";
	// simpan ke database atau hal lain dengan $name dan $email
});
```

### `$_COOKIE`

Anda dapat mengakses array `$_COOKIE` melalui properti `cookies`:

```php
Flight::route('GET /login', function(){
	$savedLogin = Flight::request()->cookies['myLoginCookie'];
	// atau
	$savedLogin = Flight::request()->cookies->myLoginCookie;
	// periksa apakah benar-benar tersimpan atau tidak dan jika ya, login otomatis
	if($savedLogin) {
		Flight::redirect('/dashboard');
		return;
	}
});
```

Untuk bantuan dalam menetapkan nilai cookie baru, lihat [overclokk/cookie](/awesome-plugins/php-cookie)

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
// atau
$uploadedFile = Flight::request()->files->myFile;
```

Lihat [Uploaded File Handler](/learn/uploaded-file) untuk informasi lebih lanjut.

#### Processing File Uploads

_v3.12.0_

Anda dapat memproses unggahan file menggunakan framework dengan beberapa metode bantu. Ini pada dasarnya 
berkurang untuk menarik data file dari permintaan, dan memindahkannya ke lokasi baru.

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

> **Catatan Keamanan:** Selalu validasi dan sanitasi input pengguna, terutama saat berurusan dengan unggahan file. Selalu validasi jenis ekstensi yang akan diizinkan diunggah, tetapi Anda juga harus memvalidasi "magic bytes" file untuk memastikan itu benar-benar jenis file yang diklaim pengguna. Ada [artikel](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [dan](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [library](https://github.com/RikudouSage/MimeTypeDetector) yang tersedia untuk membantu dengan ini.

### Request Body

Untuk mendapatkan body permintaan HTTP mentah, misalnya saat berurusan dengan permintaan POST/PUT,
Anda dapat melakukan:

```php
Flight::route('POST /users/xml', function(){
	$xmlBody = Flight::request()->getBody();
	// lakukan sesuatu dengan XML yang dikirim.
});
```

### JSON Body

Jika Anda menerima permintaan dengan jenis konten `application/json` dan data contoh `{"id": 123}`
itu akan tersedia dari properti `data`:

```php
$id = Flight::request()->data->id;
```

### Request Headers

Anda dapat mengakses header permintaan menggunakan metode `getHeader()` atau `getHeaders()`:

```php

// Mungkin Anda membutuhkan header Authorization
$host = Flight::request()->getHeader('Authorization');
// atau
$host = Flight::request()->header('Authorization');

// Jika Anda perlu mengambil semua header
$headers = Flight::request()->getHeaders();
// atau
$headers = Flight::request()->headers();
```

### Request Method

Anda dapat mengakses metode permintaan menggunakan properti `method` atau metode `getMethod()`:

```php
$method = Flight::request()->method; // sebenarnya diisi oleh getMethod()
$method = Flight::request()->getMethod();
```

**Catatan:** Metode `getMethod()` pertama-tama menarik metode dari `$_SERVER['REQUEST_METHOD']`, kemudian dapat ditimpa 
oleh `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` jika ada atau `$_REQUEST['_method']` jika ada.

## Request Object Properties

Objek request menyediakan properti berikut:

- **body** - Body permintaan HTTP mentah
- **url** - URL yang diminta
- **base** - Subdirektori induk dari URL
- **method** - Metode permintaan (GET, POST, PUT, DELETE)
- **referrer** - URL referrer
- **ip** - Alamat IP klien
- **ajax** - Apakah permintaan adalah permintaan AJAX
- **scheme** - Protokol server (http, https)
- **user_agent** - Informasi peramban
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

## URL Helper Methods

Ada beberapa metode bantu untuk menyusun bagian-bagian URL untuk kenyamanan Anda.

### Full URL

Anda dapat mengakses URL permintaan lengkap menggunakan metode `getFullUrl()`:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### Base URL

Anda dapat mengakses URL dasar menggunakan metode `getBaseUrl()`:

```php
// http://example.com/path/to/something/cool?query=yes+thanks
$url = Flight::request()->getBaseUrl();
// https://example.com
// Perhatikan, tidak ada slash akhir.
```

## Query Parsing

Anda dapat meneruskan URL ke metode `parseQuery()` untuk mengurai string query menjadi array asosiatif:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```

## See Also
- [Routing](/learn/routing) - Lihat cara memetakan rute ke controller dan render tampilan.
- [Responses](/learn/responses) - Cara menyesuaikan respons HTTP.
- [Why a Framework?](/learn/why-frameworks) - Bagaimana permintaan cocok ke dalam gambaran besar.
- [Collections](/learn/collections) - Bekerja dengan koleksi data.
- [Uploaded File Handler](/learn/uploaded-file) - Menangani unggahan file.

## Troubleshooting
- `request()->ip` dan `request()->proxy_ip` bisa berbeda jika webserver Anda berada di belakang proxy, load balancer, dll. 

## Changelog
- v3.12.0 - Ditambahkan kemampuan untuk menangani unggahan file melalui objek request.
- v1.0 - Rilis awal.