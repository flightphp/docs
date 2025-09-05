# Permintaan

Flight mengenkapsulasi permintaan HTTP menjadi satu objek, yang dapat diakses dengan:

```php
$request = Flight::request();
```

## Kasus Penggunaan Tipikal

Saat Anda bekerja dengan permintaan dalam aplikasi web, biasanya Anda ingin mengambil header, atau parameter `$_GET` atau `$_POST`, atau mungkin bahkan badan permintaan mentah. Flight menyediakan antarmuka sederhana untuk melakukan semua hal ini.

Berikut adalah contoh mendapatkan parameter string query:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "You are searching for: $keyword";
	// query database atau sesuatu yang lain dengan $keyword
});
```

Berikut adalah contoh mungkin formulir dengan metode POST:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "You submitted: $name, $email";
	// simpan ke database atau sesuatu yang lain dengan $name dan $email
});
```

## Properti Objek Permintaan

Objek permintaan menyediakan properti berikut:

- **body** - Badan permintaan HTTP mentah
- **url** - URL yang diminta
- **base** - Subdirektori induk dari URL
- **method** - Metode permintaan (GET, POST, PUT, DELETE)
- **referrer** - URL rujukan
- **ip** - Alamat IP klien
- **ajax** - Apakah permintaan adalah permintaan AJAX
- **scheme** - Protokol server (http, https)
- **user_agent** - Informasi browser
- **type** - Jenis konten
- **length** - Panjang konten
- **query** - Parameter string query
- **data** - Data POST atau data JSON
- **cookies** - Data cookie
- **files** - File yang diunggah
- **secure** - Apakah koneksi aman
- **accept** - Parameter HTTP accept
- **proxy_ip** - Alamat IP proxy klien. Memindai array `$_SERVER` untuk `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` dalam urutan itu.
- **host** - Nama host permintaan
- **servername** - SERVER_NAME dari `$_SERVER`

Anda dapat mengakses properti `query`, `data`, `cookies`, dan `files` sebagai array atau objek.

Jadi, untuk mendapatkan parameter string query, Anda dapat melakukan:

```php
$id = Flight::request()->query['id'];
```

Atau Anda dapat melakukan:

```php
$id = Flight::request()->query->id;
```

## Badan Permintaan Mentah

Untuk mendapatkan badan permintaan HTTP mentah, misalnya saat berhadapan dengan permintaan PUT, Anda dapat melakukan:

```php
$body = Flight::request()->getBody();
```

## Input JSON

Jika Anda mengirim permintaan dengan jenis `application/json` dan data `{"id": 123}`, itu akan tersedia dari properti `data`:

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
$host = Flight::request()->getVar('HTTP_HOST');
```

## Mengakses File yang Diunggah melalui `$_FILES`

Anda dapat mengakses file yang diunggah melalui properti `files`:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## Pemrosesan Unggah File (v3.12.0)

Anda dapat memroses unggah file menggunakan framework dengan beberapa metode bantu. Pada dasarnya, itu berarti menarik data file dari permintaan, dan memindahkannya ke lokasi baru.

```php
Flight::route('POST /upload', function(){
	// Jika Anda memiliki bidang input seperti <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Jika Anda memiliki beberapa file yang diunggah, Anda dapat mengulangi melalui mereka:

```php
Flight::route('POST /upload', function(){
	// Jika Anda memiliki bidang input seperti <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Catatan Keamanan:** Selalu validasi dan sanitasi input pengguna, terutama saat berhadapan dengan unggah file. Selalu validasi jenis ekstensi yang akan Anda izinkan untuk diunggah, tetapi Anda juga harus validasi "magic bytes" file untuk memastikan itu benar-benar jenis file yang diklaim pengguna. Ada [articles](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [and](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [libraries](https://github.com/RikudouSage/MimeTypeDetector) yang tersedia untuk membantu dengan ini.

## Header Permintaan

Anda dapat mengakses header permintaan menggunakan metode `getHeader()` atau `getHeaders()`:

```php
// Mungkin Anda perlu header Authorization
$host = Flight::request()->getHeader('Authorization');
// atau
$host = Flight::request()->header('Authorization');

// Jika Anda perlu mengambil semua header
$headers = Flight::request()->getHeaders();
// atau
$headers = Flight::request()->headers();
```

## Badan Permintaan

Anda dapat mengakses badan permintaan mentah menggunakan metode `getBody()`:

```php
$body = Flight::request()->getBody();
```

## Metode Permintaan

Anda dapat mengakses metode permintaan menggunakan properti `method` atau metode `getMethod()`:

```php
$method = Flight::request()->method; // sebenarnya memanggil getMethod()
$method = Flight::request()->getMethod();
```

**Catatan:** Metode `getMethod()` pertama menarik metode dari `$_SERVER['REQUEST_METHOD']`, kemudian dapat ditimpa oleh `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` jika ada atau `$_REQUEST['_method']` jika ada.

## URL Permintaan

Ada beberapa metode bantu untuk menyusun bagian-bagian URL untuk kenyamanan Anda.

### URL Lengkap

Anda dapat mengakses URL permintaan lengkap menggunakan metode `getFullUrl()`:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```

### URL Dasar

Anda dapat mengakses URL dasar menggunakan metode `getBaseUrl()`:

```php
$url = Flight::request()->getBaseUrl();
// Perhatikan, tidak ada garis miring akhir.
// https://example.com
```

## Penguraian Query

Anda dapat meneruskan URL ke metode `parseQuery()` untuk menguraikan string query menjadi array asosiatif:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```