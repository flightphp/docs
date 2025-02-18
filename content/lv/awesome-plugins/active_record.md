# Flight Aktīvo Ierakstu

Aktīvais ieraksts ir datu bāzes entitātes kartēšana uz PHP objektu. Vienkārši sakot, ja tavā datu bāzē ir lietotāju tabula, tu vari "pārvērst" rindu šajā tabulā uz `User` klasi un `$user` objektu savā kodu bāzē. Skatiet [pamatu piemēru](#pamatu-piemērs).

Noklikšķiniet [šeit](https://github.com/flightphp/active-record) uz repozitorija GitHub.

## Pamatu Piemērs

Pieņemam, ka tev ir šāda tabula:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Tagad tu vari izveidot jaunu klasi, lai attēlotu šo tabulu:

```php
/**
 * Aktīvo ierakstu klase parasti ir vienskaitlī
 * 
 * Ir spēcīgi ieteicams pievienot tabulas īpašības kā komentārus šeit
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// tu vari iestatīt to šādā veidā
		parent::__construct($database_connection, 'users');
		// vai šādā veidā
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Tagad skaties, kā notiek burvība!

```php
// sqlite
$database_connection = new PDO('sqlite:test.db'); // tas ir tikai piemēram, tu, iespējams, izmantosi īstu datu bāzes savienojumu

// mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'lietotājvārds', 'parole');

// vai mysqli
$database_connection = new mysqli('localhost', 'lietotājvārds', 'parole', 'test_db');
// vai mysqli ar neobjekta veida izveidi
$database_connection = mysqli_connect('localhost', 'lietotājvārds', 'parole', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('kāda forša parole');
$user->insert();
// vai $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('atkal kāda forša parole!!!');
$user->insert();
// nevar izmantot $user->save() šeit, jo tas domās, ka tas ir atjauninājums!

echo $user->id; // 2
```

Un bija tik viegli pievienot jaunu lietotāju! Tagad, kad datu bāzē ir lietotāja rinda, kā to izvilkt?

```php
$user->find(1); // atrast id = 1 datu bāzē un atgriezt to.
echo $user->name; // 'Bobby Tables'
```

Un kas notiek, ja tu vēlies atrast visus lietotājus?

```php
$users = $user->findAll();
```

Kas par noteiktu nosacījumu?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Skatīt, cik daudz prieka tas ir? Instalēsim to un sāksim!

## Instalācija

Vienkārši instalē ar Composer

```php
composer require flightphp/active-record 
```

## Lietošana

To var izmantot kā patstāvīgu bibliotēku vai kopā ar Flight PHP Framework. Pilnīgi atkarīgs no tevis.

### Patstāvīgi
Vienkārši pārliecinies, ka tu nodod PDO savienojumu konstruktoram.

```php
$pdo_connection = new PDO('sqlite:test.db'); // tas ir tikai piemēram, tu, iespējams, izmantosi īstu datu bāzes savienojumu

$User = new User($pdo_connection);
```

> Negribi vienmēr iestatīt savu datu bāzes savienojumu konstruktorā? Skatiet [Datu bāzes savienojuma pārvaldība](#datu-bāzes-savienojuma-pārvaldība) citiem risinājumiem!

### Reģistrēt kā metodi Flight
Ja tu izmanto Flight PHP Framework, tu vari reģistrēt Aktīvo ierakstu klasi kā pakalpojumu, bet tu, godīgi sakot, to nemaz nedrīksti darīt.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// tad tu vari izmantot to šādā veidā kontrolierī, funkcijā utt.

Flight::user()->find(1);
```

## `runway` Metodes

[runway](https://docs.flightphp.com/awesome-plugins/runway) ir CLI rīks Flight, kas ir pielāgota komanda šai bibliotēkai.

```bash
# Lietošana
php runway make:record database_table_name [class_name]

# Piemērs
php runway make:record users
```

Tas izveidos jaunu klasi `app/records/` direktorijā ar nosaukumu `UserRecord.php` un šādu saturu:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Aktīvo ierakstu klase lietotāju tabulai.
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
     * @var array $relations Iestatiet attiecības modelim
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [
		// 'relation_name' => [ self::HAS_MANY, 'RelatedClass', 'foreign_key' ],
	];

    /**
     * Konstruktors
     * @param mixed $databaseConnection Datu bāzes savienojums
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

## CRUD funkcijas

#### `find($id = null) : boolean|ActiveRecord`

Atrod vienu ierakstu un piešķir to pašreizējam objektam. Ja pasniedz kādu `$id`, tas veiks meklēšanu pa primāro atslēgu ar šo vērtību. Ja nekas netiek nodots, tas vienkārši atradīs pirmo ierakstu tabulā.

Papildus tu vari nodot to kāds citus palīgmetodes, lai vaicātu tavu tabulu.

```php
// atrod ierakstu ar dažiem nosacījumiem iepriekš
$user->notNull('password')->orderBy('id DESC')->find();

// atrod ierakstu pēc konkrēta id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Atrod visus ierakstus norādītajā tabulā.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Atgriež `true`, ja pašreizējais ieraksts ir hidratēts (izsists no datu bāzes).

```php
$user->find(1);
// ja ieraksts ir atrasts ar datiem...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

Ievieto pašreizējo ierakstu datu bāzē.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### Teksta bāzes primārās atslēgas

Ja tev ir tekstu bāzes primārā atslēga (piemēram, UUID), tu vari iestatīt primārās atslēgas vērtību pirms ievietošanas divos veidos.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'dažs-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // vai $user->save();
```

vai vari ļaut primārajai atslēgai automātiski ģenerēties notikumu laikā.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// tu vari arī iestatīt primāro atslēgu šādā veidā, nevis augšminētajā masīvā.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // vai kā jebkādā veidā tu vēlies ģenerēt savus unikālos id
	}
}
```

Ja tu neiestati primāro atslēgu pirms ievietošanas, tā tiks iestatīta uz `rowid`, un datu bāze to ģenerēs tev, bet tā netiks saglabāta, jo šī lauka var nebūt tavā tabulā. Tieši tāpēc ieteicams izmantot notikumu, lai automātiski to pārvaldītu.

#### `update(): boolean|ActiveRecord`

Atjaunina pašreizējo ierakstu datu bāzē.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Ievieto vai atjaunina pašreizējo ierakstu datu bāzē. Ja ierakstam ir id, tas atjauninās, pretējā gadījumā tas ievietos.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Piezīme:** Ja tev ir attiecības, kas definētas klasē, tās rekursīvi saglabās arī šīs attiecības, ja tās ir definētas, instancētas un kādā veidā ir "netīras" datus, ko atjaunināt. (v0.4.0 un augstāk)

#### `delete(): boolean`

Izdzēš pašreizējo ierakstu no datu bāzes.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Tu arī vari izdzēst vairākus ierakstus, izpildot meklēšanu iepriekš.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Netīrie dati attiecas uz datiem, kas mainīti ierakstā.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// šajā brīdī nekas nav "netīrs".

$user->email = 'test@example.com'; // tagad e-pasts tiek uzskatīts par "netīru", jo tas ir mainījies.
$user->update();
// tagad nav neviena "netīra" datu, jo tie ir atjaunināti un saglabāti datu bāzē

$user->password = password_hash('jaunā parole'); // tagad šis ir netīrs
$user->dirty(); // nekas netiks iztīrīts, jo nekas netika uzņemts kā netīrs.

$user->dirty([ 'name' => 'kaut kas', 'password' => password_hash('cita parole') ]);
$user->update(); // gan vārds, gan parole tiek atjaunināti.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Šī ir alternatīva `dirty()` metodei. Tas ir nedaudz skaidrāk, ko tu dari.

```php
$user->copyFrom([ 'name' => 'kaut kas', 'password' => password_hash('cita parole') ]);
$user->update(); // gan vārds, gan parole tiek atjaunināti.
```

#### `isDirty(): boolean` (v0.4.0)

Atgriež `true`, ja pašreizējais ieraksts ir mainīts.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Atjauno pašreizējo ierakstu uz tā sākotnējo stāvokli. Tas ir ļoti labs, lai izmantotu ciklos.
Ja tu nodod `true`, tas arī atjaunos meklēšanas datus, kas tika izmantoti, lai atrastu pašreizējo objektu (noklusējuma uzvedība).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // sāc ar tīru slāni
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Pēc tam, kad tu izpildi `find()`, `findAll()`, `insert()`, `update()`, vai `save()` metodi, tu vari iegūt SQL, kas tika izveidots un izmantot to atkļūdošanas mērķiem.

## SQL Vaicājumu Metodes
#### `select(string $field1 [, string $field2 ... ])`

Tu vari izvēlēties tikai dažas kolonnas tabulā, ja vēlies (tas ir efektīvāk ļoti plašās tabulās ar daudziem laukiem)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Tehniski tu vari izvēlēties citu tabulu! Kāpēc gan ne?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Tu pat vari pievienot citu tabulu datu bāzē.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Tu vari iestatīt dažus pielāgotus where argumentus (tu nevarēsi iestatīt parametrus šajā where apgalvojumā)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Drošības Piezīme** - Tev varētu būt kārdinājums darīt kaut ko tādu kā `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Lūdzu, NEDARI TO!!! Tas ir uzņēmīgs pret to, kas pazīstams kā SQL injekcijas uzbrukumi. Ir daudz rakstu internetā, lūdzu, Google "sql injection attacks php" un tu atradīsi daudz rakstu par šo tēmu. Pareizais veids, kā to apstrādāt ar šo bibliotēku, ir nevis izmantot šo `where()` metodi, bet drīzāk darīt kaut ko līdzīgu `$user->eq('id', $id)->eq('name', $name)->find();` Ja tev ir absolūti jādarbojas šādi, `PDO` bibliotēkai ir `$pdo->quote($var)` lai to izmanto. Tikai pēc tam, kad tu izmanto `quote()`, tu vari to izmantot `where()` apgalvojumā.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Grupējiet savus rezultātus pēc noteikta nosacījuma.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Sakārto atgriezto vaicājumu noteiktā veidā.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Ierobežo atgriezto ierakstu daudzumu. Ja tiek norādīta otra int, tā tiks izmantota kā pāreja, ierobežojums, tieši tāpat kā SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## WHERE nosacījumi
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Kur `field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Kur `field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

Kur `field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Kur `field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Kur `field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Kur `field < $value`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Kur `field >= $value`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Kur `field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Kur `field LIKE $value` vai `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Kur `field IN($value)` vai `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Kur `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

### OR Nosacījumi

Ir iespējams ievietot savus nosacījumus OR apgalvojumā. To var izdarīt ar `startWrap()` un `endWrap()` metodi vai norādot 3. parametru nosacījumam pēc lauka un vērtības.

```php
// Metode 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Tas novērtēs līdz `id = 1 AND (name = 'demo' OR name = 'test')`

// Metode 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Tas novērtēs līdz `id = 1 OR name = 'demo'`
```

## Attiecības
Tu vari iestatīt vairākus veidu attiecības, izmantojot šo bibliotēku. Tu vari iestatīt viena->daudzas un viena->viena attiecības starp tabulām. Tas prasīs nedaudz papildu iestatījumu klasē iepriekš.

Iestatīt `$relations` masīvu nav grūti, taču pareizās sintakses uzminēšana var būt mulsinoša.

```php
protected array $relations = [
	// tu vari nosaukt atslēgu tā, kā vēlies. Aktīvo ierakstu nosaukums, iespējams, ir labs. Piemēram: lietotājs, kontakts, klients
	'user' => [
		// obligāti
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // šī ir attiecību veids

		// obligāti
		'Some_Class', // šī ir "otra" Aktīvo ierakstu klase, uz kuru norādīs

		// obligāti
		// atkarībā no attiecību veida
		// self::HAS_ONE = ārējā atslēga, kas atsaucas uz pievienošanu
		// self::HAS_MANY = ārējā atslēga, kas atsaucas uz pievienošanu
		// self::BELONGS_TO = lokālā atslēga, kas atsaucas uz pievienošanu
		'local_or_foreign_key',
		// tikai informācijai, šis arī tikai pievienojas pie "otra" modeļa primārās atslēgas

		// optional
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // papildu nosacījumi, ko tu vēlies, kad pievieno attiecības
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// optional
		'back_reference_name' // ja tu vēlies atsaukt šo attiecību atpakaļ uz sevi Piem.: $user->contact->user;
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

Tagad mums ir atsauces iestatītas, tāpēc mēs varam tās izmantot ļoti viegli!

```php
$user = new User($pdo_connection);

// atrod visrecentāko lietotāju.
$user->notNull('id')->orderBy('id desc')->find();

// iegūst kontaktus, izmantojot attiecību:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// vai mēs varam doties pretējā virzienā.
$contact = new Contact();

// atrodi vienu kontaktu
$contact->find();

// iegūst lietotāju, izmantojot attiecību:
echo $contact->user->name; // tas ir lietotāja vārds
```

Ļoti forši, vai ne?

## Pielāgotu Datu Iestatīšana
Dažreiz tu vari gribēt pievienot kaut ko unikālu savam Aktīvajam ierakstam, piemēram, pielāgotu aprēķinu, kas varētu būt vieglāk pievienot objektam, kas vēlāk tiks nodots piemēram veidnei.

#### `setCustomData(string $field, mixed $value)`
Tu pievieno pielāgotos datus ar `setCustomData()` metodi.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

Un tad tu vienkārši atsauces to kā normālu objekta īpašību.

```php
echo $user->page_view_count;
```

## Notikumi

Vēl viena super lieliska iezīme par šo bibliotēku ir notikumi. Notikumi tiek aktivizēti noteiktos laika posmos, balstoties uz noteiktām metodēm, ko tu aicini. Tie ir ļoti noderīgi, lai automātiski izveidotu datus.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Tas ir ļoti noderīgi, ja tev nepieciešams iestatīt noklusēto savienojumu vai ko tamlīdzīgu.

```php
// index.php vai bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // neaizmirsti korespondenci ar &
		// tu vari to izdarīt, lai automātiski iestatītu savienojumu
		$config['connection'] = Flight::db();
		// vai šādi
		$self->transformAndPersistConnection(Flight::db());
		
		// Tu vari arī iestatīt tabulas nosaukumu šādā veidā.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Šis var noderēt, ja tev nepieciešama vaicājuma manipulācija katru reizi.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// vienmēr izpildi id >= 0, ja tas ir tas, ko tu gribi
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Šis, visticamāk, būs noderīgs, ja tev vienmēr nepieciešams veikt kādu loģiku katru reizi, kad šis ieraksts tiek paņemts. Vai tev ir nepieciešams dekriptēt kaut ko? Vai tev vajag izpildīt pielāgotu skaitīšanas vaicājumu katru reizi (neefektīvi, bet nu labi)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// dekriptē kaut ko
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// varbūt uzglabojot kaut ko pielāgotu, piemēram, vaicājumu???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Šis var noderēt, ja tev nepieciešama vaicājuma manipulācija katru reizi.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// vienmēr izpildi id >= 0, ja tas ir tas, ko tu gribi
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Līdzīgi kā `afterFind()`, bet tagad tu vari to darīt visiem ierakstiem!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// dari kaut ko foršu kā afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Ļoti noderīgi, ja tev vajag iestatīt kādas noklusētās vērtības katru reizi.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// iestatīt dažas prasīgas noklusētas vērtības
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

Varbūt tev ir gadījums, kad ir jāmaina dati pēc to ievietošanas?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// dari, ko tu gribi
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// vai ko citu....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Ļoti noderīgi, ja tev vajag iestatīt kādas noklusētās vērtības katru reizi atjauninot.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// iestatīt dažas prasīgas noklusētas vērtības
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Varbūt tev ir gadījums, kad ir jāmaina dati pēc to atjaunināšanas?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// dari, ko tu gribi
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// vai ko citu....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Tas ir noderīgi, ja vēlies, lai notikumi notiktu gan ievietošanas, gan atjaunināšanas gadījumos. Es tevi atbrīvošu no garas skaidrošanas, bet esmu drošs, ka tu vari uzminēt, kas tas ir.

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

Nezinu, ko tu šeit vēlies darīt, bet nebūšu spriedējs! Dari to!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Viņš bija drosmīgs karavīrs... :cry-face:';
	} 
}
```

## Datu bāzes savienojuma pārvaldība

Kad tu izmanto šo bibliotēku, tu vari iestatīt datu bāzes savienojumu vairākos veidos. Tu vari iestatīt savienojumu konstruktorā, tu vari iestatīt to caur konfigurācijas mainīgo `$config['connection']` vai arī tu vari iestatīt to ar `setDatabaseConnection()` (v0.4.1). 

```php
$pdo_connection = new PDO('sqlite:test.db'); // piemērā
$user = new User($pdo_connection);
// vai
$user = new User(null, [ 'connection' => $pdo_connection ]);
// vai
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Ja tu vēlies izvairīties no ikreiz, kad jāpievieno `$database_connection`, kad tu sauc aktīvo ierakstu, ir dažādi veidi, kā to izdarīt!

```php
// index.php vai bootstrap.php
// Iestatiet to kā reģistrētu klasi Flight
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// Un tagad, nav nepieciešami argumenti!
$user = new User();
```

> **Piezīme:** Ja plānojat veikt vienību testēšanu, darot to šādā veidā var rasties kādas grūtības, bet kopumā, jo tu vari injicēt savu 
savienojumu ar `setDatabaseConnection()` vai `$config['connection']`, nav tik traki.

Ja tev nepieciešams atjaunot datu bāzes savienojumu, piemēram, ja tu veic ilgu CLI skriptu un periodiski ir jāatjauno savienojums, tu vari atkal iestatīt savienojumu ar `$your_record->setDatabaseConnection($pdo_connection)`.

## Ieguldījumi

Lūdzu, dari to. :D

### Iestatīšana

Kad tu ieguldi, pārliecinies, ka tu izpildi `composer test-coverage`, lai saglabātu 100% pārbaužu segumu (tas nav patiesais vienību testēšanas segums, vairāk integrētā testēšana).

Arī pārliecinies, ka tu izpildi `composer beautify` un `composer phpcs`, lai novērstu jebkādas satura kļūdas.

## Licence

MIT