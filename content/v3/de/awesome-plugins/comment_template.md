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
    // Wo Ihre Template-Dateien gespeichert sind
    $engine->setTemplatesPath(__DIR__ . '/views');
    
    // Wo Ihre öffentlichen Assets serviert werden
    $engine->setPublicPath(__DIR__ . '/public');
    
    // Wo kompilierte Assets gespeichert werden
    $engine->setAssetPath('assets');
    
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
    __DIR__ . '/public',    // publicPath - wo Assets serviert werden
    __DIR__ . '/views',     // skinPath - wo Template-Dateien gespeichert sind  
    'assets',               // assetPath - wo kompilierte Assets gespeichert werden
    '.php'                  // fileExtension - Template-Dateierweiterung
]);

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

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

#### Variablenbefehle
```html
{$content|striptag|trim|escape}      <!-- Mehrere Filter ketten -->
```

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