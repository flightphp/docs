# イベント マネージャー

_v3.15.0 時点_

## 概要

イベントにより、アプリケーション内でカスタム動作を登録およびトリガーできます。`Flight::onEvent()` と `Flight::triggerEvent()` の追加により、アプリのライフサイクルにおける重要な時点にフックしたり、独自のイベント（通知やメールなど）を定義したりして、コードをよりモジュール化し、拡張しやすくできます。これらのメソッドは Flight の [mappable methods](/learn/extending) の一部であり、必要に応じて動作をオーバーライドできます。

## 理解

イベントにより、アプリケーションの異なる部分を分離し、互いに過度に依存しないようにできます。この分離—しばしば **デカップリング** と呼ばれる—は、コードの更新、拡張、デバッグを容易にします。一つの大きな塊で全てを書く代わりに、論理を特定のアクション（イベント）に応答する小さな独立したピースに分割できます。

ブログアプリを構築していると想像してください：
- ユーザーがコメントを投稿すると、以下を行いたい場合：
  - コメントをデータベースに保存。
  - ブログオーナーにメールを送信。
  - セキュリティのためにアクションをログに記録。

イベントなしでは、これらを一つの関数に詰め込むことになります。イベントを使うと、分離できます：一部がコメントを保存し、もう一部が `'comment.posted'` のようなイベントをトリガーし、別々のリスナーがメールとログを処理します。これによりコードがクリーンになり、通知のような機能を追加または削除する際にコアロジックに触れずに済みます。

### 一般的なユースケース

主に、イベントはオプションのものに適しており、システムの絶対的なコア部分ではありません。例えば、以下のものは便利ですが、何らかの理由で失敗してもアプリケーションは動作するはずです：

- **ログ記録**: ログインやエラーなどのアクションを記録し、主コードを散らかさない。
- **通知**: 何かが発生したときにメールやアラートを送信。
- **キャッシュ更新**: キャッシュを更新したり、他のシステムに変更を通知したり。

ただし、パスワードを忘れた機能があるとします。これはコア機能の一部であり、イベントではありません。なぜなら、そのメールが送信されなければ、ユーザーはパスワードをリセットできず、アプリケーションを使用できないからです。

## 基本的な使用方法

Flight のイベントシステムは、2 つの主なメソッドを中心に構築されています：イベントリスナーを登録するための `Flight::onEvent()` と、イベントを発火するための `Flight::triggerEvent()`。これらを使用する方法は以下の通りです：

### イベントリスナーの登録

イベントをリッスンするには、`Flight::onEvent()` を使用します。このメソッドにより、イベントが発生したときに何が起こるかを定義できます。

```php
Flight::onEvent(string $event, callable $callback): void
```

- `$event`: イベントの名前（例: `'user.login'`）。
- `$callback`: イベントがトリガーされたときに実行する関数。

イベントが発生したときに Flight に何をするかを伝えることで、イベントに「購読」します。コールバックはイベントトリガーから渡された引数を受け取ることができます。

Flight のイベントシステムは同期型です。つまり、各イベントリスナーは順番に実行されます。イベントをトリガーすると、そのイベントのすべての登録されたリスナーが完了するまでコードが続行されません。これは、非同期イベントシステム（リスナーが並行して実行されたり、後で実行されたりする）とは異なるため、理解が重要です。

#### シンプルな例
```php
Flight::onEvent('user.login', function ($username) {
    echo "Welcome back, $username!";

	// you can send an email if the login is from a new location
	// 新しい場所からのログインの場合、メールを送信できます
});
```
ここで、`'user.login'` イベントがトリガーされると、ユーザーを名前で挨拶し、必要に応じてメール送信のロジックを含めることができます。

> **注意:** コールバックは関数、匿名関数、またはクラスのメソッドです。

### イベントのトリガー

イベントを発生させるには、`Flight::triggerEvent()` を使用します。これにより、Flight にそのイベントのすべてのリスナーを実行し、提供したデータを渡します。

```php
Flight::triggerEvent(string $event, ...$args): void
```

- `$event`: トリガーするイベント名（登録されたイベントと一致する必要があります）。
- `...$args`: リスナーに送信するオプションの引数（任意の数の引数）。

#### シンプルな例
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
これにより、`'user.login'` イベントがトリガーされ、`'alice'` が以前定義したリスナーに送信され、出力は `Welcome back, alice!` になります。

- リスナーが登録されていない場合、何も起こりません—アプリは壊れません。
- 複数の引数を柔軟に渡すためにスプレッド演算子 (`...`) を使用します。

### イベントの停止

リスナーが `false` を返すと、そのイベントの追加のリスナーは実行されません。これにより、特定の条件に基づいてイベントチェーンを停止できます。リスナーの順序が重要であることを覚えておいてください。最初に `false` を返すものが残りを停止します。

