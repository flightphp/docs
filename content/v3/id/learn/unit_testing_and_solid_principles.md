> _Artikel ini awalnya diterbitkan di [Airpair](https://web.archive.org/web/20220204014708/https://www.airpair.com/php/posts/best-practices-for-modern-php-development#5-unit-testing) pada tahun 2015. Semua kredit diberikan kepada Airpair dan Brian Fenton yang awalnya menulis artikel ini, meskipun situs web tersebut sudah tidak tersedia lagi dan artikel hanya ada dalam [Wayback Machine](https://web.archive.org/web/20220204014708/https://www.airpair.com/php/posts/best-practices-for-modern-php-development#5-unit-testing). Artikel ini telah ditambahkan ke situs untuk tujuan pembelajaran dan pendidikan bagi komunitas PHP secara keseluruhan._

1 Pengaturan dan konfigurasi
-------------------------

### 1.1 Tetap Terbaru

Mari kita sebutkan ini dari awal - jumlah instalasi PHP yang sedikit menyedihkan di alam liar yang tetap terbaru atau dipertahankan tetap terbaru. Baik itu karena pembatasan hosting bersama, pengaturan default yang tidak ada yang berpikir untuk mengubahnya, atau tidak ada waktu/anggaran untuk pengujian peningkatan, binary PHP yang sederhana cenderung ditinggalkan. Jadi satu praktik terbaik yang jelas yang perlu lebih ditekankan adalah selalu menggunakan versi PHP yang terbaru (5.6.x pada saat artikel ini). Selanjutnya, penting juga untuk menjadwalkan peningkatan reguler baik PHP itu sendiri maupun ekstensi atau pustaka vendor apa pun yang mungkin Anda gunakan. Peningkatan memberi Anda fitur bahasa baru, kecepatan yang ditingkatkan, penggunaan memori yang lebih rendah, dan pembaruan keamanan. Semakin sering Anda meningkatkan, semakin sedikit prosesnya menjadi menyakitkan.

### 1.2 Atur default yang masuk akal

PHP melakukan pekerjaan yang layak dalam menetapkan default yang baik langsung dari kotak dengan file _php.ini.development_ dan _php.ini.production_, tetapi kita bisa lebih baik. Untuk satu, mereka tidak menetapkan zona waktu/tanggal untuk kita. Itu masuk akal dari perspektif distribusi, tetapi tanpa satu, PHP akan melemparkan kesalahan E_WARNING setiap kali kita memanggil fungsi terkait tanggal/waktu. Berikut adalah beberapa pengaturan yang direkomendasikan:

*   date.timezone - pilih dari [daftar zona waktu yang didukung](http://php.net/manual/en/timezones.php)
*   session.save_path - jika kita menggunakan file untuk sesi dan bukan penangan penyimpanan lain, atur ini ke sesuatu di luar _/tmp_. Meninggalkan ini sebagai _/tmp_ bisa berisiko di lingkungan hosting bersama karena _/tmp_ biasanya memiliki izin yang luas. Bahkan dengan bit lengket yang diatur, siapa saja yang memiliki akses untuk mencantumkan isi direktori ini bisa mengetahui semua ID sesi aktif Anda.
*   session.cookie_secure - hal yang jelas, nyalakan ini jika Anda menyajikan kode PHP Anda melalui HTTPS.
*   session.cookie_httponly - atur ini untuk mencegah cookie sesi PHP diakses melalui JavaScript
*   Lebih... gunakan alat seperti [iniscan](https://github.com/psecio/iniscan) untuk menguji konfigurasi Anda terhadap kerentanan umum

### 1.3 Ekstensi

Ini juga ide bagus untuk menonaktifkan (atau setidaknya tidak mengaktifkan) ekstensi yang tidak akan Anda gunakan, seperti driver basis data. Untuk melihat apa yang diaktifkan, jalankan perintah `phpinfo()` atau pergi ke baris perintah dan jalankan ini.

```bash
$ php -i
``` 

Informasinya sama, tetapi phpinfo() memiliki pemformatan HTML yang ditambahkan. Versi CLI lebih mudah dialirkan ke grep untuk menemukan informasi spesifik meskipun. Contoh.

```bash
$ php -i | grep error_log
```

Satu peringatan dari metode ini meskipun: mungkin ada pengaturan PHP yang berbeda yang berlaku untuk versi yang menghadap web dan versi CLI.

2 Gunakan Composer
--------------

Ini mungkin mengejutkan tetapi salah satu praktik terbaik untuk menulis PHP modern adalah menulis lebih sedikit darinya. Meskipun benar bahwa salah satu cara terbaik untuk mahir dalam pemrograman adalah melakukannya, ada banyak masalah yang sudah teratasi di ruang PHP, seperti routing, pustaka validasi input dasar, konversi unit, lapisan abstraksi basis data, dll... Cukup kunjungi [Packagist](https://www.packagist.org/) dan jelajahi. Anda mungkin menemukan bahwa bagian signifikan dari masalah yang Anda coba selesaikan sudah ditulis dan diuji.

Meskipun menggoda untuk menulis semua kode sendiri (dan tidak ada yang salah dengan menulis kerangka kerja atau pustaka Anda sendiri sebagai pengalaman belajar) Anda harus melawan perasaan Itu Tidak Diciptakan Di Sini dan menghemat banyak waktu dan sakit kepala. Ikuti doktrin PIE sebagai gantinya - Bangga Dengan Penemuan Lain. Juga, jika Anda memilih untuk menulis sendiri apa pun, jangan rilis kecuali itu melakukan sesuatu yang sangat berbeda atau lebih baik daripada penawaran yang ada.

[Composer](https://www.getcomposer.org/) adalah manajer paket untuk PHP, mirip dengan pip di Python, gem di Ruby, dan npm di Node. Ini memungkinkan Anda mendefinisikan file JSON yang mencantumkan ketergantungan kode Anda, dan itu akan mencoba menyelesaikan persyaratan tersebut dengan mengunduh dan menginstal bundel kode yang diperlukan.

### 2.1 Menginstal Composer

Kami mengasumsikan ini adalah proyek lokal, jadi mari instal instance Composer hanya untuk proyek saat ini. Navigasi ke direktori proyek Anda dan jalankan ini:
```bash
$ curl -sS https://getcomposer.org/installer | php
```

Ingat bahwa mengalirkan unduhan apa pun langsung ke penerjemah skrip (sh, ruby, php, dll...) adalah risiko keamanan, jadi baca kode instal dan pastikan Anda nyaman dengannya sebelum menjalankan perintah seperti ini.

Untuk kemudahan (jika Anda lebih suka mengetik `composer install` daripada `php composer.phar install`), Anda bisa menggunakan perintah ini untuk menginstal salinan tunggal composer secara global:

```bash
$ mv composer.phar /usr/local/bin/composer
$ chmod +x composer
```

Anda mungkin perlu menjalankannya dengan `sudo` tergantung pada izin file Anda.

### 2.2 Menggunakan Composer

Composer memiliki dua kategori utama ketergantungan yang bisa dikelolanya: "require" dan "require-dev". Ketergantungan yang tercantum sebagai "require" diinstal di mana-mana, tetapi ketergantungan "require-dev" hanya diinstal saat diminta secara spesifik. Biasanya ini adalah alat untuk saat kode sedang dikembangkan aktif, seperti [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer). Baris di bawah menunjukkan contoh cara menginstal [Guzzle](http://docs.guzzlephp.org/en/latest/), sebuah pustaka HTTP populer.

```bash
$ php composer.phar require guzzle/guzzle
```

Untuk menginstal alat hanya untuk tujuan pengembangan, tambahkan flag `--dev`:

```bash
$ php composer.phar require --dev 'sebastian/phpcpd'
```

Ini menginstal [PHP Copy-Paste Detector](https://github.com/sebastianbergmann/phpcpd), alat kualitas kode lain sebagai ketergantungan hanya untuk pengembangan.

### 2.3 Install vs update

Saat kita pertama kali menjalankan `composer install` itu akan menginstal pustaka dan ketergantungan mereka yang kita butuhkan, berdasarkan file _composer.json_. Saat selesai, composer membuat file kunci, yang dapat diprediksi disebut _composer.lock_. File ini berisi daftar ketergantungan yang ditemukan composer untuk kita dan versi tepatnya, dengan hash. Kemudian setiap kali mendatang kita menjalankan `composer install`, itu akan melihat di file kunci dan menginstal versi tepat itu.

`composer update` adalah binatang yang sedikit berbeda. Ini akan mengabaikan file _composer.lock_ (jika ada) dan mencoba menemukan versi yang paling mutakhir dari setiap ketergantungan yang masih memenuhi batasan di _composer.json_. Ini kemudian menulis file _composer.lock_ baru saat selesai.

### 2.4 Autoloading

Baik composer install maupun composer update akan menghasilkan [autoloader](https://getcomposer.org/doc/04-schema.md#autoload) untuk kita yang memberi tahu PHP di mana menemukan semua file yang diperlukan untuk menggunakan pustaka yang baru saja kita instal. Untuk menggunakannya, cukup tambahkan baris ini (biasanya ke file bootstrap yang dieksekusi pada setiap permintaan):
```php
require 'vendor/autoload.php';
```

3 Ikuti prinsip desain yang baik
-------------------------------

### 3.1 SOLID

SOLID adalah mnemonik untuk mengingatkan kita akan lima prinsip kunci dalam desain perangkat lunak berorientasi objek yang baik.

#### 3.1.1 S - Prinsip Tanggung Jawab Tunggal

Ini menyatakan bahwa kelas hanya boleh memiliki satu tanggung jawab, atau dengan kata lain, mereka hanya boleh memiliki satu alasan untuk berubah. Ini sesuai dengan filosofi Unix dari banyak alat kecil, melakukan satu hal dengan baik. Kelas yang hanya melakukan satu hal jauh lebih mudah diuji dan di-debug, dan mereka kurang mungkin mengejutkan Anda. Anda tidak ingin panggilan metode ke kelas Validator memperbarui catatan db. Berikut adalah contoh pelanggaran SRP, seperti yang umum Anda lihat di aplikasi berdasarkan [pola ActiveRecord](http://en.wikipedia.org/wiki/Active_record_pattern).

```php
class Person extends Model
{
    public $name;
    public $birthDate;
    protected $preferences;
    public function getPreferences() {}
    public function save() {}
}
```

Jadi ini adalah model entitas yang cukup dasar. Salah satu dari hal-hal ini tidak termasuk di sini meskipun. Tanggung jawab tunggal model entitas haruslah perilaku terkait entitas yang direpresentasikannya, itu tidak boleh bertanggung jawab untuk mempertahankan dirinya sendiri.

```php
class Person extends Model
{
    public $name;
    public $birthDate;
    protected $preferences;
    public function getPreferences() {}
}
class DataStore
{
    public function save(Model $model) {}
}
```

Ini lebih baik. Model Person kembali hanya melakukan satu hal, dan perilaku simpan telah dipindahkan ke objek ketekunan sebagai gantinya. Perhatikan juga bahwa saya hanya memberikan petunjuk tipe pada Model, bukan Person. Kita akan kembali ke itu saat kita sampai pada bagian L dan D dari SOLID.

#### 3.1.2 O - Prinsip Terbuka Tertutup

Ada tes luar biasa untuk ini yang cukup merangkum apa prinsip ini: pikirkan fitur untuk diimplementasikan, mungkin yang terbaru yang Anda kerjakan atau sedang kerjakan. Dapatkah Anda mengimplementasikan fitur itu di basis kode yang ada HANYA dengan menambahkan kelas baru dan tidak mengubah kelas yang ada di sistem Anda? Konfigurasi dan kode wiring Anda mendapat sedikit pengabaian, tetapi di sebagian besar sistem ini mengejutkan sulit. Anda harus bergantung banyak pada dispatch polimorfik dan sebagian besar basis kode hanya tidak diatur untuk itu. Jika Anda tertarik pada itu ada pembicaraan Google yang bagus di YouTube tentang [polimorfisme dan menulis kode tanpa Ifs](https://www.youtube.com/watch?v=4F72VULWFvc) yang menggali lebih dalam. Sebagai bonus, pembicaraan diberikan oleh [Miško Hevery](http://misko.hevery.com/), yang banyak mungkin tahu sebagai pencipta [AngularJs](https://angularjs.org/).

#### 3.1.3 L - Prinsip Penggantian Liskov

Prinsip ini dinamai untuk [Barbara Liskov](http://en.wikipedia.org/wiki/Barbara_Liskov), dan dicetak di bawah:

> "Objek dalam program harus dapat diganti dengan instance subtipe mereka tanpa mengubah kebenaran program itu."

Itu semua terdengar bagus dan bagus, tetapi lebih jelas diilustrasikan dengan contoh.

```php
abstract class Shape
{
    public function getHeight();
    public function setHeight($height);
    public function getLength();
    public function setLength($length);
}
```   

Ini akan mewakili bentuk empat sisi dasar kita. Tidak ada yang mewah di sini.

```php
class Square extends Shape
{
    protected $size;
    public function getHeight() {
        return $this->size;
    }
    public function setHeight($height) {
        $this->size = $height;
    }
    public function getLength() {
        return $this->size;
    }
    public function setLength($length) {
        $this->size = $length;
    }
}
```

Berikut bentuk pertama kita, Kotak. Bentuk yang cukup langsung, kan? Anda bisa mengasumsikan bahwa ada konstruktor di mana kita mengatur dimensi, tetapi Anda melihat dari implementasi ini bahwa panjang dan tinggi selalu akan sama. Kotak hanya seperti itu.

```php
class Rectangle extends Shape
{
    protected $height;
    protected $length;
    public function getHeight() {
        return $this->height;
    }
    public function setHeight($height) {
        $this->height = $height;
    }
    public function getLength() {
        return $this->length;
    }
    public function setLength($length) {
        $this->length = $length;
    }
}
```

Jadi di sini kita memiliki bentuk yang berbeda. Masih memiliki tanda tangan metode yang sama, itu masih bentuk empat sisi, tetapi bagaimana jika kita mulai mencoba menggunakannya sebagai pengganti satu sama lain? Sekarang tiba-tiba jika kita mengubah tinggi Bentuk kita, kita tidak lagi bisa mengasumsikan bahwa panjang bentuk kita akan cocok. Kita telah melanggar kontrak yang kita miliki dengan pengguna saat kita memberi mereka bentuk Kotak kita.

Ini adalah contoh teks pelanggaran LSP dan kita memerlukan jenis prinsip ini untuk membuat penggunaan terbaik dari sistem tipe. Bahkan [duck typing](http://en.wikipedia.org/wiki/Duck_typing) tidak akan memberi tahu kita jika perilaku dasarnya berbeda, dan karena kita tidak bisa tahu itu tanpa melihatnya rusak, yang terbaik adalah memastikan itu tidak berbeda sejak awal.

#### 3.1.3 I - Prinsip Segregasi Antarmuka

Prinsip ini mengatakan untuk lebih memilih banyak antarmuka kecil dan halus dibandingkan satu yang besar. Antarmuka harus didasarkan pada perilaku daripada "ini salah satu kelas ini". Pikirkan antarmuka yang datang dengan PHP. Traversable, Countable, Serializable, hal-hal seperti itu. Mereka mengiklankan kemampuan yang dimiliki objek, bukan apa yang diwariskannya. Jadi jaga antarmuka Anda tetap kecil. Anda tidak ingin antarmuka memiliki 30 metode di atasnya, 3 adalah tujuan yang lebih baik.

#### 3.1.4 D - Prinsip Pembalikan Ketergantungan

Anda mungkin pernah mendengar tentang ini di tempat lain yang membahas [Dependency Injection](http://en.wikipedia.org/wiki/Dependency_injection), tetapi Dependency Inversion dan Dependency Injection bukan cukup hal yang sama. Dependency inversion benar-benar hanya cara mengatakan bahwa Anda harus bergantung pada abstraksi dalam sistem Anda dan bukan pada detailnya. Sekarang apa artinya itu bagi Anda sehari-hari?

> Jangan langsung menggunakan mysqli_query() di seluruh kode Anda, gunakan sesuatu seperti DataStore->query() sebagai gantinya.

Inti dari prinsip ini sebenarnya tentang abstraksi. Ini lebih tentang mengatakan "gunakan adaptor basis data" daripada bergantung pada panggilan langsung ke sesuatu seperti mysqli_query. Jika Anda langsung menggunakan mysqli_query di setengah kelas Anda maka Anda mengikat segala sesuatu langsung ke basis data Anda. Tidak ada untuk atau melawan MySQL di sini, tetapi jika Anda menggunakan mysqli_query, jenis detail tingkat rendah itu harus disembunyikan hanya di satu tempat dan kemudian fungsionalitas itu harus diekspos melalui wrapper umum.

Sekarang saya tahu ini adalah contoh yang agak klise jika Anda memikirkannya, karena jumlah kali Anda akan benar-benar mengubah mesin basis data sepenuhnya setelah produk Anda diproduksi sangat, sangat rendah. Saya memilihnya karena saya pikir orang akan akrab dengan ide dari kode mereka sendiri. Juga, bahkan jika Anda memiliki basis data yang Anda ketahui akan tetap, objek wrapper abstrak itu memungkinkan Anda untuk memperbaiki bug, mengubah perilaku, atau mengimplementasikan fitur yang Anda inginkan basis data yang dipilih Anda miliki. Ini juga membuat pengujian unit mungkin di mana panggilan tingkat rendah tidak.

4 Latihan objek
---------------------

Ini bukan penyelaman penuh ke prinsip-prinsip ini, tetapi dua pertama mudah diingat, memberikan nilai bagus, dan bisa segera diterapkan ke hampir semua basis kode.

### 4.1 Tidak lebih dari satu level indentasi per metode

Ini adalah cara membantu untuk memikirkan dekomposisi metode menjadi potongan yang lebih kecil, meninggalkan kode yang lebih jelas dan lebih mendokumentasikan diri. Semakin banyak level indentasi yang Anda miliki, semakin banyak metode yang melakukan dan semakin banyak status yang harus Anda ingat dalam pikiran Anda saat bekerja dengannya.

Segera saya tahu orang akan keberatan dengan ini, tetapi ini hanya pedoman/heuristik, bukan aturan keras dan cepat. Saya tidak mengharapkan siapa pun untuk menegakkan aturan PHP_CodeSniffer untuk ini (meskipun [orang telah](https://github.com/object-calisthenics/phpcs-calisthenics-rules)).

Mari kita jalankan melalui sampel cepat apa ini mungkin terlihat seperti:

```php
public function transformToCsv($data)
{
    $csvLines = array();
    $csvLines[] = implode(',', array_keys($data[0]));
    foreach ($data as $row) {
        if (!$row) {
            continue;
        }
        $csvLines[] = implode(',', $row);
    }
    return $csvLines;
}
```

Meskipun ini bukan kode yang buruk (teknis benar, dapat diuji, dll...) kita bisa melakukan banyak lagi untuk membuat ini jelas. Bagaimana kita mengurangi level nesting di sini?

Kita tahu kita perlu menyederhanakan isi loop foreach (atau menghapusnya sepenuhnya) jadi mari kita mulai di sana.

```php
if (!$row) {
    continue;
}
```   

Bagian ini mudah. Yang dilakukan semua ini adalah mengabaikan baris kosong. Kita bisa shortcut proses ini seluruhnya dengan menggunakan fungsi bawaan PHP sebelum kita bahkan sampai ke loop.

```php
$data = array_filter($data);
foreach ($data as $row) {
    $csvLines[] = implode(',', $row);
}
```

Sekarang kita memiliki satu level nesting. Tetapi melihat ini, yang kita lakukan hanyalah menerapkan fungsi ke setiap item dalam array. Kita bahkan tidak perlu loop foreach untuk melakukan itu.

```php
$data = array_filter($data);
$csvLines = array_map(function($row) {
    return implode(',', $row);
}, $data);
```

Sekarang kita tidak memiliki nesting sama sekali, dan kode kemungkinan akan lebih cepat karena kita melakukan semua looping dengan fungsi C asli daripada PHP. Kita harus terlibat dalam sedikit tipuan untuk meneruskan koma ke `implode` meskipun, jadi Anda bisa berargumen bahwa berhenti di langkah sebelumnya jauh lebih bisa dipahami.

### 4.2 Coba tidak gunakan `else`

Ini benar-benar menangani dua ide utama. Yang pertama adalah pernyataan return ganda dari metode. Jika Anda memiliki cukup informasi untuk membuat keputusan tentang hasil metode, lanjutkan buat keputusan itu dan kembali. Yang kedua adalah ide yang dikenal sebagai [Guard Clauses](http://c2.com/cgi/wiki?GuardClause). Ini pada dasarnya adalah pemeriksaan validasi yang dikombinasikan dengan return awal, biasanya di dekat atas metode. Biarkan saya tunjukkan apa yang saya maksud.

```php
public function addThreeInts($first, $second, $third) {
    if (is_int($first)) {
        if (is_int($second)) {
            if (is_int($third)) {
                $sum = $first + $second + $third;
            } else {
                return null;
            }
        } else {
            return null;
        }
    } else {
        return null;
    }
    return $sum;
}
```

Jadi ini cukup langsung lagi, itu menambahkan 3 int bersama dan mengembalikan hasilnya, atau `null` jika salah satu parameter bukan integer. Mengabaikan fakta bahwa kita bisa menggabungkan semua pemeriksaan itu menjadi satu baris dengan operator AND, saya pikir Anda bisa melihat bagaimana struktur if/else bersarang membuat kode lebih sulit diikuti. Sekarang lihat contoh ini sebagai gantinya.

```php
public function addThreeInts($first, $second, $third) {
    if (!is_int($first)) {
        return null;
    }
    if (!is_int($second)) {
        return null;
    }
    if (!is_int($third)) {
        return null;
    }
    return $first + $second + $third;
}
```   

Bagi saya contoh ini jauh lebih mudah diikuti. Di sini kita menggunakan klausa guard untuk memverifikasi asumsi awal kita tentang parameter yang kita lewati dan segera keluar dari metode jika mereka tidak lulus. Kita juga tidak lagi memiliki variabel perantara untuk melacak jumlah sepanjang metode. Dalam kasus ini kita telah memverifikasi bahwa kita sudah di jalur bahagia dan kita bisa langsung melakukan apa yang kita datang kemari untuk lakukan. Sekali lagi kita bisa hanya melakukan semua pemeriksaan itu dalam satu `if` tetapi prinsipnya harus jelas.

5 Pengujian unit
--------------

Pengujian unit adalah praktik menulis tes kecil yang memverifikasi perilaku dalam kode Anda. Mereka hampir selalu ditulis dalam bahasa yang sama dengan kode (dalam kasus ini PHP) dan dimaksudkan untuk cukup cepat untuk dijalankan kapan saja. Mereka sangat berharga sebagai alat untuk meningkatkan kode Anda. Selain manfaat yang jelas memastikan bahwa kode Anda melakukan apa yang Anda pikirkan, pengujian unit juga bisa memberikan umpan balik desain yang sangat berguna. Jika potongan kode sulit diuji, itu sering menunjukkan masalah desain. Mereka juga memberi Anda jaring pengaman terhadap regresi, dan itu memungkinkan Anda melakukan refaktor lebih sering dan mengembangkan kode Anda ke desain yang lebih bersih.

### 5.1 Alat

Ada beberapa alat pengujian unit di luar sana di PHP, tetapi jauh dan paling umum adalah [PHPUnit](https://phpunit.de/). Anda bisa menginstalnya dengan mengunduh file [PHAR](http://php.net/manual/en/intro.phar.php) [langsung](https://phar.phpunit.de/phpunit.phar), atau menginstalnya dengan composer. Karena kita menggunakan composer untuk segala sesuatu yang lain, kita akan menunjukkan metode itu. Juga, karena PHPUnit tidak mungkin akan dikerahkan ke produksi, kita bisa menginstalnya sebagai ketergantungan dev dengan perintah berikut:

```bash
composer require --dev phpunit/phpunit
```

### 5.2 Tes adalah spesifikasi

Peran paling penting dari tes unit dalam kode Anda adalah memberikan spesifikasi yang dapat dieksekusi dari apa yang seharusnya dilakukan kode. Bahkan jika kode tes salah, atau kode memiliki bug, pengetahuan tentang apa yang seharusnya dilakukan sistem itu tak ternilai harganya.

### 5.3 Tulis tes Anda terlebih dahulu

Jika Anda punya kesempatan untuk melihat satu set tes yang ditulis sebelum kode dan satu yang ditulis setelah kode selesai, mereka sangat berbeda. Tes "setelah" jauh lebih khawatir dengan detail implementasi kelas dan memastikan mereka memiliki cakupan baris yang baik, sedangkan tes "sebelum" lebih tentang memverifikasi perilaku eksternal yang diinginkan. Itu benar-benar yang kita pedulikan dengan tes unit anyway, adalah memastikan kelas menunjukkan perilaku yang benar. Tes yang berfokus pada implementasi sebenarnya membuat refaktor lebih sulit karena mereka rusak jika internal kelas berubah, dan Anda baru saja kehilangan manfaat penyembunyian informasi OOP.

### 5.4 Apa yang membuat tes unit yang baik

Tes unit yang baik berbagi banyak karakteristik berikut:

*   Cepat - harus berjalan dalam milidetik.
*   Tidak ada akses jaringan - harus bisa mematikan nirkabel/mencabut dan semua tes masih lulus.
*   Akses sistem file terbatas - ini menambah kecepatan dan fleksibilitas jika menerapkan kode ke lingkungan lain.
*   Tidak ada akses basis data - menghindari aktivitas setup dan teardown yang mahal.
*   Uji hanya satu hal sekaligus - tes unit harus memiliki hanya satu alasan untuk gagal.
*   Bernama dengan baik - lihat 5.2 di atas.
*   Sebagian besar objek palsu - satu-satunya "objek nyata" dalam tes unit harus menjadi objek yang kita uji dan objek nilai sederhana. Sisanya harus berupa beberapa bentuk [test double](https://phpunit.de/manual/current/en/test-doubles.html)

Ada alasan untuk melawan beberapa dari ini tetapi sebagai pedoman umum mereka akan melayani Anda dengan baik.

### 5.5 Saat pengujian menyakitkan

> Pengujian unit memaksa Anda untuk merasakan sakit desain buruk di depan - Michael Feathers

Saat Anda menulis tes unit, Anda memaksa diri Anda untuk benar-benar menggunakan kelas untuk mencapai hal-hal. Jika Anda menulis tes di akhir, atau lebih buruk lagi, hanya melemparkan kode ke atas dinding untuk QA atau siapa pun untuk menulis tes, Anda tidak mendapatkan umpan balik tentang bagaimana kelas benar-benar berperilaku. Jika kita menulis tes, dan kelas itu nyeri nyata untuk digunakan, kita akan mengetahuinya saat kita menulisnya, yang hampir waktu termurah untuk memperbaikinya.

Jika kelas sulit diuji, itu cacat desain. Cacat yang berbeda menampakkan diri dalam cara yang berbeda, meskipun. Jika Anda harus melakukan banyak mocking, kelas Anda mungkin memiliki terlalu banyak ketergantungan, atau metode Anda melakukan terlalu banyak. Semakin banyak setup yang harus Anda lakukan untuk setiap tes, semakin mungkin metode Anda melakukan terlalu banyak. Jika Anda harus menulis skenario tes yang sangat rumit untuk melatih perilaku, metode kelas mungkin melakukan terlalu banyak. Jika Anda harus menggali di dalam banyak metode pribadi dan status untuk menguji hal-hal, mungkin ada kelas lain yang mencoba keluar. Pengujian unit sangat bagus dalam mengekspos "kelas gunung es" di mana 80% dari apa yang dilakukan kelas disembunyikan di kode yang dilindungi atau pribadi. Saya dulu penggemar besar membuat sebanyak mungkin dilindungi, tetapi sekarang saya sadar saya hanya membuat kelas individu saya bertanggung jawab atas terlalu banyak, dan solusi sebenarnya adalah memecah kelas menjadi potongan yang lebih kecil.

> **Ditulis oleh Brian Fenton** - Brian Fenton telah menjadi pengembang PHP selama 8 tahun di Midwest dan Bay Area, saat ini di Thismoment. Dia fokus pada kerajinan kode dan prinsip desain. Blog di www.brianfenton.us, Twitter di @brianfenton. Saat dia tidak sibuk menjadi ayah, dia menikmati makanan, bir, gaming, dan belajar.