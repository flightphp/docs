# インストール

### 1. ファイルをダウンロードします。

[Composer](https://getcomposer.org) を使用している場合、以下のコマンドを実行できます：

```bash
composer require flightphp/core
```

または、[ダウンロード](https://github.com/flightphp/core/archive/master.zip)して、ウェブディレクトリに直接抽出することができます。

### 2. ウェブサーバを設定します。

*Apache* の場合、`.htaccess` ファイルを以下のように編集します：

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **注**: サブディレクトリで flight を使用する必要がある場合は、`RewriteEngine On` の直後に `RewriteBase /subdir/` を追加してください。
> **注**: データベースや環境ファイルなど、すべてのサーバーファイルを保護したい場合は、`.htaccess` ファイルに以下を追加してください：

```apache
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

*Nginx* の場合、サーバ宣言に以下を追加します：

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

### 3. `index.php` ファイルを作成します。

まず、フレームワークを含めます。

```php
require 'flight/Flight.php';
```

Composer を使用している場合は、オートローダーを実行します。

```php
require 'vendor/autoload.php';
```

次に、ルートを定義し、リクエストを処理する関数を割り当てます。

```php
Flight::route('/', function () {
  echo 'hello world!';
});
```

最後に、フレームワークを開始します。

```php
Flight::start();
```