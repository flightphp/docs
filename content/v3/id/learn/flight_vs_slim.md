# Flight vs Slim

## Apa itu Slim?
[Slim](https://slimframework.com) adalah kerangka kerja mikro PHP yang membantu Anda menulis aplikasi web dan API sederhana namun kuat dengan cepat.

Banyak inspirasi untuk beberapa fitur v3 dari Flight sebenarnya berasal dari Slim. Pengelompokan rute, dan eksekusi middleware dalam urutan tertentu adalah dua fitur yang terinspirasi dari Slim. Slim v3 dirilis dengan fokus pada kesederhanaan, tetapi ada 
[ulasan campuran](https://github.com/slimphp/Slim/issues/2770) mengenai v4.

## Kelebihan dibandingkan Flight

- Slim memiliki komunitas pengembang yang lebih besar, yang pada gilirannya membuat modul berguna untuk membantu Anda tidak perlu menciptakan roda ulang.
- Slim mengikuti banyak antarmuka dan standar yang umum di komunitas PHP, yang meningkatkan interoperabilitas.
- Slim memiliki dokumentasi dan tutorial yang layak untuk digunakan belajar kerangka kerja (meskipun tidak sebaik Laravel atau Symfony).
- Slim memiliki berbagai sumber daya seperti tutorial YouTube dan artikel online yang dapat digunakan untuk belajar kerangka kerja.
- Slim memungkinkan Anda menggunakan komponen apa pun yang Anda inginkan untuk menangani fitur routing inti karena sesuai dengan PSR-7.

## Kekurangan dibandingkan Flight

- Menariknya, Slim tidak secepat yang Anda bayangkan untuk sebuah kerangka kerja mikro. Lihat 
  [benchmark TechEmpower](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3) 
  untuk informasi lebih lanjut.
- Flight ditujukan untuk pengembang yang ingin membangun aplikasi web ringan, cepat, dan mudah digunakan.
- Flight tidak memiliki ketergantungan, sedangkan [Slim memiliki beberapa ketergantungan](https://github.com/slimphp/Slim/blob/4.x/composer.json) yang harus Anda instal.
- Flight ditujukan untuk kesederhanaan dan kemudahan penggunaan.
- Salah satu fitur inti Flight adalah bahwa ia berusaha sebaik mungkin untuk mempertahankan kompatibilitas mundur. Perubahan dari Slim v3 ke v4 adalah perubahan yang merusak.
- Flight ditujukan untuk pengembang yang baru memasuki dunia kerangka kerja untuk pertama kalinya.
- Flight juga dapat menangani aplikasi tingkat enterprise, tetapi tidak memiliki sebanyak contoh dan tutorial seperti Slim.
  Ini juga akan membutuhkan lebih banyak disiplin dari pengembang untuk menjaga semuanya terorganisir dan terstruktur dengan baik.
- Flight memberikan pengembang lebih banyak kendali atas aplikasi, sedangkan Slim dapat menyelinapkan beberapa sihir di balik layar.
- Flight memiliki [PdoWrapper](/learn/pdo-wrapper) sederhana yang dapat digunakan untuk berinteraksi dengan database Anda. Slim mengharuskan Anda menggunakan pustaka pihak ketiga.
- Flight memiliki plugin [permissions](/awesome-plugins/permissions) yang dapat digunakan untuk mengamankan aplikasi Anda. Slim mengharuskan Anda menggunakan pustaka pihak ketiga.
- Flight memiliki ORM bernama [active-record](/awesome-plugins/active-record) yang dapat digunakan untuk berinteraksi dengan database Anda. Slim mengharuskan Anda menggunakan pustaka pihak ketiga.
- Flight memiliki aplikasi CLI bernama [runway](/awesome-plugins/runway) yang dapat digunakan untuk menjalankan aplikasi Anda dari baris perintah. Slim tidak memiliki.