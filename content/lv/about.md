# Kas ir lidojums?

Lidojums ir ātra, vienkārša, paplašināma PHP ietvars.
Lidojums ļauj jums ātri un viegli izveidot RESTful tīmekļa lietojumprogrammas.

```php
<?php

// ja ir instalēts ar komponistu
require 'vendor/autoload.php';
// vai, ja instalēts manuāli ar zip failu
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'sveika, pasaule!';
});

Flight::start();
```

Vai ne pietiekami vienkārši? [Uzziniet vairāk par Lidojumu!](learn)

## Skeleta lietotne
Ir piemēra lietotne, kas var jums palīdzēt sākt darbu ar Lidojumu ietvaru. Dodieties uz [flightphp/skeleton](https://github.com/flightphp/skeleton), lai iegūtu instrukcijas par to, kā sākt!

# Kopiena

Mēs esam Matrix! Sarunājieties ar mums vietnē [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Ieguldīt

Šī tīmekļa vietne tiek uzturēta vietnē [Github](https://github.com/flightphp/docs). Ja pamanāt kļūdu, droši labojiet to un iesniedziet pieprasījumu izvilkt!
Mēs cenšamies sekot līdzi lietām, bet atjauninājumi un valodas tulkojumi ir laipni gaidīti.

# Prasības

Lidojums prasa PHP 7.4 vai jaunāku.

**Piezīme:** PHP 7.4 ir atbalstīts, jo rakstīšanas brīdī (2024. gadā) PHP 7.4 ir noklusējuma versija dažiem ilgtspejas Linux izplatījumiem. Pāreja uz PHP >8 rada lielu sāpes tiem lietotājiem. Ietvars arī atbalsta PHP >8.

# Licence

Lidojums ir izdots saskaņā ar [MIT](https://github.com/flightphp/core/blob/master/LICENSE) licenci.