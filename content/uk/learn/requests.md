# Запити

Flight інкапсулює HTTP запит в один об'єкт, до якого можна
доступитися, виконавши:

```php
$request = Flight::request();
```

## Типові випадки використання

Коли ви працюєте з запитом в веб-додатку, зазвичай ви
хочете отримати заголовок, або параметр `$_GET` чи `$_POST`, або можливо
навіть сирий зміст запиту. Flight надає простий інтерфейс для всіх цих функцій.

Ось приклад отримання параметра рядка запиту:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "Ви шукаєте: $keyword";
	// запит до бази даних або щось інше з $keyword
});
```

Ось приклад можливої форми з методом POST:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "Ви надіслали: $name, $email";
	// зберегти в базі даних або щось інше з $name та $email
});
```

## Властивості об'єкта запиту

Об'єкт запиту надає такі властивості:

- **body** - Сирий HTTP зміст запиту
- **url** - Запитувана URL
- **base** - Батьківський підкаталог URL
- **method** - Метод запиту (GET, POST, PUT, DELETE)
- **referrer** - URL реферера
- **ip** - IP адреса клієнта
- **ajax** - Чи є запит AJAX запитом
- **scheme** - Протокол сервера (http, https)
- **user_agent** - Інформація про браузер
- **type** - Тип вмісту
- **length** - Довжина вмісту
- **query** - Параметри рядка запиту
- **data** - Пост дані або JSON дані
- **cookies** - Дані кукі
- **files** - Завантажені файли
- **secure** - Чи є з'єднання безпечним
- **accept** - HTTP параметри прийому
- **proxy_ip** - Проксі IP адреса клієнта. Оглядає масив `$_SERVER` на наявність `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` у цьому порядку.
- **host** - Назва хоста запиту

Ви можете доступитися до властивостей `query`, `data`, `cookies` та `files`
як масивів або об'єктів.

Отже, щоб отримати параметр рядка запиту, ви можете зробити:

```php
$id = Flight::request()->query['id'];
```

Або ви можете зробити:

```php
$id = Flight::request()->query->id;
```

## Сирий зміст запиту

Щоб отримати сирий HTTP зміст запиту, наприклад, обробляючи запити PUT,
ви можете зробити:

```php
$body = Flight::request()->getBody();
```

## JSON Вхідні дані

Якщо ви надсилаєте запит з типом `application/json` і даними `{"id": 123}`
це буде доступно з властивості `data`:

```php
$id = Flight::request()->data->id;
```

## `$_GET`

Ви можете доступитися до масиву `$_GET` через властивість `query`:

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

Ви можете доступитися до масиву `$_POST` через властивість `data`:

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

Ви можете доступитися до масиву `$_COOKIE` через властивість `cookies`:

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

Є доступна скорочена функція для доступу до масиву `$_SERVER` через метод `getVar()`:

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## Доступ до завантажених файлів через `$_FILES`

Ви можете доступитися до завантажених файлів через властивість `files`:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## Обробка завантажень файлів (v3.12.0)

Ви можете обробляти завантаження файлів, використовуючи фреймворк з деякими допоміжними методами. Це в основному 
зводиться до витягування даних файлу з запиту та переміщення їх у нове місце.

```php
Flight::route('POST /upload', function(){
	// Якщо у вас є поле введення <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Якщо ви завантажили кілька файлів, ви можете пройтися по ним:

```php
Flight::route('POST /upload', function(){
	// Якщо у вас є поле введення <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Примітка безпеки:** Завжди перевіряйте та очищуйте введення користувача, особливо під час обробки завантажень файлів. Завжди перевіряйте тип розширень, які ви дозволите завантажувати, але також слід перевіряти "магічні байти" файлу, щоб впевнитися, що це дійсно той тип файлу, який користувач стверджує, що це. Існують [статті](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [та](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [бібліотеки](https://github.com/RikudouSage/MimeTypeDetector), які можуть допомогти з цим.

## Заголовки запиту

Ви можете доступитися до заголовків запиту, використовуючи методи `getHeader()` або `getHeaders()`:

```php

// Можливо, вам потрібен заголовок Authorization
$host = Flight::request()->getHeader('Authorization');
// або
$host = Flight::request()->header('Authorization');

// Якщо вам потрібно забрати всі заголовки
$headers = Flight::request()->getHeaders();
// або
$headers = Flight::request()->headers();
```

## Зміст запиту

Ви можете доступитися до сирого змісту запиту, використовуючи метод `getBody()`:

```php
$body = Flight::request()->getBody();
```

## Метод запиту

Ви можете доступитися до методу запиту, використовуючи властивість `method` або метод `getMethod()`:

```php
$method = Flight::request()->method; // насправді викликає getMethod()
$method = Flight::request()->getMethod();
```

**Примітка:** Метод `getMethod()` спочатку отримує метод з `$_SERVER['REQUEST_METHOD']`, потім його можна переписати 
через `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']`, якщо він існує, або `$_REQUEST['_method']`, якщо він існує.

## URL запитів

Існує кілька допоміжних методів для збирання частин URL для зручності.

### Повний URL

Ви можете доступитися до повного URL запиту, використовуючи метод `getFullUrl()`:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### Базовий URL

Ви можете доступитися до базового URL, використовуючи метод `getBaseUrl()`:

```php
$url = Flight::request()->getBaseUrl();
// Зверніть увагу, без кінцевого слешу.
// https://example.com
```

## Парсинг запитів

Ви можете передати URL до методу `parseQuery()`, щоб розпарсити рядок запиту в асоціативний масив:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```