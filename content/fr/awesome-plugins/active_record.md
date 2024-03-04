# Flight Active Record

Un enregistrement actif mappe une entité de base de données sur un objet PHP. En termes simples, si vous avez une table utilisateurs dans votre base de données, vous pouvez "traduire" une ligne de cette table en une classe `User` et un objet `$user` dans votre code. Voir [exemple de base](#basic-example).

## Exemple de Base

Supposons que vous ayez la table suivante :

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	nom TEXT, 
	mot_de_passe TEXT 
);
```

Maintenant vous pouvez configurer une nouvelle classe pour représenter cette table :

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
	public function __construct($connexion_base_de_donnees)
	{
		// vous pouvez le définir de cette façon
		parent::__construct($connexion_base_de_donnees, 'users');
		// ou de cette façon
		parent::__construct($connexion_base_de_donnees, null, [ 'table' => 'users']);
	}
}
```

Maintenant regardez la magie opérer !

```php
// pour sqlite
$connexion_base_de_donnees = new PDO('sqlite:test.db'); // ceci est juste un exemple, vous utiliseriez probablement une véritable connexion de base de données

// pour mysql
$connexion_base_de_donnees = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'nom_utilisateur', 'mot_de_passe');

// ou mysqli
$connexion_base_de_donnees = new mysqli('localhost', 'nom_utilisateur', 'mot_de_passe', 'test_db');
// ou mysqli avec création non basée sur les objets
$connexion_base_de_donnees = mysqli_connect('localhost', 'nom_utilisateur', 'mot_de_passe', 'test_db');

$user = new User($connexion_base_de_donnees);
$user->name = 'Bobby Tables';
$user->password = password_hash('un mot de passe sympa');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('un autre mot de passe sympa!!!');
$user->insert();
// impossible d'utiliser $user->save() ici sinon il pensera que c'est une mise à jour !

echo $user->id; // 2
```

Et il était si facile d'ajouter un nouvel utilisateur ! Maintenant qu'il y a une ligne utilisateur dans la base de données, comment la récupérer ?

```php
$user->find(1); // trouve id = 1 dans la base de données et le renvoie.
echo $user->nom; // 'Bobby Tables'
```

Et si vous voulez trouver tous les utilisateurs ?

```php
$users = $user->findAll();
```

Et avec une certaine condition ?

```php
$users = $user->like('nom', '%mamma%')->findAll();
```

Vous voyez comme c'est amusant ? Installer et commencer !

## Installation

Installez simplement avec Composer

```php
composer require flightphp/active-record
```

## Utilisation

Cela peut être utilisé comme une bibliothèque autonome ou avec le Framework PHP Flight. C'est complètement à vous.

### Autonome
Assurez-vous simplement de passer une connexion PDO au constructeur.

```php
$connexion_pdo = new PDO('sqlite:test.db'); // ceci est juste un exemple, vous utiliseriez probablement une véritable connexion de base de données

$User = new User($connexion_pdo);
```

### Framework PHP Flight
Si vous utilisez le Framework PHP Flight, vous pouvez enregistrer la classe ActiveRecord en tant que service (mais honnêtement, vous n'êtes pas obligé).

```php
Flight::register('user', 'User', [ $connexion_pdo ]);

// ensuite vous pouvez l'utiliser comme ceci dans un contrôleur, une fonction, etc.

Flight::user()->find(1);
```

## Fonctions CRUD

#### `find($id = null): boolean|ActiveRecord`

Trouve un enregistrement et l'assigne à l'objet courant. Si vous transmettez un `$id` quelconque, il effectuera une recherche sur la clé primaire avec cette valeur. Si rien n'est transmis, il trouvera simplement le premier enregistrement dans la table.

De plus, vous pouvez lui transmettre d'autres méthodes d'aide pour interroger votre table.

```php
// trouver un enregistrement avec certaines conditions préalables
$user->notNull('mot_de_passe')->orderBy('id DESC')->find();

// trouver un enregistrement par un id spécifique
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Trouve tous les enregistrements dans la table que vous spécifiez.

```php
$user->findAll();
```

#### `insert(): boolean|ActiveRecord`

Insère l'enregistrement actuel dans la base de données.

```php
$user = new User($connexion_pdo);
$user->nom = 'démonstration';
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

#### `delete(): boolean`

Supprime l'enregistrement actuel de la base de données.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Vous pouvez également supprimer plusieurs enregistrements en exécutant une recherche au préalable.

```php
$user->like('nom', 'Bob%')->delete();
```

#### `dirty(array $dirty = []): ActiveRecord`

Les données "dirty" font référence aux données qui ont été modifiées dans un enregistrement.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// rien n'est "dirty" à ce stade.

$user->email = 'test@example.com'; // maintenant l'e-mail est considéré comme sale car il a été modifié.
$user->update();
// maintenant il n'y a plus de données sales car elles ont été mises à jour et persistées dans la base de données

$user->password = password_hash()'nouveaumotdepasse'); // maintenant c'est sale
$user->dirty(); // ne passer rien effacera toutes les entrées sales.
$user->update(); // rien ne sera mis à jour car rien n'a été capturé comme sale.

