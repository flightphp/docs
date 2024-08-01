# 設定

`set` メソッドを使用して、Flight の特定の動作をカスタマイズできます。

```php
Flight::set('flight.log_errors', true);
```

## 利用可能な設定

以下は利用可能な設定の一覧です:

- **flight.base_url** `?string` - リクエストのベース URL をオーバーライドします。 (デフォルト: null)
- **flight.case_sensitive** `bool` - URL の大文字と小文字を区別します。 (デフォルト: false)
- **flight.handle_errors** `bool` - Flight がすべてのエラーを内部で処理できるようにします。 (デフォルト: true)
- **flight.log_errors** `bool` - エラーをウェブサーバーのエラーログファイルに記録します。 (デフォルト: false)
- **flight.views.path** `string` - ビューテンプレートファイルが含まれるディレクトリ。 (デフォルト: ./views)
- **flight.views.extension** `string` - ビューテンプレートファイルの拡張子。 (デフォルト: .php)
- **flight.content_length** `bool` - `Content-Length` ヘッダーを設定します。 (デフォルト: true)
- **flight.v2.output_buffering** `bool` - 旧バージョンの出力バッファリングを使用します。 [v3 へのマイグレーション](migrating-to-v3) を参照してください。 (デフォルト: false)

## ローダーの設定

`_` をクラス名に含める場合の追加のローダーの設定があります。これにより、クラスを自動的に読み込むことができます。

```php
// アンダースコアを使用したクラスのローディングを有効にする
// デフォルトは true
Loader::$v2ClassLoading = false;
```

## 変数

Flight を使用すると、アプリケーション内のどこからでも使用できるように変数を保存できます。

```php
// 変数を保存する
Flight::set('id', 123);

// アプリケーション内の別の場所で
$id = Flight::get('id');
```

変数が設定されているかどうかを確認するには、次のようにします:

```php
if (Flight::has('id')) {
  // 何かを行う
}
```

次のようにして変数をクリアできます:

```php
// id 変数をクリアする
Flight::clear('id');

// すべての変数をクリア
Flight::clear();
```

Flight は設定目的で変数も使用します。

```php
Flight::set('flight.log_errors', true);
```

## エラー処理

### エラーと例外

すべてのエラーと例外は Flight によってキャッチされ、`error` メソッドに渡されます。デフォルトの動作は、一般的な `HTTP 500 Internal Server Error` 応答を送信し、いくつかのエラー情報を含めることです。

独自のニーズに合わせてこの動作をオーバーライドできます:

```php
Flight::map('error', function (Throwable $error) {
  // エラーを処理する
  echo $error->getTraceAsString();
});
```

デフォルトでは、エラーはウェブサーバーにログ記録されていません。これを有効にできます:

```php
Flight::set('flight.log_errors', true);
```

### 見つからない場合

URL が見つからない場合、Flight は `notFound` メソッドを呼び出します。デフォルトの動作は、簡単なメッセージを含む `HTTP 404 Not Found` 応答を送信することです。

独自のニーズに合わせてこの動作をオーバーライドできます:

```php
Flight::map('notFound', function () {
  // 見つからなかった時の処理
});
```