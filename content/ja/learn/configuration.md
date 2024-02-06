# 設定

あなたは、`set` メソッドを通じて設定値を設定することで、Flight の特定の動作をカスタマイズすることができます。

```php
Flight::set('flight.log_errors', true);
```

以下は利用可能なすべての設定のリストです：

- **flight.base_url** - リクエストのベース URL を上書きします。(デフォルト: null)
- **flight.case_sensitive** - URL の大文字小文字を区別します。(デフォルト: false)
- **flight.handle_errors** - Flight にすべてのエラーを内部で処理させることを許可します。(デフォルト: true)
- **flight.log_errors** - エラーを web サーバーのエラーログファイルに記録します。(デフォルト: false)
- **flight.views.path** - ビューテンプレートファイルを含むディレクトリです。(デフォルト: ./views)
- **flight.views.extension** - ビューテンプレートファイルの拡張子です。(デフォルト: .php)