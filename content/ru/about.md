# Что такое Flight?

Flight — это быстрый, простой, расширяемый фреймворк для PHP. Он довольно универсален и может использоваться для создания любого типа веб-приложения. Он разработан с учетом простоты и написан так, чтобы его было легко понимать и использовать.

Flight — отличный фреймворк для начинающих, которые новы в PHP и хотят научиться создавать веб-приложения. Это также отличный фреймворк для опытных разработчиков, которые хотят больше контроля над своими веб-приложениями. Он предназначен для простого создания RESTful API, простого веб-приложения или сложного веб-приложения.

## Быстрый старт

```php
<?php

// если установлен с помощью composer
require 'vendor/autoload.php';
// или если установлен вручную через zip-файл
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'привет, мир!';
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
      <span class="fligth-title-video">Достаточно просто, не так ли?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Узнайте больше о Flight в документации!</a>
    </div>
  </div>
</div>

### Скелет/Шаблон приложения

Существует пример приложения, который может помочь вам начать работать с фреймворком Flight. Перейдите к [flightphp/skeleton](https://github.com/flightphp/skeleton) для получения инструкций о том, как начать! Вы также можете посетить страницу [examples](examples) для вдохновения по некоторым вещам, которые вы можете сделать с Flight.

# Сообщество

Мы в Matrix Чате с нами по адресу [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Участие

Существует два способа, которыми вы можете внести свой вклад в Flight:

1. Вы можете внести вклад в основной фреймворк, посетив [core repository](https://github.com/flightphp/core).
2. Вы можете внести свой вклад в документацию. Этот сайт документации размещен на [Github](https://github.com/flightphp/docs). Если вы заметили ошибку или хотите улучшить что-то, не стесняйтесь исправлять это и отправлять запрос на включение! Мы стараемся держать все в порядке, но обновления и переводы на другие языки приветствуются.

# Требования

Flight требует PHP 7.4 или более поздней версии.

**Примечание:** PHP 7.4 поддерживается, потому что на данный момент написания (2024) PHP 7.4 является версией по умолчанию для некоторых LTS дистрибутивов Linux. Принудительное переход на PHP >8 вызовет много неудобств для этих пользователей. Фреймворк также поддерживает PHP >8.

# Лицензия

Flight выпущен под лицензией [MIT](https://github.com/flightphp/core/blob/master/LICENSE).