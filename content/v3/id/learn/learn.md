# Pelajari Tentang Flight

Flight adalah framework PHP yang cepat, sederhana, dan dapat diperluas. Ini sangat serbaguna dan dapat digunakan untuk membangun berbagai jenis aplikasi web. 
Ini dibangun dengan prinsip kesederhanaan dan ditulis dengan cara yang mudah dipahami dan digunakan.

> **Catatan:** Anda akan melihat contoh yang menggunakan `Flight::` sebagai variabel statis dan beberapa yang menggunakan objek Engine `$app->`. Keduanya dapat digunakan secara bergantian. `$app` dan `$this->app` dalam controller/middleware adalah pendekatan yang direkomendasikan oleh tim Flight.

## Komponen Inti

### [Routing](/learn/routing)

Pelajari cara mengelola rute untuk aplikasi web Anda. Ini juga mencakup pengelompokan rute, parameter rute, dan middleware.

### [Middleware](/learn/middleware)

Pelajari cara menggunakan middleware untuk memfilter permintaan dan respons dalam aplikasi Anda.

### [Autoloading](/learn/autoloading)

Pelajari cara autoload kelas Anda sendiri dalam aplikasi Anda.

### [Requests](/learn/requests)

Pelajari cara menangani permintaan dan respons dalam aplikasi Anda.

### [Responses](/learn/responses)

Pelajari cara mengirim respons ke pengguna Anda.

### [HTML Templates](/learn/templates)

Pelajari cara menggunakan engine tampilan bawaan untuk merender template HTML Anda.

### [Security](/learn/security)

Pelajari cara mengamankan aplikasi Anda dari ancaman keamanan umum.

### [Configuration](/learn/configuration)

Pelajari cara mengonfigurasi framework untuk aplikasi Anda.

### [Event Manager](/learn/events)

Pelajari cara menggunakan sistem event untuk menambahkan event kustom ke aplikasi Anda.

### [Extending Flight](/learn/extending)

Pelajari cara memperluas framework dengan menambahkan metode dan kelas Anda sendiri.

### [Method Hooks and Filtering](/learn/filtering)

Pelajari cara menambahkan hook event ke metode Anda dan metode framework internal.

### [Dependency Injection Container (DIC)](/learn/dependency-injection-container)

Pelajari cara menggunakan container injeksi dependensi (DIC) untuk mengelola dependensi aplikasi Anda.

## Kelas Utilitas

### [Collections](/learn/collections)

Collections digunakan untuk menyimpan data dan dapat diakses sebagai array atau objek untuk kemudahan penggunaan.

### [JSON Wrapper](/learn/json)

Ini memiliki beberapa fungsi sederhana untuk membuat pengkodean dan dekode JSON Anda konsisten.

### [SimplePdo](/learn/simple-pdo)

PDO terkadang bisa menambah sakit kepala yang tidak perlu. SimplePdo adalah kelas pembantu PDO modern dengan metode yang nyaman seperti `insert()`, `update()`, `delete()`, dan `transaction()` untuk membuat operasi database jauh lebih mudah.

### [PdoWrapper](/learn/pdo-wrapper) (Deprecated)

Wrapper PDO asli sudah deprecated sejak v3.18.0. Silakan gunakan [SimplePdo](/learn/simple-pdo) sebagai gantinya.

### [Uploaded File Handler](/learn/uploaded-file)

Kelas sederhana untuk membantu mengelola file yang diunggah dan memindahkannya ke lokasi permanen.

## Konsep Penting

### [Why a Framework?](/learn/why-frameworks)

Berikut adalah artikel singkat tentang mengapa Anda harus menggunakan framework. Ini ide bagus untuk memahami manfaat menggunakan framework sebelum Anda mulai menggunakannya.

Selain itu, tutorial yang sangat baik telah dibuat oleh [@lubiana](https://git.php.fail/lubiana). Meskipun tidak membahas secara mendetail tentang Flight secara khusus, 
panduan ini akan membantu Anda memahami beberapa konsep utama seputar framework dan mengapa mereka bermanfaat untuk digunakan. 
Anda dapat menemukan tutorial tersebut [di sini](https://git.php.fail/lubiana/no-framework-tutorial/src/branch/master/README.md).

### [Flight Compared to Other Frameworks](/learn/flight-vs-another-framework)

Jika Anda bermigrasi dari framework lain seperti Laravel, Slim, Fat-Free, atau Symfony ke Flight, halaman ini akan membantu Anda memahami perbedaan antara keduanya.

## Topik Lainnya

### [Unit Testing](/learn/unit-testing)

Ikuti panduan ini untuk mempelajari cara melakukan unit testing kode Flight Anda agar kokoh.

### [AI & Developer Experience](/learn/ai)

Pelajari bagaimana Flight bekerja dengan alat AI dan alur kerja pengembang modern untuk membantu Anda mengkode lebih cepat dan lebih pintar.

### [Migrating v2 -> v3](/learn/migrating-to-v3)

Kompatibilitas mundur sebagian besar telah dipertahankan, tetapi ada beberapa perubahan yang harus Anda ketahui saat bermigrasi dari v2 ke v3.