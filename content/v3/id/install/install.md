# Petunjuk Instalasi

Ada beberapa prasyarat dasar sebelum Anda dapat menginstal Flight. Yaitu, Anda perlu:

1. [Instal PHP di sistem Anda](#menginstal-php)
2. [Instal Composer](https://getcomposer.org) untuk pengalaman pengembang terbaik.

## Instalasi Dasar

Jika Anda menggunakan [Composer](https://getcomposer.org), Anda dapat menjalankan perintah berikut:

```bash
composer require flightphp/core
```

Ini hanya akan meletakkan file inti Flight di sistem Anda. Anda perlu mendefinisikan struktur proyek, [layout](/learn/templates), [dependencies](/learn/dependency-injection-container), [configs](/learn/configuration), [autoloading](/learn/autoloading), dll. Metode ini memastikan bahwa tidak ada dependensi lain selain Flight yang diinstal.

Anda juga dapat [mengunduh file](https://github.com/flightphp/core/archive/master.zip)
 secara langsung dan mengekstraknya ke direktori web Anda.

## Instalasi yang Direkomendasikan

Sangat disarankan untuk memulai dengan aplikasi [flightphp/skeleton](https://github.com/flightphp/skeleton) untuk proyek baru apa pun. Instalasi sangat mudah.

```bash
composer create-project flightphp/skeleton my-project/
```

Ini akan menyiapkan struktur proyek Anda, mengonfigurasi autoloading dengan namespace, menyiapkan konfigurasi, dan menyediakan alat lain seperti [Tracy](/awesome-plugins/tracy), [Tracy Extensions](/awesome-plugins/tracy-extensions), dan [Runway](/awesome-plugins/runway)

## Konfigurasi Server Web Anda

### Server Pengembangan PHP Bawaan

Ini adalah cara termudah untuk memulai dan menjalankan. Anda dapat menggunakan server bawaan untuk menjalankan aplikasi Anda dan bahkan menggunakan SQLite untuk database (selama sqlite3 diinstal di sistem Anda) dan tidak memerlukan banyak hal apa pun! Cukup jalankan perintah berikut setelah PHP diinstal:

```bash
php -S localhost:8000
# atau dengan aplikasi skeleton
composer start
```

Kemudian buka browser Anda dan pergi ke `http://localhost:8000`.

Jika Anda ingin menjadikan document root proyek Anda direktori yang berbeda (Contoh: proyek Anda adalah `~/myproject`, tetapi document root Anda adalah `~/myproject/public/`), Anda dapat menjalankan perintah berikut setelah berada di direktori `~/myproject`:

```bash
php -S localhost:8000 -t public/
# dengan aplikasi skeleton, ini sudah dikonfigurasi
composer start
```

Kemudian buka browser Anda dan pergi ke `http://localhost:8000`.

### Apache

Pastikan Apache sudah diinstal di sistem Anda. Jika tidak, cari di Google cara menginstal Apache di sistem Anda.

Untuk Apache, edit file `.htaccess` Anda dengan yang berikut:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Catatan**: Jika Anda perlu menggunakan flight di subdirektori, tambahkan baris
> `RewriteBase /subdir/` tepat setelah `RewriteEngine On`.

> **Catatan**: Jika Anda ingin melindungi semua file server, seperti file db atau env.
> Letakkan ini di file `.htaccess` Anda:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

Pastikan Nginx sudah diinstal di sistem Anda. Jika tidak, cari di Google cara menginstal Nginx di sistem Anda.

Untuk Nginx, tambahkan yang berikut ke deklarasi server Anda:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## Buat file `index.php` Anda

Jika Anda melakukan instalasi dasar, Anda akan membutuhkan beberapa kode untuk memulai.

```php
<?php

// Jika Anda menggunakan Composer, require the autoloader.
require 'vendor/autoload.php';
// jika Anda tidak menggunakan Composer, load the framework directly
// require 'flight/Flight.php';

// Kemudian definisikan rute dan tetapkan fungsi untuk menangani permintaan.
Flight::route('/', function () {
  echo 'hello world!';
});

// Akhirnya, mulai framework.
Flight::start();
```

Dengan aplikasi skeleton, ini sudah dikonfigurasi dan ditangani di file `app/config/routes.php` Anda. Layanan dikonfigurasi di `app/config/services.php`

## Menginstal PHP

Jika Anda sudah memiliki `php` yang diinstal di sistem Anda, lanjutkan dan lewati petunjuk ini dan pindah ke [bagian unduhan](#mengunduh-file)

### **macOS**

#### **Menginstal PHP menggunakan Homebrew**

1. **Instal Homebrew** (jika belum diinstal):
   - Buka Terminal dan jalankan:
     ```bash
     /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
     ```

2. **Instal PHP**:
   - Instal versi terbaru:
     ```bash
     brew install php
     ```
   - Untuk menginstal versi spesifik, misalnya, PHP 8.1:
     ```bash
     brew tap shivammathur/php
     brew install shivammathur/php/php@8.1
     ```

3. **Beralih antar versi PHP**:
   - Unlink versi saat ini dan link versi yang diinginkan:
     ```bash
     brew unlink php
     brew link --overwrite --force php@8.1
     ```
   - Verifikasi versi yang diinstal:
     ```bash
     php -v
     ```

### **Windows 10/11**

#### **Menginstal PHP secara manual**

1. **Unduh PHP**:
   - Kunjungi [PHP for Windows](https://windows.php.net/download/) dan unduh versi terbaru atau versi spesifik (misalnya, 7.4, 8.0) sebagai file zip non-thread-safe.

2. **Ekstrak PHP**:
   - Ekstrak file zip yang diunduh ke `C:\php`.

3. **Tambahkan PHP ke PATH sistem**:
   - Pergi ke **System Properties** > **Environment Variables**.
   - Di bawah **System variables**, temukan **Path** dan klik **Edit**.
   - Tambahkan path `C:\php` (atau di mana pun Anda mengekstrak PHP).
   - Klik **OK** untuk menutup semua jendela.

4. **Konfigurasi PHP**:
   - Salin `php.ini-development` ke `php.ini`.
   - Edit `php.ini` untuk mengonfigurasi PHP sesuai kebutuhan (misalnya, mengatur `extension_dir`, mengaktifkan ekstensi).

5. **Verifikasi instalasi PHP**:
   - Buka Command Prompt dan jalankan:
     ```cmd
     php -v
     ```

#### **Menginstal Beberapa Versi PHP**

1. **Ulangi langkah di atas** untuk setiap versi, letakkan masing-masing di direktori terpisah (misalnya, `C:\php7`, `C:\php8`).

2. **Beralih antar versi** dengan menyesuaikan variabel PATH sistem untuk menunjuk ke direktori versi yang diinginkan.

### **Ubuntu (20.04, 22.04, dll.)**

#### **Menginstal PHP menggunakan apt**

1. **Perbarui daftar paket**:
   - Buka Terminal dan jalankan:
     ```bash
     sudo apt update
     ```

2. **Instal PHP**:
   - Instal versi PHP terbaru:
     ```bash
     sudo apt install php
     ```
   - Untuk menginstal versi spesifik, misalnya, PHP 8.1:
     ```bash
     sudo apt install php8.1
     ```

3. **Instal modul tambahan** (opsional):
   - Misalnya, untuk menginstal dukungan MySQL:
     ```bash
     sudo apt install php8.1-mysql
     ```

4. **Beralih antar versi PHP**:
   - Gunakan `update-alternatives`:
     ```bash
     sudo update-alternatives --set php /usr/bin/php8.1
     ```

5. **Verifikasi versi yang diinstal**:
   - Jalankan:
     ```bash
     php -v
     ```

### **Rocky Linux**

#### **Menginstal PHP menggunakan yum/dnf**

1. **Aktifkan repositori EPEL**:
   - Buka Terminal dan jalankan:
     ```bash
     sudo dnf install epel-release
     ```

2. **Instal repositori Remi's**:
   - Jalankan:
     ```bash
     sudo dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
     sudo dnf module reset php
     ```

3. **Instal PHP**:
   - Untuk menginstal versi default:
     ```bash
     sudo dnf install php
     ```
   - Untuk menginstal versi spesifik, misalnya, PHP 7.4:
     ```bash
     sudo dnf module install php:remi-7.4
     ```

4. **Beralih antar versi PHP**:
   - Gunakan perintah modul `dnf`:
     ```bash
     sudo dnf module reset php
     sudo dnf module enable php:remi-8.0
     sudo dnf install php
     ```

5. **Verifikasi versi yang diinstal**:
   - Jalankan:
     ```bash
     php -v
     ```

### **Catatan Umum**

- Untuk lingkungan pengembangan, penting untuk mengonfigurasi pengaturan PHP sesuai dengan persyaratan proyek Anda. 
- Saat beralih versi PHP, pastikan semua ekstensi PHP yang relevan diinstal untuk versi spesifik yang ingin Anda gunakan.
- Restart server web Anda (Apache, Nginx, dll.) setelah beralih versi PHP atau memperbarui konfigurasi untuk menerapkan perubahan.