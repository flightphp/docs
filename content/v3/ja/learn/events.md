# Flight PHP のイベントシステム (v3.15.0+)

Flight PHP は、軽量で直感的なイベントシステムを導入し、アプリケーションでカスタムイベントを登録およびトリガーできます。`Flight::onEvent()` と `Flight::triggerEvent()` の追加により、アプリのライフサイクルにおける重要なタイミングにフックしたり、自分でイベントを定義したりして、コードをよりモジュール化し拡張しやすくできます。これらのメソッドは Flight の **マップ可能なメソッド** であり、必要に応じて動作をオーバーライドできます。

このガイドでは、イベントの基礎知識から、なぜそれらが有用か、使い方、実践的な例までをカバーし、初心者がその力を理解する手助けをします。

## なぜイベントを使うのか？

イベントを使うことで、アプリケーションの異なる部分を互いに過度に依存しないように分離できます。この分離（**デカップリング** と呼ばれる）は、コードの更新、拡張、デバッグを容易にします。一つの大きな塊で全てを書く代わりに、特定のアクション（イベント）に応答する小さな独立した部分にロジックを分割できます。

ブログアプリを作成していると想像してください：
- ユーザーがコメントを投稿したとき：
  - コメントをデータベースに保存する。
  - ブログオーナーにメールを送信する。
  - セキュリティのためにアクションをログに記録する。

イベントを使わずにこれらを一つの関数に詰め込むことになりますが、イベントを使うと分割できます：一つの部分でコメントを保存し、もう一つの部分で `'comment.posted'` というイベントをトリガーし、別のリスナーがメール送信とログを処理します。これにより、コードがクリーンになり、機能（例: 通知）を追加または削除する際にコアロジックを触らずに済みます。

### 一般的な用途
- **ログ記録**: ログインやエラーのようなアクションを記録し、メイン�コードを散らかさない。
- **通知**: 何かが起きたときにメールやアラートを送信する。
- **更新**: キャッシュをリフレッシュしたり、他のシステムに変更を通知したりする。

## イベントリスナーの登録

イベントをリッスンするには `Flight::onEvent()` を使います。このメソッドでイベントが発生したときに何が起こるかを定義します。

### 構文
```php
// イベント名とコールバックを指定します
Flight::onEvent(string $event, callable $callback): void
```
- `$event`: イベントの名前 (例: `'user.login'`).
- `$callback`: イベントがトリガーされたときに実行される関数。

### 動作の仕組み
イベントに「購読」することで、発生したときに何をするかを Flight に伝えます。コールバックはイベントトリガーから渡された引数を受け取ることができます。

Flight のイベントシステムは同期型です。つまり、各イベントリスナーは順番に実行され、すべての登録されたリスナーが完了するまでコードの実行が続きます。これは非同期のイベントシステムとは異なり、リスナーが並行して実行されたり後で実行されたりしない点が重要です。

### 簡単な例
```php
// 'user.login' イベントがトリガーされたら、ユーザーを挨拶します
Flight::onEvent('user.login', function ($username) {
    echo "Welcome back, $username!";
});
```
ここで、`'user.login'` イベントがトリガーされると、ユーザーの名前で挨拶します。

### 重要なポイント
- 同じイベントに複数のリスナーを追加できます。それらは登録された順序で実行されます。
- コールバックは関数、匿名関数、またはクラスのメソッドにできます。

## イベントのトリガー

イベントを発生させるには `Flight::triggerEvent()` を使います。これにより、登録されたリスナーを実行し、必要なデータを渡します。

### 構文
```php
// イベント名と任意の引数を指定します
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`: トリガーするイベント名 (登録されたものと一致する必要があります)。
- `...$args`: リスナーに渡すオプションの引数 (任意の数)。

### 簡単な例
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
これは `'user.login'` イベントをトリガーし、`'alice'` を先に定義したリスナーに渡します。結果として、`Welcome back, alice!` と出力されます。

### 重要なポイント
- リスナーが登録されていない場合、何も起こりません—アプリが壊れることはありません。
- スプレッドオペレーター (`...`) を使用して、柔軟に複数の引数を渡せます。

### イベントリスナーの登録

...

**さらなるリスナーの停止**:
リスナーが `false` を返す場合、そのイベントの追加のリスナーは実行されなくなります。これにより、特定の条件に基づいてイベントチェーンを停止できます。リスナーの順序が重要で、最初に `false` を返すものが残りの実行を止めます。

