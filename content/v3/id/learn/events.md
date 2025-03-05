# Sistem Acara di Flight PHP (v3.15.0+)

Flight PHP memperkenalkan sistem acara yang ringan dan intuitif yang memungkinkan Anda mendaftar dan memicu acara kustom di aplikasi Anda. Dengan penambahan `Flight::onEvent()` dan `Flight::triggerEvent()`, Anda sekarang dapat terhubung ke momen-momen penting dalam siklus hidup aplikasi Anda atau mendefinisikan acara Anda sendiri untuk membuat kode Anda lebih modular dan dapat diperluas. Metode ini adalah bagian dari **metode yang dapat dipetakan** Flight, yang berarti Anda dapat mengganti perilaku mereka sesuai dengan kebutuhan Anda.

Panduan ini mencakup semuanya yang perlu Anda ketahui untuk memulai dengan acara, termasuk mengapa mereka berharga, bagaimana cara menggunakannya, dan contoh praktis untuk membantu pemula memahami kekuatannya.

## Mengapa Menggunakan Acara?

Acara memungkinkan Anda untuk memisahkan bagian-bagian berbeda dari aplikasi Anda sehingga mereka tidak terlalu bergantung satu sama lain. Pemisahan ini—sering disebut **decoupling**—memudahkan kode Anda untuk diperbarui, diperluas, atau di-debug. Alih-alih menulis semuanya dalam satu blok besar, Anda dapat memecah logika Anda menjadi potongan-potongan kecil yang independen yang merespons tindakan spesifik (acara).

Bayangkan Anda sedang membangun aplikasi blog:
- Ketika seorang pengguna mengirimkan komentar, Anda mungkin ingin:
  - Menyimpan komentar ke dalam basis data.
  - Mengirim email kepada pemilik blog.
  - Mencatat tindakan untuk keamanan.

Tanpa acara, Anda akan memasukkan semua ini ke dalam satu fungsi. Dengan acara, Anda dapat memecahnya: satu bagian menyimpan komentar, bagian lain memicu acara seperti `'comment.posted'`, dan pendengar terpisah menangani email dan pencatatan. Ini menjaga kode Anda lebih bersih dan memungkinkan Anda menambah atau menghapus fitur (seperti notifikasi) tanpa menyentuh logika inti.

### Penggunaan Umum
- **Pencatatan**: Mencatat tindakan seperti login atau kesalahan tanpa mengacaukan kode utama Anda.
- **Notifikasi**: Mengirim email atau peringatan ketika sesuatu terjadi.
- **Pembaruan**: Menyegarkan cache atau memberi tahu sistem lain tentang perubahan.

## Mendaftar Pendengar Acara

Untuk mendengarkan sebuah acara, gunakan `Flight::onEvent()`. Metode ini memungkinkan Anda menentukan apa yang harus terjadi ketika sebuah acara terjadi.

### Sintaks
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event`: Nama untuk acara Anda (misalnya, `'user.login'`).
- `$callback`: Fungsi yang dijalankan ketika acara dipicu.

### Cara Kerjanya
Anda "berlangganan" ke sebuah acara dengan memberi tahu Flight apa yang harus dilakukan ketika acara itu terjadi. Callback dapat menerima argumen yang diteruskan dari pemicu acara.

Sistem acara Flight bersifat sinkron, yang berarti setiap pendengar acara dieksekusi secara berurutan, satu setelah yang lain. Ketika Anda memicu sebuah acara, semua pendengar terdaftar untuk acara itu akan dijalankan hingga selesai sebelum kode Anda dilanjutkan. Ini penting untuk dipahami karena berbeda dari sistem acara asinkron di mana pendengar mungkin dijalankan secara paralel atau pada waktu yang lebih lambat.

### Contoh Sederhana
```php
Flight::onEvent('user.login', function ($username) {
    echo "Selamat datang kembali, $username!";
});
```
Di sini, ketika acara `'user.login'` dipicu, ia akan menyapa pengguna dengan nama.

### Poin Utama
- Anda dapat menambahkan beberapa pendengar ke acara yang sama—mereka akan dijalankan dalam urutan Anda mendaftarkannya.
- Callback dapat berupa fungsi, fungsi anonim, atau metode dari sebuah kelas.

## Memicu Acara

Untuk membuat sebuah acara terjadi, gunakan `Flight::triggerEvent()`. Ini memberi tahu Flight untuk menjalankan semua pendengar yang terdaftar untuk acara tersebut, meneruskan data yang Anda berikan.

### Sintaks
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`: Nama acara yang Anda picu (harus cocok dengan acara terdaftar).
- `...$args`: Argumen opsional untuk dikirim ke pendengar (dapat berupa jumlah argumen apa pun).

### Contoh Sederhana
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Ini memicu acara `'user.login'` dan mengirimkan `'alice'` kepada pendengar yang telah kita definisikan sebelumnya, yang akan menghasilkan: `Selamat datang kembali, alice!`.

