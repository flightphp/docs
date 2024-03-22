# ¿Qué es Flight?

Flight es un framework rápido, simple y extensible para PHP. Es bastante versátil y se puede utilizar para construir cualquier tipo de aplicación web. Está diseñado con simplicidad en mente y está escrito de una manera que es fácil de entender y usar.

Flight es un excelente framework para principiantes que son nuevos en PHP y desean aprender a construir aplicaciones web. También es un gran framework para desarrolladores experimentados que desean tener más control sobre sus aplicaciones web. Está diseñado para construir fácilmente una API RESTful, una aplicación web simple o una aplicación web compleja.

## Inicio rápido

```php
<?php

// si está instalado con composer
require 'vendor/autoload.php';
// o si está instalado manualmente por archivo zip
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo '¡Hola Mundo!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

<div class="video-container">
	<iframe width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="Reproductor de video de YouTube" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
</div>

¿Sencillo, verdad? [¡Aprende más sobre Flight en la documentación!](learn)

### Aplicación Esqueleto/Base

Hay una aplicación de ejemplo que puede ayudarte a empezar con el Framework Flight. ¡Ve a [flightphp/skeleton](https://github.com/flightphp/skeleton) para obtener instrucciones sobre cómo empezar! También puedes visitar la página de [ejemplos](examples) para inspirarte en algunas de las cosas que puedes hacer con Flight.

# Comunidad

¡Estamos en Matrix! Chatea con nosotros en [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Contribuciones

Hay dos maneras en las que puedes contribuir a Flight:

1. Puedes contribuir al framework principal visitando el [repositorio principal](https://github.com/flightphp/core).
1. Puedes contribuir a la documentación. Este sitio web de documentación está alojado en [Github](https://github.com/flightphp/docs). ¡Si notas un error o quieres mejorar algo, siéntete libre de corregirlo y enviar una solicitud de extracción! Intentamos estar al día en las cosas, pero las actualizaciones y traducciones de idiomas son bienvenidas.

# Requisitos

Flight requiere PHP 7.4 o superior.

**Nota:** Se admite PHP 7.4 porque en el momento actual de la escritura (2024) PHP 7.4 es la versión predeterminada para algunas distribuciones de Linux LTS. Forzar un cambio a PHP >8 causaría muchos dolores de cabeza a esos usuarios. El framework también soporta PHP >8.

# Licencia

Flight se publica bajo la licencia [MIT](https://github.com/flightphp/core/blob/master/LICENSE).