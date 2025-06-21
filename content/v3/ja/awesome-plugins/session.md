# FlightPHP セッション - 軽量なファイルベースのセッション ハンドラ

これは、[Flight PHP Framework](https://docs.flightphp.com/) 向けの軽量でファイルベースのセッション ハンドラ プラグインです。ノンブロッキングのセッション読み込み、オプションの暗号化、オートコミット機能、開発用のテスト モードなどの機能を提供し、セッション管理を簡単かつ強力にします。セッション データはファイルに保存されるため、データベースを必要としないアプリケーションに理想的です。

データベースを使用したい場合は、同じ機能の多くを持つがデータベース バックエンドを備えた [ghostff/session](/awesome-plugins/ghost-session) プラグインを参照してください。

完全なソース コードと詳細については、[Github リポジトリ](https://github.com/flightphp/session)を訪問してください。

## インストール

Composer を介してプラグインをインストールします：

```bash
composer require flightphp/session
```

## 基本的な使用方法

Flight アプリケーションで `flightphp/session` プラグインを使用する簡単な例です：

```php
require 'vendor/autoload.php';

use flight\Session;

$app = Flight::app();

// セッション サービスを登録
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
        Flight::json(['message' => 'User is logged in!', 'user_id' => $session->get('user_id')]);
    }
});

Flight::route('/logout', function() {
    $session = Flight::session();
    $session->clear(); // すべてのセッション データをクリア
    Flight::json(['message' => 'Logged out successfully']);
});

Flight::start();
```

### 重要なポイント
- **Non-Blocking**: デフォルトで `read_and_close` を使用し、セッション ロックの問題を防ぎます。
- **Auto-Commit**: デフォルトで有効なので、シャットダウン時に変更が自動的に保存されますが、無効にすることもできます。
- **File Storage**: セッションはデフォルトでシステムの temp ディレクトリの下の `/flight_sessions` に保存されます。

## 構成

登録時にオプションの配列を渡すことで、セッション ハンドラをカスタマイズできます：

```php
// はい、二重配列です :)
$app->register('session', Session::class, [ [
    'save_path' => '/custom/path/to/sessions',         // セッション ファイルのディレクトリ
	'prefix' => 'myapp_',                              // セッション ファイルのプレフィックス
    'encryption_key' => 'a-secure-32-byte-key-here',   // 暗号化を有効にする (AES-256-CBC のために 32 バイト推奨)
    'auto_commit' => false,                            // オートコミットを無効にして手動制御
    'start_session' => true,                           // 自動的にセッションを開始 (デフォルト: true)
    'test_mode' => false,                              // 開発用のテスト モードを有効
    'serialization' => 'json',                         // シリアル化方法: 'json' (デフォルト) または 'php' (レガシー)
] ]);
```

### 構成オプション
| Option            | Description                                      | Default Value                     |
|-------------------|--------------------------------------------------|-----------------------------------|
| `save_path`       | セッション ファイルが保存されるディレクトリ     | `sys_get_temp_dir() . '/flight_sessions'` |
| `prefix`          | 保存されたセッション ファイルのプレフィックス    | `sess_`                           |
| `encryption_key`  | AES-256-CBC 暗号化のためのキー (オプション)      | `null` (暗号化なし)               |
| `auto_commit`     | シャットダウン時にセッション データを自動保存    | `true`                            |
| `start_session`   | 自動的にセッションを開始                         | `true`                            |
| `test_mode`       | PHP セッションに影響を与えないテスト モードで実行 | `false`                           |
| `test_session_id` | テスト モード用のカスタム セッション ID (オプション) | 設定されていない場合ランダム生成 |
| `serialization`   | シリアル化方法: 'json' (デフォルト、安全) または 'php' (レガシー、オブジェクトを許可) | `'json'` |

## シリアル化モード

このライブラリはデフォルトで **JSON シリアル化** を使用し、セッション データの安全性が高く、PHP オブジェクト注入の脆弱性を防ぎます。セッションに PHP オブジェクトを保存する必要がある場合 (ほとんどのアプリでは推奨されません) は、レガシーの PHP シリアル化を選択できます：

- `'serialization' => 'json'` (デフォルト):
  - セッション データに配列とプリミティブのみを許可。
  - より安全: PHP オブジェクト注入に耐性あり。
  - ファイルは `J` (プレーン JSON) または `F` (暗号化 JSON) でプレフィックス付け。
- `'serialization' => 'php'`:
  - PHP オブジェクトの保存を許可 (注意して使用)。
  - ファイルは `P` (プレーン PHP シリアル化) または `E` (暗号化 PHP シリアル化) でプレフィックス付け。

**注:** JSON シリアル化を使用している場合、オブジェクトを保存しようとすると例外が発生します。

## 高度な使用方法

### 手動コミット
オートコミットを無効にした場合、変更を手動でコミットする必要があります：

```php
$app->register('session', Session::class, ['auto_commit' => false]);

Flight::route('/update', function() {
    $session = Flight::session();
    $session->set('key', 'value');
    $session->commit(); // 変更を明示的に保存
});
```

### 暗号化によるセッション セキュリティ
機密データを保護するために暗号化を有効にします：

```php
$app->register('session', Session::class, [
    'encryption_key' => 'your-32-byte-secret-key-here'
]);

Flight::route('/secure', function() {
    $session = Flight::session();
    $session->set('credit_card', '4111-1111-1111-1111'); // 自動的に暗号化
    echo $session->get('credit_card'); // 取得時に復号化
});
```

### セッション ID の再生成
セキュリティのために (例: ログイン後) セッション ID を再生成します：

```php
Flight::route('/post-login', function() {
    $session = Flight::session();
    $session->regenerate(); // 新しい ID、データを保持
    // または
    $session->regenerate(true); // 新しい ID、古いデータを削除
});
```

### ミドルウェアの例
セッション ベースの認証でルートを保護します：

```php
Flight::route('/admin', function() {
    Flight::json(['message' => 'Welcome to the admin panel']);
})->addMiddleware(function() {
    $session = Flight::session();
    if (!$session->get('is_admin')) {
        Flight::halt(403, 'Access denied');
    }
});
```

これはミドルウェアでの簡単な例です。より詳細な例については、[middleware](/learn/middleware) ドキュメントを参照してください。

## メソッド

`Session` クラスは以下のメソッドを提供します：

- `set(string $key, $value)`: セッションに値を保存。
- `get(string $key, $default = null)`: 値を取得し、キーが存在しない場合にデフォルト値をオプションで指定。
- `delete(string $key)`: 特定のキーをセッションから削除。
- `clear()`: すべてのセッション データを削除しますが、同じファイル名を保持。
- `commit()`: 現在のセッション データをファイル システムに保存。
- `id()`: 現在のセッション ID を返します。
- `regenerate(bool $deleteOldFile = false)`: セッション ID を再生成し、新しいセッション ファイルを作成します。古いデータを保持し、古いファイルはシステムに残ります。`$deleteOldFile` が `true` の場合、古いセッション ファイルを削除。
- `destroy(string $id)`: 指定された ID のセッションを破棄し、セッション ファイルをシステムから削除します。これは `SessionHandlerInterface` の一部で、`$id` は必須です。典型的な使用例は `$session->destroy($session->id())` です。
- `getAll()` : 現在のセッションのすべてのデータを返します。

`get()` と `id()` を除くすべてのメソッドは、チェイニングのために `Session` インスタンスを返します。

## このプラグインを使う理由

- **Lightweight**: 外部依存なし—just ファイルのみ。
- **Non-Blocking**: デフォルトで `read_and_close` を使用してセッション ロックを回避。
- **Secure**: 機密データ用の AES-256-CBC 暗号化をサポート。
- **Flexible**: オートコミット、テスト モード、手動制御のオプション。
- **Flight-Native**: Flight フレームワーク専用に構築。

## 技術詳細

- **Storage Format**: セッション ファイルは構成された `save_path` に `sess_` でプレフィックス付けされて保存されます。ファイル コンテンツのプレフィックス:
  - `J`: プレーン JSON (デフォルト、暗号化なし)
  - `F`: 暗号化 JSON (デフォルト、暗号化あり)
  - `P`: プレーン PHP シリアル化 (レガシー、暗号化なし)
  - `E`: 暗号化 PHP シリアル化 (レガシー、暗号化あり)
- **Encryption**: `encryption_key` が提供された場合、各セッション 書き込みごとにランダム IV を使用して AES-256-CBC を適用。JSON と PHP シリアル化の両方で動作。
- **Serialization**: JSON がデフォルトで最も安全。PHP シリアル化はレガシー/高度な使用のために利用可能ですが、セキュリティが低い。
- **Garbage Collection**: 期限切れのセッションをクリーンアップするための PHP の `SessionHandlerInterface::gc()` を実装。

## 貢献

貢献を歓迎します！ [リポジトリ](https://github.com/flightphp/session) をフォークし、変更を加えてプル リクエストを送信してください。バグの報告や機能の提案は Github のイシュー トラッカーで行ってください。

## ライセンス

このプラグインは MIT ライセンスの下でライセンスされています。詳細は [Github リポジトリ](https://github.com/flightphp/session) を参照してください。