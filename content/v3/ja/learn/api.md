# フレームワークAPIメソッド

Flightは使いやすく、理解しやすいように設計されています。以下はフレームワークの完全なメソッドセットです。これは、通常の静的メソッドであるコアメソッドと、フィルタリングやオーバーライドが可能なマッピングメソッドである拡張可能メソッドで構成されています。

## コアメソッド

これらのメソッドはフレームワークのコアであり、オーバーライドすることはできません。

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // カスタムフレームワークメソッドを作成します。
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // フレームワークメソッドにクラスを登録します。
Flight::unregister(string $name) // フレームワークメソッドからクラスを登録解除します。
Flight::before(string $name, callable $callback) // フレームワークメソッドの前にフィルタを追加します。
Flight::after(string $name, callable $callback) // フレームワークメソッドの後にフィルタを追加します。
Flight::path(string $path) // クラスのオートローディング用のパスを追加します。
Flight::get(string $key) // Flight::set()によって設定された変数を取得します。
Flight::set(string $key, mixed $value) // Flightエンジン内で変数を設定します。
Flight::has(string $key) // 変数が設定されているかどうかをチェックします。
Flight::clear(array|string $key = []) // 変数をクリアします。
Flight::init() // フレームワークをデフォルト設定で初期化します。
Flight::app() // アプリケーションオブジェクトのインスタンスを取得します。
Flight::request() // リクエストオブジェクトのインスタンスを取得します。
Flight::response() // レスポンスオブジェクトのインスタンスを取得します。
Flight::router() // ルーターオブジェクトのインスタンスを取得します。
Flight::view() // ビューオブジェクトのインスタンスを取得します。
```

## 拡張可能メソッド

```php
Flight::start() // フレームワークを開始します。
Flight::stop() // フレームワークを停止し、レスポンスを送信します。
Flight::halt(int $code = 200, string $message = '') // オプションのステータスコードとメッセージとともにフレームワークを停止します。
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // URLパターンをコールバックにマッピングします。
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // POSTリクエストURLパターンをコールバックにマッピングします。
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // PUTリクエストURLパターンをコールバックにマッピングします。
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // PATCHリクエストURLパターンをコールバックにマッピングします。
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // DELETEリクエストURLパターンをコールバックにマッピングします。
Flight::group(string $pattern, callable $callback) // URLのグルーピングを作成します。パターンは文字列でなければなりません。
Flight::getUrl(string $name, array $params = []) // ルートエイリアスに基づいてURLを生成します。
Flight::redirect(string $url, int $code) // 別のURLにリダイレクトします。
Flight::download(string $filePath) // ファイルをダウンロードします。
Flight::render(string $file, array $data, ?string $key = null) // テンプレートファイルをレンダリングします。
Flight::error(Throwable $error) // HTTP 500レスポンスを送信します。
Flight::notFound() // HTTP 404レスポンスを送信します。
Flight::etag(string $id, string $type = 'string') // ETag HTTPキャッシュを実行します。
Flight::lastModified(int $time) // 最終更新のHTTPキャッシュを実行します。
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSONレスポンスを送信します。
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSONPレスポンスを送信します。
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSONレスポンスを送信し、フレームワークを停止します。
Flight::onEvent(string $event, callable $callback) // イベントリスナーを登録します。
Flight::triggerEvent(string $event, ...$args) // イベントをトリガーします。
```

`map`および`register`で追加された任意のカスタムメソッドもフィルタリングできます。これらのメソッドをマッピングする方法の例については、[Flightを拡張](/learn/extending)ガイドを参照してください。