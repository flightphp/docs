# FlightPHP Active Record

Un enregistrement actif mappe une entité de base de données à un objet PHP. En termes simples, si vous avez une table d'utilisateurs dans votre base de données, vous pouvez "traduire" une ligne dans cette table en une classe `User` et un objet `$utilisateur` dans votre base de code. Voir [exemple de base](#exemple-de-base).

## Exemple de base

Supposons que vous avez la table suivante :

```sql
CREATE TABLE utilisateurs (
	id INTEGER PRIMARY KEY, 
	nom TEXTE, 
	mot_de_passe TEXTE 
);
```

Maintenant, vous pouvez configurer une nouvelle classe pour représenter cette table :

```php
/**
 * Une classe ActiveRecord est généralement singulière
 * 
 * Il est fortement recommandé d'ajouter les propriétés de la table en tant que commentaires ici
 * 
 * @property int    $id
 * @property string $nom
 * @property string $mot_de_passe
 */ 
classe User s'étend flight\ActiveRecord {
	public function __construct($connexion_base_de_données)
	{
		// vous pouvez le définir de cette façon
 		parent::__construct($connexion_base_de_données, 'utilisateurs');
		// ou de cette façon
		parent::__construct($connexion_base_de_données, null, [ 'table' => 'utilisateurs']);
	}
}
```

Maintenant, regardez la magie opérer !

```php
// pour sqlite
$connexion_base_de_données = new PDO('sqlite:test.db'); // ceci est juste pour l'exemple, vous utiliseriez probablement une vraie connexion à la base de données

// pour mysql
$connexion_base_de_données = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'nom_utilisateur', 'mot_de_passe');

// ou mysqli
$connexion_base_de_données = new mysqli('localhost', 'nom_utilisateur', 'mot_de_passe', 'test_db');
// ou mysqli avec une création non basée sur un objet
$connexion_base_de_données = mysqli_connect('localhost', 'nom_utilisateur', 'mot_de_passe', 'test_db');

$utilisateur = new Utilisateur($connexion_base_de_données);
$utilisateur->nom = 'Bobby Tables';
$utilisateur->mot_de_passe = password_hash('un mot de passe sympa');
$utilisateur->insert();
// ou $utilisateur->save();

echo $utilisateur->id; // 1

$utilisateur->nom = 'Joseph Mamma';
$utilisateur->mot_de_passe = password_hash('un autre mot de passe sympa!!!');
$utilisateur->insert();
// ne peut pas utiliser $utilisateur->save() ici sinon il pensera que c'est une mise à jour !

echo $utilisateur->id; // 2
```

Et c'était aussi simple d'ajouter un nouvel utilisateur ! Maintenant qu'il y a une ligne utilisateur dans la base de données, comment la recuperer ?

```php
$utilisateur->find(1); // recherche l'id = 1 dans la base de données et la renvoie.
echo $utilisateur->nom; // 'Bobby Tables'
```

Et si vous voulez trouver tous les utilisateurs ?

```php
$utilisateurs = $utilisateur->findAll();
```

Et avec une certaine condition ?

```php
$utilisateurs = $utilisateur->like('nom', '%mamma%')->findAll();
```

Voyez comme c'est amusant ? Installons-le et commençons !

## Installation

Installez simplement avec Composer

```php
composer require flightphp/active-record 
```

## Utilisation

Cela peut être utilisé comme une bibliothèque autonome ou avec le cadre Flight PHP. C'est complètement à vous.

### Autonome
Assurez-vous simplement de passer une connexion PDO au constructeur.

```php
$connexion_pdo = new PDO('sqlite:test.db'); // ceci est juste un exemple, vous utiliseriez probablement une vraie connexion à la base de données

$Utilisateur = new User($connexion_pdo);
```

