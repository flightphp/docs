# Відповіді

Flight допомагає генерувати частину заголовків відповіді для вас, але ви маєте більшу частину контролю над тим, що ви надсилаєте назад користувачеві. Іноді ви можете безпосередньо доступитися до об'єкта `Response`, але в більшості випадків ви використовуватимете екземпляр Flight для надсилання відповіді.

## Надсилання базової відповіді

Flight використовує ob_start() для буферизації виводу. Це означає, що ви можете використовувати `echo` або `print` для надсилання відповіді користувачеві, і Flight захопить її та надішле назад користувачеві з відповідними заголовками.

```php
// Це надішле "Hello, World!" до браузера користувача
Flight::route('/', function() {
	echo "Hello, World!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hello, World!
```

Як альтернативу, ви можете викликати метод `write()`, щоб додати до тіла.

```php
// Це надішле "Hello, World!" до браузера користувача
Flight::route('/', function() {
	// це більш докладно, але іноді корисно, коли це потрібно
	Flight::response()->write("Hello, World!");

	// якщо ви хочете отримати тіло, яке ви встановили на цьому етапі
	// ви можете зробити це так
	$body = Flight::response()->getBody();
});
```

## Коди статусу

Ви можете встановити код статусу відповіді, використовуючи метод `status`:

```php
Flight::route('/@id', function($id) {
	if($id == 123) {
		Flight::response()->status(200);
		echo "Hello, World!";
	} else {
		Flight::response()->status(403);
		echo "Forbidden";
	}
});
```

Якщо ви хочете отримати поточний код статусу, ви можете використовувати метод `status` без аргументів:

```php
Flight::response()->status(); // 200
```

## Встановлення тіла відповіді

Ви можете встановити тіло відповіді, використовуючи метод `write`, однак, якщо ви використаєте echo або print, це буде захоплено та надіслано як тіло відповіді через буферизацію виводу.

```php
Flight::route('/', function() {
	Flight::response()->write("Hello, World!");
});

// те саме, що і

Flight::route('/', function() {
	echo "Hello, World!";
});
```

### Очищення тіла відповіді

Якщо ви хочете очистити тіло відповіді, ви можете використовувати метод `clearBody`:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### Виконання зворотного виклику на тілі відповіді

