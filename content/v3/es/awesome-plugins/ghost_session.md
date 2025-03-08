# Ghostff/Session

Gestor de Sesiones PHP (no bloqueante, flash, segmento, cifrado de sesión). Utiliza PHP open_ssl para la cifrado/descifrado opcional de los datos de la sesión. Soporta Archivos, MySQL, Redis y Memcached.

Haz clic [aquí](https://github.com/Ghostff/Session) para ver el código.

## Instalación

Instala con composer.

```bash
composer require ghostff/session
```

## Configuración Básica

No se requiere pasar nada para usar la configuración predeterminada con tu sesión. Puedes leer sobre más configuraciones en el [Github Readme](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// una cosa a recordar es que debes confirmar tu sesión en cada carga de página
// o necesitarás ejecutar auto_commit en tu configuración. 
```

## Ejemplo Sencillo

Aquí hay un ejemplo simple de cómo podrías usar esto.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// realiza tu lógica de inicio de sesión aquí
	// valida la contraseña, etc.

	// si el inicio de sesión es exitoso
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// cada vez que escribas en la sesión, debes confirmarlo deliberadamente.
	$session->commit();
});

// Esta verificación podría estar en la lógica de la página restringida, o envuelta en middleware.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// realiza tu lógica de página restringida aquí
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

// establecer una ruta personalizada para tu archivo de configuración de sesión y darle una cadena aleatoria para el id de sesión
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// o puedes sobreescribir manualmente las opciones de configuración
		$session->updateConfiguration([
			// si deseas almacenar tus datos de sesión en una base de datos (bueno si quieres algo como, "desconéctame de todos los dispositivos" funcionalidad)
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // por favor cambia esto a algo diferente
			Session::CONFIG_AUTO_COMMIT   => true, // haz esto solo si lo requiere y/o es difícil confirmarlo
												   // adicionalmente podrías hacer Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Controlador de base de datos para dns de PDO eg(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Host de base de datos
				'db_name'   => 'my_app_database',   # Nombre de la base de datos
				'db_table'  => 'sessions',          # Tabla de base de datos
				'db_user'   => 'root',              # Nombre de usuario de la base de datos
				'db_pass'   => '',                  # Contraseña de la base de datos
				'persistent_conn'=> false,          # Evita sobrecostos de establecer una nueva conexión cada vez que un script necesita comunicarse con una base de datos, resultando en una aplicación web más rápida. ENCUENTRA EL REVERSO TÚ MISMO
			]
		]);
	}
);
```

## ¡Ayuda! ¡Mis Datos de Sesión No Están Persistiendo!

¿Estás estableciendo tus datos de sesión y no están persistiendo entre solicitudes? Puede que hayas olvidado confirmar tus datos de sesión. Puedes hacerlo llamando a `$session->commit()` después de haber establecido tus datos de sesión.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// realiza tu lógica de inicio de sesión aquí
	// valida la contraseña, etc.

	// si el inicio de sesión es exitoso
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// cada vez que escribas en la sesión, debes confirmarlo deliberadamente.
	$session->commit();
});
```

La otra forma de solucionar esto es cuando configuras tu servicio de sesión, debes establecer `auto_commit` en `true` en tu configuración. Esto confirmará automáticamente tus datos de sesión después de cada solicitud.

```php

$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Adicionalmente podrías hacer `Flight::after('start', function() { Flight::session()->commit(); });` para confirmar tus datos de sesión después de cada solicitud.

## Documentación

Visita el [Github Readme](https://github.com/Ghostff/Session) para la documentación completa. Las opciones de configuración están [bien documentadas en el archivo default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php). El código es simple de entender si deseas revisar este paquete por ti mismo.