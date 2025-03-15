# Sistema de Eventos en Flight PHP (v3.15.0+)

Flight PHP introduce un sistema de eventos ligero e intuitivo que te permite registrar y activar eventos personalizados en tu aplicación. Con la adición de `Flight::onEvent()` y `Flight::triggerEvent()`, ahora puedes engancharte a momentos clave del ciclo de vida de tu aplicación o definir tus propios eventos para hacer tu código más modular y extensible. Estos métodos son parte de los **métodos mapeables** de Flight, lo que significa que puedes anular su comportamiento para adaptarlo a tus necesidades.

Esta guía cubre todo lo que necesitas saber para empezar con los eventos, incluyendo por qué son valiosos, cómo utilizarlos y ejemplos prácticos para ayudar a los principiantes a entender su poder.

## ¿Por Qué Usar Eventos?

Los eventos te permiten separar diferentes partes de tu aplicación para que no dependan demasiado entre sí. Esta separación—frecuentemente llamada **desacoplamiento**—hace que tu código sea más fácil de actualizar, extender o depurar. En lugar de escribir todo en un gran bloque, puedes dividir tu lógica en piezas más pequeñas e independientes que respondan a acciones específicas (eventos).

Imagina que estás construyendo una aplicación de blog:
- Cuando un usuario publica un comentario, podrías querer:
  - Guardar el comentario en la base de datos.
  - Enviar un correo electrónico al propietario del blog.
  - Registrar la acción por razones de seguridad.

Sin eventos, tendrías que meter todo esto en una sola función. Con eventos, puedes dividirlo: una parte guarda el comentario, otra activa un evento como `'comment.posted'`, y escuchas por separado manejan el correo electrónico y el registro. Esto mantiene tu código más limpio y te permite añadir o quitar características (como notificaciones) sin tocar la lógica central.

### Usos Comunes
- **Registro**: Grabar acciones como inicios de sesión o errores sin desordenar tu código principal.
- **Notificaciones**: Enviar correos electrónicos o alertas cuando ocurre algo.
- **Actualizaciones**: Refrescar cachés o notificar a otros sistemas sobre cambios.

## Registrando Escuchadores de Eventos

Para escuchar un evento, utiliza `Flight::onEvent()`. Este método te permite definir qué debería suceder cuando ocurre un evento.

### Sintaxis
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event`: Un nombre para tu evento (ejemplo, `'user.login'`).
- `$callback`: La función que se debe ejecutar cuando se activa el evento.

### Cómo Funciona
Te "suscribes" a un evento diciendo a Flight qué hacer cuando sucede. El callback puede aceptar argumentos pasados desde el disparador de eventos.

El sistema de eventos de Flight es sincrónico, lo que significa que cada escuchador de eventos se ejecuta en secuencia, uno tras otro. Cuando activas un evento, todos los escuchadores registrados para ese evento se ejecutarán hasta completarse antes de que tu código continúe. Esto es importante entender ya que difiere de los sistemas de eventos asincrónicos donde los escuchadores podrían ejecutarse en paralelo o en un momento posterior.

### Ejemplo Simple
```php
Flight::onEvent('user.login', function ($username) {
    echo "¡Bienvenido de nuevo, $username!";
});
```
Aquí, cuando se activa el evento `'user.login'`, saludará al usuario por su nombre.

### Puntos Clave
- Puedes añadir múltiples escuchadores al mismo evento; se ejecutarán en el orden en que los registraste.
- El callback puede ser una función, una función anónima, o un método de una clase.

## Activando Eventos

Para hacer que un evento ocurra, utiliza `Flight::triggerEvent()`. Esto le dice a Flight que ejecute todos los escuchadores registrados para ese evento, pasando junto cualquier dato que proporciones.

### Sintaxis
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`: El nombre del evento que estás activando (debe coincidir con un evento registrado).
- `...$args`: Argumentos opcionales para enviar a los escuchadores (pueden ser cualquier número de argumentos).

### Ejemplo Simple
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Esto activa el evento `'user.login'` y envía `'alice'` al escuchador que definimos antes, que mostrará: `¡Bienvenido de nuevo, alice!`.

### Puntos Clave
- Si no hay escuchadores registrados, no ocurre nada; tu aplicación no se romperá.
- Usa el operador de expansión (`...`) para pasar múltiples argumentos de manera flexible.

### Registrando Escuchadores de Eventos

...

**Deteniendo Escuchadores Adicionales**:
Si un escuchador devuelve `false`, no se ejecutarán más escuchadores para ese evento. Esto te permite detener la cadena de eventos basándote en condiciones específicas. Recuerda, el orden de los escuchadores importa, ya que el primero en devolver `false` detendrá al resto de ejecutarse.

