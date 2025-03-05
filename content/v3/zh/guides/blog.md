# 使用 Flight PHP 构建简单博客

本指南带您通过使用 Flight PHP 框架创建基本博客的过程。您将设置项目，定义路由，使用 JSON 管理帖子，并使用 Latte 模板引擎进行呈现——所有这些都展示了 Flight 的简单性和灵活性。到最后，您将拥有一个功能性博客，包含主页、单独的帖子页面和创建表单。

## 先决条件
- **PHP 7.4+**：已安装在您的系统中。
- **Composer**：用于依赖管理。
- **文本编辑器**：任何编辑器，如 VS Code 或 PHPStorm。
- PHP 和 Web 开发的基本知识。

## 第一步：设置您的项目

首先创建一个新的项目目录并通过 Composer 安装 Flight。

1. **创建目录**：
   ```bash
   mkdir flight-blog
   cd flight-blog
   ```

2. **安装 Flight**：
   ```bash
   composer require flightphp/core
   ```

3. **创建公共目录**：
   Flight 使用单个入口点 (`index.php`)。为其创建 `public/` 文件夹：
   ```bash
   mkdir public
   ```

4. **基本的 `index.php`**：
   创建 `public/index.php`，添加简单的“你好，世界”路由：
   ```php
   <?php
   require '../vendor/autoload.php';

   Flight::route('/', function () {
       echo '你好，Flight！';
   });

   Flight::start();
   ```

5. **运行内置服务器**：
   使用 PHP 的开发服务器测试您的设置：
   ```bash
   php -S localhost:8000 -t public/
   ```
   访问 `http://localhost:8000` 查看“你好，Flight！”。

## 第二步：组织您的项目结构

为了保持设置整洁，请将项目构建为如下结构：

```text
flight-blog/
├── app/
│   ├── config/
│   └── views/
├── data/
├── public/
│   └── index.php
├── vendor/
└── composer.json
```

- `app/config/`：配置文件（例如，事件，路由）。
- `app/views/`：用于呈现页面的模板。
- `data/`：用于存储博客帖子的 JSON 文件。
- `public/`：包含 `index.php` 的 Web 根目录。

## 第三步：安装和配置 Latte

Latte 是一个轻量级的模板引擎，与 Flight 很好地集成。

1. **安装 Latte**：
   ```bash
   composer require latte/latte
   ```

2. **在 Flight 中配置 Latte**：
   更新 `public/index.php` 以将 Latte 注册为视图引擎：
   ```php
   <?php
   require '../vendor/autoload.php';

   use Latte\Engine;

   Flight::register('view', Engine::class, [], function ($latte) {
       $latte->setTempDirectory(__DIR__ . '/../cache/');
       $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../app/views/'));
   });

   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => '我的博客']);
   });

   Flight::start();
   ```

3. **创建布局模板：在 `app/views/layout.latte`**：
```html
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
</head>
<body>
    <header>
        <h1>我的博客</h1>
        <nav>
            <a href="/">首页</a> | 
            <a href="/create">创建帖子</a>
        </nav>
    </header>
    <main>
        {block content}{/block}
    </main>
    <footer>
        <p>&copy; {date('Y')} Flight 博客</p>
    </footer>
</body>
</html>
```

4. **创建首页模板**：
   在 `app/views/home.latte`：
   ```html
  {extends 'layout.latte'}

	{block content}
		<h2>{$title}</h2>
		<ul>
		{foreach $posts as $post}
			<li><a href="/post/{$post['slug']}">{$post['title']}</a></li>
		{/foreach}
		</ul>
	{/block}
   ```
   如果退出服务器，请重新启动，并访问 `http://localhost:8000` 查看渲染页面。

5. **创建数据文件**：

   使用 JSON 文件模拟数据库以简化操作。

   在 `data/posts.json`：
   ```json
   [
       {
           "slug": "first-post",
           "title": "我的第一篇帖子",
           "content": "这是我用 Flight PHP 撰写的第一篇博客帖子！"
       }
   ]
   ```

## 第四步：定义路由

将路由分开到配置文件中，以便更好地组织。

1. **创建 `routes.php`**：
   在 `app/config/routes.php`：
   ```php
   <?php
   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => '我的博客']);
   });

   Flight::route('/post/@slug', function ($slug) {
       Flight::view()->render('post.latte', ['title' => '帖子：' . $slug, 'slug' => $slug]);
   });

   Flight::route('GET /create', function () {
       Flight::view()->render('create.latte', ['title' => '创建帖子']);
   });
   ```

