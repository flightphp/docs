# Kas ir Flight?

Flight ir ātrs, vienkāršs, paplašināms ietvars PHP valodai. Tas ir diezgan daudzpusīgs un var tikt izmantots jebkura veida tīmekļa lietotnes izstrādei. Tas izstrādāts, ņemot vērā vienkāršību, un uzrakstīts tādā veidā, kas ir viegli saprotams un lietojams.

Flight ir lielisks sākotnējais ietvars tiem, kas ir jauni PHP valodā un vēlas uzzināt, kā veidot tīmekļa lietotnes. Tas ir arī lielisks ietvars pieredzējušiem izstrādātājiem, kuri vēlas lielāku kontroli pār savām tīmekļa lietotnēm. Tas ir izstrādāts tā, lai viegli veidotu RESTful API, vienkāršu tīmekļa lietotni vai sarežģītu tīmekļa lietotni.

## Ātrā sākšana

```php
<?php

// ja ir instalēts ar Composer
require 'vendor/autoload.php';
// vai ja ir instalēts manuāli no zip faila
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'sveika pasaule!';
});

Flight::start();
```

Pietiekami vienkārši, vai ne? [Uzzini vairāk par Flight dokumentācijā!](learn)

### Skeleta/Vadlīnijas lietotne

Ir piemēra lietotne, kas var jums palīdzēt uzsākt darbu ar Flight ietvaru. Doties uz [flightphp/skeleton](https://github.com/flightphp/skeleton), lai iegūtu instrukcijas par to, kā sākt darbu! Jūs varat arī apmeklēt [piemērus](examples) lapu, lai iedvesmotu sevi ar dažiem no tā, ko varat darīt ar Flight.

# Kopiena

Mēs esam Matrix! Sarunājieties ar mums vietnē [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Contributing

Ir divi veidi, kā jūs varat piedalīties Flight projektam:

1. Jūs varat piedalīties pamat ietvara izstrādē, apmeklējot [pamata krātuvi](https://github.com/flightphp/core).
1. Jūs varat piedalīties dokumentācijā. Šī dokumentācijas vietne ir viesota vietnē [Github](https://github.com/flightphp/docs). Ja pamanāt kļūdu vai vēlaties uzlabot kaut ko labāk, droši labojiet to un iesniedziet "pull request"! Mēs cenšamies sekot līdzi visam, bet atjauninājumi un valodas tulkojumi ir laipni gaidīti.

# Prasības

Flight prasa PHP 7.4 vai jaunāku.

**Piezīme:** PHP 7.4 tiek atbalstīts, jo šobrīd (2024. gadā) PHP 7.4 ir noklusēta versija dažiem LTS Linux izplatījumiem. Pāreja uz PHP >8 radītu daudz problēmu šiem lietotājiem. Ietvars arī atbalsta PHP >8.

# Licences

Flight tiek izlaists saskaņā ar [MIT](https://github.com/flightphp/core/blob/master/LICENSE) licenci.