# Gestor de Eventos

_a partir de v3.15.0_

## Resumen

Los eventos te permiten registrar y activar comportamientos personalizados en tu aplicación. Con la adición de `Flight::onEvent()` y `Flight::triggerEvent()`, ahora puedes engancharte en momentos clave del ciclo de vida de tu app o definir tus propios eventos (como notificaciones y correos electrónicos) para hacer tu código más modular y extensible. Estos métodos son parte de los [métodos mapeables](/learn/extending) de Flight, lo que significa que puedes sobrescribir su comportamiento para adaptarlo a tus necesidades.

## Comprensión

Los eventos te permiten separar diferentes partes de tu aplicación para que no dependan demasiado unas de otras. Esta separación—a menudo llamada **desacoplamiento**—hace que tu código sea más fácil de actualizar, extender o depurar. En lugar de escribir todo en un gran bloque, puedes dividir tu lógica en piezas más pequeñas e independientes que respondan a acciones específicas (eventos).

Imagina que estás construyendo una app de blog:
- Cuando un usuario publica un comentario, podrías querer:
  - Guardar el comentario en la base de datos.
  - Enviar un correo electrónico al propietario del blog.
  - Registrar la acción por seguridad.

Sin eventos, todo esto se amontonarían en una sola función. Con eventos, puedes dividirlo: una parte guarda el comentario, otra activa un evento como `'comment.posted'`, y oyentes separados manejan el correo electrónico y el registro. Esto mantiene tu código más limpio y te permite agregar o eliminar características (como notificaciones) sin tocar la lógica principal.

### Casos de Uso Comunes

En la mayoría de los casos, los eventos son buenos para cosas que son opcionales, pero no una parte absolutamente central de tu sistema. Por ejemplo, lo siguiente es bueno tenerlo, pero si fallan por alguna razón, tu aplicación debería seguir funcionando:

- **Registro**: Registrar acciones como inicios de sesión o errores sin ensuciar tu código principal.
- **Notificaciones**: Enviar correos electrónicos o alertas cuando algo sucede.
- **Actualizaciones de Caché**: Refrescar cachés o notificar a otros sistemas sobre cambios.

Sin embargo, supongamos que tienes una función de contraseña olvidada. Eso debería ser parte de tu funcionalidad principal y no un evento porque si ese correo no se envía, tu usuario no puede restablecer su contraseña y usar tu aplicación.

## Uso Básico

El sistema de eventos de Flight se construye alrededor de dos métodos principales: `Flight::onEvent()` para registrar oyentes de eventos y `Flight::triggerEvent()` para activar eventos. Aquí te explico cómo usarlos:

### Registrando Oyentes de Eventos

Para escuchar un evento, usa `Flight::onEvent()`. Este método te permite definir qué debería suceder cuando ocurre un evento.

```php
Flight::onEvent(string $event, callable $callback): void
```

- `$event`: Un nombre para tu evento (por ejemplo, `'user.login'`).
- `$callback`: La función a ejecutar cuando se active el evento.

Te "suscribes" a un evento diciéndole a Flight qué hacer cuando suceda. El callback puede aceptar argumentos pasados desde la activación del evento.

El sistema de eventos de Flight es síncrono, lo que significa que cada oyente de evento se ejecuta en secuencia, uno después del otro. Cuando activas un evento, todos los oyentes registrados para ese evento se ejecutarán hasta completarse antes de que tu código continúe. Esto es importante entenderlo ya que difiere de los sistemas de eventos asíncronos donde los oyentes podrían ejecutarse en paralelo o en un momento posterior.

#### Ejemplo Simple
```php
Flight::onEvent('user.login', function ($username) {
    echo "¡Bienvenido de nuevo, $username!";

	// puedes enviar un correo si el inicio de sesión es desde una nueva ubicación
});
```
Aquí, cuando se active el evento `'user.login'`, saludará al usuario por su nombre y podría incluir lógica para enviar un correo si es necesario.

> **Nota:** El callback puede ser una función, una función anónima o un método de una clase.

### Activando Eventos

Para hacer que un evento suceda, usa `Flight::triggerEvent()`. Esto le dice a Flight que ejecute todos los oyentes registrados para ese evento, pasando cualquier dato que proporciones.

```php
Flight::triggerEvent(string $event, ...$args): void
```

- `$event`: El nombre del evento que estás activando (debe coincidir con un evento registrado).
- `...$args`: Argumentos opcionales para enviar a los oyentes (puede ser cualquier número de argumentos).

#### Ejemplo Simple
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Esto activa el evento `'user.login'` y envía `'alice'` al oyente que definimos anteriormente, lo que producirá: `¡Bienvenido de nuevo, alice!`.

- Si no hay oyentes registrados, no pasa nada—tu app no se romperá.
- Usa el operador de propagación (`...`) para pasar múltiples argumentos de manera flexible.

### Deteniendo Eventos

Si un oyente devuelve `false`, no se ejecutarán oyentes adicionales para ese evento. Esto te permite detener la cadena de eventos basada en condiciones específicas. Recuerda, el orden de los oyentes importa, ya que el primero en devolver `false` detendrá el resto de la ejecución.

