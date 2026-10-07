# Flight Active Record 

Un active record établit une correspondance entre une entité de base de données et un objet PHP. En clair, si vous avez une table `users` dans votre base de données, vous pouvez « traduire » une ligne de cette table en une classe `User` et un objet `$user` dans votre code. Voir [exemple de base](#basic-example).

Cliquez [ici](https://github.com/flightphp/active-record) pour le dépôt GitHub.

## Exemple de base

Supposons que vous ayez la table suivante :

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Vous pouvez maintenant créer une nouvelle classe pour représenter cette table :

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
		// vous pouvez la définir de cette façon
		parent::__construct($database_connection, 'users');
		// ou de cette façon
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Maintenant, regardez la magie opérer !

```php
// pour sqlite
$database_connection = new PDO('sqlite:test.db'); // ceci est juste un exemple, vous utiliseriez probablement une vraie connexion à la base de données

// pour mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// ou mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// ou mysqli avec une création non basée sur un objet
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('some cool password');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('some cool password again!!!');
$user->insert();
// vous ne pouvez pas utiliser $user->save() ici sinon il pensera que c'est une mise à jour !

echo $user->id; // 2
```

Et c'était aussi simple que cela d'ajouter un nouvel utilisateur ! Maintenant qu'il y a une ligne utilisateur dans la base de données, comment la récupérer ?

```php
$user->find(1); // recherche id = 1 dans la base de données et le retourne.
echo $user->name; // 'Bobby Tables'
```

Et si vous voulez trouver tous les utilisateurs ?

```php
$users = $user->findAll();
```

Et avec une certaine condition ?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Vous voyez à quel point c'est amusant ? Installons-le et commençons !

## Installation

Installez simplement avec Composer

```php
composer require flightphp/active-record 
```

## Utilisation

Cette bibliothèque peut être utilisée de manière autonome ou avec le framework PHP Flight. C'est entièrement à vous.

### Autonome
Assurez-vous simplement de passer une connexion PDO au constructeur.

```php
$pdo_connection = new PDO('sqlite:test.db'); // ceci est juste un exemple, vous utiliseriez probablement une vraie connexion à la base de données

$User = new User($pdo_connection);
```

> Vous ne voulez pas toujours définir votre connexion à la base de données dans le constructeur ? Voir [Gestion de la connexion à la base de données](#database-connection-management) pour d'autres idées !

### Enregistrement comme méthode dans Flight
Si vous utilisez le framework PHP Flight, vous pouvez enregistrer la classe ActiveRecord comme service, mais vous n'y êtes pas obligé.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// ensuite vous pouvez l'utiliser comme ceci dans un contrôleur, une fonction, etc.

Flight::user()->find(1);
```

## Méthodes `runway`

[runway](/awesome-plugins/runway) est un outil CLI pour Flight qui possède une commande personnalisée pour cette bibliothèque. 

```bash
# Utilisation
php runway make:record database_table_name [class_name]

# Exemple
php runway make:record users
```

Cela créera une nouvelle classe dans le répertoire `app/records/` sous le nom `UserRecord.php` avec le contenu suivant :

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Classe ActiveRecord pour la table users.
 * @link https://docs.flightphp.com/en/v3/awesome-plugins/active-record
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $created_dt
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Définit les relations pour le modèle
     *   https://docs.flightphp.com/en/v3/awesome-plugins/active-record#relationships
     */
    protected array $relations = [
		// 'relation_name' => [ self::HAS_MANY, 'RelatedClass', 'foreign_key' ],
	];

    /**
     * Constructeur
     * @param mixed $databaseConnection La connexion à la base de données
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

## Fonctions CRUD

#### `find($id = null) : boolean|ActiveRecord`

Trouve un enregistrement et l'assigne à l'objet courant. Si vous passez un `$id` d'une certaine sorte, il effectuera une recherche sur la clé primaire avec cette valeur. Si rien n'est passé, il trouvera simplement le premier enregistrement de la table.

Vous pouvez également lui passer d'autres méthodes d'aide pour interroger votre table.

```php
// trouver un enregistrement avec certaines conditions au préalable
$user->notNull('password')->orderBy('id DESC')->find();

// trouver un enregistrement par un identifiant spécifique
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Trouve tous les enregistrements de la table que vous spécifiez.

```php
$user->findAll();
```

#### `first(): ActiveRecord` (v0.8.0)

Trouve le premier enregistrement correspondant à vos conditions. Si vous n'avez pas défini d'ordre, il trie par clé primaire ascendante. Si rien ne correspond, vous obtenez l'enregistrement non hydraté, alors vérifiez `isHydrated()` si vous n'êtes pas sûr qu'une valeur soit revenue.

```php
$user->eq('status', 'active')->first();
```

#### `last(): ActiveRecord` (v0.8.0)

Identique à `first()` mais trie par clé primaire descendante. Pratique pour les requêtes du type « donne-moi le plus récent ».

```php
$user->eq('status', 'active')->last();
```

#### `count(): int` (v0.8.0)

Compte les lignes correspondant à vos conditions actuelles. Si vous avez un `groupBy()` dans la requête, `count()` l'ignore volontairement. Un simple compteur scalaire ne peut pas représenter une ligne par groupe.

```php
$user->count();
$user->eq('status', 'active')->count();
```

#### `exists(): bool` (v0.8.0)

Retourne `true` si un enregistrement correspond à vos conditions. Il exécute en interne un `SELECT 1 ... LIMIT 1` peu coûteux.

```php
$user->eq('name', 'Bobby')->exists(); // true
```

#### `pluck(string $column): array` (v0.8.0)

Retourne un tableau plat de valeurs d'une seule colonne plutôt que d'hydrater un ensemble d'objets. Combinez-le avec `distinct()` pour obtenir des valeurs uniques.

```php
$user->pluck('name'); // [ 'Bobby', 'Joseph' ]
$user->distinct()->pluck('status'); // [ 'active', 'inactive' ]
```

#### `ids(): array` (v0.8.0)

Un raccourci pour `pluck()` sur la clé primaire.

```php
$user->gt('id', 0)->ids(); // [ 1, 2, 3 ]
```

#### `isHydrated(): boolean` (v0.4.0)

Retourne `true` si l'enregistrement courant a été hydraté (récupéré depuis la base de données).

```php
$user->find(1);
// si un enregistrement est trouvé avec des données...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

Insère l'enregistrement courant dans la base de données.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### Clés primaires textuelles

Si vous avez une clé primaire textuelle (comme un UUID), vous pouvez définir la valeur de la clé primaire avant l'insertion de deux manières.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // ou $user->save();
```

ou vous pouvez laisser la clé primaire être générée automatiquement grâce aux événements.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// vous pouvez également définir primaryKey de cette façon plutôt qu'avec le tableau ci-dessus.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // ou comme vous avez besoin de générer vos identifiants uniques
	}
}
```

Si vous ne définissez pas la clé primaire avant l'insertion, elle sera définie sur `rowid` et la base de données la générera pour vous, mais elle ne sera pas persistante car ce champ peut ne pas exister dans votre table. C'est pourquoi il est recommandé d'utiliser l'événement pour gérer cela automatiquement.

#### `update(): boolean|ActiveRecord`

Met à jour l'enregistrement courant dans la base de données.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `updateAttribute(string $name, mixed $value): ActiveRecord` (v0.8.0)

Met à jour une seule colonne sur un enregistrement chargé et l'enregistre. C'est un raccourci pour `$user->dirty([ 'name' => $value ])->update()`. Vous avez besoin d'un enregistrement chargé pour cela.

```php
$user->find(1);
$user->updateAttribute('name', 'New Name');
```

#### `save(): boolean|ActiveRecord`

Insère ou met à jour l'enregistrement courant dans la base de données. Si l'enregistrement a un id, il met à jour, sinon il insère.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Remarque :** Si vous avez des relations définies dans la classe, il enregistrera également ces relations de manière récursive si elles ont été définies, instanciées et contiennent des données modifiées à mettre à jour. (v0.4.0 et supérieur)

#### `delete(): boolean`

Supprime l'enregistrement courant de la base de données.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Vous pouvez également supprimer plusieurs enregistrements en exécutant une recherche au préalable.

```php
$user->like('name', 'Bob%')->delete();
```

#### `updateAll(array $attributes, bool $allowEmptyConditions = false): int` (v0.8.0)

Met à jour tous les enregistrements correspondant à vos conditions en une seule instruction. Aucun enregistrement n'est hydraté et aucun événement n'est déclenché, c'est précisément pour cela que c'est rapide. Retourne le nombre de lignes affectées.

Il refuse de s'exécuter sans conditions WHERE sauf si vous passez `true` pour le second argument. Votre vous du futur vous remercie.

```php
$user->eq('status', 'inactive')->updateAll([ 'status' => 'active' ]);

// oui, vous voulez vraiment mettre à jour chaque ligne de la table
$user->updateAll([ 'status' => 'active' ], true);
```

#### `deleteAll(bool $allowEmptyConditions = false): int` (v0.8.0)

Supprime tous les enregistrements correspondant à vos conditions en une seule instruction. Même principe que `updateAll()` : pas d'hydratation, pas d'événements, et cela nécessite des conditions WHERE sauf si vous passez `true`. Retourne le nombre de lignes supprimées. À utiliser avec précaution !

```php
$user->eq('status', 'deleted')->deleteAll();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Les données modifiées (dirty) désignent les données qui ont été changées dans un enregistrement.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// rien n'est « dirty » à ce stade.

$user->email = 'test@example.com'; // maintenant email est considéré comme « dirty » car il a été modifié.
$user->update();
// maintenant il n'y a plus de données « dirty » car elles ont été mises à jour et persistées dans la base de données

$user->password = password_hash()'newpassword'); // maintenant c'est « dirty »
$user->dirty(); // ne rien passer efface toutes les entrées « dirty ».
$user->update(); // rien ne sera mis à jour car rien n'a été capturé comme « dirty ».

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name et password sont tous deux mis à jour.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

