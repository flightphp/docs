# Kas ir Flight?

Flight ir ātrs, vienkāršs, paplašināms PHP programmēšanas pamatsistēma. Tā ir diezgan daudzpusīga un var tikt izmantota jebkura veida tīmekļa lietotņu izstrādei. Tā ir izstrādāta, ņemot vērā vienkāršību, un ir rakstīta tā, lai būtu viegli saprotama un izmantojama.

Flight ir lieliska iesācējiem domāta pamatsistēma tiem, kuri ir jauni PHP un vēlas uzzināt, kā veidot tīmekļa lietotnes. Tāpat tā ir lieliska sistēma pieredzējušiem izstrādātājiem, kuri vēlas ātri un viegli veidot tīmekļa lietotnes. Tā ir izstrādāta tā, lai ļautu viegli izveidot RESTful API, vienkāršu tīmekļa lietotni vai sarežģītu tīmekļa lietotni.

```php
<?php

// ja instalēts ar komponistu
require 'vendor/autoload.php';
// vai ja instalēts manuāli ar zip failu
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'sveika pasaule!';
});

Flight::start();
```

Diezgan vienkārši, vai ne? [Uzzini vairāk par Flight!](learn)

## Ātrs sākums
Ir pieejama piemēra lietotne, kas var palīdzēt sākt strādāt ar Flight programmēšanas pamatsistēmu. Dodieties uz [flightphp/skeleton](https://github.com/flightphp/skeleton), lai iegūtu instrukcijas, kā sākt! Jūs varat arī apmeklēt [piemērus](examples) lapu, lai iedvesmotu sevi ar dažādām iespējām Flight izmantošanai.

# Kopiena

Mēs esam Matrix tīmeklī! Sarunājieties ar mums vietnē [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Ieguldīt

Ir divi veidi, kā jūs varat ieguldīt Flight programmēšanas pamatsistēmā:

1. Jūs varat ieguldīt pamatsistēmas kodā, apmeklējot [pamata repozitoriju](https://github.com/flightphp/core).
1. Jūs varat ieguldīt dokumentācijā. Šīs dokumentācijas vietne tiek uzturēta [Github](https://github.com/flightphp/docs). Ja Jūs pamanāt kļūdu vai vēlaties uzlabot kādu materiālu, jūs varat labot to un iesniegt pieprasījumu pull! Mēs cenšamies turēties atjaunināti, bet atjauninājumi un valodas tulkojumi ir laipni gaidīti.

# Prasības

Flight prasa PHP 7.4 vai jaunāku.

**Piezīme:** PHP 7.4 tiek atbalstīts, jo raksta laikā (2024. gadā) PHP 7.4 ir noklusējuma versija dažām LTS Linux izplatījumiem. Pāreja uz PHP >8 radītu daudz problēmu šiem lietotājiem. Pamatsistēma arī atbalsta PHP >8.

# Licences līgums

Flight ir izlaidusi [MIT](https://github.com/flightphp/core/blob/master/LICENSE) licences līgumu.