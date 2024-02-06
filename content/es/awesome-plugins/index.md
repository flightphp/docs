# Increíbles Plugins

Flight es increíblemente extensible. Hay una serie de plugins que se pueden utilizar para agregar funcionalidades a tu aplicación de Flight. Algunos son oficialmente compatibles con el Equipo de FlightPHP y otros son bibliotecas micro/lite para ayudarte a comenzar.

## Caché

La caché es una excelente manera de acelerar tu aplicación. Hay una serie de bibliotecas de caché que se pueden utilizar con Flight.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Clase de caché en archivo PHP, ligera, simple y independiente

## Depuración

La depuración es crucial cuando estás desarrollando en tu entorno local. Hay algunos plugins que pueden mejorar tu experiencia de depuración.

- [tracy/tracy](/awesome-plugins/tracy) - Este es un controlador de errores completo que se puede utilizar con Flight. Tiene una serie de paneles que pueden ayudarte a depurar tu aplicación. También es muy fácil de extender y agregar tus propios paneles.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Utilizado con el controlador de errores [Tracy](/awesome-plugins/tracy), este plugin agrega algunos paneles adicionales para ayudar con la depuración específicamente para proyectos de Flight.

## Bases de Datos

Las bases de datos son el núcleo de la mayoría de las aplicaciones. Así es como almacenas y recuperas datos. Algunas bibliotecas de bases de datos son simplemente envoltorios para escribir consultas y otras son ORM completos.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Envoltorio PDO oficial de Flight que es parte del núcleo. Este es un envoltorio simple para ayudar a simplificar el proceso de escribir consultas y ejecutarlas. No es un ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper oficial de Flight ActiveRecord. Excelente biblioteca para recuperar y almacenar datos fácilmente en tu base de datos.

## Sesión

Las sesiones no son realmente útiles para las API, pero para desarrollar una aplicación web, las sesiones pueden ser cruciales para mantener el estado e información de inicio de sesión.

- [Ghostff/Session](/awesome-plugins/session) - Gestor de Sesiones en PHP (no bloqueante, flash, segmento, cifrado de sesión). Utiliza PHP open_ssl para cifrar/descifrar opcionalmente los datos de sesión.

## Plantillas

Las plantillas son fundamentales para cualquier aplicación web con una interfaz de usuario. Hay una serie de motores de plantillas que se pueden utilizar con Flight.

- [flightphp/core View](/learn#views) - Este es un motor de plantillas muy básico que forma parte del núcleo. No se recomienda usarlo si tienes más de un par de páginas en tu proyecto.
- [latte/latte](/awesome-plugins/latte) - Latte es un motor de plantillas completo que es muy fácil de usar y se siente más cercano a una sintaxis PHP que Twig o Smarty. También es muy fácil de extender y agregar tus propios filtros y funciones.

## Contribuir

¿Tienes un plugin que te gustaría compartir? ¡Envía una solicitud de extracción para agregarlo a la lista!