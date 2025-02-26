# Kas ir Flight?

Flight ir ātrs, vienkāršs, paplašināms ietvars PHP. Tas ir diezgan daudzpusīgs un var tikt izmantots jebkāda veida tīmekļa lietotņu izveidei. Tas ir izstrādāts ar vienkāršību prātā un ir uzrakstīts tā, lai būtu viegli saprotams un lietojams.

Flight ir lielisks sākuma ietvars tiem, kuri ir jauni PHP un vēlas iemācīties, kā izveidot tīmekļa lietotnes. Tas ir arī lielisks ietvars pieredzējušiem izstrādātājiem, kuri vēlas lielāku kontroli pār savām tīmekļa lietotnēm. Tas ir izstrādāts, lai viegli izveidotu RESTful API, vienkāršu tīmekļa lietotni vai sarežģītu tīmekļa lietotni.

## Ātrā sākšana

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
      <span class="fligth-title-video">Vienkārši, vai ne?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Uzziniet vairāk par Flight dokumentācijā!</a>

    </div>
  </div>
</div>

### Skelets/Šablona lietotne

Pastāv piemēra lietotne, kas var palīdzēt jums sākt darbu ar Flight ietvaru. Dodieties uz [flightphp/skeleton](https://github.com/flightphp/skeleton) instrukcijām, kā sākt! Jūs varat arī apmeklēt [examples](examples) lapu, lai iegūtu iedvesmu par dažām lietām, ko varat darīt ar Flight.

# Kopiena

Mēs esam Matrix ātrajā saskarsmē pie [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Ieguldījumi

Ir divi veidi, kā jūs varat ieguldīt Flight:

1. Jūs varat ieguldīt pamatietvarā, apmeklējot [core repository](https://github.com/flightphp/core). 
1. Jūs varat ieguldīt dokumentācijā. Šī dokumentācijas vietne ir mitināta [Github](https://github.com/flightphp/docs). Ja pamanāt kļūdu vai vēlaties kaut ko uzlabot, jūtieties brīvi to labot un iesniegt pieprasījumu! Mēs cenšamies sekot līdzi lietām, taču atjauninājumi un valodas tulkojumi ir laipni gaidīti.

# Prasības

Flight prasības ir PHP 7.4 vai lielāka.

**Piezīme:** PHP 7.4 tiek atbalstīts, jo pašreizējā rakstīšanas laikā (2024) PHP 7.4 ir noklusējuma versija dažām LTS Linux sadales versijām. Pārvietošana uz PHP >8 izraisītu lielu neapmierinātību šiem lietotājiem. Ietvars arī atbalsta PHP >8.

# License

Flight tiek izlaists saskaņā ar [MIT](https://github.com/flightphp/core/blob/master/LICENSE) licenci.