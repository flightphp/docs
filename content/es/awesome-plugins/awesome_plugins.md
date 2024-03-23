# Increíbles Complementos

Flight es increíblemente extensible. Hay una serie de complementos que se pueden utilizar para añadir funcionalidades a tu aplicación Flight. Algunos son compatibles oficialmente por el equipo de Flight y otros son bibliotecas micro/lite para ayudarte a empezar.

## Caché

La caché es una excelente manera de acelerar tu aplicación. Hay varias bibliotecas de caché que se pueden usar con Flight.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Clase de caché PHP ligera, simple y independiente para archivos

## Cookies

Las cookies son una excelente manera de almacenar pequeños fragmentos de datos en el lado del cliente. Se pueden utilizar para almacenar preferencias de usuario, configuraciones de la aplicación y más.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie es una biblioteca de PHP que proporciona una forma simple y efectiva de gestionar cookies.

## Depuración

La depuración es crucial cuando estás desarrollando en tu entorno local. Hay algunos complementos que pueden mejorar tu experiencia de depuración.

- [tracy/tracy](/awesome-plugins/tracy) - Este es un manejador de errores completo que se puede utilizar con Flight. Tiene una serie de paneles que pueden ayudarte a depurar tu aplicación. También es muy fácil de ampliar y añadir tus propios paneles.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Utilizado con el manejador de errores [Tracy](/awesome-plugins/tracy), este complemento añade algunos paneles adicionales para ayudar con la depuración específicamente para proyectos Flight.

## Bases de datos

Las bases de datos son el núcleo de la mayoría de las aplicaciones. Así es como almacenas y recuperas datos. Algunas bibliotecas de bases de datos son simplemente envoltorios para escribir consultas y algunas son ORM completos.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Envoltorio PDO oficial de Flight que forma parte del núcleo. Este es un envoltorio simple para ayudar a simplificar el proceso de escribir consultas y ejecutarlas. No es un ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapeador activo oficial de Flight. Estupenda pequeña biblioteca para recuperar y almacenar datos fácilmente en tu base de datos.

## Encriptación

La encriptación es crucial para cualquier aplicación que almacene datos sensibles. Encriptar y desencriptar los datos no es muy difícil, pero almacenar correctamente la clave de encriptación puede ser difícil. Lo más importante es nunca almacenar tu clave de encriptación en un directorio público o hacer un commit a tu repositorio de código.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Esta es una biblioteca que se puede utilizar para encriptar y desencriptar datos. Empezar a encriptar y desencriptar datos es bastante simple.

## Sesión

Las sesiones no son realmente útiles para las API, pero para crear una aplicación web, las sesiones pueden ser cruciales para mantener el estado e información de inicio de sesión.

- [Ghostff/Session](/awesome-plugins/session) - Gestor de sesiones de PHP (asincrónico, flash, segmento, encriptación de sesiones). Utiliza PHP open_ssl para encriptar/desencriptar datos de sesión de manera opcional.

## Plantillas

Las plantillas son fundamentales para cualquier aplicación web con una interfaz de usuario. Hay varias engines de plantillas que se pueden utilizar con Flight.

- [flightphp/core View](/learn#views) - Este es un motor de plantillas muy básico que forma parte del núcleo. No se recomienda su uso si tienes más de un par de páginas en tu proyecto.
- [latte/latte](/awesome-plugins/latte) - Latte es un motor de plantillas completo que es muy fácil de usar y se siente más cercano a una sintaxis PHP que Twig o Smarty. También es muy fácil de ampliar y añadir tus propios filtros y funciones.

## Contribuir

¿Tienes un complemento que te gustaría compartir? ¡Envía una solicitud de extracción para añadirlo a la lista!