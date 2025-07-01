# Apa itu Flight?

Flight adalah kerangka kerja PHP yang cepat, sederhana, dan dapat diperluas—dibuat untuk pengembang yang ingin menyelesaikan pekerjaan dengan cepat, tanpa keribetan. Baik Anda membangun aplikasi web klasik, API yang sangat cepat, atau bereksperimen dengan alat-alat berbasis AI terbaru, jejak kecil Flight dan desainnya yang langsung membuatnya cocok sempurna.

## Mengapa Memilih Flight?

- **Ramah Pemula:** Flight adalah titik awal yang bagus untuk pengembang PHP baru. Struktur yang jelas dan sintaks sederhananya membantu Anda belajar pengembangan web tanpa tersesat dalam boilerplate.
- **Dicintai oleh Profesional:** Pengembang berpengalaman mencintai Flight karena fleksibilitas dan kendalinya. Anda dapat mengembangkannya dari prototipe kecil hingga aplikasi lengkap tanpa harus berganti kerangka kerja.
- **Ramah AI:** Overhead minimal Flight dan arsitektur bersihnya membuatnya ideal untuk mengintegrasikan alat dan API AI. Baik Anda membangun chatbot pintar, dasbor yang didorong AI, atau hanya ingin bereksperimen, Flight tidak menghalangi sehingga Anda dapat fokus pada hal yang penting. [Pelajari lebih lanjut tentang menggunakan AI dengan Flight](/learn/ai)

## Mulai Cepat

Pertama, instal dengan Composer:

```bash
composer require flightphp/core
```

Atau Anda dapat mengunduh zip dari repo [di sini](https://github.com/flightphp/core). Kemudian, Anda akan memiliki file `index.php` dasar seperti berikut:

```php
<?php

// jika diinstal dengan composer
require 'vendor/autoload.php';
// atau jika diinstal secara manual dengan file zip
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

Itu saja! Anda memiliki aplikasi Flight dasar. Anda sekarang dapat menjalankan file ini dengan `php -S localhost:8000` dan kunjungi `http://localhost:8000` di browser Anda untuk melihat output.

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">Cukup sederhana, bukan?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Pelajari lebih lanjut tentang Flight di dokumentasi!</a>
      <br>
      <button href="/learn/ai" class="btn btn-primary mt-3">Temukan bagaimana Flight membuat AI mudah</button>
    </div>
  </div>
</div>

## Apakah itu cepat?

Tentu saja! Flight adalah salah satu kerangka kerja PHP tercepat di luar sana. Inti ringannya berarti overhead lebih sedikit dan kecepatan lebih banyak—sempurna untuk aplikasi tradisional dan proyek berbasis AI modern. Anda dapat melihat semua benchmark di [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)

Lihat benchmark di bawah dengan beberapa kerangka kerja PHP populer lainnya.

| Kerangka Kerja | Permintaan Plaintext per detik | Permintaan JSON per detik |
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

## Aplikasi Kerangka/Boilerplate

Ada contoh aplikasi untuk membantu Anda memulai dengan Flight. Periksa [flightphp/skeleton](https://github.com/flightphp/skeleton) untuk proyek siap-pakai, atau kunjungi halaman [contoh](examples) untuk inspirasi. Ingin melihat bagaimana AI cocok? [Jelajahi contoh berbasis AI](/learn/ai).

# Komunitas

Kami ada di Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

Dan Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Berkontribusi

Ada dua cara Anda dapat berkontribusi ke Flight:

1. Berkontribusi ke kerangka kerja inti dengan mengunjungi [repositori inti](https://github.com/flightphp/core).
2. Bantu membuat dokumen lebih baik! Situs dokumentasi ini dihosting di [Github](https://github.com/flightphp/docs). Jika Anda menemukan kesalahan atau ingin meningkatkan sesuatu, silakan submit pull request. Kami menyukai pembaruan dan ide baru—terutama seputar AI dan teknologi baru!

# Persyaratan

Flight memerlukan PHP 7.4 atau lebih baru.

**Catatan:** PHP 7.4 didukung karena pada saat penulisan (2024) PHP 7.4 adalah versi default untuk beberapa distribusi Linux LTS. Memaksa pindah ke PHP >8 akan menimbulkan masalah bagi pengguna tersebut. Kerangka kerja juga mendukung PHP >8.

# Lisensi

Flight dirilis di bawah lisensi [MIT](https://github.com/flightphp/core/blob/master/LICENSE).