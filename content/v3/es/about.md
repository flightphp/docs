# ¿Qué es Flight?

Flight es un framework rápido, simple y extensible para PHP, diseñado para desarrolladores que quieren hacer las cosas rápidamente, sin complicaciones. Ya sea que estés construyendo una aplicación web clásica, una API ultrarrápida o experimentando con las últimas herramientas impulsadas por IA, el bajo consumo de recursos y el diseño directo de Flight lo convierten en una opción perfecta.

## ¿Por qué elegir Flight?

- **Amigable para principiantes:** Flight es un excelente punto de partida para desarrolladores nuevos en PHP. Su estructura clara y sintaxis simple te ayudan a aprender desarrollo web sin perderte en código innecesario.
- **Amado por profesionales:** Los desarrolladores experimentados aman Flight por su flexibilidad y control. Puedes escalar desde un prototipo pequeño hasta una aplicación completa sin cambiar de framework.
- **Amigable con la IA:** El mínimo sobrecarga y la arquitectura limpia de Flight lo hacen ideal para integrar herramientas y APIs de IA. Ya sea que estés construyendo chatbots inteligentes, paneles impulsados por IA o simplemente quieras experimentar, Flight se quita de en medio para que te enfoques en lo que importa. [Learn more about using AI with Flight](/learn/ai)

## Inicio rápido

Primero, instálalo con Composer:

```bash
composer require flightphp/core
```

O puedes descargar un archivo zip del repositorio [here](https://github.com/flightphp/core). Luego, tendrías un archivo básico `index.php` como el siguiente:

```php
<?php

// si se instaló con composer
require 'vendor/autoload.php';
// o si se instaló manualmente por archivo zip
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
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
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="flight-title-video">¿Suficientemente simple, verdad?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Aprenda más sobre Flight en la documentación!</a>
      <br>
      <a href="/learn/ai" class="btn btn-primary mt-3">Descubra cómo Flight facilita la IA</a>
    </div>
  </div>
</div>

## ¿Es rápido?

¡Absolutamente! Flight es uno de los frameworks de PHP más rápidos disponibles. Su núcleo ligero significa menos sobrecarga y más velocidad, perfecto para aplicaciones tradicionales y proyectos modernos impulsados por IA. Puedes ver todos los benchmarks en [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)

Mira el benchmark a continuación con algunos otros frameworks populares de PHP.

| Framework | Plaintext Reqs/sec | JSON Reqs/sec |
| --------- | ------------ | ------------ |
| Flight      | 190,421    | 182,491 |
| Yii         | 145,749    | 131,434 |
| Fat-Free    | 139,238    | 133,952 |
| Slim        | 89,588     | 87,348  |
| Phalcon     | 95,911     | 87,675  |
| Symfony     | 65,053     | 63,237  |
| Lumen       | 40,572     | 39,700  |
| Laravel     | 26,657     | 26,901  |
| CodeIgniter | 20,628     | 19,901  |

## Aplicación esqueleto/base

Hay una aplicación de ejemplo para ayudarte a comenzar con Flight. Echa un vistazo a [flightphp/skeleton](https://github.com/flightphp/skeleton) para un proyecto listo para usar, o visita la página de [examples](examples) para inspiración. ¿Quieres ver cómo encaja la IA? [Explore AI-powered examples](/learn/ai).

# Comunidad

Estamos en Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

Y Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Contribuyendo

Hay dos formas en que puedes contribuir a Flight:

1. Contribuye al framework principal visitando el [core repository](https://github.com/flightphp/core).
2. ¡Ayuda a mejorar la documentación! Este sitio web de documentación se aloja en [Github](https://github.com/flightphp/docs). Si encuentras un error o quieres mejorar algo, no dudes en enviar una solicitud de extracción. Amamos las actualizaciones y nuevas ideas, especialmente alrededor de la IA y nuevas tecnologías!

# Requisitos

Flight requiere PHP 7.4 o superior.

**Nota:** PHP 7.4 es compatible porque, en el momento de escribir esto (2024), PHP 7.4 es la versión predeterminada para algunas distribuciones LTS de Linux. Forzar un cambio a PHP >8 causaría problemas para esos usuarios. El framework también soporta PHP >8.

# Licencia

Flight se lanza bajo la [MIT](https://github.com/flightphp/core/blob/master/LICENSE) license.