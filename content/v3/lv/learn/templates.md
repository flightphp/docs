# HTML Skatījumi un Veidnes

Flight pēc noklusējuma nodrošina dažas pamata veidņu funkcionalitātes. 

Flight ļauj jums aizvietot noklusējuma skatījuma dzinēju, vienkārši reģistrējot savu
skatījuma klasi. Ritiniet uz leju, lai redzētu piemērus par to, kā izmantot Smarty, Latte, Blade un citus!

## Iebūvētais Skatījuma Dzinējs

Lai attēlotu skatījuma veidni, izsauciet `render` metodi ar veidnes faila nosaukumu 
un izvēles veidnes datiem:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Veidnes dati, kurus jūs pārsūtāt, automātiski tiek injicēti veidnē un var 
tikt norādīti kā lokālā mainīgā. Veidnes faili ir vienkārši PHP faili. Ja `hello.php` 
veidnes faila saturs ir:

```php
Hello, <?= $name ?>!
```

Izeja būtu:

```
Hello, Bob!
```

Jūs varat arī manuāli iestatīt skatījuma mainīgos, izmantojot set metodi:

```php
Flight::view()->set('name', 'Bob');
```

Mainīgais `name` tagad ir pieejams visos jūsu skatījumos. Tātad jūs varat vienkārši darīt:

```php
Flight::render('hello');
```

Ņemiet vērā, ka, specifējot veidnes nosaukumu render metodē, varat
atstāt ārā `.php` paplašinājumu.

Pēc noklusējuma Flight meklēs `views` direktorijā veidņu failus. Jūs varat
iestatīt alternatīvu ceļu jūsu veidnēm, iestādot sekojošo konfigurāciju:

```php
Flight::set('flight.views.path', '/path/to/views');
```

### Izkārtojumi

Ir izplatīti gadījumi, kad tīmekļa vietnēm ir viena izkārtojuma veidne ar mainīgu
saturu. Lai attēlotu saturu, kas tiks izmantots izkārtojumā, jūs varat pārsūtīt
izvēles parametru `render` metodei.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Jūsu skatījumam tad būs saglabāti mainīgie ar nosaukumiem `headerContent` un `bodyContent`.
Jūs varat tad attēlot savu izkārtojumu, darot:

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

Izeja būtu:
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

## Smarty

Šeit ir, kā jūs varat izmantot [Smarty](http://www.smarty.net/)
veidņu dzinēju saviem skatījumiem:

```php
// Ielādējiet Smarty bibliotēku
require './Smarty/libs/Smarty.class.php';

// Reģistrējiet Smarty kā skatījuma klasi
// Tāpat pārsūtiet atgriezeniskās saites funkciju, lai konfigurētu Smarty uz ielādēšanas
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Piešķiriet veidnes datus
Flight::view()->assign('name', 'Bob');

// Attēlojiet veidni
Flight::view()->display('hello.tpl');
```

Lai būtu pilnīgi, jums vajadzētu arī pārrakstīt Flight noklusējuma render metodi:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

## Latte

Šeit ir, kā jūs varat izmantot [Latte](https://latte.nette.org/)
veidņu dzinēju saviem skatījumiem:

```php

// Reģistrējiet Latte kā skatījuma klasi
// Tāpat pārsūtiet atgriezeniskās saites funkciju, lai konfigurētu Latte uz ielādēšanas
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Šeit Latte kešos jūsu veidnes, lai paātrinātu lietas
	// Viens patīkams aspekts par Latte ir tas, ka tas automātiski atsvaidzina jūsu
	// kešu, kad jūs veicat izmaiņas savās veidnēs!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Pasakiet Latte, kur atradīsies jūsu skatījumu saknes direktorija.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Un noslēdziet to, lai jūs varētu pareizi izmantot Flight::render()
Flight::map('render', function(string $template, array $data): void {
  // Tas ir līdzīgi kā $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```

## Blade

Šeit ir, kā jūs varat izmantot [Blade](https://laravel.com/docs/8.x/blade) veidņu dzinēju saviem skatījumiem:

Vispirms jums ir jāinstalē BladeOne bibliotēka, izmantojot Composer:

```bash
composer require eftec/bladeone
```

Tad jūs varat konfigurēt BladeOne kā skatījuma klasi Flight:

```php
<?php
// Ielādējiet BladeOne bibliotēku
use eftec\bladeone\BladeOne;

// Reģistrējiet BladeOne kā skatījuma klasi
// Tāpat pārsūtiet atgriezeniskās saites funkciju, lai konfigurētu BladeOne uz ielādēšanas
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Piešķiriet veidnes datus
Flight::view()->share('name', 'Bob');

// Attēlojiet veidni
echo Flight::view()->run('hello', []);
```

Lai būtu pilnīgi, jums vajadzētu arī pārrakstīt Flight noklusējuma render metodi:

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

Izeja būtu:

```
Hello, Bob!
```

Ievērojot šos soļus, jūs varat integrēt Blade veidņu dzinēju ar Flight un izmantot to, lai attēlotu savus skatījumus.