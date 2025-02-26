# Flight Active Record 

Un enregistrement actif est le mappage d'une entité de base de données à un objet PHP. En d'autres termes, si vous avez une table d'utilisateurs dans votre base de données, vous pouvez "traduire" une ligne de cette table en une classe `User` et un objet `$user` dans votre code. Voir [exemple de base](#basic-example).

Cliquez [ici](https://github.com/flightphp/active-record) pour le dépôt sur GitHub.

## Exemple de Base

Supposons que vous ayez la table suivante :

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
 * Il est fortement recommandé d'ajouter les propriétés de la table en tant que commentaires ici
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
		// ou de cette manière
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Maintenant, regardez la magie opérer !

```php
// pour sqlite
$database_connection = new PDO('sqlite:test.db'); // ceci est juste un exemple, vous utiliseriez probablement une véritable connexion à la base de données

// pour mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// ou mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// ou mysqli avec création non-objet
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('un mot de passe cool');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('un autre mot de passe cool !!!');
$user->insert();
// impossible d'utiliser $user->save() ici, sinon cela sera considéré comme une mise à jour !

echo $user->id; // 2
```

Et c'était aussi simple que d'ajouter un nouvel utilisateur ! Maintenant qu'il y a une ligne d'utilisateur dans la base de données, comment la récupérer ?

```php
$user->find(1); // trouver id = 1 dans la base de données et le retourner.
echo $user->name; // 'Bobby Tables'
```

Et que se passe-t-il si vous voulez trouver tous les utilisateurs ?

```php
$users = $user->findAll();
```

Que dire d'une certaine condition ?

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

Cela peut être utilisé comme une bibliothèque autonome ou avec le Framework PHP Flight. Cela dépend complètement de vous.

### Autonome
Assurez-vous simplement de passer une connexion PDO au constructeur.

```php
$pdo_connection = new PDO('sqlite:test.db'); // ceci est juste un exemple, vous utiliseriez probablement une véritable connexion à la base de données

$User = new User($pdo_connection);
```

> Vous ne voulez pas toujours définir votre connexion à la base de données dans le constructeur ? Voir [Gestion de Connexion à la Base de Données](#database-connection-management) pour d'autres idées !

### Enregistrer comme une méthode dans Flight
Si vous utilisez le Framework PHP Flight, vous pouvez enregistrer la classe ActiveRecord en tant que service, mais honnêtement, vous n'êtes pas obligé de le faire.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// ensuite, vous pouvez l'utiliser de cette manière dans un contrôleur, une fonction, etc.

Flight::user()->find(1);
```

## Méthodes `runway`

[runway](/awesome-plugins/runway) est un outil CLI pour Flight qui possède une commande personnalisée pour cette bibliothèque.

```bash
# Utilisation
php runway make:record nom_de_table_de_base_de_données [nom_classe]

# Exemple
php runway make:record users
```

Cela créera une nouvelle classe dans le répertoire `app/records/` nommé `UserRecord.php` avec le contenu suivant :

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Classe ActiveRecord pour la table des utilisateurs.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
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
     * @var array $relations Définir les relations pour le modèle
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
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

Trouver un enregistrement et l'assigner à l'objet courant. Si vous passez un `$id` quelconque, il effectuera une recherche sur la clé primaire avec cette valeur. Si rien n'est passé, il trouvera simplement le premier enregistrement de la table.

De plus, vous pouvez lui passer d'autres méthodes d'aide pour interroger votre table.

```php
// trouver un enregistrement avec certaines conditions au préalable
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

Retourne `true` si l'enregistrement actuel a été hydraté (récupéré de la base de données).

```php
$user->find(1);
// si un enregistrement est trouvé avec des données...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

Insère l'enregistrement actuel dans la base de données.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### Clés Primaires Basées sur du Texte

Si vous avez une clé primaire basée sur du texte (comme un UUID), vous pouvez définir la valeur de la clé primaire avant d'insérer de deux manières.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // ou $user->save();
```

ou vous pouvez avoir la clé primaire générée automatiquement pour vous via des événements.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// vous pouvez également définir la clé primaire de cette manière au lieu du tableau ci-dessus.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // ou comme vous avez besoin de générer vos identifiants uniques
	}
}
```

Si vous ne définissez pas la clé primaire avant d'insérer, elle sera définie sur le `rowid` et la base de données la générera pour vous, mais elle ne persistera pas car ce champ peut ne pas exister dans votre table. C'est pourquoi il est recommandé d'utiliser l'événement pour gérer cela automatiquement pour vous.

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
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Remarque :** Si vous avez des relations définies dans la classe, celles-ci seront également enregistrées récursivement si elles ont été définies, instanciées et ont des données à mettre à jour. (v0.4.0 et supérieur)

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

Les données "sales" se réfèrent aux données qui ont été changées dans un enregistrement.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// rien n'est "sale" à ce stade.

$user->email = 'test@example.com'; // maintenant l'email est considéré comme "sale" puisqu'il a changé.
$user->update();
// maintenant il n'y a plus de données "sales" car elles ont été mises à jour et persistées dans la base de données

$user->password = password_hash()'newpassword'); // maintenant cela est sale
$user->dirty(); // ne rien passer permettra d'effacer toutes les entrées sales.
$user->update(); // rien ne sera mis à jour car rien n'a été capturé comme sale.

$user->dirty([ 'name' => 'quelque chose', 'password' => password_hash('un mot de passe différent') ]);
$user->update(); // à la fois le nom et le mot de passe sont mis à jour.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Ceci est un alias pour la méthode `dirty()`. C'est un peu plus clair ce que vous faites.

```php
$user->copyFrom([ 'name' => 'quelque chose', 'password' => password_hash('un mot de passe différent') ]);
$user->update(); // à la fois le nom et le mot de passe sont mis à jour.
```

#### `isDirty(): boolean` (v0.4.0)

Retourne `true` si l'enregistrement actuel a été changé.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Réinitialise l'enregistrement actuel à son état initial. C'est vraiment bien à utiliser dans des comportements de boucle.
Si vous passez `true`, cela réinitialisera également les données de requête qui ont été utilisées pour trouver l'objet actuel (comportement par défaut).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // commencer avec une ardoise propre
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Après avoir exécuté une méthode `find()`, `findAll()`, `insert()`, `update()` ou `save()`, vous pouvez obtenir le SQL qui a été construit et l'utiliser à des fins de débogage.

## Méthodes de Requête SQL
#### `select(string $field1 [, string $field2 ... ])`

Vous pouvez sélectionner seulement quelques-unes des colonnes d'une table si vous le souhaitez (c'est plus performant sur des tables vraiment larges avec de nombreuses colonnes)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Vous pouvez techniquement choisir une autre table aussi ! Pourquoi pas ?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Vous pouvez même faire une jointure avec une autre table dans la base de données.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Vous pouvez définir quelques arguments where personnalisés (vous ne pouvez pas définir de paramètres dans cette instruction where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Remarque de sécurité** - Vous pourriez être tenté de faire quelque chose comme `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Veuillez NE PAS FAIRE CECI !!! Cela est susceptible aux attaques par injection SQL. Il existe de nombreux articles en ligne, veuillez rechercher "attaques d'injection SQL php" et vous trouverez beaucoup d'articles sur ce sujet. La bonne façon de gérer cela avec cette bibliothèque est au lieu de cette méthode `where()`, vous feriez quelque chose de plus comme `$user->eq('id', $id)->eq('name', $name)->find();` Si vous devez absolument faire cela, la bibliothèque `PDO` a `$pdo->quote($var)` pour l'échapper pour vous. Ce n'est qu'après avoir utilisé `quote()` que vous pouvez l'utiliser dans une instruction `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Groupez vos résultats par une condition particulière.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Trier la requête retournée d'une certaine manière.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limite le nombre d'enregistrements retournés. Si un second int est donné, il s'agira d'un décalage, limite tout comme en SQL.

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