C'est un alias pour la méthode `dirty()`. C'est un peu plus clair sur ce que vous faites.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name et password sont tous deux mis à jour.
```

#### `isDirty(): boolean` (v0.4.0)

Retourne `true` si l'enregistrement courant a été modifié.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Réinitialise l'enregistrement courant à son état initial. C'est très utile dans les comportements de type boucle.
Si vous passez `true`, cela réinitialise également les données de requête qui ont été utilisées pour trouver l'objet courant (comportement par défaut).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // commencez avec une ardoise propre
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Après avoir exécuté une méthode `find()`, `findAll()`, `insert()`, `update()` ou `save()`, vous pouvez obtenir le SQL qui a été construit et l'utiliser à des fins de débogage.

## Transactions

Vous devez exécuter quelques écritures qui doivent toutes réussir ensemble ? Enveloppez-les dans `transaction()` (v0.8.0). Passez-lui un callable et l'enregistrement arrive en argument. Si le callable lance une exception, tout est annulé et l'exception est relancée pour vous. Sinon, il valide et vous renvoie ce que le callable a retourné.

```php
$user->transaction(function ($user) {
	$user->name = 'Bobby Tables';
	$user->password = password_hash('correct horse battery staple');
	$user->insert();

	$user->email = 'bobby@example.com';
	$user->update();
	// la validation (commit) se produit ici si rien n'a été lancé
});
```

Les transactions imbriquées ne sont pas prises en charge (pas de savepoints), alors gardez-les plates.

## Méthodes de requête SQL
#### `select(string $field1 [, string $field2 ... ])`

Vous pouvez sélectionner seulement quelques colonnes d'une table si vous le souhaitez (c'est plus performant sur des tables très larges avec beaucoup de colonnes)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Vous pouvez techniquement choisir une autre table aussi ! Pourquoi pas ?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Vous pouvez même faire une jointure avec une autre table de la base de données.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Vous pouvez définir des conditions where personnalisées (vous ne pouvez pas définir de paramètres dans cette instruction where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Note de sécurité** - Vous pourriez être tenté de faire quelque chose comme `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. VEUILLEZ NE PAS FAIRE CELA !!! Cela est vulnérable à ce que l'on appelle les attaques par injection SQL. Il y a beaucoup d'articles en ligne, veuillez rechercher « sql injection attacks php » sur Google et vous trouverez beaucoup d'articles sur ce sujet. La bonne façon de gérer cela avec cette bibliothèque est, au lieu de cette méthode `where()`, de faire quelque chose comme `$user->eq('id', $id)->eq('name', $name)->find();`. Si vous devez absolument le faire, la bibliothèque `PDO` dispose de `$pdo->quote($var)` pour échapper la valeur. Ce n'est qu'après avoir utilisé `quote()` que vous pouvez l'utiliser dans une instruction `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Groupez vos résultats selon une condition particulière.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Triez la requête retournée d'une certaine manière.

```php
$user->orderBy('name DESC')->find();
```

#### `orderByColumn(string $column, string $direction = 'ASC')` (v0.7.2)

`order()` et `orderBy()` acceptent des fragments SQL bruts, ce qui est très bien lorsque vous codez en dur `'name DESC'`. Si le nom de la colonne provient d'une saisie utilisateur (un en-tête de tableau triable, par exemple), utilisez plutôt `orderByColumn()`. Seuls les noms de colonnes simples et les chemins `table.column` sont autorisés, et la direction doit être `ASC` ou `DESC`, donc rien ne peut être injecté.

```php
// $sortColumn provient de la requête
$user->orderByColumn($sortColumn, 'DESC')->findAll();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limitez le nombre d'enregistrements retournés. Si un second entier est fourni, ce sera un décalage (offset), puis une limite, comme en SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

