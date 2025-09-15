# Ответы

Flight помогает генерировать часть заголовков ответа для вас, но вы контролируете большую часть того, что отправляете пользователю. Иногда вы можете напрямую обращаться к объекту `Response`, но в большинстве случаев вы будете использовать экземпляр Flight для отправки ответа.

## Отправка базового ответа

Flight использует ob_start() для буферизации вывода. Это означает, что вы можете использовать `echo` или `print` для отправки ответа пользователю, и Flight захватит его и отправит обратно с соответствующими заголовками.

```php
// Это отправит "Hello, World!" в браузер пользователя
Flight::route('/', function() {
	echo "Hello, World!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hello, World!
```

В качестве альтернативы вы можете вызвать метод `write()` для добавления в тело ответа.

```php
// Это отправит "Hello, World!" в браузер пользователя
Flight::route('/', function() {
	// подробный, но иногда полезный, когда это нужно
	Flight::response()->write("Hello, World!");

	// если вы хотите получить тело, которое вы установили на этом этапе
	// вы можете сделать это так
	$body = Flight::response()->getBody();
});
```

## Коды состояния

Вы можете установить код состояния ответа, используя метод `status`:

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

Если вы хотите получить текущий код состояния, вы можете использовать метод `status` без аргументов:

```php
Flight::response()->status(); // 200
```

## Установка тела ответа

Вы можете установить тело ответа, используя метод `write`, однако, если вы используете echo или print, 
это будет захвачено и отправлено как тело ответа через буферизацию вывода.

```php
Flight::route('/', function() {
	Flight::response()->write("Hello, World!");
});

// то же самое, что

Flight::route('/', function() {
	echo "Hello, World!";
});
```

### Очистка тела ответа

Если вы хотите очистить тело ответа, вы можете использовать метод `clearBody`:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### Выполнение обратного вызова для тела ответа

