# Tracy

Tracy es un manejador de errores increíble que se puede utilizar con Flight. Tiene una serie de paneles que pueden ayudarte a depurar tu aplicación. También es muy fácil de extender y añadir tus propios paneles. El equipo de Flight ha creado algunos paneles específicamente para proyectos de Flight con el complemento [flightphp/tracy-extensions](https://github.com/flightphp/tracy-extensions).

## Instalación

Instala con composer. Y realmente querrás instalar esto sin la versión de desarrollo ya que Tracy viene con un componente de manejo de errores de producción.

```bash
composer require tracy/tracy
```

## Configuración Básica

Hay algunas opciones de configuración básicas para empezar. Puedes leer más sobre ellas en la [Documentación de Tracy](https://tracy.nette.org/es/configurando).

```php

require 'vendor/autoload.php';

use Tracy\Debugger;

// Habilitar Tracy
Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT) // a veces tienes que ser explícito(siempre Debugger::PRODUCTION)
// Debugger::enable('23.75.345.200'); // también puedes proporcionar un array de direcciones IP

// Aquí es donde se registrarán los errores y excepciones. Asegúrate de que este directorio exista y sea escribible.
Debugger::$logDirectory = __DIR__ . '/../log/';
Debugger::$strictMode = true; // mostrar todos los errores
// Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // todos los errores excepto avisos obsoletos
if (Debugger::$showBar) {
    $app->set('flight.content_length', false); // si la barra de depuración de Tracy es visible, entonces la longitud del contenido no puede ser establecida por Flight

	// Esto es específico de la Extensión de Tracy para Flight si has incluido eso
	// de lo contrario, comenta esto.
	new TracyExtensionLoader($app);
}
```

## Consejos Útiles

Cuando estés depurando tu código, hay algunas funciones muy útiles para mostrar datos para ti.

- `bdump($var)` - Esto volcará la variable en la Barra de Tracy en un panel separado.
- `dumpe($var)` - Esto volcará la variable y luego morirá inmediatamente.