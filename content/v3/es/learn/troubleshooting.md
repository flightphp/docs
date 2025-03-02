# Resolución de Problemas

Esta página te ayudará a solucionar problemas comunes que puedes encontrar al usar Flight.

## Problemas Comunes

### 404 No Encontrado o Comportamiento de Ruta Inesperado

Si ves un error 404 No Encontrado (pero juras por tu vida que realmente está ahí y no es un error tipográfico) esto realmente podría ser un problema con 
que estás devolviendo un valor en el punto final de tu ruta en lugar de simplemente hacer un eco. La razón de esto es intencional pero podría sorprender a algunos desarrolladores.

```php

Flight::route('/hello', function(){
	// Esto podría causar un error 404 No Encontrado
	return '¡Hola Mundo!';
});

// Lo que probablemente deseas
Flight::route('/hello', function(){
	echo '¡Hola Mundo!';
});

```

La razón de esto es debido a un mecanismo especial incorporado en el enrutador que maneja la salida de retorno como un solo "ir a la siguiente ruta".
Puedes ver el comportamiento documentado en la sección [Enrutamiento](/learn/routing#passing).

### Clase No Encontrada (la carga automática no funciona)

Podría haber un par de razones por las que esto no está sucediendo. A continuación se muestran algunos ejemplos, pero asegúrate de revisar también la sección de [carga automática](/learn/autoloading).

#### Nombre de Archivo Incorrecto
Lo más común es que el nombre de la clase no coincida con el nombre del archivo.

Si tienes una clase llamada `MiClase` entonces el archivo debería llamarse `MiClase.php`. Si tienes una clase llamada `MiClase` y el archivo se llama `miclase.php` 
entonces el cargador automático no podrá encontrarlo.

#### Namespace Incorrecto
Si estás utilizando espacios de nombres (namespaces), entonces el namespace debe coincidir con la estructura de directorios.

```php
// código

// si tu MyController está en el directorio app/controllers y tiene un espacio de nombres
// esto no funcionará.
Flight::route('/hello', 'MyController->hello');

// deberías elegir una de estas opciones
Flight::route('/hello', 'app\controllers\MiControlador->hello');
// o si tienes una declaración use arriba

use app\controllers\MiControlador;

Flight::route('/hello', [ MiControlador::class, 'hello' ]);
// también puede escribirse
Flight::route('/hello', MiControlador::class.'->hello');
// también...
Flight::route('/hello', [ 'app\controllers\MiControlador', 'hello' ]);
```

#### `path()` no definido

En la aplicación esquelética, esto está definido dentro del archivo `config.php`, pero para que tus clases se encuentren, debes asegurarte de que el método `path()`
esté definido (probablemente en la raíz de tu directorio) antes de intentar usarlo.

```php

// Agregar una ruta al cargador automático
Flight::path(__DIR__.'/../');

```