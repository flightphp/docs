# Vistas y plantillas HTML

## Resumen

Flight proporciona algunas funcionalidades básicas de plantillas HTML por defecto. El uso de plantillas es una forma muy eficaz de desconectar la lógica de tu aplicación de la capa de presentación. Un motor dedicado (Twig, Latte, etc.) también ofrece a las [herramientas de codificación con IA](/learn/ai) una sintaxis familiar y restringida, por lo que es menos probable que vuelquen lógica de negocio en tu HTML.

## Comprensión

Cuando estás creando una aplicación, es probable que tengas HTML que quieras devolver al usuario final. PHP por sí mismo es un lenguaje de plantillas, pero es _muy_ fácil envolver lógica de negocio, como llamadas a bases de datos, llamadas a API, etc., dentro de tu archivo HTML, convirtiendo las pruebas y el desacoplamiento en un proceso muy difícil. Al pasar datos a una plantilla y dejar que la plantilla se renderice por sí misma, resulta mucho más fácil desacoplar y probar unitariamente tu código. ¡Nos lo agradecerás si usas plantillas!

## Uso básico

Flight te permite cambiar el motor de vistas predeterminado simplemente mapeando `render` (o registrando una clase de vista). Desplázate hacia abajo para ver Twig, Latte, Smarty, Blade y más.

