# Autoloading

## Visión general

El autoloading es un concepto en PHP donde especificas un directorio o directorios para cargar clases. Esto es mucho más beneficioso que usar `require` o `include` para cargar clases. También es un requisito para usar paquetes de Composer.

## Comprensión

Por defecto, cualquier clase de `Flight` se autoload para ti automáticamente gracias a Composer. Sin embargo, si quieres autoload tus propias clases, puedes usar el método `Flight::path()` para especificar un directorio para cargar clases.

Usar un autoloader puede ayudar a simplificar tu código de manera significativa. En lugar de tener archivos que comiencen con una multitud de declaraciones `include` o `require` al principio para capturar todas las clases que se usan en ese archivo, puedes en su lugar llamar dinámicamente a tus clases y se incluirán automáticamente.

## Uso básico

Supongamos que tenemos un árbol de directorios como el siguiente:

```text
# Ejemplo de ruta
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

Puede que hayas notado que esta es la misma estructura de archivos que este sitio de documentación.

Puedes especificar cada directorio para cargar de esta manera:

```php

/**
 * public/index.php
 */

// Agregar una ruta al autoloader
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// no se requiere namespacing

// Todas las clases autoloaded se recomiendan en Pascal Case (cada palabra capitalizada, sin espacios)
class MyController {

	public function index() {
		// hacer algo
	}
}
```

## Namespaces

Si tienes namespaces, en realidad se vuelve muy fácil implementarlo. Deberías usar el método `Flight::path()` para especificar el directorio raíz (no el document root o la carpeta `public/`) de tu aplicación.

```php

/**
 * public/index.php
 */

// Agregar una ruta al autoloader
Flight::path(__DIR__.'/../');
```

Ahora es así como podría verse tu controlador. Mira el ejemplo a continuación, pero presta atención a los comentarios para información importante.

```php
/**
 * app/controllers/MyController.php
 */

// los namespaces son requeridos
// los namespaces son los mismos que la estructura de directorios
// los namespaces deben seguir el mismo case que la estructura de directorios
// los namespaces y directorios no pueden tener guiones bajos (a menos que Loader::setV2ClassLoading(false) esté configurado)
namespace app\controllers;

// Todas las clases autoloaded se recomiendan en Pascal Case (cada palabra capitalizada, sin espacios)
// A partir de 3.7.2, puedes usar Pascal_Snake_Case para los nombres de tus clases ejecutando Loader::setV2ClassLoading(false);
class MyController {

	public function index() {
		// hacer algo
	}
}
```

Y si quisieras autoload una clase en tu directorio utils, harías básicamente lo mismo:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// el namespace debe coincidir con la estructura de directorios y el case (nota que el directorio UTILS está en mayúsculas
//     como en el árbol de archivos arriba)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// hacer algo
	}
}
```

## Guiones bajos en nombres de clases

A partir de 3.7.2, puedes usar Pascal_Snake_Case para los nombres de tus clases ejecutando `Loader::setV2ClassLoading(false);`. 
Esto te permitirá usar guiones bajos en los nombres de tus clases. 
No se recomienda, pero está disponible para aquellos que lo necesiten.

```php
use flight\core\Loader;

/**
 * public/index.php
 */

// Agregar una ruta al autoloader
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');
Loader::setV2ClassLoading(false);

/**
 * app/controllers/My_Controller.php
 */

// no se requiere namespacing

class My_Controller {

	public function index() {
		// hacer algo
	}
}
```

## Ver también
- [Routing](/learn/routing) - Cómo mapear rutas a controladores y renderizar vistas.
- [Why a Framework?](/learn/why-frameworks) - Entendiendo los beneficios de usar un framework como Flight.

## Solución de problemas
- Si no puedes averiguar por qué tus clases con namespaces no se encuentran, recuerda usar `Flight::path()` al directorio raíz en tu proyecto, no a tu directorio `app/` o `src/` o equivalente.

### Clase no encontrada (autoloading no funciona)

Podría haber un par de razones para que esto no suceda. A continuación hay algunos ejemplos, pero asegúrate de revisar también la sección de [autoloading](/learn/autoloading).

#### Nombre de archivo incorrecto
El más común es que el nombre de la clase no coincida con el nombre del archivo.

Si tienes una clase llamada `MyClass`, entonces el archivo debería llamarse `MyClass.php`. Si tienes una clase llamada `MyClass` y el archivo se llama `myclass.php` 
entonces el autoloader no podrá encontrarla.

#### Namespace incorrecto
Si estás usando namespaces, entonces el namespace debería coincidir con la estructura de directorios.

```php
// ...code...

// si tu MyController está en el directorio app/controllers y está namespaced
// esto no funcionará.
Flight::route('/hello', 'MyController->hello');

// necesitarás elegir una de estas opciones
Flight::route('/hello', 'app\controllers\MyController->hello');
// o si tienes una declaración use arriba

use app\controllers\MyController;

Flight::route('/hello', [ MyController::class, 'hello' ]);
// también se puede escribir
Flight::route('/hello', MyController::class.'->hello');
// también...
Flight::route('/hello', [ 'app\controllers\MyController', 'hello' ]);
```

#### `path()` no definido

En la app skeleton, esto se define dentro del archivo `config.php`, pero para que tus clases sean encontradas, necesitas asegurarte de que el método `path()`
esté definido (probablemente a la raíz de tu directorio) antes de intentar usarlo.

```php
// Agregar una ruta al autoloader
Flight::path(__DIR__.'/../');
```

## Changelog
- v3.7.2 - Puedes usar Pascal_Snake_Case para los nombres de tus clases ejecutando `Loader::setV2ClassLoading(false);`
- v2.0 - Funcionalidad de autoload agregada.