# FlightPHP Active Record 

Un enregistrement actif fait correspondre une entité de base de données à un objet PHP. En termes simples, si vous avez une table users dans votre base de données, vous pouvez "traduire" une ligne dans cette table en une classe `User` et un objet `$user` dans votre base de code. Voir [exemple de base](#exemple-de-base).

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
 * Il est fortement recommandé d'ajouter les propriétés de la table sous forme de commentaires ici
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
$connexion_base_de_donnees = new PDO('sqlite:test.db'); // ceci est juste un exemple, vous utiliseriez probablement une véritable connexion à une base de données

// pour mysql
$connexion_base_de_donnees = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'nom_utilisateur', 'mot_de_passe');

// ou mysqli
$connexion_base_de_donnees = new mysqli('localhost', 'nom_utilisateur', 'mot_de_passe', 'test_db');
// ou mysqli avec une création non basée sur des objets
$connexion_base_de_donnees = mysqli_connect('localhost', 'nom_utilisateur', 'mot_de_passe', 'test_db');

$user = new User($connexion_base_de_donnees);
$user->name = 'Bobby Tables';
$user->password = password_hash('un mot de passe cool');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('un autre mot de passe cool !!!');
$user->insert();
// ne peut pas utiliser $user->save() ici sinon il pensera que c'est une mise à jour !

echo $user->id; // 2
```

Et c'était aussi simple d'ajouter un nouvel utilisateur ! Maintenant qu'il y a une ligne utilisateur dans la base de données, comment la récupérez-vous ?

```php
$user->find(1); // trouve id = 1 dans la base de données et le retourne.
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

Cela peut être utilisé en tant que bibliothèque autonome ou avec le framework Flight PHP. Complètement à vous.

### Autonome
Assurez-vous simplement de passer une connexion PDO au constructeur.

```php
$connexion_pdo = new PDO('sqlite:test.db'); // ceci est juste un exemple, vous utiliseriez probablement une véritable connexion à une base de données

$User = new User($connexion_pdo);
```

