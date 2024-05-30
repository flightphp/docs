# Flight Active Record

Un active record est la mise en correspondance d'une entité de base de données avec un objet PHP. Pour le dire simplement, si vous avez une table users dans votre base de données, vous pouvez "traduire" une ligne dans cette table en une classe `User` et un objet `$user` dans votre base de code. Voir [exemple de base](#exemple-de-base).

## Exemple de Base

Supposons que vous avez la table suivante :

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Maintenant, vous pouvez configurer une nouvelle classe pour représenter cette table :

```php
/**
 * Une classe ActiveRecord est généralement au singulier
 * 
 * Il est fortement recommandé d'ajouter les propriétés de la table en commentaires ici
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// vous pouvez le définir de cette manière
		parent::__construct($database_connection, 'users');
		// ou de cette façon
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Maintenant, regardez la magie opérer !

```php
// pour sqlite
$database_connection = new PDO('sqlite:test.db'); // ceci est juste un exemple, vous utiliseriez probablement une vraie connexion de base de données

// pour mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'nom d'utilisateur', 'mot de passe');

// ou mysqli
$database_connection = new mysqli('localhost', 'nom d'utilisateur', 'mot de passe', 'test_db');
// ou mysqli avec une création non basée sur les objets
$database_connection = mysqli_connect('localhost', 'nom d'utilisateur', 'mot de passe', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('un mot de passe sympa');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('encore un mot de passe sympa!!!');
$user->insert();
// impossible d'utiliser $user->save() ici sinon il pensera que c'est une mise à jour !

echo $user->id; // 2
```

Et c'était aussi simple d'ajouter un nouvel utilisateur ! Maintenant qu'il y a une ligne utilisateur dans la base de données, comment la récupérez-vous ?

```php
$user->find(1); // trouve l'id = 1 dans la base de données et le retourne.
echo $user->name; // 'Bobby Tables'
```

Et si vous voulez trouver tous les utilisateurs ?

```php
$users = $user->findAll();
```

Et pour une certaine condition ?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Voyez comme c'est amusant ? Installons-le et commençons !

## Installation

Installez simplement avec Composer

```php
composer require flightphp/active-record 
```

## Utilisation

Cela peut être utilisé en tant que bibliothèque autonome ou avec le Framework PHP Flight. Complètement à vous de décider.

### Autonome
Assurez-vous simplement de passer une connexion PDO au constructeur.

```php
$pdo_connection = new PDO('sqlite:test.db'); // ceci est juste un exemple, vous utiliseriez probablement une vraie connexion de base de données

$User = new User($pdo_connection);
```

### Framework PHP Flight
Si vous utilisez le Framework PHP Flight, vous pouvez enregistrer la classe ActiveRecord en tant que service (mais vous n'êtes honnêtement pas obligé de le faire).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// puis vous pouvez l'utiliser de cette façon dans un contrôleur, une fonction, etc.

Flight::user()->find(1);
```

## Fonctions CRUD

#### `find($id = null) : boolean|ActiveRecord`

Trouve un enregistrement et l'assigne à l'objet actuel. Si vous passez un `$id` de quelque sorte, il effectuera une recherche sur la clé primaire avec cette valeur. S'il n'y a rien de passé, il trouvera juste le premier enregistrement dans la table.

En outre, vous pouvez passer d'autres méthodes auxiliaires pour interroger votre table.

```php
// trouver un enregistrement avec des conditions au préalable
$user->notNull('password')->orderBy('id DESC')->find();

// trouver un enregistrement par un id spécifique
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Trouve tous les enregistrements dans la table que vous spécifiez.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Renvoie `true` si l'enregistrement actuel a été hydraté (récupéré de la base de données).

```php
$user->find(1);
// si un enregistrement est trouvé avec des données...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

Insère l'enregistrement actuel dans la base de données.

```php
$user = new User($pdo_connection);
$user->name = 'démo';
$user->password = md5('démo');
$user->insert();
```

##### Clés primaires basées sur du texte

Si vous avez une clé primaire basée sur du texte (comme un UUID), vous pouvez définir la valeur de la clé primaire avant l'insertion de deux manières.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'quelques-uuid';
$user->name = 'démo';
$user->password = md5('démo');
$user->insert(); // ou $user->save();
```

ou vous pouvez faire générer automatiquement la clé primaire pour vous grâce aux événements.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// vous pouvez également définir la clé primaire de cette manière au lieu de l'array ci-dessus.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // ou comme vous avez besoin de générer vos identifiants uniques
	}
}
```

Si vous ne définissez pas la clé primaire avant l'insertion, elle sera définie sur le `rowid` et la base de données la générera pour vous, mais elle ne persistera pas car ce champ peut ne pas exister dans votre table. C'est pourquoi il est recommandé d'utiliser l'événement pour gérer automatiquement cela pour vous.

#### `update(): boolean|ActiveRecord`

Met à jour l'enregistrement actuel dans la base de données.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Insère ou met à jour l'enregistrement actuel dans la base de données. Si l'enregistrement a un id, il sera mis à jour, sinon il sera inséré.

```php
$user = new User($pdo_connection);
$user->name = 'démo';
$user->password = md5('démo');
$user->save();
```

**Remarque :** Si vous avez des relations définies dans la classe, elles seront récursivement enregistrées si elles ont été définies, instanciées et ont des données à mettre à jour. (v0.4.0 et supérieur)

#### `delete(): boolean`

Supprime l'enregistrement actuel de la base de données.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Vous pouvez également supprimer plusieurs enregistrements en exécutant une recherche au préalable.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Les données sales font référence aux données qui ont été modifiées dans un enregistrement.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// rien n'est "sale" à ce stade.

$user->email = 'test@example.com'; // maintenant l'e-mail est considéré comme "sale" car il a été modifié.
$user->update();
// maintenant il n'y a pas de données sales car elles ont été mises à jour et persistées dans la base de données

$user->password = password_hash()'un nouveau mot de passe'); // maintenant il est sale
$user->dirty(); // en ne passant rien, toutes les entrées sales seront effacées.
$user->update(); // rien ne sera mis à jour car rien n'a été capturé comme sale.

$user->dirty([ 'name' => 'quelque chose', 'password' => password_hash('un mot de passe différent') ]);
$user->update(); // à la fois le nom et le mot de passe sont mis à jour.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

C'est un alias pour la méthode `dirty()`. C'est un peu plus clair ce que vous faites.

```php
$user->copyFrom([ 'name' => 'quelque chose', 'password' => password_hash('un mot de passe différent') ]);
$user->update(); // à la fois le nom et le mot de passe sont mis à jour.
```

#### `isDirty(): boolean` (v0.4.0)

Renvoie `true` si l'enregistrement actuel a été modifié.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Réinitialise l'enregistrement actuel à son état initial. C'est vraiment bien à utiliser dans des comportements de boucle.
Si vous passez `true`, il réinitialisera également les données de la requête qui ont été utilisées pour trouver l'objet actuel (comportement par défaut).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // commencez avec une feuille propre
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Après avoir exécuté une méthode `find()`, `findAll()`, `insert()`, `update()`, ou `save()`, vous pouvez obtenir le SQL qui a été généré et l'utiliser à des fins de débogage.

## Méthodes de Requête SQL

#### `select(string $champ1 [, string $champ2 ... ])`

Vous pouvez sélectionner uniquement quelques-unes des colonnes d'une table si vous le souhaitez (c'est plus performant sur des tables très larges avec de nombreuses colonnes)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Vous pouvez techniquement choisir une autre table aussi ! Pourquoi pas ?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $nom_table, string $condition_jointure)`

Vous pouvez même joindre une autre table dans la base de données.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $conditions_where)`

