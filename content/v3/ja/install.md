# インストール

## ファイルをダウンロードします。

もし[Composer](https://getcomposer.org)を使用している場合、次のコマンドを実行できます:

```bash
composer require flightphp/core
```

または、[ファイルをダウンロード](https://github.com/flightphp/core/archive/master.zip)して、それらをウェブディレクトリに直接展開することもできます。

## ウェブサーバを構成します。

### Apache
Apacheを使用する場合、`.htaccess`ファイルを以下のように編集してください:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **注意**: サブディレクトリでflightを使用する必要がある場合は、`RewriteEngine On`の直後に行を追加してください: `RewriteBase /subdir/`。

> **注意**: データベースや環境ファイルなどのすべてのサーバファイルを保護する必要がある場合は、`.htaccess`ファイルに以下を追加してください:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx
Nginxを使用する場合、以下をサーバ定義に追加してください:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## `index.php`ファイルを作成します。

```php
<?php

// Composerを使用している場合、オートローダーを要求します。
require 'vendor/autoload.php';
// Composerを使用していない場合、フレームワークを直接ロードします
// require 'flight/Flight.php';

// 次に、ルートを定義し、リクエストを処理するための関数を割り当てます。
Flight::route('/', function () {
  echo 'hello world!';
});

// 最後に、フレームワークをスタートします。
Flight::start();
```