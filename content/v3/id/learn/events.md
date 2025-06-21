# Sistem Acara di Flight PHP (v3.15.0+)

Flight PHP memperkenalkan sistem acara yang ringan dan intuitif yang memungkinkan Anda mendaftarkan dan memicu acara khusus dalam aplikasi Anda. Dengan penambahan `Flight::onEvent()` dan `Flight::triggerEvent()`, Anda sekarang dapat menghubungkan ke momen kunci dalam siklus hidup aplikasi Anda atau menentukan acara sendiri untuk membuat kode Anda lebih modular dan dapat diperluas. Metode-metode ini adalah bagian dari **metode mappable** Flight, yang berarti Anda dapat menimpa perilakunya sesuai kebutuhan.

Panduan ini mencakup semua yang perlu Anda ketahui untuk memulai dengan acara, termasuk mengapa mereka berharga, cara menggunakannya, dan contoh praktis untuk membantu pemula memahami kekuatannya.

## Mengapa Menggunakan Acara?

Acara memungkinkan Anda memisahkan bagian-bagian berbeda dari aplikasi Anda sehingga mereka tidak terlalu bergantung satu sama lain. Pemisahan ini—sering disebut **decoupling**—membuat kode Anda lebih mudah diperbarui, diperluas, atau diperbaiki. Alih-alih menulis semuanya dalam satu blok besar, Anda dapat membagi logika Anda menjadi potongan-potongan kecil yang independen yang merespons tindakan tertentu (acara).

Bayangkan Anda sedang membangun aplikasi blog:
- Saat pengguna memposting komentar, Anda mungkin ingin:
  - Menyimpan komentar ke database.
  - Mengirim email ke pemilik blog.
  - Mencatat tindakan untuk keamanan.

Tanpa acara, Anda akan memasukkan semuanya ke dalam satu fungsi. Dengan acara, Anda dapat membaginya: satu bagian menyimpan komentar, bagian lain memicu acara seperti `'comment.posted'`, dan pendengar terpisah menangani email dan pencatatan. Ini membuat kode Anda lebih bersih dan memungkinkan Anda menambahkan atau menghapus fitur (seperti notifikasi) tanpa menyentuh logika inti.

### Penggunaan Umum
- **Pencatatan**: Mencatat tindakan seperti login atau kesalahan tanpa mengotori kode utama Anda.
- **Notifikasi**: Mengirim email atau peringatan saat sesuatu terjadi.
- **Pembaruan**: Memperbarui cache atau memberi tahu sistem lain tentang perubahan.

## Mendaftarkan Pendengar Acara

Untuk mendengarkan acara, gunakan `Flight::onEvent()`. Metode ini memungkinkan Anda menentukan apa yang harus dilakukan saat acara terjadi.

### Sintaks
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event`: Nama untuk acara Anda (misalnya, `'user.login'`).
- `$callback`: Fungsi yang dijalankan saat acara dipicu.

### Cara Kerjanya
Anda "berlangganan" acara dengan memberi tahu Flight apa yang harus dilakukan saat itu terjadi. Callback dapat menerima argumen yang dikirim dari pemicu acara.

Sistem acara Flight bersifat sinkron, yang berarti setiap pendengar acara dieksekusi secara berurutan, satu demi satu. Saat Anda memicu acara, semua pendengar yang terdaftar untuk acara itu akan berjalan hingga selesai sebelum kode Anda melanjutkan. Ini penting untuk dipahami karena berbeda dari sistem acara asinkron di mana pendengar mungkin berjalan secara paralel atau pada waktu yang kemudian.

### Contoh Sederhana
```php
Flight::onEvent('user.login', function ($username) {
    echo "Welcome back, $username!";  // Menampilkan sambutan kepada pengguna berdasarkan nama
});
```
Di sini, saat acara `'user.login'` dipicu, itu akan menyapa pengguna berdasarkan nama.

### Poin Kunci
- Anda dapat menambahkan beberapa pendengar ke acara yang sama—mereka akan berjalan sesuai urutan pendaftaran.
- Callback dapat berupa fungsi, fungsi anonim, atau metode dari kelas.

## Memicu Acara

Untuk membuat acara terjadi, gunakan `Flight::triggerEvent()`. Ini memberi tahu Flight untuk menjalankan semua pendengar yang terdaftar untuk acara itu, sambil meneruskan data yang Anda berikan.

### Sintaks
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`: Nama acara yang Anda picu (harus sesuai dengan acara yang terdaftar).
- `...$args`: Argumen opsional untuk dikirim ke pendengar (bisa berupa jumlah argumen apa saja).

