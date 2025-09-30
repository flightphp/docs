# flightphp/cache

Легкий, простий і автономний PHP клас кешування в файлі, відгалужений від [Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache)

**Переваги** 
- Легкий, автономний і простий
- Увесь код в одному файлі - без зайвих драйверів.
- Безпечний - кожен згенерований файл кешу має PHP-заголовок з die, що робить прямий доступ неможливим, навіть якщо хтось знає шлях і ваш сервер не налаштований правильно
- Добре документований і протестований
- Правильно обробляє конкурентність через flock
- Підтримує PHP 7.4+
- Безкоштовний під ліцензією MIT

Цей сайт документації використовує цю бібліотеку для кешування кожної сторінки!

Натисніть [тут](https://github.com/flightphp/cache), щоб переглянути код.

## Встановлення

Встановіть через composer:

```bash
composer require flightphp/cache
```

## Використання

Використання досить просте. Це зберігає файл кешу в директорії кешу.

```php
use flight\Cache;

$app = Flight::app();

// Ви передаєте директорію, в якій буде зберігатися кеш, у конструктор
$app->register('cache', Cache::class, [ __DIR__ . '/../cache/' ], function(Cache $cache) {

	// Це забезпечує, що кеш використовується тільки в режимі виробництва
	// ENVIRONMENT - це константа, яка встановлюється у вашому файлі bootstrap або деінде у вашому додатку
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

### Отримання значення кешу

Ви використовуєте метод `get()` для отримання закешованого значення. Якщо ви хочете зручний метод, який оновить кеш, якщо він минув, ви можете використовувати `refreshIfExpired()`.

```php

// Отримати екземпляр кешу
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // повернути дані для кешування
}, 10); // 10 секунд

// або
$data = $cache->get('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->set('simple-cache-test', $data, 10); // 10 секунд
}
```

### Збереження значення кешу

Ви використовуєте метод `set()` для збереження значення в кеші.

```php
Flight::cache()->set('simple-cache-test', 'my cached data', 10); // 10 секунд
```

### Видалення значення кешу

Ви використовуєте метод `delete()` для видалення значення з кешу.

```php
Flight::cache()->delete('simple-cache-test');
```

### Перевірка існування значення кешу

Ви використовуєте метод `exists()` для перевірки, чи існує значення в кеші.

```php
if(Flight::cache()->exists('simple-cache-test')) {
	// зробити щось
}
```

### Очищення кешу
Ви використовуєте метод `flush()` для очищення всього кешу.

```php
Flight::cache()->flush();
```

### Витяг метаданих з кешу

Якщо ви хочете витягти мітки часу та інші метадані про запис кешу, переконайтеся, що ви передаєте `true` як правильний параметр.

```php
$data = $cache->refreshIfExpired("simple-cache-meta-test", function () {
    echo "Refreshing data!" . PHP_EOL;
    return date("H:i:s"); // повернути дані для кешування
}, 10, true); // true = повернути з метаданими
// або
$data = $cache->get("simple-cache-meta-test", true); // true = повернути з метаданими

/*
Приклад закершеного елемента, отриманого з метаданими:
{
    "time":1511667506, <-- збережений unix timestamp
    "expire":10,       <-- час вичерпання в секундах
    "data":"04:38:26", <-- десеріалізовані дані
    "permanent":false
}

Використовуючи метадані, ми можемо, наприклад, обчислити, коли елемент був збережений або коли він вичерпається
Ми також можемо отримати доступ до даних самих з ключа "data"
*/

$expiresin = ($data["time"] + $data["expire"]) - time(); // отримати unix timestamp, коли дані вичерпаються, і відняти поточний timestamp від нього
$cacheddate = $data["data"]; // ми отримуємо доступ до даних самих з ключа "data"

echo "Latest cache save: $cacheddate, expires in $expiresin seconds";
```

## Документація

Відвідайте [https://github.com/flightphp/cache](https://github.com/flightphp/cache), щоб переглянути код. Переконайтеся, що ви переглянули папку [examples](https://github.com/flightphp/cache/tree/master/examples) для додаткових способів використання кешу.