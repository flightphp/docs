# Ghostff/Session

Менеджер PHP-сессий (неблокирующий, flash, сегмент, шифрование сессий). Использует open_ssl PHP для необязательного шифрования/дешифрования данных сессии. Поддерживает File, MySQL, Redis и Memcached.

## Установка

Установите с помощью composer.

```bash
composer require ghostff/session
```

## Базовая настройка

Вам не нужно ничего передавать, чтобы использовать настройки по умолчанию с вашей сессией. Вы можете узнать больше о настройках в [Github Readme](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// одна вещь, которую нужно запомнить, заключается в том, что вы должны фиксировать свою сессию при каждой загрузке страницы
// или вам нужно запустить auto_commit в вашей конфигурации.
```

## Простой пример

Вот простой пример того, как вы могли бы использовать это.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// выполняйте здесь логику входа
	// проверьте пароль и т. д.

	// если вход выполнен успешно
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// в любое время, когда вы пишете в сессию, вы должны явно фиксировать ее.
	$session->commit();
});

// Эта проверка может быть в логике ограничения страницы или быть обернутой с помощью промежуточного ПО.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// выполняйте здесь логику ограниченной страницы
});

// версия с промежуточным ПО
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

// установите пользовательский путь к файлу конфигурации сеанса и дайте ему случайную строку для идентификатора сеанса
$app->register('session', Session::class, [ 'путь/к/файлу_конфигурации_сессии.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// или вы можете вручную переопределить параметры конфигурации
		$session->updateConfiguration([
			// если вы хотите хранить данные сессии в базе данных (хорошо, если вам нужна функциональность вроде "выйти из всех устройств")
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'моя-супер-секретная-соль'), // пожалуйста, поменяйте это на что-то другое
			Session::CONFIG_AUTO_COMMIT   => true, // делайте это только в случае необходимости и/или сложности фиксации вашей сессии.
												   // кроме того, вы можете сделать Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Драйвер базы данных для PDO dns например (mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Хост базы данных
				'db_name'   => 'моя_база_данных_приложения',   # Имя базы данных
				'db_table'  => 'сессии',          # Таблица базы данных
				'db_user'   => 'root',              # Имя пользователя базы данных
				'db_pass'   => '',                  # Пароль базы данных
				'persistent_conn'=> false,          # Избегайте накладных расходов на создание нового соединения каждый раз, когда скрипт должен общаться с базой данных, что приводит к более быстрому веб-приложению. НАЙДИТЕ ЭТО САМИ
			]
		]);
	}
);
```

## Помощь! Мои данные сеанса не сохраняются!

Вы устанавливаете данные вашей сессии и они не сохраняются между запросами? Возможно, вы забыли зафиксировать данные вашей сессии. Вы можете сделать это, вызвав `$session->commit()` после установки данных вашей сессии.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// выполните здесь логику входа
	// проверьте пароль и т. д.

	// если вход выполнен успешно
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// в любое время, когда вы пишете в сессию, вы должны явно фиксировать ее.
	$session->commit();
});
```

Другой способ решить эту проблему заключается в том, что при настройке вашего сеанса вам нужно установить `auto_commit` в значение `true` в вашей конфигурации. Это автоматически фиксирует данные вашей сессии после каждого запроса.

```php

$app->register('session', Session::class, [ 'путь/к/файлу_конфигурации_сессии.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Кроме того, вы можете сделать `Flight::after('start', function() { Flight::session()->commit(); });` чтобы фиксировать данные вашей сессии после каждого запроса.

## Документация

Посетите [Github Readme](https://github.com/Ghostff/Session) для полной документации. Опции конфигурации [хорошо задокументированы в default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) самого файла. Код прост в понимании, если вы захотите изучить этот пакет самостоятельно.