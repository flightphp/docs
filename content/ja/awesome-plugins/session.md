# Ghostff/Session

PHP セッションマネージャー (ノンブロッキング、フラッシュ、セグメント化、セッション暗号化)。セッションデータのオプション暗号化/復号化には PHP open_ssl を使用します。File、MySQL、Redis、およびMemcached をサポートしています。

## Installation

Composer でインストールします。

```bash
composer require ghostff/session
```

## Basic Configuration

セッションを使用するために何も渡す必要はありません。デフォルトの設定を使用するには、[Github Readme](https://github.com/Ghostff/Session)https://github.com/Ghostff/Session)で詳細な設定を確認できます。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// 各ページ読み込み時にセッションをコミットする必要があることを覚えておくこと
// または、構成で auto_commit を実行する必要があります。
```

## Simple Example

このように使用する簡単な例です。

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// ログインロジックをここに記述します
	// パスワードを検証するなど

	// ログインが成功した場合
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// セッションに書き込むたびに、必ず明示的にコミットする必要があります。
	$session->commit();
});

// このチェックは制限されたページロジック内にあるか、ミドルウェアでラップすることもできます。
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// 制限されたページロジックをここに記述します
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

## More Complex Example

このように使用するより複雑な例です。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// カスタムパスをセッション構成ファイルの指定し、セッションID用にランダムな文字列を指定します
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// または手動で構成オプションをオーバーライドできます
		$session->updateConfiguration([
			// セッションデータをデータベースに保存する場合 (ログアウト機能を実装したい場合などに便利)
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // これを適切な別のものに変更してください
			Session::CONFIG_AUTO_COMMIT   => true, // 必要な場合にのみこれを実行し、セッションを commit() するのが難しい場合
												// また、Flight::after('start', function() { Flight::session()->commit(); }); を実行することもできます
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # PDO dns のためのデータベースドライバー 例(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # データベースホスト
				'db_name'   => 'my_app_database',   # データベース名
				'db_table'  => 'sessions',          # データベーステーブル
				'db_user'   => 'root',              # データベースユーザー名
				'db_pass'   => '',                  # データベースパスワード
				'persistent_conn'=> false,          # 毎回新しい接続を確立するオーバーヘッドを避け、より高速なWebアプリケーションを実現します。自分で裏側を見つけてください
			]
		]);
	}
);
```

## Documentation

完全なドキュメントについては [Github Readme](https://github.com/Ghostff/Session) をご覧ください。構成オプションは [default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) 内でよくドキュメント化されています。このパッケージを自分で調査したい場合は、コードは理解しやすいです。