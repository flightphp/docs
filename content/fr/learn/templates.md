# Vues HTML et Modèles

Flight fournit par défaut une certaine fonctionnalité de templating de base.

Si vous avez besoin de besoins de templating plus complexes, consultez les exemples Smarty et Latte dans la section [Vues personnalisées](#vues-personnalisees).

## Moteur de vue par défaut

Pour afficher un modèle de vue, appelez la méthode `render` avec le nom du fichier modèle et des données de modèle optionnelles :

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Les données de modèle que vous transmettez sont automatiquement injectées dans le modèle et peuvent être référencées comme une variable locale. Les fichiers de modèle sont simplement des fichiers PHP. Si le contenu du fichier modèle `hello.php` est le suivant :

```php
Bonjour, <?= $name ?>!
```

La sortie serait :

```
Bonjour, Bob!
```

Vous pouvez également définir manuellement des variables de vue en utilisant la méthode `set` :

```php
Flight::view()->set('name', 'Bob');
```

La variable `name` est maintenant disponible dans toutes vos vues. Vous pouvez simplement faire :

```php
Flight::render('hello');
```

Notez que lors de la spécification du nom du modèle dans la méthode render, vous pouvez omettre l'extension `.php`.

Par défaut, Flight recherchera un répertoire `views` pour les fichiers de modèle. Vous pouvez définir un chemin alternatif pour vos modèles en définissant la configuration suivante :

```php
Flight::set('flight.views.path', '/chemin/vers/vues');
```

### Mises en page

Il est courant que les sites web aient un seul fichier de modèle de mise en page avec un contenu changeant. Pour afficher le contenu à utiliser dans une mise en page, vous pouvez transmettre un paramètre optionnel à la méthode `render`.

```php
Flight::render('header', ['heading' => 'Bonjour'], 'headerContent');
Flight::render('body', ['body' => 'Monde'], 'bodyContent');
```

Votre vue aura alors des variables enregistrées appelées `headerContent` et `bodyContent`.
Vous pouvez ensuite afficher votre mise en page en faisant :

```php
Flight::render('layout', ['title' => 'Page d'accueil']);
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
    <title>Page d'accueil</title>
  </head>
  <body>
    <h1>Bonjour</h1>
    <div>Monde</div>
  </body>
</html>
```

## Moteurs de vue personnalisés

Flight vous permet de remplacer simplement le moteur de vue par défaut en enregistrant votre propre classe de vue.

### Smarty

Voici comment vous utiliseriez le [Smarty](http://www.smarty.net/) moteur de template pour vos vues :

```php
// Charger la bibliothèque Smarty
require './Smarty/libs/Smarty.class.php';

// Enregistrer Smarty comme classe de vue
// Passez également une fonction de rappel pour configurer Smarty lors du chargement
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
});

// Assigner des données de modèle
Flight::view()->assign('name', 'Bob');

// Afficher le modèle
Flight::view()->display('hello.tpl');
```

Pour plus d'exhaustivité, vous devriez également remplacer la méthode render par défaut de Flight :

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

Voici comment vous utiliseriez le [Latte](https://latte.nette.org/) moteur de template pour vos vues :

```php

// Enregistrer Latte comme classe de vue
// Passez également une fonction de rappel pour configurer Latte lors du chargement
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // C'est ici que Latte mettra en cache vos modèles pour accélérer les choses
	// Une chose intéressante à propos de Latte est qu'il rafraîchit automatiquement votre
	// mémoire cache lorsque vous apportez des modifications à vos modèles !
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Indiquez à Latte où se trouvera le répertoire racine de vos vues.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Et enveloppez-le pour pouvoir utiliser correctement Flight::render()
Flight::map('render', function(string $template, array $data): void {
  // C'est comme $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```