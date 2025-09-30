# HTML Skati un Veidnes

## Pārskats

Flight nodrošina dažas pamata HTML veidņu funkcionalitātes pēc noklusējuma. Veidņošana ir ļoti efektīvs veids, kā atdalīt jūsu lietojumprogrammas loģiku no jūsu prezentācijas slāņa.

## Saprašana

Kad jūs būvējat lietojumprogrammu, jums, visticamāk, būs HTML, ko vēlaties nodot atpakaļ gala lietotājam. PHP pats par sevi ir veidņu valoda, bet ir _ļoti_ viegli iekļaut biznesa loģiku, piemēram, datubāzes izsaukumus, API izsaukumus utt., jūsu HTML failā un padarīt testēšanu un atdalīšanu par ļoti sarežģītu procesu. Ievadot datus veidnē un ļaujot veidnei renderēt sevi, kļūst daudz vieglāk atdalīt un veikt vienības testus jūsu kodam. Jūs mums pateiksieties, ja izmantosiet veidnes!

## Pamata Izmantošana

Flight ļauj nomainīt noklusējuma skata dzinēju, vienkārši reģistrējot savu
paša skata klasi. Ritiniet uz leju, lai redzētu piemērus, kā izmantot Smarty, Latte, Blade un vairāk!

### Latte

<span class="badge bg-info">ieteicams</span>

Šeit ir aprakstīts, kā izmantot [Latte](https://latte.nette.org/)
veidņu dzinēju jūsu skatiem.

#### Instalācija

```bash
composer require latte/latte
```

#### Pamata Konfigurācija

Galvenā ideja ir tā, ka jūs pārrakstāt `render` metodi, lai izmantotu Latte nevis noklusējuma PHP renderētāju.

```php
// overwrite the render method to use latte instead of the default PHP renderer
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Where latte specifically stores its cache
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### Latte Izmantošana Flight

Tagad, kad jūs varat renderēt ar Latte, jūs varat darīt kaut ko šāda:

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

Kad jūs apmeklējat `/Bob` savā pārlūkā, izvade būtu:

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

#### Tālāka Lasīšana

Sarežģītāks Latte izmantošanas piemērs ar izkārtojumiem ir parādīts šīs dokumentācijas [awesome plugins](/awesome-plugins/latte) sadaļā.

Jūs varat uzzināt vairāk par Latte pilnām iespējām, tostarp tulkošanu un valodas iespējām, lasot [oficiālo dokumentāciju](https://latte.nette.org/en/).

### Iebūvētais Skata Dzinējs

<span class="badge bg-warning">novecojis</span>

> **Piezīme:** Lai gan tas joprojām ir noklusējuma funkcionalitāte un tehniski joprojām darbojas.

Lai parādītu skata veidni, izsauciet `render` metodi ar veidnes faila nosaukumu
un opcionāliem veidnes datiem:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Veidnes dati, ko jūs ievadāt, automātiski tiek ievadīti veidnē un var
tikt atsauce kā lokāla mainīgā. Veidnes faili ir vienkārši PHP faili. Ja
satura `hello.php` veidnes faila ir:

```php
Hello, <?= $name ?>!
```

Izvade būtu:

```text
Hello, Bob!
```

Jūs varat arī manuāli iestatīt skata mainīgos, izmantojot set metodi:

```php
Flight::view()->set('name', 'Bob');
```

Mainīgais `name` tagad ir pieejams visos jūsu skatos. Tātad jūs varat vienkārši darīt:

```php
Flight::render('hello');
```

Ņemiet vērā, ka norādot veidnes nosaukumu render metodē, jūs varat
izlaist `.php` paplašinājumu.

Pēc noklusējuma Flight meklēs `views` direktoriju veidnes failiem. Jūs varat
iestatīt alternatīvu ceļu jūsu veidnēm, iestatot šādu konfigurāciju:

```php
Flight::set('flight.views.path', '/path/to/views');
```

#### Izkārtojumi

Ir izplatīti, ka tīmekļa vietnēm ir viens izkārtojuma veidnes fails ar mainīgu
saturu. Lai renderētu saturu, ko izmantot izkārtojumā, jūs varat ievadīt opcionālu
parametru `render` metodē.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Jūsu skats tad būs saglabājis mainīgos, ko sauc par `headerContent` un `bodyContent`.
Jūs tad varat renderēt savu izkārtojumu darot:

```php
Flight::render('layout', ['title' => 'Home Page']);
```

Ja veidnes faili izskatās šādi:

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

Izvade būtu:
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

Šeit ir aprakstīts, kā izmantot [Smarty](http://www.smarty.net/)
veidņu dzinēju jūsu skatiem:

```php
// Load Smarty library
require './Smarty/libs/Smarty.class.php';

// Register Smarty as the view class
// Also pass a callback function to configure Smarty on load
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Assign template data
Flight::view()->assign('name', 'Bob');

// Display the template
Flight::view()->display('hello.tpl');
```

Pilnīgumam, jums vajadzētu arī pārrakstīt Flight noklusējuma render metodi:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

Šeit ir aprakstīts, kā izmantot [Blade](https://laravel.com/docs/8.x/blade) veidņu dzinēju jūsu skatiem:

Vispirms jums jāinstalē BladeOne bibliotēka caur Composer:

```bash
composer require eftec/bladeone
```

Tad jūs varat konfigurēt BladeOne kā skata klasi Flight:

```php
<?php
// Load BladeOne library
use eftec\bladeone\BladeOne;

// Register BladeOne as the view class
// Also pass a callback function to configure BladeOne on load
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Assign template data
Flight::view()->share('name', 'Bob');

// Display the template
echo Flight::view()->run('hello', []);
```

Pilnīgumam, jums vajadzētu arī pārrakstīt Flight noklusējuma render metodi:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

Šajā piemērā hello.blade.php veidnes fails var izskatīties šādi:

```php
<?php
Hello, {{ $name }}!
```

Izvade būtu:

```
Hello, Bob!
```

## Skatīt Arī
- [Paplašināšana](/learn/extending) - Kā pārrakstīt `render` metodi, lai izmantotu citu veidņu dzinēju.
- [Maršrutēšana](/learn/routing) - Kā kartēt maršrutus uz kontrolieriem un renderēt skatus.
- [Atbildes](/learn/responses) - Kā pielāgot HTTP atbildes.
- [Kāpēc Ietvars?](/learn/why-frameworks) - Kā veidnes iederas lielajā attēlā.

## Traucējummeklēšana
- Ja jums ir pāradresēšana jūsu starpprogrammatūrā, bet jūsu lietojumprogramma nešķiet pāradresējamies, pārliecinieties, ka pievienojat `exit;` paziņojumu jūsu starpprogrammatūrā.

## Izmaiņu Žurnāls
- v2.0 - Sākotnējais izdevums.