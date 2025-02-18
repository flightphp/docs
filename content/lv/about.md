# Kas ir Flight?

Flight ir ātrs, vienkāršs, paplašināms ietvars PHP. Tas ir diezgan elastīgs un var tikt izmantots, lai izveidotu jebkura veida tīmekļa lietojumprogrammas. Tas ir veidots ar vienkāršību prātā un ir rakstīts tā, lai būtu viegli saprotams un lietojams.

Flight ir lielisks uzsācēju ietvars tiem, kas ir jauni PHP un vēlas mācīties, kā izveidot tīmekļa lietojumprogrammas. Tas ir arī lielisks ietvars pieredzējušiem izstrādātājiem, kuri vēlas vairāk kontroli pār savām tīmekļa lietojumprogrammām. Tas ir inženierēts, lai viegli izveidotu RESTful API, vienkāršu tīmekļa lietojumprogrammu vai sarežģītu tīmekļa lietojumprogrammu.

## Ātra sākšana

```php
<?php

// ja instalēts ar composer
require 'vendor/autoload.php';
// vai ja instalēts manuāli, izmantojot zip failu
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
      <span class="fligth-title-video">Vienkārši pietiekami, vai ne?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Uzziniet vairāk par Flight dokumentācijā!</a>

    </div>
  </div>
</div>

### Skeleta/Boilerplate lietojumprogramma

Ir piemēra lietojumprogramma, kas var palīdzēt jums uzsākt darbu ar Flight ietvaru. Dodieties uz [flightphp/skeleton](https://github.com/flightphp/skeleton) instrukcijām, kā sākt! Jūs varat arī apmeklēt [examples](examples) lapu iedvesmai par dažām lietām, ko varat darīt ar Flight.

# Kopiena

Mēs esam Matrix tērzēšanā. Sarunājieties ar mums [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Ieguldījumi

Ir divi veidi, kā jūs varat ieguldīt Flight: 

1. Jūs varat ieguldīt kodola ietvarā, apmeklējot [core repository](https://github.com/flightphp/core). 
1. Jūs varat ieguldīt dokumentācijā. Šī dokumentācijas vietne tiek mitināta [Github](https://github.com/flightphp/docs). Ja pamanāt kļūdu vai vēlaties uzlabot kaut ko, droši labojiet to un iesniedziet pieprasījumu par izmaiņām! Mēs cenšamies sekot visam, bet atjauninājumi un tulkojumi ir laipni gaidīti.

# Prasības

Flight prasa PHP 7.4 vai jaunāku.

**Piezīme:** PHP 7.4 tiek atbalstīts, jo rakstīšanas laikā (2024) PHP 7.4 ir noklusējuma versija dažām LTS Linux distribūcijām. Pārvietošana uz PHP >8 radītu daudzas neērtības šiem lietotājiem. Ietvars arī atbalsta PHP >8.

# Licences

Flight ir izlaists ar [MIT](https://github.com/flightphp/core/blob/master/LICENSE) licenci.