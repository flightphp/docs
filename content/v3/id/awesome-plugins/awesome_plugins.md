# Plugin Hebat

Flight sangat dapat diubah-ubah. Ada banyak plugin yang dapat digunakan untuk menambahkan fungsionalitas ke aplikasi Flight Anda. Beberapa didukung secara resmi oleh Tim Flight dan yang lain adalah pustaka micro/lite untuk membantu Anda memulai.

## Dokumentasi API

Dokumentasi API sangat penting untuk API mana pun. Ini membantu pengembang memahami cara berinteraksi dengan API Anda dan apa yang diharapkan sebagai balasan. Ada beberapa alat yang tersedia untuk membantu Anda menghasilkan dokumentasi API untuk Proyek Flight Anda.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Posting blog yang ditulis oleh Daniel Schreiber tentang cara menggunakan Spesifikasi OpenAPI dengan FlightPHP untuk membangun API Anda dengan pendekatan API pertama.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI adalah alat hebat untuk membantu Anda menghasilkan dokumentasi API untuk proyek Flight Anda. Sangat mudah digunakan dan dapat disesuaikan dengan kebutuhan Anda. Ini adalah pustaka PHP untuk membantu Anda menghasilkan dokumentasi Swagger.

## Pemantauan Kinerja Aplikasi (APM)

Pemantauan Kinerja Aplikasi (APM) sangat penting untuk aplikasi mana pun. Ini membantu Anda memahami bagaimana aplikasi Anda berkinerja dan di mana titik-titik penyumbatan terjadi. Ada banyak alat APM yang dapat digunakan dengan Flight.
- <span class="badge bg-info">versi uji coba</span>[flightphp/apm](/awesome-plugins/apm) - Flight APM adalah pustaka APM sederhana yang dapat digunakan untuk memantau aplikasi Flight Anda. Ini dapat digunakan untuk memantau kinerja aplikasi Anda dan membantu mengidentifikasi titik-titik penyumbatan.

## Otentikasi/Otorisasi

Otentikasi dan otorisasi sangat penting untuk aplikasi mana pun yang memerlukan kontrol untuk siapa yang dapat mengakses apa.

- <span class="badge bg-primary">resmi</span> [flightphp/permissions](/awesome-plugins/permissions) - Pustaka Permissions resmi Flight. Pustaka ini adalah cara sederhana untuk menambahkan izin tingkat pengguna dan aplikasi ke aplikasi Anda.

## Penyimpanan Cache

Penyimpanan cache adalah cara hebat untuk mempercepat aplikasi Anda. Ada banyak pustaka caching yang dapat digunakan dengan Flight.

- <span class="badge bg-primary">resmi</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Ringan, sederhana, dan pustaka caching dalam-file PHP mandiri.

## CLI

Aplikasi CLI adalah cara hebat untuk berinteraksi dengan aplikasi Anda. Anda dapat menggunakannya untuk menghasilkan pengendali, menampilkan semua rute, dan banyak lagi.

- <span class="badge bg-primary">resmi</span> [flightphp/runway](/awesome-plugins/runway) - Runway adalah aplikasi CLI yang membantu Anda mengelola aplikasi Flight Anda.

## Cookie

Cookie adalah cara hebat untuk menyimpan potongan data kecil di sisi klien. Mereka dapat digunakan untuk menyimpan preferensi pengguna, pengaturan aplikasi, dan banyak lagi.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie adalah pustaka PHP yang menyediakan cara sederhana dan efektif untuk mengelola cookie.

## Debugging

Debugging sangat penting saat Anda mengembangkan di lingkungan lokal Anda. Ada beberapa plugin yang dapat meningkatkan pengalaman debugging Anda.

- [tracy/tracy](/awesome-plugins/tracy) - Ini adalah penanganan kesalahan lengkap yang dapat digunakan dengan Flight. Ini memiliki banyak panel yang dapat membantu Anda debugging aplikasi Anda. Juga sangat mudah untuk diperluas dan menambahkan panel Anda sendiri.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Digunakan dengan penanganan kesalahan [Tracy](/awesome-plugins/tracy), plugin ini menambahkan beberapa panel ekstra untuk membantu debugging khusus untuk proyek Flight.