### Contoh Sederhana
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Ini memicu acara `'user.login'` dan mengirim `'alice'` ke pendengar yang kami tentukan sebelumnya, yang akan menghasilkan output: `Welcome back, alice!`.

### Poin Kunci
- Jika tidak ada pendengar yang terdaftar, tidak ada yang terjadi—aplikasi Anda tidak akan rusak.
- Gunakan operator spread (`...`) untuk meneruskan beberapa argumen dengan fleksibel.

### Mendaftarkan Pendengar Acara

...

**Menghentikan Pendengar Selanjutnya**:
Jika pendengar mengembalikan `false`, tidak ada pendengar tambahan untuk acara itu yang akan dieksekusi. Ini memungkinkan Anda menghentikan rantai acara berdasarkan kondisi tertentu. Ingat, urutan pendengar penting, karena yang pertama mengembalikan `false` akan menghentikan sisanya.

**Contoh**:
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false;  // Menghentikan pendengar selanjutnya
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username);  // ini tidak akan dikirim
});
```

## Menimpa Metode Acara

`Flight::onEvent()` dan `Flight::triggerEvent()` dapat [diperluas](/learn/extending), yang berarti Anda dapat mendefinisikan ulang cara kerjanya. Ini bagus untuk pengguna tingkat lanjut yang ingin menyesuaikan sistem acara, seperti menambahkan pencatatan atau mengubah cara acara didistribusikan.

### Contoh: Menyesuaikan `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Catat setiap pendaftaran acara
    error_log("New event listener added for: $event");
    // Panggil perilaku default (asumsikan sistem acara internal)
    Flight::_onEvent($event, $callback);
});
```
Sekarang, setiap kali Anda mendaftarkan acara, itu akan mencatatnya sebelum melanjutkan.

### Mengapa Menimpa?
- Tambahkan debugging atau pemantauan.
- Batasi acara di lingkungan tertentu (misalnya, nonaktifkan dalam pengujian).
- Integrasikan dengan pustaka acara yang berbeda.

## Di Mana Menempatkan Acara Anda

Sebagai pemula, Anda mungkin bertanya: *di mana saya harus mendaftarkan semua acara ini di aplikasi saya?* Sederhananya Flight berarti tidak ada aturan ketat—Anda dapat meletakkannya di mana saja yang masuk akal untuk proyek Anda. Namun, menjaganya tetap terorganisir membantu Anda mempertahankan kode saat aplikasi Anda berkembang. Berikut adalah beberapa opsi praktis dan praktik terbaik, disesuaikan dengan sifat ringan Flight:

### Opsi 1: Di File Utama `index.php`
Untuk aplikasi kecil atau prototipe cepat, Anda dapat mendaftarkan acara langsung di file `index.php` bersama rute Anda. Ini menjaga semuanya di satu tempat, yang bagus saat kesederhanaan adalah prioritas.

```php
require 'vendor/autoload.php';

// Mendaftarkan acara
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));  // Catat waktu login pengguna
});

// Tentukan rute
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **Kelebihan**: Sederhana, tidak ada file tambahan, bagus untuk proyek kecil.
- **Kekurangan**: Dapat menjadi berantakan saat aplikasi Anda berkembang dengan lebih banyak acara dan rute.

