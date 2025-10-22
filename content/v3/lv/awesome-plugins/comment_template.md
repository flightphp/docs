# CommentTemplate

[CommentTemplate](https://github.com/KnifeLemon/CommentTemplate) ir jaudīgs PHP veidņu dz motor ar resursu kompilāciju, veidņu mantojumu un mainīgo apstrādi. Tas nodrošina vienkāršu, bet elastīgu veidu, kā pārvaldīt veidnes ar iebūvētu CSS/JS minimizāciju un kešošanu.

## Funkcijas

- **Veidņu mantojums**: Izmantojiet izkārtojumus un iekļaujiet citas veidnes
- **Resursu kompilācija**: Automātiska CSS/JS minimizācija un kešošana
- **Mainīgo apstrāde**: Veidņu mainīgie ar filtrēšanu un komandām
- **Base64 kodēšana**: Iekšējie resursi kā datu URI
- **Flight Framework integrācija**: Neobligātā integrācija ar Flight PHP framework

## Instalācija

Instalējiet ar composer.

```bash
composer require knifelemon/comment-template
```

## Pamata konfigurācija

Ir daži pamata konfigurācijas varianti, lai sāktu darbu. Jūs varat lasīt vairāk par tiem [CommentTemplate Repo](https://github.com/KnifeLemon/CommentTemplate).

### 1. metode: Izmantojot atgriezeniskās saites funkciju

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

$app->register('view', Engine::class, [], function (Engine $engine) use ($app) {
    // Saknes direktorijs (kur atrodas index.php) - jūsu tīmekļa lietojumprogrammas dokumentu sakne
    $engine->setPublicPath(__DIR__);
    
    // Veidņu failu direktorijs - atbalsta relatīvos un absolūtos ceļus
    $engine->setSkinPath('views');             // Relatīvs pret publisko ceļu
    
    // Kur tiks glabāti kompilētie resursi - atbalsta relatīvos un absolūtos ceļus
    $engine->setAssetPath('assets');           // Relatīvs pret publisko ceļu
    
    // Veidnes faila paplašinājums
    $engine->setFileExtension('.php');
});

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

### 2. metode: Izmantojot konstruktoras parametrus

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

// __construct(string $publicPath = "", string $skinPath = "", string $assetPath = "", string $fileExtension = "")
$app->register('view', Engine::class, [
    __DIR__,                // publicPath - saknes direktorijs (kur atrodas index.php)
    'views',                // skinPath - veidņu ceļš (atbalsta relatīvos/absolūtos)
    'assets',               // assetPath - kompilēto resursu ceļš (atbalsta relatīvos/absolūtos)
    '.php'                  // fileExtension - veidnes faila paplašinājums
]);

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

## Ceļa konfigurācija

CommentTemplate nodrošina inteliģentu ceļu apstrādi relatīvajiem un absolūtajiem ceļiem:

### Publiskais ceļš

**Publiskais ceļš** ir jūsu tīmekļa lietojumprogrammas saknes direktorijs, parasti tur, kur atrodas `index.php`. Šis ir dokumentu sakne, no kuras tīmekļa serveri apkalpo failus.

```php
// Piemērs: ja jūsu index.php atrodas /var/www/html/myapp/index.php
$template->setPublicPath('/var/www/html/myapp');  // Saknes direktorijs

// Windows piemērs: ja jūsu index.php atrodas C:\xampp\htdocs\myapp\index.php
$template->setPublicPath('C:\\xampp\\htdocs\\myapp');
```

### Veidņu ceļa konfigurācija

Veidņu ceļš atbalsta gan relatīvos, gan absolūtos ceļus:

```php
$template = new Engine();
$template->setPublicPath('/var/www/html/myapp');  // Saknes direktorijs (kur atrodas index.php)

// Relatīvie ceļi - automātiski apvienoti ar publisko ceļu
$template->setSkinPath('views');           // → /var/www/html/myapp/views/
$template->setSkinPath('templates/pages'); // → /var/www/html/myapp/templates/pages/

// Absolūtie ceļi - izmantoti kā ir (Unix/Linux)
$template->setSkinPath('/var/www/templates');      // → /var/www/templates/
$template->setSkinPath('/full/path/to/templates'); // → /full/path/to/templates/

// Windows absolūtie ceļi
$template->setSkinPath('C:\\www\\templates');     // → C:\www\templates\
$template->setSkinPath('D:/projects/templates');  // → D:/projects/templates/

// UNC ceļi (Windows tīkla koplietošana)
$template->setSkinPath('\\\\server\\share\\templates'); // → \\server\share\templates\
```

### Resursu ceļa konfigurācija

Resursu ceļš arī atbalsta gan relatīvos, gan absolūtos ceļus:

```php
// Relatīvie ceļi - automātiski apvienoti ar publisko ceļu
$template->setAssetPath('assets');        // → /var/www/html/myapp/assets/
$template->setAssetPath('static/files');  // → /var/www/html/myapp/static/files/

// Absolūtie ceļi - izmantoti kā ir (Unix/Linux)
$template->setAssetPath('/var/www/cdn');           // → /var/www/cdn/
$template->setAssetPath('/full/path/to/assets');   // → /full/path/to/assets/

// Windows absolūtie ceļi
$template->setAssetPath('C:\\www\\static');       // → C:\www\static\
$template->setAssetPath('D:/projects/assets');    // → D:/projects/assets/

// UNC ceļi (Windows tīkla koplietošana)
$template->setAssetPath('\\\\server\\share\\assets'); // → \\server\share\assets\
```

**Viedā ceļa noteikšana:**

- **Relatīvie ceļi**: Bez sākuma atdalītājiem (`/`, `\`) vai disku burtiem
- **Unix absolūtie**: Sākas ar `/` (piem. `/var/www/assets`)
- **Windows absolūtie**: Sākas ar disku burtu (piem. `C:\www`, `D:/assets`)
- **UNC ceļi**: Sākas ar `\\` (piem. `\\server\share`)

**Kā tas darbojas:**

- Visi ceļi tiek automātiski atrisināti, pamatojoties uz tipu (relatīvs vs absolūts)
- Relatīvie ceļi tiek apvienoti ar publisko ceļu
- `@css` un `@js` izveido minimizētus failus šeit: `{resolvedAssetPath}/css/` vai `{resolvedAssetPath}/js/`
- `@asset` kopē atsevišķus failus uz: `{resolvedAssetPath}/{relativePath}`
- `@assetDir` kopē direktorijus uz: `{resolvedAssetPath}/{relativePath}`
- Viedā kešošana: faili tiek kopēti tikai tad, ja avots ir jaunāks nekā mērķis

## Veidnes direktīvas

### Izkārtojuma mantojums

Izmantojiet izkārtojumus, lai izveidotu kopīgu struktūru:

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

### Resursu pārvaldība

#### CSS faili
```html
<!--@css(/css/styles.css)-->          <!-- Minimizēts un kešots -->
<!--@cssSingle(/css/critical.css)-->  <!-- Viens fails, ne minimizēts -->
```

#### JavaScript faili
CommentTemplate atbalsta dažādas JavaScript ielādes stratēģijas:

```html
<!--@js(/js/script.js)-->             <!-- Minimizēts, ielādēts apakšā -->
<!--@jsAsync(/js/analytics.js)-->     <!-- Minimizēts, ielādēts apakšā ar async -->
<!--@jsDefer(/js/utils.js)-->         <!-- Minimizēts, ielādēts apakšā ar defer -->
<!--@jsTop(/js/critical.js)-->        <!-- Minimizēts, ielādēts galvā -->
<!--@jsTopAsync(/js/tracking.js)-->   <!-- Minimizēts, ielādēts galvā ar async -->
<!--@jsTopDefer(/js/polyfill.js)-->   <!-- Minimizēts, ielādēts galvā ar defer -->
<!--@jsSingle(/js/widget.js)-->       <!-- Viens fails, ne minimizēts -->
<!--@jsSingleAsync(/js/ads.js)-->     <!-- Viens fails, ne minimizēts, async -->
<!--@jsSingleDefer(/js/social.js)-->  <!-- Viens fails, ne minimizēts, defer -->
```

#### Resursu direktīvas CSS/JS failos

CommentTemplate apstrādā arī resursu direktīvas CSS un JavaScript failos kompilācijas laikā:

**CSS piemērs:**
```css
/* Jūsu CSS failos */
/* @font-face {
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

**JavaScript piemērs:**
```javascript
/* Jūsu JS failos */
const fontUrl = '<!--@asset(fonts/custom.woff2)-->';
const imageData = '<!--@base64(images/icon.png)-->';
```

#### Base64 kodēšana
```html
<!--@base64(images/logo.png)-->       <!-- Iekšēji kā data URI -->
```
** Piemērs: **
```html
<!-- Iekšēji mazas attēlus kā data URI ātrākai ielādei -->
<img src="<!--@base64(images/logo.png)-->" alt="Logo">
<div style="background-image: url('<!--@base64(icons/star.svg)-->');">
    Mazs ikona kā fons
</div>
```

#### Resursu kopēšana
```html
<!--@asset(images/photo.jpg)-->       <!-- Kopēt vienu resursu uz publisko direktoriju -->
<!--@assetDir(assets)-->              <!-- Kopēt visu direktoriju uz publisko direktoriju -->
```
** Piemērs: **
```html
<!-- Kopēt un atsauce uz statiskajiem resursiem -->
<img src="<!--@asset(images/hero-banner.jpg)-->" alt="Hero Banner">
<a href="<!--@asset(documents/brochure.pdf)-->" download>Lejupielādēt Brošūru</a>

<!-- Kopēt visu direktoriju (fonts, ikonas utt.) -->
<!--@assetDir(assets/fonts)-->
<!--@assetDir(assets/icons)-->
```

### Veidnes iekļaušana
```html
<!--@import(components/header)-->     <!-- Iekļaut citas veidnes -->
```
** Piemērs: **
```html
<!-- Iekļaut atkārtoti lietojamos komponentus -->
<!--@import(components/header)-->

<main>
    <h1>Sveiki mūsu vietnē</h1>
    <!--@import(components/sidebar)-->
    
    <div class="content">
        <p>Galvenais saturs šeit...</p>
    </div>
</main>

<!--@import(components/footer)-->
```

### Mainīgo apstrāde

#### Pamata mainīgie
```html
<h1>{$title}</h1>
<p>{$description}</p>
```

#### Mainīgo filtri
```html
{$title|upper}                       <!-- Pārvērst uz lielajiem burtiem -->
{$content|lower}                     <!-- Pārvērst uz mazajiem burtiem -->
{$html|striptag}                     <!-- Noņemt HTML tagus -->
{$text|escape}                       <!-- Ekrānot HTML -->
{$multiline|nl2br}                   <!-- Pārvērst jaunas rindas uz <br> -->
{$html|br2nl}                        <!-- Pārvērst <br> tagus uz jaunām rindām -->
{$description|trim}                  <!-- Apgriezt tukšvietas -->
{$subject|title}                     <!-- Pārvērst uz virsraksta gadījumu -->
```

#### Mainīgo komandas
```html
{$title|default=Default Title}       <!-- Iestatīt noklusējuma vērtību -->
{$name|concat= (Admin)}              <!-- Apvienot tekstu -->
```

#### Ķēdīt vairākus filtrus
```html
{$content|striptag|trim|escape}      <!-- Ķēdīt vairākus filtrus -->
```

### Komentāri

Veidnes komentāri tiek pilnībā noņemti no izvades un neparādās gala HTML:

```html
{* Šis ir vienas līnijas veidnes komentārs *}

{* 
   Šis ir daudzrindas
   veidnes komentārs 
   kas aptver vairākas līnijas
*}

<h1>{$title}</h1>
{* Atkļūdošanas komentārs: pārbauda vai title mainīgais darbojas *}
<p>{$content}</p>
```

**Piezīme**: Veidnes komentāri `{* ... *}` atšķiras no HTML komentāriem `<!-- ... -->`. Veidnes komentāri tiek noņemti apstrādes laikā un nekad nesasniedz pārlūkprogrammu.

## Piemēra projekta struktūra

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
│   └── assets/           # Ģenerētie resursi
│       ├── css/
│       └── js/
└── vendor/
```