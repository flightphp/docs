# Flight PHPにおけるイベントシステム (v3.15.0+)

Flight PHPは、アプリケーション内でカスタムイベントを登録およびトリガーできる軽量かつ直感的なイベントシステムを導入します。`Flight::onEvent()`と`Flight::triggerEvent()`を追加することで、アプリのライフサイクルの重要な瞬間にフックしたり、独自のイベントを定義したりして、コードをよりモジュール化し拡張可能にすることができます。これらのメソッドはFlightの**マッピング可能メソッド**の一部であり、ニーズに合わせてその動作をオーバーライドすることが可能です。

このガイドでは、イベントの価値、使い方、および初心者がそのパワーを理解するのを助ける実用的な例を含め、イベントを始めるために必要なすべてのことを説明します。

## なぜイベントを使用するのか？

イベントを使用することで、アプリケーションの異なる部分を分離し、互いに過度に依存しないようにすることができます。この分離はしばしば**デカップリング**と呼ばれ、コードの更新、拡張、デバッグが容易になります。すべてを一つの大きな塊で書く代わりに、特定のアクション（イベント）に応じて反応する小さく独立した部分にロジックを分割できます。

ブログアプリを構築していると仮定しましょう：
- ユーザーがコメントを投稿したとき、あなたは以下を行いたいかもしれません：
  - コメントをデータベースに保存する。
  - ブログのオーナーにメールを送信する。
  - セキュリティのためにアクションをログに記録する。

イベントがなければ、すべてを一つの関数に詰め込むことになります。イベントを使用すると、これを分割できます：ある部分がコメントを保存し、別の部分が`'comment.posted'`のようなイベントをトリガーし、別のリスナーがメールとロギングを処理します。これにより、コードがクリーンになり、コアロジックに手を触れずに機能（通知など）を追加または削除することができます。

### 一般的な使用例
- **ロギング**：ログインやエラーのようなアクションを主要なコードを混雑させずに記録できます。
- **通知**：何かが発生したときにメールやアラートを送信します。
- **更新**：キャッシュをリフレッシュしたり、他のシステムに変更を通知します。

## イベントリスナーの登録

イベントをリッスンするには、`Flight::onEvent()`を使用します。このメソッドは、イベントが発生したときに何が起こるべきかを定義することを可能にします。

### 構文
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event`：イベントの名前（例：`'user.login'`）。
- `$callback`：イベントがトリガーされたときに実行される関数。

### 仕組み
あなたは、イベントが発生したときにFlightに何をするかを伝えることによって、イベントに「登録」します。コールバックは、イベントトリガーから渡された引数を受け取ることができます。

Flightのイベントシステムは同期的であり、これは各イベントリスナーが順番に、1つずつ実行されることを意味します。イベントをトリガーすると、そのイベントに登録されたすべてのリスナーが実行を完了し、その後にコードが続行されます。これは、リスナーが並行して実行されるか後で実行される非同期イベントシステムとは異なるため、理解することが重要です。

### シンプルな例
```php
Flight::onEvent('user.login', function ($username) {
    echo "おかえりなさい、$username!";
});
```
ここで、`'user.login'`イベントがトリガーされると、ユーザーの名前で挨拶されます。

### 重要なポイント
- 同じイベントに複数のリスナーを追加できます—登録した順序で実行されます。
- コールバックは関数、匿名関数、またはクラスのメソッドであることができます。

## イベントのトリガー

イベントを発生させるには、`Flight::triggerEvent()`を使用します。これにより、Flightはそのイベントに登録されたすべてのリスナーを実行し、提供したデータを渡します。

### 構文
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`：トリガーするイベントの名前（登録されたイベントと一致する必要があります）。
- `...$args`：リスナーに送信するオプションの引数（任意の数の引数を送信できます）。

### シンプルな例
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
これは`'user.login'`イベントをトリガーし、以前に定義したリスナーに`'alice'`を送信します。出力は次のようになります：`おかえりなさい、alice!`。

### 重要なポイント
- 登録されたリスナーがない場合、何も起こりません—アプリが壊れることはありません。
- スプレッド演算子（`...`）を使用して、柔軟に複数の引数を渡します。

### イベントリスナーの登録

...

**さらなるリスナーを停止する**：
リスナーが`false`を返すと、そのイベントの追加のリスナーは実行されません。これにより、特定の条件に基づいてイベントチェーンを停止できます。リスナーの順序は重要であり、最初に`false`を返すものが残りの実行を停止します。

