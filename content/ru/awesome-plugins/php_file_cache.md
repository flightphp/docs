# Wruczek/PHP-File-Cache

Просветитель, простой и автономный класс кеширования PHP в файле

**Преимущества**
- Легкий, автономный и простой
- Весь код в одном файле - никаких бесполезных драйверов.
- Безопасный - каждый созданный файл кеша содержит заголовок php с die, что делает невозможным прямой доступ даже если кто-то знает путь и ваш сервер неправильно настроен
- Хорошо документирован и протестирован
- Правильно обрабатывает параллельность с помощью flock
- Поддерживает PHP 5.4.0 - 7.1+
- Бесплатно под MIT лицензией

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

// Вы передаете каталог, в котором будет храниться кеш, в конструктор
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// Это гарантирует, что кеш используется только в режиме продакшн
	// ENVIRONMENT - это константа, которая задается в вашем файле инициализации или где-то еще в вашем приложении
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Затем вы можете использовать его в своем коде так:

```php

// Получить экземпляр кеша
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // возвращаемые данные для кеширования
}, 10); // 10 секунд

// или
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 секунд
}
```

## Документация

Посетите [https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache) для полной документации и убедитесь, что вы посмотрите папку [примеры](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples).