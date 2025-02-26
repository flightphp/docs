# Flight Aktīvā Rekords

Aktīvā rekorda būtība ir datu bāzes vienības sasaistīšana ar PHP objektu. Vienkārši sakot, ja jūsu datu bāzē ir lietotāju tabula, jūs varat "tulkot" rindu šajā tabulā uz `User` klasi un `$user` objektu jūsu kodā. Skatiet [pamatu piemēru](#pamatu-piemērs).

Noklikšķiniet [šeit](https://github.com/flightphp/active-record) uz repozitorija GitHub.

## Pamatu Piemērs

Pieņemsim, ka jums ir šāda tabula:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Tagad jūs varat izveidot jaunu klasi, lai attēlotu šo tabulu:

```php
/**
 * Aktīvā Rekorda klase parasti ir vienskaitlī
 * 
 * Ieteicams pievienot tabulas īpašības kā komentārus šeit
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// jūs varat to iestatīt šādā veidā
		parent::__construct($database_connection, 'users');
		// vai šādā veidā
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Tagad skatieties, kā notiek burvība!

```php
// sqlite gadījumā
$database_connection = new PDO('sqlite:test.db'); // šis ir tikai piemēram, iespējams, jūs izmantosiet īstu datu bāzes savienojumu

// mysql gadījumā
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// vai mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// vai mysqli ar neobjektu balstītu izveidi
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('some cool password');
$user->insert();
// vai $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('some cool password again!!!');
$user->insert();
// šeit nevar izmantot $user->save(), jo tas domās, ka tas ir atjauninājums!

echo $user->id; // 2
```

Un tik vienkārši ir pievienot jaunu lietotāju! Tagad, kad datu bāzē ir lietotāja rinda, kā to izsaukt?

```php
$user->find(1); // atrod id = 1 datu bāzē un atgriež to.
echo $user->name; // 'Bobby Tables'
```

Un ko darīt, ja vēlaties atrast visus lietotājus?

```php
$users = $user->findAll();
```

Ko darīt ar konkrētu nosacījumu?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Redzat, cik jautri tas ir? Instalēsim to un uzsāksim!

## Instalācija

Vienkārši instalējiet ar Composer

```php
composer require flightphp/active-record 
```

## Izmantošana

To var izmantot kā patstāvīgu bibliotēku vai kopā ar Flight PHP Framework. Pilnībā atkarīgs no jums.

### Patstāvīgi
Vienkārši pārliecinieties, ka konstruktorā nododat PDO savienojumu.

```php
$pdo_connection = new PDO('sqlite:test.db'); // šis ir tikai piemēram, iespējams, jūs izmantosiet īstu datu bāzes savienojumu

$User = new User($pdo_connection);
```

> Nevēlaties vienmēr iestatīt savu datu bāzes savienojumu konstruktorā? Skatiet [Datu Bāzes Savienojuma Pārvaldība](#datu-bāzes-savienojuma-pārvaldība) citiem ieteikumiem!

### Reģistrējiet to kā metodi Flight
Ja jūs izmantojat Flight PHP Framework, varat reģistrēt Aktīvā Rekorda klasi kā pakalpojumu, bet patiesībā jums tas nav jāveic.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// tad jūs to varat izmantot tāpat kā kontrolierī, funkcijā utt.

Flight::user()->find(1);
```

## `runway` Metodes

[runway](/awesome-plugins/runway) ir CLI rīks Flight, kas satur pielāgotu komandu šai bibliotēkai. 

```bash
# Izmantošana
php runway make:record database_table_name [class_name]

# Piemērs
php runway make:record users
```

Tas izveidos jaunu klasi `app/records/` direktorijā kā `UserRecord.php` ar sekojošo saturu:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Aktīvā Rekorda klase lietotāju tabulai.
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
     * @param mixed $databaseConnection Savienojums ar datu bāzi
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

## CRUD funkcijas

#### `find($id = null) : boolean|ActiveRecord`

Atrod vienu ierakstu un piešķir to pašreizējam objektam. Ja jūs pārsūtāt `$id` kādu, tas veiks meklēšanu pēc primārā atslēgas ar šo vērtību. Ja nekas nav nodots, tas vienkārši atradīs pirmo ierakstu tabulā.

Papildus tam varat nodot citus palīgrīkus, lai vaicātu jūsu tabulu.

```php
// atrod ierakstu ar dažiem nosacījumiem iepriekš
$user->notNull('password')->orderBy('id DESC')->find();

// atrod ierakstu ar konkrētu id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Atrod visus ierakstus tabulā, kuru jūs norādāt.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Atgriež `true`, ja pašreizējais ieraksts ir hidratēts (iegūts no datu bāzes).

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

##### Teksta balstītas primārās atslēgas

Ja jums ir teksts balstīta primārā atslēga (piemēram, UUID), jūs varat iestatīt primārās atslēgas vērtību pirms ievietošanas vienā no divām veidām.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // vai $user->save();
```

vai jūs varat ļaut primārajai atslēgai automātiski ģenerēties jūsu notikumos.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// jūs varat arī iestatīt primāro atslēgu šādā veidā, nevis augstākajā masīvā.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // vai kā citādi jums ir nepieciešams ģenerēt jūsu unikīdus id
	}
}
```

Ja jūs nenosakāt primāro atslēgu pirms ieviešanas, tā tiks iestatīta uz `rowid` un datu bāze to ģenerēs jums, bet tā nepastāvēs, jo šis lauks var nebūt jūsu tabulā. Tāpēc ieteicams izmantot notikumu, lai automātiski to apstrādātu.

#### `update(): boolean|ActiveRecord`

Atjaunina pašreizējo ierakstu datu bāzē.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Ievieto vai atjaunina pašreizējo ierakstu datu bāzē. Ja ierakstam ir id, tas atjauninās, citādi tas ievietos.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Piezīme:** Ja jums ir attiecības definētas klasē, tās arī rekurzīvi saglabās šīs attiecības, ja tās ir definētas, inicializētas un ir mainīgie dati, kas jāatjaunina. (v0.4.0 un jaunākas versijas)

#### `delete(): boolean`

Izdzēš pašreizējo ierakstu no datu bāzes.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Jūs varat arī dzēst vairākus ierakstus, veicot meklēšanu iepriekš.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Mēģinātā dati attiecas uz datiem, kas ir mainīti ierakstā.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// šajā brīdī nekas nav "netīrs".

$user->email = 'test@example.com'; // tagad e-pasts tiek uzskatīts par "netīru", jo tas ir mainīts.
$user->update();
// tagad nav datu, kas ir netīri, jo tie ir atjaunināti un saglabāti datu bāzē

$user->password = password_hash('newpassword'); // tagad tas ir netīrs
$user->dirty(); // bez pārsūtīšanas nekas netiks iztīrīts.
$user->update(); // nekas netiks atjaunināts, jo nekas netika uzskatīts par netīru.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // abi vārdi un parole tiek atjaunināti.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Šī ir aliansa `dirty()` metodei. Tas ir nedaudz skaidrāk, ko jūs darāt.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // abi vārdi un parole tiek atjaunināti.
```

