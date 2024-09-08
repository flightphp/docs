# Безпека

Безпека є дуже важливою, коли мова йде про веб-додатки. Ви хочете переконатися, що ваш додаток безпечний і дані ваших користувачів 
захищені. Flight надає ряд можливостей для допомоги у забезпеченні безпеки ваших веб-додатків.

## Заголовки

HTTP-заголовки є одним із найпростіших способів захистити ваші веб-додатки. Ви можете використовувати заголовки, щоб запобігти клікджекингу, XSS та іншим атакам. 
Існує кілька способів додати ці заголовки до вашого додатка.

Два чудових сайти для перевірки безпеки ваших заголовків - це [securityheaders.com](https://securityheaders.com/) та 
[observatory.mozilla.org](https://observatory.mozilla.org/).

### Додати вручну

Ви можете вручну додати ці заголовки, використовуючи метод `header` об'єкта `Flight\Response`.
```php
// Встановіть заголовок X-Frame-Options, щоб запобігти клікджекингу
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Встановіть заголовок Content-Security-Policy, щоб запобігти XSS
// Примітка: цей заголовок може бути дуже складним, тому вам слід
//  звернутися до прикладів в Інтернеті для вашого додатка
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Встановіть заголовок X-XSS-Protection для запобігання XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Встановіть заголовок X-Content-Type-Options, щоб запобігти зчитуванню MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Встановіть заголовок Referrer-Policy, щоб контролювати, скільки інформації про реферер надсилається
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Встановіть заголовок Strict-Transport-Security, щоб примусити використовувати HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Встановіть заголовок Permissions-Policy, щоб контролювати, які функції та API можуть використовуватися
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Ці заголовки можуть бути додані на початку ваших файлів `bootstrap.php` або `index.php`.

### Додати як фільтр

Ви також можете додати їх у фільтр/перехоплювач, як у наступному:

```php
// Додайте заголовки у фільтр
Flight::before('start', function() {
	Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
	Flight::response()->header("Content-Security-Policy", "default-src 'self'");
	Flight::response()->header('X-XSS-Protection', '1; mode=block');
	Flight::response()->header('X-Content-Type-Options', 'nosniff');
	Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
	Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
	Flight::response()->header('Permissions-Policy', 'geolocation=()');
});
```

### Додати як проміжне програмне забезпечення

Ви також можете додати їх як клас проміжного програмного забезпечення. Це хороший спосіб підтримувати ваш код чистим і організованим.

```php
// app/middleware/SecurityHeadersMiddleware.php

namespace app\middleware;

class SecurityHeadersMiddleware
{
	public function before(array $params): void
	{
		Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
		Flight::response()->header("Content-Security-Policy", "default-src 'self'");
		Flight::response()->header('X-XSS-Protection', '1; mode=block');
		Flight::response()->header('X-Content-Type-Options', 'nosniff');
		Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
		Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		Flight::response()->header('Permissions-Policy', 'geolocation=()');
	}
}

// index.php або там, де у вас маршрути
// Для інформації, цей порожній рядок групи слугує глобальним проміжним програмним забезпеченням для
// всіх маршрутів. Звичайно, ви можете зробити те саме і просто додати
// це тільки до певних маршрутів.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// більше маршрутів
}, [ new SecurityHeadersMiddleware() ]);
```


## Підробка запиту між сайтами (CSRF)

Підробка запиту між сайтами (CSRF) - це тип атаки, при якій шкідливий веб-сайт може змусити браузер користувача надіслати запит на ваш веб-сайт. 
Це можна використовувати для виконання дій на вашому веб-сайті без відома користувача. Flight не надає вбудованого механізму захисту CSRF, 
але ви можете легко реалізувати свій власний, використовуючи проміжне програмне забезпечення.

### Налаштування

Спочатку вам потрібно згенерувати токен CSRF і зберегти його в сеансі користувача. Ви можете використовувати цей токен у своїх формах та перевіряти його, коли 
форма надсилається.

```php
// Згенеруйте токен CSRF і збережіть його в сеансі користувача
// (припускаючи, що ви створили об'єкт сесії та прикріпили його до Flight)
// дивіться документацію з сесій для отримання додаткової інформації
Flight::register('session', \Ghostff\Session\Session::class);

// Вам потрібно згенерувати лише один токен на сеанс (щоб він працював
// у кількох вкладках та запитах для одного й того ж користувача)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- Використовуйте токен CSRF у своїй формі -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- інші поля форми -->
</form>
```

#### Використання Latte

Ви можете також налаштувати функцію для виведення токена CSRF у ваших шаблонах Latte.

```php
// Налаштуйте користувацьку функцію для виведення токена CSRF
// Примітка: Подання було налаштовано з Latte як механізмом подання
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

А тепер у ваших шаблонах Latte ви можете використовувати функцію `csrf()` для виведення токена CSRF.

```html
<form method="post">
	{csrf()}
	<!-- інші поля форми -->
</form>
```

Коротко та просто, вірно?

### Перевірка токена CSRF

Ви можете перевірити токен CSRF, використовуючи фільтри подій:

```php
// Це проміжне програмне забезпечення перевіряє, чи є запит POST запитом, і якщо так, то перевіряє, чи дійсний токен CSRF
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// захопіть токен CSRF з значень форми
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Недійсний токен CSRF');
			// або для відповіді JSON
			Flight::jsonHalt(['error' => 'Недійсний токен CSRF'], 403);
		}
	}
});
```

