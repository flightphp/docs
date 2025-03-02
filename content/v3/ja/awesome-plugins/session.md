# Ghostff/Session

PHPセッションマネージャー（非同期、フラッシュ、セグメント、セッション暗号化）。セッションデータのオプションの暗号化/復号にPHP open_sslを使用します。ファイル、MySQL、Redis、およびMemcachedをサポートしています。

[こちら](https://github.com/Ghostff/Session)をクリックしてコードを表示してください。

## インストール

Composerを使用してインストールしてください。

```bash
composer require ghostff/session
```

## 基本的な設定

デフォルトの設定を使用するには何も渡す必要はありません。詳細な設定については、[Github Readme](https://github.com/Ghostff/Session)を参照してください。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// 1つ覚えておくべきことは、各ページの読み込みごとにセッションをコミットする必要があることです
// または構成でauto_commitを実行する必要があります。
```

## シンプルな例

以下は、この方法を使用する例です。

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// ここにログインロジックなどを記述します
	// パスワードの検証などを行います

	// ログインに成功した場合
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// セッションに書き込むたびに、明示的にコミットする必要があります。
	$session->commit();
});

// このチェックは制限されたページのロジック内にあるか、ミドルウェアでラップされています。
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// ここに制限されたページのロジックを記述します
});

// ミドルウェアバージョン
Flight::route('/some-restricted-page', function() {
	// 通常のページロジック
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## より複雑な例

以下は、この方法を使用するより複雑な例です。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// カスタムパスをセッション構成ファイルに設定し、セッションIDにランダムな文字列を指定します
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// または手動で構成オプションをオーバーライドできます
		$session->updateConfiguration([
			// セッションデータをデータベースに格納する場合（「すべてのデバイスからログアウト」のような機能が必要な場合に適しています）
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // これを他のものに変更してください
			Session::CONFIG_AUTO_COMMIT   => true, // 必要に応じておよび/またはセッションのコミットが難しい場合にのみ行います。
												   // さらにFlight::after('start', function() { Flight::session()->commit(); });を実行できます。
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # PDO dns用のデータベースドライバー 例(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # データベースホスト
				'db_name'   => 'my_app_database',   # データベース名
				'db_table'  => 'sessions',          # データベースのテーブル
				'db_user'   => 'root',              # データベースのユーザー名
				'db_pass'   => '',                  # データベースのパスワード
				'persistent_conn'=> false,          # スクリプトがデータベースに話すたびに新しい接続を確立するオーバーヘッドを避けるため、ウェブアプリケーションが高速になります。バックサイドを見つけてください
			]
		]);
	}
);
```

## ヘルプ！セッションデータが保持されません！

セッションデータを設定しており、リクエスト間で保持されていない場合は、セッションデータをコミットするのを忘れている可能性があります。これは、セッションデータを設定した後に`$session->commit()`を呼び出すことで行えます。

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// ここにログインロジックなどを記述します
	// パスワードの検証などを行います

	// ログインに成功した場合
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// セッションに書き込むたびに、明示的にコミットする必要があります。
	$session->commit();
});
```

別の方法は、セッションサービスを設定する際に、構成で`auto_commit`を`true`に設定することです。これにより、各リクエスト後に自動的にセッションデータがコミットされます。

```php

$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

さらに、`Flight::after('start', function() { Flight::session()->commit(); });`を使用して、各リクエスト後にセッションデータをコミットできます。

## ドキュメント

詳細なドキュメントについては、[Github Readme](https://github.com/Ghostff/Session)をご覧ください。構成オプションは、[default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php)ファイル自体で詳細に説明されています。パッケージ自体を調査したい場合は、コードが理解しやすいです。