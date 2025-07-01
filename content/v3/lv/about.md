# Kas ir Flight?

Flight ir ātrs, vienkāršs, paplašināms ietvars PHP — izveidots izstrādātājiem, kuri vēlas lietas izdarīt ātri, bez liekām raizēm. Vai jūs veidojat klasisku tīmekļa lietotni, ātru API vai eksperimentējat ar jaunākajām AI vadītajām rīkiem, Flight mazais nospiedums un vienkāršais dizains padara to par ideālu izvēli.

## Kāpēc izvēlēties Flight?

- **Piemērots iesācējiem:** Flight ir lielisks sākumpunkts jauniem PHP izstrādātājiem. Tā skaidrā struktūra un vienkāršā sintakse palīdz apgūt tīmekļa izstrādi, nezaudējot laiku liekā kodā.
- **Mīlēts profesionāļu vidū:** Pieredzējuši izstrādātāji mīl Flight par tā elastību un kontroli. Jūs varat attīstīt no neliela prototipa līdz pilnvērtīgai lietotnei, nevis mainot ietvarus.
- **AI draudzīgs:** Flight minimālais slogs un tīrais arhitektūra padara to ideālu AI rīku un API integrācijai. Vai jūs veidojat gudrus čatbotus, AI vadītus paneļus vai vienkārši eksperimentējat, Flight netraucē, lai jūs varētu koncentrēties uz to, kas ir svarīgi. [Uzziniet vairāk par AI izmantošanu ar Flight](/learn/ai)

## Ātrā uzsākšana

Vispirms instalējiet to ar Composer:

```bash
composer require flightphp/core
```

Vai arī lejupielādējiet repo zip failu [šeit](https://github.com/flightphp/core). Tad jums būs pamata `index.php` fails, piemēram, šāds:

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

Tas arī viss! Jums ir pamata Flight lietotne. Tagad jūs varat palaist šo failu ar `php -S localhost:8000` un apmeklēt `http://localhost:8000` pārlūkā, lai redzētu izvadi.

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">Vai tas nav pietiekami vienkārši?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Uzziniet vairāk par Flight dokumentācijā!</a>
      <br>
      <button href="/learn/ai" class="btn btn-primary mt-3">Atklājiet, kā Flight padara AI vienkāršu</button>
    </div>
  </div>
</div>

## Vai tas ir ātrs?

Pilnīgi noteikti! Flight ir viens no ātrākajiem PHP ietvariem. Tā vieglais kodols nozīmē mazāku slodzi un lielāku ātrumu — ideāli piemērots gan tradicionālām lietotnēm, gan mūsdienu AI vadītiem projektiem. Jūs varat redzēt visus benchmarkus vietnē [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)

Skatiet benchmarku zemāk ar dažiem citiem populāriem PHP ietvariem.

| Ietvars   | Plaintext pieprasījumi/sek | JSON pieprasījumi/sek |
| --------- | -------------------------- | --------------------- |
| Flight    | 190,421                   | 182,491              |
| Yii       | 145,749                   | 131,434              |
| Fat-Free  | 139,238                   | 133,952              |
| Slim      | 89,588                    | 87,348               |
| Phalcon   | 95,911                    | 87,675               |
| Symfony   | 65,053                    | 63,237               |
| Lumen     | 40,572                    | 39,700               |
| Laravel   | 26,657                    | 26,901               |
| CodeIgniter | 20,628                 | 19,901               |

## Skelets/Boilerplate lietotne

Ir pieejams piemērs, lai palīdzētu jums sākt ar Flight. Apskatiet [flightphp/skeleton](https://github.com/flightphp/skeleton) gatavam projektam vai apmeklējiet [examples](examples) lapu iedvesmai. Vai vēlaties redzēt, kā AI iekļaujas? [Izpētiet AI vadītos piemērus](/learn/ai).

# Kopiena

Mēs esam Matrix tērzē

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

Un Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Iesaistīšanās

Ir divi veidi, kā jūs varat piedalīties Flight:

1. Piedalieties kodola ietvara izstrādē, apmeklējot [core repository](https://github.com/flightphp/core).
2. Palīdziet uzlabot dokumentāciju! Šī dokumentācijas vietne ir mitināta uz [Github](https://github.com/flightphp/docs). Ja pamanāt kļūdu vai vēlaties kaut ko uzlabot, iesniedziet pull request. Mēs mīlam atjauninājumus un jaunas idejas — īpaši par AI un jaunām tehnoloģijām!

# Prasības

Flight prasa PHP 7.4 vai jaunāku.

**Piezīme:** PHP 7.4 tiek atbalstīts, jo rakstīšanas brīdī (2024) PHP 7.4 ir noklusētā versija dažām LTS Linux izdalēm. Spiešana pāriet uz PHP >8 izraisītu daudz problēmu lietotājiem. Ietvars arī atbalsta PHP >8.

# Licence

Flight ir izlaists zem [MIT](https://github.com/flightphp/core/blob/master/LICENSE) licences.