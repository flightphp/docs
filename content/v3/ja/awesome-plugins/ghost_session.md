# Ghostff/Session

PHPセッションマネージャー（ノンブロッキング、フラッシュ、セグメント、セッション暗号化）。オプションの暗号化/復号化のためにPHPのopen_sslを使用します。ファイル、MySQL、Redis、Memcachedをサポートしています。

[こちら](https://github.com/Ghostff/Session)をクリックしてコードを表示します。

## インストール

コンポーザーでインストールします。

```bash
composer require ghostff/session
```

## 基本設定

セッションでデフォルト設定を使用するには、何も渡す必要はありません。詳細設定については[Github Readme](https://github.com/Ghostff/Session)を参照してください。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// 一つ覚えておくべきことは、各ページのロード時にセッションをコミットする必要があることです。
// さもなければ、設定でauto_commitを実行する必要があります。
```

## シンプルな例

これは、どのようにこれを使用するかのシンプルな例です。

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// ここにログインロジックを実装します
	// パスワードを検証します等。

	// ログインが成功した場合
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// セッションに書き込むたびに、必ず意図的にコミットする必要があります。
	$session->commit();
});

// このチェックは制限付きページロジックに含まれているか、ミドルウェアでラップされている可能性があります。
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// ここに制限付きページのロジックを実装します
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

これは、どのようにこれを使用するかのより複雑な例です。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// セッション設定ファイルへのカスタムパスを設定し、セッションIDにランダムな文字列を与えます
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// または手動で設定オプションをオーバーライドすることもできます
		$session->updateConfiguration([
			// セッションデータをデータベースに保存したい場合（「すべてのデバイスからログアウトする」機能のように）
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // これを別のものに変更してください
			Session::CONFIG_AUTO_COMMIT   => true, // 必要な場合、またはセッションをcommit()するのが難しい場合のみこれを行ってください。
												   // 追加でFlight::after('start', function() { Flight::session()->commit(); });を実行できます。
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # PDO DNS用のデータベースドライバー（例:mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # データベースホスト
				'db_name'   => 'my_app_database',   # データベース名
				'db_table'  => 'sessions',          # データベーステーブル
				'db_user'   => 'root',              # データベースユーザー名
				'db_pass'   => '',                  # データベースパスワード
				'persistent_conn'=> false,          # スクリプトがデータベースと通信するたびに新しい接続を確立するオーバーヘッドを避け、その結果、より高速なWebアプリケーションになります。自分で裏側を見つけてください
			]
		]);
	}
);
```

## 助けて！セッションデータが永続化されていません！

セッションデータを設定しているのに、それがリクエスト間で永続化されていないですか？セッションデータをコミットするのを忘れているかもしれません。セッションデータを設定した後に`$session->commit()`を呼び出すことでこれを行えます。

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// ここにログインロジックを実装します
	// パスワードを検証します等。

	// ログインが成功した場合
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// セッションに書き込むたびに、必ず意図的にコミットする必要があります。
	$session->commit();
});
```

これを回避するもう一つの方法は、セッションサービスを設定する際に、設定で`auto_commit`を`true`に設定する必要があることです。これにより、各リクエストの後にセッションデータが自動的にコミットされます。

```php

$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

さらに、`Flight::after('start', function() { Flight::session()->commit(); });`を実行して、各リクエストの後にセッションデータをコミットすることもできます。

## ドキュメント

完全なドキュメントについては[Github Readme](https://github.com/Ghostff/Session)を訪れてください。設定オプションは[default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php)ファイル内で十分に文書化されています。もしこのパッケージを自分で調べようとした場合、コードは理解しやすいです。