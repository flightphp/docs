# Autocarga

## Resumen

La autocarga es un concepto en PHP donde especificas un directorio o directorios para cargar clases. Esto es mucho más beneficioso que usar `require` o `include` para cargar clases. También es un requisito para usar paquetes de Composer.

## Comprensión

Por defecto, cualquier clase de `Flight` se autocarga automáticamente para ti gracias a Composer. Sin embargo, si quieres autocargar tus propias clases, puedes usar el método `Flight::path()` para especificar un directorio desde el cual cargar clases.

Usar un autocargador puede ayudar a simplificar tu código de manera significativa. En lugar de tener archivos que comiencen con una multitud de declaraciones `include` o `require` al principio para capturar todas las clases que se usan en ese archivo, puedes en su lugar llamar dinámicamente a tus clases y se incluirán automáticamente.

## Uso Básico

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
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

Puede que hayas notado que esta es la misma estructura de archivos que el sitio de documentación.

Puedes especificar cada directorio desde el cual cargar de esta manera:

```php

/**
 * public/index.php
 */

// Agregar una ruta al autocargador
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// no se requiere nombres de espacio

// Se recomienda que todas las clases autocargadas estén en Pascal Case (cada palabra capitalizada, sin espacios)
class MyController {

	public function index() {
		// hacer algo
	}
}
```

## Espacios de Nombres

Si tienes espacios de nombres, en realidad se vuelve muy fácil implementarlo. Debes usar el método `Flight::path()` para especificar el directorio raíz (no la raíz del documento o la carpeta `public/`) de tu aplicación.

```php

/**
 * public/index.php
 */

// Agregar una ruta al autocargador
Flight::path(__DIR__.'/../');
```

Ahora es así como podría verse tu controlador. Mira el ejemplo a continuación, pero presta atención a los comentarios para información importante.

```php
/**
 * app/controllers/MyController.php
 */

// los espacios de nombres son requeridos
// los espacios de nombres son los mismos que la estructura de directorios
// los espacios de nombres deben seguir el mismo caso que la estructura de directorios
// los espacios de nombres y directorios no pueden tener guiones bajos (a menos que Loader::setV2ClassLoading(false) esté configurado)
namespace app\controllers;

// Se recomienda que todas las clases autocargadas estén en Pascal Case (cada palabra capitalizada, sin espacios)
// A partir de 3.7.2, puedes usar Pascal_Snake_Case para los nombres de tus clases ejecutando Loader::setV2ClassLoading(false);
class MyController {

	public function index() {
		// hacer algo
	}
}
```

Y si quisieras autocargar una clase en tu directorio utils, harías básicamente lo mismo:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// el espacio de nombres debe coincidir con la estructura de directorios y el caso (nota que el directorio UTILS está en mayúsculas
//     como en el árbol de archivos arriba)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// hacer algo
	}
}
```

## Guiones Bajos en Nombres de Clases

A partir de 3.7.2, puedes usar Pascal_Snake_Case para los nombres de tus clases ejecutando `Loader::setV2ClassLoading(false);`. 
Esto te permitirá usar guiones bajos en los nombres de tus clases. 
No se recomienda, pero está disponible para aquellos que lo necesiten.

```php
use flight\core\Loader;

/**
 * public/index.php
 */

// Agregar una ruta al autocargador
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');
Loader::setV2ClassLoading(false);

/**
 * app/controllers/My_Controller.php
 */

// no se requiere nombres de espacio

class My_Controller {

	public function index() {
		// hacer algo
	}
}
```

## Ver También
- [Enrutamiento](/learn/routing) - Cómo mapear rutas a controladores y renderizar vistas.
- [¿Por qué un Framework?](/learn/why-frameworks) - Entendiendo los beneficios de usar un framework como Flight.

## Solución de Problemas
- Si no puedes averiguar por qué no se encuentran tus clases con espacios de nombres, recuerda usar `Flight::path()` al directorio raíz en tu proyecto, no a tu directorio `app/` o `src/` o equivalente.

### Clase No Encontrada (autocarga no funciona)

Podría haber un par de razones para que esto no suceda. A continuación hay algunos ejemplos, pero asegúrate de revisar también la sección de [autocarga](/learn/autoloading).

#### Nombre de Archivo Incorrecto
El más común es que el nombre de la clase no coincida con el nombre del archivo.

Si tienes una clase llamada `MyClass`, entonces el archivo debería llamarse `MyClass.php`. Si tienes una clase llamada `MyClass` y el archivo se llama `myclass.php` 
entonces el autocargador no podrá encontrarla.

#### Espacio de Nombres Incorrecto
Si estás usando espacios de nombres, entonces el espacio de nombres debería coincidir con la estructura de directorios.

```php
// ...código...

// si tu MyController está en el directorio app/controllers y está con espacio de nombres
// esto no funcionará.
Flight::route('/hello', 'MyController->hello');

// necesitarás elegir una de estas opciones
Flight::route('/hello', 'app\controllers\MyController->hello');
// o si tienes una declaración use arriba

use app\controllers\MyController;

Flight::route('/hello', [ MyController::class, 'hello' ]);
// también puede escribirse
Flight::route('/hello', MyController::class.'->hello');
// también...
Flight::route('/hello', [ 'app\controllers\MyController', 'hello' ]);
```

#### `path()` no definido

En la aplicación esqueleto, esto se define dentro del archivo `config.php`, pero para que se encuentren tus clases, necesitas asegurarte de que el método `path()`
esté definido (probablemente a la raíz de tu directorio) antes de intentar usarlo.

```php
// Agregar una ruta al autocargador
Flight::path(__DIR__.'/../');
```

## Registro de Cambios
- v3.7.2 - Puedes usar Pascal_Snake_Case para los nombres de tus clases ejecutando `Loader::setV2ClassLoading(false);`
- v2.0 - Funcionalidad de autocarga agregada.