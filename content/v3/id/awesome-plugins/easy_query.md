# EasyQuery

[knifelemon/easy-query](https://github.com/knifelemon/EasyQueryBuilder) adalah pembuat kueri SQL yang ringan dan fluent yang menghasilkan SQL dan parameter untuk prepared statement. Bekerja dengan [SimplePdo](/learn/simple-pdo).

## Fitur

- 🔗 **API Fluent** - Metode berantai untuk konstruksi kueri yang mudah dibaca
- 🛡️ **Perlindungan SQL Injection** - Pengikatan parameter otomatis dengan prepared statement
- 🔧 **Dukungan Raw SQL** - Sisipkan ekspresi SQL langsung dengan `raw()`
- 📝 **Berbagai Tipe Kueri** - SELECT, INSERT, UPDATE, DELETE, COUNT
- 🔀 **Dukungan JOIN** - INNER, LEFT, RIGHT join dengan alias
- 🎯 **Kondisi Lanjutan** - LIKE, IN, NOT IN, BETWEEN, operator perbandingan
- 🌐 **Database Agnostic** - Mengembalikan SQL + params, gunakan dengan koneksi DB apapun
- 🪶 **Ringan** - Footprint minimal tanpa dependensi

## Instalasi

```bash
composer require knifelemon/easy-query
```

## Mulai Cepat

```php
use KnifeLemon\EasyQuery\Builder;

$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC')
    ->limit(10)
    ->build();

// Gunakan dengan SimplePdo Flight
$users = Flight::db()->fetchAll($q['sql'], $q['params']);
```

## Memahami build()

Metode `build()` mengembalikan array dengan `sql` dan `params`. Pemisahan ini menjaga database Anda aman dengan menggunakan prepared statement.

```php
$q = Builder::table('users')
    ->where(['email' => 'user@example.com'])
    ->build();

// Mengembalikan:
// [
//     'sql' => 'SELECT * FROM users WHERE email = ?',
//     'params' => ['user@example.com']
// ]
```

---

## Tipe Kueri

### SELECT

```php
// Pilih semua kolom
$q = Builder::table('users')->build();
// SELECT * FROM users

// Pilih kolom tertentu
$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->build();
// SELECT id, name, email FROM users

// Dengan alias tabel
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

## Kondisi WHERE

### Kesetaraan Sederhana

```php
$q = Builder::table('users')
    ->where(['id' => 123, 'status' => 'active'])
    ->build();
// WHERE id = ? AND status = ?
```

### Operator Perbandingan

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

### Kondisi OR

Gunakan `orWhere()` untuk menambahkan kondisi yang dikelompokkan dengan OR:

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

### Multiple JOIN

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

## Pengurutan, Pengelompokan, dan Limit

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

### LIMIT dan OFFSET

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

## Ekspresi Raw SQL

Gunakan `raw()` ketika Anda membutuhkan fungsi SQL atau ekspresi yang tidak boleh diperlakukan sebagai parameter terikat.

### Raw Dasar

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

### Raw dengan Parameter Terikat

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

### Raw di WHERE (Subquery)

```php
$q = Builder::table('products')
    ->where([
        'price' => ['>', Builder::raw('(SELECT AVG(price) FROM products)')]
    ])
    ->build();
// WHERE price > (SELECT AVG(price) FROM products)
```

### Identifier Aman untuk Input Pengguna

Ketika nama kolom berasal dari input pengguna, gunakan `safeIdentifier()` untuk mencegah SQL injection:

```php
$sortColumn = $_GET['sort'];  // contoh: 'created_at'
$safeColumn = Builder::safeIdentifier($sortColumn);

$q = Builder::table('users')
    ->orderBy($safeColumn . ' DESC')
    ->build();

// Jika pengguna mencoba: "name; DROP TABLE users--"
// Melempar InvalidArgumentException
```

### rawSafe untuk Nama Kolom dari Pengguna

```php
$userColumn = $_GET['aggregate_column'];

$q = Builder::table('orders')
    ->select([
        Builder::rawSafe('SUM({col})', ['col' => $userColumn])->value . ' AS total'
    ])
    ->build();
// Memvalidasi nama kolom, melempar exception jika tidak valid
```

> **Peringatan:** Jangan pernah menggabungkan input pengguna langsung ke `raw()`. Selalu gunakan parameter terikat atau `safeIdentifier()`.

---

## Penggunaan Ulang Query Builder

### Metode Clear

Hapus bagian tertentu untuk menggunakan ulang builder:

```php
$query = Builder::table('users')
    ->select(['id', 'name'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// Kueri pertama
$q1 = $query->limit(10)->build();

// Hapus dan gunakan ulang
$query->clearWhere()->clearLimit();

// Kueri kedua dengan kondisi berbeda
$q2 = $query
    ->where(['status' => 'pending'])
    ->limit(5)
    ->build();
```

### Metode Clear yang Tersedia

| Metode | Deskripsi |
|--------|-----------|
| `clearWhere()` | Hapus kondisi WHERE dan parameter |
| `clearSelect()` | Reset kolom SELECT ke default '*' |
| `clearJoin()` | Hapus semua klausa JOIN |
| `clearGroupBy()` | Hapus klausa GROUP BY |
| `clearOrderBy()` | Hapus klausa ORDER BY |
| `clearLimit()` | Hapus LIMIT dan OFFSET |
| `clearAll()` | Reset builder ke keadaan awal |

### Contoh Paginasi

```php
$baseQuery = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// Dapatkan jumlah total
$countQuery = clone $baseQuery;
$countResult = $countQuery->clearSelect()->count()->build();
$total = Flight::db()->fetchField($countResult['sql'], $countResult['params']);

// Dapatkan hasil terpaginasi
$page = 1;
$perPage = 20;
$listResult = $baseQuery->limit($perPage, ($page - 1) * $perPage)->build();
$users = Flight::db()->fetchAll($listResult['sql'], $listResult['params']);
```

---

## Pembangunan Kueri Dinamis

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

## Contoh FlightPHP Lengkap

```php
use KnifeLemon\EasyQuery\Builder;

// Daftar pengguna dengan paginasi
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

// Buat pengguna
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

// Perbarui pengguna
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

// Hapus pengguna
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

## Referensi API

### Metode Statis

| Metode | Deskripsi |
|--------|-----------|
| `Builder::table(string $table)` | Buat instance builder baru untuk tabel |
| `Builder::raw(string $sql, array $bindings = [])` | Buat ekspresi SQL mentah |
| `Builder::rawSafe(string $expr, array $identifiers, array $bindings = [])` | Ekspresi raw dengan substitusi identifier yang aman |
| `Builder::safeIdentifier(string $identifier)` | Validasi dan kembalikan nama kolom/tabel yang aman |

### Metode Instance

| Metode | Deskripsi |
|--------|-----------|
| `alias(string $alias)` | Set alias tabel |
| `select(string\|array $columns)` | Set kolom yang akan dipilih (default: '*') |
| `where(array $conditions)` | Tambah kondisi WHERE (AND) |
| `orWhere(array $conditions)` | Tambah kondisi OR WHERE |
| `join(string $table, string $condition, string $alias, string $type)` | Tambah klausa JOIN |
| `innerJoin(string $table, string $condition, string $alias)` | Tambah INNER JOIN |
| `leftJoin(string $table, string $condition, string $alias)` | Tambah LEFT JOIN |
| `groupBy(string $groupBy)` | Tambah klausa GROUP BY |
| `orderBy(string $orderBy)` | Tambah klausa ORDER BY |
| `limit(int $limit, int $offset = 0)` | Tambah LIMIT dan OFFSET |
| `count(string $column = '*')` | Set kueri ke COUNT |
| `insert(array $data)` | Set kueri ke INSERT |
| `update(array $data)` | Set kueri ke UPDATE |
| `delete()` | Set kueri ke DELETE |
| `build()` | Bangun dan kembalikan `['sql' => ..., 'params' => ...]` |
| `get()` | Alias untuk `build()` |

---

## Integrasi Tracy Debugger

EasyQuery secara otomatis terintegrasi dengan Tracy Debugger jika terinstal. Tidak perlu pengaturan!

```bash
composer require tracy/tracy
```

```php
use Tracy\Debugger;

Debugger::enable();

// Semua kueri secara otomatis dicatat ke panel Tracy
$q = Builder::table('users')->where(['status' => 'active'])->build();
```

Panel Tracy menampilkan:
- Total kueri dan rincian berdasarkan tipe
- SQL yang dihasilkan (syntax highlighting)
- Array parameter
- Detail kueri (tabel, where, join, dll.)

Untuk dokumentasi lengkap, kunjungi [repositori GitHub](https://github.com/knifelemon/EasyQueryBuilder).
