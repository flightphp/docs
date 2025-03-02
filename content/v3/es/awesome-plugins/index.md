# Increíbles complementos

Flight es increíblemente extensible. Hay varios complementos que se pueden utilizar para añadir funcionalidad a tu aplicación Flight. Algunos son oficialmente compatibles con el Equipo de Flight y otros son bibliotecas micro/lite para ayudarte a comenzar.

## Caché

La caché es una excelente manera de acelerar tu aplicación. Hay varias bibliotecas de caché que se pueden utilizar con Flight.

- [Wruczek/PHP-File-Cache](/increibles-complementos/php-file-cache) - Clase de caché en archivo PHP ligera, simple y autónoma

## Depuración

La depuración es crucial cuando estás desarrollando en tu entorno local. Hay algunos complementos que pueden mejorar tu experiencia de depuración.

- [tracy/tracy](/increibles-complementos/tracy) - Este es un manejador de errores con todas las funciones que se puede utilizar con Flight. Tiene varios paneles que pueden ayudarte a depurar tu aplicación. También es muy fácil de ampliar y agregar tus propios paneles.
- [flightphp/tracy-extensions](/increibles-complementos/tracy-extensions) - Utilizado con el manejador de errores [Tracy](/increibles-complementos/tracy), este complemento añade algunos paneles adicionales para ayudar con la depuración específicamente para proyectos de Flight.

## Bases de datos

Las bases de datos son el núcleo de la mayoría de las aplicaciones. Así es como almacenas y recuperas datos. Algunas bibliotecas de bases de datos son simplemente envoltorios para escribir consultas y otras son ORM completos.

- [flightphp/core PdoWrapper](/increibles-complementos/pdo-wrapper) - Envoltorio oficial Flight PDO que forma parte del núcleo. Este es un envoltorio simple para ayudar a simplificar el proceso de escribir y ejecutar consultas. No es un ORM.
- [flightphp/active-record](/increibles-complementos/active-record) - ORM/Mapper oficial de Flight ActiveRecord. Excelente pequeña biblioteca para recuperar y almacenar datos fácilmente en tu base de datos.

## Sesión

Las sesiones no son realmente útiles para las API, pero para desarrollar una aplicación web, las sesiones pueden ser cruciales para mantener el estado e información de inicio de sesión.

- [Ghostff/Session](/increibles-complementos/session) - Administrador de sesiones de PHP (no bloqueante, flash, segmento, cifrado de sesiones). Utiliza PHP open_ssl para cifrar/descifrar opcionalmente los datos de sesión.

## Plantillas

Las plantillas son esenciales para cualquier aplicación web con un UI. Hay varios motores de plantillas que se pueden utilizar con Flight.

- [flightphp/core View](/learn#views) - Este es un motor de plantillas muy básico que forma parte del núcleo. No se recomienda su uso si tienes más de un par de páginas en tu proyecto.
- [latte/latte](/increibles-complementos/latte) - Latte es un motor de plantillas completo que es muy fácil de usar y se siente más cercano a una sintaxis PHP que Twig o Smarty. También es muy fácil de ampliar y agregar tus propios filtros y funciones.

## Contribuir

¿Tienes un complemento que te gustaría compartir? ¡Envía una solicitud de extracción para añadirlo a la lista!