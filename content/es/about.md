# ¿Qué es Flight?

Flight es un marco rápido, simple y extensible para PHP. Es bastante versátil y se puede usar para construir cualquier tipo de aplicación web. Está construido pensando en la simplicidad y se escribe de una manera que es fácil de entender y usar.

Flight es un gran marco para principiantes que son nuevos en PHP y desean aprender a construir aplicaciones web. También es un gran marco para desarrolladores experimentados que quieren más control sobre sus aplicaciones web. Está diseñado para construir fácilmente una API RESTful, una aplicación web simple o una aplicación web compleja.

## Comenzar Rápido

```php
<?php

// si se instaló con composer
require 'vendor/autoload.php';
// o si se instaló manualmente por archivo zip
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo '¡hola mundo!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="Reproductor de video de YouTube" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">¿Suficientemente simple verdad?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">¡Aprende más sobre Flight en la documentación!</a>

    </div>
  </div>
</div>

### Aplicación Esqueleto/Plantilla

Hay una aplicación de ejemplo que puede ayudarte a comenzar con el marco Flight. Ve a [flightphp/skeleton](https://github.com/flightphp/skeleton) para instrucciones sobre cómo comenzar. También puedes visitar la página de [ejemplos](examples) para inspiración sobre algunas de las cosas que puedes hacer con Flight.

# Comunidad

Estamos en Matrix Chat con nosotros en [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Contribuyendo

Hay dos formas en que puedes contribuir a Flight: 

1. Puedes contribuir al marco central visitando el [repositorio central](https://github.com/flightphp/core). 
1. Puedes contribuir a la documentación. Este sitio web de documentación está alojado en [Github](https://github.com/flightphp/docs). ¡Si notas un error o quieres mejorar algo, siéntete libre de corregirlo y enviar una solicitud de extracción! Intentamos mantenernos al día, pero las actualizaciones y traducciones de idiomas son bienvenidas.

# Requisitos

Flight requiere PHP 7.4 o superior.

**Nota:** PHP 7.4 es compatible porque en el momento actual de escritura (2024) PHP 7.4 es la versión predeterminada para algunas distribuciones de Linux LTS. Forzar un cambio a PHP >8 causaría muchos problemas para esos usuarios. El marco también es compatible con PHP >8.

# Licencia

Flight se publica bajo la licencia [MIT](https://github.com/flightphp/core/blob/master/LICENSE).