# Міграція до v3

Зворотна сумісність у більшості випадків збережена, але є деякі зміни, про які ви повинні знати під час міграції з v2 до v3. Є деякі зміни, які надто суперечили шаблонам проєктування, тому довелося внести корективи.

## Поведінка буферизації виводу

_v3.5.0_

[Буферизація виводу](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) — це процес, коли вивід, згенерований PHP-скриптом, зберігається в буфері (внутрішньому для PHP) перед відправкою клієнту. Це дозволяє модифікувати вивід перед його відправкою клієнту.

У MVC-додатку Контролер є "менеджером" і керує тим, що робить подання. Генерація виводу поза контролером (або в випадку Flight іноді анонімною функцією) порушує шаблон MVC. Ця зміна спрямована на більшу відповідність шаблону MVC і робить фреймворк більш передбачуваним та легшим у використанні.

У v2 буферизація виводу оброблялася таким чином, що вона не послідовно закривала власний буфер виводу, що ускладнювало [юніт-тестування](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) 
та [стримінг](https://github.com/flightphp/core/issues/413). Для більшості користувачів ця зміна може не вплинути на вас. Однак, якщо ви виводите вміст поза викликами функцій та контролерами (наприклад, у хуку), ви, ймовірно, зіткнетеся з проблемами. Виведення вмісту в хуках та перед фактичним виконанням фреймворку могло працювати раніше, але надалі не працюватиме.

### Де ви можете зіткнутися з проблемами
```php
// index.php
require 'vendor/autoload.php';

// just an example
define('START_TIME', microtime(true));

function hello() {
	echo 'Hello World';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// this will actually be fine
	echo '<p>This Hello World phrase was brought to you by the letter "H"</p>';
});

Flight::before('start', function(){
	// things like this will cause an error
	echo '<html><head><title>My Page</title></head><body>';
});

Flight::route('/', function(){
	// this is actually just fine
	echo 'Hello World';

	// This should be just fine as well
	Flight::hello();
});

Flight::after('start', function(){
	// this will cause an error
	echo '<div>Your page loaded in '.(microtime(true) - START_TIME).' seconds</div></body></html>';
});
```

### Увімкнення поведінки рендерингу v2

Чи можете ви залишити старий код як є без переписування для сумісності з v3? Так, можете! Ви можете увімкнути поведінку рендерингу v2, встановивши опцію конфігурації `flight.v2.output_buffering` на `true`. Це дозволить вам продовжувати використовувати стару поведінку рендерингу, але рекомендується виправити це надалі. У v4 фреймворку це буде видалено.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// Now this will be just fine
	echo '<html><head><title>My Page</title></head><body>';
});

// more code 
```

## Зміни в Dispatcher

_v3.7.0_

Якщо ви безпосередньо викликали статичні методи для `Dispatcher`, такі як `Dispatcher::invokeMethod()`, `Dispatcher::execute()` тощо, вам потрібно оновити код, щоб не викликати ці методи безпосередньо. `Dispatcher` було перетворено на більш об'єктно-орієнтований, щоб полегшити використання контейнерів ін'єкції залежностей. Якщо вам потрібно викликати метод подібно до того, як це робив Dispatcher, ви можете вручну використовувати щось на кшталт `$result = $class->$method(...$params);` або `call_user_func_array()`.

## Зміни в `halt()` `stop()` `redirect()` та `error()`

_v3.10.0_

Поведінка за замовчуванням до 3.10.0 полягала в очищенні як заголовків, так і тіла відповіді. Це було змінено на очищення лише тіла відповіді. Якщо вам потрібно також очистити заголовки, ви можете використовувати `Flight::response()->clear()`.