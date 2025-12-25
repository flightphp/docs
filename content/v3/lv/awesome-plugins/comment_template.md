# CommentTemplate

[CommentTemplate](https://github.com/KnifeLemon/CommentTemplate) ir jaudīgs PHP veidņu dzinējs ar resursu kompilāciju, veidņu mantojumu un mainīgo apstrādi. Tas nodrošina vienkāršu, bet elastīgu veidu, kā pārvaldīt veidnes ar iebūvētu CSS/JS minimizāciju un kešošanu.

## Funkcijas

- **Veidņu mantojums**: Izmantojiet izkārtojumus un iekļaujiet citas veidnes
- **Resursu kompilācija**: Automātiska CSS/JS minimizācija un kešošana
- **Mainīgo apstrāde**: Veidņu mainīgie ar filtrēšanu un komandām
- **Base64 kodēšana**: Iekšējie resursi kā datu URI
- **Flight Framework integrācija**: Neobligātā integrācija ar Flight PHP framework

## Instalēšana

Instalējiet ar composer.

```bash
composer require knifelemon/comment-template
```

## Pamata konfigurācija

Ir dažas pamata konfigurācijas opcijas, lai sāktu. Vairāk par tām var lasīt [CommentTemplate Repo](https://github.com/KnifeLemon/CommentTemplate).

### 1. metode: Izmantojot atgriezeniskās saites funkciju

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

$app->register('view', Engine::class, [], function (Engine $engine) use ($app) {
    // Saknes direktorija (kur ir index.php) - jūsu tīmekļa lietojumprogrammas dokumentu sakne
    $engine->setPublicPath(__DIR__);
    
    // Veidņu failu direktorija - atbalsta gan relatīvās, gan absolūtās ceļus
    $engine->setSkinPath('views');             // Relatīvi pret publisko ceļu
    
    // Kur tiks glabāti kompilētie resursi - atbalsta gan relatīvās, gan absolūtās ceļus
    $engine->setAssetPath('assets');           // Relatīvi pret publisko ceļu
    
    // Veidnes faila paplašinājums
    $engine->setFileExtension('.php');
});

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

### 2. metode: Izmantojot konstruktoru parametrus

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

// __construct(string $publicPath = "", string $skinPath = "", string $assetPath = "", string $fileExtension = "")
$app->register('view', Engine::class, [
    __DIR__,                // publicPath - saknes direktorija (kur ir index.php)
    'views',                // skinPath - veidņu ceļš (atbalsta relatīvos/absolūtos)
    'assets',               // assetPath - kompilēto resursu ceļš (atbalsta relatīvos/absolūtos)
    '.php'                  // fileExtension - veidnes faila paplašinājums
]);

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

## Ceļu konfigurācija

CommentTemplate nodrošina inteliģentu ceļu apstrādi gan relatīvajiem, gan absolūtajiem ceļiem:

### Publiskais ceļš

**Publiskais ceļš** ir jūsu tīmekļa lietojumprogrammas saknes direktorija, parasti tur, kur atrodas `index.php`. Tas ir dokumentu saknes ceļš, no kura tīmekļa serveri pasniedz failus.

```php
// Piemērs: ja jūsu index.php ir /var/www/html/myapp/index.php
$template->setPublicPath('/var/www/html/myapp');  // Saknes direktorija

// Windows piemērs: ja jūsu index.php ir C:\xampp\htdocs\myapp\index.php
$template->setPublicPath('C:\\xampp\\htdocs\\myapp');
```

### Veidņu ceļa konfigurācija

Veidņu ceļš atbalsta gan relatīvos, gan absolūtos ceļus:

```php
$template = new Engine();
$template->setPublicPath('/var/www/html/myapp');  // Saknes direktorija (kur ir index.php)

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

**Inteliģenta ceļu noteikšana:**

- **Relatīvie ceļi**: Nav vadītājsimbolu (`/`, `\`) vai disketes burtiem
- **Unix absolūtie**: Sākas ar `/` (piem., `/var/www/assets`)
- **Windows absolūtie**: Sākas ar diska burtu (piem., `C:\www`, `D:/assets`)
- **UNC ceļi**: Sākas ar `\\` (piem., `\\server\share`)

**Kā tas darbojas:**

- Visi ceļi tiek automātiski atrisināti, balstoties uz tipu (relatīvs pret absolūto)
- Relatīvie ceļi tiek apvienoti ar publisko ceļu
- `@css` un `@js` izveido minimizētus failus: `{resolvedAssetPath}/css/` vai `{resolvedAssetPath}/js/`
- `@asset` kopē atsevišķus failus uz: `{resolvedAssetPath}/{relativePath}`
- `@assetDir` kopē direktorijas uz: `{resolvedAssetPath}/{relativePath}`
- Inteliģenta kešošana: faili tiek kopēti tikai tad, kad avots ir jaunāks par mērķi

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
<!--@cssSingle(/css/critical.css)-->  <!-- Atsevišķs fails, ne minimizēts -->
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
<!--@jsSingle(/js/widget.js)-->       <!-- Atsevišķs fails, ne minimizēts -->
<!--@jsSingleAsync(/js/ads.js)-->     <!-- Atsevišķs fails, ne minimizēts, async -->
<!--@jsSingleDefer(/js/social.js)-->  <!-- Atsevišķs fails, ne minimizēts, defer -->
```

#### Resursu direktīvas CSS/JS failos

CommentTemplate arī apstrādā resursu direktīvas CSS un JavaScript failos kompilācijas laikā:

**CSS piemērs:**
```css
/* Jūsu CSS failos */
/* Fontu definīcijas */
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

**JavaScript piemērs:**
```javascript
/* Jūsu JS failos */
const fontUrl = '<!--@asset(fonts/custom.woff2)-->';
const imageData = '<!--@base64(images/icon.png)-->';
```

#### Base64 kodēšana
```html
<!--@base64(images/logo.png)-->       <!-- Iekšēji kā datu URI -->
```
** Piemērs: **
```html
<!-- Iekšēji mazas bildes kā datu URI ātrākai ielādei -->
<img src="<!--@base64(images/logo.png)-->" alt="Logo">
<div style="background-image: url('<!--@base64(icons/star.svg)-->');">
    Mazs ikona kā fons
</div>
```

#### Resursu kopēšana
```html
<!--@asset(images/photo.jpg)-->       <!-- Kopē atsevišķu resursu uz publisko direktoriju -->
<!--@assetDir(assets)-->              <!-- Kopē visu direktoriju uz publisko direktoriju -->
```
** Piemērs: **
```html
<!-- Kopē un atsaucas uz statiskajiem resursiem -->
<img src="<!--@asset(images/hero-banner.jpg)-->" alt="Hero Banner">
<a href="<!--@asset(documents/brochure.pdf)-->" download>Lejupielādēt Brošūru</a>

<!-- Kopē visu direktoriju (fonti, ikonas utt.) -->
<!--@assetDir(assets/fonts)-->
<!--@assetDir(assets/icons)-->
```

### Veidnes iekļaušana
```html
<!--@import(components/header)-->     <!-- Iekļauj citas veidnes -->
```
** Piemērs: **
```html
<!-- Iekļauj atkārtoti izmantojamas sastāvdaļas -->
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
{$title|upper}                       <!-- Pārvērš uz lielajiem burtiem -->
{$content|lower}                     <!-- Pārvērš uz mazajiem burtiem -->
{$html|striptag}                     <!-- Noņem HTML atzīmes -->
{$text|escape}                       <!-- Ekrēno HTML -->
{$multiline|nl2br}                   <!-- Pārvērš jaunas rindas uz <br> -->
{$html|br2nl}                        <!-- Pārvērš <br> atzīmes uz jaunām rindām -->
{$description|trim}                  <!-- Apgriež tukšumus -->
{$subject|title}                     <!-- Pārvērš uz virsraksta gadījumu -->
```

#### Mainīgo komandas
```html
{$title|default=Default Title}       <!-- Iestata noklusējuma vērtību -->
{$name|concat= (Admin)}              <!-- Apvieno tekstu -->
```

#### Mainīgo komandas
```html
{$content|striptag|trim|escape}      <!-- Ķēžu vairākus filtrus -->
```

### Komentāri

Veidnes komentāri tiek pilnībā noņemti no izvades un neparādīsies galīgajā HTML:

```html
{* Tas ir vienrindas veidnes komentārs *}

{* 
   Tas ir vairākrindu 
   veidnes komentārs 
   kas aptver vairākas rindas
*}

<h1>{$title}</h1>
{* Debug komentārs: pārbauda, vai title mainīgais darbojas *}
<p>{$content}</p>
```

**Piezīme**: Veidnes komentāri `{* ... *}` atšķiras no HTML komentāriem `<!-- ... -->`. Veidnes komentāri tiek noņemti apstrādes laikā un nekad nenonāk pārlūkprogrammā.

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