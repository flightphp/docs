## Allée

Allée est une application CLI qui vous aide à gérer vos applications Flight. Il peut générer des contrôleurs, afficher toutes les routes et plus encore. Il est basé sur l'excellente bibliothèque [adhocore/php-cli](https://github.com/adhocore/php-cli).

## Installation

Installer avec composer.

```bash
composer require flightphp/runway
```

## Configuration de Base

La première fois que vous exécutez Allée, il vous guidera à travers un processus de configuration et créera un fichier de configuration `.runway.json` à la racine de votre projet. Ce fichier contiendra certaines configurations nécessaires pour qu'Allée fonctionne correctement.

## Utilisation

Allée a plusieurs commandes que vous pouvez utiliser pour gérer votre application Flight. Il y a deux façons faciles d'utiliser Allée.

1. Si vous utilisez le projet squelette, vous pouvez exécuter `php runway [commande]` depuis la racine de votre projet.
1. Si vous utilisez Allée en tant que package installé via composer, vous pouvez exécuter `vendor/bin/runway [commande]` depuis la racine de votre projet.

Pour n'importe quelle commande, vous pouvez ajouter le drapeau `--help` pour obtenir plus d'informations sur comment utiliser la commande.

```bash
php runway routes --help
```

Voici quelques exemples :

### Générer un Contrôleur

En fonction de la configuration dans votre fichier `.runway.json`, l'emplacement par défaut générera un contrôleur pour vous dans le répertoire `app/controllers/`.

```bash
php runway make:controller MonContrôleur
```

### Générer un Modèle de Record Actif

En fonction de la configuration dans votre fichier `.runway.json`, l'emplacement par défaut générera un contrôleur pour vous dans le répertoire `app/records/`.

```bash
php runway make:record utilisateurs
```

Si par exemple vous avez la table `utilisateurs` avec le schéma suivant : `id`, `nom`, `email`, `créé à`, `mis à jour à`, un fichier similaire au suivant sera créé dans le fichier `app/records/RecordUtilisateur.php` :

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Classe ActiveRecord pour la table utilisateurs.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $nom
 * @property string $email
 * @property string $créé à
 * @property string $mis à jour à
 * // vous pourriez également ajouter des relations ici une fois que vous les définissez dans le tableau $relations
 * @property RecordSociété $société Exemple d'une relation
 */
class RecordUtilisateur extends \flight\ActiveRecord
{
    /**
     * @var array $relations Définir les relations pour le modèle
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [];

    /**
     * Constructeur
     * @param mixed $connexionBaseDonnées La connexion à la base de données
     */
    public function __construct($connexionBaseDonnées)
    {
        parent::__construct($connexionBaseDonnées, 'utilisateurs');
    }
}
```

### Afficher Toutes les Routes

Cela affichera toutes les routes actuellement enregistrées avec Flight.

```bash
php runway routes
```

Si vous souhaitez uniquement voir des routes spécifiques, vous pouvez ajouter un drapeau pour filtrer les routes.

```bash
# Afficher uniquement les routes GET
php runway routes --get

# Afficher uniquement les routes POST
php runway routes --post

# etc.
```

## Personnalisation d'Allée

Si vous créez un package pour Flight, ou si vous souhaitez ajouter vos propres commandes personnalisées dans votre projet, vous pouvez le faire en créant un répertoire `src/commands/`, `flight/commands/`, `app/commands/`, ou `commands/` pour votre projet/package.

Pour créer une commande, il vous suffit d'étendre la classe `AbstractBaseCommand`, et implémenter au minimum une méthode `__construct` et une méthode `execute`.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class CommandeExemple extends AbstractBaseCommand
{
    /**
     * Constructeur
     *
     * @param array<string,mixed> $config Configuration JSON de .runway-config.json
     */
    public function __construct(array $config)
    {
        parent::__construct('make:exemple', 'Créer un exemple pour la documentation', $config);
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

		$io->info('Création de l'exemple...');

		// Faites quelque chose ici

		$io->ok('Exemple créé !');
	}
}
```

Consultez la [Documentation adhocore/php-cli](https://github.com/adhocore/php-cli) pour plus d'informations sur comment intégrer vos propres commandes personnalisées dans votre application Flight !