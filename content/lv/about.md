# Kas ir Flight?

Flight ir ātra, vienkārša, paplašināmais ietvars PHP programmēšanas valodai. Tas ir diezgan daudzpusīgs un var tikt izmantots, lai izveidotu jebkura veida tīmekļa lietojumprogrammu. Tas ir izveidots, ņemot vērā vienkāršību, un uzrakstīts tā, lai būtu viegli saprotams un izmantojams.

Flight ir lielisks ietvars iesācējiem tiem, kuri ir jauni PHP valodā un vēlas uzzināt, kā veidot tīmekļa lietojumprogrammas. Tas ir arī lielisks ietvars pieredzējušiem izstrādātājiem, kuri vēlas labāku kontroli pār savām tīmekļa lietojumprogrammām. Tas ir izstrādāts, lai viegli izveidotu RESTful API, vienkāršu tīmekļa lietojumprogrammu vai sarežģītu tīmekļa lietojumprogrammu.

## Ātrā sākšana

```php
<?php

// ja ir instalēts ar komponistu
require 'vendor/autoload.php';
// vai arī ja instalēts manuāli ar zip failu
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'sveika, pasaule!';
});

Flight::route('/json', function() {
  Flight::json(['sveiki' => 'pasaule']);
});

Flight::start();
```

<div class="video-container">
	<iframe width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
</div>

Pietiekami vienkārši, vai ne? [Uzzini vairāk par Flight dokumentācijā!](learn)

### Struktūras/Pamata lietotne

Ir piemēra lietotne, kas var palīdzēt jums sākt strādāt ar Flight ietvaru. Dodieties uz [flightphp/skeleton](https://github.com/flightphp/skeleton), lai iegūtu norādes par to, kā sākt! Jūs varat arī apmeklēt [examples](examples) lapu, lai iedvesmotos par dažādām lietām, ko varat darīt ar Flight.

# Kopiena

Mēs esam Matrix tērzēšanā ar mums pievienojieties pie [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Ieguldīt

Ir divi veidi, kā jūs varat ieguldīt Flight:

1. Jūs varat ieguldīt pamatstruktūrā, apmeklējot [core repository](https://github.com/flightphp/core).
1. Jūs varat ieguldīt dokumentācijā. Šīs dokumentācijas vietne ir uzglabāta vietnē [Github](https://github.com/flightphp/docs). Ja pamanāt kļūdu vai vēlaties kaut ko uzlabot, jūs droši varat labot un iesniegt pieprasījumu "pull request"! Mēs cenšamies būt atjaunināti, bet jauninājumi un valodas tulkojumi ir laipni gaidīti.

# Prasības

Flight prasa PHP 7.4 vai jaunāku.

**Piezīme:** Tiek atbalstīts PHP 7.4, jo rakstīšanas pašreizējā laikā (2024) PHP 7.4 ir noklusējuma versija dažiem LTS Linux distribūcijām. Pāreja uz PHP >8 rada daudz nespējas šiem lietotājiem. Ietvars arī atbalsta PHP >8.

# Licence

Flight izlaižas zem [MIT](https://github.com/flightphp/core/blob/master/LICENSE) licences.