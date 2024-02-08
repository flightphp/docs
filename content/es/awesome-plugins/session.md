# Ghostff/Session

Administrador de Sesiones PHP (no bloqueante, flash, segmento, encriptación de sesiones). Utiliza open_ssl de PHP para encriptar/desencriptar opcionalmente datos de sesión. Admite Archivo, MySQL, Redis y Memcached.

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

// una cosa a tener en cuenta es que debes confirmar tu sesión en cada carga de página
// o necesitarás ejecutar auto_commit en tu configuración.
```

## Ejemplo Sencillo

Aquí tienes un ejemplo sencillo de cómo podrías usar esto.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// realiza tu lógica de inicio de sesión aquí
	// valida la contraseña, etc.

	// si el inicio de sesión es correcto
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

	// realiza tu lógica de página restringida aquí
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

// establece una ruta personalizada a tu archivo de configuración de sesión y dale una cadena aleatoria para el id de sesión
$app->register('session', Session::class, [ 'ruta/a/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// o puedes anular manualmente las opciones de configuración
		$session->updateConfiguration([
			// si deseas almacenar tus datos de sesión en una base de datos (útil si deseas algo como funcionalidad "ciérrame la sesión en todos los dispositivos")
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'mi-super-secreto-salt'), // por favor cambia esto a algo más
			Session::CONFIG_AUTO_COMMIT   => true, // hazlo solo si es necesario y/o es difícil confirmar() tu sesión.
												// adicionalmente podrías hacer Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Controlador de base de datos para PDO dns, ej. (mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Host de la base de datos
				'db_name'   => 'mi_basededatos_app',   # Nombre de la base de datos
				'db_table'  => 'sesiones',          # Tabla de la base de datos
				'db_user'   => 'root',              # Nombre de usuario de la base de datos
				'db_pass'   => '',                  # Contraseña de la base de datos
				'persistent_conn'=> false,          # Evita la sobrecarga de establecer una nueva conexión cada vez que un script necesita comunicarse con una base de datos, lo que resulta en una aplicación web más rápida. BUSCA LA CONTRAPARTE TÚ MISMO
			]
		]);
	}
);
```

## Documentación

Visita el [Github Readme](https://github.com/Ghostff/Session) para ver la documentación completa. Las opciones de configuración están [bien documentadas en el archivo default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) en sí mismo. El código es fácil de entender si deseas revisar este paquete tú mismo.