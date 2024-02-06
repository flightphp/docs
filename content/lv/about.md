# Ko ir Flight?

Flight ir ātrs, vienkāršs, paplašināms PHP ietvars. Tas ir diezgan universāls un var tikt izmantots, lai veidotu jebkura veida tīmekļa lietojumprogrammas. Tas ir izstrādāts, ņemot vērā vienkāršību un ir rakstīts tā, lai būtu viegli saprotams un pielietojams.

Flight ir lielisks sākotnējais ietvars tiem, kuri tikko sāk iepazīties ar PHP un vēlas uzzināt, kā veidot tīmekļa lietojumprogrammas. Tas ir arī lielisks ietvars pieredzējušiem izstrādātājiem, kuri vēlas ātri un viegli izveidot tīmekļa lietojumprogrammas. Tas ir izstrādāts tā, lai viegli izveidotu RESTful API, vienkāršu tīmekļa lietojumprogrammu vai sarežģītu tīmekļa lietojumprogrammu.

```php
<?php

// ja instalēts ar Composer
require 'vendor/autoload.php';
// vai ja instalēts manuāli ar ZIP failu
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'sveika, pasaule!';
});

Flight::start();
```

Pietiekami vienkārši, vai ne? [Uzzini vairāk par Flight!](learn)

## Ātrā sākšana
Ir piemēra lietotne, kas var palīdzēt jums sākt darbu ar Flight ietvaru. Dodieties uz [flightphp/skeleton](https://github.com/flightphp/skeleton), lai iegūtu instrukcijas par to, kā sākt! Jūs arī varat apmeklēt [piemērus](examples) lapu, lai iedvesmotu sevi dažādos veidos, kādus varat darīt ar Flight.

# Kopiena

Mēs esam Matrix! Sarunājieties ar mums vietnē [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Līdzdarbība

Ir divi veidi, kā jūs varat veikt ieguldījumu Flight: 

1. Jūs varat veikt ieguldījumu galvenajā ietvarā, apmeklējot [galveno repozitoriju](https://github.com/flightphp/core). 
1. Jūs varat veikt ieguldījumu dokumentācijā. Šī dokumentācijas vietne tiek uzturēta [Github](https://github.com/flightphp/docs). Ja pamanāt kļūdu vai vēlaties uzlabot kaut ko labāk, droši labojiet to un iesniedziet iesniegumu ar izmaiņām! Mēģinām uzturēt visu aktuālu, bet atjauninājumi un valodas tulkojumi ir laipni gaidīti.

# Prasības

Flight prasa PHP 7.4 vai jaunāku.

**Piezīme:** PHP 7.4 tiek atbalstīts, jo rakstīšanas brīdī (2024. gadā) PHP 7.4 ir noklusējuma versija dažiem ilgtermiņa atbalstam paredzētiem Linux izplatījumiem. Pāreja uz PHP >8 izraisītu lielas problēmas šiem lietotājiem. Ietvars arī atbalsta PHP >8.

# Licences līgums

Flight ir izdots, izmantojot [MIT](https://github.com/flightphp/core/blob/master/LICENSE) licenci.