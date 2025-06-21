Tracy Flight Panel Extensions
=====

Це набір розширень, щоб зробити роботу з Flight трохи багатшою.

- Flight - Аналізувати всі змінні Flight.
- Database - Аналізувати всі запити, які виконувалися на сторінці (якщо ви правильно ініціалізували підключення до бази даних).
- Request - Аналізувати всі змінні `$_SERVER` і перевіряти всі глобальні дані (`$_GET`, `$_POST`, `$_FILES`).
- Session - Аналізувати всі змінні `$_SESSION`, якщо сесії активні.

Це панель

![Flight Бар](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

І кожна панель відображає дуже корисну інформацію про ваш додаток!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Натисніть [тут](https://github.com/flightphp/tracy-extensions), щоб переглянути код.

Installation
-------
Виконайте `composer require flightphp/tracy-extensions --dev` і ви готові!

Configuration
-------
Для початку вам потрібно дуже мало конфігурації. Вам потрібно ініціалізувати налагоджувач Tracy перед використанням [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// код завантаження
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Ви можете вказати ваше середовище за допомогою Debugger::enable(Debugger::DEVELOPMENT)

// якщо ви використовуєте підключення до бази даних у вашому додатку, є 
// необхідний обгортка PDO для використання ТІЛЬКИ В РОЗРОБЦІ (не в продакшні, будь ласка!)
// Вона має ті самі параметри, що й звичайне підключення PDO
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// або якщо ви прикріплюєте це до фреймворку Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// тепер щоразу, коли ви робите запит, він зафіксує час, запит і параметри

// Це з'єднує точки
if(Debugger::$showBar === true) {
	// Це потрібно встановити на false, інакше Tracy не зможе відобразитися :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// більше коду

Flight::start();
```

## Additional Configuration

### Session Data
Якщо у вас є власний обробник сесій (наприклад, ghostff/session), ви можете передати будь-який масив даних сесії до Tracy, і він автоматично виведе його. Ви передаєте його за допомогою ключа `session_data` у другому параметрі конструктора `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;
// або use flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Це потрібно встановити на false, інакше Tracy не зможе відобразитися :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// маршрути та інші речі...

Flight::start();
```

### Latte

Якщо у вашому проєкті встановлено Latte, ви можете використовувати панель Latte для аналізу ваших шаблонів. Ви можете передати екземпляр Latte до конструктора `TracyExtensionLoader` за допомогою ключа `latte` у другому параметрі.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// тут ви додаєте панель Latte до Tracy
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// Це потрібно встановити на false, інакше Tracy не зможе відобразитися :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
```