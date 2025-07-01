# Что такое Flight?

Flight — это быстрый, простой, расширяемый фреймворк для PHP, созданный для разработчиков, которые хотят быстро выполнять задачи, без лишней суеты. Будь то классическое веб-приложение, сверхбыстрый API или эксперименты с последними инструментами на базе ИИ, низкий объем и прямолинейный дизайн Flight делают его идеальным выбором.

## Почему выбрать Flight?

- **Дружественный для начинающих:** Flight — отличная отправная точка для новых разработчиков PHP. Его четкая структура и простой синтаксис помогут вам освоить веб-разработку, не запутавшись в шаблонном коде.
- **Любимый профессионалами:** Опытные разработчики ценят Flight за его гибкость и контроль. Вы можете масштабировать от небольшого прототипа до полноценного приложения, не переключаясь на другие фреймворки.
- **Дружественный к ИИ:** Минимальный накладный расход и чистая архитектура Flight идеально подходят для интеграции инструментов ИИ и API. Будь то создание умных чат-ботов, дашбордов на базе ИИ или просто эксперименты, Flight не мешает, чтобы вы могли сосредоточиться на главном. [Узнать больше об использовании ИИ с Flight](/learn/ai)

## Быстрый запуск

Сначала установите его с помощью Composer:

```bash
composer require flightphp/core
```

Или скачайте zip-архив репозитория [здесь](https://github.com/flightphp/core). Затем у вас будет базовый файл `index.php`, как в следующем примере:

```php
<?php

// если установлен с помощью composer
require 'vendor/autoload.php';
// или если установлен вручную из zip-файла
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

Вот и все! У вас есть базовое приложение Flight. Теперь вы можете запустить этот файл с помощью `php -S localhost:8000` и перейти по адресу `http://localhost:8000` в браузере, чтобы увидеть вывод.

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">Достаточно просто, правда?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Узнайте больше о Flight в документации!</a>
      <br>
      <a href="/learn/ai" class="btn btn-primary mt-3">Откройте, как Flight упрощает работу с ИИ</a>
    </div>
  </div>
</div>

## Это быстро?

Абсолютно! Flight — один из самых быстрых фреймворков для PHP. Его легкий ядро означает меньше накладных расходов и больше скорости — идеально для традиционных приложений и современных проектов на базе ИИ. Вы можете увидеть все результаты тестов на [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)

Смотрите ниже сравнение с некоторыми популярными фреймворками PHP.

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

## Пример приложения (Skeleton/Boilerplate)

Есть пример приложения, чтобы помочь вам начать работу с Flight. Посмотрите [flightphp/skeleton](https://github.com/flightphp/skeleton) для готового проекта или посетите страницу [примеров](examples) для вдохновения. Хотите увидеть, как вписывается ИИ? [Изучите примеры на базе ИИ](/learn/ai).

# Сообщество

Мы в Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

И в Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Вклад в развитие

Есть два способа внести вклад в Flight:

1. Внести вклад в основной фреймворк, посетив [репозиторий ядра](https://github.com/flightphp/core).
2. Помочь улучшить документацию! Этот сайт документации размещен на [Github](https://github.com/flightphp/docs). Если вы заметите ошибку или захотите что-то улучшить, не стесняйтесь отправить пулл-реквест. Мы любим обновления и новые идеи — особенно связанные с ИИ и новыми технологиями!

# Требования

Flight требует PHP 7.4 или выше.

**Примечание:** PHP 7.4 поддерживается, потому что на момент написания (2024) PHP 7.4 является версией по умолчанию для некоторых дистрибутивов Linux с долгосрочной поддержкой. Принудительное переключение на PHP >8 вызвало бы проблемы для этих пользователей. Фреймворк также поддерживает PHP >8.

# Лицензия

Flight распространяется под [лицензией MIT](https://github.com/flightphp/core/blob/master/LICENSE).