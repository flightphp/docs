# Carga automática

La carga automática es un concepto en PHP donde especificas un directorio o directorios para cargar clases desde. Esto es mucho más beneficioso que usar `require` o `include` para cargar clases. También es un requisito para usar paquetes de Composer.

Por defecto, cualquier clase de `Flight` se carga automáticamente gracias a Composer. Sin embargo, si deseas cargar tus propias clases, puedes usar el método `Flight::path` para especificar un directorio desde el cual cargar las clases.

## Ejemplo básico

Supongamos que tenemos un árbol de directorios como el siguiente:

```text
# Ejemplo de ruta
/home/usuario/proyecto/mi-proyecto-de-flight/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - contiene los controladores para este proyecto
│   ├── translations
│   ├── UTILS - contiene clases solo para esta aplicación (esto está en mayúsculas a propósito para un ejemplo posterior)
│   └── vistas
└── público
    └── css
	└── js
	└── index.php
```

Puedes especificar cada directorio desde el cual cargar de la siguiente manera:

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

// Se recomienda que todas las clases cargadas automáticamente sean de tipo Pascal Case (cada palabra en mayúscula, sin espacios)
// A partir de la versión 3.7.2, puedes utilizar Pascal_Snake_Case para los nombres de tus clases ejecutando Loader::setV2ClassLoading(false);
class MyController {

	public function index() {
		// hacer algo
	}
}
```

## Espacios de nombres

Si tienes espacios de nombres, en realidad se vuelve muy fácil de implementar. Debes usar el método `Flight::path()` para especificar el directorio raíz (no el directorio de documentos raíz o la carpeta `público/`) de tu aplicación.

```php

/**
 * public/index.php
 */

// Agregar una ruta al cargador automático
Flight::path(__DIR__.'/../');
```

Así es como podría verse tu controlador. Observa el ejemplo a continuación, pero presta atención a los comentarios para obtener información importante.

```php
/**
 * app/controllers/MyController.php
 */

// se requieren espacios de nombres
// los espacios de nombres son iguales a la estructura de directorios
// los espacios de nombres deben seguir la misma estructura de mayúsculas y minúsculas que la de los directorios
// los espacios de nombres y directorios no pueden tener guiones bajos (a menos que se establezca Loader::setV2ClassLoading(false))
namespace app\controllers;

// Se recomienda que todas las clases cargadas automáticamente sean de tipo Pascal Case (cada palabra en mayúscula, sin espacios)
// A partir de la versión 3.7.2, puedes utilizar Pascal_Snake_Case para los nombres de tus clases ejecutando Loader::setV2ClassLoading(false);
class MyController {

	public function index() {
		// hacer algo
	}
}
```

Y si deseas cargar automáticamente una clase en tu directorio `utils`, harías básicamente lo mismo:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// el espacio de nombres debe coincidir con la estructura y mayúsculas/minúsculas del directorio (nota que el directorio UTILS está todo en mayúsculas
//     como en el árbol de archivos anterior)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// hacer algo
	}
}
```

## Guiones bajos en los nombres de las clases

A partir de la versión 3.7.2, puedes usar Pascal_Snake_Case para los nombres de tus clases ejecutando `Loader::setV2ClassLoading(false);`. Esto te permitirá usar guiones bajos en los nombres de tus clases. Esto no se recomienda, pero está disponible para aquellos que lo necesiten.

```php

/**
 * public/index.php
 */

// Agregar una ruta al cargador automático
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');
Loader::setV2ClassLoading(false);

/**
 * app/controllers/My_Controller.php
 */

// no se requiere espacio de nombres

class My_Controller {

	public function index() {
		// hacer algo
	}
}
```