Або ви можете використовувати клас проміжного програмного забезпечення:

```php
// app/middleware/CsrfMiddleware.php

namespace app\middleware;

class CsrfMiddleware
{
	public function before(array $params): void
	{
		if(Flight::request()->method == 'POST') {
			$token = Flight::request()->data->csrf_token;
			if($token !== Flight::session()->get('csrf_token')) {
				Flight::halt(403, 'Недійсний токен CSRF');
			}
		}
	}
}

// index.php або там, де у вас маршрути
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// більше маршрутів
}, [ new CsrfMiddleware() ]);
```

## Міжсайтовий скриптинг (XSS)

Міжсайтовий скриптинг (XSS) - це тип атаки, при якій шкідливий веб-сайт може впроваджувати код на вашому веб-сайті. Більшість з цих можливостей виникає 
з значень форм, які заповнюють ваші кінцеві користувачі. Ви повинні **ніколи** не довіряти виходу від своїх користувачів! Завжди вважайте, що всі вони є 
найкращими хакерами у світі. Вони можуть впроваджувати шкідливий JavaScript або HTML на вашу сторінку. Цей код може бути використаний для викрадення інформації у ваших 
користувачів або виконання дій на вашому веб-сайті. Використовуючи клас подання Flight, ви можете легко екранувати вихід, щоб запобігти атакам XSS.

```php
// Припустимо, що користувач розумний і намагається використовувати це як своє ім'я
$name = '<script>alert("XSS")</script>';

// Це екранує вихід
Flight::view()->set('name', $name);
// Це виведе: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Якщо ви використовуєте щось на зразок Latte, зареєстрованого як ваш клас подання, це також автоматично екранує це.
Flight::view()->render('template', ['name' => $name]);
```

## SQL-ін'єкція

SQL-ін'єкція - це тип атаки, при якій шкідливий користувач може впроваджувати SQL-код у вашу базу даних. Це можна використовувати для викрадення інформації 
з вашої бази даних або виконання дій у вашій базі даних. Знову ж таки, ви повинні **ніколи** не довіряти введенню від своїх користувачів! Завжди вважайте, що вони 
збираються на злість. Ви можете використовувати підготовлені запити у ваших об'єктах `PDO`, щоб запобігти SQL-ін'єкції.

```php
// Припустимо, у вас зареєстровано Flight::db() як ваш об'єкт PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Якщо ви використовуєте клас PdoWrapper, це можна легко зробити в один рядок
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Ви можете зробити те саме з об'єктом PDO з заповнювачами ?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Просто пообіцяйте, що ніколи НЕ робитимете щось подібне...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// тому що що, якщо $username = "' OR 1=1; -- "; 
// Після побудови запиту це виглядає так
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Це виглядає дивно, але це дійсний запит, який спрацює. Насправді,
// це дуже поширена атака SQL-ін'єкцій, яка поверне всіх користувачів.
```

## CORS

Обмін ресурсами між походженнями (CORS) - це механізм, який дозволяє багатьом ресурсам (наприклад, шрифтам, JavaScript тощо) на веб-сторінці бути 
запрошеними з іншого домену, ніж той, з якого походить ресурс. Flight не має вбудованої функціональності, 
але це можна легко обробити за допомогою хуків, які запускаються перед викликом методу `Flight::start()`.

```php
// app/utils/CorsUtil.php

namespace app\utils;

class CorsUtil
{
	public function set(array $params): void
	{
		$request = Flight::request();
		$response = Flight::response();
		if ($request->getVar('HTTP_ORIGIN') !== '') {
			$this->allowOrigins();
			$response->header('Access-Control-Allow-Credentials', 'true');
			$response->header('Access-Control-Max-Age', '86400');
		}

		if ($request->method === 'OPTIONS') {
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_METHOD') !== '') {
				$response->header(
					'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD'
				);
			}
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS') !== '') {
				$response->header(
					"Access-Control-Allow-Headers",
					$request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS')
				);
			}

			$response->status(200);
			$response->send();
			exit;
		}
	}

	private function allowOrigins(): void
	{
		// налаштуйте тут свої дозволені хости.
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		$request = Flight::request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $allowed, true) === true) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin", $request->getVar('HTTP_ORIGIN'));
		}
	}
}

// index.php або там, де ви маєте свої маршрути
$CorsUtil = new CorsUtil();

// Це потрібно запустити до того, як запустяться стартові запити.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

## Висновок

Безпека є дуже важливою, і важливо переконатися, що ваші веб-додатки безпечні. Flight надає ряд можливостей, щоб допомогти вам 
захистити ваші веб-додатки, але важливо завжди залишатися напоготові та переконатися, що ви робите все можливе, щоб захистити дані ваших користувачів. Завжди припускайте найгірше і ніколи не довіряйте введенню від своїх користувачів. Завжди екрануйте вихід і використовуйте підготовлені запити для запобігання SQL-ін'єкції. Завжди використовуйте проміжне програмне забезпечення, щоб захистити свої маршрути від атак CSRF і CORS. Якщо ви зробите всі ці речі, ви будете на хорошому шляху до створення безпечних веб-додатків.