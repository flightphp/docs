# Sistema de Eventos en Flight PHP (v3.15.0+)

Flight PHP introduce un sistema de eventos ligero e intuitivo que te permite registrar y activar eventos personalizados en tu aplicación. Con la adición de `Flight::onEvent()` y `Flight::triggerEvent()`, ahora puedes conectarte a momentos clave del ciclo de vida de tu app o definir tus propios eventos para hacer que tu código sea más modular y extensible. Estos métodos forman parte de los **métodos mapeables** de Flight, lo que significa que puedes sobrescribir su comportamiento para adaptarlo a tus necesidades.

Esta guía cubre todo lo que necesitas saber para comenzar con los eventos, incluyendo por qué son valiosos, cómo usarlos y ejemplos prácticos para ayudar a los principiantes a entender su poder.

## ¿Por qué usar eventos?

Los eventos te permiten separar diferentes partes de tu aplicación para que no dependan demasiado unas de otras. Esta separación—conocida como **desacoplamiento**—hace que tu código sea más fácil de actualizar, extender o depurar. En lugar de escribir todo en un gran bloque, puedes dividir tu lógica en piezas más pequeñas e independientes que respondan a acciones específicas (eventos).

Imagina que estás construyendo una app de blog:
- Cuando un usuario publica un comentario, podrías querer:
  - Guardar el comentario en la base de datos.
  - Enviar un correo electrónico al propietario del blog.
  - Registrar la acción por seguridad.

Sin eventos, lo meterías todo en una sola función. Con eventos, puedes dividirlo: una parte guarda el comentario, otra activa un evento como `'comment.posted'`, y los escuchadores separados manejan el correo y el registro. Esto mantiene tu código más limpio y te permite agregar o eliminar características (como notificaciones) sin tocar la lógica principal.

### Usos comunes
- **Registro**: Registra acciones como inicios de sesión o errores sin saturar tu código principal.
- **Notificaciones**: Envía correos electrónicos o alertas cuando algo sucede.
- **Actualizaciones**: Actualiza cachés o notifica a otros sistemas sobre cambios.

## Registrando escuchadores de eventos

Para escuchar un evento, usa `Flight::onEvent()`. Este método te permite definir qué debe suceder cuando ocurre un evento.

### Sintaxis
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event`: Un nombre para tu evento (por ejemplo, `'user.login'`).
- `$callback`: La función que se ejecuta cuando se activa el evento.

### Cómo funciona
Te "susscribes" a un evento al indicarle a Flight qué hacer cuando sucede. El callback puede aceptar argumentos pasados desde la activación del evento.

El sistema de eventos de Flight es síncrono, lo que significa que cada escuchador de evento se ejecuta en secuencia, uno después del otro. Cuando activas un evento, todos los escuchadores registrados para ese evento se ejecutan hasta completarse antes de que tu código continúe. Esto es importante de entender, ya que difiere de los sistemas de eventos asíncronos donde los escuchadores podrían ejecutarse en paralelo o en un momento posterior.

### Ejemplo simple
```php
Flight::onEvent('user.login', function ($username) {
    echo "Welcome back, $username!";
});
```
Aquí, cuando se activa el evento `'user.login'`, saludará al usuario por su nombre.

### Puntos clave
- Puedes agregar múltiples escuchadores al mismo evento; se ejecutarán en el orden en que los registraste.
- El callback puede ser una función, una función anónima o un método de una clase.

## Activando eventos

Para hacer que ocurra un evento, usa `Flight::triggerEvent()`. Esto le indica a Flight que ejecute todos los escuchadores registrados para ese evento, pasando cualquier dato que proporciones.

### Sintaxis
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`: El nombre del evento que estás activando (debe coincidir con un evento registrado).
- `...$args`: Argumentos opcionales para enviar a los escuchadores (puede ser cualquier número de argumentos).

### Ejemplo simple
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Esto activa el evento `'user.login'` y envía `'alice'` al escuchador que definimos antes, lo que producirá: `Welcome back, alice!`.

### Puntos clave
- Si no hay escuchadores registrados, no sucede nada; tu app no se romperá.
- Usa el operador de propagación (`...`) para pasar múltiples argumentos de manera flexible.

