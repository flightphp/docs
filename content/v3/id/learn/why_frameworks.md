# Mengapa Kerangka?

Beberapa pemrogram sangat menentang penggunaan kerangka. Mereka berpendapat bahwa kerangka itu berlebihan, lambat, dan sulit dipelajari. 
Mereka mengatakan bahwa kerangka tidak diperlukan dan bahwa Anda dapat menulis kode yang lebih baik tanpa mereka. 
Tentu ada beberapa poin yang valid mengenai kekurangan menggunakan kerangka. Namun, ada juga banyak keuntungan dalam menggunakan kerangka.

## Alasan untuk Menggunakan Kerangka

Berikut adalah beberapa alasan mengapa Anda mungkin ingin mempertimbangkan untuk menggunakan kerangka:

- **Pengembangan Cepat**: Kerangka menyediakan banyak fungsionalitas langsung dari kotaknya. Ini berarti Anda dapat membangun aplikasi web lebih cepat. Anda tidak perlu menulis sebanyak itu karena kerangka menyediakan banyak fungsionalitas yang Anda butuhkan.
- **Konsistensi**: Kerangka menyediakan cara yang konsisten untuk melakukan hal-hal. Ini memudahkan Anda untuk memahami cara kerja kode dan memudahkan pengembang lain untuk memahami kode Anda. Jika Anda memiliki skrip demi skrip, Anda mungkin kehilangan konsistensi antara skrip, terutama jika Anda bekerja dengan tim pengembang.
- **Keamanan**: Kerangka menyediakan fitur keamanan yang membantu melindungi aplikasi web Anda dari ancaman keamanan umum. Ini berarti Anda tidak perlu khawatir sebanyak itu tentang keamanan karena kerangka mengurus banyak hal untuk Anda.
- **Komunitas**: Kerangka memiliki komunitas besar pengembang yang berkontribusi pada kerangka. Ini berarti Anda dapat mendapatkan bantuan dari pengembang lain ketika Anda memiliki pertanyaan atau masalah. Ini juga berarti bahwa ada banyak sumber daya yang tersedia untuk membantu Anda belajar cara menggunakan kerangka.
- **Praktik Terbaik**: Kerangka dibangun menggunakan praktik terbaik. Ini berarti Anda dapat belajar dari kerangka dan menggunakan praktik terbaik yang sama dalam kode Anda sendiri. Ini dapat membantu Anda menjadi pemrogram yang lebih baik. Kadang-kadang Anda tidak tahu apa yang tidak Anda ketahui dan itu dapat merugikan Anda pada akhirnya.
- **Ekstensibilitas**: Kerangka dirancang untuk diperluas. Ini berarti Anda dapat menambahkan fungsionalitas Anda sendiri ke dalam kerangka. Ini memungkinkan Anda untuk membangun aplikasi web yang disesuaikan dengan kebutuhan spesifik Anda.

Flight adalah micro-framework. Ini berarti bahwa ia kecil dan ringan. Ia tidak menyediakan sebanyak fungsionalitas seperti kerangka besar seperti Laravel atau Symfony. 
Namun, ia menyediakan banyak fungsionalitas yang Anda butuhkan untuk membangun aplikasi web. Ini juga mudah dipelajari dan digunakan. 
Ini membuatnya menjadi pilihan yang baik untuk membangun aplikasi web dengan cepat dan mudah. Jika Anda baru mengenal kerangka, Flight adalah kerangka pemula yang hebat untuk mulai digunakan. 
Ini akan membantu Anda belajar tentang keuntungan menggunakan kerangka tanpa membebani Anda dengan terlalu banyak kompleksitas. 
Setelah Anda memiliki beberapa pengalaman dengan Flight, akan lebih mudah untuk beralih ke kerangka yang lebih kompleks seperti Laravel atau Symfony, 
namun Flight masih dapat membuat aplikasi yang berhasil dan tangguh.

## Apa itu Routing?

