# Increíbles complementos

Flight es increíblemente extensible. Hay varios complementos que se pueden usar para añadir funcionalidad a su aplicación Flight. Algunos son oficialmente compatibles con el Equipo de Flight y otros son bibliotecas micro/lite para ayudarlo a empezar.

## Caché

La caché es una excelente manera de acelerar su aplicación. Hay varias bibliotecas de caché que se pueden usar con Flight.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Clase de caché en el archivo PHP ligera, simple y independiente

## CLI

Las aplicaciones de CLI son una excelente manera de interactuar con su aplicación. Puede usarlas para generar controladores, mostrar todas las rutas y más.

- [flightphp/runway](/awesome-plugins/runway) - Runway es una aplicación CLI que le ayuda a gestionar sus aplicaciones Flight.

## Cookies

Las cookies son una excelente manera de almacenar pequeños fragmentos de datos en el lado del cliente. Se pueden utilizar para almacenar preferencias de usuario, configuraciones de la aplicación y más.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie es una biblioteca PHP que proporciona una forma simple y efectiva de gestionar cookies.

## Depuración

La depuración es crucial cuando está desarrollando en su entorno local. Hay algunos complementos que pueden mejorar su experiencia de depuración.

- [tracy/tracy](/awesome-plugins/tracy) - Este es un manejador de errores completo que se puede usar con Flight. Tiene varios paneles que pueden ayudarlo a depurar su aplicación. También es muy fácil de ampliar y agregar sus propios paneles.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Utilizado con el manejador de errores [Tracy](/awesome-plugins/tracy), este complemento agrega algunos paneles adicionales para ayudar con la depuración específicamente para proyectos Flight.

## Bases de datos

Las bases de datos son el núcleo de la mayoría de las aplicaciones. Así es como se almacenan y recuperan datos. Algunas bibliotecas de bases de datos son simplemente envolturas para escribir consultas y otras son ORM completos.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Envoltura oficial de Flight PDO que forma parte del núcleo. Esta es una envoltura simple para ayudar a simplificar el proceso de escribir consultas y ejecutarlas. No es un ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord oficial de Flight. Pequeña biblioteca genial para recuperar y almacenar datos fácilmente en su base de datos.

## Encriptación

La encriptación es crucial para cualquier aplicación que almacene datos sensibles. Encriptar y desencriptar los datos no es muy difícil, pero almacenar correctamente la clave de encriptación [puede](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [ser](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [dificil](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Lo más importante es nunca almacenar su clave de encriptación en un directorio público o hacer un commit a su repositorio de código.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Esta es una biblioteca que se puede utilizar para encriptar y desencriptar datos. Empezar a encriptar y desencriptar datos es bastante simple.

## Sesión

Las sesiones no son realmente útiles para APIs, pero para desarrollar una aplicación web completo, las sesiones pueden ser cruciales para mantener el estado e información de inicio de sesión.

- [Ghostff/Session](/awesome-plugins/session) - Gestor de sesión PHP (sin bloqueo, flash, segmento, encriptación de sesión). Utiliza PHP open_ssl para encriptar/desencriptar datos de sesión de forma opcional.

## Plantillas

Las plantillas son fundamentales para cualquier aplicación web con una interfaz de usuario. Hay varios motores de plantillas que se pueden utilizar con Flight.

- [flightphp/core View](/learn#views) - Este es un motor de plantillas muy básico que forma parte del núcleo. No se recomienda utilizarlo si tiene más de un par de páginas en su proyecto.
- [latte/latte](/awesome-plugins/latte) - Latte es un motor de plantillas completo que es muy fácil de usar y se siente más cercano a una sintaxis PHP que Twig o Smarty. También es muy fácil de ampliar y agregar sus propios filtros y funciones.

## Contribuir

¿Tiene un complemento que le gustaría compartir? ¡Envíe una solicitud de extracción para agregarlo a la lista!