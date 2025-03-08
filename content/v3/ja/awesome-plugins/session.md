# FlightPHP セッション - 軽量ファイルベースのセッションハンドラー

これは、[Flight PHP Framework](https://docs.flightphp.com/) のための軽量なファイルベースのセッションハンドラープラグインです。これは、セッションの管理に関してシンプルでありながら強力なソリューションを提供し、ブロッキングしないセッションの読み取り、任意の暗号化、自動コミット機能、開発用のテストモードなどの機能を備えています。セッションデータはファイルに保存されるため、データベースを必要としないアプリケーションに最適です。

データベースを使用したい場合は、データベースバックエンドを持つこれらの同様の機能を多数備えた[ghostff/session](/awesome-plugins/ghost-session)プラグインをチェックしてください。

完全なソースコードと詳細は、[Githubリポジトリ](https://github.com/flightphp/session)を訪れてください。

## インストール

Composerを介してプラグインをインストールします：

```bash
composer require flightphp/session
```

## 基本的な使い方

ここでは、Flightアプリケーションで`flightphp/session`プラグインを使用する簡単な例を示します：

```php
require 'vendor/autoload.php';

use flight\Session;

$app = Flight::app();

// セッションサービスを登録
$app->register('session', Session::class);

// セッションを使用した例のルート
Flight::route('/login', function() {
    $session = Flight::session();
    $session->set('user_id', 123);
    $session->set('username', 'johndoe');
    $session->set('is_admin', false);

    echo $session->get('username'); // 出力: johndoe
    echo $session->get('preferences', 'default_theme'); // 出力: default_theme

    if ($session->get('user_id')) {
        Flight::json(['message' => 'ユーザーはログインしています！', 'user_id' => $session->get('user_id')]);
    }
});

Flight::route('/logout', function() {
    $session = Flight::session();
    $session->clear(); // すべてのセッションデータをクリア
    Flight::json(['message' => '正常にログアウトしました']);
});

Flight::start();
```

### 重要なポイント
- **ノンブロッキング**: セッションスタートのデフォルトとして`read_and_close`を使用し、セッションロックの問題を防ぎます。
- **自動コミット**: デフォルトで有効になっており、無効にされない限り、シャットダウン時に変更が自動的に保存されます。
- **ファイルストレージ**: セッションはデフォルトで`/flight_sessions`の下にあるシステムの一時ディレクトリに保存されます。

## 設定

セッションハンドラーを登録する際に、オプションの配列を渡すことでカスタマイズできます：

```php
$app->register('session', Session::class, [
    'save_path' => '/custom/path/to/sessions',         // セッションファイルのディレクトリ
    'encryption_key' => 'a-secure-32-byte-key-here',   // 暗号化を有効にする（AES-256-CBCに推奨される32バイト）
    'auto_commit' => false,                            // 手動制御のため自動コミットを無効にする
    'start_session' => true,                           // 自動的にセッションを開始する（デフォルト: true）
    'test_mode' => false                               // 開発用にテストモードを有効にする
]);
```

### 設定オプション
| オプション          | 説明                                           | デフォルト値                        |
|--------------------|-----------------------------------------------|-------------------------------------|
| `save_path`        | セッションファイルが保存されるディレクトリ   | `sys_get_temp_dir() . '/flight_sessions'` |
| `encryption_key`   | AES-256-CBC暗号化用のキー（オプション）      | `null`（暗号化なし）                |
| `auto_commit`      | シャットダウン時にセッションデータを自動保存 | `true`                              |
| `start_session`    | 自動的にセッションを開始                     | `true`                              |
| `test_mode`        | PHPセッションに影響を与えずにテストモードで実行 | `false`                             |
| `test_session_id`  | テストモード用のカスタムセッションID（オプション） | 設定されていない場合はランダムに生成  |

## 高度な使い方

### 手動コミット
自動コミットを無効にすると、変更を手動でコミットする必要があります：

```php
$app->register('session', Session::class, ['auto_commit' => false]);

Flight::route('/update', function() {
    $session = Flight::session();
    $session->set('key', 'value');
    $session->commit(); // 明示的に変更を保存
});
```

### 暗号化によるセッションのセキュリティ
機密データのために暗号化を有効にします：

```php
$app->register('session', Session::class, [
    'encryption_key' => 'your-32-byte-secret-key-here'
]);

Flight::route('/secure', function() {
    $session = Flight::session();
    $session->set('credit_card', '4111-1111-1111-1111'); // 自動的に暗号化されます
    echo $session->get('credit_card'); // 取得時に復号化されます
});
```

### セッション再生成
セキュリティのためにセッションIDを再生成します（例: ログイン後）：

```php
Flight::route('/post-login', function() {
    $session = Flight::session();
    $session->regenerate(); // 新しいID、データを保持
    // または
    $session->regenerate(true); // 新しいID、古いデータを削除
});
```

### ミドルウェアの例
セッションベースの認証でルートを保護します：

```php
Flight::route('/admin', function() {
    Flight::json(['message' => '管理パネルへようこそ']);
})->addMiddleware(function() {
    $session = Flight::session();
    if (!$session->get('is_admin')) {
        Flight::halt(403, 'アクセス拒否');
    }
});
```

これはミドルウェアでの使い方の簡単な例です。詳細な例については、[ミドルウェア](/learn/middleware)のドキュメントを参照してください。

## メソッド

`Session`クラスは以下のメソッドを提供します：

- `set(string $key, $value)`: セッションに値を保存します。
- `get(string $key, $default = null)`: 値を取得し、キーが存在しない場合のオプションのデフォルトを提供します。
- `delete(string $key)`: セッションから特定のキーを削除します。
- `clear()`: すべてのセッションデータを削除します。
- `commit()`: 現在のセッションデータをファイルシステムに保存します。
- `id()`: 現在のセッションIDを返します。
- `regenerate(bool $deleteOld = false)`: セッションIDを再生成し、オプションで古いデータを削除します。

`get()`と`id()`を除くすべてのメソッドは、チェーンのために`Session`インスタンスを返します。

## このプラグインを使用する理由

- **軽量**: 外部依存関係なし—ただのファイル。
- **ノンブロッキング**: デフォルトで`read_and_close`でセッションロックを回避。
- **安全**: 機密データのためのAES-256-CBC暗号化をサポート。
- **柔軟**: 自動コミット、テストモードおよび手動コントロールオプション。
- **Flightネイティブ**: Flightフレームワークのために特別に構築されています。

## 技術的詳細

- **ストレージ形式**: セッションファイルは`sess_`でプレフィックスされ、設定された`save_path`に保存されます。暗号化データは`E`プレフィックス、平文は`P`を使用します。
- **暗号化**: `encryption_key`が提供される場合、各セッション書き込みに対してランダムIVを使用したAES-256-CBCを使用します。
- **ガーベジコレクション**: PHPの`SessionHandlerInterface::gc()`を実装して、期限切れのセッションをクリーンアップします。

## 貢献

貢献は歓迎します！[リポジトリ](https://github.com/flightphp/session)をフォークし、変更を加えてプルリクエストを送信してください。バグを報告するか、Githubのイシュートラッカーを通じて機能を提案してください。

## ライセンス

このプラグインはMITライセンスの下でライセンスされています。詳細については、[Githubリポジトリ](https://github.com/flightphp/session)を参照してください。