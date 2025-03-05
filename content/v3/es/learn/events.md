# Sistema de Eventos en Flight PHP (v3.15.0+)

Flight PHP introduce un sistema de eventos ligero e intuitivo que te permite registrar y activar eventos personalizados en tu aplicación. Con la adición de `Flight::onEvent()` y `Flight::triggerEvent()`, ahora puedes engancharte a momentos clave del ciclo de vida de tu aplicación o definir tus propios eventos para hacer tu código más modular y extensible. Estos métodos son parte de los **métodos mapeables** de Flight, lo que significa que puedes sobrescribir su comportamiento para adaptarlo a tus necesidades.

Esta guía cubre todo lo que necesitas saber para comenzar con eventos, incluyendo por qué son valiosos, cómo usarlos y ejemplos prácticos para ayudar a los principiantes a entender su poder.

## ¿Por qué usar eventos?

Los eventos te permiten separar diferentes partes de tu aplicación para que no dependan demasiado entre sí. Esta separación—frecuentemente llamada **desacoplamiento**—hace que tu código sea más fácil de actualizar, extender o depurar. En lugar de escribir todo en un solo bloque grande, puedes dividir tu lógica en piezas más pequeñas e independientes que responden a acciones específicas (eventos).

Imagina que estás construyendo una aplicación de blog:
- Cuando un usuario publica un comentario, podrías querer:
  - Guardar el comentario en la base de datos.
  - Enviar un correo electrónico al propietario del blog.
  - Registrar la acción por seguridad.

Sin eventos, meterías todo esto en una sola función. Con eventos, puedes dividirlo: una parte guarda el comentario, otra activa un evento como `'comment.posted'`, y oyentes separados manejan el correo electrónico y el registro. Esto mantiene tu código más limpio y te permite agregar o eliminar características (como notificaciones) sin tocar la lógica central.

### Usos Comunes
- **Registro**: Registra acciones como inicios de sesión o errores sin desordenar tu código principal.
- **Notificaciones**: Envía correos electrónicos o alertas cuando algo sucede.
- **Actualizaciones**: Actualiza cachés o notifica a otros sistemas sobre cambios.

## Registro de Oyentes de Eventos

Para escuchar un evento, utiliza `Flight::onEvent()`. Este método te permite definir lo que debería suceder cuando un evento ocurre.

### Sintaxis
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event`: Un nombre para tu evento (por ejemplo, `'user.login'`).
- `$callback`: La función que se ejecutará cuando se active el evento.

### Cómo Funciona
"Te suscribes" a un evento diciéndole a Flight qué hacer cuando sucede. El callback puede aceptar argumentos que se pasan desde el disparador del evento.

El sistema de eventos de Flight es sincrónico, lo que significa que cada oyente de eventos se ejecuta en secuencia, uno tras otro. Cuando activas un evento, todos los oyentes registrados para ese evento se ejecutarán completamente antes de que tu código continúe. Esto es importante de entender, ya que difiere de los sistemas de eventos asincrónicos donde los oyentes pueden ejecutarse en paralelo o en un momento posterior.

### Ejemplo Sencillo
```php
Flight::onEvent('user.login', function ($username) {
    echo "¡Bienvenido de nuevo, $username!";
});
```
Aquí, cuando se activa el evento `'user.login'`, saludará al usuario por su nombre.

### Puntos Clave
- Puedes agregar múltiples oyentes al mismo evento: se ejecutarán en el orden en que los registraste.
- El callback puede ser una función, una función anónima o un método de una clase.

## Activación de Eventos

Para hacer que un evento suceda, utiliza `Flight::triggerEvent()`. Esto le dice a Flight que ejecute todos los oyentes registrados para ese evento, pasando cualquier dato que proporciones.

### Sintaxis
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`: El nombre del evento que estás activando (debe coincidir con un evento registrado).
- `...$args`: Argumentos opcionales para enviar a los oyentes (puede ser cualquier número de argumentos).

### Ejemplo Sencillo
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Esto activa el evento `'user.login'` y envía `'alice'` al oyente que definimos anteriormente, que mostrará: `¡Bienvenido de nuevo, alice!`.

### Puntos Clave
- Si no hay oyentes registrados, no sucede nada: tu aplicación no se romperá.
- Utiliza el operador de expansión (`...`) para pasar múltiples argumentos de manera flexible.