**例**:
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // Stops subsequent listeners
        // 後続のリスナーを停止
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // this is never sent
    // これは決して送信されません
});
```

### イベントメソッドのオーバーライド

`Flight::onEvent()` と `Flight::triggerEvent()` は [拡張可能](/learn/extending) です。つまり、それらの動作を再定義できます。これは、イベントシステムをカスタマイズしたい上級ユーザー（ログ追加やイベントディスパッチの変更など）にとって優れています。

#### 例: `onEvent` のカスタマイズ
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Log every event registration
    // すべてのイベント登録をログに記録
    error_log("New event listener added for: $event");
    // Call the default behavior (assuming an internal event system)
    // デフォルト動作を呼び出し（内部イベントシステムを想定）
    Flight::_onEvent($event, $callback);
});
```
これで、イベントを登録するたびにログが記録された後、処理が続行されます。

#### オーバーライドの理由
- デバッグや監視を追加。
- 特定の環境でイベントを制限（例: テストで無効化）。
- 別のイベントライブラリと統合。

### イベントの配置場所

プロジェクトでイベントの概念に慣れていない場合、*アプリ内でこれらのイベントをどこで登録するのか？* と疑問に思うかもしれません。Flight のシンプルさにより、厳格なルールはありません—プロジェクトに適した場所に配置できます。ただし、アプリが成長するにつれてコードを維持しやすくするために、整理しておくことが役立ちます。Flight の軽量な性質に合わせた実用的なオプションとベストプラクティスを以下に示します：

#### オプション 1: メインの `index.php` 内
小さなアプリやクイックプロトタイプの場合、`index.php` ファイル内でルートと共にイベントを登録できます。これにより全てを一箇所にまとめ、シンプルさを優先する場合に適しています。

```php
require 'vendor/autoload.php';

// Register events
// イベントを登録
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
    // $username が " . date('Y-m-d H:i:s') . " にログイン
});

// Define routes
// ルートを定義
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
    // ログインしました！
});

Flight::start();
```
- **利点**: シンプル、追加ファイルなし、小規模プロジェクトに最適。
- **欠点**: アプリが成長し、イベントとルートが増えると散らかりやすくなります。

#### オプション 2: 専用の `events.php` ファイル
少し大きなアプリの場合、イベント登録を `app/config/events.php` のような専用ファイルに移動することを検討してください。`index.php` でルートの前にこのファイルをインクルードします。これは、Flight プロジェクトでよく見られる `app/config/routes.php` のルート整理を模倣しています。

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
    // $username が " . date('Y-m-d H:i:s') . " にログイン
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "Email sent to $email: Welcome, $name!";
    // $email にメール送信: ようこそ、$name！
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
    // ログインしました！
});

Flight::start();
```
- **利点**: `index.php` をルーティングに集中させ、イベントを論理的に整理、検索と編集が容易。
- **欠点**: 非常に小さなアプリでは構造が過剰に感じるかもしれません。

#### オプション 3: トリガーされる場所の近く
もう一つのアプローチは、イベントをトリガーされる場所の近くで登録すること、例えばコントローラーやルート定義内です。イベントがアプリの一部の特定のものに適している場合に有効です。

```php
Flight::route('/signup', function () {
    // Register event here
    // ここでイベントを登録
    Flight::onEvent('user.registered', function ($email) {
        echo "Welcome email sent to $email!";
        // $email にようこそメールを送信！
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Signed up!";
    // 登録ありがとう！
});
```
- **利点**: 関連コードを一緒に保ち、孤立した機能に適しています。
- **欠点**: イベント登録が散らばり、全イベントを一度に把握しにくく、注意しないと重複登録のリスクがあります。

#### Flight のベストプラクティス
- **シンプルに開始**: 小さなアプリでは、`index.php` にイベントを配置。Flight のミニマリズムに合います。
- **賢く成長**: アプリが拡張（例: 5-10 個以上のイベント）したら、`app/config/events.php` ファイルを使用。ルートの整理のように自然なステップで、コードを整理しつつ複雑なフレームワークを追加しません。
- **過剰設計を避ける**: アプリが巨大になるまで、完全な「イベントマネージャー」クラスやディレクトリを作成しないでください—Flight はシンプルさを活かします。

#### ヒント: 目的別にグループ化
`events.php` 内で、関連イベント（例: すべてのユーザー関連イベント）をコメント付きでグループ化：

```php
// app/config/events.php
// User Events
// ユーザーイベント
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in");
    // $username がログイン
});
Flight::onEvent('user.registered', function ($email) {
    echo "Welcome to $email!";
    // $email へようこそ！
});

// Page Events
// ページイベント
Flight::onEvent('page.updated', function ($pageId) {
    Flight::cache()->delete("page_$pageId");
});
```

この構造は拡張しやすく、初心者向けです。

### 実世界の例

イベントの動作と有用性を示すために、いくつかの実世界のシナリオを歩いてみましょう。

#### 例 1: ユーザー login のログ記録
```php
// Step 1: Register a listener
// ステップ 1: リスナーを登録
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username logged in at $time");
    // $username が $time にログイン
});