$user->dirty([ 'name' => 'quelquechose', 'password' => password_hash('un mot de passe différent') ]);
$user->update(); // le nom et le mot de passe sont mis à jour.
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Réinitialise l'enregistrement actuel à son état initial. C'est vraiment utile à utiliser dans des comportements de type boucle.
Si vous passez `true`, il réinitialisera également les données de requête qui ont été utilisées pour trouver l'objet actuel (comportement par défaut).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($connexion_base_de_donnees);

foreach($users as $user) {
	$user_company->reset(); // partir d'une page vierge
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

## Méthodes de Requête SQL

#### `select(string $field1 [, string $field2 ... ])`

Vous pouvez sélectionner uniquement quelques-unes des colonnes dans une table si vous le souhaitez (c'est plus performant sur des tables très larges avec de nombreuses colonnes)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Vous pouvez techniquement choisir une autre table aussi ! Pourquoi pas ?!

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
$user->where('id=1 AND name="demonstration"')->find();
```

**Note de sécurité** - Vous pourriez être tenté de faire quelque chose comme `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. S'il vous plaît NE FAITES PAS CELA !!! Cela est susceptible de ce qu'on appelle les attaques par injection SQL. Il y a beaucoup d'articles en ligne, veuillez rechercher "attaques d'injection SQL php" et vous trouverez de nombreux articles sur ce sujet. La bonne façon de gérer cela avec cette bibliothèque est au lieu de cette méthode `where()`, vous feriez quelque chose comme `$user->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Groupe les résultats par une condition particulière.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Tri de la requête retournée d'une certaine manière.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limite la quantité d'enregistrements renvoyés. Si un second int est donné, ce sera un décalage, la limite fonctionne comme dans SQL.

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
$user->like('nom', 'de')->find();
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

Configurer le tableau `$relations` n'est pas difficile, mais deviner la syntaxe correcte peut être déroutant.

```php
protected array $relations = [
	// vous pouvez nommer la clé comme vous le souhaitez. Le nom de l'ActiveRecord est probablement bon. Par exemple : user, contact, client
	'user' => [
		// requis
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // c'est le type de relation

		// requis
		'Certaine_Classe', // c'est la classe ActiveRecord "autre" que cela référencera

		// requis
		// en fonction du type de relation
		// self::HAS_ONE = la clé étrangère qui fait référence à la jonction
		// self::HAS_MANY = la clé étrangère qui fait référence à la jointure
		// self::BELONGS_TO = la clé locale qui fait référence à la jonction
		'cle_locale_ou_etrangere',
		// juste pour info, cela se joint également uniquement à la clé primaire du modèle "autre"

		// facultatif
		[ 'eq' => [ 'id_client', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // conditions supplémentaires que vous voulez lorsque vous joignez la relation
		// $record->eq('id_client', 5)->select('COUNT(*) as count')->limit(5))

		// facultatif
		'nom_de_reference_arriere' // ceci est si vous voulez référencer cette relation en arrière vers elle-même Ex: $user->contact->user;
	];
]
```

```php
class User extends ActiveRecord{
	protected array $relations = [
		'contacts' => [ self::HAS_MANY, Contact::class, 'user_id' ],
		'contact' => [ self::HAS_ONE, Contact::class, 'user_id' ],
	];

	public function __construct($connexion_base_de_donnees)
	{
		parent::__construct($connexion_base_de_donnees, 'users');
	}
}

class Contact extends ActiveRecord{
	protected array $relations = [
		'user' => [ self::BELONGS_TO, User::class, 'user_id' ],
		'user_with_backref' => [ self::BELONGS_TO, User::class, 'user_id', [], 'contact' ],
	];
	public function __construct($connexion_base_de_donnees)
	{
		parent::__construct($connexion_base_de_donnees, 'contacts');
	}
}
```

Maintenant que les références sont configurées, nous pouvons les utiliser très facilement !

```php
$user = new User($connexion_pdo);

// trouve l'utilisateur le plus récent.
$user->notNull('id')->orderBy('id desc')->find();

// obtenir des contacts en utilisant la relation :
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// ou nous pouvons aller dans l'autre sens.
$contact = new Contact();

// trouve un contact
$contact->find();

// obtenir un utilisateur en utilisant la relation :
echo $contact->user->nom; // c'est le nom de l'utilisateur
```

Vraiment cool non ?

## Définition de Données Personnalisées
Parfois, vous pouvez avoir besoin de joindre quelque chose de unique à votre ActiveRecord comme un calcul personnalisé qui pourrait être plus facile à joindre à l'objet qui serait ensuite transmis à un modèle par exemple.

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

Une autre fonctionnalité super géniale de cette bibliothèque concerne les événements. Les événements sont déclenchés à certains moments en fonction de certaines méthodes que vous appelez. Ils sont très très utiles pour configurer les données automatiquement.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Cela est vraiment utile si vous avez besoin de définir une connexion par défaut ou quelque chose comme ça.

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

Cela est probablement utile si vous avez besoin d'une manipulation de la requête à chaque fois.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($connexion_base_de_donnees)
	{
		parent::__construct($connexion_base_de_donnees, 'users');
	}

	protected function```markdown
## Contribuer

S'il vous plaît faites-le.

## Configuration

Lorsque vous contribuez, assurez-vous d'exécuter `composer test-coverage` pour maintenir une couverture de test à 100% (il ne s'agit pas d'une véritable couverture de test unitaire, mais plutôt de tests d'intégration).

Assurez-vous également d'exécuter `composer beautify` et `composer phpcs` pour corriger les erreurs de formatage.

## Licence

MIT
```