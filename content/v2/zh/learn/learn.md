# 学习

此页面是学习 Flight 的指南。它涵盖了框架的基础知识以及如何使用它。

## <a name="routing"></a> 路由

在 Flight 中，路由通过将 URL 模式与回调函数进行匹配来完成。

``` php
Flight::route('/', function(){
    echo '你好，世界！';
});
```

回调可以是任何可调用的对象。因此，您可以使用常规函数：

``` php
function hello(){
    echo '你好，世界！';
}

Flight::route('/', 'hello');
```

或类方法：

``` php
class Greeting {
    public static function hello() {
        echo '你好，世界！';
    }
}

Flight::route('/', array('Greeting','hello'));
```

或对象方法：

``` php
class Greeting
{
    public function __construct() {
        $this->name = '约翰·多';
    }

    public function hello() {
        echo "你好，{$this->name}!";
    }
}

$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

路由按定义的顺序匹配。第一个匹配请求的路由将被调用。

### 方法路由

默认情况下，路由模式将与所有请求方法进行匹配。您可以通过在 URL 之前放置标识符来响应特定方法。

``` php
Flight::route('GET /', function(){
    echo '我收到了一个 GET 请求。';
});

Flight::route('POST /', function(){
    echo '我收到了一个 POST 请求。';
});
```

您还可以通过使用 `|` 分隔符将多个方法映射到单个回调：

``` php
Flight::route('GET|POST /', function(){
    echo '我收到了一个 GET 或 POST 请求。';
});
```

### 正则表达式

您可以在路由中使用正则表达式：

``` php
Flight::route('/user/[0-9]+', function(){
    // 这将匹配 /user/1234
});
```

### 命名参数

您可以在路由中指定命名参数，这些参数将传递给回调函数。

``` php
Flight::route('/@name/@id', function($name, $id){
    echo "你好, $name ($id)!";
});
```

您还可以通过使用 `:` 分隔符包含正则表达式与命名参数：

``` php
Flight::route('/@name/@id:[0-9]{3}', function($name, $id){
    // 这将匹配 /bob/123
    // 但不会匹配 /bob/12345
});
```

### 可选参数

您可以通过将片段放在括号中来指定可选参数进行匹配。

``` php
Flight::route('/blog(/@year(/@month(/@day)))', function($year, $month, $day){
    // 这将匹配以下 URL：
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
});
```

任何未匹配的可选参数将作为 NULL 传递。

### 通配符

匹配仅在单个 URL 片段上进行。如果要匹配多个片段，可以使用 `*` 通配符。

``` php
Flight::route('/blog/*', function(){
    // 这将匹配 /blog/2000/02/01
});
```

要将所有请求路由到单个回调，可以这样做：

``` php
Flight::route('*', function(){
    // 做一些事情
});
```

### 继续

您可以通过在回调函数中返回 `true` 将执行传递给下一个匹配的路由。

``` php
Flight::route('/user/@name', function($name){
    // 检查某个条件
    if ($name != "Bob") {
        // 继续到下一个路由
        return true;
    }
});

Flight::route('/user/*', function(){
    // 这将被调用
});
```

### 路由信息

如果您想查看匹配的路由信息，可以通过将 `true` 作为第三个参数传递给路由方法来请求将路由对象传递给回调。路由对象将始终是传递给回调函数的最后一个参数。

``` php
Flight::route('/', function($route){
    // 匹配的 HTTP 方法数组
    $route->methods;

    // 命名参数数组
    $route->params;

    // 匹配的正则表达式
    $route->regex;

    // 包含 URL 模式中使用的任何 '*' 的内容
    $route->splat;
}, true);
```

### 路由分组

有时您可能想将相关路由分组在一起（如 `/api/v1`）。您可以使用 `group` 方法做到这一点：

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
    // 匹配 /api/v1/users
  });

  Flight::route('/posts', function () {
    // 匹配 /api/v1/posts
  });
});
```

