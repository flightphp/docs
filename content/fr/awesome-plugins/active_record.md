# Flight Active Record 

Un enregistrement actif est la mise en correspondance d'une entité de base de données avec un objet PHP. En termes simples, si vous avez une table d'utilisateurs dans votre base de données, vous pouvez "traduire" une ligne de cette table en une classe `User` et un objet `$user` dans votre code source. Voir [exemple de base](#exemple-de-base).

Cliquez [ici](https://github.com/flightphp/active-record) pour accéder au dépôt sur GitHub.

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
$connexion_base_de_donnees = new PDO('sqlite:test.db'); // c'est juste un exemple, vous utiliseriez probablement une véritable connexion à une base de données

// pour mysql
$connexion_base_de_donnees = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'nom_utilisateur', 'mot_de_passe');

// ou mysqli
$connexion_base_de_donnees = new mysqli('localhost', 'nom_utilisateur', 'mot_de_passe', 'test_db');
// ou mysqli avec création non basée sur un objet
$connexion_base_de_donnees = mysqli_connect('localhost', 'nom_utilisateur', 'mot_de_passe', 'test_db');

$user = new User($connexion_base_de_donnees);
$user->name = 'Bobby Tables';
$user->password = password_hash('un mot de passe génial');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('encore un mot de passe génial!!!');
$user->insert();
// ne peut pas utiliser $user->save() ici sinon il pensera qu'il s'agit d'une mise à jour !

echo $user->id; // 2
```

Et c'était aussi simple d'ajouter un nouvel utilisateur ! Maintenant qu'il y a une ligne utilisateur dans la base de données, comment la récupérer ?

```php
$user->find(1); // trouve l'identifiant = 1 dans la base de données et le renvoie.
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

Cela peut être utilisé comme une bibliothèque autonome ou avec le Framework PHP Flight. C'est complètement à vous.

### Autonome
Assurez-vous simplement de passer une connexion PDO au constructeur.

```php
$connexion_pdo = new PDO('sqlite:test.db'); // c'est juste un exemple, vous utiliseriez probablement une véritable connexion à une base de données

$User = new User($connexion_pdo);
```

> **Je ne veux pas toujours définir votre connexion à la base de données dans le constructeur? Voir [Gestion de la connexion à la base de données](#gestion-de-la-connexion-à-la-base-de-données) pour d'autres idées!**

### Enregistrer en tant que méthode dans Flight
Si vous utilisez le Framework PHP Flight, vous pouvez enregistrer la classe ActiveRecord en tant que service, mais honnêtement, vous n'êtes pas obligé.

```php
Flight::register('user', 'User', [ $connexion_pdo ]);

// ensuite vous pouvez l'utiliser comme ceci dans un contrôleur, une fonction, etc.

Flight::user()->find(1);
```

## Méthodes `runway`

[runway](https://docs.flightphp.com/awesome-plugins/runway) est un outil CLI pour Flight qui a une commande personnalisée pour cette bibliothèque. 

```bash
# Utilisation
php runway make:record nom_table_base_de_données [nom_classe]

# Exemple
php runway make:record utilisateurs
```

Cela créera une nouvelle classe dans le répertoire `app/records/` en tant que `UserRecord.php` avec le contenu suivant :

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
		// 'nom_relation' => [ self::HAS_MANY, 'RelatedClass', 'foreign_key' ],
	];

    /**
     * Constructeur
     * @param mixed $connexionBaseDeDonnées La connexion à la base de données
     */
    public function __construct($connexionBaseDeDonnées)
    {
        parent::__construct($connexionBaseDeDonnées, 'users');
    }
}
```

## Fonctions CRUD

#### `find($id = null) : boolean|ActiveRecord`

Trouve un enregistrement et l'attribue à l'objet actuel. Si vous passez un `$id` de quelque sorte, il effectuera une recherche sur la clé primaire avec cette valeur. Si rien n'est passé, il cherchera simplement le premier enregistrement dans la table.

En outre, vous pouvez lui passer d'autres méthodes d'aide pour interroger votre table.

```php
// trouver un enregistrement avec certaines conditions préalables
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
$user = new User($connexion_pdo);
$user->name = 'démo';
$user->password = md5('démo');
$user->insert();
```

##### Clés primaires basées sur du texte

Si vous avez une clé primaire basée sur du texte (comme un UUID), vous pouvez définir la valeur de la clé primaire avant l'insertion de deux manières.

```php
$user = new User($connexion_pdo, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'quelque-uuid';
$user->name = 'démo';
$user->password = md5('démo');
$user->insert(); // ou $user->save();
```

ou vous pouvez avoir la clé primaire générée automatiquement pour vous via des événements.

```php
class User extends flight\ActiveRecord {
	public function __construct($connexion_base_de_donnees)
	{
		parent::__construct($connexion_base_de_donnees, 'users', [ 'primaryKey' => 'uuid' ]);
		// vous pouvez également définir la clé primaire de cette manière au lieu du tableau ci-dessus.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // ou comment vous devez générer vos identifiants uniques
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

Insère ou met à jour l'enregistrement actuel dans la base de données. Si l'enregistrement a un identifiant, il sera mis à jour, sinon il sera inséré.

```php
$user = new User($connexion_pdo);
$user->name = 'démo';
$user->password = md5('démo');
$user->save();
```

**Remarque :** Si vous avez des relations définies dans la classe, elles sauvegarderont également récursivement ces relations si elles ont été définies, instanciées et ont des données à mettre à jour. (v0.4.0 et ultérieur)

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

Les données non valides se réfèrent aux données modifiées dans un enregistrement.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// rien n'est "non valide" à ce stade.

$user->email = 'test@example.com'; // maintenant l'e-mail est considéré comme "non valide" car il a changé.
$user->update();
// maintenant il n'y a pas de données invalides car elles ont été mises à jour et persistées dans la base de données

$user->password = password_hash('un autre mot de passe'); // maintenant c'est non valide
$user->dirty(); // en ne passant rien, toutes les entrées non valides seront effacées.
$user->update(); // rien ne sera mis à jour car rien n'a été capturé comme non valide.

$user->dirty([ 'name' => 'quelque chose', 'password' => password_hash('un mot de passe différent') ]);
$user->update(); // à la fois le nom et le mot de passe sont mis à jour.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

C'est un alias de la méthode `dirty()`. C'est un peu plus clair sur ce que vous faites.

```php
$user->copyFrom([ 'name' => 'quelque chose', 'password' => password_hash('un mot de passe différent') ]);
$user->update(); // à la fois le nom et le mot de passe sont mis à jour.
```

#### `isDirty(): boolean` (v0.4.0)

Retourne `true` si l'enregistrement actuel a été modifié.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@exemple.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Réinitialise l'enregistrement actuel à son état initial. C'est vraiment utile à utiliser dans des comportements de boucle.
Si vous passez `true`, cela réinitialisera également les données de la requête utilisées pour trouver l'objet actuel (comportement par défaut).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($connexion_pdo);

foreach($users as $user) {
	$user_company->reset(); // commencer avec une page propre
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Après avoir exécuté une méthode `find()`, `findAll()`, `insert()`, `update()` ou `save()`, vous pouvez obtenir le SQL qui a été construit et l'utiliser à des fins de débogage.

## Méthodes de requête SQL
#### `select(string $field1 [, string $field2 ... ])`

Vous pouvez sélectionner uniquement quelques colonnes dans une table si vous le souhaitez (c'est plus performant sur des tables très larges avec de nombreuses colonnes)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Vous pouvez techniquement choisir une autre table aussi ! Pourquoi pas ?

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
$user->where('id=1 AND name="démo"')->find();
```

**Remarque de sécurité** - Vous pourriez être tenté de faire quelque chose comme `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. S'il vous plaît, NE FAITES PAS CELA !!! Cela est susceptible de ce qu'on appelle des attaques par injection SQL. Il y a beaucoup d'articles en ligne, veuillez faire une recherche sur Google avec "attaques par injection SQL php" et vous trouverez de nombreux articles sur ce sujet. La bonne façon de gérer cela avec cette bibliothèque est au lieu de cette méthode `where()`, vous feriez quelque chose de plus comme `$user->eq('id', $id)->eq('name', $name)->find();` Si vous devez absolument le faire, la bibliothèque `PDO` a `$pdo->quote($var)` pour l'échapper pour vous. Seulement après avoir utilisé `quote()` pouvez-vous l'utiliser dans une instruction `where()`.

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

Limite la quantité d'enregistrements renvoyés. Si un deuxième entier est donné, il sera utilisé comme décalage, la limite fonctionne comme en SQL.

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

Là où `field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Là où `field IS NOT NULL`

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

Où `field IN($value)`ou `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Où `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

## Relations
Vous pouvez définir plusieurs types de relations en utilisant cette bibliothèque. Vous pouvez définir des relations un à plusieurs et un à un entre les tables. Cela nécessite une configuration supplémentaire dans la classe auparavant.

La configuration du tableau `$relations` n'est pas difficile, mais deviner la syntaxe correcte peut être déroutant.

```php
protected array $relations = [
	// vous pouvez nommer la clé comme vous le souhaitez. Le nom de l'ActiveRecord est probablement bon. Ex : user, contact, client
	'user' => [
		// requis
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // c'est le type de relation

		// requis
		'Certaine_Classe', // c'est la classe ActiveRecord "autre" à laquelle cela fera référence

		// requis
		// selon le type de relation
		// self::HAS_ONE = la clé étrangère qui fait référence à la jointure
		// self::HAS_MANY = la clé étrangère qui fait référence à la jointure
		// self::BELONGS_TO = la clé locale qui fait référence à la jointure
		'cle_primaire_ou_etrangere',
		// juste pour info, cela se joint également uniquement à la clé primaire du modèle "autre"

		// optionnel
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // conditions supplémentaires que vous souhaitez lors de la jointure de la relation
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// optionnel
		'nom_de_référence_arrière' // c'est si vous voulez renvoyer cette relation en arrière vers elle-même Ex: $user->contact->user;
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
$user = new User($pdo_connection);

// trouve l'utilisateur le plus récent.
$user->notNull('id')->orderBy('id desc')->find();

// obtenir les contacts en utilisant la relation :
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// ou nous pouvons faire le chemin inverse.
$contact = new Contact();

// trouve un contact
$contact->find();

// obtenir l'utilisateur en utilisant la relation :
echo $contact->user->name; // c'est le nom d'utilisateur
```

Vraiment cool non ?

## Définition de Données Personnalisées
Parfois, vous devez attacher quelque chose d'unique à votre ActiveRecord tel qu'un calcul personnalisé qui pourrait être plus facile à attacher à l'objet pour ensuite être passé à un modèle par exemple.

#### `setCustomData(string $field, mixed $value)`
Vous attachez les données personnalisées avec la méthode `setCustomData()`.
```php
$user->setCustomData('nombre_de_vues_page', $nombre_de_vues_page);
```

Et ensuite, vous le référencez simplement comme une propriété d'objet normale.

```php
echo $user->nombre_de_vues_page;
```

## Evénements

Une autre fonctionnalité super géniale de cette bibliothèque concerne les événements. Les événements sont déclenchés à certains moments en fonction de certaines méthodes que vous appelez. Ils sont très très utiles pour configurer les données automatiquement.

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
		// vous pourriez le faire pour définir automatiquement la connexion
		$config['connexion'] = Flight::db();
		// ou cela
		$self->transformAndPersistConnection(Flight::db());
		
		// Vous pouvez également définir le nom de la table de cette manière.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Cela sera probablement utile uniquement si vous avez besoin d'une manipulation de requête à chaque fois.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($connexion_base_de_donnees)
	{
		parent::__construct($connexion_base_de_donnees, 'users');
	}

	protected function beforeFind(self $self) {
		// toujours exécuter id >= 0 si c'est votre truc
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Celui-ci sera probablement plus utile si vous devez exécuter une logique à chaque fois que cet enregistrement est récupéré. Voulez-vous décrypter quelque chose ? Devez-vous exécuter une requête de décompte personnalisée à chaque fois (pas performant mais peu importe) ?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($connexion_base_de_donnees)
	{
		parent::__construct($connexion_base_de_donnees, 'users');
	}

	protected function afterFind(self $self) {
		// décryptage de quelque chose
		$self->secret = votrefonctiondedécryptage($self->secret, $une_clé);

		// stocker quelque chose de personnalisé comme une requête ???
		$self->setCustomData('nombre_vues', $self->select('COUNT(*) count')->from('vues_utilisateur')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Cela sera probablement utile uniquement si vous avez besoin d'une manipulation de requête à chaque fois.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($connexion_base_de_donnees)
	{
		parent::__construct($connexion_base_de_donnees, 'users');
	}

	protected function beforeFindAll(self $self) {
		// toujours exécuter id >= 0 si c'est votre truc
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Similaire à `afterFind()` mais vous pouvez le faire pour tous les enregistrements !

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

Peut-être avez-vous un cas d'utilisation pour modifier des données après leur insertion ?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($connexion_base_de_donnees)
	{
		parent::__construct($connexion_base_de_donnees, 'users');
	}

	protected function afterInsert(self $self) {
		// vous voyez
		Flight::cache()->set('identifiant_insertion_plus_récents', $self->id);
		// ou autre chose....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Vraiment utile si vous avez des valeurs par défaut à définir à chaque mise à jour.

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
		// vous voyez
		Flight::cache()->set('identifiant_utilisateur_plus_récemment_mis_à_jour', $self->id);
		// ou autre chose....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

C'est utile si vous voulez que des événements se produisent à la fois lors des insertions ou des mises à jour. Je vais vous épargner la longue explication, mais je suis sûr que vous pouvez deviner ce que c'est.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($connexion_base_de_donnees)
	{
		parent::__construct($connexion_base_de_donnees, 'users');
	}

	protected function beforeSave(self $self) {
		$self->last_updated = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

Je ne sais pas ce que vous voulez faire ici, mais aucune autocritique ici ! Allez-y !

```php
class User extends flight\ActiveRecord {
	
	public function __construct($connexion_base_de_donnees)
	{
		parent::__construct($connexion_base_de_donnees, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Il fut un brave soldat... :cry-face:';
	} 
}
```

## Gestion de la connexion à la base de données

Lorsque vous utilisez cette bibliothèque, vous pouvez définir la connexion à la base de données de différentes manières. Vous pouvez définir la connexion dans le constructeur, vous pouvez la définir via une variable de configuration `$config['connexion']` ou vous pouvez la définir via `setDatabaseConnection()` (v0.4.1). 

```php
$connexion_pdo = new PDO('sqlite:test.db'); // par exemple
$user = new User($connexion_pdo);
// ou
$user = new User(null, [ 'connexion' => $connexion_pdo ]);
// ou
$user = new User();
$user->setDatabaseConnection($connexion_pdo);
```

Si vous souhaitez éviter de définir toujours une `$connexion_base_de_donnees` chaque fois que vous appelez un ActiveRecord, il existe des moyens de contourner cela !

```php
// index.php ou bootstrap.php
// Définissez ceci en tant que classe enregistrée dans Flight
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$connexion_base_de_donnees = $config['connexion'] ?? Flight::db();
		parent::__construct($connexion_base_de_donnees, 'users', $config);
	}
}

// Et maintenant, aucun argument requis !
$user = new User();
```

> **Remarque :** Si vous envisagez de faire des tests unitaires, le faire de cette façon peut poser quelques défis pour les tests unitaires, mais dans l'ensemble, car vous pouvez injecter votre
connexion avec `setDatabaseConnection()` ou `$config['connexion']`, ce n'est pas si mal.

Si vous devez rafraîchir la connexion à la base de données, par exemple si vous exécutez un script CLI de longue durée et devez rafraîchir la connexion de temps en temps, vous pouvez réinitialiser la connexion avec `$votre_enregistrement->setDatabaseConnection($connexion_pdo)`.