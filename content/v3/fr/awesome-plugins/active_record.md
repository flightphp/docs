# Flight Active Record

Un active record est un mappage d'une entité de base de données vers un objet PHP. En termes simples, si vous avez un tableau users dans votre base de données, vous pouvez « traduire » une ligne de ce tableau vers une classe `User` et un objet `$user` dans votre codebase. Voir [exemple de base](#basic-example).

Cliquez [ici](https://github.com/flightphp/active-record) pour le dépôt sur GitHub.

## Exemple de base

Supposons que vous ayez le tableau suivant :

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Maintenant, vous pouvez configurer une nouvelle classe pour représenter ce tableau :

```php
/**
 * Une classe ActiveRecord est généralement au singulier
 * 
 * Il est fortement recommandé d'ajouter les propriétés du tableau en tant que commentaires ici
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// vous pouvez le définir de cette façon
		parent::__construct($database_connection, 'users');
		// ou de cette façon
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Maintenant, regardez la magie opérer !

```php
// pour sqlite
$database_connection = new PDO('sqlite:test.db'); // ceci n'est qu'un exemple, vous utiliseriez probablement une vraie connexion à la base de données

// pour mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// ou mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// ou mysqli avec une création non basée sur les objets
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
// ne peut pas utiliser $user->save() ici ou il pensera que c'est une mise à jour !

echo $user->id; // 2
```

Et c'était aussi simple que cela d'ajouter un nouvel utilisateur ! Maintenant qu'il y a une ligne d'utilisateur dans la base de données, comment la récupérer ?

```php
$user->find(1); // trouve id = 1 dans la base de données et le retourne.
echo $user->name; // 'Bobby Tables'
```

Et si vous voulez trouver tous les utilisateurs ?

```php
$users = $user->findAll();
```

Et avec une certaine condition ?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Voyez comme c'est amusant ? Installons-le et commençons !

## Installation

Installez simplement avec Composer

```php
composer require flightphp/active-record 
```

## Utilisation

Cela peut être utilisé comme une bibliothèque autonome ou avec le Framework PHP Flight. C'est entièrement à vous.

### Autonome
Assurez-vous simplement de passer une connexion PDO au constructeur.

```php
$pdo_connection = new PDO('sqlite:test.db'); // ceci n'est qu'un exemple, vous utiliseriez probablement une vraie connexion à la base de données

$User = new User($pdo_connection);
```

> Vous ne voulez pas toujours définir votre connexion à la base de données dans le constructeur ? Voir [Gestion des connexions à la base de données](#database-connection-management) pour d'autres idées !

### Enregistrer comme une méthode dans Flight
Si vous utilisez le Framework PHP Flight, vous pouvez enregistrer la classe ActiveRecord comme un service, mais honnêtement, vous n'avez pas à le faire.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// ensuite vous pouvez l'utiliser comme ceci dans un contrôleur, une fonction, etc.

Flight::user()->find(1);
```

## Méthodes `runway`

[runway](/awesome-plugins/runway) est un outil CLI pour Flight qui a une commande personnalisée pour cette bibliothèque.

```bash
# Utilisation
php runway make:record database_table_name [class_name]

# Exemple
php runway make:record users
```

Cela créera une nouvelle classe dans le répertoire `app/records/` sous le nom `UserRecord.php` avec le contenu suivant :

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Classe ActiveRecord pour le tableau users.
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
     * @var array $relations Définit les relations pour le modèle
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

Trouve un enregistrement et l'assigne à l'objet courant. Si vous passez un `$id` quelconque, il effectuera une recherche sur la clé primaire avec cette valeur. Si rien n'est passé, il trouvera simplement le premier enregistrement dans le tableau.

De plus, vous pouvez lui passer d'autres méthodes d'aide pour interroger votre tableau.

```php
// trouver un enregistrement avec certaines conditions au préalable
$user->notNull('password')->orderBy('id DESC')->find();

// trouver un enregistrement par un id spécifique
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Trouve tous les enregistrements dans le tableau que vous spécifiez.

```php
$user->findAll();
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

##### Clés primaires basées sur du texte

Si vous avez une clé primaire basée sur du texte (comme un UUID), vous pouvez définir la valeur de la clé primaire avant l'insertion de deux façons.

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
		// vous pouvez aussi définir la primaryKey de cette façon au lieu du tableau ci-dessus.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // ou cependant vous devez générer vos identifiants uniques
	}
}
```

Si vous ne définissez pas la clé primaire avant l'insertion, elle sera définie sur `rowid` et la base de données la générera pour vous, mais elle ne persistera pas car ce champ peut ne pas exister dans votre tableau. C'est pourquoi il est recommandé d'utiliser l'événement pour gérer cela automatiquement.

#### `update(): boolean|ActiveRecord`

Met à jour l'enregistrement courant dans la base de données.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Insère ou met à jour l'enregistrement courant dans la base de données. Si l'enregistrement a un id, il mettra à jour, sinon il insérera.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Note :** Si vous avez des relations définies dans la classe, il sauvegardera récursivement ces relations également si elles ont été définies, instanciées et ont des données modifiées à mettre à jour. (v0.4.0 et supérieur)

#### `delete(): boolean`

Supprime l'enregistrement courant de la base de données.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Vous pouvez aussi supprimer plusieurs enregistrements en exécutant une recherche au préalable.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Les données modifiées font référence aux données qui ont été changées dans un enregistrement.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// rien n'est "modifié" à ce stade.

$user->email = 'test@example.com'; // maintenant email est considéré comme "modifié" car il a changé.
$user->update();
// maintenant il n'y a plus de données modifiées car elles ont été mises à jour et persistées dans la base de données

$user->password = password_hash()'newpassword'); // maintenant ceci est modifié
$user->dirty(); // ne passer rien effacera toutes les entrées modifiées.
$user->update(); // rien ne sera mis à jour car rien n'a été capturé comme modifié.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // nom et password sont tous deux mis à jour.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Ceci est un alias pour la méthode `dirty()`. C'est un peu plus clair ce que vous faites.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // nom et password sont tous deux mis à jour.
```

