# Запросы

## Обзор

Flight инкапсулирует HTTP-запрос в один объект, к которому можно получить доступ следующим образом:

```php
$request = Flight::request();
```

## Понимание

HTTP-запросы — это один из основных аспектов, которые нужно понять о жизненном цикле HTTP. Пользователь выполняет действие в веб-браузере или HTTP-клиенте, и они отправляют серию заголовков, тело, URL и т.д. в ваш проект. Вы можете захватить эти заголовки (язык браузера, тип сжатия, который они могут обрабатывать, пользовательский агент и т.д.) и захватить тело и URL, отправляемые в ваше приложение Flight. Эти запросы необходимы для вашего приложения, чтобы понять, что делать дальше.

## Основное использование

PHP имеет несколько суперглобальных переменных, включая `$_GET`, `$_POST`, `$_REQUEST`, `$_SERVER`, `$_FILES` и `$_COOKIE`. Flight абстрагирует их в удобные [Collections](/learn/collections). Вы можете обращаться к свойствам `query`, `data`, `cookies` и `files` как к массивам или объектам.

> **Примечание:** **СТРОГО** не рекомендуется использовать эти суперглобальные переменные в вашем проекте; они должны ссылаться через объект `request()`.

> **Примечание:** Нет доступной абстракции для `$_ENV`.

### `$_GET`

Вы можете получить доступ к массиву `$_GET` через свойство `query`:

```php
// GET /search?keyword=something
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	// or
	$keyword = Flight::request()->query->keyword;
	echo "You are searching for: $keyword";
	// query a database or something else with the $keyword
});
```

### `$_POST`

Вы можете получить доступ к массиву `$_POST` через свойство `data`:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	// or
	$name = Flight::request()->data->name;
	$email = Flight::request()->data->email;
	echo "You submitted: $name, $email";
	// save to a database or something else with the $name and $email
});
```

### `$_COOKIE`

Вы можете получить доступ к массиву `$_COOKIE` через свойство `cookies`:

```php
Flight::route('GET /login', function(){
	$savedLogin = Flight::request()->cookies['myLoginCookie'];
	// or
	$savedLogin = Flight::request()->cookies->myLoginCookie;
	// check if it's really saved or not and if it is auto log them in
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
// raw access to $_FILES property. See below for recommended approach
$uploadedFile = Flight::request()->files['myFile']; 
// or
$uploadedFile = Flight::request()->files->myFile;
```

См. [Uploaded File Handler](/learn/uploaded-file) для получения дополнительной информации.

#### Обработка загрузки файлов

_v3.12.0_

Вы можете обрабатывать загрузку файлов с помощью фреймворка, используя некоторые вспомогательные методы. По сути, это сводится к извлечению данных файла из запроса и перемещению его в новое место.

```php
Flight::route('POST /upload', function(){
	// If you had an input field like <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Если у вас загружено несколько файлов, вы можете перебрать их:

```php
Flight::route('POST /upload', function(){
	// If you had an input field like <input type="file" name="myFiles[]">
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
	// do something with the XML that was sent.
});
```

### JSON-тело

Если вы получаете запрос с типом содержимого `application/json` и примером данных `{"id": 123}`, он будет доступен из свойства `data`:

```php
$id = Flight::request()->data->id;
```

### Заголовки запроса

Вы можете получить доступ к заголовкам запроса с помощью метода `getHeader()` или `getHeaders()`:

```php

// Maybe you need Authorization header
$host = Flight::request()->getHeader('Authorization');
// or
$host = Flight::request()->header('Authorization');

// If you need to grab all headers
$headers = Flight::request()->getHeaders();
// or
$headers = Flight::request()->headers();
```

### Метод запроса

Вы можете получить доступ к методу запроса с помощью свойства `method` или метода `getMethod()`:

```php
$method = Flight::request()->method; // actually populated by getMethod()
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
- **secure** - Является ли соединение безопасным
- **accept** - Параметры HTTP accept
- **proxy_ip** - IP-адрес прокси клиента. Сканирует массив `$_SERVER` на наличие `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` в этом порядке.
- **host** - Имя хоста запроса
- **servername** - SERVER_NAME из `$_SERVER`

## Вспомогательные методы

Есть несколько вспомогательных методов для сборки частей URL или работы с определенными заголовками.

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
// Notice, no trailing slash.
```

## Разбор запроса

Вы можете передать URL методу `parseQuery()`, чтобы разобрать строку запроса в ассоциативный массив:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```

## Переговоры по типам содержимого Accept

_v3.17.2_

Вы можете использовать метод `negotiateContentType()`, чтобы определить лучший тип содержимого для ответа на основе заголовка `Accept`, отправленного клиентом.

```php

// Example Accept header: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8
// The below defines what you support.
$availableTypes = ['application/json', 'application/xml'];
$typeToServe = Flight::request()->negotiateContentType($availableTypes);
if ($typeToServe === 'application/json') {
	// Serve JSON response
} elseif ($typeToServe === 'application/xml') {
	// Serve XML response
} else {
	// Default to something else or throw an error
}
```

> **Примечание:** Если ни один из доступных типов не найден в заголовке `Accept`, метод вернет `null`. Если заголовок `Accept` не определен, метод вернет первый тип в массиве `$availableTypes`.

## См. также
- [Routing](/learn/routing) - См., как сопоставлять маршруты с контроллерами и рендерить представления.
- [Responses](/learn/responses) - Как настраивать HTTP-ответы.
- [Why a Framework?](/learn/why-frameworks) - Как запросы вписываются в общую картину.
- [Collections](/learn/collections) - Работа с коллекциями данных.
- [Uploaded File Handler](/learn/uploaded-file) - Обработка загрузки файлов.

## Устранение неисправностей
- `request()->ip` и `request()->proxy_ip` могут отличаться, если ваш веб-сервер находится за прокси, балансировщиком нагрузки и т.д.

## Журнал изменений
- v3.17.2 - Добавлен negotiateContentType()
- v3.12.0 - Добавлена возможность обработки загрузки файлов через объект запроса.
- v1.0 - Первое выпущение.