// Step 2: Trigger it in your app
// ステップ 2: アプリ内でトリガー
Flight::route('/login', function () {
    $username = 'bob'; // Pretend this comes from a form
    // フォームから来たと仮定
    Flight::triggerEvent('user.login', $username);
    echo "Hi, $username!";
    // こんにちは、$username！
});
```
**有用な理由**: ログインコードはログについて知る必要がなく、イベントをトリガーするだけです。後でリスナーを追加（例: ようこそメール送信）でき、ルートを変更せずに済みます。

#### 例 2: 新規ユーザーの通知
```php
// Listener for new registrations
// 新規登録のリスナー
Flight::onEvent('user.registered', function ($email, $name) {
    // Simulate sending an email
    // メール送信をシミュレート
    echo "Email sent to $email: Welcome, $name!";
    // $email にメール送信: ようこそ、$name！
});

// Trigger it when someone signs up
// 誰かがサインアップしたときにトリガー
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Thanks for signing up!";
    // サインアップありがとう！
});
```
**有用な理由**: サインアップロジックはユーザー作成に集中し、イベントが通知を処理します。後でリスナーを追加（例: サインアップのログ）できます。

#### 例 3: キャッシュのクリア
```php
// Listener to clear a cache
// キャッシュクリアのリスナー
Flight::onEvent('page.updated', function ($pageId) {
	// if using the flightphp/cache plugin
	// flightphp/cache プラグインを使用する場合
    Flight::cache()->delete("page_$pageId");
    echo "Cache cleared for page $pageId.";
    // ページ $pageId のキャッシュをクリア。
});

// Trigger when a page is edited
// ページが編集されたときにトリガー
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Pretend we updated the page
    // ページを更新したと仮定
    Flight::triggerEvent('page.updated', $pageId);
    echo "Page $pageId updated.";
    // ページ $pageId を更新。
});
```
**有用な理由**: 編集コードはキャッシングを気にせず、更新をシグナルするだけです。アプリの他の部分が必要に応じて反応できます。

### ベストプラクティス

- **イベント名を明確に**: `'user.login'` や `'page.updated'` のような具体的な名前を使用し、何をするかが明らかになるように。
- **リスナーをシンプルに保つ**: リスナーに遅いまたは複雑なタスクを置かない—アプリを高速に保つ。
- **イベントをテスト**: 手動でトリガーして、リスナーが期待通りに動作することを確認。
- **イベントを賢く使用**: デカップリングに優れていますが、多すぎるとコードが追いにくくなる—適切な場合に使用。

Flight PHP のイベントシステムは、`Flight::onEvent()` と `Flight::triggerEvent()` により、シンプルでありながら強力な方法で柔軟なアプリケーションを構築できます。アプリの異なる部分がイベントを通じて互いに通信することで、コードを整理、再利用しやすく、拡張しやすく保てます。アクションのログ、通知の送信、更新の管理など、イベントによりロジックを絡めずに実行できます。さらに、これらのメソッドをオーバーライドできるため、システムをニーズに合わせて調整できます。一つのイベントから小さく始め、アプリの構造がどのように変化するかを観察してください！

### 組み込みイベント

Flight PHP には、フレームワークのライフサイクルにフックするためのいくつかの組み込みイベントがあります。これらのイベントは、リクエスト/レスポンスサイクルの特定の時点でトリガーされ、特定のアクションが発生したときにカスタムロジックを実行できます。

#### 組み込みイベントリスト
- **flight.request.received**: `function(Request $request)` リクエストが受信、解析、処理されたときにトリガー。
- **flight.error**: `function(Throwable $exception)` リクエストライフサイクル中にエラーが発生したときにトリガー。
- **flight.redirect**: `function(string $url, int $status_code)` リダイレクトが開始されたときにトリガー。
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` 特定のキーでキャッシュがチェックされたときにトリガー（ヒットまたはミス）。
- **flight.middleware.before**: `function(Route $route)` ビフォーミドルウェアが実行された後にトリガー。
- **flight.middleware.after**: `function(Route $route)` アフターミドルウェアが実行された後にトリガー。
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` 任意のミドルウェアが実行された後にトリガー。
- **flight.route.matched**: `function(Route $route)` ルートがマッチしたものの、まだ実行されていないときにトリガー。
- **flight.route.executed**: `function(Route $route, float $executionTime)` ルートが実行され処理された後にトリガー。`$executionTime` はルート実行（コントローラー呼び出しなど）に要した時間。
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` ビューがレンダリングされた後にトリガー。`$executionTime` はテンプレートレンダリングに要した時間。**注意: `render` メソッドをオーバーライドした場合、このイベントを再トリガーする必要があります。**
- **flight.response.sent**: `function(Response $response, float $executionTime)` レスポンスがクライアントに送信された後にトリガー。`$executionTime` はレスポンス構築に要した時間。

## 関連項目
- [Extending Flight](/learn/extending) - Flight のコア機能を拡張およびカスタマイズする方法。
- [Cache](/awesome-plugins/php_file_cache) - ページが更新されたときにイベントを使用してキャッシュをクリアする例。

## トラブルシューティング
- イベントリスナーが呼び出されない場合、イベントをトリガーする前に登録されていることを確認してください。登録の順序が重要です。

## 変更履歴
- v3.15.0 - Flight にイベントを追加。