# Apa itu Flight?

Flight adalah framework PHP yang cepat, sederhana, dan dapat diperluas. Ini cukup serbaguna dan dapat digunakan untuk membangun segala jenis aplikasi web. Ini dirancang dengan kesederhanaan dalam pikiran dan ditulis dengan cara yang mudah dipahami dan digunakan.

Flight adalah framework yang hebat untuk pemula bagi mereka yang baru mengenal PHP dan ingin belajar cara membangun aplikasi web. Ini juga merupakan framework yang hebat untuk pengembang berpengalaman yang ingin lebih mengontrol aplikasi web mereka. Ini dirancang untuk dengan mudah membangun API RESTful, aplikasi web sederhana, atau aplikasi web kompleks.

## Memulai dengan Cepat

```php
<?php

// jika diinstal menggunakan composer
require 'vendor/autoload.php';
// atau jika diinstal secara manual dengan file zip
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'halo dunia!';
});

Flight::route('/json', function() {
  Flight::json(['halo' => 'dunia']);
});

Flight::start();
```

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="Pemutar video YouTube" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">Cukup sederhana, kan?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Pelajari lebih lanjut tentang Flight dalam dokumentasi!</a>

    </div>
  </div>
</div>

### Aplikasi Skeleton/Boilerplate

Ada sebuah contoh aplikasi yang dapat membantu Anda memulai dengan Framework Flight. Kunjungi [flightphp/skeleton](https://github.com/flightphp/skeleton) untuk instruksi tentang cara memulai! Anda juga dapat mengunjungi halaman [contoh](examples) untuk inspirasi tentang beberapa hal yang dapat Anda lakukan dengan Flight.

# Komunitas

Kami ada di Matrix Chat dengan kami di [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Kontribusi

Ada dua cara Anda dapat berkontribusi pada Flight: 

1. Anda dapat berkontribusi pada framework inti dengan mengunjungi [repository inti](https://github.com/flightphp/core). 
1. Anda dapat berkontribusi pada dokumentasi. Situs web dokumentasi ini dihosting di [Github](https://github.com/flightphp/docs). Jika Anda melihat kesalahan atau ingin mengembangkan sesuatu yang lebih baik, silakan perbaiki dan kirimkan permintaan tarik! Kami berusaha untuk mengikuti perkembangan, tetapi pembaruan dan terjemahan bahasa sangat diterima.

# Persyaratan

Flight membutuhkan PHP 7.4 atau lebih tinggi.

**Catatan:** PHP 7.4 didukung karena pada saat penulisan saat ini (2024) PHP 7.4 adalah versi default untuk beberapa distribusi Linux LTS. Memaksa berpindah ke PHP >8 akan menyebabkan banyak masalah bagi pengguna tersebut. Framework ini juga mendukung PHP >8.

# Lisensi

Flight dirilis di bawah lisensi [MIT](https://github.com/flightphp/core/blob/master/LICENSE).