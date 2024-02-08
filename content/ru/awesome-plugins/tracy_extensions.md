Tracy Flight Panel Extensions
=====

Это набор расширений, который делает работу с Flight немного более продуктивной.

- Flight - Анализ всех переменных Flight.
- Database - Анализ всех запросов, которые были выполнены на странице (если вы правильно инициировали соединение с базой данных)
- Request - Анализ всех переменных `$_SERVER` и изучение всех глобальных нагрузок (`$_GET`, `$_POST`, `$_FILES`)
- Session - Анализ всех переменных `$_SESSION`, если сеансы активны.

Это Панель

![Полоса Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

И каждая панель отображает очень полезную информацию о вашем приложении!

![Данные Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![База данных Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Запрос Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Установка
-------
Запустите `composer require flightphp/tracy-extensions --dev` и начинайте!

Настройка
-------
Вам нужно сделать очень мало настроек, чтобы начать использовать это. Вам нужно инициировать отладчик Tracy перед использованием этого [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// код инициализации
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Возможно, вам нужно указать ваше окружение с помощью Debugger::enable(Debugger::DEVELOPMENT)

// если вы используете соединения с базой данных в своем приложении, там есть 
// обязательная оболочка PDO для использования ТОЛЬКО В РАЗРАБОТКЕ (пожалуйста, не используйте в продакшене!)
// Она имеет те же параметры, что и обычное соединение с PDO
$pdo = new PdoQueryCapture('sqlite:test.db', 'пользователь', 'пароль');
// или если вы присоединяете это к фреймворку Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'пользователь', 'пароль']);
// теперь при выполнении запроса будет записано время, запрос и параметры

// Это соединяет точки
if(Debugger::$showBar === true) {
	new TracyExtensionLoader(Flight::app());
}

// ещё код

Flight::start();
```  