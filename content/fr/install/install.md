# Installation

## Téléchargement des fichiers

Assurez-vous d'avoir PHP installé sur votre système. Sinon, cliquez [ici](#installing-php) pour obtenir des instructions sur la façon de l'installer pour votre système.

Si vous utilisez [Composer](https://getcomposer.org), vous pouvez exécuter la commande suivante:

```bash
composer require flightphp/core
```

OU vous pouvez [télécharger les fichiers](https://github.com/flightphp/core/archive/master.zip) directement et les extraire dans votre répertoire Web.

## Configurez votre serveur Web

### Serveur de développement PHP intégré

C'est de loin la manière la plus simple de démarrer. Vous pouvez utiliser le serveur intégré pour exécuter votre application et même utiliser SQLite pour une base de données (tant que sqlite3 est installé sur votre système) et ne nécessitez pas grand-chose ! Exécutez simplement la commande suivante une fois PHP installé:

```bash
php -S localhost:8000
```

Ensuite, ouvrez votre navigateur et allez à `http://localhost:8000`.

Si vous souhaitez rendre le répertoire de documents de votre projet dans un répertoire différent (Ex: votre projet est `~/myproject`, mais votre répertoire de documents est `~/myproject/public/`), vous pouvez exécuter la commande suivante une fois dans le répertoire `~/myproject`:

```bash
php -S localhost:8000 -t public/
```

Ensuite, ouvrez votre navigateur et allez à `http://localhost:8000`.

### Apache

Assurez-vous qu'Apache est déjà installé sur votre système. Sinon, recherchez comment installer Apache sur votre système.

Pour Apache, éditez votre fichier `.htaccess` avec ce qui suit:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Remarque**: Si vous avez besoin d'utiliser Flight dans un sous-répertoire, ajoutez la ligne
> `RewriteBase /sous-repertoire/` juste après `RewriteEngine On`.

> **Remarque**: Si vous souhaitez protéger tous les fichiers du serveur, comme un fichier de base de données ou un fichier env.
> Mettez ceci dans votre fichier `.htaccess`:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

Assurez-vous que Nginx est déjà installé sur votre système. Sinon, recherchez comment installer Nginx sur votre système.

Pour Nginx, ajoutez ce qui suit à la déclaration de votre serveur:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## Créez votre fichier `index.php`

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

## Installation de PHP

Si vous avez déjà `php` installé sur votre système, passez ces instructions et passez à [la section de téléchargement](#download-the-files)

Bien sûr ! Voici les instructions pour installer PHP sur macOS, Windows 10/11, Ubuntu et Rocky Linux. Je vais également inclure des détails sur l'installation de différentes versions de PHP.