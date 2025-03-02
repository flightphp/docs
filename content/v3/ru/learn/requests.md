# Запросы

Flight инкапсулирует HTTP-запрос в единый объект, к которому можно получить доступ следующим образом:

```php
$request = Flight::request();
```

## Типичные Сценарии Использования

Когда вы работаете с запросом в веб-приложении, обычно вы хотите извлечь заголовок или параметр `$_GET` или `$_POST`, или, возможно, даже сырое тело запроса. Flight предоставляет простой интерфейс для выполнения всех этих действий.

Вот пример получения параметра строки запроса:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "Вы ищете: $keyword";
	// запрашиваем базу данных или что-то еще с $keyword
});
```

Вот пример формы с методом POST:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "Вы отправили: $name, $email";
	// сохранить в базе данных или что-то еще с $name и $email
});
```

## Свойства Объекта Запроса

Объект запроса предоставляет следующие свойства:

- **body** - Сырое тело HTTP-запроса
- **url** - Запрашиваемый URL
- **base** - Родительский подкаталог URL
- **method** - Метод запроса (GET, POST, PUT, DELETE)
- **referrer** - URL-адрес реферера
- **ip** - IP-адрес клиента
- **ajax** - Является ли запрос AJAX-запросом
- **scheme** - Протокол сервера (http, https)
- **user_agent** - Информация о браузере
- **type** - Тип содержимого
- **length** - Длина содержимого
- **query** - Параметры строки запроса
- **data** - Пост-данные или JSON-данные
- **cookies** - Данные cookie
- **files** - Загруз файлы
- **secure** - Является ли соединение безопасным
- **accept** - Параметры принятия HTTP
- **proxy_ip** - IP-адрес прокси-клиента. Сканирует массив `$_SERVER` на наличие `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` в указанном порядке.
- **host** - Имя хоста запроса

Вы можете получить доступ к свойствам `query`, `data`, `cookies` и `files` как к массивам или объектам.

Итак, чтобы получить параметр строки запроса, вы можете сделать:

```php
$id = Flight::request()->query['id'];
```

Или вы можете сделать:

```php
$id = Flight::request()->query->id;
```

## СЫРОЕ Тело Запроса

Чтобы получить сырое тело HTTP-запроса, например, при работе с запросами PUT, вы можете сделать:

```php
$body = Flight::request()->getBody();
```

## JSON Входные Данные

Если вы отправляете запрос с типом `application/json` и данными `{"id": 123}`, они будут доступны через свойство `data`:

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

Существует удобный способ доступа к массиву `$_SERVER` через метод `getVar()`:

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## Доступ к Загруженным Файлам через `$_FILES`

Вы можете получить доступ к загруженным файлам через свойство `files`:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## Обработка Загрузки Файлов

Вы можете обрабатывать загрузку файлов с помощью фреймворка с помощью некоторых вспомогательных методов. В основном это сводится к извлечению данных файла из запроса и перемещению их в новое местоположение.

```php
Flight::route('POST /upload', function(){
	// Если у вас было поле ввода, такое как <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Если вы загрузили несколько файлов, вы можете пройтись по ним в цикле:

```php
Flight::route('POST /upload', function(){
	// Если у вас было поле ввода, такое как <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Заметка по Безопасности:** Всегда валидируйте и очищайте ввод пользователя, особенно при работе с загрузками файлов. Всегда валидируйте типы расширений, которые вы разрешите загружать, но вы также должны валидировать "магические байты" файла, чтобы убедиться, что это действительно тот тип файла, который пользователь заявляет, что это. Существуют [статьи](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [и](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [библиотеки](https://github.com/RikudouSage/MimeTypeDetector) для помощи в этом.

## Заголовки Запроса

Вы можете получить доступ к заголовкам запроса с помощью метода `getHeader()` или `getHeaders()`:

```php

// Возможно, вам нужен заголовок авторизации
$host = Flight::request()->getHeader('Authorization');
// или
$host = Flight::request()->header('Authorization');

// Если вам нужно получить все заголовки
$headers = Flight::request()->getHeaders();
// или
$headers = Flight::request()->headers();
```

## Тело Запроса

Вы можете получить доступ к сырому телу запроса, используя метод `getBody()`:

```php
$body = Flight::request()->getBody();
```

## Метод Запроса

Вы можете получить доступ к методу запроса, используя свойство `method` или метод `getMethod()`:

```php
$method = Flight::request()->method; // фактически вызывает getMethod()
$method = Flight::request()->getMethod();
```

**Примечание:** Метод `getMethod()` сначала извлекает метод из `$_SERVER['REQUEST_METHOD']`, затем он может быть переопределен 
с помощью `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']`, если он существует, или `$_REQUEST['_method']`, если он существует.

## URL Запросов

Существуют несколько вспомогательных методов для сборки частей URL для вашего удобства.

### Полный URL

Вы можете получить доступ к полному URL запроса, используя метод `getFullUrl()`:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### Базовый URL

Вы можете получить доступ к базовому URL, используя метод `getBaseUrl()`:

```php
$url = Flight::request()->getBaseUrl();
// Обратите внимание, нет конечного слэша.
// https://example.com
```

## Парсинг Запросов

Вы можете передать URL в метод `parseQuery()`, чтобы разобрать строку запроса в ассоциативный массив:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```