Ви можете виконати зворотний виклик на тілі відповіді, використовуючи метод `addResponseBodyCallback`:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Це стисне gzip всі відповіді для будь-якого маршруту
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Ви можете додати кілька зворотних викликів, і вони виконуватимуться в порядку їх додавання. Оскільки це може приймати будь-який [callable](https://www.php.net/manual/en/language.types.callable.php), воно може приймати масив класу `[ $class, 'method' ]`, зачинений блок `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, або ім'я функції `'minify'`, якщо у вас є функція для мініфікації вашого коду HTML, наприклад.

**Примітка:** Зворотні виклики маршруту не працюватимуть, якщо ви використовуєте опцію конфігурації `flight.v2.output_buffering`.

### Зворотний виклик для конкретного маршруту

Якщо ви хочете, щоб це застосовувалося лише до конкретного маршруту, ви можете додати зворотний виклик у самому маршруті:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Це стисне gzip лише відповідь для цього маршруту
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### Опція проміжного програмування

Ви також можете використовувати проміжне програмування, щоб застосувати зворотний виклик до всіх маршрутів через проміжне програмування:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// Застосувати зворотний виклик тут до об'єкта response().
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// мініфікувати тіло якимось чином
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

## Встановлення заголовка відповіді

Ви можете встановити заголовок, наприклад тип вмісту відповіді, використовуючи метод `header`:

```php
// Це надішле "Hello, World!" до браузера користувача у простому тексті
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// або
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight надає підтримку для надсилання відповідей JSON і JSONP. Щоб надіслати відповідь JSON, ви передаєте деякі дані для кодування JSON:

```php
Flight::json(['id' => 123]);
```

> **Примітка:** За замовчуванням, Flight надішле заголовок `Content-Type: application/json` з відповіддю. Він також використовуватиме константи `JSON_THROW_ON_ERROR` і `JSON_UNESCAPED_SLASHES` під час кодування JSON.

### JSON з кодом статусу

Ви також можете передати код статусу як другий аргумент:

```php
Flight::json(['id' => 123], 201);
```

### JSON з форматуванням

Ви також можете передати аргумент в останню позицію, щоб увімкнути форматування:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Якщо ви змінюєте опції, передані в `Flight::json()`, і хочете простіший синтаксис, ви можете перевизначити метод JSON:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// І тепер його можна використовувати так
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON і зупинка виконання (v3.10.0)

Якщо ви хочете надіслати відповідь JSON і зупинити виконання, ви можете використовувати метод `jsonHalt()`.
Це корисно для випадків, коли ви перевіряєте, наприклад, авторизацію, і якщо користувач не авторизований, ви можете негайно надіслати відповідь JSON, очистити існуючий вміст тіла та зупинити виконання.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Перевірити, чи користувач авторизований
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
	}

	// Продовжити з рештою маршруту
});
```

До v3.10.0 ви б мали зробити щось на зразок цього:

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Перевірити, чи користувач авторизований
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// Продовжити з рештою маршруту
});
```

### JSONP

Для запитів JSONP ви можете опціонально передати ім'я параметра запиту, який ви використовуєте для визначення функції зворотного виклику:

```php
Flight::jsonp(['id' => 123], 'q');
```

Отже, при виконанні GET-запиту за допомогою `?q=my_func`, ви повинні отримати вивід:

```javascript
my_func({"id":123});
```

Якщо ви не передаєте ім'я параметра запиту, воно за замовчуванням буде `jsonp`.

## Перенаправлення на інший URL

Ви можете перенаправити поточний запит, використовуючи метод `redirect()` і передаючи новий URL:

```php
Flight::redirect('/new/location');
```

За замовчуванням Flight надсилає код статусу HTTP 303 ("See Other"). Ви можете опціонально встановити власний код:

```php
Flight::redirect('/new/location', 401);
```

## Зупинка

Ви можете зупинити фреймворк в будь-який момент, викликавши метод `halt`:

```php
Flight::halt();
```

Ви також можете вказати опціональний код статусу HTTP і повідомлення:

```php
Flight::halt(200, 'Be right back...');
```

Виклик `halt` відкине будь-який вміст відповіді до цього моменту. Якщо ви хочете зупинити фреймворк і вивести поточну відповідь, використовуйте метод `stop`:

```php
Flight::stop($httpStatusCode = null);
```

> **Примітка:** `Flight::stop()` має деякі дивні поведінки, такі як виведення відповіді, але продовження виконання вашого скрипту. Ви можете використовувати `exit` або `return` після виклику `Flight::stop()`, щоб запобігти подальшому виконанню, але загалом рекомендується використовувати `Flight::halt()`. 

## Очищення даних відповіді

Ви можете очистити тіло відповіді та заголовки, використовуючи метод `clear()`. Це очистить будь-які заголовки, призначені відповіді, очистить тіло відповіді та встановить код статусу на `200`.

```php
Flight::response()->clear();
```

### Очищення лише тіла відповіді

Якщо ви хочете очистити лише тіло відповіді, ви можете використовувати метод `clearBody()`:

```php
// Це все ще збереже будь-які заголовки, встановлені на об'єкті response().
Flight::response()->clearBody();
```

## Кешування HTTP

Flight надає вбудовану підтримку кешування на рівні HTTP. Якщо умова кешування виконана, Flight поверне відповідь HTTP `304 Not Modified`. Наступного разу, коли клієнт запитає той самий ресурс, йому буде запропоновано використовувати локально закешовану версію.

### Кешування на рівні маршруту

Якщо ви хочете закешувати всю вашу відповідь, ви можете використовувати метод `cache()` і передати час кешування.

```php
// Це закешеує відповідь на 5 хвилин
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'This content will be cached.';
});

// Альтернативно, ви можете використовувати рядок, який ви б передали
// методу strtotime()
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'This content will be cached.';
});
```

### Last-Modified

Ви можете використовувати метод `lastModified` і передати мітку часу UNIX, щоб встановити дату та час, коли сторінка була востаннє змінена. Клієнт продовжуватиме використовувати свій кеш, поки значення останньої зміни не зміниться.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'This content will be cached.';
});
```

### ETag

Кешування `ETag` подібне до `Last-Modified`, за винятком того, що ви можете вказати будь-який ідентифікатор, який ви хочете для ресурсу:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'This content will be cached.';
});
```

Зверніть увагу, що виклик `lastModified` або `etag` встановить і перевірить значення кешу. Якщо значення кешу однакове між запитами, Flight негайно надішле відповідь `HTTP 304` і зупинити обробку.

## Завантаження файлу (v3.12.0)

Існує допоміжний метод для завантаження файлу. Ви можете використовувати метод `download` і передати шлях.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```