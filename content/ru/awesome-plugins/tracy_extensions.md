Tracy Flight Panel Extensions
=====

Это набор расширений, чтобы работа с Flight была немного более обширной.

- Flight - Анализ всех переменных Flight.
- Database - Анализ всех запросов, выполняемых на странице (если правильно инициировать соединение с базой данных).
- Request - Анализ всех переменных `$_SERVER` и изучение всех глобальных нагрузок (`$_GET`, `$_POST`, `$_FILES`).
- Session - Анализ всех переменных `$_SESSION`, если сеансы активны.

Это Панель

![Панель Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

И каждая панель отображает очень полезную информацию о вашем приложении!

![Данные Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![База данных Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Запрос Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Нажмите [здесь](https://github.com/flightphp/tracy-extensions), чтобы просмотреть код.

Установка
-------
Запустите `composer require flightphp/tracy-extensions --dev` и вы на правильном пути!

Конфигурация
-------
Вам нужно сделать очень небольшую настройку, чтобы начать использовать это. Вам нужно инициировать отладчик Tracy перед использованием этого [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// код инициализации
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Возможно, вам нужно указать ваше окружение с помощью Debugger::enable(Debugger::DEVELOPMENT)

// если вы используете подключения к базе данных в вашем приложении, есть 
// обязательная оболочка PDO для использования ТОЛЬКО В РАЗРАБОТКЕ (пожалуйста, не в продакшн!)
// У него те же параметры, что и обычное соединение PDO
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// или если вы присоединяете это к фреймворку Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// теперь при каждом запросе он будет записывать время, запрос и параметры

// Это соединяет точки
if(Debugger::$showBar === true) {
	// Это должно быть false, иначе Tracy не сможет отображаться :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// еще код

Flight::start();
```

## Дополнительная Конфигурация

### Данные сеанса
Если у вас есть пользовательский обработчик сеансов (например, ghostff/session), вы можете передать любой массив данных сеанса в Tracy, и он автоматически выведет его для вас. Вы передаете это с ключом `session_data` вторым параметром конструктора `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Это должно быть false, иначе Tracy не сможет отображаться :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// маршруты и другие вещи...

Flight::start();
```

### Latte

Если у вас установлен Latte в вашем проекте, вы можете использовать панель Latte для анализа ваших шаблонов. Вы можете передать экземпляр Latte в конструктор `TracyExtensionLoader` с ключом `latte` вторым параметром.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// здесь вы добавляете Панель Latte в Tracy
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// Это должно быть false, иначе Tracy не сможет отображаться :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
