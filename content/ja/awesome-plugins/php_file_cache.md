# Wruczek/PHP-File-Cache

軽量でシンプルかつスタンドアロンのPHPインファイルキャッシュクラス

**利点**
- 軽量でスタンドアロンかつシンプル
- すべてのコードが1つのファイルにある - 無駄なドライバーはなし
- 安全性 - すべての生成されたキャッシュファイルにはphpのヘッダーがあり、直接アクセスは不可能になります（パスを知っていてもサーバーが適切に構成されていない場合でも）
- 十分に文書化されており、テストされています
- flockを介して正しく並行処理を処理します
- PHP 5.4.0 - 7.1+をサポート
- MITライセンスのもとで無料

## インストール

Composerを使用してインストール：

```bash
composer require wruczek/php-file-cache
```

## 使用法

使用法は非常に簡単です。

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// コンストラクタにキャッシュが保存されるディレクトリを渡します
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// これにより、キャッシュが本番モードでのみ使用されることが保証されます
	// ENVIRONMENTはブートストラップファイルや他の場所で設定される定数です
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

次に、次のようにコードで使用できます：

```php

// キャッシュインスタンスを取得
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // キャッシュされるデータを返す
}, 10); // 10秒

// または
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10秒
}
```

## ドキュメント

詳細なドキュメントについては、[https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache) を参照し、 [examples](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples) フォルダを確認してください。