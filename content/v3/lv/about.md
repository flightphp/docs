# Kas ir Flight?

Flight ir ātra, vienkārša, paplašināma PHP ietvarstruktūra. Tā ir diezgan daudzfunkcionāla un var tikt izmantota, lai izveidotu jebkura veida tīmekļa lietojumprogrammas. Tā ir izstrādāta ar vienkāršību prātā un ir uzrakstīta tā, lai to būtu viegli saprast un lietot.

Flight ir lieliska iesācēju ietvarstruktūra tiem, kas ir jauni PHP un vēlas mācīties, kā izveidot tīmekļa lietojumprogrammas. Tā ir arī lieliska ietvarstruktūra pieredzējušiem izstrādātājiem, kuri vēlas vairāk kontroli pār savām tīmekļa lietojumprogrammām. Tā ir izstrādāta, lai viegli izveidotu RESTful API, vienkāršu tīmekļa lietojumprogrammu vai sarežģītu tīmekļa lietojumprogrammu.

## Ātra uzsākšana

```php
<?php

// ja instalēts ar composer
require 'vendor/autoload.php';
// vai ja instalēts manuāli ar zip failu
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">Vienkārši, vai ne?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Uzziniet vairāk par Flight dokumentācijā!</a>

    </div>
  </div>
</div>

### Skeleta/Boilerplate lietojumprogramma

Ir pieejama paraugu lietojumprogramma, kas var palīdzēt jums sākt darbu ar Flight ietvarstruktūru. Dodieties uz [flightphp/skeleton](https://github.com/flightphp/skeleton) instrukcijām, kā sākt! Jūs varat arī apmeklēt [examples](examples) lapu, lai iedvesmotos no dažām lietām, ko varat paveikt ar Flight.

# Kopiena

Mēs esam Matrix sarunā, sazinieties ar mums [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Ieguldījums

Ir divi veidi, kā jūs varat ieguldīt Flight: 

1. Jūs varat ieguldīt pamatīetvarā, apmeklējot [core repository](https://github.com/flightphp/core). 
1. Jūs varat ieguldīt dokumentācijā. Šī dokumentācijas vietne ir pieejama [Github](https://github.com/flightphp/docs). Ja pamanāt kļūdu vai vēlaties uzlabot kaut ko, droši labojiet un iesniedziet pieprasījumu par izmaiņām! Mēs cenšamies sekot līdzi lietām, bet atjauninājumi un valodas tulkojumi ir gaidīti.

# Prasības

Flight prasa PHP 7.4 vai jaunāku.

**Piezīme:** PHP 7.4 tiek atbalstīts, jo pašreizējā rakstīšanas brīdī (2024) PHP 7.4 ir noklusējuma versija daudziem LTS Linux izplatījumiem. Pārsniegšana uz PHP >8 varētu izraisīt daudz neērtību šiem lietotājiem. Ietvarstruktūra arī atbalsta PHP >8.

# Licences

Flight tiek izlaista saskaņā ar [MIT](https://github.com/flightphp/core/blob/master/LICENSE) licenci.