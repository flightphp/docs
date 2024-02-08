# Increíbles complementos

Flight es increíblemente extensible. Hay varios complementos que se pueden utilizar para agregar funcionalidad a su aplicación Flight. Algunos son compatibles oficialmente por el equipo FlightPHP y otros son bibliotecas micro/lite para ayudarlo a comenzar.

## Caché

La caché es una excelente manera de acelerar su aplicación. Hay varias bibliotecas de caché que se pueden utilizar con Flight.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Clase de caché de archivo PHP ligera, simple y independiente

## Depuración

La depuración es crucial cuando estás desarrollando en tu entorno local. Hay algunos complementos que pueden mejorar tu experiencia de depuración.

- [tracy/tracy](/awesome-plugins/tracy) - Este es un manejador de errores completo que se puede utilizar con Flight. Tiene una serie de paneles que pueden ayudarte a depurar tu aplicación. También es muy fácil de extender y agregar tus propios paneles.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Utilizado con el manejador de errores [Tracy](/awesome-plugins/tracy), este complemento agrega algunos paneles adicionales para ayudar con la depuración específicamente para proyectos de Flight.

## Bases de datos

Las bases de datos son el núcleo de la mayoría de las aplicaciones. Así es como almacenas y recuperas datos. Algunas bibliotecas de bases de datos son simplemente envoltorios para escribir consultas y otras son ORM completos.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Envoltorio oficial de PDO de Flight que forma parte del núcleo. Este es un envoltorio simple que ayuda a simplificar el proceso de escritura y ejecución de consultas. No es un ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper oficial de Flight ActiveRecord. Excelente biblioteca para recuperar y almacenar datos fácilmente en su base de datos.

## Sesión

Las sesiones no son realmente útiles para las API, pero para desarrollar una aplicación web, las sesiones pueden ser cruciales para mantener el estado e información de inicio de sesión.

- [Ghostff/Session](/awesome-plugins/session) - Administrador de sesiones PHP (no bloqueante, flash, segmento, cifrado de sesiones). Utiliza PHP open_ssl para cifrar/descifrar opcionalmente los datos de sesión.

## Plantillas

Las plantillas son fundamentales para cualquier aplicación web con una interfaz de usuario. Hay varios motores de plantillas que se pueden utilizar con Flight.

- [flightphp/core View](/learn#views) - Este es un motor de plantillas muy básico que forma parte del núcleo. No se recomienda su uso si tienes más de un par de páginas en tu proyecto.
- [latte/latte](/awesome-plugins/latte) - Latte es un motor de plantillas completo que es muy fácil de usar y se siente más cercano a la sintaxis PHP que Twig o Smarty. También es muy fácil de extender y agregar tus propios filtros y funciones.

## Contribuir

¿Tienes un complemento que te gustaría compartir? ¡Envía una solicitud de extracción para agregarlo a la lista!