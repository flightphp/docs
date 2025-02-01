# Apa itu Flight?

Flight adalah framework PHP yang cepat, sederhana, dan dapat diperluas. Ini cukup serbaguna dan dapat digunakan untuk membangun jenis aplikasi web apa pun. Ia dibangun dengan kesederhanaan dalam pikiran dan ditulis dengan cara yang mudah dipahami dan digunakan.

Flight adalah framework yang hebat untuk pemula yang baru mengenal PHP dan ingin belajar cara membangun aplikasi web. Ini juga merupakan framework yang hebat untuk pengembang berpengalaman yang ingin memiliki lebih banyak kontrol atas aplikasi web mereka. Ini dirancang untuk dengan mudah membangun API RESTful, aplikasi web sederhana, atau aplikasi web yang kompleks.

## Mulai Cepat

```php
<?php

// jika dipasang dengan composer
require 'vendor/autoload.php';
// atau jika dipasang secara manual dengan file zip
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

Ada aplikasi contoh yang dapat membantu Anda memulai dengan Flight Framework. Kunjungi [flightphp/skeleton](https://github.com/flightphp/skeleton) untuk petunjuk tentang cara memulai! Anda juga dapat mengunjungi halaman [contoh](examples) untuk inspirasi tentang beberapa hal yang dapat Anda lakukan dengan Flight.

# Komunitas

Kami ada di Matrix Chat bersama kami di [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Kontribusi

Ada dua cara Anda dapat berkontribusi pada Flight:

1. Anda dapat berkontribusi pada framework inti dengan mengunjungi [repositori inti](https://github.com/flightphp/core).
1. Anda dapat berkontribusi pada dokumentasi. Situs web dokumentasi ini dihosting di [Github](https://github.com/flightphp/docs). Jika Anda melihat kesalahan atau ingin mengembangkan sesuatu yang lebih baik, silakan untuk memperbaikinya dan kirimkan permintaan tarik! Kami berusaha untuk terus mengikuti hal-hal tersebut, tetapi pembaruan dan terjemahan bahasa sangat diterima.

# Persyaratan

Flight memerlukan PHP 7.4 atau yang lebih besar.

**Catatan:** PHP 7.4 didukung karena pada saat penulisan ini (2024) PHP 7.4 adalah versi default untuk beberapa distribusi Linux LTS. Memaksa migrasi ke PHP >8 dapat menyebabkan banyak masalah bagi pengguna tersebut. Framework ini juga mendukung PHP >8.

# Lisensi

Flight dirilis di bawah lisensi [MIT](https://github.com/flightphp/core/blob/master/LICENSE).