#### `isDirty(): boolean` (v0.4.0)

Atgriež `true`, ja pašreizējais ieraksts ir mainīts.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Atjauno pašreizējo ierakstu uz tā sākotnējo stāvokli. Tas ir ļoti piemērots, lai to izmantotu cikla veida uzvedībā. Ja jūs pārsūtāt `true`, tas arī atjaunos vaicājumu datus, kas tika izmantoti, lai atrastu pašreizējo objektu (iepriekšējā uzvedība).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // sākot ar tīru paleti
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Pēc tam, kad jūs izpildījāt `find()`, `findAll()`, `insert()`, `update()`, vai `save()` metodi, jūs varat iegūt izveidoto SQL un izmantot to atkļūdošanas nolūkiem.

## SQL Vaicājumu Metodes
#### `select(string $field1 [, string $field2 ... ])`

Jūs varat izvēlēties tikai dažas no kolonnām tabulā, ja vēlaties (tas ir efektīvāk tiešām plašām tabulām ar daudzām kolonnām)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Tehniski jūs varat izvēlēties arī citu tabulu! Kāpēc gan ne?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Jūs pat varat pievienot citu tabulu datu bāzē.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Jūs varat iestatīt dažus pielāgotus nosacījumus (jūs nevarat iestatīt parametrus šajā where izteiksmē)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Drošības Piezīme** - jūs varētu vēlēties darīt kaut ko līdzīgu `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Lūdzu, NEDARĪJIET TO!!! Tas ir uzņēmīgs pret to, ko sauc par SQL ievietošanu. Ir daudz rakstu tiešsaistē, lūdzu, meklējiet "sql injection attacks php", un jūs atradīsiet daudz rakstu par šo tēmu. Pareizais veids, kā tikt galā ar šo bibliotēku, ir darīt kaut ko vairāk līdzīgu `$user->eq('id', $id)->eq('name', $name)->find();` Ja jums tas ir absolūti jādara, `PDO` бібліотекаi ir `$pdo->quote($var)` to, lai to jums izsist.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Grupējiet savus rezultātus pēc kāda nosacījuma.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Kārtot atgriezto vaicājumu noteiktā veidā.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

ierobežot atgriezto ierakstu skaitu. Ja tiek norādīts otrais int, tas tiks novietots, ierobežojums tieši kā SQL.

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

Ir iespējams ietīt savus nosacījumus OR izteiksmē. To var izdarīt, izmantojot vai nu `startWrap()` un `endWrap()` metodi, vai aizpildot 3. parametru nosacījumam pēc lauka un vērtības.

```php
// Metode 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Tas novērtēs līdz `id = 1 AND (name = 'demo' OR name = 'test')`

