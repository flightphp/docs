# Ghostff/Session

PHP セッションマネージャー（非ブロッキング、フラッシュ、セグメント、セッション暗号化）。PHP open_ssl を使用してセッション データのオプションの暗号化/復号化をサポートします。File, MySQL, Redis, and Memcached をサポートします。

[こちら](https://github.com/Ghostff/Session)をクリックしてコードを表示します。

## インストール

Composer でインストールします。

```bash
composer require ghostff/session
```

## 基本的な構成

デフォルトの設定を使用するには何も渡す必要はありません。詳細な設定については、[Github Readme](https://github.com/Ghostff/Session)を参照してください。

```php
use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// 各ページの読み込みでセッションをコミットする必要がありますことを覚えておいてください
// または、構成で auto_commit を実行する必要があります。
```

## 簡単な例

これがこの使用方法の簡単な例です。

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// ここでログインのロジックを実行します
	// パスワードを検証するなど。

	// ログインに成功した場合
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// セッションに書き込んだら、意図的にコミットする必要があります。
	$session->commit();
});

// このチェックは制限されたページのロジックで実行するか、ミドルウェアでラップできます。
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// ここで制限されたページのロジックを実行します
});

// ミドルウェア版
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

これがこの使用方法のより複雑な例です。

```php
use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// 最初の引数としてセッション構成ファイルのカスタムパスを設定します
// または、カスタム配列を与えます
$app->register('session', Session::class, [ 
	[
		// セッション データをデータベースに保存したい場合（例: 「すべてのデバイスからログアウト」機能）
		Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
		Session::CONFIG_ENCRYPT_DATA  => true,
		Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // これは別のものに変更してください
		Session::CONFIG_AUTO_COMMIT   => true, // これは必要で、commit() が難しい場合のみ実行してください。
												// さらに、Flight::after('start', function() { Flight::session()->commit(); }); を実行できます。
		Session::CONFIG_MYSQL_DS         => [
			'driver'    => 'mysql',             # Database driver for PDO dns eg(mysql:host=...;dbname=...)
			'host'      => '127.0.0.1',         # Database host
			'db_name'   => 'my_app_database',   # Database name
			'db_table'  => 'sessions',          # Database table
			'db_user'   => 'root',              # Database username
			'db_pass'   => '',                  # Database password
			'persistent_conn'=> false,          # スクリプトがデータベースにアクセスするたびに新しい接続を確立するオーバーヘッドを避ける。詳細は自分で確認してください
		]
	] 
]);
```

## 助けて！ 私のセッションデータが持続しません！

セッションデータを設定してもリクエスト間で持続しない場合、セッションデータをコミットすることを忘れている可能性があります。$session->commit() を呼び出して設定した後でこれを実行してください。

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// ここでログインのロジックを実行します
	// パスワードを検証するなど。

	// ログインに成功した場合
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// セッションに書き込んだら、意図的にコミットする必要があります。
	$session->commit();
});
```

これを回避する方法として、セッション サービスを設定するときに構成で `auto_commit` を `true` に設定します。これにより、各リクエスト後にセッションデータが自動的にコミットされます。

```php
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

さらに、`Flight::after('start', function() { Flight::session()->commit(); });` を実行して各リクエスト後にセッションデータをコミットすることもできます。

## ドキュメント

完全なドキュメントについては、[Github Readme](https://github.com/Ghostff/Session)を訪問してください。構成オプションは[default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) ファイル自体でよく文書化されています。コードは自分で確認したい場合に簡単に理解できます。