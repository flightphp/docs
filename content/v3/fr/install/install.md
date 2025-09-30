# Instructions d'installation

Il y a quelques prérequis de base avant de pouvoir installer Flight. En particulier, vous devrez :

1. [Installer PHP sur votre système](#installing-php)
2. [Installer Composer](https://getcomposer.org) pour une meilleure expérience de développement.

## Installation de base

Si vous utilisez [Composer](https://getcomposer.org), vous pouvez exécuter la commande suivante :

```bash
composer require flightphp/core
```

Cela ne placera que les fichiers principaux de Flight sur votre système. Vous devrez définir la structure du projet, [l'agencement](/learn/templates), [les dépendances](/learn/dependency-injection-container), [les configurations](/learn/configuration), [le chargement automatique](/learn/autoloading), etc. Cette méthode garantit qu'aucune autre dépendance n'est installée en dehors de Flight.

Vous pouvez également [télécharger les fichiers](https://github.com/flightphp/core/archive/master.zip)
 directement et les extraire dans votre répertoire web.

## Installation recommandée

Il est fortement recommandé de commencer avec l'application [flightphp/skeleton](https://github.com/flightphp/skeleton) pour tout nouveau projet. L'installation est un jeu d'enfant.

```bash
composer create-project flightphp/skeleton my-project/
```

Cela configurera la structure de votre projet, configurera le chargement automatique avec des espaces de noms, mettra en place une configuration, et fournira d'autres outils comme [Tracy](/awesome-plugins/tracy), [Extensions Tracy](/awesome-plugins/tracy-extensions), et [Runway](/awesome-plugins/runway)

## Configurer votre serveur web

### Serveur de développement PHP intégré

C'est de loin la façon la plus simple de démarrer. Vous pouvez utiliser le serveur intégré pour exécuter votre application et même utiliser SQLite pour une base de données (tant que sqlite3 est installé sur votre système) sans avoir besoin de grand-chose ! Exécutez simplement la commande suivante une fois que PHP est installé :

```bash
php -S localhost:8000
# ou avec l'application skeleton
composer start
```

Puis ouvrez votre navigateur et allez à `http://localhost:8000`.

Si vous souhaitez définir le répertoire racine de documents de votre projet comme un répertoire différent (Ex : votre projet est `~/myproject`, mais votre répertoire racine de documents est `~/myproject/public/`), vous pouvez exécuter la commande suivante une fois dans le répertoire `~/myproject` :

```bash
php -S localhost:8000 -t public/
# avec l'application skeleton, cela est déjà configuré
composer start
```

Puis ouvrez votre navigateur et allez à `http://localhost:8000`.

### Apache

Assurez-vous qu'Apache est déjà installé sur votre système. Sinon, recherchez sur Google comment installer Apache sur votre système.

Pour Apache, modifiez votre fichier `.htaccess` avec le suivant :

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Note** : Si vous devez utiliser flight dans un sous-répertoire, ajoutez la ligne
> `RewriteBase /subdir/` juste après `RewriteEngine On`.

> **Note** : Si vous souhaitez protéger tous les fichiers du serveur, comme un fichier de base de données ou env.
> Mettez cela dans votre fichier `.htaccess` :

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

Assurez-vous que Nginx est déjà installé sur votre système. Sinon, recherchez sur Google comment installer Nginx sur votre système.

Pour Nginx, ajoutez le suivant à votre déclaration de serveur :

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## Créer votre fichier `index.php`

Si vous effectuez une installation de base, vous devrez avoir du code pour commencer.

```php
<?php

// Si vous utilisez Composer, incluez le chargeur automatique.
require 'vendor/autoload.php';
// si vous n'utilisez pas Composer, chargez le framework directement
// require 'flight/Flight.php';

// Ensuite, définissez une route et assignez une fonction pour gérer la requête.
Flight::route('/', function () {
  echo 'hello world!';
});

// Enfin, démarrez le framework.
Flight::start();
```

Avec l'application skeleton, cela est déjà configuré et géré dans votre fichier `app/config/routes.php`. Les services sont configurés dans `app/config/services.php`

## Installer PHP

Si vous avez déjà `php` installé sur votre système, passez ces instructions et allez à [la section de téléchargement](#download-the-files)

### **macOS**

#### **Installer PHP en utilisant Homebrew**

1. **Installer Homebrew** (si ce n'est pas déjà installé) :
   - Ouvrez le Terminal et exécutez :
     ```bash
     /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
     ```

2. **Installer PHP** :
   - Installer la dernière version :
     ```bash
     brew install php
     ```
   - Pour installer une version spécifique, par exemple, PHP 8.1 :
     ```bash
     brew tap shivammathur/php
     brew install shivammathur/php/php@8.1
     ```

3. **Changer entre les versions de PHP** :
   - Délier la version actuelle et lier la version souhaitée :
     ```bash
     brew unlink php
     brew link --overwrite --force php@8.1
     ```
   - Vérifier la version installée :
     ```bash
     php -v
     ```

### **Windows 10/11**

#### **Installer PHP manuellement**

1. **Télécharger PHP** :
   - Visitez [PHP for Windows](https://windows.php.net/download/) et téléchargez la dernière version ou une version spécifique (par ex., 7.4, 8.0) sous forme de fichier zip non thread-safe.

2. **Extraire PHP** :
   - Extrayez le fichier zip téléchargé dans `C:\php`.

3. **Ajouter PHP au PATH système** :
   - Allez dans **Propriétés du système** > **Variables d'environnement**.
   - Sous **Variables système**, trouvez **Path** et cliquez sur **Modifier**.
   - Ajoutez le chemin `C:\php` (ou l'endroit où vous avez extrait PHP).
   - Cliquez sur **OK** pour fermer toutes les fenêtres.

4. **Configurer PHP** :
   - Copiez `php.ini-development` vers `php.ini`.
   - Modifiez `php.ini` pour configurer PHP selon vos besoins (par ex., définir `extension_dir`, activer les extensions).

5. **Vérifier l'installation de PHP** :
   - Ouvrez l'Invite de commandes et exécutez :
     ```cmd
     php -v
     ```

#### **Installer plusieurs versions de PHP**

1. **Répétez les étapes ci-dessus** pour chaque version, en les plaçant dans un répertoire séparé (par ex., `C:\php7`, `C:\php8`).

2. **Changer entre les versions** en ajustant la variable PATH système pour pointer vers le répertoire de la version souhaitée.

### **Ubuntu (20.04, 22.04, etc.)**

#### **Installer PHP en utilisant apt**

1. **Mettre à jour les listes de paquets** :
   - Ouvrez le Terminal et exécutez :
     ```bash
     sudo apt update
     ```

2. **Installer PHP** :
   - Installer la dernière version de PHP :
     ```bash
     sudo apt install php
     ```
   - Pour installer une version spécifique, par exemple, PHP 8.1 :
     ```bash
     sudo apt install php8.1
     ```

3. **Installer des modules supplémentaires** (optionnel) :
   - Par exemple, pour installer le support MySQL :
     ```bash
     sudo apt install php8.1-mysql
     ```

4. **Changer entre les versions de PHP** :
   - Utilisez `update-alternatives` :
     ```bash
     sudo update-alternatives --set php /usr/bin/php8.1
     ```

5. **Vérifier la version installée** :
   - Exécutez :
     ```bash
     php -v
     ```

### **Rocky Linux**

#### **Installer PHP en utilisant yum/dnf**

1. **Activer le dépôt EPEL** :
   - Ouvrez le Terminal et exécutez :
     ```bash
     sudo dnf install epel-release
     ```

2. **Installer le dépôt Remi** :
   - Exécutez :
     ```bash
     sudo dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
     sudo dnf module reset php
     ```

3. **Installer PHP** :
   - Pour installer la version par défaut :
     ```bash
     sudo dnf install php
     ```
   - Pour installer une version spécifique, par exemple, PHP 7.4 :
     ```bash
     sudo dnf module install php:remi-7.4
     ```

4. **Changer entre les versions de PHP** :
   - Utilisez la commande de module `dnf` :
     ```bash
     sudo dnf module reset php
     sudo dnf module enable php:remi-8.0
     sudo dnf install php
     ```

5. **Vérifier la version installée** :
   - Exécutez :
     ```bash
     php -v
     ```

### **Notes générales**

- Pour les environnements de développement, il est important de configurer les paramètres PHP selon les exigences de votre projet. 
- Lors du changement de versions de PHP, assurez-vous que toutes les extensions PHP pertinentes sont installées pour la version spécifique que vous prévoyez d'utiliser.
- Redémarrez votre serveur web (Apache, Nginx, etc.) après avoir changé de version de PHP ou mis à jour les configurations pour appliquer les changements.