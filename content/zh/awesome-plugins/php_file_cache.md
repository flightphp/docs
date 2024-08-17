# Wruczek/PHP-File-Cache

輕巧，簡單且獨立的PHP文件緩存類

**優勢**
- 輕巧，獨立且簡單
- 所有代碼在一個文件中 - 沒有多餘的驅動程序。
- 安全 - 每個生成的緩存文件都帶有帶有 die 的php頭部，即使有人知道路徑且您的服務器未正確配置，也無法直接訪問
- 良好的文檔和測試
- 通過 flock 正確處理並發
- 支持 PHP 5.4.0 - 7.1+
- 在 MIT 許可證下免費

點擊[這裡](https://github.com/Wruczek/PHP-File-Cache)查看代碼。

## 安裝

通過 composer 安裝：

```bash
composer require wruczek/php-file-cache
```

## 用法

使用非常簡單。

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// 將緩存存儲的目錄傳遞給構造函數
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// 這可以確保僅在生產模式下使用緩存
	// ENVIRONMENT 是在您的啟動文件或應用程序的其他地方設置的常數
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

然后您可以像這樣在代碼中使用它：

```php

// 獲取緩存實例
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // 返回要緩存的數據
}, 10); // 10 秒

// 或者
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 秒
}
```

## 文檔

訪問[https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache)查看完整文檔，並確保您查看[examples](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples)文件夾。