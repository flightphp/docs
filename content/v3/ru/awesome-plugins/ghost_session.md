# Ghostff/Session

PHP Менеджер сессий (неблокирующий, flash, segment, шифрование сессий). Использует PHP open_ssl для необязательного шифрования/дешифрования данных сессий. Поддерживает File, MySQL, Redis и Memcached.

Нажмите [здесь](https://github.com/Ghostff/Session), чтобы просмотреть код.

## Установка

Установите с помощью composer.

```bash
composer require ghostff/session
```

## Основная конфигурация

Вам не обязательно передавать что-либо для использования настроек по умолчанию для вашей сессии. Вы можете прочитать о дополнительных настройках в [Github Readme](https://github.com/Ghostff/Session).

```php
use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// одна вещь, которую следует помнить, это то, что вы должны фиксировать свою сессию на каждой загрузке страницы
// или вам нужно будет запустить auto_commit в вашей конфигурации. 
```

## Простой пример

Вот простой пример того, как вы можете использовать это.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// выполните здесь вашу логику входа
	// проверьте пароль и т.д.

	// если вход успешен
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// каждый раз, когда вы пишете в сессию, вы должны явно зафиксировать её.
	$session->commit();
});

// Эта проверка может быть в логике ограниченной страницы или обернута в промежуточное ПО.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// выполните здесь логику ограниченной страницы
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

// установите пользовательский путь к файлу конфигурации сессии в качестве первого аргумента
// или передайте ему пользовательский массив
$app->register('session', Session::class, [ 
	[
		// если вы хотите хранить данные сессии в базе данных (хорошо, если вы хотите что-то вроде функциональности "выйти из всех устройств")
		Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
		Session::CONFIG_ENCRYPT_DATA  => true,
		Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // пожалуйста, измените это на что-то другое
		Session::CONFIG_AUTO_COMMIT   => true, // делайте это только если это требует и/или трудно вызвать commit() для вашей сессии.
												// кроме того, вы могли бы сделать Flight::after('start', function() { Flight::session()->commit(); });
		Session::CONFIG_MYSQL_DS         => [
			'driver'    => 'mysql',             # Драйвер базы данных для PDO dns, например (mysql:host=...;dbname=...)
			'host'      => '127.0.0.1',         # Хост базы данных
			'db_name'   => 'my_app_database',   # Имя базы данных
			'db_table'  => 'sessions',          # Таблица базы данных
			'db_user'   => 'root',              # Имя пользователя базы данных
			'db_pass'   => '',                  # Пароль базы данных
			'persistent_conn'=> false,          # Избегайте накладных расходов на установку нового соединения каждый раз, когда скрипту нужно общаться с базой данных, что приводит к более быстрому веб-приложению. НАЙДИТЕ ОБРАТНУЮ СТОРОНУ САМИ
		]
	] 
]);
```

## Помощь! Мои данные сессии не сохраняются!

Вы устанавливаете данные сессии, и они не сохраняются между запросами? Вы, возможно, забыли зафиксировать данные сессии. Вы можете сделать это, вызвав `$session->commit()` после установки данных сессии.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// выполните здесь вашу логику входа
	// проверьте пароль и т.д.

	// если вход успешен
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// каждый раз, когда вы пишете в сессию, вы должны явно зафиксировать её.
	$session->commit();
});
```

Другой способ обойти это — при настройке службы сессии установить `auto_commit` в `true` в вашей конфигурации. Это автоматически зафиксирует данные сессии после каждого запроса.

```php
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Кроме того, вы могли бы сделать `Flight::after('start', function() { Flight::session()->commit(); });`, чтобы зафиксировать данные сессии после каждого запроса.

## Документация

Посетите [Github Readme](https://github.com/Ghostff/Session) для полной документации. Варианты конфигурации [хорошо задокументированы в файле default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) самом по себе. Код прост в понимании, если вы захотите просмотреть этот пакет самостоятельно.