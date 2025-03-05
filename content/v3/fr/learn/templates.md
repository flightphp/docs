# Vues et Modèles HTML

Flight fournit par défaut quelques fonctionnalités de base de modélisation.

Flight vous permet de remplacer le moteur de vue par défaut simplement en enregistrant votre propre classe de vue. Faites défiler pour voir des exemples d'utilisation de Smarty, Latte, Blade, et plus encore !

## Moteur de vue intégré

Pour afficher un modèle de vue, appelez la méthode `render` avec le nom du fichier modèle et des données de modèle facultatives :

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Les données de modèle que vous passez sont automatiquement injectées dans le modèle et peuvent être référencées comme une variable locale. Les fichiers de modèle sont simplement des fichiers PHP. Si le contenu du fichier modèle `hello.php` est :

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

Notez que lors de la spécification du nom du modèle dans la méthode render, vous pouvez omettre l'extension `.php`.

Par défaut, Flight cherchera un répertoire `views` pour les fichiers de modèle. Vous pouvez définir un chemin alternatif pour vos modèles en définissant la configuration suivante :

```php
Flight::set('flight.views.path', '/path/to/views');
```

### Dispositions

Il est courant que les sites Web aient un seul fichier modèle de disposition avec un contenu interchangeant. Pour rendre le contenu à utiliser dans une disposition, vous pouvez passer un paramètre facultatif à la méthode `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Votre vue disposera alors de variables sauvegardées appelées `headerContent` et `bodyContent`. Vous pouvez ensuite rendre votre disposition en faisant :

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

## Smarty

Voici comment vous utiliseriez le moteur de modèle [Smarty](http://www.smarty.net/) pour vos vues :

```php
// Charger la bibliothèque Smarty
require './Smarty/libs/Smarty.class.php';

// Enregistrer Smarty comme classe de vue
// Passer également une fonction de rappel pour configurer Smarty au chargement
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Assigner des données de modèle
Flight::view()->assign('name', 'Bob');

// Afficher le modèle
Flight::view()->display('hello.tpl');
```

Pour être complet, vous devriez également remplacer la méthode render par défaut de Flight :

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

## Latte

Voici comment vous utiliseriez le moteur de modèle [Latte](https://latte.nette.org/) pour vos vues :

```php
// Enregistrer Latte comme classe de vue
// Passer également une fonction de rappel pour configurer Latte au chargement
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // C'est ici que Latte mettra en cache vos modèles pour accélérer les choses
  // Une chose intéressante à propos de Latte est qu'il actualise automatiquement votre
  // cache lorsque vous apportez des modifications à vos modèles !
  $latte->setTempDirectory(__DIR__ . '/../cache/');

  // Indiquer à Latte où se trouvera le répertoire racine de vos vues.
  $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Et enveloppez le pour que vous puissiez utiliser Flight::render() correctement
Flight::map('render', function(string $template, array $data): void {
  // C'est comme $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```

## Blade

Voici comment vous utiliseriez le moteur de modèle [Blade](https://laravel.com/docs/8.x/blade) pour vos vues :

Tout d'abord, vous devez installer la bibliothèque BladeOne via Composer :

```bash
composer require eftec/bladeone
```

Ensuite, vous pouvez configurer BladeOne comme classe de vue dans Flight :

```php
<?php
// Charger la bibliothèque BladeOne
use eftec\bladeone\BladeOne;

// Enregistrer BladeOne comme classe de vue
// Passer également une fonction de rappel pour configurer BladeOne au chargement
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Assigner des données de modèle
Flight::view()->share('name', 'Bob');

// Afficher le modèle
echo Flight::view()->run('hello', []);
```

Pour être complet, vous devriez également remplacer la méthode render par défaut de Flight :

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

Dans cet exemple, le fichier modèle hello.blade.php pourrait ressembler à ceci :

```php
<?php
Hello, {{ $name }}!
```

La sortie serait :

```
Hello, Bob!
```

En suivant ces étapes, vous pouvez intégrer le moteur de modèle Blade avec Flight et l'utiliser pour rendre vos vues.