#### `isDirty(): boolean` (v0.4.0)

Retourne `true` si l'enregistrement courant a été modifié.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Réinitialise l'enregistrement courant à son état initial. C'est vraiment bien à utiliser dans des comportements de type boucle. Si vous passez `true`, il réinitialisera également les données de requête utilisées pour trouver l'objet courant (comportement par défaut).

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

Après avoir exécuté une méthode `find()`, `findAll()`, `insert()`, `update()`, ou `save()`, vous pouvez obtenir le SQL qui a été construit et l'utiliser pour des fins de débogage.

## Méthodes de requête SQL
#### `select(string $field1 [, string $field2 ... ])`

Vous pouvez sélectionner seulement quelques colonnes d'un tableau si vous le souhaitez (c'est plus performant sur des tableaux très larges avec de nombreuses colonnes)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Vous pouvez techniquement choisir un autre tableau aussi ! Pourquoi pas ?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Vous pouvez même joindre à un autre tableau dans la base de données.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Vous pouvez définir des arguments where personnalisés (vous ne pouvez pas définir de paramètres dans cette instruction where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Note de sécurité** - Vous pourriez être tenté de faire quelque chose comme `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. S'il vous plaît NE FAITES PAS CELA !!! Cela est vulnérable à ce qu'on appelle des attaques par injection SQL. Il y a beaucoup d'articles en ligne, veuillez googler "sql injection attacks php" et vous trouverez beaucoup d'articles sur ce sujet. La bonne façon de gérer cela avec cette bibliothèque est, au lieu de cette méthode `where()`, de faire quelque chose comme `$user->eq('id', $id)->eq('name', $name)->find();` Si vous devez absolument faire cela, la bibliothèque `PDO` a `$pdo->quote($var)` pour l'échapper pour vous. Seulement après avoir utilisé `quote()` pouvez-vous l'utiliser dans une instruction `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Regroupez vos résultats par une condition particulière.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Triez la requête retournée d'une certaine manière.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limitez le nombre d'enregistrements retournés. Si un second int est donné, ce sera offset, limit comme en SQL.

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

### Conditions OR

Il est possible d'entourer vos conditions d'une instruction OR. Cela se fait soit avec les méthodes `startWrap()` et `endWrap()`, soit en remplissant le 3e paramètre de la condition après le champ et la valeur.

```php
// Méthode 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Cela évaluera à `id = 1 AND (name = 'demo' OR name = 'test')`

// Méthode 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Cela évaluera à `id = 1 OR name = 'demo'`
```

## Relations
Vous pouvez définir plusieurs types de relations en utilisant cette bibliothèque. Vous pouvez définir des relations un-à-plusieurs et un-à-un entre les tableaux. Cela nécessite un peu de configuration supplémentaire dans la classe au préalable.

Définir le tableau `$relations` n'est pas difficile, mais deviner la syntaxe correcte peut être confus.

```php
protected array $relations = [
	// vous pouvez nommer la clé comme vous voulez. Le nom de l'ActiveRecord est probablement bon. Ex : user, contact, client
	'user' => [
		// requis
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // c'est le type de relation

		// requis
		'Some_Class', // c'est la classe ActiveRecord "autre" à laquelle ceci fera référence

		// requis
		// en fonction du type de relation
		// self::HAS_ONE = la clé étrangère qui référence la jointure
		// self::HAS_MANY = la clé étrangère qui référence la jointure
		// self::BELONGS_TO = la clé locale qui référence la jointure
		'local_or_foreign_key',
		// juste pour info, cela ne joint aussi qu'à la clé primaire du modèle "autre"

		// optionnel
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // conditions supplémentaires que vous voulez lors de la jointure de la relation
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// optionnel
		'back_reference_name' // c'est si vous voulez référencer en arrière cette relation vers elle-même Ex : $user->contact->user;
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

Maintenant que nous avons configuré les références, nous pouvons les utiliser très facilement !

```php
$user = new User($pdo_connection);

// trouver l'utilisateur le plus récent.
$user->notNull('id')->orderBy('id desc')->find();

// obtenir les contacts en utilisant la relation :
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// ou nous pouvons aller dans l'autre sens.
$contact = new Contact();

// trouver un contact
$contact->find();

// obtenir l'utilisateur en utilisant la relation :
echo $contact->user->name; // c'est le nom de l'utilisateur
```

Plutôt cool, hein ?

### Chargement impatient

#### Aperçu
Le chargement impatient résout le problème de requête N+1 en chargeant les relations à l'avance. Au lieu d'exécuter une requête séparée pour les relations de chaque enregistrement, le chargement impatient récupère toutes les données liées en une seule requête supplémentaire par relation.

> **Note :** Le chargement impatient n'est disponible que pour v0.7.0 et supérieur.

#### Utilisation de base
Utilisez la méthode `with()` pour spécifier quelles relations charger de manière impatiente :
```php
// Charge les utilisateurs avec leurs contacts en 2 requêtes au lieu de N+1
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    foreach ($u->contacts as $contact) {
        echo $contact->email; // Pas de requête supplémentaire !
    }
}
```

#### Relations multiples
Chargez plusieurs relations à la fois :
```php
$users = $user->with(['contacts', 'profile', 'settings'])->findAll();
```

#### Types de relations

##### HAS_MANY
```php
// Charge de manière impatiente tous les contacts pour chaque utilisateur
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
// Charge de manière impatiente un contact pour chaque utilisateur
$users = $user->with('contact')->findAll();
foreach ($users as $u) {
    // $u->contact est déjà chargé comme un objet
    echo $u->contact->email;
}
```

##### BELONGS_TO
```php
// Charge de manière impatiente les utilisateurs parents pour tous les contacts
$contacts = $contact->with('user')->findAll();
foreach ($contacts as $c) {
    // $c->user est déjà chargé
    echo $c->user->name;
}
```
##### Avec find()
Le chargement impatient fonctionne avec 
findAll()
 et 
find()
:

```php
$user = $user->with('contacts')->find(1);
// Utilisateur et tous leurs contacts chargés en 2 requêtes
```
#### Avantages en termes de performance
Sans chargement impatient (problème N+1) :
```php
$users = $user->findAll(); // 1 requête
foreach ($users as $u) {
    $contacts = $u->contacts; // N requêtes (une par utilisateur !)
}
// Total : 1 + N requêtes
```

Avec chargement impatient :

```php
$users = $user->with('contacts')->findAll(); // 2 requêtes totales
foreach ($users as $u) {
    $contacts = $u->contacts; // 0 requêtes supplémentaires !
}
// Total : 2 requêtes (1 pour les utilisateurs + 1 pour tous les contacts)
```
Pour 10 utilisateurs, cela réduit les requêtes de 11 à 2 - une réduction de 82 % !

#### Notes importantes
- Le chargement impatient est complètement optionnel - le chargement paresseux fonctionne toujours comme avant
- Les relations déjà chargées sont automatiquement ignorées
- Les références arrière fonctionnent avec le chargement impatient
- Les rappels de relation sont respectés pendant le chargement impatient

#### Limitations
- Le chargement impatient imbriqué (p. ex., 
with(['contacts.addresses'])
) n'est pas pris en charge actuellement
- Les contraintes de chargement impatient via des fermetures ne sont pas prises en charge dans cette version

## Définition de données personnalisées
Parfois, vous pourriez avoir besoin d'attacher quelque chose d'unique à votre ActiveRecord, comme un calcul personnalisé qui pourrait être plus facile à attacher à l'objet qui serait ensuite passé à un template.

#### `setCustomData(string $field, mixed $value)`
Vous attachez les données personnalisées avec la méthode `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

