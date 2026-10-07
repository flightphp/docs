# Vues HTML et templates

## Aperçu

Flight fournit par défaut quelques fonctionnalités de templating HTML de base. Le templating est un moyen très efficace de dissocier votre logique applicative de votre couche de présentation. Un moteur dédié (Twig, Latte, etc.) donne également aux [outils de codage IA](/learn/ai) une syntaxe familière et contrainte, réduisant ainsi le risque qu'ils injectent de la logique métier dans votre HTML.

## Compréhension

Lorsque vous créez une application, vous aurez probablement du HTML à renvoyer à l'utilisateur final. PHP est lui-même un langage de templating, mais il est _très_ facile d'intégrer de la logique métier (appels de base de données, appels API, etc.) dans vos fichiers HTML, ce qui rend le test et le découplage très difficiles. En poussant les données dans un template et en laissant le template se générer lui-même, il devient beaucoup plus facile de découpler et de tester unitairement votre code. Vous nous remercierez si vous utilisez des templates !

## Utilisation de base

Flight vous permet de remplacer le moteur de vues par défaut simplement en mappant `render` (ou en enregistrant une classe de vue). Faites défiler pour voir Twig, Latte, Smarty, Blade, et plus encore.

> **Défaut du squelette :** Le [flightphp/skeleton](https://github.com/flightphp/skeleton) officiel utilise **Twig uniquement** dans `app/views/` (`*.twig`). Les contrôleurs appellent `$this->app->render('welcome', $data)` (l'extension est optionnelle). C'est un choix d'application pour les nouveaux projets, et non une exigence du cœur de Flight. Latte et les autres moteurs restent pleinement pris en charge.

### Twig

<span class="badge bg-info">défaut du squelette</span>

[Twig](https://twig.symfony.com/) est un moteur de templates flexible, rapide et sécurisé, utilisé par Symfony et de nombreux autres projets PHP. Les outils de codage IA connaissent particulièrement bien Twig, et il échappe automatiquement la sortie par défaut, ce qui aide à protéger contre les XSS.

#### Installation

```bash
composer require twig/twig
```

(Déjà inclus lorsque vous lancez `composer create-project flightphp/skeleton`.)

#### Configuration de base

Remplacez la méthode `render` pour utiliser Twig à la place du moteur de rendu PHP par défaut :

```php
// remplacez la méthode render pour utiliser Twig à la place du moteur de rendu PHP par défaut
Flight::map('render', function(string $template, array $data): void {
	$loader = new \Twig\Loader\FilesystemLoader(Flight::get('flight.views.path'));
	$twig = new \Twig\Environment($loader, [
		// Emplacement où Twig stocke ses templates compilés
		'cache' => __DIR__ . '/../cache/twig',
		'auto_reload' => true,
	]);

	// Autorise "welcome" ou "welcome.twig"
	if (substr($template, -5) !== '.twig') {
		$template .= '.twig';
	}

	echo $twig->render($template, $data);
});
```

Dans le squelette, ce câblage se trouve dans `app/config/services.php` (environnement Twig partagé, chemin de cache, variables globales comme `base_url` / nonce CSP). Privilégiez l'injection du moteur `Engine` et l'appel à `$app->render()` depuis les contrôleurs pour que le code reste [compatible IA et facile à tester](/learn/ai).

#### Utiliser Twig dans Flight

Maintenant que vous pouvez générer des vues avec Twig, vous pouvez faire quelque chose comme ceci :

```html
{# app/views/home.twig #}
<html>
  <head>
	<title>{% if title %}{{ title }} - {% endif %}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Bonjour, {{ name }} !</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.twig', [
		'title' => 'Page d\'accueil',
		'name' => $name
	]);
});
```

Lorsque vous visitez `/Bob` dans votre navigateur, le résultat serait :

```html
<html>
  <head>
	<title>Page d'accueil - My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Bonjour, Bob !</h1>
  </body>
</html>
```

#### Pour aller plus loin

Un exemple plus complet d'utilisation de Twig avec des layouts est présenté dans la section [plugins géniaux](/awesome-plugins/twig) de cette documentation. Pour des métriques de rendu dans la barre Tracy, consultez le [panneau Twig dans Tracy Extensions](/awesome-plugins/tracy-extensions#twig-panel-optional).

Vous pouvez en apprendre davantage sur toutes les capacités de Twig en lisant la [documentation officielle](https://twig.symfony.com/doc/3.x/).

### Latte

<span class="badge bg-secondary">excellente alternative</span>

[Latte](https://latte.nette.org/) est un moteur complet avec une syntaxe proche de PHP. C'est toujours un excellent choix pour les applications Flight ; le squelette standardise simplement sur Twig pour un défaut partagé unique (particulièrement utile lorsque les outils IA génèrent des templates).

#### Installation

```bash
composer require latte/latte
```

#### Configuration de base

L'idée principale est de remplacer la méthode `render` pour utiliser Latte à la place du moteur de rendu PHP par défaut.

```php
// remplacez la méthode render pour utiliser Latte à la place du moteur de rendu PHP par défaut
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Emplacement où Latte stocke spécifiquement son cache
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### Utiliser Latte dans Flight

Maintenant que vous pouvez générer des vues avec Latte, vous pouvez faire quelque chose comme ceci :

```html
<!-- app/views/home.latte -->
<html>
  <head>
	<title>{$title ? $title . ' - '}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Bonjour, {$name} !</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.latte', [
		'title' => 'Page d\'accueil',
		'name' => $name
	]);
});
```

Lorsque vous visitez `/Bob` dans votre navigateur, le résultat serait :

```html
<html>
  <head>
	<title>Page d'accueil - My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Bonjour, Bob !</h1>
  </body>
