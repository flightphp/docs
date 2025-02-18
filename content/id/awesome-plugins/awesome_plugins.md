# Plugin yang Menakjubkan

Flight sangat dapat diperluas. Ada sejumlah plugin yang dapat digunakan untuk menambahkan fungsionalitas pada aplikasi Flight Anda. Beberapa didukung secara resmi oleh Tim Flight dan yang lainnya adalah pustaka mikro/lite untuk membantu Anda memulai.

## Autentikasi/Otorisasi

Autentikasi dan Otorisasi sangat penting untuk aplikasi apa pun yang memerlukan kontrol tentang siapa yang dapat mengakses apa.

- [flightphp/permissions](/awesome-plugins/permissions) - Pustaka Izin Flight resmi. Pustaka ini adalah cara sederhana untuk menambahkan izin tingkat pengguna dan aplikasi ke aplikasi Anda.

## Penyimpanan Sementara

Penyimpanan sementara adalah cara yang bagus untuk mempercepat aplikasi Anda. Ada beberapa pustaka penyimpanan sementara yang dapat digunakan dengan Flight.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Kelas penyimpanan sementara PHP di-file yang ringan, sederhana, dan mandiri

## CLI

Aplikasi CLI adalah cara yang baik untuk berinteraksi dengan aplikasi Anda. Anda dapat menggunakannya untuk menghasilkan pengendali, menampilkan semua rute, dan lainnya.

- [flightphp/runway](/awesome-plugins/runway) - Runway adalah aplikasi CLI yang membantu Anda mengelola aplikasi Flight Anda.

## Kue

Kue adalah cara yang bagus untuk menyimpan sedikit data di sisi klien. Mereka dapat digunakan untuk menyimpan preferensi pengguna, pengaturan aplikasi, dan lainnya.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie adalah pustaka PHP yang menyediakan cara sederhana dan efektif untuk mengelola kue.

## Pen-debugan

Pen-debugan sangat penting saat Anda mengembangkan di lingkungan lokal Anda. Ada beberapa plugin yang dapat meningkatkan pengalaman pen-debugan Anda.

- [tracy/tracy](/awesome-plugins/tracy) - Ini adalah pengendali kesalahan fitur lengkap yang dapat digunakan dengan Flight. Ini memiliki sejumlah panel yang dapat membantu Anda men-debug aplikasi Anda. Ini juga sangat mudah untuk diperluas dan menambahkan panel Anda sendiri.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Digunakan dengan pengendali kesalahan [Tracy](/awesome-plugins/tracy), plugin ini menambahkan beberapa panel ekstra untuk membantu dengan pen-debugan khusus untuk proyek Flight.

## Basis Data

Basis data adalah inti dari sebagian besar aplikasi. Ini adalah bagaimana Anda menyimpan dan mengambil data. Beberapa pustaka basis data adalah pembungkus untuk menulis kueri dan beberapa adalah ORM yang sepenuhnya berfungsi.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Pembungkus PDO Flight resmi yang merupakan bagian dari inti. Ini adalah pembungkus sederhana untuk membantu menyederhanakan proses menulis kueri dan mengeksekusinya. Ini bukan ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - ORM/Pemetaan ActiveRecord Flight resmi. Pustaka kecil yang hebat untuk dengan mudah mengambil dan menyimpan data di basis data Anda.

## Enkripsi

Enkripsi sangat penting untuk aplikasi apa pun yang menyimpan data sensitif. Melakukan enkripsi dan dekripsi data tidak terlalu sulit, tetapi menyimpan kunci enkripsi dengan benar [dapat](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [menjadi](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [sulit](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Hal yang paling penting adalah jangan pernah menyimpan kunci enkripsi Anda di direktori publik atau mengkomitnya ke repositori kode Anda.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Ini adalah pustaka yang dapat digunakan untuk mengenkripsi dan mendekripsi data. Memulai cukup sederhana untuk mulai mengenkripsi dan mendekripsi data.

## Sesi

Sesi tidak begitu berguna untuk API, tetapi untuk membangun aplikasi web, sesi dapat sangat penting untuk mempertahankan status dan informasi login.

- [Ghostff/Session](/awesome-plugins/session) - Pengelola Sesi PHP (non-blocking, flash, segment, enkripsi sesi). Menggunakan PHP open_ssl untuk enkripsi/dekripsi opsional data sesi.

## Penataan

Penataan adalah inti dari aplikasi web mana pun dengan antarmuka pengguna. Ada sejumlah mesin penataan yang dapat digunakan dengan Flight.

- [flightphp/core View](/learn#views) - Ini adalah mesin penataan yang sangat dasar yang merupakan bagian dari inti. Tidak disarankan untuk digunakan jika Anda memiliki lebih dari beberapa halaman di proyek Anda.
- [latte/latte](/awesome-plugins/latte) - Latte adalah mesin penataan fitur lengkap yang sangat mudah digunakan dan terasa lebih dekat dengan sintaksis PHP daripada Twig atau Smarty. Ini juga sangat mudah untuk diperluas dan menambahkan filter dan fungsi Anda sendiri.

## Berkontribusi

Apakah Anda memiliki plugin yang ingin Anda bagikan? Kirimkan permintaan tarik untuk menambahkannya ke daftar!