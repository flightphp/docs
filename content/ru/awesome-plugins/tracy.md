# Tracy

Трейси - удивительный обработчик ошибок, который можно использовать с Flight. У него есть ряд панелей, которые могут помочь вам отлаживать ваше приложение. Он также очень легок в расширении и добавлении собственных панелей. Команда Flight создала несколько панелей специально для проектов Flight с плагином [flightphp/tracy-extensions](https://github.com/flightphp/tracy-extensions).

## Установка

Установите с помощью composer. И вам действительно захочется установить это без версии для разработчиков, так как у Трейси есть компонент обработки ошибок для продакшена.

```bash
composer require tracy/tracy
```

## Базовая конфигурация

Есть некоторые базовые параметры конфигурации, чтобы начать. Вы можете узнать больше о них в [Документации по Tracy](https://tracy.nette.org/en/configuring).

```php

require 'vendor/autoload.php';

use Tracy\Debugger;

// Включение Tracy
Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT) // иногда вам придется быть явным (также Debugger::PRODUCTION)
// Debugger::enable('23.75.345.200'); // также можно предоставить массив IP-адресов
// Здесь будут регистрироваться ошибки и исключения. Убедитесь, что этот каталог существует и доступен для записи.
Debugger::$logDirectory = __DIR__ . '/../log/';
Debugger::$strictMode = true; // показывать все ошибки
// Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // все ошибки, кроме устаревших уведомлений
if (Debugger::$showBar) {
    $app->set('flight.content_length', false); // если панель отладки видима, тогда длина содержимого не может быть установлена Flight

	// Это специфично для Расширения Трейси для Flight, если вы его включили
	// в противном случае закомментируйте это.
	new TracyExtensionLoader($app);
}
```

## Полезные советы

Когда вы отлаживаете свой код, есть несколько очень полезных функций для вывода данных для вас.

- `bdump($var)` - Это выведет переменную на панель Трейси в отдельной панели.
- `dumpe($var)` - Это выведет переменную, а затем немедленно завершит работу.