#### `distinct()` (v0.8.0)

Ajoute `DISTINCT` à votre prochaine requête. Cela fonctionne sur le select normal et avec `pluck()`. `count()` l'ignore, car mettre `DISTINCT` sur une seule ligne agrégée ne fait rien.

```php
$user->distinct()->pluck('status'); // [ 'active', 'inactive' ]
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

### Conditions OR

Il est possible d'envelopper vos conditions dans une instruction OR. Cela se fait soit avec les méthodes `startWrap()` et `endWrap()`, soit en remplissant le 3ème paramètre de la condition après le champ et la valeur.

```php
// Méthode 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Cela sera évalué à `id = 1 AND (name = 'demo' OR name = 'test')`

// Méthode 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Cela sera évalué à `id = 1 OR name = 'demo'`
```

## Scopes (portées)

Les scopes (v0.8.0) sont des chaînes de requêtes réutilisables, définies comme de simples méthodes d'instance sur votre classe qui retournent `$this`. Une fois que vous en avez écrit une, elle s'enchaîne comme toute autre méthode de requête.

```php
class User extends flight\ActiveRecord {

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	public function active(): self
	{
		return $this->eq('status', 'active');
	}

	public function recent(int $days = 7): self
	{
		return $this->ge('created_at', date('Y-m-d', strtotime("-{$days} days")));
	}
}

