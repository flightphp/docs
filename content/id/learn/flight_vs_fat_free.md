# Flight vs Fat-Free

## Apa itu Fat-Free?
[Fat-Free](https://fatfreeframework.com) (dikenal dengan penuh kasih sebagai **F3**) adalah sebuah mikro-framework PHP yang kuat namun mudah digunakan, dirancang untuk membantu Anda membangun aplikasi web yang dinamis dan tangguh - dengan cepat!

Flight dibandingkan dengan Fat-Free dalam banyak hal dan mungkin merupakan kerabat terdekat dalam hal fitur dan kesederhanaan. Fat-Free memiliki
banyak fitur yang tidak dimiliki oleh Flight, tetapi juga memiliki banyak fitur yang dimiliki oleh Flight. Fat-Free mulai menunjukkan usianya
dan tidak sepopuler dulu.

Pembaruan menjadi semakin jarang dan komunitas tidak seaktif dulu. Kode ini cukup sederhana, tetapi terkadang kurangnya
disiplin sintaks dapat membuatnya sulit untuk dibaca dan dipahami. Ini berfungsi untuk PHP 8.3, tetapi kode itu sendiri masih terlihat seolah-olah berada di
PHP 5.3.

## Kelebihan dibandingkan Flight

- Fat-Free memiliki beberapa bintang lebih banyak di GitHub dibandingkan Flight.
- Fat-Free memiliki dokumentasi yang cukup baik, tetapi kurang jelas di beberapa area.
- Fat-Free memiliki beberapa sumber daya yang jarang seperti tutorial YouTube dan artikel online yang dapat digunakan untuk mempelajari framework.
- Fat-Free memiliki [beberapa plugin yang berguna](https://fatfreeframework.com/3.8/api-reference) yang terkadang bermanfaat.
- Fat-Free memiliki ORM bawaan yang disebut Mapper yang dapat digunakan untuk berinteraksi dengan database Anda. Flight memiliki [active-record](/awesome-plugins/active-record).
- Fat-Free memiliki sesi, caching, dan lokalisasi bawaan. Flight mengharuskan Anda untuk menggunakan pustaka pihak ketiga, tetapi dijelaskan dalam [dokumentasi](/awesome-plugins).
- Fat-Free memiliki sekelompok [plugin yang dibuat oleh komunitas](https://fatfreeframework.com/3.8/development#Community) yang dapat digunakan untuk memperluas framework. Flight memiliki beberapa yang dijelaskan dalam halaman [dokumentasi](/awesome-plugins) dan [contoh](/examples).
- Fat-Free seperti Flight tidak memiliki ketergantungan.
- Fat-Free seperti Flight ditujukan untuk memberikan kontrol kepada pengembang atas aplikasi mereka dan pengalaman pengembang yang sederhana.
- Fat-Free mempertahankan kompatibilitas ke belakang seperti halnya Flight (sebagian karena pembaruan semakin [jarang](https://github.com/bcosca/fatfree/releases)).
- Fat-Free seperti Flight ditujukan untuk pengembang yang menjelajah ke dunia framework untuk pertama kalinya.
- Fat-Free memiliki mesin template bawaan yang lebih kuat dibandingkan dengan mesin template Flight. Flight merekomendasikan [Latte](/awesome-plugins/latte) untuk mencapai ini.
- Fat-Free memiliki perintah CLI tipe "route" unik di mana Anda dapat membangun aplikasi CLI di dalam Fat-Free itu sendiri dan memperlakukannya seperti permintaan `GET`. Flight mencapainya dengan [runway](/awesome-plugins/runway).

## Kekurangan dibandingkan Flight

- Fat-Free memiliki beberapa pengujian implementasi dan bahkan memiliki kelas [test](https://fatfreeframework.com/3.8/test) sendiri yang sangat dasar. Namun, 
  tidak 100% diuji unit seperti Flight.
- Anda harus menggunakan mesin pencari seperti Google untuk benar-benar mencari situs dokumentasi.
- Flight memiliki mode gelap di situs dokumentasi mereka. (mic drop)
- Fat-Free memiliki beberapa modul yang sangat kurang terawat.
- Flight memiliki [PdoWrapper](/awesome-plugins/pdo-wrapper) yang sedikit lebih sederhana dibandingkan kelas `DB\SQL` bawaan Fat-Free.
- Flight memiliki [plugin izin](/awesome-plugins/permissions) yang dapat digunakan untuk mengamankan aplikasi Anda. Slim mengharuskan Anda untuk menggunakan 
  pustaka pihak ketiga.
- Flight memiliki ORM yang disebut [active-record](/awesome-plugins/active-record) yang terasa lebih seperti ORM dibandingkan dengan Mapper Fat-Free.
  Manfaat tambahan dari `active-record` adalah Anda dapat mendefinisikan hubungan antara catatan untuk penggabungan otomatis di mana Mapper Fat-Free
  mengharuskan Anda untuk membuat [tampilan SQL](https://fatfreeframework.com/3.8/databases#ProsandCons).
- Yang mengejutkan, Fat-Free tidak memiliki namespace akar. Flight memiliki namespace yang lengkap untuk tidak bertabrakan dengan kode Anda sendiri.
  kelas `Cache` adalah pelanggar terbesar di sini.
- Fat-Free tidak memiliki middleware. Sebaliknya, ada hook `beforeroute` dan `afterroute` yang dapat digunakan untuk memfilter permintaan dan respons di dalam kontroler.
- Fat-Free tidak dapat mengelompokkan routes.
- Fat-Free memiliki penangan kontainer injeksi ketergantungan, tetapi dokumentasinya sangat jarang tentang cara menggunakannya.
- Penggunaan debugging bisa sedikit rumit karena pada dasarnya semuanya disimpan dalam apa yang disebut [`HIVE`](https://fatfreeframework.com/3.8/quick-reference).