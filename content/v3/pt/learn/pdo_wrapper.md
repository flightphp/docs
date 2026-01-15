# Classe Auxiliar PDO PdoWrapper

> **AVISO**
>
> **Depreciado:** `PdoWrapper` está depreciado a partir do Flight v3.18.0. Não será removido em uma versão futura, mas será mantido para compatibilidade com versões anteriores. Por favor, use [SimplePdo](/learn/simple-pdo) em vez disso, que oferece a mesma funcionalidade mais métodos auxiliares adicionais para operações comuns de banco de dados.

## Visão Geral

A classe `PdoWrapper` no Flight é um auxiliar amigável para trabalhar com bancos de dados usando PDO. Ela simplifica tarefas comuns de banco de dados, adiciona alguns métodos úteis para buscar resultados e retorna resultados como [Collections](/learn/collections) para acesso fácil. Ela também suporta registro de consultas e monitoramento de desempenho de aplicação (APM) para casos de uso avançados.

## Entendendo

Trabalhar com bancos de dados em PHP pode ser um pouco verboso, especialmente ao usar PDO diretamente. `PdoWrapper` estende PDO e adiciona métodos que tornam a consulta, busca e manipulação de resultados muito mais fáceis. Em vez de lidar com declarações preparadas e modos de busca, você obtém métodos simples para tarefas comuns, e cada linha é retornada como uma Collection, para que você possa usar notação de array ou objeto.

Você pode registrar o `PdoWrapper` como um serviço compartilhado no Flight, e então usá-lo em qualquer lugar do seu app via `Flight::db()`.

## Uso Básico

### Registrando o Auxiliar PDO

Primeiro, registre a classe `PdoWrapper` com o Flight:

```php
Flight::register('db', \flight\database\PdoWrapper::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
]);
```

Agora você pode usar `Flight::db()` em qualquer lugar para obter sua conexão com o banco de dados.

### Executando Consultas

#### `runQuery()`

`function runQuery(string $sql, array $params = []): PDOStatement`

Use isso para INSERTs, UPDATEs, ou quando você quiser buscar resultados manualmente:

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

`function fetchRow(string $sql, array $params = []): Collection`

Obtenha uma única linha como uma Collection (acesso array/objeto):

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// ou
echo $user->name;
```

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

### Usando Placeholders `IN()`

Você pode usar um único `?` em uma cláusula `IN()` e passar um array ou string separada por vírgulas:

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
// ou
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", ['1,2,3']);
```

## Uso Avançado

### Registro de Consultas & APM

Se você quiser rastrear o desempenho de consultas, ative o rastreamento APM ao registrar:

```php
Flight::register('db', \flight\database\PdoWrapper::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [/* options */], true // último parâmetro ativa APM
]);
```

Após executar consultas, você pode registrá-las manualmente, mas o APM as registrará automaticamente se ativado:

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

    // Sintaxe especial IN()
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', ['1,2,3,4,5']);

    // Inserir um novo usuário
    Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
    $insert_id = Flight::db()->lastInsertId();

    // Atualizar um usuário
    Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

    // Deletar um usuário
    Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

    // Obter o número de linhas afetadas
    $statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
    $affected_rows = $statement->rowCount();
});
```

## Veja Também

- [Collections](/learn/collections) - Aprenda como usar a classe Collection para acesso fácil a dados.

## Solução de Problemas

- Se você receber um erro sobre conexão com o banco de dados, verifique seu DSN, nome de usuário, senha e opções.
- Todas as linhas são retornadas como Collections—se você precisar de um array simples, use `$collection->getData()`.
- Para consultas `IN (?)`, certifique-se de passar um array ou string separada por vírgulas.

## Changelog

- v3.2.0 - Lançamento inicial do PdoWrapper com métodos básicos de consulta e busca.