# Запити

## Огляд

Flight інкапсулює HTTP-запит в один об'єкт, до якого можна отримати доступ так:

```php
$request = Flight::request();
```

## Розуміння

HTTP-запити є одним з основних аспектів, які потрібно розуміти щодо життєвого циклу HTTP. Користувач виконує дію в веб-браузері або HTTP-клієнті, і вони надсилають серію заголовків, тіла, URL тощо до вашого проекту. Ви можете захоплювати ці заголовки (мова браузера, тип стиснення, який вони можуть обробляти, user agent тощо) і захоплювати тіло та URL, що надсилаються до вашої програми Flight. Ці запити є суттєвими для вашої програми, щоб зрозуміти, що робити далі.

## Базове використання

PHP має кілька суперглобальних змінних, включаючи `$_GET`, `$_POST`, `$_REQUEST`, `$_SERVER`, `$_FILES` та `$_COOKIE`. Flight абстрагує їх у зручні [Collections](/learn/collections). Ви можете отримати доступ до властивостей `query`, `data`, `cookies` та `files` як до масивів або об'єктів.

> **Примітка:** **НАЙКАТЕГОРІЧНІШЕ** не рекомендується використовувати ці суперглобальні змінні у вашому проекті, і їх слід посилатися через об'єкт `request()`.

> **Примітка:** Немає доступної абстракції для `$_ENV`.

### `$_GET`

Ви можете отримати доступ до масиву `$_GET` через властивість `query`:

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

Ви можете отримати доступ до масиву `$_POST` через властивість `data`:

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

Ви можете отримати доступ до масиву `$_COOKIE` через властивість `cookies`:

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

Для допомоги щодо встановлення нових значень cookie дивіться [overclokk/cookie](/awesome-plugins/php-cookie)

### `$_SERVER`

Доступний ярлик для доступу до масиву `$_SERVER` через метод `getVar()`:

```php

$host = Flight::request()->getVar('HTTP_HOST');
```

### `$_FILES`

Ви можете отримати доступ до завантажених файлів через властивість `files`:

```php
// raw access to $_FILES property. See below for recommended approach
$uploadedFile = Flight::request()->files['myFile']; 
// or
$uploadedFile = Flight::request()->files->myFile;
```

Дивіться [Uploaded File Handler](/learn/uploaded-file) для отримання додаткової інформації.

#### Обробка завантаження файлів

_v3.12.0_

Ви можете обробляти завантаження файлів за допомогою фреймворку з деякими допоміжними методами. По суті, це зводиться до витягування даних файлу з запиту та переміщення його в нове місце розташування.

```php
Flight::route('POST /upload', function(){
	// If you had an input field like <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Якщо у вас завантажено кілька файлів, ви можете перебирати їх:

```php
Flight::route('POST /upload', function(){
	// If you had an input field like <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Примітка щодо безпеки:** Завжди валідуйте та очищайте вхідні дані користувача, особливо при роботі з завантаженням файлів. Завжди валідуйте типи розширень, які ви дозволите завантажувати, але також валідуйте "магічні байти" файлу, щоб переконатися, що це дійсно тип файлу, який стверджує користувач. Є [статті](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [та](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [бібліотеки](https://github.com/RikudouSage/MimeTypeDetector), доступні для допомоги з цим.

### Тіло запиту

Щоб отримати сире тіло HTTP-запиту, наприклад, при роботі з POST/PUT-запитами,
ви можете зробити:

```php
Flight::route('POST /users/xml', function(){
	$xmlBody = Flight::request()->getBody();
	// do something with the XML that was sent.
});
```

### JSON тіло

Якщо ви отримуєте запит з типом вмісту `application/json` і прикладом даних `{"id": 123}`
воно буде доступне з властивості `data`:

```php
$id = Flight::request()->data->id;
```

### Заголовки запиту

Ви можете отримати доступ до заголовків запиту за допомогою методу `getHeader()` або `getHeaders()`:

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

### Метод запиту

Ви можете отримати доступ до методу запиту за допомогою властивості `method` або методу `getMethod()`:

```php
$method = Flight::request()->method; // actually populated by getMethod()
$method = Flight::request()->getMethod();
```

**Примітка:** Метод `getMethod()` спочатку витягує метод з `$_SERVER['REQUEST_METHOD']`, потім його можна перезаписати 
за допомогою `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']`, якщо він існує, або `$_REQUEST['_method']`, якщо він існує.

## Властивості об'єкта запиту

Об'єкт запиту надає такі властивості:

- **body** - Сире тіло HTTP-запиту
- **url** - URL, що запитується
- **base** - Батьківська підкаталог URL
- **method** - Метод запиту (GET, POST, PUT, DELETE)
- **referrer** - URL реферера
- **ip** - IP-адреса клієнта
- **ajax** - Чи є запит AJAX-запитом
- **scheme** - Протокол сервера (http, https)
- **user_agent** - Інформація про браузер
- **type** - Тип вмісту
- **length** - Довжина вмісту
- **query** - Параметри рядка запиту
- **data** - Дані POST або JSON-даних
- **cookies** - Дані cookie
- **files** - Завантажені файли
- **secure** - Чи є з'єднання захищеним
- **accept** - HTTP параметри accept
- **proxy_ip** - IP-адреса проксі клієнта. Сканує масив `$_SERVER` на `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` в такому порядку.
- **host** - Ім'я хоста запиту
- **servername** - SERVER_NAME з `$_SERVER`

## Допоміжні методи

Є кілька допоміжних методів для складання частин URL або роботи з певними заголовками.

### Повний URL

Ви можете отримати доступ до повного URL запиту за допомогою методу `getFullUrl()`:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### Базовий URL

Ви можете отримати доступ до базового URL за допомогою методу `getBaseUrl()`:

```php
// http://example.com/path/to/something/cool?query=yes+thanks
$url = Flight::request()->getBaseUrl();
// https://example.com
// Notice, no trailing slash.
```

## Парсинг запиту

Ви можете передати URL методу `parseQuery()`, щоб розпарсити рядок запиту в асоціативний масив:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```

## Переговори щодо типів вмісту Accept

_v3.17.2_

Ви можете використовувати метод `negotiateContentType()`, щоб визначити найкращий тип вмісту для відповіді на основі заголовка `Accept`, надісланого клієнтом.

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

> **Примітка:** Якщо жоден з доступних типів не знайдено в заголовку `Accept`, метод поверне `null`. Якщо заголовок `Accept` не визначено, метод поверне перший тип у масиві `$availableTypes`.

## Див. також
- [Routing](/learn/routing) - Дивіться, як відображати маршрути на контролери та рендерити види.
- [Responses](/learn/responses) - Як налаштовувати HTTP-відповіді.
- [Why a Framework?](/learn/why-frameworks) - Як запити вписуються в загальну картину.
- [Collections](/learn/collections) - Робота з колекціями даних.
- [Uploaded File Handler](/learn/uploaded-file) - Обробка завантаження файлів.

## Вирішення проблем
- `request()->ip` та `request()->proxy_ip` можуть відрізнятися, якщо ваш веб-сервер знаходиться за проксі, балансувальником навантаження тощо. 

## Журнал змін
- v3.17.2 - Додано negotiateContentType()
- v3.12.0 - Додано можливість обробляти завантаження файлів через об'єкт запиту.
- v1.0 - Початкове випущення.