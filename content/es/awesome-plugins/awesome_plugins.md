# Plugins Asombrosos

Flight es increíblemente extensible. Hay una serie de plugins que se pueden utilizar para agregar funcionalidad a tu aplicación Flight. Algunos son oficialmente apoyados por el Equipo de Flight y otros son bibliotecas micro/lite para ayudarte a comenzar.

## Documentación de la API

La documentación de la API es crucial para cualquier API. Ayuda a los desarrolladores a entender cómo interactuar con tu API y qué esperar a cambio. Hay un par de herramientas disponibles para ayudarte a generar documentación de API para tus Proyectos Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Publicación de blog escrita por Daniel Schreiber sobre cómo usar el Generador OpenAPI con FlightPHP para generar documentación de API.
- [Swagger UI](https://github.com/zircote/swagger-php) - Swagger UI es una gran herramienta para ayudarte a generar documentación de API para tus proyectos Flight. Es muy fácil de usar y se puede personalizar para adaptarse a tus necesidades. Esta es la biblioteca PHP para ayudarte a generar la documentación Swagger.

## Autenticación/Autorización

La Autenticación y la Autorización son cruciales para cualquier aplicación que requiera controles sobre quién puede acceder a qué.

- [flightphp/permissions](/awesome-plugins/permissions) - Biblioteca oficial de Permisos de Flight. Esta biblioteca es una forma simple de agregar permisos a nivel de usuario y aplicación a tu aplicación.

## Caching

El caching es una gran manera de acelerar tu aplicación. Hay varias bibliotecas de caching que se pueden usar con Flight.

- [flightphp/cache](/awesome-plugins/php-file-cache) - Clase de caching en archivo PHP ligera, simple y autónoma

## CLI

Las aplicaciones CLI son una gran manera de interactuar con tu aplicación. Puedes usarlas para generar controladores, mostrar todas las rutas y más.

- [flightphp/runway](/awesome-plugins/runway) - Runway es una aplicación CLI que te ayuda a gestionar tus aplicaciones Flight.

## Cookies

Las cookies son una gran manera de almacenar pequeños bits de datos en el lado del cliente. Se pueden usar para almacenar preferencias del usuario, configuraciones de la aplicación y más.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie es una biblioteca PHP que proporciona una forma simple y efectiva de gestionar cookies.

## Depuración

La depuración es crucial cuando estás desarrollando en tu entorno local. Hay algunos plugins que pueden elevar tu experiencia de depuración.

- [tracy/tracy](/awesome-plugins/tracy) - Este es un manejador de errores completo que se puede usar con Flight. Tiene una serie de paneles que pueden ayudarte a depurar tu aplicación. También es muy fácil de extender y agregar tus propios paneles.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Utilizado con el [Tracy](/awesome-plugins/tracy) manejador de errores, este plugin agrega algunos paneles extra para ayudar con la depuración específicamente para proyectos Flight.

## Bases de Datos

Las bases de datos son el núcleo de la mayoría de las aplicaciones. Así es como almacenas y recuperas datos. Algunas bibliotecas de bases de datos son simplemente envolturas para escribir consultas y algunas son ORM completas.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Envoltorio PDO oficial de Flight que es parte del núcleo. Esta es una envoltura simple para ayudar a simplificar el proceso de escribir consultas y ejecutarlas. No es un ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord oficial de Flight. Gran biblioteca para recuperar y almacenar datos en tu base de datos de manera fácil.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin para llevar un registro de todos los cambios en la base de datos para tu proyecto.

## Cifrado

El cifrado es crucial para cualquier aplicación que almacena datos sensibles. Cifrar y descifrar datos no es muy difícil, pero almacenar adecuadamente la clave de cifrado [puede](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [ser](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difícil](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Lo más importante es nunca almacenar tu clave de cifrado en un directorio público ni comprometerla en tu repositorio de código.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Esta es una biblioteca que se puede usar para cifrar y descifrar datos. Comenzar a usarla es bastante simple para empezar a cifrar y descifrar datos.

## Sesión

Las sesiones no son realmente útiles para API, pero para construir una aplicación web, las sesiones pueden ser cruciales para mantener el estado y la información de inicio de sesión.

- [Ghostff/Session](/awesome-plugins/session) - Administrador de Sesiones PHP (no bloqueante, flash, segmento, cifrado de sesión). Usa PHP open_ssl para cifrado/descifrado opcional de datos de sesión.

## Plantillas

La templating es fundamental para cualquier aplicación web con una interfaz de usuario. Hay varios motores de plantillas que se pueden usar con Flight.

- [flightphp/core View](/learn#views) - Este es un motor de plantillas muy básico que es parte del núcleo. No se recomienda usarlo si tienes más de un par de páginas en tu proyecto.
- [latte/latte](/awesome-plugins/latte) - Latte es un motor de plantillas completo que es muy fácil de usar y se siente más cercano a una sintaxis PHP que Twig o Smarty. También es muy fácil de extender y agregar tus propios filtros y funciones.

## Contribuir

¿Tienes un plugin que te gustaría compartir? ¡Envía una solicitud de extracción para agregarlo a la lista!