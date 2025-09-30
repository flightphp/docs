# Flight vs Laravel

## Apa itu Laravel?
[Laravel](https://laravel.com) adalah framework lengkap yang memiliki semua fitur lengkap dan ekosistem yang berfokus pada pengembang yang luar biasa, 
tetapi dengan biaya dalam hal performa dan kompleksitas. Tujuan Laravel adalah agar pengembang memiliki tingkat produktivitas tertinggi 
dan membuat tugas-tugas umum menjadi mudah. Laravel adalah pilihan yang bagus untuk pengembang yang ingin membangun aplikasi web 
perusahaan yang lengkap. Itu datang dengan beberapa trade-off, khususnya dalam hal performa dan kompleksitas. Belajar dasar-dasar Laravel bisa mudah, tetapi mencapai kefasihan dalam framework ini bisa memakan waktu.

Ada juga begitu banyak modul Laravel sehingga pengembang sering merasa satu-satunya cara untuk menyelesaikan masalah adalah melalui 
modul-modul ini, padahal sebenarnya Anda bisa saja menggunakan pustaka lain atau menulis kode sendiri.

## Kelebihan dibandingkan Flight

- Laravel memiliki **ekosistem besar** dari pengembang dan modul yang dapat digunakan untuk menyelesaikan masalah umum.
- Laravel memiliki ORM lengkap yang dapat digunakan untuk berinteraksi dengan database Anda.
- Laravel memiliki jumlah dokumentasi dan tutorial yang _gila_ yang dapat digunakan untuk mempelajari framework. Itu bisa bagus untuk mendalami detail halus atau buruk karena ada begitu banyak yang harus dibaca.
- Laravel memiliki sistem autentikasi bawaan yang dapat digunakan untuk mengamankan aplikasi Anda.
- Laravel memiliki podcast, konferensi, pertemuan, video, dan sumber daya lain yang dapat digunakan untuk mempelajari framework.
- Laravel ditujukan untuk pengembang berpengalaman yang ingin membangun aplikasi web perusahaan yang lengkap.

## Kekurangan dibandingkan Flight

- Laravel memiliki lebih banyak hal yang terjadi di balik layar dibandingkan Flight. Ini datang dengan biaya **dramatis** dalam hal
  performa. Lihat [benchmark TechEmpower](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3) 
  untuk informasi lebih lanjut.
- Flight ditujukan untuk pengembang yang ingin membangun aplikasi web ringan, cepat, dan mudah digunakan.
- Flight ditujukan untuk kesederhanaan dan kemudahan penggunaan.
- Salah satu fitur inti Flight adalah bahwa ia berusaha sebaik mungkin untuk mempertahankan kompatibilitas mundur. Laravel menyebabkan [banyak frustrasi](https://www.google.com/search?q=laravel+breaking+changes+major+version+complaints&sca_esv=6862a9c407df8d4e&sca_upv=1&ei=t72pZvDeI4ivptQP1qPMwQY&ved=0ahUKEwiwlurYuNCHAxWIl4kEHdYRM2gQ4dUDCBA&uact=5&oq=laravel+breaking+changes+major+version+complaints&gs_lp=Egxnd3Mtd2l6LXNlcnAiMWxhcmF2ZWwgYnJlYWtpbmcgY2hhbmdlcyBtYWpvciB2ZXJzaW9uIGNvbXBsYWludHMyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEdIjAJQAFgAcAF4AZABAJgBAKABAKoBALgBA8gBAJgCAaACB5gDAIgGAZAGCJIHATGgBwA&sclient=gws-wiz-serp) antara versi mayor.
- Flight ditujukan untuk pengembang yang baru memasuki dunia framework untuk pertama kalinya.
- Flight tidak memiliki dependensi, sedangkan [Laravel memiliki jumlah dependensi yang mengerikan](https://github.com/laravel/framework/blob/12.x/composer.json)
- Flight juga bisa melakukan aplikasi tingkat perusahaan, tetapi tidak memiliki kode boilerplate sebanyak Laravel.
  Ini juga akan membutuhkan lebih banyak disiplin dari pengembang untuk menjaga semuanya terorganisir dan terstruktur dengan baik.
- Flight memberikan pengembang lebih banyak kendali atas aplikasi, sedangkan Laravel memiliki banyak sihir di balik layar yang bisa menjengkelkan.