# flightphp/cache

Легка, проста та автономна клас кешування PHP у файлі

**Переваги** 
- Легка, автономна та проста
- Увесь код в одному файлі - без безглуздих драйверів.
- Безпечна - кожен згенерований кеш-файл має заголовок php з die, що унеможливлює прямий доступ, навіть якщо хтось знає шлях і ваш сервер неправильно налаштований
- Гарно документована та протестована
- Коректно обробляє конкуренцію через flock
- Підтримує PHP 7.4+
- Безкоштовно за ліцензією MIT

Цей сайт документації використовує цю бібліотеку для кешування кожної зі сторінок!

Натисніть [тут](https://github.com/flightphp/cache), щоб переглянути код.

## Встановлення

Встановіть через composer:

```bash
composer require flightphp/cache
```

## Використання

Використання досить просте. Це зберігає кеш-файл у каталозі кешу.

```php
use flight\Cache;

$app = Flight::app();

// Ви передаєте каталог, в якому буде зберігатися кеш, у конструктор
$app->register('cache', Cache::class, [ __DIR__ . '/../cache/' ], function(Cache $cache) {

	// Це забезпечує, що кеш використовується лише в режимі виробництва
	// ENVIRONMENT є константою, яка встановлюється у вашому файлі завантаження або в іншому місці вашого додатку
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Потім ви можете використовувати його у своєму коді так:

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

Відвідайте [https://github.com/flightphp/cache](https://github.com/flightphp/cache) для повної документації та обов'язково подивіться папку [приклади](https://github.com/flightphp/cache/tree/master/examples).