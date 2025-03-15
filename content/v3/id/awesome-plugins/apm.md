---

# Dokumentasi APM FlightPHP

Selamat datang di APM FlightPHP—pelatih kinerja pribadi untuk aplikasi Anda! Panduan ini adalah peta jalan Anda untuk mengatur, menggunakan, dan menguasai Pemantauan Kinerja Aplikasi (APM) dengan FlightPHP. Baik Anda sedang mencari permintaan yang lambat atau hanya ingin mengagumi grafik latensi, kami siap membantu. Mari kita buat aplikasi Anda lebih cepat, pengguna Anda lebih bahagia, dan sesi debugging Anda lebih mudah!

## Mengapa APM Penting

Bayangkan ini: aplikasi Anda adalah restoran yang sibuk. Tanpa cara untuk melacak berapa lama pesanan memakan waktu atau di mana dapur terhambat, Anda hanya menebak mengapa pelanggan meninggalkan tempat dengan kesal. APM adalah asisten koki Anda—ia mengawasi setiap langkah, mulai dari permintaan yang masuk hingga kueri basis data, dan menandai segala sesuatu yang memperlambat Anda. Halaman yang lambat kehilangan pengguna (penelitian mengatakan 53% meninggalkan jika situs memerlukan waktu lebih dari 3 detik untuk dimuat!), dan APM membantu Anda menangkap masalah itu *sebelum* mereka menjadi masalah. Ini memberi ketenangan pikiran yang proaktif—lebih sedikit “mengapa ini rusak?” momen, lebih banyak “lihat betapa lancarnya ini berfungsi!” kemenangan.

## Instalasi

Mulai dengan Composer:

```bash
composer require flightphp/apm
```

Anda akan membutuhkan:
- **PHP 7.4+**: Memastikan kami kompatibel dengan distribusi Linux LTS sambil mendukung PHP Modern.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Kerangka kerja ringan yang sedang kita tingkatkan.

## Memulai

Berikut adalah langkah demi langkah untuk kehebatan APM:

### 1. Daftarkan APM

Tambahkan ini ke dalam file `index.php` atau file `services.php` Anda untuk mulai melacak:

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);
```

**Apa yang terjadi di sini?**
- `LoggerFactory::create()` mengambil konfigurasi Anda (lebih lanjut tentang itu segera) dan mengatur logger—SQLite secara default.
- `Apm` adalah bintangnya—ia mendengarkan acara Flight (permintaan, rute, kesalahan, dll.) dan mengumpulkan metrik.
- `bindEventsToFlightInstance($app)` mengaitkan semuanya ke aplikasi Flight Anda.

**Tip Pro: Sampling**
Jika aplikasi Anda sibuk, mencatat *setiap* permintaan mungkin membebani. Gunakan laju sampel (0.0 hingga 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Mencatat 10% dari permintaan
```

Ini menjaga kinerja tetap cepat sambil tetap memberikan data yang solid.

### 2. Konfigurasikan

Jalankan ini untuk membuat `.runway-config.json` Anda:

```bash
php vendor/bin/runway apm:init
```

**Apa yang dilakukan ini?**
- Meluncurkan wizard yang menanyakan dari mana metrik mentah berasal (sumber) dan ke mana data yang diproses pergi (tujuan).
- Defaultnya adalah SQLite—misalnya, `sqlite:/tmp/apm_metrics.sqlite` untuk sumber, dan yang lain untuk tujuan.
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

**Mengapa dua lokasi?**
Metrik mentah menumpuk dengan cepat (pikirkan log yang tidak difilter). Pekerja memprosesnya menjadi tujuan terstruktur untuk dasbor. Menjaga semuanya rapi!

### 3. Proses Metrik dengan Pekerja

Pekerja mengubah metrik mentah menjadi data siap dasbor. Jalankan sekali:

```bash
php vendor/bin/runway apm:worker
```

