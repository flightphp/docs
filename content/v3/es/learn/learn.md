# Aprende Sobre Flight

Flight es un framework rápido, simple y extensible para PHP. Es bastante versátil y se puede usar para construir cualquier tipo de aplicación web. 
Está construido con la simplicidad en mente y está escrito de una manera que es fácil de entender y usar.

> **Nota:** Verás ejemplos que usan `Flight::` como una variable estática y algunos que usan el objeto Engine `$app->`. Ambos funcionan de manera intercambiable con el otro. `$app` y `$this->app` en un controlador/middleware es el enfoque recomendado por el equipo de Flight.

## Componentes Principales

### [Enrutamiento](/learn/routing)

Aprende cómo gestionar rutas para tu aplicación web. Esto también incluye agrupar rutas, parámetros de ruta y middleware.

### [Middleware](/learn/middleware)

Aprende cómo usar middleware para filtrar solicitudes y respuestas en tu aplicación.

### [Carga Automática](/learn/autoloading)

Aprende cómo cargar automáticamente tus propias clases en tu aplicación.

### [Solicitudes](/learn/requests)

Aprende cómo manejar solicitudes y respuestas en tu aplicación.

### [Respuestas](/learn/responses)

Aprende cómo enviar respuestas a tus usuarios.

### [Plantillas HTML](/learn/templates)

Aprende cómo usar el motor de vistas incorporado para renderizar tus plantillas HTML.

### [Seguridad](/learn/security)

Aprende cómo asegurar tu aplicación contra amenazas de seguridad comunes.

### [Configuración](/learn/configuration)

Aprende cómo configurar el framework para tu aplicación.

### [Gestor de Eventos](/learn/events)

Aprende cómo usar el sistema de eventos para agregar eventos personalizados a tu aplicación.

### [Extender Flight](/learn/extending)

Aprende cómo extender el framework agregando tus propios métodos y clases.

### [Ganchos de Métodos y Filtrado](/learn/filtering)

Aprende cómo agregar ganchos de eventos a tus métodos y métodos internos del framework.

### [Contenedor de Inyección de Dependencias (DIC)](/learn/dependency-injection-container)

Aprende cómo usar contenedores de inyección de dependencias (DIC) para gestionar las dependencias de tu aplicación.

## Clases de Utilidad

### [Colecciones](/learn/collections)

Las colecciones se usan para contener datos y ser accesibles como un arreglo o como un objeto para facilitar su uso.

### [Envoltorio JSON](/learn/json)

Esto tiene unas pocas funciones simples para hacer que la codificación y decodificación de tu JSON sea consistente.

### [Envoltorio PDO](/learn/pdo-wrapper)

PDO a veces puede causar más dolores de cabeza de los necesarios. Esta clase envolvente simple puede hacer que sea significativamente más fácil interactuar con tu base de datos.

### [Manejador de Archivos Subidos](/learn/uploaded-file)

Una clase simple para ayudar a gestionar archivos subidos y moverlos a una ubicación permanente.

## Conceptos Importantes

### [¿Por Qué un Framework?](/learn/why-frameworks)

Aquí hay un artículo corto sobre por qué deberías usar un framework. Es una buena idea entender los beneficios de usar un framework antes de comenzar a usar uno.

Además, se ha creado un excelente tutorial por [@lubiana](https://git.php.fail/lubiana). Aunque no entra en gran detalle sobre Flight específicamente, 
esta guía te ayudará a entender algunos de los conceptos principales que rodean a un framework y por qué son beneficiosos de usar. 
Puedes encontrar el tutorial [aquí](https://git.php.fail/lubiana/no-framework-tutorial/src/branch/master/README.md).

### [Flight Comparado con Otros Frameworks](/learn/flight-vs-another-framework)

Si estás migrando desde otro framework como Laravel, Slim, Fat-Free o Symfony a Flight, esta página te ayudará a entender las diferencias entre los dos.

## Otros Temas

### [Pruebas Unitarias](/learn/unit-testing)

Sigue esta guía para aprender cómo realizar pruebas unitarias en tu código de Flight para que sea sólido como una roca.

### [IA y Experiencia del Desarrollador](/learn/ai)

Aprende cómo Flight funciona con herramientas de IA y flujos de trabajo modernos de desarrolladores para ayudarte a codificar más rápido e inteligente.

### [Migrando v2 -> v3](/learn/migrating-to-v3)

La compatibilidad hacia atrás se ha mantenido en su mayor parte, pero hay algunos cambios de los que debes estar al tanto al migrar de v2 a v3.