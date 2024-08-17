# インストール

## ファイルのダウンロード

システムにPHPがインストールされていることを確認してください。されていない場合は、システムにインストールする方法については[こちら](#installing-php)をクリックしてください。

[Composer](https://getcomposer.org)を使用している場合は、次のコマンドを実行できます:

```bash
composer require flightphp/core
```

もしくはファイルを[ダウンロード](https://github.com/flightphp/core/archive/master.zip)して、それらをウェブディレクトリに直接展開します。

## Webサーバーの設定

### 組み込みのPHP開発サーバー

これは最も簡単に立ち上げる方法です。組み込みサーバーを使用してアプリケーションを実行したり、SQLiteをデータベースとして使用したりすることができます（システムにsqlite3がインストールされている限り）、そして多くのものを必要としません！ PHPがインストールされたら、次のコマンドを実行してください:

```bash
php -S localhost:8000
```

その後、ブラウザを開いて`http://localhost:8000`に移動します。

プロジェクトのドキュメントルートを異なるディレクトリにする場合（例: プロジェクトが `~/myproject` であるが、ドキュメントルートは `~/myproject/public/` である場合）、`~/myproject` ディレクトリにいる場合は、以下のコマンドを実行できます:

```bash
php -S localhost:8000 -t public/
```

その後、ブラウザを開いて`http://localhost:8000`に移動します。

### Apache

Apacheが既にシステムにインストールされていることを確認してください。されていない場合は、システムにApacheをインストールする方法をGoogleで調べてください。

Apacheの場合、`.htaccess` ファイルを次のように編集してください:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **注意**: サブディレクトリでflightを使用する必要がある場合は、`RewriteEngine On`の直後に次の行を追加してください: `RewriteBase /subdir/`

> **注意**: dbやenvファイルなどのすべてのサーバーファイルを保護する必要がある場合は、`.htaccess` ファイルに次の内容を追加してください:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

Nginxが既にシステムにインストールされていることを確認してください。Nginxをインストールしていない場合は、システムにNginx Apacheをインストールする方法をGoogleで調べてください。

Nginxの場合、サーバー宣言に次の内容を追加してください:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## `index.php` ファイルの作成

```php
<?php

// Composerを使用している場合は、オートローダーを要求します。
require 'vendor/autoload.php';
// Composerを使用していない場合は、フレームワークを直接ロードしてください
// require 'flight/Flight.php';

// 次にルートを定義し、リクエストを処理するための関数を割り当てます。
Flight::route('/', function () {
  echo 'hello world!';
});

// 最後に、フレームワークを起動します。
Flight::start();
```

## PHPのインストール

すでにシステムに `php` がインストールされている場合は、この手順をスキップして[ダウンロードセクション](#download-the-files)に移動してください

分かりました！macOS、Windows 10/11、Ubuntu、Rocky Linux にPHPをインストールする手順についての説明があります。さらに、異なるバージョンのPHPをインストールする方法についても説明します。