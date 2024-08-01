# Increíbles complementos

Flight es increíblemente extensible. Hay varios complementos que se pueden utilizar para agregar funcionalidad a su aplicación de Flight. Algunos son oficialmente compatibles con el Equipo de Flight y otros son bibliotecas micro/lite para ayudarlo a comenzar.

## Autenticación/Autorización

La autenticación y autorización son cruciales para cualquier aplicación que requiera controles para determinar quién puede acceder a qué.

- [flightphp/permissions](/es/complementos-increibles/permisos) - Biblioteca de Permisos oficial de Flight. Esta biblioteca es una forma sencilla de agregar permisos a nivel de usuario y aplicación a su aplicación.

## Caché

La caché es una excelente manera de acelerar su aplicación. Hay varias bibliotecas de caché que se pueden usar con Flight.

- [Wruczek/PHP-File-Cache](/es/complementos-increibles/caché-de-archivos-php) - Clase de caché en archivo PHP ligera, simple y autónoma

## CLI

Las aplicaciones de CLI son una excelente manera de interactuar con su aplicación. Puede usarlas para generar controladores, mostrar todas las rutas y más.

- [flightphp/runway](/es/complementos-increibles/runway) - Runway es una aplicación de CLI que le ayuda a administrar sus aplicaciones de Flight.

## Cookies

Las cookies son una excelente manera de almacenar pequeños fragmentos de datos en el lado del cliente. Se pueden usar para almacenar preferencias de usuario, configuraciones de la aplicación y más.

- [overclokk/cookie](/es/complementos-increibles/cookie-php) - PHP Cookie es una biblioteca PHP que proporciona una forma simple y efectiva de administrar cookies.

## Depuración

La depuración es crucial cuando estás desarrollando en tu entorno local. Hay algunos complementos que pueden mejorar tu experiencia de depuración.

- [tracy/tracy](/es/complementos-increibles/tracy) - Este es un manejador de errores con todas las funciones que se puede usar con Flight. Tiene varios paneles que pueden ayudarte a depurar tu aplicación. También es muy fácil de extender y agregar tus propios paneles.
- [flightphp/tracy-extensions](/es/complementos-increibles/extensiones-de-tracy) - Utilizado con el manejador de errores [Tracy](/es/complementos-increibles/tracy), este complemento agrega algunos paneles adicionales para ayudar con la depuración específicamente en proyectos de Flight.

## Bases de Datos

Las bases de datos son fundamentales para la mayoría de las aplicaciones. Así es como almacena y recupera datos. Algunas bibliotecas de bases de datos son simplemente envoltorios para escribir consultas y otras son ORM completos.

- [flightphp/core PdoWrapper](/es/complementos-increibles/envoltura-pdo) - Envoltura PDO oficial de Flight que forma parte del núcleo. Esta es una envoltura simple para ayudar a simplificar el proceso de escritura y ejecución de consultas. No es un ORM.
- [flightphp/active-record](/es/complementos-increibles/active-record) - ActiveRecord ORM/Mappeador oficial de Flight. Excelente biblioteca para recuperar y almacenar datos fácilmente en su base de datos.

## Encriptación

La encriptación es crucial para cualquier aplicación que almacene datos sensibles. Encriptar y desencriptar los datos no es demasiado difícil, pero almacenar adecuadamente la clave de encriptación puede ser difícil. Lo más importante es nunca almacenar su clave de encriptación en un directorio público ni comprometerla en su repositorio de código.

- [defuse/php-encryption](/es/complementos-increibles/encriptacion-php) - Esta es una biblioteca que se puede utilizar para encriptar y desencriptar datos. Empezar a usarla es bastante simple para comenzar a encriptar y desencriptar datos.

## Sesión

Las sesiones no son realmente útiles para las API, pero para desarrollar una aplicación web, las sesiones pueden ser cruciales para mantener el estado e información de inicio de sesión.

- [Ghostff/Session](/es/complementos-increibles/sesion) - Administrador de Sesiones de PHP (no bloqueante, flash, segmento, encriptación de sesiones). Utiliza PHP open_ssl para encriptación/desencriptación opcional de datos de sesión.

## Plantillas

Las plantillas son esenciales para cualquier aplicación web con una interfaz de usuario. Hay varios motores de plantillas que se pueden usar con Flight.

- [flightphp/core View](/es/aprender#vistas) - Este es un motor de plantillas muy básico que forma parte del núcleo. No se recomienda utilizarlo si tiene más de un par de páginas en su proyecto.
- [latte/latte](/es/complementos-increibles/latte) - Latte es un motor de plantillas completo que es muy fácil de usar y se siente más cercano a una sintaxis de PHP que Twig o Smarty. También es muy fácil de extender y agregar sus propios filtros y funciones.

## Contribuir

¿Tienes un complemento que te gustaría compartir? ¡Envía un pull request para agregarlo a la lista!