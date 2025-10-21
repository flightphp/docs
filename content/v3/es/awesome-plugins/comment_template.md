# CommentTemplate

[CommentTemplate](https://github.com/KnifeLemon/CommentTemplate) es un potente motor de plantillas PHP con compilación de activos, herencia de plantillas y procesamiento de variables. Proporciona una manera simple pero flexible de gestionar plantillas con minificación y caché integrados de CSS/JS.

## Características

- **Herencia de Plantillas**: Usa diseños y incluye otras plantillas
- **Compilación de Activos**: Minificación y caché automáticos de CSS/JS
- **Procesamiento de Variables**: Variables de plantilla con filtros y comandos
- **Codificación Base64**: Activos en línea como URIs de datos
- **Integración con Flight Framework**: Integración opcional con el framework PHP Flight

## Instalación

Instala con composer.

```bash
composer require knifelemon/comment-template
```

## Configuración Básica

Hay algunas opciones de configuración básicas para comenzar. Puedes leer más sobre ellas en el [Repositorio de CommentTemplate](https://github.com/KnifeLemon/CommentTemplate).

### Método 1: Usando Función de Retorno de Llamada

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

$app->register('view', Engine::class, [], function (Engine $engine) use ($app) {
    // Donde se almacenan tus archivos de plantilla
    $engine->setTemplatesPath(__DIR__ . '/views');
    
    // Donde se servirán tus activos públicos
    $engine->setPublicPath(__DIR__ . '/public');
    
    // Donde se almacenarán los activos compilados
    $engine->setAssetPath('assets');
    
    // Extensión de archivo de plantilla
    $engine->setFileExtension('.php');
});

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

### Método 2: Usando Parámetros del Constructor

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

// __construct(string $publicPath = "", string $skinPath = "", string $assetPath = "", string $fileExtension = "")
$app->register('view', Engine::class, [
    __DIR__ . '/public',    // publicPath - donde se servirán los activos
    __DIR__ . '/views',     // skinPath - donde se almacenan los archivos de plantilla  
    'assets',               // assetPath - donde se almacenarán los activos compilados
    '.php'                  // fileExtension - extensión de archivo de plantilla
]);

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

## Directivas de Plantilla

### Herencia de Diseño

Usa diseños para crear una estructura común:

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

### Gestión de Activos

#### Archivos CSS
```html
<!--@css(/css/styles.css)-->          <!-- Minificado y en caché -->
<!--@cssSingle(/css/critical.css)-->  <!-- Archivo único, no minificado -->
```

#### Archivos JavaScript
CommentTemplate soporta diferentes estrategias de carga de JavaScript:

```html
<!--@js(/js/script.js)-->             <!-- Minificado, cargado al final -->
<!--@jsAsync(/js/analytics.js)-->     <!-- Minificado, cargado al final con async -->
<!--@jsDefer(/js/utils.js)-->         <!-- Minificado, cargado al final con defer -->
<!--@jsTop(/js/critical.js)-->        <!-- Minificado, cargado en head -->
<!--@jsTopAsync(/js/tracking.js)-->   <!-- Minificado, cargado en head con async -->
<!--@jsTopDefer(/js/polyfill.js)-->   <!-- Minificado, cargado en head con defer -->
<!--@jsSingle(/js/widget.js)-->       <!-- Archivo único, no minificado -->
<!--@jsSingleAsync(/js/ads.js)-->     <!-- Archivo único, no minificado, async -->
<!--@jsSingleDefer(/js/social.js)-->  <!-- Archivo único, no minificado, defer -->
```

#### Directivas de Activos en Archivos CSS/JS

CommentTemplate también procesa directivas de activos dentro de archivos CSS y JavaScript durante la compilación:

**Ejemplo de CSS:**
```css
/* En tus archivos CSS */
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

**Ejemplo de JavaScript:**
```javascript
/* En tus archivos JS */
const fontUrl = '<!--@asset(fonts/custom.woff2)-->';
const imageData = '<!--@base64(images/icon.png)-->';
```

#### Codificación Base64
```html
<!--@base64(images/logo.png)-->       <!-- En línea como URI de datos -->
```
** Ejemplo: **
```html
<!-- Incorpora imágenes pequeñas como URIs de datos para una carga más rápida -->
<img src="<!--@base64(images/logo.png)-->" alt="Logo">
<div style="background-image: url('<!--@base64(icons/star.svg)-->');">
    Icono pequeño como fondo
</div>
```

#### Copia de Activos
```html
<!--@asset(images/photo.jpg)-->       <!-- Copia un activo único al directorio público -->
<!--@assetDir(assets)-->              <!-- Copia todo el directorio al directorio público -->
```
** Ejemplo: **
```html
<!-- Copia y referencia activos estáticos -->
<img src="<!--@asset(images/hero-banner.jpg)-->" alt="Hero Banner">
<a href="<!--@asset(documents/brochure.pdf)-->" download>Descargar Folleto</a>

<!-- Copia todo el directorio (fuentes, iconos, etc.) -->
<!--@assetDir(assets/fonts)-->
<!--@assetDir(assets/icons)-->
```

### Inclusiones de Plantilla
```html
<!--@import(components/header)-->     <!-- Incluye otras plantillas -->
```
** Ejemplo: **
```html
<!-- Incluye componentes reutilizables -->
<!--@import(components/header)-->

<main>
    <h1>Bienvenido a nuestro sitio web</h1>
    <!--@import(components/sidebar)-->
    
    <div class="content">
        <p>Contenido principal aquí...</p>
    </div>
</main>

<!--@import(components/footer)-->
```

### Procesamiento de Variables

#### Variables Básicas
```html
<h1>{$title}</h1>
<p>{$description}</p>
```

#### Filtros de Variables
```html
{$title|upper}                       <!-- Convertir a mayúsculas -->
{$content|lower}                     <!-- Convertir a minúsculas -->
{$html|striptag}                     <!-- Eliminar etiquetas HTML -->
{$text|escape}                       <!-- Escapar HTML -->
{$multiline|nl2br}                   <!-- Convertir saltos de línea a <br> -->
{$html|br2nl}                        <!-- Convertir etiquetas <br> a saltos de línea -->
{$description|trim}                  <!-- Recortar espacios en blanco -->
{$subject|title}                     <!-- Convertir a título -->
```

#### Comandos de Variables
```html
{$title|default=Default Title}       <!-- Establecer valor predeterminado -->
{$name|concat= (Admin)}              <!-- Concatenar texto -->
```

#### Comandos de Variables
```html
{$content|striptag|trim|escape}      <!-- Encadenar múltiples filtros -->
```

## Estructura de Proyecto de Ejemplo

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
│   └── assets/           # Activos generados
│       ├── css/
│       └── js/
└── vendor/
```