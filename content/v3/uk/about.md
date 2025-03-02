# Що таке Flight?

Flight - це швидкий, простий, розширювальний фреймворк для PHP. Він досить універсальний і може використовуватися для створення будь-яких видів веб-додатків. Він створений з урахуванням простоти і написаний так, що легко зрозуміти та використовувати.

Flight - це чудовий фреймворк для новачків, які тільки починають працювати з PHP і хочуть навчитися створювати веб-додатки. Це також чудовий фреймворк для досвідчених розробників, які хочуть мати більше контролю над своїми веб-додатками. Він спроектований для простого створення RESTful API, простого веб-додатку або складного веб-додатку.

## Швидкий старт

```php
<?php

// якщо встановлено за допомогою composer
require 'vendor/autoload.php';
// або якщо встановлено вручну за допомогою zip файлу
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
      <span class="fligth-title-video">Досить просто, правда?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Дізнайтеся більше про Flight у документації!</a>

    </div>
  </div>
</div>

### Скелет/Шаблон додатку

Є приклад додатку, який може допомогти вам розпочати роботу з фреймворком Flight. Перейдіть до [flightphp/skeleton](https://github.com/flightphp/skeleton) для отримання інструкцій щодо початку! Ви також можете відвідати сторінку [examples](examples) для натхнення щодо деяких речей, які ви можете зробити з Flight.

# Спільнота

Ми в Matrix, спілкуйтеся з нами в [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Внесок

Існує два способи, якими ви можете внести свій внесок у Flight: 

1. Ви можете внести свій внесок у основний фреймворк, відвідавши [core repository](https://github.com/flightphp/core). 
2. Ви можете внести свій внесок у документацію. Цей сайт документації розміщений на [Github](https://github.com/flightphp/docs). Якщо ви помітили помилку або хочете вдосконалити щось, не соромтеся виправити це і надіслати запит на злиття! Ми намагаємося слідкувати за усім, але оновлення та мовні переклади завжди вітаються.

# Вимоги

Flight вимагає PHP 7.4 або вище.

**Примітка:** PHP 7.4 підтримується, оскільки на момент написання (2024) PHP 7.4 є версією за замовчуванням для деяких LTS дистрибутивів Linux. Примусове переведення на PHP >8 могло б викликати багато незручностей для цих користувачів. Фреймворк також підтримує PHP >8.

# Ліцензія

Flight випускається під ліцензією [MIT](https://github.com/flightphp/core/blob/master/LICENSE).