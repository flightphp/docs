# Carga automática

La carga automática es un concepto en PHP donde especificas un directorio o directorios para cargar clases desde. Esto es mucho más beneficioso que usar `require` o `include` para cargar clases. También es un requisito para usar paquetes de Composer.

Por defecto, cualquier clase de `Flight` se carga automáticamente gracias a composer. Sin embargo, si deseas cargar automáticamente tus propias clases, puedes usar el método `Flight::path` para especificar un directorio desde el cual cargar clases.

## Ejemplo básico

Supongamos que tenemos un árbol de directorios como el siguiente:

```text
# Ruta de ejemplo
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - contiene los controladores para este proyecto
│   ├── translations
│   ├── UTILS - contiene clases solo para esta aplicación (esto está en mayúsculas a propósito para un ejemplo posterior)
│   └── vistas
└── public
    └── css
	└── js
	└── index.php
```

Puede haber notado que esta es la misma estructura de archivos que este sitio de documentación.

Puedes especificar cada directorio desde el cual cargar de esta manera:

```php

/**
 * public/index.php
 */

// Agregar una ruta al cargador automático
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// no se requiere espacio de nombres

// Se recomienda que todas las clases cargadas automáticamente estén en Pascal Case (cada palabra en mayúscula, sin espacios)
// Es un requisito que no se puede tener un guion bajo en el nombre de tu clase
class MyController {

	public function index() {
		// hacer algo
	}
}
```

## Espacios de nombres

Si tienes espacios de nombres, en realidad se vuelve muy fácil de implementar. Deberías usar el método `Flight::path()` para especificar el directorio raíz (no la raíz del documento o la carpeta `public/`) de tu aplicación.

```php

/**
 * public/index.php
 */

// Agregar una ruta al cargador automático
Flight::path(__DIR__.'/../');
```

Así es como podría lucir tu controlador. Mira el ejemplo a continuación, pero presta atención a los comentarios para obtener información importante.

```php
/**
 * app/controllers/MyController.php
 */

// Se requieren espacios de nombres
// Los espacios de nombres son iguales a la estructura de directorios
// Los espacios de nombres deben seguir el mismo caso que la estructura de directorios
// Los espacios de nombres y directorios no pueden tener guiones bajos
namespace app\controllers;

// Se recomienda que todas las clases cargadas automáticamente estén en Pascal Case (cada palabra en mayúscula, sin espacios)
// Es un requisito que no se puede tener un guion bajo en el nombre de tu clase
class MyController {

	public function index() {
		// hacer algo
	}
}
```

Y si deseas cargar automáticamente una clase en tu directorio de utils, harías básicamente lo mismo:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// el espacio de nombres debe coincidir con la estructura y el caso del directorio (nota que el directorio UTILS está en mayúsculas
//     como en el árbol de archivos anterior)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// hacer algo
	}
}
```