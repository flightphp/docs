# Flight vs Fat-Free

## Apa itu Fat-Free?
[Fat-Free](https://fatfreeframework.com) (dikenal dengan sayang sebagai **F3**) adalah micro-framework PHP yang kuat namun mudah digunakan yang dirancang untuk membantu Anda membangun aplikasi web dinamis dan kokoh - dengan cepat!

Flight dibandingkan dengan Fat-Free dalam banyak hal dan mungkin sepupu terdekat dalam hal fitur dan kesederhanaan. Fat-Free memiliki
banyak fitur yang tidak dimiliki Flight, tetapi ia juga memiliki banyak fitur yang dimiliki Flight. Fat-Free mulai menunjukkan usianya
dan tidak sepopuler dulu.

Pembaruan menjadi kurang sering dan komunitas tidak seaktif dulu. Kode sederhana, tetapi terkadang kurangnya disiplin sintaks dapat membuatnya sulit dibaca dan dipahami. Ia bekerja untuk PHP 8.3, tetapi kode itu sendiri masih terlihat seperti hidup di
PHP 5.3.

## Kelebihan dibandingkan Flight

- Fat-Free memiliki beberapa bintang lebih banyak di GitHub daripada Flight.
- Fat-Free memiliki dokumentasi yang cukup baik, tetapi kurang jelas di beberapa area.
- Fat-Free memiliki beberapa sumber daya langka seperti tutorial YouTube dan artikel online yang dapat digunakan untuk mempelajari framework.
- Fat-Free memiliki [beberapa plugin bermanfaat](https://fatfreeframework.com/3.8/api-reference) bawaan yang kadang-kadang membantu.
- Fat-Free memiliki ORM bawaan yang disebut Mapper yang dapat digunakan untuk berinteraksi dengan database Anda. Flight memiliki [active-record](/awesome-plugins/active-record).
- Fat-Free memiliki Sessions, Caching, dan lokalisasi bawaan. Flight mengharuskan Anda menggunakan pustaka pihak ketiga, tetapi tercakup dalam [dokumentasi](/awesome-plugins).
- Fat-Free memiliki kelompok kecil [plugin buatan komunitas](https://fatfreeframework.com/3.8/development#Community) yang dapat digunakan untuk memperluas framework. Flight memiliki beberapa yang tercakup dalam halaman [dokumentasi](/awesome-plugins) dan [contoh](/examples).
- Fat-Free seperti Flight tidak memiliki dependensi.
- Fat-Free seperti Flight diarahkan untuk memberikan kontrol kepada pengembang atas aplikasi mereka dan pengalaman pengembang yang sederhana.
- Fat-Free mempertahankan kompatibilitas mundur seperti Flight (sebagian karena pembaruan semakin [jarang](https://github.com/bcosca/fatfree/releases)).
- Fat-Free seperti Flight ditujukan untuk pengembang yang baru memasuki dunia framework untuk pertama kalinya.
- Fat-Free memiliki mesin template bawaan yang lebih kuat daripada mesin template Flight. Flight merekomendasikan [Latte](/awesome-plugins/latte) untuk mencapai ini.
- Fat-Free memiliki perintah CLI unik jenis "route" di mana Anda dapat membangun aplikasi CLI di dalam Fat-Free itu sendiri dan memperlakukannya seperti permintaan `GET`. Flight mencapai ini dengan [runway](/awesome-plugins/runway).

## Kekurangan dibandingkan Flight

- Fat-Free memiliki beberapa tes implementasi dan bahkan memiliki kelas [test](https://fatfreeframework.com/3.8/test) sendiri yang sangat dasar. Namun,
  ia tidak diuji unit 100% seperti Flight.
- Anda harus menggunakan mesin pencari seperti Google untuk mencari situs dokumentasi.
- Flight memiliki mode gelap di situs dokumentasi mereka. (mic drop)
- Fat-Free memiliki beberapa modul yang sangat tidak terawat.
- Flight memiliki [PdoWrapper](/learn/pdo-wrapper) sederhana yang sedikit lebih sederhana daripada kelas `DB\SQL` bawaan Fat-Free.
- Flight memiliki [plugin permissions](/awesome-plugins/permissions) yang dapat digunakan untuk mengamankan aplikasi Anda. Fat-Free mengharuskan Anda menggunakan
  pustaka pihak ketiga.
- Flight memiliki ORM yang disebut [active-record](/awesome-plugins/active-record) yang terasa lebih seperti ORM daripada Mapper Fat-Free.
  Manfaat tambahan dari `active-record` adalah Anda dapat mendefinisikan hubungan antar rekaman untuk join otomatis di mana Mapper Fat-Free
  mengharuskan Anda membuat [view SQL](https://fatfreeframework.com/3.8/databases#ProsandCons).
- Sungguh menakjubkan, Fat-Free tidak memiliki namespace root. Flight memiliki namespace sepanjang untuk menghindari tabrakan dengan kode Anda sendiri.
  kelas `Cache` adalah pelanggar terbesar di sini.
- Fat-Free tidak memiliki middleware. Sebaliknya ada hook `beforeroute` dan `afterroute` yang dapat digunakan untuk memfilter permintaan dan respons di controller.
- Fat-Free tidak dapat mengelompokkan rute.
- Fat-Free memiliki penangan kontainer injeksi dependensi, tetapi dokumentasi sangat minim tentang cara menggunakannya.
- Debugging bisa menjadi sedikit rumit karena pada dasarnya semuanya disimpan di yang disebut [`HIVE`](https://fatfreeframework.com/3.8/quick-reference)