# 安装说明

在安装 Flight 之前，需要满足一些基本先决条件。具体来说，您需要：

1. [在您的系统上安装 PHP](#installing-php)
2. [安装 Composer](https://getcomposer.org) 以获得最佳的开发者体验。

## 基本安装

如果您使用 [Composer](https://getcomposer.org)，可以运行以下命令：

```bash
composer require flightphp/core
```

这只会将 Flight 核心文件安装到您的系统上。您需要定义项目结构、[布局](/learn/templates)、[依赖项](/learn/dependency-injection-container)、[配置](/learn/configuration)、[自动加载](/learn/autoloading) 等。此方法确保除了 Flight 之外不会安装其他依赖项。

您也可以[直接下载文件](https://github.com/flightphp/core/archive/master.zip)
并将它们解压到您的 Web 目录中。

## 推荐安装

强烈推荐为任何新项目从 [flightphp/skeleton](https://github.com/flightphp/skeleton) 应用开始。安装非常简单。

```bash
composer create-project flightphp/skeleton my-project/
```

这将设置您的项目结构，使用命名空间配置自动加载，设置配置，并提供其他工具，如 [Tracy](/awesome-plugins/tracy)、[Tracy 扩展](/awesome-plugins/tracy-extensions) 和 [Runway](/awesome-plugins/runway)。

## 配置您的 Web 服务器

### 内置 PHP 开发服务器

这是启动和运行的最简单方法。您可以使用内置服务器运行您的应用，甚至使用 SQLite 作为数据库（只要您的系统上安装了 sqlite3），并且几乎不需要任何其他东西！只要 PHP 已安装，只需运行以下命令：

```bash
php -S localhost:8000
# 或使用 skeleton 应用
composer start
```

然后打开您的浏览器并访问 `http://localhost:8000`。

如果您想将项目文档根目录设置为不同的目录（例如：您的项目是 `~/myproject`，但文档根目录是 `~/myproject/public/`），您可以在 `~/myproject` 目录中运行以下命令：

```bash
php -S localhost:8000 -t public/
# 使用 skeleton 应用，这已配置好
composer start
```

然后打开您的浏览器并访问 `http://localhost:8000`。

### Apache

确保您的系统上已安装 Apache。如果没有，请在 Google 上搜索如何在您的系统上安装 Apache。

对于 Apache，请使用以下内容编辑您的 `.htaccess` 文件：

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **注意**：如果您需要在子目录中使用 flight，请在 `RewriteEngine On` 之后添加一行
> `RewriteBase /subdir/`。

> **注意**：如果您想保护所有服务器文件，如数据库或环境文件。
> 将此内容放入您的 `.htaccess` 文件中：

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

确保您的系统上已安装 Nginx。如果没有，请在 Google 上搜索如何在您的系统上安装 Nginx。

对于 Nginx，请将以下内容添加到您的服务器声明中：

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## 创建您的 `index.php` 文件

如果您进行基本安装，您需要一些代码来开始。

```php
<?php

// 如果您使用 Composer，请要求加载自动加载器。
require 'vendor/autoload.php';
// 如果您不使用 Composer，请直接加载框架
// require 'flight/Flight.php';

// 然后定义一个路由并分配一个函数来处理请求。
Flight::route('/', function () {
  echo 'hello world!';
});

// 最后，启动框架。
Flight::start();
```

使用 skeleton 应用，这已在您的 `app/config/routes.php` 文件中配置好并处理。服务在 `app/config/services.php` 中配置。

## 安装 PHP

如果您的系统上已安装 `php`，请跳过这些说明并转到[下载部分](#download-the-files)。

### **macOS**

#### **使用 Homebrew 安装 PHP**

1. **安装 Homebrew**（如果尚未安装）：
   - 打开终端并运行：
     ```bash
     /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
     ```

2. **安装 PHP**：
   - 安装最新版本：
     ```bash
     brew install php
     ```
   - 要安装特定版本，例如 PHP 8.1：
     ```bash
     brew tap shivammathur/php
     brew install shivammathur/php/php@8.1
     ```

3. **在 PHP 版本之间切换**：
   - 取消链接当前版本并链接所需版本：
     ```bash
     brew unlink php
     brew link --overwrite --force php@8.1
     ```
   - 验证安装的版本：
     ```bash
     php -v
     ```

### **Windows 10/11**

#### **手动安装 PHP**

1. **下载 PHP**：
   - 访问 [PHP for Windows](https://windows.php.net/download/) 并下载最新版本或特定版本（例如，7.4、8.0）作为非线程安全 zip 文件。

2. **解压 PHP**：
   - 将下载的 zip 文件解压到 `C:\php`。

3. **将 PHP 添加到系统 PATH**：
   - 转到 **系统属性** > **环境变量**。
   - 在 **系统变量** 下，找到 **Path** 并点击 **编辑**。
   - 添加路径 `C:\php`（或您解压 PHP 的位置）。
   - 点击 **确定** 关闭所有窗口。

4. **配置 PHP**：
   - 将 `php.ini-development` 复制到 `php.ini`。
   - 编辑 `php.ini` 以按需配置 PHP（例如，设置 `extension_dir`，启用扩展）。

5. **验证 PHP 安装**：
   - 打开命令提示符并运行：
     ```cmd
     php -v
     ```

#### **安装多个 PHP 版本**

1. **为每个版本重复上述步骤**，将每个版本放置在单独的目录中（例如，`C:\php7`、`C:\php8`）。

2. **通过调整系统 PATH 变量指向所需版本目录在版本之间切换**。

### **Ubuntu (20.04, 22.04 等)**

#### **使用 apt 安装 PHP**

1. **更新软件包列表**：
   - 打开终端并运行：
     ```bash
     sudo apt update
     ```

2. **安装 PHP**：
   - 安装最新 PHP 版本：
     ```bash
     sudo apt install php
     ```
   - 要安装特定版本，例如 PHP 8.1：
     ```bash
     sudo apt install php8.1
     ```

3. **安装附加模块**（可选）：
   - 例如，要安装 MySQL 支持：
     ```bash
     sudo apt install php8.1-mysql
     ```

4. **在 PHP 版本之间切换**：
   - 使用 `update-alternatives`：
     ```bash
     sudo update-alternatives --set php /usr/bin/php8.1
     ```

5. **验证安装的版本**：
   - 运行：
     ```bash
     php -v
     ```

### **Rocky Linux**

#### **使用 yum/dnf 安装 PHP**

1. **启用 EPEL 仓库**：
   - 打开终端并运行：
     ```bash
     sudo dnf install epel-release
     ```

2. **安装 Remi's 仓库**：
   - 运行：
     ```bash
     sudo dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
     sudo dnf module reset php
     ```

3. **安装 PHP**：
   - 要安装默认版本：
     ```bash
     sudo dnf install php
     ```
   - 要安装特定版本，例如 PHP 7.4：
     ```bash
     sudo dnf module install php:remi-7.4
     ```

4. **在 PHP 版本之间切换**：
   - 使用 `dnf` 模块命令：
     ```bash
     sudo dnf module reset php
     sudo dnf module enable php:remi-8.0
     sudo dnf install php
     ```

5. **验证安装的版本**：
   - 运行：
     ```bash
     php -v
     ```

### **一般说明**

- 对于开发环境，按照项目要求配置 PHP 设置非常重要。
- 在切换 PHP 版本时，确保为您打算使用的特定版本安装所有相关 PHP 扩展。
- 在切换 PHP 版本或更新配置后，重启您的 Web 服务器（Apache、Nginx 等）以应用更改。