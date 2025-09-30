# Latte

[Latte](https://latte.nette.org/en/guide) es un motor de plantillas completo que es muy fácil de usar y se siente más cercano a la sintaxis de PHP que Twig o Smarty. También es muy fácil de extender y agregar tus propios filtros y funciones.

## Instalación

Instala con composer.

```bash
composer require latte/latte
```

## Configuración Básica

Hay algunas opciones de configuración básicas para comenzar. Puedes leer más sobre ellas en la [Documentación de Latte](https://latte.nette.org/en/guide).

```php

require 'vendor/autoload.php';

$app = Flight::app();

$app->map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Dónde Latte almacena específicamente su caché
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

## Ejemplo Simple de Diseño

Aquí hay un ejemplo simple de un archivo de diseño. Este es el archivo que se usará para envolver todas tus otras vistas.

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="en">
	<head>
		<title>{$title ? $title . ' - '}Mi App</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<header>
			<nav>
				<!-- tus elementos de navegación aquí -->
			</nav>
		</header>
		<div id="content">
			<!-- Aquí está la magia -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Copyright
		</div>
	</body>
</html>
```

Y ahora tenemos tu archivo que se va a renderizar dentro de ese bloque de contenido:

```html
<!-- app/views/home.latte -->
<!-- Esto le dice a Latte que este archivo está "dentro" del archivo layout.latte -->
{extends layout.latte}

<!-- Este es el contenido que se renderizará dentro del diseño en el bloque de contenido -->
{block content}
	<h1>Página de Inicio</h1>
	<p>¡Bienvenido a mi app!</p>
{/block}
```

Luego, cuando vayas a renderizar esto dentro de tu función o controlador, harías algo como esto:

```php
// ruta simple
Flight::route('/', function () {
	Flight::render('home.latte', [
		'title' => 'Página de Inicio'
	]);
});

// o si estás usando un controlador
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::render('home.latte', [
			'title' => 'Página de Inicio'
		]);
	}
}
```

¡Consulta la [Documentación de Latte](https://latte.nette.org/en/guide) para obtener más información sobre cómo usar Latte a su máximo potencial!

## Depuración con Tracy

_Se requiere PHP 8.1+ para esta sección._

¡También puedes usar [Tracy](https://tracy.nette.org/en/) para ayudar con la depuración de tus archivos de plantillas Latte directamente de la caja! Si ya tienes Tracy instalado, necesitas agregar la extensión de Latte a Tracy.

```php
// services.php
use Tracy\Debugger;

$app->map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Dónde Latte almacena específicamente su caché
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	// Esto solo agregará la extensión si la Barra de Depuración de Tracy está habilitada
	if (Debugger::$showBar === true) {
		// aquí es donde agregas el Panel de Latte a Tracy
		$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
	}
	$latte->render($finalPath, $data, $block);
});
```