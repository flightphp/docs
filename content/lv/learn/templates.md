# Skati

Flight nodrošina pamata veidnes funkcionalitāti pēc noklusējuma.

Ja nepieciešamas sarežģītākas veidnes prasības, skatieties Smarty un Latte piemērus sadaļā [Pielāgoti Skati](#custom-views).

Lai attēlotu skata veidni, izsauciet `render` metodi ar veidnes faila nosaukumu un nepieciešamiem veidnes datiem:


```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Iesniegtie veidnes dati tiks automātiski iegulti veidnē un var tikt izmantoti kā lokāla mainīgā atsauce. Veidnes faili ir vienkārši PHP faili. Ja `hello.php` veidnes faila saturs ir:

```php
Sveiki, <?= $name ?>!
```

Izvade būs:

```
Sveiki, Bob!
```

Varat arī manuāli iestatīt skata mainīgos, izmantojot iestatīšanas metodi:

```php
Flight::view()->set('name', 'Bob');
```

Tagad mainīgais `name` ir pieejams visos jūsu skatos. Tāpēc vienkārši varat izdarīt:

```php
Flight::render('hello');
```

Ņemiet vērā, ka norādot veidnes nosaukumu render metodei, varat izlaist `.php` paplašinājumu.

Pēc noklusējuma Flight meklē `views` direktoriju veidņu failiem. Jūs varat norādīt alternatīvu ceļu savām veidnēm, iestatot sekojošo konfigurāciju:

```php
Flight::set('flight.views.path', '/ceļš/līdz/veidnēm');
```

## Izkārtojumi

Ir parasts, ka vietnēm ir viens izkārtojuma veidnes fails ar mainīgu saturu. Lai attēlotu saturu, kas tiks izmantots izkārtojumā, varat padot papildu parametru `render` metodē.

```php
Flight::render('header', ['heading' => 'Sveika'], 'headerContent');
Flight::render('body', ['body' => 'Pasaule'], 'bodyContent');
```

Jūsu skatam pēc tam būs saglabātie mainīgie `headerContent` un `bodyContent`.
Jūs varat attēlot savu izkārtojumu, izmantojot:

```php
Flight::render('layout', ['title' => 'Sākumlapa']);
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

Izvade būs:

```html
<html>
  <head>
    <title>Sākumlapa</title>
  </head>
  <body>
    <h1>Sveika</h1>
    <div>Pasaule</div>
  </body>
</html>
```

## Pielāgoti Skati

Flight ļauj nomainīt noklusējuma skata dzinēju vienkārši reģistrējot savu skata klasi.

### Smarty

Šeit ir kā jūs varētu izmantot [Smarty](http://www.smarty.net/) veidnes dzinēju savos skatos:

```php
// Ielādēt Smarty bibliotēku
require './Smarty/libs/Smarty.class.php';

// Reģistrēt Smarty kā skata klasi
// Arī padodiet atpakaļsauces funkciju, lai konfigurētu Smarty ielādes laikā
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
});

// Piešķir veidnes datus
Flight::view()->assign('name', 'Bob');

// Attēlot veidni
Flight::view()->display('hello.tpl');
```

Lai būtu pilnība, jums vajadzētu arī pārrakstīt Flight noklusējuma render metodi:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

Šeit ir kā jūs varētu izmantot [Latte](https://latte.nette.org/) veidnes dzinēju savos skatos:

```php

// Reģistrējiet Latte kā skata klasi
// Arī padodiet atpakaļsauces funkciju, lai konfigurētu Latte ielādes laikā
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Šeit Latte glabās jūsu veidnes kešatmiņā, lai paātrinātu lietas
	// Viens foršs Latte aspekts ir tas, ka tas automātiski atjaunos jūsu
	// kešatmiņu, ja veikt izmaiņas savās veidnēs!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Pateiciet Latte, kur būs jūsu skatu saknes direktorija.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Un ietiniet to, lai varētu pareizi izmantot Flight::render()
Flight::map('render', function(string $template, array $data): void {
  // Tas ir līdzīgi kā $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```