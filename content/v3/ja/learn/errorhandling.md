# エラー処理

## エラーと例外

Flight によってすべてのエラーや例外がキャッチされ、`error` メソッドに渡されます。
デフォルトの動作は、いくつかのエラー情報を含む汎用 `HTTP 500 内部サーバーエラー` 応答を送信することです。

独自のニーズに合わせてこの動作を上書きすることができます:

```php
Flight::map('error', function (Throwable $error) {
  // エラーを処理する
  echo $error->getTraceAsString();
});
```

デフォルトでは、エラーはウェブサーバーに記録されません。これを有効にすることでログを取得できます:

```php
Flight::set('flight.log_errors', true);
```

## 見つかりません

URL が見つからない場合、Flight は `notFound` メソッドを呼び出します。デフォルトの動作は、簡単なメッセージを含む `HTTP 404 Not Found` 応答を送信することです。

独自のニーズに合わせてこの動作を上書きすることができます:

```php
Flight::map('notFound', function () {
  // 見つからない場合の処理
});
```