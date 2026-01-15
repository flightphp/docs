# Aprende Sobre Flight

Flight es un framework rápido, simple y extensible para PHP. Es bastante versátil y se puede usar para construir cualquier tipo de aplicación web. 
Está construido con la simplicidad en mente y está escrito de una manera que es fácil de entender y usar.

> **Nota:** Verás ejemplos que usan `Flight::` como una variable estática y algunos que usan el objeto Engine `$app->`. Ambos funcionan de manera intercambiable con el otro. `$app` y `$this->app` en un controlador/intermediario es el enfoque recomendado por el equipo de Flight.

## Componentes Principales

### [Routing](/learn/routing)

Aprende cómo gestionar rutas para tu aplicación web. Esto también incluye agrupar rutas, parámetros de ruta e intermediarios.

### [Middleware](/learn/middleware)

Aprende cómo usar intermediarios para filtrar solicitudes y respuestas en tu aplicación.

### [Autoloading](/learn/autoloading)

Aprende cómo cargar automáticamente tus propias clases en tu aplicación.

### [Requests](/learn/requests)

Aprende cómo manejar solicitudes y respuestas en tu aplicación.

### [Responses](/learn/responses)

Aprende cómo enviar respuestas a tus usuarios.

### [HTML Templates](/learn/templates)

Aprende cómo usar el motor de vistas integrado para renderizar tus plantillas HTML.

### [Security](/learn/security)

Aprende cómo asegurar tu aplicación contra amenazas de seguridad comunes.

### [Configuration](/learn/configuration)

Aprende cómo configurar el framework para tu aplicación.

### [Event Manager](/learn/events)

Aprende cómo usar el sistema de eventos para agregar eventos personalizados a tu aplicación.

### [Extending Flight](/learn/extending)

Aprende cómo extender el framework agregando tus propios métodos y clases.

### [Method Hooks and Filtering](/learn/filtering)

Aprende cómo agregar ganchos de eventos a tus métodos y métodos internos del framework.

### [Dependency Injection Container (DIC)](/learn/dependency-injection-container)

Aprende cómo usar contenedores de inyección de dependencias (DIC) para gestionar las dependencias de tu aplicación.

## Clases de Utilidad

### [Collections](/learn/collections)

Las colecciones se usan para mantener datos y ser accesibles como un array o como un objeto para facilitar su uso.

### [JSON Wrapper](/learn/json)

Esto tiene unas pocas funciones simples para hacer que la codificación y decodificación de tu JSON sea consistente.

### [SimplePdo](/learn/simple-pdo)

PDO a veces puede agregar más dolores de cabeza de los necesarios. SimplePdo es una clase auxiliar moderna para PDO con métodos convenientes como `insert()`, `update()`, `delete()` y `transaction()` para hacer las operaciones de base de datos mucho más fáciles.

### [PdoWrapper](/learn/pdo-wrapper) (Deprecated)

El envoltorio original de PDO está obsoleto a partir de v3.18.0. Por favor, usa [SimplePdo](/learn/simple-pdo) en su lugar.

### [Uploaded File Handler](/learn/uploaded-file)

Una clase simple para ayudar a gestionar archivos subidos y moverlos a una ubicación permanente.

## Conceptos Importantes

### [Why a Framework?](/learn/why-frameworks)

Aquí hay un artículo corto sobre por qué deberías usar un framework. Es una buena idea entender los beneficios de usar un framework antes de comenzar a usarlo.

Además, se ha creado un excelente tutorial por [@lubiana](https://git.php.fail/lubiana). Aunque no entra en gran detalle sobre Flight específicamente, 
esta guía te ayudará a entender algunos de los conceptos principales que rodean a un framework y por qué son beneficiosos de usar. 
Puedes encontrar el tutorial [aquí](https://git.php.fail/lubiana/no-framework-tutorial/src/branch/master/README.md).

### [Flight Compared to Other Frameworks](/learn/flight-vs-another-framework)

Si estás migrando desde otro framework como Laravel, Slim, Fat-Free o Symfony a Flight, esta página te ayudará a entender las diferencias entre los dos.

## Otros Temas

### [Unit Testing](/learn/unit-testing)

Sigue esta guía para aprender cómo probar unidades tu código de Flight para que sea sólido como una roca.

### [AI & Developer Experience](/learn/ai)

Aprende cómo Flight funciona con herramientas de IA y flujos de trabajo modernos de desarrolladores para ayudarte a codificar más rápido y de manera más inteligente.

### [Migrating v2 -> v3](/learn/migrating-to-v3)

La compatibilidad hacia atrás se ha mantenido en su mayor parte, pero hay algunos cambios de los que debes estar al tanto al migrar de v2 a v3.