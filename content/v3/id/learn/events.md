# Sistem Acara di Flight PHP (v3.15.0+)

Flight PHP memperkenalkan sistem acara yang ringan dan intuitif yang memungkinkan Anda mendaftarkan dan memicu acara kustom dalam aplikasi Anda. Dengan penambahan `Flight::onEvent()` dan `Flight::triggerEvent()`, Anda sekarang dapat terhubung dengan momen kunci dari siklus hidup aplikasi Anda atau mendefinisikan acara Anda sendiri untuk membuat kode Anda lebih modular dan dapat diperluas. Metode-metode ini adalah bagian dari **metode yang dapat dipetakan** di Flight, yang berarti Anda dapat mengganti perilakunya untuk memenuhi kebutuhan Anda.

Panduan ini mencakup semua yang Anda perlu ketahui untuk memulai dengan acara, termasuk mengapa acara itu berharga, cara menggunakannya, dan contoh praktis untuk membantu pemula memahami kekuatannya.

## Mengapa Menggunakan Acara?

Acara memungkinkan Anda untuk memisahkan berbagai bagian dari aplikasi Anda sehingga mereka tidak tergantung satu sama lain secara berlebihan. Pemisahan ini—sering disebut **decoupling**—mempermudah kode Anda untuk diperbarui, diperluas, atau debuggé. Alih-alih menulis semuanya dalam satu bagian besar, Anda dapat membagi logika Anda menjadi bagian yang lebih kecil dan independen yang merespons tindakan tertentu (acara).

Bayangkan Anda sedang membangun aplikasi blog:
- Ketika seorang pengguna mengirim komentar, Anda mungkin ingin:
  - Menyimpan komentar ke database.
  - Mengirim email ke pemilik blog.
  - Mencatat aksi untuk keamanan.

Tanpa acara, Anda akan menggabungkan semua ini menjadi satu fungsi. Dengan acara, Anda dapat membaginya: satu bagian menyimpan komentar, bagian lain memicu acara seperti `'comment.posted'`, dan pendengar terpisah menangani email dan pencatatan. Ini menjaga kode Anda lebih bersih dan memungkinkan Anda menambah atau menghapus fitur (seperti notifikasi) tanpa mengganggu logika inti.

### Penggunaan Umum
- **Pencatatan**: Mencatat aksi seperti login atau kesalahan tanpa mengotori kode utama Anda.
- **Notifikasi**: Mengirim email atau peringatan ketika sesuatu terjadi.
- **Pembaruan**: Menyegarkan cache atau memberi tahu sistem lain tentang perubahan.

## Mendaftarkan Pendengar Acara

Untuk mendengarkan sebuah acara, gunakan `Flight::onEvent()`. Metode ini memungkinkan Anda mendefinisikan apa yang harus dilakukan ketika sebuah acara terjadi.

### Sintaks
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event`: Sebuah nama untuk acara Anda (misalnya, `'user.login'`).
- `$callback`: Fungsi yang dijalankan ketika acara dipicu.

### Cara Kerjanya
Anda "berlangganan" ke sebuah acara dengan memberi tahu Flight apa yang harus dilakukan ketika acara itu terjadi. Callback dapat menerima argumen yang dikirim dari pemicu acara.

Sistem acara di Flight bersifat sinkron, yang berarti setiap pendengar acara dieksekusi secara berurutan, satu setelah yang lain. Ketika Anda memicu sebuah acara, semua pendengar yang terdaftar untuk acara tersebut akan dijalankan hingga selesai sebelum kode Anda melanjutkan. Ini penting untuk dipahami karena berbeda dengan sistem acara asinkron di mana pendengar mungkin dijalankan secara paralel atau pada waktu yang lebih lambat.

### Contoh Sederhana
```php
Flight::onEvent('user.login', function ($username) {
    echo "Selamat datang kembali, $username!";
});
```
Di sini, ketika acara `'user.login'` dipicu, itu akan menyapa pengguna dengan namanya.

### Poin Penting
- Anda dapat menambahkan beberapa pendengar untuk acara yang sama—mereka akan dijalankan dalam urutan Anda mendaftarkannya.
- Callback bisa berupa fungsi, fungsi anonim, atau metode dari sebuah kelas.

## Memicu Acara

Untuk membuat sebuah acara terjadi, gunakan `Flight::triggerEvent()`. Ini memberi tahu Flight untuk menjalankan semua pendengar yang terdaftar untuk acara tersebut, meneruskan data yang Anda berikan.

### Sintaks
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`: Nama acara yang Anda picu (harus cocok dengan acara yang terdaftar).
- `...$args`: Argumen opsional untuk dikirim ke pendengar (dapat berupa jumlah argumen berapa pun).

