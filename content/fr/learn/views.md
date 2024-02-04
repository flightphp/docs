# Vues

Flight offre par défaut une fonctionnalité de base de templating. Pour afficher une vue
modèle, appelez la méthode `render` avec le nom du fichier modèle et des données de modèle optionnelles :

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Les données de modèle que vous passez sont automatiquement injectées dans le modèle et peuvent
être référencées comme une variable locale. Les fichiers de modèle sont simplement des fichiers PHP. Si le
contenu du fichier modèle `hello.php` est :

```php
Bonjour, <?= $name ?>!
```

La sortie serait :

```
Bonjour, Bob!
```

Vous pouvez également définir manuellement des variables de vue en utilisant la méthode set :

```php
Flight::view()->set('name', 'Bob');
```

La variable `name` est maintenant disponible dans toutes vos vues. Vous pouvez donc simplement faire :

```php
Flight::render('hello');
```

Notez que lors de la spécification du nom du modèle dans la méthode render, vous pouvez
omettre l'extension `.php`.

Par défaut, Flight recherchera un répertoire `views` pour les fichiers de modèle. Vous pouvez
définir un chemin alternatif pour vos modèles en configurant comme suit :

```php
Flight::set('flight.views.path', '/chemin/vers/vues');
```

## Mises en page

Il est courant que les sites web aient un seul fichier modèle de mise en page avec un contenu
interchangeable. Pour rendre du contenu à utiliser dans une mise en page, vous pouvez passer un
paramètre facultatif à la méthode `render`.

```php
Flight::render('header', ['heading' => 'Bonjour'], 'headerContent');
Flight::render('body', ['body' => 'Monde'], 'bodyContent');
```

Votre vue aura alors des variables enregistrées appelées `headerContent` et `bodyContent`.
Vous pouvez ensuite rendre votre mise en page en faisant :

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

## Vues personnalisées

Flight vous permet de remplacer le moteur de vue par défaut en enregistrant simplement votre
propre classe de vue. Voici comment vous utiliseriez le [Smarty](http://www.smarty.net/)
moteur de template pour vos vues :

```php
// Charger la bibliothèque Smarty
require './Smarty/libs/Smarty.class.php';

// Enregistrer Smarty en tant que classe de vue
// Passez également une fonction de rappel pour configurer Smarty au chargement
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

Pour plus de complétude, vous devriez également remplacer la méthode render par défaut de Flight :

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```