**Ejemplo**:
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // Detiene oyentes subsiguientes
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // esto nunca se envía
});
```

### Sobrescribiendo Métodos de Eventos

`Flight::onEvent()` y `Flight::triggerEvent()` están disponibles para ser [extendidos](/learn/extending), lo que significa que puedes redefinir cómo funcionan. Esto es genial para usuarios avanzados que quieran personalizar el sistema de eventos, como agregar registro o cambiar cómo se despachan los eventos.

#### Ejemplo: Personalizando `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Registrar cada registro de evento
    error_log("Nuevo oyente de evento agregado para: $event");
    // Llamar al comportamiento predeterminado (asumiendo un sistema de eventos interno)
    Flight::_onEvent($event, $callback);
});
```
Ahora, cada vez que registres un evento, se registrará antes de proceder.

#### ¿Por Qué Sobrescribir?
- Agregar depuración o monitoreo.
- Restringir eventos en ciertos entornos (por ejemplo, deshabilitar en pruebas).
- Integrar con una biblioteca de eventos diferente.

### Dónde Poner Tus Eventos

Si eres nuevo en los conceptos de eventos en tu proyecto, podrías preguntarte: *¿dónde registro todos estos eventos en mi app?* La simplicidad de Flight significa que no hay una regla estricta—puedes ponerlos donde tenga sentido para tu proyecto. Sin embargo, mantenerlos organizados te ayuda a mantener tu código a medida que tu app crece. Aquí hay algunas opciones prácticas y mejores prácticas, adaptadas a la naturaleza ligera de Flight:

#### Opción 1: En Tu Archivo Principal `index.php`
Para apps pequeñas o prototipos rápidos, puedes registrar eventos directamente en tu archivo `index.php` junto con tus rutas. Esto mantiene todo en un solo lugar, lo cual está bien cuando la simplicidad es tu prioridad.

```php
require 'vendor/autoload.php';

// Registrar eventos
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
});

// Definir rutas
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **Pros**: Simple, sin archivos extra, genial para proyectos pequeños.
- **Cons**: Puede volverse desordenado a medida que tu app crece con más eventos y rutas.

#### Opción 2: Un Archivo Separado `events.php`
Para una app un poco más grande, considera mover los registros de eventos a un archivo dedicado como `app/config/events.php`. Incluye este archivo en tu `index.php` antes de tus rutas. Esto imita cómo se organizan a menudo las rutas en `app/config/routes.php` en proyectos de Flight.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "Email sent to $email: Welcome, $name!";
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **Pros**: Mantiene `index.php` enfocado en el enrutamiento, organiza los eventos lógicamente, fácil de encontrar y editar.
- **Cons**: Agrega un poco de estructura, lo que podría parecer excesivo para apps muy pequeñas.

#### Opción 3: Cerca de Dónde Se Activan

Otro enfoque es registrar eventos cerca de donde se activan, como dentro de un controlador o definición de ruta. Esto funciona bien si un evento es específico de una parte de tu app.

```php
Flight::route('/signup', function () {
    // Registrar evento aquí
    Flight::onEvent('user.registered', function ($email) {
        echo "Welcome email sent to $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Signed up!";
});
```
- **Pros**: Mantiene el código relacionado junto, bueno para características aisladas.
- **Cons**: Dispersa los registros de eventos, haciendo más difícil ver todos los eventos de una vez; riesgo de registros duplicados si no se tiene cuidado.

#### Mejor Práctica para Flight
- **Empieza Simple**: Para apps pequeñas, pon los eventos en `index.php`. Es rápido y se alinea con el minimalismo de Flight.
- **Crece Inteligente**: A medida que tu app se expande (por ejemplo, más de 5-10 eventos), usa un archivo `app/config/events.php`. Es un paso natural, como organizar rutas, y mantiene tu código ordenado sin agregar marcos complejos.
- **Evita la Sobreingeniería**: No crees una clase o directorio "gestor de eventos" completo a menos que tu app sea enorme—Flight prospera en la simplicidad, así que manténlo ligero.

#### Consejo: Agrupa por Propósito
En `events.php`, agrupa eventos relacionados (por ejemplo, todos los eventos relacionados con usuarios juntos) con comentarios para mayor claridad:

```php
// app/config/events.php
// Eventos de Usuario
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in");
});
Flight::onEvent('user.registered', function ($email) {
    echo "Welcome to $email!";
});

// Eventos de Página
Flight::onEvent('page.updated', function ($pageId) {
    Flight::cache()->delete("page_$pageId");
});
```

Esta estructura escala bien y se mantiene amigable para principiantes.

### Ejemplos del Mundo Real

Vamos a recorrer algunos escenarios del mundo real para mostrar cómo funcionan los eventos y por qué son útiles.

