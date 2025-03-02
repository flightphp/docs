```ja
# フレームワーク API メソッド

Flight は使いやすく理解しやすいように設計されています。以下はフレームワークの完全なメソッドセットです。
これには、通常の静的メソッドであるコアメソッドと、フィルタリングやオーバーライドが可能なマップされたメソッドである拡張可能メソッドが含まれています。

## コアメソッド

これらのメソッドはフレームワークのコアであり、オーバーライドすることはできません。

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // カスタムフレームワークメソッドを作成します。
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // クラスをフレームワークメソッドに登録します。
Flight::unregister(string $name) // クラスのフレームワークメソッド登録を解除します。
Flight::before(string $name, callable $callback) // フレームワークメソッドの前にフィルタを追加します。
Flight::after(string $name, callable $callback) // フレームワークメソッドの後にフィルタを追加します。
Flight::path(string $path) // クラスの自動読み込みのためのパスを追加します。
Flight::get(string $key) // Flight::set() によって設定された変数を取得します。
Flight::set(string $key, mixed $value) // Flight エンジン内で変数を設定します。
Flight::has(string $key) // 変数が設定されているかどうかを確認します。
Flight::clear(array|string $key = []) // 変数をクリアします。
Flight::init() // フレームワークをデフォルト設定に初期化します。
Flight::app() // アプリケーションオブジェクトインスタンスを取得します。
Flight::request() // リクエストオブジェクトインスタンスを取得します。
Flight::response() // レスポンスオブジェクトインスタンスを取得します。
Flight::router() // ルーターオブジェクトインスタンスを取得します。
Flight::view() // ビューオブジェクトインスタンスを取得します。
```

## 拡張可能メソッド

```php
Flight::start() // フレームワークを開始します。
Flight::stop() // フレームワークを停止し、レスポンスを送信します。
Flight::halt(int $code = 200, string $message = '') // オプションのステータスコードとメッセージでフレームワークを停止します。
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // URL パターンをコールバックにマップします。
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // POST リクエストの URL パターンをコールバックにマップします。
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // PUT リクエストの URL パターンをコールバックにマップします。
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // PATCH リクエストの URL パターンをコールバックにマップします。
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // DELETE リクエストの URL パターンをコールバックにマップします。
Flight::group(string $pattern, callable $callback) // URL のグループ化を作成します。パターンは文字列である必要があります。
Flight::getUrl(string $name, array $params = []) // ルートエイリアスに基づいた URL を生成します。
Flight::redirect(string $url, int $code) // 別の URL にリダイレクトします。
Flight::download(string $filePath) // ファイルをダウンロードします。
Flight::render(string $file, array $data, ?string $key = null) // テンプレートファイルをレンダリングします。
Flight::error(Throwable $error) // HTTP 500 レスポンスを送信します。
Flight::notFound() // HTTP 404 レスポンスを送信します。
Flight::etag(string $id, string $type = 'string') // ETag HTTP キャッシュを実行します。
Flight::lastModified(int $time) // 最終変更日 HTTP キャッシュを実行します。
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSON レスポンスを送信します。
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSONP レスポンスを送信します。
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSON レスポンスを送信し、フレームワークを停止します。
```

`map` と `register` で追加されたカスタムメソッドは、フィルタリングすることもできます。
これらのメソッドをどのようにマップするかの例については、[Extending Flight](/learn/extending) ガイドを参照してください。
```