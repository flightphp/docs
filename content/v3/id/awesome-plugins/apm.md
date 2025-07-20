# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk mengatur, menggunakan, dan menguasai Application Performance Monitoring (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan yang lambat atau hanya ingin bersemangat dengan grafik latensi, kami sudah menutupinya. Mari buat aplikasi Anda lebih cepat, pengguna Anda lebih bahagia, dan sesi debugging Anda lebih mudah!

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda seperti restoran sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur mengalami kemacetan, Anda hanya menebak mengapa pelanggan pergi dengan marah. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari permintaan masuk hingga query database, dan menandai apa saja yang memperlambat Anda. Halaman yang lambat kehilangan pengguna (studi mengatakan 53% pengguna akan pergi jika situs memerlukan lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah tersebut *sebelum* menyakiti. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “mengapa ini rusak?”, lebih banyak kemenangan “lihat betapa lancarnya ini berjalan!”.

## Instalasi

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda memerlukan:
- **PHP 7.4+**: Menjaga kompatibilitas dengan distro Linux LTS sambil mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Kerangka kerja ringan yang kami tingkatkan.

## Memulai

Inilah langkah-demi-langkah untuk kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke dalam file `index.php` atau `services.php` untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Ini mengambil konfigurasi Anda (lebih lanjut nanti) dan mengatur logger—SQLite secara default.
$Apm = new Apm($ApmLogger); // Apm adalah bintangnya—ia mendengarkan acara Flight (permintaan, rute, error, dll.) dan mengumpulkan metrik.
$Apm->bindEventsToFlightInstance($app); // Ini menghubungkan semuanya ke aplikasi Flight Anda.
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut tentang itu nanti) dan mengatur logger—SQLite secara default.
- `Apm` adalah yang utama—ia mendengarkan acara Flight (permintaan, rute, error, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` menghubungkan semuanya ke aplikasi Flight Anda.

**Tips Pro: Pengambilan Contoh**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani sistem. Gunakan tingkat sampel (0.0 hingga 1.0):

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
- Meluncurkan wizard yang meminta di mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
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
Metrik mentah menumpuk dengan cepat (pikirkan log yang tidak disaring). Worker memprosesnya menjadi tujuan yang terstruktur untuk dashboard. Menjaga segala sesuatu tetap rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data yang siap dashboard. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke tujuan Anda.
- Berhenti ketika selesai atau jika tidak ada metrik yang tersisa.

**Jaga Agar Tetap Berjalan**
Untuk aplikasi langsung, Anda ingin pemrosesan berkelanjutan. Berikut adalah opsi Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat mereka datang. Bagus untuk pengembangan atau pengaturan kecil.

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
  Menjaga agar tetap berjalan bahkan jika Anda logout.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Proses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Berhenti setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dashboard Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dashboard

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menjalankan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat error, dan banyak lagi.

**Sesuaikan Itu**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana saja (berguna untuk melihat jarak jauh).
- `--port 8080`: Gunakan port berbeda jika 8001 sedang digunakan.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Kunjungi URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dashboard karena mungkin ada firewall dan langkah keamanan lainnya. Berikut adalah beberapa opsi:

- **Gunakan Reverse Proxy**: Atur Nginx atau Apache untuk meneruskan permintaan ke dashboard.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk mentransfer dashboard ke mesin lokal Anda.
- **VPN**: Jika server Anda di balik VPN, sambungkan ke itu dan akses dashboard secara langsung.
- **Konfigurasikan Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasikan Apache/Nginx**: Jika Anda memiliki web server di depan aplikasi Anda, Anda bisa mengonfigurasikannya ke domain atau subdomain. Jika Anda melakukannya, atur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Ingin dashboard yang berbeda?

Anda bisa membangun dashboard Anda sendiri jika Anda mau! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dashboard Anda sendiri!

## Fitur Dashboard

Dashboard adalah markas APM Anda—ini yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan stempel waktu, URL, kode respons, dan waktu total. Klik “Details” untuk middleware, query, dan error.
- **Permintaan Terlambat**: 5 permintaan teratas yang menghabiskan waktu (misalnya, “/api/heavy” pada 2.5 detik).
- **Rute Terlambat**: 5 rute teratas berdasarkan waktu rata-rata—bagus untuk menemukan pola.
- **Tingkat Error**: Persentase permintaan yang gagal (misalnya, 2.3% 500s).
- **Persentil Latensi**: 95th (p95) dan 99th (p99) waktu respons—ketahui skenario terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Query/Middleware Panjang**: 5 panggilan database lambat dan lapisan middleware teratas.
- **Cache Hit/Miss**: Seberapa sering cache menyelamatkan hari Anda.

**Ekstra**:
- Filter berdasarkan “Last Hour,” “Last Day,” atau “Last Week.”
- Alihkan mode gelap untuk sesi malam telat.

**Contoh**:
Permintaan ke `/users` mungkin menunjukkan:
- Total Time: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit on `user_list` (5ms)

## Menambahkan Acara Kustom

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
])); // Di mana ini muncul? Di detail permintaan dashboard di bawah “Custom Events”—dapat diperluas dengan pemformatan JSON yang bagus.
```

**Kasus Penggunaan**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
])); // Sekarang Anda akan melihat jika API itu menarik aplikasi Anda!
```

## Pemantauan Database

Lacak query PDO seperti ini:

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite');
$Apm->addPdoConnection($pdo); // Apa yang Anda dapatkan: Teks query (misalnya, `SELECT * FROM users WHERE id = ?`), waktu eksekusi (misalnya, 0.015 detik), dan jumlah baris (misalnya, 42)
```

