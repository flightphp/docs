# FlightPHP Session - Gestionnaire de sessions léger basé sur des fichiers

Ceci est un gestionnaire de sessions léger basé sur des fichiers, plugin pour le [Flight PHP Framework](https://docs.flightphp.com/). Il fournit une solution simple mais puissante pour gérer les sessions, avec des fonctionnalités comme des lectures de sessions non bloquantes, un chiffrement optionnel, une fonctionnalité d'auto-commit et un mode test pour le développement. Les données de session sont stockées dans des fichiers, ce qui en fait un choix idéal pour les applications qui n'ont pas besoin d'une base de données.

Si vous souhaitez utiliser une base de données, consultez le plugin [ghostff/session](/awesome-plugins/ghost-session) qui propose de nombreuses fonctionnalités similaires mais avec un backend de base de données.

Visitez le [dépôt Github](https://github.com/flightphp/session) pour le code source complet et les détails.

## Installation

Installez le plugin via Composer :

```bash
composer require flightphp/session
```

## Utilisation de base

Voici un exemple simple de l'utilisation du plugin `flightphp/session` dans votre application Flight :

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
        Flight::json(['message' => 'L\'utilisateur est connecté !', 'user_id' => $session->get('user_id')]);
    }
});

Flight::route('/logout', function() {
    $session = Flight::session();
    $session->clear(); // Effacer toutes les données de session
    Flight::json(['message' => 'Déconnexion réussie']);
});

