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
    // Répertoire racine (où se trouve index.php) - la racine du document de votre application web
    $engine->setPublicPath(__DIR__);
    
    // Répertoire des fichiers de templates - supporte les chemins relatifs et absolus
    $engine->setSkinPath('views');             // Relatif au chemin public
    
    // Où les actifs compilés seront stockés - supporte les chemins relatifs et absolus
    $engine->setAssetPath('assets');           // Relatif au chemin public
    
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
    __DIR__,                // publicPath - répertoire racine (où se trouve index.php)
    'views',                // skinPath - chemin des templates (supporte relatif/absolu)
    'assets',               // assetPath - chemin des actifs compilés (supporte relatif/absolu)
    '.php'                  // fileExtension - extension des fichiers de template
]);

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

## Configuration des Chemins

CommentTemplate offre une gestion intelligente des chemins pour les chemins relatifs et absolus :

### Chemin Public

Le **Chemin Public** est le répertoire racine de votre application web, généralement où se trouve `index.php`. C'est la racine du document que les serveurs web utilisent pour servir les fichiers.

```php
// Exemple : si votre index.php est à /var/www/html/myapp/index.php
$template->setPublicPath('/var/www/html/myapp');  // Répertoire racine

// Exemple Windows : si votre index.php est à C:\xampp\htdocs\myapp\index.php
$template->setPublicPath('C:\\xampp\\htdocs\\myapp');
```

### Configuration du Chemin des Templates

Le chemin des templates supporte les chemins relatifs et absolus :

```php
$template = new Engine();
$template->setPublicPath('/var/www/html/myapp');  // Répertoire racine (où se trouve index.php)

// Chemins relatifs - automatiquement combinés avec le chemin public
$template->setSkinPath('views');           // → /var/www/html/myapp/views/
$template->setSkinPath('templates/pages'); // → /var/www/html/myapp/templates/pages/

// Chemins absolus - utilisés tel quel (Unix/Linux)
$template->setSkinPath('/var/www/templates');      // → /var/www/templates/
$template->setSkinPath('/full/path/to/templates'); // → /full/path/to/templates/

// Chemins absolus Windows
$template->setSkinPath('C:\\www\\templates');     // → C:\www\templates\
$template->setSkinPath('D:/projects/templates');  // → D:/projects/templates/

// Chemins UNC (partages réseau Windows)
$template->setSkinPath('\\\\server\\share\\templates'); // → \\server\share\templates\
```

### Configuration du Chemin des Actifs

Le chemin des actifs supporte aussi les chemins relatifs et absolus :

```php
// Chemins relatifs - automatiquement combinés avec le chemin public
$template->setAssetPath('assets');        // → /var/www/html/myapp/assets/
$template->setAssetPath('static/files');  // → /var/www/html/myapp/static/files/

// Chemins absolus - utilisés tel quel (Unix/Linux)
$template->setAssetPath('/var/www/cdn');           // → /var/www/cdn/
$template->setAssetPath('/full/path/to/assets');   // → /full/path/to/assets/

// Chemins absolus Windows
$template->setAssetPath('C:\\www\\static');       // → C:\www\static\
$template->setAssetPath('D:/projects/assets');    // → D:/projects/assets/

// Chemins UNC (partages réseau Windows)
$template->setAssetPath('\\\\server\\share\\assets'); // → \\server\share\assets\
```

**Détection Intelligente des Chemins :**

- **Chemins Relatifs** : Pas de séparateurs de début (`/`, `\`) ou de lettres de lecteur
- **Unix Absolu** : Commence par `/` (ex : `/var/www/assets`)
- **Windows Absolu** : Commence par une lettre de lecteur (ex : `C:\www`, `D:/assets`)
- **Chemins UNC** : Commence par `\\` (ex : `\\server\share`)

**Comment ça fonctionne :**

- Tous les chemins sont automatiquement résolus selon le type (relatif vs absolu)
- Les chemins relatifs sont combinés avec le chemin public
- `@css` et `@js` créent des fichiers minifiés dans : `{resolvedAssetPath}/css/` ou `{resolvedAssetPath}/js/`
- `@asset` copie les fichiers uniques vers : `{resolvedAssetPath}/{relativePath}`
- `@assetDir` copie les répertoires vers : `{resolvedAssetPath}/{relativePath}`
- Cache intelligent : les fichiers ne sont copiés que lorsque la source est plus récente que la destination

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

#### Chaîner Plusieurs Filtres
```html
{$content|striptag|trim|escape}      <!-- Chaîner plusieurs filtres -->
```

### Commentaires

Les commentaires de template sont entièrement supprimés de la sortie et n'apparaissent pas dans le HTML final :

```html
{* Ceci est un commentaire de template sur une ligne *}

{* 
   Ceci est un commentaire de template
   multi-lignes qui s'étend sur
   plusieurs lignes
*}

<h1>{$title}</h1>
{* Commentaire de débogage : vérifie si la variable title fonctionne *}
<p>{$content}</p>
```

**Note** : Les commentaires de template `{* ... *}` sont différents des commentaires HTML `<!-- ... -->`. Les commentaires de template sont supprimés lors du traitement et n'atteignent jamais le navigateur.

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