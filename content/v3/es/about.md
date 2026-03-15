# Flight PHP Framework

Flight es un framework rápido, simple y extensible para PHP—construido para desarrolladores que quieren hacer las cosas rápidamente, sin complicaciones. Ya sea que estés construyendo una aplicación web clásica, una API ultrarrápida o experimentando con las últimas herramientas impulsadas por IA, la baja huella y el diseño directo de Flight lo hacen perfecto. Flight está diseñado para ser ligero, pero también puede manejar requisitos de arquitectura empresarial.

## ¿Por qué elegir Flight?

- **Amigable para principiantes:** Flight es un gran punto de partida para nuevos desarrolladores de PHP. Su estructura clara y sintaxis simple te ayudan a aprender desarrollo web sin perderte en código boilerplate.
- **Amado por profesionales:** Los desarrolladores experimentados aman Flight por su flexibilidad y control. Puedes escalar desde un prototipo pequeño hasta una aplicación completa sin cambiar de framework.
- **Compatible hacia atrás:** Valoramos tu tiempo. Flight v3 es una ampliación de v2, manteniendo casi toda la misma API. Creemos en la evolución, no en la revolución—no más "rompiendo el mundo" cada vez que sale una versión mayor.
- **Cero dependencias:** El núcleo de Flight es completamente libre de dependencias—no polyfills, no paquetes externos, ni siquiera interfaces PSR. Esto significa menos vectores de ataque, una huella más pequeña y no hay cambios rompedores sorpresa de dependencias upstream. Los plugins opcionales pueden incluir dependencias, pero el núcleo siempre permanecerá ligero y seguro.
- **Enfocado en IA:** La sobrecarga mínima y la arquitectura limpia de Flight lo hacen ideal para integrar herramientas y APIs de IA. Ya sea que estés construyendo chatbots inteligentes, tableros impulsados por IA o solo quieras experimentar, Flight se aparta para que puedas enfocarte en lo que importa. La [aplicación skeleton](https://github.com/flightphp/skeleton) viene con archivos de instrucciones pre-construidos para los principales asistentes de codificación de IA directamente de la caja! [Aprende más sobre el uso de IA con Flight](/learn/ai)

## Resumen en Video

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 fs-5 text-center mt-5 pt-5">
      <span class="flight-title-video">¿Suficientemente simple, verdad?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Aprende más</a> sobre Flight en la documentación!
    </div>
  </div>
</div>

## Inicio Rápido

Para una instalación rápida y básica, instálalo con Composer:

```bash
composer require flightphp/core
```

O puedes descargar un zip del repositorio [aquí](https://github.com/flightphp/core). Luego tendrías un archivo `index.php` básico como el siguiente:

```php
<?php

// if installed with composer
require 'vendor/autoload.php';
// or if installed manually by zip file
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

## Aplicación Skeleton/Boilerplate

Hay una aplicación de ejemplo para ayudarte a comenzar tu proyecto con Flight. Tiene un diseño estructurado, configuraciones básicas todas listas y maneja scripts de composer directamente de la salida! Revisa [flightphp/skeleton](https://github.com/flightphp/skeleton) para un proyecto listo para usar, o visita la página de [ejemplos](examples) para inspiración. ¿Quieres ver cómo encaja la IA? [Explora ejemplos impulsados por IA](/learn/ai).

## Instalando la Aplicación Skeleton

¡Suficientemente fácil!

```bash
# Create the new project
composer create-project flightphp/skeleton my-project/
# Enter your new project directory
cd my-project/
# Bring up the local dev-server to get started right away!
composer start
```

Creará la estructura del proyecto, configurará los archivos que necesitas, ¡y estás listo para ir!

## Alto Rendimiento

Flight es uno de los frameworks de PHP más rápidos disponibles. Su núcleo ligero significa menos sobrecarga y más velocidad—perfecto tanto para aplicaciones tradicionales como para proyectos modernos impulsados por IA. Puedes ver todos los benchmarks en [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)

Mira el benchmark a continuación con algunos otros frameworks de PHP populares.

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


## Flight e IA

¿Curioso sobre cómo maneja la IA? [Descubre](/learn/ai) cómo Flight hace que trabajar con tu LLM de codificación favorita sea fácil!

## Estabilidad y Compatibilidad Hacia Atrás

Valoramos tu tiempo. Todos hemos visto frameworks que se reinventan completamente cada par de años, dejando a los desarrolladores con código roto y migraciones costosas. Flight es diferente. Flight v3 fue diseñado como una ampliación de v2, lo que significa que la API que conoces y amas no ha sido eliminada. De hecho, la mayoría de los proyectos v2 funcionarán sin cambios en v3. 

Estamos comprometidos a mantener Flight estable para que puedas enfocarte en construir tu aplicación, no en arreglar tu framework.

# Comunidad

Estamos en Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

Y Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Contribuyendo

Hay dos formas en que puedes contribuir a Flight:

1. Contribuye al framework principal visitando el [repositorio principal](https://github.com/flightphp/core).
2. ¡Ayuda a mejorar la documentación! Este sitio web de documentación está alojado en [Github](https://github.com/flightphp/docs). Si ves un error o quieres mejorar algo, siéntete libre de enviar un pull request. Amamos las actualizaciones y nuevas ideas—especialmente alrededor de IA y nueva tecnología!

# Requisitos

Flight requiere PHP 7.4 o superior.

**Nota:** PHP 7.4 es soportado porque en el momento actual de escritura (2024) PHP 7.4 es la versión predeterminada para algunas distribuciones Linux LTS. Forzar un movimiento a PHP >8 causaría mucho dolor de cabeza para esos usuarios. El framework también soporta PHP >8.

# Licencia

Flight se libera bajo la licencia [MIT](https://github.com/flightphp/core/blob/master/LICENSE).