Вы можете выполнить обратный вызов для тела ответа, используя метод `addResponseBodyCallback`:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Это сжмет gzip все ответы для любого маршрута
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Вы можете добавить несколько обратных вызовов, и они будут выполняться в порядке добавления. Поскольку это может принимать любой [callable](https://www.php.net/manual/en/language.types.callable.php), оно может принимать массив класса `[ $class, 'method' ]`, замыкание `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, или имя функции `'minify'`, если у вас есть функция для минимизации вашего html-кода, например.

**Примечание:** Обратные вызовы маршрутов не будут работать, если вы используете опцию конфигурации `flight.v2.output_buffering`.

### Обратный вызов для конкретного маршрута

Если вы хотите, чтобы это применялось только к конкретному маршруту, вы можете добавить обратный вызов в самом маршруте:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Это сжмет gzip только ответ для этого маршрута
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### Вариант с посредником

Вы также можете использовать посредник, чтобы применить обратный вызов ко всем маршрутам через посредник:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// Примените обратный вызов здесь к объекту response().
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// минимизируйте тело каким-то образом
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

## Установка заголовка ответа

Вы можете установить заголовок, такой как тип содержимого ответа, используя метод `header`:

```php
// Это отправит "Hello, World!" в браузер пользователя в виде простого текста
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// или
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight предоставляет поддержку для отправки ответов JSON и JSONP. Чтобы отправить ответ JSON, вы
передаете некоторые данные для кодирования в JSON:

```php
Flight::json(['id' => 123]);
```

> **Примечание:** По умолчанию Flight отправит заголовок `Content-Type: application/json` с ответом. Он также будет использовать константы `JSON_THROW_ON_ERROR` и `JSON_UNESCAPED_SLASHES` при кодировании JSON.

### JSON с кодом состояния

Вы также можете передать код состояния в качестве второго аргумента:

```php
Flight::json(['id' => 123], 201);
```

### JSON с красивым выводом

Вы также можете передать аргумент в последнюю позицию, чтобы включить красивый вывод:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Если вы изменяете опции, передаваемые в `Flight::json()`, и хотите более простой синтаксис, вы можете 
просто переопределить метод JSON:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// И теперь его можно использовать так
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON и остановка выполнения (v3.10.0)

Если вы хотите отправить ответ JSON и остановить выполнение, вы можете использовать метод `jsonHalt()`.
Это полезно для случаев, когда вы проверяете, например, авторизацию, и если пользователь не авторизован, вы можете отправить ответ JSON немедленно, очистить существующее содержимое тела и остановить выполнение.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Проверьте, авторизован ли пользователь
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
	}

	// Продолжите с остальной частью маршрута
});
```

До v3.10.0 вы бы делали что-то вроде этого:

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Проверьте, авторизован ли пользователь
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// Продолжите с остальной частью маршрута
});
```

### JSONP

Для запросов JSONP вы можете опционально передать имя параметра запроса, которое вы используете для определения функции обратного вызова:

```php
Flight::jsonp(['id' => 123], 'q');
```

Итак, при выполнении GET-запроса с использованием `?q=my_func`, вы должны получить вывод:

```javascript
my_func({"id":123});
```

Если вы не передадите имя параметра запроса, оно будет по умолчанию `jsonp`.

## Перенаправление на другой URL

Вы можете перенаправить текущий запрос, используя метод `redirect()` и передавая
новый URL:

```php
Flight::redirect('/new/location');
```

По умолчанию Flight отправляет код состояния HTTP 303 ("See Other"). Вы можете опционально установить
пользовательский код:

```php
Flight::redirect('/new/location', 401);
```

## Остановка

Вы можете остановить фреймворк в любой момент, вызвав метод `halt`:

```php
Flight::halt();
```

Вы также можете указать опциональный код состояния `HTTP` и сообщение:

```php
Flight::halt(200, 'Be right back...');
```

Вызов `halt` отбросит любое содержимое ответа на этом этапе. Если вы хотите остановить
фреймворк и вывести текущий ответ, используйте метод `stop`:

```php
Flight::stop($httpStatusCode = null);
```

> **Примечание:** `Flight::stop()` имеет некоторое странное поведение, такое как оно выведет ответ, но продолжит выполнение вашего скрипта. Вы можете использовать `exit` или `return` после вызова `Flight::stop()`, чтобы предотвратить дальнейшее выполнение, но в общем рекомендуется использовать `Flight::halt()`. 

## Очистка данных ответа

Вы можете очистить тело ответа и заголовки, используя метод `clear()`. Это очистит
любые заголовки, назначенные ответу, очистит тело ответа и установит код состояния на `200`.

```php
Flight::response()->clear();
```

### Очистка только тела ответа

Если вы хотите очистить только тело ответа, вы можете использовать метод `clearBody()`:

```php
// Это сохранит любые заголовки, установленные на объекте response().
Flight::response()->clearBody();
```

## Кэширование HTTP

Flight предоставляет встроенную поддержку кэширования на уровне HTTP. Если условие кэширования
выполнено, Flight вернет ответ HTTP `304 Not Modified`. В следующий раз, когда клиент
запросит тот же ресурс, ему будет предложено использовать локально кэшированную версию.

### Кэширование на уровне маршрута

Если вы хотите кэшировать весь ответ, вы можете использовать метод `cache()` и передать время кэширования.

```php
// Это кэширует ответ на 5 минут
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'This content will be cached.';
});

// В качестве альтернативы, вы можете использовать строку, которую вы бы передали
// методу strtotime()
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'This content will be cached.';
});
```

### Last-Modified

Вы можете использовать метод `lastModified` и передать UNIX-временную метку, чтобы установить дату
и время, когда страница была последним образом изменена. Клиент будет продолжать использовать свой кэш, пока
значение последнего изменения не изменится.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'This content will be cached.';
});
```

### ETag

Кэширование `ETag` похоже на `Last-Modified`, кроме того, что вы можете указать любой идентификатор, который
вы хотите для ресурса:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'This content will be cached.';
});
```

Помните, что вызов либо `lastModified`, либо `etag` установит и проверит значение
кэша. Если значение кэша одинаково между запросами, Flight немедленно
отправит ответ `HTTP 304` и остановит обработку.

## Скачивание файла (v3.12.0)

Есть вспомогательный метод для скачивания файла. Вы можете использовать метод `download` и передать путь.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```