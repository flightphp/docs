# Complementos Asombrosos

Flight es increíblemente extensible. Hay varios complementos que se pueden usar para agregar funcionalidad a tu aplicación Flight. Algunos son apoyados oficialmente por el equipo de Flight y otros son bibliotecas micro/lite para ayudarte a comenzar.

## Documentación de la API

La documentación de la API es crucial para cualquier API. Ayuda a los desarrolladores a entender cómo interactuar con tu API y qué esperar a cambio. Hay un par de herramientas disponibles para ayudarte a generar la documentación de la API para tus proyectos Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Entrada de blog escrita por Daniel Schreiber sobre cómo usar la especificación OpenAPI con FlightPHP para construir tu API utilizando un enfoque centrado en la API.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI es una excelente herramienta para ayudarte a generar documentación de la API para tus proyectos Flight. Es muy fácil de usar y se puede personalizar para ajustarse a tus necesidades. Esta es la biblioteca PHP para ayudarte a generar la documentación de Swagger.

## Autenticación/Autorización

La autenticación y la autorización son cruciales para cualquier aplicación que requiera controles sobre quién puede acceder a qué.

- [flightphp/permissions](/awesome-plugins/permissions) - Biblioteca oficial de Permisos de Flight. Esta biblioteca es una forma sencilla de agregar permisos a nivel de usuario y aplicación a tu aplicación.

## Caché

El almacenamiento en caché es una excelente manera de acelerar tu aplicación. Hay varias bibliotecas de caché que se pueden utilizar con Flight.

- [flightphp/cache](/awesome-plugins/php-file-cache) - Clase de caché en archivo de PHP ligera, simple y autónoma.

## CLI

Las aplicaciones CLI son una excelente manera de interactuar con tu aplicación. Puedes usarlas para generar controladores, mostrar todas las rutas y más.

- [flightphp/runway](/awesome-plugins/runway) - Runway es una aplicación CLI que te ayuda a gestionar tus aplicaciones Flight.

## Cookies

Las cookies son una excelente manera de almacenar pequeños fragmentos de datos en el lado del cliente. Se pueden utilizar para almacenar preferencias de usuario, configuraciones de la aplicación y más.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie es una biblioteca PHP que proporciona una manera simple y efectiva de gestionar cookies.

## Depuración

La depuración es crucial cuando estás desarrollando en tu entorno local. Hay algunos complementos que pueden mejorar tu experiencia de depuración.

- [tracy/tracy](/awesome-plugins/tracy) - Este es un controlador de errores completo que se puede usar con Flight. Tiene varios paneles que pueden ayudarte a depurar tu aplicación. También es muy fácil de extender y agregar tus propios paneles.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Usado con el controlador de errores [Tracy](/awesome-plugins/tracy), este complemento agrega algunos paneles extra para ayudar con la depuración específicamente para proyectos Flight.

## Bases de datos

Las bases de datos son el núcleo de la mayoría de las aplicaciones. Así es como almacenas y recuperas datos. Algunas bibliotecas de bases de datos son simplemente envolturas para escribir consultas y otras son ORM completos.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Envoltura PDO oficial de Flight que es parte del núcleo. Esta es una envoltura simple para ayudar a simplificar el proceso de escribir consultas y ejecutarlas. No es un ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord oficial de Flight. Gran biblioteca para recuperar y almacenar datos fácilmente en tu base de datos.
- [byjg/php-migration](/awesome-plugins/migrations) - Complemento para rastrear todos los cambios de la base de datos para tu proyecto.

## Cifrado

El cifrado es crucial para cualquier aplicación que almacene datos sensibles. Cifrar y descifrar los datos no es terriblemente difícil, pero almacenar correctamente la clave de cifrado [puede](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [ser](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difícil](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Lo más importante es nunca almacenar tu clave de cifrado en un directorio público o comprometerla a tu repositorio de código.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Esta es una biblioteca que se puede usar para cifrar y descifrar datos. Empezar a cifrar y descifrar datos es bastante simple.

## Cola de Trabajos

Las colas de trabajos son muy útiles para procesar tareas de manera asincrónica. Esto puede ser enviar correos electrónicos, procesar imágenes, o cualquier cosa que no necesite hacerse en tiempo real.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue es una biblioteca que se puede usar para procesar trabajos de manera asincrónica. Se puede utilizar con beanstalkd, MySQL/MariaDB, SQLite y PostgreSQL.

## Sesión

Las sesiones no son realmente útiles para las APIs, pero para desarrollar una aplicación web, las sesiones pueden ser cruciales para mantener el estado y la información de inicio de sesión.

- [Ghostff/Session](/awesome-plugins/session) - Administrador de Sesiones PHP (sin bloqueo, flash, segmento, cifrado de sesión). Utiliza PHP open_ssl para cifrado/descifrado opcional de los datos de sesión.

## Plantillas

Las plantillas son fundamentales para cualquier aplicación web con una interfaz de usuario. Hay varios motores de plantillas que se pueden utilizar con Flight.

- [flightphp/core View](/learn#views) - Este es un motor de plantillas muy básico que es parte del núcleo. No se recomienda usarlo si tienes más de un par de páginas en tu proyecto.
- [latte/latte](/awesome-plugins/latte) - Latte es un motor de plantillas completo que es muy fácil de usar y se siente más cercano a una sintaxis de PHP que Twig o Smarty. También es muy fácil de extender y agregar tus propios filtros y funciones.

## Contribuciones

¿Tienes un complemento que te gustaría compartir? ¡Envía una solicitud de extracción para agregarlo a la lista!