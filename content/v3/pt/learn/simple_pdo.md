# Classe Auxiliar SimplePdo PDO

## Visão Geral

A classe `SimplePdo` no Flight é um auxiliar moderno e rico em recursos para trabalhar com bancos de dados usando PDO. Ela estende `PdoWrapper` e adiciona métodos auxiliares convenientes para operações comuns de banco de dados, como `insert()`, `update()`, `delete()` e transações. Ela simplifica tarefas de banco de dados, retorna resultados como [Collections](/learn/collections) para acesso fácil e suporta registro de consultas e monitoramento de desempenho de aplicação (APM) para casos de uso avançados.

## Entendendo

A classe `SimplePdo` é projetada para tornar o trabalho com bancos de dados em PHP muito mais fácil. Em vez de lidar com declarações preparadas, modos de busca e operações SQL verbosas, você obtém métodos limpos e simples para tarefas comuns. Cada linha é retornada como uma Collection, para que você possa usar tanto notação de array (`$row['name']`) quanto notação de objeto (`$row->name`).

Esta classe é um superconjunto de `PdoWrapper`, o que significa que ela inclui toda a funcionalidade de `PdoWrapper` mais métodos auxiliares adicionais que tornam seu código mais limpo e mais fácil de manter. Se você está usando `PdoWrapper` atualmente, atualizar para `SimplePdo` é direto, pois ela estende `PdoWrapper`.

Você pode registrar `SimplePdo` como um serviço compartilhado no Flight e, em seguida, usá-lo em qualquer lugar em seu app via `Flight::db()`.

## Uso Básico

### Registrando SimplePdo

Primeiro, registre a classe `SimplePdo` com o Flight:

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

> **NOTA**
>
> Se você não especificar `PDO::ATTR_DEFAULT_FETCH_MODE`, `SimplePdo` o definirá automaticamente como `PDO::FETCH_ASSOC` para você.

Agora você pode usar `Flight::db()` em qualquer lugar para obter sua conexão com o banco de dados.

### Executando Consultas

#### `runQuery()`

`function runQuery(string $sql, array $params = []): PDOStatement`

Use isso para INSERTs, UPDATEs ou quando você quiser buscar resultados manualmente:

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM users WHERE status = ?", ['active']);
while ($row = $statement->fetch()) {
    // $row é um array
}
```

Você também pode usá-lo para gravações:

```php
$db->runQuery("INSERT INTO users (name) VALUES (?)", ['Alice']);
$db->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 1]);
```

#### `fetchField()`

`function fetchField(string $sql, array $params = []): mixed`

Obtenha um único valor do banco de dados:

```php
$count = Flight::db()->fetchField("SELECT COUNT(*) FROM users WHERE status = ?", ['active']);
```

#### `fetchRow()`

`function fetchRow(string $sql, array $params = []): ?Collection`

Obtenha uma única linha como uma Collection (acesso array/objeto):

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// ou
echo $user->name;
```

> **DICA**
>
> `SimplePdo` adiciona automaticamente `LIMIT 1` às consultas `fetchRow()` se não estiver presente, tornando suas consultas mais eficientes.

#### `fetchAll()`

`function fetchAll(string $sql, array $params = []): array<Collection>`

Obtenha todas as linhas como um array de Collections:

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

Busque uma única coluna como um array:

```php
$ids = Flight::db()->fetchColumn("SELECT id FROM users WHERE active = ?", [1]);
// Retorna: [1, 2, 3, 4, 5]
```

#### `fetchPairs()`

`function fetchPairs(string $sql, array $params = []): array`

Busque resultados como pares chave-valor (primeira coluna como chave, segunda como valor):

```php
$userNames = Flight::db()->fetchPairs("SELECT id, name FROM users");
// Retorna: [1 => 'John', 2 => 'Jane', 3 => 'Bob']
```

### Usando Placeholders `IN()`

