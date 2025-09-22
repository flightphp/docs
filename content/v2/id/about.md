# Apa itu Flight?

Flight adalah framework PHP yang cepat, sederhana, dan dapat diperluas.  
Flight memungkinkan Anda untuk dengan cepat dan mudah membangun aplikasi web RESTful.

``` php
require 'flight/Flight.php';

Flight::route('/', function(){
  // menampilkan 'hello world!'
  echo 'hello world!';
});

// memulai aplikasi
Flight::start();
```

[Pelajari lebih lanjut](learn)

# Persyaratan

Flight memerlukan PHP 7.4 atau yang lebih tinggi.

# Lisensi

Flight dirilis di bawah lisensi [MIT](https://github.com/mikecao/flight/blob/master/LICENSE).

# Komunitas

Kami berada di Matrix! Obrolan dengan kami di [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Kontribusi

Situs web ini dihosting di [Github](https://github.com/mikecao/flightphp.com).  
Pembaruan dan terjemahan bahasa sangat diterima.