### Framework Flight PHP
Si vous utilisez le framework Flight PHP, vous pouvez inscrire la classe ActiveRecord en tant que service (mais vous n'êtes honnêtement pas obligé).

```php
Flight::register('utilisateur', 'User', [ $connexion_pdo ]);

// ensuite vous pouvez l'utiliser de cette façon dans un contrôleur, une fonction, etc.

Flight::utilisateur()->find(1);
```

## Référence de l'API
### Fonctions CRUD

#### `find($id = null) : boolean|ActiveRecord`

Trouve un enregistrement et l'assigne à l'objet actuel. Si vous transmettez une `$id` de quelque sorte, il effectuera une recherche sur la clé primaire avec cette valeur. Si rien n'est transmis, il trouvera simplement le premier enregistrement dans la table.

De plus, vous pouvez lui transmettre d'autres méthodes d'aide pour interroger votre table.

```php
// trouver un enregistrement avec des conditions préalables
$user->notNull('password')->orderBy('id DESC')->find();

// trouver un enregistrement par un identifiant spécifique
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

#### `delete(): boolean`

Supprime l'enregistrement actuel de la base de données.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Les données dirty font référence aux données qui ont été modifiées dans un enregistrement.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// rien n'est "dirty" jusqu'à ce point.

$user->email = 'test@example.com'; // maintenant l'e-mail est considéré comme "dirty" car il a été modifié.
$user->update();
// maintenant il n'y a pas de données dirty car elles ont été mises à jour et persistées dans la base de données

$user->password = password_hash()'newpassword'); // maintenant c'est dirty
$user->dirty(); // ne rien passer effacera toutes les entrées dirty.
$user->update(); // rien ne sera mis à jour car rien n'a été capturé comme dirty.

$user->dirty([ 'name' => 'quelque chose', 'password' => password_hash('un mot de passe différent') ]);
$user->update(); // à la fois name et password sont mis à jour.
```

### Méthodes de requête SQL
#### `select(string $champ1 [, string $champ2 ... ])`

Vous pouvez sélectionner uniquement quelques colonnes dans une table si vous le souhaitez (c'est plus performant sur des tables très larges avec de nombreuses colonnes)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Vous pouvez techniquement choisir une autre table aussi ! Pourquoi diable pas ?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $nom_table, string $condition_jointure)`

Vous pouvez même vous joindre à une autre table dans la base de données.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $conditions_where)`

Vous pouvez définir des arguments where personnalisés (vous ne pouvez pas définir de paramètres dans cette instruction where)

```php
$user->where('id=1 AND name="démonstration"')->find();
```

**Remarque de sécurité** - Vous pourriez être tenté de faire quelque chose comme `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. S'il vous plaît NE FAITES PAS CECI !!! Cela est susceptible d'être une attaque par injection SQL. Il y a beaucoup d'articles en ligne, recherchez "attaques par injection SQL php" sur Google et vous trouverez beaucoup d'articles sur ce sujet. La bonne façon de gérer cela avec cette bibliothèque est au lieu de cette méthode `where()`, vous feriez quelque chose de plus comme `$user->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Groupe les résultats par une condition particulière.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Trie la requête retournée d'une certaine manière.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limite le nombre d'enregistrements retournés. Si un deuxième entier est donné, ce sera un décalage et une limite, comme en SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

### Conditions WHERE
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

### Relations
Vous pouvez définir plusieurs types de relations en utilisant cette bibliothèque. Vous pouvez définir des relations un->many et one->one entre les tables. Cela nécessite une configuration un peu plus avancée dans la classe au préalable.

Le réglage de l'array `$relations` n'est pas difficile, mais deviner la syntaxe correcte peut être déroutant.

```php
protected array $relations = [
	// vous pouvez nommer la clé comme vous le souhaitez. Le nom de l'ActiveRecord est probablement bon. Par exemple : user, contact, client
	'whatever_active_record' => [
		// requis
		self::HAS_ONE, // c'est le type de relation

		// requis
		'Some_Class', // c'est la classe ActiveRecord "autre" à laquelle celle-ci fera référence

		// requis
		'clé_locale', // c'est la clé_locale qui fait référence à la jointure.
		// juste une information, cela ne joint également qu'à la clé primaire du modèle "autre"

		// optionnel
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // méthodes personnalisées que vous voulez exécuter. [] si vous n'en voulez pas.

		// optionnel
		'nom_de_référence_retour' // c'est si vous voulez faire référence à cette relation de retour à elle-même Ex: $user->contact->user;
	];
]
```

```php
class User extends ActiveRecord{
	protected array $relations = [
		'contacts' => [ self::HAS_MANY, Contact::class, 'user_id' ],
		'contact' => [ self::HAS_ONE, Contact::class, 'user_id' ],
	];

	public function __construct($connexion_base_de_données)
	{
		parent::__construct($connexion_base_de_données, 'users');
	}
}

class Contact extends ActiveRecord{
	protected array $relations = [
		'user' => [ self::BELONGS_TO, User::class, 'user_id' ],
		'user_with_backref' => [ self::BELONGS_TO, User::class, 'user_id', [], 'contact' ],
	];
	public function __construct($connexion_base_de_données)
	{
		parent::__construct($connexion_base_de_données, 'contacts');
	}
}
```

Maintenant que les références sont configurées, vous pouvez les utiliser très facilement !

```php
$user = new User($connexion_pdo);

// trouver le dernier utilisateur.
$user->notNull('id')->orderBy('id desc')->find();

// obtenir des contacts en utilisant la relation :
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// ou nous pouvons faire le chemin inverse.
$contact = new Contact();

// trouver un contact
$contact->find();

// obtenir un utilisateur en utilisant la relation :
echo $contact->user->name; // c'est le nom de l'utilisateur
```

Assez cool n'est-ce pas ?

### Définition de données personnalisées
Parfois, vous devez attacher quelque chose d'unique à votre ActiveRecord, comme un calcul personnalisé qui pourrait être plus facile à attacher simplement à l'objet qui serait ensuite transmis à un modèle par exemple.

#### `setCustomData(string $champ, mixed $valeur)`
Vous attachez les données personnalisées avec la méthode `setCustomData()`.
```php
$user->setCustomData('page_view_count', $nombre_de_vues_de_page);
```

Et ensuite vous le référencez simplement comme une propriété normale de l'objet.

```php
echo $user->page_view_count;
```

### Événements

Une autre fonctionnalité super géniale de cette bibliothèque concerne les événements. Les événements sont déclenchés à certains moments en fonction de certaines méthodes que vous appelez. Ils sont très très utiles pour configurer automatiquement des données.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

C'est vraiment utile si vous devez définir une connexion par défaut ou quelque chose comme ça.

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

C'est probablement utile uniquement si vous avez besoin d'une manipulation de requête à chaque fois.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($connexion_base_de_données)
	{
		parent::__construct($connexion_base_de_données, 'users');
	}

	protected function beforeFind(self $self) {
		// exécuter toujours id >= 0 si c'est votre truc
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Celui-ci est probablement plus utile si vous devez toujours exécuter une certaine logique à chaque fois que cet enregistrement est récupéré. Dois-je décrypter quelque chose ? Dois-je exécuter une requête de comptage personnalisée à chaque fois (pas performant mais peu importe) ?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($connexion_base_de_données)
	{
		parent::__construct($connexion_base_de_données, 'users');
	}

	protected function afterFind(self $self) {
		// décrypter quelque chose
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// peut-être stocker quelque chose de personnalisé comme une requête ??
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

C'est probablement utile uniquement si vous avez besoin d'une manipulation de requête à chaque fois.

```php
class User```

# FlightPHP Active Record 

Un enregistrement actif est la mise en correspondance d'une entité de base de données à un objet PHP. En termes simples, si vous avez une table d'utilisateurs dans votre base de données, vous pouvez "traduire" une ligne de cette table en une classe `User` et un objet `$user` dans votre code. Voir [exemple de base](#exemple-de-base).

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
 * Une classe Active Record est généralement au singulier
 * 
 * Il est fortement recommandé d'ajouter les propriétés de la table en commentaires ici
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($connexion_base_donnees)
	{
		// vous pouvez le définir de cette façon
		parent::__construct($connexion_base_donnees, 'users');
		// ou de cette façon
		parent::__construct($connexion_base_donnees, null, [ 'table' => 'users']);
	}
}
```

Maintenant, regardez la magie opérer !

```php
// pour sqlite
$connexion_base_donnees = new PDO('sqlite:test.db'); // ceci est juste un exemple, vous utiliseriez probablement une véritable connexion à une base de données

// pour mysql
$connexion_base_donnees = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'nom_utilisateur', 'mot_de_passe');

// ou mysqli
$connexion_base_donnees = new mysqli('localhost', 'nom_utilisateur', 'mot_de_passe', 'test_db');
// ou mysqli avec création non basée sur les objets
$connexion_base_donnees = mysqli_connect('localhost', 'nom_utilisateur', 'mot_de_passe', 'test_db');

$user = new User($connexion_base_donnees);
$user->name = 'Bobby Tables';
$user->password = password_hash('un mot de passe cool');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('un autre mot de passe cool !!!');
$user->insert();
// ne peut pas utiliser $user->save() ici sinon il pensera que c'est une mise à jour !

echo $user->id; // 2
```

Et c'était aussi simple d'ajouter un nouvel utilisateur ! Maintenant qu'il y a une ligne utilisateur dans la base de données, comment la récupérez-vous ?

```php
$user->find(1); // trouve id = 1 dans la base de données et le retourne.
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

Cela peut être utilisé en tant que bibliothèque autonome ou avec le framework Flight PHP. Complètement à vous.

### Autonome
Assurez-vous simplement de passer une connexion PDO au constructeur.

```php
$connexion_pdo = new PDO('sqlite:test.db'); // ceci est juste un exemple, vous utiliseriez probablement une véritable connexion à une base de données

$User = new User($connexion_pdo);
```

### Framework Flight PHP
Si vous utilisez le framework Flight PHP, vous pouvez inscrire la classe ActiveRecord en tant que service (mais vous n'êtes honnêtement pas obligé).

```php
Flight::register('utilisateur', 'User', [ $connexion_pdo ]);

// ensuite vous pouvez l'utiliser de cette façon dans un contrôleur, une fonction, etc.

Flight::utilisateur()->find(1);
```

## Référence de l'API
### Fonctions CRUD

#### `find($id = null) : boolean|ActiveRecord`

Trouve un enregistrement et l'assigne à l'objet actuel. Si vous transmettez une `$id` de quelque sorte, il effectuera une recherche sur la clé primaire avec cette valeur. Si rien n'est transmis, il trouvera simplement le premier enregistrement dans la table.

De plus, vous pouvez lui transmettre d'autres méthodes d'aide pour interroger votre table.

```php
// trouver un enregistrement avec des conditions préalables
$user->notNull('password')->orderBy('id DESC')->find();

// trouver un enregistrement par un identifiant spécifique
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

#### `delete(): boolean`

Supprime l'enregistrement actuel de la base de données.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Les données dirty font référence aux données qui ont été modifiées dans un enregistrement.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// rien n'est "dirty" jusqu'à ce point.

$user->email = 'test@example.com'; // maintenant l'e-mail est considéré comme "dirty" car il a été modifié.
$user->update();
// maintenant il n'y a pas de données dirty car elles ont été mises à jour et persistées dans la base de données

$user->password = password_hash()'newpassword'); // maintenant c'est dirty
$user->dirty(); // ne rien passer effacera toutes les entrées dirty.
$user->update(); // rien ne sera mis à jour car rien n'a été capturé comme dirty.

