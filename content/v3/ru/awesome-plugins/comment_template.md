# CommentTemplate

[CommentTemplate](https://github.com/KnifeLemon/CommentTemplate) — это мощный шаблонизатор PHP с компиляцией активов, наследованием шаблонов и обработкой переменных. Он предоставляет простой и гибкий способ управления шаблонами с встроенной минификацией CSS/JS и кэшированием.

## Особенности

- **Наследование шаблонов**: Использование макетов и включение других шаблонов
- **Компиляция активов**: Автоматическая минификация и кэширование CSS/JS
- **Обработка переменных**: Переменные шаблонов с фильтрами и командами
- **Кодирование Base64**: Встраивание активов как data URI
- **Интеграция с фреймворком Flight**: Необязательная интеграция с фреймворком PHP Flight

## Установка

Установите с помощью composer.

```bash
composer require knifelemon/comment-template
```

## Базовая конфигурация

Есть некоторые базовые опции конфигурации для начала работы. Вы можете прочитать больше о них в [CommentTemplate Repo](https://github.com/KnifeLemon/CommentTemplate).

### Метод 1: Использование функции обратного вызова

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

$app->register('view', Engine::class, [], function (Engine $engine) use ($app) {
    // Где хранятся файлы шаблонов
    $engine->setTemplatesPath(__DIR__ . '/views');
    
    // Откуда будут обслуживаться публичные активы
    $engine->setPublicPath(__DIR__ . '/public');
    
    // Где будут храниться скомпилированные активы
    $engine->setAssetPath('assets');
    
    // Расширение файла шаблона
    $engine->setFileExtension('.php');
});

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

### Метод 2: Использование параметров конструктора

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

// __construct(string $publicPath = "", string $skinPath = "", string $assetPath = "", string $fileExtension = "")
$app->register('view', Engine::class, [
    __DIR__ . '/public',    // publicPath - откуда будут обслуживаться активы
    __DIR__ . '/views',     // skinPath - где хранятся файлы шаблонов  
    'assets',               // assetPath - где будут храниться скомпилированные активы
    '.php'                  // fileExtension - расширение файла шаблона
]);

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

## Директивы шаблонов

### Наследование макетов

Используйте макеты для создания общей структуры:

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

### Управление активами

#### Файлы CSS
```html
<!--@css(/css/styles.css)-->          <!-- Минифицировано и кэшировано -->
<!--@cssSingle(/css/critical.css)-->  <!-- Один файл, не минифицировано -->
```

#### Файлы JavaScript
CommentTemplate поддерживает различные стратегии загрузки JavaScript:

```html
<!--@js(/js/script.js)-->             <!-- Минифицировано, загружается внизу -->
<!--@jsAsync(/js/analytics.js)-->     <!-- Минифицировано, загружается внизу с async -->
<!--@jsDefer(/js/utils.js)-->         <!-- Минифицировано, загружается внизу с defer -->
<!--@jsTop(/js/critical.js)-->        <!-- Минифицировано, загружается в head -->
<!--@jsTopAsync(/js/tracking.js)-->   <!-- Минифицировано, загружается в head с async -->
<!--@jsTopDefer(/js/polyfill.js)-->   <!-- Минифицировано, загружается в head с defer -->
<!--@jsSingle(/js/widget.js)-->       <!-- Один файл, не минифицировано -->
<!--@jsSingleAsync(/js/ads.js)-->     <!-- Один файл, не минифицировано, async -->
<!--@jsSingleDefer(/js/social.js)-->  <!-- Один файл, не минифицировано, defer -->
```

#### Директивы активов в файлах CSS/JS

CommentTemplate также обрабатывает директивы активов в файлах CSS и JavaScript во время компиляции:

**Пример CSS:**
```css
/* В ваших файлах CSS */
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

**Пример JavaScript:**
```javascript
/* В ваших файлах JS */
const fontUrl = '<!--@asset(fonts/custom.woff2)-->';
const imageData = '<!--@base64(images/icon.png)-->';
```

#### Кодирование Base64
```html
<!--@base64(images/logo.png)-->       <!-- Встраивается как data URI -->
```
** Пример: **
```html
<!-- Встраивание маленьких изображений как data URI для более быстрой загрузки -->
<img src="<!--@base64(images/logo.png)-->" alt="Logo">
<div style="background-image: url('<!--@base64(icons/star.svg)-->');">
    Маленькая иконка как фон
</div>
```

#### Копирование активов
```html
<!--@asset(images/photo.jpg)-->       <!-- Копирование одного актива в публичную директорию -->
<!--@assetDir(assets)-->              <!-- Копирование всей директории в публичную директорию -->
```
** Пример: **
```html
<!-- Копирование и ссылка на статические активы -->
<img src="<!--@asset(images/hero-banner.jpg)-->" alt="Hero Banner">
<a href="<!--@asset(documents/brochure.pdf)-->" download>Скачать брошюру</a>

<!-- Копирование всей директории (шрифты, иконки и т.д.) -->
<!--@assetDir(assets/fonts)-->
<!--@assetDir(assets/icons)-->
```

### Включение шаблонов
```html
<!--@import(components/header)-->     <!-- Включение других шаблонов -->
```
** Пример: **
```html
<!-- Включение переиспользуемых компонентов -->
<!--@import(components/header)-->

<main>
    <h1>Добро пожаловать на наш сайт</h1>
    <!--@import(components/sidebar)-->
    
    <div class="content">
        <p>Основной контент здесь...</p>
    </div>
</main>

<!--@import(components/footer)-->
```

### Обработка переменных

#### Базовые переменные
```html
<h1>{$title}</h1>
<p>{$description}</p>
```

#### Фильтры переменных
```html
{$title|upper}                       <!-- Преобразование в верхний регистр -->
{$content|lower}                     <!-- Преобразование в нижний регистр -->
{$html|striptag}                     <!-- Удаление HTML-тегов -->
{$text|escape}                       <!-- Экранирование HTML -->
{$multiline|nl2br}                   <!-- Преобразование переносов строк в <br> -->
{$html|br2nl}                        <!-- Преобразование тегов <br> в переносы строк -->
{$description|trim}                  <!-- Обрезка пробелов -->
{$subject|title}                     <!-- Преобразование в заглавный регистр -->
```

#### Команды переменных
```html
{$title|default=Default Title}       <!-- Установка значения по умолчанию -->
{$name|concat= (Admin)}              <!-- Конкатенация текста -->
```

#### Команды переменных
```html
{$content|striptag|trim|escape}      <!-- Цепочка нескольких фильтров -->
```

## Структура примера проекта

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
│   └── assets/           # Сгенерированные активы
│       ├── css/
│       └── js/
└── vendor/
```