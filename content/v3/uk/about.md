# Що таке Flight?

Flight — це швидкий, простий, розширюваний фреймворк для PHP, створений для розробників, які хочуть швидко виконувати завдання, без зайвих клопотів. Чи ви створюєте класичний веб-додаток, блискавичний API чи експериментуєте з останніми інструментами на основі ШІ, низький слід Flight і простий дизайн роблять його ідеальним варіантом.

## Чому обрати Flight?

- **Дружній для початківців:** Flight — чудова стартова точка для нових розробників PHP. Його чітка структура і проста синтаксис допомагають навчитися веб-розробці, не гублячись у шаблонному коді.
- **Улюблений професіоналами:** Досвідчені розробники люблять Flight за його гнучкість і контроль. Ви можете масштабувати від маленького прототипу до повноцінного додатку, не змінюючи фреймворків.
- **Дружній до ШІ:** Мінімальний наклад і чиста архітектура Flight роблять його ідеальним для інтеграції інструментів і API ШІ. Чи ви створюєте розумних чатботів, панелі керування на основі ШІ, чи просто експериментуєте, Flight не заважає, щоб ви могли зосередитися на тому, що важливо. [Дізнатися більше про використання ШІ з Flight](/learn/ai)

## Швидкий старт

Спочатку встановіть за допомогою Composer:

```bash
composer require flightphp/core
```

Або ви можете завантажити zip-архів репозиторію [тут](https://github.com/flightphp/core). Тоді у вас буде базовий файл `index.php`, як ось:

```php
<?php

// якщо встановлено за допомогою composer
require 'vendor/autoload.php';
// або якщо встановлено вручну за допомогою zip-файлу
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

Ось і все! У вас є базовий додаток Flight. Тепер ви можете запустити цей файл за допомогою `php -S localhost:8000` і відвідати `http://localhost:8000` у своєму браузері, щоб побачити вивід.

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">Достатньо просто, правда?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Дізнатися більше про Flight у документації!</a>
      <br>
      <a href="/learn/ai" class="btn btn-primary mt-3">Відкрийте, як Flight полегшує ШІ</a>
    </div>
  </div>
</div>

## Чи це швидко?

Абсолютно! Flight — один з найшвидших фреймворків PHP. Його легка основа означає менше накладних витрат і більше швидкості — ідеально для традиційних додатків і сучасних проєктів на основі ШІ. Ви можете переглянути всі бенчмарки на [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)

Дивіться бенчмарк нижче з деякими іншими популярними фреймворками PHP.

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

## Скелет/Базовий додаток

Є приклад додатку, щоб допомогти вам розпочати з Flight. Перегляньте [flightphp/skeleton](https://github.com/flightphp/skeleton) для готового проєкту, або відвідайте сторінку [приклади](examples) для натхнення. Хочете побачити, як вписується ШІ? [Ознайомтеся з прикладами на основі ШІ](/learn/ai).

# Спільнота

Ми в Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

І в Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Співпраця

Існує два способи, як ви можете внести свій внесок у Flight:

1. Внесіть свій внесок у основний фреймворк, відвідавши [репозиторій core](https://github.com/flightphp/core).
2. Допоможіть покращити документацію! Цей веб-сайт документації розміщений на [Github](https://github.com/flightphp/docs). Якщо ви помітили помилку або хочете щось покращити, будь ласка, надішліть pull request. Ми любимо оновлення та нові ідеї — особливо навколо ШІ та нових технологій!

# Вимоги

Flight вимагає PHP 7.4 або вище.

**Примітка:** PHP 7.4 підтримується, тому що на час написання (2024) PHP 7.4 є версією за замовчуванням для деяких дистрибутивів Linux LTS. Примусовий перехід на PHP >8 спричинив би багато проблем для цих користувачів. Фреймворк також підтримує PHP >8.

# Ліцензія

Flight випущено під [MIT](https://github.com/flightphp/core/blob/master/LICENSE) ліцензією.