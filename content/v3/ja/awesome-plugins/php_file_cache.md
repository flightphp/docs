# flightphp/cache

[Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache) からフォークされた、軽量でシンプルなスタンドアロンのPHPインファイルキャッシングクラス

**利点** 
- 軽量でスタンドアロンでシンプル
- すべてのコードが1つのファイル内 - 無駄なドライバなし。
- セキュア - 生成されるすべてのキャッシュファイルにPHPヘッダーとdieが含まれており、パスを知っていてもサーバーが正しく設定されていない場合でも直接アクセスを不可能にします
- よく文書化され、テスト済み
- flock を使用して同時実行を正しく処理
- PHP 7.4+ をサポート
- MITライセンスの下で無料

このドキュメントサイトはこのライブラリを使用して各ページをキャッシュしています！

コードを見るには [here](https://github.com/flightphp/cache) をクリックしてください。

## インストール

Composer を使用してインストール：

```bash
composer require flightphp/cache
```

## 使い方

使い方はかなりストレートフォワードです。これによりキャッシュディレクトリにキャッシュファイルが保存されます。

```php
use flight\Cache;

$app = Flight::app();

// コンストラクタにキャッシュが保存されるディレクトリを渡します
$app->register('cache', Cache::class, [ __DIR__ . '/../cache/' ], function(Cache $cache) {

	// これにより、プロダクションモードでのみキャッシュが使用されることを保証します
	// ENVIRONMENT はブートストラップファイルやアプリの他の場所で設定される定数です
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

### キャッシュ値を取得

`get()` メソッドを使用してキャッシュされた値を取得します。期限切れの場合にキャッシュを更新する便利なメソッドが必要な場合は、`refreshIfExpired()` を使用できます。

```php

// キャッシュインスタンスを取得
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // キャッシュするデータを返す
}, 10); // 10秒

// または
$data = $cache->get('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->set('simple-cache-test', $data, 10); // 10秒
}
```

### キャッシュ値を保存

`set()` メソッドを使用してキャッシュに値を保存します。

```php
Flight::cache()->set('simple-cache-test', 'my cached data', 10); // 10秒
```

### キャッシュ値を消去

`delete()` メソッドを使用してキャッシュから値を消去します。

```php
Flight::cache()->delete('simple-cache-test');
```

### キャッシュ値の存在を確認

`exists()` メソッドを使用してキャッシュに値が存在するかを確認します。

```php
if(Flight::cache()->exists('simple-cache-test')) {
	// 何かを実行
}
```

### キャッシュをクリア
`flush()` メソッドを使用してキャッシュ全体をクリアします。

```php
Flight::cache()->flush();
```

### キャッシュのメタデータを取得

キャッシュエントリのタイムスタンプやその他のメタデータを取得したい場合は、正しいパラメータとして `true` を渡してください。

```php
$data = $cache->refreshIfExpired("simple-cache-meta-test", function () {
    echo "Refreshing data!" . PHP_EOL;
    return date("H:i:s"); // キャッシュするデータを返す
}, 10, true); // true = メタデータ付きで返す
// または
$data = $cache->get("simple-cache-meta-test", true); // true = メタデータ付きで返す

/*
メタデータ付きで取得したキャッシュアイテムの例:
{
    "time":1511667506, <-- 保存時のUnixタイムスタンプ
    "expire":10,       <-- 秒単位の有効期限
    "data":"04:38:26", <-- 逆シリアライズされたデータ
    "permanent":false
}

メタデータを使用して、例えばアイテムが保存された時刻や有効期限を計算できます
また、"data" キーでデータ自体にアクセスできます
*/

$expiresin = ($data["time"] + $data["expire"]) - time(); // データの有効期限のUnixタイムスタンプを取得し、現在のタイムスタンプを引く
$cacheddate = $data["data"]; // "data" キーでデータ自体にアクセス

echo "Latest cache save: $cacheddate, expires in $expiresin seconds";
```

## ドキュメント

コードを見るには [https://github.com/flightphp/cache](https://github.com/flightphp/cache) を訪れてください。キャッシュの使用方法の追加例については [examples](https://github.com/flightphp/cache/tree/master/examples) フォルダを確認してください。