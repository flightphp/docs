Tracy Flight Panel Extensions
=====

Це набір розширень, щоб зробити роботу з Flight трохи більш насиченою.

- Flight - Аналізувати всі змінні Flight.
- Database - Аналізувати всі запити, що виконалися на сторінці (якщо ви правильно ініціювали з'єднання з базою даних)
- Request - Аналізувати всі змінні `$_SERVER` та перевіряти всі глобальні дані (`$_GET`, `$_POST`, `$_FILES`)
- Session - Аналізувати всі змінні `$_SESSION`, якщо сесії активно використовуються.

Це панель

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

І кожна панель показує дуже корисну інформацію про вашу програму!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Натисніть [тут](https://github.com/flightphp/tracy-extensions), щоб переглянути код.

Installation
-------
Запустіть `composer require flightphp/tracy-extensions --dev` і ви на правильному шляху!

Configuration
-------
Є дуже небагато конфігурацій, які вам потрібно зробити, щоб це запрацювало. Вам потрібно ініціювати відладчик Tracy перед використанням цього [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// код завантаження
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Можливо, вам потрібно вказати ваше середовище з Debugger::enable(Debugger::DEVELOPMENT)

// якщо ви використовуєте з'єднання з базою даних у вашій програмі, 
// необхідний обгортка PDO, щоб використовувати ТІЛЬКИ В РОЗРОБЦІ (не в продукції, будь ласка!)
// Він має ті ж параметри, що й звичайне з'єднання PDO
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// або, якщо ви прикріпите це до фреймворку Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// тепер щоразу, коли ви виконуєте запит, він буде реєструвати час, запит та параметри

// Це з'єднує всі точки
if(Debugger::$showBar === true) {
	// Це повинно бути false або Tracy не зможе відобразити :( 
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// більше коду

Flight::start();
```

## Додаткове налаштування

### Дані сесії
Якщо у вас є власний обробник сесій (такий як ghostff/session), ви можете передати будь-який масив даних сесії до Tracy, і вона автоматично виведе його для вас. Ви передаєте його з ключем `session_data` у другому параметрі конструктора `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Це повинно бути false або Tracy не зможе відобразити :( 
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// маршрути та інші речі...

Flight::start();
```

### Latte

Якщо у вас є Latte, встановлений у вашому проекті, ви можете використовувати панель Latte для аналізу ваших шаблонів. Ви можете передати екземпляр Latte до конструктора `TracyExtensionLoader` з ключем `latte` у другому параметрі.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// це те місце, де ви додаєте панель Latte до Tracy
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// Це повинно бути false або Tracy не зможе відобразити :( 
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
```