```lv
# Skati

Flight pēc noklusējuma nodrošina dažas pamata veidnes funkcijas.

Ja jums ir nepieciešamas sarežģītākas veidņu prasības, skatiet Smarty un Latte piemērus sadaļā [Pielāgoti skati](#custom-views).

Lai parādītu skata veidni, izsauciet `render` metodi ar veidnes faila nosaukumu un neobligātu veidnes datu:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Veidnes dati, ko jūs nododat, tiek automātiski injicēti veidnē un var tikt atsaukti kā lokāla mainīgā. Veidnes faili vienkārši ir PHP faili. Ja `hello.php` veidnes faila saturs ir:

```php
Sveiki, <?= $name ?>!
```

Izvade būs:

```
Sveiki, Bob!
```

Jūs arī varat manuāli iestatīt skata mainīgos, izmantojot set metodi:

```php
Flight::view()->set('name', 'Bob');
```

Tagad mainīgais `name` ir pieejams visos jūsu skatos. Tāpēc jūs vienkārši varat:

```php
Flight::render('hello');
```

Ņemiet vērā, ka, nosakot veidnes nosaukumu render metodē, jūs varat izlaist `.php` paplašinājumu.

Pēc noklusējuma Flight meklē `views` katalogu veidņu failiem. Jūs varat norādīt alternatīvu ceļu savām veidnēm, iestatot sekojošo konfigurāciju:

```php
Flight::set('flight.views.path', '/ceļš/līdz/veidnēm');
```

## Izkārtojumi

Ir parasts, ka vietnēm ir viens izkārtojuma veidne ar mainīgu saturu. Lai renderētu saturu, kas tiks izmantots izkārtojumā, jūs varat nodot neobligātu parametru `render` metodē.

```php
Flight::render('header', ['heading' => 'Sveiki'], 'headerContent');
Flight::render('body', ['body' => 'Pasaule'], 'bodyContent');
```

Tad jūsu skatījumā būs saglabāti mainīgie, ko sauc par `headerContent` un `bodyContent`. Tad jūs varēsiet renderēt savu izkārtojumu, darot:

```php
Flight::render('layout', ['title' => 'Sākumlapa']);
```

Ja veidne faili izskatās šādi:

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
    <h1>Sveiki</h1>
    <div>Pasaule</div>
  </body>
</html>
```

## Pielāgoti Skati

Flight ļauj jums mainīt noklusējuma skata dzinēju vienkārši, reģistrējot savu skata klasi.

### Smarty

Šeit ir, kā jūs varētu izmantot [Smarty](http://www.smarty.net/) veidnes dzinēju savos skatos:

```php
// Ielādēt Smarty bibliotēku
require './Smarty/libs/Smarty.class.php';

// Reģistrēt Smarty kā skata klasi
// Tāpat nododot atsauces funkciju, lai konfigurētu Smarty ielādes laikā
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

Pilnīgumam, jums arī vajadzētu pārkārtot Flight noklusējuma render metodi:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

Šeit ir, kā jūs varētu izmantot [Latte](https://latte.nette.org/) veidnes dzinēju savos skatos:

```php

// Reģistrēt Latte kā skata klasi
// Tāpat nododot atsauces funkciju, lai konfigurētu Latte ielādes laikā
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Šeit Latte saglabās jūsu veidnes, lai paātrinātu procesu
  // Viena lieliska lieta par Latte ir tā, ka tas automātiski atsvaidzinās jūsu
  // kešatmiņu, kad jūs veicat izmaiņas šablonos!
  $latte->setTempDirectory(__DIR__ . '/../cache/');

  // Pateikiet Latte, kur būs jūsu skatu saknes katalogs.
  $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Un iesaiņojiet to, lai jūs varat pareizi izmantot Flight::render()
Flight::map('render', function(string $template, array $data): void {
  // Tas ir līdzīgi kā $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```
```