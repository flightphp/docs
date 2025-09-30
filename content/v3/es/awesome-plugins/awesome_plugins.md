# Plugins Geniales

Flight es increíblemente extensible. Hay una serie de plugins que se pueden usar para agregar funcionalidad a tu aplicación Flight. Algunos son soportados oficialmente por el Equipo de Flight y otros son bibliotecas micro/lite para ayudarte a comenzar.

## Documentación de API

La documentación de API es crucial para cualquier API. Ayuda a los desarrolladores a entender cómo interactuar con tu API y qué esperar a cambio. Hay un par de herramientas disponibles para ayudarte a generar documentación de API para tus Proyectos Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Publicación de blog escrita por Daniel Schreiber sobre cómo usar la Especificación OpenAPI con FlightPHP para construir tu API utilizando un enfoque API first.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI es una gran herramienta para ayudarte a generar documentación de API para tus proyectos Flight. Es muy fácil de usar y se puede personalizar para adaptarse a tus necesidades. Esta es la biblioteca PHP para ayudarte a generar la documentación Swagger.

## Monitoreo de Rendimiento de Aplicaciones (APM)

El Monitoreo de Rendimiento de Aplicaciones (APM) es crucial para cualquier aplicación. Te ayuda a entender cómo está funcionando tu aplicación y dónde están los cuellos de botella. Hay una serie de herramientas APM que se pueden usar con Flight.
- <span class="badge bg-primary">oficial</span> [flightphp/apm](/awesome-plugins/apm) - Flight APM es una biblioteca APM simple que se puede usar para monitorear tus aplicaciones Flight. Se puede usar para monitorear el rendimiento de tu aplicación y ayudarte a identificar cuellos de botella.

## Autorización/Permisos

La Autorización y Permisos son cruciales para cualquier aplicación que requiera controles en su lugar para quién puede acceder a qué.

- <span class="badge bg-primary">oficial</span> [flightphp/permissions](/awesome-plugins/permissions) - Biblioteca oficial de Permisos de Flight. Esta biblioteca es una forma simple de agregar permisos a nivel de usuario y aplicación a tu aplicación. 

## Caché

El Caché es una gran manera de acelerar tu aplicación. Hay una serie de bibliotecas de caché que se pueden usar con Flight.

- <span class="badge bg-primary">oficial</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Clase ligera, simple y standalone de caché en archivo PHP

## CLI

Las aplicaciones CLI son una gran manera de interactuar con tu aplicación. Puedes usarlas para generar controladores, mostrar todas las rutas y más.

- <span class="badge bg-primary">oficial</span> [flightphp/runway](/awesome-plugins/runway) - Runway es una aplicación CLI que te ayuda a gestionar tus aplicaciones Flight.

## Cookies

Las Cookies son una gran manera de almacenar pequeños bits de datos en el lado del cliente. Se pueden usar para almacenar preferencias de usuario, configuraciones de aplicación y más.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie es una biblioteca PHP que proporciona una forma simple y efectiva de gestionar cookies.

## Depuración

La Depuración es crucial cuando estás desarrollando en tu entorno local. Hay unos pocos plugins que pueden elevar tu experiencia de depuración.

- [tracy/tracy](/awesome-plugins/tracy) - Esta es un manejador de errores completo que se puede usar con Flight. Tiene una serie de paneles que pueden ayudarte a depurar tu aplicación. También es muy fácil de extender y agregar tus propios paneles.
- <span class="badge bg-primary">oficial</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Usado con el manejador de errores [Tracy](/awesome-plugins/tracy), este plugin agrega unos pocos paneles extra para ayudar con la depuración específicamente para proyectos Flight.

## Bases de Datos

Las Bases de Datos son el núcleo de la mayoría de las aplicaciones. Así es como almacenas y recuperas datos. Algunas bibliotecas de bases de datos son simplemente wrappers para escribir consultas y otras son ORMs completos.

- <span class="badge bg-primary">oficial</span> [flightphp/core PdoWrapper](/learn/pdo-wrapper) - Wrapper oficial de PDO de Flight que forma parte del núcleo. Este es un wrapper simple para ayudar a simplificar el proceso de escribir consultas y ejecutarlas. No es un ORM.
- <span class="badge bg-primary">oficial</span> [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord oficial de Flight. Gran biblioteca pequeña para recuperar y almacenar datos fácilmente en tu base de datos.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin para rastrear todos los cambios de base de datos para tu proyecto.

## Encriptación

La Encriptación es crucial para cualquier aplicación que almacene datos sensibles. Encriptar y desencriptar los datos no es terriblemente difícil, pero almacenar correctamente la clave de encriptación [puede](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [ser](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difícil](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Lo más importante es nunca almacenar tu clave de encriptación en un directorio público o cometerla en tu repositorio de código.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Esta es una biblioteca que se puede usar para encriptar y desencriptar datos. Ponerse en marcha es bastante simple para comenzar a encriptar y desencriptar datos.

## Cola de Trabajos

Las colas de trabajos son realmente útiles para procesar tareas de manera asíncrona. Esto puede ser enviar correos electrónicos, procesar imágenes o cualquier cosa que no necesite hacerse en tiempo real.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue es una biblioteca que se puede usar para procesar trabajos de manera asíncrona. Se puede usar con beanstalkd, MySQL/MariaDB, SQLite y PostgreSQL.

## Sesión

Las Sesiones no son realmente útiles para APIs, pero para construir una aplicación web, las sesiones pueden ser cruciales para mantener el estado e información de inicio de sesión.

- <span class="badge bg-primary">oficial</span> [flightphp/session](/awesome-plugins/session) - Biblioteca oficial de Sesión de Flight. Esta es una biblioteca de sesión simple que se puede usar para almacenar y recuperar datos de sesión. Usa el manejo de sesiones integrado de PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - Gestor de Sesiones PHP (no bloqueante, flash, segmento, encriptación de sesión). Usa PHP open_ssl para encriptación/desencriptación opcional de datos de sesión.

## Plantillas

La Plantillación es el núcleo de cualquier aplicación web con una UI. Hay una serie de motores de plantillas que se pueden usar con Flight.

- <span class="badge bg-warning">deprecado</span> [flightphp/core View](/learn#views) - Este es un motor de plantillas muy básico que forma parte del núcleo. No se recomienda usarlo si tienes más de un par de páginas en tu proyecto.
- [latte/latte](/awesome-plugins/latte) - Latte es un motor de plantillas completo que es muy fácil de usar y se siente más cercano a la sintaxis PHP que Twig o Smarty. También es muy fácil de extender y agregar tus propios filtros y funciones.

## Integración con WordPress

¿Quieres usar Flight en tu proyecto WordPress? ¡Hay un plugin práctico para eso!

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - Este plugin de WordPress te permite ejecutar Flight justo al lado de WordPress. Es perfecto para agregar APIs personalizadas, microservicios o incluso aplicaciones completas a tu sitio WordPress usando el framework Flight. ¡Súper útil si quieres lo mejor de ambos mundos!

## Contribuir

¿Tienes un plugin que te gustaría compartir? ¡Envía una solicitud de pull para agregarlo a la lista!