// et maintenant vos requêtes se lisent comme des phrases
(new User($pdo_connection))->active()->recent(30)->findAll();
```

Vous pouvez également appeler un scope par son nom avec `scope()`, ce qui est pratique lorsque le nom du scope provient d'ailleurs dans votre code. Il lève une `BadMethodCallException` si la méthode n'existe pas.

```php
$user->scope('active')->findAll();
$user->scope('recent', 30)->findAll();
```

## Relations
Vous pouvez définir plusieurs types de relations avec cette bibliothèque. Vous pouvez définir des relations un-à-plusieurs et un-à-un entre les tables. Cela nécessite un peu de configuration supplémentaire dans la classe au préalable.

Définir le tableau `$relations` n'est pas difficile, mais deviner la syntaxe correcte peut être déroutant.

```php
protected array $relations = [
	// vous pouvez nommer la clé comme vous le souhaitez. Le nom de l'ActiveRecord est probablement bien. Ex : user, contact, client
	'user' => [
		// requis
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // c'est le type de relation

		// requis
		'Some_Class', // c'est l'autre classe ActiveRecord à laquelle cela fera référence

		// requis
		// selon le type de relation
		// self::HAS_ONE = la clé étrangère qui référence la jointure
		// self::HAS_MANY = la clé étrangère qui référence la jointure
		// self::BELONGS_TO = la clé locale qui référence la jointure
		'local_or_foreign_key',
		// pour information, cela ne joint également qu'à la clé primaire de l'autre modèle

		// optionnel
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // conditions supplémentaires que vous souhaitez lors de la jonction de la relation
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// optionnel
		'back_reference_name' // c'est si vous voulez faire une référence arrière de cette relation vers elle-même. Ex : $user->contact->user;
	]
];
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

Maintenant que nous avons les références configurées, nous pouvons les utiliser très facilement !

```php
$user = new User($pdo_connection);

// trouver l'utilisateur le plus récent.
$user->notNull('id')->orderBy('id desc')->find();

// obtenir les contacts en utilisant la relation :
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// ou nous pouvons faire l'inverse.
$contact = new Contact();

// trouver un contact
$contact->find();

// obtenir l'utilisateur en utilisant la relation :
echo $contact->user->name; // c'est le nom de l'utilisateur
```

