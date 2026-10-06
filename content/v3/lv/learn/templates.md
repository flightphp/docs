# HTML skati un veidnes

## Pārskats

Flight pēc noklusējuma nodrošina dažas pamata HTML veidņu funkcionalitātes. Veidņošana ir ļoti efektīvs veids, kā atdalīt lietotnes loģiku no prezentācijas slāņa. Īpašs dzinējs (Twig, Latte u.c.) arī sniedz [AI kodēšanas rīkiem](/learn/ai) pazīstamu, ierobežotu sintaksi, tāpēc tie mazāk tiecas ievietot biznesa loģiku jūsu HTML.

## Izpratne

Kad veidojat lietotni, jums, visticamāk, būs HTML, ko vēlēsities atgriezt galalietotājam. PHP pats par sevi ir veidņu valoda, taču ir _ļoti_ viegli iekļaut biznesa loģiku, piemēram, datubāzes izsaukumus, API izsaukumus u.c., savā HTML failā un padarīt testēšanu un atsaistīšanu par ļoti sarežģītu procesu. Ievietojot datus veidnē un ļaujot veidnei renderēties pašai, kļūst daudz vieglāk atsaistīt un unit testēt savu kodu. Jūs mums pateiksities, ja izmantosiet veidnes!

## Pamata lietojums

Flight ļauj nomainīt noklusējuma skata dzinēju, vienkārši mapējot `render` (vai reģistrējot skata klasi). Ritiniet uz leju, lai uzzinātu par Twig, Latte, Smarty, Blade un citiem.

