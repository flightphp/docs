# Flight Active Record 

Un enregistrement actif est la cartographie d'une entité de base de données vers un objet PHP. En termes simples, si vous avez une table utilisateurs dans votre base de données, vous pouvez "traduire" une ligne de cette table vers une classe `User` et un objet `$user` dans votre code source. Voir [exemple de base](#exemple-de-base).

## Exemple de Base

Supposons que vous avez la table suivante:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Maintenant, vous pouvez configurer une nouvelle classe pour représenter cette table:

```php
/**
 * Une classe ActiveRecord est généralement au singulier
 * 
 * Il est fortement recommandé d'ajouter les propriétés de la table comme commentaires ici
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// vous pouvez le configurer de cette manière
		parent::__construct($database_connection, 'users');
		// ou de cette manière
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Maintenant, regardez la magie opérer!

```php
// pour sqlite
$database_connection = new PDO('sqlite:test.db'); // ceci est juste un exemple, vous utiliseriez probablement une véritable connexion de base de données

// pour mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// ou mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// ou mysqli avec une création non basée sur un objet
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('quel mot de passe cool');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('quel mot de passe cool à nouveau !!!');
$user->insert();
// impossible d'utiliser $user->save() ici sinon il pensera que c'est une mise à jour !

echo $user->id; // 2
```

Et c'était aussi simple d'ajouter un nouvel utilisateur ! Maintenant qu'il y a une ligne d'utilisateur dans la base de données, comment la récupérer ?

```php
$user->find(1); // trouve id = 1 dans la base de données et la renvoie.
echo $user->name; // 'Bobby Tables'
```

Et si vous voulez trouver tous les utilisateurs ?

```php
$users = $user->findAll();
```

Et avec une certaine condition?

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

Cela peut être utilisé comme une bibliothèque autonome ou avec le framework PHP Flight. Complètement à vous.

### Autonome
Assurez-vous simplement de passer une connexion PDO au constructeur.

```php
$pdo_connection = new PDO('sqlite:test.db'); // ceci est juste un exemple, vous utiliseriez probablement une véritable connexion de base de données

$User = new User($pdo_connection);
```

### Framework PHP Flight
Si vous utilisez le framework PHP Flight, vous pouvez enregistrer la classe ActiveRecord en tant que service (mais vous n'êtes honnêtement pas obligé).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// puis vous pouvez l'utiliser comme ceci dans un contrôleur, une fonction, etc.

Flight::user()->find(1);
```

## Fonctions CRUD

#### `find($id = null) : boolean|ActiveRecord`

Trouve un enregistrement et l'assigne à l'objet actuel. Si vous passez un `$id` de quelque sorte, cela effectuera une recherche sur la clé primaire avec cette valeur. Si rien n'est passé, il trouvera simplement le premier enregistrement dans la table.

De plus, vous pouvez lui passer d'autres méthodes d'aide pour interroger votre table.

