# CommentTemplate

[CommentTemplate](https://github.com/KnifeLemon/CommentTemplate) — потужний рушій шаблонів PHP з компіляцією активів, успадкуванням шаблонів та обробкою змінних. Він надає простий, але гнучкий спосіб керування шаблонами з вбудованою мініфікацією CSS/JS та кешуванням.

## Особливості

- **Успадкування шаблонів**: Використовуйте макети та включайте інші шаблони
- **Компіляція активів**: Автоматична мініфікація CSS/JS та кешування
- **Обробка змінних**: Змінні шаблонів з фільтрами та командами
- **Кодування Base64**: Вбудовані активи як data URI
- **Інтеграція з Flight Framework**: Опціональна інтеграція з PHP фреймворком Flight

## Встановлення

Встановіть за допомогою composer.

```bash
composer require knifelemon/comment-template
```

## Базова конфігурація

Є деякі базові опції конфігурації для початку. Ви можете прочитати більше про них у [CommentTemplate Repo](https://github.com/KnifeLemon/CommentTemplate).

### Метод 1: Використання функції зворотного виклику

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

$app->register('view', Engine::class, [], function (Engine $engine) use ($app) {
    // Кореневий каталог (де знаходиться index.php) - кореневий документ вашого веб-застосунку
    $engine->setPublicPath(__DIR__);
    
    // Каталог файлів шаблонів - підтримує відносні та абсолютні шляхи
    $engine->setSkinPath('views');             // Відносно публічного шляху
    
    // Де зберігатимуться скомпільовані активи - підтримує відносні та абсолютні шляхи
    $engine->setAssetPath('assets');           // Відносно публічного шляху
    
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
    __DIR__,                // publicPath - кореневий каталог (де знаходиться index.php)
    'views',                // skinPath - шлях шаблонів (підтримує відносні/абсолютні)
    'assets',               // assetPath - шлях скомпільованих активів (підтримує відносні/абсолютні)
    '.php'                  // fileExtension - розширення файлу шаблону
]);

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

## Конфігурація шляхів

CommentTemplate забезпечує інтелектуальну обробку шляхів для відносних та абсолютних шляхів:

### Публічний шлях

**Публічний шлях** - це кореневий каталог вашого веб-застосунку, зазвичай де знаходиться `index.php`. Це кореневий документ, з якого веб-сервери надають файли.

```php
// Приклад: якщо ваш index.php знаходиться в /var/www/html/myapp/index.php
$template->setPublicPath('/var/www/html/myapp');  // Кореневий каталог

// Приклад Windows: якщо ваш index.php знаходиться в C:\xampp\htdocs\myapp\index.php
$template->setPublicPath('C:\\xampp\\htdocs\\myapp');
```

### Конфігурація шляху шаблонів

Шлях шаблонів підтримує як відносні, так і абсолютні шляхи:

```php
$template = new Engine();
$template->setPublicPath('/var/www/html/myapp');  // Кореневий каталог (де знаходиться index.php)

// Відносні шляхи - автоматично поєднуються з публічним шляхом
$template->setSkinPath('views');           // → /var/www/html/myapp/views/
$template->setSkinPath('templates/pages'); // → /var/www/html/myapp/templates/pages/

// Абсолютні шляхи - використовуються як є (Unix/Linux)
$template->setSkinPath('/var/www/templates');      // → /var/www/templates/
$template->setSkinPath('/full/path/to/templates'); // → /full/path/to/templates/

// Абсолютні шляхи Windows
$template->setSkinPath('C:\\www\\templates');     // → C:\www\templates\
$template->setSkinPath('D:/projects/templates');  // → D:/projects/templates/

// UNC шляхи (мережеві ресурси Windows)
$template->setSkinPath('\\\\server\\share\\templates'); // → \\server\share\templates\
```

### Конфігурація шляху активів

Шлях активів також підтримує як відносні, так і абсолютні шляхи:

```php
// Відносні шляхи - автоматично поєднуються з публічним шляхом
$template->setAssetPath('assets');        // → /var/www/html/myapp/assets/
$template->setAssetPath('static/files');  // → /var/www/html/myapp/static/files/

// Абсолютні шляхи - використовуються як є (Unix/Linux)
$template->setAssetPath('/var/www/cdn');           // → /var/www/cdn/
$template->setAssetPath('/full/path/to/assets');   // → /full/path/to/assets/

// Абсолютні шляхи Windows
$template->setAssetPath('C:\\www\\static');       // → C:\www\static\
$template->setAssetPath('D:/projects/assets');    // → D:/projects/assets/

// UNC шляхи (мережеві ресурси Windows)
$template->setAssetPath('\\\\server\\share\\assets'); // → \\server\share\assets\
```

**Розумне визначення шляхів:**

- **Відносні шляхи**: Без початкових роздільників (`/`, `\`) або літер дисків
- **Unix абсолютні**: Починаються з `/` (напр. `/var/www/assets`)
- **Windows абсолютні**: Починаються з літери диска (напр. `C:\www`, `D:/assets`)
- **UNC шляхи**: Починаються з `\\` (напр. `\\server\share`)

**Як це працює:**

- Всі шляхи автоматично розв'язуються на основі типу (відносний vs абсолютний)
- Відносні шляхи поєднуються з публічним шляхом
- `@css` та `@js` створюють мінімізовані файли в: `{resolvedAssetPath}/css/` або `{resolvedAssetPath}/js/`
- `@asset` копіює окремі файли до: `{resolvedAssetPath}/{relativePath}`
- `@assetDir` копіює каталоги до: `{resolvedAssetPath}/{relativePath}`
- Розумне кешування: файли копіюються лише коли джерело новіше за призначення

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

CommentTemplate також обробляє директиви активів у CSS та JavaScript файлах під час компіляції:

**Приклад CSS:**
```css
/* У ваших CSS файлах */
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
/* У ваших JS файлах */
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
<!--@asset(images/photo.jpg)-->       <!-- Копіювати один актив до публічної директорії -->
<!--@assetDir(assets)-->              <!-- Копіювати всю директорію до публічної директорії -->
```
** Приклад: **
```html
<!-- Копіювати та посилатися на статичні активи -->
<img src="<!--@asset(images/hero-banner.jpg)-->" alt="Hero Banner">
<a href="<!--@asset(documents/brochure.pdf)-->" download>Завантажити Брошуру</a>

<!-- Копіювати всю директорію (шрифти, іконки тощо) -->
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
        <p>Основний контент тут...</p>
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
{$title|upper}                       <!-- Перетворити на великі літери -->
{$content|lower}                     <!-- Перетворити на малі літери -->
{$html|striptag}                     <!-- Видалити HTML теги -->
{$text|escape}                       <!-- Екранувати HTML -->
{$multiline|nl2br}                   <!-- Перетворити нові рядки на <br> -->
{$html|br2nl}                        <!-- Перетворити <br> теги на нові рядки -->
{$description|trim}                  <!-- Обрізати пробіли -->
{$subject|title}                     <!-- Перетворити на title case -->
```

#### Команди змінних
```html
{$title|default=Default Title}       <!-- Встановити значення за замовчуванням -->
{$name|concat= (Admin)}              <!-- Об'єднати текст -->
```

#### Ланцюжок кількох фільтрів
```html
{$content|striptag|trim|escape}      <!-- Ланцюжок кількох фільтрів -->
```

### Коментарі

Коментарі шаблонів повністю видаляються з виводу і не з'являються в кінцевому HTML:

```html
{* Це однорядковий коментар шаблону *}

{* 
   Це багаторядковий
   коментар шаблону 
   на кілька рядків
*}

<h1>{$title}</h1>
{* Відлагоджувальний коментар: перевіряємо чи працює змінна title *}
<p>{$content}</p>
```

**Примітка**: Коментарі шаблонів `{* ... *}` відрізняються від HTML коментарів `<!-- ... -->`. Коментарі шаблонів видаляються під час обробки і ніколи не досягають браузера.

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