您甚至可以嵌套分组：

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
    // Flight::get() 获取变量，而不是设置路由！请查看下面的对象上下文
    Flight::route('GET /users', function () {
      // 匹配 GET /api/v1/users
    });

    Flight::post('/posts', function () {
      // 匹配 POST /api/v1/posts
    });

    Flight::put('/posts/1', function () {
      // 匹配 PUT /api/v1/posts
    });
  });
  Flight::group('/v2', function () {
    // Flight::get() 获取变量，而不是设置路由！请查看下面的对象上下文
    Flight::route('GET /users', function () {
      // 匹配 GET /api/v2/users
    });
  });
});
```

#### 使用对象上下文进行分组

您仍然可以使用 `Engine` 对象进行路由分组，如下所示：

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {
  $router->get('/users', function () {
    // 匹配 GET /api/v1/users
  });

  $router->post('/posts', function () {
    // 匹配 POST /api/v1/posts
  });
});
```

### 路由别名

您可以为路由分配别名，以便在代码中可以动态生成 URL（例如，一个模板）。

```php
Flight::route('/users/@id', function($id) { echo '用户:'.$id; }, false, 'user_view');

// 稍后在代码的某个地方
Flight::getUrl('user_view', [ 'id' => 5 ]); // 将返回 '/users/5'
```

这在您的 URL 发生变化时尤其有用。在上面的示例中，假设用户被移动到了 `/admin/users/@id`。
有了别名，您不必更改引用别名的任何地方，因为别名将现在返回 `/admin/users/5`，就像上面的示例一样。

路由别名仍然在分组中有效：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo '用户:'.$id; }, false, 'user_view');
});

// 稍后在代码的某个地方
Flight::getUrl('user_view', [ 'id' => 5 ]); // 将返回 '/users/5'
```

## <a name="extending"></a> 扩展

Flight 旨在成为一个可扩展的框架。框架附带了一组默认的方法和组件，但它允许您映射自己的方法、注册自己的类，甚至覆盖现有的类和方法。

### 映射方法

要映射您自己的自定义方法，您使用 `map` 函数：

``` php
// 映射您的方法
Flight::map('hello', function($name){
    echo "你好 $name!";
});

// 调用您的自定义方法
Flight::hello('Bob');
```

### 注册类

要注册您自己的类，您使用 `register` 函数：

``` php
// 注册您的类
Flight::register('user', 'User');

// 获取您的类的实例
$user = Flight::user();
```

注册方法还允许您将参数传递给类构造函数。因此，当您加载自定义类时，它将预先初始化。您可以通过传递额外的数组来定义构造函数参数。这是加载数据库连接的示例：

``` php
// 注册带有构造函数参数的类
Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=test','user','pass'));

