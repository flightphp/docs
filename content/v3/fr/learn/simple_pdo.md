# Classe d'aide SimplePdo PDO

## Aperçu

La classe `SimplePdo` dans Flight est un assistant moderne et riche en fonctionnalités pour travailler avec des bases de données en utilisant PDO. Elle étend `PdoWrapper` et ajoute des méthodes d'aide pratiques pour les opérations courantes sur les bases de données comme `insert()`, `update()`, `delete()`, et les transactions. Elle simplifie les tâches liées aux bases de données, retourne les résultats sous forme de [Collections](/learn/collections) pour un accès facile, et prend en charge la journalisation des requêtes et la surveillance des performances des applications (APM) pour les cas d'utilisation avancés.

## Comprendre

La classe `SimplePdo` est conçue pour rendre le travail avec les bases de données en PHP beaucoup plus facile. Au lieu de jongler avec des instructions préparées, des modes de récupération et des opérations SQL verbeuses, vous obtenez des méthodes propres et simples pour les tâches courantes. Chaque ligne est retournée sous forme de Collection, afin que vous puissiez utiliser à la fois la notation tableau (`$row['name']`) et la notation objet (`$row->name`).

Cette classe est un sur-ensemble de `PdoWrapper`, ce qui signifie qu'elle inclut toute la fonctionnalité de `PdoWrapper` plus des méthodes d'aide supplémentaires qui rendent votre code plus propre et plus maintenable. Si vous utilisez actuellement `PdoWrapper`, la mise à niveau vers `SimplePdo` est simple car elle étend `PdoWrapper`.

Vous pouvez enregistrer `SimplePdo` en tant que service partagé dans Flight, puis l'utiliser n'importe où dans votre application via `Flight::db()`.

## Utilisation de base

### Enregistrement de SimplePdo

D'abord, enregistrez la classe `SimplePdo` avec Flight :

```php
Flight::register('db', \flight\database\SimplePdo::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
]);
```

> **NOTE**
>
> Si vous ne spécifiez pas `PDO::ATTR_DEFAULT_FETCH_MODE`, `SimplePdo` le définira automatiquement à `PDO::FETCH_ASSOC` pour vous.

Maintenant, vous pouvez utiliser `Flight::db()` n'importe où pour obtenir votre connexion à la base de données.

### Exécution de requêtes

#### `runQuery()`

`function runQuery(string $sql, array $params = []): PDOStatement`

Utilisez ceci pour les INSERT, UPDATE, ou lorsque vous voulez récupérer les résultats manuellement :

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM users WHERE status = ?", ['active']);
while ($row = $statement->fetch()) {
    // $row est un tableau
}
```

Vous pouvez aussi l'utiliser pour les écritures :

```php
$db->runQuery("INSERT INTO users (name) VALUES (?)", ['Alice']);
$db->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 1]);
```

#### `fetchField()`

`function fetchField(string $sql, array $params = []): mixed`

Obtenez une seule valeur de la base de données :

```php
$count = Flight::db()->fetchField("SELECT COUNT(*) FROM users WHERE status = ?", ['active']);
```

#### `fetchRow()`

`function fetchRow(string $sql, array $params = []): ?Collection`

Obtenez une seule ligne sous forme de Collection (accès tableau/objet) :

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// ou
echo $user->name;
```

> **TIP**
>
> `SimplePdo` ajoute automatiquement `LIMIT 1` aux requêtes `fetchRow()` si elle n'est pas déjà présente, rendant vos requêtes plus efficaces.

#### `fetchAll()`

`function fetchAll(string $sql, array $params = []): array<Collection>`

Obtenez toutes les lignes sous forme de tableau de Collections :

```php
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE status = ?", ['active']);
foreach ($users as $user) {
    echo $user['name'];
    // ou
    echo $user->name;
}
```

#### `fetchColumn()`

`function fetchColumn(string $sql, array $params = []): array`

Récupérez une seule colonne sous forme de tableau :

```php
$ids = Flight::db()->fetchColumn("SELECT id FROM users WHERE active = ?", [1]);
// Retourne : [1, 2, 3, 4, 5]
```

#### `fetchPairs()`

`function fetchPairs(string $sql, array $params = []): array`

Récupérez les résultats sous forme de paires clé-valeur (première colonne comme clé, seconde comme valeur) :

```php
$userNames = Flight::db()->fetchPairs("SELECT id, name FROM users");
// Retourne : [1 => 'John', 2 => 'Jane', 3 => 'Bob']
```

### Utilisation des placeholders `IN()`

