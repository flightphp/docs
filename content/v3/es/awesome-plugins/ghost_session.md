# Ghostff/Session

Administrador de sesiones PHP (no bloqueante, flash, segment, encriptación de sesión). Usa PHP open_ssl para encriptación/desencriptación opcional de datos de sesión. Admite File, MySQL, Redis y Memcached.

Haz clic [aquí](https://github.com/Ghostff/Session) para ver el código.

## Instalación

Instala con composer.

```bash
composer require ghostff/session
```

## Configuración Básica

No es necesario pasar nada para usar la configuración predeterminada con tu sesión. Puedes leer sobre más configuraciones en el [Github Readme](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// una cosa a recordar es que debes confirmar tu sesión en cada carga de página
// o tendrás que ejecutar auto_commit en tu configuración. 
```

## Ejemplo Simple

Aquí hay un ejemplo simple de cómo podrías usar esto.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// haz tu lógica de inicio de sesión aquí
	// valida la contraseña, etc.

	// si el inicio de sesión es exitoso
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// cualquier vez que escribas en la sesión, debes confirmarla deliberadamente.
	$session->commit();
});

// Esta verificación podría estar en la lógica de la página restringida, o envuelta con middleware.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// haz tu lógica de página restringida aquí
});

// la versión con middleware
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

Aquí hay un ejemplo más complejo de cómo podrías usar esto.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// establece una ruta personalizada a tu archivo de configuración de sesión como el primer argumento
// o dale el arreglo personalizado
$app->register('session', Session::class, [ 
	[
		// si quieres almacenar tus datos de sesión en una base de datos (bueno si quieres algo como, "cerrar sesión en todos los dispositivos" funcionalidad)
		Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
		Session::CONFIG_ENCRYPT_DATA  => true,
		Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // por favor cambia esto a algo más
		Session::CONFIG_AUTO_COMMIT   => true, // solo haz esto si es necesario y/o es difícil de confirmar() tu sesión.
												// adicionalmente podrías hacer Flight::after('start', function() { Flight::session()->commit(); });
		Session::CONFIG_MYSQL_DS         => [
			'driver'    => 'mysql',             # Controlador de base de datos para PDO dns ej(mysql:host=...;dbname=...)
			'host'      => '127.0.0.1',         # Host de la base de datos
			'db_name'   => 'my_app_database',   # Nombre de la base de datos
			'db_table'  => 'sessions',          # Tabla de la base de datos
			'db_user'   => 'root',              # Nombre de usuario de la base de datos
			'db_pass'   => '',                  # Contraseña de la base de datos
			'persistent_conn'=> false,          # Evita el costo de establecer una nueva conexión cada vez que un script necesita hablar con una base de datos, lo que resulta en una aplicación web más rápida. ENCUENTRA EL LADO NEGATIVO TÚ MISMO
		]
	] 
]);
```

## ¡Ayuda! ¡Mis Datos de Sesión No Se Están Manteniendo!

¿Estás estableciendo tus datos de sesión y no se mantienen entre solicitudes? Quizás olvidaste confirmar tus datos de sesión. Puedes hacer esto llamando a `$session->commit()` después de haber establecido tus datos de sesión.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// haz tu lógica de inicio de sesión aquí
	// valida la contraseña, etc.

	// si el inicio de sesión es exitoso
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// cualquier vez que escribas en la sesión, debes confirmarla deliberadamente.
	$session->commit();
});
```

La otra forma de manejar esto es cuando configuras tu servicio de sesión, debes establecer `auto_commit` en `true` en tu configuración. Esto confirmará automáticamente tus datos de sesión después de cada solicitud.

```php

$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Adicionalmente, podrías hacer `Flight::after('start', function() { Flight::session()->commit(); });` para confirmar tus datos de sesión después de cada solicitud.

## Documentación

Visita el [Github Readme](https://github.com/Ghostff/Session) para la documentación completa. Las opciones de configuración están [bien documentadas en el archivo default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) mismo. El código es simple de entender si quieres explorar este paquete tú mismo.