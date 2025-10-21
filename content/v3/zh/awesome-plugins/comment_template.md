# CommentTemplate

[CommentTemplate](https://github.com/KnifeLemon/CommentTemplate) 是一个强大的 PHP 模板引擎，具有资产生成、模板继承和变量处理功能。它提供了一种简单而灵活的管理模板的方式，内置 CSS/JS 压缩和缓存。

## 功能

- **模板继承**：使用布局并包含其他模板
- **资产生成**：自动 CSS/JS 压缩和缓存
- **变量处理**：带有过滤器和命令的模板变量
- **Base64 编码**：将内联资产生成为数据 URI
- **Flight 框架集成**：与 Flight PHP 框架的可选集成

## 安装

使用 Composer 安装。

```bash
composer require knifelemon/comment-template
```

## 基本配置

有一些基本的配置选项来开始使用。您可以在 [CommentTemplate Repo](https://github.com/KnifeLemon/CommentTemplate) 中阅读更多关于它们的信息。

### 方法 1：使用回调函数

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

$app->register('view', Engine::class, [], function (Engine $engine) use ($app) {
    // 模板文件存储的位置
    $engine->setTemplatesPath(__DIR__ . '/views');
    
    // 公共资产服务的路径
    $engine->setPublicPath(__DIR__ . '/public');
    
    // 编译资产的存储位置
    $engine->setAssetPath('assets');
    
    // 模板文件扩展名
    $engine->setFileExtension('.php');
});

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

### 方法 2：使用构造函数参数

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

// __construct(string $publicPath = "", string $skinPath = "", string $assetPath = "", string $fileExtension = "")
$app->register('view', Engine::class, [
    __DIR__ . '/public',    // publicPath - 资产服务的路径
    __DIR__ . '/views',     // skinPath - 模板文件存储的位置  
    'assets',               // assetPath - 编译资产的存储位置
    '.php'                  // fileExtension - 模板文件扩展名
]);

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

## 模板指令

### 布局继承

使用布局来创建共同结构：

**layout/global_layout.php**：
```html
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
</head>
<body>
    <!--@contents-->
</body>
</html>
```

**view/page.php**：
```html
<!--@layout(layout/global_layout)-->
<h1>{$title}</h1>
<p>{$content}</p>
```

### 资产管理

#### CSS 文件
```html
<!--@css(/css/styles.css)-->          <!-- 压缩并缓存 -->
<!--@cssSingle(/css/critical.css)-->  <!-- 单个文件，不压缩 -->
```

#### JavaScript 文件
CommentTemplate 支持不同的 JavaScript 加载策略：

```html
<!--@js(/js/script.js)-->             <!-- 压缩，在底部加载 -->
<!--@jsAsync(/js/analytics.js)-->     <!-- 压缩，在底部异步加载 -->
<!--@jsDefer(/js/utils.js)-->         <!-- 压缩，在底部延迟加载 -->
<!--@jsTop(/js/critical.js)-->        <!-- 压缩，在头部加载 -->
<!--@jsTopAsync(/js/tracking.js)-->   <!-- 压缩，在头部异步加载 -->
<!--@jsTopDefer(/js/polyfill.js)-->   <!-- 压缩，在头部延迟加载 -->
<!--@jsSingle(/js/widget.js)-->       <!-- 单个文件，不压缩 -->
<!--@jsSingleAsync(/js/ads.js)-->     <!-- 单个文件，不压缩，异步 -->
<!--@jsSingleDefer(/js/social.js)-->  <!-- 单个文件，不压缩，延迟 -->
```

#### CSS/JS 文件中的资产指令

CommentTemplate 还在编译期间处理 CSS 和 JavaScript 文件中的资产指令：

**CSS 示例：**
```css
/* 在您的 CSS 文件中 */
@font-face {
    font-family: 'CustomFont';
    src: url('<!--@asset(fonts/custom.woff2)-->') format('woff2');
}

.background-image {
    background: url('<!--@asset(images/bg.jpg)-->');
}

.inline-icon {
    background: url('<!--@base64(icons/star.svg)-->');
}
```

**JavaScript 示例：**
```javascript
/* 在您的 JS 文件中 */
const fontUrl = '<!--@asset(fonts/custom.woff2)-->';
const imageData = '<!--@base64(images/icon.png)-->';
```

#### Base64 编码
```html
<!--@base64(images/logo.png)-->       <!-- 内联作为数据 URI -->
```
** 示例： **
```html
<!-- 将小图像内联作为数据 URI 以加快加载 -->
<img src="<!--@base64(images/logo.png)-->" alt="Logo">
<div style="background-image: url('<!--@base64(icons/star.svg)-->');">
    小图标作为背景
</div>
```

#### 资产复制
```html
<!--@asset(images/photo.jpg)-->       <!-- 将单个资产生复制到公共目录 -->
<!--@assetDir(assets)-->              <!-- 将整个目录复制到公共目录 -->
```
** 示例： **
```html
<!-- 复制并引用静态资产 -->
<img src="<!--@asset(images/hero-banner.jpg)-->" alt="Hero Banner">
<a href="<!--@asset(documents/brochure.pdf)-->" download>下载宣传册</a>

<!-- 复制整个目录（字体、图标等） -->
<!--@assetDir(assets/fonts)-->
<!--@assetDir(assets/icons)-->
```

### 模板包含
```html
<!--@import(components/header)-->     <!-- 包含其他模板 -->
```
** 示例： **
```html
<!-- 包含可重用组件 -->
<!--@import(components/header)-->

<main>
    <h1>欢迎访问我们的网站</h1>
    <!--@import(components/sidebar)-->
    
    <div class="content">
        <p>主要内容在这里...</p>
    </div>
</main>

<!--@import(components/footer)-->
```

### 变量处理

#### 基本变量
```html
<h1>{$title}</h1>
<p>{$description}</p>
```

#### 变量过滤器
```html
{$title|upper}                       <!-- 转换为大写 -->
{$content|lower}                     <!-- 转换为小写 -->
{$html|striptag}                     <!-- 去除 HTML 标签 -->
{$text|escape}                       <!-- 转义 HTML -->
{$multiline|nl2br}                   <!-- 将换行转换为 <br> -->
{$html|br2nl}                        <!-- 将 <br> 标签转换为换行 -->
{$description|trim}                  <!-- 去除空白 -->
{$subject|title}                     <!-- 转换为标题大小写 -->
```

#### 变量命令
```html
{$title|default=Default Title}       <!-- 设置默认值 -->
{$name|concat= (Admin)}              <!-- 连接文本 -->
```

#### 变量命令
```html
{$content|striptag|trim|escape}      <!-- 链式多个过滤器 -->
```

## 示例项目结构

```
project/
├── source/
│   ├── layouts/
│   │   └── default.php
│   ├── components/
│   │   ├── header.php
│   │   └── footer.php
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   └── custom.css
│   ├── js/
│   │   ├── app.js
│   │   └── bootstrap.min.js
│   └── homepage.php
├── public/
│   └── assets/           # 生成的资产
│       ├── css/
│       └── js/
└── vendor/
```