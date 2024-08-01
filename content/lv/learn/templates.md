# HTML Skati un Sagataves

`Flight` nodrošina pamata sagatavošanas funkcionalitāti pēc noklusējuma.

Ja jums ir nepieciešama sarežģītāka sagatavošanas vajadzība, skatiet Smarty un Latte piemērus sadaļā [Pielāgoti skati](#custom-views).

## Noklusētais skata dzinējs

Lai parādītu skata sagatavi, izsauciet `render` metodi ar sagataves faila nosaukumu un neobligātu sagataves datu:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Sagataves dati, ko jūs padodat, automātiski tiek ievietoti sagatavē un var tikt atsaukti kā lokāla mainīgā. Sagataves faili vienkārši ir PHP faili. Ja `hello.php` sagataves faila saturs ir:

```php
Sveiki, <?= $name ?>!
```

Izvade būs:

```
Sveiki, Bob!
```

Jūs varat arī manuāli iestatīt skata mainīgos, izmantojot set metodi:

```php
Flight::view()->set('name', 'Bob');
```

Tagad mainīgais `name` ir pieejams visos jūsu skatos. Tādējādi jūs vienkārši varat izdarīt:

```php
Flight::render('hello');
```

Jāpiebilst, ka, norādot sagataves nosaukumu render metodē, jūs varat izlaist `.php` paplašinājumu.

Pēc noklusējuma `Flight` meklē `views` direktoriju sagatavošanas failiem. Jūs varat 
iestatīt alternatīvu ceļu savām sagatavēm, iestatot sekojošo konfigurāciju:

```php
Flight::set('flight.views.path', '/ceļš/uz/skatiem');
```

### Izkārtojumi

Ir parasts, ka tīmekļa vietnēm ir viens izkārtojuma sagataves fails ar mainīgu saturu. Lai atveidotu saturu, kas tiks izmantots izkārtojumā, varat padot neobligātu parametru `render` metodē.

```php
Flight::render('header', ['heading' => 'Sveiki'], 'headerContent');
Flight::render('body', ['body' => 'Pasaulē'], 'bodyContent');
```

Tad jūsu skatās būs saglabāti mainīgie ar nosaukumiem `headerContent` un `bodyContent`.
Jūs varat atveidot savu izkārtojumu, darot:

```php
Flight::render('layout', ['title' => 'Mājas lapā']);
```

Ja sagataves faili izskatās šādi:

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
    <title>Mājas lapā</title>
  </head>
  <body>
    <h1>Sveiki</h1>
    <div>Pasaulē</div>
  </body>
</html>
```

## Pielāgoti skata dzinēji

`Flight` ļauj jums vienkārši nomainīt noklusēto skata dzinēju, vienkārši reģistrējot savu skata klasi.

### Smarty

Šeit ir kā jūs varētu izmantot [Smarty](http://www.smarty.net/) sagatavošanas dzinēju savos skatos:

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

// Piešķirt sagataves datus
Flight::view()->assign('name', 'Bob');

// Rādīt sagatavi
Flight::view()->display('hello.tpl');
```

Lai būtu pilnīgas, jums vajadzētu pārrakstīt arī `Flight` noklusēto render metodi:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

Šeit ir kā jūs varētu izmantot [Latte](https://latte.nette.org/) sagatavošanas dzinēju savos skatos:

```php

// Reģistrēt Latte kā skata klasi
// Tāpat nododot atsauces funkciju, lai konfigurētu Latte ielādes laikā
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Šeit Latte saglabās jūsu sagataves, lai paātrinātu lietas
  // Viens īpaši labs Latte aspekts ir tas, ka tas automātiski atjauno jūsu
  // kešatmiņu, kad jūs veicat izmaiņas savās sagatavēs!
  $latte->setTempDirectory(__DIR__ . '/../cache/');

  // Paziņojiet Latte, kur būs jūsu skatu saknes direktorijs.
  $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Un iesaiņojiet to, lai jūs varētu pareizi izmantot Flight::render()
Flight::map('render', function(string $template, array $data): void {
  // Tas ir kā $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```