> **Skeleta noklusējums:** Oficiālais [flightphp/skeleton](https://github.com/flightphp/skeleton) izmanto **tikai Twig** zem `app/views/` (`*.twig`). Kontrolieri izsauc `$this->app->render('welcome', $data)` (paplašinājums nav obligāts). Tā ir lietotnes izvēle jauniem projektiem, nevis Flight kodola prasība. Latte un citi dzinēji joprojām tiek pilnībā atbalstīti.

### Twig

<span class="badge bg-info">skeleta noklusējums</span>

[Twig](https://twig.symfony.com/) ir elastīgs, ātrs un drošs veidņu dzinējs, ko izmanto Symfony un daudzi citi PHP projekti. AI kodēšanas rīki mēdz īpaši labi pārzināt Twig, un tas pēc noklusējuma automātiski aizsargā izvadi, kas palīdz aizsargāties pret XSS.

#### Instalēšana

```bash
composer require twig/twig
```

(Jau iekļauts, kad palaižat `composer create-project flightphp/skeleton`.)

#### Pamata konfigurācija

Pārrakstiet `render` metodi, lai izmantotu Twig noklusējuma PHP renderētāja vietā:

```php
// pārrakstiet render metodi, lai izmantotu Twig noklusējuma PHP renderētāja vietā
Flight::map('render', function(string $template, array $data): void {
	$loader = new \Twig\Loader\FilesystemLoader(Flight::get('flight.views.path'));
	$twig = new \Twig\Environment($loader, [
		// Kur Twig glabā savas kompilētās veidnes
		'cache' => __DIR__ . '/../cache/twig',
		'auto_reload' => true,
	]);

	// Atļaut "welcome" vai "welcome.twig"
	if (substr($template, -5) !== '.twig') {
		$template .= '.twig';
	}

	echo $twig->render($template, $data);
});
```

Skeletā šī savienošana atrodas `app/config/services.php` (koplietota Twig vide, kešatmiņas ceļš, globālie mainīgie, piemēram, `base_url` / CSP nonce). Priekšroka dodama `Engine` injicēšanai un `$app->render()` izsaukšanai no kontrolieriem, lai kods saglabātos [AI un testiem draudzīgs](/learn/ai).

#### Twig izmantošana Flight

Tagad, kad varat renderēt ar Twig, varat darīt kaut ko šādu:

```html
{# app/views/home.twig #}
<html>
  <head>
	<title>{% if title %}{{ title }} - {% endif %}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, {{ name }}!</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.twig', [
		'title' => 'Home Page',
		'name' => $name
	]);
});
```

Kad pārlūkā apmeklējat `/Bob`, izvade būs:

```html
<html>
  <head>
	<title>Home Page - My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, Bob!</h1>
  </body>
</html>
```

#### Papildu lasīšana

Pilnīgāks Twig izmantošanas ar izkārtojumiem piemērs ir parādīts šīs dokumentācijas [lielisko spraudņu](/awesome-plugins/twig) sadaļā. Lai iegūtu renderēšanas laika metriku Tracy joslā, skatiet [Twig paneli Tracy paplašinājumos](/awesome-plugins/tracy-extensions#twig-panel-optional).

Vairāk par Twig pilnajām iespējām varat uzzināt, lasot [oficiālo dokumentāciju](https://twig.symfony.com/doc/3.x/).

### Latte

<span class="badge bg-secondary">lieliska alternatīva</span>

[Latte](https://latte.nette.org/) ir pilnvērtīgs dzinējs ar PHP līdzīgu sintaksi. Tas joprojām ir lieliska izvēle Flight lietotnēm; skelets vienkārši standartizē Twig kā vienu kopīgu noklusējumu (īpaši noderīgi, kad AI rīki ģenerē veidnes).

#### Instalēšana

```bash
composer require latte/latte
```

#### Pamata konfigurācija

Galvenā ideja ir pārrakstīt `render` metodi, lai izmantotu Latte noklusējuma PHP renderētāja vietā.

```php
// pārrakstiet render metodi, lai izmantotu latte noklusējuma PHP renderētāja vietā
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Kur latte konkrēti glabā savu kešatmiņu
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### Latte izmantošana Flight

Tagad, kad varat renderēt ar Latte, varat darīt kaut ko šādu:

```html
<!-- app/views/home.latte -->
<html>
  <head>
	<title>{$title ? $title . ' - '}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, {$name}!</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.latte', [
		'title' => 'Home Page',
		'name' => $name
	]);
});
```

Kad pārlūkā apmeklējat `/Bob`, izvade būs:

```html
<html>
  <head>
	<title>Home Page - My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, Bob!</h1>
  </body>
</html>
```

#### Papildu lasīšana

Sarežģītāks Latte izmantošanas ar izkārtojumiem piemērs ir parādīts šīs dokumentācijas [lielisko spraudņu](/awesome-plugins/latte) sadaļā.

Vairāk par Latte pilnajām iespējām, tostarp tulkošanas un valodu iespējām, varat uzzināt, lasot [oficiālo dokumentāciju](https://latte.nette.org/en/).

### Iebūvētais skata dzinējs

<span class="badge bg-warning">novecojis</span>

> **Piezīme:** Lai gan tā joprojām ir noklusējuma funkcionalitāte un tehniski joprojām darbojas.

Lai parādītu skata veidni, izsauciet `render` metodi ar veidnes faila nosaukumu un neobligātiem veidnes datiem:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Jūsu nodotie veidnes dati tiek automātiski ievadīti veidnē, un uz tiem var atsaukties kā uz lokālu mainīgo. Veidņu faili ir vienkārši PHP faili. Ja `hello.php` veidnes faila saturs ir:

```php
Hello, <?= $name ?>!
```

Izvade būs:

```text
Hello, Bob!
```

Varat arī manuāli iestatīt skata mainīgos, izmantojot set metodi:

```php
Flight::view()->set('name', 'Bob');
```

Mainīgais `name` tagad ir pieejams visos jūsu skatos. Tāpēc varat vienkārši darīt:

```php
Flight::render('hello');
```

Ņemiet vērā, ka, norādot veidnes nosaukumu render metodē, varat izlaist `.php` paplašinājumu.

Pēc noklusējuma Flight meklēs `views` direktoriju veidņu failiem. Varat iestatīt alternatīvu ceļu savām veidnēm, iestatot šādu konfigurāciju:

```php
Flight::set('flight.views.path', '/path/to/views');
```

Pēc noklusējuma Flight iebūvētais `View` arī pieņems absolūtu veidnes ceļu vai nosaukumu, kas izkāpj no šī direktorija. Lielākajai daļai lietotņu tas būtu jāierobežo:

```php
Flight::set('flight.views.restrict_to_path', true);
```

Tas notur `render()`, `fetch()` un `exists()` `flight.views.path` ietvaros. Pēc noklusējuma tas ir izslēgts saderībai ar iepriekšējām versijām. Skatiet [Drošību](/learn/security#flightviewsrestrict_to_path).

#### Izkārtojumi

Vietnēm parasti ir viens izkārtojuma veidnes fails ar mainīgu saturu. Lai renderētu saturu, ko izmantot izkārtojumā, varat nodot neobligātu parametru `render` metodei.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Jūsu skatā pēc tam būs saglabāti mainīgie ar nosaukumiem `headerContent` un `bodyContent`. Pēc tam varat renderēt savu izkārtojumu, veicot:

```php
Flight::render('layout', ['title' => 'Home Page']);
```

Ja veidņu faili izskatās šādi:

`header.php`:

```php
<h1><?= $heading ?></h1>
```

`body.php`:

```php
<div><?= $body ?></div>
```

`layout.php`:

```php
<html>
  <head>
    <title><?= $title ?></title>
  </head>
  <body>
    <?= $headerContent ?>
    <?= $bodyContent ?>
  </body>
</html>
```

Izvade būs:

```html
<html>
  <head>
    <title>Home Page</title>
  </head>
  <body>
    <h1>Hello</h1>
    <div>World</div>
  </body>
</html>
```

### Smarty

Lūk, kā jūs izmantotu [Smarty](http://www.smarty.net/) veidņu dzinēju saviem skatiem:

```php
// Ielādēt Smarty bibliotēku
require './Smarty/libs/Smarty.class.php';

// Reģistrēt Smarty kā skata klasi
// Arī nodot atzvanīšanas funkciju, lai konfigurētu Smarty ielādēšanas laikā
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Piešķirt veidnes datus
Flight::view()->assign('name', 'Bob');

// Parādīt veidni
Flight::view()->display('hello.tpl');
```

Pilnības labad jums arī jāpārraksta Flight noklusējuma render metode:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

Lūk, kā jūs izmantotu [Blade](https://laravel.com/docs/8.x/blade) veidņu dzinēju saviem skatiem:

Vispirms jums ir jāinstalē BladeOne bibliotēka, izmantojot Composer:

```bash
composer require eftec/bladeone
```

Pēc tam varat konfigurēt BladeOne kā skata klasi Flight:

```php
<?php
// Ielādēt BladeOne bibliotēku
use eftec\bladeone\BladeOne;

// Reģistrēt BladeOne kā skata klasi
// Arī nodot atzvanīšanas funkciju, lai konfigurētu BladeOne ielādēšanas laikā
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Piešķirt veidnes datus
Flight::view()->share('name', 'Bob');

// Parādīt veidni
echo Flight::view()->run('hello', []);
```

Pilnības labad jums arī jāpārraksta Flight noklusējuma render metode:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

Šajā piemērā hello.blade.php veidnes fails varētu izskatīties šādi:

```php
<?php
Hello, {{ $name }}!
```

Izvade būs:

```
Hello, Bob!
```

## Skatīt arī
- [Instalēšana](/install) - Skeleta izkārtojums (`app/views/*.twig`) jauniem projektiem.
- [Paplašināšana](/learn/extending) - Kā pārrakstīt `render` metodi, lai izmantotu citu veidņu dzinēju.
- [Maršrutēšana](/learn/routing) - Kā kartēt maršrutus uz kontrolieriem un renderēt skatus.
- [Atbildes](/learn/responses) - Kā pielāgot HTTP atbildes.
- [Drošība](/learn/security) - Automātiskā aizsargāšana, XSS un `flight.views.restrict_to_path`.
- [AI un izstrādātāju pieredze](/learn/ai) - Kāpēc viens noklusējuma skata dzinējs palīdz kodēšanas aģentiem.
- [Kāpēc ietvars?](/learn/why-frameworks) - Kā veidnes iekļaujas kopainā.

## Problēmu novēršana
- Ja jums ir pāradresācija starpprogrammatūrā, bet šķiet, ka lietotne nepāradresē, pārliecinieties, ka starpprogrammatūrā pievienojat `exit;` priekšrakstu.
- Ja Twig nevar atrast veidni, pārbaudiet `flight.views.path` un vai fails pastāv šajā ceļā ar gaidīto paplašinājumu (skelets: `app/views/`).

## Izmaiņu žurnāls
- Dokumentācija – Dokumentēts `flight.views.restrict_to_path` vietējiem PHP skatiem.
- Dokumentācija – Twig dokumentēts kā oficiālais skeleta noklusējums; Latte joprojām ir pirmšķirīga alternatīva.
- v2.0 - Sākotnējā laidiena.