**例**：
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // 後続のリスナーを停止
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // これは決して送信されません
});
```

## イベントメソッドのオーバーライド

`Flight::onEvent()`と`Flight::triggerEvent()`は[拡張可能](/learn/extending)であり、機能を再定義することができます。これは、ログ記録を追加したり、イベントがどのように派遣されるかを変更したりしたい高度なユーザーにとって素晴らしいものです。

### 例：`onEvent`のカスタマイズ
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // 新しいイベントリスナーの追加をログに記録
    error_log("新しいイベントリスナーが追加されました: $event");
    // デフォルトの動作を呼び出す（内部イベントシステムを想定）
    Flight::_onEvent($event, $callback);
});
```
これにより、イベントを登録するたびに、処理を続ける前にログが記録されます。

### なぜオーバーライドするのか？
- デバッグやモニタリングを追加。
- 特定の環境（例：テスト中に無効化）でイベントを制限。
- 別のイベントライブラリと統合。

## イベントを配置する場所

初心者として、あなたは疑問に思うかもしれません:*アプリ内のすべてのイベントをどこに登録しますか？* Flightのシンプルさは厳格なルールがないことを意味します—プロジェクトに合った任意の場所に配置できます。ただし、整理された状態を保つことで、アプリが成長したときにコードの維持が楽になります。ここでは、Flightの軽量な性質に合わせた実用的なオプションとベストプラクティスをいくつか示します。

### オプション1: メインの`index.php`に
小規模なアプリや迅速なプロトタイプの場合、`index.php`ファイル内にルートと一緒にイベントを登録できます。これにより、すべてが一つの場所にまとまり、シンプルさが優先される場合には良い選択です。

```php
require 'vendor/autoload.php';

// イベントの登録
Flight::onEvent('user.login', function ($username) {
    error_log("$usernameは" . date('Y-m-d H:i:s') . "にログインしました");
});

// ルートの定義
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "ログインしました!";
});

Flight::start();
```
- **利点**：シンプルで追加のファイルが不要、小規模プロジェクトに最適。
- **欠点**：アプリが成長すると、より多くのイベントやルートで混雑する可能性がある。

### オプション2: 別の`events.php`ファイル
やや大きなアプリの場合、イベントの登録を`app/config/events.php`のような専用ファイルに移動することを検討してください。このファイルを`index.php`でルートの前にインクルードします。これは、Flightプロジェクトでルートが`app/config/routes.php`に整理されるのと同様です。

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$usernameは" . date('Y-m-d H:i:s') . "にログインしました");
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "$emailにメールを送信しました: ようこそ、$name!";
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "ログインしました!";
});

Flight::start();
```
- **利点**：`index.php`をルーティングに集中させ、イベントを論理的に整理し、見つけやすく編集しやすくする。
- **欠点**：小さなアプリでは過剰に感じるかもしれない、わずかな構造が追加される。

### オプション3: トリガーされる近くに
別のアプローチは、コントローラーやルート定義の内部のように、トリガーされる近くにイベントを登録することです。これは、イベントがアプリの一部に特定のものである場合にうまく機能します。

```php
Flight::route('/signup', function () {
    // ここでイベントを登録
    Flight::onEvent('user.registered', function ($email) {
        echo "$emailにようこそメールを送信しました!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "サインアップしました!";
});
```
- **利点**：関連するコードをまとめて保持し、孤立した機能に適しています。
- **欠点**：イベントの登録が散らばり、すべてのイベントを一度に確認するのが難しくなり、注意を怠ると重複登録のリスクがある。

### Flightのベストプラクティス
- **シンプルから始める**：小さなアプリの場合、イベントを`index.php`に置く。迅速で、Flightのミニマリズムに合う。
- **賢く成長する**：アプリが拡張するにつれて（例：5-10のイベント以上）、`app/config/events.php`ファイルを使用する。ルートを整理するのと同様の自然なステップであり、コードをきれいに保ちつつも複雑なフレームワークを追加しない。
- **過剰設計を避ける**：アプリが巨大になるまでは、完全な「イベントマネージャー」クラスやディレクトリは作成しないでください—Flightはシンプルさを重視しているため、軽量に保つこと。

### ヒント：目的別にグループ化
`events.php`内では、関連するイベントをグループ化（例：すべてのユーザー関連のイベントを一緒に）し、明確さのためにコメントを追加します：

```php
// app/config/events.php
// ユーザーイベント
Flight::onEvent('user.login', function ($username) {
    error_log("$usernameがログインしました");
});
Flight::onEvent('user.registered', function ($email) {
    echo "$emailにようこそ!";
});

// ページイベント
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);
});
```

この構造はうまくスケールし、初心者にも優しいです。

## 初心者向けの例

イベントがどのように機能し、なぜ役立つのかを示すために、いくつかの実際のシナリオを見てみましょう。

### 例1: ユーザーログインのログ記録
```php
// ステップ1: リスナーを登録
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$usernameが$timeにログインしました");
});

