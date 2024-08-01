# Запросы

Flight инкапсулирует HTTP запрос в один объект, который можно
получить следующим образом:

```php
$request = Flight::request();
```

## Типичные сценарии использования

Когда вы работаете с запросом в веб-приложении, обычно вам нужно
извлечь заголовок, параметр `$_GET` или `$_POST`, а также,
возможно, необработанное тело запроса. Flight предоставляет простой
интерфейс для выполнения всех этих задач.

Вот пример извлечения параметра строки запроса:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "Вы ищете: $keyword";
	// выполнение запроса к базе данных или чего-то ещё с использованием $keyword
});
```

Вот пример, возможно, формы с методом POST:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "Вы отправили: $name, $email";
	// сохранение в базу данных или чего-то ещё с использованием $name и $email
});
```

## Свойства объекта запроса

Объект запроса предоставляет следующие свойства:

- **body** - Необработанное тело HTTP запроса
- **url** - Запрашиваемый URL
- **base** - Базовый подкаталог URL
- **method** - Метод запроса (GET, POST, PUT, DELETE)
- **referrer** - URL-адрес источника
- **ip** - IP-адрес клиента
- **ajax** - Является ли запрос запросом AJAX
- **scheme** - Протокол сервера (http, https)
- **user_agent** - Информация о браузере
- **type** - Тип содержимого
- **length** - Длина содержимого
- **query** - Параметры строки запроса
- **data** - Данные POST или JSON
- **cookies** - Данные cookie
- **files** - Загруженные файлы
- **secure** - Является ли соединение защищенным
- **accept** - Параметры принятия HTTP
- **proxy_ip** - IP-адрес прокси-сервера клиента. Сканирует массив `$_SERVER` на наличие `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` в данном порядке.
- **host** - Имя хоста запроса

Можно получить доступ к свойствам `query`, `data`, `cookies` и `files`
как к массивам или объектам.

Таким образом, для получения параметра строки запроса можно использовать:

```php
$id = Flight::request()->query['id'];
```

Или можно использовать:

```php
$id = Flight::request()->query->id;
```

## Необработанное тело запроса

Для получения необработанного тела HTTP запроса, например, при работе с запросами PUT,
можно сделать следующее:

```php
$body = Flight::request()->getBody();
```

## Ввод JSON

Если вы отправляете запрос с типом `application/json` и данными `{"id": 123}`,
он будет доступен из свойства `data`:

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

Доступен сокращенный способ доступа к массиву `$_SERVER` с помощью метода `getVar()`:

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## Загруженные файлы через `$_FILES`

Вы можете получить доступ к загруженным файлам через свойство `files`:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

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

Вы можете получить доступ к необработанному телу запроса с помощью метода `getBody()`:

```php
$body = Flight::request()->getBody();
```

## Метод запроса

Вы можете получить доступ к методу запроса через свойство `method` или метод `getMethod()`:

```php
$method = Flight::request()->method; // фактически вызывает getMethod()
$method = Flight::request()->getMethod();
```

**Примечание:** Метод `getMethod()` сначала извлекает метод из `$_SERVER['REQUEST_METHOD']`, затем его можно перезаписать
с помощью `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']`, если он существует, или `$_REQUEST['_method']`, если он существует.

## URL запроса

Есть несколько вспомогательных методов для объединения частей URL для вашего удобства.

### Полный URL

Вы можете получить доступ к полному URL запроса с помощью метода `getFullUrl()`:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### Базовый URL

Вы можете получить доступ к базовому URL с помощью метода `getBaseUrl()`:

```php
$url = Flight::request()->getBaseUrl();
// Обратите внимание, без завершающего слэша.
// https://example.com
```

## Разбор запроса

Можно передать URL методу `parseQuery()` для разбора строки запроса в ассоциативный массив:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```