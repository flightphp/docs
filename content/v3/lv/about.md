# Flight PHP Framework

Flight ir ātrs, vienkāršs, paplašināms PHP ietvars — izveidots izstrādātājiem, kuri vēlas ātri paveikt lietas, bez liekiem sarežģījumiem. Vai jūs veidojat klasisku tīmekļa lietotni, ātru API vai eksperimentējat ar jaunākajām AI balstītām rīkiem, Flight mazais pēdaizmērs un skaidrais dizains padara to par ideālu izvēli. Flight paredzēts būt vieglam, bet tas var arī apstrādāt uzņēmējdarbības arhitektūras prasības.

## Kāpēc izvēlēties Flight?

- **Draudzīgs iesācējiem:** Flight ir lielisks sākumpunkts jaunajiem PHP izstrādātājiem. Tā skaidrā struktūra un vienkāršā sintakse palīdz mācīties tīmekļa izstrādi, neaizmirstoties veidlapās.
- **Mīlēts profesionāļu vidū:** Pieredzējuši izstrādātāji mīl Flight par tā elastību un kontroli. Jūs varat skalēt no mazas prototipa līdz pilnvērtīgai lietotnei, nevis mainot ietvarus.
- **Atpakaļ savietojams:** Mēs augstu vērtējam jūsu laiku. Flight v3 ir v2 papildinājums, saglabājot gandrīz visu to pašu API. Mēs ticam evolūcijai, nevis revolūcijai — vairs nav "laupīšanas pasaulei" katru reizi, kad iznāk lielāka versija.
- **Bez atkarībām:** Flight kodols ir pilnībā bez atkarībām — bez polifiliem, bez ārējiem pakotnēm, pat bez PSR saskarnēm. Tas nozīmē mazāk uzbrukuma vektoru, mazāku pēdaizmēru un bez negaidītiem salauztiem izmaiņām no augšupstrēmes atkarībām. Neobligātie spraudņi var ietvert atkarības, bet kodols vienmēr paliks viegls un drošs.
- **Vairāks AI:** Flight minimālais overhead un tīrā arhitektūra padara to ideālu AI rīku un API integrācijai. Vai jūs veidojat gudrus čatbotus, AI vadītus panelus vai tikai vēlaties eksperimentēt, Flight netraucē, lai jūs varētu koncentrēties uz svarīgo. [Skeleton app](https://github.com/flightphp/skeleton) nāk ar iepriekš sagatavotiem instrukciju failiem galvenajām AI kodēšanas asistentēm jau no kastes! [Uzzināt vairāk par AI izmantošanu ar Flight](/learn/ai)

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

Vai arī lejupielādējiet repo zip [šeit](https://github.com/flightphp/core). Tad jums būs pamata `index.php` fails kā sekojošais:

```php
<?php

// if installed with composer
require 'vendor/autoload.php';
// or if installed manually by zip file
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

Tas ir viss! Jums ir pamata Flight lietojumprogramma. Jūs tagad varat palaist šo failu ar `php -S localhost:8000` un apmeklēt `http://localhost:8000` savā pārlūkprogrammā, lai redzētu izvadi.

## Skeleton/Boilerplate App

Ir piemēra lietojumprogramma, lai palīdzētu sākt jūsu projektu ar Flight. Tā satur strukturētu izkārtojumu, pamata konfigurācijas visas iestatītas un apstrādā composer skriptus tieši no vārtiem! Pārbaudiet [flightphp/skeleton](https://github.com/flightphp/skeleton) gatavam projektam vai apmeklējiet [examples](examples) lapu iedvesmai. Vēlaties redzēt, kā AI iederas? [Izpētīt AI balstītus piemērus](/learn/ai).

## Skeleton App instalēšana

Pietiekami viegli!

```bash
# Create the new project
composer create-project flightphp/skeleton my-project/
# Enter your new project directory
cd my-project/
# Bring up the local dev-server to get started right away!
composer start
```

Tas izveidos projektu struktūru, iestatīs jūsu vajadzīgos failus, un jūs esat gatavs doties!

## Augsta veiktspēja

Flight ir viens no ātrākajiem PHP ietvariem. Tā vieglais kodols nozīmē mazāku overhead un lielāku ātrumu — ideāli gan tradicionālām lietojumprogrammām, gan moderniem AI balstītiem projektiem. Jūs varat redzēt visus etalonus [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)

Redziet etalonu zemāk ar dažiem citiem populāriem PHP ietvariem.

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

Ziņkārīgs, kā tas apstrādā AI? [Atklājiet](/learn/ai) kā Flight padara darbu ar jūsu iecienītākajiem kodēšanas LLM vieglu!

## Stabilitāte un atpakaļ savietojamība

Mēs augstu vērtējam jūsu laiku. Mēs visi esam redzējuši ietvarus, kas pilnībā izdomā sevi no jauna ik pēc pāris gadiem, atstājot izstrādātājus ar salauztu kodu un dārgām migrācijām. Flight ir citāds. Flight v3 tika izstrādāts kā v2 papildinājums, kas nozīmē, ka API, ko jūs pazīstat un mīlat, nav noņemts. Patiesībā, lielākā daļa v2 projektu darbosies bez izmaiņām v3. 

Mēs apņēmusies saglabāt Flight stabilu, lai jūs varētu koncentrēties uz jūsu lietojumprogrammas veidošanu, nevis ietvara labošanu.

# Kopiena

Mēs esam Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

Un Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Iesaiste

Ir divi veidi, kā jūs varat ieguldīt Flight:

1. Ieguldīt kodola ietvarā apmeklējot [core repository](https://github.com/flightphp/core).
2. Palīdiet uzlabot dokumentāciju! Šī dokumentācijas vietne ir mitināta [Github](https://github.com/flightphp/docs). Ja jūs pamanāt kļūdu vai vēlaties kaut ko uzlabot, droši iesniedziet pull request. Mēs mīlam atjauninājumus un jaunas idejas — īpaši ap AI un jaunām tehnoloģijām!

# Prasības

Flight prasa PHP 7.4 vai jaunāku.

**Piezīme:** PHP 7.4 ir atbalstīts, jo pašreizējā rakstīšanas laikā (2024) PHP 7.4 ir noklusējuma versija dažām LTS Linux distribūcijām. Piespiežot pāreju uz PHP >8 radītu daudz sāpju tiem lietotājiem. Ietvars arī atbalsta PHP >8.

# Licence

Flight ir izlaists saskaņā ar [MIT](https://github.com/flightphp/core/blob/master/LICENSE) licenci.