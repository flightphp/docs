# Flight Aktīvais Ieraksts

Aktīvais ieraksts ir datubāzes entitātes kartēšana uz PHP objektu. Sakot vienkārši, ja jums ir lietotāju tabula jūsu datubāzē, jūs varat "tulkot" rindu šajā tabulā uz `User` klasi un `$user` objektu jūsu kodā. Skatiet [pamatu piemēru](#pamatu-piemērs).

Noklikšķiniet [šeit](https://github.com/flightphp/active-record) uz noliktavas GitHub.

## Pamatu Piemērs

Pieņemam, ka jums ir šāda tabula:

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
 * Aktīvā ieraksta klase parasti ir vienskaitļa formā
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
		// jūs to varat iestatīt šādā veidā
		parent::__construct($database_connection, 'users');
		// vai šādā veidā
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Tagad skatiet, kā notiek burvība!

```php
// sqlite gadījumā
$database_connection = new PDO('sqlite:test.db'); // tas ir tikai piemēram, jūs, iespējams, izmantotu reālu datubāzes savienojumu

// mysql gadījumā
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// vai mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// vai mysqli bez objektu balstīta izveidošana
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('kāds foršs parole');
$user->insert();
// vai $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('kāds foršs parole atkal!!!');
$user->insert();
// šeit nevar izmantot $user->save(), jo tas domās, ka tas ir atjauninājums!

echo $user->id; // 2
```

Un bija tik viegli pievienot jaunu lietotāju! Tagad, kad datubāzē ir lietotāja rinda, kā to izvilkt?

```php
$user->find(1); // atrast id = 1 datubāzē un atgriezt to.
echo $user->name; // 'Bobby Tables'
```

Un kas notiks, ja vēlaties atrast visus lietotājus?

```php
$users = $user->findAll();
```

Ko teikt par noteiktu nosacījumu?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Redzat, cik tas ir aizraujoši? Instalēsim to un sāksim!

## Instalācija

Vienkārši instalējiet ar Composer

```php
composer require flightphp/active-record 
```

## Izmantošana

To var izmantot kā patstāvīgu bibliotēku vai kopā ar Flight PHP rāmci. Pilnīgi atkarīgs no jums.

### Patstāvīgi
Vienkārši pārliecinieties, ka nododat PDO savienojumu konstruktoram.

```php
$pdo_connection = new PDO('sqlite:test.db'); // tas ir tikai piemēram, jūs, iespējams, izmantotu reālu datubāzes savienojumu

$User = new User($pdo_connection);
```

> Negribiet katru reizi iestatīt savu datubāzes savienojumu konstruktorā? Skatiet [Datubāzes Savienojuma Pārvaldība](#datubāzes-savienojuma-pārvaldība) citām idejām!

### Reģistrēt kā metodi Flight
Ja jūs izmantojat Flight PHP rāmi, varat reģistrēt aktīvā ieraksta klasi kā pakalpojumu, bet jums to patiesībā nav jāizdara.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// tad jūs varat to izmantot šādi kontrolierī, funkcijā utt.

Flight::user()->find(1);
```

## `runway` Metodes

[runway](/awesome-plugins/runway) ir CLI rīks Flight, kas ir izstrādāts ar īpašu komandu šai bibliotēkai.

```bash
# Izmantošana
php runway make:record database_table_name [class_name]

# Piemērs
php runway make:record users
```

Tas izveidos jaunu klasi mapē `app/records/` kā `UserRecord.php` ar sekojošo saturu:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Aktīvā ieraksta klase lietotāju tabulai.
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
     * @var array $relations Iestatīt attiecības modelim
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [
		// 'relation_name' => [ self::HAS_MANY, 'RelatedClass', 'foreign_key' ],
	];

    /**
     * Konstruktor
     * @param mixed $databaseConnection Savienojums ar datubāzi
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

## CRUD funkcijas

#### `find($id = null) : boolean|ActiveRecord`

Atrast vienu ierakstu un piešķirt to pašreizējam objektam. Ja jūs nododat `$id` kādu vērtību, tas veiks meklēšanu pēc primārā atslēga ar šo vērtību. Ja nekas netiek nodots, tas vienkārši atradīs pirmo ierakstu tabulā.

Turklāt jūs varat nodot tam citas palīgmetodes, lai vaicātu jūsu tabulu.

```php
// atrast ierakstu ar dažiem nosacījumiem iepriekš
$user->notNull('password')->orderBy('id DESC')->find();

// atrast ierakstu pēc noteikta id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Atrast visus ierakstus tabulā, kuru jūs norādāt.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Atgriež `true`, ja pašreizējais ieraksts ir bijis hidrogenizēts (iegūts no datubāzes).

```php
$user->find(1);
// ja ir atrasts ieraksts ar datiem...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

Ievieto pašreizējo ierakstu datubāzē.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### Teksta balstītas primārās atslēgas

Ja jums ir teksta balstīta primārā atslēga (piemēram, UUID), jūs varat iestatīt primārās atslēgas vērtību pirms ievietošanas vienā no diviem veidiem.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // vai $user->save();
```

vai jūs varat ļaut primārajai atslēgai automātiski tikt ģenerētai jūsu vietā.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// jūs varat arī iestatīt primāro atslēgu šādā veidā, nevis augstāk norādītajā masīvā.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // vai kā citādi nepieciešams ģenerēt jūsu unikālos id
	}
}
```

Ja jūs neiestatāt primāro atslēgu pirms ievietošanas, tā tiks iestatīta uz `rowid` un datubāze to ģenerēs jums, taču tā neuzturēsies, jo šis lauks var nepastāvēt jūsu tabulā. Tāpēc ieteicams izmantot notikumu, lai automātiski to apstrādātu.

#### `update(): boolean|ActiveRecord`

Atjaunina pašreizējo ierakstu datubāzē.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Ievieto vai atjaunina pašreizējo ierakstu datubāzē. Ja ierakstam ir id, tas atjauninās, citādi tas ievietos.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Piezīme:** Ja jums ir attiecības, kas definētas klasē, tās tiek rekurzīvi saglabātas, ja tās ir bijušas definētas, instancētas un ir netīri dati, ko atjaunināt. (v0.4.0 un augstāks)

#### `delete(): boolean`

Dzēš pašreizējo ierakstu no datubāzes.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Jūs arī varat dzēst vairākus ierakstus, veicot meklēšanu iepriekš.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Netīri dati attiecas uz datiem, kas ir mainīti ierakstā.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// šajā brīdī nav "netīru" datu.

$user->email = 'test@example.com'; // tagad e-pasts tiek uzskatīts par "netīru", jo tas ir mainījies.
$user->update();
// tagad nav nevienu datu, kas ir netīri, jo tie ir atjaunināti un uzglabāti datubāzē

$user->password = password_hash('jaunā parole'); // tagad šis ir netīrs
$user->dirty(); // neko nenododot, tiks notīrīti visi netīrie ieraksti.
$user->update(); // nekas netiks atjaunināts, jo nekas netika sagūstīts kā netīrs.

$user->dirty([ 'name' => 'kaut kas', 'password' => password_hash('cita parole') ]);
$user->update(); // gan vārds, gan parole tiek atjaunināti.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Tas ir alias `dirty()` metodei. Tas ir nedaudz vairāk skaidrāks par to, ko jūs darāt.

```php
$user->copyFrom([ 'name' => 'kaut kas', 'password' => password_hash('cita parole') ]);
$user->update(); // abi vārds un parole tiek atjaunināti.
```

#### `isDirty(): boolean` (v0.4.0)

Atgriež `true`, ja pašreizējais ieraksts ir mainīts.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Atjauno pašreizējo ierakstu tā sākotnējā stāvoklī. Tas ir ļoti labi izmantot ciklu tipa uzvedībā.
Ja nododat `true`, tas arī atiestatīs vaicājuma datus, kas tika izmantoti, lai atrastu pašreizējo objektu (pamatuzvedība).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // sāk ar tīru lapu
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Pēc tam, kad esat izpildījis `find()`, `findAll()`, `insert()`, `update()`, vai `save()` metodi, jūs varat iegūt SQL, kas tika izveidots un izmantot to problēmu risināšanai.

## SQL Vaicājumu Metodes
#### `select(string $field1 [, string $field2 ... ])`

Jūs varat atlasīt tikai dažus no kolonnām tabulā, ja vēlaties (tas ir efektīvāk tiešām plašām tabulām ar daudzām kolonnām)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Tehniski jūs varat izvēlēties arī citu tabulu! Kāpēc gan ne?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Jūs pat varat pievienot citu tabulu datubāzē.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Jūs varat iestatīt dažus pielāgotus where argumentus (jūs nevarat iestatīt parametrus šajā where paziņojumā)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Drošības Piezīme** - Iespējams, ka jūs sadarbojas ar kaut ko līdzīgu `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Lūdzu, NEDARĪT TO!!! Tas var būt pakļauts tam, ko sauc par SQL injekcijas uzbrukumiem. Ir daudz rakstu tiešsaistē, lūdzu, Google "sql injekcijas uzbrukumi php" un jūs atradīsiet daudz rakstu par šo tēmu. Pareizā metode, kā to apstrādāt ar šo bibliotēku, ir tā, ka, nevis šo `where()` metodi, jūs to darītu vairāk līdzīgi `$user->eq('id', $id)->eq('name', $name)->find();`. Ja jums ir absolūti jādara tā, `PDO` bibliotēkai ir `$pdo->quote($var)`, lai to izdzēstu priekš jums. Tikai pēc tam, kad esat izmantojuši `quote()`, varat to izmantot `where()` paziņojumā.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Grupējiet savus rezultātus pēc noteikta nosacījuma.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Kārtot atgriezto vaicājumu noteiktā veidā.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Ierobežojiet atgriezto ierakstu skaitu. Ja tiek norādīta otrā int, tā būs offset, limits tieši tāpat kā SQL.

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

Ir iespējams savus nosacījumus ietīt OR paziņojumā. To var izdarīt, izmantojot `startWrap()` un `endWrap()` metodi vai norādīt 3. parametru nosacījumam pēc lauka un vērtības.

```php
// Metode 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Tas novērtēs `id = 1 AND (name = 'demo' OR name = 'test')`

// Metode 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Tas novērtēs `id = 1 OR name = 'demo'`
```

## Attiecības
Jūs varat iestatīt vairākus veidus attiecības, izmantojot šo bibliotēku. Jūs varat iestatīt vienu->daudz un vienu->vienu attiecības starp tabulām. Tas prasa mazliet papildus sagatavošanu klasē iepriekš.

Iestatīt `$relations` masīvu nav grūti, bet pareizā sintakse var būt mulsinoša.

```php
protected array $relations = [
	// jūs varat nosaukt atslēgu kā vēlaties. Aktīvā ieraksta nosaukums, iespējams, ir labs. Piemērs: lietotājs, kontakts, klients
	'user' => [
		// obligāti
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // šis ir attiecību veids

		// obligāti
		'Some_Class', // šī ir 'otra' Aktīvā ieraksta klase, uz kuru atsaucas

		// obligāti
		// atkarībā no attiecību veida
		// self::HAS_ONE = ārējā atslēga, kas atsaucas uz pievienošanu
		// self::HAS_MANY = ārējā atslēga, kas atsaucas uz pievienošanu
		// self::BELONGS_TO = lokālā atslēga, kas atsaucas uz pievienošanu
		'local_or_foreign_key',
		// tikai FYI, tas arī pievienojas tikai uz "otras" modeļa primāro atslēgu

		// izvēles
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // papildu nosacījumi, kurus vēlaties, pievienojot attiecību
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// izvēles
		'back_reference_name' // ja vēlaties atsaukt šo attiecību atpakaļ uz sevi, piemēram, `$user->contact->user`;
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

Tagad mums ir atsauces iestatītas, lai mēs varētu tās izmantot ļoti viegli!

```php
$user = new User($pdo_connection);

// atrast jaunāko lietotāju.
$user->notNull('id')->orderBy('id desc')->find();

// iegūt kontaktus, izmantojot attiecību:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// vai mēs varam doties otrā virzienā.
$contact = new Contact();

// atrast vienu kontaktu
$contact->find();

// iegūt lietotāju, izmantojot attiecību:
echo $contact->user->name; // tas ir lietotāja vārds
```

Ļoti jauki, vai ne?

## Iestatīt Pielāgotus Datus
Reizēm jums var būt nepieciešams pievienot kaut ko unikālu savam Aktīvam Ierakstam, piemēram, pielāgotu aprēķinu, kas varētu būt vieglāk pievienot objektam, kas pēc tam būtu nodots, piemēram, šablonam.

#### `setCustomData(string $field, mixed $value)`
Jūs pievienojat pielāgoto datu ar `setCustomData()` metodi.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

Un tad jūs vienkārši atsaucaties uz to kā uz normālu objekta īpašību.

```php
echo $user->page_view_count;
```

## Notikumi

Vēl viena super jauka funkcija šajā bibliotēkā ir notikumi. Notikumi tiek aktivizēti noteiktos brīžos, pamatojoties uz noteiktām metodēm, ko jūs izsaucat. Tie ir ļoti noderīgi, lai automātiski izveidotu datus jums.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Tas ir ļoti noderīgi, ja nepieciešams iestatīt noklusējuma savienojumu vai kaut ko tamlīdzīgu.

```php
// index.php vai bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // neaizmirstiet par & atsauci
		// jūs varētu to izdarīt, lai automātiski iestatītu savienojumu
		$config['connection'] = Flight::db();
		// vai arī šo
		$self->transformAndPersistConnection(Flight::db());
		
		// jūs varat arī šādi iestatīt tabulas nosaukumu.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Šis visticamāk ir noderīgs, ja jums nepieciešams vaicājuma manipulācija katru reizi.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// vienmēr palaist id >= 0, ja tā jums patīk
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Šis visticamāk ir noderīgāks, ja jums katru reizi ir jāpalaista kāda loģika, kad šis ieraksts tiek iegūts. Vai jums jādešifrē kaut kas? Vai jums katru reizi jāveic pielāgots skaitīšanas vaicājums (neefektīvs, bet, nu...).

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// kaut ko dešifrējot
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// varbūt glabājot kaut ko pielāgotu, piemēram, vaicājumu???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Šis varētu būt tikai noderīgs, ja jums nepieciešama vaicājuma manipulācija katru reizi.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// vienmēr palaist id >= 0, ja tā jums patīk
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Līdzīgi `afterFind()`, bet jūs varat to izdarīt visiem ierakstiem!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// darīt kaut ko foršu līdzīgi kā afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Ļoti noderīgi, ja nepieciešams iestatīt noklusējuma vērtības katru reizi.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// iestatīt dažus labus noklusējumus
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

Varbūt jums ir lietotāja scenārijs, lai mainītu datus pēc ievietošanas?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// jūs darāt sevi
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// vai jebkas cits...
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Ļoti noderīgi, ja jums vajag dažas noklusējuma vērtības katru reizi atjaunināšanai.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// iestatīt dažus labus noklusējumus
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Varbūt jums ir lietotāja gadījums, lai mainītu datus pēc atjaunināšanas?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// jūs darāt sevi
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// vai jebkas cits....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Tas ir noderīgi, ja vēlaties, lai notikumi notiktu gan ievietošanas, gan atjaunināšanas laikā. Es ietaupīšu jums garu skaidrojumu, bet, esmu pārliecināts, ka jūs varat uzminēt, kas tas ir.

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

Nezinu, ko jūs šeit vēlētos darīt, bet šeit nav spriedumu! Iet uz priekšu!

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

## Datubāzes Savienojuma Pārvaldība

Kad jūs izmantojat šo bibliotēku, jūs varat iestatīt datubāzes savienojumu dažādos veidos. Jūs varat iestatīt savienojumu konstruktorā, jūs varat iestatīt to caur konfigurācijas mainīgo `$config['connection']` vai jūs varat iestatīt to caur `setDatabaseConnection()` (v0.4.1). 

```php
$pdo_connection = new PDO('sqlite:test.db'); // piemēram
$user = new User($pdo_connection);
// vai
$user = new User(null, [ 'connection' => $pdo_connection ]);
// vai
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Ja vēlaties izvairīties no katra reizes `$database_connection` iestatīšanas, ir veidi, kā to izdarīt!

```php
// index.php vai bootstrap.php
// Iestatiet šo kā reģistrētu klasi Flight
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

> **Piezīme:** Ja plānojat veikt vienību testēšanu, to izdarot, var rasties grūtības, bet kopumā, jo jūs varat injicēt savu 
savienojumu ar `setDatabaseConnection()` vai `$config['connection']`, tas nav pārāk slikti.

Ja jums nepieciešams atsvaidzināt datubāzes savienojumu, piemēram, ja jūs veicat garu CLI skriptu un periodiski nepieciešams atsvaidzināt savienojumu, jūs varat atkārtoti iestatīt savienojumu ar `$your_record->setDatabaseConnection($pdo_connection)`.

## Ieteikums

Lūdzu, dariet to. :D

### Iestatīšana

Kad jūs piedalāties, pārliecinieties, ka izpildāt `composer test-coverage`, lai uzturētu 100% testēšanas pārklājumu (šis nav patiesais vienību testēšanas pārklājums, drīzāk integrācijas testēšana).

Tāpat pārliecinieties, ka izpildāt `composer beautify` un `composer phpcs`, lai novērstu jebkādas linting kļūdas.

## Licence

MIT