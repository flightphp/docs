# Skati

Flight no nodrošina pamata veidnes funkcionalitāti. Lai parādītu skata
veidni, izsauciet `render` metodi ar veidnes faila nosaukumu un opciju
veidnes datiem:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Veidnes dati, ko nododat, tiek automātiski ievietoti veidnē un var tikt
atsauce kā vietējā mainīgā. Veidnes faili ir vienkārši PHP faili. Ja
`hello.php` veidnes faila saturs ir:

```php
Sveiki, <?= $name ?>!
```

Izvade būtu:

```
Sveiki, Bob!
```

Jūs arī varat manuāli iestatīt skata mainīgos, izmantojot iestatīšanas metodi:

```php
Flight::view()->set('name', 'Bob');
```

Tagad mainīgais `name` ir pieejams visos jūsu skatos. Tāpēc vienkārši varat:

```php
Flight::render('hello');
```

Piezīmējiet, ka, norādot veidnes nosaukumu render metodei, varat
izlaist `.php` paplašinājumu.

Pēc noklusējuma Flight meklē `views` direktoriju veidņu failiem. Jūs varat
iestatīt alternatīvu ceļu savām veidnēm, iestatot šādu konfigurāciju:

```php
Flight::set('flight.views.path', '/ceļš/līdz/veidnēm');
```

## Izkārtojumi

Ir parasts, ka tīmekļa vietnēm ir viens izkārtojuma veidnes fails ar izmaināmiem
saturs. Lai atveidotu saturu, ko izmantot izkārtojumā, varat nodot opcijas
parametru `render` metodē.

```php
Flight::render('header', ['heading' => 'Sveiki'], 'headerContent');
Flight::render('body', ['body' => 'Pasaule'], 'bodyContent');
```

Jūsu skatā tiks saglabāti mainīgie ar nosaukumiem `headerContent` un `bodyContent`.
Tad varat atveidot savu izkārtojumu:

```php
Flight::render('layout', ['title' => 'Sākumlapa']);
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

Izvade būtu:
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

Flight ļauj nomainīt noklusējuma skata dzinēju, vienkārši reģistrējot savu
skata klasi. Šeit ir, kā jūs varētu izmantot [Smarty](http://www.smarty.net/)
veidnes dzini savos skatos:

```php
// Ielādēt Smarty bibliotēku
require './Smarty/libs/Smarty.class.php';

// Reģistrēt Smarty kā skata klasi
// Arī nododiet atsauces funkciju, lai konfigurētu Smarty ielādes laikā
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Piešķirt veidnes datus
Flight::view()->assign('name', 'Bob');

// Attēlot veidni
Flight::view()->display('hello.tpl');
```

Lai būtu pilnīgs, jums vajadzētu arī pārkārtot Flight noklusējuma render metodi:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```  