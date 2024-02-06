# Skati

Flight nodrošina pamata templēšanas funkcionalitāti pēc noklusējuma. Lai parādītu skata
templātu, izsauciet `render` metodi ar templāta faila nosaukumu un opcijas
templātu datiem:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Datubāzi, ko jūs nododat iekšā, automātiski ievada templātā un var
būt atsauce kā lokāls mainīgais. Templāta faili ir vienkārši PHP faili. Ja
`hello.php` templāta faila saturs ir:

```php
Sveiki, <?= $name ?>!
```

Izvade būtu:

```
Sveiki, Bob!
```

Jūs arī varat manuāli iestatīt skata mainīgos, izmantojot set metodi:

```php
Flight::view()->set('name', 'Bob');
```

Mainīgais `name` tagad ir pieejams visos jūsu skatos. Tātad vienkārši varat darīt:

```php
Flight::render('hello');
```

Ņemiet vērā, ka norādot templāta nosaukumu render metodē, jūs varat
izlaist `.php` paplašinājumu.

Pēc noklusējuma Flight meklēs `views` direktoriju templātu failiem. Jūs varat
iestatīt alternatīvu ceļu saviem templātiem, iestatot šādu konfigurāciju:

```php
Flight::set('flight.views.path', '/ceļš/līdz/views');
```

## Izkārtojumi

Ir parasts, ka tīmekļa vietnēm ir viens izkārtojuma templāta fails, kurā mainās
saturs. Lai attēlotu saturu, kas jāizmanto izkārtojumā, jūs varat nodot opcijas
parametru `render` metodē.

```php
Flight::render('header', ['heading' => 'Sveiki'], 'headerContent');
Flight::render('body', ['body' => 'Pasaulē'], 'bodyContent');
```

Jūsu skatam tad būs saglabāti mainīgie ar nosaukumiem `headerContent` un `bodyContent`.
Tad jūs varat attēlot savu izkārtojumu, darot:

```php
Flight::render('layout', ['title' => 'Mājas lapā']);
```

Ja templātu faili izskatās šādi:

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

## Pielāgoti skati

Flight ļauj nomainīt noklusējuma skata dzinēju, vienkārši reģistrējot savu
skata klasi. Šeit ir, kā jūs izmantotu [Smarty](http://www.smarty.net/)
templāta dzinēju savām skatam:

```php
// Ielādēt Smarty bibliotēku
require './Smarty/libs/Smarty.class.php';

// Reģistrējiet Smarty kā skata klasi
// Iepriekš padodot atsauces funkciju, lai konfigurētu Smarty, ielādējot
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Piešķiriet templāta datus
Flight::view()->assign('name', 'Bob');

// Rādīt templātu
Flight::view()->display('hello.tpl');
```

Lai būtu pilnīgs, jums vajadzētu arī pārrakstīt Flight noklusējuma render metodi:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```  