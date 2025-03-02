# Tracy

Tracy — це чудовий обробник помилок, який можна використовувати з Flight. Він має кілька панелей, які можуть допомогти вам налагодити вашу програму. Також його дуже легко розширити та додати свої панелі. Команда Flight створила кілька панелей спеціально для проектів Flight за допомогою плагіна [flightphp/tracy-extensions](https://github.com/flightphp/tracy-extensions).

## Встановлення

Встановіть з Composer. І вам насправді захочеться встановити це без версії для розробників, оскільки Tracy постачається з компонентом обробки помилок для виробництва.

```bash
composer require tracy/tracy
```

## Основна конфігурація

Є кілька основних параметрів конфігурації, щоб почати. Ви можете прочитати більше про них у [Документації Tracy](https://tracy.nette.org/en/configuring).

```php

require 'vendor/autoload.php';

use Tracy\Debugger;

// Увімкніть Tracy
Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT) // іноді вам потрібно бути явним (також Debugger::PRODUCTION)
// Debugger::enable('23.75.345.200'); // ви також можете надати масив IP-адрес

// Тут будуть записані помилки та виключення. Переконайтеся, що цей каталог існує і має право на запис.
Debugger::$logDirectory = __DIR__ . '/../log/';
Debugger::$strictMode = true; // показувати всі помилки
// Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // всі помилки, крім застарілих повідомлень
if (Debugger::$showBar) {
    $app->set('flight.content_length', false); // якщо панель Debugger видима, то довжину вмісту не можна встановити Flight

	// Це специфічно для розширення Tracy для Flight, якщо ви це включили
	// інакше прокоментуйте це.
	new TracyExtensionLoader($app);
}
```

## Корисні поради

Коли ви налагоджуєте свій код, є кілька дуже корисних функцій для виводу даних для вас.

- `bdump($var)` - Це відобразить змінну в панелі Tracy в окремій секції.
- `dumpe($var)` - Це відобразить змінну, а потім відразу зупинить виконання.