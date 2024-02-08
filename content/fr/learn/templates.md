# Vues

Flight fournit par défaut certaines fonctionnalités de base pour le templating.

Si vous avez des besoins de templating plus complexes, consultez les exemples de Smarty et Latte dans la section [Vues Personnalisées](#custom-views).

Pour afficher un modèle de vue, appelez la méthode `render` avec le nom
du fichier de modèle et des données de modèle optionnelles :

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Les données de modèle que vous transmettez sont automatiquement injectées dans le modèle et peuvent
être référencées comme une variable locale. Les fichiers de modèle sont simplement des fichiers PHP. Si le
contenu du fichier de modèle `hello.php` est :

```php
Bonjour, <?= $name ?> !
```

Le résultat serait :

```
Bonjour, Bob !
```

Vous pouvez également définir manuellement des variables de vue en utilisant la méthode set :

```php
Flight::view()->set('name', 'Bob');
```

La variable `name` est maintenant disponible dans toutes vos vues. Ainsi, vous pouvez simplement faire :

```php
Flight::render('hello');
```

Notez que lors de la spécification du nom du modèle dans la méthode render, vous pouvez
omettre l'extension `.php`.

Par défaut, Flight cherchera un répertoire `views` pour les fichiers de modèle. Vous pouvez
définir un chemin alternatif pour vos modèles en configurant ce qui suit :

```php
Flight::set('flight.views.path', '/chemin/vers/views');
```

## Mises en page

Il est courant pour les sites web d'avoir un unique fichier modèle de mise en page avec un contenu changeant.
Pour rendre le contenu à utiliser dans une mise en page, vous pouvez transmettre un paramètre optionnel à la méthode `render`.

```php
Flight::render('header', ['heading' => 'Bonjour'], 'headerContent');
Flight::render('body', ['body' => 'Monde'], 'bodyContent');
```

Votre vue aura alors des variables enregistrées appelées `headerContent` et `bodyContent`.
Vous pouvez ensuite rendre votre mise en page en faisant :

```php
Flight::render('layout', ['title' => 'Page d'Accueil']);
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

Le résultat serait :
```html
<html>
  <head>
    <title>Page d'Accueil</title>
  </head>
  <body>
    <h1>Bonjour</h1>
    <div>Monde</div>
  </body>
</html>
```

## Vues Personnalisées

Flight vous permet de remplacer le moteur de vue par défaut simplement en enregistrant votre
propre classe de vue.

### Smarty

Voici comment vous pourriez utiliser le moteur de template [Smarty](http://www.smarty.net/)
pour vos vues :

```php
// Charger la bibliothèque Smarty
require './Smarty/libs/Smarty.class.php';

// Enregistrer Smarty en tant que classe de vue
// Passe également une fonction de rappel pour configurer Smarty lors du chargement
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
});

// Attribuer des données de modèle
Flight::view()->assign('name', 'Bob');

// Afficher le modèle
Flight::view()->display('hello.tpl');
```

Pour plus de complétude, vous devriez également substituer la méthode de rendu par défaut de Flight :

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

Voici comment vous pourriez utiliser le moteur de template [Latte](https://latte.nette.org/)
pour vos vues :

```php

// Enregistrer Latte en tant que classe de vue
// Passe également une fonction de rappel pour configurer Latte lors du chargement
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // C'est là que Latte mettra en cache vos modèles pour accélérer les choses
	// Une chose intéressante à propos de Latte est qu'il rafraîchit automatiquement votre
	// cache lorsque vous apportez des modifications à vos modèles !
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Indiquez à Latte où se trouvera le répertoire racine de vos vues.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Et finalisez pour pouvoir utiliser correctement Flight::render()
Flight::map('render', function(string $template, array $data): void {
  // C'est comme $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```