Flight::start();
```

### Points clés
- **Non-Blocking** : Utilise `read_and_close` pour le démarrage de session par défaut, évitant les problèmes de verrouillage de session.
- **Auto-Commit** : Activé par défaut, les modifications sont enregistrées automatiquement à l'arrêt, sauf si désactivé.
- **Stockage de fichiers** : Les sessions sont stockées dans le répertoire temporaire du système sous `/flight_sessions` par défaut.

## Configuration

Vous pouvez personnaliser le gestionnaire de sessions en passant un tableau d'options lors de l'enregistrement :

```php
// Oui, c'est un double tableau :)
$app->register('session', Session::class, [ [
    'save_path' => '/custom/path/to/sessions',         // Répertoire pour les fichiers de session
	'prefix' => 'myapp_',                              // Préfixe pour les fichiers de session
    'encryption_key' => 'a-secure-32-byte-key-here',   // Activer le chiffrement (32 octets recommandés pour AES-256-CBC)
    'auto_commit' => false,                            // Désactiver l'auto-commit pour un contrôle manuel
    'start_session' => true,                           // Démarrer la session automatiquement (par défaut : true)
    'test_mode' => false,                              // Activer le mode test pour le développement
    'serialization' => 'json',                         // Méthode de sérialisation : 'json' (par défaut) ou 'php' (léguée)
] ]);
```

### Options de configuration
| Option            | Description                                      | Valeur par défaut                     |
|-------------------|--------------------------------------------------|---------------------------------------|
| `save_path`       | Répertoire où les fichiers de session sont stockés | `sys_get_temp_dir() . '/flight_sessions'` |
| `prefix`          | Préfixe pour le fichier de session enregistré    | `sess_`                           |
| `encryption_key`  | Clé pour le chiffrement AES-256-CBC (optionnel)  | `null` (pas de chiffrement)            |
| `auto_commit`     | Enregistrement automatique des données de session à l'arrêt | `true`                            |
| `start_session`   | Démarrer la session automatiquement             | `true`                            |
| `test_mode`       | Exécuter en mode test sans affecter les sessions PHP | `false`                           |
| `test_session_id` | ID de session personnalisé pour le mode test (optionnel) | Généré aléatoirement s'il n'est pas défini |
| `serialization`   | Méthode de sérialisation : 'json' (par défaut, sécurisé) ou 'php' (léguée, permet les objets) | `'json'` |

## Modes de sérialisation

Par défaut, cette bibliothèque utilise la **sérialisation JSON** pour les données de session, ce qui est sécurisé et empêche les vulnérabilités d'injection d'objets PHP. Si vous devez stocker des objets PHP dans la session (non recommandé pour la plupart des applications), vous pouvez choisir la sérialisation PHP léguée :

- `'serialization' => 'json'` (par défaut) :
  - Seuls les tableaux et les primitives sont autorisés dans les données de session.
  - Plus sécurisé : immunisé contre l'injection d'objets PHP.
  - Les fichiers sont préfixés avec `J` (JSON pur) ou `F` (JSON chiffré).
- `'serialization' => 'php'` :
  - Permet de stocker des objets PHP (à utiliser avec précaution).
  - Les fichiers sont préfixés avec `P` (sérialisation PHP pure) ou `E` (sérialisation PHP chiffrée).

**Note :** Si vous utilisez la sérialisation JSON, tenter de stocker un objet lèvera une exception.

## Utilisation avancée

### Commit manuel
Si vous désactivez l'auto-commit, vous devez manuellement enregistrer les modifications :

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
Régénérez l'ID de session pour des raisons de sécurité (par exemple, après la connexion) :

```php
Flight::route('/post-login', function() {
    $session = Flight::session();
    $session->regenerate(); // Nouvel ID, conserver les données
    // OU
    $session->regenerate(true); // Nouvel ID, supprimer les anciennes données
});
```

### Exemple de middleware
Protégez les routes avec une authentification basée sur les sessions :

```php
Flight::route('/admin', function() {
    Flight::json(['message' => 'Bienvenue sur le panneau d\'administration']);
})->addMiddleware(function() {
    $session = Flight::session();
    if (!$session->get('is_admin')) {
        Flight::halt(403, 'Accès refusé');
    }
});
```

Ceci est juste un exemple simple de l'utilisation dans un middleware. Pour un exemple plus approfondi, consultez la documentation sur le [middleware](/learn/middleware).

## Méthodes

La classe `Session` fournit ces méthodes :

- `set(string $key, $value)`: Stocke une valeur dans la session.
- `get(string $key, $default = null)`: Récupère une valeur, avec une valeur par défaut optionnelle si la clé n'existe pas.
- `delete(string $key)`: Supprime une clé spécifique de la session.
- `clear()`: Supprime toutes les données de session, mais conserve le même nom de fichier pour la session.
- `commit()`: Enregistre les données de session actuelles sur le système de fichiers.
- `id()`: Retourne l'ID de session actuel.
- `regenerate(bool $deleteOldFile = false)`: Régénère l'ID de session, y compris la création d'un nouveau fichier de session, en conservant toutes les anciennes données et le ancien fichier reste sur le système. Si `$deleteOldFile` est `true`, l'ancien fichier de session est supprimé.
- `destroy(string $id)`: Détruit une session par ID et supprime le fichier de session du système. Ceci fait partie de l'interface `SessionHandlerInterface` et `$id` est requis. L'utilisation typique serait `$session->destroy($session->id())`.
- `getAll()` : Retourne toutes les données de la session actuelle.

Toutes les méthodes sauf `get()` et `id()` renvoient l'instance `Session` pour la chaîne.

## Pourquoi utiliser ce plugin ?

- **Lightweight** : Pas de dépendances externes – juste des fichiers.
- **Non-Blocking** : Évite le verrouillage de session avec `read_and_close` par défaut.
- **Secure** : Prend en charge le chiffrement AES-256-CBC pour les données sensibles.
- **Flexible** : Options d'auto-commit, mode test et contrôle manuel.
- **Flight-Native** : Conçu spécifiquement pour le framework Flight.

## Détails techniques

- **Format de stockage** : Les fichiers de session sont préfixés avec `sess_` et stockés dans le `save_path` configuré. Les préfixes de contenu de fichier :
  - `J` : JSON pur (par défaut, sans chiffrement)
  - `F` : JSON chiffré (par défaut avec chiffrement)
  - `P` : Sérialisation PHP pure (léguée, sans chiffrement)
  - `E` : Sérialisation PHP chiffrée (léguée avec chiffrement)
- **Chiffrement** : Utilise AES-256-CBC avec un IV aléatoire par écriture de session lorsqu'une `encryption_key` est fournie. Le chiffrement fonctionne pour les modes de sérialisation JSON et PHP.
- **Sérialisation** : JSON est la méthode par défaut et la plus sécurisée. La sérialisation PHP est disponible pour une utilisation léguée/avancée, mais elle est moins sécurisée.
- **Collecte des déchets** : Implémente `SessionHandlerInterface::gc()` de PHP pour nettoyer les sessions expirées.

## Contribution

Les contributions sont les bienvenues ! Forkez le [dépôt](https://github.com/flightphp/session), apportez vos modifications et soumettez une pull request. Signalez les bogues ou suggérez des fonctionnalités via le tracker d'issues Github.

## Licence

Ce plugin est sous licence MIT. Consultez le [dépôt Github](https://github.com/flightphp/session) pour plus de détails.