# Flight vs Slim

## Apa itu Slim?
[Slim](https://slimframework.com) adalah kerangka mikro PHP yang membantu Anda dengan cepat menulis aplikasi web dan API yang sederhana namun kuat.

Banyak inspirasi untuk beberapa fitur v3 dari Flight sebenarnya datang dari Slim. Pengelompokan rute, dan menjalankan middleware dalam urutan tertentu adalah dua fitur yang terinspirasi oleh Slim. Slim v3 diluncurkan dengan mengutamakan kesederhanaan, tetapi terdapat 
[ulasan campuran](https://github.com/slimphp/Slim/issues/2770) mengenai v4.

## Kelebihan dibandingkan Flight

- Slim memiliki komunitas pengembang yang lebih besar, yang pada gilirannya membuat modul-modul berguna untuk membantu Anda tidak menciptakan kembali roda.
- Slim mengikuti banyak antarmuka dan standar yang umum di komunitas PHP, yang meningkatkan interoperabilitas.
- Slim memiliki dokumentasi dan tutorial yang baik yang dapat digunakan untuk mempelajari kerangka kerja (tidak ada yang sebanding dengan Laravel atau Symfony).
- Slim memiliki berbagai sumber daya seperti tutorial YouTube dan artikel online yang dapat digunakan untuk mempelajari kerangka kerja.
- Slim memungkinkan Anda menggunakan komponen apa pun yang Anda inginkan untuk menangani fitur routing inti karena memenuhi standar PSR-7.

## Kekurangan dibandingkan Flight

- Secara mengejutkan, Slim tidak secepat yang Anda kira untuk sebuah mikro-kerangka. Lihat 
  [benchmark TechEmpower](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3) 
  untuk informasi lebih lanjut.
- Flight ditujukan untuk pengembang yang ingin membangun aplikasi web yang ringan, cepat, dan mudah digunakan.
- Flight tidak memiliki ketergantungan, sedangkan [Slim memiliki beberapa ketergantungan](https://github.com/slimphp/Slim/blob/4.x/composer.json) yang harus Anda instal.
- Flight ditujukan untuk kesederhanaan dan kemudahan penggunaan.
- Salah satu fitur inti dari Flight adalah bahwa ia melakukan yang terbaik untuk mempertahankan kompatibilitas mundur. Perubahan dari Slim v3 ke v4 adalah perubahan yang merusak.
- Flight dimaksudkan untuk pengembang yang memasuki dunia kerangka kerja untuk pertama kalinya.
- Flight juga dapat melakukan aplikasi tingkat perusahaan, tetapi tidak memiliki sebanyak contoh dan tutorial seperti yang dimiliki Slim. 
  Ini juga akan memerlukan lebih banyak disiplin dari pihak pengembang untuk menjaga semuanya tetap teratur dan terstruktur dengan baik.
- Flight memberikan pengembang lebih banyak kontrol atas aplikasi, sedangkan Slim dapat menyisipkan beberapa sihir di balik layar.
- Flight memiliki [PdoWrapper](/awesome-plugins/pdo-wrapper) yang sederhana yang dapat digunakan untuk berinteraksi dengan basis data Anda. Slim mengharuskan Anda menggunakan 
  perpustakaan pihak ketiga.
- Flight memiliki [plugin izin](/awesome-plugins/permissions) yang dapat digunakan untuk mengamankan aplikasi Anda. Slim mengharuskan Anda menggunakan 
  perpustakaan pihak ketiga.
- Flight memiliki ORM yang disebut [active-record](/awesome-plugins/active-record) yang dapat digunakan untuk berinteraksi dengan basis data Anda. Slim mengharuskan Anda menggunakan 
  perpustakaan pihak ketiga.
- Flight memiliki aplikasi CLI yang disebut [runway](/awesome-plugins/runway) yang dapat digunakan untuk menjalankan aplikasi Anda dari command line. Slim tidak memiliki.