### Deteniendo escuchadores posteriores
Si un escuchador devuelve `false`, no se ejecutarán escuchadores adicionales para ese evento. Esto te permite detener la cadena de eventos basada en condiciones específicas. Recuerda, el orden de los escuchadores importa, ya que el primero en devolver `false` detendrá el resto.

**Ejemplo**:
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // Detiene los escuchadores posteriores
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // esto nunca se envía
});
```

## Sobrescribiendo métodos de eventos

`Flight::onEvent()` y `Flight::triggerEvent()` están disponibles para ser [extendidos](/learn/extending), lo que significa que puedes redefinir cómo funcionan. Esto es genial para usuarios avanzados que quieran personalizar el sistema de eventos, como agregar registro o cambiar cómo se despachan los eventos.

### Ejemplo: Personalizando `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Registrar cada registro de evento
    error_log("Nuevo escuchador de evento agregado para: $event");
    // Llamar al comportamiento predeterminado (asumiendo un sistema de eventos interno)
    Flight::_onEvent($event, $callback);
});
```
Ahora, cada vez que registres un evento, se registra antes de proceder.

### ¿Por qué sobrescribir?
- Agregar depuración o monitoreo.
- Restringir eventos en ciertos entornos (por ejemplo, deshabilitarlos en pruebas).
- Integrar con una biblioteca de eventos diferente.

## Dónde colocar tus eventos

Como principiante, podrías preguntarte: *¿dónde registro todos estos eventos en mi app?* La simplicidad de Flight significa que no hay una regla estricta; puedes colocarlos donde tenga sentido para tu proyecto. Sin embargo, mantenerlos organizados te ayuda a mantener tu código a medida que tu app crece. Aquí hay algunas opciones prácticas y mejores prácticas, adaptadas a la naturaleza ligera de Flight:

### Opción 1: En tu archivo principal `index.php`
Para apps pequeñas o prototipos rápidos, puedes registrar eventos directamente en tu archivo `index.php` junto con tus rutas. Esto mantiene todo en un solo lugar, lo cual es adecuado cuando la simplicidad es tu prioridad.

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

### Opción 2: Un archivo separado `events.php`
Para una app un poco más grande, considera mover los registros de eventos a un archivo dedicado como `app/config/events.php`. Incluye este archivo en tu `index.php` antes de tus rutas. Esto imita cómo se organizan las rutas a menudo en `app/config/routes.php` en proyectos de Flight.

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

### Opción 3: Cerca de donde se activan
Otra aproximación es registrar eventos cerca de donde se activan, como dentro de un controlador o definición de ruta. Esto funciona bien si un evento es específico de una parte de tu app.

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
- **Pros**: Mantiene el código relacionado juntos, bueno para características aisladas.
- **Cons**: Dispersa los registros de eventos, haciendo más difícil ver todos los eventos de una vez; riesgo de registros duplicados si no se tiene cuidado.

### Mejor práctica para Flight
- **Comienza simple**: Para apps pequeñas, coloca eventos en `index.php`. Es rápido y se alinea con el minimalismo de Flight.
- **Crece inteligentemente**: A medida que tu app se expande (por ejemplo, más de 5-10 eventos), usa un archivo `app/config/events.php`. Es un paso natural, como organizar rutas, y mantiene tu código ordenado sin agregar marcos complejos.
- **Evita sobreingeniería**: No crees una clase o directorio completo de "gestor de eventos" a menos que tu app sea enorme; Flight prospera en la simplicidad, así que manténlo ligero.

### Consejo: Agrupa por propósito
En `events.php`, agrupa eventos relacionados (por ejemplo, todos los eventos relacionados con usuarios juntos) con comentarios para claridad:

```php
// app/config/events.php
// Eventos de usuario
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in");
});
Flight::onEvent('user.registered', function ($email) {
    echo "Welcome to $email!";
});

// Eventos de página
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);
});
```

Esta estructura escala bien y permanece amigable para principiantes.

## Ejemplos para principiantes

Recorramos algunos escenarios del mundo real para mostrar cómo funcionan los eventos y por qué son útiles.

### Ejemplo 1: Registrar un inicio de sesión de usuario
```php
// Paso 1: Registrar un escuchador
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username logged in at $time");
});

