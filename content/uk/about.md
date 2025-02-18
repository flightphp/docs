# Що таке Flight?

Flight — це швидкий, простий, розширювальний фреймворк для PHP. Він досить універсальний і може використовуватися для створення будь-якого роду веб-додатків. Він створений з урахуванням простоти і написаний так, що легко зрозуміти і використовувати.

Flight — це чудовий фреймворк для початківців, які нові в PHP і хочуть навчитися створювати веб-додатки. Це також чудовий фреймворк для досвідчених розробників, які хочуть більшого контролю над своїми веб-додатками. Він спроектований для легкого створення RESTful API, простого веб-додатку або складного веб-додатку.

## Швидкий початок

```php
<?php

// якщо встановлено через composer
require 'vendor/autoload.php';
// або якщо встановлено вручну за допомогою zip-файлу
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'привіт світ!';
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
      <span class="fligth-title-video">Досить просто, чи не так?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Дізнайтесь більше про Flight у документації!</a>

    </div>
  </div>
</div>

### Скелет/Базовий додаток

Існує приклад додатка, який може допомогти вам розпочати роботу з фреймворком Flight. Перейдіть до [flightphp/skeleton](https://github.com/flightphp/skeleton) для інструкцій щодо початку роботи! Ви також можете відвідати сторінку [приклади](examples) для надихнення щодо деяких речей, які ви можете зробити з Flight.

# Спільнота

Ми в Matrix, спілкуйтеся з нами на [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Участь у проекті

Є два способи, як ви можете допомогти Flight: 

1. Ви можете внести свій внесок у основний фреймворк, відвідавши [основний репозиторій](https://github.com/flightphp/core). 
1. Ви можете внести свій внесок у документацію. Цей веб-сайт документації розміщено на [Github](https://github.com/flightphp/docs). Якщо ви помітили помилку або хочете щось покращити, не соромтеся виправити це та надіслати запит на витяг! Ми намагаємося слідкувати за справами, але оновлення та переклади на інші мови вітаються.

# Вимоги

Flight вимагає PHP 7.4 або вище.

**Примітка:** PHP 7.4 підтримується, оскільки на момент написання (2024) PHP 7.4 є стандартною версією для деяких LTS дистрибутивів Linux. Примусове переходження на PHP >8 викликало б багато труднощів для цих користувачів. Фреймворк також підтримує PHP >8.

# Ліцензія

Flight випускається під ліцензією [MIT](https://github.com/flightphp/core/blob/master/LICENSE).