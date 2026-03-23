# EasyQuery

[knifelemon/easy-query](https://github.com/knifelemon/EasyQueryBuilder) ir viegls, plūstošs SQL vaicājumu veidotājs, kas ģenerē SQL un parametrus sagatavotiem vaicājumiem. Darbojas ar [SimplePdo](/learn/simple-pdo).

## Funkcijas

- 🔗 **Plūstoša API** - Ķēžu metodes lasāmu vaicājumu veidošanai
- 🛡️ **SQL Injekciju Aizsardzība** - Automātiska parametru saistīšana ar sagatavotiem vaicājumiem
- 🔧 **Neapstrādāta SQL Atbalsts** - Ievietojiet neapstrādātas SQL izteiksmes ar `raw()`
- 📝 **Vairāki Vaicājumu Veidi** - SELECT, INSERT, UPDATE, DELETE, COUNT
- 🔀 **JOIN Atbalsts** - Iekšējie, kreisās un labās savienojumi ar segvārdiem
- 🎯 **Uzlaboti Nosacījumi** - LIKE, IN, NOT IN, BETWEEN, salīdzinājuma operatori
- 🌐 **Datu Bāzes Neatkarīgs** - Atgriež SQL + parametrus, izmantojiet ar jebkuru DB savienojumu
- 🪶 **Viegls** - Minimāla pēda bez atkarībām

## Instalēšana

```bash
composer require knifelemon/easy-query
```

## Ātrais Sākums

```php
use KnifeLemon\EasyQuery\Builder;

$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC')
    ->limit(10)
    ->build();

// Izmantojiet ar Flight's SimplePdo
$users = Flight::db()->fetchAll($q['sql'], $q['params']);
```

## Saprašana build()

`build()` metode atgriež masīvu ar `sql` un `params`. Šī atdalīšana uztur jūsu datu bāzi drošu, izmantojot sagatavotus vaicājumus.

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

## Vaicājumu Veidi

### SELECT

```php
// Izvēlieties visas kolonnas
$q = Builder::table('users')->build();
// SELECT * FROM users

// Izvēlieties specifiskas kolonnas
$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->build();
// SELECT id, name, email FROM users

// Ar tabulas segvārdu
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

## WHERE Nosacījumi

### Vienkārša Vienlīdzība

```php
$q = Builder::table('users')
    ->where(['id' => 123, 'status' => 'active'])
    ->build();
// WHERE id = ? AND status = ?
```

### Salīdzinājuma Operatori

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

### OR Nosacījumi

Izmantojiet `orWhere()`, lai pievienotu OR grupētus nosacījumus:

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

### Iekšējais JOIN

```php
$q = Builder::table('users')
    ->alias('u')
    ->select(['u.id', 'u.name', 'p.title'])
    ->innerJoin('posts', 'u.id = p.user_id', 'p')
    ->build();
// SELECT u.id, u.name, p.title FROM users AS u INNER JOIN posts AS p ON u.id = p.user_id
```

### Kreisais JOIN

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

## Sakārtošana, Grupēšana un Ierobežojumi

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

## Neapstrādātas SQL Izteiksmes

Izmantojiet `raw()`, kad nepieciešamas SQL funkcijas vai izteiksmes, kas nedrīkst tikt apstrādātas kā saistīti parametri.

### Pamata Neapstrādāts

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

### Neapstrādāts ar Saistītiem Parametriem

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

### Neapstrādāts WHERE (Apakšvaicājums)

```php
$q = Builder::table('products')
    ->where([
        'price' => ['>', Builder::raw('(SELECT AVG(price) FROM products)')]
    ])
    ->build();
// WHERE price > (SELECT AVG(price) FROM products)
```

### Droši Identifikatori Lietotāja Ievadei

Kad kolonnu nosaukumi nāk no lietotāja ievades, izmantojiet `safeIdentifier()`, lai novērstu SQL injekcijas:

```php
$sortColumn = $_GET['sort'];  // piem., 'created_at'
$safeColumn = Builder::safeIdentifier($sortColumn);

$q = Builder::table('users')
    ->orderBy($safeColumn . ' DESC')
    ->build();

// Ja lietotājs mēģina: "name; DROP TABLE users--"
// Izmet InvalidArgumentException
```

### rawSafe Lietotāja Norādītiem Kolonnu Nosaukumiem

```php
$userColumn = $_GET['aggregate_column'];

$q = Builder::table('orders')
    ->select([
        Builder::rawSafe('SUM({col})', ['col' => $userColumn])->value . ' AS total'
    ])
    ->build();
// Validē kolonnu nosaukumu, izmet izņēmumu, ja nederīgs
```

> **Brīdinājums:** Nekad neiekļaujiet lietotāja ievadi tieši `raw()`. Vienmēr izmantojiet saistītus parametrus vai `safeIdentifier()`.

---

## Vaicājuma Veidotāja atkārtota Izmantošana

### Notīrīšanas Metodes

Notīriet specifiskas daļas, lai atkārtoti izmantotu veidotāju:

```php
$query = Builder::table('users')
    ->select(['id', 'name'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// Pirmais vaicājums
$q1 = $query->limit(10)->build();

// Notīriet un atkārtoti izmantojiet
$query->clearWhere()->clearLimit();

// Otrais vaicājums ar atšķirīgiem nosacījumiem
$q2 = $query
    ->where(['status' => 'pending'])
    ->limit(5)
    ->build();
```

### Pieejamās Notīrīšanas Metodes

| Metode | Apraksts |
|--------|----------|
| `clearWhere()` | Notīra WHERE nosacījumus un parametrus |
| `clearSelect()` | Atstata SELECT kolonnas uz noklusējuma '*' |
| `clearJoin()` | Notīra visus JOIN pantus |
| `clearGroupBy()` | Notīra GROUP BY pantu |
| `clearOrderBy()` | Notīra ORDER BY pantu |
| `clearLimit()` | Notīra LIMIT un OFFSET |
| `clearAll()` | Atstata veidotāju uz sākotnējo stāvokli |

### Piemērs ar Lapināšanu

```php
$baseQuery = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// Iegūstiet kopējo skaitu
$countQuery = clone $baseQuery;
$countResult = $countQuery->clearSelect()->count()->build();
$total = Flight::db()->fetchField($countResult['sql'], $countResult['params']);

// Iegūstiet lapinātos rezultātus
$page = 1;
$perPage = 20;
$listResult = $baseQuery->limit($perPage, ($page - 1) * $perPage)->build();
$users = Flight::db()->fetchAll($listResult['sql'], $listResult['params']);
```

---

## Dinamiska Vaicājuma Veidošana

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

## Pilns FlightPHP Piemērs

```php
use KnifeLemon\EasyQuery\Builder;

// Lietotāju saraksts ar lapināšanu
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

// Lietotāja izveide
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

// Lietotāja atjaunināšana
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

// Lietotāja dzēšana
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

## API Atsauce

### Statiskās Metodes

| Metode | Apraksts |
|--------|----------|
| `Builder::table(string $table)` | Izveido jaunu veidotāja instanci tabulai |
| `Builder::raw(string $sql, array $bindings = [])` | Izveido neapstrādātu SQL izteiksmi |
| `Builder::rawSafe(string $expr, array $identifiers, array $bindings = [])` | Neapstrādāta izteiksme ar drošu identifikatora aizstāšanu |
| `Builder::safeIdentifier(string $identifier)` | Validē un atgriež drošu kolonnas/tabula nosaukumu |

### Instanču Metodes

| Metode | Apraksts |
|--------|----------|
| `alias(string $alias)` | Iestata tabulas segvārdu |
| `select(string\|array $columns)` | Iestata kolonnas izvēlei (noklusējums: '*') |
| `where(array $conditions)` | Pievieno WHERE nosacījumus (AND) |
| `orWhere(array $conditions)` | Pievieno OR WHERE nosacījumus |
| `join(string $table, string $condition, string $alias, string $type)` | Pievieno JOIN pantu |
| `innerJoin(string $table, string $condition, string $alias)` | Pievieno Iekšējo JOIN |
| `leftJoin(string $table, string $condition, string $alias)` | Pievieno Kreisais JOIN |
| `groupBy(string $groupBy)` | Pievieno GROUP BY pantu |
| `orderBy(string $orderBy)` | Pievieno ORDER BY pantu |
| `limit(int $limit, int $offset = 0)` | Pievieno LIMIT un OFFSET |
| `count(string $column = '*')` | Iestata vaicājumu uz COUNT |
| `insert(array $data)` | Iestata vaicājumu uz INSERT |
| `update(array $data)` | Iestata vaicājumu uz UPDATE |
| `delete()` | Iestata vaicājumu uz DELETE |
| `build()` | Veido un atgriež `['sql' => ..., 'params' => ...]` |
| `get()` | Aliasam `build()` |

---

## Tracy Debugger Integrācija

EasyQuery automātiski integrējas ar Tracy Debugger, ja instalēts. Nav nepieciešama iestatīšana!

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
- Kopējo vaicājumu skaitu un sadalījumu pēc veida
- Ģenerēto SQL (sinakses izcelts)
- Parametru masīvu
- Vaicājuma detaļas (tabula, where, joins utt.)

Pilnai dokumentācijai apmeklējiet [GitHub repozitoriju](https://github.com/knifelemon/EasyQueryBuilder).