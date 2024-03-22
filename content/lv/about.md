# Kas ir Flight?

Flight ir ātrs, vienkāršs, paplašināms PHP ietvars. Tas ir diezgan daudzveidīgs un var tikt izmantots, lai izveidotu jebkura veida tīmekļa lietojumprogrammu. Tas ir izveidots, ņemot vērā vienkāršību, un rakstīts tā, lai būtu viegli saprotams un izmantojams.

Flight ir lielisks iesācējiem paredzēts ietvars tiem, kuriem PHP ir jaunums un kuri vēlas uzzināt, kā veidot tīmekļa lietojumprogrammas. Tas ir arī lielisks ietvars pieredzējušiem izstrādātājiem, kuri vēlas vairāk kontroles pār savām tīmekļa lietojumprogrammām. Tas ir izstrādāts tā, lai viegli varētu izveidot RESTful API, vienkāršu tīmekļa lietojumprogrammu vai sarežģītu tīmekļa lietojumprogrammu.

## Ātrā sākšana

```php
<?php

// ja instalēts ar komponistu
require 'vendor/autoload.php';
// vai ja instalēts manuāli ar zip failu
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'sveika, pasaule!';
});

Flight::route('/json', function() {
  Flight::json(['sveika' => 'pasaule']);
});

Flight::start();
```

<div class="video-container">
	<iframe width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
</div>

Diezgan vienkārši, vai ne? [Uzzini vairāk par Flight dokumentācijā!](learn)

### Ķermeņa struktūra/uzgrieznis lietojumprogramma

Ir piemēra lietojumprogramma, kas var palīdzēt jums sākt darbu ar Flight ietvaru. Dodieties uz [flightphp/skeleton](https://github.com/flightphp/skeleton), lai saņemtu instrukcijas, kā sākt darbu! Vai arī apmeklējiet [piemēru](examples) lapu, lai iedvesmotu jūs par dažām lietām, ko varat darīt ar Flight.

# Kopiena

Mēs esam Matrix! Sarunājieties ar mums vietnē [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Piedalīšanās

Ir divi veidi, kā jūs varat piedalīties projektā Flight: 

1. Jūs varat piedalīties pamata ietvara izstrādē, apmeklējot [pamata izstrādes krātuvi](https://github.com/flightphp/core). 
1. Jūs varat piedalīties dokumentācijā. Šī dokumentācijas vietne tiek uzturēta [Github](https://github.com/flightphp/docs). Ja pamanāt kādu kļūdu vai vēlaties labāk izstrādāt kādu lietu, droši labojiet to un iesniedziet “pull request”! Mēs cenšamies turēties līdzi lietām, bet atjauninājumi un valodas tulkojumi tiek uzņemti ar prieku.

# Prasības

Flight prasa PHP 7.4 vai jaunāku versiju.

**Piezīme:** PHP 7.4 tiek atbalstīts, jo rakstīšanas brīdī (2024. gadā) PHP 7.4 ir noklusējuma versija dažiem LTS Linux izplatījumiem. Pāreja uz PHP >8 liktu lielu galvassāpi šo lietotāju. Ietvars atbalsta arī PHP >8.

# Licences līgums

Flight ir izdotss pēc [MIT](https://github.com/flightphp/core/blob/master/LICENSE) licences.