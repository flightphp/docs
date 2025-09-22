# Instalasi

### 1. Unduh file-file tersebut.

Jika Anda menggunakan [Composer](https://getcomposer.org), Anda dapat menjalankan perintah berikut:

```bash
composer require flightphp/core
```

ATAU Anda dapat [mengunduh](https://github.com/flightphp/core/archive/master.zip) langsung dan mengekstraknya ke direktori web Anda.

### 2. Konfigurasi server web Anda.

Untuk *Apache*, edit file `.htaccess` Anda dengan yang berikut:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Catatan**: Jika Anda perlu menggunakan flight dalam subdirektori, tambahkan baris
> `RewriteBase /subdir/` tepat setelah `RewriteEngine On`.
> **Catatan**: Jika Anda ingin melindungi semua file server, seperti file db atau env.
> Letakkan ini di file `.htaccess` Anda:

```apache
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

Untuk *Nginx*, tambahkan yang berikut ini ke deklarasi server Anda:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

### 3. Buat file `index.php` Anda.

Pertama, sertakan framework.

```php
require 'flight/Flight.php';
```

Jika Anda menggunakan Composer, jalankan autoloader sebagai gantinya.

```php
require 'vendor/autoload.php';
```

Kemudian, definisikan rute dan tetapkan fungsi untuk menangani permintaan.

```php
Flight::route('/', function () {
  echo 'hello world!';
});
```

Terakhir, mulai framework.

```php
Flight::start();
```
