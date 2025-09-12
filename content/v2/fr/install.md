# Installation

### 1. Téléchargez les fichiers.

Si vous utilisez [Composer](https://getcomposer.org), vous pouvez exécuter la commande suivante :

```bash
composer require flightphp/core
```

OU vous pouvez [télécharger](https://github.com/flightphp/core/archive/master.zip) les fichiers directement et les extraire dans votre répertoire web.

### 2. Configurez votre serveur web.

Pour *Apache*, modifiez votre fichier `.htaccess` avec ce qui suit :

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Remarque** : Si vous devez utiliser Flight dans un sous-répertoire, ajoutez la ligne
> `RewriteBase /subdir/` juste après `RewriteEngine On`.
> **Remarque** : Si vous souhaitez protéger tous les fichiers du serveur, comme un fichier db ou env.
> Mettez ceci dans votre fichier `.htaccess` :

```apache
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

Pour *Nginx*, ajoutez ce qui suit à votre déclaration de serveur :

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

### 3. Créez votre fichier `index.php`.

Tout d'abord, incluez le framework.

```php
require 'flight/Flight.php';
```

Si vous utilisez Composer, exécutez plutôt l'autoloader.

```php
require 'vendor/autoload.php';
```

Ensuite, définissez une route et assignez une fonction pour traiter la requête.

```php
Flight::route('/', function () {
  echo 'hello world!';
});
```

Enfin, démarrez le framework.

```php
Flight::start();
```
