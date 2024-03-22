# Что такое Flight?

Flight - быстрый, простой, расширяемый фреймворк для PHP. Он довольно универсален и может использоваться для создания любого вида веб-приложений. Он создан с учетом простоты и написан таким образом, чтобы быть легким в понимании и использовании.

Flight - отличный начальный фреймворк для тех, кто нов в PHP и хочет научиться создавать веб-приложения. Он также отлично подходит опытным разработчикам, которые хотят иметь больше контроля над своими веб-приложениями. Он предназначен для легкого создания RESTful API, простого веб-приложения или сложного веб-приложения.

## Быстрый старт

```php
<?php

// если установлен с помощью composer
require 'vendor/autoload.php';
// или, если установлен вручную из zip-файла
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'Привет, мир!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

<div class="video-container">
	<iframe width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
</div>

Достаточно просто, верно? [Узнайте больше о Flight в документации!](learn)

### Пример приложения "Skeleton/Boilerplate"

Существует пример приложения, который поможет вам начать работу с фреймворком Flight. Перейдите по ссылке [flightphp/skeleton](https://github.com/flightphp/skeleton) для получения инструкций о том, как начать! Вы также можете посетить страницу [examples](examples) для вдохновения на то, что можно сделать с помощью Flight.

# Сообщество

Мы находимся в Matrix! Общайтесь с нами в чате [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Вклад

Есть два способа внести свой вклад в Flight:

1. Вы можете внести вклад в основной фреймворк, посетив [core repository](https://github.com/flightphp/core).
1. Вы можете внести вклад в документацию. Этот веб-сайт документации размещен на [Github](https://github.com/flightphp/docs). Если вы заметили ошибку или хотите улучшить что-то, не стесняйтесь исправить и отправить запрос на включение изменений! Мы стараемся быть в курсе происходящего, но обновления и переводы на другие языки приветствуются.

# Требования

Flight требует PHP 7.4 или выше.

**Примечание:** PHP 7.4 поддерживается, потому что на момент написания (2024 год) PHP 7.4 является версией по умолчанию для некоторых дистрибутивов LTS Linux. Принудительный переход на PHP >8 вызвал бы много проблем для таких пользователей. Фреймворк также поддерживает PHP >8.

# Лицензия

Flight выпущен под лицензией [MIT](https://github.com/flightphp/core/blob/master/LICENSE).