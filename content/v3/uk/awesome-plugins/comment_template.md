# CommentTemplate

[CommentTemplate](https://github.com/KnifeLemon/CommentTemplate) — це потужний шаблонний двигун PHP з компіляцією активів, успадкуванням шаблонів та обробкою змінних. Він надає простий, але гнучкий спосіб керування шаблонами з вбудованою мініфікацією CSS/JS та кешуванням.

## Особливості

- **Успадкування шаблонів**: Використовуйте макети та включайте інші шаблони
- **Компіляція активів**: Автоматична мініфікація та кешування CSS/JS
- **Обробка змінних**: Змінні шаблонів з фільтрами та командами
- **Кодування Base64**: Вбудовані активи як data URI
- **Інтеграція з Flight Framework**: Опціональна інтеграція з PHP-фреймворком Flight

## Встановлення

Встановіть за допомогою composer.

```bash
composer require knifelemon/comment-template
```

## Базова конфігурація

Є деякі базові опції конфігурації для початку роботи. Ви можете прочитати більше про них у [CommentTemplate Repo](https://github.com/KnifeLemon/CommentTemplate).

### Метод 1: Використання функції зворотного виклику

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

$app->register('view', Engine::class, [], function (Engine $engine) use ($app) {
    // Кореневий каталог (де знаходиться index.php) — корінь документа вашого веб-додатка
    $engine->setPublicPath(__DIR__);
    
    // Каталог файлів шаблонів — підтримує як відносні, так і абсолютні шляхи
    $engine->setSkinPath('views');             // Відносно до public path
    
    // Де зберігатимуться скомпільовані активи — підтримує як відносні, так і абсолютні шляхи
    $engine->setAssetPath('assets');           // Відносно до public path
    
    // Розширення файлу шаблону
    $engine->setFileExtension('.php');
});

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

### Метод 2: Використання параметрів конструктора

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

// __construct(string $publicPath = "", string $skinPath = "", string $assetPath = "", string $fileExtension = "")
$app->register('view', Engine::class, [
    __DIR__,                // publicPath — кореневий каталог (де index.php)
    'views',                // skinPath — шлях до шаблонів (підтримує відносні/абсолютні)
    'assets',               // assetPath — шлях до скомпільованих активів (підтримує відносні/абсолютні)
    '.php'                  // fileExtension — розширення файлу шаблону
]);

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

## Конфігурація шляхів

CommentTemplate надає інтелектуальне керування шляхами як для відносних, так і для абсолютних шляхів:

### Public Path

**Public Path** — це кореневий каталог вашого веб-додатка, зазвичай де розташовано `index.php`. Це корінь документа, з якого веб-сервери обслуговують файли.

```php
// Приклад: якщо ваш index.php знаходиться в /var/www/html/myapp/index.php
$template->setPublicPath('/var/www/html/myapp');  // Кореневий каталог

// Приклад для Windows: якщо ваш index.php знаходиться в C:\xampp\htdocs\myapp\index.php
$template->setPublicPath('C:\\xampp\\htdocs\\myapp');
```

### Конфігурація шляху до шаблонів

Шлях до шаблонів підтримує як відносні, так і абсолютні шляхи:

```php
$template = new Engine();
$template->setPublicPath('/var/www/html/myapp');  // Кореневий каталог (де index.php)

// Відносні шляхи — автоматично об'єднуються з public path
$template->setSkinPath('views');           // → /var/www/html/myapp/views/
$template->setSkinPath('templates/pages'); // → /var/www/html/myapp/templates/pages/

// Абсолютні шляхи — використовуються як є (Unix/Linux)
$template->setSkinPath('/var/www/templates');      // → /var/www/templates/
$template->setSkinPath('/full/path/to/templates'); // → /full/path/to/templates/

// Абсолютні шляхи для Windows
$template->setSkinPath('C:\\www\\templates');     // → C:\www\templates\
$template->setSkinPath('D:/projects/templates');  // → D:/projects/templates/

// UNC шляхи (мережеві ресурси Windows)
$template->setSkinPath('\\\\server\\share\\templates'); // → \\server\share\templates\
```

### Конфігурація шляху до активів

Шлях до активів також підтримує як відносні, так і абсолютні шляхи:

```php
// Відносні шляхи — автоматично об'єднуються з public path
$template->setAssetPath('assets');        // → /var/www/html/myapp/assets/
$template->setAssetPath('static/files');  // → /var/www/html/myapp/static/files/

// Абсолютні шляхи — використовуються як є (Unix/Linux)
$template->setAssetPath('/var/www/cdn');           // → /var/www/cdn/
$template->setAssetPath('/full/path/to/assets');   // → /full/path/to/assets/

// Абсолютні шляхи для Windows
$template->setAssetPath('C:\\www\\static');       // → C:\www\static\
$template->setAssetPath('D:/projects/assets');    // → D:/projects/assets/

// UNC шляхи (мережеві ресурси Windows)
$template->setAssetPath('\\\\server\\share\\assets'); // → \\server\share\assets\
```

**Інтелектуальне виявлення шляхів:**

- **Відносні шляхи**: Без початкових розділювачів (`/`, `\`) або букв дисків
- **Абсолютні Unix**: Починаються з `/` (наприклад, `/var/www/assets`)
- **Абсолютні Windows**: Починаються з літери диска (наприклад, `C:\www`, `D:/assets`)
- **UNC шляхи**: Починаються з `\\` (наприклад, `\\server\share`)

**Як це працює:**

- Усі шляхи автоматично розв'язуються на основі типу (відносний проти абсолютного)
- Відносні шляхи об'єднуються з public path
- `@css` та `@js` створюють мініфіковані файли в: `{resolvedAssetPath}/css/` або `{resolvedAssetPath}/js/`
- `@asset` копіює окремі файли до: `{resolvedAssetPath}/{relativePath}`
- `@assetDir` копіює каталоги до: `{resolvedAssetPath}/{relativePath}`
- Інтелектуальне кешування: файли копіюються тільки коли джерело новіше за призначення

## Директиви шаблонів

### Успадкування макетів

Використовуйте макети для створення спільної структури:

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

### Керування активами

#### Файли CSS
```html
<!--@css(/css/styles.css)-->          <!-- Мініфіковано та кешовано -->
<!--@cssSingle(/css/critical.css)-->  <!-- Один файл, не мініфіковано -->
```

#### Файли JavaScript
CommentTemplate підтримує різні стратегії завантаження JavaScript:

```html
<!--@js(/js/script.js)-->             <!-- Мініфіковано, завантажено внизу -->
<!--@jsAsync(/js/analytics.js)-->     <!-- Мініфіковано, завантажено внизу з async -->
<!--@jsDefer(/js/utils.js)-->         <!-- Мініфіковано, завантажено внизу з defer -->
<!--@jsTop(/js/critical.js)-->        <!-- Мініфіковано, завантажено в head -->
<!--@jsTopAsync(/js/tracking.js)-->   <!-- Мініфіковано, завантажено в head з async -->
<!--@jsTopDefer(/js/polyfill.js)-->   <!-- Мініфіковано, завантажено в head з defer -->
<!--@jsSingle(/js/widget.js)-->       <!-- Один файл, не мініфіковано -->
<!--@jsSingleAsync(/js/ads.js)-->     <!-- Один файл, не мініфіковано, async -->
<!--@jsSingleDefer(/js/social.js)-->  <!-- Один файл, не мініфіковано, defer -->
```

#### Директиви активів у файлах CSS/JS

CommentTemplate також обробляє директиви активів у файлах CSS та JavaScript під час компіляції:

**Приклад CSS:**
```css
/* У ваших файлах CSS */
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

**Приклад JavaScript:**
```javascript
/* У ваших файлах JS */
const fontUrl = '<!--@asset(fonts/custom.woff2)-->';
const imageData = '<!--@base64(images/icon.png)-->';
```

#### Кодування Base64
```html
<!--@base64(images/logo.png)-->       <!-- Вбудовано як data URI -->
```
** Приклад: **
```html
<!-- Вбудовуйте малі зображення як data URI для швидшого завантаження -->
<img src="<!--@base64(images/logo.png)-->" alt="Logo">
<div style="background-image: url('<!--@base64(icons/star.svg)-->');">
    Маленька іконка як фон
</div>
```

#### Копіювання активів
```html
<!--@asset(images/photo.jpg)-->       <!-- Копіювати один актив до публічного каталогу -->
<!--@assetDir(assets)-->              <!-- Копіювати весь каталог до публічного каталогу -->
```
** Приклад: **
```html
<!-- Копіювати та посилатися на статичні активи -->
<img src="<!--@asset(images/hero-banner.jpg)-->" alt="Hero Banner">
<a href="<!--@asset(documents/brochure.pdf)-->" download>Завантажити брошуру</a>

<!-- Копіювати весь каталог (шрифти, іконки тощо) -->
<!--@assetDir(assets/fonts)-->
<!--@assetDir(assets/icons)-->
```

### Включення шаблонів
```html
<!--@import(components/header)-->     <!-- Включити інші шаблони -->
```
** Приклад: **
```html
<!-- Включити повторно використовувані компоненти -->
<!--@import(components/header)-->

<main>
    <h1>Ласкаво просимо на наш веб-сайт</h1>
    <!--@import(components/sidebar)-->
    
    <div class="content">
        <p>Основний вміст тут...</p>
    </div>
</main>

<!--@import(components/footer)-->
```

### Обробка змінних

#### Базові змінні
```html
<h1>{$title}</h1>
<p>{$description}</p>
```

#### Фільтри змінних
```html
{$title|upper}                       <!-- Перетворити у верхній регістр -->
{$content|lower}                     <!-- Перетворити у нижній регістр -->
{$html|striptag}                     <!-- Видалити HTML-теги -->
{$text|escape}                       <!-- Екранувати HTML -->
{$multiline|nl2br}                   <!-- Перетворити нові рядки на <br> -->
{$html|br2nl}                        <!-- Перетворити теги <br> на нові рядки -->
{$description|trim}                  <!-- Обрізати пробіли -->
{$subject|title}                     <!-- Перетворити у title case -->
```

#### Команди змінних
```html
{$title|default=Default Title}       <!-- Встановити значення за замовчуванням -->
{$name|concat= (Admin)}              <!-- Об'єднати текст -->
```

#### Команди змінних
```html
{$content|striptag|trim|escape}      <!-- Ланцюжок кількох фільтрів -->
```

### Коментарі

Коментарі шаблонів повністю видаляються з виводу та не з'являються в остаточному HTML:

```html
{* Це однорядковий коментар шаблону *}

{* 
   Це багаторядковий 
   коментар шаблону 
   що охоплює кілька рядків
*}

<h1>{$title}</h1>
{* Коментар для налагодження: перевірка, чи працює змінна title *}
<p>{$content}</p>
```

**Примітка**: Коментарі шаблонів `{* ... *}` відрізняються від HTML-коментарів `<!-- ... -->`. Коментарі шаблонів видаляються під час обробки та ніколи не досягають браузера.

## Приклад структури проекту

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
│   └── assets/           # Згенеровані активи
│       ├── css/
│       └── js/
└── vendor/
```