#### Ejemplo 1: Registrando un Inicio de Sesión de Usuario
```php
// Paso 1: Registrar un oyente
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username logged in at $time");
});

// Paso 2: Activarlo en tu app
Flight::route('/login', function () {
    $username = 'bob'; // Pretende que esto viene de un formulario
    Flight::triggerEvent('user.login', $username);
    echo "Hi, $username!";
});
```
**Por Qué Es Útil**: El código de inicio de sesión no necesita saber sobre el registro—solo activa el evento. Puedes agregar más oyentes después (por ejemplo, enviar un correo de bienvenida) sin cambiar la ruta.

#### Ejemplo 2: Notificando Sobre Nuevos Usuarios
```php
// Oyente para nuevos registros
Flight::onEvent('user.registered', function ($email, $name) {
    // Simular envío de correo
    echo "Email sent to $email: Welcome, $name!";
});

// Activar cuando alguien se registra
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Thanks for signing up!";
});
```
**Por Qué Es Útil**: La lógica de registro se enfoca en crear el usuario, mientras que el evento maneja las notificaciones. Podrías agregar más oyentes (por ejemplo, registrar el registro) después.

#### Ejemplo 3: Limpiando un Caché
```php
// Oyente para limpiar un caché
Flight::onEvent('page.updated', function ($pageId) {
	// si usas el plugin flightphp/cache
    Flight::cache()->delete("page_$pageId");
    echo "Cache cleared for page $pageId.";
});

// Activar cuando se edita una página
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Pretende que actualizamos la página
    Flight::triggerEvent('page.updated', $pageId);
    echo "Page $pageId updated.";
});
```
**Por Qué Es Útil**: El código de edición no se preocupa por el caché—solo señala la actualización. Otras partes de la app pueden reaccionar según sea necesario.

### Mejores Prácticas

- **Nombra Eventos Claramente**: Usa nombres específicos como `'user.login'` o `'page.updated'` para que sea obvio qué hacen.
- **Mantén Oyentes Simples**: No pongas tareas lentas o complejas en oyentes—mantén tu app rápida.
- **Prueba Tus Eventos**: Actívalos manualmente para asegurar que los oyentes funcionen como se espera.
- **Usa Eventos con Sabiduría**: Son geniales para desacoplar, pero demasiados pueden hacer que tu código sea difícil de seguir—úsalos cuando tenga sentido.

El sistema de eventos en Flight PHP, con `Flight::onEvent()` y `Flight::triggerEvent()`, te da una manera simple pero poderosa de construir aplicaciones flexibles. Al permitir que diferentes partes de tu app se comuniquen entre sí a través de eventos, puedes mantener tu código organizado, reutilizable y fácil de expandir. Ya sea que estés registrando acciones, enviando notificaciones o gestionando actualizaciones, los eventos te ayudan a hacerlo sin enredar tu lógica. Además, con la capacidad de sobrescribir estos métodos, tienes la libertad de adaptar el sistema a tus necesidades. Empieza pequeño con un solo evento y observa cómo transforma la estructura de tu app!

### Eventos Integrados

Flight PHP viene con algunos eventos integrados que puedes usar para engancharte en el ciclo de vida del framework. Estos eventos se activan en puntos específicos del ciclo de solicitud/respuesta, permitiéndote ejecutar lógica personalizada cuando ocurren ciertas acciones.

#### Lista de Eventos Integrados
- **flight.request.received**: `function(Request $request)` Activado cuando se recibe, analiza y procesa una solicitud.
- **flight.error**: `function(Throwable $exception)` Activado cuando ocurre un error durante el ciclo de vida de la solicitud.
- **flight.redirect**: `function(string $url, int $status_code)` Activado cuando se inicia una redirección.
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` Activado cuando se verifica el caché para una clave específica y si hubo acierto o fallo en el caché.
- **flight.middleware.before**: `function(Route $route)` Activado después de que se ejecute el middleware before.
- **flight.middleware.after**: `function(Route $route)` Activado después de que se ejecute el middleware after.
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` Activado después de que se ejecute cualquier middleware.
- **flight.route.matched**: `function(Route $route)` Activado cuando se coincide una ruta, pero aún no se ejecuta.
- **flight.route.executed**: `function(Route $route, float $executionTime)` Activado después de que se ejecute y procese una ruta. `$executionTime` es el tiempo que tomó ejecutar la ruta (llamar al controlador, etc.).
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` Activado después de que se renderice una vista. `$executionTime` es el tiempo que tomó renderizar la plantilla. **Nota: Si sobrescribes el método `render`, necesitarás reactivar este evento.**
- **flight.response.sent**: `function(Response $response, float $executionTime)` Activado después de que se envíe una respuesta al cliente. `$executionTime` es el tiempo que tomó construir la respuesta.

## Ver También
- [Extending Flight](/learn/extending) - Cómo extender y personalizar la funcionalidad principal de Flight.
- [Cache](/awesome-plugins/php_file_cache) - Ejemplo de usar eventos para limpiar el caché cuando se actualiza una página.

## Solución de Problemas
- Si no ves que se llamen tus oyentes de eventos, asegúrate de registrarlos antes de activar los eventos. El orden de registro importa.

## Registro de Cambios
- v3.15.0 - Agregados eventos a Flight.