Routing adalah inti dari kerangka Flight, tetapi apa itu sebenarnya? Routing adalah proses mengambil URL dan mencocokkannya dengan fungsi tertentu di kode Anda. 
Inilah cara Anda dapat membuat situs web Anda melakukan hal-hal yang berbeda berdasarkan URL yang diminta. Misalnya, Anda mungkin ingin menampilkan profil pengguna ketika mereka 
mengunjungi `/user/1234`, tetapi menampilkan daftar semua pengguna ketika mereka mengunjungi `/users`. Semua ini dilakukan melalui routing.

Ini mungkin bekerja seperti ini:

- Seorang pengguna pergi ke peramban Anda dan mengetik `http://example.com/user/1234`.
- Server menerima permintaan dan melihat URL dan meneruskannya ke kode aplikasi Flight Anda.
- Katakanlah di kode Flight Anda Anda memiliki sesuatu seperti `Flight::route('/user/@id', [ 'UserController', 'viewUserProfile' ]);`. Kode aplikasi Flight Anda melihat URL dan melihat bahwa itu cocok dengan jalur yang telah Anda definisikan, dan kemudian menjalankan kode yang telah Anda definisikan untuk jalur tersebut.  
- Router Flight kemudian akan dijalankan dan memanggil metode `viewUserProfile($id)` dalam kelas `UserController`, dengan melewatkan `1234` sebagai argumen `$id` dalam metode tersebut.
- Kode dalam metode `viewUserProfile()` Anda kemudian akan dijalankan dan melakukan apa yang telah Anda katakan untuk dilakukan. Anda mungkin akan mengeluarkan beberapa HTML untuk halaman profil pengguna, atau jika ini adalah API RESTful, Anda mungkin akan mengeluarkan respons JSON dengan informasi pengguna.
- Flight membungkus ini dengan rapi, menghasilkan header respons dan mengirimkannya kembali ke peramban pengguna.
- Pengguna merasa senang dan memberi diri mereka pelukan hangat!

### Dan Mengapa Ini Penting?

Memiliki router terpusat yang baik sebenarnya dapat membuat hidup Anda jauh lebih mudah! Ini mungkin sulit dilihat pada awalnya. Berikut adalah beberapa alasan mengapa:

- **Routing Terpusat**: Anda dapat menyimpan semua jalur Anda di satu tempat. Ini memudahkan untuk melihat jalur mana yang Anda miliki dan apa yang mereka lakukan. Ini juga memudahkan untuk mengubahnya jika Anda perlu.
- **Parameter Jalur**: Anda dapat menggunakan parameter jalur untuk melewatkan data ke metode jalur Anda. Ini adalah cara yang bagus untuk menjaga kode Anda tetap bersih dan teratur.
- **Kelompok Jalur**: Anda dapat mengelompokkan jalur bersama. Ini bagus untuk menjaga kode Anda teratur dan untuk menerapkan [middleware](middleware) ke sekelompok jalur.
- **Alias Jalur**: Anda dapat menetapkan alias ke sebuah jalur, sehingga URL dapat dibuat secara dinamis nanti di kode Anda (seperti template misalnya). Contoh: alih-alih menghardcode `/user/1234` di kode Anda, Anda bisa merujuk ke alias `user_view` dan melewatkan `id` sebagai parameter. Ini sangat memudahkan jika Anda memutuskan untuk mengubahnya menjadi `/admin/user/1234` nanti. Anda tidak perlu mengubah semua URL yang Anda hardcode, cukup URL yang terhubung ke jalur.
- **Middleware Jalur**: Anda dapat menambahkan middleware ke jalur Anda. Middleware sangat kuat dalam menambahkan perilaku tertentu ke aplikasi Anda, seperti mengotentikasi bahwa pengguna tertentu dapat mengakses jalur atau kelompok jalur.

