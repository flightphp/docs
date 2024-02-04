# Latte

Latte es un motor de plantillas completo que es muy fácil de usar y se siente más cercano a una sintaxis PHP que Twig o Smarty. También es muy fácil de ampliar y agregar sus propios filtros y funciones.

## Instalación

Instale con composer.

```bash
composer require latte/latte
```

## Configuración Básica

Hay algunas opciones de configuración básicas para comenzar. Puede obtener más información sobre ellas en la [Documentación de Latte](https://latte.nette.org/es/guía).

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// Aquí es donde Latte almacenará en caché sus plantillas para acelerar las cosas
	// ¡Una cosa interesante sobre Latte es que actualiza automáticamente su
	// caché cuando realiza cambios en sus plantillas!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Dígale a Latte dónde estará el directorio raíz de sus vistas.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});
```

## Ejemplo de Diseño Simple

Aquí hay un ejemplo simple de un archivo de diseño. Este es el archivo que se utilizará para envolver todas sus otras vistas.

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
			<!-- Aquí es donde está la magia -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Derechos de autor
		</div>
	</body>
</html>
```

Y ahora tenemos su archivo que se va a mostrar dentro de ese bloque de contenido:

```html
<!-- app/views/home.latte -->
<!-- Esto le dice a Latte que este archivo está "dentro" del archivo layout.latte -->
{extends layout.latte}

<!-- Este es el contenido que se renderizará dentro del diseño dentro del bloque de contenido -->
{block content}
	<h1>Página de inicio</h1>
	<p>¡Bienvenido a mi aplicación!</p>
{/block}
```

Luego, cuando vaya a renderizar esto dentro de su función o controlador, haría algo así:

```php
// ruta simple
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => 'Página de inicio'
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
			'title' => 'Página de inicio'
		]);
	}
}
```

¡Consulte la [Documentación de Latte](https://latte.nette.org/es/guía) para obtener más información sobre cómo utilizar Latte al máximo potencial!