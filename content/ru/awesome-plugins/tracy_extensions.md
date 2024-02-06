Tracy Flight Панель Расширений
=====

Это набор расширений, сделанных для более удобной работы с Flight.

- Flight - Анализировать все переменные Flight.
- Database - Анализировать все запросы, выполненные на странице (если вы правильно инициируете соединение с базой данных)
- Request - Анализировать все переменные `$_SERVER` и изучить все глобальные нагрузки (`$_GET`, `$_POST`, `$_FILES`)
- Session - Анализировать все переменные `$_SESSION`, если сеансы активны.

Это Панель

![Панель Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

И каждая панель отображает очень полезную информацию о вашем приложении!

![Данные Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![База данных Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Запрос Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Установка
-------
Запустите `composer require flightphp/tracy-extensions --dev` и вы в пути!

Конфигурация
-------
Вам нужно выполнить очень небольшую настройку, чтобы начать работу с этим. Вам нужно инициировать отладчик Tracy перед использованием этого [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// код запуска
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Возможно, вам нужно указать вашу среду с помощью Debugger::enable(Debugger::DEVELOPMENT)

// если вы используете соединения с базой данных в вашем приложении, есть 
// необходимая обертка PDO для использования ТОЛЬКО В РАЗРАБОТКЕ (пожалуйста, не в продакшене!)
// Она имеет те же параметры, что и обычное соединение PDO
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// или если вы присоединяете это к фреймворку Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// теперь при каждом запросе будет записано время, запрос и параметры

// Это соединяет точки
if(Debugger::$showBar === true) {
	new TracyExtensionLoader(Flight::app());
}

// еще код

Flight::start();
``` 