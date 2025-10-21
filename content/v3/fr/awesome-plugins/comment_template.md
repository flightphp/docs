# CommentTemplate

[CommentTemplate](https://github.com/KnifeLemon/CommentTemplate) est un puissant moteur de templates PHP avec compilation d'actifs, héritage de templates et traitement des variables. Il fournit une façon simple mais flexible de gérer les templates avec une minification CSS/JS intégrée et un cache.

## Fonctionnalités

- **Héritage de Templates** : Utilisez des layouts et incluez d'autres templates
- **Compilation d'Actifs** : Minification et cache automatique CSS/JS
- **Traitement des Variables** : Variables de template avec filtres et commandes
- **Encodage Base64** : Actifs en ligne sous forme d'URI de données
- **Intégration avec le Framework Flight** : Intégration optionnelle avec le framework PHP Flight

## Installation

Installez avec composer.

```bash
composer require knifelemon/comment-template
```

## Configuration de Base

Il existe quelques options de configuration de base pour commencer. Vous pouvez en lire plus à leur sujet dans le [Repo CommentTemplate](https://github.com/KnifeLemon/CommentTemplate).

### Méthode 1 : Utilisation d'une Fonction de Callback

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

$app->register('view', Engine::class, [], function (Engine $engine) use ($app) {
    // Où vos fichiers de templates sont stockés
    $engine->setTemplatesPath(__DIR__ . '/views');
    
    // D'où vos actifs publics seront servis
    $engine->setPublicPath(__DIR__ . '/public');
    
    // Où les actifs compilés seront stockés
    $engine->setAssetPath('assets');
    
    // Extension des fichiers de template
    $engine->setFileExtension('.php');
});

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

### Méthode 2 : Utilisation des Paramètres du Constructeur

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

// __construct(string $publicPath = "", string $skinPath = "", string $assetPath = "", string $fileExtension = "")
$app->register('view', Engine::class, [
    __DIR__ . '/public',    // publicPath - d'où les actifs seront servis
    __DIR__ . '/views',     // skinPath - où les fichiers de templates sont stockés  
    'assets',               // assetPath - où les actifs compilés seront stockés
    '.php'                  // fileExtension - extension des fichiers de template
]);

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

## Directives de Template

### Héritage de Layout

Utilisez des layouts pour créer une structure commune :

**layout/global_layout.php** :
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

**view/page.php** :
```html
<!--@layout(layout/global_layout)-->
<h1>{$title}</h1>
<p>{$content}</p>
```

### Gestion des Actifs

#### Fichiers CSS
```html
<!--@css(/css/styles.css)-->          <!-- Minifié et mis en cache -->
<!--@cssSingle(/css/critical.css)-->  <!-- Fichier unique, non minifié -->
```

#### Fichiers JavaScript
CommentTemplate prend en charge différentes stratégies de chargement JavaScript :

```html
<!--@js(/js/script.js)-->             <!-- Minifié, chargé en bas -->
<!--@jsAsync(/js/analytics.js)-->     <!-- Minifié, chargé en bas avec async -->
<!--@jsDefer(/js/utils.js)-->         <!-- Minifié, chargé en bas avec defer -->
<!--@jsTop(/js/critical.js)-->        <!-- Minifié, chargé dans la tête -->
<!--@jsTopAsync(/js/tracking.js)-->   <!-- Minifié, chargé dans la tête avec async -->
<!--@jsTopDefer(/js/polyfill.js)-->   <!-- Minifié, chargé dans la tête avec defer -->
<!--@jsSingle(/js/widget.js)-->       <!-- Fichier unique, non minifié -->
<!--@jsSingleAsync(/js/ads.js)-->     <!-- Fichier unique, non minifié, async -->
<!--@jsSingleDefer(/js/social.js)-->  <!-- Fichier unique, non minifié, defer -->
```

#### Directives d'Actifs dans les Fichiers CSS/JS

CommentTemplate traite également les directives d'actifs au sein des fichiers CSS et JavaScript lors de la compilation :

**Exemple CSS :**
```css
/* Dans vos fichiers CSS */
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

**Exemple JavaScript :**
```javascript
/* Dans vos fichiers JS */
const fontUrl = '<!--@asset(fonts/custom.woff2)-->';
const imageData = '<!--@base64(images/icon.png)-->';
```

#### Encodage Base64
```html
<!--@base64(images/logo.png)-->       <!-- En ligne sous forme d'URI de données -->
```
** Exemple : **
```html
<!-- Intégrez de petites images sous forme d'URI de données pour un chargement plus rapide -->
<img src="<!--@base64(images/logo.png)-->" alt="Logo">
<div style="background-image: url('<!--@base64(icons/star.svg)-->');">
    Petite icône en arrière-plan
</div>
```

#### Copie d'Actifs
```html
<!--@asset(images/photo.jpg)-->       <!-- Copie un actif unique vers le répertoire public -->
<!--@assetDir(assets)-->              <!-- Copie un répertoire entier vers le répertoire public -->
```
** Exemple : **
```html
<!-- Copiez et référencez des actifs statiques -->
<img src="<!--@asset(images/hero-banner.jpg)-->" alt="Bannière Héroïque">
<a href="<!--@asset(documents/brochure.pdf)-->" download>Télécharger la Brochure</a>

<!-- Copiez un répertoire entier (polices, icônes, etc.) -->
<!--@assetDir(assets/fonts)-->
<!--@assetDir(assets/icons)-->
```

### Inclutions de Templates
```html
<!--@import(components/header)-->     <!-- Incluez d'autres templates -->
```
** Exemple : **
```html
<!-- Incluez des composants réutilisables -->
<!--@import(components/header)-->

<main>
    <h1>Bienvenue sur notre site web</h1>
    <!--@import(components/sidebar)-->
    
    <div class="content">
        <p>Contenu principal ici...</p>
    </div>
</main>

<!--@import(components/footer)-->
```

### Traitement des Variables

#### Variables de Base
```html
<h1>{$title}</h1>
<p>{$description}</p>
```

#### Filtres de Variables
```html
{$title|upper}                       <!-- Convertir en majuscules -->
{$content|lower}                     <!-- Convertir en minuscules -->
{$html|striptag}                     <!-- Supprimer les balises HTML -->
{$text|escape}                       <!-- Échapper le HTML -->
{$multiline|nl2br}                   <!-- Convertir les nouvelles lignes en <br> -->
{$html|br2nl}                        <!-- Convertir les balises <br> en nouvelles lignes -->
{$description|trim}                  <!-- Supprimer les espaces blancs -->
{$subject|title}                     <!-- Convertir en casse titre -->
```

#### Commandes de Variables
```html
{$title|default=Default Title}       <!-- Définir une valeur par défaut -->
{$name|concat= (Admin)}              <!-- Concaténer du texte -->
```

#### Commandes de Variables
```html
{$content|striptag|trim|escape}      <!-- Chaîner plusieurs filtres -->
```

## Structure de Projet Exemple

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
│   └── assets/           # Actifs générés
│       ├── css/
│       └── js/
└── vendor/
```