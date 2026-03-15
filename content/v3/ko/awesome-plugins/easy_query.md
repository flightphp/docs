# EasyQuery

[knifelemon/easy-query](https://github.com/knifelemon/EasyQueryBuilder)는 SQL과 파라미터를 생성하는 경량의 Fluent SQL 쿼리 빌더입니다. [SimplePdo](/learn/simple-pdo)와 함께 사용할 수 있습니다.

## 특징

- 🔗 **Fluent API** - 메서드 체이닝으로 가독성 높은 쿼리 작성
- 🛡️ **SQL 인젝션 방지** - Prepared Statement를 통한 자동 파라미터 바인딩
- 🔧 **Raw SQL 지원** - `raw()`로 SQL 표현식 직접 삽입
- 📝 **다양한 쿼리 타입** - SELECT, INSERT, UPDATE, DELETE, COUNT
- 🔀 **JOIN 지원** - INNER, LEFT, RIGHT 조인과 별칭
- 🎯 **고급 조건** - LIKE, IN, NOT IN, BETWEEN, 비교 연산자
- 🌐 **데이터베이스 독립적** - SQL + params 반환, 어떤 DB 연결에서도 사용 가능
- 🪶 **경량** - 의존성 없이 최소한의 용량

## 설치

```bash
composer require knifelemon/easy-query
```

## 빠른 시작

```php
use KnifeLemon\EasyQuery\Builder;

$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC')
    ->limit(10)
    ->build();

// Flight의 SimplePdo와 함께 사용
$users = Flight::db()->fetchAll($q['sql'], $q['params']);
```

## build() 이해하기

`build()` 메서드는 `sql`과 `params`를 포함한 배열을 반환합니다. 이 분리는 prepared statement를 사용하여 데이터베이스를 안전하게 보호합니다.

```php
$q = Builder::table('users')
    ->where(['email' => 'user@example.com'])
    ->build();

// 반환값:
// [
//     'sql' => 'SELECT * FROM users WHERE email = ?',
//     'params' => ['user@example.com']
// ]
```

---

## 쿼리 타입

### SELECT

```php
// 모든 컬럼 선택
$q = Builder::table('users')->build();
// SELECT * FROM users

// 특정 컬럼 선택
$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->build();
// SELECT id, name, email FROM users

// 테이블 별칭 사용
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

## WHERE 조건

### 단순 동등 비교

```php
$q = Builder::table('users')
    ->where(['id' => 123, 'status' => 'active'])
    ->build();
// WHERE id = ? AND status = ?
```

### 비교 연산자

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

### OR 조건

`orWhere()`를 사용하여 OR로 그룹화된 조건을 추가합니다:

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

### 다중 JOIN

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

## 정렬, 그룹화, 제한

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

### LIMIT과 OFFSET

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

## Raw SQL 표현식

SQL 함수나 바운드 파라미터로 처리하면 안 되는 표현식이 필요할 때 `raw()`를 사용합니다.

### 기본 Raw

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

### 바운드 파라미터가 있는 Raw

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

### WHERE에서 Raw (서브쿼리)

```php
$q = Builder::table('products')
    ->where([
        'price' => ['>', Builder::raw('(SELECT AVG(price) FROM products)')]
    ])
    ->build();
// WHERE price > (SELECT AVG(price) FROM products)
```

### 사용자 입력에 대한 안전한 식별자

컬럼 이름이 사용자 입력에서 올 때, `safeIdentifier()`를 사용하여 SQL 인젝션을 방지합니다:

```php
$sortColumn = $_GET['sort'];  // 예: 'created_at'
$safeColumn = Builder::safeIdentifier($sortColumn);

$q = Builder::table('users')
    ->orderBy($safeColumn . ' DESC')
    ->build();

// 사용자가 "name; DROP TABLE users--"를 시도하면
// InvalidArgumentException 발생
```

### 사용자 제공 컬럼 이름에 대한 rawSafe

```php
$userColumn = $_GET['aggregate_column'];

$q = Builder::table('orders')
    ->select([
        Builder::rawSafe('SUM({col})', ['col' => $userColumn])->value . ' AS total'
    ])
    ->build();
// 컬럼 이름 유효성 검사, 유효하지 않으면 예외 발생
```

> **경고:** 사용자 입력을 절대 `raw()`에 직접 연결하지 마세요. 항상 바운드 파라미터나 `safeIdentifier()`를 사용하세요.

---

## 쿼리 빌더 재사용

### Clear 메서드

빌더를 재사용하기 위해 특정 부분을 초기화합니다:

```php
$query = Builder::table('users')
    ->select(['id', 'name'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// 첫 번째 쿼리
$q1 = $query->limit(10)->build();

// 초기화하고 재사용
$query->clearWhere()->clearLimit();

// 다른 조건으로 두 번째 쿼리
$q2 = $query
    ->where(['status' => 'pending'])
    ->limit(5)
    ->build();
```

### 사용 가능한 Clear 메서드

| 메서드 | 설명 |
|--------|------|
| `clearWhere()` | WHERE 조건과 파라미터 초기화 |
| `clearSelect()` | SELECT 컬럼을 기본값 '*'로 초기화 |
| `clearJoin()` | 모든 JOIN 절 초기화 |
| `clearGroupBy()` | GROUP BY 절 초기화 |
| `clearOrderBy()` | ORDER BY 절 초기화 |
| `clearLimit()` | LIMIT과 OFFSET 초기화 |
| `clearAll()` | 빌더를 초기 상태로 리셋 |

### 페이지네이션 예제

```php
$baseQuery = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// 총 개수 가져오기
$countQuery = clone $baseQuery;
$countResult = $countQuery->clearSelect()->count()->build();
$total = Flight::db()->fetchField($countResult['sql'], $countResult['params']);

// 페이지네이션된 결과 가져오기
$page = 1;
$perPage = 20;
$listResult = $baseQuery->limit($perPage, ($page - 1) * $perPage)->build();
$users = Flight::db()->fetchAll($listResult['sql'], $listResult['params']);
```

---

## 동적 쿼리 빌딩

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

## FlightPHP 전체 예제

```php
use KnifeLemon\EasyQuery\Builder;

// 페이지네이션으로 사용자 목록 조회
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

// 사용자 생성
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

// 사용자 업데이트
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

// 사용자 삭제
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

## API 레퍼런스

### 정적 메서드

| 메서드 | 설명 |
|--------|------|
| `Builder::table(string $table)` | 테이블에 대한 새 빌더 인스턴스 생성 |
| `Builder::raw(string $sql, array $bindings = [])` | Raw SQL 표현식 생성 |
| `Builder::rawSafe(string $expr, array $identifiers, array $bindings = [])` | 안전한 식별자 치환이 있는 Raw 표현식 |
| `Builder::safeIdentifier(string $identifier)` | 안전한 컬럼/테이블 이름 검증 및 반환 |

### 인스턴스 메서드

| 메서드 | 설명 |
|--------|------|
| `alias(string $alias)` | 테이블 별칭 설정 |
| `select(string\|array $columns)` | 선택할 컬럼 설정 (기본값: '*') |
| `where(array $conditions)` | WHERE 조건 추가 (AND) |
| `orWhere(array $conditions)` | OR WHERE 조건 추가 |
| `join(string $table, string $condition, string $alias, string $type)` | JOIN 절 추가 |
| `innerJoin(string $table, string $condition, string $alias)` | INNER JOIN 추가 |
| `leftJoin(string $table, string $condition, string $alias)` | LEFT JOIN 추가 |
| `groupBy(string $groupBy)` | GROUP BY 절 추가 |
| `orderBy(string $orderBy)` | ORDER BY 절 추가 |
| `limit(int $limit, int $offset = 0)` | LIMIT과 OFFSET 추가 |
| `count(string $column = '*')` | COUNT 쿼리로 설정 |
| `insert(array $data)` | INSERT 쿼리로 설정 |
| `update(array $data)` | UPDATE 쿼리로 설정 |
| `delete()` | DELETE 쿼리로 설정 |
| `build()` | `['sql' => ..., 'params' => ...]` 빌드 및 반환 |
| `get()` | `build()`의 별칭 |

---

## Tracy 디버거 통합

EasyQuery는 Tracy Debugger가 설치되어 있으면 자동으로 통합됩니다. 설정이 필요 없습니다!

```bash
composer require tracy/tracy
```

```php
use Tracy\Debugger;

Debugger::enable();

// 모든 쿼리가 자동으로 Tracy 패널에 기록됩니다
$q = Builder::table('users')->where(['status' => 'active'])->build();
```

Tracy 패널에서 보여주는 것:
- 총 쿼리 수와 타입별 분류
- 생성된 SQL (구문 강조)
- 파라미터 배열
- 쿼리 세부 정보 (테이블, where, join 등)

전체 문서는 [GitHub 저장소](https://github.com/knifelemon/EasyQueryBuilder)를 참조하세요.
