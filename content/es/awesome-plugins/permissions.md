# FlightPHP/Permisos

Este es un módulo de permisos que se puede utilizar en tus proyectos si tienes múltiples roles en tu aplicación y cada rol tiene una funcionalidad un poco diferente. Este módulo te permite definir permisos para cada rol y luego verificar si el usuario actual tiene el permiso para acceder a una página específica o realizar una acción determinada.

Instalación
-------
¡Ejecuta `composer require flightphp/permissions` y estás listo para empezar!

Uso
-------
Primero necesitas configurar tus permisos, luego le indicas a tu aplicación qué significan los permisos. En última instancia, verificarás tus permisos con `$Permissions->has()`, `->can()` o `is()`. `has()` y `can()` tienen la misma funcionalidad, pero tienen nombres diferentes para que tu código sea más legible.

## Ejemplo Básico

Imaginemos que tienes una funcionalidad en tu aplicación que verifica si un usuario ha iniciado sesión. Puedes crear un objeto de permisos así:

```php
// index.php
require 'vendor/autoload.php';

// algo de código

// luego probablemente tienes algo que te dice cuál es el rol actual de la persona
// probablemente tienes algo donde recuperas el rol actual
// de una variable de sesión que lo define
// después de que alguien inicie sesión, de lo contrario tendrán un rol 'invitado' o 'público'.
$rol_actual = 'admin';

// configurar permisos
$permiso = new \flight\Permission($rol_actual);
$permiso->defineRule('iniciadoSesion', function($rol_actual) {
	return $rol_actual !== 'invitado';
});

// Probablemente querrás persistir este objeto en Flight en algún lugar
Flight::set('permiso', $permiso);
```

Luego en algún controlador, podrías tener algo como esto.

```php
<?php

// algún controlador
class AlgunControlador {
	public function algunaAccion() {
		$permiso = Flight::get('permiso');
		if ($permiso->has('iniciadoSesion')) {
			// hacer algo
		} else {
			// hacer algo más
		}
	}
}
```

También puedes usar esto para rastrear si tienen permiso para hacer algo en tu aplicación.
Por ejemplo, si tienes una forma en la que los usuarios pueden interactuar con publicaciones en tu software, puedes verificar si tienen permiso para realizar ciertas acciones.

```php
$rol_actual = 'admin';

// configurar permisos
$permiso = new \flight\Permission($rol_actual);
$permiso->defineRule('publicación', function($rol_actual) {
	if($rol_actual === 'admin') {
		$permisos = ['crear', 'leer', 'actualizar', 'eliminar'];
	} else if($rol_actual === 'editor') {
		$permisos = ['crear', 'leer', 'actualizar'];
	} else if($rol_actual === 'autor') {
		$permisos = ['crear', 'leer'];
	} else if($rol_actual === 'colaborador') {
		$permisos = ['crear'];
	} else {
		$permisos = [];
	}
	return $permisos;
});
Flight::set('permiso', $permiso);
```

Luego en algún controlador...

```php
class ControladorPublicación {
	public function crear() {
		$permiso = Flight::get('permiso');
		if ($permiso->can('publicación.crear')) {
			// hacer algo
		} else {
			// hacer algo más
		}
	}
}
```

## Inyección de dependencias
Puedes inyectar dependencias en el cierre que define los permisos. Esto es útil si tienes algún tipo de interruptor, id o cualquier otro punto de datos que desees verificar. Lo mismo funciona para llamadas de tipo Clase->Método, excepto que defines los argumentos en el método.

### Cierres

```php
$Permission->defineRule('orden', function(string $rol_actual, MyDependency $MyDependency = null) {
	// ... código
});

// en tu archivo de controlador
public function crearOrden() {
	$MyDependency = Flight::myDependency();
	$permiso = Flight::get('permiso');
	if ($permiso->can('orden.crear', $MyDependency)) {
		// hacer algo
	} else {
		// hacer algo más
	}
}
```

