# Migrations

Migrasi untuk proyek Anda menjaga semua perubahan basis data yang terlibat dalam proyek Anda.  
[byjg/php-migration](https://github.com/byjg/php-migration) adalah pustaka inti yang sangat membantu untuk memulai.

## Menginstal

### Pustaka PHP

Jika Anda ingin menggunakan hanya Pustaka PHP di proyek Anda:

```bash
composer require "byjg/migration"
```

### Antarmuka Baris Perintah

Antarmuka baris perintah berdiri sendiri dan tidak memerlukan Anda menginstalnya bersama proyek Anda.

Anda dapat menginstalnya secara global dan membuat tautan simbolis

```bash
composer require "byjg/migration-cli"
```

Silakan kunjungi [byjg/migration-cli](https://github.com/byjg/migration-cli) untuk mendapatkan lebih banyak informasi tentang Migration CLI.

## Basis data yang didukung

| Basis Data      | Driver                                                                          | String Koneksi                                        |
| -------------- | ------------------------------------------------------------------------------- | ---------------------------------------------------- |
| Sqlite         | [pdo_sqlite](https://www.php.net/manual/en/ref.pdo-sqlite.php)                  | sqlite:///path/to/file                               |
| MySql/MariaDb  | [pdo_mysql](https://www.php.net/manual/en/ref.pdo-mysql.php)                    | mysql://username:password@hostname:port/database     |
| Postgres       | [pdo_pgsql](https://www.php.net/manual/en/ref.pdo-pgsql.php)                    | pgsql://username:password@hostname:port/database     |
| Sql Server     | [pdo_dblib, pdo_sysbase](https://www.php.net/manual/en/ref.pdo-dblib.php) Linux | dblib://username:password@hostname:port/database     |
| Sql Server     | [pdo_sqlsrv](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx) Windows   | sqlsrv://username:password@hostname:port/database    |

## Bagaimana Ini Bekerja?

Migrasi Basis Data menggunakan SQL MURNI untuk mengelola versi basis data.  
Untuk dapat berfungsi, Anda perlu:

* Membuat Skrip SQL
* Mengelola menggunakan Baris Perintah atau API.

### Skrip SQL

Skrip dibagi menjadi tiga set skrip:

* Skrip BASIS berisi SEMUA perintah SQL untuk membuat basis data yang baru;
* Skrip UP berisi semua perintah migrasi SQL untuk "naik" versi basis data;
* Skrip DOWN berisi semua perintah migrasi SQL untuk "turun" atau mengembalikan versi basis data;

Direktori skrip adalah:

```text
 <root dir>
     |
     +-- base.sql
     |
     +-- /migrations
              |
              +-- /up
                   |
                   +-- 00001.sql
                   +-- 00002.sql
              +-- /down
                   |
                   +-- 00000.sql
                   +-- 00001.sql
```

* "base.sql" adalah skrip dasar
* Folder "up" berisi skrip untuk migrasi naik versi.
   Sebagai contoh: 00002.sql adalah skrip untuk mengubah basis data dari versi '1' ke '2'.
* Folder "down" berisi skrip untuk migrasi turun versi.
   Sebagai contoh: 00001.sql adalah skrip untuk mengubah basis data dari versi '2' ke '1'.
   Folder "down" adalah opsional.

### Lingkungan Pengembangan Multi

Jika Anda bekerja dengan beberapa pengembang dan beberapa cabang, sulit untuk menentukan nomor berikutnya.

Dalam kasus itu, Anda mempunyai akhiran "-dev" setelah nomor versi.

Lihat skenarionya:

* Pengembang 1 membuat cabang dan versi terbaru misalnya 42.
* Pengembang 2 membuat cabang pada saat yang sama dan memiliki nomor versi basis data yang sama.

Dalam kedua kasus, para pengembang akan membuat file bernama 43-dev.sql. Kedua pengembang akan bermigrasi NAIK dan TURUN tanpa masalah dan versi lokal Anda akan menjadi 43.

Namun pengembang 1 menggabungkan perubahan Anda dan membuat versi akhir 43.sql (`git mv 43-dev.sql 43.sql`). Jika pengembang 2 memperbarui cabang lokal Anda, dia akan memiliki file 43.sql (dari dev 1) dan file Anda 43-dev.sql.  
Jika dia mencoba untuk bermigrasi NAIK atau TURUN, skrip migrasi akan turun dan memberi tahu bahwa terdapat DUA versi 43. Dalam kasus ini, pengembang 2 harus memperbarui file-nya menjadi 44-dev.sql dan melanjutkan bekerja hingga menggabungkan perubahan Anda dan menghasilkan versi akhir.

## Menggunakan API PHP dan Mengintegrasikannya ke dalam Proyek Anda

Penggunaan dasar adalah

* Membuat koneksi objek ConnectionManagement. Untuk informasi lebih lanjut, lihat komponen "byjg/anydataset".
* Membuat objek Migrasi dengan koneksi ini dan folder tempat skrip SQL berada.
* Gunakan perintah yang sesuai untuk "reset", "up", atau "down" skrip migrasi.

Lihat contohnya:

```php
<?php
// Membuat URI Koneksi
// Lihat lebih lanjut: https://github.com/byjg/anydataset#connection-based-on-uri
$connectionUri = new \ByJG\Util\Uri('mysql://migrateuser:migratepwd@localhost/migratedatabase');

// Daftarkan Database atau Basis Data yang dapat menangani URI tersebut:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Membuat instance Migrasi
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Tambahkan fungsi progres callback untuk menerima info dari eksekusi
$migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
    echo "$action, $currentVersion, ${fileInfo['description']}\n";
});

// Mengembalikan basis data menggunakan skrip "base.sql"
// dan menjalankan SEMUA skrip yang ada untuk menaikkan versi basis data ke versi terbaru
$migration->reset();

// Jalankan SEMUA skrip yang ada untuk naik atau turun versi basis data
// dari versi sekarang hingga nomor $version;
// Jika nomor versi tidak ditentukan, migrasi hingga versi basis data terakhir
$migration->update($version = null);
```

Objek Migrasi mengontrol versi basis data.

### Membuat kontrol versi di proyek Anda

```php
<?php
// Daftarkan Database atau Basis Data yang dapat menangani URI tersebut:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Membuat instance Migrasi
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Perintah ini akan membuat tabel versi di basis data Anda
$migration->createVersion();
```

### Mendapatkan versi saat ini

```php
<?php
$migration->getCurrentVersion();
```

### Menambahkan Callback untuk mengontrol progres

```php
<?php
$migration->addCallbackProgress(function ($command, $version, $fileInfo) {
    echo "Melakukan Perintah: $command di versi $version - ${fileInfo['description']}, ${fileInfo['exists']}, ${fileInfo['file']}, ${fileInfo['checksum']}\n";
});
```

### Mendapatkan instance Driver Db

```php
<?php
$migration->getDbDriver();
```

Untuk menggunakannya, silakan kunjungi: [https://github.com/byjg/anydataset-db](https://github.com/byjg/anydataset-db)

### Menghindari Migrasi Parsial (tidak tersedia untuk MySQL)

Migrasi parsial adalah ketika skrip migrasi terhenti di tengah proses karena kesalahan atau penghentian manual.

Tabel migrasi akan memiliki status `partial up` atau `partial down` dan perlu diperbaiki secara manual sebelum dapat bermigrasi lagi. 

Untuk menghindari situasi ini, Anda dapat menentukan migrasi akan dijalankan dalam konteks transaksional.  
Jika skrip migrasi gagal, transaksi akan dibatalkan dan tabel migrasi akan ditandai sebagai `complete` dan versi akan menjadi versi sebelumnya yang segera sebelum skrip yang menyebabkan kesalahan.

Untuk mengaktifkan fitur ini, Anda perlu memanggil metode `withTransactionEnabled` dengan melewatkan `true` sebagai parameter:

```php
<?php
$migration->withTransactionEnabled(true);
```

**CATATAN: Fitur ini tidak tersedia untuk MySQL karena tidak mendukung perintah DDL di dalam transaksi.**  
Jika Anda menggunakan metode ini dengan MySQL, Migrasi akan mengabaikannya tanpa pemberitahuan.  
Info lebih lanjut: [https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html](https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html)

## Tips dalam menulis migrasi SQL untuk Postgres

### Saat membuat trigger dan fungsi SQL

```sql
-- Lakukan
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Periksa bahwa empname dan salary diberikan
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname tidak boleh null'; -- tidak masalah apakah komentar ini kosong atau tidak
        END IF; --
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% tidak dapat memiliki salary null', NEW.empname; --
        END IF; --

        -- Siapa yang bekerja untuk kita ketika mereka harus membayar untuk itu?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% tidak dapat memiliki salary negatif', NEW.empname; --
        END IF; --

        -- Ingat siapa yang mengubah gaji ketika
        NEW.last_date := current_timestamp; --
        NEW.last_user := current_user; --
        RETURN NEW; --
    END; --
$emp_stamp$ LANGUAGE plpgsql;


-- JANGAN
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Periksa bahwa empname dan salary diberikan
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname tidak boleh null';
        END IF;
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% tidak dapat memiliki salary null', NEW.empname;
        END IF;

        -- Siapa yang bekerja untuk kita ketika mereka harus membayar untuk itu?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% tidak dapat memiliki salary negatif', NEW.empname;
        END IF;

        -- Ingat siapa yang mengubah gaji ketika
        NEW.last_date := current_timestamp;
        NEW.last_user := current_user;
        RETURN NEW;
    END;
$emp_stamp$ LANGUAGE plpgsql;
```

Karena lapisan abstraksi basis data `PDO` tidak dapat menjalankan kelompok pernyataan SQL,  
ketika `byjg/migration` membaca file migrasi, itu harus memisahkan seluruh isi file SQL pada titik koma, dan menjalankan pernyataan satu per satu. Namun, ada satu jenis pernyataan yang dapat memiliki beberapa titik koma di antara tubuhnya: fungsi.

Agar dapat mem-parsing fungsi dengan benar, `byjg/migration` 2.1.0 mulai memisahkan file migrasi pada urutan `semicolon + EOL` bukannya hanya titik koma. Dengan cara ini, jika Anda menambahkan komentar kosong setelah setiap titik koma dalam definisi fungsi, `byjg/migration` akan dapat mem-parsingnya.

Sayangnya, jika Anda lupa menambahkan salah satu komentar ini, pustaka akan memisahkan pernyataan `CREATE FUNCTION` menjadi beberapa bagian dan migrasi akan gagal.

### Hindari karakter titik dua (`:`)

```sql
-- Lakukan
CREATE TABLE bookings (
  booking_id UUID PRIMARY KEY,
  booked_at  TIMESTAMPTZ NOT NULL CHECK (CAST(booked_at AS DATE) <= check_in),
  check_in   DATE NOT NULL
);


-- JANGAN
CREATE TABLE bookings (
  booking_id UUID PRIMARY KEY,
  booked_at  TIMESTAMPTZ NOT NULL CHECK (booked_at::DATE <= check_in),
  check_in   DATE NOT NULL
);
```

Karena `PDO` menggunakan karakter titik dua untuk menjelaskan parameter bernama dalam pernyataan yang sudah disiapkan, penggunaannya akan menyebabkan kesalahan dalam konteks lain.

Misalnya, pernyataan PostgreSQL dapat menggunakan `::` untuk mengonversi nilai antar tipe. Di sisi lain, `PDO` akan membaca ini sebagai parameter bernama yang tidak valid dalam konteks yang tidak valid dan gagal ketika mencoba menjalankannya.

Satu-satunya cara untuk memperbaiki ketidakkonsistenan ini adalah dengan menghindari titik dua sama sekali (dalam hal ini, PostgreSQL juga memiliki sintaks alternatif: `CAST(value AS type)`).

### Gunakan editor SQL

Akhirnya, menulis migrasi SQL manual bisa melelahkan, tetapi jauh lebih mudah jika Anda menggunakan editor yang mampu memahami sintaks SQL, menyediakan autocompletion, mengintrospeksi skema basis data Anda saat ini dan/atau memformat kode Anda secara otomatis.

## Menangani berbagai migrasi di dalam satu skema

Jika Anda perlu membuat skrip migrasi yang berbeda dan versi di dalam skema yang sama, itu mungkin  
tetapi terlalu berisiko dan saya **tidak** merekomendasikannya sama sekali.

Untuk melakukan ini, Anda perlu membuat "tabel migrasi" yang berbeda dengan mengoper parameter pada konstruktor.

```php
<?php
$migration = new \ByJG\DbMigration\Migration("db:/uri", "/path", true, "NEW_MIGRATION_TABLE_NAME");
```

Untuk alasan keamanan, fitur ini tidak tersedia di baris perintah, tetapi Anda dapat menggunakan variabel lingkungan  
`MIGRATION_VERSION` untuk menyimpan namanya.

Kami sangat merekomendasikan untuk tidak menggunakan fitur ini. Rekomendasi adalah satu migrasi untuk satu skema.

## Menjalankan Uji Unit

Uji unit dasar dapat dijalankan dengan:

```bash
vendor/bin/phpunit
```

## Menjalankan uji basis data

Menjalankan uji integrasi memerlukan Anda untuk memiliki basis data yang aktif dan berjalan. Kami menyediakan `docker-compose.yml` dasar dan Anda  
dapat menggunakannya untuk memulai basis data untuk pengujian.

### Menjalankan basis data

```bash
docker-compose up -d postgres mysql mssql
```

### Menjalankan uji

```bash
vendor/bin/phpunit
vendor/bin/phpunit tests/SqliteDatabase*
vendor/bin/phpunit tests/MysqlDatabase*
vendor/bin/phpunit tests/PostgresDatabase*
vendor/bin/phpunit tests/SqlServerDblibDatabase*
vendor/bin/phpunit tests/SqlServerSqlsrvDatabase*
```

Opsional Anda dapat mengatur host dan kata sandi yang digunakan oleh uji unit

```bash
export MYSQL_TEST_HOST=localhost     # default ke localhost
export MYSQL_PASSWORD=newpassword    # gunakan '.' jika ingin memiliki kata sandi null
export PSQL_TEST_HOST=localhost      # default ke localhost
export PSQL_PASSWORD=newpassword     # gunakan '.' jika ingin memiliki kata sandi null
export MSSQL_TEST_HOST=localhost     # default ke localhost
export MSSQL_PASSWORD=Pa55word
export SQLITE_TEST_HOST=/tmp/test.db      # default ke /tmp/test.db
```