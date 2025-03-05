# ¿Qué es Flight?

Flight es un marco rápido, simple y extensible para PHP. Es bastante versátil y se puede usar para construir cualquier tipo de aplicación web. Está construido con la simplicidad en mente y está escrito de una manera que es fácil de entender y usar.

Flight es un gran marco para principiantes que son nuevos en PHP y quieren aprender a construir aplicaciones web. También es un gran marco para desarrolladores experimentados que desean más control sobre sus aplicaciones web. Está diseñado para construir fácilmente una API RESTful, una aplicación web simple o una aplicación web compleja.

## Comenzar Rápido

Primero instálalo con Composer

```bash
composer require flightphp/core
```

o puedes descargar un zip del repositorio [aquí](https://github.com/flightphp/core). Luego tendrías un archivo básico `index.php` como el siguiente:

```php
<?php

// si se instaló con composer
require 'vendor/autoload.php';
// o si se instaló manualmente mediante un archivo zip
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo '¡hola mundo!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

¡Eso es todo! Tienes una aplicación básica de Flight. Ahora puedes ejecutar este archivo con `php -S localhost:8000` y visitar `http://localhost:8000` en tu navegador para ver la salida.

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="Reproductor de video de YouTube" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">¿Lo suficientemente simple, verdad?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">¡Aprende más sobre Flight en la documentación!</a>

    </div>
  </div>
</div>

## ¿Es rápido?

¡Sí! Flight es rápido. Es uno de los marcos PHP más rápidos disponibles. Puedes ver todos los benchmarks en [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)

Ve el benchmark a continuación con algunos otros marcos PHP populares.

| Marco      | Reqs de texto plano/sec | Reqs de JSON/sec |
| --------- | -------------------- | ---------------- |
| Flight      | 190,421            | 182,491         |
| Yii         | 145,749            | 131,434         |
| Fat-Free    | 139,238            | 133,952         |
| Slim        | 89,588             | 87,348          |
| Phalcon     | 95,911             | 87,675          |
| Symfony     | 65,053             | 63,237          |
| Lumen	      | 40,572             | 39,700          |
| Laravel     | 26,657             | 26,901          |
| CodeIgniter | 20,628             | 19,901          |

## Aplicación Esqueleto/Plantilla

Hay una aplicación de ejemplo que puede ayudarte a comenzar con el marco Flight. Ve a [flightphp/skeleton](https://github.com/flightphp/skeleton) para obtener instrucciones sobre cómo comenzar. También puedes visitar la página de [ejemplos](examples) para inspirarte en algunas de las cosas que puedes hacer con Flight.

# Comunidad

Estamos en Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

Y en Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Contribuciones

Hay dos formas en que puedes contribuir a Flight:

1. Puedes contribuir al marco central visitando el [repositorio principal](https://github.com/flightphp/core).
1. Puedes contribuir a la documentación. Este sitio web de documentación está alojado en [Github](https://github.com/flightphp/docs). Si notas un error o quieres mejorar algo, siéntete libre de corregirlo y enviar una solicitud de extracción. Intentamos mantenernos al día, pero las actualizaciones y traducciones de idiomas son bienvenidas.

# Requisitos

Flight requiere PHP 7.4 o superior.

**Nota:** PHP 7.4 es compatible porque en el momento actual de escribir (2024) PHP 7.4 es la versión predeterminada para algunas distribuciones Linux LTS. Forzar un cambio a PHP >8 causaría muchos inconvenientes para esos usuarios. El marco también es compatible con PHP >8.

# Licencia

Flight se publica bajo la licencia [MIT](https://github.com/flightphp/core/blob/master/LICENSE).