```php
// trouver un enregistrement avec certaines conditions au préalable
$user->notNull('password')->orderBy('id DESC')->find();

// trouver un enregistrement par un ID spécifique
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Trouve tous les enregistrements dans la table que vous spécifiez.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Renvoie `true` si l'enregistrement actuel a été «hydraté» (récupéré de la base de données).

```php
$user->find(1);
// si un enregistrement est trouvé avec des données...
$user->isHydrated(); // vrai
```

#### `insert(): boolean|ActiveRecord`

Insère l'enregistrement actuel dans la base de données.

```php
$user = new User($pdo_connection);
$user->name = 'démonstration';
$user->password = md5('démonstration');
$user->insert();
```

#### `update(): boolean|ActiveRecord`

Met à jour l'enregistrement actuel dans la base de données.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Insère ou met à jour l'enregistrement actuel dans la base de données. Si l'enregistrement a un identifiant, il sera mis à jour, sinon il sera inséré.

```php
$user = new User($pdo_connection);
$user->name = 'démonstration';
$user->password = md5('démonstration');
$user->save();
```

**Remarque :** Si vous avez des relations définies dans la classe, elles enregistreront récursivement ces relations également si elles ont été définies, instanciées et ont des données à mettre à jour. (v0.4.0 et supérieur)

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

Les données sales se rapportent aux données qui ont été modifiées dans un enregistrement.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// rien n'est "sale" à ce stade.

$user->email = 'test@example.com'; // maintenant l'e-mail est considéré comme étant "sale" car il a été modifié.
$user->update();
// maintenant, il n'y a pas de données sales parce qu'elles ont été mises à jour et persistées dans la base de données.

$user->password = password_hash('nouveaumotdepasse'); // maintenant c'est sale
$user->dirty(); // ne rien passer effacera toutes les entrées sales.
$user->update(); // rien ne sera mis à jour car rien n'a été capturé comme étant sale.

$user->dirty([ 'name' => 'quelquechose', 'password' => password_hash('un mot de passe différent') ]);
$user->update(); // à la fois le nom et le mot de passe sont mis à jour.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Il s'agit d'un alias de la méthode `dirty()`. C'est un peu plus clair ce que vous faites.

```php
$user->copyFrom([ 'name' => 'quelquechose', 'password' => password_hash('un mot de passe différent') ]);
$user->update(); // à la fois le nom et le mot de passe sont mis à jour.
```

#### `isDirty(): boolean` (v0.4.0)

Renvoie `true` si l'enregistrement actuel a été modifié.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // vrai
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Réinitialise l'enregistrement actuel à son état initial. C'est vraiment utile à utiliser dans des comportements de boucle.
Si vous passez `true`, il réinitialisera également les données de la requête qui ont été utilisées pour trouver l'objet actuel (comportement par défaut).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // commencer avec une page propre
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Après avoir exécuté une méthode `find()`, `findAll()`, `insert()`, `update()` ou `save()`, vous pouvez obtenir le SQL qui a été généré et l'utiliser à des fins de débogage.

## Méthodes de Requête SQL
#### `select(string $field1 [, string $field2 ... ])`

Vous pouvez sélectionner uniquement quelques-unes des colonnes dans une table si vous le souhaitez (c'est plus performant sur des tables très larges avec de nombreuses colonnes)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Vous pouvez techniquement choisir une autre table aussi ! Pourquoi diable pas ?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Vous pouvez même joindre une autre table dans la base de données.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Vous pouvez définir des arguments where personnalisés (vous ne pouvez pas définir de paramètres dans cette instruction where)

```php
$user->where('id=1 AND name="démonstration"')->find();
```

**Note de sécurité** - Vous pourriez être tenté de faire quelque chose comme `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. S'il vous plaît, NE FAITES PAS CECI !!! Cela est susceptible de ce que l'on appelle des attaques par injection SQL. Il y a beaucoup d'articles en ligne, veuillez rechercher "attaques par injection SQL php" et vous trouverez beaucoup d'articles sur ce sujet. La bonne façon de gérer cela avec cette bibliothèque est au lieu de cette méthode `where()`, vous feriez quelque chose comme `$user->eq('id', $id)->eq('name', $name)->find();` Si vous devez absolument le faire, la bibliothèque `PDO` a `$pdo->quote($var)` pour l'échapper pour vous. Seulement après avoir utilisé `quote()`, vous pouvez l'utiliser dans une instruction `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Regroupez vos résultats par une condition particulière.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Triez la requête renvoyée d'une certaine manière.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limitez le nombre d'enregistrements renvoyés. Si un deuxième entier est donné, il sera compensé, limit comme en SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## Conditions WHERE
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Où `champ = $valeur`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Où `champ <> $valeur`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

Où `champ IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Où `champ IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Où `champ > $valeur`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Où `champ < $valeur`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Où `champ >= $valeur`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Où `champ <= $valeur`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Où `champ LIKE $valeur` ou `champ NOT LIKE $valeur`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Où `champ IN($valeur)` ou `champ NOT IN($valeur)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Où `champ ENTRE $valeur ET $valeur1`