Você pode usar um único `?` em uma cláusula `IN()` e passar um array:

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
```

## Métodos Auxiliares

Uma das principais vantagens do `SimplePdo` sobre `PdoWrapper` é a adição de métodos auxiliares convenientes para operações comuns de banco de dados.

### `insert()`

`function insert(string $table, array $data): string`

Insira uma ou mais linhas e retorne o último ID de inserção.

**Inserção única:**

```php
$id = Flight::db()->insert('users', [
    'name' => 'John',
    'email' => 'john@example.com'
]);
```

**Inserção em massa:**

```php
$id = Flight::db()->insert('users', [
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com'],
]);
```

### `update()`

`function update(string $table, array $data, string $where, array $whereParams = []): int`

Atualize linhas e retorne o número de linhas afetadas:

```php
$affected = Flight::db()->update(
    'users',
    ['name' => 'Jane', 'email' => 'jane@example.com'],
    'id = ?',
    [1]
);
```

> **NOTA**
>
> O `rowCount()` do SQLite retorna o número de linhas onde os dados realmente mudaram. Se você atualizar uma linha com os mesmos valores que ela já tem, `rowCount()` retornará 0. Isso difere do comportamento do MySQL ao usar `PDO::MYSQL_ATTR_FOUND_ROWS`.

### `delete()`

`function delete(string $table, string $where, array $whereParams = []): int`

Exclua linhas e retorne o número de linhas excluídas:

```php
$deleted = Flight::db()->delete('users', 'id = ?', [1]);
```

### `transaction()`

`function transaction(callable $callback): mixed`

Execute um callback dentro de uma transação. A transação é confirmada automaticamente em caso de sucesso ou revertida em caso de erro:

```php
$result = Flight::db()->transaction(function($db) {
    $db->insert('users', ['name' => 'John']);
    $db->insert('logs', ['action' => 'user_created']);
    return $db->lastInsertId();
});
```

Se qualquer exceção for lançada dentro do callback, a transação é revertida automaticamente e a exceção é relançada.

## Uso Avançado

### Registro de Consultas & APM

Se você quiser rastrear o desempenho de consultas, habilite o rastreamento de APM ao registrar:

```php
Flight::register('db', \flight\database\SimplePdo::class, [
    'mysql:host=localhost;dbname=cool_db_name',
    'user',
    'pass',
    [/* opções PDO */],
    [
        'trackApmQueries' => true,
        'maxQueryMetrics' => 1000
    ]
]);
```

Após executar consultas, você pode registrá-las manualmente, mas o APM as registrará automaticamente se habilitado:

```php
Flight::db()->logQueries();
```

Isso acionará um evento (`flight.db.queries`) com métricas de conexão e consulta, que você pode escutar usando o sistema de eventos do Flight.

### Exemplo Completo

```php
Flight::route('/users', function () {
    // Obter todos os usuários
    $users = Flight::db()->fetchAll('SELECT * FROM users');

    // Transmitir todos os usuários
    $statement = Flight::db()->runQuery('SELECT * FROM users');
    while ($user = $statement->fetch()) {
        echo $user['name'];
    }

    // Obter um único usuário
    $user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

    // Obter um único valor
    $count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

    // Obter uma única coluna
    $ids = Flight::db()->fetchColumn('SELECT id FROM users');

    // Obter pares chave-valor
    $userNames = Flight::db()->fetchPairs('SELECT id, name FROM users');

    // Sintaxe especial IN()
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);

    // Inserir um novo usuário
    $id = Flight::db()->insert('users', [
        'name' => 'Bob',
        'email' => 'bob@example.com'
    ]);

    // Inserção em massa de usuários
    Flight::db()->insert('users', [
        ['name' => 'Bob', 'email' => 'bob@example.com'],
        ['name' => 'Jane', 'email' => 'jane@example.com']
    ]);

    // Atualizar um usuário
    $affected = Flight::db()->update('users', ['name' => 'Bob'], 'id = ?', [123]);

    // Excluir um usuário
    $deleted = Flight::db()->delete('users', 'id = ?', [123]);

    // Usar uma transação
    $result = Flight::db()->transaction(function($db) {
        $db->insert('users', ['name' => 'John', 'email' => 'john@example.com']);
        $db->insert('audit_log', ['action' => 'user_created']);
        return $db->lastInsertId();
    });
});
```

## Migrando de PdoWrapper

Se você está usando `PdoWrapper` atualmente, migrar para `SimplePdo` é direto:

1. **Atualize seu registro:**
   ```php
   // Antigo
   Flight::register('db', \flight\database\PdoWrapper::class, [ /* ... */ ]);
   
   // Novo
   Flight::register('db', \flight\database\SimplePdo::class, [ /* ... */ ]);
   ```

2. **Todos os métodos existentes de `PdoWrapper` funcionam no `SimplePdo`** - Não há mudanças que quebrem o código. Seu código existente continuará funcionando.

3. **Opcionalmente, use os novos métodos auxiliares** - Comece a usar `insert()`, `update()`, `delete()` e `transaction()` para simplificar seu código.

## Veja Também

- [Collections](/learn/collections) - Aprenda como usar a classe Collection para acesso fácil aos dados.
- [PdoWrapper](/learn/pdo-wrapper) - A classe auxiliar PDO legada (depreciada).

## Solução de Problemas

- Se você receber um erro sobre conexão com o banco de dados, verifique seu DSN, nome de usuário, senha e opções.
- Todas as linhas são retornadas como Collections—se você precisar de um array simples, use `$collection->getData()`.
- Para consultas `IN (?)`, certifique-se de passar um array.
- Se você estiver enfrentando problemas de memória com registro de consultas em processos de longa duração, ajuste a opção `maxQueryMetrics`.

## Registro de Alterações

- v3.18.0 - Lançamento inicial do SimplePdo com métodos auxiliares para insert, update, delete e transações.