# Cookies

[overclokk/cookie](https://github.com/overclokk/cookie) es una biblioteca sencilla para administrar cookies dentro de su aplicación.

## Instalación

La instalación es sencilla con composer.

```bash
composer require overclokk/cookie
```

## Uso

El uso es tan simple como registrar un nuevo método en la clase Flight.

```php
use Overclokk\Cookie\Cookie;

/*
 * Establezca en su archivo bootstrap o public/index.php
 */

Flight::register('cookie', Cookie::class);

/**
 * ExampleController.php
 */

class ExampleController {
	public function login() {
		// Establecer una cookie

		// querrás que esto sea falso para obtener una nueva instancia
		// usa el comentario a continuación si deseas el autocompletado
		/** @var \Overclokk\Cookie\Cookie $cookie */
		$cookie = Flight::cookie(false);
		$cookie->set(
			'stay_logged_in', // nombre de la cookie
			'1', // el valor que deseas establecer
			86400, // número de segundos que la cookie debe durar
			'/', // ruta en la que estará disponible la cookie
			'example.com', // dominio en el que estará disponible la cookie
			true, // la cookie solo se transmitirá a través de una conexión segura HTTPS
			true // la cookie solo estará disponible a través del protocolo HTTP
		);

		// opcionalmente, si deseas mantener los valores predeterminados
		// y tener una forma rápida de establecer una cookie por mucho tiempo
		$cookie->forever('stay_logged_in', '1');
	}

	public function home() {
		// Verifica si tienes la cookie
		if (Flight::cookie()->has('stay_logged_in')) {
			// ponlos en el área del panel, por ejemplo.
			Flight::redirect('/dashboard');
		}
	}
}