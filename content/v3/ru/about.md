# Что такое Flight?

Flight — это быстрый, простой и расширяемый фреймворк для PHP. Он довольно универсален и может быть использован для создания любого типа веб-приложения. Он разработан с учетом простоты и написан так, чтобы его было легко понять и использовать.

Flight — отличный фреймворк для начинающих, которые новички в PHP и хотят научиться создавать веб-приложения. Это также отличный фреймворк для опытных разработчиков, которые хотят больше контроля над своими веб-приложениями. Он спроектирован для легкого создания RESTful API, простого веб-приложения или сложного веб-приложения.

## Быстрый старт

Сначала установите его с помощью Composer

```bash
composer require flightphp/core
```

или вы можете скачать zip-архив репозитория [здесь](https://github.com/flightphp/core). Затем у вас будет базовый файл `index.php`, похожий на следующий:

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
  Flight::json(['привет' => 'мир']);
});

Flight::start();
```

Вот и все! У вас есть базовое приложение на Flight. Теперь вы можете запустить этот файл с помощью `php -S localhost:8000` и посетить `http://localhost:8000` в вашем браузере, чтобы увидеть вывод.

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="Видеоплеер YouTube" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">Достаточно просто, верно?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Узнайте больше о Flight в документации!</a>

    </div>
  </div>
</div>

## Это быстро?

Да! Flight быстрый. Это один из самых быстрых фреймворков PHP, доступных на данный момент. Вы можете увидеть все тесты на [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)

Смотрите тест ниже с некоторыми другими популярными фреймворками PHP.

| Фреймворк  | Plaintext Reqs/sec | JSON Reqs/sec |
| ---------- | ------------------ | ------------- |
| Flight     | 190,421            | 182,491       |
| Yii        | 145,749            | 131,434       |
| Fat-Free   | 139,238            | 133,952       |
| Slim       | 89,588             | 87,348        |
| Phalcon    | 95,911             | 87,675        |
| Symfony    | 65,053             | 63,237        |
| Lumen      | 40,572             | 39,700        |
| Laravel    | 26,657             | 26,901        |
| CodeIgniter| 20,628             | 19,901        |

## Skeleton/Boilerplate App

Существует пример приложения, который может помочь вам начать работу с фреймворком Flight. Перейдите к [flightphp/skeleton](https://github.com/flightphp/skeleton) для инструкций о том, как начать! Вы также можете посетить страницу [примеры](examples) для вдохновения на некоторые вещи, которые вы можете сделать с Flight.

# Сообщество

Мы в Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

И в Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Участие

Существуют два способа, которыми вы можете внести свой вклад в Flight:

1. Вы можете внести свой вклад в ядро фреймворка, посетив [основной репозиторий](https://github.com/flightphp/core).
1. Вы можете внести свой вклад в документацию. Этот веб-сайт документации размещен на [Github](https://github.com/flightphp/docs). Если вы заметите ошибку или хотите улучшить что-то, не стесняйтесь исправить это и отправить запрос на изменение! Мы стараемся следить за всем, но обновления и переводы языков приветствуются.

# Требования

Flight требует PHP 7.4 или выше.

**Примечание:** PHP 7.4 поддерживается, потому что на данный момент (2024) PHP 7.4 является версией по умолчанию для некоторых LTS-дистрибутивов Linux. Принуждение к переходу на PHP >8 вызовет много недовольства у этих пользователей. Фреймворк также поддерживает PHP >8.

# Лицензия

Flight выпускается под лицензией [MIT](https://github.com/flightphp/core/blob/master/LICENSE).