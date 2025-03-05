# Vistas HTML y Plantillas

Flight proporciona una funcionalidad básica de plantillas por defecto.

Flight te permite cambiar el motor de vista predeterminado simplemente registrando tu propia clase de vista. Desplázate hacia abajo para ver ejemplos de cómo usar Smarty, Latte, Blade y más.

## Motor de Vista Integrado

Para mostrar una plantilla de vista, llama al método `render` con el nombre del archivo de plantilla y datos de plantilla opcionales:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Los datos de plantilla que pases se inyectan automáticamente en la plantilla y se pueden referenciar como una variable local. Los archivos de plantilla son simplemente archivos PHP. Si el contenido del archivo de plantilla `hello.php` es:

```php
¡Hola, <?= $name ?>!
```

La salida sería:

```text
¡Hola, Bob!
```

También puedes establecer manualmente variables de vista utilizando el método set:

```php
Flight::view()->set('name', 'Bob');
```

La variable `name` ahora está disponible en todas tus vistas. Así que puedes hacerlo simplemente:

```php
Flight::render('hello');
```

Ten en cuenta que al especificar el nombre de la plantilla en el método render, puedes omitir la extensión `.php`.

Por defecto, Flight buscará un directorio `views` para archivos de plantilla. Puedes establecer una ruta alternativa para tus plantillas configurando lo siguiente:

```php
Flight::set('flight.views.path', '/path/to/views');
```

### Diseños

Es común que los sitios web tengan un único archivo de plantilla de diseño con contenido intercambiable. Para renderizar contenido que se utilizará en un diseño, puedes pasar un parámetro opcional al método `render`.

```php
Flight::render('header', ['heading' => 'Hola'], 'headerContent');
Flight::render('body', ['body' => 'Mundo'], 'bodyContent');
```

Tu vista tendrá variables guardadas llamadas `headerContent` y `bodyContent`. Luego puedes renderizar tu diseño haciendo:

```php
Flight::render('layout', ['title' => 'Página Principal']);
```

Si los archivos de plantilla se ven así:

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
    <title>Página Principal</title>
  </head>
  <body>
    <h1>Hola</h1>
    <div>Mundo</div>
  </body>
</html>
```

## Smarty

Aquí tienes cómo usar el motor de plantillas [Smarty](http://www.smarty.net/) para tus vistas:

```php
// Cargar la biblioteca Smarty
require './Smarty/libs/Smarty.class.php';

// Registrar Smarty como la clase de vista
// También pasar una función de callback para configurar Smarty al cargar
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Asignar datos a la plantilla
Flight::view()->assign('name', 'Bob');

// Mostrar la plantilla
Flight::view()->display('hello.tpl');
```

Para ser completo, también deberías sobrescribir el método render predeterminado de Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

## Latte

Aquí tienes cómo usar el motor de plantillas [Latte](https://latte.nette.org/) para tus vistas:

```php
// Registrar Latte como la clase de vista
// También pasar una función de callback para configurar Latte al cargar
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Aquí es donde Latte almacenará en caché tus plantillas para acelerar las cosas
	// Una característica interesante de Latte es que automáticamente actualiza tu
	// caché cuando haces cambios en tus plantillas.
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Indicar a Latte dónde estará el directorio raíz para tus vistas.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Y rematarlo para que puedas usar Flight::render() correctamente
Flight::map('render', function(string $template, array $data): void {
  // Esto es como $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```

## Blade

Aquí tienes cómo usar el motor de plantillas [Blade](https://laravel.com/docs/8.x/blade) para tus vistas:

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
// También pasar una función de callback para configurar BladeOne al cargar
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Asignar datos a la plantilla
Flight::view()->share('name', 'Bob');

// Mostrar la plantilla
echo Flight::view()->run('hello', []);
```

Para ser completo, también deberías sobrescribir el método render predeterminado de Flight:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

En este ejemplo, el archivo de plantilla hello.blade.php podría verse así:

```php
<?php
¡Hola, {{ $name }}!
```

La salida sería:

```
¡Hola, Bob!
```

Al seguir estos pasos, puedes integrar el motor de plantillas Blade con Flight y usarlo para renderizar tus vistas.