# Ghostff/Session

Менеджер сеансов PHP (неблокирующий, flash, сегмент, шифрование сеанса). Использует open_ssl PHP для необязательного шифрования/дешифрования данных сеанса. Поддерживает File, MySQL, Redis и Memcached.

## Установка

Установите с помощью composer.

```bash
composer require ghostff/session
```

## Основная конфигурация

Вам не обязательно передавать что-либо, чтобы использовать настройки по умолчанию с вашим сеансом. Вы можете прочитать о других настройках в [Github Readme](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// одна вещь, которую нужно помнить, состоит в том, что вы должны фиксировать свой сеанс при каждой загрузке страницы
// или вам нужно запустить auto_commit в вашей конфигурации.
```

## Простой пример

Вот простой пример того, как вы могли бы использовать это.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// выполните ваши действия по входу здесь
	// проверьте пароль и т. д.

	// если вход выполнен успешно
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// в любое время, когда вы пишете в сеанс, вы должны явно его фиксировать.
	$session->commit();
});

// Эта проверка может быть в логике ограниченной страницы или обернута middleware.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// выполните здесь логику ограниченной страницы
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

Вот более сложный пример того, как вы можете использовать это.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// установите пользовательский путь к вашему файлу конфигурации сеансов и дайте ему случайную строку для идентификатора сеанса
$app->register('session', Session::class, [ 'путь/к/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// или вы можете вручную переопределить параметры конфигурации
		$session->updateConfiguration([
			// если вы хотите хранить данные вашего сеанса в базе данных (хорошо, если вам нужна функциональность вроде "выйти из всех устройств")
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'моя-супер-С3КР3ТНАЯ-соль'), // измените это на что-то другое
			Session::CONFIG_AUTO_COMMIT   => true, // делайте это только если это требуется и/или сложно фиксировать() ваш сеанс.
												// кроме того, вы можете сделать Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Драйвер базы данных для DNS PDO, например (mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Хост базы данных
				'db_name'   => 'my_app_database',   # Имя базы данных
				'db_table'  => 'sessions',          # Таблица базы данных
				'db_user'   => 'root',              # Имя пользователя базы данных
				'db_pass'   => '',                  # Пароль базы данных
				'persistent_conn'=> false,          # Избегайте накладных расходов на установку нового подключения каждый раз, когда скрипт должен общаться с базой данных, что приводит к более быстрому веб-приложению. НАЙДИТЕ ЗАДНЮЮ ЧАСТЬ САМИ
			]
		]);
	}
);
```

## Документация

Посетите [Github Readme](https://github.com/Ghostff/Session) для полной документации. Параметры конфигурации [хорошо задокументированы в default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) файле самого себя. Код прост в понимании, если вы захотите изучить этот пакет самостоятельно.