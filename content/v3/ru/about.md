# Фреймворк PHP Flight

Flight — это быстрый, простой и расширяемый фреймворк для PHP, созданный для разработчиков, которые хотят быстро выполнять задачи без лишних хлопот. Независимо от того, создаете ли вы классическое веб-приложение, сверхбыстрый API или экспериментируете с последними инструментами на базе ИИ, низкая нагрузка и прямолинейный дизайн Flight делают его идеальным выбором. Flight предназначен для того, чтобы быть легким, но при этом он может справляться с требованиями корпоративной архитектуры.

## Почему выбрать Flight?

- **Дружественный для начинающих:** Flight — отличная отправная точка для новых разработчиков PHP. Его четкая структура и простой синтаксис помогают освоить веб-разработку, не запутавшись в шаблонном коде.
- **Любимый профессионалами:** Опытные разработчики любят Flight за его гибкость и контроль. Вы можете масштабировать от маленького прототипа до полноценного приложения, не переключаясь на другие фреймворки.
- **Дружественный к ИИ:** Минимальная нагрузка и чистая архитектура Flight идеально подходят для интеграции инструментов и API ИИ. Если вы создаете умные чатботы, дашборды на базе ИИ или просто экспериментируете, Flight не мешает, позволяя сосредоточиться на главном. В [skeleton app](https://github.com/flightphp/skeleton) уже есть предустановленные инструкции для основных ассистентов ИИ прямо из коробки! [Узнайте больше об использовании ИИ с Flight](/learn/ai)

## Обзор в видео

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 fs-5 text-center mt-5 pt-5">
      <span class="flight-title-video">Достаточно просто, верно?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Узнайте больше</a> о Flight в документации!
    </div>
  </div>
</div>

## Быстрый старт

Для быстрой базовой установки установите его с помощью Composer:

```bash
composer require flightphp/core
```

Или вы можете скачать ZIP-архив репозитория [here](https://github.com/flightphp/core). Затем у вас будет базовый файл `index.php`, как в следующем примере:

```php
<?php

// если установлен с помощью composer
require 'vendor/autoload.php';
// или если установлен вручную из ZIP-файла
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

Вот и все! У вас есть базовое приложение Flight. Теперь вы можете запустить этот файл с помощью `php -S localhost:8000` и посетить `http://localhost:8000` в браузере, чтобы увидеть вывод.

## Пример приложения (Skeleton/Boilerplate)

Есть пример приложения, чтобы помочь вам начать проект с Flight. В нем есть структурированная разметка, базовые конфигурации и обработка сценариев Composer прямо из коробки! Посмотрите [flightphp/skeleton](https://github.com/flightphp/skeleton) для готового проекта или посетите страницу [examples](examples) для вдохновения. Хотите увидеть, как вписывается ИИ? [Изучите примеры на базе ИИ](/learn/ai).

## Установка примера приложения

Очень просто!

```bash
# Создайте новый проект
composer create-project flightphp/skeleton my-project/
# Войдите в директорию нового проекта
cd my-project/
# Запустите локальный сервер разработки, чтобы начать сразу!
composer start
```

Это создаст структуру проекта, настроит необходимые файлы, и вы готовы к работе!

## Высокая производительность

Flight — один из самых быстрых фреймворков PHP. Его легкий ядро означает меньше нагрузки и больше скорости — идеально для традиционных приложений и современных проектов на базе ИИ. Вы можете увидеть все тесты производительности на [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks).

Смотрите тест ниже с некоторыми популярными фреймворками PHP.

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


## Flight и ИИ

Интересно, как он работает с ИИ? [Узнайте](/learn/ai), как Flight облегчает работу с вашим любимым LLM для кодирования!

# Сообщество

Мы в Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

И в Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Вклад

Есть два способа внести вклад в Flight:

1. Внести вклад в основной фреймворк, посетив [core repository](https://github.com/flightphp/core).
2. Помочь улучшить документацию! Этот сайт документации размещен на [Github](https://github.com/flightphp/docs). Если вы заметили ошибку или хотите что-то улучшить, отправьте pull request. Мы любим обновления и новые идеи — особенно связанные с ИИ и новыми технологиями!

# Требования

Flight требует PHP 7.4 или выше.

**Примечание:** PHP 7.4 поддерживается, потому что на момент написания (2024) PHP 7.4 является версией по умолчанию для некоторых дистрибутивов Linux с долгосрочной поддержкой. Принудительный переход на PHP >8 вызвал бы проблемы для пользователей. Фреймворк также поддерживает PHP >8.

# Лицензия

Flight распространяется под [MIT](https://github.com/flightphp/core/blob/master/LICENSE) лицензией.