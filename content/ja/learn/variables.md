# 変数

Flight は、変数を保存してアプリケーション内のどこでも使用できるようにします。

```php
// あなたの変数を保存する
Flight::set('id', 123);

// アプリケーションの別の場所で
$id = Flight::get('id');
```
変数が設定されているかどうかを確認するには、次のようにします。

```php
if (Flight::has('id')) {
  // 何かを実行
}
```

変数をクリアするには、次のようにします。

```php
// id 変数をクリア
Flight::clear('id');

// すべての変数をクリア
Flight::clear();
```

Flight は設定目的で変数も使用します。

```php
Flight::set('flight.log_errors', true);
```