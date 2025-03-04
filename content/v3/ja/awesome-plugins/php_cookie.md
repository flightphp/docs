# クッキー

[overclokk/cookie](https://github.com/overclokk/cookie) はアプリ内でクッキーを管理するためのシンプルなライブラリです。

## インストール

composerを使用して簡単にインストールできます。

```bash
composer require overclokk/cookie
```

## 使用法

使用法は、Flightクラスに新しいメソッドを登録するだけです。

```php
use Overclokk\Cookie\Cookie;

/*
 * ブートストラップまたはpublic/index.phpファイルに設定
 */

Flight::register('cookie', Cookie::class);

/**
 * ExampleController.php
 */

class ExampleController {
	public function login() {
		// クッキーを設定します

		// インスタンスを取得するためfalseである必要があります
		// オートコンプリートを有効にしたい場合は以下のコメントを使用してください
		/** @var \Overclokk\Cookie\Cookie $cookie */
		$cookie = Flight::cookie(false);
		$cookie->set(
			'stay_logged_in', // クッキーの名前
			'1', // 設定したい値
			86400, // クッキーの有効期間（秒）
			'/', // クッキーが利用可能なパス
			'example.com', // クッキーが利用可能なドメイン
			true, // セキュアな HTTPS 接続でのみクッキーが送信されます
			true // クッキーはHTTPプロトコルを介してのみ利用可能です
		);

		// オプションで、デフォルト値を維持したい場合や、
		// 長期間にわたってクッキーを簡単に設定したい場合
		$cookie->forever('stay_logged_in', '1');
	}

	public function home() {
		// クッキーがあるかどうかをチェック
		if (Flight::cookie()->has('stay_logged_in')) {
			// 例えば、ダッシュボードエリアにリダイレクトします。
			Flight::redirect('/dashboard');
		}
	}
}