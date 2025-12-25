# CommentTemplate

[CommentTemplate](https://github.com/KnifeLemon/CommentTemplate) — это мощный шаблонный движок для PHP с компиляцией ресурсов, наследованием шаблонов и обработкой переменных. Он предоставляет простой и гибкий способ управления шаблонами с встроенной минификацией CSS/JS и кэшированием.

## Особенности

- **Наследование шаблонов**: Используйте макеты и включайте другие шаблоны
- **Компиляция ресурсов**: Автоматическая минификация и кэширование CSS/JS
- **Обработка переменных**: Переменные шаблонов с фильтрами и командами
- **Кодирование Base64**: Встраивание ресурсов как data URI
- **Интеграция с Flight Framework**: Опциональная интеграция с PHP-фреймворком Flight

## Установка

Установите с помощью Composer.

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
    // Корневая директория (где находится index.php) — корень документов вашего веб-приложения
    $engine->setPublicPath(__DIR__);
    
    // Директория файлов шаблонов — поддерживает как относительные, так и абсолютные пути
    $engine->setSkinPath('views');             // Относительно пути public
    
    // Куда будут сохраняться скомпилированные ресурсы — поддерживает как относительные, так и абсолютные пути
    $engine->setAssetPath('assets');           // Относительно пути public
    
    // Расширение файлов шаблонов
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
    __DIR__,                // publicPath — корневая директория (где находится index.php)
    'views',                // skinPath — путь к шаблонам (поддерживает относительные/абсолютные)
    'assets',               // assetPath — путь к скомпилированным ресурсам (поддерживает относительные/абсолютные)
    '.php'                  // fileExtension — расширение файлов шаблонов
]);

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

## Конфигурация путей

CommentTemplate предоставляет интеллектуальную обработку путей как для относительных, так и для абсолютных путей:

### Публичный путь

**Публичный путь** — это корневая директория вашего веб-приложения, обычно там, где находится `index.php`. Это корень документов, из которого веб-серверы обслуживают файлы.

```php
// Пример: если ваш index.php находится в /var/www/html/myapp/index.php
$template->setPublicPath('/var/www/html/myapp');  // Корневая директория

// Пример для Windows: если ваш index.php находится в C:\xampp\htdocs\myapp\index.php
$template->setPublicPath('C:\\xampp\\htdocs\\myapp');
```

### Конфигурация пути к шаблонам

Путь к шаблонам поддерживает как относительные, так и абсолютные пути:

```php
$template = new Engine();
$template->setPublicPath('/var/www/html/myapp');  // Корневая директория (где находится index.php)

// Относительные пути — автоматически объединяются с публичным путем
$template->setSkinPath('views');           // → /var/www/html/myapp/views/
$template->setSkinPath('templates/pages'); // → /var/www/html/myapp/templates/pages/

// Абсолютные пути — используются как есть (Unix/Linux)
$template->setSkinPath('/var/www/templates');      // → /var/www/templates/
$template->setSkinPath('/full/path/to/templates'); // → /full/path/to/templates/

// Абсолютные пути для Windows
$template->setSkinPath('C:\\www\\templates');     // → C:\www\templates\
$template->setSkinPath('D:/projects/templates');  // → D:/projects/templates/

// UNC-пути (сетевые ресурсы Windows)
$template->setSkinPath('\\\\server\\share\\templates'); // → \\server\share\templates\
```

### Конфигурация пути к ресурсам

Путь к ресурсам также поддерживает как относительные, так и абсолютные пути:

```php
// Относительные пути — автоматически объединяются с публичным путем
$template->setAssetPath('assets');        // → /var/www/html/myapp/assets/
$template->setAssetPath('static/files');  // → /var/www/html/myapp/static/files/

// Абсолютные пути — используются как есть (Unix/Linux)
$template->setAssetPath('/var/www/cdn');           // → /var/www/cdn/
$template->setAssetPath('/full/path/to/assets');   // → /full/path/to/assets/

// Абсолютные пути для Windows
$template->setAssetPath('C:\\www\\static');       // → C:\www\static\
$template->setAssetPath('D:/projects/assets');    // → D:/projects/assets/

// UNC-пути (сетевые ресурсы Windows)
$template->setAssetPath('\\\\server\\share\\assets'); // → \\server\share\assets\
```

**Умное определение путей:**

- **Относительные пути**: Без ведущих разделителей (`/`, `\`) или букв дисков
- **Абсолютные Unix**: Начинаются с `/` (например, `/var/www/assets`)
- **Абсолютные Windows**: Начинаются с буквы диска (например, `C:\www`, `D:/assets`)
- **UNC-пути**: Начинаются с `\\` (например, `\\server\share`)

**Как это работает:**

- Все пути автоматически разрешаются в зависимости от типа (относительный vs абсолютный)
- Относительные пути объединяются с публичным путем
- `@css` и `@js` создают минифицированные файлы в: `{resolvedAssetPath}/css/` или `{resolvedAssetPath}/js/`
- `@asset` копирует отдельные файлы в: `{resolvedAssetPath}/{relativePath}`
- `@assetDir` копирует директории в: `{resolvedAssetPath}/{relativePath}`
- Умное кэширование: файлы копируются только если исходный файл новее целевого

## Интеграция с Tracy Debugger

CommentTemplate включает интеграцию с [Tracy Debugger](https://tracy.nette.org/) для логирования и отладки в разработке.

![Comment Template Tracy](https://raw.githubusercontent.com/KnifeLemon/CommentTemplate/refs/heads/master/tracy.jpeg)

### Установка

```bash
composer require tracy/tracy
```

### Использование

```php
<?php
use KnifeLemon\CommentTemplate\Engine;
use Tracy\Debugger;

// Включить Tracy (должно быть вызвано перед любым выводом)
Debugger::enable(Debugger::DEVELOPMENT);
Flight::set('flight.content_length', false);

// Переопределение шаблона
$app->register('view', Engine::class, [], function (Engine $builder) use ($app) {
    $builder->setPublicPath($app->get('flight.views.topPath'));
    $builder->setAssetPath($app->get('flight.views.assetPath'));
    $builder->setSkinPath($app->get('flight.views.path'));
    $builder->setFileExtension($app->get('flight.views.extension'));
});
$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});

