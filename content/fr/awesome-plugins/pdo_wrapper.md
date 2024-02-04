# Classe d'aide PdoWrapper PDO

Flight est fourni avec une classe d'aide pour PDO. Cela vous permet d'interroger facilement votre base de données
avec toutes les bizarreries prepared/execute/fetchAll(). Cela simplifie grandement la façon dont vous pouvez
interroger votre base de données.

## Enregistrement de la classe d'aide PDO

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
Utilisez ceci pour INSERTS, UPDATES, ou si vous prévoyez d'utiliser un SELECT dans une boucle while

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM table WHERE something = ?", [ $something ]);
while($row = $statement->fetch()) {
	// ...
}

// Ou écriture dans la base de données
$db->runQuery("INSERT INTO table (name) VALUES (?)", [ $name ]);
$db->runQuery("UPDATE table SET name = ? WHERE id = ?", [ $name, $id ]);
```

### `fetchField(string $sql, array $params = []): mixed`
Récupère le premier champ de la requête

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM table WHERE something = ?", [ $something ]);
```

### `fetchRow(string $sql, array $params = []): array`
Récupère une ligne de la requête

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT * FROM table WHERE id = ?", [ $id ]);
```

### `fetchAll(string $sql, array $params = []): array`
Récupère toutes les lignes de la requête

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT * FROM table WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	// faire quelque chose
}
```

## Remarque avec la syntaxe `IN()`
Cela possède également une enveloppe utile pour les déclarations `IN()`. Vous pouvez simplement passer un point d'interrogation unique en tant que paramètre de l'opérateur `IN()` puis un tableau de valeurs. Voici un exemple de ce à quoi cela pourrait ressembler :

```php
$db = Flight::db();
$name = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT * FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## Exemple complet

```php
// Route d'exemple et comment vous utiliseriez cet enveloppe
Flight::route('/utilisateurs', function () {
	// Obtenir tous les utilisateurs
	$utilisateurs = Flight::db()->fetchAll('SELECT * FROM utilisateurs');

	// Stream de tous les utilisateurs
	$statement = Flight::db()->runQuery('SELECT * FROM utilisateurs');
	while ($utilisateur = $statement->fetch()) {
		echo $utilisateur['nom'];
	}

	// Obtenir un seul utilisateur
	$utilisateur = Flight::db()->fetchRow('SELECT * FROM utilisateurs WHERE id = ?', [123]);

	// Obtenir une seule valeur
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM utilisateurs');

	// Syntaxe spéciale IN() pour aider (assurez-vous que IN est en majuscules)
	$utilisateurs = Flight::db()->fetchAll('SELECT * FROM utilisateurs WHERE id IN (?)', [[1,2,3,4,5]]);
	// vous pourriez également faire ceci
	$utilisateurs = Flight::db()->fetchAll('SELECT * FROM utilisateurs WHERE id IN (?)', [ '1,2,3,4,5']);

	// Insérer un nouvel utilisateur
	Flight::db()->runQuery("INSERT INTO utilisateurs (nom, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
	$insert_id = Flight::db()->lastInsertId();

	// Mettre à jour un utilisateur
	Flight::db()->runQuery("UPDATE utilisateurs SET nom = ? WHERE id = ?", ['Bob', 123]);

	// Supprimer un utilisateur
	Flight::db()->runQuery("DELETE FROM utilisateurs WHERE id = ?", [123]);

	// Obtenir le nombre de lignes affectées
	$statement = Flight::db()->runQuery("UPDATE utilisateurs SET nom = ? WHERE nom = ?", ['Bob', 'Sally']);
	$affected_rows = $statement->rowCount();

});
```