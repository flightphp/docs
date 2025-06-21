# Ghostff/Session

PHP Session Manager (неблокувальний, флеш, сегмент, шифрування сесій). Використовує PHP open_ssl для необов'язкового шифрування/розшифрування даних сесій. Підтримує File, MySQL, Redis, and Memcached.

Натисніть [here](https://github.com/Ghostff/Session), щоб переглянути код.

## Встановлення

Встановіть за допомогою composer.

```bash
composer require ghostff/session
```

## Основна Конфігурація

Вам не потрібно передавати нічого, щоб використовувати налаштування за замовчуванням для вашої сесії. Ви можете прочитати про інші налаштування в [Github Readme](https://github.com/Ghostff/Session).

```php
use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// одна річ, яку потрібно пам'ятати, це те, що ви повинні зафіксувати свою сесію на кожному завантаженні сторінки
// або вам доведеться запустити auto_commit у вашій конфігурації. 
```

## Простий Приклад

Ось простий приклад того, як ви можете використовувати це.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// виконайте вашу логіку входу тут
	// перевірте пароль тощо.

	// якщо вхід успішний
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// щоразу, коли ви записуєте в сесію, ви повинні зафіксувати її навмисно.
	$session->commit();
});

// Ця перевірка може бути в логіці обмеженої сторінки або обгорнута в middleware.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// виконайте вашу логіку обмеженої сторінки тут
});

// версія з middleware
Flight::route('/some-restricted-page', function() {
	// регулярна логіка сторінки
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## Більш Складний Приклад

Ось більш складний приклад того, як ви можете використовувати це.

```php
use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// вкажіть шлях до вашого файлу конфігурації сесії як перший аргумент
// або передайте йому спеціальний масив
$app->register('session', Session::class, [ 
	[
		// якщо ви хочете зберігати дані сесії в базі даних (добре для чогось на кшталт "вийти з усіх пристроїв" функціональності)
		Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
		Session::CONFIG_ENCRYPT_DATA  => true,
		Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // будь ласка, змініть це на щось інше
		Session::CONFIG_AUTO_COMMIT   => true, // робіть це тільки якщо це вимагається і/або важко викликати commit() для вашої сесії.
												// додатково ви можете зробити Flight::after('start', function() { Flight::session()->commit(); });
		Session::CONFIG_MYSQL_DS         => [
			'driver'    => 'mysql',             # драйвер бази даних для PDO dns, наприклад (mysql:host=...;dbname=...)
			'host'      => '127.0.0.1',         # хост бази даних
			'db_name'   => 'my_app_database',   # назва бази даних
			'db_table'  => 'sessions',          # таблиця бази даних
			'db_user'   => 'root',              # ім'я користувача бази даних
			'db_pass'   => '',                  # пароль бази даних
			'persistent_conn'=> false,          # Уникайте накладних витрат на встановлення нового з'єднання кожного разу, коли скрипт потребує спілкування з базою даних, що призводить до швидшої веб-додатки. ЗНАЙДІТЬ ЗВОРОТНІЙ БІК САМІ
		]
	] 
]);
```

## Допомога! Мої Дані Сесії Не Зберігаються!

Ви встановлюєте дані сесії, але вони не зберігаються між запитами? Можливо, ви забули зафіксувати дані сесії. Ви можете зробити це, викликавши `$session->commit()` після встановлення даних сесії.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// виконайте вашу логіку входу тут
	// перевірте пароль тощо.

	// якщо вхід успішний
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// щоразу, коли ви записуєте в сесію, ви повинні зафіксувати її навмисно.
	$session->commit();
});
```

Інший спосіб — коли ви налаштовуєте службу сесії, ви повинні встановити `auto_commit` на `true` у вашій конфігурації. Це автоматично зафіксує дані сесії після кожного запиту.

```php
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Додатково ви можете зробити `Flight::after('start', function() { Flight::session()->commit(); });`, щоб зафіксувати дані сесії після кожного запиту.

## Документація

Відвідайте [Github Readme](https://github.com/Ghostff/Session) для повної документації. Параметри конфігурації [добре задокументовані в файлі default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php). Код простий для розуміння, якщо ви хочете переглянути цей пакет самостійно.