### Cadre Flight PHP
Si vous utilisez le cadre Flight PHP, vous pouvez enregistrer la classe ActiveRecord en tant que service (mais honnêtement, ce n'est pas obligatoire).

```php
Flight::register('utilisateur', 'Utilisateur', [ $connexion_pdo ]);

// ensuite, vous pouvez l'utiliser de cette manière dans un contrôleur, une fonction, etc.

Flight::utilisateur()->find(1);
```

## Référence de l'API
### Fonctions CRUD

#### `find($id = null) : boolean|ActiveRecord`

Trouve un enregistrement et l'assigne à l'objet actuel. Si vous passez un `$id` de n'importe quel type, il effectuera une recherche sur la clé primaire avec cette valeur. Si rien n'est passé, il trouvera simplement le premier enregistrement dans la table.

De plus, vous pouvez lui passer d'autres méthodes d'assistance pour interroger votre table.

```php
// trouver un enregistrement avec des conditions préalables
$utilisateur->notNull('mot_de_passe')->orderBy('id DESC')->find();

// trouver un enregistrement par un identifiant spécifique
$id = 123;
$utilisateur->find($id);
```

#### `findAll(): tableau<int,ActiveRecord>`

Trouve tous les enregistrements dans la table que vous spécifiez.

```php
$utilisateur->findAll();
```

#### `insert(): boolean|ActiveRecord`

Insère l'enregistrement actuel dans la base de données.

```php
$utilisateur = new Utilisateur($connexion_pdo);
$utilisateur->nom = 'démonstration';
$utilisateur->mot_de_passe = md5('démonstration');
$utilisateur->insert();
```

#### `update(): boolean|ActiveRecord`

Met à jour l'enregistrement actuel dans la base de données.

```php
$utilisateur->greaterThan('id', 0)->orderBy('id desc')->find();
$utilisateur->email = 'test@example.com';
$utilisateur->update();
```

#### `delete(): boolean`

Supprime l'enregistrement actuel de la base de données.

```php
$utilisateur->gt('id', 0)->orderBy('id desc')->find();
$utilisateur->delete();
```

Vous pouvez également supprimer plusieurs enregistrements en exécutant une recherche au préalable.

```php
$utilisateur->like('nom', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Les données sales font référence aux données modifiées dans un enregistrement.

```php
$utilisateur->greaterThan('id', 0)->orderBy('id desc')->find();

// rien n'est "dirty" à ce stade.

$utilisateur->email = 'test@example.com'; // maintenant l'e-mail est considéré comme "dirty" car il a été modifié.
$utilisateur->update();
// il n'y a maintenant aucune donnée sale car elle a été mise à jour et persistée dans la base de données

$utilisateur->mot_de_passe = password_hash()'nouveaumotdepasse'); // maintenant cela est "dirty"
$utilisateur->dirty(); // ne rien passer effacera toutes les entrées sales.
$utilisateur->update(); // rien ne sera mis à jour car rien n'a été capturé comme sale.

$utilisateur->dirty([ 'nom' => 'quelque chose', 'mot_de_passe' => password_hash('un mot de passe différent') ]);
$utilisateur->update(); // à la fois le nom et le mot de passe sont mis à jour.
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Réinitialise l'enregistrement actuel à son état initial. C'est vraiment utile à utiliser dans des comportements de boucle.
Si vous passez `true`, il réinitialisera également les données de la requête qui ont été utilisées pour trouver l'objet actuel (comportement par défaut).

```php
$utilisateurs = $utilisateur->greaterThan('id', 0)->orderBy('id desc')->find();
$entreprise_utilisateur = new UserCompany($connexion_pdo);

foreach($utilisateurs as $utilisateur) {
	$entreprise_utilisateur->reset(); // commencez avec une page vierge
	$entreprise_utilisateur->user_id = $utilisateur->id;
	$entreprise_utilisateur->company_id = $some_company_id;
	$entreprise_utilisateur->insert();
}
```

### Méthodes de requête SQL
#### `select(string $field1 [, string $field2 ... ])`

Vous pouvez sélectionner seulement quelques-unes des colonnes d'une table si vous le souhaitez (c'est plus performant sur des tables très larges avec de nombreuses colonnes)

```php
$utilisateur->select('id', 'nom')->find();
```

#### `from(string $table)`

Vous pouvez théoriquement choisir une autre table aussi! Pourquoi diable pas ?!

```php
$utilisateur->select('id', 'nom')->from('utilisateur')->find();
```

#### `join(string $table_name, string $join_condition)`

Vous pouvez même joindre une autre table dans la base de données.

```php
$utilisateur->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Vous pouvez définir des arguments WHERE personnalisés (vous ne pouvez pas définir de paramètres dans cette instruction WHERE)

```php
$utilisateur->where('id=1 AND name="démonstration"')->find();
```

**Note de sécurité** - Vous pourriez être tenté de faire quelque chose comme `$utilisateur->where("id = '{$id}' AND name = '{$name}'")->find();`. S'il vous plaît NE FAITES PAS CECI!!! C'est susceptible ce qui est connu sous le nom d'attaques par injection SQL. Il y a beaucoup d'articles en ligne, veuillez rechercher "attaques d'injection sql php" et vous trouverez beaucoup d'articles sur ce sujet. La bonne façon de gérer cela avec cette bibliothèque est plutôt que cette méthode `where()`, vous feriez quelque chose comme `$utilisateur->eq('id', $id)->eq('nom', $nom)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Groupes vos résultats par une condition particulière.

