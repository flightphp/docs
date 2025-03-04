# Ghostff/Session

Менеджер сессий PHP (неблокирующий, flash, сегмент, шифрование сессий). Использует PHP open_ssl для необязательного шифрования/дешифрования данных сессии. Поддерживает File, MySQL, Redis и Memcached.

Нажмите [сюда](https://github.com/Ghostff/Session), чтобы просмотреть код.

## Установка

Установите с помощью composer.

```bash
composer require ghostff/session
```

## Базовая настройка

Вам не нужно передавать что-либо, чтобы использовать настройки по умолчанию для вашей сессии. Вы можете прочитать о других настройках в [Github Readme] (https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// важно помнить, что вам нужно подтвердить вашу сессию при каждой загрузке страницы
// или вам нужно запустить auto_commit в вашей конфигурации.
```

## Простой пример

Вот простой пример того, как вы могли бы использовать это.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// сделайте здесь логику входа
	// проверка пароля и т. д.

	// если вход прошел успешно
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// каждый раз, когда вы записываете в сессию, вы должны подтвердить ее нарочно.
	$session->commit();
});

// Эта проверка может быть в логике ограниченной страницы или обернута с помощью промежуточного программного обеспечения.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// сделайте здесь логику ограниченной страницы
});

// версия промежуточного программного обеспечения
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

// установите пользовательский путь к файлу конфигурации вашей сессии и укажите случайную строку для идентификатора сессии
$app->register('session', Session::class, [ 'путь/к/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// или вы можете вручную переопределить параметры конфигурации
		$session->updateConfiguration([
			// если вы хотите хранить данные сеанса в базе данных (хорошо, если вам нужна функциональность типа "разлогиниться со всех устройств")
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // пожалуйста, измените это на что-то другое
			Session::CONFIG_AUTO_COMMIT   => true, // сделайте это только если это необходимо или трудно подтвердить вашу сессию.
												   // кроме того, вы можете сделать Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Драйвер базы данных для PDO dns, например (mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Хост базы данных
				'db_name'   => 'my_app_database',   # Имя базы данных
				'db_table'  => 'sessions',          # Таблица базы данных
				'db_user'   => 'root',              # Имя пользователя базы данных
				'db_pass'   => '',                  # Пароль базы данных
				'persistent_conn'=> false,          # Избегайте накладных расходов на установку нового соединения каждый раз, когда скрипту необходимо общаться с базой данных, что приводит к более быстрой веб-приложению. НАЙДИТЕ ЗА ПОДСТАВКУ САМИ
			]
		]);
	}
);
```

## Помощь! Мои данные сессии не сохраняются!

Вы устанавливаете данные вашей сессии, и они не сохраняются между запросами? Возможно, вы забыли подтвердить данные вашей сессии. Вы можете сделать это, вызвав `$session->commit()` после установки данных вашей сессии.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// сделайте здесь логику входа
	// проверка пароля и т. д.

	// если вход прошел успешно
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// каждый раз, когда вы записываете в сессию, вы должны подтвердить ее нарочно.
	$session->commit();
});
```

Другой способ решить эту проблему заключается в том, что при настройке вашего сервиса сессий вам нужно установить `auto_commit` в `true` в вашей конфигурации. Это автоматически подтвердит ваши данные сессии после каждого запроса.

```php

$app->register('session', Session::class, [ 'путь/к/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Кроме того, вы можете использовать `Flight::after('start', function() { Flight::session()->commit(); });` для подтверждения ваших данных сессии после каждого запроса.

## Документация

Посетите [Github Readme](https://github.com/Ghostff/Session) для полной документации. Параметры конфигурации [хорошо задокументированы в default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) файле самого кода. Код прост в понимании, если вы захотите изучить этот пакет самостоятельно.