### Contoh Sederhana
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Ini memicu acara `'user.login'` dan mengirimkan `'alice'` ke pendengar yang telah kita definisikan sebelumnya, yang akan menghasilkan output: `Selamat datang kembali, alice!`.

### Poin Penting
- Jika tidak ada pendengar yang terdaftar, tidak ada yang terjadi—aplikasi Anda tidak akan rusak.
- Gunakan operator spread (`...`) untuk meneruskan beberapa argumen dengan fleksibel.

### Mendaftarkan Pendengar Acara

...

**Menghentikan Pendengar Lebih Lanjut**:
Jika sebuah pendengar mengembalikan `false`, tidak ada pendengar tambahan untuk acara tersebut yang akan dieksekusi. Ini memungkinkan Anda untuk menghentikan rantai acara berdasarkan kondisi tertentu. Ingat, urutan pendengar itu penting, karena yang pertama mengembalikan `false` akan menghentikan sisanya dari menjalankan.

**Contoh**:
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // Menghentikan pendengar berikutnya
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // ini tidak pernah dikirim
});
```

## Mengganti Metode Acara

`Flight::onEvent()` dan `Flight::triggerEvent()` tersedia untuk [diperluas](/learn/extending), yang berarti Anda dapat mendefinisikan ulang bagaimana cara kerjanya. Ini sangat bagus untuk pengguna tingkat lanjut yang ingin menyesuaikan sistem acara, seperti menambahkan pencatatan atau mengubah cara acara dikirim.

### Contoh: Menyesuaikan `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Mencatat setiap pendaftaran acara
    error_log("Pendengar acara baru ditambahkan untuk: $event");
    // Panggil perilaku default (dengan asumsi ada sistem acara internal)
    Flight::_onEvent($event, $callback);
});
```
Sekarang, setiap kali Anda mendaftarkan sebuah acara, itu mencatatnya sebelum melanjutkan.

### Mengapa Mengganti?
- Menambahkan debugging atau pemantauan.
- Membatasi acara dalam lingkungan tertentu (misalnya, menonaktifkan dalam pengujian).
- Mengintegrasikan dengan perpustakaan acara yang berbeda.

## Di Mana Menempatkan Acara Anda

Sebagai pemula, Anda mungkin bertanya: *di mana saya mendaftarkan semua acara ini dalam aplikasi saya?* Kesederhanaan Flight berarti tidak ada aturan ketat—Anda dapat menempatkannya di mana pun yang masuk akal untuk proyek Anda. Namun, menjaga mereka terorganisir membantu Anda memelihara kode Anda seiring pertumbuhan aplikasi Anda. Berikut beberapa opsi praktis dan praktik terbaik, disesuaikan dengan sifat ringan Flight:

### Opsi 1: Di `index.php` Utama Anda
Untuk aplikasi kecil atau prototipe cepat, Anda dapat mendaftarkan acara langsung di file `index.php` Anda bersama dengan jalur Anda. Ini menjaga segala sesuatunya dalam satu tempat, yang baik ketika kesederhanaan adalah prioritas Anda.

```php
require 'vendor/autoload.php';

// Mendaftarkan acara
Flight::onEvent('user.login', function ($username) {
    error_log("$username login pada " . date('Y-m-d H:i:s'));
});

// Menentukan jalur
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Terlogin!";
});

