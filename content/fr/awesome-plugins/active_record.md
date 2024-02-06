# FlightPHP Active Record 

Un enregistrement actif fait correspondre une entité de base de données à un objet PHP. En termes simples, si vous avez une table d'utilisateurs dans votre base de données, vous pouvez "traduire" une ligne dans cette table en une classe `User` et un objet `$user` dans votre base de code. Voir [exemple de base](#exemple-de-base).

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
 * Une classe Active Record est généralement singulière
 * 
 * Il est fortement recommandé d'ajouter les propriétés de la table comme commentaires ici
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($connexion_base_de_données)
	{
		// vous pouvez le configurer de cette manière
		parent::__construct($connexion_base_de_données, 'users');
		// ou de cette manière
		parent::__construct($connexion_base_de_données, null, [ 'table' => 'users']);
	}
}
```

Maintenant regardez la magie opérer !

```php
// pour SQLite
$connexion_base_de_données = new PDO('sqlite:test.db'); // c'est juste un exemple, vous utiliseriez probablement une vraie connexion à une base de données

// pour MySQL
$connexion_base_de_données = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'nom_utilisateur', 'mot_de_passe');

// ou MySQLi
$connexion_base_de_données = new mysqli('localhost', 'nom_utilisateur', 'mot_de_passe', 'test_db');
// ou MySQLi avec création non basée sur un objet
$connexion_base_de_données = mysqli_connect('localhost', 'nom_utilisateur', 'mot_de_passe', 'test_db');

$user = new User($connexion_base_de_données);
$user->name = 'Bobby Tables';
$user->password = password_hash('un mot de passe cool');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('un autre mot de passe cool!!!');
$user->insert();
// ne peut pas utiliser $user->save() ici sinon il pensera que c'est une mise à jour !

echo $user->id; // 2
```

Et c'était aussi simple d'ajouter un nouvel utilisateur ! Maintenant qu'il y a une ligne d'utilisateur dans la base de données, comment la récupérer ?

```php
$user->find(1); // trouve l'id = 1 dans la base de données et le renvoie.
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

Voyez comme c'est amusant ? Installons-le et commençons !

## Installation

Il suffit d'installer avec Composer

```php
composer require flightphp/active-record 
```

## Utilisation

Ceci peut être utilisé comme une bibliothèque autonome ou avec le framework Flight PHP. C'est totalement à vous de décider.

### Autonome
Assurez-vous simplement de passer une connexion PDO au constructeur.

```php
$pdo_connexion = new PDO('sqlite:test.db'); // c'est juste un exemple, vous utiliseriez probablement une vraie connexion à une base de données

$User = new User($pdo_connexion);
```

### Framework Flight PHP
Si vous utilisez le framework Flight PHP, vous pouvez enregistrer la classe ActiveRecord en tant que service (mais honnêtement, vous n'êtes pas obligé).

```php
Flight::register('utilisateur', 'Utilisateur', [ $pdo_connexion ]);

// ensuite vous pouvez l'utiliser de cette manière dans un contrôleur, une fonction, etc.

Flight::utilisateur()->find(1);
```

## Référence de l'API
### Fonctions CRUD

#### `find($id = null) : boolean|ActiveRecord`

Trouve un enregistrement et l'assigne à l'objet actuel. Si vous passez un `$id` de quelque sorte, il effectuera une recherche sur la clé primaire avec cette valeur. S'il n'y a rien de passé, il trouvera simplement le premier enregistrement dans la table.

En outre, vous pouvez lui passer d'autres méthodes d'aide pour interroger votre table.

```php
// trouver un enregistrement avec quelques conditions au préalable
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

#### `insert(): boolean|ActiveRecord`

Insère l'enregistrement actuel dans la base de données.

```php
$user = new User($pdo_connexion);
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

Les données "dirty" font référence aux données qui ont été modifiées dans un enregistrement.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// rien n'est "dirty" à ce stade.

$user->email = 'test@example.com'; // maintenant l'e-mail est considéré comme "dirty" car il a été modifié.
$user->update();
// il n'y a maintenant aucune donnée qui est considérée comme dirty car elle a été mise à jour et persistée dans la base de données

$user->password = password_hash()'nouveaumotdepasse'); // maintenant c'est dirty
$user->dirty(); // ne rien passer effacera toutes les entrées dirty.
$user->update(); // rien ne sera mis à jour car rien n'a été capturé comme dirty.

