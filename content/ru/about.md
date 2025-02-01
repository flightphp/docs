# Что такое Flight?

Flight — это быстрый, простой и расширяемый фреймворк для PHP. Он довольно универсален и может использоваться для создания любого типа веб-приложений. Он создан с учетом простоты и написан так, что легко понимать и использовать.

Flight является отличным стартовым фреймворком для тех, кто новичок в PHP и хочет научиться создавать веб-приложения. Это также отличный фреймворк для опытных разработчиков, которые хотят больше контроля над своими веб-приложениями. Он спроектирован так, чтобы легко создавать RESTful API, простое веб-приложение или сложное веб-приложение.

## Быстрый старт

```php
<?php

// если установлен с помощью composer
require 'vendor/autoload.php';
// или если установлен вручную из zip файла
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">Достаточно просто, правда?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Узнайте больше о Flight в документации!</a>

    </div>
  </div>
</div>

### Скелет/Шаблон приложения

Существует пример приложения, которое может помочь вам начать работу с фреймворком Flight. Перейдите на [flightphp/skeleton](https://github.com/flightphp/skeleton) для получения инструкций о том, как начать! Вы также можете посетить страницу [examples](examples) для вдохновения о некоторых вещах, которые вы можете сделать с Flight.

# Сообщество

Мы на Matrix. Общайтесь с нами на [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Вклад

Существует два способа, с помощью которых вы можете внести вклад в Flight:

1. Вы можете внести вклад в основной фреймворк, посетив [core repository](https://github.com/flightphp/core).
1. Вы можете внести вклад в документацию. Этот сайт документации размещен на [Github](https://github.com/flightphp/docs). Если вы заметили ошибку или хотите лучше объяснить что-то, не стесняйтесь исправлять и отправлять запрос на изменение! Мы стараемся следить за всем, но обновления и переводы языков всегда приветствуются.

# Требования

Flight требует PHP 7.4 или выше.

**Примечание:** PHP 7.4 поддерживается, потому что на момент написания (2024) PHP 7.4 является стандартной версией для некоторых LTS дистрибутивов Linux. Принуждение к переходу на PHP >8 вызвало бы много проблем для этих пользователей. Фреймворк также поддерживает PHP >8.

# Лицензия

Flight выпущен под лицензией [MIT](https://github.com/flightphp/core/blob/master/LICENSE).