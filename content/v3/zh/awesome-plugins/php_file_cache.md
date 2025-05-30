# flightphp/cache

轻量、简单且独立的 PHP 文件缓存类

**优点** 
- 轻量、独立且简单
- 所有代码在一个文件中 - 没有无意义的驱动程序。
- 安全 - 每个生成的缓存文件都有带有 die 的 PHP 头，即使有人知道路径并且您的服务器配置不正确，也无法直接访问
- 文档齐全且经过测试
- 通过 flock 正确处理并发
- 支持 PHP 7.4+
- 在 MIT 许可下免费

此文档网站正在使用此库来缓存每个页面！

点击 [这里](https://github.com/flightphp/cache) 查看代码。

## 安装

通过 composer 安装：

```bash
composer require flightphp/cache
```

## 使用

使用相当简单。这样会在缓存目录中保存缓存文件。

```php
use flight\Cache;

$app = Flight::app();

// 您将将缓存存储的目录传递给构造函数
$app->register('cache', Cache::class, [ __DIR__ . '/../cache/' ], function(Cache $cache) {

	// 这确保只有在生产模式下才使用缓存
	// ENVIRONMENT 是在引导文件或应用的其他地方设置的常量
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

然后您可以像这样在代码中使用它：

```php

// 获取缓存实例
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // 返回要缓存的数据
}, 10); // 10 秒

// 或
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 秒
}
```

## 文档

访问 [https://github.com/flightphp/cache](https://github.com/flightphp/cache) 获取完整文档，并确保查看 [examples](https://github.com/flightphp/cache/tree/master/examples) 文件夹。