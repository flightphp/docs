# ルートミドルウェア

Flight はルートとグループルートミドルウェアをサポートしています。ミドルウェアは、ルートコールバックの前（または後）に実行される関数です。これは、コードに API 認証チェックを追加したり、ユーザーがルートにアクセスする権限を持っていることを検証したりする素晴らしい方法です。

## 基本ミドルウェア

以下は基本的な例です:

```php
// 無名関数のみを提供する場合、ルートコールバックの前に実行されます。
// クラスを除いて「後」のミドルウェア関数はありません（以下を参照）
Flight::route('/path', function() { echo 'ここにいます！'; })->addMiddleware(function() {
    echo '最初にミドルウェア!';
});

Flight::start();

// これにより「最初にミドルウェア！ここにいます！」と出力されます。
```

ミドルウェアについて知っておくべき非常に重要な注意事項がいくつかあります:
- ミドルウェア関数はルートに追加された順に実行されます。実行は[こちらの Slim Framework が扱っている方法](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)に類似しています。
   - ビフォーは追加された順に実行され、アフターは逆の順に実行されます。
- ミドルウェア関数が false を返すと、すべての実行が停止され、403 Forbidden エラーがスローされます。これをより適切に扱いたい場合は、`Flight::redirect()` や類似の方法で処理する必要があります。
- ルートからパラメータを必要とする場合、これらは1つの配列としてミドルウェア関数に渡されます (`function($params) { ... }` や `public function before($params) {}`)。これは、パラメータをグループ化し、いくつかのグループにおいて、パラメータが異なる順序で表示される可能性があるため、ミドルウェア関数が誤ったパラメータを参照することで壊れるのを避けるためです。この方法で、位置ではなく名前でそれらにアクセスできます。

## ミドルウェアクラス

ミドルウェアはクラスとしても登録できます。"後"の機能が必要な場合は、**必ず**クラスを使用する必要があります。

```php
class MyMiddleware {
    public function before($params) {
        echo '最初にミドルウェア!';
    }

    public function after($params) {
        echo '最後にミドルウェア!';
    }
}

$MyMiddleware = new MyMiddleware();
Flight::route('/path', function() { echo 'ここにいます! '; })->addMiddleware($MyMiddleware); // または ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// これにより "最初にミドルウェア！ここにいます！最後にミドルウェア！" が表示されます。
```

## ミドルウェアをグループ化

ルートグループを追加し、そのグループ内のすべてのルートに同じミドルウェアが適用されるようにすることができます。これは、ヘッダーの API キーをチェックする Auth ミドルウェアでルートをグループ化する必要がある場合などに便利です。

```php

// グループメソッドの最後に追加
Flight::group('/api', function() {

    // この見た目が「空」のルートは実際には /api と一致します
    Flight::route('', function() { echo 'api'; }, false, 'api');
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
    Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

すべてのルートにグローバルミドルウェアを適用したい場合は、「空」のグループを追加できます:

```php

// グループメソッドの最後に追加
Flight::group('', function() {
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
    Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```  