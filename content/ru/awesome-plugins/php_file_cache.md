# Wruczek/PHP-File-Cache

Легкий, простой и автономный класс кэширования PHP в файле

**Преимущества** 
- Легкий, автономный и простой
- Весь код в одном файле - нет бесполезных драйверов.
- Безопасный - каждый созданный файл кэша имеет заголовок php с die, делая прямой доступ невозможным даже если кто-то знает путь и сервер не настроен правильно
- Хорошо документирован и протестирован
- Правильно обрабатывает параллельность через flock
- Поддерживает PHP 5.4.0 - 7.1+
- Бесплатный под лицензией MIT

Нажмите [здесь](https://github.com/Wruczek/PHP-File-Cache) для просмотра кода.

## Установка

Установите через composer:

```bash
composer require wruczek/php-file-cache
```

## Использование

Использование довольно просто.

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// Вы передаете каталог, в котором будет сохранен кэш, в конструктор
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// Это гарантирует, что кэш используется только в режиме продакшн
	// ENVIRONMENT - это константа, которая устанавливается в вашем файле запуска или в другом месте в вашем приложении
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Затем вы можете использовать его в своем коде следующим образом:

```php

// Получить экземпляр кэша
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // возвращает данные для кэширования
}, 10); // 10 секунд

// или
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 секунд
}
```

## Документация

Посетите [https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache) для полной документации и убедитесь, что вы посмотрите папку [examples](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples).