# HTTP キャッシング

Flight は HTTP レベルのキャッシングを組み込みでサポートしています。キャッシングの条件を満たすと、Flight は HTTP `304 Not Modified` のレスポンスを返します。クライアントが同じリソースを次にリクエストするときは、ローカルにキャッシュされたバージョンを使用するよう促されます。

## Last-Modified

`lastModified` メソッドを使用して、UNIX タイムスタンプを渡すことでページが最後に変更された日時を設定できます。クライアントは最終変更日時の値が変更されるまで、キャッシュを引き続き使用します。

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'このコンテンツはキャッシュされます。';
});
```

## ETag

`ETag` キャッシングは `Last-Modified` と似ていますが、リソースに任意の ID を指定できます:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'このコンテンツはキャッシュされます。';
});
```

`lastModified` または `etag` のいずれかを呼び出すと、キャッシュの値が設定されてチェックされます。リクエスト間でキャッシュの値が同じ場合、Flight は直ちに `HTTP 304` レスポンスを送信して処理を停止します。