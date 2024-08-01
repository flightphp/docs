# Latte

Latte es un motor de plantillas completo que es muy fácil de usar y se siente más cercano a una sintaxis de PHP que Twig o Smarty. También es muy fácil de extender y agregar sus propios filtros y funciones.

## Instalación

Instalar con composer.

```bash
composer require latte/latte
```

## Configuración Básica

Hay algunas opciones de configuración básicas para empezar. Puede leer más sobre ellas en la [Documentación de Latte](https://latte.nette.org/en/guide).

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// Aquí es donde Latte almacenará en caché sus plantillas para acelerar las cosas
	// ¡Una cosa genial acerca de Latte es que actualiza automáticamente su caché
	// cuando realiza cambios en sus plantillas!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Indique a Latte cuál será el directorio raíz para sus vistas.
	// $app->get('flight.views.path') se establece en el archivo config.php
	//   También podría simplemente hacer algo como `__DIR__ . '/../views/'`
	$latte->setLoader(new \Latte\Loaders\FileLoader($app->get('flight.views.path')));
});
```

## Ejemplo de Diseño Simple

Aquí tienes un ejemplo simple de un archivo de diseño. Este es el archivo que se usará para envolver todas sus otras vistas.

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="es">
	<head>
		<title>{$title ? $title . ' - '}Mi Aplicación</title>
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

Y ahora tenemos su archivo que se va a renderizar dentro de ese bloque de contenido:

```html
<!-- app/views/home.latte -->
<!-- Esto le dice a Latte que este archivo está "dentro" del archivo layout.latte -->
{extends layout.latte}

<!-- Este es el contenido que se renderizará dentro del diseño dentro del bloque de contenido -->
{block content}
	<h1>Página de Inicio</h1>
	<p>¡Bienvenido a mi aplicación!</p>
{/block}
```

Entonces, cuando vaya a renderizar esto dentro de su función o controlador, haría algo así:

```php
// ruta simple
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => 'Página de Inicio'
	]);
});

// o si está utilizando un controlador
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

¡Consulte la [Documentación de Latte](https://latte.nette.org/en/guide) para obtener más información sobre cómo utilizar Latte en su máximo potencial!