### Poin Utama
- Jika tidak ada pendengar yang terdaftar, tidak ada yang terjadi—aplikasi Anda tidak akan rusak.
- Gunakan operator spread (`...`) untuk meneruskan beberapa argumen dengan fleksibel.

### Mendaftar Pendengar Acara

...

**Menghentikan Pendengar Selanjutnya**:
Jika sebuah pendengar mengembalikan `false`, pendengar tambahan untuk acara itu tidak akan dieksekusi. Ini memungkinkan Anda untuk menghentikan rantai acara berdasarkan kondisi tertentu. Ingat, urutan pendengar itu penting, karena pendengar pertama yang mengembalikan `false` akan menghentikan yang lainnya dari berjalan.

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

`Flight::onEvent()` dan `Flight::triggerEvent()` tersedia untuk [diperluas](/learn/extending), yang berarti Anda dapat mendefinisikan ulang bagaimana mereka bekerja. Ini sangat baik untuk pengguna tingkat lanjut yang ingin menyesuaikan sistem acara, seperti menambahkan pencatatan atau mengubah cara acara dikirimkan.

### Contoh: Menyesuaikan `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Mencatat setiap pendaftaran acara
    error_log("Pendengar acara baru ditambahkan untuk: $event");
    // Panggil perilaku default (mengasumsikan ada sistem acara internal)
    Flight::_onEvent($event, $callback);
});
```
Sekarang, setiap kali Anda mendaftarkan sebuah acara, ia akan mencatatnya sebelum melanjutkan.

### Mengapa Mengganti?
- Menambahkan debugging atau pemantauan.
- Membatasi acara di lingkungan tertentu (misalnya, menonaktifkan saat pengujian).
- Mengintegrasikan dengan pustaka acara yang berbeda.

## Di Mana Menempatkan Acara Anda

Sebagai pemula, Anda mungkin bertanya: *di mana saya mendaftarkan semua acara ini di aplikasi saya?* Kesederhanaan Flight berarti tidak ada aturan ketat—Anda dapat menempatkannya di mana pun terasa tepat untuk proyek Anda. Namun, menjaga mereka terorganisir membantu Anda mempertahankan kode Anda seiring pertumbuhan aplikasi Anda. Berikut adalah beberapa opsi praktis dan praktik terbaik, disesuaikan dengan sifat ringan Flight:

### Opsi 1: Di `index.php` Utama Anda
Untuk aplikasi kecil atau prototipe cepat, Anda dapat mendaftarkan acara langsung di file `index.php` Anda bersamaan dengan rute Anda. Ini menjaga semuanya di satu tempat, yang baik ketika kesederhanaan adalah prioritas Anda.

```php
require 'vendor/autoload.php';

// Mendaftarkan acara
Flight::onEvent('user.login', function ($username) {
    error_log("$username login pada " . date('Y-m-d H:i:s'));
});

// Mendefinisikan rute
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Telah login!";
});

Flight::start();
```
- **Pro**: Sederhana, tidak ada file tambahan, bagus untuk proyek kecil.
- **Kontra**: Dapat menjadi berantakan seiring pertumbuhan aplikasi Anda dengan lebih banyak acara dan rute.

### Opsi 2: File `events.php` Terpisah
Untuk aplikasi yang sedikit lebih besar, pertimbangkan untuk memindahkan pendaftaran acara ke dalam file khusus seperti `app/config/events.php`. Sertakan file ini di `index.php` Anda sebelum rute Anda. Ini meniru cara rute sering diorganisir dalam `app/config/routes.php` di proyek Flight.

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
    echo "Telah login!";
});

Flight::start();
```
- **Pro**: Menjaga `index.php` terfokus pada routing, mengorganisir acara secara logis, mudah ditemukan dan diedit.
- **Kontra**: Menambahkan sedikit struktur, yang mungkin terasa berlebihan untuk aplikasi yang sangat kecil.

### Opsi 3: Dekat Tempat Acara Dipicu
Pendekatan lain adalah mendaftarkan acara dekat tempat mereka dipicu, seperti di dalam kontroler atau definisi rute. Ini bekerja dengan baik jika sebuah acara khusus untuk satu bagian aplikasi Anda.

```php
Flight::route('/signup', function () {
    // Mendaftarkan acara di sini
    Flight::onEvent('user.registered', function ($email) {
        echo "Email selamat datang dikirim ke $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Telah mendaftar!";
});
```
- **Pro**: Menjaga kode terkait bersama, baik untuk fitur terisolasi.
- **Kontra**: Menyebar pendaftaran acara, membuatnya lebih sulit untuk melihat semua acara sekaligus; berisiko pendaftaran duplikat jika tidak hati-hati.