**Ejemplo**:
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // Detiene los escuchadores posteriores.
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // este nunca se enviará.
});
```

## Sobrescribiendo Métodos de Evento

`Flight::onEvent()` y `Flight::triggerEvent()` están disponibles para ser [extendidos](/learn/extending), lo que significa que puedes redefinir cómo funcionan. Esto es genial para usuarios avanzados que quieren personalizar el sistema de eventos, como añadir registro o cambiar cómo se despachan los eventos.

### Ejemplo: Personalizando `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Registra cada registro de evento
    error_log("Nuevo escuchador de evento añadido para: $event");
    // Llama al comportamiento predeterminado (suponiendo un sistema de eventos interno)
    Flight::_onEvent($event, $callback);
});
```
Ahora, cada vez que registras un evento, se registra antes de continuar.

### ¿Por Qué Sobrescribir?
- Añadir depuración o monitoreo.
- Restringir eventos en ciertos entornos (ejemplo, desactivar en pruebas).
- Integrar con una biblioteca de eventos diferente.

## Dónde Poner Tus Eventos

Como principiante, podrías preguntarte: *¿dónde registro todos estos eventos en mi aplicación?* La simplicidad de Flight significa que no hay una regla estricta; puedes ponerlos donde tenga sentido para tu proyecto. Sin embargo, mantenerlos organizados te ayudará a mantener tu código a medida que tu aplicación crezca. Aquí tienes algunas opciones prácticas y mejores prácticas, adaptadas a la naturaleza ligera de Flight:

### Opción 1: En Tu Archivo Principal `index.php`
Para aplicaciones pequeñas o prototipos rápidos, puedes registrar eventos directamente en tu archivo `index.php` junto con tus rutas. Esto mantiene todo en un solo lugar, lo cual está bien cuando la simplicidad es tu prioridad.

```php
require 'vendor/autoload.php';

// Registrar eventos
Flight::onEvent('user.login', function ($username) {
    error_log("$username inició sesión a las " . date('Y-m-d H:i:s'));
});

// Definir rutas
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "¡Conectado!";
});

Flight::start();
```
- **Pros**: Simple, sin archivos adicionales, excelente para proyectos pequeños.
- **Contras**: Puede volverse desordenado a medida que tu aplicación crece con más eventos y rutas.

### Opción 2: Un Archivo `events.php` Separado
Para una aplicación un poco más grande, considera mover las registraciones de eventos a un archivo dedicado como `app/config/events.php`. Incluye este archivo en tu `index.php` antes de tus rutas. Esto imita cómo a menudo se organizan las rutas en `app/config/routes.php` en los proyectos de Flight.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username inició sesión a las " . date('Y-m-d H:i:s'));
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "Correo enviado a $email: ¡Bienvenido, $name!";
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "¡Conectado!";
});

Flight::start();
```
- **Pros**: Mantiene `index.php` enfocado en el enrutamiento, organiza eventos lógicamente, fácil de encontrar y editar.
- **Contras**: Añade un poco de estructura, que podría parecer excesiva para aplicaciones muy pequeñas.

### Opción 3: Cerca de Donde Se Activan
Otro enfoque es registrar eventos cerca de donde se activan, como dentro de un controlador o definición de ruta. Esto funciona bien si un evento es específico de una parte de tu aplicación.

```php
Flight::route('/signup', function () {
    // Registrar evento aquí
    Flight::onEvent('user.registered', function ($email) {
        echo "¡Correo de bienvenida enviado a $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "¡Te has registrado!";
});
```
- **Pros**: Mantiene el código relacionado juntos, bueno para características aisladas.
- **Contras**: Dispersa las registraciones de eventos, dificultando la visualización de todos los eventos a la vez; arriesga registros duplicados si no se tiene cuidado.

### Mejor Práctica para Flight
- **Comienza Simple**: Para aplicaciones pequeñas, coloca eventos en `index.php`. Es rápido y se alinea con el minimalismo de Flight.
- **Crecimiento Inteligente**: A medida que tu aplicación se expande (por ejemplo, más de 5-10 eventos), utiliza un archivo `app/config/events.php`. Es un paso natural hacia arriba, como organizar rutas, y mantiene tu código ordenado sin añadir marcos complejos.
- **Evita la Sobrecarga**: No crees una clase o directorio completo de "gestor de eventos" a menos que tu aplicación se vuelva enorme; Flight prospera en la simplicidad, así que manténlo ligero.

### Consejo: Agrupar por Propósito
En `events.php`, agrupa eventos relacionados (por ejemplo, todos los eventos relacionados con usuarios juntos) con comentarios para mayor claridad:

```php
// app/config/events.php
// Eventos de Usuario
Flight::onEvent('user.login', function ($username) {
    error_log("$username inició sesión");
});
Flight::onEvent('user.registered', function ($email) {
    echo "¡Bienvenido a $email!";
});

// Eventos de Página
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);
});
```

Esta estructura escala bien y se mantiene amigable para principiantes.

## Ejemplos para Principiantes

Vamos a repasar algunos escenarios del mundo real para mostrar cómo funcionan los eventos y por qué son útiles.

