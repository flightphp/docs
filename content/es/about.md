# ¿Qué es Flight?

Flight es un framework rápido, simple y extensible para PHP.
Flight te permite construir aplicaciones web RESTful de forma rápida y sencilla.

```php
<?php

// si se instaló con composer
require 'vendor/autoload.php';
// o si se instaló manualmente mediante archivo zip
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo '¡Hola Mundo!';
});

Flight::start();
```

¿Suficientemente simple verdad? [¡Aprende más sobre Flight!](learn)

## Aplicación Skeleton
Hay una aplicación de ejemplo que puede ayudarte a comenzar con el Framework Flight. ¡Ve a [flightphp/skeleton](https://github.com/flightphp/skeleton) para obtener instrucciones sobre cómo empezar!

# Comunidad

¡Estamos en Matrix! Chatea con nosotros en [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Contribuir

Este sitio web está alojado en [Github](https://github.com/flightphp/docs). Si notas un error, ¡siéntete libre de corregirlo y enviar un pull request!
Intentamos estar al día con las cosas, pero las actualizaciones y traducciones de idiomas son bienvenidas.

# Requisitos

Flight requiere PHP 7.4 o superior.

**Nota:** PHP 7.4 es compatible porque en el momento actual de escritura (2024) es la versión predeterminada para algunas distribuciones de Linux LTS. Forzar un cambio a PHP >8 causaría muchos dolores de cabeza para esos usuarios. El framework también es compatible con PHP >8.

# Licencia

Flight se publica bajo la licencia [MIT](https://github.com/flightphp/core/blob/master/LICENSE).