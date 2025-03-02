## Vues

Flight fournit par défaut quelques fonctionnalités de base de templating. Pour afficher un template de vue, appelez la méthode `render` avec le nom du fichier template et des données de template optionnelles :

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Les données de template que vous transmettez sont automatiquement injectées dans le template et peuvent être référencées comme une variable locale. Les fichiers de template sont simplement des fichiers PHP. Si le contenu du fichier template `hello.php` est :

```php
Hello, <?= $name ?>!
```

La sortie serait :

```
Hello, Bob!
```

Vous pouvez également définir manuellement des variables de vue en utilisant la méthode set :

```php
Flight::view()->set('name', 'Bob');
```

La variable `name` est désormais disponible dans toutes vos vues. Vous pouvez simplement faire :

```php
Flight::render('hello');
```

Notez que lors de la spécification du nom du template dans la méthode render, vous pouvez omettre l'extension `.php`.

Par défaut, Flight recherchera un répertoire `views` pour les fichiers de template. Vous pouvez définir un chemin alternatif pour vos templates en configurant ce qui suit :

```php
Flight::set('flight.views.path', '/chemin/vers/views');
```

## Mises en page

Il est courant pour les sites web d'avoir un seul fichier de template de mise en page avec un contenu interchangeable. Pour afficher le contenu à utiliser dans une mise en page, vous pouvez transmettre un paramètre optionnel à la méthode `render`.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Votre vue aura ensuite des variables enregistrées appelées `headerContent` et `bodyContent`. Vous pouvez ensuite afficher votre mise en page en faisant :

```php
Flight::render('layout', ['title' => 'Page d\'accueil']);
```

Si les fichiers de template ressemblent à ceci :

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
    <h1>Hello</h1>
    <div>World</div>
  </body>
</html>
```

## Vues Personnalisées

Flight vous permet de remplacer simplement le moteur de vue par défaut en enregistrant votre propre classe de vue. Voici comment vous utiliseriez le moteur de template [Smarty](http://www.smarty.net/) pour vos vues :

```php
// Charger la bibliothèque Smarty
require './Smarty/libs/Smarty.class.php';

// Enregistrer Smarty en tant que classe de vue
// Transmettre également une fonction de rappel pour configurer Smarty au chargement
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Assigner des données de template
Flight::view()->assign('name', 'Bob');

// Afficher le template
Flight::view()->display('hello.tpl');
```

Pour plus de complétude, vous devriez également remplacer la méthode de rendu par défaut de Flight :

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```