Flight::start();
```
- **Kelebihan**: Sederhana, tidak perlu file tambahan, bagus untuk proyek kecil.
- **Kekurangan**: Dapat menjadi berantakan seiring pertumbuhan aplikasi Anda dengan lebih banyak acara dan jalur.

### Opsi 2: File Terpisah `events.php`
Untuk aplikasi yang sedikit lebih besar, pertimbangkan untuk memindahkan pendaftaran acara ke dalam file khusus seperti `app/config/events.php`. Sertakan file ini di `index.php` Anda sebelum jalur Anda. Ini menyerupai bagaimana jalur sering diorganisir di `app/config/routes.php` dalam proyek Flight.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username login pada " . date('Y-m-d H:i:s'));
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "Email dikirim ke $email: Selamat datang, $name!";
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Terlogin!";
});

Flight::start();
```
- **Kelebihan**: Menjaga `index.php` fokus pada pengalihan, mengorganisir acara secara logis, mudah ditemukan dan diperbarui.
- **Kekurangan**: Menambah sedikit struktur, yang mungkin terasa berlebihan untuk aplikasi yang sangat kecil.

### Opsi 3: Dekat Tempat Mereka Dipicu
Pendekatan lain adalah mendaftarkan acara dekat tempat mereka dipicu, seperti di dalam pengontrol atau definisi jalur. Ini bekerja dengan baik jika acara spesifik untuk satu bagian dari aplikasi Anda.

```php
Flight::route('/signup', function () {
    // Mendaftarkan acara di sini
    Flight::onEvent('user.registered', function ($email) {
        echo "Email selamat datang dikirim ke $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Terdaftar!";
});
```
- **Kelebihan**: Menjaga kode yang terkait bersama, bagus untuk fitur terisolasi.
- **Kekurangan**: Mendistribusikan pendaftaran acara, membuat lebih sulit untuk melihat semua acara sekaligus; risiko pendaftaran duplikat jika tidak hati-hati.

### Praktik Terbaik untuk Flight
- **Mulailah Sederhana**: Untuk aplikasi kecil, letakkan acara di `index.php`. Ini cepat dan selaras dengan minimalisme Flight.
- **Berkembang dengan Cerdas**: Seiring pertumbuhan aplikasi Anda (misalnya, lebih dari 5-10 acara), gunakan file `app/config/events.php`. Ini merupakan langkah alami ke atas, seperti mengorganisir jalur, dan menjaga kode Anda rapi tanpa menambah kerumitan.
- **Hindari Over-Engineering**: Jangan buat kelas atau direktori “manajer acara” yang besar kecuali aplikasi Anda menjadi besar—Flight berkembang pada kesederhanaan, jadi pertahankan agar tetap ringan.

### Tip: Kelompokkan Berdasarkan Tujuan
Dalam `events.php`, kelompokkan acara terkait (misalnya, semua acara yang terkait dengan pengguna bersama-sama) dengan komentar untuk kejelasan:

```php
// app/config/events.php
// Acara Pengguna
Flight::onEvent('user.login', function ($username) {
    error_log("$username login");
});
Flight::onEvent('user.registered', function ($email) {
    echo "Selamat datang di $email!";
});

// Acara Halaman
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);
});
```

Struktur ini berkembang dengan baik dan tetap ramah pemula.

## Contoh untuk Pemula

Mari kita tinjau beberapa skenario dunia nyata untuk menunjukkan bagaimana acara bekerja dan mengapa mereka berguna.

### Contoh 1: Mencatat Login Pengguna
```php
// Langkah 1: Mendaftarkan pendengar
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username login pada $time");
});

// Langkah 2: Memicunya di aplikasi Anda
Flight::route('/login', function () {
    $username = 'bob'; // Anggap ini berasal dari formulir
    Flight::triggerEvent('user.login', $username);
    echo "Hai, $username!";
});
```
**Mengapa Ini Berguna**: Kode login tidak perlu tahu tentang pencatatan—ia hanya memicu acara. Anda dapat menambahkan lebih banyak pendengar kemudian (misalnya, mengirim email sambutan) tanpa mengubah jalur.

### Contoh 2: Memberitahukan Tentang Pengguna Baru
```php
// Pendengar untuk pendaftaran baru
Flight::onEvent('user.registered', function ($email, $name) {
    // Simulasikan pengiriman email
    echo "Email dikirim ke $email: Selamat datang, $name!";
});

// Memicunya ketika seseorang mendaftar
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Terima kasih telah mendaftar!";
});
```
**Mengapa Ini Berguna**: Logika pendaftaran fokus pada pembuatan pengguna, sementara acara menangani notifikasi. Anda dapat menambahkan lebih banyak pendengar (misalnya, mencatat pendaftaran) kemudian.

