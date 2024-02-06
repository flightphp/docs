# Installation

## Téléchargement des fichiers.

Si vous utilisez [Composer](https://getcomposer.org), vous pouvez exécuter la commande suivante :

```bash
composer require flightphp/core
```

OU vous pouvez [télécharger les fichiers](https://github.com/flightphp/core/archive/master.zip) directement et les extraire dans votre répertoire web.

## Configurez votre serveur Web.

### Apache
Pour Apache, modifiez votre fichier `.htaccess` comme suit :

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Remarque** : Si vous devez utiliser Flight dans un sous-répertoire, ajoutez la ligne
> `RewriteBase /subdir/` juste après `RewriteEngine On`.

> **Remarque** : Si vous souhaitez protéger tous les fichiers du serveur, comme un fichier db ou env.
> Mettez ceci dans votre fichier `.htaccess` :

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx
Pour Nginx, ajoutez ce qui suit à votre déclaration du serveur :

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## Créez votre fichier `index.php`.

```php
<?php

// Si vous utilisez Composer, requirez l'autoloader.
require 'vendor/autoload.php';
// si vous n'utilisez pas Composer, chargez le framework directement
// require 'flight/Flight.php';

// Ensuite, définissez une route et attribuez une fonction pour gérer la requête.
Flight::route('/', function () {
  echo 'bonjour le monde!';
});

// Enfin, démarrez le framework.
Flight::start();
```