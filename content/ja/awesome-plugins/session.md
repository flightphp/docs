# Ghostff/Session

PHPセッションマネージャー（非同期、フラッシュ、セグメント、セッション暗号化）。オプションでセッションデータの暗号化/復号化にPHP open_sslを使用します。File、MySQL、Redis、およびMemcachedをサポートしています。

## インストール

Composerを使用してインストールしてください。

```bash
composer require ghostff/session
```

## 基本設定

セッションを使用するにはデフォルト設定を渡す必要はありません。[Github Readme](https://github.com/Ghostff/Session)で詳細な設定について読むことができます。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// 1ページロードごとにセッションをコミットする必要があることを覚えておいてください
// または構成で auto_commit を実行する必要があります。
```

## シンプルな例

これはこの方法で使用する単純な例です。

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// ここにログイン論理を行います
	// パスワードの検証など

	// ログインが成功した場合
	$session->set('is_logged_in', true);
	$session->set('user', $user);

    	// セッションに書き込むたびに、わざとコミットする必要があります。
	$session->commit();
});

// このチェックは制限されたページロジック内にあるか、ミドルウェアでラップされているかもしれません。
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// ここに制限されたページロジックを記述します
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

これはこの方法で使用するより複雑な例です。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// カスタムパスをセッション構成ファイルに設定し、セッションIDにランダムな文字列を指定します
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// または手動で構成オプションをオーバーライドすることができます
		$session->updateConfiguration([
			// セッションデータをデータベースに保存したい場合（「すべてのデバイスからログアウト」機能などが必要な場合に適しています）
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // これを他のものに変更してください
			Session::CONFIG_AUTO_COMMIT   => true, // セッションをコミットする必要がある場合だけこれを行ってくださいおよび/またはセッションをコミットするのが難しい場合にのみ
												// さらに Flight::after('start', function() { Flight::session()->commit(); }); を実行できます。
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # PDO dnsのためのデータベースドライバー例(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # データベースホスト
				'db_name'   => 'my_app_database',   # データベース名
				'db_table'  => 'sessions',          # データベーステーブル
				'db_user'   => 'root',              # データベースユーザー名
				'db_pass'   => '',                  # データベースパスワード
				'persistent_conn'=> false,          # スクリプトがデータベースと通信するたびに新しい接続を確立する手間を避け、より速いWebアプリケーションを実現します。バックサイドを見つけてください
			]
		]);
	}
);
```

## ドキュメント

完全なドキュメントのために[GitHub Readme](https://github.com/Ghostff/Session)を参照してください。構成オプションは[default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php)ファイルでよく文書化されています。パッケージ自体を調べたい場合は、コードは理解しやすいです。