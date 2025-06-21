# Plugin Hebat

Flight sangat mudah diperluas. Ada banyak plugin yang bisa digunakan untuk menambahkan fungsionalitas ke aplikasi Flight Anda. Beberapa didukung secara resmi oleh Tim Flight dan yang lain adalah pustaka mikro/lite untuk membantu Anda memulai.

## Dokumentasi API

Dokumentasi API sangat penting untuk API apa pun. Ini membantu pengembang memahami cara berinteraksi dengan API Anda dan apa yang diharapkan sebagai hasil. Ada beberapa alat yang tersedia untuk membantu Anda menghasilkan dokumentasi API untuk Proyek Flight Anda.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Posting blog yang ditulis oleh Daniel Schreiber tentang cara menggunakan Spesifikasi OpenAPI dengan FlightPHP untuk membangun API Anda dengan pendekatan API pertama.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI adalah alat hebat untuk membantu Anda menghasilkan dokumentasi API untuk proyek Flight. Sangat mudah digunakan dan bisa disesuaikan dengan kebutuhan Anda. Ini adalah pustaka PHP untuk membantu Anda menghasilkan dokumentasi Swagger.

## Pemantauan Kinerja Aplikasi (APM)

Pemantauan Kinerja Aplikasi (APM) sangat penting untuk aplikasi apa pun. Ini membantu Anda memahami bagaimana kinerja aplikasi Anda dan di mana titik-titik lemahnya. Ada banyak alat APM yang bisa digunakan dengan Flight.
- <span class="badge bg-info">versi beta</span>[flightphp/apm](/awesome-plugins/apm) - Flight APM adalah pustaka APM sederhana yang bisa digunakan untuk memantau aplikasi Flight Anda. Ini bisa digunakan untuk memantau kinerja aplikasi Anda dan membantu mengidentifikasi titik-titik lemah.

## Otentikasi/Otorisasi

Otentikasi dan Otorisasi sangat penting untuk aplikasi apa pun yang memerlukan kontrol untuk menentukan siapa yang bisa mengakses apa.

- <span class="badge bg-primary">resmi</span> [flightphp/permissions](/awesome-plugins/permissions) - Pustaka Permissions resmi Flight. Pustaka ini adalah cara sederhana untuk menambahkan izin tingkat pengguna dan aplikasi ke aplikasi Anda.

## Penyimpanan Cache

Penyimpanan cache adalah cara hebat untuk mempercepat aplikasi Anda. Ada banyak pustaka caching yang bisa digunakan dengan Flight.

- <span class="badge bg-primary">resmi</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Ringan, sederhana dan berdiri sendiri, kelas caching dalam-file PHP

## CLI

Aplikasi CLI adalah cara hebat untuk berinteraksi dengan aplikasi Anda. Anda bisa menggunakannya untuk menghasilkan controller, menampilkan semua rute, dan banyak lagi.

- <span class="badge bg-primary">resmi</span> [flightphp/runway](/awesome-plugins/runway) - Runway adalah aplikasi CLI yang membantu Anda mengelola aplikasi Flight Anda.

## Cookie

Cookie adalah cara hebat untuk menyimpan bit data kecil di sisi klien. Mereka bisa digunakan untuk menyimpan preferensi pengguna, pengaturan aplikasi, dan banyak lagi.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie adalah pustaka PHP yang menyediakan cara sederhana dan efektif untuk mengelola cookie.

## Debugging

Debugging sangat penting saat Anda mengembangkan di lingkungan lokal. Ada beberapa plugin yang bisa meningkatkan pengalaman debugging Anda.

- [tracy/tracy](/awesome-plugins/tracy) - Ini adalah penanganan error lengkap yang bisa digunakan dengan Flight. Ini memiliki banyak panel yang bisa membantu Anda melakukan debugging aplikasi Anda. Juga sangat mudah untuk diperluas dan menambahkan panel sendiri.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Digunakan dengan penanganan error [Tracy](/awesome-plugins/tracy), plugin ini menambahkan beberapa panel ekstra untuk membantu debugging khusus untuk proyek Flight.

## Basis Data

Basis data adalah inti dari sebagian besar aplikasi. Ini adalah cara Anda menyimpan dan mengambil data. Beberapa pustaka basis data hanya wrapper untuk menulis query dan yang lain adalah ORM lengkap.

- <span class="badge bg-primary">resmi</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Wrapper PDO resmi Flight yang merupakan bagian dari core. Ini adalah wrapper sederhana untuk membantu menyederhanakan proses menulis query dan mengeksekusinya. Ini bukan ORM.
- <span class="badge bg-primary">resmi</span> [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord resmi Flight. Pustaka kecil hebat untuk dengan mudah mengambil dan menyimpan data di basis data Anda.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin untuk melacak semua perubahan basis data untuk proyek Anda.

## Enkripsi

Enkripsi sangat penting untuk aplikasi apa pun yang menyimpan data sensitif. Mengenkripsi dan mendekripsi data tidak terlalu sulit, tetapi menyimpan kunci enkripsi dengan benar [bisa](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [menjadi](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [sulit](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Hal terpenting adalah jangan pernah menyimpan kunci enkripsi Anda di direktori publik atau mengkomitnya ke repositori kode Anda.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Ini adalah pustaka yang bisa digunakan untuk mengenkripsi dan mendekripsi data. Memulai dan berjalan cukup sederhana untuk mulai mengenkripsi dan mendekripsi data.

## Antrian Pekerjaan

Antrian pekerjaan sangat membantu untuk memproses tugas secara asinkron. Ini bisa berupa mengirim email, memproses gambar, atau apa saja yang tidak perlu dilakukan secara real-time.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue adalah pustaka yang bisa digunakan untuk memproses pekerjaan secara asinkron. Ini bisa digunakan dengan beanstalkd, MySQL/MariaDB, SQLite, dan PostgreSQL.

## Sesi

Sesi tidak terlalu berguna untuk API, tetapi untuk membangun aplikasi web, sesi bisa sangat penting untuk mempertahankan status dan informasi login.

- <span class="badge bg-primary">resmi</span> [flightphp/session](/awesome-plugins/session) - Pustaka Sesi resmi Flight. Ini adalah pustaka sesi sederhana yang bisa digunakan untuk menyimpan dan mengambil data sesi. Ini menggunakan penanganan sesi bawaan PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - Manajer Sesi PHP (non-blocking, flash, segment, enkripsi sesi). Menggunakan PHP open_ssl untuk enkripsi/dekripsi sesi opsional.

## Templating

Templating adalah inti dari aplikasi web apa pun dengan UI. Ada banyak mesin templating yang bisa digunakan dengan Flight.

- <span class="badge bg-warning">tidak digunakan lagi</span> [flightphp/core View](/learn#views) - Ini adalah mesin templating sangat dasar yang merupakan bagian dari core. Tidak disarankan untuk digunakan jika proyek Anda memiliki lebih dari beberapa halaman.
- [latte/latte](/awesome-plugins/latte) - Latte adalah mesin templating lengkap yang sangat mudah digunakan dan terasa lebih dekat dengan sintaks PHP daripada Twig atau Smarty. Juga sangat mudah untuk diperluas dan menambahkan filter serta fungsi sendiri.

## Berkontribusi

Ada plugin yang ingin Anda bagikan? Kirimkan pull request untuk menambahkannya ke daftar!