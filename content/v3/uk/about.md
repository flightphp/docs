# Що таке Flight?

Flight – це швидкий, простий, розширювальний фреймворк для PHP. Він досить універсальний і може бути використаний для створення будь-якого виду веб-додатків. Він побудований з урахуванням простоти і написаний так, щоб його було легко зрозуміти та використовувати.

Flight – це чудовий фреймворк для початківців, які нові у PHP і хочуть навчитися створювати веб-додатки. Це також чудовий фреймворк для досвідчених розробників, які хочуть мати більше контролю над своїми веб-додатками. Він спроектований для легкого створення RESTful API, простого веб-додатка або складного веб-додатка.

## Швидкий старт

Перш ніж його встановити, використовуючи Composer

```bash
composer require flightphp/core
```

або ви можете завантажити zip-архів репозиторію [тут](https://github.com/flightphp/core). Тоді у вас буде базовий файл `index.php`, схожий на наступний:

```php
<?php

// якщо встановлено за допомогою composer
require 'vendor/autoload.php';
// або якщо встановлено вручну за допомогою zip-файлу
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'привіт, світ!';
});

Flight::route('/json', function() {
  Flight::json(['привіт' => 'світ']);
});

Flight::start();
```

Ось і все! У вас є базовий додаток Flight. Тепер ви можете запустити цей файл за допомогою `php -S localhost:8000` і відвідати `http://localhost:8000` у вашому браузері, щоб побачити результат.

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">Досить просто, так?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Дізнайтеся більше про Flight у документації!</a>

    </div>
  </div>
</div>

## Чи швидкий він?

Так! Flight швидкий. Він є одним з найбільш швидких фреймворків PHP. Ви можете побачити всі бенчмарки на [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)

Дивіться бенчмарк нижче з деякими іншими популярними фреймворками PHP.

| Фреймворк | Plaintext Reqs/sec | JSON Reqs/sec |
| --------- | ------------ | ------------ |
| Flight      | 190,421    | 182,491 |
| Yii         | 145,749    | 131,434 |
| Fat-Free    | 139,238	   | 133,952 |
| Slim        | 89,588     | 87,348  |
| Phalcon     | 95,911     | 87,675  |
| Symfony     | 65,053     | 63,237  |
| Lumen	      | 40,572     | 39,700  |
| Laravel     | 26,657     | 26,901  |
| CodeIgniter | 20,628     | 19,901  |

## Скелет/Шаблон додатка

Існує приклад додатка, який може допомогти вам почати працювати з фреймворком Flight. Перейдіть на [flightphp/skeleton](https://github.com/flightphp/skeleton) для отримання інструкцій, як почати! Ви також можете відвідати сторінку [прикладів](examples) для натхнення щодо деяких речей, які ви можете зробити з Flight.

# Спільнота

Ми в Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

І в Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Участь

Є два способи, якими ви можете внести свій внесок у Flight:

1. Ви можете внести свій внесок у основний фреймворк, відвідавши [основний репозиторій](https://github.com/flightphp/core).
1. Ви можете внести свій внесок у документацію. Цей веб-сайт документації розміщено на [Github](https://github.com/flightphp/docs). Якщо ви помітите помилку або хочете вдосконалити щось, не соромтеся виправити це та надіслати запит на злиття! Ми намагаємося стежити за речами, але оновлення та переклади мов бажані.

# Вимоги

Flight вимагає PHP 7.4 або новішої версії.

**Примітка:** PHP 7.4 підтримується, оскільки на момент написання (2024) PHP 7.4 є версією за замовчуванням для деяких LTS-дистрибутивів Linux. Примусове переходження на PHP >8 викликало б багато труднощів для цих користувачів. Фреймворк також підтримує PHP >8.

# Ліцензія

Flight випущений під ліцензією [MIT](https://github.com/flightphp/core/blob/master/LICENSE).