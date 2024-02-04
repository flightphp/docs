# Ghostff/Session

Administrador de Sesiones PHP (no bloqueante, flash, segmento, encriptación de sesión). Utiliza open_ssl de PHP para encriptar/desencriptar opcionalmente los datos de sesión. Admite Archivo, MySQL, Redis y Memcached.

## Instalación

Instala con composer.

```bash
composer require ghostff/session
```

## Configuración Básica

No es necesario pasar nada para usar la configuración predeterminada con tu sesión. Puedes leer sobre más configuraciones en el [Leeme de Github](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// una cosa a recordar es que debes confirmar tu sesión en cada carga de página
// o necesitarás ejecutar auto_commit en tu configuración.
```

## Ejemplo Simple

Aquí tienes un ejemplo simple de cómo podrías usar esto.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// haz tu lógica de inicio de sesión aquí
	// valida la contraseña, etc.

	// si el inicio de sesión es exitoso
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// cada vez que escribas en la sesión, debes confirmarlo deliberadamente.
	$session->commit();
});

// Esta comprobación podría estar en la lógica de la página restringida, o envuelta con middleware.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// haz tu lógica de la página restringida aquí
});

// la versión de middleware
Flight::route('/some-restricted-page', function() {
	// lógica de página regular
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## Ejemplo Más Complejo

Aquí tienes un ejemplo más complejo de cómo podrías usar esto.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// establece una ruta personalizada a tu archivo de configuración de sesión y asigna una cadena aleatoria para el id de sesión
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// o también puedes anular manualmente las opciones de configuración
		$session->updateConfiguration([
			// si quieres almacenar tus datos de sesión en una base de datos (útil si quieres algo así como funcionalidad de "cerrar sesión en todos los dispositivos")
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'mi-súper-clave-secreta'), // por favor cambia esto a otra cosa
			Session::CONFIG_AUTO_COMMIT   => true, // haz esto solo si es necesario y/o es difícil confirmar() tu sesión.
												// adicionalmente podrías hacer Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Controlador de base de datos para la dns de PDO ej(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Anfitrión de la base de datos
				'db_name'   => 'mi_base_de_datos_aplicación',   # Nombre de la base de datos
				'db_table'  => 'sesiones',          # Tabla de la base de datos
				'db_user'   => 'root',              # Nombre de usuario de la base de datos
				'db_pass'   => '',                  # Contraseña de base de datos
				'persistent_conn'=> false,          # Evita la sobrecarga de establecer una nueva conexión cada vez que un script necesite comunicarse con una base de datos, resultando en una aplicación web más rápida. ENCUENTRA EL LADO TRASERO TÚ MISMO
			]
		]);
	}
);
```

## Documentación

Visita el [Leeme de Github](https://github.com/Ghostff/Session) para ver la documentación completa. Las opciones de configuración están [bien documentadas en el archivo default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) en sí mismo. El código es fácil de entender si quisieras estudiar este paquete tú mismo.