Plutôt cool, non ?

### Chargement eager (anticipé)

#### Aperçu
Le chargement eager résout le problème des requêtes N+1 en chargeant les relations à l'avance. Au lieu d'exécuter une requête séparée pour les relations de chaque enregistrement, le chargement eager récupère toutes les données liées en une seule requête supplémentaire par relation.

> **Remarque :** Le chargement eager n'est disponible qu'à partir de la version v0.7.0.

#### Utilisation de base
Utilisez la méthode `with()` pour spécifier les relations à charger eager :
```php
// Charge les utilisateurs avec leurs contacts en 2 requêtes au lieu de N+1
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    foreach ($u->contacts as $contact) {
        echo $contact->email; // Aucune requête supplémentaire !
    }
}
```

#### Relations multiples
Chargez plusieurs relations à la fois :
```php
$users = $user->with(['contacts', 'profile', 'settings'])->findAll();
```

#### Types de relations

##### HAS_MANY
```php
// Charge eager tous les contacts pour chaque utilisateur
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    // $u->contacts est déjà chargé comme un tableau
    foreach ($u->contacts as $contact) {
        echo $contact->email;
    }
}
```
##### HAS_ONE
```php
// Charge eager un contact pour chaque utilisateur
$users = $user->with('contact')->findAll();
foreach ($users as $u) {
    // $u->contact est déjà chargé comme un objet
    echo $u->contact->email;
}
```

##### BELONGS_TO
```php
// Charge eager les utilisateurs parents pour tous les contacts
$contacts = $contact->with('user')->findAll();
foreach ($contacts as $c) {
    // $c->user est déjà chargé
    echo $c->user->name;
}
```
##### Avec find()
Le chargement eager fonctionne aussi bien avec 
findAll()
 qu'avec 
find()
 :

```php
$user = $user->with('contacts')->find(1);
// L'utilisateur et tous ses contacts sont chargés en 2 requêtes
```
#### Avantages en termes de performance
Sans chargement eager (problème N+1) :
```php
$users = $user->findAll(); // 1 requête
foreach ($users as $u) {
    $contacts = $u->contacts; // N requêtes (une par utilisateur !)
}
// Total : 1 + N requêtes
```

Avec le chargement eager :

```php
$users = $user->with('contacts')->findAll(); // 2 requêtes au total
foreach ($users as $u) {
    $contacts = $u->contacts; // 0 requête supplémentaire !
}
// Total : 2 requêtes (1 pour les utilisateurs + 1 pour tous les contacts)
```
Pour 10 utilisateurs, cela réduit les requêtes de 11 à 2 — une réduction de 82 % !

#### Remarques importantes
- Le chargement eager est complètement optionnel — le chargement paresseux (lazy loading) fonctionne toujours comme avant
- Les relations déjà chargées sont automatiquement ignorées
- Les références arrières fonctionnent avec le chargement eager
- Les callbacks de relations sont respectés lors du chargement eager

#### Limitations
- Le chargement eager imbriqué (par exemple, 
with(['contacts.addresses'])
) n'est pas pris en charge actuellement
- Les contraintes de chargement eager via des fermetures (closures) ne sont pas prises en charge dans cette version

## Définir des données personnalisées
Parfois, vous pouvez avoir besoin d'attacher quelque chose d'unique à votre ActiveRecord, comme un calcul personnalisé qui pourrait être plus facile à attacher à l'objet et qui serait ensuite transmis, par exemple, à un template.

