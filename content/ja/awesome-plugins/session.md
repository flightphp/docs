# Ghostff/Session

PHP セッションマネージャー（ノンブロッキング、フラッシュ、セグメント、セッション暗号化）。セッションデータのオプションの暗号化/復号化に PHP open_ssl を使用します。File、MySQL、Redis、および Memcached をサポートしています。

## インストール

Composer を使用してインストールします。

```bash
composer require ghostff/session
```

## 基本的な設定

セッションを使用するためにデフォルト設定を渡す必要はありません。詳細な設定については [Github Readme](https://github.com/Ghostff/Session) を参照してください。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// 各ページロードでセッションをコミットする必要があることを覚えておく必要があります
// または構成で auto_commit を実行する必要があります。
```

## シンプルな例

これはこのように使用する可能性のあるシンプルな例です。

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// ここでログインロジックを実行します
	// パスワードの検証など

	// ログインが成功した場合
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// セッションに書き込むたびに、明示的にコミットする必要があります。
	$session->commit();
});

// このチェックは、制限されたページロジック内にあるか、ミドルウェアでラップされているかもしれません。
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// 制限されたページロジックをここで実行します
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

これはこのように使用する可能性のあるより複雑な例です。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// カスタムパスをセッション構成ファイルに設定し、セッション ID にランダムな文字列を指定します
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// または手動で構成オプションをオーバーライドできます
		$session->updateConfiguration([
			// セッションデータをデータベースに保存する場合（「すべてのデバイスからログアウトする」などの機能が必要な場合に適しています）
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // これを別のものに変更してください
			Session::CONFIG_AUTO_COMMIT   => true, // 必要であればこれを実行するか、session を commit() するのが難しい場合にのみ実行します。
												   // さらに Flight::after('start', function() { Flight::session()->commit(); }); を行うこともできます。
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # PDO の DNS 用のデータベースドライバー 例(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # データベースホスト
				'db_name'   => 'my_app_database',   # データベース名
				'db_table'  => 'sessions',          # データベーステーブル
				'db_user'   => 'root',              # データベースユーザー名
				'db_pass'   => '',                  # データベースパスワード
				'persistent_conn'=> false,          # スクリプトがデータベースと通信するたびに新しい接続を確立するオーバーヘッドを避け、より速い Web アプリケーションを実現します。バックサイドを自分で見つけなさい
			]
		]);
	}
);
```

## ヘルプ！セッションデータが保持されません！

セッションデータを設定してもリクエスト間で保持されない場合は、セッションデータをコミットするのを忘れた可能性があります。セッションデータを設定した後に `$session->commit()` を呼び出すことで行えます。

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// ここでログインロジックを実行します
	// パスワードの検証など

	// ログインが成功した場合
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// セッションに書き込むたびに、明示的にコミットする必要があります。
	$session->commit();
});
```

これの回避策は、セッションサービスを設定するときに、構成で `auto_commit` を `true` に設定する必要があるということです。これにより、各リクエスト後にセッションデータが自動的にコミットされます。

```php

$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

また、各リクエスト後にセッションデータをコミットするようにするには、`Flight::after('start', function() { Flight::session()->commit(); });` を行うこともできます。

## ドキュメント

詳細なドキュメントについては、[Github Readme](https://github.com/Ghostff/Session) を参照してください。構成オプションは [default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) ファイルにて詳細に説明されています。パッケージを自分で調べる場合は、コードは理解しやすいです。