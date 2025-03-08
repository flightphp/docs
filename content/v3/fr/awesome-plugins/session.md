# FlightPHP Session - Gestionnaire de session léger basé sur des fichiers

Il s'agit d'un plugin de gestion des sessions léger et basé sur des fichiers pour le [Flight PHP Framework](https://docs.flightphp.com/). Il fournit une solution simple mais puissante pour la gestion des sessions, avec des fonctionnalités telles que des lectures de session non bloquantes, un chiffrement optionnel, une fonctionnalité d'auto-commit et un mode test pour le développement. Les données de session sont stockées dans des fichiers, ce qui le rend idéal pour les applications qui n'ont pas besoin d'une base de données.

Si vous souhaitez utiliser une base de données, consultez le plugin [ghostff/session](/awesome-plugins/ghost-session) avec de nombreuses fonctionnalités similaires mais avec un backend de base de données.

Visitez le [dépôt Github](https://github.com/flightphp/session) pour le code source complet et des détails.

## Installation

Installez le plugin via Composer :

```bash
composer require flightphp/session
```

## Utilisation de base

Voici un exemple simple de la façon d'utiliser le plugin `flightphp/session` dans votre application Flight :

```php
require 'vendor/autoload.php';

use flight\Session;

$app = Flight::app();

// Enregistrer le service de session
$app->register('session', Session::class);

// Exemple de route avec utilisation de session
Flight::route('/login', function() {
    $session = Flight::session();
    $session->set('user_id', 123);
    $session->set('username', 'johndoe');
    $session->set('is_admin', false);

    echo $session->get('username'); // Affiche : johndoe
    echo $session->get('preferences', 'default_theme'); // Affiche : default_theme

    if ($session->get('user_id')) {
        Flight::json(['message' => 'L’utilisateur est connecté !', 'user_id' => $session->get('user_id')]);
    }
});

Flight::route('/logout', function() {
    $session = Flight::session();
    $session->clear(); // Efface toutes les données de session
    Flight::json(['message' => 'Déconnexion réussie']);
});

Flight::start();
```

### Points clés
- **Non-Bloquant** : Utilise `read_and_close` pour le démarrage de session par défaut, ce qui empêche les problèmes de verrouillage des sessions.
- **Auto-Commit** : Activé par défaut, donc les modifications sont enregistrées automatiquement à l'arrêt à moins que désactivé.
- **Stockage de fichiers** : Les sessions sont stockées dans le répertoire temporaire du système sous `/flight_sessions` par défaut.

## Configuration

Vous pouvez personnaliser le gestionnaire de session en passant un tableau d'options lors de l'enregistrement :

```php
$app->register('session', Session::class, [
    'save_path' => '/custom/path/to/sessions',         // Répertoire pour les fichiers de session
    'encryption_key' => 'a-secure-32-byte-key-here',   // Activer le chiffrement (32 octets recommandés pour AES-256-CBC)
    'auto_commit' => false,                            // Désactiver l'auto-commit pour un contrôle manuel
    'start_session' => true,                           // Démarrer la session automatiquement (par défaut : true)
    'test_mode' => false                               // Activer le mode test pour le développement
]);
```

### Options de configuration
| Option            | Description                                      | Valeur par défaut                     |
|-------------------|--------------------------------------------------|-----------------------------------|
| `save_path`       | Répertoire où sont stockés les fichiers de session         | `sys_get_temp_dir() . '/flight_sessions'` |
| `encryption_key`  | Clé pour le chiffrement AES-256-CBC (optionnel)        | `null` (pas de chiffrement)            |
| `auto_commit`     | Sauvegarde automatique des données de session à l'arrêt               | `true`                            |
| `start_session`   | Démarrer la session automatiquement                  | `true`                            |
| `test_mode`       | Exécuter en mode test sans affecter les sessions PHP  | `false`                           |
| `test_session_id` | ID de session personnalisé pour le mode test (optionnel)       | Généré aléatoirement si non défini     |

## Utilisation avancée

### Commit manuel
Si vous désactivez l'auto-commit, vous devez engager manuellement les modifications :

```php
$app->register('session', Session::class, ['auto_commit' => false]);

Flight::route('/update', function() {
    $session = Flight::session();
    $session->set('key', 'value');
    $session->commit(); // Enregistrer explicitement les modifications
});
```

### Sécurité des sessions avec chiffrement
Activez le chiffrement pour les données sensibles :

```php
$app->register('session', Session::class, [
    'encryption_key' => 'your-32-byte-secret-key-here'
]);

Flight::route('/secure', function() {
    $session = Flight::session();
    $session->set('credit_card', '4111-1111-1111-1111'); // Chiffré automatiquement
    echo $session->get('credit_card'); // Déchiffré lors de la récupération
});
```

### Régénération de session
Régénérez l'ID de session pour des raisons de sécurité (par exemple, après une connexion) :

```php
Flight::route('/post-login', function() {
    $session = Flight::session();
    $session->regenerate(); // Nouvel ID, garder les données
    // OU
    $session->regenerate(true); // Nouvel ID, supprimer les anciennes données
});
```

### Exemple de middleware
Protégez les routes avec une authentification basée sur la session :

```php
Flight::route('/admin', function() {
    Flight::json(['message' => 'Bienvenue sur le panneau d’administration']);
})->addMiddleware(function() {
    $session = Flight::session();
    if (!$session->get('is_admin')) {
        Flight::halt(403, 'Accès refusé');
    }
});
```

Ceci est juste un exemple simple de la façon d'utiliser cela dans un middleware. Pour un exemple plus approfondi, consultez la documentation [middleware](/learn/middleware).

## Méthodes

La classe `Session` fournit ces méthodes :

- `set(string $key, $value)`: Stocke une valeur dans la session.
- `get(string $key, $default = null)`: Récupère une valeur, avec une valeur par défaut optionnelle si la clé n’existe pas.
- `delete(string $key)`: Supprime une clé spécifique de la session.
- `clear()`: Supprime toutes les données de la session.
- `commit()`: Sauvegarde les données de session actuelles sur le système de fichiers.
- `id()`: Renvoie l'ID de session actuel.
- `regenerate(bool $deleteOld = false)`: Régénère l’ID de session, en supprimant éventuellement les anciennes données.

Toutes les méthodes sauf `get()` et `id()` retournent l'instance `Session` pour le chaînage.

## Pourquoi utiliser ce plugin ?

- **Léger** : Pas de dépendances externes—juste des fichiers.
- **Non-Bloquant** : Évite le verrouillage des sessions avec `read_and_close` par défaut.
- **Securisé** : Prend en charge le chiffrement AES-256-CBC pour les données sensibles.
- **Flexible** : Options d'auto-commit, mode test et contrôle manuel.
- **Natifs à Flight** : Construit spécifiquement pour le framework Flight.

## Détails techniques

- **Format de stockage** : Les fichiers de session sont préfixés par `sess_` et stockés dans le `save_path` configuré. Les données chiffrées utilisent un préfixe `E`, les données en texte clair utilisent `P`.
- **Chiffrement** : Utilise AES-256-CBC avec un IV aléatoire par écriture de session lorsqu'une `encryption_key` est fournie.
- **Collecte des déchets** : Implémente `SessionHandlerInterface::gc()` de PHP pour nettoyer les sessions expirées.

## Contribuer

Les contributions sont les bienvenues ! Forkez le [dépôt](https://github.com/flightphp/session), apportez vos modifications et soumettez une demande de tirage. Signalez des bugs ou suggérez des fonctionnalités via le suivi des problèmes de Github.

## Licence

Ce plugin est sous licence MIT. Consultez le [dépôt Github](https://github.com/flightphp/session) pour plus de détails.