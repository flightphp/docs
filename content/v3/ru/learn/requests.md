# Запросы

Flight инкапсулирует HTTP-запрос в один объект, который можно получить следующим образом:

```php
$request = Flight::request();
```

## Типичные сценарии использования

При работе с запросом в веб-приложении обычно вам захочется извлечь заголовок, параметр из `$_GET` или `$_POST`, или, возможно, сырой тело запроса. Flight предоставляет простой интерфейс для выполнения всех этих действий.

Вот пример получения параметра строки запроса:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "You are searching for: $keyword";
	// запрос к базе данных или что-то другое с $keyword
});
```

Вот пример, возможно, формы с методом POST:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "You submitted: $name, $email";
	// сохранение в базу данных или что-то другое с $name и $email
});
```

## Свойства объекта запроса

Объект запроса предоставляет следующие свойства:

- **body** - Сырой HTTP-тело запроса
- **url** - URL, который запрашивается
- **base** - Родительский подкаталог URL
- **method** - Метод запроса (GET, POST, PUT, DELETE)
- **referrer** - URL-ссылка
- **ip** - IP-адрес клиента
- **ajax** - Является ли запрос AJAX-запросом
- **scheme** - Протокол сервера (http, https)
- **user_agent** - Информация о браузере
- **type** - Тип содержимого
- **length** - Длина содержимого
- **query** - Параметры строки запроса
- **data** - Данные POST или JSON
- **cookies** - Данные cookie
- **files** - Загруженные файлы
- **secure** - Является ли соединение безопасным
- **accept** - Параметры HTTP accept
- **proxy_ip** - IP-адрес прокси клиента. Сканирует массив `$_SERVER` для `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` в этом порядке.
- **host** - Имя хоста запроса
- **servername** - SERVER_NAME из `$_SERVER`

Вы можете обращаться к свойствам `query`, `data`, `cookies` и `files` как к массивам или объектам.

Итак, чтобы получить параметр строки запроса, вы можете сделать:

```php
$id = Flight::request()->query['id'];
```

Или вы можете сделать:

```php
$id = Flight::request()->query->id;
```

## Сырое тело запроса

Чтобы получить сырое HTTP-тело запроса, например, при работе с запросами PUT, вы можете сделать:

```php
$body = Flight::request()->getBody();
```

## JSON-вход

Если вы отправляете запрос с типом `application/json` и данными `{"id": 123}`, он будет доступен из свойства `data`:

```php
$id = Flight::request()->data->id;
```

## `$_GET`

Вы можете получить доступ к массиву `$_GET` через свойство `query`:

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

Вы можете получить доступ к массиву `$_POST` через свойство `data`:

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

Вы можете получить доступ к массиву `$_COOKIE` через свойство `cookies`:

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

Доступен ярлык для доступа к массиву `$_SERVER` через метод `getVar()`:

```php
$host = Flight::request()->getVar('HTTP_HOST');
```

## Доступ к загруженным файлам через `$_FILES`

Вы можете получить доступ к загруженным файлам через свойство `files`:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## Обработка загрузки файлов (v3.12.0)

Вы можете обработать загрузку файлов с помощью фреймворка с помощью вспомогательных методов. Это в основном сводится к извлечению данных файла из запроса и перемещению его в новое место.

```php
Flight::route('POST /upload', function(){
	// Если у вас было поле ввода вроде <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Если у вас загружено несколько файлов, вы можете перебирать их:

```php
Flight::route('POST /upload', function(){
	// Если у вас было поле ввода вроде <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Примечание по безопасности:** Всегда проверяйте и очищайте пользовательский ввод, особенно при работе с загрузкой файлов. Всегда проверяйте типы расширений, которые вы разрешите загружать, но также проверяйте "магические байты" файла, чтобы убедиться, что это именно тот тип файла, который пользователь указал. Есть [статьи](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [и](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [библиотеки](https://github.com/RikudouSage/MimeTypeDetector), которые помогут с этим.

## Заголовки запроса

Вы можете получить доступ к заголовкам запроса с помощью метода `getHeader()` или `getHeaders()`:

```php
// Возможно, вам нужен заголовок Authorization
$host = Flight::request()->getHeader('Authorization');
// или
$host = Flight::request()->header('Authorization');

// Если вам нужно получить все заголовки
$headers = Flight::request()->getHeaders();
// или
$headers = Flight::request()->headers();
```

## Тело запроса

Вы можете получить доступ к сыром телу запроса с помощью метода `getBody()`:

```php
$body = Flight::request()->getBody();
```

## Метод запроса

Вы можете получить доступ к методу запроса с помощью свойства `method` или метода `getMethod()`:

```php
$method = Flight::request()->method; // фактически вызывает getMethod()
$method = Flight::request()->getMethod();
```

**Примечание:** Метод `getMethod()` сначала извлекает метод из `$_SERVER['REQUEST_METHOD']`, затем он может быть перезаписан `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']`, если он существует, или `$_REQUEST['_method']`, если он существует.

## URL запроса

Есть несколько вспомогательных методов для сборки частей URL для вашего удобства.

### Полный URL

Вы можете получить полный URL запроса с помощью метода `getFullUrl()`:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```

### Базовый URL

Вы можете получить базовый URL с помощью метода `getBaseUrl()`:

```php
$url = Flight::request()->getBaseUrl();
// Обратите внимание, без конечного слеша.
// https://example.com
```

## Разбор строки запроса

Вы можете передать URL методу `parseQuery()`, чтобы разобрать строку запроса в ассоциативный массив:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```