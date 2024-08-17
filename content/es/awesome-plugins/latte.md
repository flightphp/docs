# Latte

[Latte](https://latte.nette.org/en/guide) es un motor de plantillas completo que es muy fácil de usar y se siente más cercano a una sintaxis de PHP que Twig o Smarty. También es muy fácil de extender y agregar tus propios filtros y funciones.

## Instalación

Instalar con composer.

```bash
composer require latte/latte
```

## Configuración Básica

Hay algunas opciones de configuración básicas para comenzar. Puedes leer más sobre ellas en la [Documentación de Latte](https://latte.nette.org/en/guide).

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// Aquí es donde Latte almacenará en caché tus plantillas para acelerar las cosas
	// ¡Una cosa genial sobre Latte es que actualiza automáticamente tu caché
	// cuando realizas cambios en tus plantillas!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Indica a Latte dónde estará el directorio raíz de tus vistas.
	// $app->get('flight.views.path') se establece en el archivo config.php
	// También podrías hacer algo como `__DIR__ . '/../views/'`
	$latte->setLoader(new \Latte\Loaders\FileLoader($app->get('flight.views.path')));
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
			&copy; Derechos de Autor
		</div>
	</body>
</html>
```

Y ahora tenemos tu archivo que se va a renderizar dentro de ese bloque de contenido:

```html
<!-- app/views/home.latte -->
<!-- Esto le dice a Latte que este archivo está "dentro" del archivo layout.latte -->
{extends layout.latte}

<!-- Este es el contenido que se renderizará dentro del diseño dentro del bloque de contenido -->
{block content}
	<h1>Página de Inicio</h1>
	<p>¡Bienvenido a mi app!</p>
{/block}
```

Luego, cuando vayas a renderizar esto dentro de tu función o controlador, harías algo así:

```php
// ruta simple
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
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
		Flight::latte()->render('home.latte', [
			'title' => 'Página de Inicio'
		]);
	}
}
```

¡Consulta la [Documentación de Latte](https://latte.nette.org/en/guide) para obtener más información sobre cómo utilizar Latte al máximo!