### Ejemplo 1: Registro de un Inicio de Sesión de Usuario
```php
// Paso 1: Registrar un escuchador
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username inició sesión a las $time");
});

// Paso 2: Activarlo en tu aplicación
Flight::route('/login', function () {
    $username = 'bob'; // Supón que esto viene de un formulario
    Flight::triggerEvent('user.login', $username);
    echo "¡Hola, $username!";
});
```
**Por Qué Es Útil**: El código de inicio de sesión no necesita saber sobre el registro; simplemente activa el evento. Más tarde puedes añadir más escuchadores (por ejemplo, enviar un correo de bienvenida) sin cambiar la ruta.

### Ejemplo 2: Notificar Sobre Nuevos Usuarios
```php
// Escuchador para nuevos registros
Flight::onEvent('user.registered', function ($email, $name) {
    // Simular el envío de un correo electrónico
    echo "Correo enviado a $email: ¡Bienvenido, $name!";
});

// Activarlo cuando alguien se registra
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "¡Gracias por registrarte!";
});
```
**Por Qué Es Útil**: La lógica del registro se centra en crear al usuario, mientras que el evento maneja las notificaciones. Podrías añadir más escuchadores (por ejemplo, registrar la inscripción) más tarde.

### Ejemplo 3: Limpiando un Caché
```php
// Escuchador para limpiar un caché
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // Limpiar caché de sesión si es aplicable
    echo "Caché limpiada para la página $pageId.";
});

// Activarlo cuando se edita una página
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Supón que actualizamos la página
    Flight::triggerEvent('page.updated', $pageId);
    echo "Página $pageId actualizada.";
});
```
**Por Qué Es Útil**: El código de edición no se preocupa por el caché; simplemente señala la actualización. Otras partes de la aplicación pueden reaccionar según sea necesario.

## Mejores Prácticas

- **Nombra los Eventos Claramente**: Usa nombres específicos como `'user.login'` o `'page.updated'` para que sea obvio lo que hacen.
- **Mantén los Escuchadores Simples**: No pongas tareas lentas o complejas en escuchadores; mantén tu aplicación rápida.
- **Prueba Tus Eventos**: Actívalos manualmente para asegurar que los escuchadores funcionen como se espera.
- **Usa Eventos de Manera Inteligente**: Son geniales para desacoplar, pero demasiados pueden hacer que tu código sea difícil de seguir; úsalos cuando tenga sentido.

El sistema de eventos en Flight PHP, con `Flight::onEvent()` y `Flight::triggerEvent()`, te brinda una forma simple pero poderosa de construir aplicaciones flexibles. Al permitir que diferentes partes de tu aplicación se comuniquen entre sí a través de eventos, puedes mantener tu código organizado, reutilizable y fácil de expandir. Ya sea registrando acciones, enviando notificaciones o gestionando actualizaciones, los eventos te ayudan a hacerlo sin entrelazar tu lógica. Además, con la capacidad de sobrescribir estos métodos, tienes la libertad de adaptar el sistema a tus necesidades. Comienza con un evento único y observa cómo transforma la estructura de tu aplicación.

## Eventos Incorporados

Flight PHP viene con algunos eventos incorporados que puedes utilizar para engancharte al ciclo de vida del marco. Estos eventos se activan en puntos específicos del ciclo de solicitud/respuesta, lo que te permite ejecutar lógica personalizada cuando ocurren ciertas acciones.

### Lista de Eventos Incorporados
- **flight.request.received**: `function(Request $request)` Se activa cuando se recibe, analiza y procesa una solicitud.
- **flight.error**: `function(Throwable $exception)` Se activa cuando ocurre un error durante el ciclo de vida de la solicitud.
- **flight.redirect**: `function(string $url, int $status_code)` Se activa cuando se inicia una redirección.
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` Se activa cuando se verifica la caché para una clave específica y si la caché fue un acierto o un fallo.
- **flight.middleware.before**: `function(Route $route)` Se activa después de que se ejecuta el middleware anterior.
- **flight.middleware.after**: `function(Route $route)` Se activa después de que se ejecuta el middleware posterior.
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` Se activa después de que se ejecuta cualquier middleware.
- **flight.route.matched**: `function(Route $route)` Se activa cuando se coincide una ruta, pero aún no se ejecuta.
- **flight.route.executed**: `function(Route $route, float $executionTime)` Se activa después de que se ejecuta y procesa una ruta. `$executionTime` es el tiempo que tomó ejecutar la ruta (llamar al controlador, etc.).
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` Se activa después de que se renderiza una vista. `$executionTime` es el tiempo que tomó renderizar la plantilla. **Nota: Si sobrescribes el método `render`, necesitarás volver a activar este evento.**
- **flight.response.sent**: `function(Response $response, float $executionTime)` Se activa después de que se envía una respuesta al cliente. `$executionTime` es el tiempo que tomó construir la respuesta.