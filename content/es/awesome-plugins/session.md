# Ghostff/Session

Administrador de Sesión PHP (no bloqueante, flash, segmento, cifrado de sesión). Utiliza PHP open_ssl para cifrar/descifrar opcionalmente los datos de la sesión. Admite Archivo, MySQL, Redis y Memcached.

## Instalación

Instalar con composer.

```bash
composer require ghostff/session
```

## Configuración Básica

No es necesario pasar nada para usar la configuración predeterminada con tu sesión. Puedes leer acerca de más configuraciones en el [Github Readme](https://github.com/Ghostff/Session).

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

	// cada vez que escribas en la sesión, debes confirmarla deliberadamente.
	$session->commit();
});

// Esta verificación podría estar en la lógica de la página restringida, o envuelta con middleware.
Flight::route('/algunapagina-restringida', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// haz tu lógica de la página restringida aquí
});

// la versión del middleware
Flight::route('/algunapagina-restringida', function() {
	// lógica de página regular
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## Ejemplo más Complejo

Aquí tienes un ejemplo más complejo de cómo podrías usar esto.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// establece una ruta personalizada a tu archivo de configuración de sesión y dale una cadena aleatoria para el id de sesión
$app->register('session', Session::class, [ 'ruta/hacia/configuracion_sesion.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// o puedes anular manualmente las opciones de configuración
		$session->updateConfiguration([
			// si quieres almacenar tus datos de sesión en una base de datos (útil si deseas algo como funcionalidad de "cerrar sesión en todos los dispositivos")
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'mi-super-s4l-secret4'), // por favor cambia esto a algo diferente
			Session::CONFIG_AUTO_COMMIT   => true, // solo haz esto si es necesario y/o es difícil confirmar() tu sesión.
												// adicionalmente podrías hacer Flight::after('start', function() { Flight::session()->confirm(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Controlador de base de datos para dns de PDO ej(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Host de base de datos
				'db_name'   => 'mi_basededatos_app',   # Nombre de la base de datos
				'db_table'  => 'sesiones',          # Tabla de base de datos
				'db_user'   => 'root',              # Usuario de base de datos
				'db_pass'   => '',                  # Contraseña de base de datos
				'persistent_conn'=> false,          # Evita la sobrecarga de establecer una nueva conexión cada vez que un script necesita hablar con una base de datos, lo que resulta en una aplicación web más rápida. ENCUENTRA EL LADO OSCURO TÚ MISMO
			]
		]);
	}
);
```

## Documentación

Visita el [Github Readme](https://github.com/Ghostff/Session) para obtener la documentación completa. Las opciones de configuración están [bien documentadas en el archivo default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) en sí mismo. El código es fácil de entender si quisieras ver este paquete por tu cuenta.