// ステップ2: アプリ内でトリガーする
Flight::route('/login', function () {
    $username = 'bob'; // フォームから取得されると仮定
    Flight::triggerEvent('user.login', $username);
    echo "こんにちは、$username!";
});
```
**なぜこれは役立つのか**：ログインコードはロギングについて知る必要はありません—それはイベントをトリガーするだけです。後で、より多くのリスナー（例：ウェルカムメールの送信）を追加できますが、ルートを変更する必要はありません。

### 例2: 新しいユーザーについての通知
```php
// 新規登録のリスナー
Flight::onEvent('user.registered', function ($email, $name) {
    // メール送信のシミュレーション
    echo "$emailにメールを送信しました: ようこそ、$name!";
});

// 誰かがサインアップしたときにトリガー
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "サインアップありがとう!";
});
```
**なぜこれは役立つのか**：サインアップロジックはユーザーを作成することに焦点を当て、イベントが通知を処理します。他の部分も必要に応じて反応できます。

### 例3: キャッシュのクリア
```php
// キャッシュをクリアするリスナー
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // 適用可能な場合、セッションキャッシュをクリア
    echo "ページ$pageIdのキャッシュがクリアされました。";
});

// ページが編集されるときにトリガー
Flight::route('/edit-page/(@id)', function ($pageId) {
    // ページが更新されたと仮定
    Flight::triggerEvent('page.updated', $pageId);
    echo "ページ$pageIdが更新されました。";
});
```
**なぜこれは役立つのか**：編集コードはキャッシングに関することは気にしません—それは更新を信号します。他のアプリ部分も必要に応じて反応できます。

## ベストプラクティス

- **イベントに明確な名前を付ける**：`'user.login'`や`'page.updated'`のように特定の名前を使用して、何をするかを明確にします。
- **リスナーをシンプルに保つ**：リスナー内に遅いまたは複雑なタスクを置かないでください—アプリを迅速に保ちます。
- **イベントをテストする**：手動でトリガーしてリスナーが期待通りに機能することを確認します。
- **イベントを賢く使用する**：デカップリングに最適ですが、あまりにも多いとコードが見にくくなる可能性があるため、適切な場合にのみ使用します。

`Flight::onEvent()`と`Flight::triggerEvent()`を使用したFlight PHPのイベントシステムは、柔軟なアプリケーションを構築するためのシンプルで強力な方法を提供します。アプリの異なる部分がイベントを通じて互いに話し合うことを可能にすることで、コードを整理、再利用、拡張しやすいものに保つことができます。アクションのログ記録、通知の送信、更新の管理に関係なく、イベントを使用することで複雑なロジックに干渉することなく実現できます。さらに、これらのメソッドをオーバーライドできることで、必要に応じてシステムをカスタマイズする自由があります。単一のイベントから小さく始めて、アプリの構造がどのように変わるかを見てみましょう！

## ビルトインイベント

Flight PHPには、フレームワークのライフサイクルでフックするために使用できるいくつかのビルトインイベントがあります。これらのイベントは、リクエスト/レスポンスサイクルの特定のポイントでトリガーされ、特定のアクションが発生したときにカスタムロジックを実行できます。

### ビルトインイベントリスト
- **flight.request.received**: `function(Request $request)` リクエストが受信され、解析され、処理されるとトリガーされます。
- **flight.error**: `function(Throwable $exception)` リクエストライフサイクル中にエラーが発生したときにトリガーされます。
- **flight.redirect**: `function(string $url, int $status_code)` リダイレクトが開始されたときにトリガーされます。
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` 特定のキーのキャッシュが確認されたときにトリガーされ、キャッシュのヒットまたはミス。
- **flight.middleware.before**: `function(Route $route)` beforeミドルウェアが実行された後にトリガーされます。
- **flight.middleware.after**: `function(Route $route)` afterミドルウェアが実行された後にトリガーされます。
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` 任意のミドルウェアが実行された後にトリガーされます。
- **flight.route.matched**: `function(Route $route)` ルートが一致したときに、まだ実行されていないときにトリガーされます。
- **flight.route.executed**: `function(Route $route, float $executionTime)` ルートが実行され、処理された後にトリガーされます。`$executionTime`は、ルートを実行するのにかかった時間（コントローラーを呼び出すなど）です。
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` ビューがレンダリングされた後にトリガーされます。`$executionTime`は、テンプレートをレンダリングするのにかかった時間です。**注意：`render`メソッドをオーバーライドする場合は、このイベントを再トリガーする必要があります。**
- **flight.response.sent**: `function(Response $response, float $executionTime)` レスポンスがクライアントに送信された後にトリガーされます。`$executionTime`は、レスポンスを構築するのにかかった時間です。