```php
$utilisateur->select('COUNT(*) as count')->groupBy('nom')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Trie la requête retournée d'une certaine manière.

```php
$utilisateur->orderBy('nom DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limite la quantité d'enregistrements retournés. Si un second entier est donné, il sera décalé, limité tout comme en SQL.

```php
$utilisateur->orderby('nom DESC')->limit(0, 10)->findAll();
```

### Conditions WHERE
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Où `field = $value`

```php
$utilisateur->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Où `field <> $value`

```php
$utilisateur->ne('id', 1)->find();
```

#### `isNull(string $field)`

Où `field IS NULL`

```php
$utilisateur->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Où `field IS NOT NULL`

```php
$utilisateur->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Où `field > $value`

```php
$utilisateur->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Où `field < $value`

```php
$utilisateur->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Où `field >= $value`

```php
$utilisateur->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Où `field <= $value`

```php
$utilisateur->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Où `field LIKE $value` ou `field NOT LIKE $value`

```php
$utilisateur->like('nom', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Où `field IN($value)` ou `field NOT IN($value)`

```php
$utilisateur->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Où `field BETWEEN $value AND $value1`

```php
$utilisateur->between('id', [1, 2])->find();
```

### Relations
Vous pouvez définir plusieurs types de relations en utilisant cette bibliothèque. Vous pouvez définir des relations un->plusieurs et un->une entre les tables. Cela nécessite une configuration supplémentaire dans la classe au préalable.

Définir le tableau `$relations` n'est pas difficile, mais deviner la syntaxe correcte peut être déroutant.

```php
protected array $relations = [
	// vous pouvez nommer la clé comme vous voulez. Le nom du ActiveRecord est probablement bien. Par exemple : utilisateur, contact, client
	'n'importe_quel_active_record' => [
		// requis
		self::HAS_ONE, // c'est le type de relation

		// requis
		'Certaine_Classe', // c'est la classe ActiveRecord "autre" à laquelle cela fera référence

		// requis
		'clé_locale', // c'est la clé locale qui fait référence à la jointure.
		// juste pour info, cela ne fait également référence qu'à la clé primaire du modèle "autre"

		// optionnel
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // méthodes personnalisées que vous voulez exécuter. [] si vous n'en voulez pas.

		// optionnel
		'nom_reference_retour' // c'est si vous voulez reprendre cette relation vers elle-même Ex : $utilisateur->contact->user;
	];
]
```

```php
classe Utilisateur s'étend ActiveRecord{
	protected array $relations = [
		'contacts' => [ self::HAS_MANY, Contact::class, 'user_id' ],
		'contact' => [ self::HAS_ONE, Contact::class, 'user_id' ],
	];

	public function __construct($connexion_base_de_données)
	{
		parent::__construct($connexion_base_de_données, 'utilisateurs');
	}
}

classe Contact s'étend ActiveRecord{
	protected array $relations = [
		'user' => [ self::BELONGS_TO, Utilisateur::class, 'user_id' ],
		'user_with_backref' => [ self::BELONGS_TO, Utilisateur::class, 'user_id', [], 'contact' ],
	];
	public function __construct($connexion_base_de_données)
	{
		parent::__construct($connexion_base_de_données, 'contacts');
	}
}
```

Maintenant que les références sont configurées, nous pouvons les utiliser très facilement !

```php
$utilisateur = new Utilisateur($connexion_pdo);

// trouvez l'utilisateur le plus récent.
$utilisateur->notNull('id')->orderBy('id desc')->find();

// obtenez des contacts en utilisant la relation :
foreach($utilisateur->contacts as $contact) {
	echo $contact->id;
}

// ou nous pouvons aller dans l'autre sens.
$contact = new Contact();

// trouvez un contact
$contact->find();

