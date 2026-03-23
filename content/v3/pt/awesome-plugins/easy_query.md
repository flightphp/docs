# EasyQuery

[knifelemon/easy-query](https://github.com/knifelemon/EasyQueryBuilder) é um construtor de consultas SQL leve e fluente que gera SQL e parâmetros para declarações preparadas. Funciona com [SimplePdo](/learn/simple-pdo).

## Recursos

- 🔗 **API Fluente** - Encadeie métodos para construção de consultas legível
- 🛡️ **Proteção contra Injeção SQL** - Ligação automática de parâmetros com declarações preparadas
- 🔧 **Suporte a SQL Bruto** - Insira expressões SQL brutas com `raw()`
- 📝 **Múltiplos Tipos de Consulta** - SELECT, INSERT, UPDATE, DELETE, COUNT
- 🔀 **Suporte a JOIN** - INNER, LEFT, RIGHT joins com aliases
- 🎯 **Condições Avançadas** - LIKE, IN, NOT IN, BETWEEN, operadores de comparação
- 🌐 **Agnóstico de Banco de Dados** - Retorna SQL + params, use com qualquer conexão de BD
- 🪶 **Leve** - Pegada mínima com zero dependências

## Instalação

```bash
composer require knifelemon/easy-query
```

## Início Rápido

```php
use KnifeLemon\EasyQuery\Builder;

$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC')
    ->limit(10)
    ->build();

// Use com SimplePdo do Flight
$users = Flight::db()->fetchAll($q['sql'], $q['params']);
```

## Entendendo build()

O método `build()` retorna um array com `sql` e `params`. Essa separação mantém seu banco de dados seguro usando declarações preparadas.

```php
$q = Builder::table('users')
    ->where(['email' => 'user@example.com'])
    ->build();

// Retorna:
// [
//     'sql' => 'SELECT * FROM users WHERE email = ?',
//     'params' => ['user@example.com']
// ]
```

---

## Tipos de Consulta

### SELECT

```php
// Seleciona todas as colunas
$q = Builder::table('users')->build();
// SELECT * FROM users

// Seleciona colunas específicas
$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->build();
// SELECT id, name, email FROM users

// Com alias de tabela
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

## Condições WHERE

### Igualdade Simples

```php
$q = Builder::table('users')
    ->where(['id' => 123, 'status' => 'active'])
    ->build();
// WHERE id = ? AND status = ?
```

### Operadores de Comparação

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

### Condições OR

Use `orWhere()` para adicionar condições agrupadas OR:

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

### Múltiplos JOINs

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

## Ordenação, Agrupamento e Limites

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

### LIMIT e OFFSET

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

## Expressões SQL Brutas

Use `raw()` quando precisar de funções ou expressões SQL que não devem ser tratadas como parâmetros vinculados.

### Raw Básico

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

### Raw com Parâmetros Vinculados

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

### Raw em WHERE (Subconsulta)

```php
$q = Builder::table('products')
    ->where([
        'price' => ['>', Builder::raw('(SELECT AVG(price) FROM products)')]
    ])
    ->build();
// WHERE price > (SELECT AVG(price) FROM products)
```

### Identificadores Seguros para Entrada do Usuário

Quando nomes de colunas vêm de entrada do usuário, use `safeIdentifier()` para prevenir injeção SQL:

```php
$sortColumn = $_GET['sort'];  // e.g., 'created_at'
$safeColumn = Builder::safeIdentifier($sortColumn);

$q = Builder::table('users')
    ->orderBy($safeColumn . ' DESC')
    ->build();

// Se o usuário tentar: "name; DROP TABLE users--"
// Lança InvalidArgumentException
```

### rawSafe para Nomes de Colunas Fornecidos pelo Usuário

```php
$userColumn = $_GET['aggregate_column'];

$q = Builder::table('orders')
    ->select([
        Builder::rawSafe('SUM({col})', ['col' => $userColumn])->value . ' AS total'
    ])
    ->build();
// Valida o nome da coluna, lança exceção se inválido
```

> **Aviso:** Nunca concatene entrada do usuário diretamente em `raw()`. Sempre use parâmetros vinculados ou `safeIdentifier()`.

---

## Reutilização do Construtor de Consultas

### Métodos de Limpeza

Limpe partes específicas para reutilizar o construtor:

```php
$query = Builder::table('users')
    ->select(['id', 'name'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// Primeira consulta
$q1 = $query->limit(10)->build();

// Limpe e reutilize
$query->clearWhere()->clearLimit();

// Segunda consulta com condições diferentes
$q2 = $query
    ->where(['status' => 'pending'])
    ->limit(5)
    ->build();
```

### Métodos de Limpeza Disponíveis

| Método | Descrição |
|--------|-------------|
| `clearWhere()` | Limpa condições WHERE e parâmetros |
| `clearSelect()` | Redefine colunas SELECT para o padrão '*' |
| `clearJoin()` | Limpa todas as cláusulas JOIN |
| `clearGroupBy()` | Limpa cláusula GROUP BY |
| `clearOrderBy()` | Limpa cláusula ORDER BY |
| `clearLimit()` | Limpa LIMIT e OFFSET |
| `clearAll()` | Redefine o construtor para o estado inicial |

### Exemplo de Paginação

```php
$baseQuery = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// Obtém contagem total
$countQuery = clone $baseQuery;
$countResult = $countQuery->clearSelect()->count()->build();
$total = Flight::db()->fetchField($countResult['sql'], $countResult['params']);

// Obtém resultados paginados
$page = 1;
$perPage = 20;
$listResult = $baseQuery->limit($perPage, ($page - 1) * $perPage)->build();
$users = Flight::db()->fetchAll($listResult['sql'], $listResult['params']);
```

---

## Construção Dinâmica de Consultas

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

## Exemplo Completo com FlightPHP

```php
use KnifeLemon\EasyQuery\Builder;

// Lista usuários com paginação
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

// Cria usuário
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

// Atualiza usuário
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

// Deleta usuário
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

## Referência da API

### Métodos Estáticos

| Método | Descrição |
|--------|-------------|
| `Builder::table(string $table)` | Cria uma nova instância do construtor para a tabela |
| `Builder::raw(string $sql, array $bindings = [])` | Cria uma expressão SQL bruta |
| `Builder::rawSafe(string $expr, array $identifiers, array $bindings = [])` | Expressão bruta com substituição segura de identificadores |
| `Builder::safeIdentifier(string $identifier)` | Valida e retorna um nome de coluna/tabela seguro |

### Métodos de Instância

| Método | Descrição |
|--------|-------------|
| `alias(string $alias)` | Define alias da tabela |
| `select(string\|array $columns)` | Define colunas a selecionar (padrão: '*') |
| `where(array $conditions)` | Adiciona condições WHERE (AND) |
| `orWhere(array $conditions)` | Adiciona condições OR WHERE |
| `join(string $table, string $condition, string $alias, string $type)` | Adiciona cláusula JOIN |
| `innerJoin(string $table, string $condition, string $alias)` | Adiciona INNER JOIN |
| `leftJoin(string $table, string $condition, string $alias)` | Adiciona LEFT JOIN |
| `groupBy(string $groupBy)` | Adiciona cláusula GROUP BY |
| `orderBy(string $orderBy)` | Adiciona cláusula ORDER BY |
| `limit(int $limit, int $offset = 0)` | Adiciona LIMIT e OFFSET |
| `count(string $column = '*')` | Define consulta para COUNT |
| `insert(array $data)` | Define consulta para INSERT |
| `update(array $data)` | Define consulta para UPDATE |
| `delete()` | Define consulta para DELETE |
| `build()` | Constrói e retorna `['sql' => ..., 'params' => ...]` |
| `get()` | Alias para `build()` |

---

## Integração com Tracy Debugger

EasyQuery integra automaticamente com Tracy Debugger se instalado. Nenhuma configuração necessária!

```bash
composer require tracy/tracy
```

```php
use Tracy\Debugger;

Debugger::enable();

// Todas as consultas são automaticamente registradas no painel Tracy
$q = Builder::table('users')->where(['status' => 'active'])->build();
```

O painel Tracy mostra:
- Total de consultas e divisão por tipo
- SQL gerado (com destaque de sintaxe)
- Array de parâmetros
- Detalhes da consulta (tabela, where, joins, etc.)

Para documentação completa, visite o [repositório GitHub](https://github.com/knifelemon/EasyQueryBuilder).