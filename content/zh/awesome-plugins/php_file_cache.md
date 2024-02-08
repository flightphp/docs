# Wruczek/PHP-File-Cache

轻量、简单且独立的PHP内部文件缓存类

**优势**
- 轻巧、独立且简单
- 所有代码都在一个文件中 - 没有多余的驱动程序。
- 安全性 - 每个生成的缓存文件都有一个带有die的php头部，即使有人知道路径并且您的服务器配置不正确，也无法直接访问
- 文档完善，经过测试
- 通过flock正确处理并发
- 支持PHP 5.4.0 - 7.1+
- 在MIT许可下免费

## 安装

通过composer安装：

```bash
composer require wruczek/php-file-cache
```

## 用法

使用非常简单。

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// 将存储缓存的目录传递给构造函数
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// 确保只在生产模式下使用缓存
	// ENVIRONMENT是在您的引导文件或应用程序其他位置设置的常量
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

然后您可以像这样在代码中使用：

```php

// 获取缓存实例
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // 返回要缓存的数据
}, 10); // 10秒

// 或者
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10秒
}
```

## 文档

访问[https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache) 获取完整文档，并确保查看[examples](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples)文件夹。