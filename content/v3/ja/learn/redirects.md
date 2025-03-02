# リダイレクト

`redirect` メソッドを使用して、新しい URL を指定して現在のリクエストをリダイレクトできます:

```php
Flight::redirect('/new/location');
```

Flight はデフォルトで HTTP 303 ステータスコードを送信します。オプションでカスタムコードを設定できます:

```php
Flight::redirect('/new/location', 401);
```