# flightphp/cache

軽量でシンプルでスタンドアロンの PHP インファイルキャッシュクラス

**利点**
- 軽量でスタンドアロンでシンプル
- コードが1つのファイルにすべて含まれている - 無駄なドライバなし。
- セキュア - 生成されたキャッシュファイルには死を含む PHP ヘッダーがあり、誰かがパスを知っていても直接アクセスできないようにしている、サーバーが適切に構成されていない場合でも
- よく文書化されており、テストされている
- flock によって同時実行性を正しく処理
- PHP 7.4+ をサポート
- MIT ライセンスの下で無料

このドキュメントサイトは、各ページをキャッシュするためにこのライブラリを使用しています！

コードを見るには [こちら](https://github.com/flightphp/cache) をクリックしてください。

## インストール

Composer を使ってインストール：

```bash
composer require flightphp/cache
```

## 使い方

使い方は非常に簡単です。これはキャッシュディレクトリにキャッシュファイルを保存します。

```php
use flight\Cache;

$app = Flight::app();

// キャッシュが保存されるディレクトリをコンストラクタに渡します
$app->register('cache', Cache::class, [ __DIR__ . '/../cache/' ], function(Cache $cache) {

	// これにより、キャッシュは本番モードのときだけ使用されることが保証されます
	// ENVIRONMENT は、お使いのブートストラップファイルまたはアプリの他の場所で設定される定数です
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

次のようにコード内で使用できます：

```php

// キャッシュインスタンスを取得
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // キャッシュするデータを返します
}, 10); // 10 秒

// または
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 秒
}
```

## ドキュメント

完全なドキュメントについては [https://github.com/flightphp/cache](https://github.com/flightphp/cache) を訪問し、[examples](https://github.com/flightphp/cache/tree/master/examples) フォルダーも確認してください。