### Registro de Oyentes de Eventos

...

**Deteniendo Oyentes Adicionales**:
Si un oyente devuelve `false`, no se ejecutarán oyentes adicionales para ese evento. Esto te permite detener la cadena de eventos según condiciones específicas. Recuerda, el orden de los oyentes es importante, ya que el primero que devuelva `false` detendrá el resto.

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

## Sobrescribiendo Métodos de Eventos

`Flight::onEvent()` y `Flight::triggerEvent()` están disponibles para ser [extendidos](/learn/extending), lo que significa que puedes redefinir cómo funcionan. Esto es excelente para usuarios avanzados que desean personalizar el sistema de eventos, como agregar registro o cambiar la forma en que se envían los eventos.

### Ejemplo: Personalizando `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Registra cada registro de evento
    error_log("Nuevo oyente de evento agregado para: $event");
    // Llama al comportamiento por defecto (asumiendo un sistema de eventos interno)
    Flight::_onEvent($event, $callback);
});
```
Ahora, cada vez que registras un evento, se registra antes de continuar.

### ¿Por qué sobrescribir?
- Agregar depuración o monitoreo.
- Restringir eventos en ciertos entornos (por ejemplo, deshabilitar en pruebas).
- Integrar con una biblioteca de eventos diferente.

## Dónde Colocar Tus Eventos

Como principiante, podrías preguntarte: *¿dónde registro todos estos eventos en mi aplicación?* La simplicidad de Flight significa que no hay reglas estrictas: puedes colocarlos donde tenga sentido para tu proyecto. Sin embargo, mantenerlos organizados te ayuda a mantener tu código a medida que tu aplicación crece. Aquí hay algunas opciones prácticas y mejores prácticas, adaptadas a la naturaleza ligera de Flight:

### Opción 1: En tu `index.php` Principal
Para aplicaciones pequeñas o prototipos rápidos, puedes registrar eventos directamente en tu archivo `index.php` junto a tus rutas. Esto mantiene todo en un solo lugar, lo cual está bien cuando la simplicidad es tu prioridad.

```php
require 'vendor/autoload.php';

// Registrar eventos
Flight::onEvent('user.login', function ($username) {
    error_log("$username inició sesión en " . date('Y-m-d H:i:s'));
});

// Definir rutas
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "¡Iniciado sesión!";
});

Flight::start();
```
- **Pros**: Simple, sin archivos extra, excelente para proyectos pequeños.
- **Contras**: Puede volverse desordenado a medida que tu aplicación crece con más eventos y rutas.

### Opción 2: Un Archivo `events.php` Separado
Para una aplicación ligeramente más grande, considera mover registros de eventos a un archivo dedicado como `app/config/events.php`. Incluye este archivo en tu `index.php` antes de tus rutas. Esto imita cómo a menudo se organizan las rutas en `app/config/routes.php` en proyectos de Flight.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username inició sesión en " . date('Y-m-d H:i:s'));
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
    echo "¡Iniciado sesión!";
});

Flight::start();
```
- **Pros**: Mantiene `index.php` enfocado en el enrutamiento, organiza los eventos lógicamente, fácil de encontrar y editar.
- **Contras**: Agrega un poco de estructura, lo que podría sentirse como excesivo para aplicaciones muy pequeñas.

### Opción 3: Cerca de Donde Son Activados
Otro enfoque es registrar eventos cerca de donde son activados, como dentro de un controlador o definición de ruta. Esto funciona bien si un evento es específico de una parte de tu aplicación.

```php
Flight::route('/signup', function () {
    // Registrar evento aquí
    Flight::onEvent('user.registered', function ($email) {
        echo "Correo de bienvenida enviado a $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "¡Registrado!";
});
```
- **Pros**: Mantiene el código relacionado junto, bueno para características aisladas.
- **Contras**: Dispersa los registros de eventos, dificultando ver todos los eventos a la vez; riesgo de registros duplicados si no se tiene cuidado.

### Mejores Prácticas para Flight
- **Comienza Simple**: Para aplicaciones pequeñas, coloca eventos en `index.php`. Es rápido y se alinea con el minimalismo de Flight.
- **Crecimiento Inteligente**: A medida que tu aplicación se expande (por ejemplo, más de 5-10 eventos), usa un archivo `app/config/events.php`. Es un paso natural hacia arriba, como organizar rutas, y mantiene tu código ordenado sin añadir marcos complejos.
- **Evita la Sobrecarga de Ingeniería**: No crees una clase o directorio de "gestor de eventos" a menos que tu aplicación crezca enormemente: Flight prospera en la simplicidad, así que manténlo ligero.

