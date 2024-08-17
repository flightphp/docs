# Wruczek/PHP-File-Cache

軽量でシンプルかつスタンドアロンのPHPインファイルキャッシングクラス

**利点**
- 軽量でスタンドアロンかつシンプル
- すべてのコードが1つのファイルに含まれています - 無駄なドライバーはありません。
- セキュア - 生成されたキャッシュファイルごとにdieを含むphpヘッダーがあるため、直接アクセスが不可能です。たとえ誰かがパスを知っていても、サーバーが適切に構成されていない場合でも
- 十分に文書化され、テストされています
- flockを介して適切に並行性を処理します
- PHP 5.4.0 - 7.1+をサポート
- MITライセンスの下で無料

コードを表示するには[ここ](https://github.com/Wruczek/PHP-File-Cache)をクリックしてください。

## インストール

Composerを介してインストールする：

```bash
composer require wruczek/php-file-cache
```

## 使用法

使用法は非常に簡単です。

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// キャッシュが保存されるディレクトリをコンストラクタに渡します
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// これにより、キャッシュは本番モードでのみ使用されることが保証されます
	// ENVIRONMENTは、ブートストラップファイルやアプリ内の他の場所で設定されている定数です
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

その後、次のようにコードで使用できます：

```php

// キャッシュインスタンス取得
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // キャッシュするデータを返します
}, 10); // 10秒

// または
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10秒
}
```

## ドキュメント

完全なドキュメントを確認するには[https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache)を訪れ、[examples](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples)フォルダをご覧ください。