$app->start();
```

### Функции панели отладки

CommentTemplate добавляет пользовательскую панель в панель отладки Tracy с четырьмя вкладками:

- **Overview**: Конфигурация, метрики производительности и счетчики
- **Assets**: Детали компиляции CSS/JS с коэффициентами сжатия
- **Variables**: Исходные и преобразованные значения с примененными фильтрами
- **Timeline**: Хронологический вид всех операций шаблонов

### Что логируется

- Рендеринг шаблонов (начало/конец, длительность, макеты, импорты)
- Компиляция ассетов (файлы CSS/JS, размеры, коэффициенты сжатия)
- Обработка переменных (исходные/преобразованные значения, фильтры)
- Операции с ассетами (кодирование base64, копирование файлов)
- Метрики производительности (длительность, использование памяти)

**Примечание:** Нулевое влияние на производительность, когда Tracy не установлен или отключен.

См. [полный рабочий пример с Flight PHP](https://github.com/KnifeLemon/CommentTemplate/tree/master/examples/flightphp).

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

### Управление ресурсами

#### CSS-файлы
```html
<!--@css(/css/styles.css)-->          <!-- Минифицировано и кэшировано -->
<!--@cssSingle(/css/critical.css)-->  <!-- Один файл, не минифицирован -->
```

#### JavaScript-файлы
CommentTemplate поддерживает различные стратегии загрузки JavaScript:

```html
<!--@js(/js/script.js)-->             <!-- Минифицировано, загружается внизу -->
<!--@jsAsync(/js/analytics.js)-->     <!-- Минифицировано, загружается внизу с async -->
<!--@jsDefer(/js/utils.js)-->         <!-- Минифицировано, загружается внизу с defer -->
<!--@jsTop(/js/critical.js)-->        <!-- Минифицировано, загружается в head -->
<!--@jsTopAsync(/js/tracking.js)-->   <!-- Минифицировано, загружается в head с async -->
<!--@jsTopDefer(/js/polyfill.js)-->   <!-- Минифицировано, загружается в head с defer -->
<!--@jsSingle(/js/widget.js)-->       <!-- Один файл, не минифицирован -->
<!--@jsSingleAsync(/js/ads.js)-->     <!-- Один файл, не минифицирован, async -->
<!--@jsSingleDefer(/js/social.js)-->  <!-- Один файл, не минифицирован, defer -->
```

#### Директивы ресурсов в CSS/JS-файлах

CommentTemplate также обрабатывает директивы ресурсов внутри CSS- и JavaScript-файлов во время компиляции:

**Пример CSS:**
```css
/* В ваших CSS-файлах */
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
/* В ваших JS-файлах */
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

#### Копирование ресурсов
```html
<!--@asset(images/photo.jpg)-->       <!-- Копирование одного ресурса в публичную директорию -->
<!--@assetDir(assets)-->              <!-- Копирование всей директории в публичную директорию -->
```
** Пример: **
```html
<!-- Копирование и ссылка на статические ресурсы -->
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
<!-- Включение повторно используемых компонентов -->
<!--@import(components/header)-->

<main>
    <h1>Добро пожаловать на наш сайт</h1>
    <!--@import(components/sidebar)-->
    
    <div class="content">
        <p>Основное содержимое здесь...</p>
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
{$title|upper}                       <!-- Преобразовать в верхний регистр -->
{$content|lower}                     <!-- Преобразовать в нижний регистр -->
{$html|striptag}                     <!-- Удалить HTML-теги -->
{$text|escape}                       <!-- Экранировать HTML -->
{$multiline|nl2br}                   <!-- Преобразовать переносы строк в <br> -->
{$html|br2nl}                        <!-- Преобразовать теги <br> в переносы строк -->
{$description|trim}                  <!-- Удалить пробелы -->
{$subject|title}                     <!-- Преобразовать в заглавный регистр -->
```

#### Команды переменных
```html
{$title|default=Default Title}       <!-- Установить значение по умолчанию -->
{$name|concat= (Admin)}              <!-- Конкатенация текста -->
```

#### Команды переменных
```html
{$content|striptag|trim|escape}      <!-- Цепочка нескольких фильтров -->
```

### Комментарии

Комментарии шаблонов полностью удаляются из вывода и не появляются в итоговом HTML:

```html
{* Это однострочный комментарий шаблона *}

{* 
   Это многострочный 
   комментарий шаблона 
   который занимает несколько строк
*}

<h1>{$title}</h1>
{* Отладочный комментарий: проверка работы переменной title *}
<p>{$content}</p>
```

**Примечание**: Комментарии шаблонов `{* ... *}` отличаются от HTML-комментариев `<!-- ... -->`. Комментарии шаблонов удаляются во время обработки и никогда не доходят до браузера.

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
│   └── assets/           # Сгенерированные ресурсы
│       ├── css/
│       └── js/
└── vendor/
```