Saya yakin Anda sudah familiar dengan cara skrip demi skrip untuk membuat situs web. Anda mungkin memiliki file bernama `index.php` yang memiliki banyak pernyataan `if` 
untuk memeriksa URL dan kemudian menjalankan fungsi tertentu berdasarkan URL tersebut. Ini adalah bentuk routing, tetapi tidak sangat teratur dan dapat 
menjadi tidak terkendali dengan cepat. Sistem routing Flight adalah cara yang jauh lebih teratur dan kuat untuk menangani routing.

Ini?

```php

// /user/view_profile.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	viewUserProfile($id);
}

// /user/edit_profile.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	editUserProfile($id);
}

// dll...
```

Atau ini?

```php

// index.php
Flight::route('/user/@id', [ 'UserController', 'viewUserProfile' ]);
Flight::route('/user/@id/edit', [ 'UserController', 'editUserProfile' ]);

// Mungkin di dalam app/controllers/UserController.php Anda
class UserController {
	public function viewUserProfile($id) {
		// lakukan sesuatu
	}

	public function editUserProfile($id) {
		// lakukan sesuatu
	}
}
```

Semoga Anda mulai melihat manfaat menggunakan sistem routing terpusat. Ini jauh lebih mudah untuk dikelola dan dipahami dalam jangka panjang!

## Permintaan dan Respons

Flight menyediakan cara yang sederhana dan mudah untuk menangani permintaan dan respons. Ini adalah inti dari apa yang dilakukan kerangka web. Ini menerima permintaan 
dari peramban pengguna, memprosesnya, dan kemudian mengirim kembali respons. Ini adalah cara Anda dapat membangun aplikasi web yang melakukan hal-hal seperti menampilkan profil pengguna, 
memungkinkan pengguna masuk, atau memungkinkan pengguna membuat posting blog baru.

### Permintaan

Permintaan adalah apa yang dikirim peramban pengguna ke server Anda ketika mereka mengunjungi situs web Anda. Permintaan ini mengandung informasi tentang apa yang ingin dilakukan pengguna. 
Misalnya, mungkin berisi informasi tentang URL apa yang ingin dikunjungi pengguna, data apa yang ingin dikirim pengguna ke server Anda, atau jenis data apa yang ingin diterima pengguna dari server Anda. 
Penting untuk diketahui bahwa permintaan bersifat read-only. Anda tidak dapat mengubah permintaan, tetapi Anda dapat membacanya.

Flight menyediakan cara yang sederhana untuk mengakses informasi tentang permintaan tersebut. Anda dapat mengakses informasi tentang permintaan menggunakan metode `Flight::request()`
. Metode ini mengembalikan objek `Request` yang berisi informasi tentang permintaan. Anda dapat menggunakan objek ini untuk mengakses informasi tentang 
permintaan, seperti URL, metode, atau data yang dikirim pengguna ke server Anda.

### Respons

Respons adalah apa yang dikirim server Anda kembali ke peramban pengguna ketika mereka mengunjungi situs web Anda. Respons ini berisi informasi tentang apa yang ingin dilakukan server Anda. 
Misalnya, mungkin berisi informasi tentang jenis data apa yang ingin dikirim server Anda kepada pengguna, jenis data apa yang ingin diterima server Anda dari pengguna, 
atau jenis data apa yang ingin disimpan server Anda di komputer pengguna.

Flight menyediakan cara yang sederhana untuk mengirim respons ke peramban pengguna. Anda dapat mengirim respons menggunakan metode `Flight::response()`. Metode ini 
mengambil objek `Response` sebagai argumen dan mengirimkan respons ke peramban pengguna. Anda dapat menggunakan objek ini untuk mengirim respons kepada peramban pengguna, 
seperti HTML, JSON, atau file. Flight membantu Anda secara otomatis menghasilkan beberapa bagian dari respons untuk mempermudah, tetapi pada akhirnya Anda memiliki 
kendali atas apa yang Anda kirim kembali kepada pengguna.