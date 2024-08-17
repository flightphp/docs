# Ghostff/Session

Gestor de Sesiones PHP (no bloqueante, flash, segmento, cifrado de sesiones). Utiliza PHP open_ssl para cifrar/descifrar datos de sesión de forma opcional. Admite Archivo, MySQL, Redis y Memcached.

Haga clic [aquí](https://github.com/Ghostff/Session) para ver el código.

## Instalación

Instalar con composer.

```bash
composer require ghostff/session
```

## Configuración Básica

No es necesario pasar nada para usar la configuración predeterminada con tu sesión. Puedes leer más sobre la configuración en el [README de Github](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// una cosa a tener en cuenta es que debes confirmar tu sesión en cada carga de página
// o necesitarás ejecutar auto_commit en tu configuración.
```

## Ejemplo Simple

Aquí tienes un ejemplo simple de cómo podrías usar esto.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// realiza tu lógica de inicio de sesión aquí
	// valídalo contraseña, etc.

	// si el inicio de sesión es exitoso
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// cada vez que escribas en la sesión, debes confirmarla deliberadamente.
	$session->commit();
});

// Esta verificación podría estar en la lógica de la página restringida, o envuelta con un middleware.
Flight::route('/alguna-página-restringida', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/inicio');
	}

	// realiza tu lógica de página restringida aquí
});

// la versión de middleware
Flight::route('/alguna-página-restringida', function() {
	// lógica regular de la página
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/inicio');
	}
});
```

## Ejemplo Más Complejo

Aquí tienes un ejemplo más complejo de cómo podrías usar esto.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// establece una ruta personalizada a tu archivo de configuración de sesión y dale una cadena aleatoria para la ID de sesión
$app->register('session', Session::class, [ 'ruta/a/sesion_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// o puedes anular manualmente las opciones de configuración
		$session->updateConfiguration([
			// si deseas almacenar tus datos de sesión en una base de datos (bueno si deseas algo como, "cierra la sesión en todos los dispositivos" funcionalidad)
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'mi-super-s3cret-salt'), // por favor cambia esto a algo diferente
			Session::CONFIG_AUTO_COMMIT   => true, // solo hazlo si es necesario y/o es difícil confirmar() tu sesión.
												   // además podrías hacer Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Controlador de base de datos para dsn de PDO ej.(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Host de la base de datos
				'db_name'   => 'mi_base_de_datos_app',   # Nombre de la base de datos
				'db_table'  => 'sesiones',          # Tabla de la base de datos
				'db_user'   => 'root',              # Nombre de usuario de la base de datos
				'db_pass'   => '',                  # Contraseña de la base de datos
				'persistent_conn'=> false,          # Evitar la carga de establecer una nueva conexión cada vez que un script necesita hablar con una base de datos, lo que resulta en una aplicación web más rápida. BUSCA LA DESVENTAJA TÚ MISMO
			]
		]);
	}
);
```

## ¡Ayuda! ¡Mis Datos de Sesión no Persisten!

¿Estás configurando tus datos de sesión y no persisten entre peticiones? Podrías haber olvidado confirmar tus datos de sesión. Puedes hacer esto llamando a `$session->commit()` después de haber establecido tus datos de sesión.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// realiza tu lógica de inicio de sesión aquí
	// valídalo contraseña, etc.

	// si el inicio de sesión es exitoso
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// cada vez que escribas en la sesión, debes confirmarla deliberadamente.
	$session->commit();
});
```

La otra forma de solucionar esto es cuando configuras tu servicio de sesión, debes establecer `auto_commit` en `true` en tu configuración. Esto confirmará automáticamente tus datos de sesión después de cada petición.

```php

$app->register('session', Session::class, [ 'ruta/a/sesion_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Además podrías hacer `Flight::after('start', function() { Flight::session()->commit(); });` para confirmar tus datos de sesión después de cada petición.

## Documentación

Visita el [README de Github](https://github.com/Ghostff/Session) para ver la documentación completa. Las opciones de configuración están [bien documentadas en el archivo default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) en sí. El código es fácil de entender si deseas examinar este paquete por ti mismo.

```