```php
$user->between('id', [1, 2])->find();
```

## Relations
Vous pouvez définir plusieurs types de relations en utilisant cette bibliothèque. Vous pouvez définir des relations un à plusieurs et un à un entre les tables. Cela nécessite une petite configuration supplémentaire dans la classe au préalable.

Définir le tableau `$relations` n'est pas difficile, mais deviner la syntaxe correcte peut être déroutant.

```php
protected array $relations = [
	// vous pouvez nommer la clé comme vous le souhaitez. Le nom de la ActiveRecord est probablement bon. Ex: user, contact, client
	'user' => [
		// requis
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // c'est le type de relation

		// obligatoire
		'Some_Class', // c'est la classe ActiveRecord "autre" à laquelle cela fera référence

		// obligatoire
		// en fonction du type de relation
		// self::HAS_ONE = la clé étrangère qui fait référence à la jointure
		// self::HAS_MANY = la clé étrangère qui fait référence à la jointure
		// self::BELONGS_TO = la clé locale qui fait référence à la jointure
		'clé_locale_ou_étrangère',
		// juste pour info, cela rejoint également uniquement la clé primaire du modèle "autre"

		// optionnel
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' => 5 ], // conditions supplémentaires que vous souhaitez lors de la jointure de la relation
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// optionnel
		'back_reference_name' // c'est si vous voulez faire référence à cette relation en arrière à lui-même Ex: $user->contact->user;
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

Maintenant que les références sont configurées, nous pouvons les utiliser très facilement !

```php
$user = new User($pdo_connection);

// trouver l'utilisateur le plus récent.
$user->notNull('id')->orderBy('id desc')->find();

// obtenir des contacts en utilisant la relation :
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// ou nous pouvons aller dans l'autre sens.
$contact = new Contact();

// trouver un contact
$contact->find();

// obtenir l'utilisateur en utilisant la relation :
echo $contact->user->name; cela est le nom de l'utilisateur
```

Assez cool, n'est-ce pas ?

## Configuration de Données Personnalisées
Parfois, vous pouvez avoir besoin d'attacher quelque chose d'unique à votre ActiveRecord tel qu'un calcul personnalisé qui pourrait être plus simple à attacher à l'objet qui serait ensuite passé à un modèle par exemple.

#### `setCustomData(string $field, mixed $value)`
Vous attachez les données personnalisées avec la méthode `setCustomData()`.
```php
$user->setCustomData('nombre_vues_page', $nombre_vues_page);
```

Et ensuite vous le référencez simplement comme une propriété d'objet normale.

```php
echo $user->nombre_vues_page;
```

## Événements

Encore une fonctionnalité super géniale de cette bibliothèque concerne les événements. Les événements se déclenchent à certains moments en fonction de certaines méthodes que vous appelez. Ils sont très très utiles pour configurer automatiquement des données pour vous.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Ceci est vraiment utile si vous devez définir une connexion par défaut ou quelque chose comme ça.

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
		$config['connexion'] = Flight::db();
		// ou ceci
		$self->transformAndPersistConnection(Flight::db());
		
		// Vous pouvez également définir le nom de la table de cette manière.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Cela est probablement seulement utile si vous avez besoin d'une manipulation de requête à chaque fois.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($connexion_base_de_donnees)
	{
		parent::__construct($connexion_base_de_donnees, 'users');
	}

	protected function beforeFind(self $self) {
		// exécutez toujours id >= 0 si c'est votre truc
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Celui-ci est probablement plus utile si vous avez toujours besoin d'exécuter une certaine logique à chaque fois que cet enregistrement est récupéré. Avez-vous besoin de décrypter quelque chose ? Avez-vous besoin d'exécuter une requête de décompte personnalisée chaque fois (pas performant mais peu importe) ?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($connexion_base_de_donnees)
	{
		parent::__construct($connexion_base_de_donnees, 'users');
	}

	protected function afterFind(self $self) {
		// décryptage de quelque chose
		$self->secret = votreFonctionDeDecryptage($self->secret, $une_clé);

		// peut-être stocker quelque chose de personnalisé comme une requête ?
		$self->setCustomData('nombre_vues', $self->select('COUNT(*) count')->from('vues_utilisateur')->eq('id_utilisateur', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Cela est probablement seulement utile si vous avez besoin d'une manipulation de requête à chaque fois.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($connexion_base_de_donnees)
	{
		parent::__construct($connexion_base_de_donnees, 'users');
	}

	protected function beforeFindAll(self $self) {
		// exécutez toujours id >= 0 si c'est votre truc
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Similaire à `afterFind()` mais vous permet de le faire pour tous les enregistrements !

```php
class User extends flight\ActiveRecord {
	
