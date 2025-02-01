# Instalasi

## Unduh berkas

Pastikan Anda memiliki PHP terinstal di sistem Anda. Jika tidak, klik [di sini](#installing-php) untuk petunjuk tentang cara menginstalnya di sistem Anda.

Jika Anda menggunakan [Composer](https://getcomposer.org), Anda dapat menjalankan perintah berikut:

```bash
composer require flightphp/core
```

ATAU Anda dapat [mengunduh berkas](https://github.com/flightphp/core/archive/master.zip) langsung dan mengekstraknya ke direktori web Anda.

## Konfigurasikan Server Web Anda

### Server Pengembangan PHP Bawaan

Ini adalah cara termudah untuk memulai dan menjalankan aplikasi. Anda dapat menggunakan server bawaan untuk menjalankan aplikasi Anda dan bahkan menggunakan SQLite untuk basis data (selama sqlite3 terinstal di sistem Anda) dan tidak memerlukan banyak hal! Jalankan perintah berikut setelah PHP terinstal:

```bash
php -S localhost:8000
```

Kemudian buka browser Anda dan pergi ke `http://localhost:8000`.

Jika Anda ingin membuat akar dokumen proyek Anda ke direktori yang berbeda (Mis: proyek Anda adalah `~/myproject`, tetapi akar dokumen Anda adalah `~/myproject/public/`), Anda dapat menjalankan perintah berikut setelah Anda berada di direktori `~/myproject`:

```bash
php -S localhost:8000 -t public/
```

Kemudian buka browser Anda dan pergi ke `http://localhost:8000`.

### Apache

Pastikan Apache sudah terinstal di sistem Anda. Jika tidak, cari tahu cara menginstal Apache di sistem Anda.

Untuk Apache, edit file `.htaccess` Anda dengan yang berikut:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Catatan**: Jika Anda perlu menggunakan flight di subdirektori, tambahkan baris `RewriteBase /subdir/` tepat setelah `RewriteEngine On`.

> **Catatan**: Jika Anda ingin melindungi semua berkas server, seperti berkas db atau env.
> Tempatkan ini di file `.htaccess` Anda:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

Pastikan Nginx sudah terinstal di sistem Anda. Jika tidak, cari tahu cara menginstal Nginx di sistem Anda.

Untuk Nginx, tambahkan yang berikut ke deklarasi server Anda:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## Buat file `index.php` Anda

```php
<?php

// Jika Anda menggunakan Composer, persyaratan autoloader.
require 'vendor/autoload.php';
// jika Anda tidak menggunakan Composer, muat kerangka kerja secara langsung
// require 'flight/Flight.php';

// Kemudian tetapkan rute dan tetapkan fungsi untuk menangani permintaan.
Flight::route('/', function () {
  echo 'hello world!';
});

// Akhirnya, mulai kerangka kerja.
Flight::start();
```

## Instalasi PHP

Jika Anda sudah memiliki `php` terinstal di sistem Anda, silakan lewati petunjuk ini dan lanjut ke [bagian unduh](#download-the-files)

### **macOS**

#### **Menginstal PHP menggunakan Homebrew**

1. **Instal Homebrew** (jika belum terinstal):
   - Buka Terminal dan jalankan:
     ```bash
     /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
     ```

2. **Instal PHP**:
   - Instal versi terbaru:
     ```bash
     brew install php
     ```
   - Untuk menginstal versi tertentu, misalnya, PHP 8.1:
     ```bash
     brew tap shivammathur/php
     brew install shivammathur/php/php@8.1
     ```

3. **Beralih antara versi PHP**:
   - Lepaskan versi saat ini dan tautkan versi yang diinginkan:
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
   - Kunjungi [PHP untuk Windows](https://windows.php.net/download/) dan unduh versi terbaru atau versi tertentu (misalnya, 7.4, 8.0) sebagai berkas zip yang tidak aman untuk thread.

2. **Ekstrak PHP**:
   - Ekstrak berkas zip yang diunduh ke `C:\php`.

3. **Tambahkan PHP ke PATH sistem**:
   - Masuk ke **Properti Sistem** > **Variabel Lingkungan**.
   - Di bawah **variabel sistem**, cari **Path** dan klik **Edit**.
   - Tambahkan jalur `C:\php` (atau di mana pun Anda mengekstrak PHP).
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

1. **Ulangi langkah di atas** untuk setiap versi, menempatkan masing-masing di direktori terpisah (misalnya, `C:\php7`, `C:\php8`).

2. **Beralih antara versi** dengan menyesuaikan variabel PATH sistem untuk menunjuk ke direktori versi yang diinginkan.

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
   - Untuk menginstal versi tertentu, misalnya, PHP 8.1:
     ```bash
     sudo apt install php8.1
     ```

3. **Instal modul tambahan** (opsional):
   - Misalnya, untuk menginstal dukungan MySQL:
     ```bash
     sudo apt install php8.1-mysql
     ```

4. **Beralih antara versi PHP**:
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

2. **Instal repositori Remi**:
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
   - Untuk menginstal versi tertentu, misalnya, PHP 7.4:
     ```bash
     sudo dnf module install php:remi-7.4
     ```

4. **Beralih antara versi PHP**:
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

- Untuk lingkungan pengembangan, penting untuk mengonfigurasi pengaturan PHP sesuai dengan kebutuhan proyek Anda. 
- Saat beralih antara versi PHP, pastikan semua ekstensi PHP yang relevan terinstal untuk versi tertentu yang ingin Anda gunakan.
- Restart server web Anda (Apache, Nginx, dll.) setelah beralih versi PHP atau memperbarui konfigurasi untuk menerapkan perubahan.