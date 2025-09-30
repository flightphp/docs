# Фреймворк Flight PHP

Flight — це швидкий, простий, розширюваний фреймворк для PHP, створений для розробників, які хочуть швидко виконувати завдання, без зайвого клопоту. Незалежно від того, чи створюєте ви класичний веб-додаток, швидкісний API чи експериментуєте з останніми інструментами на основі ШІ, низький відбиток і простий дизайн Flight роблять його ідеальним вибором. Flight призначений бути легким, але також може задовольняти вимоги корпоративної архітектури.

## Чому обрати Flight?

- **Дружечний для початківців:** Flight — чудова стартова точка для нових розробників PHP. Його чітка структура і проста синтаксис допомагають вивчати веб-розробку, не гублячись у зайвому коді.
- **Улюблений професіоналами:** Досвідчені розробники люблять Flight за його гнучкість і контроль. Ви можете масштабувати від маленького прототипу до повноцінного додатку, не змінюючи фреймворків.
- **Дружечний до ШІ:** Мінімальний наклад і чиста архітектура Flight роблять його ідеальним для інтеграції інструментів і API ШІ. Чи то ви створюєте розумні чатботи, панелі керування на основі ШІ, чи просто експериментуєте, Flight не заважає, щоб ви могли зосередитися на головному. [skeleton app](https://github.com/flightphp/skeleton) містить попередньо налаштовані файли інструкцій для основних помічників кодування на основі ШІ прямо з коробки! [Дізнатися більше про використання ШІ з Flight](/learn/ai)

## Огляд у відео

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 fs-5 text-center mt-5 pt-5">
      <span class="flight-title-video">Досить просто, правда?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Дізнатися більше</a> про Flight у документації!
    </div>
  </div>
</div>

## Швидкий старт

Щоб швидко встановити базову версію, встановіть за допомогою Composer:

```bash
composer require flightphp/core
```

Або ви можете завантажити zip-архів репозиторію [here](https://github.com/flightphp/core). Тоді у вас буде базовий файл `index.php`, як ось:

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
  Flight::json([
	'hello' => 'world'
  ]);
});

Flight::start();
```

Ось і все! У вас є базовий додаток Flight. Тепер ви можете запустити цей файл за допомогою `php -S localhost:8000` і відвідати `http://localhost:8000` у своєму браузері, щоб побачити вивід.

## Skeleton/Boilerplate App

Є приклад додатку, який допоможе вам розпочати проект з Flight. Він має структуровану розмітку, базові налаштування, готові до використання, і обробляє скрипти composer прямо з коробки! Перегляньте [flightphp/skeleton](https://github.com/flightphp/skeleton) для готового проекту, або відвідайте сторінку [examples](examples) для натхнення. Хочете побачити, як вписується ШІ? [Explore AI-powered examples](/learn/ai).

## Встановлення Skeleton App

Дуже просто!

```bash
# Створіть новий проект
composer create-project flightphp/skeleton my-project/
# Введіть у директорію нового проекту
cd my-project/
# Запустіть локальний сервер розробки, щоб розпочати одразу!
composer start
```

Він створить структуру проекту, налаштує потрібні файли, і ви готові до роботи!

## Висока продуктивність

Flight — один з найшвидших фреймворків PHP. Його легке ядро означає менше накладу і більше швидкості — ідеально для традиційних додатків і сучасних проектів на основі ШІ. Ви можете переглянути всі бенчмарки на [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)

Перегляньте бенчмарк нижче з деякими популярними PHP фреймворками.

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


## Flight і ШІ

Цікаво, як він працює з ШІ? [Discover](/learn/ai) як Flight полегшує роботу з вашим улюбленим кодувальним LLM!

# Спільнота

Ми в Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

І в Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Співпраця

Є два способи, як ви можете внести внесок у Flight:

1. Внесіть внесок у основний фреймворк, відвідавши [core repository](https://github.com/flightphp/core).
2. Допоможіть покращити документацію! Цей веб-сайт документації розміщений на [Github](https://github.com/flightphp/docs). Якщо ви помітили помилку або хочете щось покращити, будь ласка, надішліть pull request. Ми любимо оновлення і нові ідеї — особливо щодо ШІ і нових технологій!

# Вимоги

Flight вимагає PHP 7.4 або вище.

**Примітка:** PHP 7.4 підтримується, бо на момент написання (2024) PHP 7.4 є версією за замовчуванням для деяких LTS дистрибутивів Linux. Примусовий перехід на PHP >8 спричинив би багато проблем для цих користувачів. Фреймворк також підтримує PHP >8.

# Ліцензія

Flight поширюється під [MIT](https://github.com/flightphp/core/blob/master/LICENSE) ліцензією.