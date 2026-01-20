# EasyQuery

[knifelemon/easy-query](https://github.com/knifelemon/EasyQueryBuilder) est un constructeur de requêtes SQL léger et fluide qui génère du SQL et des paramètres pour les requêtes préparées. Fonctionne avec [SimplePdo](/learn/simple-pdo).

## Fonctionnalités

- 🔗 **API Fluide** - Méthodes chaînées pour une construction de requêtes lisible
- 🛡️ **Protection contre l'Injection SQL** - Liaison automatique des paramètres avec les requêtes préparées
- 🔧 **Support Raw SQL** - Insérer des expressions SQL directement avec `raw()`
- 📝 **Types de Requêtes Multiples** - SELECT, INSERT, UPDATE, DELETE, COUNT
- 🔀 **Support JOIN** - INNER, LEFT, RIGHT joins avec alias
- 🎯 **Conditions Avancées** - LIKE, IN, NOT IN, BETWEEN, opérateurs de comparaison
- 🌐 **Agnostique de Base de Données** - Retourne SQL + params, utilisable avec n'importe quelle connexion DB
- 🪶 **Léger** - Empreinte minimale sans dépendances

## Installation

```bash
composer require knifelemon/easy-query
```

## Démarrage Rapide

```php
use KnifeLemon\EasyQuery\Builder;

$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC')
    ->limit(10)
    ->build();

// Utiliser avec SimplePdo de Flight
$users = Flight::db()->fetchAll($q['sql'], $q['params']);
```

## Comprendre build()

La méthode `build()` retourne un tableau avec `sql` et `params`. Cette séparation protège votre base de données en utilisant des requêtes préparées.

```php
$q = Builder::table('users')
    ->where(['email' => 'user@example.com'])
    ->build();

// Retourne:
// [
//     'sql' => 'SELECT * FROM users WHERE email = ?',
//     'params' => ['user@example.com']
// ]
```

---

## Types de Requêtes

### SELECT

```php
// Sélectionner toutes les colonnes
$q = Builder::table('users')->build();
// SELECT * FROM users

// Sélectionner des colonnes spécifiques
$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->build();
// SELECT id, name, email FROM users

// Avec alias de table
$q = Builder::table('users')
    ->alias('u')
    ->select(['u.id', 'u.name'])
    ->build();
// SELECT u.id, u.name FROM users AS u
```

### INSERT

```php
$q = Builder::table('users')
    ->insert([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'status' => 'active'
    ])
    ->build();
// INSERT INTO users SET name = ?, email = ?, status = ?

Flight::db()->runQuery($q['sql'], $q['params']);
$userId = Flight::db()->lastInsertId();
```

### UPDATE

```php
$q = Builder::table('users')
    ->update(['status' => 'inactive', 'updated_at' => date('Y-m-d H:i:s')])
    ->where(['id' => 123])
    ->build();
// UPDATE users SET status = ?, updated_at = ? WHERE id = ?

Flight::db()->runQuery($q['sql'], $q['params']);
```

### DELETE

```php
$q = Builder::table('users')
    ->delete()
    ->where(['id' => 123])
    ->build();
// DELETE FROM users WHERE id = ?

Flight::db()->runQuery($q['sql'], $q['params']);
```

### COUNT

```php
$q = Builder::table('users')
    ->count()
    ->where(['status' => 'active'])
    ->build();
// SELECT COUNT(*) AS cnt FROM users WHERE status = ?

$count = Flight::db()->fetchField($q['sql'], $q['params']);
```

---

## Conditions WHERE

### Égalité Simple

```php
$q = Builder::table('users')
    ->where(['id' => 123, 'status' => 'active'])
    ->build();
// WHERE id = ? AND status = ?
```

### Opérateurs de Comparaison

```php
$q = Builder::table('users')
    ->where([
        'age' => ['>=', 18],
        'score' => ['<', 100],
        'name' => ['!=', 'admin']
    ])
    ->build();
// WHERE age >= ? AND score < ? AND name != ?
```

### LIKE

```php
$q = Builder::table('users')
    ->where(['name' => ['LIKE', '%john%']])
    ->build();
// WHERE name LIKE ?
```

### IN / NOT IN

```php
// IN
$q = Builder::table('users')
    ->where(['id' => ['IN', [1, 2, 3, 4, 5]]])
    ->build();
// WHERE id IN (?, ?, ?, ?, ?)

// NOT IN
$q = Builder::table('users')
    ->where(['status' => ['NOT IN', ['banned', 'deleted']]])
    ->build();
// WHERE status NOT IN (?, ?)
```

### BETWEEN

```php
$q = Builder::table('products')
    ->where(['price' => ['BETWEEN', [100, 500]]])
    ->build();
// WHERE price BETWEEN ? AND ?
```

### Conditions OR

Utilisez `orWhere()` pour ajouter des conditions groupées avec OR:

```php
$q = Builder::table('users')
    ->where(['status' => 'active'])
    ->orWhere([
        'role' => 'admin',
        'permissions' => ['LIKE', '%manage%']
    ])
    ->build();
// WHERE status = ? AND (role = ? OR permissions LIKE ?)
```

---

## JOIN

### INNER JOIN

```php
$q = Builder::table('users')
    ->alias('u')
    ->select(['u.id', 'u.name', 'p.title'])
    ->innerJoin('posts', 'u.id = p.user_id', 'p')
    ->build();
// SELECT u.id, u.name, p.title FROM users AS u INNER JOIN posts AS p ON u.id = p.user_id
```

### LEFT JOIN

```php
$q = Builder::table('users')
    ->alias('u')
    ->select(['u.name', 'o.total'])
    ->leftJoin('orders', 'u.id = o.user_id', 'o')
    ->build();
// ... LEFT JOIN orders AS o ON u.id = o.user_id
```

### JOINs Multiples

```php
$q = Builder::table('orders')
    ->alias('o')
    ->select(['o.id', 'u.name AS customer', 'p.title AS product'])
    ->innerJoin('users', 'o.user_id = u.id', 'u')
    ->leftJoin('order_items', 'o.id = oi.order_id', 'oi')
    ->leftJoin('products', 'oi.product_id = p.id', 'p')
    ->where(['o.status' => 'completed'])
    ->build();
```

---

## Tri, Groupement et Limites

### ORDER BY

```php
$q = Builder::table('users')
    ->orderBy('created_at DESC')
    ->build();
// ORDER BY created_at DESC
```

### GROUP BY

```php
$q = Builder::table('orders')
    ->select(['user_id', 'COUNT(*) as order_count'])
    ->groupBy('user_id')
    ->build();
// SELECT user_id, COUNT(*) as order_count FROM orders GROUP BY user_id
```

### LIMIT et OFFSET

```php
$q = Builder::table('users')
    ->limit(10)
    ->build();
// LIMIT 10

$q = Builder::table('users')
    ->limit(10, 20)  // limit, offset
    ->build();
// LIMIT 10 OFFSET 20
```

---

## Expressions Raw SQL

Utilisez `raw()` quand vous avez besoin de fonctions SQL ou d'expressions qui ne doivent pas être traitées comme des paramètres liés.

### Raw Basique

```php
$q = Builder::table('users')
    ->update([
        'login_count' => Builder::raw('login_count + 1'),
        'updated_at' => Builder::raw('NOW()')
    ])
    ->where(['id' => 123])
    ->build();
// SET login_count = login_count + 1, updated_at = NOW()
```

### Raw avec Paramètres Liés

```php
$q = Builder::table('orders')
    ->update([
        'total' => Builder::raw('COALESCE(subtotal, ?) + ?', [0, 10])
    ])
    ->where(['id' => 1])
    ->build();
// SET total = COALESCE(subtotal, ?) + ?
// params: [0, 10, 1]
```

### Raw dans WHERE (Sous-requête)

```php
$q = Builder::table('products')
    ->where([
        'price' => ['>', Builder::raw('(SELECT AVG(price) FROM products)')]
    ])
    ->build();
// WHERE price > (SELECT AVG(price) FROM products)
```

### Identifiants Sécurisés pour les Entrées Utilisateur

Quand les noms de colonnes viennent d'entrées utilisateur, utilisez `safeIdentifier()` pour prévenir l'injection SQL:

```php
$sortColumn = $_GET['sort'];  // ex: 'created_at'
$safeColumn = Builder::safeIdentifier($sortColumn);

$q = Builder::table('users')
    ->orderBy($safeColumn . ' DESC')
    ->build();

// Si l'utilisateur essaie: "name; DROP TABLE users--"
// Lance InvalidArgumentException
```

### rawSafe pour les Noms de Colonnes Utilisateur

```php
$userColumn = $_GET['aggregate_column'];

$q = Builder::table('orders')
    ->select([
        Builder::rawSafe('SUM({col})', ['col' => $userColumn])->value . ' AS total'
    ])
    ->build();
// Valide le nom de colonne, lance une exception si invalide
```

> **Avertissement:** Ne concaténez jamais directement les entrées utilisateur dans `raw()`. Utilisez toujours des paramètres liés ou `safeIdentifier()`.

---

## Réutilisation du Query Builder

### Méthodes Clear

Effacez des parties spécifiques pour réutiliser le builder:

```php
$query = Builder::table('users')
    ->select(['id', 'name'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// Première requête
$q1 = $query->limit(10)->build();

// Effacer et réutiliser
$query->clearWhere()->clearLimit();

// Deuxième requête avec des conditions différentes
$q2 = $query
    ->where(['status' => 'pending'])
    ->limit(5)
    ->build();
```

### Méthodes Clear Disponibles

| Méthode | Description |
|---------|-------------|
| `clearWhere()` | Effacer les conditions WHERE et les paramètres |
| `clearSelect()` | Réinitialiser les colonnes SELECT à '*' par défaut |
| `clearJoin()` | Effacer toutes les clauses JOIN |
| `clearGroupBy()` | Effacer la clause GROUP BY |
| `clearOrderBy()` | Effacer la clause ORDER BY |
| `clearLimit()` | Effacer LIMIT et OFFSET |
| `clearAll()` | Réinitialiser le builder à l'état initial |

### Exemple de Pagination

```php
$baseQuery = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// Obtenir le nombre total
$countQuery = clone $baseQuery;
$countResult = $countQuery->clearSelect()->count()->build();
$total = Flight::db()->fetchField($countResult['sql'], $countResult['params']);

// Obtenir les résultats paginés
$page = 1;
$perPage = 20;
$listResult = $baseQuery->limit($perPage, ($page - 1) * $perPage)->build();
$users = Flight::db()->fetchAll($listResult['sql'], $listResult['params']);
```

---

## Construction Dynamique de Requêtes

```php
$query = Builder::table('products')->alias('p');

if (!empty($categoryId)) {
    $query->where(['p.category_id' => $categoryId]);
}

if (!empty($minPrice)) {
    $query->where(['p.price' => ['>=', $minPrice]]);
}

if (!empty($maxPrice)) {
    $query->where(['p.price' => ['<=', $maxPrice]]);
}

if (!empty($searchTerm)) {
    $query->where(['p.name' => ['LIKE', "%{$searchTerm}%"]]);
}

$result = $query->orderBy('p.created_at DESC')->limit(20)->build();
$products = Flight::db()->fetchAll($result['sql'], $result['params']);
```

---

## Exemple Complet FlightPHP

```php
use KnifeLemon\EasyQuery\Builder;

// Lister les utilisateurs avec pagination
Flight::route('GET /users', function() {
    $page = (int) (Flight::request()->query['page'] ?? 1);
    $perPage = 20;

    $q = Builder::table('users')
        ->select(['id', 'name', 'email', 'created_at'])
        ->where(['status' => 'active'])
        ->orderBy('created_at DESC')
        ->limit($perPage, ($page - 1) * $perPage)
        ->build();
    
    $users = Flight::db()->fetchAll($q['sql'], $q['params']);
    Flight::json(['users' => $users, 'page' => $page]);
});

// Créer un utilisateur
Flight::route('POST /users', function() {
    $data = Flight::request()->data;
    
    $q = Builder::table('users')
        ->insert([
            'name' => $data->name,
            'email' => $data->email,
            'created_at' => Builder::raw('NOW()')
        ])
        ->build();
    
    Flight::db()->runQuery($q['sql'], $q['params']);
    Flight::json(['id' => Flight::db()->lastInsertId()]);
});

// Mettre à jour un utilisateur
Flight::route('PUT /users/@id', function($id) {
    $data = Flight::request()->data;
    
    $q = Builder::table('users')
        ->update([
            'name' => $data->name,
            'email' => $data->email,
            'updated_at' => Builder::raw('NOW()')
        ])
        ->where(['id' => $id])
        ->build();
    
    Flight::db()->runQuery($q['sql'], $q['params']);
    Flight::json(['success' => true]);
});

// Supprimer un utilisateur
Flight::route('DELETE /users/@id', function($id) {
    $q = Builder::table('users')
        ->delete()
        ->where(['id' => $id])
        ->build();
    
    Flight::db()->runQuery($q['sql'], $q['params']);
    Flight::json(['success' => true]);
});
```

---

## Référence API

### Méthodes Statiques

| Méthode | Description |
|---------|-------------|
| `Builder::table(string $table)` | Créer une nouvelle instance du builder pour la table |
| `Builder::raw(string $sql, array $bindings = [])` | Créer une expression SQL brute |
| `Builder::rawSafe(string $expr, array $identifiers, array $bindings = [])` | Expression raw avec substitution sécurisée des identifiants |
| `Builder::safeIdentifier(string $identifier)` | Valider et retourner un nom de colonne/table sécurisé |

### Méthodes d'Instance

| Méthode | Description |
|---------|-------------|
| `alias(string $alias)` | Définir l'alias de table |
| `select(string\|array $columns)` | Définir les colonnes à sélectionner (défaut: '*') |
| `where(array $conditions)` | Ajouter des conditions WHERE (AND) |
| `orWhere(array $conditions)` | Ajouter des conditions OR WHERE |
| `join(string $table, string $condition, string $alias, string $type)` | Ajouter une clause JOIN |
| `innerJoin(string $table, string $condition, string $alias)` | Ajouter INNER JOIN |
| `leftJoin(string $table, string $condition, string $alias)` | Ajouter LEFT JOIN |
| `groupBy(string $groupBy)` | Ajouter la clause GROUP BY |
| `orderBy(string $orderBy)` | Ajouter la clause ORDER BY |
| `limit(int $limit, int $offset = 0)` | Ajouter LIMIT et OFFSET |
| `count(string $column = '*')` | Définir la requête en COUNT |
| `insert(array $data)` | Définir la requête en INSERT |
| `update(array $data)` | Définir la requête en UPDATE |
| `delete()` | Définir la requête en DELETE |
| `build()` | Construire et retourner `['sql' => ..., 'params' => ...]` |
| `get()` | Alias de `build()` |

---

## Intégration Tracy Debugger

EasyQuery s'intègre automatiquement avec Tracy Debugger s'il est installé. Aucune configuration nécessaire!

```bash
composer require tracy/tracy
```

```php
use Tracy\Debugger;

Debugger::enable();

// Toutes les requêtes sont automatiquement enregistrées dans le panneau Tracy
$q = Builder::table('users')->where(['status' => 'active'])->build();
```

Le panneau Tracy affiche:
- Total des requêtes et répartition par type
- SQL généré (coloration syntaxique)
- Tableau des paramètres
- Détails de requête (table, where, joins, etc.)

Pour la documentation complète, visitez le [dépôt GitHub](https://github.com/knifelemon/EasyQueryBuilder).
