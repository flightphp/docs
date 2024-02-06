## Latte

Latte is a full featured templating engine that is very easy to use and feels closer to a PHP syntax than Twig or Smarty. It's also very easy to extend and add your own filters and functions.

## Instalación

Instala con composer.

```bash
composer require latte/latte
```

## Configuración Básica

Hay algunas opciones de configuración básicas para empezar. Puedes leer más sobre ellas en la [Documentación de Latte](https://latte.nette.org/es/guia).

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// Aquí es donde Latte almacenará la caché de tus plantillas para acelerar las cosas
	// ¡Una cosa genial sobre Latte es que actualiza automáticamente tu caché
	// cuando realizas cambios en tus plantillas!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Indícale a Latte dónde estará ubicado el directorio raíz para tus vistas.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});
```

## Ejemplo de Diseño Simple

Aquí tienes un ejemplo simple de un archivo de diseño. Este es el archivo que se utilizará para envolver todas tus otras vistas.

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="es">
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
			&copy; Derechos de autor
		</div>
	</body>
</html>
```

Y ahora tenemos tu archivo que se va a renderizar dentro de ese bloque de contenido:

```html
<!-- app/views/home.latte -->
<!-- Esto le indica a Latte que este archivo está "dentro" del archivo layout.latte -->
{extends layout.latte}

<!-- Este es el contenido que se renderizará dentro del diseño dentro del bloque de contenido -->
{block content}
	<h1>Página de Inicio</h1>
	<p>¡Bienvenido a mi aplicación!</p>
{/block}
```

Entonces, cuando vayas a renderizar esto dentro de tu función o controlador, deberías hacer algo como esto:

```php
// ruta simple
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => 'Página de Inicio'
	]);
});

// o si estás utilizando un controlador
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::latte()->render('home.latte', [
			'title' => 'Página de Inicio'
		]);
	}
}
```

¡Consulta la [Documentación de Latte](https://latte.nette.org/es/guia) para obtener más información sobre cómo utilizar Latte al máximo!