## Basis Data

Basis data adalah inti dari sebagian besar aplikasi. Ini adalah cara Anda menyimpan dan mengambil data. Beberapa pustaka basis data hanyalah pembungkus untuk menulis query dan beberapa adalah ORM lengkap.

- <span class="badge bg-primary">resmi</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Pembungkus PDO resmi Flight yang merupakan bagian dari core. Ini adalah pembungkus sederhana untuk membantu menyederhanakan proses menulis query dan mengeksekusinya. Ini bukan ORM.
- <span class="badge bg-primary">resmi</span> [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord resmi Flight. Pustaka kecil hebat untuk dengan mudah mengambil dan menyimpan data di basis data Anda.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin untuk melacak semua perubahan basis data untuk proyek Anda.

## Enkripsi

Enkripsi sangat penting untuk aplikasi mana pun yang menyimpan data sensitif. Mengenkripsi dan mendekripsi data tidak terlalu sulit, tetapi menyimpan kunci enkripsi dengan benar [bisa](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [menjadi](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [sulit](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Hal terpenting adalah jangan pernah menyimpan kunci enkripsi Anda di direktori publik atau mengkomitnya ke repositori kode Anda.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Ini adalah pustaka yang dapat digunakan untuk mengenkripsi dan mendekripsi data. Memulai dan berjalan cukup sederhana untuk mulai mengenkripsi dan mendekripsi data.

## Antrian Pekerjaan

Antrian pekerjaan sangat membantu untuk memproses tugas secara asynchronous. Ini bisa mengirim email, memproses gambar, atau apa saja yang tidak perlu dilakukan secara real-time.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue adalah pustaka yang dapat digunakan untuk memproses pekerjaan secara asynchronous. Ini dapat digunakan dengan beanstalkd, MySQL/MariaDB, SQLite, dan PostgreSQL.

## Sesi

Sesi tidak terlalu berguna untuk API tetapi untuk membangun aplikasi web, sesi dapat sangat penting untuk mempertahankan status dan informasi login.

- <span class="badge bg-primary">resmi</span> [flightphp/session](/awesome-plugins/session) - Pustaka Sesi resmi Flight. Ini adalah pustaka sesi sederhana yang dapat digunakan untuk menyimpan dan mengambil data sesi. Ini menggunakan penanganan sesi bawaan PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - Manajer Sesi PHP (non-blocking, flash, segment, enkripsi sesi). Menggunakan PHP open_ssl untuk enkripsi/dekripsi sesi opsional.

## Templating

Templating adalah inti dari aplikasi web apa pun dengan UI. Ada banyak mesin templating yang dapat digunakan dengan Flight.

- <span class="badge bg-warning">tidak digunakan lagi</span> [flightphp/core View](/learn#views) - Ini adalah mesin templating sangat dasar yang merupakan bagian dari core. Tidak disarankan digunakan jika Anda memiliki lebih dari beberapa halaman di proyek Anda.
- [latte/latte](/awesome-plugins/latte) - Latte adalah mesin templating lengkap yang sangat mudah digunakan dan terasa lebih dekat dengan sintaks PHP daripada Twig atau Smarty. Juga sangat mudah untuk diperluas dan menambahkan filter dan fungsi Anda sendiri.

## Integrasi WordPress

Ingin menggunakan Flight di proyek WordPress Anda? Ada plugin berguna untuk itu!

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - Plugin WordPress ini memungkinkan Anda menjalankan Flight tepat di samping WordPress. Sempurna untuk menambahkan API khusus, microservices, atau bahkan aplikasi lengkap ke situs WordPress Anda menggunakan kerangka Flight. Sangat berguna jika Anda ingin yang terbaik dari kedua dunia!

## Berkontribusi

Punya plugin yang ingin Anda bagikan? Kirimkan pull request untuk menambahkannya ke daftar!