</html>
```

#### Pour aller plus loin

Un exemple plus complexe d'utilisation de Latte avec des layouts est présenté dans la section [plugins géniaux](/awesome-plugins/latte) de cette documentation.

Vous pouvez en apprendre davantage sur toutes les capacités de Latte, y compris la traduction et les langues, en lisant la [documentation officielle](https://latte.nette.org/en/).

### Moteur de vues intégré

<span class="badge bg-warning">obsolète</span>

> **Remarque :** Cela reste le fonctionnement par défaut et cela fonctionne toujours techniquement.

Pour afficher un template de vue, appelez la méthode `render` avec le nom du fichier template et des données de template facultatives :

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Les données de template que vous passez sont automatiquement injectées dans le template et peuvent être référencées comme une variable locale. Les fichiers template sont simplement des fichiers PHP. Si le contenu du fichier template `hello.php` est :

```php
Bonjour, <?= $name ?> !
```

Le résultat serait :

```text
Bonjour, Bob !
```

Vous pouvez également définir manuellement des variables de vue en utilisant la méthode `set` :

```php
Flight::view()->set('name', 'Bob');
```

La variable `name` est maintenant disponible dans toutes vos vues. Vous pouvez donc simplement faire :

```php
Flight::render('hello');
```

Notez que lorsque vous spécifiez le nom du template dans la méthode `render`, vous pouvez omettre l'extension `.php`.

Par défaut, Flight recherche un répertoire `views` pour les fichiers template. Vous pouvez définir un chemin alternatif pour vos templates en définissant la configuration suivante :

```php
Flight::set('flight.views.path', '/path/to/views');
```

Par défaut, la classe `View` intégrée de Flight accepte également un chemin de template absolu, ou un nom qui sort de ce répertoire. Pour la plupart des applications, vous devriez restreindre cela :

```php
Flight::set('flight.views.restrict_to_path', true);
```

Cela maintient `render()`, `fetch()` et `exists()` à l'intérieur de `flight.views.path`. Cette option est désactivée par défaut pour des raisons de compatibilité ascendante. Voir [Sécurité](/learn/security#flightviewsrestrict_to_path).

#### Layouts

Il est courant que les sites web aient un fichier de layout unique avec un contenu interchangeable. Pour générer du contenu à utiliser dans un layout, vous pouvez passer un paramètre facultatif à la méthode `render`.

```php
Flight::render('header', ['heading' => 'Bonjour'], 'headerContent');
Flight::render('body', ['body' => 'Monde'], 'bodyContent');
```

Votre vue contiendra alors des variables enregistrées appelées `headerContent` et `bodyContent`. Vous pouvez ensuite générer votre layout en faisant :

```php
Flight::render('layout', ['title' => 'Page d\'accueil']);
```

Si les fichiers template ressemblent à ceci :

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

Le résultat serait :
```html
<html>
  <head>
    <title>Page d'accueil</title>
  </head>
  <body>
    <h1>Bonjour</h1>
    <div>Monde</div>
  </body>
