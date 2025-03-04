# 什么是 Flight ?

Flight 是一个快速、简单、可扩展的 PHP 框架。
Flight 使您能够快速轻松地构建 RESTful 网络应用程序。

``` php
require 'flight/Flight.php';

// 定义路由
Flight::route('/', function(){
  echo 'hello world!';
});

// 启动 Flight
Flight::start();
```

[了解更多](learn)

# 需求

Flight 需要 PHP 7.4 或更高版本。

# 许可

Flight 根据 [MIT](https://github.com/mikecao/flight/blob/master/LICENSE) 许可发布。

# 社区

我们在 Matrix 上！请在 [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org) 与我们聊天。

# 贡献

本网站托管在 [Github](https://github.com/mikecao/flightphp.com) 上。
欢迎更新和语言翻译。