**Peringatan**:
- **Opsional**: Lewati ini jika Anda tidak perlu pelacakan DB.
- **Hanya PdoWrapper**: PDO inti belum dihubungkan—tetap ikuti!
- **Peringatan Performa**: Mencatat setiap query di situs yang berat DB bisa memperlambat hal-hal. Gunakan sampel (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Keluaran Contoh**:
- Query: `SELECT name FROM products WHERE price > 100`
- Time: 0.023 detik
- Rows: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Batas pada 500 metrik—menjaganya tetap terbatas.
- `--batch_size 200`: Proses 200 sekaligus—menyeimbangkan kecepatan dan memori.
- `--daemon`: Berjalan tanpa henti—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## ID Permintaan di Aplikasi

Setiap permintaan memiliki ID permintaan unik untuk pelacakan. Anda bisa menggunakan ID ini di aplikasi Anda untuk mengkorelasikan log dan metrik. Misalnya, Anda bisa menambahkan ID permintaan ke halaman error:

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

Jika Anda meningkatkan ke versi APM yang lebih baru, ada kemungkinan migrasi database yang perlu dijalankan. Anda bisa melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema database ke versi terbaru.

**Catatan:** Jika database APM Anda besar, migrasi ini mungkin memerlukan waktu. Anda mungkin ingin menjalankan perintah ini selama jam non-puncak.

## Menghapus Data Lama

Untuk menjaga database Anda rapi, Anda bisa menghapus data lama. Ini sangat berguna jika Anda menjalankan aplikasi sibuk dan ingin menjaga ukuran database tetap terkendali.
Anda bisa melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data yang lebih lama dari 30 hari dari database. Anda bisa menyesuaikan jumlah hari dengan meneruskan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data yang lebih lama dari 7 hari dari database.

## Pemecahan Masalah

Tertahan? Coba ini:

- **Tidak Ada Data Dashboard?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Apakah path konfigurasi cocok? Verifikasi DSN di `.runway-config.json` menunjuk ke file yang sebenarnya.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik tertunda.

- **Error Worker?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dashboard Tidak Akan Dimulai?**
  - Port 8001 sedang digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan tingkat sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.