# Запросы

## Обзор

Flight инкапсулирует HTTP-запрос в один объект, к которому можно получить доступ следующим образом:

```php
$request = Flight::request();
```

## Понимание

HTTP-запросы — это один из ключевых аспектов, которые нужно понять о жизненном цикле HTTP. Пользователь выполняет действие в веб-браузере или HTTP-клиенте, и они отправляют серию заголовков, тело, URL и т.д. в ваш проект. Вы можете захватывать эти заголовки (язык браузера, тип сжатия, который они могут обрабатывать, пользовательский агент и т.д.) и захватывать тело и URL, отправляемые в ваше приложение Flight. Эти запросы необходимы для вашего приложения, чтобы понять, что делать дальше.

## Базовое использование

PHP имеет несколько суперглобальных переменных, включая `$_GET`, `$_POST`, `$_REQUEST`, `$_SERVER`, `$_FILES` и `$_COOKIE`. Flight абстрагирует их в удобные [Collections](/learn/collections). Вы можете обращаться к свойствам `query`, `data`, `cookies` и `files` как к массивам или объектам.

> **Примечание:** **СТРОГО** не рекомендуется использовать эти суперглобальные переменные в вашем проекте, и их следует ссылаться через объект `request()`.

> **Примечание:** Нет доступной абстракции для `$_ENV`.

### `$_GET`

Вы можете получить доступ к массиву `$_GET` через свойство `query`:

```php
// GET /search?keyword=something
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	// или
	$keyword = Flight::request()->query->keyword;
	echo "Вы ищете: $keyword";
	// запрос к базе данных или что-то еще с $keyword
});
```

### `$_POST`

Вы можете получить доступ к массиву `$_POST` через свойство `data`:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	// или
	$name = Flight::request()->data->name;
	$email = Flight::request()->data->email;
	echo "Вы отправили: $name, $email";
	// сохранить в базу данных или что-то еще с $name и $email
});
```

### `$_COOKIE`

Вы можете получить доступ к массиву `$_COOKIE` через свойство `cookies`:

```php
Flight::route('GET /login', function(){
	$savedLogin = Flight::request()->cookies['myLoginCookie'];
	// или
	$savedLogin = Flight::request()->cookies->myLoginCookie;
	// проверить, действительно ли сохранено, и если да, автоматически войти
	if($savedLogin) {
		Flight::redirect('/dashboard');
		return;
	}
});
```

Для помощи по установке новых значений cookie см. [overclokk/cookie](/awesome-plugins/php-cookie)

### `$_SERVER`

Доступен ярлык для доступа к массиву `$_SERVER` через метод `getVar()`:

```php

$host = Flight::request()->getVar('HTTP_HOST');
```

### `$_FILES`

Вы можете получить доступ к загруженным файлам через свойство `files`:

```php
// сырой доступ к свойству $_FILES. См. ниже рекомендуемый подход
$uploadedFile = Flight::request()->files['myFile']; 
// или
$uploadedFile = Flight::request()->files->myFile;
```

См. [Uploaded File Handler](/learn/uploaded-file) для получения дополнительной информации.

#### Обработка загрузки файлов

_v3.12.0_

Вы можете обрабатывать загрузку файлов с помощью фреймворка, используя некоторые вспомогательные методы. По сути, это сводится к извлечению данных файла из запроса и перемещению его в новое место.

```php
Flight::route('POST /upload', function(){
	// Если у вас есть поле ввода вроде <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Если у вас загружено несколько файлов, вы можете пройтись по ним в цикле:

```php
Flight::route('POST /upload', function(){
	// Если у вас есть поле ввода вроде <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Примечание по безопасности:** Всегда проверяйте и очищайте пользовательский ввод, особенно при работе с загрузкой файлов. Всегда проверяйте типы расширений, которые вы разрешаете загружать, но также проверяйте "магические байты" файла, чтобы убедиться, что это действительно тип файла, который утверждает пользователь. Есть [статьи](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [и](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [библиотеки](https://github.com/RikudouSage/MimeTypeDetector), доступные для помощи в этом.

### Тело запроса

Чтобы получить сырое тело HTTP-запроса, например, при работе с POST/PUT-запросами, вы можете сделать:

```php
Flight::route('POST /users/xml', function(){
	$xmlBody = Flight::request()->getBody();
	// сделать что-то с отправленным XML.
});
```

### JSON-тело

Если вы получаете запрос с типом содержимого `application/json` и примерами данных `{"id": 123}`, оно будет доступно из свойства `data`:

```php
$id = Flight::request()->data->id;
```

### Заголовки запроса

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

### Метод запроса

Вы можете получить доступ к методу запроса с помощью свойства `method` или метода `getMethod()`:

```php
$method = Flight::request()->method; // фактически заполняется getMethod()
$method = Flight::request()->getMethod();
```

**Примечание:** Метод `getMethod()` сначала извлекает метод из `$_SERVER['REQUEST_METHOD']`, затем он может быть перезаписан `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']`, если он существует, или `$_REQUEST['_method']`, если он существует.

## Свойства объекта запроса

Объект запроса предоставляет следующие свойства:

- **body** - Сырое тело HTTP-запроса
- **url** - Запрашиваемый URL
- **base** - Родительская поддиректория URL
- **method** - Метод запроса (GET, POST, PUT, DELETE)
- **referrer** - URL реферера
- **ip** - IP-адрес клиента
- **ajax** - Является ли запрос AJAX-запросом
- **scheme** - Протокол сервера (http, https)
- **user_agent** - Информация о браузере
- **type** - Тип содержимого
- **length** - Длина содержимого
- **query** - Параметры строки запроса
- **data** - Данные POST или JSON-данные
- **cookies** - Данные cookie
- **files** - Загруженные файлы
- **secure** - Является ли соединение защищенным
- **accept** - Параметры HTTP accept
- **proxy_ip** - IP-адрес прокси клиента. Сканирует массив `$_SERVER` на наличие `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` в этом порядке.
- **host** - Имя хоста запроса
- **servername** - SERVER_NAME из `$_SERVER`

## Вспомогательные методы для URL

Есть несколько вспомогательных методов для сборки частей URL для вашего удобства.

### Полный URL

Вы можете получить доступ к полному URL запроса с помощью метода `getFullUrl()`:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### Базовый URL

Вы можете получить доступ к базовому URL с помощью метода `getBaseUrl()`:

```php
// http://example.com/path/to/something/cool?query=yes+thanks
$url = Flight::request()->getBaseUrl();
// https://example.com
// Обратите внимание, нет завершающего слеша.
```

## Разбор запроса

Вы можете передать URL методу `parseQuery()`, чтобы разобрать строку запроса в ассоциативный массив:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```

## См. также
- [Routing](/learn/routing) - Узнайте, как сопоставлять маршруты с контроллерами и рендерить представления.
- [Responses](/learn/responses) - Как настраивать HTTP-ответы.
- [Why a Framework?](/learn/why-frameworks) - Как запросы вписываются в общую картину.
- [Collections](/learn/collections) - Работа с коллекциями данных.
- [Uploaded File Handler](/learn/uploaded-file) - Обработка загрузки файлов.

## Устранение неисправностей
- `request()->ip` и `request()->proxy_ip` могут отличаться, если ваш веб-сервер находится за прокси, балансировщиком нагрузки и т.д. 

## Журнал изменений
- v3.12.0 - Добавлена возможность обработки загрузки файлов через объект запроса.
- v1.0 - Первоначальный выпуск.