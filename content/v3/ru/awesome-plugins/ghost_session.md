# Ghostff/Session

Менеджер сессий PHP (неблокирующий, flash, сегмент, шифрование сессий). Использует PHP open_ssl для необязательной шифровки/расшифровки данных сессии. Поддерживает File, MySQL, Redis и Memcached.

Нажмите [здесь](https://github.com/Ghostff/Session), чтобы просмотреть код.

## Установка

Установите с помощью composer.

```bash
composer require ghostff/session
```

## Базовая конфигурация

Вы не обязаны передавать ничего, чтобы использовать настройки по умолчанию с вашей сессией. Вы можете прочитать о дополнительных настройках в [Github Readme](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// одно, что следует помнить, это то, что вы должны фиксировать вашу сессию при каждом загрузке страницы
// или вам нужно будет запустить auto_commit в вашей конфигурации. 
```

## Простой пример

Вот простой пример того, как вы можете использовать это.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// выполните вашу логику входа здесь
	// проверьте пароль и т.д.

	// если вход успешен
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// всякий раз, когда вы записываете в сессию, вы должны фиксировать это намеренно.
	$session->commit();
});

// Эта проверка может быть в логике защищенной страницы или обернута в промежуточное ПО.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// выполните свою логику защищенной страницы здесь
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

Вот более сложный пример того, как вы можете использовать это.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// установите пользовательский путь к вашему файлу конфигурации сессии и дайте ему случайную строку для идентификатора сессии
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// или вы можете вручную переопределить параметры конфигурации
		$session->updateConfiguration([
			// если вы хотите хранить данные своей сессии в базе данных (хорошо, если вы хотите, чтобы что-то вроде "выйти из всех устройств")
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // пожалуйста, измените это на что-то другое
			Session::CONFIG_AUTO_COMMIT   => true, // делайте это только если это требуется и/или трудно вызвать commit() для вашей сессии.
												   // дополнительно вы можете сделать Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Драйвер базы данных для PDO dns, например (mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Хост базы данных
				'db_name'   => 'my_app_database',   # Имя базы данных
				'db_table'  => 'sessions',          # Таблица базы данных
				'db_user'   => 'root',              # Имя пользователя базы данных
				'db_pass'   => '',                  # Пароль базы данных
				'persistent_conn'=> false,          # Избегайте накладных расходов на установление нового соединения каждый раз, когда скрипту необходимо взаимодействовать с базой данных, что приводит к более быстрому веб-приложению. НИЖНЯЯ СТОРОНА ВЫ БУДЕТЕ ИСКАТЬ САМИ
			]
		]);
	}
);
```

## Помогите! Мои данные сессии не сохраняются!

Вы устанавливаете данные своей сессии, и они не сохраняются между запросами? Возможно, вы забыли зафиксировать данные вашей сессии. Вы можете сделать это, вызвав `$session->commit()` после того, как установили данные вашей сессии.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// выполните вашу логику входа здесь
	// проверьте пароль и т.д.

	// если вход успешен
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// всякий раз, когда вы записываете в сессию, вы должны фиксировать это намеренно.
	$session->commit();
});
```

Другой способ обойти это - когда вы настраиваете свой сервис сессий, вы должны установить `auto_commit` в `true` в вашей конфигурации. Это автоматически зафиксирует данные вашей сессии после каждого запроса.

```php

$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Кроме того, вы можете сделать `Flight::after('start', function() { Flight::session()->commit(); });`, чтобы фиксировать данные вашей сессии после каждого запроса.

## Документация

Посетите [Github Readme](https://github.com/Ghostff/Session) для полной документации. Параметры конфигурации [хорошо задокументированы в default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) файле. Код прост для понимания, если вы хотите просмотреть этот пакет сами.