### Opsi 2: File Terpisah `events.php`
Untuk aplikasi yang sedikit lebih besar, pertimbangkan untuk memindahkan pendaftaran acara ke file khusus seperti `app/config/events.php`. Sertakan file ini di `index.php` sebelum rute Anda. Ini meniru cara rute sering diatur di `app/config/routes.php` dalam proyek Flight.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));  // Catat waktu login pengguna
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "Email sent to $email: Welcome, $name!";  // Simulasikan pengiriman email sambutan
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **Kelebihan**: Menjaga `index.php` fokus pada rute, mengatur acara secara logis, mudah ditemukan dan diedit.
- **Kekurangan**: Menambahkan sedikit struktur, yang mungkin terasa berlebihan untuk aplikasi sangat kecil.

### Opsi 3: Dekat Di Mana Mereka Dipicu
Pendekatan lain adalah mendaftarkan acara dekat di mana mereka dipicu, seperti di dalam pengendali atau definisi rute. Ini berfungsi dengan baik jika acara spesifik untuk satu bagian aplikasi.

```php
Flight::route('/signup', function () {
    // Mendaftarkan acara di sini
    Flight::onEvent('user.registered', function ($email) {
        echo "Welcome email sent to $email!";  // Kirim email sambutan ke pengguna
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Signed up!";
});
```
- **Kelebihan**: Menjaga kode terkait bersama, bagus untuk fitur yang terisolasi.
- **Kekurangan**: Menyebarkan pendaftaran acara, membuatnya lebih sulit untuk melihat semua acara sekaligus; berisiko pendaftaran duplikat jika tidak hati-hati.

### Praktik Terbaik untuk Flight
- **Mulai Sederhana**: Untuk aplikasi kecil, letakkan acara di `index.php`. Ini cepat dan selaras dengan minimalisme Flight.
- **Tumbuh Cerdas**: Saat aplikasi Anda berkembang (misalnya, lebih dari 5-10 acara), gunakan file `app/config/events.php`. Ini adalah langkah alami, seperti mengatur rute, dan menjaga kode Anda rapi tanpa menambahkan kerangka kerja yang kompleks.
- **Hindari Over-Engineering**: Jangan buat kelas atau direktori "event manager" penuh kecuali aplikasi Anda sangat besar—Flight berkembang dengan kesederhanaan, jadi jaga tetap ringan.

### Tip: Kelompokkan Berdasarkan Tujuan
Di `events.php`, kelompokkan acara terkait (misalnya, semua acara terkait pengguna bersama) dengan komentar untuk kejelasan:

```php
// app/config/events.php
// Acara Pengguna
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in");  // Catat login pengguna
});
Flight::onEvent('user.registered', function ($email) {
    echo "Welcome to $email!";  // Kirim sambutan ke email
});

// Acara Halaman
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);  // Hapus cache sesi untuk halaman
});
```

Struktur ini skalabel dan tetap ramah pemula.

## Contoh untuk Pemula

Mari kita jelajahi beberapa skenario dunia nyata untuk menunjukkan bagaimana acara bekerja dan mengapa mereka membantu.

### Contoh 1: Mencatat Login Pengguna
```php
// Langkah 1: Mendaftarkan pendengar
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username logged in at $time");  // Catat waktu login pengguna
});

// Langkah 2: Picu di aplikasi Anda
Flight::route('/login', function () {
    $username = 'bob';  // Berpura-pura ini berasal dari formulir
    Flight::triggerEvent('user.login', $username);
    echo "Hi, $username!";
});
```
**Mengapa Berguna**: Kode login tidak perlu tahu tentang pencatatan—hanya memicu acara. Anda dapat menambahkan pendengar lebih lanjut (misalnya, kirim email sambutan) nanti tanpa mengubah rute.

### Contoh 2: Memberi Tahu Tentang Pengguna Baru
```php
// Pendengar untuk pendaftaran baru
Flight::onEvent('user.registered', function ($email, $name) {
    // Simulasikan pengiriman email
    echo "Email sent to $email: Welcome, $name!";  // Kirim email sambutan
});

// Picu saat seseorang mendaftar
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Thanks for signing up!";
});
```
**Mengapa Berguna**: Logika pendaftaran fokus pada pembuatan pengguna, sementara acara menangani notifikasi. Anda bisa menambahkan pendengar lebih lanjut (misalnya, catat pendaftaran) nanti.