### Consejo: Agrupar por Propósito
En `events.php`, agrupa eventos relacionados (por ejemplo, todos los eventos relacionados con usuarios juntos) con comentarios para claridad:

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

Veamos algunos escenarios reales para mostrar cómo funcionan los eventos y por qué son útiles.

### Ejemplo 1: Registrando un Inicio de Sesión de Usuario
```php
// Paso 1: Registrar un oyente
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username inició sesión en $time");
});

// Paso 2: Activarlo en tu aplicación
Flight::route('/login', function () {
    $username = 'bob'; // Suponiendo que esto proviene de un formulario
    Flight::triggerEvent('user.login', $username);
    echo "¡Hola, $username!";
});
```
**Por qué es útil**: El código de inicio de sesión no necesita saber sobre el registro: simplemente activa el evento. Podrías agregar más oyentes más tarde (por ejemplo, enviar un correo de bienvenida) sin cambiar la ruta.

### Ejemplo 2: Notificando sobre Nuevos Usuarios
```php
// Oyente para nuevos registros
Flight::onEvent('user.registered', function ($email, $name) {
    // Simular el envío de un correo electrónico
    echo "Correo enviado a $email: ¡Bienvenido, $name!";
});

// Activarlo cuando alguien se registre
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "¡Gracias por registrarte!";
});
```
**Por qué es útil**: La lógica de registro se centra en crear al usuario, mientras que el evento maneja las notificaciones. Podrías agregar más oyentes (por ejemplo, registrar el registro) más tarde.

### Ejemplo 3: Limpiando una Caché
```php
// Oyente para limpiar una caché
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // Limpiar caché de sesión si corresponde
    echo "Caché limpiada para la página $pageId.";
});

// Activarlo cuando se edita una página
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Suponiendo que actualizamos la página
    Flight::triggerEvent('page.updated', $pageId);
    echo "Página $pageId actualizada.";
});
```
**Por qué es útil**: El código de edición no se preocupa por la caché: simplemente señala la actualización. Otras partes de la aplicación pueden reaccionar según sea necesario.

## Mejores Prácticas

- **Nombra los eventos claramente**: Usa nombres específicos como `'user.login'` o `'page.updated'` para que sea evidente lo que hacen.
- **Mantén los oyentes simples**: No pongas tareas lentas o complejas en los oyentes: mantén tu aplicación rápida.
- **Prueba tus eventos**: Actívalos manualmente para asegurar que los oyentes funcionen como se espera.
- **Usa eventos sabiamente**: Son excelentes para desacoplar, pero demasiados pueden complicar tu código: úsalos cuando tenga sentido.

El sistema de eventos en Flight PHP, con `Flight::onEvent()` y `Flight::triggerEvent()`, te ofrece una forma simple pero poderosa de construir aplicaciones flexibles. Al permitir que diferentes partes de tu aplicación se comuniquen entre sí a través de eventos, puedes mantener tu código organizado, reutilizable y fácil de expandir. Ya sea que estés registrando acciones, enviando notificaciones o gestionando actualizaciones, los eventos te ayudan a hacerlo sin enredar tu lógica. Además, con la capacidad de sobrescribir estos métodos, tienes la libertad de adaptar el sistema a tus necesidades. Comienza con un solo evento y observa cómo transforma la estructura de tu aplicación.

## Eventos Incorporados

Flight PHP viene con algunos eventos incorporados que puedes usar para engancharte al ciclo de vida del marco. Estos eventos se activan en puntos específicos del ciclo de solicitud/respuesta, permitiéndote ejecutar lógica personalizada cuando ocurren ciertas acciones.

### Lista de Eventos Incorporados
- `flight.request.received`: Activado cuando se recibe, analiza y procesa una solicitud.
- `flight.route.middleware.before`: Activado después de que se ejecute el middleware anterior.
- `flight.route.middleware.after`: Activado después de que se ejecute el middleware posterior.
- `flight.route.executed`: Activado después de que se ejecute y procese una ruta.
- `flight.response.sent`: Activado después de que se envíe una respuesta al cliente.