// obtenir l'utilisateur en utilisant la relation :
echo $contact->user->nom; // c'est le nom de l'utilisateur
```

Plutôt cool, non ?

### Définition de données personnalisées
Parfois, vous devrez peut-être attacher quelque chose d'unique à votre ActiveRecord comme un calcul personnalisé qui pourrait être plus facile à attacher à l'objet qui serait ensuite passé à un modèle, par exemple.

#### `setCustomData(string $field, mixed $value)`
Vous attachez les données personnalisées avec la méthode `setCustomData()`.
```php
$utilisateur->setCustomData('page_view_count', $page_view_count);
```

Et ensuite, il vous suffit de le référencer comme une propriété d'objet normale.

```php
echo $utilisateur->page_view_count;
```

### Événements

Une autre fonctionnalité super géniale de cette bibliothèque concerne les événements. Les événements sont déclenchés à certains moments en fonction de certaines méthodes que vous appelez. Ils sont très très utiles pour configurer automatiquement les données pour vous.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Cela est vraiment utile si vous devez définir une connexion par défaut ou quelque chose du genre.

```php
// index.php ou bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
classe User s'étend flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // n'oubliez pas la référence &
		// vous pourriez faire cela pour définir automatiquement la connexion
		$config['connection'] = Flight::db();
		// ou ceci
		$self->transformAndPersistConnectionprotected function onConstruct(self $self, array &$config) { // n'oubliez pas la référence &
		// you could do this to automatically set the connection
		$config['connection'] = Flight::db();
		// ou this
		$self->transformAndPersistConnection(Flight::db());
		
		// Vous pouvez également définir le nom de la table de cette manière.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Il est probablement utile que vous ayez une manipulation de requête à chaque fois.

```php
classe User s'étend flight\ActiveRecord {
	
	public function __construct($connexion_base_de_données)
	{
		parent::__construct($connexion_base_de_données, 'users');
	}

	protected function beforeFind(self $self) {
		// exécutez toujours id >= 0 si c'est comme ça que vous le faites
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Celui-ci est probablement plus utile si vous devez exécuter une logique à chaque fois que cet enregistrement est récupéré. Do you need to decrypt something? Do you need to run a custom count query each time (not performant but whatevs)?

```php
classe User s'étend flight\ActiveRecord {
	
	public function __construct($connexion_base_de_données)
	{
		parent::__construct($connexion_base_de_données, 'users');
	}

	protected function afterFind(self $self) {
		// décrypter quelque chose
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// peut-être stocker quelque chose de personnalisé comme une requête ???
		$self->setCustomData('nombre_vues', $self->select('COUNT(*) count')->from('vues_utilisateurs')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Il est probablement utile que vous ayez une manipulation de requête à chaque fois.

```php
classe User s'étend flight\ActiveRecord {
	
	public function __construct($connexion_base_de_données)
	{
		parent::__construct($connexion_base_de_données, 'users');
	}

	protected function beforeFindAll(self $self) {
		// exécutez toujours id >= 0 si c'est comme ça que vous le faites
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Similaire à `afterFind()` mais vous pouvez le faire pour tous les enregistrements !

```php
classe User s'étend flight\ActiveRecord {
	
	public function __construct($connexion_base_de_données)
	{
		parent::__construct($connexion_base_de_données, 'users');
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
classe User s'étend flight\ActiveRecord {
	
	public function __construct($connexion_base_de_données)
	{
		parent::__construct($connexion_base_de_données, 'users');
	}

	protected function beforeInsert(self $self) {
		// définir quelques valeurs par défaut
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

Peut-être avez-vous un cas d'utilisation pour modifier les données après leur insertion ?

```php
classe User s'étend flight\ActiveRecord {
	
	public function __construct($connexion_base_de_données)
	{
		parent::__construct($connexion_base_de_données, 'users');
	}

	protected function afterInsert(self $self) {
		// vous faites ce que vous voulez
		Flight::cache()->set('id_dernière_insertion_plus_récente', $self->id);
		// ou n'importe quoi d'autre....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Vraiment utile si vous avez besoin de définir des valeurs par défaut à chaque mise à jour.

```php
classe User s'étend flight\ActiveRecord {
	
	public function __construct($connexion_base_de_données)
	{
		parent::__construct($connexion_base_de_données, 'users');
	}

	protected function beforeInsert(self $self) {
		// définir quelques valeurs par défaut
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Peut-être avez-vous un cas d'utilisation pour modifier les données après leur mise à jour ?

```php
classe User s'étend flight\ActiveRecord {
	
	public function __construct($connexion_base_de_données)
	{
		parent::__construct($connexion_base_de_données, 'users');
	}

	protected function afterInsert(self $self) {
		// vous faites ce que vous voulez
		Flight::cache()->set('id_utilisateur_le_plus_récemment_mis_à_jour', $self->id);
		// ou n'importe quoi....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

C'est utile si vous voulez que des événements se produisent à la fois lors des insertions ou des mises à jour. Je vous épargne les longues explications, mais je suis sûr que vous pouvez deviner ce que c'est.

```php
classe User s'étend flight\ActiveRecord {
	
	public function __construct($connexion_base_de_données)
	{
		parent::__construct($connexion_base_de_données, 'users');
	}

	protected function beforeSave(self $self) {
		$self->last_updated = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

Je ne sais pas ce que vous voulez faire ici, mais pas de jugement ici ! Allez-y !

```php
classe User s'étend flight\ActiveRecord {
	
	public function __construct($connexion_base_de_données)
	{
		parent::__construct($connexion_base_de_données, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Il était un brave soldat... :cry-face:';
	} 
}
```

## Contribuer

S'il vous plaît faire.

### Configuration

Lorsque vous contribuez, assurez-vous de lancer `composer test-coverage` pour maintenir une couverture de test de 100 % (il ne s'agit pas d'une couverture de test unitaire réelle, mais plutôt de tests d'intégration).

Assurez-vous également de lancer `composer beautify` et `composer phpcs` pour corriger d'éventuelles erreurs de linting.

## Licence

MIT