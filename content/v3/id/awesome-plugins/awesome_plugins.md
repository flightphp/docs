# Plugin Luar Biasa

Flight sangat dapat diperluas. Ada sejumlah plugin yang dapat digunakan untuk menambahkan fungsionalitas ke aplikasi Flight Anda. Beberapa didukung secara resmi oleh Tim Flight dan lainnya adalah pustaka mikro/lite untuk membantu Anda memulai.

## Dokumentasi API

Dokumentasi API sangat penting untuk API apa pun. Ini membantu pengembang memahami cara berinteraksi dengan API Anda dan apa yang diharapkan sebagai balasan. Ada beberapa alat yang tersedia untuk membantu Anda menghasilkan dokumentasi API untuk Proyek Flight Anda.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Pos blog yang ditulis oleh Daniel Schreiber tentang cara menggunakan Spesifikasi OpenAPI dengan FlightPHP untuk membangun API Anda menggunakan pendekatan API pertama.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI adalah alat hebat untuk membantu Anda menghasilkan dokumentasi API untuk proyek Flight Anda. Ini sangat mudah digunakan dan dapat disesuaikan agar sesuai dengan kebutuhan Anda. Ini adalah pustaka PHP untuk membantu Anda menghasilkan dokumentasi Swagger.

## Autentikasi/Otorisasi

Autentikasi dan Otorisasi sangat penting untuk setiap aplikasi yang memerlukan kontrol untuk siapa yang dapat mengakses apa.

- <span class="badge bg-primary">resmi</span> [flightphp/permissions](/awesome-plugins/permissions) - Pustaka Izin Flight resmi. Pustaka ini adalah cara sederhana untuk menambahkan izin tingkat pengguna dan aplikasi ke aplikasi Anda.

## Cache

Cache adalah cara yang bagus untuk mempercepat aplikasi Anda. Ada sejumlah pustaka caching yang dapat digunakan dengan Flight.

- <span class="badge bg-primary">resmi</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Kelas caching PHP dalam file yang ringan, sederhana, dan mandiri.

## CLI

Aplikasi CLI adalah cara yang bagus untuk berinteraksi dengan aplikasi Anda. Anda dapat menggunakannya untuk menghasilkan pengontrol, menampilkan semua rute, dan lainnya.

- <span class="badge bg-primary">resmi</span> [flightphp/runway](/awesome-plugins/runway) - Runway adalah aplikasi CLI yang membantu Anda mengelola aplikasi Flight Anda.

## Cookies

Cookies adalah cara yang bagus untuk menyimpan sedikit data di sisi klien. Ini dapat digunakan untuk menyimpan preferensi pengguna, pengaturan aplikasi, dan lainnya.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie adalah pustaka PHP yang menyediakan cara sederhana dan efektif untuk mengelola cookies.

## Debugging

Debugging sangat penting ketika Anda sedang mengembangkan di lingkungan lokal Anda. Ada beberapa plugin yang dapat meningkatkan pengalaman debugging Anda.

- [tracy/tracy](/awesome-plugins/tracy) - Ini adalah pengendali kesalahan dengan fitur lengkap yang dapat digunakan dengan Flight. Ini memiliki sejumlah panel yang dapat membantu Anda melakukan debug aplikasi Anda. Ini juga sangat mudah untuk diperluas dan menambahkan panel Anda sendiri.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Digunakan dengan [Tracy](/awesome-plugins/tracy) pengendali kesalahan, plugin ini menambahkan beberapa panel ekstra untuk membantu dengan debugging khusus untuk proyek Flight.

## Basis Data

Basis data adalah inti dari sebagian besar aplikasi. Ini adalah cara Anda menyimpan dan mengambil data. Beberapa pustaka basis data hanyalah pembungkus untuk menulis kueri dan beberapa adalah ORM lengkap.

- <span class="badge bg-primary">resmi</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Pembungkus PDO resmi Flight yang merupakan bagian dari inti. Ini adalah pembungkus sederhana untuk membantu menyederhanakan proses menulis kueri dan mengeksekusinya. Ini bukan ORM.
- <span class="badge bg-primary">resmi</span> [flightphp/active-record](/awesome-plugins/active-record) - ORM/Pemetaan ActiveRecord resmi Flight. Pustaka kecil yang hebat untuk dengan mudah mengambil dan menyimpan data di basis data Anda.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin untuk melacak semua perubahan basis data untuk proyek Anda.

## Enkripsi

Enkripsi sangat penting untuk aplikasi apa pun yang menyimpan data sensitif. Mengenskripsi dan mendekripsi data tidak terlalu sulit, tetapi menyimpan kunci enkripsi dengan benar [dapat](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [menjadi](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [sulit](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Hal yang paling penting adalah tidak pernah menyimpan kunci enkripsi Anda di direktori publik atau mengkomitnya ke repositori kode Anda.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Ini adalah pustaka yang dapat digunakan untuk mengenkripsi dan mendekripsi data. Memulai dan berjalan cukup mudah untuk mulai mengenkripsi dan mendekripsi data.

## Antrian Pekerjaan

Antrian pekerjaan sangat membantu untuk memproses tugas secara asinkron. Ini dapat berupa mengirim email, memproses gambar, atau apa pun yang tidak perlu dilakukan secara waktu nyata.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Antrian Pekerjaan Sederhana adalah pustaka yang dapat digunakan untuk memproses pekerjaan secara asinkron. Ini dapat digunakan dengan beanstalkd, MySQL/MariaDB, SQLite, dan PostgreSQL.

## Sesi

Sesi tidak terlalu berguna untuk API, tetapi untuk membangun aplikasi web, sesi dapat sangat penting untuk memelihara status dan informasi login.

- <span class="badge bg-primary">resmi</span> [flightphp/session](/awesome-plugins/session) - Pustaka Sesi resmi Flight. Ini adalah pustaka sesi sederhana yang dapat digunakan untuk menyimpan dan mengambil data sesi. Ini menggunakan penanganan sesi bawaan PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - Pengelola Sesi PHP (non-blocking, flash, segment, enkripsi sesi). Menggunakan open_ssl PHP untuk enkripsi/dekripsi opsional data sesi.

## Templating

Templating adalah inti dari setiap aplikasi web dengan UI. Ada sejumlah mesin templating yang dapat digunakan dengan Flight.

- <span class="badge bg-warning">ditinggalkan</span> [flightphp/core View](/learn#views) - Ini adalah mesin templating yang sangat dasar yang merupakan bagian dari inti. Ini tidak dianjurkan digunakan jika Anda memiliki lebih dari beberapa halaman dalam proyek Anda.
- [latte/latte](/awesome-plugins/latte) - Latte adalah mesin templating dengan fitur lengkap yang sangat mudah digunakan dan terasa lebih dekat dengan sintaks PHP dibandingkan Twig atau Smarty. Ini juga sangat mudah untuk diperluas dan menambahkan filter serta fungsi Anda sendiri.

## Kontribusi

Apakah Anda memiliki plugin yang ingin Anda bagikan? Kirim permohonan tarik untuk menambahkannya ke daftar!