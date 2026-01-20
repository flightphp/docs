# EasyQuery

[knifelemon/easy-query](https://github.com/knifelemon/EasyQueryBuilder)は、SQLとパラメータを生成する軽量なFluent SQLクエリビルダーです。[SimplePdo](/learn/simple-pdo)と連携できます。

## 特徴

- 🔗 **Fluent API** - メソッドチェーンで可読性の高いクエリ構築
- 🛡️ **SQLインジェクション防止** - プリペアドステートメントによる自動パラメータバインディング
- 🔧 **Raw SQLサポート** - `raw()`でSQL式を直接挿入
- 📝 **複数のクエリタイプ** - SELECT, INSERT, UPDATE, DELETE, COUNT
- 🔀 **JOINサポート** - INNER, LEFT, RIGHTジョインとエイリアス
- 🎯 **高度な条件** - LIKE, IN, NOT IN, BETWEEN, 比較演算子
- 🌐 **データベース非依存** - SQL + paramsを返し、どのDB接続でも使用可能
- 🪶 **軽量** - 依存関係なしで最小限のフットプリント

## インストール

```bash
composer require knifelemon/easy-query
```

## クイックスタート

```php
use KnifeLemon\EasyQuery\Builder;

$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC')
    ->limit(10)
    ->build();

// FlightのSimplePdoで使用
$users = Flight::db()->fetchAll($q['sql'], $q['params']);
```

## build()の理解

`build()`メソッドは`sql`と`params`を含む配列を返します。この分離により、プリペアドステートメントを使用してデータベースを安全に保護します。

```php
$q = Builder::table('users')
    ->where(['email' => 'user@example.com'])
    ->build();

// 戻り値:
// [
//     'sql' => 'SELECT * FROM users WHERE email = ?',
//     'params' => ['user@example.com']
// ]
```

---

## クエリタイプ

### SELECT

```php
// すべてのカラムを選択
$q = Builder::table('users')->build();
// SELECT * FROM users

// 特定のカラムを選択
$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->build();
// SELECT id, name, email FROM users

// テーブルエイリアスを使用
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

## WHERE条件

### 単純な等価比較

```php
$q = Builder::table('users')
    ->where(['id' => 123, 'status' => 'active'])
    ->build();
// WHERE id = ? AND status = ?
```

### 比較演算子

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

### OR条件

`orWhere()`を使用してORでグループ化された条件を追加します:

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

### 複数JOIN

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

## ソート、グループ化、制限

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

### LIMITとOFFSET

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

## Raw SQL式

バウンドパラメータとして処理すべきでないSQL関数や式が必要な場合は`raw()`を使用します。

### 基本的なRaw

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

### バウンドパラメータ付きRaw

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

### WHEREでのRaw（サブクエリ）

```php
$q = Builder::table('products')
    ->where([
        'price' => ['>', Builder::raw('(SELECT AVG(price) FROM products)')]
    ])
    ->build();
// WHERE price > (SELECT AVG(price) FROM products)
```

### ユーザー入力の安全な識別子

カラム名がユーザー入力から来る場合、`safeIdentifier()`を使用してSQLインジェクションを防止します:

```php
$sortColumn = $_GET['sort'];  // 例: 'created_at'
$safeColumn = Builder::safeIdentifier($sortColumn);

$q = Builder::table('users')
    ->orderBy($safeColumn . ' DESC')
    ->build();

// ユーザーが"name; DROP TABLE users--"を試みた場合
// InvalidArgumentExceptionがスローされます
```

### ユーザー提供のカラム名用rawSafe

```php
$userColumn = $_GET['aggregate_column'];

$q = Builder::table('orders')
    ->select([
        Builder::rawSafe('SUM({col})', ['col' => $userColumn])->value . ' AS total'
    ])
    ->build();
// カラム名を検証し、無効な場合は例外をスロー
```

> **警告:** ユーザー入力を`raw()`に直接連結しないでください。常にバウンドパラメータまたは`safeIdentifier()`を使用してください。

---

## クエリビルダーの再利用

### Clearメソッド

ビルダーを再利用するために特定の部分をクリアします:

```php
$query = Builder::table('users')
    ->select(['id', 'name'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// 最初のクエリ
$q1 = $query->limit(10)->build();

// クリアして再利用
$query->clearWhere()->clearLimit();

// 異なる条件で2番目のクエリ
$q2 = $query
    ->where(['status' => 'pending'])
    ->limit(5)
    ->build();
```

### 利用可能なClearメソッド

| メソッド | 説明 |
|----------|------|
| `clearWhere()` | WHERE条件とパラメータをクリア |
| `clearSelect()` | SELECTカラムをデフォルト'*'にリセット |
| `clearJoin()` | すべてのJOIN句をクリア |
| `clearGroupBy()` | GROUP BY句をクリア |
| `clearOrderBy()` | ORDER BY句をクリア |
| `clearLimit()` | LIMITとOFFSETをクリア |
| `clearAll()` | ビルダーを初期状態にリセット |

### ページネーション例

```php
$baseQuery = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// 総数を取得
$countQuery = clone $baseQuery;
$countResult = $countQuery->clearSelect()->count()->build();
$total = Flight::db()->fetchField($countResult['sql'], $countResult['params']);

// ページネーションされた結果を取得
$page = 1;
$perPage = 20;
$listResult = $baseQuery->limit($perPage, ($page - 1) * $perPage)->build();
$users = Flight::db()->fetchAll($listResult['sql'], $listResult['params']);
```

---

## 動的クエリビルディング

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

## FlightPHP完全例

```php
use KnifeLemon\EasyQuery\Builder;

// ページネーションでユーザー一覧取得
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

// ユーザー作成
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

// ユーザー更新
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

// ユーザー削除
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

## APIリファレンス

### 静的メソッド

| メソッド | 説明 |
|----------|------|
| `Builder::table(string $table)` | テーブル用の新しいビルダーインスタンスを作成 |
| `Builder::raw(string $sql, array $bindings = [])` | Raw SQL式を作成 |
| `Builder::rawSafe(string $expr, array $identifiers, array $bindings = [])` | 安全な識別子置換付きRaw式 |
| `Builder::safeIdentifier(string $identifier)` | 安全なカラム/テーブル名を検証して返す |

### インスタンスメソッド

| メソッド | 説明 |
|----------|------|
| `alias(string $alias)` | テーブルエイリアスを設定 |
| `select(string\|array $columns)` | 選択するカラムを設定（デフォルト: '*'） |
| `where(array $conditions)` | WHERE条件を追加（AND） |
| `orWhere(array $conditions)` | OR WHERE条件を追加 |
| `join(string $table, string $condition, string $alias, string $type)` | JOIN句を追加 |
| `innerJoin(string $table, string $condition, string $alias)` | INNER JOINを追加 |
| `leftJoin(string $table, string $condition, string $alias)` | LEFT JOINを追加 |
| `groupBy(string $groupBy)` | GROUP BY句を追加 |
| `orderBy(string $orderBy)` | ORDER BY句を追加 |
| `limit(int $limit, int $offset = 0)` | LIMITとOFFSETを追加 |
| `count(string $column = '*')` | COUNTクエリに設定 |
| `insert(array $data)` | INSERTクエリに設定 |
| `update(array $data)` | UPDATEクエリに設定 |
| `delete()` | DELETEクエリに設定 |
| `build()` | `['sql' => ..., 'params' => ...]`をビルドして返す |
| `get()` | `build()`のエイリアス |

---

## Tracyデバッガー統合

EasyQueryはTracy Debuggerがインストールされていれば自動的に統合されます。設定は不要です！

```bash
composer require tracy/tracy
```

```php
use Tracy\Debugger;

Debugger::enable();

// すべてのクエリが自動的にTracyパネルに記録されます
$q = Builder::table('users')->where(['status' => 'active'])->build();
```

Tracyパネルで表示されるもの:
- 総クエリ数とタイプ別内訳
- 生成されたSQL（構文ハイライト）
- パラメータ配列
- クエリ詳細（テーブル、where、joinなど）

完全なドキュメントは[GitHubリポジトリ](https://github.com/knifelemon/EasyQueryBuilder)をご覧ください。
