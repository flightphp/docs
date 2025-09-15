# Запити

Flight encapsulates the HTTP request into a single object, which can be
accessed by doing:

```php
$request = Flight::request();
```

## Типові випадки використання

Коли ви працюєте з запитом у веб-застосунку, зазвичай ви хочете витягнути заголовок, або параметр `$_GET` чи `$_POST`, або, можливо,
навіть сире тіло запиту. Flight надає простий інтерфейс для виконання всіх цих дій.

Ось приклад отримання параметра рядка запиту:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "You are searching for: $keyword";
	// запитайте базу даних або щось інше з $keyword
});
```

Ось приклад, можливо, форми з методом POST:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "You submitted: $name, $email";
	// збережіть у базу даних або щось інше з $name і $email
});
```

## Властивості об'єкта запиту

Об'єкт запиту надає такі властивості:

- **body** - Сире тіло HTTP-запиту
- **url** - URL, який запитується
- **base** - Батьківська піддиректорія URL
- **method** - Метод запиту (GET, POST, PUT, DELETE)
- **referrer** - URL-ссилка
- **ip** - IP-адреса клієнта
- **ajax** - Чи є запит AJAX-запитом
- **scheme** - Протокол сервера (http, https)
- **user_agent** - Інформація про браузер
- **type** - Тип вмісту
- **length** - Довжина вмісту
- **query** - Параметри рядка запиту
- **data** - Дані POST або JSON-дані
- **cookies** - Дані cookie
- **files** - Завантажені файли
- **secure** - Чи є з'єднання захищеним
- **accept** - Параметри HTTP accept
- **proxy_ip** - IP-адреса проксі клієнта. Сканує масив `$_SERVER` для `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` у такому порядку.
- **host** - Ім'я хоста запиту
- **servername** - SERVER_NAME з `$_SERVER`

Ви можете отримати доступ до властивостей `query`, `data`, `cookies` і `files`
як до масивів або об'єктів.

Отже, щоб отримати параметр рядка запиту, ви можете зробити:

```php
$id = Flight::request()->query['id'];
```

Або ви можете зробити:

```php
$id = Flight::request()->query->id;
```

## Сире тіло запиту

Щоб отримати сире тіло HTTP-запиту, наприклад, при роботі з запитами PUT,
ви можете зробити:

```php
$body = Flight::request()->getBody();
```

## Ввід JSON

Якщо ви надсилаєте запит з типом `application/json` і даними `{"id": 123}`,
вони будуть доступні з властивості `data`:

```php
$id = Flight::request()->data->id;
```

## `$_GET`

Ви можете отримати доступ до масиву `$_GET` через властивість `query`:

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

Ви можете отримати доступ до масиву `$_POST` через властивість `data`:

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

Ви можете отримати доступ до масиву `$_COOKIE` через властивість `cookies`:

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

Доступний скорочений спосіб доступу до масиву `$_SERVER` через метод `getVar()`:

```php
$host = Flight::request()->getVar('HTTP_HOST');
```

## Доступ до завантажених файлів через `$_FILES`

Ви можете отримати доступ до завантажених файлів через властивість `files`:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## Обробка завантажень файлів (v3.12.0)

Ви можете обробляти завантаження файлів за допомогою фреймворку з деякими допоміжними методами. Це базується на витягуванні даних файлу з запиту та переміщенні його в нове місце.

```php
Flight::route('POST /upload', function(){
	// Якщо у вас є поле вводу, як <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Якщо ви маєте кілька завантажених файлів, ви можете перебрати їх:

```php
Flight::route('POST /upload', function(){
	// Якщо у вас є поле вводу, як <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Примітка безпеки:** Завжди перевіряйте та очищуйте вхідні дані користувача, особливо при роботі з завантаженнями файлів. Завжди перевіряйте типи розширень, які ви дозволяєте завантажувати, але також перевіряйте "магічні байти" файлу, щоб забезпечити, що це справді тип файлу, який заявляє користувач. Є [articles](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [and](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [libraries](https://github.com/RikudouSage/MimeTypeDetector) для допомоги з цим.

## Заголовки запиту

Ви можете отримати доступ до заголовків запиту за допомогою методів `getHeader()` або `getHeaders()`:

```php
// Можливо, вам потрібен заголовок Authorization
$host = Flight::request()->getHeader('Authorization');
// або
$host = Flight::request()->header('Authorization');

// Якщо вам потрібно отримати всі заголовки
$headers = Flight::request()->getHeaders();
// або
$headers = Flight::request()->headers();
```

## Тіло запиту

Ви можете отримати доступ до сирого тіла запиту за допомогою методу `getBody()`:

```php
$body = Flight::request()->getBody();
```

## Метод запиту

Ви можете отримати доступ до методу запиту за допомогою властивості `method` або методу `getMethod()`:

```php
$method = Flight::request()->method; // фактично викликає getMethod()
$method = Flight::request()->getMethod();
```

**Примітка:** Метод `getMethod()` спочатку витягує метод з `$_SERVER['REQUEST_METHOD']`, потім його можна перезаписати
за допомогою `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']`, якщо він існує, або `$_REQUEST['_method']`, якщо він існує.

## URL запиту

Є кілька допоміжних методів для складання частин URL для вашої зручності.

### Повний URL

Ви можете отримати доступ до повного URL запиту за допомогою методу `getFullUrl()`:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### Базовий URL

Ви можете отримати доступ до базового URL за допомогою методу `getBaseUrl()`:

```php
$url = Flight::request()->getBaseUrl();
// Зверніть увагу, без завершуючого слеша.
// https://example.com
```

## Аналіз рядка запиту

Ви можете передати URL методу `parseQuery()` для аналізу рядка запиту в асоціативний масив:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```