2. **更新 `index.php`**：
   包含路由文件：
   ```php
   <?php
   require '../vendor/autoload.php';

   use Latte\Engine;

   Flight::register('view', Engine::class, [], function ($latte) {
       $latte->setTempDirectory(__DIR__ . '/../cache/');
       $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../app/views/'));
   });

   require '../app/config/routes.php';

   Flight::start();
   ```

## 第五步：存储和检索博客帖子

添加加载和保存帖子的功能。

1. **添加帖子方法**：
   在 `index.php` 中，添加一个加载帖子的的方法：
   ```php
   Flight::map('posts', function () {
       $file = __DIR__ . '/../data/posts.json';
       return json_decode(file_get_contents($file), true);
   });
   ```

2. **更新路由**：
   修改 `app/config/routes.php` 以使用帖子：
   ```php
   <?php
   Flight::route('/', function () {
       $posts = Flight::posts();
       Flight::view()->render('home.latte', [
           'title' => '我的博客',
           'posts' => $posts
       ]);
   });

   Flight::route('/post/@slug', function ($slug) {
       $posts = Flight::posts();
       $post = array_filter($posts, fn($p) => $p['slug'] === $slug);
       $post = reset($post) ?: null;
       if (!$post) {
           Flight::notFound();
           return;
       }
       Flight::view()->render('post.latte', [
           'title' => $post['title'],
           'post' => $post
       ]);
   });

   Flight::route('GET /create', function () {
       Flight::view()->render('create.latte', ['title' => '创建帖子']);
   });
   ```

## 第六步：创建模板

更新您的模板以显示帖子。

1. **帖子页面 (`app/views/post.latte`)**：
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$post['title']}</h2>
		<div class="post-content">
			<p>{$post['content']}</p>
		</div>
	{/block}
   ```

## 第七步：添加帖子创建功能

处理表单提交以添加新帖子。

1. **创建表单 (`app/views/create.latte`)**：
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$title}</h2>
		<form method="POST" action="/create">
			<div class="form-group">
				<label for="title">标题：</label>
				<input type="text" name="title" id="title" required>
			</div>
			<div class="form-group">
				<label for="content">内容：</label>
				<textarea name="content" id="content" required></textarea>
			</div>
			<button type="submit">保存帖子</button>
		</form>
	{/block}
   ```

2. **添加 POST 路由**：
   在 `app/config/routes.php`：
   ```php
   Flight::route('POST /create', function () {
       $request = Flight::request();
       $title = $request->data['title'];
       $content = $request->data['content'];
       $slug = strtolower(str_replace(' ', '-', $title));

       $posts = Flight::posts();
       $posts[] = ['slug' => $slug, 'title' => $title, 'content' => $content];
       file_put_contents(__DIR__ . '/../../data/posts.json', json_encode($posts, JSON_PRETTY_PRINT));

       Flight::redirect('/');
   });
   ```

3. **测试它**：
   - 访问 `http://localhost:8000/create`。
   - 提交新的帖子（例如，“第二篇帖子”，以及一些内容）。
   - 检查主页以查看其是否已列出。

## 第八步：增强错误处理

覆盖 `notFound` 方法以提供更好的 404 体验。

在 `index.php`：
```php
Flight::map('notFound', function () {
    Flight::view()->render('404.latte', ['title' => '未找到页面']);
});
```

创建 `app/views/404.latte`：
```html
{extends 'layout.latte'}

{block content}
    <h2>404 - {$title}</h2>
    <p>抱歉，该页面不存在！</p>
{/block}
```

## 下一步
- **添加样式**：在您的模板中使用 CSS 以获得更好的外观。
- **数据库**：使用 `PdoWrapper` 替换 `posts.json` 为数据库，例如 SQLite。
- **验证**：添加对重复 slug 或空输入的检查。
- **中间件**：实施身份验证以进行帖子创建。

## 结论

您已使用 Flight PHP 构建了一个简单的博客！本指南展示了核心功能，如路由、使用 Latte 进行模板处理和处理表单提交——同时保持轻量化。探索 Flight 的文档以获取更多高级功能以进一步提升您的博客！