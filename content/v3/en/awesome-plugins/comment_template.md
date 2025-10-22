# CommentTemplate

[CommentTemplate](https://github.com/KnifeLemon/CommentTemplate) is a powerful PHP template engine with asset compilation, template inheritance, and variable processing. It provides a simple yet flexible way to manage templates with built-in CSS/JS minification and caching.

## Features

- **Template Inheritance**: Use layouts and include other templates
- **Asset Compilation**: Automatic CSS/JS minification and caching
- **Variable Processing**: Template variables with filters and commands
- **Base64 Encoding**: Inline assets as data URIs
- **Flight Framework Integration**: Optional integration with Flight PHP framework

## Installation

Install with composer.

```bash
composer require knifelemon/comment-template
```

## Basic Configuration

There are some basic configuration options to get started. You can read more about them in the [CommentTemplate Repo](https://github.com/KnifeLemon/CommentTemplate).

### Method 1: Using Callback Function

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

$app->register('view', Engine::class, [], function (Engine $engine) use ($app) {
    // Root directory (where index.php is) - the document root of your web application
    $engine->setPublicPath(__DIR__);
    
    // Template files directory - supports both relative and absolute paths
    $engine->setSkinPath('views');             // Relative to public path
    
    // Where compiled assets will be stored - supports both relative and absolute paths
    $engine->setAssetPath('assets');           // Relative to public path
    
    // Template file extension
    $engine->setFileExtension('.php');
});

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

### Method 2: Using Constructor Parameters

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

// __construct(string $publicPath = "", string $skinPath = "", string $assetPath = "", string $fileExtension = "")
$app->register('view', Engine::class, [
    __DIR__,                // publicPath - root directory (where index.php is)
    'views',                // skinPath - templates path (supports relative/absolute)
    'assets',               // assetPath - compiled assets path (supports relative/absolute)
    '.php'                  // fileExtension - template file extension
]);

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

## Path Configuration

CommentTemplate provides intelligent path handling for both relative and absolute paths:

### Public Path

The **Public Path** is the root directory of your web application, typically where `index.php` resides. This is the document root that web servers serve files from.

```php
// Example: if your index.php is at /var/www/html/myapp/index.php
$template->setPublicPath('/var/www/html/myapp');  // Root directory

// Windows example: if your index.php is at C:\xampp\htdocs\myapp\index.php
$template->setPublicPath('C:\\xampp\\htdocs\\myapp');
```

### Templates Path Configuration

Templates path supports both relative and absolute paths:

```php
$template = new Engine();
$template->setPublicPath('/var/www/html/myapp');  // Root directory (where index.php is)

// Relative paths - automatically combined with public path
$template->setSkinPath('views');           // → /var/www/html/myapp/views/
$template->setSkinPath('templates/pages'); // → /var/www/html/myapp/templates/pages/

// Absolute paths - used as-is (Unix/Linux)
$template->setSkinPath('/var/www/templates');      // → /var/www/templates/
$template->setSkinPath('/full/path/to/templates'); // → /full/path/to/templates/

// Windows absolute paths
$template->setSkinPath('C:\\www\\templates');     // → C:\www\templates\
$template->setSkinPath('D:/projects/templates');  // → D:/projects/templates/

// UNC paths (Windows network shares)
$template->setSkinPath('\\\\server\\share\\templates'); // → \\server\share\templates\
```

### Asset Path Configuration

Asset path also supports both relative and absolute paths:

```php
// Relative paths - automatically combined with public path
$template->setAssetPath('assets');        // → /var/www/html/myapp/assets/
$template->setAssetPath('static/files');  // → /var/www/html/myapp/static/files/

// Absolute paths - used as-is (Unix/Linux)
$template->setAssetPath('/var/www/cdn');           // → /var/www/cdn/
$template->setAssetPath('/full/path/to/assets');   // → /full/path/to/assets/

// Windows absolute paths
$template->setAssetPath('C:\\www\\static');       // → C:\www\static\
$template->setAssetPath('D:/projects/assets');    // → D:/projects/assets/

// UNC paths (Windows network shares)
$template->setAssetPath('\\\\server\\share\\assets'); // → \\server\share\assets\
```

**Smart Path Detection:**

- **Relative Paths**: No leading separators (`/`, `\`) or drive letters
- **Unix Absolute**: Starts with `/` (e.g., `/var/www/assets`)
- **Windows Absolute**: Starts with drive letter (e.g., `C:\www`, `D:/assets`)
- **UNC Paths**: Starts with `\\` (e.g., `\\server\share`)

**How it works:**

- All paths are automatically resolved based on type (relative vs absolute)
- Relative paths are combined with the public path
- `@css` and `@js` create minified files in: `{resolvedAssetPath}/css/` or `{resolvedAssetPath}/js/`
- `@asset` copies single files to: `{resolvedAssetPath}/{relativePath}`
- `@assetDir` copies directories to: `{resolvedAssetPath}/{relativePath}`
- Smart caching: files only copied when source is newer than destination

## Template Directives

### Layout Inheritance

Use layouts to create a common structure:

**layout/global_layout.php**:
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

**view/page.php**:
```html
<!--@layout(layout/global_layout)-->
<h1>{$title}</h1>
<p>{$content}</p>
```

### Asset Management

#### CSS Files
```html
<!--@css(/css/styles.css)-->          <!-- Minified and cached -->
<!--@cssSingle(/css/critical.css)-->  <!-- Single file, not minified -->
```

#### JavaScript Files
CommentTemplate supports different JavaScript loading strategies:

```html
<!--@js(/js/script.js)-->             <!-- Minified, loaded at bottom -->
<!--@jsAsync(/js/analytics.js)-->     <!-- Minified, loaded at bottom with async -->
<!--@jsDefer(/js/utils.js)-->         <!-- Minified, loaded at bottom with defer -->
<!--@jsTop(/js/critical.js)-->        <!-- Minified, loaded in head -->
<!--@jsTopAsync(/js/tracking.js)-->   <!-- Minified, loaded in head with async -->
<!--@jsTopDefer(/js/polyfill.js)-->   <!-- Minified, loaded in head with defer -->
<!--@jsSingle(/js/widget.js)-->       <!-- Single file, not minified -->
<!--@jsSingleAsync(/js/ads.js)-->     <!-- Single file, not minified, async -->
<!--@jsSingleDefer(/js/social.js)-->  <!-- Single file, not minified, defer -->
```

#### Asset Directives in CSS/JS Files

CommentTemplate also processes asset directives within CSS and JavaScript files during compilation:

**CSS Example:**
```css
/* In your CSS files */
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

**JavaScript Example:**
```javascript
/* In your JS files */
const fontUrl = '<!--@asset(fonts/custom.woff2)-->';
const imageData = '<!--@base64(images/icon.png)-->';
```

#### Base64 Encoding
```html
<!--@base64(images/logo.png)-->       <!-- Inline as data URI -->
```
** Example: **
```html
<!-- Inline small images as data URIs for faster loading -->
<img src="<!--@base64(images/logo.png)-->" alt="Logo">
<div style="background-image: url('<!--@base64(icons/star.svg)-->');">
    Small icon as background
</div>
```

#### Asset Copying
```html
<!--@asset(images/photo.jpg)-->       <!-- Copy single asset to public directory -->
<!--@assetDir(assets)-->              <!-- Copy entire directory to public directory -->
```
** Example: **
```html
<!-- Copy and reference static assets -->
<img src="<!--@asset(images/hero-banner.jpg)-->" alt="Hero Banner">
<a href="<!--@asset(documents/brochure.pdf)-->" download>Download Brochure</a>

<!-- Copy entire directory (fonts, icons, etc.) -->
<!--@assetDir(assets/fonts)-->
<!--@assetDir(assets/icons)-->
```

### Template Includes
```html
<!--@import(components/header)-->     <!-- Include other templates -->
```
** Example: **
```html
<!-- Include reusable components -->
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

### Variable Processing

#### Basic Variables
```html
<h1>{$title}</h1>
<p>{$description}</p>
```

#### Variable Filters
```html
{$title|upper}                       <!-- Convert to uppercase -->
{$content|lower}                     <!-- Convert to lowercase -->
{$html|striptag}                     <!-- Strip HTML tags -->
{$text|escape}                       <!-- Escape HTML -->
{$multiline|nl2br}                   <!-- Convert newlines to <br> -->
{$html|br2nl}                        <!-- Convert <br> tags to newlines -->
{$description|trim}                  <!-- Trim whitespace -->
{$subject|title}                     <!-- Convert to title case -->
```

#### Variable Commands
```html
{$title|default=Default Title}       <!-- Set default value -->
{$name|concat= (Admin)}              <!-- Concatenate text -->
```

#### Variable Commands
```html
{$content|striptag|trim|escape}      <!-- Chain multiple filters -->
```

### Comments

Template comments are completely removed from the output and won't appear in the final HTML:

```html
{* This is a single-line template comment *}

{* 
   This is a multi-line 
   template comment 
   that spans several lines
*}

<h1>{$title}</h1>
{* Debug comment: checking if title variable works *}
<p>{$content}</p>
```

**Note**: Template comments `{* ... *}` are different from HTML comments `<!-- ... -->`. Template comments are removed during processing and never reach the browser.

## Example Project Structure

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
│   └── assets/           # Generated assets
│       ├── css/
│       └── js/
└── vendor/
```