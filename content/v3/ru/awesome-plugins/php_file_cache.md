# flightphp/cache

Легкий, простой и автономный класс PHP для кэширования в файле, форкнутый из [Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache)

**Преимущества** 
- Легкий, автономный и простой
- Весь код в одном файле - никаких бесполезных драйверов.
- Безопасный - каждый сгенерированный файл кэша имеет заголовок PHP с die, что делает прямой доступ невозможным, даже если кто-то знает путь и ваш сервер не настроен правильно
- Хорошо документированный и протестированный
- Правильно обрабатывает параллелизм через flock
- Поддерживает PHP 7.4+
- Бесплатный под лицензией MIT

Этот сайт документации использует эту библиотеку для кэширования каждой из страниц!

Нажмите [здесь](https://github.com/flightphp/cache), чтобы просмотреть код.

## Установка

Установите через composer:

```bash
composer require flightphp/cache
```

## Использование

Использование довольно прямолинейное. Это сохраняет файл кэша в директории кэша.

```php
use flight\Cache;

$app = Flight::app();

// Вы передаете директорию, в которой будет храниться кэш, в конструктор
$app->register('cache', Cache::class, [ __DIR__ . '/../cache/' ], function(Cache $cache) {

	// Это гарантирует, что кэш используется только в режиме производства
	// ENVIRONMENT - это константа, которая устанавливается в вашем файле bootstrap или в другом месте вашего приложения
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

### Получить значение кэша

Вы используете метод `get()` для получения закэшированного значения. Если вы хотите удобный метод, который обновит кэш, если он истек, вы можете использовать `refreshIfExpired()`.

```php

// Получить экземпляр кэша
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // return data to be cached
}, 10); // 10 секунд

// или
$data = $cache->get('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->set('simple-cache-test', $data, 10); // 10 секунд
}
```

### Сохранить значение кэша

Вы используете метод `set()` для сохранения значения в кэше.

```php
Flight::cache()->set('simple-cache-test', 'my cached data', 10); // 10 секунд
```

### Удалить значение кэша

Вы используете метод `delete()` для удаления значения из кэша.

```php
Flight::cache()->delete('simple-cache-test');
```

### Проверить, существует ли значение кэша

Вы используете метод `exists()` для проверки, существует ли значение в кэше.

```php
if(Flight::cache()->exists('simple-cache-test')) {
	// do something
}
```

### Очистить кэш
Вы используете метод `flush()` для очистки всего кэша.

```php
Flight::cache()->flush();
```

### Извлечь метаданные с кэшем

Если вы хотите извлечь временные метки и другие метаданные о записи кэша, убедитесь, что вы передаете `true` в качестве соответствующего параметра.

```php
$data = $cache->refreshIfExpired("simple-cache-meta-test", function () {
    echo "Refreshing data!" . PHP_EOL;
    return date("H:i:s"); // return data to be cached
}, 10, true); // true = return with metadata
// или
$data = $cache->get("simple-cache-meta-test", true); // true = return with metadata

/*
Example cached item retrieved with metadata:
{
    "time":1511667506, <-- save unix timestamp
    "expire":10,       <-- expire time in seconds
    "data":"04:38:26", <-- unserialized data
    "permanent":false
}

Using metadata, we can, for example, calculate when item was saved or when it expires
We can also access the data itself with the "data" key
*/

$expiresin = ($data["time"] + $data["expire"]) - time(); // get unix timestamp when data expires and subtract current timestamp from it
$cacheddate = $data["data"]; // we access the data itself with the "data" key

echo "Latest cache save: $cacheddate, expires in $expiresin seconds";
```

## Документация

Посетите [https://github.com/flightphp/cache](https://github.com/flightphp/cache), чтобы просмотреть код. Убедитесь, что вы посмотрите папку [examples](https://github.com/flightphp/cache/tree/master/examples) для дополнительных способов использования кэша.