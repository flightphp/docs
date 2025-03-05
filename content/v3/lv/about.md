# Kas ir Flight?

Flight ir ātrs, vienkāršs, paplašināms ietvars PHP. Tas ir diezgan daudzfunkcionāls un var tikt izmantots jebkāda veida tīmekļa lietojumprogrammu veidošanai. Tas ir izstrādāts ar vienkāršību prātā un ir uzrakstīts tā, lai būtu viegli saprotams un lietojams.

Flight ir lielisks iesācēju ietvars tiem, kuri ir jauni PHP un vēlas iemācīties, kā veidot tīmekļa lietojumprogrammas. Tas ir arī lielisks ietvars pieredzējušiem izstrādātājiem, kuri vēlas vairāk kontroli pār savām tīmekļa lietojumprogrammām. Tas ir izstrādāts, lai viegli veidotu RESTful API, vienkāršu tīmekļa lietojumprogrammu vai sarežģītu tīmekļa lietojumprogrammu.

## Ātrais sākums

Vispirms instalējiet to ar Composer

```bash
composer require flightphp/core
```

vai varat lejupielādēt repo zip failu [šeit](https://github.com/flightphp/core). Tad jums būtu jābūt pamata `index.php` failam, piemēram, sekojošam:

```php
<?php

// ja instalēts ar composer
require 'vendor/autoload.php';
// vai ja instalēts manuāli, izmantojot zip failu
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'sveika pasaule!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

Tas arī viss! Jums ir pamata Flight lietojumprogramma. Tagad varat palaist šo failu ar `php -S localhost:8000` un apmeklēt `http://localhost:8000` savā pārlūkprogrammā, lai redzētu rezultātu.

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

## Vai tas ir ātrs?

Jā! Flight ir ātrs. Tas ir viens no ātrākajiem pieejamajiem PHP ietvariem. Jūs varat redzēt visus salīdzinājumus vietnē [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)

Skatiet salīdzinājumu zemāk ar dažiem citiem populāriem PHP ietvariem.

| Ietvars   | Plaintext Reqs/sec | JSON Reqs/sec |
| --------- | ------------ | ------------ |
| Flight    | 190,421      | 182,491 |
| Yii       | 145,749      | 131,434 |
| Fat-Free  | 139,238      | 133,952 |
| Slim      | 89,588       | 87,348  |
| Phalcon   | 95,911       | 87,675  |
| Symfony   | 65,053       | 63,237  |
| Lumen     | 40,572       | 39,700  |
| Laravel   | 26,657       | 26,901  |
| CodeIgniter | 20,628    | 19,901  |

## Skeleta/Paraugprogrammu

Ir piemēra lietojumprogramma, kas var palīdzēt jums sākt darbu ar Flight ietvaru. Dodieties uz [flightphp/skeleton](https://github.com/flightphp/skeleton), lai iegūtu instrukcijas, kā sākt! Jūs varat arī apmeklēt [paraugus](examples) lapu, lai gūtu iedvesmu par dažām lietām, ko varat darīt ar Flight.

# Kopiena

Mēs esam Matrix čatā

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

Un Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Ieguldījumi

Ir divi veidi, kā jūs varat ieguldīt Flight:

1. Jūs varat ieguldīt pamatā esošajā ietvarā, apmeklējot [pamatkrātuvi](https://github.com/flightphp/core).
1. Jūs varat ieguldīt dokumentācijā. Šī dokumentācijas vietne tiek hostēta vietnē [Github](https://github.com/flightphp/docs). Ja pamanāt kļūdu vai vēlaties kaut ko uzlabot, laipni aicināti to izlabot un iesniegt izmaiņu pieprasījumu! Mēs cenšamies sekot notikumiem, bet jauninājumi un tulkojumi ir laipni gaidīti.

# Prasības

Flight prasa PHP 7.4 vai jaunāku.

**Piezīme:** PHP 7.4 tiek atbalstīts, jo pašreizējā rakstīšanas laikā (2024) PHP 7.4 ir noklusējuma versija dažām LTS Linux izplatīšanām. Pāriešana uz PHP >8 radītu daudz neērtību šiem lietotājiem. Ietvars arī atbalsta PHP >8.

# Licences

Flight tiek izlaists saskaņā ar [MIT](https://github.com/flightphp/core/blob/master/LICENSE) licenci.