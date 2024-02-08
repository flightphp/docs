# オーバーライド

Flightは、コードを変更することなく、独自のニーズに合わせてデフォルトの機能をオーバーライドできるようにします。

たとえば、FlightがURLをルートにマッチさせられない場合、`notFound`メソッドが呼び出され、一般的な `HTTP 404` レスポンスが送信されます。この動作を以下のようにオーバーライドできます：

```php
Flight::map('notFound', function() {
  // カスタム404ページを表示
  include 'errors/404.html';
});
```

Flightはまた、フレームワークのコアコンポーネントを置換することを許可します。
たとえば、デフォルトのRouterクラスを独自のカスタムクラスで置き換えることができます：

```php
// カスタムクラスを登録
Flight::register('router', MyRouter::class);

// FlightがRouterインスタンスをロードするとき、あなたのクラスがロードされます
$myrouter = Flight::router();
```

`map`や`register`などのフレームワークのメソッドはオーバーライドできません。これを試みるとエラーが発生します。