// Paso 2: Activarlo en tu app
Flight::route('/login', function () {
    $username = 'bob'; // Simula que esto viene de un formulario
    Flight::triggerEvent('user.login', $username);
    echo "Hi, $username!";
});
```
**Por qué es útil**: El código de inicio de sesión no necesita saber sobre el registro; solo activa el evento. Más tarde, puedes agregar más escuchadores (por ejemplo, enviar un correo de bienvenida) sin cambiar la ruta.

### Ejemplo 2: Notificar sobre nuevos usuarios
```php
// Escuchador para nuevos registros
Flight::onEvent('user.registered', function ($email, $name) {
    // Simular el envío de un correo electrónico
    echo "Email sent to $email: Welcome, $name!";
});

// Activarlo cuando alguien se registra
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Thanks for signing up!";
});
```
**Por qué es útil**: La lógica de registro se enfoca en crear el usuario, mientras que el evento maneja las notificaciones. Podrías agregar más escuchadores (por ejemplo, registrar el registro) más tarde.

### Ejemplo 3: Limpiar un caché
```php
// Escuchador para limpiar un caché
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // Limpiar caché de sesión si aplica
    echo "Cache cleared for page $pageId.";
});

// Activarlo cuando se edita una página
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Simula que actualizamos la página
    Flight::triggerEvent('page.updated', $pageId);
    echo "Page $pageId updated.";
});
```
**Por qué es útil**: El código de edición no se preocupa por el caché; solo señala la actualización. Otras partes de la app pueden reaccionar según sea necesario.

## Mejores prácticas

- **Nombra eventos claramente**: Usa nombres específicos como `'user.login'` o `'page.updated'` para que sea obvio qué hacen.
- **Mantén escuchadores simples**: No coloques tareas lentas o complejas en escuchadores; mantén tu app rápida.
- **Prueba tus eventos**: Actívalos manualmente para asegurarte de que los escuchadores funcionen como se espera.
- **Usa eventos con sabiduría**: Son geniales para desacoplar, pero demasiados pueden hacer que tu código sea difícil de seguir; úsalos cuando tenga sentido.

El sistema de eventos en Flight PHP, con `Flight::onEvent()` y `Flight::triggerEvent()`, te ofrece una forma simple pero poderosa de construir aplicaciones flexibles. Al permitir que diferentes partes de tu app se comuniquen a través de eventos, puedes mantener tu código organizado, reutilizable y fácil de expandir. Ya sea que estés registrando acciones, enviando notificaciones o manejando actualizaciones, los eventos te ayudan a hacerlo sin enredar tu lógica. Además, con la capacidad de sobrescribir estos métodos, tienes la libertad de adaptar el sistema a tus necesidades. Comienza pequeño con un solo evento y observa cómo transforma la estructura de tu app.

## Eventos incorporados

Flight PHP viene con algunos eventos incorporados que puedes usar para conectarte al ciclo de vida del framework. Estos eventos se activan en puntos específicos del ciclo de solicitud/respuesta, permitiéndote ejecutar lógica personalizada cuando ocurren ciertas acciones.

### Lista de eventos incorporados
- **flight.request.received**: `function(Request $request)` Se activa cuando se recibe una solicitud, se analiza y procesa.
- **flight.error**: `function(Throwable $exception)` Se activa cuando ocurre un error durante el ciclo de vida de la solicitud.
- **flight.redirect**: `function(string $url, int $status_code)` Se activa cuando se inicia una redirección.
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` Se activa cuando se verifica el caché para una clave específica y si hay acierto o fallo en el caché.
- **flight.middleware.before**: `function(Route $route)` Se activa después de que se ejecuta el middleware antes.
- **flight.middleware.after**: `function(Route $route)` Se activa después de que se ejecuta el middleware después.
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` Se activa después de que se ejecuta cualquier middleware.
- **flight.route.matched**: `function(Route $route)` Se activa cuando se coincide con una ruta, pero aún no se ejecuta.
- **flight.route.executed**: `function(Route $route, float $executionTime)` Se activa después de que se ejecuta y procesa una ruta. `$executionTime` es el tiempo que tardó en ejecutar la ruta (llamar al controlador, etc.).
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` Se activa después de que se renderiza una vista. `$executionTime` es el tiempo que tardó en renderizar la plantilla. **Nota: Si sobrescribes el método `render`, necesitarás reactivar este evento.**
- **flight.response.sent**: `function(Response $response, float $executionTime)` Se activa después de que se envía una respuesta al cliente. `$executionTime` es el tiempo que tardó en construir la respuesta.