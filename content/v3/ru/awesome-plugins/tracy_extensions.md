Расширения панели Tracy Flight
=====

Это набор расширений, чтобы сделать работу с Flight немного богаче.

- Flight - Анализировать все переменные Flight.
- Database - Анализировать все запросы, которые были выполнены на странице (если вы правильно инициализировали соединение с базой данных).
- Request - Анализировать все переменные `$_SERVER` и осматривать все глобальные полезные нагрузки (`$_GET`, `$_POST`, `$_FILES`).
- Session - Анализировать все переменные `$_SESSION`, если сессии активны.

Это панель

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

И каждая панель отображает очень полезную информацию о вашем приложении!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Нажмите [здесь](https://github.com/flightphp/tracy-extensions), чтобы просмотреть код.

Установка
-------
Выполните `composer require flightphp/tracy-extensions --dev`, и вы готовы!

Конфигурация
-------
Вам нужно очень мало настроек, чтобы начать. Вам потребуется инициализировать отладчик Tracy до использования этого [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// код загрузки
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Возможно, вам нужно указать вашу среду с Debugger::enable(Debugger::DEVELOPMENT)

// если вы используете соединения с базой данных в вашем приложении, требуется
// обертка PDO для использования ТОЛЬКО В РАЗРАБОТКЕ (не в производстве, пожалуйста!)
// Она имеет те же параметры, что и обычное соединение PDO
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// или если вы прикрепляете это к фреймворку Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// теперь при каждом запросе он будет захватывать время, запрос и параметры

// Это соединяет точки
if(Debugger::$showBar === true) {
	// Это нужно установить в false, иначе Tracy не сможет отобразить
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// дополнительный код

Flight::start();
```

## Дополнительная конфигурация

### Данные сессии
Если у вас есть пользовательский обработчик сессий (например, ghostff/session), вы можете передать любой массив данных сессии в Tracy, и он автоматически выведет его. Вы передаете это с ключом `session_data` во втором параметре конструктора `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;
// или используйте flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Это нужно установить в false, иначе Tracy не сможет отобразить
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// маршруты и другие вещи...

Flight::start();
```

### Latte

Если у вас установлен Latte в проекте, вы можете использовать панель Latte для анализа шаблонов. Вы можете передать экземпляр Latte в конструктор `TracyExtensionLoader` с ключем `latte` во втором параметре.

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
	// Это нужно установить в false, иначе Tracy не сможет отобразить
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
```