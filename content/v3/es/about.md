# Marco de trabajo PHP Flight

Flight es un framework rápido, simple y extensible para PHP, diseñado para desarrolladores que quieren hacer las cosas rápidamente, sin complicaciones. Ya sea que estés construyendo una aplicación web clásica, una API ultrarrápida o experimentando con las últimas herramientas impulsadas por IA, la huella baja de Flight y su diseño directo lo convierten en una opción perfecta. Flight está destinado a ser ligero, pero también puede manejar requisitos de arquitectura empresarial.

## ¿Por qué elegir Flight?

- **Amigable para principiantes:** Flight es un gran punto de partida para nuevos desarrolladores de PHP. Su estructura clara y sintaxis simple te ayudan a aprender desarrollo web sin perderte en código innecesario.
- **Amado por los profesionales:** Los desarrolladores experimentados aman Flight por su flexibilidad y control. Puedes escalar desde un prototipo pequeño hasta una aplicación completa sin cambiar de framework.
- **Amigable con la IA:** La sobrecarga mínima y la arquitectura limpia de Flight lo hacen ideal para integrar herramientas y APIs de IA. Ya sea que estés construyendo chatbots inteligentes, tableros impulsados por IA o simplemente quieras experimentar, Flight se quita de en medio para que te enfoques en lo que importa. ¡La [aplicación esqueleto](https://github.com/flightphp/skeleton) viene con archivos de instrucciones precompilados para los principales asistentes de codificación de IA desde el principio! [Aprende más sobre el uso de IA con Flight](/learn/ai)

## Resumen en video

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="Reproductor de video de YouTube" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 fs-5 text-center mt-5 pt-5">
      <span class="flight-title-video">¿Suficientemente simple, verdad?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Aprende más</a> sobre Flight en la documentación!
    </div>
  </div>
</div>

## Inicio rápido

Para hacer una instalación básica y rápida, instálalo con Composer:

```bash
composer require flightphp/core
```

O puedes descargar un zip del repositorio [aquí](https://github.com/flightphp/core). Luego, tendrías un archivo básico `index.php` como el siguiente:

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
  Flight::json([
	'hello' => 'world'
  ]);
});

Flight::start();
```

¡Eso es todo! Tienes una aplicación básica de Flight. Ahora puedes ejecutar este archivo con `php -S localhost:8000` y visitar `http://localhost:8000` en tu navegador para ver la salida.

## Aplicación esqueleto/plantilla

Hay una aplicación de ejemplo para ayudarte a comenzar tu proyecto con Flight. Tiene una estructura organizada, configuraciones básicas listas y maneja scripts de composer desde el principio. Echa un vistazo a [flightphp/skeleton](https://github.com/flightphp/skeleton) para un proyecto listo para usar, o visita la página de [ejemplos](examples) para inspiración. ¿Quieres ver cómo encaja la IA? [Explora ejemplos impulsados por IA](/learn/ai).

## Instalando la aplicación esqueleto

¡Fácil!

```bash
# Crea el nuevo proyecto
composer create-project flightphp/skeleton my-project/
# Entra en el directorio de tu nuevo proyecto
cd my-project/
# Inicia el servidor de desarrollo local para comenzar de inmediato
composer start
```

¡Creará la estructura del proyecto, configurará los archivos que necesitas y estarás listo para comenzar!

## Alto rendimiento

Flight es uno de los frameworks de PHP más rápidos disponibles. Su núcleo ligero significa menos sobrecarga y más velocidad, perfecto para aplicaciones tradicionales y proyectos modernos impulsados por IA. Puedes ver todos los benchmarks en [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks).

Ve el benchmark a continuación con algunos otros frameworks de PHP populares.

| Framework | Reqs/sec en texto plano | Reqs/sec en JSON |
| --------- | ------------------------ | ---------------- |
| Flight      | 190,421                | 182,491         |
| Yii         | 145,749                | 131,434         |
| Fat-Free    | 139,238                | 133,952         |
| Slim        | 89,588                 | 87,348          |
| Phalcon     | 95,911                 | 87,675          |
| Symfony     | 65,053                 | 63,237          |
| Lumen       | 40,572                 | 39,700          |
| Laravel     | 26,657                 | 26,901          |
| CodeIgniter | 20,628                 | 19,901          |

## Flight y la IA

¿Curioso de cómo maneja la IA? [Descubre](/learn/ai) cómo Flight facilita trabajar con tu LLM de codificación favorito.

# Comunidad

Estamos en Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

Y Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Contribuyendo

Hay dos formas en que puedes contribuir a Flight:

1. Contribuye al framework principal visitando el [repositorio principal](https://github.com/flightphp/core).
2. ¡Ayuda a mejorar los documentos! Este sitio web de documentación se aloja en [Github](https://github.com/flightphp/docs). Si encuentras un error o quieres mejorar algo, no dudes en enviar una solicitud de extracción. ¡Amamos las actualizaciones y nuevas ideas, especialmente alrededor de la IA y las nuevas tecnologías!

# Requisitos

Flight requiere PHP 7.4 o superior.

**Nota:** PHP 7.4 es compatible porque, en el momento de escribir esto (2024), PHP 7.4 es la versión predeterminada para algunas distribuciones LTS de Linux. Forzar un cambio a PHP >8 causaría muchos problemas para esos usuarios. El framework también soporta PHP >8.

# Licencia

Flight se libera bajo la [licencia MIT](https://github.com/flightphp/core/blob/master/LICENSE).