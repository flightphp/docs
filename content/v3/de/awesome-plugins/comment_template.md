# CommentTemplate

[CommentTemplate](https://github.com/KnifeLemon/CommentTemplate) ist ein leistungsstarkes PHP-Template-Engine mit Asset-Kompilierung, Template-Vererbung und Variablenverarbeitung. Es bietet eine einfache, aber flexible Möglichkeit, Templates zu verwalten, mit integrierter CSS/JS-Minifizierung und Caching.

## Features

- **Template-Vererbung**: Verwenden von Layouts und Einbinden anderer Templates
- **Asset-Kompilierung**: Automatische CSS/JS-Minifizierung und Caching
- **Variablenverarbeitung**: Template-Variablen mit Filtern und Befehlen
- **Base64-Kodierung**: Inline-Assets als Data-URIs
- **Flight-Framework-Integration**: Optionale Integration mit dem Flight-PHP-Framework

## Installation

Installieren Sie es mit Composer.

```bash
composer require knifelemon/comment-template
```

## Grundlegende Konfiguration

Es gibt einige grundlegende Konfigurationsoptionen, um zu starten. Sie können mehr darüber in der [CommentTemplate-Repo](https://github.com/KnifeLemon/CommentTemplate) lesen.

### Methode 1: Verwendung einer Callback-Funktion

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

$app->register('view', Engine::class, [], function (Engine $engine) use ($app) {
    // Wurzelverzeichnis (wo sich index.php befindet) - das Document Root Ihrer Webanwendung
    $engine->setPublicPath(__DIR__);
    
    // Template-Dateiverzeichnis - unterstützt relative und absolute Pfade
    $engine->setSkinPath('views');             // Relativ zum öffentlichen Pfad
    
    // Wo kompilierte Assets gespeichert werden - unterstützt relative und absolute Pfade
    $engine->setAssetPath('assets');           // Relativ zum öffentlichen Pfad
    
    // Template-Dateierweiterung
    $engine->setFileExtension('.php');
});

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

### Methode 2: Verwendung von Konstruktor-Parametern

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

// __construct(string $publicPath = "", string $skinPath = "", string $assetPath = "", string $fileExtension = "")
$app->register('view', Engine::class, [
    __DIR__,                // publicPath - Wurzelverzeichnis (wo index.php ist)
    'views',                // skinPath - Template-Pfad (unterstützt relativ/absolut)
    'assets',               // assetPath - kompilierter Asset-Pfad (unterstützt relativ/absolut)
    '.php'                  // fileExtension - Template-Dateierweiterung
]);

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

## Pfad-Konfiguration

CommentTemplate bietet intelligente Pfad-Behandlung für relative und absolute Pfade:

### Öffentlicher Pfad

Der **Öffentliche Pfad** ist das Wurzelverzeichnis Ihrer Webanwendung, typischerweise wo sich `index.php` befindet. Dies ist das Document Root, von dem Webserver Dateien bereitstellen.

```php
// Beispiel: wenn Ihre index.php bei /var/www/html/myapp/index.php liegt
$template->setPublicPath('/var/www/html/myapp');  // Wurzelverzeichnis

// Windows-Beispiel: wenn Ihre index.php bei C:\xampp\htdocs\myapp\index.php liegt
$template->setPublicPath('C:\\xampp\\htdocs\\myapp');
```

### Template-Pfad-Konfiguration

Template-Pfad unterstützt sowohl relative als auch absolute Pfade:

```php
$template = new Engine();
$template->setPublicPath('/var/www/html/myapp');  // Wurzelverzeichnis (wo index.php ist)

// Relative Pfade - automatisch mit öffentlichem Pfad kombiniert
$template->setSkinPath('views');           // → /var/www/html/myapp/views/
$template->setSkinPath('templates/pages'); // → /var/www/html/myapp/templates/pages/

// Absolute Pfade - wie sie sind verwendet (Unix/Linux)
$template->setSkinPath('/var/www/templates');      // → /var/www/templates/
$template->setSkinPath('/full/path/to/templates'); // → /full/path/to/templates/

// Windows absolute Pfade
$template->setSkinPath('C:\\www\\templates');     // → C:\www\templates\
$template->setSkinPath('D:/projects/templates');  // → D:/projects/templates/

// UNC-Pfade (Windows-Netzwerkfreigaben)
$template->setSkinPath('\\\\server\\share\\templates'); // → \\server\share\templates\
```

### Asset-Pfad-Konfiguration

Asset-Pfad unterstützt auch sowohl relative als auch absolute Pfade:

```php
// Relative Pfade - automatisch mit öffentlichem Pfad kombiniert
$template->setAssetPath('assets');        // → /var/www/html/myapp/assets/
$template->setAssetPath('static/files');  // → /var/www/html/myapp/static/files/

// Absolute Pfade - wie sie sind verwendet (Unix/Linux)
$template->setAssetPath('/var/www/cdn');           // → /var/www/cdn/
$template->setAssetPath('/full/path/to/assets');   // → /full/path/to/assets/

// Windows absolute Pfade
$template->setAssetPath('C:\\www\\static');       // → C:\www\static\
$template->setAssetPath('D:/projects/assets');    // → D:/projects/assets/

// UNC-Pfade (Windows-Netzwerkfreigaben)
$template->setAssetPath('\\\\server\\share\\assets'); // → \\server\share\assets\
```

**Intelligente Pfad-Erkennung:**

- **Relative Pfade**: Keine führenden Trennzeichen (`/`, `\`) oder Laufwerksbuchstaben
- **Unix Absolut**: Beginnt mit `/` (z.B. `/var/www/assets`)
- **Windows Absolut**: Beginnt mit Laufwerksbuchstabe (z.B. `C:\www`, `D:/assets`)
- **UNC-Pfade**: Beginnt mit `\\` (z.B. `\\server\share`)

**Wie es funktioniert:**

- Alle Pfade werden automatisch basierend auf dem Typ aufgelöst (relativ vs absolut)
- Relative Pfade werden mit dem öffentlichen Pfad kombiniert
- `@css` und `@js` erstellen minifizierte Dateien in: `{resolvedAssetPath}/css/` oder `{resolvedAssetPath}/js/`
- `@asset` kopiert einzelne Dateien nach: `{resolvedAssetPath}/{relativePath}`
- `@assetDir` kopiert Verzeichnisse nach: `{resolvedAssetPath}/{relativePath}`
- Intelligente Zwischenspeicherung: Dateien werden nur kopiert, wenn die Quelle neuer ist als das Ziel

## Template-Direktiven

### Layout-Vererbung

Verwenden Sie Layouts, um eine gemeinsame Struktur zu erstellen:

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

### Asset-Verwaltung

#### CSS-Dateien
```html
<!--@css(/css/styles.css)-->          <!-- Minifiziert und gecacht -->
<!--@cssSingle(/css/critical.css)-->  <!-- Einzelne Datei, nicht minifiziert -->
```

#### JavaScript-Dateien
CommentTemplate unterstützt verschiedene JavaScript-Lade-Strategien:

```html
<!--@js(/js/script.js)-->             <!-- Minifiziert, geladen am Ende -->
<!--@jsAsync(/js/analytics.js)-->     <!-- Minifiziert, geladen am Ende mit async -->
<!--@jsDefer(/js/utils.js)-->         <!-- Minifiziert, geladen am Ende mit defer -->
<!--@jsTop(/js/critical.js)-->        <!-- Minifiziert, geladen im Head -->
<!--@jsTopAsync(/js/tracking.js)-->   <!-- Minifiziert, geladen im Head mit async -->
<!--@jsTopDefer(/js/polyfill.js)-->   <!-- Minifiziert, geladen im Head mit defer -->
<!--@jsSingle(/js/widget.js)-->       <!-- Einzelne Datei, nicht minifiziert -->
<!--@jsSingleAsync(/js/ads.js)-->     <!-- Einzelne Datei, nicht minifiziert, async -->
<!--@jsSingleDefer(/js/social.js)-->  <!-- Einzelne Datei, nicht minifiziert, defer -->
```

#### Asset-Direktiven in CSS/JS-Dateien

CommentTemplate verarbeitet auch Asset-Direktiven innerhalb von CSS- und JavaScript-Dateien während der Kompilierung:

**CSS-Beispiel:**
```css
/* In Ihren CSS-Dateien */
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

**JavaScript-Beispiel:**
```javascript
/* In Ihren JS-Dateien */
const fontUrl = '<!--@asset(fonts/custom.woff2)-->';
const imageData = '<!--@base64(images/icon.png)-->';
```

#### Base64-Kodierung
```html
<!--@base64(images/logo.png)-->       <!-- Inline als Data-URI -->
```
**Beispiel:**
```html
<!-- Kleine Bilder inline als Data-URIs für schnelleres Laden -->
<img src="<!--@base64(images/logo.png)-->" alt="Logo">
<div style="background-image: url('<!--@base64(icons/star.svg)-->');">
    Kleines Icon als Hintergrund
</div>
```

#### Asset-Kopieren
```html
<!--@asset(images/photo.jpg)-->       <!-- Einzelnes Asset in das öffentliche Verzeichnis kopieren -->
<!--@assetDir(assets)-->              <!-- Ganzes Verzeichnis in das öffentliche Verzeichnis kopieren -->
```
**Beispiel:**
```html
<!-- Statische Assets kopieren und referenzieren -->
<img src="<!--@asset(images/hero-banner.jpg)-->" alt="Hero Banner">
<a href="<!--@asset(documents/brochure.pdf)-->" download>Download Brochure</a>

<!-- Ganzes Verzeichnis kopieren (Fonts, Icons usw.) -->
<!--@assetDir(assets/fonts)-->
<!--@assetDir(assets/icons)-->
```

### Template-Einbindungen
```html
<!--@import(components/header)-->     <!-- Andere Templates einbinden -->
```
**Beispiel:**
```html
<!-- Wiederverwendbare Komponenten einbinden -->
<!--@import(components/header)-->

<main>
    <h1>Willkommen auf unserer Website</h1>
    <!--@import(components/sidebar)-->
    
    <div class="content">
        <p>Hauptinhalt hier...</p>
    </div>
</main>

<!--@import(components/footer)-->
```

### Variablenverarbeitung

#### Grundlegende Variablen
```html
<h1>{$title}</h1>
<p>{$description}</p>
```

#### Variablenfilter
```html
{$title|upper}                       <!-- In Großbuchstaben umwandeln -->
{$content|lower}                     <!-- In Kleinbuchstaben umwandeln -->
{$html|striptag}                     <!-- HTML-Tags entfernen -->
{$text|escape}                       <!-- HTML escapen -->
{$multiline|nl2br}                   <!-- Zeilenumbrüche in <br> umwandeln -->
{$html|br2nl}                        <!-- <br>-Tags in Zeilenumbrüche umwandeln -->
{$description|trim}                  <!-- Leerzeichen kürzen -->
{$subject|title}                     <!-- In Titel-Großschreibung umwandeln -->
```

#### Variablenbefehle
```html
{$title|default=Default Title}       <!-- Standardwert setzen -->
{$name|concat= (Admin)}              <!-- Text anhängen -->
```

#### Mehrere Filter verketten
```html
{$content|striptag|trim|escape}      <!-- Mehrere Filter ketten -->
```

### Kommentare

Template-Kommentare werden vollständig aus der Ausgabe entfernt und erscheinen nicht im finalen HTML:

```html
{* Dies ist ein einzeiliger Template-Kommentar *}

{* 
   Dies ist ein mehrzeiliger
   Template-Kommentar 
   über mehrere Zeilen
*}

<h1>{$title}</h1>
{* Debug-Kommentar: Prüfen ob title-Variable funktioniert *}
<p>{$content}</p>
```

**Hinweis**: Template-Kommentare `{* ... *}` unterscheiden sich von HTML-Kommentaren `<!-- ... -->`. Template-Kommentare werden während der Verarbeitung entfernt und erreichen nie den Browser.

## Beispiel-Projektstruktur

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
│   └── assets/           # Generierte Assets
│       ├── css/
│       └── js/
└── vendor/
```