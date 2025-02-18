# Permintaan

Flight mengenkapsulasi permintaan HTTP ke dalam satu objek, yang dapat diakses dengan melakukan:

```php
$request = Flight::request();
```

## Kasus Penggunaan Umum

Ketika Anda sedang bekerja dengan permintaan di aplikasi web, biasanya Anda ingin mengambil header, atau parameter `$_GET` atau `$_POST`, atau mungkin bahkan body permintaan mentah. Flight menyediakan antarmuka sederhana untuk melakukan semua hal ini.

Berikut adalah contoh mengambil parameter string kueri:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "Anda sedang mencari: $keyword";
	// kueri database atau sesuatu lainnya dengan $keyword
});
```

Berikut adalah contoh dari mungkin sebuah formulir dengan metode POST:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "Anda mengirim: $name, $email";
	// simpan ke dalam database atau sesuatu lainnya dengan $name dan $email
});
```

## Properti Objek Permintaan

Objek permintaan menyediakan properti berikut:

- **body** - Body permintaan HTTP mentah
- **url** - URL yang diminta
- **base** - Subdirektori induk dari URL
- **method** - Metode permintaan (GET, POST, PUT, DELETE)
- **referrer** - URL referer
- **ip** - Alamat IP dari klien
- **ajax** - Apakah permintaan adalah permintaan AJAX
- **scheme** - Protokol server (http, https)
- **user_agent** - Informasi browser
- **type** - Tipe konten
- **length** - Panjang konten
- **query** - Parameter string kueri
- **data** - Data post atau data JSON
- **cookies** - Data cookie
- **files** - File yang diunggah
- **secure** - Apakah koneksi aman
- **accept** - Parameter HTTP accept
- **proxy_ip** - Alamat IP proxy dari klien. Memindai array `$_SERVER` untuk `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` dalam urutan itu.
- **host** - Nama host permintaan

Anda dapat mengakses properti `query`, `data`, `cookies`, dan `files` sebagai array atau objek.

Jadi, untuk mendapatkan parameter string kueri, Anda dapat melakukan:

```php
$id = Flight::request()->query['id'];
```

Atau Anda dapat melakukan:

```php
$id = Flight::request()->query->id;
```

## Body Permintaan RAW

Untuk mendapatkan body permintaan HTTP mentah, misalnya saat menangani permintaan PUT, Anda dapat melakukan:

```php
$body = Flight::request()->getBody();
```

## Input JSON

Jika Anda mengirimkan permintaan dengan tipe `application/json` dan data `{"id": 123}` itu akan tersedia dari properti `data`:

```php
$id = Flight::request()->data->id;
```

## `$_GET`

Anda dapat mengakses array `$_GET` melalui properti `query`:

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

Anda dapat mengakses array `$_POST` melalui properti `data`:

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

Anda dapat mengakses array `$_COOKIE` melalui properti `cookies`:

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

Ada pintasan yang tersedia untuk mengakses array `$_SERVER` melalui metode `getVar()`:

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## Mengakses File yang Diunggah melalui `$_FILES`

Anda dapat mengakses file yang diunggah melalui properti `files`:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## Memproses Unggahan File (v3.12.0)

Anda dapat memproses unggahan file menggunakan framework dengan beberapa metode pembantu. Secara dasar, ini menyangkut mengambil data file dari permintaan, dan memindahkannya ke lokasi baru.

```php
Flight::route('POST /upload', function(){
	// Jika Anda memiliki field input seperti <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Jika Anda memiliki beberapa file yang diunggah, Anda dapat melakukan pengulangan melalui mereka:

```php
Flight::route('POST /upload', function(){
	// Jika Anda memiliki field input seperti <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Catatan Keamanan:** Selalu validasi dan bersihkan input pengguna, terutama saat menangani unggahan file. Selalu validasi tipe ekstensi yang akan Anda izinkan untuk diunggah, tetapi Anda juga harus memvalidasi "magic bytes" dari file untuk memastikan file tersebut sebenarnya adalah tipe file yang diklaim pengguna. Terdapat [artikel](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [dan](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [perpustakaan](https://github.com/RikudouSage/MimeTypeDetector) yang tersedia untuk membantu dengan ini.

## Header Permintaan

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

## Body Permintaan

Anda dapat mengakses body permintaan mentah menggunakan metode `getBody()`:

```php
$body = Flight::request()->getBody();
```

## Metode Permintaan

Anda dapat mengakses metode permintaan menggunakan properti `method` atau metode `getMethod()`:

```php
$method = Flight::request()->method; // sebenarnya memanggil getMethod()
$method = Flight::request()->getMethod();
```

**Catatan:** Metode `getMethod()` pertama-tama menarik metode dari `$_SERVER['REQUEST_METHOD']`, kemudian bisa ditimpa oleh `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` jika ada atau `$_REQUEST['_method']` jika ada.

## URL Permintaan

Terdapat beberapa metode pembantu untuk menyusun bagian dari URL demi kenyamanan Anda.

### URL Lengkap

Anda dapat mengakses URL permintaan penuh menggunakan metode `getFullUrl()`:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### URL Dasar

Anda dapat mengakses URL dasar menggunakan metode `getBaseUrl()`:

```php
$url = Flight::request()->getBaseUrl();
// Perhatikan, tidak ada garis miring di akhir.
// https://example.com
```

## Penguraian Kueri

Anda dapat mengoper URL ke metode `parseQuery()` untuk menguraikan string kueri menjadi array asosiatif:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```