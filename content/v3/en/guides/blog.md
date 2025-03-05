# Building a Simple Blog with Flight PHP

This guide walks you through creating a basic blog using the Flight PHP framework. You'll set up a project, define routes, manage posts with JSON, and render them with the Latte templating engine—all showcasing Flight’s simplicity and flexibility. By the end, you’ll have a functional blog with a homepage, individual post pages, and a creation form.

## Prerequisites
- **PHP 7.4+**: Installed on your system.
- **Composer**: For dependency management.
- **Text Editor**: Any editor like VS Code or PHPStorm.
- Basic knowledge of PHP and web development.

## Step 1: Set Up Your Project

Start by creating a new project directory and installing Flight via Composer.

1. **Create a Directory**:
   ```bash
   mkdir flight-blog
   cd flight-blog
   ```

2. **Install Flight**:
   ```bash
   composer require flightphp/core
   ```

3. **Create a Public Directory**:
   Flight uses a single entry point (`index.php`). Create a `public/` folder for it:
   ```bash
   mkdir public
   ```

4. **Basic `index.php`**:
   Create `public/index.php` with a simple “hello world” route:
   ```php
   <?php
   require '../vendor/autoload.php';

   Flight::route('/', function () {
       echo 'Hello, Flight!';
   });

   Flight::start();
   ```

5. **Run the Built-in Server**:
   Test your setup with PHP’s development server:
   ```bash
   php -S localhost:8000 -t public/
   ```
   Visit `http://localhost:8000` to see “Hello, Flight!”.

## Step 2: Organize Your Project Structure

For a clean setup, structure your project like this:

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

- `app/config/`: Configuration files (e.g., events, routes).
- `app/views/`: Templates for rendering pages.
- `data/`: JSON file for storing blog posts.
- `public/`: Web root with `index.php`.

## Step 3: Install and Configure Latte

Latte is a lightweight templating engine that integrates well with Flight.

1. **Install Latte**:
   ```bash
   composer require latte/latte
   ```

2. **Configure Latte in Flight**:
   Update `public/index.php` to register Latte as the view engine:
   ```php
   <?php
   require '../vendor/autoload.php';

   use Latte\Engine;

   Flight::register('view', Engine::class, [], function ($latte) {
       $latte->setTempDirectory(__DIR__ . '/../cache/');
       $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../app/views/'));
   });

   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => 'My Blog']);
   });

   Flight::start();
   ```

3. **Create a Layout Template: 
In `app/views/layout.latte`**:
```html
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
</head>
<body>
    <header>
        <h1>My Blog</h1>
        <nav>
            <a href="/">Home</a> | 
            <a href="/create">Create a Post</a>
        </nav>
    </header>
    <main>
        {block content}{/block}
    </main>
    <footer>
        <p>&copy; {date('Y')} Flight Blog</p>
    </footer>
</body>
</html>
```

4. **Create a Home Template**:
   In `app/views/home.latte`:
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
   Restart the server if you got out of it and visit `http://localhost:8000` to see the rendered page.

5. **Create a Data File**:

   Use a JSON file to simulate a database for simplicity.

   In `data/posts.json`:
   ```json
   [
       {
           "slug": "first-post",
           "title": "My First Post",
           "content": "This is my very first blog post with Flight PHP!"
       }
   ]
   ```

## Step 4: Define Routes

Separate your routes into a config file for better organization.

1. **Create `routes.php`**:
   In `app/config/routes.php`:
   ```php
   <?php
   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => 'My Blog']);
   });

   Flight::route('/post/@slug', function ($slug) {
       Flight::view()->render('post.latte', ['title' => 'Post: ' . $slug, 'slug' => $slug]);
   });

   Flight::route('GET /create', function () {
       Flight::view()->render('create.latte', ['title' => 'Create a Post']);
   });
   ```

2. **Update `index.php`**:
   Include the routes file:
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

## Step 5: Store and Retrieve Blog Posts

Add the methods to load and save posts.

1. **Add a Posts Method**:
   In `index.php`, add a method to load posts:
   ```php
   Flight::map('posts', function () {
       $file = __DIR__ . '/../data/posts.json';
       return json_decode(file_get_contents($file), true);
   });
   ```

2. **Update Routes**:
   Modify `app/config/routes.php` to use posts:
   ```php
   <?php
   Flight::route('/', function () {
       $posts = Flight::posts();
       Flight::view()->render('home.latte', [
           'title' => 'My Blog',
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
       Flight::view()->render('create.latte', ['title' => 'Create a Post']);
   });
   ```

## Step 6: Create Templates

Update your templates to display posts.

1. **Post Page (`app/views/post.latte`)**:
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$post['title']}</h2>
		<div class="post-content">
			<p>{$post['content']}</p>
		</div>
	{/block}
   ```

## Step 7: Add Post Creation

Handle form submission to add new posts.

1. **Create Form (`app/views/create.latte`)**:
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$title}</h2>
		<form method="POST" action="/create">
			<div class="form-group">
				<label for="title">Title:</label>
				<input type="text" name="title" id="title" required>
			</div>
			<div class="form-group">
				<label for="content">Content:</label>
				<textarea name="content" id="content" required></textarea>
			</div>
			<button type="submit">Save Post</button>
		</form>
	{/block}
   ```

2. **Add POST Route**:
   In `app/config/routes.php`:
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

3. **Test It**:
   - Visit `http://localhost:8000/create`.
   - Submit a new post (e.g., “Second Post” with some content).
   - Check the homepage to see it listed.

## Step 8: Enhance with Error Handling

Override the `notFound` method for a better 404 experience.

In `index.php`:
```php
Flight::map('notFound', function () {
    Flight::view()->render('404.latte', ['title' => 'Page Not Found']);
});
```

Create `app/views/404.latte`:
```html
{extends 'layout.latte'}

{block content}
    <h2>404 - {$title}</h2>
    <p>Sorry, that page doesn't exist!</p>
{/block}
```

## Next Steps
- **Add Styling**: Use CSS in your templates for a better look.
- **Database**: Replace `posts.json` with a database like SQLite using `PdoWrapper`.
- **Validation**: Add checks for duplicate slugs or empty inputs.
- **Middleware**: Implement authentication for post creation.

## Conclusion

You’ve built a simple blog with Flight PHP! This guide demonstrates core features like routing, templating with Latte, and handling form submissions—all while keeping things lightweight. Explore Flight’s documentation for more advanced features to take your blog further!