# Flight PHP Framework

Flight ir ātrs, vienkāršs, paplašināms ietvars PHP—izveidots izstrādātājiem, kuri vēlas ātri paveikt darbus, bez liekām raizēm. Vai jūs veidojat klasisku tīmekļa lietotni, ātru API vai eksperimentējat ar jaunākajām AI balstītajām rīkiem, Flight zems nospiedums un vienkāršais dizains padara to par ideālu izvēli. Flight ir domāts, lai būtu viegls, bet tas var arī tikt galā ar uzņēmuma arhitektūras prasībām.

## Kāpēc izvēlēties Flight?

- **Sācējiem draudzīgs:** Flight ir lielisks sākumpunkts jauniem PHP izstrādātājiem. Tā skaidrā struktūra un vienkāršā sintakse palīdz iemācīties tīmekļa attīstību, nezaudējot laiku liekā kodā.
- **Profesionāļu mīlulis:** Pieredzējuši izstrādātāji mīl Flight par tās elastību un kontroli. Jūs varat augt no neliela prototipa līdz pilnvērtīgai lietotnei, neizmainot ietvarus.
- **AI draudzīgs:** Flight minimālais slogs un tīrais arhitektūras dizains padara to ideālu AI rīku un API integrācijai. Vai jūs veidojat gudrus čatbotus, AI vadītas paneļus vai vienkārši vēlaties eksperimentēt, Flight netraucē, ļaujot koncentrēties uz svarīgo. [Skeletapp](https://github.com/flightphp/skeleton) nāk ar iepriekš sagatavotiem instrukciju failiem galvenajiem AI kodēšanas asistentiem jau no kastes! [Uzziniet vairāk par AI izmantošanu ar Flight](/learn/ai)

## Video Overview

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 fs-5 text-center mt-5 pt-5">
      <span class="flight-title-video">Vai tas nav pietiekami vienkārši?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Uzziniet vairāk</a> par Flight dokumentācijā!
    </div>
  </div>
</div>

## Quick Start

Lai veiktu ātru, pamata instalāciju, instalējiet to ar Composer:

```bash
composer require flightphp/core
```

Vai jūs varat lejupielādēt zip failu no repozitorijas [šeit](https://github.com/flightphp/core). Tad jums būs pamata `index.php` fails, piemēram, šāds:

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

Tas ir viss! Jums ir pamata Flight lietotne. Tagad jūs varat palaist šo failu ar `php -S localhost:8000` un apmeklēt `http://localhost:8000` savā pārlūkā, lai redzētu izvadi.

## Skeleton/Boilerplate App

Ir piemērs app, lai palīdzētu jums sākt projektu ar Flight. Tajā ir strukturēta izkārtojums, pamata konfigurācijas un saderība ar composer skriptiem jau no sākuma! Apskatiet [flightphp/skeleton](https://github.com/flightphp/skeleton) gatavam projektam vai apmeklējiet [piemērus](examples) lapu iedvesmai. Vēlaties redzēt, kā AI iederas? [Izpētiet AI balstītus piemērus](/learn/ai).

## Installing the Skeleton App

Tas ir pietiekami vienkārši!

```bash
# Izveidojiet jauno projektu
composer create-project flightphp/skeleton my-project/
# Ieejiet jaunajā projektu direktorijā
cd my-project/
# Palaidiet lokālo attīstības serveri, lai sāktu nekavējoties!
composer start
```

Tas izveidos projektu struktūru, iestatīs vajadzīgos failus, un jūs esat gatavs!

## High Performance

Flight ir viens no ātrākajiem PHP ietvariem. Tā vieglais kodols nozīmē mazāku slogu un vairāk ātrumu—ideāli piemērots gan tradicionālām lietotnēm, gan mūsdienu AI balstītiem projektiem. Jūs varat redzēt visus benchmarkus [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)

Skatiet benchmarku zemāk ar dažiem citiem populāriem PHP ietvariem.

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


## Flight and AI

Vai jūs interesē, kā tas darbojas ar AI? [Atklājiet](/learn/ai), kā Flight atvieglo darbu ar jūsu mīļākajiem kodēšanas LLM!

# Community

Mēs esam Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

Un Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Contributing

Ir divi veidi, kā jūs varat piedalīties Flight:

1. Piedalieties kodola ietvara izstrādē, apmeklējot [core repozitoriju](https://github.com/flightphp/core).
2. Palīdziet uzlabot dokumentāciju! Šī dokumentācijas vietne ir mitināta [Github](https://github.com/flightphp/docs). Ja jūs pamanāt kļūdu vai vēlaties kaut ko uzlabot, iesniedziet pull request. Mēs mīlam atjauninājumus un jaunas idejas—īpaši ap AI un jaunām tehnoloģijām!

# Requirements

Flight prasa PHP 7.4 vai jaunāku.

**Piezīme:** PHP 7.4 tiek atbalstīts, jo šobrīd (2024. gadā) PHP 7.4 ir noklusētā versija dažām LTS Linux izdalēm. Spiešana uz pāreju uz PHP >8 izraisītu daudz neērtību lietotājiem. Ietvars arī atbalsta PHP >8.

# License

Flight ir izlaists zem [MIT](https://github.com/flightphp/core/blob/master/LICENSE) licences.