Vous pouvez définir des arguments where personnalisés (vous ne pouvez pas définir de paramètres dans cette instruction where)

```php
$user->where('id=1 AND name="démo"')->find();
```

**Note de Sécurité** - Vous pourriez être tenté de faire quelque chose comme `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. S'il vous plaît NE FAITES PAS CECI !!! Cela est susceptible de ce que l'on appelle des attaques par injection SQL. Il y a beaucoup d'articles en ligne, veuillez chercher "attaques par injection sql php" sur Google et vous trouverez beaucoup d'articles sur ce sujet. La bonne manière de gérer cela avec cette bibliothèque est au lieu de cette méthode `where()`, vous feriez quelque chose comme `$user->eq('id', $id)->eq('name', $name)->find();` Si vous devez absolument le faire, la bibliothèque `PDO` a `$pdo->quote($var)` pour l'échapper pour vous. Seulement après avoir utilisé `quote()`, vous pouvez l'utiliser dans une instruction `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Groupe vos résultats selon une condition particulière.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Trie la requête retournée d'une certaine manière.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limite le nombre d'enregistrements retournés. Si un second int est donné, il sera décalage, limite exactement comme en SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## Conditions WHERE
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Où `field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Où `field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

Où `field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Où `field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Où `field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Où `field < $value`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Où `field >= $value`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Où `field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Où `field LIKE $value` ou `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Où `field IN($value)` ou `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Où `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

## Relations
Vous pouvez définir plusieurs types de relations en utilisant cette bibliothèque. Vous pouvez définir des relations un->plusieurs et un->un entre les tables. Cela nécessite une configuration supplémentaire dans la classe au préalable.

Le réglage de l'array `$relations` n'est pas difficile, mais deviner correctement la syntaxe peut être déroutant.

```php
protected array $relations = [
	// vous pouvez nommer la clé comme vous le souhaitez. Le nom de l'ActiveRecord est probablement bon. Ex : utilisateur, contact, client
	'user' => [
		// requis
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // c'est le type de relation

		// requis
		'Some_Class', // c'est la classe ActiveRecord "autre" à laquelle cela fera référence

		// requis
		// en fonction du type de relation
		// self::HAS_ONE = la clé étrangère qui fait référence à la jointure
		// self::HAS_MANY = la clé étrangère qui fait référence à la jointure
		// self::BELONGS_TO = la clé locale qui fait référence à la jointure
		'cle_locale_ou_etrangere',
		// juste pour information, cela se joint également uniquement à la clé primaire du modèle "autre"

		// facultatif
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // conditions supplémentaires que vous souhaitez lors de la jointure de la relation
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// facultatif
		'nom_reference_arriere' // c'est si vous voulez réfé# Flight Active Record

Un enregistrement actif est une mise en correspondance d'une entité de base de données avec un objet PHP. Pour le dire simplement, si vous avez une table utilisateurs dans votre base de données, vous pouvez "traduire" une ligne dans cette table en une classe `User` et un objet `$utilisateur` dans votre base de code. Voir [exemple de base](#exemple-de-base).

## Exemple de Base

Supposons que vous avez la table suivante :

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Maintenant, vous pouvez configurer une nouvelle classe pour représenter cette table :

```php
/**
 * Une classe ActiveRecord est généralement singulière
 * 
 * Il est fortement recommandé d'ajouter les propriétés de la table en commentaires ici
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// vous pouvez le définir de cette manière
		parent::__construct($database_connection, 'users');
		// ou de cette façon
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Maintenant, regardez la magie opérer !

```php
// pour sqlite
$database_connection = new PDO('sqlite:test.db'); // ceci est juste un exemple, vous utiliseriez probablement une vraie connexion de base de données

// pour mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'nom d'utilisateur', 'mot de passe');

// ou mysqli
$database_connection = new mysqli('localhost', 'nom d'utilisateur', 'mot de passe', 'test_db');
// ou mysqli avec une création non basée sur les objets
$database_connection = mysqli_connect('localhost', 'nom d'utilisateur', 'mot de passe', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('un mot de passe sympa');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('encore un mot de passe sympa!!!');
$user->insert();
// impossible d'utiliser $user->save() ici sinon il pensera que c'est une mise à jour !

echo $user->id; // 2
```

Et c'était aussi simple d'ajouter un nouvel utilisateur ! Maintenant qu'il y a une ligne utilisateur dans la base de données, comment la récupérez-vous ?

```php
$user->find(1); // trouve l'id = 1 dans la base de données et le retourne.
echo $user->name; // 'Bobby Tables'
```

Et si vous voulez trouver tous les utilisateurs ?

```php
$users = $user->findAll();
```

Et pour une certaine condition ?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Voyez comme c'est amusant ? Installons-le et commençons !

## Installation

Installez simplement avec Composer

```php
composer require flightphp/active-record 
```

## Utilisation

Cela peut être utilisé en tant que bibliothèque autonome ou avec le Framework PHP Flight. Complètement à vous de décider.

### Autonome
Assurez-vous simplement de passer une connexion PDO au constructeur.

```php
$pdo_connection = new PDO('sqlite:test.db'); // ceci est juste un exemple, vous utiliseriez probablement une vraie connexion de base de données

$User = new User($pdo_connection);
```

### Framework PHP Flight
Si vous utilisez le Framework PHP Flight, vous pouvez enregistrer la classe ActiveRecord en tant que service (mais vous n'êtes honnêtement pas obligé de le faire).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// puis vous pouvez l'utiliser de cette façon dans un contrôleur, une fonction, etc.

Flight::user()->find(1);
```

## Fonctions CRUD

#### `find($id = null) : boolean|ActiveRecord`

Trouve un enregistrement et l'assigne à l'objet actuel. Si vous passez un `$id` de quelque sorte, il effectuera une recherche sur la clé primaire avec cette valeur. S'il n'y a rien de passé, il trouvera juste le premier enregistrement dans la table.

En outre, vous pouvez passer d'autres méthodes auxiliaires pour interroger votre table.

```php
// trouver un enregistrement avec des conditions au préalable
$user->notNull('password')->orderBy('id DESC')->find();

// trouver un enregistrement par un id spécifique
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Trouve tous les enregistrements dans la table que vous spécifiez.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Renvoie `true` si l'enregistrement actuel a été hydraté (récupéré de la base de données).

```php
$user->find(1);
// si un enregistrement est trouvé avec des données...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

Insère l'enregistrement actuel dans la base de données.

```php
$user = new User($pdo_connection);
$user->name = 'démo';
$user->password = md5('démo');
$user->insert();
```

##### Clés primaires basées sur du texte

Si vous avez une clé primaire basée sur du texte (comme un UUID), vous pouvez définir la valeur de la clé primaire avant l'insertion de deux manières.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'quelques-uuid';
$user->name = 'démo';
$user->password = md5('démo');
$user->insert(); // ou $user->save();
```

ou vous pouvez faire générer automatiquement la clé primaire pour vous grâce aux événements.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// vous pouvez également définir la clé primaire de cette manière au lieu de l'array ci-dessus.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // ou comme vous avez besoin de générer vos identifiants uniques
	}
}
```

Si vous ne définissez pas la clé primaire avant l'insertion, elle sera définie sur le `rowid` et la base de données la générera pour vous, mais elle ne persistera pas car ce champ peut ne pas exister dans votre table. C'est pourquoi il est recommandé d'utiliser l'événement pour gérer automatiquement cela pour vous.

#### `update(): boolean|ActiveRecord`

Met à jour l'enregistrement actuel dans la base de données.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Insère ou met à jour l'enregistrement actuel dans la base de données. Si l'enregistrement a un id, il sera mis à jour, sinon il sera inséré.

```php
$user = new User($pdo_connection);
$user->name = 'démo';
$user->password = md5('démo');
$user->save();
```

**Remarque :** Si vous avez des relations définies dans la classe, elles seront récursivement enregistrées si elles ont été définies, instanciées et ont des données à mettre à jour. (v0.4.0 et supérieur)

#### `delete(): boolean`

Supprime l'enregistrement actuel de la base de données.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Vous pouvez également supprimer plusieurs enregistrements en exécutant une recherche au préalable.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Les données sales font référence aux données qui ont été modifiées dans un enregistrement.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// rien n'est "sale" à ce stade.

$user->email = 'test@example.com'; // maintenant l'e-mail est considéré comme "sale" car il a été modifié.
$user->update();
// maintenant il n'y a pas de données sales car elles ont été mises à jour et persistées dans la base de données

$user->password = password_hash()'un nouveau mot de passe'); // maintenant il est sale
$user->dirty(); // en ne passant rien, toutes les entrées sales seront effacées.
$user->update(); // rien ne sera mis à jour car rien n'a été capturé comme sale.

$user->dirty([ 'name' => 'quelque chose', 'password' => password_hash('un mot de passe différent') ]);
$user->update(); // à la fois le nom et le mot de passe sont mis à jour.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

C'est un alias pour la méthode `dirty()`. C'est un peu plus clair ce que vous faites.

```php
$user->copyFrom([ 'name' => 'quelque chose', 'password' => password_hash('un mot de passe différent') ]);
$user->update(); // à la fois le nom et le mot de passe sont mis à jour.
```

#### `isDirty(): boolean` (v0.4.0)

Renvoie `true` si l'enregistrement actuel a été modifié.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Réinitialise l'enregistrement actuel à son état initial. C'est vraiment bien à utiliser dans des comportements de boucle.
Si vous passez `true`, il réinitialisera également les données de la requête qui ont été utilisées pour trouver l'objet actuel (comportement par défaut).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // commencez avec une feuille propre
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Après avoir exécuté une méthode `find()`, `findAll()`, `insert()`, `update()`, ou `save()`, vous pouvez obtenir le SQL qui a été généré et l'utiliser à des fins de débogage.

## Méthodes de Requête SQL

#### `select(string $champ1 [, string $champ2 ... ])`

Vous pouvez sélectionner uniquement quelques-unes des colonnes d'une table si vous le souhaitez (c'est plus performant sur des tables très larges avec de nombreuses colonnes)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Vous pouvez techniquement choisir une autre table aussi ! Pourquoi pas ?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $nom_table, string $condition_jointure)`

Vous pouvez même joindre une autre table dans la base de données.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $conditions_where)`

Vous pouvez définir des arguments where personnalisés (vous ne pouvez pas définir de paramètres dans cette instruction where)

```php
$user->where('id=1 AND name="démo"')->find();
```

**Note de Sécurité** - Vous pourriez être tenté de faire quelque chose comme `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. S'il vous plaît NE FAITES PAS CECI !!! Cela est susceptible de ce que l'on appelle des attaques par injection SQL. Il y a beaucoup d'articles en ligne, veuillez chercher "attaques par injection sql php" sur Google et vous trouverez beaucoup d'articles sur ce sujet. La bonne manière de gérer cela avec cette bibliothèque est au lieu de cette méthode `where()`, vous feriez quelque chose comme `$user->eq('id', $id)->eq('name', $name)->find();` Si vous devez absolument le faire, la bibliothèque `PDO` a `$pdo->quote($var)` pour l'échapper pour vous. Seulement après avoir utilisé `quote()`, vous pouvez l'utiliser dans une instruction `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Groupe vos résultats selon une condition particulière.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Trie la requête retournée d'une certaine manière.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limite le nombre d'enregistrements retournés. Si un second int est donné, il sera décalage, limite exactement comme en SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## Conditions WHERE
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Où `field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Où `field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

Où `field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Où `field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Où `field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Où `field < $value`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Où `field >= $value`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Où `field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Où `field LIKE $value` ou `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Où `field IN($value)` ou `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Où `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

## Relations
Vous pouvez définir plusieurs types de relations en utilisant cette bibliothèque. Vous pouvez définir des relations un->plusieurs et un->un entre les tables. Cela nécessite une configuration supplémentaire dans la classe au préalable.

Le réglage de l'array `$relations` n'est pas difficile, mais deviner correctement la syntaxe peut être déroutant.

```php
protected array $relations = [
	// vous pouvez nommer la clé comme vous le souhaitez. Le nom de l'ActiveRecord est probablement bon. Ex : utilisateur, contact, client
	'user' => [
		// requis
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // c'est le type de relation

		// requis
		'Some_Class', // c'est la classe ActiveRecord "autre" à laquelle cela fera référence

		// requis
		// en fonction du type de relation
		// self::HAS_ONE = la clé étrangère qui fait référence à la jointure
		// self::HAS_MANY = la clé étrangère qui fait référence à la jointure
		// self::BELONGS_TO = la clé locale qui fait référence à la jointure
		'cle_locale_ou_etrangere',
		// juste pour information, cela se joint également uniquement à la clé primaire du modèle "autre"

		// facultatif
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // conditions supplémentaires que vous souhaitez lors de la jointure de la relation
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// facultatif
		'nom_reference_arriere' // c'est sivous voulez référencer cette relation en arrière. Ex: $user->contact->user;
	];
]
```

```php
class User extends ActiveRecord{
	protected array $relations = [
		'contacts' => [ self::HAS_MANY, Contact::class, 'user_id' ],
		'contact' => [ self::HAS_ONE, Contact::class, 'user_id' ],
	];

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}

class Contact extends ActiveRecord{
	protected array $relations = [
		'user' => [ self::BELONGS_TO, User::class, 'user_id' ],
		'user_with_backref' => [ self::BELONGS_TO, User::class, 'user_id', [], 'contact' ],
	];
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'contacts');
	}
}
```

Maintenant que les références sont configurées, vous pouvez les utiliser très facilement !

```php
$user = new User($pdo_connection);