Et ensuite vous le référencez simplement comme une propriété d'objet normale.

```php
echo $user->page_view_count;
```

## Événements

Une autre fonctionnalité super géniale de cette bibliothèque concerne les événements. Les événements sont déclenchés à certains moments en fonction de certaines méthodes que vous appelez. Ils sont très très utiles pour configurer des données pour vous automatiquement.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Ceci est vraiment utile si vous avez besoin de définir une connexion par défaut ou quelque chose comme ça.

```php
// index.php ou bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // n'oubliez pas la référence &
		// vous pourriez faire ceci pour définir automatiquement la connexion
		$config['connection'] = Flight::db();
		// ou ceci
		$self->transformAndPersistConnection(Flight::db());
		
		// Vous pouvez aussi définir le nom du tableau de cette façon.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Ceci est probablement utile seulement si vous avez besoin d'une manipulation de requête à chaque fois.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// exécutez toujours id >= 0 si c'est votre truc
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Celui-ci est probablement plus utile si vous avez toujours besoin d'exécuter une logique à chaque fois que cet enregistrement est récupéré. Avez-vous besoin de déchiffrer quelque chose ? Avez-vous besoin d'exécuter une requête de comptage personnalisée à chaque fois (pas performant mais bon) ?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// déchiffrage de quelque chose
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// peut-être stocker quelque chose de personnalisé comme une requête ???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Ceci est probablement utile seulement si vous avez besoin d'une manipulation de requête à chaque fois.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// exécutez toujours id >= 0 si c'est votre truc
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Similaire à `afterFind()` mais vous pouvez le faire pour tous les enregistrements au lieu d'un seul !

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// faites quelque chose de cool comme afterFind()
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
		// définissez des valeurs par défaut solides
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

Peut-être que vous avez un cas d'utilisation pour changer des données après leur insertion ?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// faites ce que vous voulez
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// ou quoi que ce soit....
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
		// définissez des valeurs par défaut solides
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Peut-être que vous avez un cas d'utilisation pour changer des données après leur mise à jour ?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// faites ce que vous voulez
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// ou quoi que ce soit....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Ceci est utile si vous voulez que des événements se produisent à la fois lors des insertions ou des mises à jour. Je vais vous épargner la longue explication, mais je suis sûr que vous pouvez deviner ce que c'est.

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

Pas sûr de ce que vous voudriez faire ici, mais pas de jugement ici ! Allez-y !

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'He was a brave soldier... :cry-face:';
	} 
}
```

