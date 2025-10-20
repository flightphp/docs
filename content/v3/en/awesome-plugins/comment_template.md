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
    // Where your template files are stored
    $engine->setTemplatesPath(__DIR__ . '/views');
    
    // Where your public assets will be served from
    $engine->setPublicPath(__DIR__ . '/public');
    
    // Where compiled assets will be stored
    $engine->setAssetPath('assets');
    
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
    __DIR__ . '/public',    // publicPath - where assets will be served from
    __DIR__ . '/views',     // skinPath - where template files are stored  
    'assets',               // assetPath - where compiled assets will be stored
    '.php'                  // fileExtension - template file extension
]);

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

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