### Conditions OU

Il est possible d'enfermer vos conditions dans une instruction OU. Cela se fait soit avec les méthodes `startWrap()` et `endWrap()`, soit en remplissant le 3ème paramètre de la condition après le champ et la valeur.

```php
// Méthode 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Cela sera évalué à `id = 1 AND (name = 'demo' OR name = 'test')`

// Méthode 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Cela sera évalué à `id = 1 OR name = 'demo'`
```

## Relations
Vous pouvez définir plusieurs types de relations en utilisant cette bibliothèque. Vous pouvez définir des relations un->plusieurs et un->un entre les tables. Cela nécessite un peu de configuration supplémentaire dans la classe au préalable.

Définir le tableau `$relations` n'est pas difficile, mais deviner la bonne syntaxe peut être déroutant.

```php
protected array $relations = [
	// vous pouvez nommer la clé comme vous le souhaitez. Le nom de l'ActiveRecord est probablement bon. Ex: user, contact, client
	'user' => [
		// requis
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // c'est le type de relation

		// requis
		'Some_Class', // c'est la classe ActiveRecord "autre" à laquelle cela fera référence

		// requis
		// selon le type de relation
		// self::HAS_ONE = la clé étrangère qui fait référence à la jointure
		// self::HAS_MANY = la clé étrangère qui fait référence à la jointure
		// self::BELONGS_TO = la clé locale qui fait référence à la jointure
		'local_or_foreign_key',
		// juste FYI, cela ne joint également qu'à la clé primaire du modèle "autre"

		// optionnel
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // conditions supplémentaires que vous souhaitez lorsque vous rejoignez la relation
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// optionnel
		'back_reference_name' // si vous souhaitez faire une référence inverse à cette relation
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

Maintenant, nous avons la configuration des références, donc nous pouvons les utiliser très facilement !

```php
$user = new User($pdo_connection);