### Praktik Terbaik untuk Flight
- **Mulailah Sederhana**: Untuk aplikasi kecil, letakkan acara di `index.php`. Ini cepat dan selaras dengan minimalisme Flight.
- **Bertumbuh dengan Cerdas**: Saat aplikasi Anda berkembang (misalnya, lebih dari 5-10 acara), gunakan file `app/config/events.php`. Ini adalah langkah alami ke atas, seperti mengorganisir rute, dan menjaga kode Anda rapi tanpa menambahkan framework yang kompleks.
- **Hindari Over-Engineering**: Jangan membuat kelas atau direktori "manajer acara" yang besar kecuali aplikasi Anda sangat besar—Flight berkembang dalam kesederhanaan, jadi jaga tetap ringan.

### Tip: Kelompokkan Berdasarkan Tujuan
Di `events.php`, kelompokkan acara terkait (misalnya, semua acara terkait pengguna bersama) dengan komentar untuk kejelasan:

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

Mari kita lihat beberapa skenario dunia nyata untuk menunjukkan bagaimana acara bekerja dan mengapa mereka berguna.

### Contoh 1: Mencatat Login Pengguna
```php
// Langkah 1: Mendaftarkan pendengar
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username login pada $time");
});

// Langkah 2: Memicu di aplikasi Anda
Flight::route('/login', function () {
    $username = 'bob'; // Asumsikan ini berasal dari formulir
    Flight::triggerEvent('user.login', $username);
    echo "Hai, $username!";
});
```
**Mengapa Ini Berguna**: Kode login tidak perlu tahu tentang pencatatan—hanya memicu acara. Anda bisa menambahkan lebih banyak pendengar (misalnya, mengirim email selamat datang) tanpa mengubah rute.

### Contoh 2: Memberi Tahu tentang Pengguna Baru
```php
// Pendengar untuk pendaftaran baru
Flight::onEvent('user.registered', function ($email, $name) {
    // Mensimulasikan mengirim email
    echo "Email dikirim ke $email: Selamat datang, $name!";
});

// Memicu ketika seseorang mendaftar
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Terima kasih telah mendaftar!";
});
```
**Mengapa Ini Berguna**: Logika pendaftaran fokus pada pembuatan pengguna, sementara acara menangani notifikasi. Anda dapat menambahkan lebih banyak pendengar (misalnya, mencatat pendaftaran) nanti.

### Contoh 3: Menghapus Cache
```php
// Pendengar untuk menghapus cache
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // Menghapus cache sesi jika berlaku
    echo "Cache dihapus untuk halaman $pageId.";
});

// Memicu saat halaman diedit
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Asumsikan kami memperbarui halaman
    Flight::triggerEvent('page.updated', $pageId);
    echo "Halaman $pageId diperbarui.";
});
```
**Mengapa Ini Berguna**: Kode pengeditan tidak peduli tentang caching—hanya memberi sinyal pembaruan. Bagian lain dari aplikasi dapat bereaksi sesuai kebutuhan.

## Praktik Terbaik

- **Berikan Nama Acara dengan Jelas**: Gunakan nama spesifik seperti `'user.login'` atau `'page.updated'` agar jelas apa fungsinya.
- **Simpan Pendengar Sederhana**: Jangan letakkan tugas lambat atau kompleks dalam pendengar—jaga aplikasi Anda cepat.
- **Uji Acara Anda**: Picu secara manual untuk memastikan pendengar berfungsi seperti yang diharapkan.
- **Gunakan Acara dengan Bijak**: Mereka hebat untuk pemisahan, tetapi terlalu banyak bisa membuat kode Anda sulit diikuti—gunakan ketika masuk akal.

Sistem acara di Flight PHP, dengan `Flight::onEvent()` dan `Flight::triggerEvent()`, memberi Anda cara yang sederhana namun kuat untuk membangun aplikasi yang fleksibel. Dengan membiarkan bagian-bagian berbeda dari aplikasi Anda berkomunikasi melalui acara, Anda dapat menjaga kode Anda terorganisir, dapat digunakan kembali, dan mudah untuk diperluas. Apakah Anda sedang mencatat tindakan, mengirim notifikasi, atau mengelola pembaruan, acara membantu Anda melakukannya tanpa merumitkan logika Anda. Ditambah dengan kemampuan untuk menimpa metode-metode ini, Anda memiliki kebebasan untuk menyesuaikan sistem dengan kebutuhan Anda. Mulailah kecil dengan satu acara, dan saksikan bagaimana hal itu mengubah struktur aplikasi Anda!

## Acara Bawaan

Flight PHP dilengkapi dengan beberapa acara bawaan yang dapat Anda gunakan untuk terhubung ke siklus hidup framework. Acara ini dipicu pada titik tertentu dalam siklus permintaan/respons, memungkinkan Anda untuk mengeksekusi logika kustom saat tindakan tertentu terjadi.

### Daftar Acara Bawaan
- `flight.request.received`: Dipicu saat permintaan diterima, diparsing, dan diproses.
- `flight.route.middleware.before`: Dipicu setelah middleware sebelum dieksekusi.
- `flight.route.middleware.after`: Dipicu setelah middleware setelah dieksekusi.
- `flight.route.executed`: Dipicu setelah sebuah rute dieksekusi dan diproses.
- `flight.response.sent`: Dipicu setelah respons dikirim ke klien.