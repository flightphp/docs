# 設定

特定の動作をカスタマイズするには、`set` メソッドを使用して Flight の設定値を設定できます。

```php
Flight::set('flight.log_errors', true);
```

## 利用可能な設定

以下は利用可能なすべての設定のリストです:

- **flight.base_url** `?string` - リクエストのベースURLを上書きします。(デフォルト: null)
- **flight.case_sensitive** `bool` - URLの大文字小文字を区別するかどうか。 (デフォルト: false)
- **flight.handle_errors** `bool` - Flight がすべてのエラーを内部で処理するかどうか。 (デフォルト: true)
- **flight.log_errors** `bool` - エラーをウェブサーバーのエラーログファイルに記録します。 (デフォルト: false)
- **flight.views.path** `string` - ビューテンプレートファイルが含まれるディレクトリ。 (デフォルト: ./views)
- **flight.views.extension** `string` - ビューテンプレートファイルの拡張子。 (デフォルト: .php)
- **flight.content_length** `bool` - `Content-Length` ヘッダーを設定します。 (デフォルト: true)
- **flight.v2.output_buffering** `bool` - 旧バージョンの出力バッファリングを使用します。[v3 への移行](migrating-to-v3) を参照してください。 (デフォルト: false)

## 変数

Flight では、変数を保存してアプリケーションのどこでも使用できるようにすることができます。

```php
// 変数を保存する
Flight::set('id', 123);

// アプリケーションの他の場所で
$id = Flight::get('id');
```

変数が設定されているかどうかを確認するには、次のようにします:

```php
if (Flight::has('id')) {
  // 何かを行う
}
```

変数をクリアするには、次のようにします:

```php
// id変数をクリアする
Flight::clear('id');

// すべての変数をクリアする
Flight::clear();
```

Flight は設定目的で変数も使用します。

```php
Flight::set('flight.log_errors', true);
```

## エラー処理

### エラーと例外

すべてのエラーと例外は Flight によってキャッチされ、`error` メソッドに渡されます。
デフォルトの動作は、一般的な `HTTP 500 Internal Server Error` 応答を送信し、一部のエラー情報を含めます。

これを独自のニーズに合わせて上書きできます:

```php
Flight::map('error', function (Throwable $error) {
  // エラーを処理する
  echo $error->getTraceAsString();
});
```

デフォルトでは、エラーはウェブサーバーのエラーログに記録されません。これを有効にするには
設定を変更します:

```php
Flight::set('flight.log_errors', true);
```

### 見つからない場合

URL が見つからない場合、Flight は `notFound` メソッドを呼び出します。デフォルトの動作は、
簡単なメッセージを含む `HTTP 404 Not Found` 応答を送信することです。

これを独自のニーズに合わせて上書きできます:

```php
Flight::map('notFound', function () {
  // 見つからない場合の処理
});
```