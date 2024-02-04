# Classe de Auxílio PDO PdoWrapper

O Flight vem com uma classe de auxílio para PDO. Isso permite que você consulte facilmente o banco de dados
com toda a loucura de preparar/executar/fetchAll(). Simplifica bastante como você pode
consultar seu banco de dados.

## Registrando a Classe de Auxílio PDO

```php
// Registrar a classe de auxílio PDO
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## Uso
Este objeto estende o PDO, então todos os métodos normais do PDO estão disponíveis. Os métodos a seguir são adicionados para facilitar a consulta ao banco de dados:

### `runQuery(string $sql, array $params = []): PDOStatement`
Use isso para INSERTS, UPDATES, ou se planeja usar um SELECT em um loop while

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM table WHERE something = ?", [ $something ]);
while($row = $statement->fetch()) {
	// ...
}

// Ou escrevendo no banco de dados
$db->runQuery("INSERT INTO table (name) VALUES (?)", [ $name ]);
$db->runQuery("UPDATE table SET name = ? WHERE id = ?", [ $name, $id ]);
```

### `fetchField(string $sql, array $params = []): mixed`
Puxa o primeiro campo da consulta

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM table WHERE something = ?", [ $something ]);
```

### `fetchRow(string $sql, array $params = []): array`
Puxa uma linha da consulta

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT * FROM table WHERE id = ?", [ $id ]);
```

### `fetchAll(string $sql, array $params = []): array`
Puxa todas as linhas da consulta

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT * FROM table WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	// faça algo
}
```

## Nota com a sintaxe `IN()`
Isso também tem um wrapper útil para declarações `IN()`. Você pode simplesmente passar um ponto de interrogação como um espaço reservado para `IN()` e então um array de valores. Aqui está um exemplo de como isso poderia ser:

```php
$db = Flight::db();
$name = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT * FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## Exemplo Completo

```php
// Rota de exemplo e como usar este wrapper
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

	// Sintaxe especial IN() para ajudar (certifique-se de que o IN está em maiúsculas)
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
	// você também poderia fazer isto
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [ '1,2,3,4,5']);

	// Inserir um novo usuário
	Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
	$insert_id = Flight::db()->lastInsertId();

	// Atualizar um usuário
	Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

	// Excluir um usuário
	Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

	// Obter o número de linhas afetadas
	$statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
	$affected_rows = $statement->rowCount();

});
```