### Clases

```php
namespace MiApp;

class Permisos {

	public function orden(string $rol_actual, MyDependency $MyDependency = null) {
		// ... código
	}
}
```

## Atajo para establecer permisos con clases
También puedes usar clases para definir tus permisos. Esto es útil si tienes muchos permisos y deseas mantener tu código limpio. Puedes hacer algo así:
```php
<?php

// código de arranque
$Permisos = new \flight\Permission($rol_actual);
$Permisos->defineRule('orden', 'MiApp\Permisos->orden');

// miapp/Permisos.php
namespace MiApp;

class Permisos {

	public function orden(string $rol_actual, int $id_usuario) {
		// Suponiendo que configuraste esto de antemano
		/** @var \flight\database\PdoWrapper $db */
		$db = Flight::db();
		$permisos_permitidos = ['leer']; // todos pueden ver una orden
		if($rol_actual === 'gerente') {
			$permisos_permitidos[] = 'crear'; // los gerentes pueden crear órdenes
		}
		$algún_interruptor_especial_de_db = $db->fetchField('SELECT algún_interruptor_especial FROM configuraciones WHERE id = ?', [ $id_usuario ]);
		if($algún_interruptor_especial_de_db) {
			$permisos_permitidos[] = 'actualizar'; // si el usuario tiene un interruptor especial, puede actualizar órdenes
		}
		if($rol_actual === 'admin') {
			$permisos_permitidos[] = 'eliminar'; // los administradores pueden eliminar órdenes
		}
		return $permisos_permitidos;
	}
}
```
Lo interesante es que también hay un atajo que puedes usar (¡que también se puede cachear!) donde solo le indicas a la clase de permisos que mapee todos los métodos de una clase en permisos. Entonces, si tienes un método llamado `orden()` y un método llamado `empresa()`, estos se mapearán automáticamente para que puedas ejecutar `$Permisos->has('orden.leer')` o `$Permisos->has('empresa.leer')` y funcionará. Definir esto es muy difícil, así que quédate conmigo aquí. Solo necesitas hacer esto:

Crear la clase de permisos que deseas agrupar.
```php
class MisPermisos {
	public function orden(string $rol_actual, int $id_orden = 0): array {
		// código para determinar permisos
		return $array_permisos;
	}

	public function empresa(string $rol_actual, int $id_empresa): array {
		// código para determinar permisos
		return $array_permisos;
	}
}
```

Luego haz que los permisos sean descubribles usando esta biblioteca.

```php
$Permisos = new \flight\Permission($rol_actual);
$Permisos->defineRulesFromClassMethods(MiApp\Permisos::class);
Flight::set('permisos', $Permisos);
```

Finalmente, llama al permiso en tu base de código para verificar si el usuario tiene permitido realizar un permiso dado.

```php
class AlgunControlador {
	public function crearOrden() {
		if(Flight::get('permisos')->can('orden.crear') === false) {
			die('¡No puedes crear una orden. ¡Lo siento!');
		}
	}
}
```

### Caché

Para habilitar la caché, consulta la sencilla [librería wruczak/phpfilecache](https://docs.flightphp.com/awesome-plugins/php-file-cache). Un ejemplo de cómo habilitarlo se muestra a continuación.
```php

// esta $app puede ser parte de tu código, o
// simplemente puedes pasar null y
// se obtendrá de Flight::app() en el constructor
$app = Flight::app();

// Por ahora acepta esto como una caché de archivos. Otros pueden agregarse fácilmente en el futuro.
$Caché = new Wruczek\PhpFileCache\PhpFileCache;

$Permisos = new \flight\Permission($rol_actual, $app, $Caché);
$Permisos->defineRulesFromClassMethods(MiApp\Permisos::class, 3600); // 3600 es cuántos segundos almacenar en caché esto. Déjalo fuera para no usar caché
``` 

¡Y listo!