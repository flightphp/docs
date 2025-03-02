# Ghostff/Session

PHP Менеджер Сесій (нема блокувань, flash, сегмент, шифрування сесій). Використовує PHP open_ssl для необов’язкового шифрування/дешифрування даних сесії. Підтримує файли, MySQL, Redis та Memcached.

Натисніть [тут](https://github.com/Ghostff/Session), щоб переглянути код.

## Встановлення

Встановіть за допомогою composer.

```bash
composer require ghostff/session
```

## Основна конфігурація

Вам не потрібно передавати нічого, щоб використовувати налаштування за замовчуванням з вашою сесією. Ви можете прочитати про більше налаштувань у [Github Readme](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// одна річ, яку потрібно пам’ятати, це те, що ви повинні зафіксувати свою сесію при завантаженні кожної сторінки
// або вам потрібно буде виконати auto_commit у вашій конфігурації.
```

## Простий приклад

Ось простий приклад того, як ви могли б це використовувати.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// виконайте вашу логіку входу тут
	// перевірте пароль тощо.

	// якщо вхід успішний
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// кожного разу, коли ви записуєте у сесію, ви повинні зафіксувати її навмисно.
	$session->commit();
});

// Ця перевірка могла б бути в логіці обмеженої сторінки або обернута з middleware.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// виконайте вашу логіку обмеженої сторінки тут
});

// версія middleware
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

Ось більш складний приклад того, як ви могли б це використовувати.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// задайте власний шлях до вашого файлу конфігурації сесій і дайте йому випадковий рядок для ідентифікатора сесії
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// або ви можете вручну перезаписати параметри конфігурації
		$session->updateConfiguration([
			// якщо ви хочете зберігати дані своєї сесії в базі даних (добре, якщо ви хочете щось на кшталт "вийти з усіх пристроїв")
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // будь ласка, змініть це на щось інше
			Session::CONFIG_AUTO_COMMIT   => true, // робіть це лише якщо це потрібно і/або важко зафіксувати() вашу сесію.
												   // додатково ви могли б зробити Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Драйвер бази даних для PDO dns eg(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Хост бази даних
				'db_name'   => 'my_app_database',   # Назва бази даних
				'db_table'  => 'sessions',          # Таблиця бази даних
				'db_user'   => 'root',              # Ім’я користувача бази даних
				'db_pass'   => '',                  # Пароль бази даних
				'persistent_conn'=> false,          # Уникайте накладних витрат на установку нового з’єднання щоразу, коли скрипт потребує зв’язку з базою даних, що призводить до швидшого веб-застосунку. ЗНАЙДІТЬ ЗВОРОТНІСТЬ САМОСТІЙНО
			]
		]);
	}
);
```

## Допоможіть! Мої дані сесії не зберігаються!

Ви встановлюєте свої дані сесії, але вони не зберігаються між запитами? Ви могли забути зафіксувати свої дані сесії. Ви можете зробити це, викликавши `$session->commit()`, після того як ви встановили свої дані сесії.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// виконайте вашу логіку входу тут
	// перевірте пароль тощо.

	// якщо вхід успішний
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// кожного разу, коли ви записуєте у сесію, ви повинні зафіксувати її навмисно.
	$session->commit();
});
```

Інший спосіб вирішення цієї проблеми - це коли ви налаштовуєте свою службу сесій, ви повинні встановити `auto_commit` на `true` у вашій конфігурації. Це автоматично зафіксує ваші дані сесії після кожного запиту.

```php

$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Додатково ви могли б зробити `Flight::after('start', function() { Flight::session()->commit(); });`, щоб зафіксувати свої дані сесії після кожного запиту.

## Документація

Відвідайте [Github Readme](https://github.com/Ghostff/Session) для повної документації. Параметри конфігурації [добре задокументовані у файлі default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php). Код простий для розуміння, якщо ви хочете переглянути цей пакет самостійно.