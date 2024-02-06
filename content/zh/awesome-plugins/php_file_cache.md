# Wruczek/PHP-File-Cache

轻量、简单且独立的 PHP 内置缓存类

**优势**
- 轻量、独立且简单
- 所有代码都在一个文件中 - 没有无用的驱动程序。
- 安全 - 每个生成的缓存文件都有一个带有 die 的 php 头文件，即使有人知道路径，并且您的服务器配置不正确，也无法直接访问
- 文档完善，经过测试
- 通过 flock 正确处理并发性
- 支持 PHP 5.4.0 - 7.1+
- 采用 MIT 许可证免费提供

## 安装

通过 Composer 安装:

```bash
composer require wruczek/php-file-cache
```

## 用法

用法非常简单。

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// 将要存储缓存的目录传递给构造函数
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// 确保只在生产模式下使用缓存
	// ENVIRONMENT 是在您的引导文件或应用程序其他位置设置的常量
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

然后您可以这样在代码中使用：

```php

// 获取缓存实例
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // 返回要缓存的数据
}, 10); // 10 秒

// 或者
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 秒
}
```

## 文档

访问 [https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache) 获取完整文档，并确保查看 [examples](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples) 文件夹。