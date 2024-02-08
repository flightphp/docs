# Wruczek/PHP-File-Cache

軽量でシンプルでスタンドアロンのPHPインファイルキャッシュクラス

**利点**
- 軽量で、独立しておりシンプル
- すべてのコードが1つのファイルに含まれています - 不要なドライバーはありません。
- 安全 - すべての生成されたキャッシュファイルには、phpヘッダーとdieがあるため、直接アクセスが不可能です。誰かがパスを知っていても、サーバーが適切に構成されていない場合でも
- 良く文書化され、テスト済み
- flockを介して同時実行を正しく処理します
- PHP 5.4.0 - 7.1+をサポート
- MITライセンスの下で無料

## インストール

Composerを使用してインストール：

```bash
composer require wruczek/php-file-cache
```

## 使用法

使用法はかなり簡単です。

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// キャッシュが保存されるディレクトリをコンストラクタに渡します
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// これにより、キャッシュは本番モードでのみ使用されることが保証されます
	// ENVIRONMENTは、ブートストラップファイルやアプリの他の場所で設定された定数です
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

その後、次のようにコードで使用できます：

```php
// キャッシュインスタンスを取得
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // キャッシュされるデータを返します
}, 10); // 10秒

// または
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10秒
}
```

## ドキュメント

詳細なドキュメントについては、[https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache) をご覧ください。また、[examples](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples) フォルダを確認してください。