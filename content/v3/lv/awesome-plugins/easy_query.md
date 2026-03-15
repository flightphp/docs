# EasyQuery

[knifelemon/easy-query](https://github.com/knifelemon/EasyQueryBuilder) ir viegls, plūstošs SQL vaicājumu veidotājs, kas ģenerē SQL un parametrus prepared statements. Strādā ar [SimplePdo](/learn/simple-pdo).

## Iespējas

- 🔗 **Plūstoša API** - Ķēdētas metodes lasāmai vaicājumu veidošanai
- 🛡️ **SQL injekcijas aizsardzība** - Automātiska parametru saistīšana ar prepared statements
- 🔧 **Raw SQL atbalsts** - Ievietojiet SQL izteiksmes tieši ar `raw()`
- 📝 **Vairāki vaicājumu tipi** - SELECT, INSERT, UPDATE, DELETE, COUNT
- 🔀 **JOIN atbalsts** - INNER, LEFT, RIGHT join ar aizstājvārdiem
- 🎯 **Paplašināti nosacījumi** - LIKE, IN, NOT IN, BETWEEN, salīdzināšanas operatori
- 🌐 **Datubāzes neatkarīgs** - Atgriež SQL + params, izmantojiet ar jebkuru DB savienojumu
- 🪶 **Viegls** - Minimāls izmērs bez atkarībām

## Instalācija

```bash
composer require knifelemon/easy-query
```

## Ātrais sākums

```php
use KnifeLemon\EasyQuery\Builder;

$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC')
    ->limit(10)
    ->build();

// Izmantojiet ar Flight SimplePdo
$users = Flight::db()->fetchAll($q['sql'], $q['params']);
```

## Izpratne par build()

`build()` metode atgriež masīvu ar `sql` un `params`. Šī atdalīšana aizsargā jūsu datubāzi, izmantojot prepared statements.

```php
$q = Builder::table('users')
    ->where(['email' => 'user@example.com'])
    ->build();

// Atgriež:
// [
//     'sql' => 'SELECT * FROM users WHERE email = ?',
//     'params' => ['user@example.com']
// ]
```

---

## Vaicājumu tipi

### SELECT

```php
// Atlasīt visas kolonnas
$q = Builder::table('users')->build();
// SELECT * FROM users

// Atlasīt konkrētas kolonnas
$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->build();
// SELECT id, name, email FROM users

// Ar tabulas aizstājvārdu
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

## WHERE nosacījumi

### Vienkārša vienādība

```php
$q = Builder::table('users')
    ->where(['id' => 123, 'status' => 'active'])
    ->build();
// WHERE id = ? AND status = ?
```

### Salīdzināšanas operatori

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

### OR nosacījumi

Izmantojiet `orWhere()`, lai pievienotu OR grupētos nosacījumus:

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

### Vairāki JOIN

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

## Kārtošana, grupēšana un limiti

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

### LIMIT un OFFSET

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

## Raw SQL izteiksmes

Izmantojiet `raw()`, kad nepieciešamas SQL funkcijas vai izteiksmes, kas nedrīkst tikt apstrādātas kā saistītie parametri.

### Pamata Raw

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

### Raw ar saistītiem parametriem

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

### Raw WHERE (apakšvaicājums)

```php
$q = Builder::table('products')
    ->where([
        'price' => ['>', Builder::raw('(SELECT AVG(price) FROM products)')]
    ])
    ->build();
// WHERE price > (SELECT AVG(price) FROM products)
```

### Droši identifikatori lietotāja ievadei

Kad kolonnu nosaukumi nāk no lietotāja ievades, izmantojiet `safeIdentifier()`, lai novērstu SQL injekciju:

```php
$sortColumn = $_GET['sort'];  // piem.: 'created_at'
$safeColumn = Builder::safeIdentifier($sortColumn);

$q = Builder::table('users')
    ->orderBy($safeColumn . ' DESC')
    ->build();

// Ja lietotājs mēģina: "name; DROP TABLE users--"
// Izmet InvalidArgumentException
```

### rawSafe lietotāja kolonnu nosaukumiem

```php
$userColumn = $_GET['aggregate_column'];

$q = Builder::table('orders')
    ->select([
        Builder::rawSafe('SUM({col})', ['col' => $userColumn])->value . ' AS total'
    ])
    ->build();
// Validē kolonnas nosaukumu, izmet izņēmumu, ja nederīgs
```

> **Brīdinājums:** Nekad nekonkatenējiet lietotāja ievadi tieši `raw()`. Vienmēr izmantojiet saistītos parametrus vai `safeIdentifier()`.

---

## Query Builder atkārtota izmantošana

### Clear metodes

Notīriet konkrētas daļas, lai atkārtoti izmantotu builder:

```php
$query = Builder::table('users')
    ->select(['id', 'name'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// Pirmais vaicājums
$q1 = $query->limit(10)->build();

// Notīrīt un izmantot atkārtoti
$query->clearWhere()->clearLimit();

// Otrais vaicājums ar citiem nosacījumiem
$q2 = $query
    ->where(['status' => 'pending'])
    ->limit(5)
    ->build();
```

### Pieejamās Clear metodes

| Metode | Apraksts |
|--------|----------|
| `clearWhere()` | Notīrīt WHERE nosacījumus un parametrus |
| `clearSelect()` | Atiestatīt SELECT kolonnas uz noklusējuma '*' |
| `clearJoin()` | Notīrīt visas JOIN klauzulas |
| `clearGroupBy()` | Notīrīt GROUP BY klauzulu |
| `clearOrderBy()` | Notīrīt ORDER BY klauzulu |
| `clearLimit()` | Notīrīt LIMIT un OFFSET |
| `clearAll()` | Atiestatīt builder sākuma stāvoklī |

### Lapošanas piemērs

```php
$baseQuery = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// Iegūt kopējo skaitu
$countQuery = clone $baseQuery;
$countResult = $countQuery->clearSelect()->count()->build();
$total = Flight::db()->fetchField($countResult['sql'], $countResult['params']);

// Iegūt lapotus rezultātus
$page = 1;
$perPage = 20;
$listResult = $baseQuery->limit($perPage, ($page - 1) * $perPage)->build();
$users = Flight::db()->fetchAll($listResult['sql'], $listResult['params']);
```

---

## Dinamiska vaicājumu veidošana

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

## Pilns FlightPHP piemērs

```php
use KnifeLemon\EasyQuery\Builder;

// Lietotāju saraksts ar lapošanu
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

// Izveidot lietotāju
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

// Atjaunināt lietotāju
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

// Dzēst lietotāju
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

## API atsauce

### Statiskās metodes

| Metode | Apraksts |
|--------|----------|
| `Builder::table(string $table)` | Izveidot jaunu builder instanci tabulai |
| `Builder::raw(string $sql, array $bindings = [])` | Izveidot raw SQL izteiksmi |
| `Builder::rawSafe(string $expr, array $identifiers, array $bindings = [])` | Raw izteiksme ar drošu identifikatoru aizstāšanu |
| `Builder::safeIdentifier(string $identifier)` | Validēt un atgriezt drošu kolonnas/tabulas nosaukumu |

### Instances metodes

| Metode | Apraksts |
|--------|----------|
| `alias(string $alias)` | Iestatīt tabulas aizstājvārdu |
| `select(string\|array $columns)` | Iestatīt atlasāmās kolonnas (noklusējums: '*') |
| `where(array $conditions)` | Pievienot WHERE nosacījumus (AND) |
| `orWhere(array $conditions)` | Pievienot OR WHERE nosacījumus |
| `join(string $table, string $condition, string $alias, string $type)` | Pievienot JOIN klauzulu |
| `innerJoin(string $table, string $condition, string $alias)` | Pievienot INNER JOIN |
| `leftJoin(string $table, string $condition, string $alias)` | Pievienot LEFT JOIN |
| `groupBy(string $groupBy)` | Pievienot GROUP BY klauzulu |
| `orderBy(string $orderBy)` | Pievienot ORDER BY klauzulu |
| `limit(int $limit, int $offset = 0)` | Pievienot LIMIT un OFFSET |
| `count(string $column = '*')` | Iestatīt vaicājumu uz COUNT |
| `insert(array $data)` | Iestatīt vaicājumu uz INSERT |
| `update(array $data)` | Iestatīt vaicājumu uz UPDATE |
| `delete()` | Iestatīt vaicājumu uz DELETE |
| `build()` | Izveidot un atgriezt `['sql' => ..., 'params' => ...]` |
| `get()` | Aizstājvārds `build()` |

---

## Tracy atkļūdotāja integrācija

EasyQuery automātiski integrējas ar Tracy Debugger, ja tas ir instalēts. Nav nepieciešama konfigurācija!

```bash
composer require tracy/tracy
```

```php
use Tracy\Debugger;

Debugger::enable();

// Visi vaicājumi automātiski tiek reģistrēti Tracy panelī
$q = Builder::table('users')->where(['status' => 'active'])->build();
```

Tracy panelis rāda:
- Kopējo vaicājumu skaitu un sadalījumu pa tipiem
- Ģenerēto SQL (sintakses izcelšana)
- Parametru masīvu
- Vaicājuma detaļas (tabula, where, join utt.)

Pilnai dokumentācijai apmeklējiet [GitHub repozitoriju](https://github.com/knifelemon/EasyQueryBuilder).
