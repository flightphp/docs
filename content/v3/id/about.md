# Flight PHP Framework

Flight adalah framework PHP yang cepat, sederhana, dan dapat diperluas—dibangun untuk pengembang yang ingin menyelesaikan pekerjaan dengan cepat, tanpa kerumitan. Baik Anda membangun aplikasi web klasik, API yang sangat cepat, atau bereksperimen dengan alat berbasis AI terbaru, jejak rendah dan desain sederhana Flight membuatnya cocok sempurna. Flight dirancang untuk tetap ramping, tetapi juga dapat menangani persyaratan arsitektur enterprise.

## Mengapa Memilih Flight?

- **Ramah Pemula:** Flight adalah titik awal yang bagus untuk pengembang PHP baru. Strukturnya yang jelas dan sintaks sederhana membantu Anda belajar pengembangan web tanpa tersesat dalam kode boilerplate.
- **Disukai oleh Profesional:** Pengembang berpengalaman menyukai Flight karena fleksibilitas dan kendalinya. Anda dapat menskalakan dari prototipe kecil hingga aplikasi lengkap tanpa harus mengganti framework.
- **Kompatibel ke Belakang:** Kami menghargai waktu Anda. Flight v3 adalah peningkatan dari v2, mempertahankan hampir seluruh API yang sama. Kami percaya pada evolusi, bukan revolusi—tidak ada lagi "merusak dunia" setiap kali versi utama dirilis.
- **Tanpa Dependensi:** Inti Flight sepenuhnya bebas dependensi—tidak ada polyfills, tidak ada paket eksternal, bahkan tidak ada antarmuka PSR. Ini berarti lebih sedikit vektor serangan, jejak yang lebih kecil, dan tidak ada perubahan pemecah yang mengejutkan dari dependensi hulu. Plugin opsional mungkin menyertakan dependensi, tetapi inti akan selalu tetap ramping dan aman.
- **Berfokus pada AI:** Overhead minimal dan arsitektur bersih Flight membuatnya ideal untuk mengintegrasikan alat dan API AI. Baik Anda membangun chatbot pintar, dashboard berbasis AI, atau hanya ingin bereksperimen, Flight tidak menghalangi sehingga Anda dapat fokus pada hal yang penting. Aplikasi [skeleton](https://github.com/flightphp/skeleton) dilengkapi dengan file instruksi pra-bangun untuk asisten pengkodean AI utama langsung dari kotak! [Pelajari lebih lanjut tentang menggunakan AI dengan Flight](/learn/ai)

## Video Overview

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 fs-5 text-center mt-5 pt-5">
      <span class="flight-title-video">Cukup sederhana, bukan?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Pelajari lebih lanjut</a> tentang Flight di dokumentasi!
    </div>
  </div>
</div>

## Quick Start

Untuk instalasi cepat tanpa tambahan, instal dengan Composer:

```bash
composer require flightphp/core
```

Atau Anda dapat mengunduh zip dari repo [di sini](https://github.com/flightphp/core). Kemudian Anda akan memiliki file `index.php` dasar seperti berikut:

```php
<?php

// if installed with composer
require 'vendor/autoload.php';
// or if installed manually by zip file
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::route('/json', function() {
  Flight::json([
	'hello' => 'world'
  ]);
});

Flight::start();
```

Itu saja! Anda memiliki aplikasi Flight dasar. Anda sekarang dapat menjalankan file ini dengan `php -S localhost:8000` dan kunjungi `http://localhost:8000` di browser Anda untuk melihat outputnya.

## Skeleton/Boilerplate App

Ada aplikasi contoh untuk membantu Anda memulai proyek dengan Flight. Ini memiliki tata letak terstruktur, konfigurasi dasar yang sudah disetel, dan menangani skrip composer langsung dari gerbang! Lihat [flightphp/skeleton](https://github.com/flightphp/skeleton) untuk proyek siap pakai, atau kunjungi halaman [examples](examples) untuk inspirasi. Ingin melihat bagaimana AI cocok? [Jelajahi contoh berbasis AI](/learn/ai).

## Installing the Skeleton App

Cukup mudah!

```bash
# Create the new project
composer create-project flightphp/skeleton my-project/
# Enter your new project directory
cd my-project/
# Bring up the local dev-server to get started right away!
composer start
```

Ini akan membuat struktur proyek, menyiapkan file yang Anda butuhkan, dan Anda siap untuk memulai!

## High Performance

Flight adalah salah satu framework PHP tercepat di luar sana. Inti ringannya berarti overhead lebih sedikit dan kecepatan lebih tinggi—sempurna untuk aplikasi tradisional dan proyek berbasis AI modern. Anda dapat melihat semua benchmark di [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)

Lihat benchmark di bawah ini dengan beberapa framework PHP populer lainnya.

| Framework | Plaintext Reqs/sec | JSON Reqs/sec |
| --------- | ------------ | ------------ |
| Flight      | 190,421    | 182,491 |
| Yii         | 145,749    | 131,434 |
| Fat-Free    | 139,238    | 133,952 |
| Slim        | 89,588     | 87,348  |
| Phalcon     | 95,911     | 87,675  |
| Symfony     | 65,053     | 63,237  |
| Lumen       | 40,572     | 39,700  |
| Laravel     | 26,657     | 26,901  |
| CodeIgniter | 20,628     | 19,901  |


## Flight and AI

Penasaran bagaimana ia menangani AI? [Temukan](/learn/ai) bagaimana Flight membuat bekerja dengan LLM pengkodean favorit Anda menjadi mudah!

## Stability and Backwards Compatibility

Kami menghargai waktu Anda. Kami semua pernah melihat framework yang sepenuhnya menciptakan ulang diri mereka setiap beberapa tahun, meninggalkan pengembang dengan kode rusak dan migrasi mahal. Flight berbeda. Flight v3 dirancang sebagai peningkatan dari v2, yang berarti API yang Anda kenal dan sukai tidak dihilangkan. Bahkan, sebagian besar proyek v2 akan berfungsi tanpa perubahan apa pun di v3. 

Kami berkomitmen untuk menjaga Flight tetap stabil sehingga Anda dapat fokus membangun aplikasi Anda, bukan memperbaiki framework Anda.

# Community

Kami ada di Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

Dan Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Contributing

Ada dua cara Anda dapat berkontribusi ke Flight:

1. Berkontribusi ke framework inti dengan mengunjungi [core repository](https://github.com/flightphp/core).
2. Bantu buat dokumentasi lebih baik! Situs web dokumentasi ini dihosting di [Github](https://github.com/flightphp/docs). Jika Anda menemukan kesalahan atau ingin meningkatkan sesuatu, jangan ragu untuk mengirimkan pull request. Kami menyukai pembaruan dan ide baru—terutama seputar AI dan teknologi baru!

# Requirements

Flight memerlukan PHP 7.4 atau lebih tinggi.

**Catatan:** PHP 7.4 didukung karena pada saat penulisan ini (2024) PHP 7.4 adalah versi default untuk beberapa distribusi Linux LTS. Memaksa perpindahan ke PHP >8 akan menyebabkan banyak masalah bagi pengguna tersebut. Framework juga mendukung PHP >8.

# License

Flight dirilis di bawah lisensi [MIT](https://github.com/flightphp/core/blob/master/LICENSE).