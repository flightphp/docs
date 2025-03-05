# HTML Skatījumi un veidnes

Flight pēc noklusējuma nodrošina dažas pamata veidņu funkcionalitātes.

Flight ļauj jums aizstāt noklusējuma skatījumu dzinēju, vienkārši reģistrējot savu
skatu klasi. Ritiniet lejup, lai redzētu piemērus par to, kā izmantot Smarty, Latte, Blade un citus!

## Iebūvēts Skatījumu Dzinējs

Lai parādītu skata veidni, izsauciet `render` metodi ar veidņu faila nosaukumu 
un opciju veidņu datiem:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Veidņu dati, kurus jūs nododat, automātiski tiek injicēti veidnē un var būt
atsaucami kā vietējā mainīgā. Veidņu faili ir vienkārši PHP faili. Ja
`hello.php` veidnes faila saturs ir:

```php
Hello, <?= $name ?>!
```

Izeja būtu:

```text
Hello, Bob!
```

Jūs arī varat manuāli iestatīt skata mainīgos, izmantojot set metodi:

```php
Flight::view()->set('name', 'Bob');
```

Mainīgais `name` tagad ir pieejams visos jūsu skatījumos. Tātad jūs varat vienkārši darīt:

```php
Flight::render('hello');
```

Ņemiet vērā, ka, nosakot veidnes nosaukumu render metodi, jūs varat
izlaist `.php` paplašinājumu.

Noklusējuma Flight meklēs `views` direktorijā veidņu failus. Jūs varat
iestatīt alternatīvu ceļu savām veidnēm, iestatot sekojošo konfigurāciju:

```php
Flight::set('flight.views.path', '/path/to/views');
```

### Izkārtojumi

Ir ierasti, ka vietnēm ir viens izkārtojuma veidnes fails ar mainīgu
saturu. Lai attēlotu saturu, kas tiks izmantots izkārtojumā, varat nodot opciju
parametru `render` metodei.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Jūsu skatījumā tad būs saglabāti mainīgie, kuri saucas `headerContent` un `bodyContent`.
Tad varat renderēt savu izkārtojumu darot:

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

Šeit ir kā jūs varat izmantot [Smarty](http://www.smarty.net/)
veidņu dzinēju saviem skatījumiem:

```php
// Ielādējiet Smarty bibliotēku
require './Smarty/libs/Smarty.class.php';

// Reģistrējiet Smarty kā skata klasi
// Tāpat nododiet atgriezenisko izsaukumu funkciju, lai konfigurētu Smarty ielādēšanas laikā
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Piešķiriet veidņu datus
Flight::view()->assign('name', 'Bob');

// Parādiet veidni
Flight::view()->display('hello.tpl');
```

Lai pabeigtu, jums arī vajadzētu pārrakstīt Flight noklusējuma render metodi:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

## Latte

Šeit ir kā jūs varat izmantot [Latte](https://latte.nette.org/)
veidņu dzinēju saviem skatījumiem:

```php
// Reģistrējiet Latte kā skata klasi
// Tāpat nododiet atgriezenisko izsaukumu funkciju, lai konfigurētu Latte ielādēšanas laikā
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Šeit Latte saglabās jūsu veidnes, lai paātrinātu lietas
	// Viens interesants aspekts par Latte ir tas, ka tas automātiski atjauno jūsu
	// kešatmiņu, kad jūs veicat izmaiņas savās veidnēs!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Paziņojiet Latte, kur būs jūsu skatījumu saknes direktorija.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Un apkopojiet to, lai jūs varētu izmantot Flight::render() pareizi
Flight::map('render', function(string $template, array $data): void {
  // Tas ir līdzīgu $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```

## Blade

Šeit ir kā jūs varat izmantot [Blade](https://laravel.com/docs/8.x/blade) veidņu dzinēju saviem skatījumiem:

Vispirms jums jāinstalē BladeOne bibliotēka, izmantojot Composer:

```bash
composer require eftec/bladeone
```

Tad jūs varat konfigurēt BladeOne kā skata klasi Flight:

```php
<?php
// Ielādējiet BladeOne bibliotēku
use eftec\bladeone\BladeOne;

// Reģistrējiet BladeOne kā skata klasi
// Tāpat nododiet atgriezenisko izsaukumu funkciju, lai konfigurētu BladeOne ielādēšanas laikā
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Piešķiriet veidņu datus
Flight::view()->share('name', 'Bob');

// Parādiet veidni
echo Flight::view()->run('hello', []);
```

Lai pabeigtu, jums arī vajadzētu pārrakstīt Flight noklusējuma render metodi:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

Šajā piemēru `hello.blade.php` veidnes fails varētu izskatīties šādi:

```php
<?php
Hello, {{ $name }}!
```

Izeja būtu:

```
Hello, Bob!
```

Ievērojot šos soļus, jūs varat integrēt Blade veidņu dzinēju ar Flight un izmantot to, lai renderētu savus skatījumus.