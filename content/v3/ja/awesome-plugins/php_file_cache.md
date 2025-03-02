# flightphp/cache

軽量でシンプルなスタンドアロンPHPインファイルキャッシュクラス

**利点**  
- 軽量でスタンドアロン、シンプル
- すべてのコードが1つのファイルに - 無駄なドライバーなし
- セキュア - 生成されるすべてのキャッシュファイルにはdieを含むPHPヘッダーが含まれており、パスを知っていても直接アクセスが不可能
- 良好なドキュメントとテスト済み
- flockを介して同時実行を正しく処理
- PHP 7.4+をサポート
- MITライセンスの下で無料

このドキュメントサイトは、このライブラリを使用して各ページをキャッシュしています！

コードを表示するには[こちら](https://github.com/flightphp/cache)をクリックしてください。

## インストール

composerを介してインストール：

```bash
composer require flightphp/cache
```

## 使用法

使用法は非常に簡単です。これはキャッシュディレクトリにキャッシュファイルを保存します。

```php
use flight\Cache;

$app = Flight::app();

// キャッシュが保存されるディレクトリをコンストラクタに渡します
$app->register('cache', Cache::class, [ __DIR__ . '/../cache/' ], function(Cache $cache) {

	// キャッシュはプロダクションモードのときのみ使用されることを保証します
	// ENVIRONMENTはブートストラップファイルまたはアプリ内の他の場所で設定される定数です
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

次のようにコード内で使用できます：

```php

// キャッシュインスタンスを取得
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

## ドキュメンテーション

完全なドキュメンテーションについては[https://github.com/flightphp/cache](https://github.com/flightphp/cache)をご覧いただき、[examples](https://github.com/flightphp/cache/tree/master/examples)フォルダーを必ず確認してください。