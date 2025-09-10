# ¿Qué es Flight?

Flight es un marco rápido, simple y extensible para PHP.  
Flight te permite construir aplicaciones web RESTful de manera rápida y fácil.

``` php
require 'flight/Flight.php';

// Define una ruta de aplicación
Flight::route('/', function(){
  echo '¡hola mundo!';
});

// Inicia la aplicación
Flight::start();
```

[Aprende más](learn)

# Requisitos

Flight requiere PHP 7.4 o superior.

# Licencia

Flight se publica bajo la licencia [MIT](https://github.com/mikecao/flight/blob/master/LICENSE).

# Comunidad

¡Estamos en Matrix! Chatea con nosotros en [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Contribuyendo

Este sitio web está alojado en [Github](https://github.com/mikecao/flightphp.com).  
Actualizaciones y traducciones de idiomas son bienvenidas.