// trouver le plus récent utilisateur.
$user->notNull('id')->orderBy('id desc')->find();

// obtenir des contacts en utilisant la relation:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// ou nous pouvons y aller dans l'autre sens.
$contact = new Contact();

// trouver un contact
$contact->find();

// obtenir un utilisateur en utilisant la relation:
echo $contact->user->name; // c'est le nom de l'utilisateur
```

Vraiment cool, non?

## Configuration de Données Personnalisées
Parfois, vous devez attacher quelque chose d'unique à votre ActiveRecord tel qu'un calcul personnalisé qui pourrait être plus facile à attacher simplement à l'objet qui serait ensuite passé à un modèle, par exemple.

#### `setCustomData(string $champ, mixed $valeur)`
Vous attachez les données personnalisées avec la méthode `setCustomData()`.
```php
$user->setCustomData('page_view_count', $nombre_de_vues_page);
```

Et ensuite vous le référencez simplement comme une propriété d'objet normale.

```php
echo $user->page_view_count;
```

## Événements

Une autre fonctionnalité super géniale de cette bibliothèque concerne les événements. Les événements sont déclenchés à certains moments en fonction de certaines méthodes que vous appelez. Ils sont très très utiles pour configurer automatiquement des données pour vous.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Cela est vraiment utile si vous avez besoin de définir une connexion par défaut par exemple.

```php
// index.php ou bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // n'oubliez pas la référence &
		// vous pourriez faire cela pour définir automatiquement la connexion
		$config['connection'] = Flight::db();
		// ou ceci
		$self->transformAndPersistConnection(Flight::db());
		
		// Vous pouvez également définir le nom de la table de cette manière.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Cela est probablement utile uniquement si vous avez besoin d'une manipulation de requête à chaque fois.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// exécuter toujours id >= 0 si c'est votre truc
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Celui-ci est probablement plus utile si vous avez toujours besoin d'exécuter une certaine logique à chaque fois que cet enregistrement est récupéré. Avez-vous besoin de décrypter quelque chose ? Avez-vous besoin d'exécuter une requête de comptage personnalisée chaque fois (pas performant mais peu importe) ?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// décryptage de quelque chose
		$self->secret = votrefonctiondecrypt($self->secret, votre_clé);

		// peut-être stocker quelque chose de personnalisé comme une requête ???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Cela est probablement utile uniquement si vous avez besoin d'une manipulation de requête à chaque fois.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// exécuter toujours id >= 0 si c'est votre truc
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Similaire à `afterFind()` mais vous pouvez le faire pour tous les enregistrements !

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// faire quelque chose de sympa comme afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Vraiment utile si vous avez besoin de définir des valeurs par défaut à chaque fois.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// définir certaines valeurs par défaut
		if(!$self->created_date) {
			$self->created_date = gmdate('Y-m-d');
		}

		if(!$self->password) {
			$self->password = password_hash((string) microtime(true));
		}
	} 
}
```

#### `afterInsert(ActiveRecord $ActiveRecord)`

Peut-être avez-vous un cas d'utilisation pour modifier des données après leur insertion ?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// vous faites ce que vous voulez
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// ou autre chose....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Vraiment utile si vous avez besoin de définir des valeurs par défaut à chaque mise à jour.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// définir certaines valeurs par défaut
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Peut-être avez-vous un cas d'utilisation pour modifier des données après leur mise à jour ?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// vous faites ce que vous voulez
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// ou autre chose....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Ceci est utile si vous voulez que des événements se produisent à la fois lors de l'insertion ou de la mise à jour. Je vous épargne l'explication longue, mais je suis sûr que vous pouvez deviner ce que c'est.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeSave(self $self) {
		$self->last_updated = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

Je ne sais pas ce que vous voulez faire ici, mais pas de jugements ici ! Faites-le !

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Il était un brave soldat... :cry-face:';
	} 
}
```

## Gestion de la Connexion à la Base de Données

Lorsque vous utilisez cette bibliothèque, vous pouvez définir la connexion à la base de données de plusieurs manières. Vous pouvez définir la connexion dans le constructeur, vous pouvez la définir via une variable de configuration `$config['connection']` ou vous pouvez la définir via `setDatabaseConnection()` (v0.4.1). 

```php
$pdo_connection = new PDO('sqlite:test.db'); // par exemple
$user = new User($pdo_connection);
// ou
$user = new User(null, [ 'connection' => $pdo_connection ]);
// ou
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Si vous devez actualiser la connexion à la base de données, par exemple si vous exécutez un script CLI à long terme et que vous devez rafraîchir la connexion de temps en temps, vous pouvez réinitialiser la connexion avec `$your_record->setDatabaseConnection($pdo_connection)`.

## Contribuer

S'il vous plaît faites-le. :D

## Configuration

Lorsque vous contribuez, assurez-vous d'exécuter `composer test-coverage` pour maintenir une couverture de test à 100% (ce n'est pas une couverture de test unitaire réelle, mais plus des tests d'intégration).

Assurez-vous également d'exécuter `composer beautify` et `composer phpcs` pour corriger les éventuelles