</html>
```

### Smarty

Voici comment utiliser le moteur de templates [Smarty](http://www.smarty.net/) pour vos vues :

```php
// Chargez la bibliothèque Smarty
require './Smarty/libs/Smarty.class.php';

// Enregistrez Smarty en tant que classe de vue
// Passez également une fonction de rappel pour configurer Smarty au chargement
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Assignez les données du template
Flight::view()->assign('name', 'Bob');

// Affichez le template
Flight::view()->display('hello.tpl');
```

Par souci d'exhaustivité, vous devriez également remplacer la méthode render par défaut de Flight :

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

Voici comment utiliser le moteur de templates [Blade](https://laravel.com/docs/8.x/blade) pour vos vues :

Tout d'abord, vous devez installer la bibliothèque BladeOne via Composer :

```bash
composer require eftec/bladeone
```

Ensuite, vous pouvez configurer BladeOne en tant que classe de vue dans Flight :

```php
<?php
// Chargez la bibliothèque BladeOne
use eftec\bladeone\BladeOne;

// Enregistrez BladeOne en tant que classe de vue
// Passez également une fonction de rappel pour configurer BladeOne au chargement
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Assignez les données du template
Flight::view()->share('name', 'Bob');

// Affichez le template
echo Flight::view()->run('hello', []);
```

Par souci d'exhaustivité, vous devriez également remplacer la méthode render par défaut de Flight :

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

Dans cet exemple, le fichier template `hello.blade.php` pourrait ressembler à ceci :

```php
<?php
Bonjour, {{ $name }} !
```

Le résultat serait :

```
Bonjour, Bob !
```

## Voir aussi
- [Installation](/install) - Structure du squelette (`app/views/*.twig`) pour les nouveaux projets.
- [Extension](/learn/extending) - Comment remplacer la méthode `render` pour utiliser un autre moteur de templates.
- [Routage](/learn/routing) - Comment mapper des routes vers des contrôleurs et générer des vues.
- [Réponses](/learn/responses) - Comment personnaliser les réponses HTTP.
- [Sécurité](/learn/security) - Échappement automatique, XSS et `flight.views.restrict_to_path`.
- [IA et expérience développeur](/learn/ai) - Pourquoi un moteur de vues par défaut aide les agents de codage.
- [Pourquoi un framework ?](/learn/why-frameworks) - Comment les templates s'intègrent dans la vue d'ensemble.

## Dépannage
- Si vous avez une redirection dans votre middleware, mais que votre application ne semble pas rediriger, assurez-vous d'ajouter une instruction `exit;` dans votre middleware.
- Si Twig ne trouve pas un template, vérifiez `flight.views.path` et que le fichier existe sous ce chemin avec l'extension attendue (squelette : `app/views/`).

## Journal des modifications
- Docs – Documentation de `flight.views.restrict_to_path` pour les vues PHP natives.
- Docs – Twig documenté comme défaut officiel du squelette ; Latte reste une alternative de premier ordre.
- v2.0 - Première version.