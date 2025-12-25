# CommentTemplate

[CommentTemplate](https://github.com/KnifeLemon/CommentTemplate) 是一个强大的 PHP 模板引擎，支持资产编译、模板继承和变量处理。它提供了一种简单而灵活的管理模板的方式，内置 CSS/JS 压缩和缓存功能。

## 功能特性

- **模板继承**：使用布局并包含其他模板
- **资产编译**：自动 CSS/JS 压缩和缓存
- **变量处理**：带有过滤器和命令的模板变量
- **Base64 编码**：将内联资产作为数据 URI
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
    // 根目录（index.php 所在位置） - Web 应用程序的文档根目录
    $engine->setPublicPath(__DIR__);
    
    // 模板文件目录 - 支持相对和绝对路径
    $engine->setSkinPath('views');             // 相对于公共路径
    
    // 编译资产存储位置 - 支持相对和绝对路径
    $engine->setAssetPath('assets');           // 相对于公共路径
    
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
    __DIR__,                // publicPath - 根目录（index.php 所在位置）
    'views',                // skinPath - 模板路径（支持相对/绝对）
    'assets',               // assetPath - 编译资产路径（支持相对/绝对）
    '.php'                  // fileExtension - 模板文件扩展名
]);

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

## 路径配置

CommentTemplate 为相对和绝对路径提供智能路径处理：

### 公共路径

**公共路径** 是 Web 应用程序的根目录，通常是 `index.php` 所在的位置。这是 Web 服务器提供文件的文档根目录。

```php
// 示例：如果您的 index.php 位于 /var/www/html/myapp/index.php
$template->setPublicPath('/var/www/html/myapp');  // 根目录

// Windows 示例：如果您的 index.php 位于 C:\xampp\htdocs\myapp\index.php
$template->setPublicPath('C:\\xampp\\htdocs\\myapp');
```

### 模板路径配置

模板路径支持相对和绝对路径：

```php
$template = new Engine();
$template->setPublicPath('/var/www/html/myapp');  // 根目录（index.php 所在位置）

// 相对路径 - 自动与公共路径组合
$template->setSkinPath('views');           // → /var/www/html/myapp/views/
$template->setSkinPath('templates/pages'); // → /var/www/html/myapp/templates/pages/

// 绝对路径 - 原样使用（Unix/Linux）
$template->setSkinPath('/var/www/templates');      // → /var/www/templates/
$template->setSkinPath('/full/path/to/templates'); // → /full/path/to/templates/

// Windows 绝对路径
$template->setSkinPath('C:\\www\\templates');     // → C:\www\templates\
$template->setSkinPath('D:/projects/templates');  // → D:/projects/templates/

// UNC 路径（Windows 网络共享）
$template->setSkinPath('\\\\server\\share\\templates'); // → \\server\share\templates\
```

### 资产路径配置

资产路径也支持相对和绝对路径：

```php
// 相对路径 - 自动与公共路径组合
$template->setAssetPath('assets');        // → /var/www/html/myapp/assets/
$template->setAssetPath('static/files');  // → /var/www/html/myapp/static/files/

// 绝对路径 - 原样使用（Unix/Linux）
$template->setAssetPath('/var/www/cdn');           // → /var/www/cdn/
$template->setAssetPath('/full/path/to/assets');   // → /full/path/to/assets/

// Windows 绝对路径
$template->setAssetPath('C:\\www\\static');       // → C:\www\static\
$template->setAssetPath('D:/projects/assets');    // → D:/projects/assets/

// UNC 路径（Windows 网络共享）
$template->setAssetPath('\\\\server\\share\\assets'); // → \\server\share\assets\
```

**智能路径检测：**

- **相对路径**：无前导分隔符（`/`、`\'）或驱动器字母
- **Unix 绝对**：以 `/` 开头（例如，`/var/www/assets`）
- **Windows 绝对**：以驱动器字母开头（例如，`C:\www`、`D:/assets`）
- **UNC 路径**：以 `\\` 开头（例如，`\\server\share`）

**工作原理：**

- 所有路径根据类型（相对 vs 绝对）自动解析
- 相对路径与公共路径组合
- `@css` 和 `@js` 在以下位置创建压缩文件：`{resolvedAssetPath}/css/` 或 `{resolvedAssetPath}/js/`
- `@asset` 将单个文件复制到：`{resolvedAssetPath}/{relativePath}`
- `@assetDir` 将目录复制到：`{resolvedAssetPath}/{relativePath}`
- 智能缓存：仅当源文件比目标文件新时才复制文件

## 模板指令

### 布局继承

使用布局创建通用结构：

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
<!--@jsAsync(/js/analytics.js)-->     <!-- 压缩，在底部加载并使用 async -->
<!--@jsDefer(/js/utils.js)-->         <!-- 压缩，在底部加载并使用 defer -->
<!--@jsTop(/js/critical.js)-->        <!-- 压缩，在 head 中加载 -->
<!--@jsTopAsync(/js/tracking.js)-->   <!-- 压缩，在 head 中加载并使用 async -->
<!--@jsTopDefer(/js/polyfill.js)-->   <!-- 压缩，在 head 中加载并使用 defer -->
<!--@jsSingle(/js/widget.js)-->       <!-- 单个文件，不压缩 -->
<!--@jsSingleAsync(/js/ads.js)-->     <!-- 单个文件，不压缩，使用 async -->
<!--@jsSingleDefer(/js/social.js)-->  <!-- 单个文件，不压缩，使用 defer -->
```

#### CSS/JS 文件中的资产指令

CommentTemplate 在编译期间还会处理 CSS 和 JavaScript 文件中的资产指令：

**CSS 示例：**
```css
/* 在您的 CSS 文件中 */
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
<!--@base64(images/logo.png)-->       <!-- 作为数据 URI 内联 -->
```
** 示例： **
```html
<!-- 将小图像作为数据 URI 内联以加快加载 -->
<img src="<!--@base64(images/logo.png)-->" alt="Logo">
<div style="background-image: url('<!--@base64(icons/star.svg)-->');">
    小图标作为背景
</div>
```

#### 资产复制
```html
<!--@asset(images/photo.jpg)-->       <!-- 将单个资产复制到公共目录 -->
<!--@assetDir(assets)-->              <!-- 将整个目录复制到公共目录 -->
```
** 示例： **
```html
<!-- 复制并引用静态资产 -->
<img src="<!--@asset(images/hero-banner.jpg)-->" alt="Hero Banner">
<a href="<!--@asset(documents/brochure.pdf)-->" download>Download Brochure</a>

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
    <h1>Welcome to our website</h1>
    <!--@import(components/sidebar)-->
    
    <div class="content">
        <p>Main content here...</p>
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

### 注释

模板注释会完全从输出中移除，不会出现在最终的 HTML 中：

```html
{* 这是一个单行模板注释 *}

{* 
   这是一个多行 
   模板注释 
   跨越多行
*}

<h1>{$title}</h1>
{* 调试注释：检查标题变量是否工作 *}
<p>{$content}</p>
```

**注意**：模板注释 `{* ... *}` 与 HTML 注释 `<!-- ... -->` 不同。模板注释在处理期间被移除，从未到达浏览器。

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