# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk menyiapkan, menggunakan, dan menguasai Application Performance Monitoring (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan lambat atau hanya ingin antusias dengan grafik latensi, kami telah menyiapkannya untuk Anda. Mari buat aplikasi Anda lebih cepat, pengguna Anda lebih bahagia, dan sesi debugging Anda menjadi lebih mudah!

Lihat [demo](https://flightphp-docs-apm.sky-9.com/apm/dashboard) dari dashboard untuk situs Flight Docs.

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda adalah restoran sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur tersendat, Anda hanya menebak mengapa pelanggan pergi dengan kesal. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari permintaan masuk hingga kueri database, dan menandai apa pun yang memperlambat Anda. Halaman lambat membuat pengguna hilang (studi mengatakan 53% bounce jika situs memakan waktu lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah tersebut *sebelum* mereka menyengat. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “mengapa ini rusak?” dan lebih banyak kemenangan “lihat betapa lancarnya ini berjalan!”

## Instalasi

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda akan membutuhkan:
- **PHP 7.4+**: Menjaga kami kompatibel dengan distribusi Linux LTS sambil mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Framework ringan yang kami tingkatkan.

## Database yang Didukung

FlightPHP APM saat ini mendukung database berikut untuk menyimpan metrik:

- **SQLite3**: Sederhana, berbasis file, dan bagus untuk pengembangan lokal atau aplikasi kecil. Opsi default di sebagian besar pengaturan.
- **MySQL/MariaDB**: Ideal untuk proyek besar atau lingkungan produksi di mana Anda membutuhkan penyimpanan yang kuat dan skalabel.

Anda dapat memilih jenis database Anda selama langkah konfigurasi (lihat di bawah). Pastikan lingkungan PHP Anda memiliki ekstensi yang diperlukan terinstal (misalnya, `pdo_sqlite` atau `pdo_mysql`).

## Memulai

Berikut langkah demi langkah Anda menuju kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke dalam file `index.php` atau `services.php` Anda untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory;
use flight\database\PdoWrapper;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);

// Jika Anda menambahkan koneksi database
// Harus berupa PdoWrapper atau PdoQueryCapture dari Tracy Extensions
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut nanti) dan menyiapkan logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan peristiwa Flight (permintaan, rute, kesalahan, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengikat semuanya ke aplikasi Flight Anda.

**Tips Pro: Sampling**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani. Gunakan tingkat sampel (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% permintaan
```

Ini menjaga performa tetap cepat sambil tetap memberikan data yang solid.

### 2. Konfigurasikan Itu

Jalankan ini untuk membuat `.runway-config.json` Anda:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan dari mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
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

> Proses ini juga akan menanyakan apakah Anda ingin menjalankan migrasi untuk pengaturan ini. Jika Anda menyiapkannya untuk pertama kali, jawabannya ya.

**Mengapa dua lokasi?**
Metrik mentah menumpuk cepat (pikirkan log yang tidak difilter). Worker memprosesnya menjadi tujuan terstruktur untuk dashboard. Menjaga semuanya rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data siap dashboard. Jalankannya sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) menjadi tujuan Anda.
- Berhenti ketika selesai atau jika tidak ada metrik yang tersisa.

**Jaga Agar Tetap Berjalan**
Untuk aplikasi langsung, Anda akan ingin pemrosesan berkelanjutan. Berikut opsi Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat mereka datang. Bagus untuk dev atau pengaturan kecil.

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
  # Ctrl+B, kemudian D untuk melepaskan; `tmux attach -t apm-worker` untuk terhubung kembali
  ```
  Menjaganya berjalan bahkan jika Anda logout.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Proses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Keluar setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dashboard Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dashboard

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Memutar server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat kesalahan, dan banyak lagi.

**Kustomisasi Itu**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana pun (berguna untuk melihat jarak jauh).
- `--port 8080`: Gunakan port berbeda jika 8001 sudah digunakan.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Buka URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dashboard karena mungkin ada firewall dan langkah keamanan lainnya. Berikut beberapa opsi:

- **Gunakan Reverse Proxy**: Siapkan Nginx atau Apache untuk meneruskan permintaan ke dashboard.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk menyalurkan dashboard ke mesin lokal Anda.
- **VPN**: Jika server Anda di belakang VPN, hubungkan ke itu dan akses dashboard secara langsung.
- **Konfigurasikan Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasikan Apache/Nginx**: Jika Anda memiliki server web di depan aplikasi Anda, Anda bisa mengonfigurasinya ke domain atau subdomain. Jika Anda melakukan ini, Anda akan mengatur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`

#### Ingin dashboard yang berbeda?

Anda bisa membangun dashboard sendiri jika ingin! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dashboard Anda sendiri!

## Fitur Dashboard

Dashboard adalah markas APM Anda—berikut yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan cap waktu, URL, kode respons, dan waktu total. Klik “Detail” untuk middleware, kueri, dan kesalahan.
- **Permintaan Terlambat**: 5 permintaan teratas yang memakan waktu (misalnya, “/api/heavy” di 2.5s).
- **Rute Terlambat**: 5 rute teratas berdasarkan waktu rata-rata—bagus untuk melihat pola.
- **Tingkat Kesalahan**: Persentase permintaan yang gagal (misalnya, 2.3% 500s).
- **Persentil Latensi**: 95th (p95) dan 99th (p99) waktu respons—ketahui skenario kasus terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Kueri/Middleware Panjang**: 5 panggilan database lambat teratas dan lapisan middleware.
- **Cache Hit/Miss**: Seberapa sering cache Anda menyelamatkan hari.

**Ekstra**:
- Filter berdasarkan “Jam Terakhir,” “Hari Terakhir,” atau “Minggu Terakhir.”
- Toggle mode gelap untuk sesi malam hari itu.

**Contoh**:
Permintaan ke `/users` mungkin menunjukkan:
- Waktu Total: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Kueri: `SELECT * FROM users` (80ms)
- Cache: Hit pada `user_list` (5ms)

## Menambahkan Peristiwa Kustom

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana itu muncul?**
Di detail permintaan dashboard di bawah “Peristiwa Kustom”—dapat diperluas dengan format JSON yang bagus.

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
Sekarang Anda akan melihat jika API itu menyeret aplikasi Anda!

## Pemantauan Database

Lacak kueri PDO seperti ini:

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True diperlukan untuk mengaktifkan pelacakan di APM.
$Apm->addPdoConnection($pdo);
```

**Apa yang Anda Dapat**:
- Teks kueri (misalnya, `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (misalnya, 0.015s)
- Jumlah baris (misalnya, 42)

**Peringatan**:
- **Opsional**: Lewati ini jika Anda tidak membutuhkan pelacakan DB.
- **Hanya PdoWrapper**: PDO inti belum terhubung—pantau terus!
- **Peringatan Performa**: Mencatat setiap kueri di situs berat DB bisa memperlambat. Gunakan sampling (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Contoh Output**:
- Kueri: `SELECT name FROM products WHERE price > 100`
- Waktu: 0.023s
- Baris: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Dibatasi pada 500 metrik—menjaganya terbatas.
- `--batch_size 200`: Memproses 200 sekaligus—menseimbangkan kecepatan dan memori.
- `--daemon`: Berjalan tanpa henti—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## ID Permintaan di Aplikasi

Setiap permintaan memiliki ID permintaan unik untuk pelacakan. Anda bisa menggunakan ID ini di aplikasi Anda untuk mengkorelasikan log dan metrik. Misalnya, Anda bisa menambahkan ID permintaan ke halaman kesalahan:

```php
Flight::map('error', function($message) {
	// Dapatkan ID permintaan dari header respons X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Selain itu, Anda bisa mengambilnya dari variabel Flight
	// Metode ini tidak akan bekerja dengan baik di swoole atau platform async lainnya.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Upgrade

Jika Anda sedang mengupgrade ke versi APM yang lebih baru, ada kemungkinan ada migrasi database yang perlu dijalankan. Anda bisa melakukan ini dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema database ke versi terbaru.

**Catatan:** Jika database APM Anda besar ukurannya, migrasi ini mungkin memakan waktu. Anda mungkin ingin menjalankan perintah ini selama jam non-puncak.

### Upgrade dari 0.4.3 -> 0.5.0

Jika Anda sedang mengupgrade dari 0.4.3 ke 0.5.0, Anda perlu menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:config-migrate
```

Ini akan memigrasikan konfigurasi Anda dari format lama menggunakan file `.runway-config.json` ke format baru yang menyimpan key/value di file `config.php`.

## Membersihkan Data Lama

Untuk menjaga database Anda rapi, Anda bisa membersihkan data lama. Ini sangat berguna jika Anda menjalankan aplikasi sibuk dan ingin menjaga ukuran database tetap terkendali.
Anda bisa melakukan ini dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data lebih tua dari 30 hari dari database. Anda bisa menyesuaikan jumlah hari dengan melewatkan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data lebih tua dari 7 hari dari database.

## Pemecahan Masalah

Tersangkut? Coba ini:

- **Tidak Ada Data Dashboard?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Path konfigurasi cocok? Verifikasi DSN `.runway-config.json` mengarah ke file nyata.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik tertunda.

- **Kesalahan Worker?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dashboard Tidak Mau Mulai?**
  - Port 8001 digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan tingkat sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.

- **Tidak Melacak Pengecualian/Kesalahan?**
  - Jika Anda memiliki [Tracy](https://tracy.nette.org/) diaktifkan untuk proyek Anda, itu akan mengganti penanganan kesalahan Flight. Anda perlu menonaktifkan Tracy dan kemudian pastikan bahwa `Flight::set('flight.handle_errors', true);` diatur.

- **Tidak Melacak Kueri Database?**
  - Pastikan Anda menggunakan `PdoWrapper` untuk koneksi database Anda.
  - Pastikan Anda membuat argumen terakhir di konstruktor `true`.