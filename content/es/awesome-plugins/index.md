# Increíbles Complementos

Flight es increíblemente extensible. Hay varios complementos que se pueden utilizar para agregar funcionalidad a su aplicación Flight. Algunos son oficialmente compatibles con el Equipo FlightPHP y otros son bibliotecas micro/lite para ayudarlo a comenzar.

## Caché

La caché es una excelente manera de acelerar su aplicación. Hay varias bibliotecas de caché que se pueden utilizar con Flight.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Clase de caché en archivo PHP ligera, simple y independiente

## Depuración

La depuración es crucial cuando está desarrollando en su entorno local. Hay algunos complementos que pueden mejorar su experiencia de depuración.

- [tracy/tracy](/awesome-plugins/tracy) - Este es un controlador de errores completo que se puede utilizar con Flight. Tiene varias paneles que pueden ayudarlo a depurar su aplicación. También es muy fácil de ampliar y agregar sus propios paneles.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Utilizado con el controlador de errores [Tracy](/awesome-plugins/tracy), este complemento agrega algunos paneles adicionales para ayudar con la depuración específicamente para proyectos de Flight.

## Bases de datos

Las bases de datos son el núcleo de la mayoría de las aplicaciones. Así es como almacena y recupera datos. Algunas bibliotecas de bases de datos son simplemente envoltorios para escribir consultas y otras son ORM completos.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Envoltorio PDO oficial de Flight que forma parte del núcleo. Este es un envoltorio simple para ayudar a simplificar el proceso de escribir consultas y ejecutarlas. No es un ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord oficial de Flight. Excelente pequeña biblioteca para recuperar y almacenar datos fácilmente en su base de datos.

## Sesión

Las sesiones no son realmente útiles para las API, pero para desarrollar una aplicación web, las sesiones pueden ser cruciales para mantener el estado y la información de inicio de sesión.

- [Ghostff/Session](/awesome-plugins/session) - Gestor de sesiones PHP (no bloqueante, flash, segmento, cifrado de sesiones). Utiliza PHP open_ssl para cifrar/descifrar opcionalmente los datos de sesión.

## Generación de plantillas

La generación de plantillas es fundamental para cualquier aplicación web con una interfaz de usuario. Hay varios motores de plantillas que se pueden utilizar con Flight.

- [flightphp/core View](/learn#views) - Este es un motor de plantillas muy básico que forma parte del núcleo. No se recomienda su uso si tiene más de un par de páginas en su proyecto.
- [latte/latte](/awesome-plugins/latte) - Latte es un motor de plantillas completo que es muy fácil de usar y se siente más cercano a una sintaxis PHP que Twig o Smarty. También es muy fácil de ampliar y agregar sus propios filtros y funciones.

## Contribuciones

¿Tiene un complemento que le gustaría compartir? ¡Envíe una solicitud de extracción para agregarlo a la lista!