// Metode 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Tas novērtēs līdz `id = 1 OR name = 'demo'`
```

## Attiecības
Jūs varat iestatīt vairākus veidus attiecību izmantojot šo bibliotēku. Jūs varat iestatīt vienu->daudz un vienu->vienu attiecības starp tabulām. Tam ir nepieciešama neliela papildu sagatavošana klasē iepriekš.

`$relations` masīva iestatīšana nav grūta, bet pareizā sintakse var būt mulsinoša.

```php
protected array $relations = [
	// jūs varat nosaukt atslēgu pēc jūsu izvēles. Aktīvā Rekorda nosaukums, iespējams, ir labs. Piemērs: lietotājs, kontakts, klients
	'user' => [
		// obligāti
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // šis ir attiecību veids

		// obligāti
		'Some_Class', // šī ir "otra" Aktīvā Rekorda klase, kurai tas norādīs

		// obligāti
		// atkarībā no attiecību veida
		// self::HAS_ONE = ārējā atslēga, kas atsaucas uz savienojumu
		// self::HAS_MANY = ārējā atslēga, kas atsaucas uz savienojumu
		// self::BELONGS_TO = iekšējā atslēga, kas atsaucas uz savienojumu
		'local_or_foreign_key',
		// tikai FYI, šī arī tikai pievieno primāro atslēgu "otrajai" modelei

		// opcional
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // papildu nosacījumi, kurus vēlaties pievienot, kad pievienojat attiecību
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// opcional
		'back_reference_name' // tas, ja vēlaties atsaukt šo attiecību atpakaļ uz sevi Piemērs: $user->contact->user;
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

Tagad mēs esam uzstādījuši atsaucības, tāpēc mēs varam tās izmantot ļoti viegli!

```php
$user = new User($pdo_connection);

// atrod visjaunāko lietotāju.
$user->notNull('id')->orderBy('id desc')->find();

// iegūst kontakta, izmantojot attiecību:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// vai mēs varam iet pa citu ceļu.
$contact = new Contact();

// atrod vienu kontaktu
$contact->find();

// iegūst lietotāju, izmantojot attiecību:
echo $contact->user->name; // tas ir lietotāja vārds
```

Ļoti forši, vai ne?

## Pielāgota Datu Iestatīšana
Dažreiz jums var būt nepieciešams pievienot kaut ko unikālu savam Aktīvā Rekorda objekta, piemēram, pielāgotu aprēķinu, ko varētu būt vieglāk pievienot objektam, ko pēc tam nodos, piemēram, veidne.

#### `setCustomData(string $field, mixed $value)`
Jūs pievienojat pielāgotos datus ar `setCustomData()` metodi.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

Un tad jūs vienkārši atsaucaties uz to kā uz normālu objekta īpašību.

```php
echo $user->page_view_count;
```

## Notikumi

Vēl viena ļoti forša šīs bibliotēkas iezīme ir par notikumiem. Notikumi tiek aktivizēti noteiktos laikos, pamatojoties uz noteiktām metodēm, ko jūs saucat. Tie ir ļoti noderīgi, lai automātiski sagatavotu datus.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Tas ir ļoti noderīgi, ja jums jāiestata noklusējuma savienojums vai kaut kas tamlīdzīgs.

```php
// index.php vai bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // neaizmirstiet & atsauci
		// jūs varētu to darīt, lai automātiski iestatītu savienojumu
		$config['connection'] = Flight::db();
		// vai arī
		$self->transformAndPersistConnection(Flight::db());
		
		// Jūs varat arī iestatīt tabulas nosaukumu šādā veidā.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Šis noteikti ir noderīgs, ja jums ir nepieciešama vaicājuma manipulācija katru reizi.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// vienmēr izpildīt id >= 0, ja tas ir jūsu stils
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Šis, iespējams, būs noderīgāks, ja jums vienmēr ir jāveic kaut kas pēc tam, kad šis ieraksts ir iegūts. Vai jums ir nepieciešams dešifrēt kaut ko? Vai katru reizi ir jāpārskata pielāgots skaitīšanas vaicājums (neefektīvi, bet kas nu tur...)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// dešifrējot kaut ko
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// varbūt uzglabojot kaut ko pielāgotu, piemēram, vaicājumu???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']); 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Šis noteikti būs noderīgs, ja jums ir nepieciešama vaicājuma manipulācija katru reizi.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// vienmēr izpildīt id >= 0, ja tas ir jūsu stils
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Līdzīgi kā `afterFind()`, bet jūs to varat darīt visiem ierakstiem!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// dariet kaut ko foršu, kā `afterFind()`
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Patiesi noderīgs, ja jums ir nepieciešami noklusējuma vērtības katru reizi iestatītas.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// iestatiet dažas labas noklusējuma vērtības
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

Varbūt jums ir lietotāja gadījums, kad jāmaina dati pēc pievienošanas?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// jūs darāt to, ko vēlaties
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// vai kā varbūt....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Ļoti noderīgi, ja jums katru reizi ir nepieciešama noklusējuma vērtību iestatīšana atjaunināšanai.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// iestatiet dažas labas noklusējuma vērtības
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Varbūt jums ir lietotāja gadījums, kad jāmaina dati pēc atjaunināšanas?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// jūs darāt to, ko vēlaties
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// vai kā varbūt....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Šis ir noderīgs, ja vēlaties, lai notikumi notiktu gan pievienojumiem, gan atjauninājumiem. Es jums atstāšu garu skaidrojumu, bet, esmu pārliecināts, ka varat uzminēt, kas tas ir.

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

Nezinu, ko jūs šeit vēlētos darīt, bet nav nekādu spriedumu! Rīkojieties!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Viņš bija drosmīgs kareivis... :cry-face:';
	} 
}
```

## Datu Bāzes Savienojuma Pārvaldība

Izmantojot šo bibliotēku, jūs varat iestatīt datu bāzes savienojumu dažādos veidos. Jūs varat iestatīt savienojumu konstruktorā, varat to iestatīt pa konfigurācijas mainīgo `$config['connection']` vai arī varat to iestatīt, izmantojot `setDatabaseConnection()` (v0.4.1). 

```php
$pdo_connection = new PDO('sqlite:test.db'); // piemēram
$user = new User($pdo_connection);
// vai
$user = new User(null, [ 'connection' => $pdo_connection ]);
// vai
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Ja vēlaties izvairīties no katra reizes, kad zvanāt aktīvā ierakstam, iestatīt `$database_connection`, pastāv risinājumi!

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

// Un tagad iestatījums nav nepieciešams!
$user = new User();
```

> **Piezīme:** Ja plānojat veikt vienības testēšanu, to darot, var radīt grūtības, bet, kopumā, tāpēc, ka varat injicēt savu 
savienojumu ar `setDatabaseConnection()` vai `$config['connection']`, tas nav tik slikti.

Ja jūs nepieciešams atsvaidzināt datu bāzes savienojumu, piemēram, ja jūs veicat ilgu skriptu, un jums jāatsvaidzina savienojums katru laiku, jūs varat atkārtoti iestatīt savienojumu ar `$your_record->setDatabaseConnection($pdo_connection)`.

## Piedalīšanās

Lūdzu, dariet to. :D

### Iestatīšana

Kad jūs piedalāties, pārliecinieties, ka veicat `composer test-coverage`, lai uzturētu 100% testu pārklājumu (tas nav īsts vienības testu pārklājums, bet drīzāk integrācijas testi).

Tāpat pārliecinieties, ka izpildāt `composer beautify` un `composer phpcs`, lai novērstu visus gramatikas kļūdas.

## Licences

MIT