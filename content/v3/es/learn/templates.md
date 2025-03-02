# Vistas HTML y Plantillas

Flight proporciona algunas funciones básicas de plantillas por defecto.

Flight te permite cambiar el motor de vista predeterminado simplemente registrando tu propia clase de vista. ¡Desplázate hacia abajo para ver ejemplos de cómo usar Smarty, Latte, Blade y más!

## Motor de Vista Incorporado

Para mostrar una plantilla de vista, llama al método `render` con el nombre del archivo de plantilla y los datos de plantilla opcionales:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Los datos de plantilla que pasas se inyectan automáticamente en la plantilla y pueden referenciarse como una variable local. Los archivos de plantilla son simplemente archivos PHP. Si el contenido del archivo de plantilla `hello.php` es:

```php
Hello, <?= $name ?>!
```

La salida sería:

```
Hello, Bob!
```

También puedes establecer manualmente variables de vista usando el método set:

```php
Flight::view()->set('name', 'Bob');
```

La variable `name` ahora está disponible en todas tus vistas. Así que puedes simplemente hacer:

```php
Flight::render('hello');
```

Ten en cuenta que al especificar el nombre de la plantilla en el método render, puedes omitir la extensión `.php`.

Por defecto, Flight buscará un directorio `views` para los archivos de plantilla. Puedes establecer una ruta alternativa para tus plantillas configurando lo siguiente:

```php
Flight::set('flight.views.path', '/path/to/views');
```

### Diseños

Es común que los sitios web tengan un único archivo de plantilla de diseño con contenido intercambiable. Para renderizar contenido que se usará en un diseño, puedes pasar un parámetro opcional al método `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Tu vista tendrá entonces variables guardadas llamadas `headerContent` y `bodyContent`. Luego puedes renderizar tu diseño haciendo:

```php
Flight::render('layout', ['title' => 'Home Page']);
```

Si los archivos de plantilla lucen así:

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

La salida sería:
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

Así es como usarías el motor de plantillas [Smarty](http://www.smarty.net/) para tus vistas:

```php
// Cargar la biblioteca Smarty
require './Smarty/libs/Smarty.class.php';

// Registrar Smarty como la clase de vista
// También pasa una función de callback para configurar Smarty al cargar
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Asignar datos de plantilla
Flight::view()->assign('name', 'Bob');

// Mostrar la plantilla
Flight::view()->display('hello.tpl');
```

Para completar, también deberías anular el método de renderizado predeterminado de Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

## Latte

Así es como usarías el motor de plantillas [Latte](https://latte.nette.org/) para tus vistas:

```php

// Registrar Latte como la clase de vista
// También pasa una función de callback para configurar Latte al cargar
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Aquí es donde Latte almacenará en caché tus plantillas para acelerar las cosas
	// Una cosa interesante sobre Latte es que actualiza automáticamente tu
	// caché cuando realizas cambios en tus plantillas.
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Indica a Latte dónde estará el directorio raíz para tus vistas.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Y envuélvelo para que puedas usar Flight::render() correctamente
Flight::map('render', function(string $template, array $data): void {
  // Esto es como $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```

## Blade

Así es como usarías el motor de plantillas [Blade](https://laravel.com/docs/8.x/blade) para tus vistas:

Primero, necesitas instalar la biblioteca BladeOne a través de Composer:

```bash
composer require eftec/bladeone
```

Luego, puedes configurar BladeOne como la clase de vista en Flight:

```php
<?php
// Cargar la biblioteca BladeOne
use eftec\bladeone\BladeOne;

// Registrar BladeOne como la clase de vista
// También pasa una función de callback para configurar BladeOne al cargar
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Asignar datos de plantilla
Flight::view()->share('name', 'Bob');

// Mostrar la plantilla
echo Flight::view()->run('hello', []);
```

Para completar, también deberías anular el método de renderizado predeterminado de Flight:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

En este ejemplo, el archivo de plantilla hello.blade.php podría lucir así:

```php
<?php
Hello, {{ $name }}!
```

La salida sería:

```
Hello, Bob!
```

Siguiendo estos pasos, puedes integrar el motor de plantillas Blade con Flight y utilizarlo para renderizar tus vistas.