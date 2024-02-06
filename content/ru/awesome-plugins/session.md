# Ghostff/Session

Менеджер сессий PHP (неблокирующий, flash, сегмент, шифрование сессий). Использует PHP open_ssl для необязательного шифрования/дешифрования данных сессии. Поддерживает файл, MySQL, Redis и Memcached.

## Установка

Установите с помощью composer.

```bash
composer require ghostff/session
```

## Базовая настройка

Вам не обязательно что-либо передавать, чтобы использовать настройки по умолчанию с вашей сессией. Вы можете узнать больше о настройках в [Github Readme](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// одна вещь, которую нужно помнить, это то, что вы должны фиксировать вашу сессию на каждой загрузке страницы
// или вам нужно запускать auto_commit в вашей конфигурации.
```

## Простой пример

Вот простой пример того, как вы могли бы использовать это.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// сделайте вашу логику входа здесь
	// валидируйте пароль и т. д.

	// если вход успешен
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// каждый раз, когда вы пишете в сессию, вам нужно явно ее зафиксировать.
	$session->commit();
});

// Эта проверка может быть в логике ограниченной страницы или быть обернута в middleware.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// сделайте вашу логику ограниченной страницы здесь
});

// версия middleware
Flight::route('/some-restricted-page', function() {
	// обычная логика страницы
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## Более сложный пример

Вот более сложный пример того, как вы могли бы использовать это.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// установите свой собственный путь к файлу конфигурации сессии и укажите для идентификатора сессии случайную строку
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// или вы можете вручную переопределить параметры конфигурации
		$session->updateConfiguration([
			// если вы хотите хранить данные сеанса в базе данных (хорошо, если вы хотите что-то вроде функционала "выйти из всех устройств")
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'моя-супер-S3CR3T-соль'), // пожалуйста, измените это на что-то другое
			Session::CONFIG_AUTO_COMMIT   => true, // делайте это только если это требуется или вам трудно фиксировать вашу сессию.
												// дополнительно, вы можете сделать Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Драйвер базы данных для PDO dns, например (mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Хост базы данных
				'db_name'   => 'my_app_database',   # Имя базы данных
				'db_table'  => 'sessions',          # Таблица базы данных
				'db_user'   => 'root',              # Имя пользователя базы данных
				'db_pass'   => '',                  # Пароль базы данных
				'persistent_conn'=> false,          # Избегайте издержек на установление нового подключения каждый раз, когда скрипту нужно общаться с базой данных, что приводит к более быстрому веб-приложению. НАЙДИ ЗАДНИЙ АРГУМЕНТ САМОСТОЯТЕЛЬНО
			]
		]);
	}
);
```

## Документация

Посетите [Github Readme](https://github.com/Ghostff/Session) для полной документации. Параметры конфигурации [хорошо задокументированы в default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) файле. Код прост в понимании, если вы захотите изучить этот пакет самостоятельно.