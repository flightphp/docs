# Flight PHP Framework

Flight ir ātra, vienkārša, paplašināma framework PHP—izveidota izstrādātājiem, kuri vēlas ātri paveikt lietas, bez liekiem sarežģījumiem. Vai jūs veidojat klasisku tīmekļa lietojumprogrammu, ātru API vai eksperimentējat ar jaunākajām AI vadītajām rīkiem, Flight mazā pēda un vienkāršais dizains padara to par ideālu izvēli. Flight ir paredzēts būt tievs, bet tas var arī apstrādāt uzņēmuma arhitektūras prasības.

## Kāpēc izvēlēties Flight?

- **Draudzīgs iesācējiem:** Flight ir lielisks sākumpunkts jaunajiem PHP izstrādātājiem. Tā skaidrā struktūra un vienkāršā sintakse palīdz mācīties tīmekļa izstrādi, nezaudējot ceļu birokrātijā.
- **Mīlēts profesionāļu vidū:** Pieredzējuši izstrādātāji mīl Flight par tā elastību un kontroli. Jūs varat skalēt no maza prototipa līdz pilnvērtīgai lietojumprogrammai, nevis mainot framework.
- **Atpakaļsaderīgs:** Mēs augstu vērtējam jūsu laiku. Flight v3 ir v2 paplašinājums, saglabājot gandrīz visu API. Mēs ticam evolūcijai, nevis revolūcijai—vairs nav "iznīcināšanas pasaules" katru reizi, kad iznāk galvenā versija.
- **Bez atkarībām:** Flight kodols ir pilnībā bez atkarībām—nav polyfill, nav ārēju pakotņu, pat ne PSR saskarnes. Tas nozīmē mazāk uzbrukuma vektoru, mazāku pēdu un nav negaidītu salauzumu no augšstāvokļa atkarībām. Neobligātie spraudņi var ietvert atkarības, bet kodols vienmēr paliks tievs un drošs.
- **Veltīts AI:** Flight minimālais overhead un tīrā arhitektūra padara to ideālu AI rīku un API integrācijai. Vai jūs veidojat gudrus čatbotus, AI vadītus panelus vai tikai vēlaties eksperimentēt, Flight netraucē, lai jūs varat koncentrēties uz svarīgo. [Skeletu lietojumprogramma](https://github.com/flightphp/skeleton) nāk ar iepriekš sagatavotiem instrukciju failiem galvenajām AI kodēšanas asistentēm jau no kastes! [Uzzināt vairāk par AI izmantošanu ar Flight](/learn/ai)

## Video pārskats

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 fs-5 text-center mt-5 pt-5">
      <span class="flight-title-video"> pietiekami vienkārši, vai ne?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Uzzināt vairāk</a> par Flight dokumentācijā!
    </div>
  </div>
</div>

## Ātrais starts

Lai veiktu ātru, vienkāršu instalāciju, instalējiet to ar Composer:

```bash
composer require flightphp/core
```

Vai arī jūs varat lejupielādēt repo zip [šeit](https://github.com/flightphp/core). Tad jums būs pamata `index.php` fails kā sekojošais:

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
  Flight::json([
	'hello' => 'world'
  ]);
});

Flight::start();
```

Tas ir viss! Jums ir pamata Flight lietojumprogramma. Tagad jūs varat palaist šo failu ar `php -S localhost:8000` un apmeklēt `http://localhost:8000` savā pārlūkprogrammā, lai redzētu izvadi.

## Skeletu/Boilerplate lietojumprogramma

Ir piemēra lietojumprogramma, lai palīdzētu sākt savu projektu ar Flight. Tā ir strukturēta izkārtojuma, pamata konfigurācijas, kas jau iestatītas, un apstrādā composer skriptus jau no sākuma! Pārbaudiet [flightphp/skeleton](https://github.com/flightphp/skeleton) gatavam projektam vai apmeklējiet [piemēru](examples) lapu iedvesmai. Vēlaties redzēt, kā AI iekļaujas? [Izpētīt AI vadītus piemērus](/learn/ai).

## Skeletu lietojumprogrammas instalēšana

Pietiekami viegli!

```bash
# Izveidot jauno projektu
composer create-project flightphp/skeleton my-project/
# Ienākt jaunajā projektu direktorijā
cd my-project/
# Palaist lokālo dev-server, lai sāktu uzreiz!
composer start
```

Tas izveidos projektu struktūru, iestatīs vajadzīgos failus, un jūs esat gatavs!

## Augsta veiktspēja

Flight ir viens no ātrākajiem PHP framework ārā. Tā vieglais kodols nozīmē mazāku overhead un lielāku ātrumu—ideāli gan tradicionālām lietojumprogrammām, gan moderniem AI vadītiem projektiem. Jūs varat redzēt visus benchmarkus pie [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)

Redziet benchmarku zemāk ar dažiem citiem populāriem PHP framework.

| Framework | Plaintext Reqs/sec | JSON Reqs/sec |
| --------- | ------------ | ------------ |
| Flight      | 190,421    | 182,491 |
| Yii         | 145,749    | 131,434 |
| Fat-Free    | 139,238    | 133,952 |
| Slim        | 89,588     | 87,348  |
| Phalcon     | 95,911     | 87,675  |
| Symfony     | 65,053     | 63,237  |
| Lumen       | 40,572     | 39,700  |
| Laravel     | 26,657     | 26,901  |
| CodeIgniter | 20,628     | 19,901  |


## Flight un AI

Ziņkārīgs, kā tas apstrādā AI? [Atklājiet](/learn/ai) kā Flight padara darbu ar jūsu iecienītāko kodēšanas LLM vieglu!

## Stabilitāte un atpakaļsaderība

Mēs augstu vērtējam jūsu laiku. Mēs visi esam redzējuši framework, kas pilnībā izdomā sevi no jauna ik pēc pāris gadiem, atstājot izstrādātājus ar salauztu kodu un dārgām migrācijām. Flight ir citāds. Flight v3 tika izstrādāts kā v2 paplašinājums, kas nozīmē, ka API, ko jūs pazīstat un mīlat, nav noņemts. Patiesībā, lielākā daļa v2 projektu darbosies bez izmaiņām v3. 

Mēs apņēmusies saglabāt Flight stabilu, lai jūs varat koncentrēties uz savas lietojumprogrammas veidošanu, nevis framework labošanu.

# Kopiena

Mēs esam Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

Un Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Iesaiste

Ir divi veidi, kā jūs varat ieguldīt Flight:

1. Ieguldīt kodolā, apmeklējot [kodola repozitoriju](https://github.com/flightphp/core).
2. Palīdzi uzlabot dokumentāciju! Šī dokumentācijas vietne ir mitināta pie [Github](https://github.com/flightphp/docs). Ja jūs pamanāt kļūdu vai vēlaties uzlabot kaut ko, droši iesniedziet pull request. Mēs mīlam atjauninājumus un jaunas idejas—īpaši ap AI un jauno tehnoloģiju!

# Prasības

Flight prasa PHP 7.4 vai augstāku.

**Piezīme:** PHP 7.4 ir atbalstīts, jo pašreizējā rakstīšanas laikā (2024) PHP 7.4 ir noklusējuma versija dažām LTS Linux distribūcijām. Piespiežot pāriet uz PHP >8, tas radītu daudz problēmu tiem lietotājiem. Framework arī atbalsta PHP >8.

# Licence

Flight ir izlaists zem [MIT](https://github.com/flightphp/core/blob/master/LICENSE) licences.