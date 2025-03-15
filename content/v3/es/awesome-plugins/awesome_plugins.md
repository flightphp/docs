# Complementos Asombrosos

Flight es increíblemente extensible. Hay una serie de complementos que se pueden usar para añadir funcionalidad a tu aplicación Flight. Algunos son oficialmente soportados por el equipo de Flight y otros son bibliotecas micro/lite para ayudarte a comenzar.

## Documentación de la API

La documentación de la API es crucial para cualquier API. Ayuda a los desarrolladores a entender cómo interactuar con tu API y qué esperar a cambio. Hay un par de herramientas disponibles para ayudarte a generar la documentación de la API para tus proyectos Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Publicación de blog escrita por Daniel Schreiber sobre cómo usar la especificación OpenAPI con FlightPHP para construir tu API utilizando un enfoque de API primero.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI es una gran herramienta para ayudarte a generar documentación de API para tus proyectos Flight. Es muy fácil de usar y se puede personalizar para adaptarse a tus necesidades. Esta es la biblioteca PHP para ayudarte a generar la documentación de Swagger.

## Monitoreo del Rendimiento de la Aplicación (APM)

El Monitoreo del Rendimiento de la Aplicación (APM) es crucial para cualquier aplicación. Te ayuda a entender cómo está funcionando tu aplicación y dónde están los cuellos de botella. Hay una serie de herramientas APM que se pueden usar con Flight.
- <span class="badge bg-info">beta</span>[flightphp/apm](/awesome-plugins/apm) - Flight APM es una biblioteca APM simple que se puede usar para monitorear tus aplicaciones Flight. Se puede usar para monitorear el rendimiento de tu aplicación y ayudarte a identificar cuellos de botella.

## Autenticación/Autorización

La Autenticación y la Autorización son cruciales para cualquier aplicación que requiera controles sobre quién puede acceder a qué.

- <span class="badge bg-primary">official</span> [flightphp/permissions](/awesome-plugins/permissions) - Biblioteca de Permisos oficial de Flight. Esta biblioteca es una forma simple de añadir permisos a nivel de usuario y de aplicación a tu aplicación.

## Caché

El caché es una gran manera de acelerar tu aplicación. Hay una serie de bibliotecas de caché que se pueden usar con Flight.

- <span class="badge bg-primary">official</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Clase de caché en archivo PHP liviana, simple y autónoma.

## CLI

Las aplicaciones CLI son una gran manera de interactuar con tu aplicación. Puedes usarlas para generar controladores, mostrar todas las rutas, y más.

- <span class="badge bg-primary">official</span> [flightphp/runway](/awesome-plugins/runway) - Runway es una aplicación CLI que te ayuda a gestionar tus aplicaciones Flight.

## Cookies

Las cookies son una gran manera de almacenar pequeñas cantidades de datos en el lado del cliente. Se pueden usar para almacenar preferencias de usuario, configuraciones de la aplicación y más.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie es una biblioteca PHP que proporciona una forma simple y efectiva de gestionar cookies.

## Depuración

La depuración es crucial cuando estás desarrollando en tu entorno local. Hay algunos complementos que pueden elevar tu experiencia de depuración.

- [tracy/tracy](/awesome-plugins/tracy) - Este es un controlador de errores con todas las funciones que se puede utilizar con Flight. Tiene una serie de paneles que pueden ayudarte a depurar tu aplicación. También es muy fácil de extender y añadir tus propios paneles.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Utilizado con el [Tracy](/awesome-plugins/tracy) controlador de errores, este complemento añade algunos paneles extra para ayudar con la depuración específicamente para proyectos Flight.

## Bases de Datos

Las bases de datos son el núcleo de la mayoría de las aplicaciones. Así es como almacenas y recuperas datos. Algunas bibliotecas de bases de datos son simplemente envoltorios para escribir consultas y algunas son ORM totalmente desarrollados.

- <span class="badge bg-primary">official</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Envoltorio PDO oficial de Flight que forma parte del núcleo. Este es un envoltorio simple para ayudar a simplificar el proceso de escritura de consultas y ejecutarlas. No es un ORM.
- <span class="badge bg-primary">official</span> [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord oficial de Flight. Gran biblioteca pequeña para recuperar y almacenar datos fácilmente en tu base de datos.
- [byjg/php-migration](/awesome-plugins/migrations) - Complemento para rastrear todos los cambios en la base de datos para tu proyecto.

## Cifrado

El cifrado es crucial para cualquier aplicación que almacene datos sensibles. Cifrar y descifrar los datos no es terriblemente complicado, pero almacenar correctamente la clave de cifrado [puede](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [ser](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difícil](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Lo más importante es nunca almacenar tu clave de cifrado en un directorio público o comprometerla en tu repositorio de código.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Esta es una biblioteca que se puede usar para cifrar y descifrar datos. Comenzar a usarla es bastante simple para comenzar a cifrar y descifrar datos.

## Cola de Trabajos

Las colas de trabajos son realmente útiles para procesar tareas de forma asíncrona. Esto puede ser enviar correos electrónicos, procesar imágenes, o cualquier cosa que no necesite hacerse en tiempo real.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue es una biblioteca que se puede usar para procesar trabajos de forma asíncrona. Se puede utilizar con beanstalkd, MySQL/MariaDB, SQLite y PostgreSQL.

## Sesión

Las sesiones no son realmente útiles para las API, pero para construir una aplicación web, las sesiones pueden ser cruciales para mantener el estado y la información de inicio de sesión.

- <span class="badge bg-primary">official</span> [flightphp/session](/awesome-plugins/session) - Biblioteca de Sesiones oficial de Flight. Esta es una biblioteca de sesiones simple que se puede usar para almacenar y recuperar datos de sesión. Utiliza el manejo de sesiones incorporado de PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - Gestor de Sesiones PHP (sin bloqueo, flash, segmento, cifrado de sesiones). Utiliza PHP open_ssl para cifrado/descifrado opcional de datos de sesión.

## Plantillas

Las plantillas son fundamentales para cualquier aplicación web con una interfaz de usuario. Hay una serie de motores de plantillas que se pueden usar con Flight.

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - Este es un motor de plantillas muy básico que forma parte del núcleo. No se recomienda su uso si tienes más de un par de páginas en tu proyecto.
- [latte/latte](/awesome-plugins/latte) - Latte es un motor de plantillas completo que es muy fácil de usar y se siente más cercano a una sintaxis PHP que Twig o Smarty. También es muy fácil de extender y añadir tus propios filtros y funciones.

## Contribuyendo

¿Tienes un complemento que te gustaría compartir? ¡Envía una solicitud de extracción para añadirlo a la lista!