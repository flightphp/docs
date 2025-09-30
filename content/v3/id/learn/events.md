# Pengelola Acara

_sejak v3.15.0_

## Gambaran Umum

Acara memungkinkan Anda mendaftarkan dan memicu perilaku khusus dalam aplikasi Anda. Dengan penambahan `Flight::onEvent()` dan `Flight::triggerEvent()`, Anda sekarang dapat menghubungkan ke momen kunci dalam siklus hidup aplikasi Anda atau mendefinisikan acara Anda sendiri (seperti notifikasi dan email) untuk membuat kode Anda lebih modular dan dapat diperluas. Metode-metode ini adalah bagian dari [metode yang dapat dipetakan](/learn/extending) milik Flight, yang berarti Anda dapat menimpa perilakunya sesuai kebutuhan Anda.

## Pemahaman

Acara memungkinkan Anda memisahkan berbagai bagian aplikasi Anda sehingga mereka tidak terlalu bergantung satu sama lain. Pemisahan ini—sering disebut **decoupling**—membuat kode Anda lebih mudah untuk diperbarui, diperluas, atau di-debug. Alih-alih menulis semuanya dalam satu blok besar, Anda dapat membagi logika Anda menjadi potongan-potongan kecil yang independen yang merespons tindakan tertentu (acara).

Bayangkan Anda sedang membangun aplikasi blog:
- Ketika pengguna memposting komentar, Anda mungkin ingin:
  - Menyimpan komentar ke database.
  - Mengirim email ke pemilik blog.
  - Mencatat tindakan untuk keamanan.

Tanpa acara, Anda akan memasukkan semuanya ke dalam satu fungsi. Dengan acara, Anda dapat membaginya: satu bagian menyimpan komentar, bagian lain memicu acara seperti `'comment.posted'`, dan pendengar terpisah menangani email dan pencatatan. Ini membuat kode Anda lebih bersih dan memungkinkan Anda menambahkan atau menghapus fitur (seperti notifikasi) tanpa menyentuh logika inti.

### Kasus Penggunaan Umum

Untuk sebagian besar, acara bagus untuk hal-hal yang opsional, tetapi bukan bagian inti mutlak dari sistem Anda. Misalnya, berikut adalah hal-hal yang baik untuk dimiliki tetapi jika mereka gagal karena alasan tertentu, aplikasi Anda masih harus berfungsi:

- **Pencatatan**: Mencatat tindakan seperti login atau kesalahan tanpa mengacaukan kode utama Anda.
- **Notifikasi**: Mengirim email atau peringatan ketika sesuatu terjadi.
- **Pembaruan Cache**: Memperbarui cache atau memberi tahu sistem lain tentang perubahan.

Namun, katakanlah Anda memiliki fitur lupa kata sandi. Itu harus menjadi bagian dari fungsionalitas inti Anda dan bukan acara karena jika email itu tidak terkirim, pengguna Anda tidak dapat mereset kata sandi mereka dan menggunakan aplikasi Anda.

## Penggunaan Dasar

Sistem acara Flight dibangun di sekitar dua metode utama: `Flight::onEvent()` untuk mendaftarkan pendengar acara dan `Flight::triggerEvent()` untuk memicu acara. Berikut adalah cara Anda dapat menggunakannya:

### Mendaftarkan Pendengar Acara

Untuk mendengarkan acara, gunakan `Flight::onEvent()`. Metode ini memungkinkan Anda mendefinisikan apa yang harus terjadi ketika acara terjadi.

```php
Flight::onEvent(string $event, callable $callback): void
```

- `$event`: Nama untuk acara Anda (misalnya, `'user.login'`).
- `$callback`: Fungsi yang dijalankan ketika acara dipicu.

Anda "berlangganan" ke acara dengan memberi tahu Flight apa yang harus dilakukan ketika itu terjadi. Callback dapat menerima argumen yang diteruskan dari pemicu acara.

Sistem acara Flight bersifat sinkron, yang berarti setiap pendengar acara dieksekusi secara berurutan, satu demi satu. Ketika Anda memicu acara, semua pendengar yang terdaftar untuk acara itu akan berjalan hingga selesai sebelum kode Anda melanjutkan. Ini penting untuk dipahami karena berbeda dari sistem acara asinkron di mana pendengar mungkin berjalan secara paralel atau pada waktu yang kemudian.