**例**:
```php
// ユーザーが禁止されている場合、ログアウトし、以降のリスナーを停止します
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // 以降のリスナーを停止
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // これは実行されません
});
```

## イベントメソッドのオーバーライド

`Flight::onEvent()` と `Flight::triggerEvent()` は [拡張](/learn/extending) 可能で、動作を再定義できます。これはイベントシステムをカスタマイズしたい上級ユーザーに便利です。例えば、ログの追加やイベントのディスパッチ方法の変更などです。

### 例: `onEvent` のカスタマイズ
```php
// イベント登録をログに記録します
Flight::map('onEvent', function (string $event, callable $callback) {
    // 毎回のイベント登録をログに記録
    error_log("New event listener added for: $event");
    // 内部のデフォルト動作を呼び出す (内部イベントシステムを仮定)
    Flight::_onEvent($event, $callback);
});
```
今度は、イベントを登録するたびにログが記録されます。

### なぜオーバーライドするのか？
- デバッグや監視を追加する。
- 特定の環境でイベントを制限する (例: テスト環境で無効化)。
- 他のイベントライブラリと統合する。

## イベントをどこに置くか

初心者の方は、*アプリでこれらのイベントをどこに登録するのか？* と疑問に思うかもしれません。Flight のシンプルさから厳格なルールはありませんが、整理しておくことでアプリが成長してもコードを維持しやすくなります。以下は実践的なオプションとベストプラクティスで、Flight の軽量性を考慮しています：

### オプション 1: メインの `index.php` ファイル内
小さなアプリやクイックプロトタイプの場合、イベントを `index.php` ファイルにルートと一緒に登録できます。これにより全てが一つの場所にまとまり、シンプルさを優先できます。

```php
require 'vendor/autoload.php';

// イベントを登録
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
});

// ルートを定義
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **利点**: シンプルで、追加のファイル不要。小規模プロジェクトに最適。
- **欠点**: アプリが成長すると、イベントとルートが増えて散らかりやすくなる。

### オプション 2: 別々の `events.php` ファイル
少し大きなアプリの場合、イベント登録を `app/config/events.php` のような専用ファイルに移動し、`index.php` でインクルードします。これは Flight プロジェクトでルートを整理するのと同じアプローチです。

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "Email sent to $email: Welcome, $name!";
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **利点**: `index.php` をルーティングに集中させ、イベントを論理的に整理。編集しやすく。
- **欠点**: 非常に小さなアプリでは、構造を追加するのが過剰に感じるかも。

### オプション 3: トリガーされる場所の近く
もう一つのアプローチは、イベントをトリガーされる場所に近い、例えばコントローラーやルート定義内に登録するものです。これがアプリの特定の部分に特化している場合に有効です。

```php
Flight::route('/signup', function () {
    // ここでイベントを登録
    Flight::onEvent('user.registered', function ($email) {
        echo "Welcome email sent to $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Signed up!";
});
```
- **利点**: 関連するコードを一緒に保ち、孤立した機能に適する。
- **欠点**: イベント登録が散らばり、全イベントを一目で確認しにくくなる。重複のリスクあり。

### Flight 向けのベストプラクティス
- **シンプルから始める**: 小さなアプリでは `index.php` にイベントを置く。Flight のミニマリズムに合っている。
- **賢く成長させる**: アプリが拡大したら (例: 5-10 以上のイベント)、`app/config/events.php` ファイルを使う。ルートを整理するのと同じく、自然なステップアップ。
- **過度な設計を避ける**: アプリが巨大にならない限り、フルブローの「イベントマネージャー」クラスやディレクトリを作成しない。Flight はシンプルさを活かすべき。

### ヒント: 目的ごとにグループ化
`events.php` では、関連するイベントをコメント付きでグループ化：

```php
// app/config/events.php
// ユーザー関連のイベント
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in");
});
Flight::onEvent('user.registered', function ($email) {
    echo "Welcome to $email!";
});

// ページ関連のイベント
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);
});
```

この構造はスケーラブルで、初心者にも親しみやすい。

## 初心者向けの例

実際のシナリオを通じて、イベントがどのように動作し、なぜ役立つかを説明します。

### 例 1: ユーザーログインのログ記録
```php
// ステップ 1: リスナーを登録
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username logged in at $time");
});

