# Ghostff/Session

Менеджер сесій PHP (без блокування, флеш, сегмент, шифрування сесій). Використовує PHP open_ssl для необов'язкового шифрування/дешифрування даних сесії. Підтримує File, MySQL, Redis та Memcached.

Натисніть [тут](https://github.com/Ghostff/Session), щоб переглянути код.

## Встановлення

Встановіть за допомогою composer.

```bash
composer require ghostff/session
```

## Базова конфігурація

Вам не потрібно передавати нічого, щоб використовувати налаштування за замовчуванням у вашій сесії. Ви можете прочитати про більше налаштувань у [Github Readme](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// єдине, що потрібно пам'ятати, це те, що ви повинні зафіксувати свою сесію при кожному завантаженні сторінки
// або вам потрібно буде запустити auto_commit у вашій конфігурації. 
```

## Простой приклад

Ось простий приклад того, як ви можете це використовувати.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// виконайте свою логіку входу тут
	// перевірте пароль тощо.

	// якщо вхід успішний
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// щоразу, коли ви записуєте в сесію, ви повинні зафіксувати це навмисно.
	$session->commit();
});

// Ця перевірка може бути в обмеженій логіці сторінки або обгорнута в посередника.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// виконайте свою логіку обмеженої сторінки тут
});

// версія посередника
Flight::route('/some-restricted-page', function() {
	// звичайна логіка сторінки
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## Більш складний приклад

Ось більш складний приклад того, як ви можете це використовувати.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// вкажіть користувацький шлях до вашого файлу конфігурації сесії та надайте випадковий рядок для ідентифікатора сесії
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// або ви можете вручну переоприділити параметри конфігурації
		$session->updateConfiguration([
			// якщо ви хочете зберігати свої дані сесії в базі даних (добре, якщо ви хочете таку функцію, як "вийти з усіх пристроїв")
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // будь ласка, змініть це на щось інше
			Session::CONFIG_AUTO_COMMIT   => true, // робіть це тільки якщо це потрібно і/або важко зафіксувати() вашу сесію.
												   // крім того, ви могли б зробити Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Драйвер бази даних для PDO dns eg(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Хост бази даних
				'db_name'   => 'my_app_database',   # Назва бази даних
				'db_table'  => 'sessions',          # Таблиця бази даних
				'db_user'   => 'root',              # Ім'я користувача бази даних
				'db_pass'   => '',                  # Пароль бази даних
				'persistent_conn'=> false,          # Уникнення витрат на встановлення нового з'єднання щоразу, коли скрипту потрібно взаємодіяти з базою даних, що призводить до швидшого веб-додатку. ЗНАЙДІТЬ ЗВОРОТНЮ СТОРОНУ САМОСТІЙНО
			]
		]);
	}
);
```

## Допомога! Мої дані сесії не зберігаються!

Ви налаштовуєте свої дані сесії, і вони не зберігаються між запитами? Можливо, ви забули зафіксувати свої дані сесії. Ви можете зробити це, викликавши `$session->commit()`, після того як ви налаштували свої дані сесії.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// виконайте свою логіку входу тут
	// перевірте пароль тощо.

	// якщо вхід успішний
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// щоразу, коли ви записуєте в сесію, ви повинні зафіксувати це навмисно.
	$session->commit();
});
```

Інший спосіб обійти це, коли ви налаштовуєте свою службу сесії, вам потрібно встановити `auto_commit` на `true` у вашій конфігурації. Це автоматично зафіксує ваші дані сесії після кожного запиту.

```php

$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Крім того, ви можете зробити `Flight::after('start', function() { Flight::session()->commit(); });`, щоб зафіксувати ваші дані сесії після кожного запиту.

## Документація

Відвідайте [Github Readme](https://github.com/Ghostff/Session) для повної документації. Параметри конфігурації [добре задокументовані у файлі default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php). Код легко зрозуміти, якщо ви хочете ознайомитися з цим пакетом самостійно.