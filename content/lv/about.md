# Kas ir Flight?

Flight ir ātrs, vienkāršs, paplašināms ietvars PHP valodā. Tas ir diezgan daudzpusīgs un var tikt izmantots, lai izveidotu jebkura veida tīmekļa lietojumprogrammu. Tas ir izveidots, ņemot vērā vienkāršību, un ir rakstīts tā, lai būtu viegli saprotams un lietojams.

Flight ir lielisks ietvars iesācējiem tiem, kas nav iepazinušies ar PHP un vēlas uzzināt, kā veidot tīmekļa lietojumprogrammas. Tas ir arī lielisks ietvars pieredzējušiem izstrādātājiem, kuri vēlas ātri un viegli veidot tīmekļa lietojumprogrammas. Tas ir izstrādāts tā, lai viegli būtu iespējams izveidot RESTful API, vienkāršu tīmekļa lietojumprogrammu vai sarežģītu tīmekļa lietojumprogrammu.

```php
<?php

// ja ir uzstādīts ar komponistu
require 'vendor/autoload.php';
// vai arī ja ir uzstādīti manuāli, izmantojot zip failu
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'sveika, pasaule!';
});

Flight::start();
```

Pietiekami vienkārši, vai ne? [Uzziniet vairāk par Flight!](learn)

## Ātrā sākšana
Ir piemēra lietotne, kas var jums palīdzēt sākt darbu ar Flight ietvaru. Dodieties uz [flightphp/skeleton](https://github.com/flightphp/skeleton), lai iegūtu norādes par to, kā sākt darbu! Jūs arī varat apmeklēt [piemēri](examples) lapu, lai iedvesmotu idejas par to, kas viss ir iespējams ar Flight.

# Kopiena

Mēs esam pieejami Matrix! Sarunājieties ar mums šeit: [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Contributing

Ir divi veidi, kā jūs varat piedalīties Flight:

1. Jūs varat piedalīties pamat ietvarā, apmeklējot [core repository](https://github.com/flightphp/core).
1. Jūs varat piedalīties dokumentācijā. Šī dokumentācijas vietne ir uzglabāta [Github](https://github.com/flightphp/docs). Ja pamanāt kādu kļūdu vai vēlaties uzlabot kaut ko, jūs varat to izlabot un iesniegt Pieprasījumu izmaiņām! Mēs cenšamies uzturēt atjauninājumus, bet labprāt sagaidīsim jauninājumus un valodas tulkojumus.

# Prasības

Flight prasa PHP 7.4 vai vēl jaunāku versiju.

**Piezīme:** PHP 7.4 tiek atbalstīts, jo rakstīšanas (2024. gadā) brīdī PHP 7.4 ir pēc noklusējuma versija dažiem LTS Linux izplatījumiem. Piespiežot pāriet uz PHP >8, radītu daudz sarežģījumu šiem lietotājiem. Ietvars arī atbalsta PHP >8.

# Licence

Flight ir izlaists zem [MIT](https://github.com/flightphp/core/blob/master/LICENSE) licences.