# 安装

## 下载文件

如果您正在使用[Composer](https://getcomposer.org)，可以运行以下命令：

```bash
composer require flightphp/core
```

或者您可以直接[下载文件](https://github.com/flightphp/core/archive/master.zip) 并将其提取到您的 web 目录中。

## 配置您的 Web 服务器

### Apache

对于 Apache，请编辑您的 `.htaccess` 文件如下：

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **注意**: 如果您需要在子目录中使用 Flight，请在 `RewriteEngine On` 之后添加 `RewriteBase /subdir/`。

> **注意**: 如果您希望保护所有服务器文件，例如 db 或 env 文件，
> 请将以下内容放入您的 `.htaccess` 文件中：

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

对于 Nginx，请将以下内容添加到您的服务器声明中：

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

// 如果您正在使用 Composer，请要求自动加载程序。
require 'vendor/autoload.php';
// 如果您没有使用 Composer，请直接加载框架
// require 'flight/Flight.php';

// 然后定义一个路由并分配一个函数来处理请求。
Flight::route('/', function () {
  echo 'hello world!';
});

// 最后，启动框架。
Flight::start();
```  