#### Contoh Sederhana
```php
Flight::onEvent('user.login', function ($username) {
    echo "Selamat datang kembali, $username!";

	// Anda dapat mengirim email jika login dari lokasi baru
});
```
Di sini, ketika acara `'user.login'` dipicu, itu akan menyapa pengguna dengan nama dan juga dapat menyertakan logika untuk mengirim email jika diperlukan.

> **Catatan:** Callback dapat berupa fungsi, fungsi anonim, atau metode dari kelas.

### Memicu Acara

Untuk membuat acara terjadi, gunakan `Flight::triggerEvent()`. Ini memberi tahu Flight untuk menjalankan semua pendengar yang terdaftar untuk acara itu, sambil meneruskan data apa pun yang Anda berikan.

```php
Flight::triggerEvent(string $event, ...$args): void
```

- `$event`: Nama acara yang Anda picu (harus cocok dengan acara yang terdaftar).
- `...$args`: Argumen opsional untuk dikirim ke pendengar (bisa berupa jumlah argumen berapa pun).

#### Contoh Sederhana
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Ini memicu acara `'user.login'` dan mengirim `'alice'` ke pendengar yang kita definisikan sebelumnya, yang akan menghasilkan: `Selamat datang kembali, alice!`.

- Jika tidak ada pendengar yang terdaftar, tidak ada yang terjadi—aplikasi Anda tidak akan rusak.
- Gunakan operator spread (`...`) untuk meneruskan beberapa argumen secara fleksibel.

### Menghentikan Acara

Jika pendengar mengembalikan `false`, tidak ada pendengar tambahan untuk acara itu yang akan dieksekusi. Ini memungkinkan Anda menghentikan rantai acara berdasarkan kondisi tertentu. Ingat, urutan pendengar penting, karena yang pertama mengembalikan `false` akan menghentikan yang lainnya dari berjalan.

**Contoh**:
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // Menghentikan pendengar selanjutnya
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // ini tidak pernah dikirim
});
```

### Menimpa Metode Acara

`Flight::onEvent()` dan `Flight::triggerEvent()` tersedia untuk [diperluas](/learn/extending), yang berarti Anda dapat mendefinisikan ulang cara kerjanya. Ini bagus untuk pengguna lanjutan yang ingin menyesuaikan sistem acara, seperti menambahkan pencatatan atau mengubah cara acara didistribusikan.

#### Contoh: Menyesuaikan `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Catat setiap pendaftaran acara
    error_log("Pendengar acara baru ditambahkan untuk: $event");
    // Panggil perilaku default (asumsi sistem acara internal)
    Flight::_onEvent($event, $callback);
});
```
Sekarang, setiap kali Anda mendaftarkan acara, itu akan mencatatnya sebelum melanjutkan.

#### Mengapa Menimpa?
- Tambahkan debugging atau pemantauan.
- Batasi acara di lingkungan tertentu (misalnya, nonaktifkan saat pengujian).
- Integrasikan dengan pustaka acara yang berbeda.

### Di Mana Menempatkan Acara Anda

Jika Anda baru dengan konsep acara di proyek Anda, Anda mungkin bertanya-tanya: *di mana saya mendaftarkan semua acara ini di aplikasi saya?* Kesederhanaan Flight berarti tidak ada aturan ketat—Anda dapat menempatkannya di mana pun yang masuk akal untuk proyek Anda. Namun, menjaga agar mereka terorganisir membantu Anda mempertahankan kode Anda saat aplikasi Anda berkembang. Berikut adalah beberapa opsi praktis dan praktik terbaik, disesuaikan dengan sifat ringan Flight:

#### Opsi 1: Di File `index.php` Utama Anda
Untuk aplikasi kecil atau prototipe cepat, Anda dapat mendaftarkan acara langsung di file `index.php` Anda bersama dengan rute Anda. Ini menjaga semuanya di satu tempat, yang baik ketika kesederhanaan adalah prioritas Anda.

```php
require 'vendor/autoload.php';

// Daftarkan acara
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
});

// Definisikan rute
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **Kelebihan**: Sederhana, tidak ada file tambahan, bagus untuk proyek kecil.
- **Kekurangan**: Dapat menjadi berantakan saat aplikasi Anda berkembang dengan lebih banyak acara dan rute.