$user->dirty([ 'name' => 'quelquechose', 'password' => password_hash('un mot de passe différent') ]);
$user->update(); // à la fois le nom et le mot de passe sont mis à jour.
```

### Méthodes de requêtes SQL
#### `select(string $field1 [, string $field2 ... ])`

Vous pouvez sélectionner uniquement quelques-unes des colonnes dans une table si vous le souhaitez (c'est plus performant sur des tables vraiment larges avec de nombreuses colonnes)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Vous pouvez techniquement choisir une autre table aussi ! Pourquoi diable pas ?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Vous pouvez même Joindre à une autre table dans la base de données.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Vous pouvez définir des arguments WHERE personnalisés (vous ne pouvez pas définir de paramètres dans cette instruction WHERE)

```php
$user->where('id=1 AND name="démonstration"')->find();
```

**Note de sécurité** - Vous pourriez être tenté de faire quelque chose comme `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. S'il vous plaît NE FAITES PAS CELA !!! C'est susceptible de ce qu'on appelle des attaques par injection SQL. Il y a beaucoup d'articles en ligne, veuillez rechercher "attaques par injection SQL php" et vous trouverez beaucoup d'articles sur ce sujet. La bonne façon de gérer cela avec cette bibliothèque est plutôt que cette méthode `where()`, vous feriez quelque chose comme `$user->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Groupe vos résultats par une condition particulière.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Trier la requête retournée d'une certaine manière.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limite la quantité d'enregistrements retournés. Si un deuxième entier est donné, il sera l'offset, la limite comme en SQL.

