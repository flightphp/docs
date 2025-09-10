# 安装

### 1. 下载文件。

如果您使用的是 [Composer](https://getcomposer.org)，您可以运行以下命令：

```bash
composer require flightphp/core
```

或者您可以直接 [下载](https://github.com/flightphp/core/archive/master.zip) 文件并将其提取到您的 Web 目录中。

### 2. 配置您的 Web 服务器。

对于 *Apache*，用以下内容编辑您的 `.htaccess` 文件：

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **注意**：如果您需要在子目录中使用 flight，请在 `RewriteEngine On` 后添加一行 `RewriteBase /subdir/`。
> **注意**：如果您想保护所有服务器文件，比如数据库或环境文件。
> 请将其放入您的 `.htaccess` 文件中：

```apache
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

对于 *Nginx*，在您的服务器声明中添加以下内容：

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

### 3. 创建您的 `index.php` 文件。

首先引入框架。

```php
require 'flight/Flight.php';
```

如果您使用 Composer，请改为运行自动加载器。

```php
require 'vendor/autoload.php';
```

然后定义一个路由并分配一个函数来处理请求。

```php
Flight::route('/', function () {
  echo '你好，世界！';
});
```

最后，启动框架。

```php
Flight::start();
```