#### Opsi 2: File `events.php` Terpisah
Untuk aplikasi yang sedikit lebih besar, pertimbangkan untuk memindahkan pendaftaran acara ke file khusus seperti `app/config/events.php`. Sertakan file ini di `index.php` Anda sebelum rute Anda. Ini meniru cara rute sering diorganisir di `app/config/routes.php` dalam proyek Flight.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "Email sent to $email: Welcome, $name!";
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
- **Kelebihan**: Menjaga `index.php` fokus pada routing, mengorganisir acara secara logis, mudah ditemukan dan diedit.
- **Kekurangan**: Menambahkan sedikit struktur, yang mungkin terasa berlebihan untuk aplikasi sangat kecil.

#### Opsi 3: Dekat dengan Tempat Mereka Dipicu
Pendekatan lain adalah mendaftarkan acara dekat dengan tempat mereka dipicu, seperti di dalam controller atau definisi rute. Ini bekerja dengan baik jika acara spesifik untuk satu bagian dari aplikasi Anda.

```php
Flight::route('/signup', function () {
    // Daftarkan acara di sini
    Flight::onEvent('user.registered', function ($email) {
        echo "Welcome email sent to $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Signed up!";
});
```
- **Kelebihan**: Menjaga kode terkait bersama, bagus untuk fitur terisolasi.
- **Kekurangan**: Menyebarkan pendaftaran acara, membuat lebih sulit untuk melihat semua acara sekaligus; berisiko pendaftaran duplikat jika tidak hati-hati.

#### Praktik Terbaik untuk Flight
- **Mulai Sederhana**: Untuk aplikasi kecil, letakkan acara di `index.php`. Ini cepat dan selaras dengan minimalisme Flight.
- **Tumbuh Cerdas**: Saat aplikasi Anda berkembang (misalnya, lebih dari 5-10 acara), gunakan file `app/config/events.php`. Ini adalah langkah alami, seperti mengorganisir rute, dan menjaga kode Anda rapi tanpa menambahkan framework kompleks.
- **Hindari Over-Engineering**: Jangan buat kelas “pengelola acara” lengkap atau direktori kecuali aplikasi Anda menjadi sangat besar—Flight berkembang dengan kesederhanaan, jadi jaga agar tetap ringan.

#### Tips: Kelompokkan berdasarkan Tujuan
Di `events.php`, kelompokkan acara terkait (misalnya, semua acara terkait pengguna bersama) dengan komentar untuk kejelasan:

```php
// app/config/events.php
// Acara Pengguna
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in");
});
Flight::onEvent('user.registered', function ($email) {
    echo "Welcome to $email!";
});

// Acara Halaman
Flight::onEvent('page.updated', function ($pageId) {
    Flight::cache()->delete("page_$pageId");
});
```

Struktur ini skalabel dengan baik dan tetap ramah pemula.

### Contoh Dunia Nyata

Mari kita jelajahi beberapa skenario dunia nyata untuk menunjukkan bagaimana acara bekerja dan mengapa mereka membantu.

#### Contoh 1: Mencatat Login Pengguna
```php
// Langkah 1: Daftarkan pendengar
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username logged in at $time");
});

// Langkah 2: Picu di aplikasi Anda
Flight::route('/login', function () {
    $username = 'bob'; // Berpura-pura ini berasal dari formulir
    Flight::triggerEvent('user.login', $username);
    echo "Hi, $username!";
});
```
**Mengapa Berguna**: Kode login tidak perlu tahu tentang pencatatan—ia hanya memicu acara. Anda dapat menambahkan lebih banyak pendengar (misalnya, kirim email selamat datang) nanti tanpa mengubah rute.

#### Contoh 2: Memberi Pemberitahuan Tentang Pengguna Baru
```php
// Pendengar untuk pendaftaran baru
Flight::onEvent('user.registered', function ($email, $name) {
    // Simulasikan pengiriman email
    echo "Email sent to $email: Welcome, $name!";
});

// Picu ketika seseorang mendaftar
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Thanks for signing up!";
});
```
**Mengapa Berguna**: Logika pendaftaran fokus pada pembuatan pengguna, sementara acara menangani notifikasi. Anda dapat menambahkan lebih banyak pendengar (misalnya, catat pendaftaran) nanti.

