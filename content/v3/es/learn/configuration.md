# Configuración

## Resumen

Flight ofrece una manera sencilla de configurar varios aspectos del framework para adaptarse a las necesidades de tu aplicación. Algunos se establecen por defecto, pero puedes anularlos según sea necesario. También puedes configurar tus propias variables para usarlas en toda tu aplicación.

Una configuración clara y en capas (valores predeterminados de archivo + secretos de entorno) también ayuda a las [herramientas de IA](/learn/ai): los agentes aprenden un lugar para los literales y un lugar para los secretos, en lugar de inventar lecturas de `$_ENV` dentro de los controladores.

## Comprensión

Puedes personalizar ciertos comportamientos de Flight estableciendo valores de configuración mediante el método `set`.

```php
Flight::set('flight.log_errors', true);
```

En una aplicación estructurada (incluido el [skeleton](https://github.com/flightphp/skeleton)), normalmente cargas la configuración del proyecto desde `app/config/config.php` y luego aplicas las claves relevantes al Engine (por ejemplo `flight.base_url`, `flight.views.path`). También puedes inyectar un pequeño objeto de configuración en los controladores en lugar de leer globales en todas partes; más amigable para las pruebas y para los agentes que siguen `AGENTS.md`.

## Uso Básico

### Opciones de Configuración de Flight

La siguiente es una lista de todos los ajustes de configuración disponibles:

- **flight.base_url** `?string` - Sobrescribe la URL base de la solicitud si Flight se ejecuta en un subdirectorio. (predeterminado: null)
- **flight.case_sensitive** `bool` - Coincidencia sensible a mayúsculas para URLs. (predeterminado: false)
- **flight.handle_errors** `bool` - Permitir que Flight maneje todos los errores internamente. (predeterminado: true)
  - Si quieres que Flight maneje los errores en lugar del comportamiento predeterminado de PHP, esto debe ser true.
  - Si tienes [Tracy](/awesome-plugins/tracy) instalado, debes establecer esto en false para que Tracy pueda manejar los errores.
  - Si tienes el plugin [APM](/awesome-plugins/apm) instalado, debes establecer esto en true para que el APM pueda registrar los errores.
- **flight.log_errors** `bool` - Registrar errores en el archivo de registro de errores del servidor web. (predeterminado: false)
  - Si tienes [Tracy](/awesome-plugins/tracy) instalado, Tracy registrará los errores según las configuraciones de Tracy, no según esta configuración.
- **flight.debug** `bool` - Mostrar información detallada del error (mensaje de excepción, código y traza de pila) en el navegador cuando ocurre un error. (predeterminado: false)
  - **Nunca habilites esto en producción**: filtra detalles internos de la aplicación. Úsalo solo para desarrollo local o ensayo (staging).
  - Cuando es `false`, se muestra un `500 Internal Server Error` genérico en su lugar. Combínalo con `flight.log_errors` para capturar errores en el lado del servidor.
- **flight.allow_method_override** `bool` - Permitir que el método HTTP se sobrescriba mediante la cabecera de solicitud `X-HTTP-Method-Override` o un campo `_method` en el cuerpo POST. (predeterminado: true)
  - **Se recomienda establecer esto en `false`** para aplicaciones que no necesitan suplantación de método basada en formularios HTML, ya que evita que los clientes forjen solicitudes `DELETE` o `PUT` a través de un formulario POST estándar.
  - Consulta [Seguridad](/learn/security#flight-configuration-hardening) para más detalles.
- **flight.views.path** `string` - Directorio que contiene los archivos de plantilla de vista. (predeterminado: ./views)
- **flight.views.extension** `string` - Extensión de archivo de plantilla de vista. (predeterminado: `.php`; el skeleton oficial lo establece en `.twig` cuando se usa Twig)
- **flight.views.restrict_to_path** `bool` - Cuando es `true`, la `View` nativa de Flight solo acepta archivos de plantilla que se resuelvan dentro de `flight.views.path`. (predeterminado: `false`). **Actívalo** para aplicaciones que usan vistas nativas. Consulta [Seguridad](/learn/security#flightviewsrestrict_to_path).
- **flight.content_length** `bool` - Establecer la cabecera `Content-Length`. (predeterminado: true)
  - Si estás usando [Tracy](/awesome-plugins/tracy), esto debe establecerse en false para que Tracy pueda renderizarse correctamente.
- **flight.v2.output_buffering** `bool` - Usar el almacenamiento en búfer de salida heredado. Consulta [migrando a v3](migrating-to-v3). (predeterminado: false)

### Configuración del Cargador

Hay además otro ajuste de configuración para el cargador. Esto te permitirá autocargar clases con `_` en el nombre de la clase.

```php
// Habilitar la carga de clases con guiones bajos
// Valor predeterminado: true
Loader::$v2ClassLoading = false;
```

Recuerda que [la autocarga](/learn/autoloading) también depende de que las **carpetas con mayúsculas/minúsculas** coincidan con tus espacios de nombres, especialmente con la estructura `App\` + `app/Controller/` del skeleton.

### Configuración del proyecto y `.env` (patrón del skeleton)

El núcleo de Flight no requiere archivos `.env`. Muchas aplicaciones solo usan un arreglo de configuración PHP. El skeleton oficial organiza la configuración en capas para que los secretos permanezcan fuera de git mientras Runway aún puede reescribir de manera segura la configuración **literal**:

1. **`.env` / entorno real**: secretos y anulaciones de despliegue (ignorados por git).
2. **`app/config/config.php`** — valores predeterminados literales de un arreglo PHP (copiado de `config_sample.php`). Prefiere **no** usar expresiones `$_ENV[...]` dentro de este archivo: herramientas como `runway config:set` pueden reescribirlo como valores estáticos y podrían incrustar secretos en el archivo.
3. **Fusión al arrancar (bootstrap)**: el entorno gana para las claves mapeadas; el código de la aplicación lee un objeto de configuración o `$app->get()`, no `$_ENV` en los controladores.

Ejemplo de la estructura de `config_sample.php` / `config.php` (simplificado):

```php
<?php
// Solo literales: los secretos pertenecen a .env en el flujo de trabajo del skeleton
return [
	'app' => [
		'env' => 'development',
		'debug' => true,
		'base_url' => '/',
		'timezone' => 'UTC',
	],
	'database' => [
		'driver' => 'sqlite', // o mysql, o '' para deshabilitar
		'host' => 'localhost',
		'dbname' => '',
		'user' => '',
		'password' => '',
		'file_path' => __DIR__ . '/../../database.sqlite',
	],
	// ...
];
```

```bash
# .env.example → .env (skeleton)
APP_ENV=development
APP_DEBUG=true
FLIGHT_BASE_URL=/
DB_DRIVER=sqlite
# DB_PASSWORD=...
```

Esta división es deliberada para [proyectos amigables con la IA](/learn/ai): las instrucciones pueden decir “valores predeterminados en `config.php`, secretos en `.env`, inyecta Config / Engine—nunca inventes acceso al entorno en un controlador”. Las aplicaciones existentes pueden ignorar `.env` por completo y mantener un único archivo de configuración.

### Variables

Flight te permite guardar variables para que puedan usarse en cualquier lugar de tu aplicación.

```php
// Guarda tu variable
Flight::set('id', 123);

// En cualquier otro lugar de tu aplicación
$id = Flight::get('id');
```

Para ver si una variable ha sido establecida, puedes hacer:

```php
if (Flight::has('id')) {
  // Haz algo
}
```

Puedes limpiar una variable haciendo:

```php
// Limpia la variable id
Flight::clear('id');

// Limpia todas las variables
Flight::clear();
```

> **Nota:** El hecho de que puedas establecer una variable no significa que debas hacerlo. Usa esta característica con moderación. La razón es que cualquier cosa almacenada aquí se convierte en una variable global. Las variables globales son malas porque pueden cambiarse desde cualquier lugar de tu aplicación, lo que dificulta rastrear errores. Además, esto puede complicar cosas como las [pruebas unitarias](/guides/unit-testing). Prefiere la inyección por constructor (como en el skeleton + la configuración de Dice) para los servicios y la configuración que los controladores necesitan.

### Errores y Excepciones

Todos los errores y excepciones son capturados por Flight y pasados al método `error` si `flight.handle_errors` está establecido a true.

El comportamiento predeterminado es enviar una respuesta genérica `HTTP 500 Internal Server Error` con algo de información del error.

Puedes [anular](/learn/extending) este comportamiento según tus propias necesidades:

```php
Flight::map('error', function (Throwable $error) {
  // Manejar el error
  echo $error->getTraceAsString();
});
```

Por defecto, los errores no se registran en el servidor web. Puedes habilitar esto cambiando la configuración:

```php
Flight::set('flight.log_errors', true);
```

#### 404 No Encontrado

Cuando no se puede encontrar una URL, Flight llama al método `notFound`. El comportamiento predeterminado es enviar una respuesta `HTTP 404 Not Found` con un mensaje simple.

Puedes [anular](/learn/extending) este comportamiento según tus propias necesidades:

```php
Flight::map('notFound', function () {
  // Manejar el no encontrado
});
```

## Ver También

- [Instalación](/install) - Configuración del skeleton, `.env` y estructura de arranque.
- [Autocarga](/learn/autoloading) - Espacios de nombres y mayúsculas/minúsculas en carpetas.
- [Extender Flight](/learn/extending) - Cómo extender y personalizar la funcionalidad principal de Flight.
- [Pruebas unitarias](/guides/unit-testing) - Cómo escribir pruebas unitarias para tu aplicación Flight.
- [IA y experiencia de desarrollo](/learn/ai) - `AGENTS.md` e instrucciones de proyecto consistentes.
- [Tracy](/awesome-plugins/tracy) - Un plugin para el manejo avanzado de errores y depuración.
- [Extensiones de Tracy](/awesome-plugins/tracy_extensions) - Extensiones para integrar Tracy con Flight.
- [APM](/awesome-plugins/apm) - Un plugin para monitoreo del rendimiento de aplicaciones y seguimiento de errores.
- [Seguridad](/learn/security) - Indicadores de endurecimiento y manejo de secretos.

## Solución de Problemas

- Si tienes problemas para conocer todos los valores de tu configuración, puedes hacer `var_dump(Flight::get());`
- Si Runway o las herramientas de despliegue reescribieron `config.php`, confirma que los secretos no se hayan comprometido; mantenlos en `.env` o en el entorno real cuando uses el patrón del skeleton.

## Registro de Cambios

- Documentación: se señaló `flight.views.restrict_to_path` junto a la configuración de la ruta de vistas.
- Documentación: se documentó la configuración estilo skeleton / capas de `.env` y la extensión de vista Twig predeterminada para proyectos nuevos.
- v3.18.1 - Se agregaron las opciones de configuración `flight.debug` y `flight.allow_method_override`.
- v3.5.0 - Se agregó configuración para `flight.v2.output_buffering` para admitir el comportamiento de almacenamiento en búfer de salida heredado.
- v2.0 - Se agregaron las configuraciones principales.