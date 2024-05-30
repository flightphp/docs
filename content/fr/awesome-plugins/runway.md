# Piste

Piste est une application CLI qui vous aide à gérer vos applications Flight. Il peut générer des contrôleurs, afficher toutes les routes, et plus encore. Il est basé sur l'excellente bibliothèque [adhocore/php-cli](https://github.com/adhocore/php-cli).

## Installation

Installer avec composer.

```bash
composer require flightphp/runway
```

## Configuration de base

La première fois que vous exécutez Piste, il vous guidera à travers un processus de configuration et créera un fichier de configuration `.runway.json` à la racine de votre projet. Ce fichier contiendra quelques configurations nécessaires pour que Piste fonctionne correctement.

## Utilisation

Piste possède un certain nombre de commandes que vous pouvez utiliser pour gérer votre application Flight. Il existe deux façons faciles d'utiliser Piste.

1. Si vous utilisez le projet squelette, vous pouvez exécuter `php runway [commande]` depuis la racine de votre projet.
1. Si vous utilisez Piste en tant que package installé via composer, vous pouvez exécuter `vendor/bin/runway [commande]` depuis la racine de votre projet.

Pour n'importe quelle commande, vous pouvez passer le drapeau `--help` pour obtenir plus d'informations sur comment utiliser la commande.

```bash
php runway routes --help
```

Voici quelques exemples :

### Générer un contrôleur

Basé sur la configuration dans votre fichier `.runway.json`, l'emplacement par défaut générera un contrôleur pour vous dans le répertoire `app/controllers/`.

```bash
php runway make:controller MonControleur
```

### Générer un modèle Active Record

Basé sur la configuration dans votre fichier `.runway.json`, l'emplacement par défaut générera un contrôleur pour vous dans le répertoire `app/records/`.

```bash
php runway make:record utilisateurs
```

Si par exemple vous avez la table `users` avec le schéma suivant : `id`, `nom`, `email`, `créé_le`, `mis_à_jour_le`, un fichier similaire au suivant sera créé dans le fichier `app/records/UtilisateurRecord.php` :

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Classe Active Record pour la table des utilisateurs.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $nom
 * @property string $email
 * @property string $créé_le
 * @property string $mis_à_jour_le
 */
class UtilisateurRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Définit les relations pour le modèle
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [];

    /**
     * Constructeur
     * @param mixed $connexionBaseDeDonnées La connexion à la base de données
     */
    public function __construct($connexionBaseDeDonnées)
    {
        parent::__construct($connexionBaseDeDonnées, 'users');
    }
}
```

### Afficher toutes les routes

Cela affichera toutes les routes actuellement enregistrées avec Flight.

```bash
php runway routes
```

Si vous souhaitez uniquement voir des routes spécifiques, vous pouvez passer un drapeau pour filtrer les routes.

```bash
# Afficher uniquement les routes GET
php runway routes --get

# Afficher uniquement les routes POST
php runway routes --post

# etc.
```

## Personnalisation de Piste

Si vous créez un package pour Flight, ou si vous souhaitez ajouter vos propres commandes personnalisées dans votre projet, vous pouvez le faire en créant un répertoire `src/commands/`, `flight/commands/`, `app/commands/` ou `commands/` pour votre projet/package.

Pour créer une commande, vous étendez simplement la classe `AbstractBaseCommand`, et implémentez au minimum une méthode `__construct` et une méthode `execute`.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class CommandeExemple extends AbstractBaseCommand
{
	/**
     * Construire
     *
     * @param array<string,mixed> $config Configuration JSON de .runway-config.json
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Créer un exemple pour la documentation', $config);
        $this->argument('<gif-amusant>', 'Le nom du gif amusant');
    }

	/**
     * Exécute la fonction
     *
     * @return void
     */
    public function execute(string $contrôleur)
    {
        $io = $this->app()->io();

		$io->info('Création de l\'exemple...');

		// Faire quelque chose ici

		$io->ok('Exemple créé !');
	}
}
```

Consultez la [Documentation adhocore/php-cli](https://github.com/adhocore/php-cli) pour plus d'informations sur la façon de créer vos propres commandes personnalisées dans votre application Flight !