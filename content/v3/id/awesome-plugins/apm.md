# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk mengatur, menggunakan, dan menguasai Pemantauan Kinerja Aplikasi (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan yang lambat atau hanya ingin bersemangat dengan grafik latensi, kami sudah menutupinya. Mari buat aplikasi Anda lebih cepat, pengguna lebih bahagia, dan sesi debugging lebih mudah!

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda seperti restoran yang sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur terganggu, Anda hanya menebak mengapa pelanggan pergi dengan marah. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari permintaan masuk hingga kueri basis data, dan menandai apa saja yang memperlambat Anda. Halaman yang lambat kehilangan pengguna (studi mengatakan 53% mundur jika situs memakan waktu lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah *sebelum* menyakiti. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “mengapa ini rusak?”, lebih banyak kemenangan “lihat betapa licinnya ini berjalan!”.

## Pemasangan

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda memerlukan:
- **PHP 7.4+**: Menjaga kami kompatibel dengan distribusi Linux LTS sambil mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Kerangka ringan yang kami tingkatkan.

## Basis Data yang Didukung

FlightPHP APM saat ini mendukung basis data berikut untuk menyimpan metrik:

- **SQLite3**: Sederhana, berbasis file, dan bagus untuk pengembangan lokal atau aplikasi kecil. Pilihan default dalam sebagian besar pengaturan.
- **MySQL/MariaDB**: Ideal untuk proyek yang lebih besar atau lingkungan produksi di mana Anda memerlukan penyimpanan yang kuat dan skalabel.

Anda dapat memilih tipe basis data selama langkah konfigurasi (lihat di bawah). Pastikan lingkungan PHP Anda memiliki ekstensi yang diperlukan terpasang (misalnya, `pdo_sqlite` atau `pdo_mysql`).

## Memulai

Inilah langkah-demi-langkah untuk kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke dalam `index.php` atau file `services.php` untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory; // Impor untuk membuat logger
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Membuat logger dari konfigurasi
$Apm = new Apm($ApmLogger); // Inisialisasi APM
$Apm->bindEventsToFlightInstance($app); // Mengikat acara ke instance Flight

// Jika Anda menambahkan koneksi basis data
// Harus berupa PdoWrapper atau PdoQueryCapture dari Ekstensi Tracy
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut tentang itu sebentar lagi) dan mengatur logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan acara Flight (permintaan, rute, kesalahan, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengikat semuanya ke aplikasi Flight Anda.

**Tip Pro: Pengambilan Sampel**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani. Gunakan tingkat sampel (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% dari permintaan
```

Ini menjaga performa tetap cepat sambil tetap memberikan data yang solid.

### 2. Konfigurasikan Itu

Jalankan ini untuk membuat `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan di mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
- Default adalah SQLite—misalnya, `sqlite:/tmp/apm_metrics.sqlite` untuk sumber, yang lain untuk tujuan.
- Anda akan mendapatkan konfigurasi seperti:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Proses ini juga akan menanyakan apakah Anda ingin menjalankan migrasi untuk pengaturan ini. Jika Anda mengatur ini untuk pertama kalinya, jawabannya adalah ya.

**Mengapa dua lokasi?**
Metrik mentah menumpuk dengan cepat (pikirkan log yang tidak disaring). Worker memprosesnya menjadi tujuan yang terstruktur untuk dasbor. Menjaga segala sesuatu rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data yang siap dasbor. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke tujuan Anda.
- Berhenti saat selesai atau jika tidak ada metrik yang tersisa.

**Jaga Agar Tetap Berjalan**
Untuk aplikasi langsung, Anda ingin pemrosesan kontinu. Berikut pilihan Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat datang. Bagus untuk dev atau pengaturan kecil.

- **Crontab**:
  Tambahkan ini ke crontab Anda (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Berjalan setiap menit—sempurna untuk produksi.

- **Tmux/Screen**:
  Mulai sesi yang dapat dilepas:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, kemudian D untuk melepaskan; `tmux attach -t apm-worker` untuk menyambung kembali
  ```
  Menjaganya berjalan bahkan jika Anda keluar.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Proses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Berhenti setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dasbor Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dasbor

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menghidupkan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat kesalahan, dan banyak lagi.

**Sesuaikan Itu**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana saja (berguna untuk melihat jarak jauh).
- `--port 8080`: Gunakan port berbeda jika 8001 digunakan.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Kunjungi URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dasbor karena mungkin ada firewall dan langkah keamanan lainnya. Berikut beberapa pilihan:

- **Gunakan Reverse Proxy**: Atur Nginx atau Apache untuk meneruskan permintaan ke dasbor.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk mentunnel dasbor ke mesin lokal Anda.
- **VPN**: Jika server Anda di balik VPN, sambungkan ke sana dan akses dasbor langsung.
- **Konfigurasi Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasi Apache/Nginx**: Jika Anda memiliki server web di depan aplikasi Anda, Anda dapat mengonfigurasikannya ke domain atau subdomain. Jika Anda melakukan ini, Anda akan mengatur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Ingin dasbor yang berbeda?

Anda dapat membangun dasbor Anda sendiri jika Anda mau! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dasbor Anda sendiri!

## Fitur Dasbor

Dasbor adalah markas APM Anda—ini yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan cap waktu, URL, kode respons, dan waktu total. Klik “Detail” untuk middleware, kueri, dan kesalahan.
- **Permintaan Terlambat**: 5 permintaan teratas yang menghabiskan waktu (misalnya, “/api/heavy” pada 2.5s).
- **Rute Terlambat**: 5 rute teratas berdasarkan waktu rata-rata—bagus untuk menemukan pola.
- **Tingkat Kesalahan**: Persentase permintaan yang gagal (misalnya, 2.3% 500s).
- **Persentil Latensi**: 95th (p95) dan 99th (p99) waktu respons—ketahui skenario terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Kueri/Middleware Panjang**: 5 panggilan basis data lambat dan lapisan middleware teratas.
- **Cache Hit/Miss**: Seberapa sering cache menyelamatkan hari Anda.

**Ekstra**:
- Filter berdasarkan “Last Hour,” “Last Day,” atau “Last Week.”
- Alihkan mode gelap untuk sesi malam yang larut.

**Contoh**:
Permintaan ke `/users` mungkin menunjukkan:
- Total Time: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit on `user_list` (5ms)

## Menambahkan Acara Kustom

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent; // Impor untuk acara kustom

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana itu muncul?**
Di detail permintaan dasbor di bawah “Custom Events”—dapat diperluas dengan pemformatan JSON yang bagus.

**Kasus Penggunaan**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Sekarang Anda akan melihat jika API itu menarik aplikasi Anda ke bawah!

## Pemantauan Basis Data

Lacak kueri PDO seperti ini:

```php
use flight\database\PdoWrapper; // Impor untuk wrapper PDO

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang Anda Dapatkan**:
- Teks kueri (misalnya, `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (misalnya, 0.015s)
- Jumlah baris (misalnya, 42)

**Peringatan**:
- **Opsional**: Lewati ini jika Anda tidak perlu pelacakan DB.
- **Hanya PdoWrapper**: PDO inti belum dihubungkan—tetap ikuti!
- **Peringatan Performa**: Mencatat setiap kueri di situs yang berat DB dapat memperlambat hal-hal. Gunakan pengambilan sampel (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Keluaran Contoh**:
- Query: `SELECT name FROM products WHERE price > 100`
- Time: 0.023s
- Rows: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Batas pada 500 metrik—menjaganya terbatas.
- `--batch_size 200`: Proses 200 sekaligus—menyeimbangkan kecepatan dan memori.
- `--daemon`: Berjalan tanpa henti—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## ID Permintaan di Aplikasi

Setiap permintaan memiliki ID permintaan unik untuk pelacakan. Anda dapat menggunakan ID ini di aplikasi Anda untuk menghubungkan log dan metrik. Misalnya, Anda dapat menambahkan ID permintaan ke halaman kesalahan:

```php
Flight::map('error', function($message) {
	// Dapatkan ID permintaan dari header respons X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Selain itu, Anda bisa mengambilnya dari variabel Flight
	// Metode ini tidak akan berfungsi dengan baik di swoole atau platform async lainnya.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Meningkatkan

Jika Anda meningkatkan ke versi APM yang lebih baru, ada kemungkinan migrasi basis data yang perlu dijalankan. Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema basis data ke versi terbaru.

**Catatan:** Jika basis data APM Anda besar, migrasi ini mungkin memakan waktu. Anda mungkin ingin menjalankan perintah ini selama jam non-puncak.

## Menghapus Data Lama

Untuk menjaga basis data Anda rapi, Anda dapat menghapus data lama. Ini sangat berguna jika Anda menjalankan aplikasi yang sibuk dan ingin menjaga ukuran basis data tetap terkelola.
Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data yang lebih lama dari 30 hari dari basis data. Anda dapat menyesuaikan jumlah hari dengan meneruskan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data yang lebih lama dari 7 hari dari basis data.

## Memecahkan Masalah

Tertahan? Coba ini:

- **Tidak Ada Data Dasbor?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Jalur konfigurasi cocok? Verifikasi DSN di `.runway-config.json` menunjuk ke file asli.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik yang tertunda.

- **Kesalahan Worker?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dasbor Tidak Akan Dimulai?**
  - Port 8001 digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan tingkat sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.

- **Tidak Melacak Pengecualian/Kesalahan?**
  - Jika Anda memiliki [Tracy](https://tracy.nette.org/) diaktifkan untuk proyek Anda, itu akan menimpa penanganan kesalahan Flight. Anda perlu menonaktifkan Tracy dan pastikan `Flight::set('flight.handle_errors', true);` diatur.

- **Tidak Melacak Kueri Basis Data?**
  - Pastikan Anda menggunakan `PdoWrapper` untuk koneksi basis data Anda.
  - Pastikan Anda membuat argumen terakhir di konstruktor `true`.# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk mengatur, menggunakan, dan menguasai Pemantauan Kinerja Aplikasi (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan yang lambat atau hanya ingin bersemangat dengan grafik latensi, kami sudah menutupinya. Mari buat aplikasi Anda lebih cepat, pengguna lebih bahagia, dan sesi debugging lebih mudah!

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda seperti restoran yang sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur terganggu, Anda hanya menebak mengapa pelanggan pergi dengan marah. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari permintaan masuk hingga kueri basis data, dan menandai apa saja yang memperlambat Anda. Halaman yang lambat kehilangan pengguna (studi mengatakan 53% mundur jika situs memakan waktu lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah *sebelum* menyakiti. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “mengapa ini rusak?”, lebih banyak kemenangan “lihat betapa licinnya ini berjalan!”.

## Pemasangan

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda memerlukan:
- **PHP 7.4+**: Menjaga kami kompatibel dengan distribusi Linux LTS sambil mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Kerangka ringan yang kami tingkatkan.

## Basis Data yang Didukung

FlightPHP APM saat ini mendukung basis data berikut untuk menyimpan metrik:

- **SQLite3**: Sederhana, berbasis file, dan bagus untuk pengembangan lokal atau aplikasi kecil. Pilihan default dalam sebagian besar pengaturan.
- **MySQL/MariaDB**: Ideal untuk proyek yang lebih besar atau lingkungan produksi di mana Anda memerlukan penyimpanan yang kuat dan skalabel.

Anda dapat memilih tipe basis data selama langkah konfigurasi (lihat di bawah). Pastikan lingkungan PHP Anda memiliki ekstensi yang diperlukan terpasang (misalnya, `pdo_sqlite` atau `pdo_mysql`).

## Memulai

Inilah langkah-demi-langkah untuk kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke dalam `index.php` atau file `services.php` untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory; // Impor untuk membuat logger
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Membuat logger dari konfigurasi
$Apm = new Apm($ApmLogger); // Inisialisasi APM
$Apm->bindEventsToFlightInstance($app); // Mengikat acara ke instance Flight

// Jika Anda menambahkan koneksi basis data
// Harus berupa PdoWrapper atau PdoQueryCapture dari Ekstensi Tracy
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut tentang itu sebentar lagi) dan mengatur logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan acara Flight (permintaan, rute, kesalahan, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengikat semuanya ke aplikasi Flight Anda.

**Tip Pro: Pengambilan Sampel**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani. Gunakan tingkat sampel (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% dari permintaan
```

Ini menjaga performa tetap cepat sambil tetap memberikan data yang solid.

### 2. Konfigurasikan Itu

Jalankan ini untuk membuat `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan di mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
- Default adalah SQLite—misalnya, `sqlite:/tmp/apm_metrics.sqlite` untuk sumber, yang lain untuk tujuan.
- Anda akan mendapatkan konfigurasi seperti:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Proses ini juga akan menanyakan apakah Anda ingin menjalankan migrasi untuk pengaturan ini. Jika Anda mengatur ini untuk pertama kalinya, jawabannya adalah ya.

**Mengapa dua lokasi?**
Metrik mentah menumpuk dengan cepat (pikirkan log yang tidak disaring). Worker memprosesnya menjadi tujuan yang terstruktur untuk dasbor. Menjaga segala sesuatu rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data yang siap dasbor. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke tujuan Anda.
- Berhenti saat selesai atau jika tidak ada metrik yang tersisa.

**Jaga Agar Tetap Berjalan**
Untuk aplikasi langsung, Anda ingin pemrosesan kontinu. Berikut pilihan Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat datang. Bagus untuk dev atau pengaturan kecil.

- **Crontab**:
  Tambahkan ini ke crontab Anda (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Berjalan setiap menit—sempurna untuk produksi.

- **Tmux/Screen**:
  Mulai sesi yang dapat dilepas:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, kemudian D untuk melepaskan; `tmux attach -t apm-worker` untuk menyambung kembali
  ```
  Menjaganya berjalan bahkan jika Anda keluar.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Proses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Berhenti setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dasbor Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dasbor

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menghidupkan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat kesalahan, dan banyak lagi.

**Sesuaikan Itu**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana saja (berguna untuk melihat jarak jauh).
- `--port 8080`: Gunakan port berbeda jika 8001 digunakan.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Kunjungi URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dasbor karena mungkin ada firewall dan langkah keamanan lainnya. Berikut beberapa pilihan:

- **Gunakan Reverse Proxy**: Atur Nginx atau Apache untuk meneruskan permintaan ke dasbor.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk mentunnel dasbor ke mesin lokal Anda.
- **VPN**: Jika server Anda di balik VPN, sambungkan ke sana dan akses dasbor langsung.
- **Konfigurasi Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasi Apache/Nginx**: Jika Anda memiliki server web di depan aplikasi Anda, Anda dapat mengonfigurasikannya ke domain atau subdomain. Jika Anda melakukan ini, Anda akan mengatur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Ingin dasbor yang berbeda?

Anda dapat membangun dasbor Anda sendiri jika Anda mau! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dasbor Anda sendiri!

## Fitur Dasbor

Dasbor adalah markas APM Anda—ini yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan cap waktu, URL, kode respons, dan waktu total. Klik “Detail” untuk middleware, kueri, dan kesalahan.
- **Permintaan Terlambat**: 5 permintaan teratas yang menghabiskan waktu (misalnya, “/api/heavy” pada 2.5s).
- **Rute Terlambat**: 5 rute teratas berdasarkan waktu rata-rata—bagus untuk menemukan pola.
- **Tingkat Kesalahan**: Persentase permintaan yang gagal (misalnya, 2.3% 500s).
- **Persentil Latensi**: 95th (p95) dan 99th (p99) waktu respons—ketahui skenario terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Kueri/Middleware Panjang**: 5 panggilan basis data lambat dan lapisan middleware teratas.
- **Cache Hit/Miss**: Seberapa sering cache menyelamatkan hari Anda.

**Ekstra**:
- Filter berdasarkan “Last Hour,” “Last Day,” atau “Last Week.”
- Alihkan mode gelap untuk sesi malam yang larut.

**Contoh**:
Permintaan ke `/users` mungkin menunjukkan:
- Total Time: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit on `user_list` (5ms)

## Menambahkan Acara Kustom

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent; // Impor untuk acara kustom

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana itu muncul?**
Di detail permintaan dasbor di bawah “Custom Events”—dapat diperluas dengan pemformatan JSON yang bagus.

**Kasus Penggunaan**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Sekarang Anda akan melihat jika API itu menarik aplikasi Anda ke bawah!

## Pemantauan Basis Data

Lacak kueri PDO seperti ini:

```php
use flight\database\PdoWrapper; // Impor untuk wrapper PDO

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang Anda Dapatkan**:
- Teks kueri (misalnya, `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (misalnya, 0.015s)
- Jumlah baris (misalnya, 42)

**Peringatan**:
- **Opsional**: Lewati ini jika Anda tidak perlu pelacakan DB.
- **Hanya PdoWrapper**: PDO inti belum dihubungkan—tetap ikuti!
- **Peringatan Performa**: Mencatat setiap kueri di situs yang berat DB dapat memperlambat hal-hal. Gunakan pengambilan sampel (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Keluaran Contoh**:
- Query: `SELECT name FROM products WHERE price > 100`
- Time: 0.023s
- Rows: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Batas pada 500 metrik—menjaganya terbatas.
- `--batch_size 200`: Proses 200 sekaligus—menyeimbangkan kecepatan dan memori.
- `--daemon`: Berjalan tanpa henti—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## ID Permintaan di Aplikasi

Setiap permintaan memiliki ID permintaan unik untuk pelacakan. Anda dapat menggunakan ID ini di aplikasi Anda untuk menghubungkan log dan metrik. Misalnya, Anda dapat menambahkan ID permintaan ke halaman kesalahan:

```php
Flight::map('error', function($message) {
	// Dapatkan ID permintaan dari header respons X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Selain itu, Anda bisa mengambilnya dari variabel Flight
	// Metode ini tidak akan berfungsi dengan baik di swoole atau platform async lainnya.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Meningkatkan

Jika Anda meningkatkan ke versi APM yang lebih baru, ada kemungkinan migrasi basis data yang perlu dijalankan. Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema basis data ke versi terbaru.

**Catatan:** Jika basis data APM Anda besar, migrasi ini mungkin memakan waktu. Anda mungkin ingin menjalankan perintah ini selama jam non-puncak.

## Menghapus Data Lama

Untuk menjaga basis data Anda rapi, Anda dapat menghapus data lama. Ini sangat berguna jika Anda menjalankan aplikasi yang sibuk dan ingin menjaga ukuran basis data tetap terkelola.
Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data yang lebih lama dari 30 hari dari basis data. Anda dapat menyesuaikan jumlah hari dengan meneruskan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data yang lebih lama dari 7 hari dari basis data.

## Memecahkan Masalah

Tertahan? Coba ini:

- **Tidak Ada Data Dasbor?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Jalur konfigurasi cocok? Verifikasi DSN di `.runway-config.json` menunjuk ke file asli.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik yang tertunda.

- **Kesalahan Worker?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dasbor Tidak Akan Dimulai?**
  - Port 8001 digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan tingkat sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.

- **Tidak Melacak Pengecualian/Kesalahan?**
  - Jika Anda memiliki [Tracy](https://tracy.nette.org/) diaktifkan untuk proyek Anda, itu akan menimpa penanganan kesalahan Flight. Anda perlu menonaktifkan Tracy dan pastikan `Flight::set('flight.handle_errors', true);` diatur.

- **Tidak Melacak Kueri Basis Data?**
  - Pastikan Anda menggunakan `PdoWrapper` untuk koneksi basis data Anda.
  - Pastikan Anda membuat argumen terakhir di konstruktor `true`.# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk mengatur, menggunakan, dan menguasai Pemantauan Kinerja Aplikasi (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan yang lambat atau hanya ingin bersemangat dengan grafik latensi, kami sudah menutupinya. Mari buat aplikasi Anda lebih cepat, pengguna lebih bahagia, dan sesi debugging lebih mudah!

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda seperti restoran yang sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur terganggu, Anda hanya menebak mengapa pelanggan pergi dengan marah. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari permintaan masuk hingga kueri basis data, dan menandai apa saja yang memperlambat Anda. Halaman yang lambat kehilangan pengguna (studi mengatakan 53% mundur jika situs memakan waktu lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah *sebelum* menyakiti. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “mengapa ini rusak?”, lebih banyak kemenangan “lihat betapa licinnya ini berjalan!”.

## Pemasangan

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda memerlukan:
- **PHP 7.4+**: Menjaga kami kompatibel dengan distribusi Linux LTS sambil mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Kerangka ringan yang kami tingkatkan.

## Basis Data yang Didukung

FlightPHP APM saat ini mendukung basis data berikut untuk menyimpan metrik:

- **SQLite3**: Sederhana, berbasis file, dan bagus untuk pengembangan lokal atau aplikasi kecil. Pilihan default dalam sebagian besar pengaturan.
- **MySQL/MariaDB**: Ideal untuk proyek yang lebih besar atau lingkungan produksi di mana Anda memerlukan penyimpanan yang kuat dan skalabel.

Anda dapat memilih tipe basis data selama langkah konfigurasi (lihat di bawah). Pastikan lingkungan PHP Anda memiliki ekstensi yang diperlukan terpasang (misalnya, `pdo_sqlite` atau `pdo_mysql`).

## Memulai

Inilah langkah-demi-langkah untuk kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke dalam `index.php` atau file `services.php` untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory; // Impor untuk membuat logger
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Membuat logger dari konfigurasi
$Apm = new Apm($ApmLogger); // Inisialisasi APM
$Apm->bindEventsToFlightInstance($app); // Mengikat acara ke instance Flight

// Jika Anda menambahkan koneksi basis data
// Harus berupa PdoWrapper atau PdoQueryCapture dari Ekstensi Tracy
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut tentang itu sebentar lagi) dan mengatur logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan acara Flight (permintaan, rute, kesalahan, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengikat semuanya ke aplikasi Flight Anda.

**Tip Pro: Pengambilan Sampel**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani. Gunakan tingkat sampel (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% dari permintaan
```

Ini menjaga performa tetap cepat sambil tetap memberikan data yang solid.

### 2. Konfigurasikan Itu

Jalankan ini untuk membuat `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan di mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
- Default adalah SQLite—misalnya, `sqlite:/tmp/apm_metrics.sqlite` untuk sumber, yang lain untuk tujuan.
- Anda akan mendapatkan konfigurasi seperti:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Proses ini juga akan menanyakan apakah Anda ingin menjalankan migrasi untuk pengaturan ini. Jika Anda mengatur ini untuk pertama kalinya, jawabannya adalah ya.

**Mengapa dua lokasi?**
Metrik mentah menumpuk dengan cepat (pikirkan log yang tidak disaring). Worker memprosesnya menjadi tujuan yang terstruktur untuk dasbor. Menjaga segala sesuatu rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data yang siap dasbor. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke tujuan Anda.
- Berhenti saat selesai atau jika tidak ada metrik yang tersisa.

**Jaga Agar Tetap Berjalan**
Untuk aplikasi langsung, Anda ingin pemrosesan kontinu. Berikut pilihan Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat datang. Bagus untuk dev atau pengaturan kecil.

- **Crontab**:
  Tambahkan ini ke crontab Anda (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Berjalan setiap menit—sempurna untuk produksi.

- **Tmux/Screen**:
  Mulai sesi yang dapat dilepas:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, kemudian D untuk melepaskan; `tmux attach -t apm-worker` untuk menyambung kembali
  ```
  Menjaganya berjalan bahkan jika Anda keluar.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Proses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Berhenti setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dasbor Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dasbor

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menghidupkan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat kesalahan, dan banyak lagi.

**Sesuaikan Itu**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana saja (berguna untuk melihat jarak jauh).
- `--port 8080`: Gunakan port berbeda jika 8001 digunakan.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Kunjungi URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dasbor karena mungkin ada firewall dan langkah keamanan lainnya. Berikut beberapa pilihan:

- **Gunakan Reverse Proxy**: Atur Nginx atau Apache untuk meneruskan permintaan ke dasbor.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk mentunnel dasbor ke mesin lokal Anda.
- **VPN**: Jika server Anda di balik VPN, sambungkan ke sana dan akses dasbor langsung.
- **Konfigurasi Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasi Apache/Nginx**: Jika Anda memiliki server web di depan aplikasi Anda, Anda dapat mengonfigurasikannya ke domain atau subdomain. Jika Anda melakukan ini, Anda akan mengatur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Ingin dasbor yang berbeda?

Anda dapat membangun dasbor Anda sendiri jika Anda mau! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dasbor Anda sendiri!

## Fitur Dasbor

Dasbor adalah markas APM Anda—ini yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan cap waktu, URL, kode respons, dan waktu total. Klik “Detail” untuk middleware, kueri, dan kesalahan.
- **Permintaan Terlambat**: 5 permintaan teratas yang menghabiskan waktu (misalnya, “/api/heavy” pada 2.5s).
- **Rute Terlambat**: 5 rute teratas berdasarkan waktu rata-rata—bagus untuk menemukan pola.
- **Tingkat Kesalahan**: Persentase permintaan yang gagal (misalnya, 2.3% 500s).
- **Persentil Latensi**: 95th (p95) dan 99th (p99) waktu respons—ketahui skenario terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Kueri/Middleware Panjang**: 5 panggilan basis data lambat dan lapisan middleware teratas.
- **Cache Hit/Miss**: Seberapa sering cache menyelamatkan hari Anda.

**Ekstra**:
- Filter berdasarkan “Last Hour,” “Last Day,” atau “Last Week.”
- Alihkan mode gelap untuk sesi malam yang larut.

**Contoh**:
Permintaan ke `/users` mungkin menunjukkan:
- Total Time: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit on `user_list` (5ms)

## Menambahkan Acara Kustom

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent; // Impor untuk acara kustom

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana itu muncul?**
Di detail permintaan dasbor di bawah “Custom Events”—dapat diperluas dengan pemformatan JSON yang bagus.

**Kasus Penggunaan**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Sekarang Anda akan melihat jika API itu menarik aplikasi Anda ke bawah!

## Pemantauan Basis Data

Lacak kueri PDO seperti ini:

```php
use flight\database\PdoWrapper; // Impor untuk wrapper PDO

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang Anda Dapatkan**:
- Teks kueri (misalnya, `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (misalnya, 0.015s)
- Jumlah baris (misalnya, 42)

**Peringatan**:
- **Opsional**: Lewati ini jika Anda tidak perlu pelacakan DB.
- **Hanya PdoWrapper**: PDO inti belum dihubungkan—tetap ikuti!
- **Peringatan Performa**: Mencatat setiap kueri di situs yang berat DB dapat memperlambat hal-hal. Gunakan pengambilan sampel (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Keluaran Contoh**:
- Query: `SELECT name FROM products WHERE price > 100`
- Time: 0.023s
- Rows: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Batas pada 500 metrik—menjaganya terbatas.
- `--batch_size 200`: Proses 200 sekaligus—menyeimbangkan kecepatan dan memori.
- `--daemon`: Berjalan tanpa henti—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## ID Permintaan di Aplikasi

Setiap permintaan memiliki ID permintaan unik untuk pelacakan. Anda dapat menggunakan ID ini di aplikasi Anda untuk menghubungkan log dan metrik. Misalnya, Anda dapat menambahkan ID permintaan ke halaman kesalahan:

```php
Flight::map('error', function($message) {
	// Dapatkan ID permintaan dari header respons X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Selain itu, Anda bisa mengambilnya dari variabel Flight
	// Metode ini tidak akan berfungsi dengan baik di swoole atau platform async lainnya.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Meningkatkan

Jika Anda meningkatkan ke versi APM yang lebih baru, ada kemungkinan migrasi basis data yang perlu dijalankan. Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema basis data ke versi terbaru.

**Catatan:** Jika basis data APM Anda besar, migrasi ini mungkin memakan waktu. Anda mungkin ingin menjalankan perintah ini selama jam non-puncak.

## Menghapus Data Lama

Untuk menjaga basis data Anda rapi, Anda dapat menghapus data lama. Ini sangat berguna jika Anda menjalankan aplikasi yang sibuk dan ingin menjaga ukuran basis data tetap terkelola.
Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data yang lebih lama dari 30 hari dari basis data. Anda dapat menyesuaikan jumlah hari dengan meneruskan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data yang lebih lama dari 7 hari dari basis data.

## Memecahkan Masalah

Tertahan? Coba ini:

- **Tidak Ada Data Dasbor?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Jalur konfigurasi cocok? Verifikasi DSN di `.runway-config.json` menunjuk ke file asli.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik yang tertunda.

- **Kesalahan Worker?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dasbor Tidak Akan Dimulai?**
  - Port 8001 digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan tingkat sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.

- **Tidak Melacak Pengecualian/Kesalahan?**
  - Jika Anda memiliki [Tracy](https://tracy.nette.org/) diaktifkan untuk proyek Anda, itu akan menimpa penanganan kesalahan Flight. Anda perlu menonaktifkan Tracy dan pastikan `Flight::set('flight.handle_errors', true);` diatur.

- **Tidak Melacak Kueri Basis Data?**
  - Pastikan Anda menggunakan `PdoWrapper` untuk koneksi basis data Anda.
  - Pastikan Anda membuat argumen terakhir di konstruktor `true`.# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk mengatur, menggunakan, dan menguasai Pemantauan Kinerja Aplikasi (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan yang lambat atau hanya ingin bersemangat dengan grafik latensi, kami sudah menutupinya. Mari buat aplikasi Anda lebih cepat, pengguna lebih bahagia, dan sesi debugging lebih mudah!

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda seperti restoran yang sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur terganggu, Anda hanya menebak mengapa pelanggan pergi dengan marah. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari permintaan masuk hingga kueri basis data, dan menandai apa saja yang memperlambat Anda. Halaman yang lambat kehilangan pengguna (studi mengatakan 53% mundur jika situs memakan waktu lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah *sebelum* menyakiti. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “mengapa ini rusak?”, lebih banyak kemenangan “lihat betapa licinnya ini berjalan!”.

## Pemasangan

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda memerlukan:
- **PHP 7.4+**: Menjaga kami kompatibel dengan distribusi Linux LTS sambil mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Kerangka ringan yang kami tingkatkan.

## Basis Data yang Didukung

FlightPHP APM saat ini mendukung basis data berikut untuk menyimpan metrik:

- **SQLite3**: Sederhana, berbasis file, dan bagus untuk pengembangan lokal atau aplikasi kecil. Pilihan default dalam sebagian besar pengaturan.
- **MySQL/MariaDB**: Ideal untuk proyek yang lebih besar atau lingkungan produksi di mana Anda memerlukan penyimpanan yang kuat dan skalabel.

Anda dapat memilih tipe basis data selama langkah konfigurasi (lihat di bawah). Pastikan lingkungan PHP Anda memiliki ekstensi yang diperlukan terpasang (misalnya, `pdo_sqlite` atau `pdo_mysql`).

## Memulai

Inilah langkah-demi-langkah untuk kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke dalam `index.php` atau file `services.php` untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory; // Impor untuk membuat logger
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Membuat logger dari konfigurasi
$Apm = new Apm($ApmLogger); // Inisialisasi APM
$Apm->bindEventsToFlightInstance($app); // Mengikat acara ke instance Flight

// Jika Anda menambahkan koneksi basis data
// Harus berupa PdoWrapper atau PdoQueryCapture dari Ekstensi Tracy
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut tentang itu sebentar lagi) dan mengatur logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan acara Flight (permintaan, rute, kesalahan, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengikat semuanya ke aplikasi Flight Anda.

**Tip Pro: Pengambilan Sampel**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani. Gunakan tingkat sampel (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% dari permintaan
```

Ini menjaga performa tetap cepat sambil tetap memberikan data yang solid.

### 2. Konfigurasikan Itu

Jalankan ini untuk membuat `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan di mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
- Default adalah SQLite—misalnya, `sqlite:/tmp/apm_metrics.sqlite` untuk sumber, yang lain untuk tujuan.
- Anda akan mendapatkan konfigurasi seperti:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Proses ini juga akan menanyakan apakah Anda ingin menjalankan migrasi untuk pengaturan ini. Jika Anda mengatur ini untuk pertama kalinya, jawabannya adalah ya.

**Mengapa dua lokasi?**
Metrik mentah menumpuk dengan cepat (pikirkan log yang tidak disaring). Worker memprosesnya menjadi tujuan yang terstruktur untuk dasbor. Menjaga segala sesuatu rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data yang siap dasbor. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke tujuan Anda.
- Berhenti saat selesai atau jika tidak ada metrik yang tersisa.

**Jaga Agar Tetap Berjalan**
Untuk aplikasi langsung, Anda ingin pemrosesan kontinu. Berikut pilihan Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat datang. Bagus untuk dev atau pengaturan kecil.

- **Crontab**:
  Tambahkan ini ke crontab Anda (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Berjalan setiap menit—sempurna untuk produksi.

- **Tmux/Screen**:
  Mulai sesi yang dapat dilepas:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, kemudian D untuk melepaskan; `tmux attach -t apm-worker` untuk menyambung kembali
  ```
  Menjaganya berjalan bahkan jika Anda keluar.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Proses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Berhenti setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dasbor Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dasbor

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menghidupkan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat kesalahan, dan banyak lagi.

**Sesuaikan Itu**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana saja (berguna untuk melihat jarak jauh).
- `--port 8080`: Gunakan port berbeda jika 8001 digunakan.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Kunjungi URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dasbor karena mungkin ada firewall dan langkah keamanan lainnya. Berikut beberapa pilihan:

- **Gunakan Reverse Proxy**: Atur Nginx atau Apache untuk meneruskan permintaan ke dasbor.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk mentunnel dasbor ke mesin lokal Anda.
- **VPN**: Jika server Anda di balik VPN, sambungkan ke sana dan akses dasbor langsung.
- **Konfigurasi Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasi Apache/Nginx**: Jika Anda memiliki server web di depan aplikasi Anda, Anda dapat mengonfigurasikannya ke domain atau subdomain. Jika Anda melakukan ini, Anda akan mengatur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Ingin dasbor yang berbeda?

Anda dapat membangun dasbor Anda sendiri jika Anda mau! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dasbor Anda sendiri!

## Fitur Dasbor

Dasbor adalah markas APM Anda—ini yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan cap waktu, URL, kode respons, dan waktu total. Klik “Detail” untuk middleware, kueri, dan kesalahan.
- **Permintaan Terlambat**: 5 permintaan teratas yang menghabiskan waktu (misalnya, “/api/heavy” pada 2.5s).
- **Rute Terlambat**: 5 rute teratas berdasarkan waktu rata-rata—bagus untuk menemukan pola.
- **Tingkat Kesalahan**: Persentase permintaan yang gagal (misalnya, 2.3% 500s).
- **Persentil Latensi**: 95th (p95) dan 99th (p99) waktu respons—ketahui skenario terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Kueri/Middleware Panjang**: 5 panggilan basis data lambat dan lapisan middleware teratas.
- **Cache Hit/Miss**: Seberapa sering cache menyelamatkan hari Anda.

**Ekstra**:
- Filter berdasarkan “Last Hour,” “Last Day,” atau “Last Week.”
- Alihkan mode gelap untuk sesi malam yang larut.

**Contoh**:
Permintaan ke `/users` mungkin menunjukkan:
- Total Time: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit on `user_list` (5ms)

## Menambahkan Acara Kustom

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent; // Impor untuk acara kustom

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana itu muncul?**
Di detail permintaan dasbor di bawah “Custom Events”—dapat diperluas dengan pemformatan JSON yang bagus.

**Kasus Penggunaan**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Sekarang Anda akan melihat jika API itu menarik aplikasi Anda ke bawah!

## Pemantauan Basis Data

Lacak kueri PDO seperti ini:

```php
use flight\database\PdoWrapper; // Impor untuk wrapper PDO

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang Anda Dapatkan**:
- Teks kueri (misalnya, `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (misalnya, 0.015s)
- Jumlah baris (misalnya, 42)

**Peringatan**:
- **Opsional**: Lewati ini jika Anda tidak perlu pelacakan DB.
- **Hanya PdoWrapper**: PDO inti belum dihubungkan—tetap ikuti!
- **Peringatan Performa**: Mencatat setiap kueri di situs yang berat DB dapat memperlambat hal-hal. Gunakan pengambilan sampel (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Keluaran Contoh**:
- Query: `SELECT name FROM products WHERE price > 100`
- Time: 0.023s
- Rows: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Batas pada 500 metrik—menjaganya terbatas.
- `--batch_size 200`: Proses 200 sekaligus—menyeimbangkan kecepatan dan memori.
- `--daemon`: Berjalan tanpa henti—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## ID Permintaan di Aplikasi

Setiap permintaan memiliki ID permintaan unik untuk pelacakan. Anda dapat menggunakan ID ini di aplikasi Anda untuk menghubungkan log dan metrik. Misalnya, Anda dapat menambahkan ID permintaan ke halaman kesalahan:

```php
Flight::map('error', function($message) {
	// Dapatkan ID permintaan dari header respons X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Selain itu, Anda bisa mengambilnya dari variabel Flight
	// Metode ini tidak akan berfungsi dengan baik di swoole atau platform async lainnya.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Meningkatkan

Jika Anda meningkatkan ke versi APM yang lebih baru, ada kemungkinan migrasi basis data yang perlu dijalankan. Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema basis data ke versi terbaru.

**Catatan:** Jika basis data APM Anda besar, migrasi ini mungkin memakan waktu. Anda mungkin ingin menjalankan perintah ini selama jam non-puncak.

## Menghapus Data Lama

Untuk menjaga basis data Anda rapi, Anda dapat menghapus data lama. Ini sangat berguna jika Anda menjalankan aplikasi yang sibuk dan ingin menjaga ukuran basis data tetap terkelola.
Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data yang lebih lama dari 30 hari dari basis data. Anda dapat menyesuaikan jumlah hari dengan meneruskan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data yang lebih lama dari 7 hari dari basis data.

## Memecahkan Masalah

Tertahan? Coba ini:

- **Tidak Ada Data Dasbor?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Jalur konfigurasi cocok? Verifikasi DSN di `.runway-config.json` menunjuk ke file asli.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik yang tertunda.

- **Kesalahan Worker?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dasbor Tidak Akan Dimulai?**
  - Port 8001 digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan tingkat sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.

- **Tidak Melacak Pengecualian/Kesalahan?**
  - Jika Anda memiliki [Tracy](https://tracy.nette.org/) diaktifkan untuk proyek Anda, itu akan menimpa penanganan kesalahan Flight. Anda perlu menonaktifkan Tracy dan pastikan `Flight::set('flight.handle_errors', true);` diatur.

- **Tidak Melacak Kueri Basis Data?**
  - Pastikan Anda menggunakan `PdoWrapper` untuk koneksi basis data Anda.
  - Pastikan Anda membuat argumen terakhir di konstruktor `true`.# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk mengatur, menggunakan, dan menguasai Pemantauan Kinerja Aplikasi (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan yang lambat atau hanya ingin bersemangat dengan grafik latensi, kami sudah menutupinya. Mari buat aplikasi Anda lebih cepat, pengguna lebih bahagia, dan sesi debugging lebih mudah!

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda seperti restoran yang sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur terganggu, Anda hanya menebak mengapa pelanggan pergi dengan marah. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari permintaan masuk hingga kueri basis data, dan menandai apa saja yang memperlambat Anda. Halaman yang lambat kehilangan pengguna (studi mengatakan 53% mundur jika situs memakan waktu lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah *sebelum* menyakiti. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “mengapa ini rusak?”, lebih banyak kemenangan “lihat betapa licinnya ini berjalan!”.

## Pemasangan

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda memerlukan:
- **PHP 7.4+**: Menjaga kami kompatibel dengan distribusi Linux LTS sambil mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Kerangka ringan yang kami tingkatkan.

## Basis Data yang Didukung

FlightPHP APM saat ini mendukung basis data berikut untuk menyimpan metrik:

- **SQLite3**: Sederhana, berbasis file, dan bagus untuk pengembangan lokal atau aplikasi kecil. Pilihan default dalam sebagian besar pengaturan.
- **MySQL/MariaDB**: Ideal untuk proyek yang lebih besar atau lingkungan produksi di mana Anda memerlukan penyimpanan yang kuat dan skalabel.

Anda dapat memilih tipe basis data selama langkah konfigurasi (lihat di bawah). Pastikan lingkungan PHP Anda memiliki ekstensi yang diperlukan terpasang (misalnya, `pdo_sqlite` atau `pdo_mysql`).

## Memulai

Inilah langkah-demi-langkah untuk kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke dalam `index.php` atau file `services.php` untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory; // Impor untuk membuat logger
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Membuat logger dari konfigurasi
$Apm = new Apm($ApmLogger); // Inisialisasi APM
$Apm->bindEventsToFlightInstance($app); // Mengikat acara ke instance Flight

// Jika Anda menambahkan koneksi basis data
// Harus berupa PdoWrapper atau PdoQueryCapture dari Ekstensi Tracy
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut tentang itu sebentar lagi) dan mengatur logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan acara Flight (permintaan, rute, kesalahan, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengikat semuanya ke aplikasi Flight Anda.

**Tip Pro: Pengambilan Sampel**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani. Gunakan tingkat sampel (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% dari permintaan
```

Ini menjaga performa tetap cepat sambil tetap memberikan data yang solid.

### 2. Konfigurasikan Itu

Jalankan ini untuk membuat `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan di mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
- Default adalah SQLite—misalnya, `sqlite:/tmp/apm_metrics.sqlite` untuk sumber, yang lain untuk tujuan.
- Anda akan mendapatkan konfigurasi seperti:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Proses ini juga akan menanyakan apakah Anda ingin menjalankan migrasi untuk pengaturan ini. Jika Anda mengatur ini untuk pertama kalinya, jawabannya adalah ya.

**Mengapa dua lokasi?**
Metrik mentah menumpuk dengan cepat (pikirkan log yang tidak disaring). Worker memprosesnya menjadi tujuan yang terstruktur untuk dasbor. Menjaga segala sesuatu rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data yang siap dasbor. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke tujuan Anda.
- Berhenti saat selesai atau jika tidak ada metrik yang tersisa.

**Jaga Agar Tetap Berjalan**
Untuk aplikasi langsung, Anda ingin pemrosesan kontinu. Berikut pilihan Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat datang. Bagus untuk dev atau pengaturan kecil.

- **Crontab**:
  Tambahkan ini ke crontab Anda (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Berjalan setiap menit—sempurna untuk produksi.

- **Tmux/Screen**:
  Mulai sesi yang dapat dilepas:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, kemudian D untuk melepaskan; `tmux attach -t apm-worker` untuk menyambung kembali
  ```
  Menjaganya berjalan bahkan jika Anda keluar.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Proses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Berhenti setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dasbor Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dasbor

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menghidupkan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat kesalahan, dan banyak lagi.

**Sesuaikan Itu**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana saja (berguna untuk melihat jarak jauh).
- `--port 8080`: Gunakan port berbeda jika 8001 digunakan.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Kunjungi URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dasbor karena mungkin ada firewall dan langkah keamanan lainnya. Berikut beberapa pilihan:

- **Gunakan Reverse Proxy**: Atur Nginx atau Apache untuk meneruskan permintaan ke dasbor.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk mentunnel dasbor ke mesin lokal Anda.
- **VPN**: Jika server Anda di balik VPN, sambungkan ke sana dan akses dasbor langsung.
- **Konfigurasi Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasi Apache/Nginx**: Jika Anda memiliki server web di depan aplikasi Anda, Anda dapat mengonfigurasikannya ke domain atau subdomain. Jika Anda melakukan ini, Anda akan mengatur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Ingin dasbor yang berbeda?

Anda dapat membangun dasbor Anda sendiri jika Anda mau! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dasbor Anda sendiri!

## Fitur Dasbor

Dasbor adalah markas APM Anda—ini yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan cap waktu, URL, kode respons, dan waktu total. Klik “Detail” untuk middleware, kueri, dan kesalahan.
- **Permintaan Terlambat**: 5 permintaan teratas yang menghabiskan waktu (misalnya, “/api/heavy” pada 2.5s).
- **Rute Terlambat**: 5 rute teratas berdasarkan waktu rata-rata—bagus untuk menemukan pola.
- **Tingkat Kesalahan**: Persentase permintaan yang gagal (misalnya, 2.3% 500s).
- **Persentil Latensi**: 95th (p95) dan 99th (p99) waktu respons—ketahui skenario terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Kueri/Middleware Panjang**: 5 panggilan basis data lambat dan lapisan middleware teratas.
- **Cache Hit/Miss**: Seberapa sering cache menyelamatkan hari Anda.

**Ekstra**:
- Filter berdasarkan “Last Hour,” “Last Day,” atau “Last Week.”
- Alihkan mode gelap untuk sesi malam yang larut.

**Contoh**:
Permintaan ke `/users` mungkin menunjukkan:
- Total Time: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit on `user_list` (5ms)

## Menambahkan Acara Kustom

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent; // Impor untuk acara kustom

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana itu muncul?**
Di detail permintaan dasbor di bawah “Custom Events”—dapat diperluas dengan pemformatan JSON yang bagus.

**Kasus Penggunaan**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Sekarang Anda akan melihat jika API itu menarik aplikasi Anda ke bawah!

## Pemantauan Basis Data

Lacak kueri PDO seperti ini:

```php
use flight\database\PdoWrapper; // Impor untuk wrapper PDO

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang Anda Dapatkan**:
- Teks kueri (misalnya, `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (misalnya, 0.015s)
- Jumlah baris (misalnya, 42)

**Peringatan**:
- **Opsional**: Lewati ini jika Anda tidak perlu pelacakan DB.
- **Hanya PdoWrapper**: PDO inti belum dihubungkan—tetap ikuti!
- **Peringatan Performa**: Mencatat setiap kueri di situs yang berat DB dapat memperlambat hal-hal. Gunakan pengambilan sampel (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Keluaran Contoh**:
- Query: `SELECT name FROM products WHERE price > 100`
- Time: 0.023s
- Rows: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Batas pada 500 metrik—menjaganya terbatas.
- `--batch_size 200`: Proses 200 sekaligus—menyeimbangkan kecepatan dan memori.
- `--daemon`: Berjalan tanpa henti—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## ID Permintaan di Aplikasi

Setiap permintaan memiliki ID permintaan unik untuk pelacakan. Anda dapat menggunakan ID ini di aplikasi Anda untuk menghubungkan log dan metrik. Misalnya, Anda dapat menambahkan ID permintaan ke halaman kesalahan:

```php
Flight::map('error', function($message) {
	// Dapatkan ID permintaan dari header respons X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Selain itu, Anda bisa mengambilnya dari variabel Flight
	// Metode ini tidak akan berfungsi dengan baik di swoole atau platform async lainnya.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Meningkatkan

Jika Anda meningkatkan ke versi APM yang lebih baru, ada kemungkinan migrasi basis data yang perlu dijalankan. Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema basis data ke versi terbaru.

**Catatan:** Jika basis data APM Anda besar, migrasi ini mungkin memakan waktu. Anda mungkin ingin menjalankan perintah ini selama jam non-puncak.

## Menghapus Data Lama

Untuk menjaga basis data Anda rapi, Anda dapat menghapus data lama. Ini sangat berguna jika Anda menjalankan aplikasi yang sibuk dan ingin menjaga ukuran basis data tetap terkelola.
Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data yang lebih lama dari 30 hari dari basis data. Anda dapat menyesuaikan jumlah hari dengan meneruskan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data yang lebih lama dari 7 hari dari basis data.

## Memecahkan Masalah

Tertahan? Coba ini:

- **Tidak Ada Data Dasbor?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Jalur konfigurasi cocok? Verifikasi DSN di `.runway-config.json` menunjuk ke file asli.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik yang tertunda.

- **Kesalahan Worker?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dasbor Tidak Akan Dimulai?**
  - Port 8001 digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan tingkat sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.

- **Tidak Melacak Pengecualian/Kesalahan?**
  - Jika Anda memiliki [Tracy](https://tracy.nette.org/) diaktifkan untuk proyek Anda, itu akan menimpa penanganan kesalahan Flight. Anda perlu menonaktifkan Tracy dan pastikan `Flight::set('flight.handle_errors', true);` diatur.

- **Tidak Melacak Kueri Basis Data?**
  - Pastikan Anda menggunakan `PdoWrapper` untuk koneksi basis data Anda.
  - Pastikan Anda membuat argumen terakhir di konstruktor `true`.# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk mengatur, menggunakan, dan menguasai Pemantauan Kinerja Aplikasi (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan yang lambat atau hanya ingin bersemangat dengan grafik latensi, kami sudah menutupinya. Mari buat aplikasi Anda lebih cepat, pengguna lebih bahagia, dan sesi debugging lebih mudah!

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda seperti restoran yang sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur terganggu, Anda hanya menebak mengapa pelanggan pergi dengan marah. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari permintaan masuk hingga kueri basis data, dan menandai apa saja yang memperlambat Anda. Halaman yang lambat kehilangan pengguna (studi mengatakan 53% mundur jika situs memakan waktu lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah *sebelum* menyakiti. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “mengapa ini rusak?”, lebih banyak kemenangan “lihat betapa licinnya ini berjalan!”.

## Pemasangan

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda memerlukan:
- **PHP 7.4+**: Menjaga kami kompatibel dengan distribusi Linux LTS sambil mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Kerangka ringan yang kami tingkatkan.

## Basis Data yang Didukung

FlightPHP APM saat ini mendukung basis data berikut untuk menyimpan metrik:

- **SQLite3**: Sederhana, berbasis file, dan bagus untuk pengembangan lokal atau aplikasi kecil. Pilihan default dalam sebagian besar pengaturan.
- **MySQL/MariaDB**: Ideal untuk proyek yang lebih besar atau lingkungan produksi di mana Anda memerlukan penyimpanan yang kuat dan skalabel.

Anda dapat memilih tipe basis data selama langkah konfigurasi (lihat di bawah). Pastikan lingkungan PHP Anda memiliki ekstensi yang diperlukan terpasang (misalnya, `pdo_sqlite` atau `pdo_mysql`).

## Memulai

Inilah langkah-demi-langkah untuk kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke dalam `index.php` atau file `services.php` untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory; // Impor untuk membuat logger
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Membuat logger dari konfigurasi
$Apm = new Apm($ApmLogger); // Inisialisasi APM
$Apm->bindEventsToFlightInstance($app); // Mengikat acara ke instance Flight

// Jika Anda menambahkan koneksi basis data
// Harus berupa PdoWrapper atau PdoQueryCapture dari Ekstensi Tracy
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut tentang itu sebentar lagi) dan mengatur logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan acara Flight (permintaan, rute, kesalahan, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengikat semuanya ke aplikasi Flight Anda.

**Tip Pro: Pengambilan Sampel**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani. Gunakan tingkat sampel (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% dari permintaan
```

Ini menjaga performa tetap cepat sambil tetap memberikan data yang solid.

### 2. Konfigurasikan Itu

Jalankan ini untuk membuat `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan di mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
- Default adalah SQLite—misalnya, `sqlite:/tmp/apm_metrics.sqlite` untuk sumber, yang lain untuk tujuan.
- Anda akan mendapatkan konfigurasi seperti:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Proses ini juga akan menanyakan apakah Anda ingin menjalankan migrasi untuk pengaturan ini. Jika Anda mengatur ini untuk pertama kalinya, jawabannya adalah ya.

**Mengapa dua lokasi?**
Metrik mentah menumpuk dengan cepat (pikirkan log yang tidak disaring). Worker memprosesnya menjadi tujuan yang terstruktur untuk dasbor. Menjaga segala sesuatu rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data yang siap dasbor. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke tujuan Anda.
- Berhenti saat selesai atau jika tidak ada metrik yang tersisa.

**Jaga Agar Tetap Berjalan**
Untuk aplikasi langsung, Anda ingin pemrosesan kontinu. Berikut pilihan Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat datang. Bagus untuk dev atau pengaturan kecil.

- **Crontab**:
  Tambahkan ini ke crontab Anda (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Berjalan setiap menit—sempurna untuk produksi.

- **Tmux/Screen**:
  Mulai sesi yang dapat dilepas:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, kemudian D untuk melepaskan; `tmux attach -t apm-worker` untuk menyambung kembali
  ```
  Menjaganya berjalan bahkan jika Anda keluar.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Proses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Berhenti setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dasbor Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dasbor

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menghidupkan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat kesalahan, dan banyak lagi.

**Sesuaikan Itu**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana saja (berguna untuk melihat jarak jauh).
- `--port 8080`: Gunakan port berbeda jika 8001 digunakan.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Kunjungi URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dasbor karena mungkin ada firewall dan langkah keamanan lainnya. Berikut beberapa pilihan:

- **Gunakan Reverse Proxy**: Atur Nginx atau Apache untuk meneruskan permintaan ke dasbor.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk mentunnel dasbor ke mesin lokal Anda.
- **VPN**: Jika server Anda di balik VPN, sambungkan ke sana dan akses dasbor langsung.
- **Konfigurasi Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasi Apache/Nginx**: Jika Anda memiliki server web di depan aplikasi Anda, Anda dapat mengonfigurasikannya ke domain atau subdomain. Jika Anda melakukan ini, Anda akan mengatur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Ingin dasbor yang berbeda?

Anda dapat membangun dasbor Anda sendiri jika Anda mau! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dasbor Anda sendiri!

## Fitur Dasbor

Dasbor adalah markas APM Anda—ini yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan cap waktu, URL, kode respons, dan waktu total. Klik “Detail” untuk middleware, kueri, dan kesalahan.
- **Permintaan Terlambat**: 5 permintaan teratas yang menghabiskan waktu (misalnya, “/api/heavy” pada 2.5s).
- **Rute Terlambat**: 5 rute teratas berdasarkan waktu rata-rata—bagus untuk menemukan pola.
- **Tingkat Kesalahan**: Persentase permintaan yang gagal (misalnya, 2.3% 500s).
- **Persentil Latensi**: 95th (p95) dan 99th (p99) waktu respons—ketahui skenario terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Kueri/Middleware Panjang**: 5 panggilan basis data lambat dan lapisan middleware teratas.
- **Cache Hit/Miss**: Seberapa sering cache menyelamatkan hari Anda.

**Ekstra**:
- Filter berdasarkan “Last Hour,” “Last Day,” atau “Last Week.”
- Alihkan mode gelap untuk sesi malam yang larut.

**Contoh**:
Permintaan ke `/users` mungkin menunjukkan:
- Total Time: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit on `user_list` (5ms)

## Menambahkan Acara Kustom

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent; // Impor untuk acara kustom

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana itu muncul?**
Di detail permintaan dasbor di bawah “Custom Events”—dapat diperluas dengan pemformatan JSON yang bagus.

**Kasus Penggunaan**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Sekarang Anda akan melihat jika API itu menarik aplikasi Anda ke bawah!

## Pemantauan Basis Data

Lacak kueri PDO seperti ini:

```php
use flight\database\PdoWrapper; // Impor untuk wrapper PDO

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang Anda Dapatkan**:
- Teks kueri (misalnya, `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (misalnya, 0.015s)
- Jumlah baris (misalnya, 42)

**Peringatan**:
- **Opsional**: Lewati ini jika Anda tidak perlu pelacakan DB.
- **Hanya PdoWrapper**: PDO inti belum dihubungkan—tetap ikuti!
- **Peringatan Performa**: Mencatat setiap kueri di situs yang berat DB dapat memperlambat hal-hal. Gunakan pengambilan sampel (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Keluaran Contoh**:
- Query: `SELECT name FROM products WHERE price > 100`
- Time: 0.023s
- Rows: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Batas pada 500 metrik—menjaganya terbatas.
- `--batch_size 200`: Proses 200 sekaligus—menyeimbangkan kecepatan dan memori.
- `--daemon`: Berjalan tanpa henti—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## ID Permintaan di Aplikasi

Setiap permintaan memiliki ID permintaan unik untuk pelacakan. Anda dapat menggunakan ID ini di aplikasi Anda untuk menghubungkan log dan metrik. Misalnya, Anda dapat menambahkan ID permintaan ke halaman kesalahan:

```php
Flight::map('error', function($message) {
	// Dapatkan ID permintaan dari header respons X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Selain itu, Anda bisa mengambilnya dari variabel Flight
	// Metode ini tidak akan berfungsi dengan baik di swoole atau platform async lainnya.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Meningkatkan

Jika Anda meningkatkan ke versi APM yang lebih baru, ada kemungkinan migrasi basis data yang perlu dijalankan. Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema basis data ke versi terbaru.

**Catatan:** Jika basis data APM Anda besar, migrasi ini mungkin memakan waktu. Anda mungkin ingin menjalankan perintah ini selama jam non-puncak.

## Menghapus Data Lama

Untuk menjaga basis data Anda rapi, Anda dapat menghapus data lama. Ini sangat berguna jika Anda menjalankan aplikasi yang sibuk dan ingin menjaga ukuran basis data tetap terkelola.
Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data yang lebih lama dari 30 hari dari basis data. Anda dapat menyesuaikan jumlah hari dengan meneruskan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data yang lebih lama dari 7 hari dari basis data.

## Memecahkan Masalah

Tertahan? Coba ini:

- **Tidak Ada Data Dasbor?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Jalur konfigurasi cocok? Verifikasi DSN di `.runway-config.json` menunjuk ke file asli.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik yang tertunda.

- **Kesalahan Worker?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dasbor Tidak Akan Dimulai?**
  - Port 8001 digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan tingkat sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.

- **Tidak Melacak Pengecualian/Kesalahan?**
  - Jika Anda memiliki [Tracy](https://tracy.nette.org/) diaktifkan untuk proyek Anda, itu akan menimpa penanganan kesalahan Flight. Anda perlu menonaktifkan Tracy dan pastikan `Flight::set('flight.handle_errors', true);` diatur.

- **Tidak Melacak Kueri Basis Data?**
  - Pastikan Anda menggunakan `PdoWrapper` untuk koneksi basis data Anda.
  - Pastikan Anda membuat argumen terakhir di konstruktor `true`.# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk mengatur, menggunakan, dan menguasai Pemantauan Kinerja Aplikasi (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan yang lambat atau hanya ingin bersemangat dengan grafik latensi, kami sudah menutupinya. Mari buat aplikasi Anda lebih cepat, pengguna lebih bahagia, dan sesi debugging lebih mudah!

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda seperti restoran yang sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur terganggu, Anda hanya menebak mengapa pelanggan pergi dengan marah. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari permintaan masuk hingga kueri basis data, dan menandai apa saja yang memperlambat Anda. Halaman yang lambat kehilangan pengguna (studi mengatakan 53% mundur jika situs memakan waktu lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah *sebelum* menyakiti. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “mengapa ini rusak?”, lebih banyak kemenangan “lihat betapa licinnya ini berjalan!”.

## Pemasangan

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda memerlukan:
- **PHP 7.4+**: Menjaga kami kompatibel dengan distribusi Linux LTS sambil mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Kerangka ringan yang kami tingkatkan.

## Basis Data yang Didukung

FlightPHP APM saat ini mendukung basis data berikut untuk menyimpan metrik:

- **SQLite3**: Sederhana, berbasis file, dan bagus untuk pengembangan lokal atau aplikasi kecil. Pilihan default dalam sebagian besar pengaturan.
- **MySQL/MariaDB**: Ideal untuk proyek yang lebih besar atau lingkungan produksi di mana Anda memerlukan penyimpanan yang kuat dan skalabel.

Anda dapat memilih tipe basis data selama langkah konfigurasi (lihat di bawah). Pastikan lingkungan PHP Anda memiliki ekstensi yang diperlukan terpasang (misalnya, `pdo_sqlite` atau `pdo_mysql`).

## Memulai

Inilah langkah-demi-langkah untuk kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke dalam `index.php` atau file `services.php` untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory; // Impor untuk membuat logger
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Membuat logger dari konfigurasi
$Apm = new Apm($ApmLogger); // Inisialisasi APM
$Apm->bindEventsToFlightInstance($app); // Mengikat acara ke instance Flight

// Jika Anda menambahkan koneksi basis data
// Harus berupa PdoWrapper atau PdoQueryCapture dari Ekstensi Tracy
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut tentang itu sebentar lagi) dan mengatur logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan acara Flight (permintaan, rute, kesalahan, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengikat semuanya ke aplikasi Flight Anda.

**Tip Pro: Pengambilan Sampel**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani. Gunakan tingkat sampel (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% dari permintaan
```

Ini menjaga performa tetap cepat sambil tetap memberikan data yang solid.

### 2. Konfigurasikan Itu

Jalankan ini untuk membuat `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan di mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
- Default adalah SQLite—misalnya, `sqlite:/tmp/apm_metrics.sqlite` untuk sumber, yang lain untuk tujuan.
- Anda akan mendapatkan konfigurasi seperti:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Proses ini juga akan menanyakan apakah Anda ingin menjalankan migrasi untuk pengaturan ini. Jika Anda mengatur ini untuk pertama kalinya, jawabannya adalah ya.

**Mengapa dua lokasi?**
Metrik mentah menumpuk dengan cepat (pikirkan log yang tidak disaring). Worker memprosesnya menjadi tujuan yang terstruktur untuk dasbor. Menjaga segala sesuatu rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data yang siap dasbor. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke tujuan Anda.
- Berhenti saat selesai atau jika tidak ada metrik yang tersisa.

**Jaga Agar Tetap Berjalan**
Untuk aplikasi langsung, Anda ingin pemrosesan kontinu. Berikut pilihan Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat datang. Bagus untuk dev atau pengaturan kecil.

- **Crontab**:
  Tambahkan ini ke crontab Anda (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Berjalan setiap menit—sempurna untuk produksi.

- **Tmux/Screen**:
  Mulai sesi yang dapat dilepas:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, kemudian D untuk melepaskan; `tmux attach -t apm-worker` untuk menyambung kembali
  ```
  Menjaganya berjalan bahkan jika Anda keluar.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Proses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Berhenti setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dasbor Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dasbor

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menghidupkan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat kesalahan, dan banyak lagi.

**Sesuaikan Itu**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana saja (berguna untuk melihat jarak jauh).
- `--port 8080`: Gunakan port berbeda jika 8001 digunakan.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Kunjungi URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dasbor karena mungkin ada firewall dan langkah keamanan lainnya. Berikut beberapa pilihan:

- **Gunakan Reverse Proxy**: Atur Nginx atau Apache untuk meneruskan permintaan ke dasbor.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk mentunnel dasbor ke mesin lokal Anda.
- **VPN**: Jika server Anda di balik VPN, sambungkan ke sana dan akses dasbor langsung.
- **Konfigurasi Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasi Apache/Nginx**: Jika Anda memiliki server web di depan aplikasi Anda, Anda dapat mengonfigurasikannya ke domain atau subdomain. Jika Anda melakukan ini, Anda akan mengatur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Ingin dasbor yang berbeda?

Anda dapat membangun dasbor Anda sendiri jika Anda mau! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dasbor Anda sendiri!

## Fitur Dasbor

Dasbor adalah markas APM Anda—ini yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan cap waktu, URL, kode respons, dan waktu total. Klik “Detail” untuk middleware, kueri, dan kesalahan.
- **Permintaan Terlambat**: 5 permintaan teratas yang menghabiskan waktu (misalnya, “/api/heavy” pada 2.5s).
- **Rute Terlambat**: 5 rute teratas berdasarkan waktu rata-rata—bagus untuk menemukan pola.
- **Tingkat Kesalahan**: Persentase permintaan yang gagal (misalnya, 2.3% 500s).
- **Persentil Latensi**: 95th (p95) dan 99th (p99) waktu respons—ketahui skenario terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Kueri/Middleware Panjang**: 5 panggilan basis data lambat dan lapisan middleware teratas.
- **Cache Hit/Miss**: Seberapa sering cache menyelamatkan hari Anda.

**Ekstra**:
- Filter berdasarkan “Last Hour,” “Last Day,” atau “Last Week.”
- Alihkan mode gelap untuk sesi malam yang larut.

**Contoh**:
Permintaan ke `/users` mungkin menunjukkan:
- Total Time: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit on `user_list` (5ms)

## Menambahkan Acara Kustom

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent; // Impor untuk acara kustom

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana itu muncul?**
Di detail permintaan dasbor di bawah “Custom Events”—dapat diperluas dengan pemformatan JSON yang bagus.

**Kasus Penggunaan**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Sekarang Anda akan melihat jika API itu menarik aplikasi Anda ke bawah!

## Pemantauan Basis Data

Lacak kueri PDO seperti ini:

```php
use flight\database\PdoWrapper; // Impor untuk wrapper PDO

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang Anda Dapatkan**:
- Teks kueri (misalnya, `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (misalnya, 0.015s)
- Jumlah baris (misalnya, 42)

**Peringatan**:
- **Opsional**: Lewati ini jika Anda tidak perlu pelacakan DB.
- **Hanya PdoWrapper**: PDO inti belum dihubungkan—tetap ikuti!
- **Peringatan Performa**: Mencatat setiap kueri di situs yang berat DB dapat memperlambat hal-hal. Gunakan pengambilan sampel (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Keluaran Contoh**:
- Query: `SELECT name FROM products WHERE price > 100`
- Time: 0.023s
- Rows: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Batas pada 500 metrik—menjaganya terbatas.
- `--batch_size 200`: Proses 200 sekaligus—menyeimbangkan kecepatan dan memori.
- `--daemon`: Berjalan tanpa henti—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## ID Permintaan di Aplikasi

Setiap permintaan memiliki ID permintaan unik untuk pelacakan. Anda dapat menggunakan ID ini di aplikasi Anda untuk menghubungkan log dan metrik. Misalnya, Anda dapat menambahkan ID permintaan ke halaman kesalahan:

```php
Flight::map('error', function($message) {
	// Dapatkan ID permintaan dari header respons X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Selain itu, Anda bisa mengambilnya dari variabel Flight
	// Metode ini tidak akan berfungsi dengan baik di swoole atau platform async lainnya.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Meningkatkan

Jika Anda meningkatkan ke versi APM yang lebih baru, ada kemungkinan migrasi basis data yang perlu dijalankan. Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema basis data ke versi terbaru.

**Catatan:** Jika basis data APM Anda besar, migrasi ini mungkin memakan waktu. Anda mungkin ingin menjalankan perintah ini selama jam non-puncak.

## Menghapus Data Lama

Untuk menjaga basis data Anda rapi, Anda dapat menghapus data lama. Ini sangat berguna jika Anda menjalankan aplikasi yang sibuk dan ingin menjaga ukuran basis data tetap terkelola.
Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data yang lebih lama dari 30 hari dari basis data. Anda dapat menyesuaikan jumlah hari dengan meneruskan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data yang lebih lama dari 7 hari dari basis data.

## Memecahkan Masalah

Tertahan? Coba ini:

- **Tidak Ada Data Dasbor?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Jalur konfigurasi cocok? Verifikasi DSN di `.runway-config.json` menunjuk ke file asli.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik yang tertunda.

- **Kesalahan Worker?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dasbor Tidak Akan Dimulai?**
  - Port 8001 digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan tingkat sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.

- **Tidak Melacak Pengecualian/Kesalahan?**
  - Jika Anda memiliki [Tracy](https://tracy.nette.org/) diaktifkan untuk proyek Anda, itu akan menimpa penanganan kesalahan Flight. Anda perlu menonaktifkan Tracy dan pastikan `Flight::set('flight.handle_errors', true);` diatur.

- **Tidak Melacak Kueri Basis Data?**
  - Pastikan Anda menggunakan `PdoWrapper` untuk koneksi basis data Anda.
  - Pastikan Anda membuat argumen terakhir di konstruktor `true`.# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk mengatur, menggunakan, dan menguasai Pemantauan Kinerja Aplikasi (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan yang lambat atau hanya ingin bersemangat dengan grafik latensi, kami sudah menutupinya. Mari buat aplikasi Anda lebih cepat, pengguna lebih bahagia, dan sesi debugging lebih mudah!

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda seperti restoran yang sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur terganggu, Anda hanya menebak mengapa pelanggan pergi dengan marah. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari permintaan masuk hingga kueri basis data, dan menandai apa saja yang memperlambat Anda. Halaman yang lambat kehilangan pengguna (studi mengatakan 53% mundur jika situs memakan waktu lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah *sebelum* menyakiti. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “mengapa ini rusak?”, lebih banyak kemenangan “lihat betapa licinnya ini berjalan!”.

## Pemasangan

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda memerlukan:
- **PHP 7.4+**: Menjaga kami kompatibel dengan distribusi Linux LTS sambil mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Kerangka ringan yang kami tingkatkan.

## Basis Data yang Didukung

FlightPHP APM saat ini mendukung basis data berikut untuk menyimpan metrik:

- **SQLite3**: Sederhana, berbasis file, dan bagus untuk pengembangan lokal atau aplikasi kecil. Pilihan default dalam sebagian besar pengaturan.
- **MySQL/MariaDB**: Ideal untuk proyek yang lebih besar atau lingkungan produksi di mana Anda memerlukan penyimpanan yang kuat dan skalabel.

Anda dapat memilih tipe basis data selama langkah konfigurasi (lihat di bawah). Pastikan lingkungan PHP Anda memiliki ekstensi yang diperlukan terpasang (misalnya, `pdo_sqlite` atau `pdo_mysql`).

## Memulai

Inilah langkah-demi-langkah untuk kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke dalam `index.php` atau file `services.php` untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory; // Impor untuk membuat logger
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Membuat logger dari konfigurasi
$Apm = new Apm($ApmLogger); // Inisialisasi APM
$Apm->bindEventsToFlightInstance($app); // Mengikat acara ke instance Flight

// Jika Anda menambahkan koneksi basis data
// Harus berupa PdoWrapper atau PdoQueryCapture dari Ekstensi Tracy
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut tentang itu sebentar lagi) dan mengatur logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan acara Flight (permintaan, rute, kesalahan, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengikat semuanya ke aplikasi Flight Anda.

**Tip Pro: Pengambilan Sampel**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani. Gunakan tingkat sampel (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% dari permintaan
```

Ini menjaga performa tetap cepat sambil tetap memberikan data yang solid.

### 2. Konfigurasikan Itu

Jalankan ini untuk membuat `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan di mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
- Default adalah SQLite—misalnya, `sqlite:/tmp/apm_metrics.sqlite` untuk sumber, yang lain untuk tujuan.
- Anda akan mendapatkan konfigurasi seperti:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Proses ini juga akan menanyakan apakah Anda ingin menjalankan migrasi untuk pengaturan ini. Jika Anda mengatur ini untuk pertama kalinya, jawabannya adalah ya.

**Mengapa dua lokasi?**
Metrik mentah menumpuk dengan cepat (pikirkan log yang tidak disaring). Worker memprosesnya menjadi tujuan yang terstruktur untuk dasbor. Menjaga segala sesuatu rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data yang siap dasbor. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke tujuan Anda.
- Berhenti saat selesai atau jika tidak ada metrik yang tersisa.

**Jaga Agar Tetap Berjalan**
Untuk aplikasi langsung, Anda ingin pemrosesan kontinu. Berikut pilihan Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat datang. Bagus untuk dev atau pengaturan kecil.

- **Crontab**:
  Tambahkan ini ke crontab Anda (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Berjalan setiap menit—sempurna untuk produksi.

- **Tmux/Screen**:
  Mulai sesi yang dapat dilepas:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, kemudian D untuk melepaskan; `tmux attach -t apm-worker` untuk menyambung kembali
  ```
  Menjaganya berjalan bahkan jika Anda keluar.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Proses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Berhenti setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dasbor Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dasbor

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menghidupkan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat kesalahan, dan banyak lagi.

**Sesuaikan Itu**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana saja (berguna untuk melihat jarak jauh).
- `--port 8080`: Gunakan port berbeda jika 8001 digunakan.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Kunjungi URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dasbor karena mungkin ada firewall dan langkah keamanan lainnya. Berikut beberapa pilihan:

- **Gunakan Reverse Proxy**: Atur Nginx atau Apache untuk meneruskan permintaan ke dasbor.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk mentunnel dasbor ke mesin lokal Anda.
- **VPN**: Jika server Anda di balik VPN, sambungkan ke sana dan akses dasbor langsung.
- **Konfigurasi Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasi Apache/Nginx**: Jika Anda memiliki server web di depan aplikasi Anda, Anda dapat mengonfigurasikannya ke domain atau subdomain. Jika Anda melakukan ini, Anda akan mengatur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Ingin dasbor yang berbeda?

Anda dapat membangun dasbor Anda sendiri jika Anda mau! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dasbor Anda sendiri!

## Fitur Dasbor

Dasbor adalah markas APM Anda—ini yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan cap waktu, URL, kode respons, dan waktu total. Klik “Detail” untuk middleware, kueri, dan kesalahan.
- **Permintaan Terlambat**: 5 permintaan teratas yang menghabiskan waktu (misalnya, “/api/heavy” pada 2.5s).
- **Rute Terlambat**: 5 rute teratas berdasarkan waktu rata-rata—bagus untuk menemukan pola.
- **Tingkat Kesalahan**: Persentase permintaan yang gagal (misalnya, 2.3% 500s).
- **Persentil Latensi**: 95th (p95) dan 99th (p99) waktu respons—ketahui skenario terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Kueri/Middleware Panjang**: 5 panggilan basis data lambat dan lapisan middleware teratas.
- **Cache Hit/Miss**: Seberapa sering cache menyelamatkan hari Anda.

**Ekstra**:
- Filter berdasarkan “Last Hour,” “Last Day,” atau “Last Week.”
- Alihkan mode gelap untuk sesi malam yang larut.

**Contoh**:
Permintaan ke `/users` mungkin menunjukkan:
- Total Time: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit on `user_list` (5ms)

## Menambahkan Acara Kustom

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent; // Impor untuk acara kustom

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana itu muncul?**
Di detail permintaan dasbor di bawah “Custom Events”—dapat diperluas dengan pemformatan JSON yang bagus.

**Kasus Penggunaan**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Sekarang Anda akan melihat jika API itu menarik aplikasi Anda ke bawah!

## Pemantauan Basis Data

Lacak kueri PDO seperti ini:

```php
use flight\database\PdoWrapper; // Impor untuk wrapper PDO

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang Anda Dapatkan**:
- Teks kueri (misalnya, `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (misalnya, 0.015s)
- Jumlah baris (misalnya, 42)

**Peringatan**:
- **Opsional**: Lewati ini jika Anda tidak perlu pelacakan DB.
- **Hanya PdoWrapper**: PDO inti belum dihubungkan—tetap ikuti!
- **Peringatan Performa**: Mencatat setiap kueri di situs yang berat DB dapat memperlambat hal-hal. Gunakan pengambilan sampel (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Keluaran Contoh**:
- Query: `SELECT name FROM products WHERE price > 100`
- Time: 0.023s
- Rows: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Batas pada 500 metrik—menjaganya terbatas.
- `--batch_size 200`: Proses 200 sekaligus—menyeimbangkan kecepatan dan memori.
- `--daemon`: Berjalan tanpa henti—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## ID Permintaan di Aplikasi

Setiap permintaan memiliki ID permintaan unik untuk pelacakan. Anda dapat menggunakan ID ini di aplikasi Anda untuk menghubungkan log dan metrik. Misalnya, Anda dapat menambahkan ID permintaan ke halaman kesalahan:

```php
Flight::map('error', function($message) {
	// Dapatkan ID permintaan dari header respons X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Selain itu, Anda bisa mengambilnya dari variabel Flight
	// Metode ini tidak akan berfungsi dengan baik di swoole atau platform async lainnya.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Meningkatkan

Jika Anda meningkatkan ke versi APM yang lebih baru, ada kemungkinan migrasi basis data yang perlu dijalankan. Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema basis data ke versi terbaru.

**Catatan:** Jika basis data APM Anda besar, migrasi ini mungkin memakan waktu. Anda mungkin ingin menjalankan perintah ini selama jam non-puncak.

## Menghapus Data Lama

Untuk menjaga basis data Anda rapi, Anda dapat menghapus data lama. Ini sangat berguna jika Anda menjalankan aplikasi yang sibuk dan ingin menjaga ukuran basis data tetap terkelola.
Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data yang lebih lama dari 30 hari dari basis data. Anda dapat menyesuaikan jumlah hari dengan meneruskan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data yang lebih lama dari 7 hari dari basis data.

## Memecahkan Masalah

Tertahan? Coba ini:

- **Tidak Ada Data Dasbor?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Jalur konfigurasi cocok? Verifikasi DSN di `.runway-config.json` menunjuk ke file asli.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik yang tertunda.

- **Kesalahan Worker?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dasbor Tidak Akan Dimulai?**
  - Port 8001 digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan tingkat sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.

- **Tidak Melacak Pengecualian/Kesalahan?**
  - Jika Anda memiliki [Tracy](https://tracy.nette.org/) diaktifkan untuk proyek Anda, itu akan menimpa penanganan kesalahan Flight. Anda perlu menonaktifkan Tracy dan pastikan `Flight::set('flight.handle_errors', true);` diatur.

- **Tidak Melacak Kueri Basis Data?**
  - Pastikan Anda menggunakan `PdoWrapper` untuk koneksi basis data Anda.
  - Pastikan Anda membuat argumen terakhir di konstruktor `true`.# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk mengatur, menggunakan, dan menguasai Pemantauan Kinerja Aplikasi (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan yang lambat atau hanya ingin bersemangat dengan grafik latensi, kami sudah menutupinya. Mari buat aplikasi Anda lebih cepat, pengguna lebih bahagia, dan sesi debugging lebih mudah!

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda seperti restoran yang sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur terganggu, Anda hanya menebak mengapa pelanggan pergi dengan marah. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari permintaan masuk hingga kueri basis data, dan menandai apa saja yang memperlambat Anda. Halaman yang lambat kehilangan pengguna (studi mengatakan 53% mundur jika situs memakan waktu lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah *sebelum* menyakiti. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “mengapa ini rusak?”, lebih banyak kemenangan “lihat betapa licinnya ini berjalan!”.

## Pemasangan

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda memerlukan:
- **PHP 7.4+**: Menjaga kami kompatibel dengan distribusi Linux LTS sambil mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Kerangka ringan yang kami tingkatkan.

## Basis Data yang Didukung

FlightPHP APM saat ini mendukung basis data berikut untuk menyimpan metrik:

- **SQLite3**: Sederhana, berbasis file, dan bagus untuk pengembangan lokal atau aplikasi kecil. Pilihan default dalam sebagian besar pengaturan.
- **MySQL/MariaDB**: Ideal untuk proyek yang lebih besar atau lingkungan produksi di mana Anda memerlukan penyimpanan yang kuat dan skalabel.

Anda dapat memilih tipe basis data selama langkah konfigurasi (lihat di bawah). Pastikan lingkungan PHP Anda memiliki ekstensi yang diperlukan terpasang (misalnya, `pdo_sqlite` atau `pdo_mysql`).

## Memulai

Inilah langkah-demi-langkah untuk kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke dalam `index.php` atau file `services.php` untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory; // Impor untuk membuat logger
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Membuat logger dari konfigurasi
$Apm = new Apm($ApmLogger); // Inisialisasi APM
$Apm->bindEventsToFlightInstance($app); // Mengikat acara ke instance Flight

// Jika Anda menambahkan koneksi basis data
// Harus berupa PdoWrapper atau PdoQueryCapture dari Ekstensi Tracy
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut tentang itu sebentar lagi) dan mengatur logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan acara Flight (permintaan, rute, kesalahan, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengikat semuanya ke aplikasi Flight Anda.

**Tip Pro: Pengambilan Sampel**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani. Gunakan tingkat sampel (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% dari permintaan
```

Ini menjaga performa tetap cepat sambil tetap memberikan data yang solid.

### 2. Konfigurasikan Itu

Jalankan ini untuk membuat `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan di mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
- Default adalah SQLite—misalnya, `sqlite:/tmp/apm_metrics.sqlite` untuk sumber, yang lain untuk tujuan.
- Anda akan mendapatkan konfigurasi seperti:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Proses ini juga akan menanyakan apakah Anda ingin menjalankan migrasi untuk pengaturan ini. Jika Anda mengatur ini untuk pertama kalinya, jawabannya adalah ya.

**Mengapa dua lokasi?**
Metrik mentah menumpuk dengan cepat (pikirkan log yang tidak disaring). Worker memprosesnya menjadi tujuan yang terstruktur untuk dasbor. Menjaga segala sesuatu rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data yang siap dasbor. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke tujuan Anda.
- Berhenti saat selesai atau jika tidak ada metrik yang tersisa.

**Jaga Agar Tetap Berjalan**
Untuk aplikasi langsung, Anda ingin pemrosesan kontinu. Berikut pilihan Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat datang. Bagus untuk dev atau pengaturan kecil.

- **Crontab**:
  Tambahkan ini ke crontab Anda (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Berjalan setiap menit—sempurna untuk produksi.

- **Tmux/Screen**:
  Mulai sesi yang dapat dilepas:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, kemudian D untuk melepaskan; `tmux attach -t apm-worker` untuk menyambung kembali
  ```
  Menjaganya berjalan bahkan jika Anda keluar.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Proses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Berhenti setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dasbor Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dasbor

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menghidupkan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat kesalahan, dan banyak lagi.

**Sesuaikan Itu**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana saja (berguna untuk melihat jarak jauh).
- `--port 8080`: Gunakan port berbeda jika 8001 digunakan.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Kunjungi URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dasbor karena mungkin ada firewall dan langkah keamanan lainnya. Berikut beberapa pilihan:

- **Gunakan Reverse Proxy**: Atur Nginx atau Apache untuk meneruskan permintaan ke dasbor.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk mentunnel dasbor ke mesin lokal Anda.
- **VPN**: Jika server Anda di balik VPN, sambungkan ke sana dan akses dasbor langsung.
- **Konfigurasi Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasi Apache/Nginx**: Jika Anda memiliki server web di depan aplikasi Anda, Anda dapat mengonfigurasikannya ke domain atau subdomain. Jika Anda melakukan ini, Anda akan mengatur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Ingin dasbor yang berbeda?

Anda dapat membangun dasbor Anda sendiri jika Anda mau! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dasbor Anda sendiri!

## Fitur Dasbor

Dasbor adalah markas APM Anda—ini yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan cap waktu, URL, kode respons, dan waktu total. Klik “Detail” untuk middleware, kueri, dan kesalahan.
- **Permintaan Terlambat**: 5 permintaan teratas yang menghabiskan waktu (misalnya, “/api/heavy” pada 2.5s).
- **Rute Terlambat**: 5 rute teratas berdasarkan waktu rata-rata—bagus untuk menemukan pola.
- **Tingkat Kesalahan**: Persentase permintaan yang gagal (misalnya, 2.3% 500s).
- **Persentil Latensi**: 95th (p95) dan 99th (p99) waktu respons—ketahui skenario terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Kueri/Middleware Panjang**: 5 panggilan basis data lambat dan lapisan middleware teratas.
- **Cache Hit/Miss**: Seberapa sering cache menyelamatkan hari Anda.

**Ekstra**:
- Filter berdasarkan “Last Hour,” “Last Day,” atau “Last Week.”
- Alihkan mode gelap untuk sesi malam yang larut.

**Contoh**:
Permintaan ke `/users` mungkin menunjukkan:
- Total Time: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit on `user_list` (5ms)

## Menambahkan Acara Kustom

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent; // Impor untuk acara kustom

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana itu muncul?**
Di detail permintaan dasbor di bawah “Custom Events”—dapat diperluas dengan pemformatan JSON yang bagus.

**Kasus Penggunaan**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Sekarang Anda akan melihat jika API itu menarik aplikasi Anda ke bawah!

## Pemantauan Basis Data

Lacak kueri PDO seperti ini:

```php
use flight\database\PdoWrapper; // Impor untuk wrapper PDO

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang Anda Dapatkan**:
- Teks kueri (misalnya, `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (misalnya, 0.015s)
- Jumlah baris (misalnya, 42)

**Peringatan**:
- **Opsional**: Lewati ini jika Anda tidak perlu pelacakan DB.
- **Hanya PdoWrapper**: PDO inti belum dihubungkan—tetap ikuti!
- **Peringatan Performa**: Mencatat setiap kueri di situs yang berat DB dapat memperlambat hal-hal. Gunakan pengambilan sampel (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Keluaran Contoh**:
- Query: `SELECT name FROM products WHERE price > 100`
- Time: 0.023s
- Rows: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Batas pada 500 metrik—menjaganya terbatas.
- `--batch_size 200`: Proses 200 sekaligus—menyeimbangkan kecepatan dan memori.
- `--daemon`: Berjalan tanpa henti—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## ID Permintaan di Aplikasi

Setiap permintaan memiliki ID permintaan unik untuk pelacakan. Anda dapat menggunakan ID ini di aplikasi Anda untuk menghubungkan log dan metrik. Misalnya, Anda dapat menambahkan ID permintaan ke halaman kesalahan:

```php
Flight::map('error', function($message) {
	// Dapatkan ID permintaan dari header respons X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Selain itu, Anda bisa mengambilnya dari variabel Flight
	// Metode ini tidak akan berfungsi dengan baik di swoole atau platform async lainnya.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Meningkatkan

Jika Anda meningkatkan ke versi APM yang lebih baru, ada kemungkinan migrasi basis data yang perlu dijalankan. Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema basis data ke versi terbaru.

**Catatan:** Jika basis data APM Anda besar, migrasi ini mungkin memakan waktu. Anda mungkin ingin menjalankan perintah ini selama jam non-puncak.

## Menghapus Data Lama

Untuk menjaga basis data Anda rapi, Anda dapat menghapus data lama. Ini sangat berguna jika Anda menjalankan aplikasi yang sibuk dan ingin menjaga ukuran basis data tetap terkelola.
Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data yang lebih lama dari 30 hari dari basis data. Anda dapat menyesuaikan jumlah hari dengan meneruskan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data yang lebih lama dari 7 hari dari basis data.

## Memecahkan Masalah

Tertahan? Coba ini:

- **Tidak Ada Data Dasbor?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Jalur konfigurasi cocok? Verifikasi DSN di `.runway-config.json` menunjuk ke file asli.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik yang tertunda.

- **Kesalahan Worker?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dasbor Tidak Akan Dimulai?**
  - Port 8001 digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan tingkat sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.

- **Tidak Melacak Pengecualian/Kesalahan?**
  - Jika Anda memiliki [Tracy](https://tracy.nette.org/) diaktifkan untuk proyek Anda, itu akan menimpa penanganan kesalahan Flight. Anda perlu menonaktifkan Tracy dan pastikan `Flight::set('flight.handle_errors', true);` diatur.

- **Tidak Melacak Kueri Basis Data?**
  - Pastikan Anda menggunakan `PdoWrapper` untuk koneksi basis data Anda.
  - Pastikan Anda membuat argumen terakhir di konstruktor `true`.# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk mengatur, menggunakan, dan menguasai Pemantauan Kinerja Aplikasi (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan yang lambat atau hanya ingin bersemangat dengan grafik latensi, kami sudah menutupinya. Mari buat aplikasi Anda lebih cepat, pengguna lebih bahagia, dan sesi debugging lebih mudah!

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda seperti restoran yang sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur terganggu, Anda hanya menebak mengapa pelanggan pergi dengan marah. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari permintaan masuk hingga kueri basis data, dan menandai apa saja yang memperlambat Anda. Halaman yang lambat kehilangan pengguna (studi mengatakan 53% mundur jika situs memakan waktu lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah *sebelum* menyakiti. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “mengapa ini rusak?”, lebih banyak kemenangan “lihat betapa licinnya ini berjalan!”.

## Pemasangan

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda memerlukan:
- **PHP 7.4+**: Menjaga kami kompatibel dengan distribusi Linux LTS sambil mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Kerangka ringan yang kami tingkatkan.

## Basis Data yang Didukung

FlightPHP APM saat ini mendukung basis data berikut untuk menyimpan metrik:

- **SQLite3**: Sederhana, berbasis file, dan bagus untuk pengembangan lokal atau aplikasi kecil. Pilihan default dalam sebagian besar pengaturan.
- **MySQL/MariaDB**: Ideal untuk proyek yang lebih besar atau lingkungan produksi di mana Anda memerlukan penyimpanan yang kuat dan skalabel.

Anda dapat memilih tipe basis data selama langkah konfigurasi (lihat di bawah). Pastikan lingkungan PHP Anda memiliki ekstensi yang diperlukan terpasang (misalnya, `pdo_sqlite` atau `pdo_mysql`).

## Memulai

Inilah langkah-demi-langkah untuk kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke dalam `index.php` atau file `services.php` untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory; // Impor untuk membuat logger
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Membuat logger dari konfigurasi
$Apm = new Apm($ApmLogger); // Inisialisasi APM
$Apm->bindEventsToFlightInstance($app); // Mengikat acara ke instance Flight

// Jika Anda menambahkan koneksi basis data
// Harus berupa PdoWrapper atau PdoQueryCapture dari Ekstensi Tracy
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut tentang itu sebentar lagi) dan mengatur logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan acara Flight (permintaan, rute, kesalahan, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengikat semuanya ke aplikasi Flight Anda.

**Tip Pro: Pengambilan Sampel**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani. Gunakan tingkat sampel (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% dari permintaan
```

Ini menjaga performa tetap cepat sambil tetap memberikan data yang solid.

### 2. Konfigurasikan Itu

Jalankan ini untuk membuat `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan di mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
- Default adalah SQLite—misalnya, `sqlite:/tmp/apm_metrics.sqlite` untuk sumber, yang lain untuk tujuan.
- Anda akan mendapatkan konfigurasi seperti:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Proses ini juga akan menanyakan apakah Anda ingin menjalankan migrasi untuk pengaturan ini. Jika Anda mengatur ini untuk pertama kalinya, jawabannya adalah ya.

**Mengapa dua lokasi?**
Metrik mentah menumpuk dengan cepat (pikirkan log yang tidak disaring). Worker memprosesnya menjadi tujuan yang terstruktur untuk dasbor. Menjaga segala sesuatu rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data yang siap dasbor. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke tujuan Anda.
- Berhenti saat selesai atau jika tidak ada metrik yang tersisa.

**Jaga Agar Tetap Berjalan**
Untuk aplikasi langsung, Anda ingin pemrosesan kontinu. Berikut pilihan Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat datang. Bagus untuk dev atau pengaturan kecil.

- **Crontab**:
  Tambahkan ini ke crontab Anda (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Berjalan setiap menit—sempurna untuk produksi.

- **Tmux/Screen**:
  Mulai sesi yang dapat dilepas:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, kemudian D untuk melepaskan; `tmux attach -t apm-worker` untuk menyambung kembali
  ```
  Menjaganya berjalan bahkan jika Anda keluar.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Proses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Berhenti setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dasbor Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dasbor

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menghidupkan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat kesalahan, dan banyak lagi.

**Sesuaikan Itu**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana saja (berguna untuk melihat jarak jauh).
- `--port 8080`: Gunakan port berbeda jika 8001 digunakan.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Kunjungi URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dasbor karena mungkin ada firewall dan langkah keamanan lainnya. Berikut beberapa pilihan:

- **Gunakan Reverse Proxy**: Atur Nginx atau Apache untuk meneruskan permintaan ke dasbor.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk mentunnel dasbor ke mesin lokal Anda.
- **VPN**: Jika server Anda di balik VPN, sambungkan ke sana dan akses dasbor langsung.
- **Konfigurasi Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasi Apache/Nginx**: Jika Anda memiliki server web di depan aplikasi Anda, Anda dapat mengonfigurasikannya ke domain atau subdomain. Jika Anda melakukan ini, Anda akan mengatur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Ingin dasbor yang berbeda?

Anda dapat membangun dasbor Anda sendiri jika Anda mau! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dasbor Anda sendiri!

## Fitur Dasbor

Dasbor adalah markas APM Anda—ini yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan cap waktu, URL, kode respons, dan waktu total. Klik “Detail” untuk middleware, kueri, dan kesalahan.
- **Permintaan Terlambat**: 5 permintaan teratas yang menghabiskan waktu (misalnya, “/api/heavy” pada 2.5s).
- **Rute Terlambat**: 5 rute teratas berdasarkan waktu rata-rata—bagus untuk menemukan pola.
- **Tingkat Kesalahan**: Persentase permintaan yang gagal (misalnya, 2.3% 500s).
- **Persentil Latensi**: 95th (p95) dan 99th (p99) waktu respons—ketahui skenario terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Kueri/Middleware Panjang**: 5 panggilan basis data lambat dan lapisan middleware teratas.
- **Cache Hit/Miss**: Seberapa sering cache menyelamatkan hari Anda.

**Ekstra**:
- Filter berdasarkan “Last Hour,” “Last Day,” atau “Last Week.”
- Alihkan mode gelap untuk sesi malam yang larut.

**Contoh**:
Permintaan ke `/users` mungkin menunjukkan:
- Total Time: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit on `user_list` (5ms)

## Menambahkan Acara Kustom

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent; // Impor untuk acara kustom

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana itu muncul?**
Di detail permintaan dasbor di bawah “Custom Events”—dapat diperluas dengan pemformatan JSON yang bagus.

**Kasus Penggunaan**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Sekarang Anda akan melihat jika API itu menarik aplikasi Anda ke bawah!

## Pemantauan Basis Data

Lacak kueri PDO seperti ini:

```php
use flight\database\PdoWrapper; // Impor untuk wrapper PDO

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang Anda Dapatkan**:
- Teks kueri (misalnya, `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (misalnya, 0.015s)
- Jumlah baris (misalnya, 42)

**Peringatan**:
- **Opsional**: Lewati ini jika Anda tidak perlu pelacakan DB.
- **Hanya PdoWrapper**: PDO inti belum dihubungkan—tetap ikuti!
- **Peringatan Performa**: Mencatat setiap kueri di situs yang berat DB dapat memperlambat hal-hal. Gunakan pengambilan sampel (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Keluaran Contoh**:
- Query: `SELECT name FROM products WHERE price > 100`
- Time: 0.023s
- Rows: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Batas pada 500 metrik—menjaganya terbatas.
- `--batch_size 200`: Proses 200 sekaligus—menyeimbangkan kecepatan dan memori.
- `--daemon`: Berjalan tanpa henti—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## ID Permintaan di Aplikasi

Setiap permintaan memiliki ID permintaan unik untuk pelacakan. Anda dapat menggunakan ID ini di aplikasi Anda untuk menghubungkan log dan metrik. Misalnya, Anda dapat menambahkan ID permintaan ke halaman kesalahan:

```php
Flight::map('error', function($message) {
	// Dapatkan ID permintaan dari header respons X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Selain itu, Anda bisa mengambilnya dari variabel Flight
	// Metode ini tidak akan berfungsi dengan baik di swoole atau platform async lainnya.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Meningkatkan

Jika Anda meningkatkan ke versi APM yang lebih baru, ada kemungkinan migrasi basis data yang perlu dijalankan. Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema basis data ke versi terbaru.

**Catatan:** Jika basis data APM Anda besar, migrasi ini mungkin memakan waktu. Anda mungkin ingin menjalankan perintah ini selama jam non-puncak.

## Menghapus Data Lama

Untuk menjaga basis data Anda rapi, Anda dapat menghapus data lama. Ini sangat berguna jika Anda menjalankan aplikasi yang sibuk dan ingin menjaga ukuran basis data tetap terkelola.
Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data yang lebih lama dari 30 hari dari basis data. Anda dapat menyesuaikan jumlah hari dengan meneruskan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data yang lebih lama dari 7 hari dari basis data.

## Memecahkan Masalah

Tertahan? Coba ini:

- **Tidak Ada Data Dasbor?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Jalur konfigurasi cocok? Verifikasi DSN di `.runway-config.json` menunjuk ke file asli.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik yang tertunda.

- **Kesalahan Worker?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dasbor Tidak Akan Dimulai?**
  - Port 8001 digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan tingkat sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.

- **Tidak Melacak Pengecualian/Kesalahan?**
  - Jika Anda memiliki [Tracy](https://tracy.nette.org/) diaktifkan untuk proyek Anda, itu akan menimpa penanganan kesalahan Flight. Anda perlu menonaktifkan Tracy dan pastikan `Flight::set('flight.handle_errors', true);` diatur.

- **Tidak Melacak Kueri Basis Data?**
  - Pastikan Anda menggunakan `PdoWrapper` untuk koneksi basis data Anda.
  - Pastikan Anda membuat argumen terakhir di konstruktor `true`.# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk mengatur, menggunakan, dan menguasai Pemantauan Kinerja Aplikasi (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan yang lambat atau hanya ingin bersemangat dengan grafik latensi, kami sudah menutupinya. Mari buat aplikasi Anda lebih cepat, pengguna lebih bahagia, dan sesi debugging lebih mudah!

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda seperti restoran yang sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur terganggu, Anda hanya menebak mengapa pelanggan pergi dengan marah. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari permintaan masuk hingga kueri basis data, dan menandai apa saja yang memperlambat Anda. Halaman yang lambat kehilangan pengguna (studi mengatakan 53% mundur jika situs memakan waktu lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah *sebelum* menyakiti. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “mengapa ini rusak?”, lebih banyak kemenangan “lihat betapa licinnya ini berjalan!”.

## Pemasangan

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda memerlukan:
- **PHP 7.4+**: Menjaga kami kompatibel dengan distribusi Linux LTS sambil mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Kerangka ringan yang kami tingkatkan.

## Basis Data yang Didukung

FlightPHP APM saat ini mendukung basis data berikut untuk menyimpan metrik:

- **SQLite3**: Sederhana, berbasis file, dan bagus untuk pengembangan lokal atau aplikasi kecil. Pilihan default dalam sebagian besar pengaturan.
- **MySQL/MariaDB**: Ideal untuk proyek yang lebih besar atau lingkungan produksi di mana Anda memerlukan penyimpanan yang kuat dan skalabel.

Anda dapat memilih tipe basis data selama langkah konfigurasi (lihat di bawah). Pastikan lingkungan PHP Anda memiliki ekstensi yang diperlukan terpasang (misalnya, `pdo_sqlite` atau `pdo_mysql`).

## Memulai

Inilah langkah-demi-langkah untuk kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke dalam `index.php` atau file `services.php` untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory; // Impor untuk membuat logger
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Membuat logger dari konfigurasi
$Apm = new Apm($ApmLogger); // Inisialisasi APM
$Apm->bindEventsToFlightInstance($app); // Mengikat acara ke instance Flight

// Jika Anda menambahkan koneksi basis data
// Harus berupa PdoWrapper atau PdoQueryCapture dari Ekstensi Tracy
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut tentang itu sebentar lagi) dan mengatur logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan acara Flight (permintaan, rute, kesalahan, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengikat semuanya ke aplikasi Flight Anda.

**Tip Pro: Pengambilan Sampel**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani. Gunakan tingkat sampel (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% dari permintaan
```

Ini menjaga performa tetap cepat sambil tetap memberikan data yang solid.

### 2. Konfigurasikan Itu

Jalankan ini untuk membuat `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan di mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
- Default adalah SQLite—misalnya, `sqlite:/tmp/apm_metrics.sqlite` untuk sumber, yang lain untuk tujuan.
- Anda akan mendapatkan konfigurasi seperti:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Proses ini juga akan menanyakan apakah Anda ingin menjalankan migrasi untuk pengaturan ini. Jika Anda mengatur ini untuk pertama kalinya, jawabannya adalah ya.

**Mengapa dua lokasi?**
Metrik mentah menumpuk dengan cepat (pikirkan log yang tidak disaring). Worker memprosesnya menjadi tujuan yang terstruktur untuk dasbor. Menjaga segala sesuatu rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data yang siap dasbor. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke tujuan Anda.
- Berhenti saat selesai atau jika tidak ada metrik yang tersisa.

**Jaga Agar Tetap Berjalan**
Untuk aplikasi langsung, Anda ingin pemrosesan kontinu. Berikut pilihan Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat datang. Bagus untuk dev atau pengaturan kecil.

- **Crontab**:
  Tambahkan ini ke crontab Anda (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Berjalan setiap menit—sempurna untuk produksi.

- **Tmux/Screen**:
  Mulai sesi yang dapat dilepas:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, kemudian D untuk melepaskan; `tmux attach -t apm-worker` untuk menyambung kembali
  ```
  Menjaganya berjalan bahkan jika Anda keluar.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Proses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Berhenti setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dasbor Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dasbor

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menghidupkan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat kesalahan, dan banyak lagi.

**Sesuaikan Itu**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana saja (berguna untuk melihat jarak jauh).
- `--port 8080`: Gunakan port berbeda jika 8001 digunakan.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Kunjungi URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dasbor karena mungkin ada firewall dan langkah keamanan lainnya. Berikut beberapa pilihan:

- **Gunakan Reverse Proxy**: Atur Nginx atau Apache untuk meneruskan permintaan ke dasbor.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk mentunnel dasbor ke mesin lokal Anda.
- **VPN**: Jika server Anda di balik VPN, sambungkan ke sana dan akses dasbor langsung.
- **Konfigurasi Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasi Apache/Nginx**: Jika Anda memiliki server web di depan aplikasi Anda, Anda dapat mengonfigurasikannya ke domain atau subdomain. Jika Anda melakukan ini, Anda akan mengatur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Ingin dasbor yang berbeda?

Anda dapat membangun dasbor Anda sendiri jika Anda mau! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dasbor Anda sendiri!

## Fitur Dasbor

Dasbor adalah markas APM Anda—ini yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan cap waktu, URL, kode respons, dan waktu total. Klik “Detail” untuk middleware, kueri, dan kesalahan.
- **Permintaan Terlambat**: 5 permintaan teratas yang menghabiskan waktu (misalnya, “/api/heavy” pada 2.5s).
- **Rute Terlambat**: 5 rute teratas berdasarkan waktu rata-rata—bagus untuk menemukan pola.
- **Tingkat Kesalahan**: Persentase permintaan yang gagal (misalnya, 2.3% 500s).
- **Persentil Latensi**: 95th (p95) dan 99th (p99) waktu respons—ketahui skenario terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Kueri/Middleware Panjang**: 5 panggilan basis data lambat dan lapisan middleware teratas.
- **Cache Hit/Miss**: Seberapa sering cache menyelamatkan hari Anda.

**Ekstra**:
- Filter berdasarkan “Last Hour,” “Last Day,” atau “Last Week”.
- Alihkan mode gelap untuk sesi malam yang larut.

**Contoh**:
Permintaan ke `/users` mungkin menunjukkan:
- Total Time: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit on `user_list` (5ms)

## Menambahkan Acara Kustom

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent; // Impor untuk acara kustom

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana itu muncul?**
Di detail permintaan dasbor di bawah “Custom Events”—dapat diperluas dengan pemformatan JSON yang bagus.

**Kasus Penggunaan**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Sekarang Anda akan melihat jika API itu menarik aplikasi Anda ke bawah!

## Pemantauan Basis Data

Lacak kueri PDO seperti ini:

```php
use flight\database\PdoWrapper; // Impor untuk wrapper PDO

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang Anda Dapatkan**:
- Teks kueri (misalnya, `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (misalnya, 0.015s)
- Jumlah baris (misalnya, 42)

**Peringatan**:
- **Opsional**: Lewati ini jika Anda tidak perlu pelacakan DB.
- **Hanya PdoWrapper**: PDO inti belum dihubungkan—tetap ikuti!
- **Peringatan Performa**: Mencatat setiap kueri di situs yang berat DB dapat memperlambat hal-hal. Gunakan pengambilan sampel (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Keluaran Contoh**:
- Query: `SELECT name FROM products WHERE price > 100`
- Time: 0.023s
- Rows: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Batas pada 500 metrik—menjaganya terbatas.
- `--batch_size 200`: Proses 200 sekaligus—menyeimbangkan kecepatan dan memori.
- `--daemon`: Berjalan tanpa henti—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## ID Permintaan di Aplikasi

Setiap permintaan memiliki ID permintaan unik untuk pelacakan. Anda dapat menggunakan ID ini di aplikasi Anda untuk menghubungkan log dan metrik. Misalnya, Anda dapat menambahkan ID permintaan ke halaman kesalahan:

```php
Flight::map('error', function($message) {
	// Dapatkan ID permintaan dari header respons X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Selain itu, Anda bisa mengambilnya dari variabel Flight
	// Metode ini tidak akan berfungsi dengan baik di swoole atau platform async lainnya.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Meningkatkan

Jika Anda meningkatkan ke versi APM yang lebih baru, ada kemungkinan migrasi basis data yang perlu dijalankan. Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema basis data ke versi terbaru.

**Catatan:** Jika basis data APM Anda besar, migrasi ini mungkin memakan waktu. Anda mungkin ingin menjalankan perintah ini selama jam non-puncak.

## Menghapus Data Lama

Untuk menjaga basis data Anda rapi, Anda dapat menghapus data lama. Ini sangat berguna jika Anda menjalankan aplikasi yang sibuk dan ingin menjaga ukuran basis data tetap terkelola.
Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data yang lebih lama dari 30 hari dari basis data. Anda dapat menyesuaikan jumlah hari dengan meneruskan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data yang lebih lama dari 7 hari dari basis data.

## Memecahkan Masalah

Tertahan? Coba ini:

- **Tidak Ada Data Dasbor?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Jalur konfigurasi cocok? Verifikasi DSN di `.runway-config.json` menunjuk ke file asli.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik yang tertunda.

- **Kesalahan Worker?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dasbor Tidak Akan Dimulai?**
  - Port 8001 digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan tingkat sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.

- **Tidak Melacak Pengecualian/Kesalahan?**
  - Jika Anda memiliki [Tracy](https://tracy.nette.org/) diaktifkan untuk proyek Anda, itu akan menimpa penanganan kesalahan Flight. Anda perlu menonaktifkan Tracy dan pastikan `Flight::set('flight.handle_errors', true);` diatur.

- **Tidak Melacak Kueri Basis Data?**
  - Pastikan Anda menggunakan `PdoWrapper` untuk koneksi basis data Anda.
  - Pastikan Anda membuat argumen terakhir di konstruktor `true`.# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk mengatur, menggunakan, dan menguasai Pemantauan Kinerja Aplikasi (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan yang lambat atau hanya ingin bersemangat dengan grafik latensi, kami sudah menutupinya. Mari buat aplikasi Anda lebih cepat, pengguna lebih bahagia, dan sesi debugging lebih mudah!

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda seperti restoran yang sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur terganggu, Anda hanya menebak mengapa pelanggan pergi dengan marah. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari permintaan masuk hingga kueri basis data, dan menandai apa saja yang memperlambat Anda. Halaman yang lambat kehilangan pengguna (studi mengatakan 53% mundur jika situs memakan waktu lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah *sebelum* menyakiti. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “mengapa ini rusak?”, lebih banyak kemenangan “lihat betapa licinnya ini berjalan!”.

## Pemasangan

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda memerlukan:
- **PHP 7.4+**: Menjaga kami kompatibel dengan distribusi Linux LTS sambil mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Kerangka ringan yang kami tingkatkan.

## Basis Data yang Didukung

FlightPHP APM saat ini mendukung basis data berikut untuk menyimpan metrik:

- **SQLite3**: Sederhana, berbasis file, dan bagus untuk pengembangan lokal atau aplikasi kecil. Pilihan default dalam sebagian besar pengaturan.
- **MySQL/MariaDB**: Ideal untuk proyek yang lebih besar atau lingkungan produksi di mana Anda memerlukan penyimpanan yang kuat dan skalabel.

Anda dapat memilih tipe basis data selama langkah konfigurasi (lihat di bawah). Pastikan lingkungan PHP Anda memiliki ekstensi yang diperlukan terpasang (misalnya, `pdo_sqlite` atau `pdo_mysql`).

## Memulai

Inilah langkah-demi-langkah untuk kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke dalam `index.php` atau file `services.php` untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory; // Impor untuk membuat logger
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Membuat logger dari konfigurasi
$Apm = new Apm($ApmLogger); // Inisialisasi APM
$Apm->bindEventsToFlightInstance($app); // Mengikat acara ke instance Flight

// Jika Anda menambahkan koneksi basis data
// Harus berupa PdoWrapper atau PdoQueryCapture dari Ekstensi Tracy
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut tentang itu sebentar lagi) dan mengatur logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan acara Flight (permintaan, rute, kesalahan, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengikat semuanya ke aplikasi Flight Anda.

**Tip Pro: Pengambilan Sampel**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani. Gunakan tingkat sampel (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% dari permintaan
```

Ini menjaga performa tetap cepat sambil tetap memberikan data yang solid.

### 2. Konfigurasikan Itu

Jalankan ini untuk membuat `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan di mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
- Default adalah SQLite—misalnya, `sqlite:/tmp/apm_metrics.sqlite` untuk sumber, yang lain untuk tujuan.
- Anda akan mendapatkan konfigurasi seperti:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Proses ini juga akan menanyakan apakah Anda ingin menjalankan migrasi untuk pengaturan ini. Jika Anda mengatur ini untuk pertama kalinya, jawabannya adalah ya.

**Mengapa dua lokasi?**
Metrik mentah menumpuk dengan cepat (pikirkan log yang tidak disaring). Worker memprosesnya menjadi tujuan yang terstruktur untuk dasbor. Menjaga segala sesuatu rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data yang siap dasbor. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke tujuan Anda.
- Berhenti saat selesai atau jika tidak ada metrik yang tersisa.

**Jaga Agar Tetap Berjalan**
Untuk aplikasi langsung, Anda ingin pemrosesan kontinu. Berikut pilihan Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat datang. Bagus untuk dev atau pengaturan kecil.

- **Crontab**:
  Tambahkan ini ke crontab Anda (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Berjalan setiap menit—sempurna untuk produksi.

- **Tmux/Screen**:
  Mulai sesi yang dapat dilepas:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, kemudian D untuk melepaskan; `tmux attach -t apm-worker` untuk menyambung kembali
  ```
  Menjaganya berjalan bahkan jika Anda keluar.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Proses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Berhenti setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dasbor Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dasbor

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menghidupkan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat kesalahan, dan banyak lagi.

**Sesuaikan Itu**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana saja (berguna untuk melihat jarak jauh).
- `--port 8080`: Gunakan port berbeda jika 8001 digunakan.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Kunjungi URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dasbor karena mungkin ada firewall dan langkah keamanan lainnya. Berikut beberapa pilihan:

- **Gunakan Reverse Proxy**: Atur Nginx atau Apache untuk meneruskan permintaan ke dasbor.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk mentunnel dasbor ke mesin lokal Anda.
- **VPN**: Jika server Anda di balik VPN, sambungkan ke sana dan akses dasbor langsung.
- **Konfigurasi Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasi Apache/Nginx**: Jika Anda memiliki server web di depan aplikasi Anda, Anda dapat mengonfigurasikannya ke domain atau subdomain. Jika Anda melakukan ini, Anda akan mengatur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Ingin dasbor yang berbeda?

Anda dapat membangun dasbor Anda sendiri jika Anda mau! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dasbor Anda sendiri!

## Fitur Dasbor

Dasbor adalah markas APM Anda—ini yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan cap waktu, URL, kode respons, dan waktu total. Klik “Detail” untuk middleware, kueri, dan kesalahan.
- **Permintaan Terlambat**: 5 permintaan teratas yang menghabiskan waktu (misalnya, “/api/heavy” pada 2.5s).
- **Rute Terlambat**: 5 rute teratas berdasarkan waktu rata-rata—bagus untuk menemukan pola.
- **Tingkat Kesalahan**: Persentase permintaan yang gagal (misalnya, 2.3% 500s).
- **Persentil Latensi**: 95th (p95) dan 99th (p99) waktu respons—ketahui skenario terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Kueri/Middleware Panjang**: 5 panggilan basis data lambat dan lapisan middleware teratas.
- **Cache Hit/Miss**: Seberapa sering cache menyelamatkan hari Anda.

**Ekstra**:
- Filter berdasarkan “Last Hour,” “Last Day,” atau “Last Week”.
- Alihkan mode gelap untuk sesi malam yang larut.

**Contoh**:
Permintaan ke `/users` mungkin menunjukkan:
- Total Time: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit on `user_list` (5ms)

## Menambahkan Acara Kustom

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent; // Impor untuk acara kustom

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana itu muncul?**
Di detail permintaan dasbor di bawah “Custom Events”—dapat diperluas dengan pemformatan JSON yang bagus.

**Kasus Penggunaan**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Sekarang Anda akan melihat jika API itu menarik aplikasi Anda ke bawah!

## Pemantauan Basis Data

Lacak kueri PDO seperti ini:

```php
use flight\database\PdoWrapper; // Impor untuk wrapper PDO

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang Anda Dapatkan**:
- Teks kueri (misalnya, `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (misalnya, 0.015s)
- Jumlah baris (misalnya, 42)

**Peringatan**:
- **Opsional**: Lewati ini jika Anda tidak perlu pelacakan DB.
- **Hanya PdoWrapper**: PDO inti belum dihubungkan—tetap ikuti!
- **Peringatan Performa**: Mencatat setiap kueri di situs yang berat DB dapat memperlambat hal-hal. Gunakan pengambilan sampel (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Keluaran Contoh**:
- Query: `SELECT name FROM products WHERE price > 100`
- Time: 0.023s
- Rows: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Batas pada 500 metrik—menjaganya terbatas.
- `--batch_size 200`: Proses 200 sekaligus—menyeimbangkan kecepatan dan memori.
- `--daemon`: Berjalan tanpa henti—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## ID Permintaan di Aplikasi

Setiap permintaan memiliki ID permintaan unik untuk pelacakan. Anda dapat menggunakan ID ini di aplikasi Anda untuk menghubungkan log dan metrik. Misalnya, Anda dapat menambahkan ID permintaan ke halaman kesalahan:

```php
Flight::map('error', function($message) {
	// Dapatkan ID permintaan dari header respons X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Selain itu, Anda bisa mengambilnya dari variabel Flight
	// Metode ini tidak akan berfungsi dengan baik di swoole atau platform async lainnya.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Meningkatkan

Jika Anda meningkatkan ke versi APM yang lebih baru, ada kemungkinan migrasi basis data yang perlu dijalankan. Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema basis data ke versi terbaru.

**Catatan:** Jika basis data APM Anda besar, migrasi ini mungkin memakan waktu. Anda mungkin ingin menjalankan perintah ini selama jam non-puncak.

## Menghapus Data Lama

Untuk menjaga basis data Anda rapi, Anda dapat menghapus data lama. Ini sangat berguna jika Anda menjalankan aplikasi yang sibuk dan ingin menjaga ukuran basis data tetap terkelola.
Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data yang lebih lama dari 30 hari dari basis data. Anda dapat menyesuaikan jumlah hari dengan meneruskan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data yang lebih lama dari 7 hari dari basis data.

## Memecahkan Masalah

Tertahan? Coba ini:

- **Tidak Ada Data Dasbor?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Jalur konfigurasi cocok? Verifikasi DSN di `.runway-config.json` menunjuk ke file asli.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik yang tertunda.

- **Kesalahan Worker?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dasbor Tidak Akan Dimulai?**
  - Port 8001 digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan tingkat sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.

- **Tidak Melacak Pengecualian/Kesalahan?**
  - Jika Anda memiliki [Tracy](https://tracy.nette.org/) diaktifkan untuk proyek Anda, itu akan menimpa penanganan kesalahan Flight. Anda perlu menonaktifkan Tracy dan pastikan `Flight::set('flight.handle_errors', true);` diatur.

- **Tidak Melacak Kueri Basis Data?**
  - Pastikan Anda menggunakan `PdoWrapper` untuk koneksi basis data Anda.
  - Pastikan Anda membuat argumen terakhir di konstruktor `true`.# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk mengatur, menggunakan, dan menguasai Pemantauan Kinerja Aplikasi (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan yang lambat atau hanya ingin bersemangat dengan grafik latensi, kami sudah menutupinya. Mari buat aplikasi Anda lebih cepat, pengguna lebih bahagia, dan sesi debugging lebih mudah!

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda seperti restoran yang sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur terganggu, Anda hanya menebak mengapa pelanggan pergi dengan marah. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari permintaan masuk hingga kueri basis data, dan menandai apa saja yang memperlambat Anda. Halaman yang lambat kehilangan pengguna (studi mengatakan 53% mundur jika situs memakan waktu lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah *sebelum* menyakiti. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “mengapa ini rusak?”, lebih banyak kemenangan “lihat betapa licinnya ini berjalan!”.

## Pemasangan

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda memerlukan:
- **PHP 7.4+**: Menjaga kami kompatibel dengan distribusi Linux LTS sambil mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Kerangka ringan yang kami tingkatkan.

## Basis Data yang Didukung

FlightPHP APM saat ini mendukung basis data berikut untuk menyimpan metrik:

- **SQLite3**: Sederhana, berbasis file, dan bagus untuk pengembangan lokal atau aplikasi kecil. Pilihan default dalam sebagian besar pengaturan.
- **MySQL/MariaDB**: Ideal untuk proyek yang lebih besar atau lingkungan produksi di mana Anda memerlukan penyimpanan yang kuat dan skalabel.

Anda dapat memilih tipe basis data selama langkah konfigurasi (lihat di bawah). Pastikan lingkungan PHP Anda memiliki ekstensi yang diperlukan terpasang (misalnya, `pdo_sqlite` atau `pdo_mysql`).

## Memulai

Inilah langkah-demi-langkah untuk kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke dalam `index.php` atau file `services.php` untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory; // Impor untuk membuat logger
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Membuat logger dari konfigurasi
$Apm = new Apm($ApmLogger); // Inisialisasi APM
$Apm->bindEventsToFlightInstance($app); // Mengikat acara ke instance Flight

// Jika Anda menambahkan koneksi basis data
// Harus berupa PdoWrapper atau PdoQueryCapture dari Ekstensi Tracy
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut tentang itu sebentar lagi) dan mengatur logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan acara Flight (permintaan, rute, kesalahan, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengikat semuanya ke aplikasi Flight Anda.

**Tip Pro: Pengambilan Sampel**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani. Gunakan tingkat sampel (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% dari permintaan
```

Ini menjaga performa tetap cepat sambil tetap memberikan data yang solid.

### 2. Konfigurasikan Itu

Jalankan ini untuk membuat `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan di mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
- Default adalah SQLite—misalnya, `sqlite:/tmp/apm_metrics.sqlite` untuk sumber, yang lain untuk tujuan.
- Anda akan mendapatkan konfigurasi seperti:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Proses ini juga akan menanyakan apakah Anda ingin menjalankan migrasi untuk pengaturan ini. Jika Anda mengatur ini untuk pertama kalinya, jawabannya adalah ya.

**Mengapa dua lokasi?**
Metrik mentah menumpuk dengan cepat (pikirkan log yang tidak disaring). Worker memprosesnya menjadi tujuan yang terstruktur untuk dasbor. Menjaga segala sesuatu rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data yang siap dasbor. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke tujuan Anda.
- Berhenti saat selesai atau jika tidak ada metrik yang tersisa.

**Jaga Agar Tetap Berjalan**
Untuk aplikasi langsung, Anda ingin pemrosesan kontinu. Berikut pilihan Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat datang. Bagus untuk dev atau pengaturan kecil.

- **Crontab**:
  Tambahkan ini ke crontab Anda (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Berjalan setiap menit—sempurna untuk produksi.

- **Tmux/Screen**:
  Mulai sesi yang dapat dilepas:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, kemudian D untuk melepaskan; `tmux attach -t apm-worker` untuk menyambung kembali
  ```
  Menjaganya berjalan bahkan jika Anda keluar.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Proses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Berhenti setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dasbor Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dasbor

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menghidupkan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat kesalahan, dan banyak lagi.

**Sesuaikan Itu**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana saja (berguna untuk melihat jarak jauh).
- `--port 8080`: Gunakan port berbeda jika 8001 digunakan.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Kunjungi URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dasbor karena mungkin ada firewall dan langkah keamanan lainnya. Berikut beberapa pilihan:

- **Gunakan Reverse Proxy**: Atur Nginx atau Apache untuk meneruskan permintaan ke dasbor.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk mentunnel dasbor ke mesin lokal Anda.
- **VPN**: Jika server Anda di balik VPN, sambungkan ke sana dan akses dasbor langsung.
- **Konfigurasi Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasi Apache/Nginx**: Jika Anda memiliki server web di depan aplikasi Anda, Anda dapat mengonfigurasikannya ke domain atau subdomain. Jika Anda melakukan ini, Anda akan mengatur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Ingin dasbor yang berbeda?

Anda dapat membangun dasbor Anda sendiri jika Anda mau! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dasbor Anda sendiri!

## Fitur Dasbor

Dasbor adalah markas APM Anda—ini yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan cap waktu, URL, kode respons, dan waktu total. Klik “Detail” untuk middleware, kueri, dan kesalahan.
- **Permintaan Terlambat**: 5 permintaan teratas yang menghabiskan waktu (misalnya, “/api/heavy” pada 2.5s).
- **Rute Terlambat**: 5 rute teratas berdasarkan waktu rata-rata—bagus untuk menemukan pola.
- **Tingkat Kesalahan**: Persentase permintaan yang gagal (misalnya, 2.3% 500s).
- **Persentil Latensi**: 95th (p95) dan 99th (p99) waktu respons—ketahui skenario terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Kueri/Middleware Panjang**: 5 panggilan basis data lambat dan lapisan middleware teratas.
- **Cache Hit/Miss**: Seberapa sering cache menyelamatkan hari Anda.

**Ekstra**:
- Filter berdasarkan “Last Hour,” “Last Day,” atau “Last Week”.
- Alihkan mode gelap untuk sesi malam yang larut.

**Contoh**:
Permintaan ke `/users` mungkin menunjukkan:
- Total Time: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit on `user_list` (5ms)

## Menambahkan Acara Kustom

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent; // Impor untuk acara kustom

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana itu muncul?**
Di detail permintaan dasbor di bawah “Custom Events”—dapat diperluas dengan pemformatan JSON yang bagus.

**Kasus Penggunaan**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Sekarang Anda akan melihat jika API itu menarik aplikasi Anda ke bawah!

## Pemantauan Basis Data

Lacak kueri PDO seperti ini:

```php
use flight\database\PdoWrapper; // Impor untuk wrapper PDO

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang Anda Dapatkan**:
- Teks kueri (misalnya, `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (misalnya, 0.015s)
- Jumlah baris (misalnya, 42)

**Peringatan**:
- **Opsional**: Lewati ini jika Anda tidak perlu pelacakan DB.
- **Hanya PdoWrapper**: PDO inti belum dihubungkan—tetap ikuti!
- **Peringatan Performa**: Mencatat setiap kueri di situs yang berat DB dapat memperlambat hal-hal. Gunakan pengambilan sampel (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Keluaran Contoh**:
- Query: `SELECT name FROM products WHERE price > 100`
- Time: 0.023s
- Rows: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Batas pada 500 metrik—menjaganya terbatas.
- `--batch_size 200`: Proses 200 sekaligus—menyeimbangkan kecepatan dan memori.
- `--daemon`: Berjalan tanpa henti—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## ID Permintaan di Aplikasi

Setiap permintaan memiliki ID permintaan unik untuk pelacakan. Anda dapat menggunakan ID ini di aplikasi Anda untuk menghubungkan log dan metrik. Misalnya, Anda dapat menambahkan ID permintaan ke halaman kesalahan:

```php
Flight::map('error', function($message) {
	// Dapatkan ID permintaan dari header respons X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Selain itu, Anda bisa mengambilnya dari variabel Flight
	// Metode ini tidak akan berfungsi dengan baik di swoole atau platform async lainnya.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Meningkatkan

Jika Anda meningkatkan ke versi APM yang lebih baru, ada kemungkinan migrasi basis data yang perlu dijalankan. Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema basis data ke versi terbaru.

**Catatan:** Jika basis data APM Anda besar, migrasi ini mungkin memakan waktu. Anda mungkin ingin menjalankan perintah ini selama jam non-puncak.

## Menghapus Data Lama

Untuk menjaga basis data Anda rapi, Anda dapat menghapus data lama. Ini sangat berguna jika Anda menjalankan aplikasi yang sibuk dan ingin menjaga ukuran basis data tetap terkelola.
Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data yang lebih lama dari 30 hari dari basis data. Anda dapat menyesuaikan jumlah hari dengan meneruskan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data yang lebih lama dari 7 hari dari basis data.

## Memecahkan Masalah

Tertahan? Coba ini:

- **Tidak Ada Data Dasbor?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Jalur konfigurasi cocok? Verifikasi DSN di `.runway-config.json` menunjuk ke file asli.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik yang tertunda.

- **Kesalahan Worker?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dasbor Tidak Akan Dimulai?**
  - Port 8001 digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan tingkat sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.

- **Tidak Melacak Pengecualian/Kesalahan?**
  - Jika Anda memiliki [Tracy](https://tracy.nette.org/) diaktifkan untuk proyek Anda, itu akan menimpa penanganan kesalahan Flight. Anda perlu menonaktifkan Tracy dan pastikan `Flight::set('flight.handle_errors', true);` diatur.

- **Tidak Melacak Kueri Basis Data?**
  - Pastikan Anda menggunakan `PdoWrapper` untuk koneksi basis data Anda.
  - Pastikan Anda membuat argumen terakhir di konstruktor `true`.# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk mengatur, menggunakan, dan menguasai Pemantauan Kinerja Aplikasi (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan yang lambat atau hanya ingin bersemangat dengan grafik latensi, kami sudah menutupinya. Mari buat aplikasi Anda lebih cepat, pengguna lebih bahagia, dan sesi debugging lebih mudah!

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda seperti restoran yang sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur terganggu, Anda hanya menebak mengapa pelanggan pergi dengan marah. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari permintaan masuk hingga kueri basis data, dan menandai apa saja yang memperlambat Anda. Halaman yang lambat kehilangan pengguna (studi mengatakan 53% mundur jika situs memakan waktu lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah *sebelum* menyakiti. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “mengapa ini rusak?”, lebih banyak kemenangan “lihat betapa licinnya ini berjalan!”.

## Pemasangan

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda memerlukan:
- **PHP 7.4+**: Menjaga kami kompatibel dengan distribusi Linux LTS sambil mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Kerangka ringan yang kami tingkatkan.

## Basis Data yang Didukung

FlightPHP APM saat ini mendukung basis data berikut untuk menyimpan metrik:

- **SQLite3**: Sederhana, berbasis file, dan bagus untuk pengembangan lokal atau aplikasi kecil. Pilihan default dalam sebagian besar pengaturan.
- **MySQL/MariaDB**: Ideal untuk proyek yang lebih besar atau lingkungan produksi di mana Anda memerlukan penyimpanan yang kuat dan skalabel.

Anda dapat memilih tipe basis data selama langkah konfigurasi (lihat di bawah). Pastikan lingkungan PHP Anda memiliki ekstensi yang diperlukan terpasang (misalnya, `pdo_sqlite` atau `pdo_mysql`).

## Memulai

Inilah langkah-demi-langkah untuk kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke dalam `index.php` atau file `services.php` untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory; // Impor untuk membuat logger
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Membuat logger dari konfigurasi
$Apm = new Apm($ApmLogger); // Inisialisasi APM
$Apm->bindEventsToFlightInstance($app); // Mengikat acara ke instance Flight

// Jika Anda menambahkan koneksi basis data
// Harus berupa PdoWrapper atau PdoQueryCapture dari Ekstensi Tracy
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut tentang itu sebentar lagi) dan mengatur logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan acara Flight (permintaan, rute, kesalahan, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengikat semuanya ke aplikasi Flight Anda.

**Tip Pro: Pengambilan Sampel**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani. Gunakan tingkat sampel (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% dari permintaan
```

Ini menjaga performa tetap cepat sambil tetap memberikan data yang solid.

### 2. Konfigurasikan Itu

Jalankan ini untuk membuat `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan di mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
- Default adalah SQLite—misalnya, `sqlite:/tmp/apm_metrics.sqlite` untuk sumber, yang lain untuk tujuan.
- Anda akan mendapatkan konfigurasi seperti:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Proses ini juga akan menanyakan apakah Anda ingin menjalankan migrasi untuk pengaturan ini. Jika Anda mengatur ini untuk pertama kalinya, jawabannya adalah ya.

**Mengapa dua lokasi?**
Metrik mentah menumpuk dengan cepat (pikirkan log yang tidak disaring). Worker memprosesnya menjadi tujuan yang terstruktur untuk dasbor. Menjaga segala sesuatu rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data yang siap dasbor. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke tujuan Anda.
- Berhenti saat selesai atau jika tidak ada metrik yang tersisa.

**Jaga Agar Tetap Berjalan**
Untuk aplikasi langsung, Anda ingin pemrosesan kontinu. Berikut pilihan Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat datang. Bagus untuk dev atau pengaturan kecil.

- **Crontab**:
  Tambahkan ini ke crontab Anda (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Berjalan setiap menit—sempurna untuk produksi.

- **Tmux/Screen**:
  Mulai sesi yang dapat dilepas:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, kemudian D untuk melepaskan; `tmux attach -t apm-worker` untuk menyambung kembali
  ```
  Menjaganya berjalan bahkan jika Anda keluar.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Proses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Berhenti setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dasbor Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dasbor

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menghidupkan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat kesalahan, dan banyak lagi.

**Sesuaikan Itu**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana saja (berguna untuk melihat jarak jauh).
- `--port 8080`: Gunakan port berbeda jika 8001 digunakan.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Kunjungi URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dasbor karena mungkin ada firewall dan langkah keamanan lainnya. Berikut beberapa pilihan:

- **Gunakan Reverse Proxy**: Atur Nginx atau Apache untuk meneruskan permintaan ke dasbor.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk mentunnel dasbor ke mesin lokal Anda.
- **VPN**: Jika server Anda di balik VPN, sambungkan ke sana dan akses dasbor langsung.
- **Konfigurasi Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasi Apache/Nginx**: Jika Anda memiliki server web di depan aplikasi Anda, Anda dapat mengonfigurasikannya ke domain atau subdomain. Jika Anda melakukan ini, Anda akan mengatur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Ingin dasbor yang berbeda?

Anda dapat membangun dasbor Anda sendiri jika Anda mau! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dasbor Anda sendiri!

## Fitur Dasbor

Dasbor adalah markas APM Anda—ini yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan cap waktu, URL, kode respons, dan waktu total. Klik “Detail” untuk middleware, kueri, dan kesalahan.
- **Permintaan Terlambat**: 5 permintaan teratas yang menghabiskan waktu (misalnya, “/api/heavy” pada 2.5s).
- **Rute Terlambat**: 5 rute teratas berdasarkan waktu rata-rata—bagus untuk menemukan pola.
- **Tingkat Kesalahan**: Persentase permintaan yang gagal (misalnya, 2.3% 500s).
- **Persentil Latensi**: 95th (p95) dan 99th (p99) waktu respons—ketahui skenario terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Kueri/Middleware Panjang**: 5 panggilan basis data lambat dan lapisan middleware teratas.
- **Cache Hit/Miss**: Seberapa sering cache menyelamatkan hari Anda.

**Ekstra**:
- Filter berdasarkan “Last Hour,” “Last Day,” atau “Last Week”.
- Alihkan mode gelap untuk sesi malam yang larut.

**Contoh**:
Permintaan ke `/users` mungkin menunjukkan:
- Total Time: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit on `user_list` (5ms)

## Menambahkan Acara Kustom

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent; // Impor untuk acara kustom

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana itu muncul?**
Di detail permintaan dasbor di bawah “Custom Events”—dapat diperluas dengan pemformatan JSON yang bagus.

**Kasus Penggunaan**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Sekarang Anda akan melihat jika API itu menarik aplikasi Anda ke bawah!

## Pemantauan Basis Data

Lacak kueri PDO seperti ini:

```php
use flight\database\PdoWrapper; // Impor untuk wrapper PDO

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang Anda Dapatkan**:
- Teks kueri (misalnya, `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (misalnya, 0.015s)
- Jumlah baris (misalnya, 42)

**Peringatan**:
- **Opsional**: Lewati ini jika Anda tidak perlu pelacakan DB.
- **Hanya PdoWrapper**: PDO inti belum dihubungkan—tetap ikuti!
- **Peringatan Performa**: Mencatat setiap kueri di situs yang berat DB dapat memperlambat hal-hal. Gunakan pengambilan sampel (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Keluaran Contoh**:
- Query: `SELECT name FROM products WHERE price > 100`
- Time: 0.023s
- Rows: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Batas pada 500 metrik—menjaganya terbatas.
- `--batch_size 200`: Proses 200 sekaligus—menyeimbangkan kecepatan dan memori.
- `--daemon`: Berjalan tanpa henti—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## ID Permintaan di Aplikasi

Setiap permintaan memiliki ID permintaan unik untuk pelacakan. Anda dapat menggunakan ID ini di aplikasi Anda untuk menghubungkan log dan metrik. Misalnya, Anda dapat menambahkan ID permintaan ke halaman kesalahan:

```php
Flight::map('error', function($message) {
	// Dapatkan ID permintaan dari header respons X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Selain itu, Anda bisa mengambilnya dari variabel Flight
	// Metode ini tidak akan berfungsi dengan baik di swoole atau platform async lainnya.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Meningkatkan

Jika Anda meningkatkan ke versi APM yang lebih baru, ada kemungkinan migrasi basis data yang perlu dijalankan. Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema basis data ke versi terbaru.

**Catatan:** Jika basis data APM Anda besar, migrasi ini mungkin memakan waktu. Anda mungkin ingin menjalankan perintah ini selama jam non-puncak.

## Menghapus Data Lama

Untuk menjaga basis data Anda rapi, Anda dapat menghapus data lama. Ini sangat berguna jika Anda menjalankan aplikasi yang sibuk dan ingin menjaga ukuran basis data tetap terkelola.
Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data yang lebih lama dari 30 hari dari basis data. Anda dapat menyesuaikan jumlah hari dengan meneruskan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data yang lebih lama dari 7 hari dari basis data.

## Memecahkan Masalah

Tertahan? Coba ini:

- **Tidak Ada Data Dasbor?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Jalur konfigurasi cocok? Verifikasi DSN di `.runway-config.json` menunjuk ke file asli.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik yang tertunda.

- **Kesalahan Worker?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dasbor Tidak Akan Dimulai?**
  - Port 8001 digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan tingkat sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.

- **Tidak Melacak Pengecualian/Kesalahan?**
  - Jika Anda memiliki [Tracy](https://tracy.nette.org/) diaktifkan untuk proyek Anda, itu akan menimpa penanganan kesalahan Flight. Anda perlu menonaktifkan Tracy dan pastikan `Flight::set('flight.handle_errors', true);` diatur.

- **Tidak Melacak Kueri Basis Data?**
  - Pastikan Anda menggunakan `PdoWrapper` untuk koneksi basis data Anda.
  - Pastikan Anda membuat argumen terakhir di konstruktor `true`.# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk mengatur, menggunakan, dan menguasai Pemantauan Kinerja Aplikasi (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan yang lambat atau hanya ingin bersemangat dengan grafik latensi, kami sudah menutupinya. Mari buat aplikasi Anda lebih cepat, pengguna lebih bahagia, dan sesi debugging lebih mudah!

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda seperti restoran yang sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur terganggu, Anda hanya menebak mengapa pelanggan pergi dengan marah. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari permintaan masuk hingga kueri basis data, dan menandai apa saja yang memperlambat Anda. Halaman yang lambat kehilangan pengguna (studi mengatakan 53% mundur jika situs memakan waktu lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah *sebelum* menyakiti. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “mengapa ini rusak?”, lebih banyak kemenangan “lihat betapa licinnya ini berjalan!”.

## Pemasangan

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda memerlukan:
- **PHP 7.4+**: Menjaga kami kompatibel dengan distribusi Linux LTS sambil mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Kerangka ringan yang kami tingkatkan.

## Basis Data yang Didukung

FlightPHP APM saat ini mendukung basis data berikut untuk menyimpan metrik:

- **SQLite3**: Sederhana, berbasis file, dan bagus untuk pengembangan lokal atau aplikasi kecil. Pilihan default dalam sebagian besar pengaturan.
- **MySQL/MariaDB**: Ideal untuk proyek yang lebih besar atau lingkungan produksi di mana Anda memerlukan penyimpanan yang kuat dan skalabel.

Anda dapat memilih tipe basis data selama langkah konfigurasi (lihat di bawah). Pastikan lingkungan PHP Anda memiliki ekstensi yang diperlukan terpasang (misalnya, `pdo_sqlite` atau `pdo_mysql`).

## Memulai

Inilah langkah-demi-langkah untuk kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke dalam `index.php` atau file `services.php` untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory; // Impor untuk membuat logger
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Membuat logger dari konfigurasi
$Apm = new Apm($ApmLogger); // Inisialisasi APM
$Apm->bindEventsToFlightInstance($app); // Mengikat acara ke instance Flight

// Jika Anda menambahkan koneksi basis data
// Harus berupa PdoWrapper atau PdoQueryCapture dari Ekstensi Tracy
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut tentang itu sebentar lagi) dan mengatur logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan acara Flight (permintaan, rute, kesalahan, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengikat semuanya ke aplikasi Flight Anda.

**Tip Pro: Pengambilan Sampel**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani. Gunakan tingkat sampel (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% dari permintaan
```

Ini menjaga performa tetap cepat sambil tetap memberikan data yang solid.

### 2. Konfigurasikan Itu

Jalankan ini untuk membuat `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan di mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
- Default adalah SQLite—misalnya, `sqlite:/tmp/apm_metrics.sqlite` untuk sumber, yang lain untuk tujuan.
- Anda akan mendapatkan konfigurasi seperti:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Proses ini juga akan menanyakan apakah Anda ingin menjalankan migrasi untuk pengaturan ini. Jika Anda mengatur ini untuk pertama kalinya, jawabannya adalah ya.

**Mengapa dua lokasi?**
Metrik mentah menumpuk dengan cepat (pikirkan log yang tidak disaring). Worker memprosesnya menjadi tujuan yang terstruktur untuk dasbor. Menjaga segala sesuatu rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data yang siap dasbor. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke tujuan Anda.
- Berhenti saat selesai atau jika tidak ada metrik yang tersisa.

**Jaga Agar Tetap Berjalan**
Untuk aplikasi langsung, Anda ingin pemrosesan kontinu. Berikut pilihan Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat datang. Bagus untuk dev atau pengaturan kecil.

- **Crontab**:
  Tambahkan ini ke crontab Anda (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Berjalan setiap menit—sempurna untuk produksi.

- **Tmux/Screen**:
  Mulai sesi yang dapat dilepas:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, kemudian D untuk melepaskan; `tmux attach -t apm-worker` untuk menyambung kembali
  ```
  Menjaganya berjalan bahkan jika Anda keluar.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Proses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Berhenti setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dasbor Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dasbor

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menghidupkan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat kesalahan, dan banyak lagi.

**Sesuaikan Itu**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana saja (berguna untuk melihat jarak jauh).
- `--port 8080`: Gunakan port berbeda jika 8001 digunakan.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Kunjungi URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dasbor karena mungkin ada firewall dan langkah keamanan lainnya. Berikut beberapa pilihan:

- **Gunakan Reverse Proxy**: Atur Nginx atau Apache untuk meneruskan permintaan ke dasbor.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk mentunnel dasbor ke mesin lokal Anda.
- **VPN**: Jika server Anda di balik VPN, sambungkan ke sana dan akses dasbor langsung.
- **Konfigurasi Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasi Apache/Nginx**: Jika Anda memiliki server web di depan aplikasi Anda, Anda dapat mengonfigurasikannya ke domain atau subdomain. Jika Anda melakukan ini, Anda akan mengatur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Ingin dasbor yang berbeda?

Anda dapat membangun dasbor Anda sendiri jika Anda mau! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dasbor Anda sendiri!

## Fitur Dasbor

Dasbor adalah markas APM Anda—ini yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan cap waktu, URL, kode respons, dan waktu total. Klik “Detail” untuk middleware, kueri, dan kesalahan.
- **Permintaan Terlambat**: 5 permintaan teratas yang menghabiskan waktu (misalnya, “/api/heavy” pada 2.5s).
- **Rute Terlambat**: 5 rute teratas berdasarkan waktu rata-rata—bagus untuk menemukan pola.
- **Tingkat Kesalahan**: Persentase permintaan yang gagal (misalnya, 2.3% 500s).
- **Persentil Latensi**: 95th (p95) dan 99th (p99) waktu respons—ketahui skenario terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Kueri/Middleware Panjang**: 5 panggilan basis data lambat dan lapisan middleware teratas.
- **Cache Hit/Miss**: Seberapa sering cache menyelamatkan hari Anda.

**Ekstra**:
- Filter berdasarkan “Last Hour,” “Last Day,” atau “Last Week”.
- Alihkan mode gelap untuk sesi malam yang larut.

**Contoh**:
Permintaan ke `/users` mungkin menunjukkan:
- Total Time: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit on `user_list` (5ms)

## Menambahkan Acara Kustom

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent; // Impor untuk acara kustom

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana itu muncul?**
Di detail permintaan dasbor di bawah “Custom Events”—dapat diperluas dengan pemformatan JSON yang bagus.

**Kasus Penggunaan**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Sekarang Anda akan melihat jika API itu menarik aplikasi Anda ke bawah!

## Pemantauan Basis Data

Lacak kueri PDO seperti ini:

```php
use flight\database\PdoWrapper; // Impor untuk wrapper PDO

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang Anda Dapatkan**:
- Teks kueri (misalnya, `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (misalnya, 0.015s)
- Jumlah baris (misalnya, 42)

**Peringatan**:
- **Opsional**: Lewati ini jika Anda tidak perlu pelacakan DB.
- **Hanya PdoWrapper**: PDO inti belum dihubungkan—tetap ikuti!
- **Peringatan Performa**: Mencatat setiap kueri di situs yang berat DB dapat memperlambat hal-hal. Gunakan pengambilan sampel (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Keluaran Contoh**:
- Query: `SELECT name FROM products WHERE price > 100`
- Time: 0.023s
- Rows: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Batas pada 500 metrik—menjaganya terbatas.
- `--batch_size 200`: Proses 200 sekaligus—menyeimbangkan kecepatan dan memori.
- `--daemon`: Berjalan tanpa henti—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## ID Permintaan di Aplikasi

Setiap permintaan memiliki ID permintaan unik untuk pelacakan. Anda dapat menggunakan ID ini di aplikasi Anda untuk menghubungkan log dan metrik. Misalnya, Anda dapat menambahkan ID permintaan ke halaman kesalahan:

```php
Flight::map('error', function($message) {
	// Dapatkan ID permintaan dari header respons X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Selain itu, Anda bisa mengambilnya dari variabel Flight
	// Metode ini tidak akan berfungsi dengan baik di swoole atau platform async lainnya.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Meningkatkan

Jika Anda meningkatkan ke versi APM yang lebih baru, ada kemungkinan migrasi basis data yang perlu dijalankan. Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema basis data ke versi terbaru.

**Catatan:** Jika basis data APM Anda besar, migrasi ini mungkin memakan waktu. Anda mungkin ingin menjalankan perintah ini selama jam non-puncak.

## Menghapus Data Lama

Untuk menjaga basis data Anda rapi, Anda dapat menghapus data lama. Ini sangat berguna jika Anda menjalankan aplikasi yang sibuk dan ingin menjaga ukuran basis data tetap terkelola.
Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data yang lebih lama dari 30 hari dari basis data. Anda dapat menyesuaikan jumlah hari dengan meneruskan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data yang lebih lama dari 7 hari dari basis data.

## Memecahkan Masalah

Tertahan? Coba ini:

- **Tidak Ada Data Dasbor?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Jalur konfigurasi cocok? Verifikasi DSN di `.runway-config.json` menunjuk ke file asli.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik yang tertunda.

- **Kesalahan Worker?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dasbor Tidak Akan Dimulai?**
  - Port 8001 digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan tingkat sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.

- **Tidak Melacak Pengecualian/Kesalahan?**
  - Jika Anda memiliki [Tracy](https://tracy.nette.org/) diaktifkan untuk proyek Anda, itu akan menimpa penanganan kesalahan Flight. Anda perlu menonaktifkan Tracy dan pastikan `Flight::set('flight.handle_errors', true);` diatur.

- **Tidak Melacak Kueri Basis Data?**
  - Pastikan Anda menggunakan `PdoWrapper` untuk koneksi basis data Anda.
  - Pastikan Anda membuat argumen terakhir di konstruktor `true`.# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk mengatur, menggunakan, dan menguasai Pemantauan Kinerja Aplikasi (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan yang lambat atau hanya ingin bersemangat dengan grafik latensi, kami sudah menutupinya. Mari buat aplikasi Anda lebih cepat, pengguna lebih bahagia, dan sesi debugging lebih mudah!

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda seperti restoran yang sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur terganggu, Anda hanya menebak mengapa pelanggan pergi dengan marah. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari permintaan masuk hingga kueri basis data, dan menandai apa saja yang memperlambat Anda. Halaman yang lambat kehilangan pengguna (studi mengatakan 53% mundur jika situs memakan waktu lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah *sebelum* menyakiti. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “mengapa ini rusak?”, lebih banyak kemenangan “lihat betapa licinnya ini berjalan!”.

## Pemasangan

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda memerlukan:
- **PHP 7.4+**: Menjaga kami kompatibel dengan distribusi Linux LTS sambil mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Kerangka ringan yang kami tingkatkan.

## Basis Data yang Didukung

FlightPHP APM saat ini mendukung basis data berikut untuk menyimpan metrik:

- **SQLite3**: Sederhana, berbasis file, dan bagus untuk pengembangan lokal atau aplikasi kecil. Pilihan default dalam sebagian besar pengaturan.
- **MySQL/MariaDB**: Ideal untuk proyek yang lebih besar atau lingkungan produksi di mana Anda memerlukan penyimpanan yang kuat dan skalabel.

Anda dapat memilih tipe basis data selama langkah konfigurasi (lihat di bawah). Pastikan lingkungan PHP Anda memiliki ekstensi yang diperlukan terpasang (misalnya, `pdo_sqlite` atau `pdo_mysql`).

## Memulai

Inilah langkah-demi-langkah untuk kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke dalam `index.php` atau file `services.php` untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory; // Impor untuk membuat logger
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Membuat logger dari konfigurasi
$Apm = new Apm($ApmLogger); // Inisialisasi APM
$Apm->bindEventsToFlightInstance($app); // Mengikat acara ke instance Flight

// Jika Anda menambahkan koneksi basis data
// Harus berupa PdoWrapper atau PdoQueryCapture dari Ekstensi Tracy
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut tentang itu sebentar lagi) dan mengatur logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan acara Flight (permintaan, rute, kesalahan, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengikat semuanya ke aplikasi Flight Anda.

**Tip Pro: Pengambilan Sampel**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani. Gunakan tingkat sampel (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% dari permintaan
```

Ini menjaga performa tetap cepat sambil tetap memberikan data yang solid.

### 2. Konfigurasikan Itu

Jalankan ini untuk membuat `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan di mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
- Default adalah SQLite—misalnya, `sqlite:/tmp/apm_metrics.sqlite` untuk sumber, yang lain untuk tujuan.
- Anda akan mendapatkan konfigurasi seperti:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Proses ini juga akan menanyakan apakah Anda ingin menjalankan migrasi untuk pengaturan ini. Jika Anda mengatur ini untuk pertama kalinya, jawabannya adalah ya.

**Mengapa dua lokasi?**
Metrik mentah menumpuk dengan cepat (pikirkan log yang tidak disaring). Worker memprosesnya menjadi tujuan yang terstruktur untuk dasbor. Menjaga segala sesuatu rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data yang siap dasbor. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke tujuan Anda.
- Berhenti saat selesai atau jika tidak ada metrik yang tersisa.

**Jaga Agar Tetap Berjalan**
Untuk aplikasi langsung, Anda ingin pemrosesan kontinu. Berikut pilihan Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat datang. Bagus untuk dev atau pengaturan kecil.

- **Crontab**:
  Tambahkan ini ke crontab Anda (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Berjalan setiap menit—sempurna untuk produksi.

- **Tmux/Screen**:
  Mulai sesi yang dapat dilepas:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, kemudian D untuk melepaskan; `tmux attach -t apm-worker` untuk menyambung kembali
  ```
  Menjaganya berjalan bahkan jika Anda keluar.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Proses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Berhenti setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dasbor Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dasbor

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menghidupkan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat kesalahan, dan banyak lagi.

**Sesuaikan Itu**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana saja (berguna untuk melihat jarak jauh).
- `--port 8080`: Gunakan port berbeda jika 8001 digunakan.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Kunjungi URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dasbor karena mungkin ada firewall dan langkah keamanan lainnya. Berikut beberapa pilihan:

- **Gunakan Reverse Proxy**: Atur Nginx atau Apache untuk meneruskan permintaan ke dasbor.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk mentunnel dasbor ke mesin lokal Anda.
- **VPN**: Jika server Anda di balik VPN, sambungkan ke sana dan akses dasbor langsung.
- **Konfigurasi Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasi Apache/Nginx**: Jika Anda memiliki server web di depan aplikasi Anda, Anda dapat mengonfigurasikannya ke domain atau subdomain. Jika Anda melakukan ini, Anda akan mengatur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Ingin dasbor yang berbeda?

Anda dapat membangun dasbor Anda sendiri jika Anda mau! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dasbor Anda sendiri!

## Fitur Dasbor

Dasbor adalah markas APM Anda—ini yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan cap waktu, URL, kode respons, dan waktu total. Klik “Detail” untuk middleware, kueri, dan kesalahan.
- **Permintaan Terlambat**: 5 permintaan teratas yang menghabiskan waktu (misalnya, “/api/heavy” pada 2.5s).
- **Rute Terlambat**: 5 rute teratas berdasarkan waktu rata-rata—bagus untuk menemukan pola.
- **Tingkat Kesalahan**: Persentase permintaan yang gagal (misalnya, 2.3% 500s).
- **Persentil Latensi**: 95th (p95) dan 99th (p99) waktu respons—ketahui skenario terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Kueri/Middleware Panjang**: 5 panggilan basis data lambat dan lapisan middleware teratas.
- **Cache Hit/Miss**: Seberapa sering cache menyelamatkan hari Anda.

**Ekstra**:
- Filter berdasarkan “Last Hour,” “Last Day,” atau “Last Week”.
- Alihkan mode gelap untuk sesi malam yang larut.

**Contoh**:
Permintaan ke `/users` mungkin menunjukkan:
- Total Time: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit on `user_list` (5ms)

## Menambahkan Acara Kustom

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent; // Impor untuk acara kustom

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana itu muncul?**
Di detail permintaan dasbor di bawah “Custom Events”—dapat diperluas dengan pemformatan JSON yang bagus.

**Kasus Penggunaan**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Sekarang Anda akan melihat jika API itu menarik aplikasi Anda ke bawah!

## Pemantauan Basis Data

Lacak kueri PDO seperti ini:

```php
use flight\database\PdoWrapper; // Impor untuk wrapper PDO

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang Anda Dapatkan**:
- Teks kueri (misalnya, `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (misalnya, 0.015s)
- Jumlah baris (misalnya, 42)

**Peringatan**:
- **Opsional**: Lewati ini jika Anda tidak perlu pelacakan DB.
- **Hanya PdoWrapper**: PDO inti belum dihubungkan—tetap ikuti!
- **Peringatan Performa**: Mencatat setiap kueri di situs yang berat DB dapat memperlambat hal-hal. Gunakan pengambilan sampel (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Keluaran Contoh**:
- Query: `SELECT name FROM products WHERE price > 100`
- Time: 0.023s
- Rows: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Batas pada 500 metrik—menjaganya terbatas.
- `--batch_size 200`: Proses 200 sekaligus—menyeimbangkan kecepatan dan memori.
- `--daemon`: Berjalan tanpa henti—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## ID Permintaan di Aplikasi

Setiap permintaan memiliki ID permintaan unik untuk pelacakan. Anda dapat menggunakan ID ini di aplikasi Anda untuk menghubungkan log dan metrik. Misalnya, Anda dapat menambahkan ID permintaan ke halaman kesalahan:

```php
Flight::map('error', function($message) {
	// Dapatkan ID permintaan dari header respons X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Selain itu, Anda bisa mengambilnya dari variabel Flight
	// Metode ini tidak akan berfungsi dengan baik di swoole atau platform async lainnya.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Meningkatkan

Jika Anda meningkatkan ke versi APM yang lebih baru, ada kemungkinan migrasi basis data yang perlu dijalankan. Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema basis data ke versi terbaru.

**Catatan:** Jika basis data APM Anda besar, migrasi ini mungkin memakan waktu. Anda mungkin ingin menjalankan perintah ini selama jam non-puncak.

## Menghapus Data Lama

Untuk menjaga basis data Anda rapi, Anda dapat menghapus data lama. Ini sangat berguna jika Anda menjalankan aplikasi yang sibuk dan ingin menjaga ukuran basis data tetap terkelola.
Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data yang lebih lama dari 30 hari dari basis data. Anda dapat menyesuaikan jumlah hari dengan meneruskan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data yang lebih lama dari 7 hari dari basis data.

## Memecahkan Masalah

Tertahan? Coba ini:

- **Tidak Ada Data Dasbor?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Jalur konfigurasi cocok? Verifikasi DSN di `.runway-config.json` menunjuk ke file asli.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik yang tertunda.

- **Kesalahan Worker?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dasbor Tidak Akan Dimulai?**
  - Port 8001 digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan tingkat sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.

- **Tidak Melacak Pengecualian/Kesalahan?**
  - Jika Anda memiliki [Tracy](https://tracy.nette.org/) diaktifkan untuk proyek Anda, itu akan menimpa penanganan kesalahan Flight. Anda perlu menonaktifkan Tracy dan pastikan `Flight::set('flight.handle_errors', true);` diatur.

- **Tidak Melacak Kueri Basis Data?**
  - Pastikan Anda menggunakan `PdoWrapper` untuk koneksi basis data Anda.
  - Pastikan Anda membuat argumen terakhir di konstruktor `true`.# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk mengatur, menggunakan, dan menguasai Pemantauan Kinerja Aplikasi (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan yang lambat atau hanya ingin bersemangat dengan grafik latensi, kami sudah menutupinya. Mari buat aplikasi Anda lebih cepat, pengguna lebih bahagia, dan sesi debugging lebih mudah!

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda seperti restoran yang sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur terganggu, Anda hanya menebak mengapa pelanggan pergi dengan marah. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari permintaan masuk hingga kueri basis data, dan menandai apa saja yang memperlambat Anda. Halaman yang lambat kehilangan pengguna (studi mengatakan 53% mundur jika situs memakan waktu lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah *sebelum* menyakiti. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “mengapa ini rusak?”, lebih banyak kemenangan “lihat betapa licinnya ini berjalan!”.

## Pemasangan

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda memerlukan:
- **PHP 7.4+**: Menjaga kami kompatibel dengan distribusi Linux LTS sambil mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Kerangka ringan yang kami tingkatkan.

## Basis Data yang Didukung

FlightPHP APM saat ini mendukung basis data berikut untuk menyimpan metrik:

- **SQLite3**: Sederhana, berbasis file, dan bagus untuk pengembangan lokal atau aplikasi kecil. Pilihan default dalam sebagian besar pengaturan.
- **MySQL/MariaDB**: Ideal untuk proyek yang lebih besar atau lingkungan produksi di mana Anda memerlukan penyimpanan yang kuat dan skalabel.

Anda dapat memilih tipe basis data selama langkah konfigurasi (lihat di bawah). Pastikan lingkungan PHP Anda memiliki ekstensi yang diperlukan terpasang (misalnya, `pdo_sqlite` atau `pdo_mysql`).

## Memulai

Inilah langkah-demi-langkah untuk kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke dalam `index.php` atau file `services.php` untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory; // Impor untuk membuat logger
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Membuat logger dari konfigurasi
$Apm = new Apm($ApmLogger); // Inisialisasi APM
$Apm->bindEventsToFlightInstance($app); // Mengikat acara ke instance Flight

// Jika Anda menambahkan koneksi basis data
// Harus berupa PdoWrapper atau PdoQueryCapture dari Ekstensi Tracy
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut tentang itu sebentar lagi) dan mengatur logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan acara Flight (permintaan, rute, kesalahan, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengikat semuanya ke aplikasi Flight Anda.

**Tip Pro: Pengambilan Sampel**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani. Gunakan tingkat sampel (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% dari permintaan
```

Ini menjaga performa tetap cepat sambil tetap memberikan data yang solid.

### 2. Konfigurasikan Itu

Jalankan ini untuk membuat `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan di mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
- Default adalah SQLite—misalnya, `sqlite:/tmp/apm_metrics.sqlite` untuk sumber, yang lain untuk tujuan.
- Anda akan mendapatkan konfigurasi seperti:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Proses ini juga akan menanyakan apakah Anda ingin menjalankan migrasi untuk pengaturan ini. Jika Anda mengatur ini untuk pertama kalinya, jawabannya adalah ya.

**Mengapa dua lokasi?**
Metrik mentah menumpuk dengan cepat (pikirkan log yang tidak disaring). Worker memprosesnya menjadi tujuan yang terstruktur untuk dasbor. Menjaga segala sesuatu rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data yang siap dasbor. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke tujuan Anda.
- Berhenti saat selesai atau jika tidak ada metrik yang tersisa.

**Jaga Agar Tetap Berjalan**
Untuk aplikasi langsung, Anda ingin pemrosesan kontinu. Berikut pilihan Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat datang. Bagus untuk dev atau pengaturan kecil.

- **Crontab**:
  Tambahkan ini ke crontab Anda (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Berjalan setiap menit—sempurna untuk produksi.

- **Tmux/Screen**:
  Mulai sesi yang dapat dilepas:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, kemudian D untuk melepaskan; `tmux attach -t apm-worker` untuk menyambung kembali
  ```
  Menjaganya berjalan bahkan jika Anda keluar.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Proses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Berhenti setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dasbor Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dasbor

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menghidupkan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat kesalahan, dan banyak lagi.

**Sesuaikan Itu**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana saja (berguna untuk melihat jarak jauh).
- `--port 8080`: Gunakan port berbeda jika 8001 digunakan.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Kunjungi URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dasbor karena mungkin ada firewall dan langkah keamanan lainnya. Berikut beberapa pilihan:

- **Gunakan Reverse Proxy**: Atur Nginx atau Apache untuk meneruskan permintaan ke dasbor.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk mentunnel dasbor ke mesin lokal Anda.
- **VPN**: Jika server Anda di balik VPN, sambungkan ke sana dan akses dasbor langsung.
- **Konfigurasi Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasi Apache/Nginx**: Jika Anda memiliki server web di depan aplikasi Anda, Anda dapat mengonfigurasikannya ke domain atau subdomain. Jika Anda melakukan ini, Anda akan mengatur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Ingin dasbor yang berbeda?

Anda dapat membangun dasbor Anda sendiri jika Anda mau! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dasbor Anda sendiri!

## Fitur Dasbor

Dasbor adalah markas APM Anda—ini yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan cap waktu, URL, kode respons, dan waktu total. Klik “Detail” untuk middleware, kueri, dan kesalahan.
- **Permintaan Terlambat**: 5 permintaan teratas yang menghabiskan waktu (misalnya, “/api/heavy” pada 2.5s).
- **Rute Terlambat**: 5 rute teratas berdasarkan waktu rata-rata—bagus untuk menemukan pola.
- **Tingkat Kesalahan**: Persentase permintaan yang gagal (misalnya, 2.3% 500s).
- **Persentil Latensi**: 95th (p95) dan 99th (p99) waktu respons—ketahui skenario terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Kueri/Middleware Panjang**: 5 panggilan basis data lambat dan lapisan middleware teratas.
- **Cache Hit/Miss**: Seberapa sering cache menyelamatkan hari Anda.

**Ekstra**:
- Filter berdasarkan “Last Hour,” “Last Day,” atau “Last Week”.
- Alihkan mode gelap untuk sesi malam yang larut.

**Contoh**:
Permintaan ke `/users` mungkin menunjukkan:
- Total Time: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit on `user_list` (5ms)

## Menambahkan Acara Kustom

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent; // Impor untuk acara kustom

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana itu muncul?**
Di detail permintaan dasbor di bawah “Custom Events”—dapat diperluas dengan pemformatan JSON yang bagus.

**Kasus Penggunaan**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Sekarang Anda akan melihat jika API itu menarik aplikasi Anda ke bawah!

## Pemantauan Basis Data

Lacak kueri PDO seperti ini:

```php
use flight\database\PdoWrapper; // Impor untuk wrapper PDO

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang Anda Dapatkan**:
- Teks kueri (misalnya, `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (misalnya, 0.015s)
- Jumlah baris (misalnya, 42)

**Peringatan**:
- **Opsional**: Lewati ini jika Anda tidak perlu pelacakan DB.
- **Hanya PdoWrapper**: PDO inti belum dihubungkan—tetap ikuti!
- **Peringatan Performa**: Mencatat setiap kueri di situs yang berat DB dapat memperlambat hal-hal. Gunakan pengambilan sampel (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Keluaran Contoh**:
- Query: `SELECT name FROM products WHERE price > 100`
- Time: 0.023s
- Rows: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Batas pada 500 metrik—menjaganya terbatas.
- `--batch_size 200`: Proses 200 sekaligus—menyeimbangkan kecepatan dan memori.
- `--daemon`: Berjalan tanpa henti—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## ID Permintaan di Aplikasi

Setiap permintaan memiliki ID permintaan unik untuk pelacakan. Anda dapat menggunakan ID ini di aplikasi Anda untuk menghubungkan log dan metrik. Misalnya, Anda dapat menambahkan ID permintaan ke halaman kesalahan:

```php
Flight::map('error', function($message) {
	// Dapatkan ID permintaan dari header respons X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Selain itu, Anda bisa mengambilnya dari variabel Flight
	// Metode ini tidak akan berfungsi dengan baik di swoole atau platform async lainnya.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Meningkatkan

Jika Anda meningkatkan ke versi APM yang lebih baru, ada kemungkinan migrasi basis data yang perlu dijalankan. Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema basis data ke versi terbaru.

**Catatan:** Jika basis data APM Anda besar, migrasi ini mungkin memakan waktu. Anda mungkin ingin menjalankan perintah ini selama jam non-puncak.

## Menghapus Data Lama

Untuk menjaga basis data Anda rapi, Anda dapat menghapus data lama. Ini sangat berguna jika Anda menjalankan aplikasi yang sibuk dan ingin menjaga ukuran basis data tetap terkelola.
Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data yang lebih lama dari 30 hari dari basis data. Anda dapat menyesuaikan jumlah hari dengan meneruskan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data yang lebih lama dari 7 hari dari basis data.

## Memecahkan Masalah

Tertahan? Coba ini:

- **Tidak Ada Data Dasbor?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Jalur konfigurasi cocok? Verifikasi DSN di `.runway-config.json` menunjuk ke file asli.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik yang tertunda.

- **Kesalahan Worker?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dasbor Tidak Akan Dimulai?**
  - Port 8001 digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan tingkat sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.

- **Tidak Melacak Pengecualian/Kesalahan?**
  - Jika Anda memiliki [Tracy](https://tracy.nette.org/) diaktifkan untuk proyek Anda, itu akan menimpa penanganan kesalahan Flight. Anda perlu menonaktifkan Tracy dan pastikan `Flight::set('flight.handle_errors', true);` diatur.

- **Tidak Melacak Kueri Basis Data?**
  - Pastikan Anda menggunakan `PdoWrapper` untuk koneksi basis data Anda.
  - Pastikan Anda membuat argumen terakhir di konstruktor `true`.# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk mengatur, menggunakan, dan menguasai Pemantauan Kinerja Aplikasi (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan yang lambat atau hanya ingin bersemangat dengan grafik latensi, kami sudah menutupinya. Mari buat aplikasi Anda lebih cepat, pengguna lebih bahagia, dan sesi debugging lebih mudah!

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda seperti restoran yang sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur terganggu, Anda hanya menebak mengapa pelanggan pergi dengan marah. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari permintaan masuk hingga kueri basis data, dan menandai apa saja yang memperlambat Anda. Halaman yang lambat kehilangan pengguna (studi mengatakan 53% mundur jika situs memakan waktu lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah *sebelum* menyakiti. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “mengapa ini rusak?”, lebih banyak kemenangan “lihat betapa licinnya ini berjalan!”.

## Pemasangan

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda memerlukan:
- **PHP 7.4+**: Menjaga kami kompatibel dengan distribusi Linux LTS sambil mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Kerangka ringan yang kami tingkatkan.

## Basis Data yang Didukung

FlightPHP APM saat ini mendukung basis data berikut untuk menyimpan metrik:

- **SQLite3**: Sederhana, berbasis file, dan bagus untuk pengembangan lokal atau aplikasi kecil. Pilihan default dalam sebagian besar pengaturan.
- **MySQL/MariaDB**: Ideal untuk proyek yang lebih besar atau lingkungan produksi di mana Anda memerlukan penyimpanan yang kuat dan skalabel.

Anda dapat memilih tipe basis data selama langkah konfigurasi (lihat di bawah). Pastikan lingkungan PHP Anda memiliki ekstensi yang diperlukan terpasang (misalnya, `pdo_sqlite` atau `pdo_mysql`).

## Memulai

Inilah langkah-demi-langkah untuk kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke dalam `index.php` atau file `services.php` untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory; // Impor untuk membuat logger
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Membuat logger dari konfigurasi
$Apm = new Apm($ApmLogger); // Inisialisasi APM
$Apm->bindEventsToFlightInstance($app); // Mengikat acara ke instance Flight

// Jika Anda menambahkan koneksi basis data
// Harus berupa PdoWrapper atau PdoQueryCapture dari Ekstensi Tracy
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut tentang itu sebentar lagi) dan mengatur logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan acara Flight (permintaan, rute, kesalahan, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengikat semuanya ke aplikasi Flight Anda.

**Tip Pro: Pengambilan Sampel**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani. Gunakan tingkat sampel (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% dari permintaan
```

Ini menjaga performa tetap cepat sambil tetap memberikan data yang solid.

### 2. Konfigurasikan Itu

Jalankan ini untuk membuat `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan di mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
- Default adalah SQLite—misalnya, `sqlite:/tmp/apm_metrics.sqlite` untuk sumber, yang lain untuk tujuan.
- Anda akan mendapatkan konfigurasi seperti:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Proses ini juga akan menanyakan apakah Anda ingin menjalankan migrasi untuk pengaturan ini. Jika Anda mengatur ini untuk pertama kalinya, jawabannya adalah ya.

**Mengapa dua lokasi?**
Metrik mentah menumpuk dengan cepat (pikirkan log yang tidak disaring). Worker memprosesnya menjadi tujuan yang terstruktur untuk dasbor. Menjaga segala sesuatu rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data yang siap dasbor. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke tujuan Anda.
- Berhenti saat selesai atau jika tidak ada metrik yang tersisa.

**Jaga Agar Tetap Berjalan**
Untuk aplikasi langsung, Anda ingin pemrosesan kontinu. Berikut pilihan Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat datang. Bagus untuk dev atau pengaturan kecil.

- **Crontab**:
  Tambahkan ini ke crontab Anda (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Berjalan setiap menit—sempurna untuk produksi.

- **Tmux/Screen**:
  Mulai sesi yang dapat dilepas:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, kemudian D untuk melepaskan; `tmux attach -t apm-worker` untuk menyambung kembali
  ```
  Menjaganya berjalan bahkan jika Anda keluar.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Proses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Berhenti setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dasbor Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dasbor

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menghidupkan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat kesalahan, dan banyak lagi.

**Sesuaikan Itu**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana saja (berguna untuk melihat jarak jauh).
- `--port 8080`: Gunakan port berbeda jika 8001 digunakan.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Kunjungi URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dasbor karena mungkin ada firewall dan langkah keamanan lainnya. Berikut beberapa pilihan:

- **Gunakan Reverse Proxy**: Atur Nginx atau Apache untuk meneruskan permintaan ke dasbor.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk mentunnel dasbor ke mesin lokal Anda.
- **VPN**: Jika server Anda di balik VPN, sambungkan ke sana dan akses dasbor langsung.
- **Konfigurasi Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasi Apache/Nginx**: Jika Anda memiliki server web di depan aplikasi Anda, Anda dapat mengonfigurasikannya ke domain atau subdomain. Jika Anda melakukan ini, Anda akan mengatur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Ingin dasbor yang berbeda?

Anda dapat membangun dasbor Anda sendiri jika Anda mau! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dasbor Anda sendiri!

## Fitur Dasbor

Dasbor adalah markas APM Anda—ini yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan cap waktu, URL, kode respons, dan waktu total. Klik “Detail” untuk middleware, kueri, dan kesalahan.
- **Permintaan Terlambat**: 5 permintaan teratas yang menghabiskan waktu (misalnya, “/api/heavy” pada 2.5s).
- **Rute Terlambat**: 5 rute teratas berdasarkan waktu rata-rata—bagus untuk menemukan pola.
- **Tingkat Kesalahan**: Persentase permintaan yang gagal (misalnya, 2.3% 500s).
- **Persentil Latensi**: 95th (p95) dan 99th (p99) waktu respons—ketahui skenario terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Kueri/Middleware Panjang**: 5 panggilan basis data lambat dan lapisan middleware teratas.
- **Cache Hit/Miss**: Seberapa sering cache menyelamatkan hari Anda.

**Ekstra**:
- Filter berdasarkan “Last Hour,” “Last Day,” atau “Last Week”.
- Alihkan mode gelap untuk sesi malam yang larut.

**Contoh**:
Permintaan ke `/users` mungkin menunjukkan:
- Total Time: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit on `user_list` (5ms)

## Menambahkan Acara Kustom

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent; // Impor untuk acara kustom

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana itu muncul?**
Di detail permintaan dasbor di bawah “Custom Events”—dapat diperluas dengan pemformatan JSON yang bagus.

**Kasus Penggunaan**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Sekarang Anda akan melihat jika API itu menarik aplikasi Anda ke bawah!

## Pemantauan Basis Data

Lacak kueri PDO seperti ini:

```php
use flight\database\PdoWrapper; // Impor untuk wrapper PDO

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang Anda Dapatkan**:
- Teks kueri (misalnya, `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (misalnya, 0.015s)
- Jumlah baris (misalnya, 42)

**Peringatan**:
- **Opsional**: Lewati ini jika Anda tidak perlu pelacakan DB.
- **Hanya PdoWrapper**: PDO inti belum dihubungkan—tetap ikuti!
- **Peringatan Performa**: Mencatat setiap kueri di situs yang berat DB dapat memperlambat hal-hal. Gunakan pengambilan sampel (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Keluaran Contoh**:
- Query: `SELECT name FROM products WHERE price > 100`
- Time: 0.023s
- Rows: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Batas pada 500 metrik—menjaganya terbatas.
- `--batch_size 200`: Proses 200 sekaligus—menyeimbangkan kecepatan dan memori.
- `--daemon`: Berjalan tanpa henti—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## ID Permintaan di Aplikasi

Setiap permintaan memiliki ID permintaan unik untuk pelacakan. Anda dapat menggunakan ID ini di aplikasi Anda untuk menghubungkan log dan metrik. Misalnya, Anda dapat menambahkan ID permintaan ke halaman kesalahan:

```php
Flight::map('error', function($message) {
	// Dapatkan ID permintaan dari header respons X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Selain itu, Anda bisa mengambilnya dari variabel Flight
	// Metode ini tidak akan berfungsi dengan baik di swoole atau platform async lainnya.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Meningkatkan

Jika Anda meningkatkan ke versi APM yang lebih baru, ada kemungkinan migrasi basis data yang perlu dijalankan. Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema basis data ke versi terbaru.

**Catatan:** Jika basis data APM Anda besar, migrasi ini mungkin memakan waktu. Anda mungkin ingin menjalankan perintah ini selama jam non-puncak.

## Menghapus Data Lama

Untuk menjaga basis data Anda rapi, Anda dapat menghapus data lama. Ini sangat berguna jika Anda menjalankan aplikasi yang sibuk dan ingin menjaga ukuran basis data tetap terkelola.
Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data yang lebih lama dari 30 hari dari basis data. Anda dapat menyesuaikan jumlah hari dengan meneruskan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data yang lebih lama dari 7 hari dari basis data.

## Memecahkan Masalah

Tertahan? Coba ini:

- **Tidak Ada Data Dasbor?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Jalur konfigurasi cocok? Verifikasi DSN di `.runway-config.json` menunjuk ke file asli.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik yang tertunda.

- **Kesalahan Worker?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dasbor Tidak Akan Dimulai?**
  - Port 8001 digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan tingkat sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.

- **Tidak Melacak Pengecualian/Kesalahan?**
  - Jika Anda memiliki [Tracy](https://tracy.nette.org/) diaktifkan untuk proyek Anda, itu akan menimpa penanganan kesalahan Flight. Anda perlu menonaktifkan Tracy dan pastikan `Flight::set('flight.handle_errors', true);` diatur.

- **Tidak Melacak Kueri Basis Data?**
  - Pastikan Anda menggunakan `PdoWrapper` untuk koneksi basis data Anda.
  - Pastikan Anda membuat argumen terakhir di konstruktor `true`.# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk mengatur, menggunakan, dan menguasai Pemantauan Kinerja Aplikasi (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan yang lambat atau hanya ingin bersemangat dengan grafik latensi, kami sudah menutupinya. Mari buat aplikasi Anda lebih cepat, pengguna lebih bahagia, dan sesi debugging lebih mudah!

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda seperti restoran yang sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur terganggu, Anda hanya menebak mengapa pelanggan pergi dengan marah. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari permintaan masuk hingga kueri basis data, dan menandai apa saja yang memperlambat Anda. Halaman yang lambat kehilangan pengguna (studi mengatakan 53% mundur jika situs memakan waktu lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah *sebelum* menyakiti. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “mengapa ini rusak?”, lebih banyak kemenangan “lihat betapa licinnya ini berjalan!”.

## Pemasangan

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda memerlukan:
- **PHP 7.4+**: Menjaga kami kompatibel dengan distribusi Linux LTS sambil mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Kerangka ringan yang kami tingkatkan.

## Basis Data yang Didukung

FlightPHP APM saat ini mendukung basis data berikut untuk menyimpan metrik:

- **SQLite3**: Sederhana, berbasis file, dan bagus untuk pengembangan lokal atau aplikasi kecil. Pilihan default dalam sebagian besar pengaturan.
- **MySQL/MariaDB**: Ideal untuk proyek yang lebih besar atau lingkungan produksi di mana Anda memerlukan penyimpanan yang kuat dan skalabel.

Anda dapat memilih tipe basis data selama langkah konfigurasi (lihat di bawah). Pastikan lingkungan PHP Anda memiliki ekstensi yang diperlukan terpasang (misalnya, `pdo_sqlite` atau `pdo_mysql`).

## Memulai

Inilah langkah-demi-langkah untuk kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke dalam `index.php` atau file `services.php` untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory; // Impor untuk membuat logger
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Membuat logger dari konfigurasi
$Apm = new Apm($ApmLogger); // Inisialisasi APM
$Apm->bindEventsToFlightInstance($app); // Mengikat acara ke instance Flight

// Jika Anda menambahkan koneksi basis data
// Harus berupa PdoWrapper atau PdoQueryCapture dari Ekstensi Tracy
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut tentang itu sebentar lagi) dan mengatur logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan acara Flight (permintaan, rute, kesalahan, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengikat semuanya ke aplikasi Flight Anda.

**Tip Pro: Pengambilan Sampel**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani. Gunakan tingkat sampel (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% dari permintaan
```

Ini menjaga performa tetap cepat sambil tetap memberikan data yang solid.

### 2. Konfigurasikan Itu

Jalankan ini untuk membuat `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan di mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
- Default adalah SQLite—misalnya, `sqlite:/tmp/apm_metrics.sqlite` untuk sumber, yang lain untuk tujuan.
- Anda akan mendapatkan konfigurasi seperti:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Proses ini juga akan menanyakan apakah Anda ingin menjalankan migrasi untuk pengaturan ini. Jika Anda mengatur ini untuk pertama kalinya, jawabannya adalah ya.

**Mengapa dua lokasi?**
Metrik mentah menumpuk dengan cepat (pikirkan log yang tidak disaring). Worker memprosesnya menjadi tujuan yang terstruktur untuk dasbor. Menjaga segala sesuatu rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data yang siap dasbor. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke tujuan Anda.
- Berhenti saat selesai atau jika tidak ada metrik yang tersisa.

**Jaga Agar Tetap Berjalan**
Untuk aplikasi langsung, Anda ingin pemrosesan kontinu. Berikut pilihan Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat datang. Bagus untuk dev atau pengaturan kecil.

- **Crontab**:
  Tambahkan ini ke crontab Anda (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Berjalan setiap menit—sempurna untuk produksi.

- **Tmux/Screen**:
  Mulai sesi yang dapat dilepas:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, kemudian D untuk melepaskan; `tmux attach -t apm-worker` untuk menyambung kembali
  ```
  Menjaganya berjalan bahkan jika Anda keluar.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Proses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Berhenti setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dasbor Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dasbor

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menghidupkan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat kesalahan, dan banyak lagi.

**Sesuaikan Itu**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana saja (berguna untuk melihat jarak jauh).
- `--port 8080`: Gunakan port berbeda jika 8001 digunakan.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Kunjungi URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dasbor karena mungkin ada firewall dan langkah keamanan lainnya. Berikut beberapa pilihan:

- **Gunakan Reverse Proxy**: Atur Nginx atau Apache untuk meneruskan permintaan ke dasbor.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk mentunnel dasbor ke mesin lokal Anda.
- **VPN**: Jika server Anda di balik VPN, sambungkan ke sana dan akses dasbor langsung.
- **Konfigurasi Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasi Apache/Nginx**: Jika Anda memiliki server web di depan aplikasi Anda, Anda dapat mengonfigurasikannya ke domain atau subdomain. Jika Anda melakukan ini, Anda akan mengatur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Ingin dasbor yang berbeda?

Anda dapat membangun dasbor Anda sendiri jika Anda mau! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dasbor Anda sendiri!

## Fitur Dasbor

Dasbor adalah markas APM Anda—ini yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan cap waktu, URL, kode respons, dan waktu total. Klik “Detail” untuk middleware, kueri, dan kesalahan.
- **Permintaan Terlambat**: 5 permintaan teratas yang menghabiskan waktu (misalnya, “/api/heavy” pada 2.5s).
- **Rute Terlambat**: 5 rute teratas berdasarkan waktu rata-rata—bagus untuk menemukan pola.
- **Tingkat Kesalahan**: Persentase permintaan yang gagal (misalnya, 2.3% 500s).
- **Persentil Latensi**: 95th (p95) dan 99th (p99) waktu respons—ketahui skenario terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Kueri/Middleware Panjang**: 5 panggilan basis data lambat dan lapisan middleware teratas.
- **Cache Hit/Miss**: Seberapa sering cache menyelamatkan hari Anda.

**Ekstra**:
- Filter berdasarkan “Last Hour,” “Last Day,” atau “Last Week”.
- Alihkan mode gelap untuk sesi malam yang larut.

**Contoh**:
Permintaan ke `/users` mungkin menunjukkan:
- Total Time: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit on `user_list` (5ms)

## Menambahkan Acara Kustom

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent; // Impor untuk acara kustom

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana itu muncul?**
Di detail permintaan dasbor di bawah “Custom Events”—dapat diperluas dengan pemformatan JSON yang bagus.

**Kasus Penggunaan**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Sekarang Anda akan melihat jika API itu menarik aplikasi Anda ke bawah!

## Pemantauan Basis Data

Lacak kueri PDO seperti ini:

```php
use flight\database\PdoWrapper; // Impor untuk wrapper PDO

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang Anda Dapatkan**:
- Teks kueri (misalnya, `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (misalnya, 0.015s)
- Jumlah baris (misalnya, 42)

**Peringatan**:
- **Opsional**: Lewati ini jika Anda tidak perlu pelacakan DB.
- **Hanya PdoWrapper**: PDO inti belum dihubungkan—tetap ikuti!
- **Peringatan Performa**: Mencatat setiap kueri di situs yang berat DB dapat memperlambat hal-hal. Gunakan pengambilan sampel (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Keluaran Contoh**:
- Query: `SELECT name FROM products WHERE price > 100`
- Time: 0.023s
- Rows: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Batas pada 500 metrik—menjaganya terbatas.
- `--batch_size 200`: Proses 200 sekaligus—menyeimbangkan kecepatan dan memori.
- `--daemon`: Berjalan tanpa henti—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## ID Permintaan di Aplikasi

Setiap permintaan memiliki ID permintaan unik untuk pelacakan. Anda dapat menggunakan ID ini di aplikasi Anda untuk menghubungkan log dan metrik. Misalnya, Anda dapat menambahkan ID permintaan ke halaman kesalahan:

```php
Flight::map('error', function($message) {
	// Dapatkan ID permintaan dari header respons X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Selain itu, Anda bisa mengambilnya dari variabel Flight
	// Metode ini tidak akan berfungsi dengan baik di swoole atau platform async lainnya.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Meningkatkan

Jika Anda meningkatkan ke versi APM yang lebih baru, ada kemungkinan migrasi basis data yang perlu dijalankan. Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema basis data ke versi terbaru.

**Catatan:** Jika basis data APM Anda besar, migrasi ini mungkin memakan waktu. Anda mungkin ingin menjalankan perintah ini selama jam non-puncak.

## Menghapus Data Lama

Untuk menjaga basis data Anda rapi, Anda dapat menghapus data lama. Ini sangat berguna jika Anda menjalankan aplikasi yang sibuk dan ingin menjaga ukuran basis data tetap terkelola.
Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data yang lebih lama dari 30 hari dari basis data. Anda dapat menyesuaikan jumlah hari dengan meneruskan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data yang lebih lama dari 7 hari dari basis data.

## Memecahkan Masalah

Tertahan? Coba ini:

- **Tidak Ada Data Dasbor?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Jalur konfigurasi cocok? Verifikasi DSN di `.runway-config.json` menunjuk ke file asli.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik yang tertunda.

- **Kesalahan Worker?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dasbor Tidak Akan Dimulai?**
  - Port 8001 digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan tingkat sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.

- **Tidak Melacak Pengecualian/Kesalahan?**
  - Jika Anda memiliki [Tracy](https://tracy.nette.org/) diaktifkan untuk proyek Anda, itu akan menimpa penanganan kesalahan Flight. Anda perlu menonaktifkan Tracy dan pastikan `Flight::set('flight.handle_errors', true);` diatur.

- **Tidak Melacak Kueri Basis Data?**
  - Pastikan Anda menggunakan `PdoWrapper` untuk koneksi basis data Anda.
  - Pastikan Anda membuat argumen terakhir di konstruktor `true`.# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk mengatur, menggunakan, dan menguasai Pemantauan Kinerja Aplikasi (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan yang lambat atau hanya ingin bersemangat dengan grafik latensi, kami sudah menutupinya. Mari buat aplikasi Anda lebih cepat, pengguna lebih bahagia, dan sesi debugging lebih mudah!

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda seperti restoran yang sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur terganggu, Anda hanya menebak mengapa pelanggan pergi dengan marah. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari permintaan masuk hingga kueri basis data, dan menandai apa saja yang memperlambat Anda. Halaman yang lambat kehilangan pengguna (studi mengatakan 53% mundur jika situs memakan waktu lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah *sebelum* menyakiti. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “mengapa ini rusak?”, lebih banyak kemenangan “lihat betapa licinnya ini berjalan!”.

## Pemasangan

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda memerlukan:
- **PHP 7.4+**: Menjaga kami kompatibel dengan distribusi Linux LTS sambil mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Kerangka ringan yang kami tingkatkan.

## Basis Data yang Didukung

FlightPHP APM saat ini mendukung basis data berikut untuk menyimpan metrik:

- **SQLite3**: Sederhana, berbasis file, dan bagus untuk pengembangan lokal atau aplikasi kecil. Pilihan default dalam sebagian besar pengaturan.
- **MySQL/MariaDB**: Ideal untuk proyek yang lebih besar atau lingkungan produksi di mana Anda memerlukan penyimpanan yang kuat dan skalabel.

Anda dapat memilih tipe basis data selama langkah konfigurasi (lihat di bawah). Pastikan lingkungan PHP Anda memiliki ekstensi yang diperlukan terpasang (misalnya, `pdo_sqlite` atau `pdo_mysql`).

## Memulai

Inilah langkah-demi-langkah untuk kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke dalam `index.php` atau file `services.php` untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory; // Impor untuk membuat logger
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Membuat logger dari konfigurasi
$Apm = new Apm($ApmLogger); // Inisialisasi APM
$Apm->bindEventsToFlightInstance($app); // Mengikat acara ke instance Flight

// Jika Anda menambahkan koneksi basis data
// Harus berupa PdoWrapper atau PdoQueryCapture dari Ekstensi Tracy
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut tentang itu sebentar lagi) dan mengatur logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan acara Flight (permintaan, rute, kesalahan, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengikat semuanya ke aplikasi Flight Anda.

**Tip Pro: Pengambilan Sampel**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani. Gunakan tingkat sampel (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% dari permintaan
```

### 2. Konfigurasikan Itu

Jalankan ini untuk membuat `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan di mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
- Default adalah SQLite—misalnya, `sqlite:/tmp/apm_metrics.sqlite` untuk sumber, yang lain untuk tujuan.
- Anda akan mendapatkan konfigurasi seperti:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Proses ini juga akan menanyakan apakah Anda ingin menjalankan migrasi untuk pengaturan ini. Jika Anda mengatur ini untuk pertama kalinya, jawabannya adalah ya.

**Mengapa dua lokasi?**
Metrik mentah menumpuk dengan cepat (pikirkan log yang tidak disaring). Worker memprosesnya menjadi tujuan yang terstruktur untuk dasbor. Menjaga segala sesuatu rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data yang siap dasbor. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke tujuan Anda.
- Berhenti saat selesai atau jika tidak ada metrik yang tersisa.

**Jaga Agar Tetap Berjalan**
Untuk aplikasi langsung, Anda ingin pemrosesan kontinu. Berikut pilihan Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat datang. Bagus untuk dev atau pengaturan kecil.

- **Crontab**:
  Tambahkan ini ke crontab Anda (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Berjalan setiap menit—sempurna untuk produksi.

- **Tmux/Screen**:
  Mulai sesi yang dapat dilepas:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, kemudian D untuk melepaskan; `tmux attach -t apm-worker` untuk menyambung kembali
  ```
  Menjaganya berjalan bahkan jika Anda keluar.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Proses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Berhenti setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dasbor Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dasbor

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menghidupkan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat kesalahan, dan banyak lagi.

**Sesuaikan Itu**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana saja (berguna untuk melihat jarak jauh).
- `--port 8080`: Gunakan port berbeda jika 8001 digunakan.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Kunjungi URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dasbor karena mungkin ada firewall dan langkah keamanan lainnya. Berikut beberapa pilihan:

- **Gunakan Reverse Proxy**: Atur Nginx atau Apache untuk meneruskan permintaan ke dasbor.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk mentunnel dasbor ke mesin lokal Anda.
- **VPN**: Jika server Anda di balik VPN, sambungkan ke sana dan akses dasbor langsung.
- **Konfigurasi Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasi Apache/Nginx**: Jika Anda memiliki server web di depan aplikasi Anda, Anda dapat mengonfigurasikannya ke domain atau subdomain. Jika Anda melakukan ini, Anda akan mengatur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Ingin dasbor yang berbeda?

Anda dapat membangun dasbor Anda sendiri jika Anda mau! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dasbor Anda sendiri!

## Fitur Dasbor

Dasbor adalah markas APM Anda—ini yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan cap waktu, URL, kode respons, dan waktu total. Klik “Detail” untuk middleware, kueri, dan kesalahan.
- **Permintaan Terlambat**: 5 permintaan teratas yang menghabiskan waktu (misalnya, “/api/heavy” pada 2.5s).
- **Rute Terlambat**: 5 rute teratas berdasarkan waktu rata-rata—bagus untuk menemukan pola.
- **Tingkat Kesalahan**: Persentase permintaan yang gagal (misalnya, 2.3% 500s).
- **Persentil Latensi**: 95th (p95) dan 99th (p99) waktu respons—ketahui skenario terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Kueri/Middleware Panjang**: 5 panggilan basis data lambat dan lapisan middleware teratas.
- **Cache Hit/Miss**: Seberapa sering cache menyelamatkan hari Anda.

**Ekstra**:
- Filter berdasarkan “Last Hour,” “Last Day,” atau “Last Week”.
- Alihkan mode gelap untuk sesi malam yang larut.

**Contoh**:
Permintaan ke `/users` mungkin menunjukkan:
- Total Time: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit on `user_list` (5ms)

## Menambahkan Acara Kustom

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent; // Impor untuk acara kustom

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana itu muncul?**
Di detail permintaan dasbor di bawah “Custom Events”—dapat diperluas dengan pemformatan JSON yang bagus.

**Kasus Penggunaan**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Sekarang Anda akan melihat jika API itu menarik aplikasi Anda ke bawah!

## Pemantauan Basis Data

Lacak kueri PDO seperti ini:

```php
use flight\database\PdoWrapper; // Impor untuk wrapper PDO

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang Anda Dapatkan**:
- Teks kueri (misalnya, `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (misalnya, 0.015s)
- Jumlah baris (misalnya, 42)

**Peringatan**:
- **Opsional**: Lewati ini jika Anda tidak perlu pelacakan DB.
- **Hanya PdoWrapper**: PDO inti belum dihubungkan—tetap ikuti!
- **Peringatan Performa**: Mencatat setiap kueri di situs yang berat DB dapat memperlambat hal-hal. Gunakan pengambilan sampel (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Keluaran Contoh**:
- Query: `SELECT name FROM products WHERE price > 100`
- Time: 0.023s
- Rows: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Batas pada 500 metrik—menjaganya terbatas.
- `--batch_size 200`: Proses 200 sekaligus—menyeimbangkan kecepatan dan memori.
- `--daemon`: Berjalan tanpa henti—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## ID Permintaan di Aplikasi

Setiap permintaan memiliki ID permintaan unik untuk pelacakan. Anda dapat menggunakan ID ini di aplikasi Anda untuk menghubungkan log dan metrik. Misalnya, Anda dapat menambahkan ID permintaan ke halaman kesalahan:

```php
Flight::map('error', function($message) {
	// Dapatkan ID permintaan dari header respons X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Selain itu, Anda bisa mengambilnya dari variabel Flight
	// Metode ini tidak akan berfungsi dengan baik di swoole atau platform async lainnya.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Meningkatkan

Jika Anda meningkatkan ke versi APM yang lebih baru, ada kemungkinan migrasi basis data yang perlu dijalankan. Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema basis data ke versi terbaru.

**Catatan:** Jika basis data APM Anda besar, migrasi ini mungkin memakan waktu. Anda mungkin ingin menjalankan perintah ini selama jam non-puncak.

## Menghapus Data Lama

Untuk menjaga basis data Anda rapi, Anda dapat menghapus data lama. Ini sangat berguna jika Anda menjalankan aplikasi yang sibuk dan ingin menjaga ukuran basis data tetap terkelola.
Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data yang lebih lama dari 30 hari dari basis data. Anda dapat menyesuaikan jumlah hari dengan meneruskan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data yang lebih lama dari 7 hari dari basis data.

## Memecahkan Masalah

Tertahan? Coba ini:

- **Tidak Ada Data Dasbor?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Jalur konfigurasi cocok? Verifikasi DSN di `.runway-config.json` menunjuk ke file asli.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik yang tertunda.

- **Kesalahan Worker?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dasbor Tidak Akan Dimulai?**
  - Port 8001 digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan tingkat sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.

- **Tidak Melacak Pengecualian/Kesalahan?**
  - Jika Anda memiliki [Tracy](https://tracy.nette.org/) diaktifkan untuk proyek Anda, itu akan menimpa penanganan kesalahan Flight. Anda perlu menonaktifkan Tracy dan pastikan `Flight::set('flight.handle_errors', true);` diatur.

- **Tidak Melacak Kueri Basis Data?**
  - Pastikan Anda menggunakan `PdoWrapper` untuk koneksi basis data Anda.
  - Pastikan Anda membuat argumen terakhir di konstruktor `true`.