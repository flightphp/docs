# Apa itu Flight?

Flight adalah framework PHP yang cepat, sederhana, dan dapat diperluas. Ini cukup serbaguna dan dapat digunakan untuk membangun berbagai jenis aplikasi web. Dibangun dengan pemikiran kesederhanaan dan ditulis dengan cara yang mudah dipahami dan digunakan.

Flight adalah framework yang hebat untuk pemula yang baru mengenal PHP dan ingin belajar bagaimana membangun aplikasi web. Ini juga merupakan framework yang hebat untuk pengembang berpengalaman yang ingin lebih mengontrol aplikasi web mereka. Ini dirancang untuk dengan mudah membangun RESTful API, aplikasi web sederhana, atau aplikasi web yang kompleks.

## Mulai Cepat

Pertama, instal dengan Composer

```bash
composer require flightphp/core
```

atau Anda bisa mengunduh zip dari repositori [di sini](https://github.com/flightphp/core). Kemudian Anda akan memiliki file dasar `index.php` seperti berikut:

```php
<?php

// jika diinstal dengan composer
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

Itu saja! Anda memiliki aplikasi dasar Flight. Anda sekarang dapat menjalankan file ini dengan `php -S localhost:8000` dan kunjungi `http://localhost:8000` di browser Anda untuk melihat output.

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="Pemutar video YouTube" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">Cukup sederhana, bukan?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Pelajari lebih lanjut tentang Flight dalam dokumentasi!</a>

    </div>
  </div>
</div>

## Apakah ini cepat?

Ya! Flight cepat. Ini adalah salah satu framework PHP tercepat yang tersedia. Anda dapat melihat semua tolok ukur di [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)

Lihat tolok ukur di bawah ini dengan beberapa framework PHP populer lainnya.

| Framework | Permintaan Teks Biasa/detik | Permintaan JSON/detik |
| --------- | ------------ | ------------ |
| Flight      | 190.421    | 182.491 |
| Yii         | 145.749    | 131.434 |
| Fat-Free    | 139.238	   | 133.952 |
| Slim        | 89.588     | 87.348  |
| Phalcon     | 95.911     | 87.675  |
| Symfony     | 65.053     | 63.237  |
| Lumen	      | 40.572     | 39.700  |
| Laravel     | 26.657     | 26.901  |
| CodeIgniter | 20.628     | 19.901  |

## Aplikasi Skeleton/Boilerplate

Ada aplikasi contoh yang dapat membantu Anda memulai dengan Framework Flight. Kunjungi [flightphp/skeleton](https://github.com/flightphp/skeleton) untuk instruksi tentang cara memulai! Anda juga dapat mengunjungi halaman [contoh](examples) untuk inspirasi tentang beberapa hal yang dapat Anda lakukan dengan Flight.

# Komunitas

Kami ada di Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

Dan Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Kontribusi

Ada dua cara Anda dapat berkontribusi pada Flight: 

1. Anda dapat berkontribusi pada framework inti dengan mengunjungi [repositori inti](https://github.com/flightphp/core). 
1. Anda dapat berkontribusi pada dokumentasi. Situs web dokumentasi ini dihosting di [Github](https://github.com/flightphp/docs). Jika Anda menemukan kesalahan atau ingin memperbaiki sesuatu menjadi lebih baik, silakan perbaiki dan kirim permintaan tarik! Kami berusaha untuk tetap mengikuti hal-hal, tetapi pembaruan dan terjemahan bahasa sangat diterima.

# Persyaratan

Flight memerlukan PHP 7.4 atau lebih tinggi.

**Catatan:** PHP 7.4 didukung karena pada saat penulisan ini (2024) PHP 7.4 adalah versi default untuk beberapa distribusi Linux LTS. Memaksa pindah ke PHP >8 akan menyebabkan banyak masalah bagi pengguna tersebut. Framework ini juga mendukung PHP >8.

# Lisensi

Flight dirilis di bawah lisensi [MIT](https://github.com/flightphp/core/blob/master/LICENSE).