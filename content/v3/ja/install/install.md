# インストール手順

Flight をインストールする前に、いくつかの基本的な前提条件があります。つまり、以下のことをする必要があります：

1. [システムに PHP をインストールする](#installing-php)
2. 最高の開発者体験のために [Composer をインストールする](https://getcomposer.org)。

## 基本インストール

[Composer](https://getcomposer.org) を使用している場合、以下のコマンドを実行できます：

```bash
composer require flightphp/core
```

これは、Flight のコアファイルのみをシステムに配置します。プロジェクト構造、[レイアウト](/learn/templates)、[依存関係](/learn/dependency-injection-container)、[設定](/learn/configuration)、[自動読み込み](/learn/autoloading) などを定義する必要があります。この方法により、Flight 以外の依存関係はインストールされません。

また、[ファイルをダウンロード](https://github.com/flightphp/core/archive/master.zip) して、Web ディレクトリに抽出することもできます。

## 推奨インストール

新しいプロジェクトの場合、[flightphp/skeleton](https://github.com/flightphp/skeleton) アプリから始めることを強く推奨します。インストールは簡単です。

```bash
composer create-project flightphp/skeleton my-project/
```

これにより、プロジェクト構造が設定され、名前空間付きの自動読み込みが構成され、設定がセットアップされ、[Tracy](/awesome-plugins/tracy)、[Tracy Extensions](/awesome-plugins/tracy-extensions)、[Runway](/awesome-plugins/runway) などの他のツールが提供されます。

## Web サーバーの設定

### ビルトイン PHP 開発サーバー

これは、起動して実行する最も簡単な方法です。ビルトインサーバーを使用してアプリケーションを実行し、データベースとして SQLite を使用することもできます（システムに sqlite3 がインストールされている限り）。ほとんど何も必要ありません！ PHP がインストールされたら、以下のコマンドを実行するだけです：

```bash
php -S localhost:8000
# または skeleton アプリの場合
composer start
```

次に、ブラウザを開いて `http://localhost:8000` にアクセスします。

プロジェクトのドキュメントルートを別のディレクトリにしたい場合（例: プロジェクトが `~/myproject` ですが、ドキュメントルートが `~/myproject/public/` の場合）、`~/myproject` ディレクトリ内にいる状態で以下のコマンドを実行できます：

```bash
php -S localhost:8000 -t public/
# skeleton アプリの場合、これはすでに構成されています
composer start
```

次に、ブラウザを開いて `http://localhost:8000` にアクセスします。

### Apache

システムに Apache がすでにインストールされていることを確認してください。インストールされていない場合、システムに Apache をインストールする方法を Google で検索してください。

Apache の場合、`.htaccess` ファイルを以下のように編集します：

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **注意**: サブディレクトリで flight を使用する必要がある場合、`RewriteEngine On` の直後に `RewriteBase /subdir/` の行を追加します。

> **注意**: サーバーのすべてのファイルを保護したい場合、例えば db や env ファイルのように。
> `.htaccess` ファイルに以下を配置します：

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

システムに Nginx がすでにインストールされていることを確認してください。インストールされていない場合、システムに Nginx をインストールする方法を Google で検索してください。

Nginx の場合、サーバー宣言に以下を追加します：

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## `index.php` ファイルの作成

基本インストールを行っている場合、開始するためのコードが必要です。

```php
<?php

// Composer を使用している場合、オートローダーを require します。
require 'vendor/autoload.php';
// Composer を使用していない場合、フレームワークを直接ロードします
// require 'flight/Flight.php';

// 次に、ルートを定義し、リクエストを処理する関数を割り当てます。
Flight::route('/', function () {
  echo 'hello world!';
});

// 最後に、フレームワークを開始します。
Flight::start();
```

skeleton アプリの場合、これはすでに構成されており、`app/config/routes.php` ファイルで処理されます。サービスは `app/config/services.php` で構成されます。

## PHP のインストール

システムに `php` がすでにインストールされている場合、これらの手順をスキップして [ダウンロードセクション](#download-the-files) に進んでください。

### **macOS**

#### **Homebrew を使用した PHP のインストール**

1. **Homebrew をインストール** (すでにインストールされていない場合)：
   - ターミナルを開いて実行：
     ```bash
     /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
     ```

2. **PHP をインストール**：
   - 最新バージョンをインストール：
     ```bash
     brew install php
     ```
   - 特定のバージョンをインストールする場合、例えば PHP 8.1：
     ```bash
     brew tap shivammathur/php
     brew install shivammathur/php/php@8.1
     ```

3. **PHP バージョンの切り替え**：
   - 現在のバージョンをリンク解除し、希望のバージョンをリンク：
     ```bash
     brew unlink php
     brew link --overwrite --force php@8.1
     ```
   - インストールされたバージョンを確認：
     ```bash
     php -v
     ```

### **Windows 10/11**

#### **PHP を手動でインストール**

1. **PHP をダウンロード**：
   - [PHP for Windows](https://windows.php.net/download/) を訪れて、最新版または特定のバージョン（例: 7.4, 8.0）を non-thread-safe zip ファイルとしてダウンロードします。

2. **PHP を抽出**：
   - ダウンロードした zip ファイルを `C:\php` に抽出します。

3. **PHP をシステム PATH に追加**：
   - **システムのプロパティ** > **環境変数** に移動。
   - **システム変数**の下で **Path** を見つけ、**編集** をクリック。
   - パス `C:\php` (または PHP を抽出した場所) を追加。
   - すべてのウィンドウを閉じるために **OK** をクリック。

4. **PHP を構成**：
   - `php.ini-development` を `php.ini` にコピー。
   - `php.ini` を編集して PHP を必要に応じて構成（例: `extension_dir` の設定、拡張機能の有効化）。

5. **PHP インストールの確認**：
   - コマンドプロンプトを開いて実行：
     ```cmd
     php -v
     ```

#### **複数の PHP バージョンをインストール**

1. **上記のステップを各バージョンで繰り返す**、各々を別々のディレクトリに配置（例: `C:\php7`, `C:\php8`）。

2. **バージョンの切り替え** は、システム PATH 変数を希望のバージョンディレクトリを指すように調整します。

### **Ubuntu (20.04, 22.04 など)**

#### **apt を使用した PHP のインストール**

1. **パッケージリストを更新**：
   - ターミナルを開いて実行：
     ```bash
     sudo apt update
     ```

2. **PHP をインストール**：
   - 最新の PHP バージョンをインストール：
     ```bash
     sudo apt install php
     ```
   - 特定のバージョンをインストールする場合、例えば PHP 8.1：
     ```bash
     sudo apt install php8.1
     ```

3. **追加モジュールをインストール** (オプション)：
   - 例えば、MySQL サポートをインストール：
     ```bash
     sudo apt install php8.1-mysql
     ```

4. **PHP バージョンの切り替え**：
   - `update-alternatives` を使用：
     ```bash
     sudo update-alternatives --set php /usr/bin/php8.1
     ```

5. **インストールされたバージョンを確認**：
   - 実行：
     ```bash
     php -v
     ```

### **Rocky Linux**

#### **yum/dnf を使用した PHP のインストール**

1. **EPEL リポジトリを有効化**：
   - ターミナルを開いて実行：
     ```bash
     sudo dnf install epel-release
     ```

2. **Remi のリポジトリをインストール**：
   - 実行：
     ```bash
     sudo dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
     sudo dnf module reset php
     ```

3. **PHP をインストール**：
   - デフォルトバージョンをインストール：
     ```bash
     sudo dnf install php
     ```
   - 特定のバージョンをインストールする場合、例えば PHP 7.4：
     ```bash
     sudo dnf module install php:remi-7.4
     ```

4. **PHP バージョンの切り替え**：
   - `dnf` モジュールコマンドを使用：
     ```bash
     sudo dnf module reset php
     sudo dnf module enable php:remi-8.0
     sudo dnf install php
     ```

5. **インストールされたバージョンを確認**：
   - 実行：
     ```bash
     php -v
     ```

### **一般的な注意事項**

- 開発環境の場合、プロジェクトの要件に応じて PHP 設定を構成することが重要です。
- PHP バージョンを切り替える際、使用する予定の特定のバージョンにすべての関連 PHP 拡張機能がインストールされていることを確認してください。
- PHP バージョンを切り替えたり、設定を更新した後、変更を適用するために Web サーバー (Apache、Nginx など) を再起動してください。