	public function __construct($connexion_base_de_donnees)
	{
		parent::__construct($connexion_base_de_donnees, 'users');
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
	
	public function __construct($connexion_base_de_donnees)
	{
		parent::__construct($connexion_base_de_donnees, 'users');
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

Peut-être avez-vous besoin de changer des données après leur insertion ?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($connexion_base_de_donnees)
	{
		parent::__construct($connexion_base_de_donnees, 'users');
	}

	protected function afterInsert(self $self) {
		// à vous de jouer
		Flight::cache()->set('identifiant_inseré_le_plus_récemment', $self->id);
		// ou quoi que ce soit d'autre....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Vraiment utile si vous avez besoin de définir des valeurs par défaut à chaque mise à jour.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($connexion_base_de_donnees)
	{
		parent::__construct($connexion_base_de_donnees, 'users');
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
	
	public function __construct($connexion_base_de_donnees)
	{
		parent::__construct($connexion_base_de_donnees, 'users');
	}

	protected function afterInsert(self $self) {
		// à vous de jouer
		Flight::cache()->set('identifiant_utilisateur_les_plus_récents', $self->id);
		// ou quoi que ce soit d'autre....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Cela est utile si vous voulez que des événements se produisent à la fois lors des insertions ou des mises à jour. Je vous épargne la longue explication, mais je suis sûr que vous pouvez deviner ce que c'est.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($connexion_base_de_donnees)
	{
		parent::__construct($connexion_base_de_donnees, 'users');
	}

	protected function beforeSave(self $self) {
		$self->dernière_mise_à_jour = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

Je ne suis pas sûr de ce que vous voudriez faire ici, mais pas de jugements ici ! Vous y allez !

```php
class User extends flight\ActiveRecord {
	
	public function __construct($connexion_base_de_donnees)
	{
		parent::__construct($connexion_base_de_donnees, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Il était un brave soldat... :cry-face:';
	} 
}
```

## Gestion de la Connexion à la Base de Données

Lorsque vous utilisez cette bibliothèque, vous pouvez configurer la connexion à la base de données de plusieurs manières. Vous pouvez définir la connexion dans le constructeur, vous pouvez la définir via une variable de configuration `$config['connexion']` ou vous pouvez la définir via `setDatabaseConnection()` (v0.4.1). 

```php
$connexion_pdo = new PDO('sqlite:test.db'); // par exemple
$user = new User($connexion_pdo);
// ou
$user = new User(null, [ 'connexion' => $connexion_pdo ]);
// ou
$user = new User();
$user->setDatabaseConnection($connexion_pdo);
```

Si vous devez actualiser la connexion à la base de données, par exemple si vous exécutez un script CLI de longue durée et devez actualiser la connexion de temps en temps, vous pouvez réinitialiser la connexion avec `$votre_enregistrement->setDatabaseConnection($connexion_pdo)`.

## Contribuer

S'il vous plaît faites-le. :D

## Configuration

Lorsque vous contribuez, assurez-vous de lancer `composer test-coverage` pour maintenir une couverture de 100% des tests (ce n'est pas une véritable couverture de test unitaire, mais plutôt des tests d'intégration).

Assurez-vous également de lancer `composer beautify` et `composer phpcs` pour corriger les erreurs de formatage.

## Licence

MIT