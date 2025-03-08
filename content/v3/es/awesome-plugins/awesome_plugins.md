# Plugins Asombrosos

Flight es increíblemente extensible. Hay una serie de plugins que se pueden usar para agregar funcionalidad a tu aplicación Flight. Algunos son oficialmente compatibles con el equipo de Flight y otros son bibliotecas micro/lite para ayudarte a comenzar.

## Documentación de la API

La documentación de la API es crucial para cualquier API. Ayuda a los desarrolladores a entender cómo interactuar con tu API y qué esperar a cambio. Hay un par de herramientas disponibles para ayudarte a generar documentación de la API para tus proyectos Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Publicación de blog escrita por Daniel Schreiber sobre cómo usar la especificación OpenAPI con FlightPHP para construir tu API utilizando un enfoque primero de API.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI es una gran herramienta para ayudarte a generar documentación de API para tus proyectos Flight. Es muy fácil de usar y se puede personalizar para adaptarse a tus necesidades. Esta es la biblioteca PHP para ayudarte a generar la documentación Swagger.

## Autenticación/Autorización

La autenticación y la autorización son cruciales para cualquier aplicación que requiera controles sobre quién puede acceder a qué.

- <span class="badge bg-primary">oficial</span> [flightphp/permissions](/awesome-plugins/permissions) - Biblioteca de permisos de Flight oficial. Esta biblioteca es una forma simple de agregar permisos a nivel de usuario y a nivel de aplicación a tu aplicación.

## Caching

El caching es una excelente manera de acelerar tu aplicación. Hay varias bibliotecas de caching que se pueden utilizar con Flight.

- <span class="badge bg-primary">oficial</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Clase de caching en archivo PHP ligera, simple y autónoma.

## CLI

Las aplicaciones CLI son una excelente manera de interactuar con tu aplicación. Puedes usarlas para generar controladores, mostrar todas las rutas y más.

- <span class="badge bg-primary">oficial</span> [flightphp/runway](/awesome-plugins/runway) - Runway es una aplicación CLI que te ayuda a gestionar tus aplicaciones Flight.

## Cookies

Las cookies son una excelente manera de almacenar pequeñas cantidades de datos en el lado del cliente. Pueden usarse para almacenar preferencias del usuario, configuraciones de la aplicación y más.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie es una biblioteca PHP que proporciona una forma simple y efectiva de gestionar cookies.

## Depuración

La depuración es crucial cuando estás desarrollando en tu entorno local. Hay algunos plugins que pueden elevar tu experiencia de depuración.

- [tracy/tracy](/awesome-plugins/tracy) - Este es un manejador de errores completo que se puede usar con Flight. Tiene varios paneles que pueden ayudarte a depurar tu aplicación. También es muy fácil de extender y agregar tus propios paneles.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Usado con el manejador de errores [Tracy](/awesome-plugins/tracy), este plugin agrega algunos paneles adicionales para ayudar con la depuración específicamente para proyectos Flight.

## Bases de Datos

Las bases de datos son el núcleo de la mayoría de las aplicaciones. Así es como almacenas y recuperas datos. Algunas bibliotecas de bases de datos son simplemente envolturas para escribir consultas y algunas son ORM completamente desarrolladas.

- <span class="badge bg-primary">oficial</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Envoltura PDO oficial de Flight que es parte del núcleo. Esta es una envoltura simple para ayudar a simplificar el proceso de escritura de consultas y su ejecución. No es un ORM.
- <span class="badge bg-primary">oficial</span> [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord oficial de Flight. Gran pequeña biblioteca para recuperar y almacenar datos fácilmente en tu base de datos.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin para realizar un seguimiento de todos los cambios en la base de datos para tu proyecto.

## Cifrado

El cifrado es crucial para cualquier aplicación que almacene datos sensibles. Cifrar y descifrar los datos no es terriblemente difícil, pero almacenar adecuadamente la clave de cifrado [puede](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [ser](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difícil](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Lo más importante es nunca almacenar tu clave de cifrado en un directorio público o comprometerla en tu repositorio de código.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Esta es una biblioteca que se puede usar para cifrar y descifrar datos. Comenzar a usarla es bastante simple para comenzar a cifrar y descifrar datos.

## Cola de Trabajo

Las colas de trabajo son realmente útiles para procesar tareas de manera asíncrona. Esto puede ser enviar correos electrónicos, procesar imágenes o cualquier cosa que no necesite hacerse en tiempo real.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue es una biblioteca que se puede usar para procesar trabajos de manera asíncrona. Se puede usar con beanstalkd, MySQL/MariaDB, SQLite y PostgreSQL.

## Sesión

Las sesiones realmente no son útiles para APIs, pero para construir una aplicación web, las sesiones pueden ser cruciales para mantener el estado y la información de inicio de sesión.

- <span class="badge bg-primary">oficial</span> [flightphp/session](/awesome-plugins/session) - Biblioteca de sesión oficial de Flight. Esta es una biblioteca de sesión simple que se puede usar para almacenar y recuperar datos de sesión. Utiliza el manejo de sesiones incorporado de PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - Administrador de sesiones PHP (sin bloqueo, flash, segmento, cifrado de sesión). Utiliza PHP open_ssl para cifrado/descifrado opcional de datos de sesión.

## Plantillas

Las plantillas son fundamentales para cualquier aplicación web con una UI. Hay una serie de motores de plantillas que se pueden utilizar con Flight.

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - Este es un motor de plantillas muy básico que es parte del núcleo. No se recomienda su uso si tienes más de un par de páginas en tu proyecto.
- [latte/latte](/awesome-plugins/latte) - Latte es un motor de plantillas completo que es muy fácil de usar y se siente más cercano a una sintaxis de PHP que Twig o Smarty. También es muy fácil de extender y agregar tus propios filtros y funciones.

## Contribuyendo

¿Tienes un plugin que te gustaría compartir? ¡Envía una solicitud de extracción para agregarlo a la lista!