> **Predeterminado del esqueleto:** El [flightphp/skeleton](https://github.com/flightphp/skeleton) oficial utiliza **solo Twig** en `app/views/` (`*.twig`). Los controladores llaman a `$this->app->render('welcome', $data)` (la extensión es opcional). Es una elección de aplicación para proyectos nuevos, no un requisito del núcleo de Flight. Latte y otros motores siguen siendo totalmente compatibles.

### Twig

<span class="badge bg-info">predeterminado del esqueleto</span>

[Twig](https://twig.symfony.com/) es un motor de plantillas flexible, rápido y seguro utilizado por Symfony y muchos otros proyectos PHP. Las herramientas de codificación con IA tienden a conocer Twig especialmente bien, y escapa automáticamente la salida por defecto, lo que ayuda a proteger contra XSS.

#### Instalación

```bash
composer require twig/twig
```

(Ya incluido cuando ejecutas `composer create-project flightphp/skeleton`).

#### Configuración básica

Sobrescribe el método `render` para usar Twig en lugar del renderizador PHP predeterminado:

```php
// sobrescribe el método render para usar Twig en lugar del renderizador PHP predeterminado
Flight::map('render', function(string $template, array $data): void {
	$loader = new \Twig\Loader\FilesystemLoader(Flight::get('flight.views.path'));
	$twig = new \Twig\Environment($loader, [
		// Donde Twig almacena sus plantillas compiladas
		'cache' => __DIR__ . '/../cache/twig',
		'auto_reload' => true,
	]);

	// Permite "welcome" o "welcome.twig"
	if (substr($template, -5) !== '.twig') {
		$template .= '.twig';
	}

	echo $twig->render($template, $data);
});
```

En el esqueleto, esta configuración se encuentra en `app/config/services.php` (entorno Twig compartido, ruta de caché, variables globales como `base_url` / nonce CSP). Prefiere inyectar `Engine` y llamar a `$app->render()` desde los controladores para que el código siga siendo [amigable con la IA y las pruebas](/learn/ai).

#### Usando Twig en Flight

Ahora que puedes renderizar con Twig, puedes hacer algo como esto:

```html
{# app/views/home.twig #}
<html>
  <head>
	<title>{% if title %}{{ title }} - {% endif %}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, {{ name }}!</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.twig', [
		'title' => 'Home Page',
		'name' => $name
	]);
});
```

Cuando visites `/Bob` en tu navegador, la salida sería:

```html
<html>
  <head>
	<title>Home Page - My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, Bob!</h1>
  </body>
</html>
```

#### Lectura adicional

En la sección [plugins increíbles](/awesome-plugins/twig) de esta documentación se muestra un ejemplo más completo del uso de Twig con diseños. Para métricas de tiempo de renderizado en la barra de Tracy, consulta el [panel Twig en Tracy Extensions](/awesome-plugins/tracy-extensions#twig-panel-optional).

Puedes aprender más sobre todas las capacidades de Twig leyendo la [documentación oficial](https://twig.symfony.com/doc/3.x/).

### Latte

<span class="badge bg-secondary">gran alternativa</span>

[Latte](https://latte.nette.org/) es un motor completo con una sintaxis similar a PHP. Sigue siendo una excelente opción para aplicaciones Flight; el esqueleto simplemente se estandariza en Twig como único valor predeterminado compartido (especialmente útil cuando las herramientas de IA generan plantillas).

#### Instalación

```bash
composer require latte/latte
```

#### Configuración básica

La idea principal es que sobrescribas el método `render` para usar Latte en lugar del renderizador PHP predeterminado.

```php
// sobrescribe el método render para usar Latte en lugar del renderizador PHP predeterminado
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Donde Latte almacena específicamente su caché
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### Usando Latte en Flight

Ahora que puedes renderizar con Latte, puedes hacer algo como esto:

```html
<!-- app/views/home.latte -->
<html>
  <head>
	<title>{$title ? $title . ' - '}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, {$name}!</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.latte', [
		'title' => 'Home Page',
		'name' => $name
	]);
});
```

Cuando visites `/Bob` en tu navegador, la salida sería:

```html
<html>
  <head>
	<title>Home Page - My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, Bob!</h1>
  </body>
</html>
```

#### Lectura adicional

En la sección [plugins increíbles](/awesome-plugins/latte) de esta documentación se muestra un ejemplo más complejo del uso de Latte con diseños.

Puedes aprender más sobre todas las capacidades de Latte, incluidas las de traducción e idiomas, leyendo la [documentación oficial](https://latte.nette.org/en/).

### Motor de vistas integrado

<span class="badge bg-warning">obsoleto</span>

> **Nota:** Aunque esto sigue siendo la funcionalidad predeterminada y técnicamente aún funciona.

Para mostrar una plantilla de vista, llama al método `render` con el nombre del archivo de plantilla y datos de plantilla opcionales:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Los datos de plantilla que pasas se inyectan automáticamente en la plantilla y se pueden referenciar como una variable local. Los archivos de plantilla son simplemente archivos PHP. Si el contenido del archivo de plantilla `hello.php` es:

```php
Hello, <?= $name ?>!
```

La salida sería:

```text
Hello, Bob!
```

También puedes configurar variables de vista manualmente usando el método `set`:

```php
Flight::view()->set('name', 'Bob');
```

La variable `name` ahora está disponible en todas tus vistas. Así que puedes simplemente hacer:

```php
Flight::render('hello');
```

Ten en cuenta que al especificar el nombre de la plantilla en el método `render`, puedes omitir la extensión `.php`.

Por defecto, Flight buscará un directorio `views` para los archivos de plantilla. Puedes establecer una ruta alternativa para tus plantillas configurando lo siguiente:

```php
Flight::set('flight.views.path', '/path/to/views');
```

Por defecto, la `View` integrada de Flight también acepta una ruta de plantilla absoluta, o un nombre que salga de ese directorio. Para la mayoría de las aplicaciones, deberías restringir esto:

```php
Flight::set('flight.views.restrict_to_path', true);
```

Eso mantiene `render()`, `fetch()` y `exists()` dentro de `flight.views.path`. Está desactivado por defecto para compatibilidad hacia atrás. Consulta [Seguridad](/learn/security#flightviewsrestrict_to_path).

#### Diseños

Es común que los sitios web tengan un único archivo de plantilla de diseño con contenido intercambiable. Para renderizar contenido que se utilizará en un diseño, puedes pasar un parámetro opcional al método `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Tu vista tendrá entonces variables guardadas llamadas `headerContent` y `bodyContent`. Luego puedes renderizar tu diseño haciendo:

```php
Flight::render('layout', ['title' => 'Home Page']);
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
    <title>Home Page</title>
  </head>
  <body>
    <h1>Hello</h1>
    <div>World</div>
  </body>
</html>
```

### Smarty

Así es como usarías el motor de plantillas [Smarty](http://www.smarty.net/) para tus vistas:

```php
// Cargar la librería Smarty
require './Smarty/libs/Smarty.class.php';

// Registrar Smarty como clase de vista
// También pasar una función de callback para configurar Smarty al cargar
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

Para completar, también deberías sobrescribir el método `render` predeterminado de Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

Así es como usarías el motor de plantillas [Blade](https://laravel.com/docs/8.x/blade) para tus vistas:

Primero, necesitas instalar la librería BladeOne mediante Composer:

```bash
composer require eftec/bladeone
```

Luego, puedes configurar BladeOne como clase de vista en Flight:

```php
<?php
// Cargar la librería BladeOne
use eftec\bladeone\BladeOne;

// Registrar BladeOne como clase de vista
// También pasar una función de callback para configurar BladeOne al cargar
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

Para completar, también deberías sobrescribir el método `render` predeterminado de Flight:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

En este ejemplo, el archivo de plantilla `hello.blade.php` podría verse así:

```php
<?php
Hello, {{ $name }}!
```

La salida sería:

```
Hello, Bob!
```

## Véase también

- [Instalación](/install) - Diseño del esqueleto (`app/views/*.twig`) para proyectos nuevos.
- [Extensión](/learn/extending) - Cómo sobrescribir el método `render` para usar un motor de plantillas diferente.
- [Enrutamiento](/learn/routing) - Cómo mapear rutas a controladores y renderizar vistas.
- [Respuestas](/learn/responses) - Cómo personalizar las respuestas HTTP.
- [Seguridad](/learn/security) - Escapado automático, XSS y `flight.views.restrict_to_path`.
- [IA y experiencia de desarrollo](/learn/ai) - Por qué un motor de vistas predeterminado ayuda a los agentes de codificación.
- [¿Por qué un framework?](/learn/why-frameworks) - Cómo encajan las plantillas en el panorama general.

## Solución de problemas

- Si tienes una redirección en tu middleware, pero tu aplicación no parece estar redirigiendo, asegúrate de agregar una sentencia `exit;` en tu middleware.
- Si Twig no puede encontrar una plantilla, verifica `flight.views.path` y que el archivo exista en esa ruta con la extensión esperada (esqueleto: `app/views/`).

## Historial de cambios

- Documentación – Se documentó `flight.views.restrict_to_path` para vistas PHP nativas.
- Documentación – Twig documentado como el valor predeterminado oficial del esqueleto; Latte sigue siendo una alternativa de primera clase.
- v2.0 - Versión inicial.