#### Contoh 3: Membersihkan Cache
```php
// Pendengar untuk membersihkan cache
Flight::onEvent('page.updated', function ($pageId) {
	// jika menggunakan plugin flightphp/cache
    Flight::cache()->delete("page_$pageId");
    echo "Cache cleared for page $pageId.";
});

// Picu ketika halaman diedit
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Berpura-pura kita memperbarui halaman
    Flight::triggerEvent('page.updated', $pageId);
    echo "Page $pageId updated.";
});
```
**Mengapa Berguna**: Kode pengeditan tidak peduli dengan caching—ia hanya memberi sinyal pembaruan. Bagian lain dari aplikasi dapat bereaksi sesuai kebutuhan.

### Praktik Terbaik

- **Berikan Nama Acara dengan Jelas**: Gunakan nama spesifik seperti `'user.login'` atau `'page.updated'` sehingga jelas apa yang mereka lakukan.
- **Jaga Pendengar Sederhana**: Jangan letakkan tugas lambat atau kompleks di pendengar—jaga aplikasi Anda tetap cepat.
- **Uji Acara Anda**: Picu mereka secara manual untuk memastikan pendengar bekerja seperti yang diharapkan.
- **Gunakan Acara dengan Bijak**: Mereka bagus untuk decoupling, tetapi terlalu banyak dapat membuat kode Anda sulit diikuti—gunakan mereka ketika masuk akal.

Sistem acara di Flight PHP, dengan `Flight::onEvent()` dan `Flight::triggerEvent()`, memberi Anda cara sederhana namun kuat untuk membangun aplikasi fleksibel. Dengan membiarkan berbagai bagian aplikasi Anda berbicara satu sama lain melalui acara, Anda dapat menjaga kode Anda terorganisir, dapat digunakan kembali, dan mudah diperluas. Apakah Anda mencatat tindakan, mengirim notifikasi, atau mengelola pembaruan, acara membantu Anda melakukannya tanpa mengacaukan logika Anda. Plus, dengan kemampuan untuk menimpa metode ini, Anda memiliki kebebasan untuk menyesuaikan sistem sesuai kebutuhan Anda. Mulai kecil dengan satu acara, dan lihat bagaimana itu mengubah struktur aplikasi Anda!

### Acara Bawaan

Flight PHP dilengkapi dengan beberapa acara bawaan yang dapat Anda gunakan untuk menghubungkan ke siklus hidup framework. Acara ini dipicu pada titik tertentu dalam siklus permintaan/respons, memungkinkan Anda mengeksekusi logika khusus ketika tindakan tertentu terjadi.

#### Daftar Acara Bawaan
- **flight.request.received**: `function(Request $request)` Dipicu ketika permintaan diterima, diurai, dan diproses.
- **flight.error**: `function(Throwable $exception)` Dipicu ketika kesalahan terjadi selama siklus hidup permintaan.
- **flight.redirect**: `function(string $url, int $status_code)` Dipicu ketika pengalihan dimulai.
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` Dipicu ketika cache diperiksa untuk kunci tertentu dan apakah cache hit atau miss.
- **flight.middleware.before**: `function(Route $route)`Dipicu setelah middleware before dieksekusi.
- **flight.middleware.after**: `function(Route $route)` Dipicu setelah middleware after dieksekusi.
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` Dipicu setelah middleware apa pun dieksekusi
- **flight.route.matched**: `function(Route $route)` Dipicu ketika rute cocok, tetapi belum dijalankan.
- **flight.route.executed**: `function(Route $route, float $executionTime)` Dipicu setelah rute dieksekusi dan diproses. `$executionTime` adalah waktu yang dibutuhkan untuk mengeksekusi rute (memanggil controller, dll).
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` Dipicu setelah tampilan dirender. `$executionTime` adalah waktu yang dibutuhkan untuk merender template. **Catatan: Jika Anda menimpa metode `render`, Anda perlu memicu ulang acara ini.**
- **flight.response.sent**: `function(Response $response, float $executionTime)` Dipicu setelah respons dikirim ke klien. `$executionTime` adalah waktu yang dibutuhkan untuk membangun respons.

## Lihat Juga
- [Memperluas Flight](/learn/extending) - Cara memperluas dan menyesuaikan fungsionalitas inti Flight.
- [Cache](/awesome-plugins/php_file_cache) - Contoh menggunakan acara untuk membersihkan cache ketika halaman diperbarui.

## Pemecahan Masalah
- Jika Anda tidak melihat pendengar acara Anda dipanggil, pastikan Anda mendaftarkannya sebelum memicu acara. Urutan pendaftaran penting.

## Changelog
- v3.15.0 - Menambahkan acara ke Flight.