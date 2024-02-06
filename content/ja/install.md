# インストール

## ファイルをダウンロードします。

もし[Composer](https://getcomposer.org)を使用している場合、以下のコマンドを実行できます:

```bash
composer require flightphp/core
```

または[ファイルをダウンロード](https://github.com/flightphp/core/archive/master.zip)して、それらをウェブディレクトリに直接展開できます。

## Webサーバーを設定します。

### Apache
Apacheの場合、`.htaccess`ファイルを以下のように編集します:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **注意**: もしflightをサブディレクトリで使用する必要がある場合は、`RewriteEngine On`のすぐ後に`RewriteBase /subdir/`という行を追加してください。

> **注意**: もしdbやenvファイルなどのすべてのサーバーファイルを保護したい場合は、次の内容を`.htaccess`ファイルに追加してください:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

Nginxの場合、以下をサーバーの宣言に追加してください:

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

// もしComposerを使用している場合、オートローダーをrequireしてください。
require 'vendor/autoload.php';
// もしComposerを使用していない場合は、フレームワークを直接ロードしてください
// require 'flight/Flight.php';

// 次に、ルートを定義し、リクエストを処理する関数を割り当てます。
Flight::route('/', function () {
  echo 'hello world!';
});

// 最後に、フレームワークを起動します。
Flight::start();
```