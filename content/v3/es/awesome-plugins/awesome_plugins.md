# Plugins Impresionantes

Flight es increíblemente extensible. Hay una serie de plugins que se pueden usar para agregar funcionalidad a tu aplicación Flight. Algunos son oficialmente soportados por el equipo de Flight y otros son bibliotecas micro/lite para ayudarte a comenzar.

## Documentación de API

La documentación de API es crucial para cualquier API. Ayuda a los desarrolladores a entender cómo interactuar con tu API y qué esperar a cambio. Hay un par de herramientas disponibles para ayudarte a generar documentación de API para tus proyectos Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Publicación de blog escrita por Daniel Schreiber sobre cómo usar la especificación OpenAPI con FlightPHP para construir tu API usando un enfoque API first.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI es una gran herramienta para ayudarte a generar documentación de API para tus proyectos Flight. Es muy fácil de usar y se puede personalizar según tus necesidades. Esta es la biblioteca PHP para ayudarte a generar la documentación de Swagger.

## Monitoreo de Rendimiento de Aplicaciones (APM)

El monitoreo de rendimiento de aplicaciones (APM) es crucial para cualquier aplicación. Ayuda a entender cómo está funcionando tu aplicación y dónde se encuentran los cuellos de botella. Hay una serie de herramientas de APM que se pueden usar con Flight.
- <span class="badge bg-primary">oficial</span> [flightphp/apm](/awesome-plugins/apm) - Flight APM es una biblioteca de APM simple que se puede usar para monitorear tus aplicaciones Flight. Se puede usar para monitorear el rendimiento de tu aplicación y ayudarte a identificar cuellos de botella.

## Autorización/Permisos

La autorización y los permisos son cruciales para cualquier aplicación que requiera controles para determinar quién puede acceder a qué.

- <span class="badge bg-primary">oficial</span> [flightphp/permissions](/awesome-plugins/permissions) - Biblioteca oficial de permisos de Flight. Esta biblioteca es una forma simple de agregar permisos a nivel de usuario y aplicación a tu aplicación. 

## Caché

La caché es una gran manera de acelerar tu aplicación. Hay una serie de bibliotecas de caché que se pueden usar con Flight.

- <span class="badge bg-primary">oficial</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Clase ligera, simple y autónoma de caché en archivo PHP

## CLI

Las aplicaciones CLI son una gran manera de interactuar con tu aplicación. Puedes usarlas para generar controladores, mostrar todas las rutas y más.

- <span class="badge bg-primary">oficial</span> [flightphp/runway](/awesome-plugins/runway) - Runway es una aplicación CLI que te ayuda a gestionar tus aplicaciones Flight.

## Cookies

Las cookies son una gran manera de almacenar pequeños bits de datos en el lado del cliente. Se pueden usar para almacenar preferencias de usuario, configuraciones de la aplicación y más.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie es una biblioteca PHP que proporciona una forma simple y efectiva de gestionar cookies.

## Depuración

La depuración es crucial cuando estás desarrollando en tu entorno local. Hay unos pocos plugins que pueden elevar tu experiencia de depuración.

- [tracy/tracy](/awesome-plugins/tracy) - Este es un manejador de errores con todas las funciones que se puede usar con Flight. Tiene una serie de paneles que pueden ayudarte a depurar tu aplicación. También es muy fácil de extender y agregar tus propios paneles.
- <span class="badge bg-primary">oficial</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Usado con el [Tracy](/awesome-plugins/tracy) manejador de errores, este plugin agrega unos pocos paneles extra para ayudar con la depuración específicamente para proyectos Flight.

## Bases de Datos

Las bases de datos son el núcleo de la mayoría de las aplicaciones. Es así como almacenas y recuperas datos. Algunas bibliotecas de bases de datos son simplemente wrappers para escribir consultas y otras son ORMs de pleno derecho.

- <span class="badge bg-primary">oficial</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Wrapper oficial de PDO de Flight que forma parte del núcleo. Este es un wrapper simple para ayudar a simplificar el proceso de escribir consultas y ejecutarlas. No es un ORM.
- <span class="badge bg-primary">oficial</span> [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord oficial de Flight. Gran biblioteca pequeña para recuperar y almacenar datos en tu base de datos de manera fácil.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin para rastrear todos los cambios de la base de datos para tu proyecto.

## Encriptación

La encriptación es crucial para cualquier aplicación que almacene datos sensibles. Encriptar y desencriptar los datos no es terriblemente difícil, pero almacenar correctamente la clave de encriptación [puede](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [ser](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difícil](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Lo más importante es nunca almacenar tu clave de encriptación en un directorio público o cometerla en tu repositorio de código.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Esta es una biblioteca que se puede usar para encriptar y desencriptar datos. Ponerse en marcha es bastante simple para comenzar a encriptar y desencriptar datos.

## Cola de Tareas

Las colas de tareas son realmente útiles para procesar tareas de manera asíncrona. Esto puede ser enviar correos electrónicos, procesar imágenes o cualquier cosa que no necesite hacerse en tiempo real.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue es una biblioteca que se puede usar para procesar tareas de manera asíncrona. Se puede usar con beanstalkd, MySQL/MariaDB, SQLite y PostgreSQL.

## Sesión

Las sesiones no son realmente útiles para APIs, pero para construir una aplicación web, las sesiones pueden ser cruciales para mantener el estado y la información de inicio de sesión.

- <span class="badge bg-primary">oficial</span> [flightphp/session](/awesome-plugins/session) - Biblioteca oficial de sesiones de Flight. Esta es una biblioteca de sesiones simple que se puede usar para almacenar y recuperar datos de sesión. Usa el manejo de sesiones incorporado de PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - Administrador de sesiones PHP (no bloqueante, flash, segmento, encriptación de sesión). Usa PHP open_ssl para la encriptación/decifrado opcional de datos de sesión.

## Plantillas

Las plantillas son el núcleo de cualquier aplicación web con una interfaz de usuario. Hay una serie de motores de plantillas que se pueden usar con Flight.

- <span class="badge bg-warning">desaprobado</span> [flightphp/core View](/learn#views) - Este es un motor de plantillas muy básico que forma parte del núcleo. No se recomienda usarlo si tienes más de un par de páginas en tu proyecto.
- [latte/latte](/awesome-plugins/latte) - Latte es un motor de plantillas con todas las funciones que es muy fácil de usar y se siente más cercano a una sintaxis PHP que Twig o Smarty. También es muy fácil de extender y agregar tus propios filtros y funciones.

## Integración con WordPress

¿Quieres usar Flight en tu proyecto WordPress? ¡Hay un plugin útil para eso!

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - Este plugin de WordPress te permite ejecutar Flight junto con WordPress. Es perfecto para agregar APIs personalizadas, microservicios o incluso aplicaciones completas a tu sitio WordPress usando el framework Flight. ¡Super útil si quieres lo mejor de ambos mundos!

## Contribuyendo

¿Tienes un plugin que quieras compartir? ¡Envía una solicitud de pull para agregarlo a la lista!