Vous pouvez utiliser un seul `?` dans une clause `IN()` et passer un tableau :

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
```

## Méthodes d'aide

L'un des principaux avantages de `SimplePdo` par rapport à `PdoWrapper` est l'ajout de méthodes d'aide pratiques pour les opérations courantes sur les bases de données.

### `insert()`

`function insert(string $table, array $data): string`

Insérez une ou plusieurs lignes et retournez le dernier ID inséré.

**Insertion unique :**

```php
$id = Flight::db()->insert('users', [
    'name' => 'John',
    'email' => 'john@example.com'
]);
```

**Insertion en masse :**

```php
$id = Flight::db()->insert('users', [
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com'],
]);
```

### `update()`

`function update(string $table, array $data, string $where, array $whereParams = []): int`

Mettez à jour des lignes et retournez le nombre de lignes affectées :

```php
$affected = Flight::db()->update(
    'users',
    ['name' => 'Jane', 'email' => 'jane@example.com'],
    'id = ?',
    [1]
);
```

> **NOTE**
>
> Le `rowCount()` de SQLite retourne le nombre de lignes où les données ont réellement changé. Si vous mettez à jour une ligne avec les mêmes valeurs qu'elle a déjà, `rowCount()` retournera 0. Cela diffère du comportement de MySQL lors de l'utilisation de `PDO::MYSQL_ATTR_FOUND_ROWS`.

### `delete()`

`function delete(string $table, string $where, array $whereParams = []): int`

Supprimez des lignes et retournez le nombre de lignes supprimées :

```php
$deleted = Flight::db()->delete('users', 'id = ?', [1]);
```

### `transaction()`

`function transaction(callable $callback): mixed`

Exécutez un rappel dans une transaction. La transaction s'engage automatiquement en cas de succès ou se déroule en cas d'erreur :

```php
$result = Flight::db()->transaction(function($db) {
    $db->insert('users', ['name' => 'John']);
    $db->insert('logs', ['action' => 'user_created']);
    return $db->lastInsertId();
});
```

Si une exception est levée dans le rappel, la transaction est automatiquement déroulée et l'exception est relancée.

## Utilisation avancée

### Journalisation des requêtes & APM

Si vous voulez suivre les performances des requêtes, activez le suivi APM lors de l'enregistrement :

```php
Flight::register('db', \flight\database\SimplePdo::class, [
    'mysql:host=localhost;dbname=cool_db_name',
    'user',
    'pass',
    [/* options PDO */],
    [
        'trackApmQueries' => true,
        'maxQueryMetrics' => 1000
    ]
]);
```

Après avoir exécuté des requêtes, vous pouvez les journaliser manuellement, mais l'APM les journalisera automatiquement si activé :

```php
Flight::db()->logQueries();
```

Cela déclenchera un événement (`flight.db.queries`) avec les métriques de connexion et de requête, que vous pouvez écouter en utilisant le système d'événements de Flight.

### Exemple complet

```php
Flight::route('/users', function () {
    // Obtenir tous les utilisateurs
    $users = Flight::db()->fetchAll('SELECT * FROM users');

    // Diffuser tous les utilisateurs
    $statement = Flight::db()->runQuery('SELECT * FROM users');
    while ($user = $statement->fetch()) {
        echo $user['name'];
    }

    // Obtenir un seul utilisateur
    $user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

    // Obtenir une seule valeur
    $count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

    // Obtenir une seule colonne
    $ids = Flight::db()->fetchColumn('SELECT id FROM users');

    // Obtenir des paires clé-valeur
    $userNames = Flight::db()->fetchPairs('SELECT id, name FROM users');

    // Syntaxe spéciale IN()
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);

    // Insérer un nouvel utilisateur
    $id = Flight::db()->insert('users', [
        'name' => 'Bob',
        'email' => 'bob@example.com'
    ]);

    // Insertion en masse d'utilisateurs
    Flight::db()->insert('users', [
        ['name' => 'Bob', 'email' => 'bob@example.com'],
        ['name' => 'Jane', 'email' => 'jane@example.com']
    ]);

    // Mettre à jour un utilisateur
    $affected = Flight::db()->update('users', ['name' => 'Bob'], 'id = ?', [123]);

    // Supprimer un utilisateur
    $deleted = Flight::db()->delete('users', 'id = ?', [123]);

    // Utiliser une transaction
    $result = Flight::db()->transaction(function($db) {
        $db->insert('users', ['name' => 'John', 'email' => 'john@example.com']);
        $db->insert('audit_log', ['action' => 'user_created']);
        return $db->lastInsertId();
    });
});
```

## Migration depuis PdoWrapper

Si vous utilisez actuellement `PdoWrapper`, la migration vers `SimplePdo` est simple :

1. **Mettez à jour votre enregistrement :**
   ```php
   // Ancien
   Flight::register('db', \flight\database\PdoWrapper::class, [ /* ... */ ]);
   
   // Nouveau
   Flight::register('db', \flight\database\SimplePdo::class, [ /* ... */ ]);
   ```

2. **Toutes les méthodes existantes de `PdoWrapper` fonctionnent dans `SimplePdo`** - Il n'y a pas de changements cassants. Votre code existant continuera à fonctionner.

3. **Utilisez optionnellement les nouvelles méthodes d'aide** - Commencez à utiliser `insert()`, `update()`, `delete()`, et `transaction()` pour simplifier votre code.

## Voir aussi

- [Collections](/learn/collections) - Apprenez comment utiliser la classe Collection pour un accès facile aux données.
- [PdoWrapper](/learn/pdo-wrapper) - La classe d'aide PDO legacy (dépréciée).

## Dépannage

- Si vous obtenez une erreur concernant la connexion à la base de données, vérifiez votre DSN, nom d'utilisateur, mot de passe et options.
- Toutes les lignes sont retournées sous forme de Collections — si vous avez besoin d'un tableau simple, utilisez `$collection->getData()`.
- Pour les requêtes `IN (?)`, assurez-vous de passer un tableau.
- Si vous rencontrez des problèmes de mémoire avec la journalisation des requêtes dans des processus longs, ajustez l'option `maxQueryMetrics`.

## Journal des modifications

- v3.18.0 - Première publication de SimplePdo avec des méthodes d'aide pour insert, update, delete, et transactions.