$user->dirty([ 'name' => 'quelque chose', 'password' => password_hash('un mot de passe différent') ]);
$user->update(); // à la fois name et password sont mis à jour.
```

### Méthodes de requête SQL
#### `select(string $champ1 [, string $champ2 ... ])`

Vous pouvez sélectionner uniquement quelques colonnes dans une table si vous le souhaitez (c'est plus performant sur des tables très larges avec de nombreuses colonnes)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Vous pouvez techniquement choisir une autre table aussi ! Pourquoi diable pas ?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $nom_table, string $condition_jointure)`

Vous pouvez même vous joindre à une autre table dans la base de données.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $conditions_where)`

Vous pouvez définir des arguments where personnalisés (vous ne pouvez pas définir de paramètres dans cette instruction where)

```php
$user->where('id=1 AND name="démonstration"')->find();
```

**Remarque de sécurité** - Vous pourriez être tenté de faire quelque chose comme `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. S'il vous plaît NE FAITES PAS CECI !!! Cela est susceptible d'être une attaque par injection SQL. Il y a beaucoup d'articles en ligne, recherchez "attaques par injection SQL php" sur Google et vous trouverez beaucoup d'articles sur ce sujet. La bonne façon de gérer cela avec cette bibliothèque est au lieu de cette méthode `where()`, vous feriez quelque chose de plus comme `$user->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Groupe les résultats par une condition particulière.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Trie la requête retournée d'une certaine manière.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limite le nombre d'enregistrements retournés. Si un deuxième entier est donné, ce sera un décalage et une limite, comme en SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

### Conditions WHERE
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

### Relations
Vous pouvez définir plusieurs types de relations en utilisant cette bibliothèque. Vous pouvez définir des relations un->many et one->one entre les tables. Cela nécessite une configuration un peu plus avancée dans la classe au préalable.

Le réglage de l'array `$relations` n'est pas difficile, mais deviner la syntaxe correcte peut être déroutant.

```php
protected array $relations = [
	// vous pouvez nommer la clé comme vous le souhaitez. Le nom de l'ActiveRecord est probablement bon. Par exemple : user, contact, client
	'whatever_active_record' => [
		// requis
		self::HAS_ONE, // c'est le type de relation

		// requis
		'Some_Class', // c'est la classe ActiveRecord "autre" à laquelle celle-ci fera référence

		// requis
		'clé_locale', // c'est la clé_locale qui fait référence à la jointure.
		// juste une information, cela ne joint également qu'à la clé primaire du modèle "autre"

		// optionnel
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // méthodes personnalisées que vous voulez exécuter. [] si vous n'en voulez pas.

		// optionnel
		'nom_de_référence_retour' // c'est si vous voulez faire référence à cette relation de retour à elle-même Ex: $user->contact->user;
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

Maintenant que les références sont configurées, vous pouvez les utiliser très facilement !

```php
$user = new User($connexion_pdo);

// trouver le dernier utilisateur.
$user->notNull('id')->orderBy('id desc')->find();

// obtenir des contacts en utilisant la relation :
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// ou nous pouvons faire le chemin inverse.
$contact = new Contact();

// trouver un contact
$contact->find();

// obtenir un utilisateur en utilisant la relation :
echo $contact->user->name; // c'est le nom de l'utilisateur
```

Assez cool n'est-ce pas ?

### Définition de données personnalisées
Parfois, vous devez attacher quelque chose d'unique à votre ActiveRecord, comme un calcul personnalisé qui pourrait être plus facile à attacher simplement à l'objet qui serait ensuite transmis à un modèle par exemple.

#### `setCustomData(string $champ, mixed $valeur)`
Vous attachez les données personnalisées avec la méthode `setCustomData()`.
```php
$user->setCustomData('page_view_count', $nombre_de_vues_de_page);
```

Et ensuite vous le référencez simplement comme une propriété normale de l'objet.

```php
echo $user->page_view_count;
```

### Événements

Une autre fonctionnalité super géniale de cette bibliothèque concerne les événements. Les événements sont déclenchés à certains moments en fonction de certaines méthodes que vous appelez. Ils sont très très utiles pour configurer automatiquement des données.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

C'est vraiment utile si vous devez définir une connexion par défaut ou quelque chose comme ça.

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
		
		// Vous pouvez également définir le nom de la table de cette manière.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

C'est probablement utile uniquement si vous avez besoin d'une manipulation de requête à chaque fois.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($connexion_base_donnees)
	{
		parent::__construct($connexion_base_donnees, 'users');
	}

	protected function beforeFind(self $self) {
		// exécuter toujours id >= 0 si c'est votre truc
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Celui-ci est probablement plus utile si vous devez toujours exécuter une certaine logique à chaque fois que cet enregistrement est récupéré. Dois-je décrypter quelque chose ? Dois-je exécuter une requête de comptage personnalisée à chaque fois (pas performant mais peu importe) ?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($connexion_base_donnees)
	{
		parent::__construct($connexion_base_donnees, 'users');
	}

	protected function afterFind(self $self) {
		// décrypter quelque chose
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// peut-être stocker quelque chose de personnalisé comme une requête ??
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

C'est probablement utile uniquement si vous avez besoin d'une manipulation de requête à chaque fois.

```php
class User