// 获取您的类的实例
// 这将使用定义的参数创建一个对象
//
//     new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();
```

如果您传递额外的回调参数，它将在类构造后立即执行。这允许您为新对象执行任何设置程序。回调函数接受一个参数，即新对象的实例。

``` php
// 回调将传递构造的对象
Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=test','user','pass'),
  function($db){
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

默认情况下，每次加载类时，您将获得一个共享实例。要获得类的新实例，只需将 `false` 作为参数传入：

``` php
// 类的共享实例
$shared = Flight::db();

// 类的新实例
$new = Flight::db(false);
```

请记住，映射的方法优先于注册的类。如果您使用相同的名称同时声明两者，则仅调用映射的方法。

## <a name="overriding"></a> 重写

Flight 允许您重写其默认功能，以适应您的需求，而不必修改任何代码。

例如，当 Flight 无法将 URL 匹配到路由时，它调用 `notFound` 方法，并发送通用的 `HTTP 404` 响应。您可以通过使用 `map` 方法重写此行为：

``` php
Flight::map('notFound', function(){
    // 显示自定义 404 页面
    include 'errors/404.html';
});
```

Flight 还允许您替换框架的核心组件。
例如，您可以用自己的自定义类替换默认 Router 类：

``` php
// 注册您的自定义类
Flight::register('router', 'MyRouter');

// 当 Flight 加载 Router 实例时，它将加载您的类
$myrouter = Flight::router();
```

然而，像 `map` 和 `register` 这样的框架方法是无法被重写的。如果您尝试这样做，将会引发错误。

## <a name="filtering"></a> 过滤

Flight 允许您在调用方法之前和之后进行过滤。没有预定义的钩子需要记忆。您可以过滤任何默认框架方法以及您映射的任何自定义方法。

过滤函数如下所示：

``` php
function(&$params, &$output) {
    // 过滤代码
}
```

使用传入的变量，您可以操作输入参数和/或输出。

您可以在方法之前运行过滤：

``` php
Flight::before('start', function(&$params, &$output){
    // 做一些事情
});
```

您可以在方法之后运行过滤：

``` php
Flight::after('start', function(&$params, &$output){
    // 做一些事情
});
```

您可以向任何方法添加任意数量的过滤器。它们将按照声明的顺序被调用。

以下是过滤过程的示例：

``` php
// 映射一个自定义方法
Flight::map('hello', function($name){
    return "你好, $name!";
});

// 添加一个之前的过滤器
Flight::before('hello', function(&$params, &$output){
    // 操纵参数
    $params[0] = 'Fred';
});

// 添加一个之后的过滤器
Flight::after('hello', function(&$params, &$output){
    // 操纵输出
    $output .= " 祝您有个愉快的一天！";
});

// 调用自定义方法
echo Flight::hello('Bob');
```

这应该显示：

``` html
你好 Fred! 祝您有个愉快的一天！
```

如果您定义了多个过滤器，您可以通过在任何过滤函数中返回 `false` 来打破链：

``` php
Flight::before('start', function(&$params, &$output){
    echo '一';
});

Flight::before('start', function(&$params, &$output){
    echo '二';

    // 这将结束链
    return false;
});

// 这将不会被调用
Flight::before('start', function(&$params, &$output){
    echo '三';
});
```

请注意，核心方法如 `map` 和 `register` 不能被过滤，因为它们是直接调用的，而不是动态调用的。

## <a name="variables"></a> 变量

Flight 允许您保存变量，以便在应用程序的任何地方使用。

``` php
// 保存您的变量
Flight::set('id', 123);

// 在您应用程序的其他地方
$id = Flight::get('id');
```
要查看变量是否已设置，您可以执行：

``` php
if (Flight::has('id')) {
     // 做一些事情
}
```

您可以通过以下方式清除变量：

``` php
// 清除 id 变量
Flight::clear('id');

// 清除所有变量
Flight::clear();
```

Flight 还使用变量进行配置。

``` php
Flight::set('flight.log_errors', true);
```

## <a name="views"></a> 视图

Flight 默认提供一些基本的模板功能。要显示视图模板，请调用 `render` 方法，并传入模板文件的名称和可选的模板数据：

``` php
Flight::render('hello.php', array('name' => 'Bob'));
```

您传入的模板数据会自动注入到模板中，并可以像局部变量一样引用。模板文件仅是 PHP 文件。如果 `hello.php` 模板文件的内容为：

``` php
你好，'<?php echo $name; ?>'!
```

输出将是：

``` html
你好，Bob!
```

您还可以通过使用 set 方法手动设置视图变量：

``` php
Flight::view()->set('name', 'Bob');
```

变量 `name` 现在可以在您所有的视图中使用。因此，您可以简单地做：

``` php
Flight::render('hello');
```

请注意，当在渲染方法中指定模板的名称时，您可以省略 `.php` 扩展名。

默认情况下，Flight 将查找一个 `views` 目录来查找模板文件。您可以通过设置以下配置来指定模板的替代路径：

``` php
Flight::set('flight.views.path', '/path/to/views');
```

### 布局

网站通常有一个单一的布局模板文件，具有互换内容。要呈现内容以在布局中使用，您可以将可选参数传递给 `render` 方法。

``` php
Flight::render('header', array('heading' => '你好'), 'header_content');
Flight::render('body', array('body' => '世界'), 'body_content');
```

您的视图将保存名为 `header_content` 和 `body_content` 的变量。然后，您可以通过做以下操作来渲染您的布局：

``` php
Flight::render('layout', array('title' => '主页'));
```

如果模板文件看起来像这样：

`header.php`:

``` php
<h1><?php echo $heading; ?></h1>
```

`body.php`:

``` php
<div><?php echo $body; ?></div>
```

`layout.php`:

``` php
<html>
<head>
<title><?php echo $title; ?></title>
</head>
<body>
<?php echo $header_content; ?>
<?php echo $body_content; ?>
</body>
</html>
```

输出将是：

``` html
<html>
<head>
<title>主页</title>
</head>
<body>
<h1>你好</h1>
<div>世界</div>
</body>
</html>
```

### 自定义视图

Flight 允许您通过注册自己的视图类来替换默认视图引擎。以下是如何为您的视图使用 [Smarty](http://www.smarty.net/) 模板引擎：

``` php
// 加载 Smarty 库
require './Smarty/libs/Smarty.class.php';

// 将 Smarty 注册为视图类
// 还传入一个回调函数以在加载时配置 Smarty
Flight::register('view', 'Smarty', array(), function($smarty){
    $smarty->template_dir = './templates/';
    $smarty->compile_dir = './templates_c/';
    $smarty->config_dir = './config/';
    $smarty->cache_dir = './cache/';
});

// 分配模板数据
Flight::view()->assign('name', 'Bob');

// 显示模板
Flight::view()->display('hello.tpl');
```

为了完整性，您还应该重写 Flight 的默认 render 方法：

``` php
Flight::map('render', function($template, $data){
    Flight::view()->assign($data);
    Flight::view()->display($template);
});
```

## <a name="errorhandling"></a> 错误处理

### 错误和异常

所有错误和异常都由 Flight 捕获并传递给 `error` 方法。默认行为是发送通用的 `HTTP 500 内部服务器错误` 响应，并附带一些错误信息。

您可以重写此行为以满足您的需求：

``` php
Flight::map('error', function(Exception $ex){
    // 处理错误
    echo $ex->getTraceAsString();
});
```

默认情况下，错误不会记录到 Web 服务器。您可以通过更改配置来启用此操作：

``` php
Flight::set('flight.log_errors', true);
```

### 找不到

当找不到 URL 时，Flight 会调用 `notFound` 方法。默认行为是发送 `HTTP 404 找不到` 响应，并附带一条简单的消息。

您可以重写此行为以满足您的需求：

``` php
Flight::map('notFound', function(){
    // 处理未找到
});
```

## <a name="redirects"></a> 重定向

您可以使用 `redirect` 方法通过传入新 URL 来重定向当前请求：

``` php
Flight::redirect('/new/location');
```

默认情况下，Flight 发送 HTTP 303 状态代码。您可以选择设置自定义代码：

``` php
Flight::redirect('/new/location', 401);
```

## <a name="requests"></a> 请求

Flight 将 HTTP 请求封装为一个单一对象，可以通过以下方式访问：

``` php
$request = Flight::request();
```

请求对象提供以下属性：

``` html
url - 请求的 URL
base - URL 的父子目录
method - 请求方法 (GET, POST, PUT, DELETE)
referrer - 引用 URL
ip - 客户端的 IP 地址
ajax - 请求是否为 AJAX 请求
scheme - 服务器协议 (http, https)
user_agent - 浏览器信息
type - 内容类型
length - 内容长度
query - 查询字符串参数
data - POST 数据或 JSON 数据
cookies - Cookie 数据
files - 上传的文件
secure - 连接是否安全
accept - HTTP 接受参数
proxy_ip - 客户端的 Proxy IP 地址
```

您可以将 `query`、`data`、`cookies` 和 `files` 属性作为数组或对象访问。

因此，要获取查询字符串参数，您可以执行：

``` php
$id = Flight::request()->query['id'];
```

或者您可以执行：

``` php
$id = Flight::request()->query->id;
```

### 原始请求体

要获取原始 HTTP 请求体，例如处理 PUT 请求时，您可以执行：

``` php
$body = Flight::request()->getBody();
```

### JSON 输入

如果您发送一个类型为 `application/json` 和数据为 `{"id": 123}` 的请求，它将在 `data` 属性中可用：

``` php
$id = Flight::request()->data->id;
```

## <a name="stopping"></a> 停止

您可以通过调用 `halt` 方法在任何时刻停止框架：

``` php
Flight::halt();
```

您还可以指定可选的 `HTTP` 状态代码和消息：

``` php
Flight::halt(200, '稍后回来...');
```

调用 `halt` 将丢弃到那时为止的任何响应内容。如果您想停止框架并输出当前响应，请使用 `stop` 方法：

``` php
Flight::stop();
```

## <a name="httpcaching"></a> HTTP 缓存

Flight 提供对 HTTP 级缓存的内置支持。如果满足缓存条件，Flight 将返回 HTTP `304 未修改` 响应。下次客户端请求同一资源时，他们将被提示使用其本地缓存版本。

### 最后修改

您可以使用 `lastModified` 方法并传入 UNIX 时间戳来设置页面上次修改的日期和时间。客户端将继续使用其缓存，直到最后一次修改值更改。

``` php
Flight::route('/news', function(){
    Flight::lastModified(1234567890);
    echo '此内容将被缓存。';
});
```

### ETag

`ETag` 缓存类似于 `Last-Modified`，但您可以为资源指定任何您想要的 ID：

``` php
Flight::route('/news', function(){
    Flight::etag('my-unique-id');
    echo '此内容将被缓存。';
});
```

请记住，调用 `lastModified` 或 `etag` 将设置并检查缓存值。如果请求之间的缓存值相同，Flight 将立即发送 `HTTP 304` 响应并停止处理。

## <a name="json"></a> JSON

Flight 提供对发送 JSON 和 JSONP 响应的支持。要发送 JSON 响应，您只需传递一些数据以进行 JSON 编码：

``` php
Flight::json(array('id' => 123));
```

对于 JSONP 请求，您可以选择传入用于定义回调函数的查询参数名称：

``` php
Flight::jsonp(array('id' => 123), 'q');
```

因此，当使用 `?q=my_func` 发起 GET 请求时，您应该接收到以下输出：

``` json
my_func({"id":123});
```

如果您没有传入查询参数名称，默认为 `jsonp`。

## <a name="configuration"></a> 配置

您可以通过设置配置值来自定义 Flight 的某些行为，方法是通过 `set` 方法。

``` php
Flight::set('flight.log_errors', true);
```

以下是所有可用配置设置的列表：

``` html 
flight.base_url - 覆盖请求的基本 URL。（默认为：null）
flight.case_sensitive - 对 URL 进行大小写敏感匹配。（默认为：false）
flight.handle_errors - 允许 Flight 内部处理所有错误。（默认为：true）
flight.log_errors - 将错误记录到 Web 服务器的错误日志文件。（默认为：false）
flight.views.path - 包含视图模板文件的目录。（默认为：./views）
flight.views.extension - 视图模板文件扩展名。（默认为：.php）
```

## <a name="frameworkmethods"></a> 框架方法

Flight 旨在易于使用和理解。以下是框架的完整方法集。它由核心方法（常规静态方法）和可扩展方法（可过滤或重写的映射方法）组成。

### 核心方法

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // 创建自定义框架方法。
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // 将类注册到框架方法。
Flight::before(string $name, callable $callback) // 在框架方法之前添加过滤器。
Flight::after(string $name, callable $callback) // 在框架方法之后添加过滤器。
Flight::path(string $path) // 添加用于自动加载类的路径。
Flight::get(string $key) // 获取变量。
Flight::set(string $key, mixed $value) // 设置变量。
Flight::has(string $key) // 检查变量是否已设置。
Flight::clear(array|string $key = []) // 清除变量。
Flight::init() // 将框架初始化为默认设置。
Flight::app() // 获取应用程序对象实例
```

### 可扩展方法

```php
Flight::start() // 启动框架。
Flight::stop() // 停止框架并发送响应。
Flight::halt(int $code = 200, string $message = '') // 以可选状态码和消息停止框架。
Flight::route(string $pattern, callable $callback, bool $pass_route = false) // 将 URL 模式映射到回调。
Flight::group(string $pattern, callable $callback) // 创建 URL 分组，模式必须是字符串。
Flight::redirect(string $url, int $code) // 重定向到另一个 URL。
Flight::render(string $file, array $data, ?string $key = null) // 渲染模板文件。
Flight::error(Throwable $error) // 发送 HTTP 500 响应。
Flight::notFound() // 发送 HTTP 404 响应。
Flight::etag(string $id, string $type = 'string') // 执行 ETag HTTP 缓存。
Flight::lastModified(int $time) // 执行最后修改的 HTTP 缓存。
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // 发送 JSON 响应。
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // 发送 JSONP 响应。
```

任何使用 `map` 和 `register` 添加的自定义方法也可以被过滤。

## <a name="frameworkinstance"></a> 框架实例

您可以选择以对象实例的方式运行 Flight，而不是以全局静态类的方式运行。

``` php
require 'flight/autoload.php';

use flight\Engine;

$app = new Engine();

$app->route('/', function(){
    echo '你好，世界！';
});

$app->start();
```

因此，您将以名为 Engine 的对象调用相同名称的实例方法，而不是调用静态方法。