**Apa yang dilakukannya?**
- Membaca dari sumber Anda (misalnya, `apm_metrics.sqlite`).
- Memproses hingga 100 metrik (ukuran batch default) ke tujuan Anda.
- Berhenti ketika selesai atau jika tidak ada metrik yang tersisa.

**Jaga agar Tetap Berjalan**
Untuk aplikasi langsung, Anda akan ingin pemrosesan yang berkelanjutan. Berikut adalah pilihan Anda:

- **Mode Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Berjalan selamanya, memproses metrik saat mereka datang. Sangat baik untuk pengembangan atau pengaturan kecil.

- **Crontab**:
  Tambahkan ini ke crontab Anda (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Menyala setiap menit—sempurna untuk produksi.

- **Tmux/Screen**:
  Mulai sesi yang bisa dipisahkan:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, lalu D untuk melepaskan; `tmux attach -t apm-worker` untuk terhubung kembali
  ```
  Menjaga agar tetap berjalan bahkan jika Anda log keluar.

- **Penyesuaian Kustom**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Memproses 50 metrik sekaligus.
  - `--max_messages 1000`: Berhenti setelah 1000 metrik.
  - `--timeout 300`: Keluar setelah 5 menit.

**Mengapa repot?**
Tanpa pekerja, dasbor Anda kosong. Ini adalah jembatan antara log mentah dan wawasan yang dapat ditindaklanjuti.

### 4. Luncurkan Dasbor

Lihat vital aplikasi Anda:

```bash
php vendor/bin/runway apm:dashboard
```

**Apa ini?**
- Menghidupkan server PHP di `http://localhost:8001/apm/dashboard`.
- Menampilkan log permintaan, rute lambat, tingkat kesalahan, dan lainnya.

**Kustomisasi**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Dapat diakses dari IP mana pun (berguna untuk tampilan jarak jauh).
- `--port 8080`: Gunakan port yang berbeda jika 8001 sudah terpakai.
- `--php-path`: Arahkan ke PHP jika tidak ada di PATH Anda.

Kunjungi URL di browser Anda dan jelajahi!

#### Mode Produksi

Untuk produksi, Anda mungkin harus mencoba beberapa teknik untuk menjalankan dasbor karena ada kemungkinan firewall dan langkah-langkah keamanan lainnya. Berikut adalah beberapa opsi:

- **Gunakan Proxy Terbalik**: Atur Nginx atau Apache untuk meneruskan permintaan ke dasbor.
- **SSH Tunnel**: Jika Anda bisa SSH ke server, gunakan `ssh -L 8080:localhost:8001 youruser@yourserver` untuk melakukan tunneling dasbor ke mesin lokal Anda.
- **VPN**: Jika server Anda berada di belakang VPN, sambungkan dan akses dasbor langsung.
- **Konfigurasi Firewall**: Buka port 8001 untuk IP Anda atau jaringan server. (atau port apa pun yang Anda atur).
- **Konfigurasi Apache/Nginx**: Jika Anda memiliki server web di depan aplikasi Anda, Anda dapat mengkonfigurasinya ke domain atau subdomain. Jika Anda melakukan ini, Anda akan mengatur root dokumen ke `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Ingin dasbor yang berbeda?

Anda dapat membangun dasbor Anda sendiri jika Anda mau! Periksa direktori vendor/flightphp/apm/src/apm/presenter untuk ide tentang cara menyajikan data untuk dasbor Anda sendiri!

## Fitur Dasbor

Dasbor adalah markas APM Anda—berikut adalah yang akan Anda lihat:

- **Log Permintaan**: Setiap permintaan dengan stempel waktu, URL, kode respons, dan total waktu. Klik "Detail" untuk middleware, kueri, dan kesalahan.
- **Permintaan Ter Lambat**: 5 permintaan teratas yang memakan waktu (misalnya, “/api/heavy” dalam 2.5 detik).
- **Rute Ter Lambat**: 5 rute teratas berdasarkan waktu rata-rata—baik untuk mengetahui pola.
- **Tingkat Kesalahan**: Persentase permintaan yang gagal (misalnya, 2.3% 500).
- **Persentil Latensi**: Waktu respons p95 (95th) dan p99 (99th)—ketahui skenario terburuk Anda.
- **Grafik Kode Respons**: Visualisasikan 200s, 404s, 500s seiring waktu.
- **Kueri/Pemrograman Lambat**: 5 panggilan basis data dan lapisan middleware yang lambat.
- **Cache Hit/Miss**: Seberapa sering cache Anda menolong.

**Ekstra**:
- Filter berdasarkan “Jam Terakhir,” “Hari Terakhir,” atau “Minggu Terakhir.”
- Alihkan mode gelap untuk sesi larut malam.

**Contoh**:
Sebuah permintaan ke `/users` mungkin menunjukkan:
- Total Waktu: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Kueri: `SELECT * FROM users` (80ms)
- Cache: Hit pada `user_list` (5ms)

## Menambahkan Acara Kustom

Lacak apa pun—seperti panggilan API atau proses pembayaran:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->emit('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Di mana ini muncul?**
Di detail permintaan dasbor di bawah “Kegiatan Kustom”—dapat diperluas dengan format JSON yang rapi.

**Kasus Penggunaan**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->emit('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Sekarang Anda akan melihat apakah API tersebut memperlambat aplikasi Anda!

## Pemantauan Basis Data

Lacak kueri PDO seperti ini:

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite');
$Apm->addPdoConnection($pdo);
```

**Apa yang Anda Dapatkan**:
- Teks kueri (misalnya, `SELECT * FROM users WHERE id = ?`)
- Waktu eksekusi (misalnya, 0.015s)
- Jumlah baris (misalnya, 42)

**Perhatian**:
- **Opsional**: Lewati ini jika Anda tidak memerlukan pelacakan DB.
- **Hanya PdoWrapper**: Core PDO belum terhubung—nantikan!
- **Peringatan Kinerja**: Mencatat setiap kueri di situs yang berat DB dapat memperlambat segalanya. Gunakan sampling (`$Apm = new Apm($ApmLogger, 0.1)`) untuk mengurangi beban.

**Contoh Output**:
- Kueri: `SELECT name FROM products WHERE price > 100`
- Waktu: 0.023s
- Baris: 15

## Opsi Pekerja

Sesuaikan pekerja sesuai keinginan Anda:

- `--timeout 300`: Berhenti setelah 5 menit—baik untuk pengujian.
- `--max_messages 500`: Maksimal 500 metrik—menjaga agar tetap terbatas.
- `--batch_size 200`: Memproses 200 sekaligus—seimbang antara kecepatan dan memori.
- `--daemon`: Berjalan terus-menerus—ideal untuk pemantauan langsung.

**Contoh**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Berjalan selama satu jam, memproses 100 metrik pada satu waktu.

## Pemecahan Masalah

Terjebak? Coba ini:

- **Tidak ada Data Dasbor?**
  - Apakah pekerja sedang berjalan? Periksa `ps aux | grep apm:worker`.
  - Apa jalur konfigurasi cocok? Verifikasi DSN `.runway-config.json` mengarah ke file yang nyata.
  - Jalankan `php vendor/bin/runway apm:worker` secara manual untuk memproses metrik yang tertunda.

- **Kesalahan Pekerja?**
  - Lihat file SQLite Anda (misalnya, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Periksa log PHP untuk jejak tumpukan.

- **Dasbor Tidak Mau Menyala?**
  - Port 8001 digunakan? Gunakan `--port 8080`.
  - PHP tidak ditemukan? Gunakan `--php-path /usr/bin/php`.
  - Firewall menghalangi? Buka port atau gunakan `--host localhost`.

- **Terlalu Lambat?**
  - Turunkan laju sampel: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Kurangi ukuran batch: `--batch_size 20`.