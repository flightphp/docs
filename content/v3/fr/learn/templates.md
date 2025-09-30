# Vues et modèles HTML

## Aperçu

Flight fournit par défaut une fonctionnalité de base de templating HTML. Le templating est une façon très efficace de déconnecter la logique de votre application de votre couche de présentation.

## Compréhension

Lorsque vous construisez une application, vous aurez probablement du HTML que vous souhaiterez renvoyer à l'utilisateur final. PHP en soi est un langage de templating, mais il est _très_ facile d'intégrer de la logique métier comme des appels à la base de données, des appels API, etc., dans votre fichier HTML, rendant le test et le découplage très difficiles. En poussant les données dans un modèle et en laissant le modèle se rendre lui-même, il devient beaucoup plus facile de découpler et de tester votre code en unités. Vous nous remercierez si vous utilisez des modèles !

## Utilisation de base

Flight vous permet de remplacer le moteur de vue par défaut simplement en enregistrant votre propre classe de vue. Faites défiler vers le bas pour voir des exemples sur l'utilisation de Smarty, Latte, Blade, et plus encore !

### Latte

<span class="badge bg-info">recommandé</span>

Voici comment utiliser le moteur de templates [Latte](https://latte.nette.org/)
pour vos vues.

#### Installation

```bash
composer require latte/latte
```

#### Configuration de base

L'idée principale est de surcharger la méthode `render` pour utiliser Latte au lieu du renderer PHP par défaut.

```php
// surcharge la méthode render pour utiliser latte au lieu du renderer PHP par défaut
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Où latte stocke spécifiquement son cache
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### Utilisation de Latte dans Flight

Maintenant que vous pouvez rendre avec Latte, vous pouvez faire quelque chose comme ceci :

```html
<!-- app/views/home.latte -->
<html>
  <head>
	<title>{$title ? $title . ' - '}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, {$name}!</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.latte', [
		'title' => 'Home Page',
		'name' => $name
	]);
});
```

Lorsque vous visitez `/Bob` dans votre navigateur, la sortie serait :

```html
<html>
  <head>
	<title>Home Page - My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, Bob!</h1>
  </body>
</html>
```

#### Lecture supplémentaire

Un exemple plus complexe d'utilisation de Latte avec des mises en page est montré dans la section [awesome plugins](/awesome-plugins/latte) de cette documentation.

Vous pouvez en apprendre plus sur les capacités complètes de Latte, y compris la traduction et les capacités linguistiques, en lisant la [documentation officielle](https://latte.nette.org/en/).

### Moteur de vue intégré

<span class="badge bg-warning">déprécié</span>

> **Note :** Bien que cela reste la fonctionnalité par défaut et qu'elle fonctionne encore techniquement.

Pour afficher un modèle de vue, appelez la méthode `render` avec le nom 
du fichier de modèle et des données de modèle optionnelles :

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Les données de modèle que vous passez sont automatiquement injectées dans le modèle et peuvent
être référencées comme une variable locale. Les fichiers de modèle sont simplement des fichiers PHP. Si le
contenu du fichier de modèle `hello.php` est :

```php
Hello, <?= $name ?>!
```

La sortie serait :

```text
Hello, Bob!
```

Vous pouvez également définir manuellement des variables de vue en utilisant la méthode set :

```php
Flight::view()->set('name', 'Bob');
```

La variable `name` est maintenant disponible dans toutes vos vues. Vous pouvez donc simplement faire :

```php
Flight::render('hello');
```

Notez que lorsque vous spécifiez le nom du modèle dans la méthode render, vous pouvez
omettre l'extension `.php`.

Par défaut, Flight cherchera un répertoire `views` pour les fichiers de modèle. Vous pouvez
définir un chemin alternatif pour vos modèles en configurant ce qui suit :

```php
Flight::set('flight.views.path', '/path/to/views');
```

#### Mises en page

Il est courant pour les sites web d'avoir un seul fichier de modèle de mise en page avec un contenu
interchangeable. Pour rendre du contenu à utiliser dans une mise en page, vous pouvez passer un paramètre
optionnel à la méthode `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Votre vue aura alors des variables sauvegardées appelées `headerContent` et `bodyContent`.
Vous pouvez ensuite rendre votre mise en page en faisant :

```php
Flight::render('layout', ['title' => 'Home Page']);
```

Si les fichiers de modèle ressemblent à ceci :

`header.php` :

```php
<h1><?= $heading ?></h1>
```

`body.php` :

```php
<div><?= $body ?></div>
```

`layout.php` :

```php
<html>
  <head>
    <title><?= $title ?></title>
  </head>
  <body>
    <?= $headerContent ?>
    <?= $bodyContent ?>
  </body>
</html>
```

La sortie serait :
```html
<html>
  <head>
    <title>Home Page</title>
  </head>
  <body>
    <h1>Hello</h1>
    <div>World</div>
  </body>
</html>
```

### Smarty

Voici comment utiliser le moteur de templates [Smarty](http://www.smarty.net/)
pour vos vues :

```php
// Charge la bibliothèque Smarty
require './Smarty/libs/Smarty.class.php';

// Enregistre Smarty comme classe de vue
// Passe également une fonction de callback pour configurer Smarty au chargement
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Assigne les données de modèle
Flight::view()->assign('name', 'Bob');

// Affiche le modèle
Flight::view()->display('hello.tpl');
```

Pour plus de complétude, vous devriez également surcharger la méthode render par défaut de Flight :

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

Voici comment utiliser le moteur de templates [Blade](https://laravel.com/docs/8.x/blade) pour vos vues :

D'abord, vous devez installer la bibliothèque BladeOne via Composer :

```bash
composer require eftec/bladeone
```

Ensuite, vous pouvez configurer BladeOne comme classe de vue dans Flight :

```php
<?php
// Charge la bibliothèque BladeOne
use eftec\bladeone\BladeOne;

// Enregistre BladeOne comme classe de vue
// Passe également une fonction de callback pour configurer BladeOne au chargement
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Assigne les données de modèle
Flight::view()->share('name', 'Bob');

// Affiche le modèle
echo Flight::view()->run('hello', []);
```

Pour plus de complétude, vous devriez également surcharger la méthode render par défaut de Flight :

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

Dans cet exemple, le fichier de modèle hello.blade.php pourrait ressembler à ceci :

```php
<?php
Hello, {{ $name }}!
```

La sortie serait :

```
Hello, Bob!
```

## Voir aussi
- [Extending](/learn/extending) - Comment surcharger la méthode `render` pour utiliser un moteur de template différent.
- [Routing](/learn/routing) - Comment mapper des routes vers des contrôleurs et rendre des vues.
- [Responses](/learn/responses) - Comment personnaliser les réponses HTTP.
- [Why a Framework?](/learn/why-frameworks) - Comment les modèles s'intègrent dans l'ensemble.

## Dépannage
- Si vous avez une redirection dans votre middleware, mais que votre application ne semble pas rediriger, assurez-vous d'ajouter une instruction `exit;` dans votre middleware.

## Changelog
- v2.0 - Version initiale.