### Contoh 3: Membersihkan Cache
```php
// Pendengar untuk membersihkan cache
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);  // Hapus cache sesi jika berlaku
    echo "Cache cleared for page $pageId.";  // Konfirmasi cache telah dibersihkan
});

// Picu saat halaman diedit
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Berpura-pura kami memperbarui halaman
    Flight::triggerEvent('page.updated', $pageId);
    echo "Page $pageId updated.";
});
```
**Mengapa Berguna**: Kode pengeditan tidak peduli dengan caching—hanya memberi sinyal pembaruan. Bagian lain aplikasi dapat bereaksi sesuai kebutuhan.

## Praktik Terbaik

- **Berikan Nama Acara yang Jelas**: Gunakan nama spesifik seperti `'user.login'` atau `'page.updated'` sehingga jelas apa yang mereka lakukan.
- **Jaga Pendengar Tetap Sederhana**: Jangan letakkan tugas yang lambat atau kompleks di pendengar—jaga agar aplikasi Anda cepat.
- **Uji Acara Anda**: Picu secara manual untuk memastikan pendengar bekerja seperti yang diharapkan.
- **Gunakan Acara dengan Bijak**: Mereka bagus untuk decoupling, tetapi terlalu banyak bisa membuat kode Anda sulit diikuti—gunakan saat masuk akal.

Sistem acara di Flight PHP, dengan `Flight::onEvent()` dan `Flight::triggerEvent()`, memberi Anda cara sederhana namun kuat untuk membangun aplikasi fleksibel. Dengan membiarkan bagian-bagian berbeda aplikasi Anda berbicara melalui acara, Anda dapat menjaga kode Anda tetap terorganisir, dapat digunakan kembali, dan mudah diperluas. Baik Anda mencatat tindakan, mengirim notifikasi, atau mengelola pembaruan, acara membantu Anda melakukannya tanpa mengacaukan logika Anda. Plus, dengan kemampuan untuk menimpa metode ini, Anda memiliki kebebasan untuk menyesuaikan sistem sesuai kebutuhan Anda. Mulailah kecil dengan satu acara, dan lihat bagaimana itu mengubah struktur aplikasi Anda!

## Acara Bawaan

Flight PHP dilengkapi dengan beberapa acara bawaan yang dapat Anda gunakan untuk menghubungkan ke siklus hidup kerangka kerja. Acara ini dipicu pada titik-titik tertentu dalam siklus permintaan/respon, memungkinkan Anda menjalankan logik khusus saat tindakan tertentu terjadi.

### Daftar Acara Bawaan
- **flight.request.received**: `function(Request $request)` Dipicu saat permintaan diterima, diproses, dan diparsing.
- **flight.error**: `function(Throwable $exception)` Dipicu saat kesalahan terjadi selama siklus permintaan.
- **flight.redirect**: `function(string $url, int $status_code)` Dipicu saat pengalihan dimulai.
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` Dipicu saat cache diperiksa untuk kunci tertentu dan apakah cache hit atau miss.
- **flight.middleware.before**: `function(Route $route)` Dipicu setelah middleware sebelumnya dieksekusi.
- **flight.middleware.after**: `function(Route $route)` Dipicu setelah middleware sesudahnya dieksekusi.
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` Dipicu setelah middleware apa pun dieksekusi
- **flight.route.matched**: `function(Route $route)` Dipicu saat rute cocok, tetapi belum dijalankan.
- **flight.route.executed**: `function(Route $route, float $executionTime)` Dipicu setelah rute dieksekusi dan diproses. `$executionTime` adalah waktu yang dibutuhkan untuk mengeksekusi rute (memanggil pengendali, dll).
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` Dipicu setelah tampilan dirender. `$executionTime` adalah waktu yang dibutuhkan untuk me-render template. **Catatan: Jika Anda menimpa metode `render`, Anda perlu memicu acara ini kembali.**
- **flight.response.sent**: `function(Response $response, float $executionTime)` Dipicu setelah respons dikirim ke klien. `$executionTime` adalah waktu yang dibutuhkan untuk membangun respons.