# Ghostff/Session

Administrador de Sesiones PHP (sin bloqueo, flash, segmento, cifrado de sesión). Utiliza PHP open_ssl para cifrar/descifrar opcionalmente los datos de sesión. Admite File, MySQL, Redis y Memcached.

## Instalación

Instalar con composer.

```bash
composer require ghostff/session
```

## Configuración Básica

No es necesario pasar nada para usar la configuración predeterminada con su sesión. Puede leer sobre más configuraciones en el [Readme de Github](https://github.com/Ghostff/Session).

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

// Esta verificación podría estar en la lógica de la página restringida, o envuelta con middleware.
Flight::route('/alguna-página-restringida', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// haz tu lógica de página restringida aquí
});

// la versión del middleware
Flight::route('/alguna-página-restringida', function() {
	// lógica regular de la página
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

// establece una ruta personalizada a tu archivo de configuración de sesión y dale una cadena aleatoria para el id de sesión
$app->register('session', Session::class, [ 'ruta/hacia/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// o puedes anular manualmente las opciones de configuración
		$session->updateConfiguration([
			// si deseas almacenar tus datos de sesión en una base de datos (útil si deseas algo como funcionalidad de "cerrar sesión en todos los dispositivos")
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'mi-súper-S3GR3T-salt'), // cambia esto a algo diferente
			Session::CONFIG_AUTO_COMMIT   => true, // haz esto solo si es necesario y/o es difícil confirmar() tu sesión.
												   // además podrías hacer Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Controlador de base de datos para PDO dns ej(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Host de la base de datos
				'db_name'   => 'mi_base_de_datos_app',   # Nombre de la base de datos
				'db_table'  => 'sesiones',          # Tabla de la base de datos
				'db_user'   => 'root',              # Nombre de usuario de la base de datos
				'db_pass'   => '',                  # Contraseña de la base de datos
				'persistent_conn'=> false,          # Evita el costo de establecer una nueva conexión cada vez que un script necesita comunicarse con una base de datos, lo que resulta en una aplicación web más rápida. ENCUENTRA EL LADO NEGATIVO TÚ MISMO
			]
		]);
	}
);
```

## ¡Ayuda! ¡Mis Datos de Sesión no se Están Manteniendo!

¿Estás estableciendo tus datos de sesión y no se mantienen entre solicitudes? Es posible que hayas olvidado confirmar tus datos de sesión. Puedes hacer esto llamando a `$session->commit()` después de haber establecido tus datos de sesión.

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
```

La otra forma de solucionar esto es cuando configuras tu servicio de sesión, debes establecer `auto_commit` en `true` en tu configuración. Esto confirmará automáticamente tus datos de sesión después de cada solicitud.

```php

$app->register('session', Session::class, [ 'ruta/hacia/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Además podrías hacer `Flight::after('start', function() { Flight::session()->commit(); });` para confirmar tus datos de sesión después de cada solicitud.

## Documentación

Visita el [Readme de Github](https://github.com/Ghostff/Session) para ver la documentación completa. Las opciones de configuración están [bien documentadas en default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) en sí. El código es fácil de entender si deseas inspeccionar este paquete tú mismo.