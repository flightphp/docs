# Wruczek/PHP-File-Cache

Легка, проста та самостійна PHP клас кешування файлів

**Переваги** 
- Легка, самостійна та проста
- Увесь код в одному файлі - без зайвих драйверів.
- Захищена - кожен згенерований файл кешу має заголовок php з die, що робить прямий доступ неможливим, навіть якщо хтось знає шлях і ваш сервер не налаштований належним чином
- Добре документована та протестована
- Коректно обробляє одночасність за допомогою flock
- Підтримує PHP 5.4.0 - 7.1+
- Безкоштовна під ліцензією MIT

Натисніть [тут](https://github.com/Wruczek/PHP-File-Cache), щоб переглянути код.

## Встановлення

Встановіть за допомогою composer:

```bash
composer require wruczek/php-file-cache
```

## Використання

Використання досить просте.

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// Ви передаєте директорію, в якій буде зберігатися кеш, в конструктор
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// Це забезпечує, що кеш використовується лише в продуктивному режимі
	// ENVIRONMENT - це константа, яка встановлюється у вашому bootstrap файлі або в іншому місці вашого додатку
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Тоді ви можете використовувати це у своєму коді так:

```php

// Отримати екземпляр кешу
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // повернути дані для кешування
}, 10); // 10 секунд

// або
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 секунд
}
```

## Документація

Відвідайте [https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache) для повної документації і обов'язково перегляньте папку [прикладів](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples).