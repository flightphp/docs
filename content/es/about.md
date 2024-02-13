# ¿Qué es Flight?

Flight es un framework rápido, simple y extensible para PHP. Es bastante versátil y se puede utilizar para construir cualquier tipo de aplicación web. Está construido con la simplicidad en mente y está escrito de una manera que es fácil de entender y usar.

Flight es un gran framework para principiantes que son nuevos en PHP y quieren aprender a construir aplicaciones web. También es un gran framework para developers experimentados que desean más control sobre sus aplicaciones web. Está diseñado para construir fácilmente una API RESTful, una aplicación web simple o una aplicación web compleja.

## Inicio Rápido

```php
<?php

// si se instaló con composer
require 'vendor/autoload.php';
// o si se instaló manualmente por archivo zip
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo '¡hola mundo!';
});

Flight::start();
```

¿Sencillo verdad? [¡Aprende más sobre Flight en la documentación!](learn)

### Aplicación Esqueleto/Plantilla

Hay una aplicación de ejemplo que puede ayudarte a comenzar con el Framework Flight. ¡Ve a [flightphp/skeleton](https://github.com/flightphp/skeleton) para obtener instrucciones sobre cómo comenzar! También puedes visitar la página de [ejemplos](examples) para inspirarte en algunas de las cosas que puedes hacer con Flight.

# Comunidad

¡Estamos en Matrix! Chatea con nosotros en [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Contribuir

Hay dos formas en las que puedes contribuir a Flight:

1. Puedes contribuir al framework principal visitando el [repositorio principal](https://github.com/flightphp/core).
1. Puedes contribuir a la documentación. Este sitio web de documentación está alojado en [Github](https://github.com/flightphp/docs). ¡Si notas un error o quieres mejorar algo, siéntete libre de corregirlo y enviar un pull request! Intentamos mantenernos actualizados, pero las actualizaciones y traducciones de idiomas son bienvenidas.

# Requisitos

Flight requiere PHP 7.4 o superior.

**Nota:** PHP 7.4 es compatible porque en el momento actual de escritura (2024) PHP 7.4 es la versión predeterminada para algunas distribuciones LTS de Linux. Forzar un cambio a PHP >8 causaría muchos problemas para esos usuarios. El framework también es compatible con PHP >8.

# Licencia

Flight se publica bajo la licencia [MIT](https://github.com/flightphp/core/blob/master/LICENSE).