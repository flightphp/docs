# 安装

## 下载文件

确保您的系统上已安装PHP。如果没有，请单击[这里](#installing-php)获取有关如何为您的系统安装它的说明。

如果您使用[Composer](https://getcomposer.org)，可以运行以下命令：

```bash
composer require flightphp/core
```

或者您可以[下载文件](https://github.com/flightphp/core/archive/master.zip)并将其直接提取到您的web目录中。

## 配置您的 Web 服务器

### 内置 PHP 开发服务器

这是迄今为止最简单的启动方式。您可以使用内置服务器来运行应用程序，甚至可以使用SQLite作为数据库（只要您的系统上安装了sqlite3）而无需进行太多设置！只需在安装了PHP后运行以下命令：

```bash
php -S localhost:8000
```

然后在浏览器中打开`http://localhost:8000`。

如果您想将项目的文档根目录设置为不同的目录（例如：您的项目是`~/myproject`，但您的文档根目录是`~/myproject/public/`），则可以在进入`~/myproject`目录后运行以下命令：

```bash
php -S localhost:8000 -t public/
```

然后在浏览器中打开`http://localhost:8000`。

### Apache

确保Apache已经安装在您的系统上。如果没有，请搜索如何在您的系统上安装Apache。

对于Apache，请使用以下内容编辑您的`.htaccess`文件：

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **注意**：如果您需要在子目录中使用flight，请在`RewriteEngine On`之后添加一行`RewriteBase /subdir/`。

> **注意**：如果要保护所有服务器文件，例如数据库或env文件。请将以下内容放入您的`.htaccess`文件：

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

确保Nginx已经安装在您的系统上。如果没有，请搜索如何在您的系统上安装Nginx。

对于Nginx，请将以下内容添加到您的服务器声明中：

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## 创建您的 `index.php` 文件

```php
<?php

// If you're using Composer, require the autoloader.
require 'vendor/autoload.php';
// 如果您没有使用Composer，请直接加载框架
// require 'flight/Flight.php';

// 然后定义一个路由，并分配一个处理请求的函数。
Flight::route('/', function () {
  echo 'hello world!';
});

// 最后，启动框架。
Flight::start();
```

## 安装 PHP

如果您的系统上已安装`php`，请跳过这些说明并转到[下载部分](#download-the-files)

当然！以下是在macOS、Windows 10/11、Ubuntu 和 Rocky Linux上安装PHP的说明。我还将包括有关如何安装不同版本的PHP的详细信息。

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
   - 要安装特定版本，例如，PHP 8.1：
     ```bash
     brew tap shivammathur/php
     brew install shivammathur/php/php@8.1
     ```

3. **在不同版本之间切换**：
   - 解除当前版本的链接并链接所需的版本：
     ```bash
     brew unlink php
     brew link --overwrite --force php@8.1
     ```
   - 验证已安装的版本：
     ```bash
     php -v
     ```

### **Windows 10/11**

#### **手动安装 PHP**

1. **下载 PHP**：
   - 访问[PHP for Windows](https://windows.php.net/download/)，下载最新版本或特定版本（例如，7.4、8.0）的非线程安全zip文件。

2. **解压 PHP**：
   - 将下载的zip文件解压缩到`C:\php`目录。

3. **将 PHP 添加到系统PATH**：
   - 转到**系统属性** > **环境变量**。
   - 在**系统变量**下，找到**Path**并单击**编辑**。
   - 添加路径`C:\php`（或者您解压缩PHP的任何位置）。
   - 点击**确定**关闭所有窗口。

4. **配置 PHP**：
   - 将`php.ini-development`复制到`php.ini`。
   - 编辑`php.ini`以根据需要配置PHP（例如，设置`extension_dir`，启用扩展）。

5. **验证 PHP 安装**：
   - 打开命令提示符并运行：
     ```cmd
     php -v
     ```

#### **安装多个 PHP 版本**

1. 对于每个版本，重复上述步骤，将每个版本放在单独的目录中（例如，`C:\php7`，`C:\php8`）。

2. 通过调整系统PATH变量指向所需版本目录来在不同版本之间切换。

### **Ubuntu（20.04、22.04等）**

#### **使用apt安装 PHP**

1. **更新软件包列表**：
   - 打开终端并运行：
     ```bash
     sudo apt update
     ```

2. **安装 PHP**：
   - 安装最新的PHP版本：
     ```bash
     sudo apt install php
     ```
   - 要安装特定版本，例如，PHP 8.1：
     ```bash
     sudo apt install php8.1
     ```

3. **安装额外模块**（可选）：
   - 例如，安装MySQL支持：
     ```bash
     sudo apt install php8.1-mysql
     ```

4. **在PHP版本之间切换**：
   - 使用`update-alternatives`：
     ```bash
     sudo update-alternatives --set php /usr/bin/php8.1
     ```

5. **验证已安装的版本**：
   - 运行：
     ```bash
     php -v
     ```

### **Rocky Linux**

#### **使用yum/dnf安装 PHP**

1. **启用 EPEL repository**：
   - 打开终端并运行：
     ```bash
     sudo dnf install epel-release
     ```

2. **安装 Remi's repository**：
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
   - 要安装特定版本，例如，PHP 7.4：
     ```bash
     sudo dnf module install php:remi-7.4
     ```

4. **在PHP版本之间切换**：
   - 使用`dnf`模块命令：
     ```bash
     sudo dnf module reset php
     sudo dnf module enable php:remi-8.0
     sudo dnf install php
     ```

5. **验证已安装的版本**：
   - 运行：
     ```bash
     php -v
     ```

### **一般说明**

- 对于开发环境，根据项目要求配置PHP设置非常重要。
- 在切换PHP版本时，确保针对您打算使用的特定版本安装了所有相关的PHP扩展。
- 在切换PHP版本或更新配置后，重新启动您的Web服务器（Apache、Nginx等）以应用更改。