#### `setCustomData(string $field, mixed $value)`
Vous attachez les données personnalisées avec la méthode `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

Ensuite, vous pouvez simplement y faire référence comme une propriété d'objet normale.

```php
echo $user->page_view_count;
```

## Horodatage (Timestamps)

Si votre table comporte des colonnes `created_at` et `updated_at`, vous pouvez laisser la bibliothèque les remplir pour vous (v0.8.0). Définissez `protected bool $timestamps = true;` dans votre classe et elle définira `created_at` et `updated_at` lors de l'insertion, et `updated_at` lors de la mise à jour. Le format est `Y-m-d H:i:s`. Si vous définissez vous-même l'une de ces colonnes, la bibliothèque laisse votre valeur tranquille.

```php
class User extends flight\ActiveRecord {
	protected bool $timestamps = true;

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}
```

Votre table doit réellement contenir ces colonnes, sinon les insertions et les mises à jour échoueront.

## Événements

Une autre fonctionnalité super géniale de cette bibliothèque concerne les événements. Les événements sont déclenchés à certains moments en fonction de certaines méthodes que vous appelez. Ils sont très très utiles pour configurer des données automatiquement pour vous.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

C'est très utile si vous devez définir une connexion par défaut ou quelque chose comme cela.

```php
// index.php ou bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // n'oubliez pas la référence &
		// vous pouvez faire cela pour définir automatiquement la connexion
		$config['connection'] = Flight::db();
		// ou ceci
		$self->transformAndPersistConnection(Flight::db());
		
		// Vous pouvez également définir le nom de la table de cette façon.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Cela n'est probablement utile que si vous avez besoin d'une manipulation de requête à chaque fois.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// toujours exécuter id >= 0 si c'est votre truc
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Celle-ci est probablement plus utile si vous devez toujours exécuter une certaine logique à chaque fois que cet enregistrement est récupéré. Devez-vous déchiffrer quelque chose ? Devez-vous exécuter une requête de comptage personnalisée à chaque fois (pas performant, mais peu importe) ?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// déchiffrer quelque chose
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// peut-être stocker quelque chose de personnalisé comme une requête ???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Cela n'est probablement utile que si vous avez besoin d'une manipulation de requête à chaque fois.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// toujours exécuter id >= 0 si c'est votre truc
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Similaire à `afterFind()` mais vous pouvez le faire sur tous les enregistrements à la fois !

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// faites quelque chose de cool comme dans afterFind()
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
		// définir de bonnes valeurs par défaut
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

Peut-être avez-vous un cas d'usage pour modifier des données après leur insertion ?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// faites ce que vous voulez
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// ou peu importe...
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Vraiment utile si vous avez besoin de définir des valeurs par défaut à chaque fois lors d'une mise à jour.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// définir de bonnes valeurs par défaut
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Peut-être avez-vous un cas d'usage pour modifier des données après leur mise à jour ?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// faites ce que vous voulez
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// ou peu importe...
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

C'est utile si vous voulez que des événements se produisent à la fois lors des insertions et des mises à jour. Je vous épargne la longue explication, mais je suis sûr que vous pouvez deviner de quoi il s'agit.

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

Je ne sais pas ce que vous voudriez faire ici, mais pas de jugement ici ! Allez-y !

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'C'était un brave soldat... :visage-pleurant:';
	} 
}
```

## Gestion de la connexion à la base de données

Lorsque vous utilisez cette bibliothèque, vous pouvez définir la connexion à la base de données de différentes manières. Vous pouvez définir la connexion dans le constructeur, via une variable de configuration `$config['connection']` ou via `setDatabaseConnection()` (v0.4.1). 

```php
$pdo_connection = new PDO('sqlite:test.db'); // par exemple
$user = new User($pdo_connection);
// ou
$user = new User(null, [ 'connection' => $pdo_connection ]);
// ou
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Si vous voulez éviter de toujours définir un `$database_connection` à chaque fois que vous appelez un active record, il y a des moyens de contourner cela !

```php
// index.php ou bootstrap.php
// Définissez ceci comme une classe enregistrée dans Flight
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// Et maintenant, aucun argument n'est requis !
$user = new User();
```

> **Remarque :** Si vous prévoyez de faire des tests unitaires, procéder de cette façon peut ajouter quelques défis aux tests unitaires, mais globalement, comme vous pouvez injecter votre connexion avec `setDatabaseConnection()` ou `$config['connection']`, ce n'est pas trop grave.

Si vous devez rafraîchir la connexion à la base de données, par exemple si vous exécutez un script CLI de longue durée et devez rafraîchir la connexion de temps en temps, vous pouvez redéfinir la connexion avec `$your_record->setDatabaseConnection($pdo_connection)`.

## Contribution

Faites-le, je vous en prie. :D

### Configuration

Lorsque vous contribuez, assurez-vous d'exécuter `composer test-coverage` pour maintenir une couverture de tests à 100 % (ce n'est pas une véritable couverture de tests unitaires, mais plutôt des tests d'intégration).

Assurez-vous également d'exécuter `composer beautify` et `composer phpcs` pour corriger les éventuelles erreurs de style.

## Licence

MIT