// ステップ 2: アプリ内でトリガー
Flight::route('/login', function () {
    $username = 'bob'; // フォームから取得したと仮定
    Flight::triggerEvent('user.login', $username);
    echo "Hi, $username!";
});
```
**なぜ役立つか**: ログインコードはログについて知らなくてもイベントをトリガーするだけです。後でリスナーを追加 (例: ウェルカムメール) してもルートを変更せずに済みます。

### 例 2: 新規ユーザーの通知
```php
// 新規登録のリスナー
Flight::onEvent('user.registered', function ($email, $name) {
    // メール送信をシミュレート
    echo "Email sent to $email: Welcome, $name!";
});

// サインアップ時にトリガー
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Thanks for signing up!";
});
```
**なぜ役立つか**: サインアップロジックはユーザー作成に集中し、イベントが通知を処理します。後でリスナーを追加 (例: サインアップのログ) できます。

### 例 3: キャッシュのクリア
```php
// キャッシュをクリアするリスナー
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // セッションキャッシュをクリア
    echo "Cache cleared for page $pageId.";
});

// ページを編集したときにトリガー
Flight::route('/edit-page/(@id)', function ($pageId) {
    // ページを更新したと仮定
    Flight::triggerEvent('page.updated', $pageId);
    echo "Page $pageId updated.";
});
```
**なぜ役立つか**: 編集コードはキャッシングを気にせず、更新をシグナルするだけです。他の部分が対応できます。

## ベストプラクティス

- **イベント名を明確に**: `'user.login'` や `'page.updated'` のように具体的な名前を使い、何をするのかを明示する。
- **リスナーをシンプルに**: 遅いタスクや複雑な処理をリスナーに入れない—アプリを高速に保つ。
- **イベントをテスト**: 手動でトリガーして、期待通りに動作することを確認。
- **イベントを賢く使う**: デカップリングに最適だが、多用しすぎるとコードが追いにくくなる—必要に応じて。

Flight PHP のイベントシステムは、`Flight::onEvent()` と `Flight::triggerEvent()` により、シンプルでありながら強力な柔軟なアプリケーション構築を可能にします。イベントを通じてアプリの異なる部分が通信することで、コードを整理し、再利用しやすく、拡張しやすくします。アクションのログ、通知の送信、更新の管理など、イベントがあればロジックを絡ませずに実現できます。さらに、これらのメソッドをオーバーライドできるので、システムをニーズに合わせてカスタマイズできます。単一のイベントから始め、アプリの構造がどのように変わるかを見てください！

## ビルトインイベント

Flight PHP には、フレームワークのライフサイクルにフックするためのビルトインイベントがいくつか用意されています。これらのイベントは、リクエスト/レスポンスサイクルの特定のタイミングでトリガーされ、カスタムロジックを実行できます。

### ビルトインイベント一覧
- **flight.request.received**: `function(Request $request)` リクエストが受信され、解析・処理されたときにトリガー。
- **flight.error**: `function(Throwable $exception)` リクエストライフサイクル中にエラーが発生したときにトリガー。
- **flight.redirect**: `function(string $url, int $status_code)` リダイレクトが開始されたときにトリガー。
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` 特定のキーのキャッシュがチェックされ、ヒットしたかどうかのときにトリガー。
- **flight.middleware.before**: `function(Route $route)` ビフォアミドルウェアが実行された後にトリガー。
- **flight.middleware.after**: `function(Route $route)` アフターミドルウェアが実行された後にトリガー。
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` 任意のミドルウェアが実行された後にトリガー。
- **flight.route.matched**: `function(Route $route)` ルートがマッチしたが、まだ実行されていないときにトリガー。
- **flight.route.executed**: `function(Route $route, float $executionTime)` ルートが実行され、処理された後にトリガー。`$executionTime` はルート実行にかかった時間。
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` ビューがレンダリングされた後にトリガー。`$executionTime` はテンプレートのレンダリングにかかった時間。**注意: `render` メソッドをオーバーライドした場合は、このイベントを再トリガーする必要があります。**
- **flight.response.sent**: `function(Response $response, float $executionTime)` レスポンスがクライアントに送信された後にトリガー。`$executionTime` はレスポンスの構築にかかった時間。