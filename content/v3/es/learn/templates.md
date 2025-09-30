# Vistas y Plantillas HTML

## Resumen

Flight proporciona funcionalidad básica de plantillas HTML por defecto. El templado es una forma muy efectiva para que desconectes la lógica de tu aplicación de la capa de presentación.

## Comprensión

Cuando estás construyendo una aplicación, probablemente tendrás HTML que querrás entregar de vuelta al usuario final. PHP por sí solo es un lenguaje de plantillas, pero es _muy_ fácil envolver lógica de negocio como llamadas a bases de datos, llamadas a API, etc., en tu archivo HTML y hacer que las pruebas y el desacoplamiento sean un proceso muy difícil. Al empujar datos a una plantilla y dejar que la plantilla se renderice a sí misma, se vuelve mucho más fácil desacoplar y realizar pruebas unitarias en tu código. ¡Nos lo agradecerás si usas plantillas!

## Uso Básico

Flight te permite intercambiar el motor de vistas predeterminado simplemente registrando tu propia clase de vista. ¡Desplázate hacia abajo para ver ejemplos de cómo usar Smarty, Latte, Blade y más!

### Latte

<span class="badge bg-info">recomendado</span>

Aquí te explico cómo usar el motor de plantillas [Latte](https://latte.nette.org/) para tus vistas.

#### Instalación

```bash
composer require latte/latte
```

#### Configuración Básica

La idea principal es que sobrescribas el método `render` para usar Latte en lugar del renderizador PHP predeterminado.

```php
// sobrescribe el método render para usar latte en lugar del renderizador PHP predeterminado
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Dónde Latte almacena específicamente su caché
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### Usar Latte en Flight

Ahora que puedes renderizar con Latte, puedes hacer algo como esto:

```html
<!-- app/views/home.latte -->
<html>
  <head>
	<title>{$title ? $title . ' - '}Mi App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>¡Hola, {$name}!</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.latte', [
		'title' => 'Página de Inicio',
		'name' => $name
	]);
});
```

Cuando visites `/Bob` en tu navegador, la salida sería:

```html
<html>
  <head>
	<title>Página de Inicio - Mi App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>¡Hola, Bob!</h1>
  </body>
</html>
```

#### Lectura Adicional

Un ejemplo más complejo de usar Latte con layouts se muestra en la sección de [plugins increíbles](/awesome-plugins/latte) de esta documentación.

Puedes aprender más sobre las capacidades completas de Latte, incluyendo traducción y capacidades de idioma, leyendo la [documentación oficial](https://latte.nette.org/en/).

### Motor de Vistas Integrado

<span class="badge bg-warning">deprecado</span>

> **Nota:** Aunque esta sigue siendo la funcionalidad predeterminada y técnicamente aún funciona.

Para mostrar una plantilla de vista, llama al método `render` con el nombre del archivo de plantilla y datos de plantilla opcionales:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Los datos de plantilla que pasas se inyectan automáticamente en la plantilla y pueden referenciarse como una variable local. Los archivos de plantilla son simplemente archivos PHP. Si el contenido del archivo de plantilla `hello.php` es:

```php
¡Hola, <?= $name ?>!
```

La salida sería:

```text
¡Hola, Bob!
```

También puedes establecer variables de vista manualmente usando el método set:

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

#### Layouts

Es común que los sitios web tengan un solo archivo de plantilla de layout con contenido intercambiable. Para renderizar contenido que se use en un layout, puedes pasar un parámetro opcional al método `render`.

```php
Flight::render('header', ['heading' => 'Hola'], 'headerContent');
Flight::render('body', ['body' => 'Mundo'], 'bodyContent');
```

Tu vista entonces tendrá variables guardadas llamadas `headerContent` y `bodyContent`. Puedes renderizar tu layout haciendo:

```php
Flight::render('layout', ['title' => 'Página de Inicio']);
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
    <title>Página de Inicio</title>
  </head>
  <body>
    <h1>Hola</h1>
    <div>Mundo</div>
  </body>
</html>
```

### Smarty

Aquí te explico cómo usar el motor de plantillas [Smarty](http://www.smarty.net/) para tus vistas:

```php
// Cargar la biblioteca Smarty
require './Smarty/libs/Smarty.class.php';

// Registrar Smarty como la clase de vista
// También pasar una función de devolución de llamada para configurar Smarty al cargar
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

Para mayor completitud, también deberías sobrescribir el método render predeterminado de Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

Aquí te explico cómo usar el motor de plantillas [Blade](https://laravel.com/docs/8.x/blade) para tus vistas:

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
// También pasar una función de devolución de llamada para configurar BladeOne al cargar
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

Para mayor completitud, también deberías sobrescribir el método render predeterminado de Flight:

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

## Ver También
- [Extensión](/learn/extending) - Cómo sobrescribir el método `render` para usar un motor de plantillas diferente.
- [Enrutamiento](/learn/routing) - Cómo mapear rutas a controladores y renderizar vistas.
- [Respuestas](/learn/responses) - Cómo personalizar respuestas HTTP.
- [¿Por qué un Framework?](/learn/why-frameworks) - Cómo encajan las plantillas en el panorama general.

## Solución de Problemas
- Si tienes una redirección en tu middleware, pero tu app no parece estar redirigiendo, asegúrate de agregar una instrucción `exit;` en tu middleware.

## Registro de Cambios
- v2.0 - Lanzamiento inicial.