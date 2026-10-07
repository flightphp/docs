# Plugin Luar Biasa

Flight sangat dapat diperluas. Ada sejumlah plugin yang dapat digunakan untuk menambahkan fungsionalitas ke aplikasi Flight Anda. Beberapa didukung secara resmi oleh Tim Flight dan lainnya adalah library mikro/ringan untuk membantu Anda memulai.

## Alat AI

Flight dapat menjadi lebih keren dengan plugin bertenaga AI.

- [Flight MCP](/awesome-plugins/mcp) - Plugin untuk mengintegrasikan MCP (Model Control Protocol) dengan Flight, memungkinkan fungsionalitas bertenaga AI yang mulus. Sebagian besar berfokus pada halaman dokumentasi, ini membantu menjaga biaya token tetap rendah dengan menyediakan informasi terbaru tentang proyek Flight Anda.
- [stribus/mcp-flightphp-server-skeleton](https://github.com/stribus/mcp-flightphp-server-skeleton) - Kerangka kerja server MCP FlightPHP dengan HTTP dan stdio, plus penemuan otomatis alat, prompt, dan sumber daya.

## Dokumentasi API

Dokumentasi API sangat penting untuk API apa pun. Ini membantu pengembang memahami cara berinteraksi dengan API Anda dan apa yang diharapkan sebagai balasannya. Ada beberapa alat yang tersedia untuk membantu Anda membuat dokumentasi API untuk Proyek Flight Anda.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Tulisan blog oleh Daniel Schreiber tentang cara menggunakan Spesifikasi OpenAPI dengan FlightPHP untuk membangun API Anda menggunakan pendekatan API first.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI adalah alat hebat untuk membantu Anda membuat dokumentasi API untuk proyek Flight Anda. Sangat mudah digunakan dan dapat disesuaikan dengan kebutuhan Anda. Ini adalah library PHP untuk membantu Anda membuat dokumentasi Swagger.

## Pemantauan Kinerja Aplikasi (APM)

Pemantauan Kinerja Aplikasi (APM) sangat penting untuk aplikasi apa pun. Ini membantu Anda memahami bagaimana kinerja aplikasi Anda dan di mana titik hambatannya. Ada sejumlah alat APM yang dapat digunakan dengan Flight.
- <span class="badge bg-primary">official</span> [flightphp/apm](/awesome-plugins/apm) - Flight APM adalah library APM sederhana yang dapat digunakan untuk memantau aplikasi Flight Anda. Ini dapat digunakan untuk memantau kinerja aplikasi Anda dan membantu Anda mengidentifikasi titik hambatan.

## Asinkron

Flight sudah menjadi framework yang cepat, tetapi memasangkan mesin turbo padanya membuat segalanya lebih menyenangkan (dan menantang)!

- [flightphp/async](/awesome-plugins/async) - Library Async resmi Flight. Library ini adalah cara sederhana untuk menambahkan pemrosesan asinkron ke aplikasi Anda. Library ini menggunakan Swoole/Openswoole di dalamnya untuk menyediakan cara yang sederhana dan efektif untuk menjalankan tugas secara asinkron.

## Otorisasi/Izin

Otorisasi dan Izin sangat penting untuk aplikasi apa pun yang memerlukan kontrol untuk menentukan siapa yang dapat mengakses apa.

- <span class="badge bg-primary">official</span> [flightphp/permissions](/awesome-plugins/permissions) - Library Izin Flight resmi. Library ini adalah cara sederhana untuk menambahkan izin tingkat pengguna dan aplikasi ke aplikasi Anda.

## Autentikasi

Autentikasi sangat penting untuk aplikasi yang perlu memverifikasi identitas pengguna dan mengamankan endpoint API.

- [firebase/php-jwt](/awesome-plugins/jwt) - Library JSON Web Token (JWT) untuk PHP. Cara sederhana dan aman untuk menerapkan autentikasi berbasis token di aplikasi Flight Anda. Sempurna untuk autentikasi API stateless, melindungi rute dengan middleware, dan menerapkan alur otorisasi ala OAuth.

## Caching

Caching adalah cara yang bagus untuk mempercepat aplikasi Anda. Ada sejumlah library caching yang dapat digunakan dengan Flight.

- <span class="badge bg-primary">official</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Kelas caching file-dalam-PHP yang ringan, sederhana, dan mandiri

## CLI

Aplikasi CLI adalah cara yang bagus untuk berinteraksi dengan aplikasi Anda. Anda dapat menggunakannya untuk menghasilkan controller, menampilkan semua rute, dan lainnya.

- <span class="badge bg-primary">official</span> [flightphp/runway](/awesome-plugins/runway) - Runway adalah aplikasi CLI yang membantu Anda mengelola aplikasi Flight Anda.

## Cookie

Cookie adalah cara yang bagus untuk menyimpan data kecil di sisi klien. Cookie dapat digunakan untuk menyimpan preferensi pengguna, pengaturan aplikasi, dan lainnya.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie adalah library PHP yang menyediakan cara sederhana dan efektif untuk mengelola cookie.

## Debugging

Debugging sangat penting saat Anda mengembangkan di lingkungan lokal. Ada beberapa plugin yang dapat meningkatkan pengalaman debugging Anda.

- [tracy/tracy](/awesome-plugins/tracy) - Ini adalah penanganan error berfitur lengkap yang dapat digunakan dengan Flight. Ia memiliki sejumlah panel yang dapat membantu Anda debug aplikasi Anda. Ia juga sangat mudah diperluas dan menambahkan panel sendiri.
- <span class="badge bg-primary">official</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Digunakan dengan penanganan error [Tracy](/awesome-plugins/tracy), plugin ini menambahkan beberapa panel tambahan untuk membantu debugging khusus untuk proyek Flight.

## Database

Database adalah inti dari sebagian besar aplikasi. Ini adalah cara Anda menyimpan dan mengambil data. Beberapa library database hanyalah pembungkus untuk menulis query dan beberapa adalah ORM lengkap.

- <span class="badge bg-primary">official</span> [flightphp/core SimplePdo](/learn/simple-pdo) - Pembantu PDO Flight resmi yang merupakan bagian dari core. Ini adalah pembungkus modern dengan metode pembantu yang praktis seperti `insert()`, `update()`, `delete()`, dan `transaction()` untuk menyederhanakan operasi database. Semua hasil dikembalikan sebagai Collection untuk akses array/objek yang fleksibel. Bukan ORM, hanya cara yang lebih baik untuk bekerja dengan PDO.
- <span class="badge bg-warning">deprecated</span> [flightphp/core PdoWrapper](/learn/pdo-wrapper) - Pembungkus PDO Flight resmi yang merupakan bagian dari core (deprecated sejak v3.18.0). Gunakan SimplePdo sebagai gantinya.
- <span class="badge bg-primary">official</span> [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord Flight resmi. Library kecil yang hebat untuk dengan mudah mengambil dan menyimpan data di database Anda.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin untuk melacak semua perubahan database untuk proyek Anda.
- [knifelemon/easy-query](/awesome-plugins/easy-query) - Pembuat query SQL ringan dan fluent yang menghasilkan SQL dan parameter untuk prepared statements. Berfungsi hebat dengan [SimplePdo](/learn/simple-pdo).

## Enkripsi

Enkripsi sangat penting untuk aplikasi apa pun yang menyimpan data sensitif. Enkripsi dan dekripsi data tidak terlalu sulit, tetapi menyimpan kunci enkripsi dengan benar [bisa](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [menjadi](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [sulit](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Hal yang paling penting adalah jangan pernah menyimpan kunci enkripsi Anda di direktori publik atau melakukan commit ke repositori kode Anda.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Ini adalah library yang dapat digunakan untuk mengenkripsi dan mendekripsi data. Memulai dan menjalankannya cukup sederhana untuk mulai mengenkripsi dan mendekripsi data.

## Email

Mengirim email adalah kebutuhan inti bagi sebagian besar aplikasi web - pesan selamat datang, reset kata sandi, notifikasi. Library-library ini membuatnya mudah dilakukan sambil menjaga keterkiriman tetap solid.

- [ryanstubbs/flightmail](/awesome-plugins/flightmail) - FlightMail membungkus Symfony Mailer dengan API yang ramah-Flight dan lancar. Kirim melalui SMTP atau penyedia utama mana pun melalui string DSN sederhana, atur rute penyedia yang berbeda per pesan, dan render body dengan template Twig atau Latte. Ini adalah plugin tidak resmi untuk Flight dan tidak dikelola oleh tim Flight.

## Antrean Tugas

Antrean tugas sangat membantu untuk memproses tugas secara asinkron. Ini bisa berupa mengirim email, memproses gambar, atau apa pun yang tidak perlu dilakukan secara real-time.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue adalah library yang dapat digunakan untuk memproses pekerjaan secara asinkron. Dapat digunakan dengan beanstalkd, MySQL/MariaDB, SQLite, dan PostgreSQL.

## Sesi

Sesi tidak terlalu berguna untuk API, tetapi untuk membangun aplikasi web, sesi bisa menjadi sangat penting untuk menjaga status dan informasi login.

- <span class="badge bg-primary">official</span> [flightphp/session](/awesome-plugins/session) - Library Sesi Flight resmi. Ini adalah library sesi sederhana yang dapat digunakan untuk menyimpan dan mengambil data sesi. Library ini menggunakan penanganan sesi bawaan PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - Manajer Sesi PHP (non-blocking, flash, segmen, enkripsi sesi). Menggunakan open_ssl PHP untuk enkripsi/dekripsi opsional data sesi.

## Templating

Templating adalah inti dari aplikasi web apa pun yang memiliki UI. Ada sejumlah mesin templating yang dapat digunakan dengan Flight.

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - Ini adalah mesin templating yang sangat dasar yang merupakan bagian dari core. Tidak disarankan untuk digunakan jika Anda memiliki lebih dari beberapa halaman di proyek Anda.
- [latte/latte](/awesome-plugins/latte) - Latte adalah mesin templating berfitur lengkap yang sangat mudah digunakan dan terasa lebih dekat dengan sintaks PHP daripada Twig atau Smarty. Ini juga sangat mudah diperluas dan menambahkan filter dan fungsi Anda sendiri.
- [twig/twig](/awesome-plugins/twig) - Twig adalah mesin template yang fleksibel, cepat, dan aman (yang sama digunakan oleh Symfony). Alat AI dan banyak pengembang PHP sangat mengenalnya, secara default ia meng-escape output secara otomatis, dan memiliki ekosistem ekstensi yang besar.
- [knifelemon/comment-template](/awesome-plugins/comment-template) - CommentTemplate adalah mesin template PHP yang kuat dengan kompilasi aset, pewarisan template, dan pemrosesan variabel. Fitur-fitur: minifikasi CSS/JS otomatis, caching, encoding Base64, dan integrasi kerangka kerja Flight PHP opsional.

## Integrasi WordPress

Ingin menggunakan Flight di proyek WordPress Anda? Ada plugin praktis untuk itu!

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - Plugin WordPress ini memungkinkan Anda menjalankan Flight tepat di samping WordPress. Ini sempurna untuk menambahkan API kustom, layanan mikro, atau bahkan aplikasi penuh ke situs WordPress Anda menggunakan kerangka kerja Flight. Sangat berguna jika Anda ingin mendapatkan yang terbaik dari keduanya!

## Berkontribusi

Punya plugin yang ingin Anda bagikan? Kirimkan pull request untuk menambahkannya ke daftar!