```php
$user->orderBy('name DESC')->limit(0, 10)->findAll();
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
Vous pouvez configurer plusieurs types de relations en utilisant cette bibliothèque. Vous pouvez définir des relations un->plusieurs et un->un entre les tables. Cela nécessite une configuration supplémentaire dans la classe au préalable.

Le paramètre `$relations` n'est pas difficile à configurer, mais deviner la syntaxe correcte peut être déroutant.

```php
protected array $relations = [
	// vous pouvez nommer la clé comme bon vous semble. Le nom de l'ActiveRecord est probablement bon. Ex: utilisateur, contact, client
	'n'importequel_active_record' => [
		// requis
		self::HAS_ONE, // c'est le type de relation

		// requis
		'Certaine_Classe', // c'est la classe "autre" ActiveRecord à laquelle cela fera référence

		// requis
		'clé_locale', // c'est la clé locale qui fait référence à la jointure.
		// juste pour info, cela rejoint également uniquement à la clé primaire du modèle "autre"

		// optionnel
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // les méthodes personnalisées que vous souhaitez exécuter. [] si vous n'en voulez pas.

		// optionnel
		'nom_de_référence_arrière' // c'est si vous voulez faire référence en arrière à cette relation à elle-même Ex: $user->contact->user;
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
$user = new User($pdo_connexion);

// trouver le plus récent utilisateur.
$user->notNull('id')->orderBy('id desc')->find();

// obtenir des contacts en utilisant la relation :
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// ou nous pouvons aller dans l'autre sens.
$contact = new Contact();

// trouver un contact
$contact->find();

// obtenir un utilisateur en utilisant la relation :
echo $contact->user->name; // c'est le nom de l'utilisateur
```

Assez cool, non ?

### Réglage des données personnalisées
Parfois, vous devrez peut-être attacher quelque chose d'unique à votre ActiveRecord tel qu'un calcul personnalisé qui pourrait être plus facile à simplement attacher à l'objet qui serait ensuite transmis à un modèle par exemple.

#### `setCustomData(string $field, mixed $value)`
Vous attachez les données personnalisées avec la méthode `setCustomData()`.
```php
$user->setCustomData('nombre_de_vues_page', $nombre_de_vues_page);
```

Et ensuite vous le référencez simplement comme une propriété d'objet normale.

```php
echo $user->nombre_de_vues_page;
```

### Événements

Une autre fonctionnalité super géniale de cette bibliothèque concerne les événements. Les événements sont déclenchés à certains moments en fonction de certaines méthodes que vous appelez. Ils sont très très utiles pour configurer automatiquement des données pour vous.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Ceci est vraiment utile si vous avez besoin de définir une connexion par défaut ou quelque chose du genre.

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

Cela est probablement seulement utile si vous avez besoin d'une manipulation de requête à chaque fois.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($connexion_base_de_données)
	{
		parent::__construct($connexion_base_de_données, 'users');
	}

	protected function beforeFind(self $self) {
		// exécuter toujours id >= 0 si c'est ce que vous aimez
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Celui-ci est probablement plus utile si vous avez toujours besoin d'exécuter une certaine logique chaque fois que cet enregistrement est récupéré. Devez-vous déchiffrer quelque chose ? Devez-vous exécuter une requête de comptage personnalisée à chaque fois (pas performant mais bon) ?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($connexion_base_de_données)
	{
		parent::__construct($connexion_base_de_données, 'users');
	}

	protected function afterFind(self $self) {
		// déchiffrage de quelque chose
		$self->secret = votreFonctionDeDéchiffrement($self->secret, $une_clé);

		// peut-être stocker quelque chose de personnalisé comme une requête???
		$self->setCustomData('nombre_de_vues', $self->select('COUNT(*) count')->from('vues_utilisateur')->eq('id_utilisateur', $self->id)['count']; 
	} 
}
# FlightPHP Active Record 

Um registro ativo é mapear uma entidade de banco de dados para um objeto PHP. Falando claramente, se você tiver uma tabela de usuários em seu banco de dados, pode "traduzir" uma linha nessa tabela para uma classe `User` e um objeto `$user` em sua base de código. Veja [exemplo básico](#exemplo-básico).

## Exemplo Básico

Vamos supor que você tenha a seguinte tabela:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Agora você pode configurar uma nova classe para representar essa tabela:

```php
/**
 * Uma classe Active Record geralmente é singular
 * 
 * É altamente recomendável adicionar as propriedades da tabela como comentários aqui
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($conexao_banco_de_dados)
	{
		// você pode configurá-lo desta forma
		parent::__construct($conexao_banco_de_dados, 'users');
		// ou desta forma
		parent::__construct($conexao_banco_de_dados, null, [ 'table' => 'users']);
	}
}
```

Agora observe a mágica acontecer!

```php
// para SQLite
$conexao_banco_de_dados = new PDO('sqlite:test.db'); // este é apenas um exemplo, você provavelmente usaria uma conexão de banco de dados real

// para MySQL
$conexao_banco_de_dados = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'nome_usuário', 'senha');

// ou MySQLi
$conexao_banco_de_dados = new mysqli('localhost', 'nome_usuário', 'senha', 'test_db');
// ou MySQLi com criação não baseada em objeto
$conexao_banco_de_dados = mysqli_connect('localhost', 'nome_usuário', 'senha', 'test_db');

$user = new User($conexao_banco_de_dados);
$user->name = 'Bobby Tables';
$user->password = password_hash('uma senha legal');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('outra senha legal!!!');
$user->insert();
// não pode usar $user->save() aqui senão pensará que é uma atualização!

echo $user->id; // 2
```

E foi tão fácil adicionar um novo usuário! Agora que há uma linha de usuário no banco de dados, como você a retira?

```php
$user->find(1); // encontra id = 1 no banco de dados e o retorna.
echo $user->name; // 'Bobby Tables'
```

E se você quiser encontrar todos os usuários?

```php
$users = $user->findAll();
```

E com uma certa condição?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Veja quanta diversão isso é? Vamos instalar e começar!

## Instalação

Basta instalar com o Composer

```php
composer require flightphp/active-record 
```

## Uso

Isso pode ser usado como uma biblioteca autônoma ou com o framework Flight PHP. Totalmente com você.

### Autônomo
Apenas certifique-se de passar uma conexão PDO para o construtor.

```php
$pdo_conexão = new PDO('sqlite:test.db'); // este é apenas um exemplo, você provavelmente usaria uma conexão de banco de dados real

$User = new User($pdo_conexão);
```

### Framework Flight PHP
Se você estiver usando o framework Flight PHP, você pode registrar a classe ActiveRecord como um serviço (mas honestamente, você não precisa).

```php
Flight::register('usuário', 'Usuário', [ $pdo_conexão ]);

// então você pode usá-lo assim em um controlador, uma função, etc.

Flight::usuário()->find(1);
```

## Referência da API
### Funções CRUD

#### `find($id = null) : boolean|ActiveRecord`

Encontra um registro e o atribui ao objeto atual. Se você passar um `$id` de algum tipo, ele fará uma pesquisa na chave primária com esse valor. Se nada for passado, ele encontrará apenas o primeiro registro na tabela.

Além disso, você pode passar outros métodos auxiliares para consultar sua tabela.

```php
// encontre um registro com algumas condições anteriores
$user->notNull('password')->orderBy('id DESC')->find();

// find a record by a specific id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Encontra todos os registros na tabela que você especificar.

```php
$user->findAll();
```

#### `insert(): boolean|ActiveRecord`

Insere o registro atual no banco de dados.

```php
$user = new User($pdo_conexão);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

#### `update(): boolean|ActiveRecord`

Atualiza o registro atual no banco de dados.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `delete(): boolean`

Exclui o registro atual do banco de dados.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Dados "sujos" se referem aos dados que foram alterados em um registro.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nothing is "dirty" at this point.

$user->email = 'test@example.com'; // now email is considered "dirty" since it's changed.
$user->update();
// now there is no data that is dirty because it's been updated and persisted in the database

$user->password = password_hash()'newpassword'); // now this is dirty
$user->dirty(); // passing nothing will clear all the dirty entries.
$user->update(); // nothing will update cause nothing was captured as dirty.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // both name and password are updated.
```

### Métodos de Consulta SQL
#### `select(string $field1 [, string $field2 ... ])`

Você pode selecionar apenas algumas das colunas em uma tabela, se desejar (é mais eficiente em tabelas realmente largas com muitas colunas)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Você pode tecnicamente escolher outra tabela também! Por que diabos não ?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Você pode até mesmo juntar-se a outra tabela no banco de dados.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Você pode definir alguns argumentos where personalizados (você não pode definir parâmetros nesta instrução where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Nota de segurança** - Você pode ser tentado a fazer algo como `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. POR FAVOR, NÃO FAÇA ISSO!!! Isso é suscetível ao que é conhecido como ataques de injeção SQL. Existem muitos artigos online, por favor, pesquise "ataques de injeção SQL php" e você encontrará muitos artigos sobre esse assunto. A maneira correta de lidar com isso com esta biblioteca é em vez do método `where()`, você faria algo mais como `$user->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Agrupe seus resultados por uma condição específica.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Classifique a consulta retornada de uma determinada maneira.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limite a quantidade de registros retornados. Se um segundo inteiro for dado, será um deslocamento, limite assim como no SQL.

```php
$user->orderBy('name DESC')->limit(0, 10)->findAll();
```

### Condições WHERE
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Onde `field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Onde `field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

Onde `field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Onde `field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Onde `field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Onde `field < $value`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Onde `field >= $value`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Onde `field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Onde `field LIKE $value` ou `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Onde `field IN($value)` ou `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Onde `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

### Relacionamentos
Você pode configurar vários tipos de relacionamentos usando esta biblioteca. Você pode definir relacionamentos um->muitos e um->um entre tabelas. Isso requer uma configuração extra na classe antecipadamente.

Configurar o array `$relations` não é difícil, mas adivinhar a sintaxe correta pode ser confuso.

```php
protected array $relations = [
	// você pode nomear a chave como quiser. O nome do ActiveRecord é provavelmente bom. Ex: usuário, contato, cliente
	'qualquer_active_record' => [
		// obrigatório
		self::HAS_ONE, // este é o tipo de relacionamento

		// obrigatório
		'Alguma_Classe', // esta é a classe ActiveRecord "outro" a que isso fará referência

		// obrigatório
		'chave_local', // esta é a chave local que faz referência à junção.
		// apenas para sua informação, isso também se junta apenas à chave primária do modelo "outro"

		// opcional
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // métodos personalizados que você deseja executar. [] se você não quiser nenhum.

		// opcional
		'nome_referência_traseira' // isso é se você quiser referenciar esse relacionamento de volta a si mesmo Ex: $user->contact->user;
	];
]
```

```php
class User extends ActiveRecord{
	protected array $relations = [
		'contacts' => [ self::HAS_MANY, Contact::class, 'user_id' ],
		'contact' => [ self::HAS_ONE, Contact::class, 'user_id' ],
	];

	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'users');
	}
}

class Contact extends ActiveRecord{
	protected array $relations = [
		'user' => [ self::BELONGS_TO, User::class, 'user_id' ],
		'user_with_backref' => [ self::BELONGS_TO, User::class, 'user_id', [], 'contact' ],
	];
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'contacts');
	}
}
```

Agora que as referências estão configuradas, você pode usá-las muito facilmente!

```php
$user = new User($pdo_conexão);

// encontrar o usuário mais recente.
$user->notNull('id')->orderBy('id desc')->find();

// obter contatos usando relação:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// ou podemos ir no sentido oposto.
$contact = new Contact();

// encontre um contato
$contact->find();

// obter usuário usando relação:
echo $contact->user->name; // este é o nome do usuário
```

Muito legal, não é?

### Definindo Dados Personalizados
Às vezes, você pode precisar anexar algo único ao seu ActiveRecord, como um cálculo personalizado que pode ser mais fácil de apenas anexar ao objeto que seria então passado para um modelo por exemplo.

#### `setCustomData(string $field, mixed $value)`
Você anexa os dados personalizados com o método `setCustomData()`.
```php
$user->setCustomData('contagem_visualizações_página', $contagem_visualizações_página);
```

E então você simplesmente referencia como uma propriedade de objeto normal.

```php
echo $user->contagem_visualizações_página;
```

### Eventos

Uma outra característica super interessante desta biblioteca é sobre eventos. Os eventos são acionados em determinados momentos com base em certos métodos que você chama. Eles são muito, muito úteis para configurar dados para você automaticamente.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Isso é realmente útil se você precisar definir uma conexão padrão ou algo assim.

```php
// index.php ou bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // não esqueça a referência &
		// você poderia fazer isso para definir automaticamente a conexão
		$config['connection'] = Flight::db();
		// ou isso
		$self->transformAndPersistConnection(Flight::db());
		
		// Você também pode definir o nome da tabela dessa forma.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Isso é provavelmente útil apenas se você precisar de uma manipulação de consulta toda vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'users');
	}

	protected function beforeFind(self $self) {
		// sempre execute id >= 0 se for do seu agrado
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Este é provavelmente mais útil se você sempre precisar executar alguma lógica toda vez que este registro for buscado. Precisa descriptografar algo? Precisa executar uma consulta de contagem personalizada toda vez (não é performático, mas tudo bem)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'users');
	}

	protected function afterFind(self $self) {
		// descriptografando algo
		$self->segredo = suaFunçãoDeDescriptografia($self->segredo, $uma_chave);

		// talvez armazenar algo personalizado como uma consulta???
		$self->setCustomData('contagem_visualizações', $self->select('COUNT(*) count')->from('visualizações_usuário')->eq('id_usuário', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Isso é provavelmente útil apenas se você precisar de uma manipulação de consulta toda vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'users');
	}

	protected function beforeFindAll(self $self) {
		// sempre execute id >= 0 se for do seu agrado
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Semelhante ao `afterFind()` mas você pode fazer para todos os registros!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'users');
	}

	protected```md
# FlightPHP Active Record 

Un enregistrement actif est la mise en correspondance d'une entité de base de données à un objet PHP. En termes simples, si vous avez une table d'utilisateurs dans votre base de données, vous pouvez "traduire" une ligne dans cette table en une classe `User` et un objet `$user` dans votre code source. Voir [exemple de base](#ex-basic).

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
 * Une classe Active Record est généralement singulière
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
		// vous pouvez le configurer de cette façon
		parent::__construct($database_connection, 'users');
		// ou de cette façon
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Maintenant regardez la magie opérer!

```php
// pour SQLite
$database_connection = new PDO('sqlite:test.db'); // ceci est juste un exemple, vous utiliseriez probablement une vraie connexion de base de données

// pour MySQL
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'nom_utilisateur', 'mot_de_passe');

// ou MySQLi
$database_connection = new mysqli('localhost', 'nom_utilisateur', 'mot_de_passe', 'test_db');
// ou MySQLi avec une création non basée sur un objet
$database_connection = mysqli_connect('localhost', 'nom_utilisateur', 'mot_de_passe', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('un mot de passe sympa');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('un autre mot de passe sympa !!!');
$user->insert();
// on ne peut pas utiliser $user->save() ici sinon il pensera que c'est une mise à jour!

echo $user->id; // 2
```

Et c'était aussi simple d'ajouter un nouvel utilisateur ! Maintenant qu'il y a une ligne d'utilisateur dans la base de données, comment la récupérer ?

```php
$user->find(1); // trouve l'id = 1 dans la base de données et le retourne.
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

Voyez comme c'est amusant ? Installons-le et commençons !

## Installation

Installez simplement avec Composer

```php
composer require flightphp/active-record 
```

## Utilisation

Cela peut être utilisé comme une bibliothèque autonome ou avec le cadre Flight PHP. Entièrement à vous.

### Autonome
Assurez-vous simplement de passer une connexion PDO au constructeur.

```php
$pdo_connection = new PDO('sqlite:test.db'); // ceci est juste un exemple, vous utiliseriez probablement une vraie connexion de base de données

$User = new User($pdo_connection);
```

### Cadre Flight PHP
Si vous utilisez le cadre Flight PHP, vous pouvez enregistrer la classe ActiveRecord en tant que service (mais honnêtement, vous n'êtes pas obligé).

```php
Flight::register('utilisateur', 'Utilisateur', [ $pdo_connection ]);

// alors vous pouvez l'utiliser de cette façon dans un contrôleur, une fonction, etc.

Flight::utilisateur()->find(1);
```

## Référence de l'API
### Fonctions CRUD

#### `find($id = null) : boolean|ActiveRecord`

Trouve un enregistrement et l'assigne à l'objet actuel. Si vous passez un `$id` de quelque sorte, il effectuera une recherche sur la clé primaire avec cette valeur. S'il n'y a rien de passé, il trouvera simplement le premier enregistrement dans la table.

En outre, vous pouvez lui passer d'autres méthodes d'aide pour interroger votre table.

```php
// trouver un enregistrement avec quelques conditions au préalable
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

#### `delete(): boolean`

Supprime l'enregistrement actuel de la base de données.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Les données "dirty" se réfèrent aux données qui ont été modifiées dans un enregistrement.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// rien n'est "dirty" à ce stade.

$user->email = 'test@example.com'; // maintenant l'e-mail est considéré "dirty" car il a été modifié.
$user->update();
// maintenant il n'y a plus de données "dirty" car elles ont été mises à jour et persistées dans la base de données

$user->password = password_hash()'newpassword'); // maintenant c'est "dirty"
$user->dirty(); // ne rien passer nettoiera toutes les entrées "dirty".
$user->update(); // rien ne sera mis à jour car rien n'a été capturé comme "dirty".

$user->dirty([ 'name' => 'quelque chose', 'password' => password_hash('un mot de passe différent') ]);
$user->update(); // à la fois le nom et le mot de passe sont mis à jour.
```

### Méthodes de Requête SQL
#### `select(string $field1 [, string $field2 ... ])`

Vous pouvez sélectionner uniquement quelques-unes des colonnes dans une table si vous le souhaitez (c'est plus performant sur des tables vraiment larges avec beaucoup de colonnes)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Vous pouvez techniquement choisir une autre table !
```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Vous pouvez même joindre à une autre table dans la base de données.
```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Vous pouvez définir des arguments WHERE personnalisés (vous ne pouvez pas définir de paramètres dans cette instruction WHERE)
```php
$user->where('id=1 AND name="démonstration"')->find();
```

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Regroupe vos résultats par une condition spécifique.
```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Trie la requête retournée d'une certaine manière.
```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limite la quantité de résultats retournée. Si un deuxième entier est donné, il sera un décalage, la limite comme en SQL.
```php
$user->orderBy('name DESC')->limit(0, 10)->findAll();
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