## Gestion des connexions à la base de données

Lorsque vous utilisez cette bibliothèque, vous pouvez définir la connexion à la base de données de plusieurs façons différentes. Vous pouvez la définir dans le constructeur, vous pouvez la définir via une variable de configuration `$config['connection']` ou vous pouvez la définir via `setDatabaseConnection()` (v0.4.1).

```php
$pdo_connection = new PDO('sqlite:test.db'); // pour exemple
$user = new User($pdo_connection);
// ou
$user = new User(null, [ 'connection' => $pdo_connection ]);
// ou
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Si vous voulez éviter de toujours définir une `$database_connection` à chaque fois que vous appelez un active record, il y a des moyens d'y remédier !

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

// Et maintenant, pas d'arguments requis !
$user = new User();
```

> **Note :** Si vous prévoyez de faire des tests unitaires, le faire de cette façon peut ajouter quelques défis aux tests unitaires, mais globalement, parce que vous pouvez injecter votre connexion avec `setDatabaseConnection()` ou `$config['connection']`, ce n'est pas trop mal.

Si vous avez besoin de rafraîchir la connexion à la base de données, par exemple si vous exécutez un script CLI de longue durée et avez besoin de rafraîchir la connexion de temps en temps, vous pouvez la redéfinir avec `$your_record->setDatabaseConnection($pdo_connection)`.

## Contribution

S'il vous plaît, faites-le. :D

### Configuration

Lorsque vous contribuez, assurez-vous d'exécuter `composer test-coverage` pour maintenir une couverture de test à 100 % (ceci n'est pas une vraie couverture de test unitaire, plus comme des tests d'intégration).

Assurez-vous aussi d'exécuter `composer beautify` et `composer phpcs` pour corriger les erreurs de linting.

## Licence

MIT