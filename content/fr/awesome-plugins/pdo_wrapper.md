# Classe d'aide PdoWrapper PDO

Flight est livré avec une classe d'aide pour PDO. Il vous permet d'interroger facilement votre base de données avec toute la bizarrerie préparée/execute/fetchAll(). Cela simplifie grandement la façon dont vous pouvez interroger votre base de données. Chaque résultat de ligne est renvoyé sous forme de classe Collection de Flight qui vous permet d'accéder à vos données via une syntaxe de tableau ou une syntaxe d'objet.

## Enregistrer la classe d'aide PDO

```php
// Enregistrer la classe d'aide PDO
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## Utilisation
Cet objet étend PDO, donc toutes les méthodes normales de PDO sont disponibles. Les méthodes suivantes sont ajoutées pour faciliter l'interrogation de la base de données :

### `runQuery(string $sql, array $params = []): PDOStatement`
Utilisez ceci pour les INSERT, les UPDATES, ou si vous envisagez d'utiliser un SELECT dans une boucle while

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM table WHERE something = ?", [ $something ]);
while($row = $statement->fetch()) {
	// ...
}

// Ou pour écrire dans la base de données
$db->runQuery("INSERT INTO table (name) VALUES (?)", [ $name ]);
$db->runQuery("UPDATE table SET name = ? WHERE id = ?", [ $name, $id ]);
```

### `fetchField(string $sql, array $params = []): mixed`
Extraie le premier champ de la requête

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM table WHERE something = ?", [ $something ]);
```

### `fetchRow(string $sql, array $params = []): array`
Récupère une ligne de la requête

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT id, name FROM table WHERE id = ?", [ $id ]);
echo $row['name'];
// or
echo $row->name;
```

### `fetchAll(string $sql, array $params = []): array`
Récupère toutes les lignes de la requête

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT id, name FROM table WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	echo $row['name'];
	// or
	echo $row->name;
}
```

## Remarque avec la syntaxe `IN()`
Il existe également un wrapper utile pour les déclarations `IN()`. Vous pouvez simplement passer un point d'interrogation unique en guise de paramètre pour `IN()` puis un tableau de valeurs. Voici un exemple de ce à quoi cela pourrait ressembler :

```php
$db = Flight::db();
$name = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT id, name FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## Exemple complet

```php
// Exemple de route et comment vous utiliseriez ce wrapper
Flight::route('/utilisateurs', function () {
	// Obtenir tous les utilisateurs
	$users = Flight::db()->fetchAll('SELECT * FROM users');

	// Diffuser tous les utilisateurs
	$statement = Flight::db()->runQuery('SELECT * FROM users');
	while ($user = $statement->fetch()) {
		echo $user['name'];
		// ou echo $user->name;
	}

	// Obtenir un seul utilisateur
	$user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

	// Obtenir une seule valeur
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

	// Syntaxe spéciale IN() pour aider (assurez-vous que IN est en majuscules)
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
	// vous pourriez aussi faire ceci
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [ '1,2,3,4,5']);

	// Insérer un nouvel utilisateur
	Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
	$insert_id = Flight::db()->lastInsertId();

	// Mettre à jour un utilisateur
	Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

	// Supprimer un utilisateur
	Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

	// Obtenir le nombre de lignes affectées
	$statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
	$affected_rows = $statement->rowCount();

});
```