// trouver l'utilisateur le plus récent.
$user->notNull('id')->orderBy('id desc')->find();

// obtenir des contacts par le biais de la relation :
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// ou nous pouvons aller de l'autre côté.
$contact = new Contact();

// trouver un contact
$contact->find();

// obtenir un utilisateur par le biais de la relation :
echo $contact->user->name; // c'est le nom de l'utilisateur
```

Assez cool, non ?

## Définir des Données Personnalisées
Parfois, vous pouvez avoir besoin d'attacher quelque chose d'unique à votre ActiveRecord, comme un calcul personnalisé qui serait plus facile à attacher à l'objet qui serait ensuite passé à un modèle.

#### `setCustomData(string $field, mixed $value)`
Vous attachez les données personnalisées avec la méthode `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

Et ensuite, vous y faites simplement référence comme à une propriété d'objet normale.

```php
echo $user->page_view_count;
```

## Événements

Une autre fonctionnalité super géniale de cette bibliothèque concerne les événements. Les événements sont déclenchés à certains moments en fonction de certaines méthodes que vous appelez. Ils sont très utiles pour configurer des données automatiquement pour vous.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

C'est vraiment utile si vous avez besoin de définir une connexion par défaut ou quelque chose comme ça.

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
		// ou cela
		$self->transformAndPersistConnection(Flight::db());
		
		// Vous pouvez également définir le nom de la table de cette manière.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Ceci est probablement seulement utile si vous avez besoin d'une manipulation de requête à chaque fois.

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

Ceci est probablement plus utile si vous devez toujours exécuter une certaine logique chaque fois que cet enregistrement est récupéré. Avez-vous besoin de décrypter quelque chose ? Avez-vous besoin d'exécuter une requête de comptage personnalisée à chaque fois (non performant mais bon) ?

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
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']); 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Ceci est probablement seulement utile si vous avez besoin d'une manipulation de requête à chaque fois.

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

Similaire à `afterFind()` mais vous pouvez le faire sur tous les enregistrements à la place !

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// faire quelque chose de cool comme afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Vraiment utile si vous avez besoin de définir certaines valeurs par défaut à chaque fois.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// définir des valeurs par défaut
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
		// ou autre....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Vraiment utile si vous avez besoin de définir certaines valeurs par défaut à chaque fois lors d'une mise à jour.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeUpdate(self $self) {
		// définir des valeurs par défaut
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

	protected function afterUpdate(self $self) {
		// vous faites ce que vous voulez
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// ou autre....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Ceci est utile si vous voulez que des événements se produisent à la fois lors des insertions et des mises à jour. Je vais vous épargner la longue explication, mais je suis sûr que vous pouvez deviner de quoi il s'agit.

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

Je ne suis pas sûr de ce que vous voudriez faire ici, mais pas de jugements ! Allez-y !

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Il était un soldat courageux... :cry-face:';
	} 
}
```

## Gestion de Connexion à la Base de Données

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

Si vous souhaitez éviter de toujours définir une `$database_connection` chaque fois que vous appelez un enregistrement actif, il y a des moyens de contourner cela !

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

// Et maintenant, aucun argument requis !
$user = new User();
```

> **Remarque :** Si vous envisagez de tester unitaire, faire cela peut ajouter des défis aux tests unitaires, mais dans l'ensemble, comme vous pouvez injecter votre 
connexion avec `setDatabaseConnection()` ou `$config['connection']`, ce n'est pas trop mal.

Si vous avez besoin de rafraîchir la connexion à la base de données, par exemple si vous exécutez un script CLI de longue durée et devez rafraîchir la connexion de temps en temps, vous pouvez réinitialiser la connexion avec `$your_record->setDatabaseConnection($pdo_connection)`.

## Contribution

Veuillez le faire. :D

### Configuration

Lorsque vous contribuez, assurez-vous d'exécuter `composer test-coverage` pour maintenir une couverture de test de 100 % (cela n'est pas une véritable couverture de test unitaire, plus comme des tests d'intégration).

Assurez-vous également d'exécuter `composer beautify` et `composer phpcs` pour corriger toute erreur de linting.

## Licence

MIT