### Contoh 3: Menghapus Cache
```php
// Pendengar untuk menghapus cache
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // Hapus cache sesi jika berlaku
    echo "Cache dihapus untuk halaman $pageId.";
});

// Memicunya ketika sebuah halaman diedit
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Anggap kami telah memperbarui halaman
    Flight::triggerEvent('page.updated', $pageId);
    echo "Halaman $pageId diperbarui.";
});
```
**Mengapa Ini Berguna**: Kode pengeditan tidak peduli tentang caching—ia hanya memberi sinyal pembaruan. Bagian lain aplikasi dapat merespons sesuai kebutuhan.

## Praktik Terbaik

- **Berikan Nama Acara dengan Jelas**: Gunakan nama spesifik seperti `'user.login'` atau `'page.updated'` sehingga jelas apa yang mereka lakukan.
- **Jaga Pendengar Tetap Sederhana**: Jangan letakkan tugas yang lambat atau rumit dalam pendengar—jaga aplikasi Anda tetap cepat.
- **Uji Acara Anda**: Picu mereka secara manual untuk memastikan pendengar bekerja seperti yang diharapkan.
- **Gunakan Acara dengan Bijak**: Mereka sangat berguna untuk pemisahan, tetapi terlalu banyak dapat membuat kode Anda sulit diikuti—gunakan mereka ketika masuk akal.

Sistem acara di Flight PHP, dengan `Flight::onEvent()` dan `Flight::triggerEvent()`, memberi Anda cara sederhana dan kuat untuk membangun aplikasi yang fleksibel. Dengan membiarkan berbagai bagian dari aplikasi Anda berkomunikasi satu sama lain melalui acara, Anda dapat menjaga kode Anda terorganisir, dapat digunakan kembali, dan mudah untuk diperluas. Apakah Anda mencatat tindakan, mengirim notifikasi, atau mengelola pembaruan, acara membantu Anda melakukannya tanpa mempersulit logika Anda. Selain itu, dengan kemampuan untuk mengganti metode ini, Anda memiliki kebebasan untuk menyesuaikan sistem sesuai kebutuhan Anda. Mulailah dengan satu acara kecil, dan saksikan bagaimana hal itu mengubah struktur aplikasi Anda!

## Acara Bawaan

Flight PHP dilengkapi dengan beberapa acara bawaan yang dapat Anda gunakan untuk menyambungkan siklus hidup framework. Acara-acara ini dipicu pada poin tertentu dalam siklus permintaan/respons, memungkinkan Anda untuk mengeksekusi logika kustom ketika tindakan tertentu terjadi.

### Daftar Acara Bawaan
- **flight.request.received**: `function(Request $request)` Dipicu ketika permintaan diterima, diparsing dan diproses.
- **flight.error**: `function(Throwable $exception)` Dipicu ketika kesalahan terjadi selama siklus permintaan.
- **flight.redirect**: `function(string $url, int $status_code)` Dipicu ketika pengalihan diinisiasi.
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` Dipicu ketika cache diperiksa untuk kunci tertentu dan apakah cache tersebut hit atau miss.
- **flight.middleware.before**: `function(Route $route)` Dipicu setelah middleware sebelum dieksekusi.
- **flight.middleware.after**: `function(Route $route)` Dipicu setelah middleware setelah dieksekusi.
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` Dipicu setelah middleware dieksekusi.
- **flight.route.matched**: `function(Route $route)` Dipicu ketika sebuah rute dicocokkan, tetapi belum dieksekusi.
- **flight.route.executed**: `function(Route $route, float $executionTime)` Dipicu setelah rute dieksekusi dan diproses. `$executionTime` adalah waktu yang dibutuhkan untuk mengeksekusi rute (memanggil pengontrol, dll).
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` Dipicu setelah tampilan dirender. `$executionTime` adalah waktu yang dibutuhkan untuk merender template. **Catatan: Jika Anda mengganti metode `render`, Anda perlu memicu ulang acara ini.**
- **flight.response.sent**: `function(Response $response, float $executionTime)` Dipicu setelah respons dikirim ke klien. `$executionTime` adalah waktu yang dibutuhkan untuk membangun respons.