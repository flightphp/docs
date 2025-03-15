Tracy Flight Панель Расширения
=====

Это набор расширений, чтобы сделать работу с Flight немного более насыщенной.

- Flight - Анализировать все переменные Flight.
- Database - Анализировать все запросы, которые были выполнены на странице (если вы правильно инициируете подключение к базе данных)
- Request - Анализировать все переменные `$_SERVER` и examine все глобальные нагрузки (`$_GET`, `$_POST`, `$_FILES`)
- Session - Анализировать все переменные `$_SESSION`, если сессии активны.

Это Панель

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

И каждая панель отображает очень полезную информацию о вашем приложении!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Нажмите [здесь](https://github.com/flightphp/tracy-extensions), чтобы посмотреть код.

Установка
-------
Запустите `composer require flightphp/tracy-extensions --dev`, и вы на пути!

Конфигурация
-------
Есть очень мало конфигураций, которые вам нужно выполнить, чтобы начать. Вам нужно будет инициировать отладчик Tracy перед использованием этого [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// код загрузки
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Возможно, вам нужно будет указать вашу среду с помощью Debugger::enable(Debugger::DEVELOPMENT)

// если вы используете подключения к базе данных в вашем приложении, есть
// обязательная обертка PDO, которую нужно использовать ТОЛЬКО В РАЗРАБОТКЕ (пожалуйста, не в продакшене!)
// Она имеет те же параметры, что и обычное PDO соединение
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// или если вы прикрепляете это к фреймворку Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// теперь каждый раз, когда вы выполняете запрос, он будет захватывать время, запрос и параметры

// Это соединяет точки
if(Debugger::$showBar === true) {
	// Это должно быть ложным, иначе Tracy не сможет рендерить :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// больше кода

Flight::start();
```

## Дополнительная конфигурация

### Данные сессии
Если у вас есть собственный обработчик сессий (например, ghostff/session), вы можете передать любой массив данных сессии в Tracy, и он автоматически выведет его для вас. Вы передаете его с помощью ключа `session_data` во втором параметре конструктора `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;
// или используйте flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Это должно быть ложным, иначе Tracy не сможет рендерить :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// маршруты и другие вещи...

Flight::start();
```

### Latte

Если у вас установлен Latte в вашем проекте, вы можете использовать панель Latte для анализа ваших шаблонов. Вы можете передать экземпляр Latte в конструктор `TracyExtensionLoader` с помощью ключа `latte` во втором параметре.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// здесь вы добавляете панель Latte в Tracy
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// Это должно быть ложным, иначе Tracy не сможет рендерить :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
```