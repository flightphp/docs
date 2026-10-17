# Dokumentasi FlightPHP APM

Selamat datang di FlightPHP APM—pelatih performa pribadi aplikasi Anda! Panduan ini adalah peta jalan Anda untuk menyiapkan, menggunakan, dan menguasai Application Performance Monitoring (APM) dengan FlightPHP. Entah Anda sedang memburu request yang lambat atau sekadar ingin mengagumi grafik latensi, kami siap membantu. Mari buat aplikasi Anda lebih cepat, pengguna lebih senang, dan sesi debugging Anda lebih mudah!

Lihat [demo](https://flightphp-docs-apm.sky-9.com/apm/dashboard) dashboard untuk situs Flight Docs.

![FlightPHP APM](/images/apm.png)

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda adalah restoran yang sibuk. Tanpa cara untuk melacak berapa lama pesanan diproses atau di mana dapur tersendat, Anda hanya menebak-nebak mengapa pelanggan pergi dengan kesal. APM adalah sous-chef Anda—ia mengawasi setiap langkah, dari request yang masuk hingga query database, dan menandai apa pun yang memperlambat Anda. Halaman lambat kehilangan pengguna (studi mengatakan 53% bounce jika situs membutuhkan lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah tersebut *sebelum* berdampak buruk. Ini adalah ketenangan pikiran yang proaktif—lebih sedikit momen “kenapa ini rusak?” dan lebih banyak kemenangan “lihat betapa mulusnya ini berjalan!”.

## Instalasi

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda memerlukan:
- **PHP 7.4+**: Menjaga kami tetap kompatibel dengan distro Linux LTS sekaligus mendukung PHP modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Framework ringan yang sedang kami tingkatkan.

## Database yang Didukung

FlightPHP APM saat ini mendukung database berikut untuk menyimpan metrik:

- **SQLite3**: Sederhana, berbasis file, dan cocok untuk pengembangan lokal atau aplikasi kecil. Opsi default di sebagian besar penyiapan.
- **MySQL/MariaDB**: Ideal untuk proyek yang lebih besar atau lingkungan produksi yang membutuhkan penyimpanan tangguh dan skalabel.

Anda dapat memilih tipe database selama langkah konfigurasi (lihat di bawah). Pastikan lingkungan PHP Anda memiliki ekstensi yang diperlukan (mis., `pdo_sqlite` atau `pdo_mysql`).

## Memulai

Berikut langkah demi langkah menuju kehebatan APM:

### 1. Daftarkan APM

Masukkan ini ke `index.php` atau file `services.php` Anda untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory;
use flight\database\SimplePdo;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);

// Jika Anda menambahkan koneksi database
// Utamakan SimplePdo (atau PdoQueryCapture dari Tracy Extensions saat dev).
// Aktifkan pelacakan query APM melalui array opsi (argumen ke-5).
$pdo = new SimplePdo('mysql:host=localhost;dbname=example', 'user', 'pass', null, [
	'trackApmQueries' => true, // diperlukan untuk menangkap query untuk APM
]);
$Apm->addPdoConnection($pdo);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut segera) dan menyiapkan logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan event Flight (request, route, error, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengikat semuanya ke aplikasi Flight Anda.

**Tips Pro: Sampling**
Jika aplikasi Anda sibuk, mencatat *setiap* request bisa membebani. Gunakan sample rate (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% request
```

Ini menjaga performa tetap gesit sekaligus memberi Anda data yang solid.

### 2. Konfigurasikan

Jalankan ini untuk membuat `.runway-config.json` Anda:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan dari mana metrik mentah berasal (source) dan ke mana data yang diproses pergi (destination).
- Default adalah SQLite—mis., `sqlite:/tmp/apm_metrics.sqlite` untuk source, dan file lain untuk destination.
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

> Proses ini juga akan menanyakan apakah Anda ingin menjalankan migrasi untuk penyiapan ini. Jika ini pertama kali Anda menyiapkannya, jawabannya adalah ya.

**Mengapa dua lokasi?**
Metrik mentah menumpuk dengan cepat (bayangkan log tanpa filter). Worker memprosesnya menjadi destination terstruktur untuk dashboard. Ini menjaga semuanya tetap rapi!

### 3. Proses Metrik dengan Worker

Worker mengubah metrik mentah menjadi data siap dashboard. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari source Anda (mis., `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke destination Anda.
- Berhenti setelah selesai atau jika tidak ada metrik tersisa.

**Biarkan Tetap Berjalan**
Untuk aplikasi live, Anda ingin pemrosesan berkelanjutan. Berikut opsi Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat datang. Cocok untuk dev atau penyiapan kecil.

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
  # Ctrl+B, lalu D untuk melepas; `tmux attach -t apm-worker` untuk menyambung kembali
  ```
  Menjaga tetap berjalan bahkan jika Anda logout.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Memproses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Berhenti setelah 5 menit.

**Mengapa repot?**
Tanpa worker, dashboard Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dashboard

Lihat tanda-tanda vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menjalankan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log request, route lambat, tingkat error, dan lainnya.

**Sesuaikan**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana pun (berguna untuk melihat dari jarak jauh).
- `--port 8080`: Gunakan port lain jika 8001 sudah dipakai.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Buka URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik agar dashboard berjalan karena kemungkinan ada firewall dan tindakan keamanan lain. Berikut beberapa opsi:

- **Gunakan Reverse Proxy**: Siapkan Nginx atau Apache untuk meneruskan request ke dashboard.
- **Tunnel SSH**: Jika Anda dapat SSH ke server, gunakan `ssh -L 8080:localhost:8001
youruser@yourserver` untuk membuat tunnel dashboard ke mesin lokal Anda.
- **VPN**: Jika server Anda berada di belakang VPN, sambungkan ke VPN dan akses dashboard secara langsung.
- **Konfigurasikan Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda gunakan).
- **Konfigurasikan Apache/Nginx**: Jika Anda memiliki web server di depan aplikasi, Anda dapat mengonfigurasinya ke domain atau subdomain. Arahkan host tersebut ke `dashboard/index.php` (server bawaan menggunakannya sebagai router). File statis berada di sebelahnya di `dashboard/css` dan `dashboard/js`.
- **Basic auth**: set `apm.dashboard_auth.user` dan `apm.dashboard_auth.password` di konfigurasi runway. Jika salah satu nilainya kosong, dashboard tetap terbuka. Ini adalah satu password bersama, bukan akun pengguna aplikasi.

#### Ingin dashboard yang berbeda?

Anda dapat membangun dashboard sendiri jika mau! Lihat direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dashboard Anda sendiri!

## Fitur Dashboard

Dashboard dibuka dengan angka, lalu grafik, lalu log request.

- **Baris statistik**: jumlah request, tingkat error, p95, p99, latensi rata-rata, throughput per menit, dan cache hit rate.
- **Backlog worker**: berapa banyak metrik mentah yang masih tersisa di `apm_metrics_log`. Angka tinggi berarti worker tertinggal. Dashboard kosong dengan backlog 0 berarti belum ada yang dikumpulkan.
- **Grafik latensi**: latensi rata-rata dan jumlah request untuk bucket yang sama. Satu jam terakhir menggunakan bucket 5 menit, satu hari terakhir menggunakan bucket 15 menit, dan satu minggu terakhir menggunakan bucket 6 jam. Rentang kustom memilih bucket yang menjaga sekitar 40 titik. Waktu disimpan dalam UTC dan digambar dalam zona waktu yang Anda pilih.
- **Grafik status**: jumlah `2xx`, `3xx`, `4xx`, dan `5xx` bertumpuk. Klik seri untuk memfilter log ke kelas tersebut.
- **Route, middleware, query, dan request lambat**: masing-masing lima. Query dikelompokkan sehingga `WHERE id = 1` dan `WHERE id = 2` dihitung sebagai satu statement. Klik route, request lambat, atau grup error untuk memfilter log.
- **Custom event**: setiap tipe custom event yang dipicu aplikasi Anda, dengan jumlahnya, kapan terakhir terlihat, dan garis tren kecil untuk jendela yang dipilih. Diurutkan berdasarkan jumlah. Ini mengikuti rentang waktu, Sembunyikan bot, dan filter lainnya. Klik tipe untuk memfilter log ke tipe tersebut.
- **Log request**: metode, URL, status, waktu, memori, host, dan flag bot. Detail dimuat saat Anda membuka baris dan mencakup bilah waktu, view, parameter query, trace error, dan custom event.
- **Sembunyikan bot**: mengeluarkan lalu lintas bot dari agregat dan log. Crawler pencarian dan crawler LLM (GPTBot, ClaudeBot, PerplexityBot, SearxNG, dan lainnya) ditandai sebagai bot.
- **Rentang kustom**: pilih jendela dari/ke selain jam, hari, atau minggu terakhir.
- **Filter**: URL, status, request ID, route, IP, host, session, user agent, tipe custom event, data event, dan pesan error. Query string mempertahankannya saat refresh.

**Contoh**:
Request ke `/users` mungkin menampilkan:
- Total Waktu: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Waktu render view
- Cache: Hit pada `user_list` (5ms)

**Contoh**:
Request ke `/users` mungkin menampilkan:
- Total Waktu: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit pada `user_list` (5ms)

## Menambahkan Custom Event

Lacak apa saja—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana ini muncul?**
Di detail request dashboard under “Custom Events”—dapat diperluas dengan format JSON yang rapi. Panel **Custom events** juga menghitung setiap tipe untuk Anda, jadi event `docs.markdown_served` singkat adalah semua yang Anda butuhkan untuk menjawab “seberapa sering ini terjadi?”

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
Sekarang Anda akan melihat apakah API itu memperlambat aplikasi Anda!

## Pemantauan Database

Lacak query PDO seperti ini:

```php
use flight\database\SimplePdo;

$pdo = new SimplePdo('sqlite:/path/to/db.sqlite', null, null, null, [
	'trackApmQueries' => true, // diperlukan untuk menangkap query untuk APM
]);
$Apm->addPdoConnection($pdo);
```

**Yang Anda Dapatkan**:
- Teks query (mis., `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (mis., 0.015s)
- Jumlah baris (mis., 42)

**Perhatian**:
- **Opsional**: Lewati ini jika Anda tidak memerlukan pelacakan DB.
- **SimplePdo (disarankan)**: Gunakan `SimplePdo` dengan `trackApmQueries => true`. `PdoWrapper` yang sudah deprecated masih berfungsi (argumen konstruktor ke-5 `true`). PDO core mentah belum terhubung—nantikan!
- **Peringatan Performa**: Mencatat setiap query di situs yang berat DB dapat memperlambat. Gunakan sampling (`$Apm = new Apm($ApmLogger, 0.1)`) untuk meringankan beban.

**Contoh Output**:
- Query: `SELECT name FROM products WHERE price > 100`
- Waktu: 0.023s
- Baris: 15

## Opsi Worker

Sesuaikan worker sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—bagus untuk pengujian.
- `--max_messages 500`: Membatasi pada 500 metrik—menjaga tetap terbatas.
- `--batch_size 200`: Memproses 200 sekaligus—menyeimbangkan kecepatan dan memori.
- `--daemon`: Berjalan non-stop—ideal untuk pemantauan live.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik sekaligus.

## Request ID di Aplikasi

Setiap request memiliki request ID unik untuk pelacakan. Anda dapat menggunakan ID ini di aplikasi Anda untuk mengorelasikan log dan metrik. Misalnya, Anda dapat menambahkan request ID ke halaman error:

```php
Flight::map('error', function($message) {
	// Dapatkan request ID dari header respons X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Sebagai tambahan, Anda bisa mengambilnya dari variabel Flight
	// Metode ini tidak akan bekerja dengan baik di swoole atau platform async lainnya.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Peningkatan Versi

Jika Anda meningkatkan ke versi APM yang lebih baru, ada kemungkinan ada migrasi database yang perlu dijalankan. Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:migrate
```
Ini akan menjalankan migrasi apa pun yang diperlukan untuk memperbarui skema database ke versi terbaru.

**Catatan:** Jika database APM Anda besar, migrasi ini mungkin membutuhkan waktu. Jalankan selama periode sepi. Worker dapat tetap berjalan. Migrasi indeks tidak menulis ulang baris yang telah dikumpulkan.

### Peningkatan melewati 0.5.2 (dashboard dan indeks)

0.5.2 hanya memperluas deteksi bot. File indeks di bawah dikirim dalam rilis setelah 0.5.2.

Cadangkan database destination terlebih dahulu. Salin file SQLite, atau `mysqldump` skema MySQL/MariaDB.

```bash
composer update flightphp/apm
php vendor/bin/runway apm:migrate
```

Itu menerapkan:

- SQLite: `0006-dest-indexes.sql` (indeks `request_dt` dihapus saat `0005` membangun ulang `apm_requests`)
- MySQL/MariaDB: `0002-dest-indexes.sql` (`request_dt` dan filter dashboard lainnya)

`apm:migrate` mencatat nama file dan melewatinya lain kali. Menjalankannya dua kali aman. `apm:purge` membandingkan `request_dt` dengan cutoff UTC, yang cocok dengan cara baris disimpan. Purge pertama setelah peningkatan ini dapat mempertahankan jendela yang berbeda jika zona waktu server sebelumnya bukan UTC.

`X-Flight-Request-Id` cocok dengan `request_token` yang disimpan untuk request tersebut.

### Peningkatan dari 0.4.3 -> 0.5.0

Jika Anda meningkatkan dari 0.4.3 ke 0.5.0, Anda perlu menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:config-migrate
```

Ini akan memigrasikan konfigurasi Anda dari format lama menggunakan file `.runway-config.json` ke format baru yang menyimpan key/value di file `config.php`.

## Membersihkan Data Lama

Untuk menjaga database tetap rapi, Anda dapat membersihkan data lama. Ini sangat berguna jika Anda menjalankan aplikasi yang sibuk dan ingin menjaga ukuran database tetap terkelola.
Anda dapat melakukannya dengan menjalankan perintah berikut:

```bash
php vendor/bin/runway apm:purge
```
Ini akan menghapus semua data yang lebih lama dari 30 hari dari database. Anda dapat menyesuaikan jumlah hari dengan memberikan nilai berbeda ke opsi `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Ini akan menghapus semua data yang lebih lama dari 7 hari dari database.

## Pemecahan Masalah

Buntu? Coba ini:

- **Tidak Ada Data Dashboard?**
  - Apakah worker berjalan? Periksa `ps aux | grep apm:worker`.
  - Apakah path konfigurasi cocok? Verifikasi DSN `.runway-config.json` menunjuk ke file yang benar-benar ada.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik yang tertunda.

- **Error Worker?**
  - Intip file SQLite Anda (mis., `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk stack trace.

- **Dashboard Tidak Mau Mulai?**
  - Port 8001 dipakai? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall memblokir? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan sample rate: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.

- **Tidak Melacak Exception/Error?**
  - Jika Anda mengaktifkan [Tracy](https://tracy.nette.org/) untuk proyek Anda, Tracy akan menimpa penanganan error Flight. Anda perlu menonaktifkan Tracy lalu pastikan `Flight::set('flight.handle_errors', true);` diatur.

- **Tidak Melacak Query Database?**
  - Utamakan `SimplePdo` dengan `['trackApmQueries' => true]` sebagai argumen konstruktor ke-5 (array opsi).
  - Jika Anda masih menggunakan `PdoWrapper` yang deprecated, berikan `true` sebagai argumen ke-5.
  - Panggil `$Apm->addPdoConnection($pdo)` setelah membuat koneksi.