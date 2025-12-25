# Flight Active Record 

Aktīvs ieraksts ir datubāzes entītijas kartēšana uz PHP objektu. Vienkārši izteikts, ja jums ir lietotāju tabula datubāzē, jūs varat "tulkot" rindas šajā tabulā uz `User` klasi un `$user` objektu jūsu koda bāzē. Skatiet [pamatinstanci](#basic-example).

Noklikšķiniet [šeit](https://github.com/flightphp/active-record), lai iegūtu repozitoriju GitHub.

## Pamatinstance

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
 * ActiveRecord klase parasti ir vienskaitlī
 * 
 * Ļoti ieteicams pievienot tabulas īpašības kā komentārus šeit
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// jūs varat iestatīt to šādi
		parent::__construct($database_connection, 'users');
		// vai šādi
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Tagad vērojiet, kā notiek burvība!

```php
// sqlite gadījumā
$database_connection = new PDO('sqlite:test.db'); // tas ir tikai piemēram, jūs droši vien izmantosiet īstu datubāzes savienojumu

// mysql gadījumā
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// vai mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// vai mysqli ar neobjekta bāzētu izveidi
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
// šeit nevar izmantot $user->save(), pretējā gadījumā tas domās, ka tas ir atjauninājums!

echo $user->id; // 2
```

Un tik viegli bija pievienot jaunu lietotāju! Tagad, kad datubāzē ir lietotāja rinda, kā jūs to izvilksiet?

```php
$user->find(1); // atrast id = 1 datubāzē un atgriezt to.
echo $user->name; // 'Bobby Tables'
```

Un kas, ja jūs vēlaties atrast visus lietotājus?

```php
$users = $user->findAll();
```

Ko darīt ar noteiktu nosacījumu?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Redziet, cik jautri tas ir? Instalēsim to un sāksim!

## Instalēšana

Vienkārši instalējiet ar Composer

```php
composer require flightphp/active-record 
```

## Lietošana

To var izmantot kā neatkarīgu bibliotēku vai ar Flight PHP Framework. Pilnībā atkarīgs no jums.

### Neatkarīgi
Vienkārši pārliecinieties, ka konstruktoram nododat PDO savienojumu.

```php
$pdo_connection = new PDO('sqlite:test.db'); // tas ir tikai piemēram, jūs droši vien izmantosiet īstu datubāzes savienojumu

$User = new User($pdo_connection);
```

> Vai nevēlaties vienmēr iestatīt datubāzes savienojumu konstruktorā? Skatiet [Datubāzes savienojuma pārvaldību](#database-connection-management) citiem variantiem!

### Reģistrēšana kā metode Flight
Ja izmantojat Flight PHP Framework, jūs varat reģistrēt ActiveRecord klasi kā servisu, bet patiesībā nav obligāti.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// tad jūs varat izmantot to šādi kontrolierī, funkcijā utt.

Flight::user()->find(1);
```

## `runway` Metodes

[runway](/awesome-plugins/runway) ir CLI rīks Flight, kas ir ar pielāgotu komandu šai bibliotēkai. 

```bash
# Lietošana
php runway make:record database_table_name [class_name]

# Piemērs
php runway make:record users
```

Tas izveidos jaunu klasi `app/records/` direktorijā kā `UserRecord.php` ar šādu saturu:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * ActiveRecord klase lietotāju tabulai.
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
     * @var array $relations Iestatīt modeļa attiecības
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [
		// 'relation_name' => [ self::HAS_MANY, 'RelatedClass', 'foreign_key' ],
	];

    /**
     * Konstruktors
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

Atrast vienu ierakstu un piešķirt to pašreizējam objektam. Ja nododat kādu `$id`, tas veiks meklēšanu primārajā atslēgā ar šo vērtību. Ja nekas netiek nodots, tas atradīs pirmo ierakstu tabulā.

Turklāt jūs varat nodot citas palīgmēģenes, lai vaicātu tabulu.

```php
// atrast ierakstu ar dažiem nosacījumiem iepriekš
$user->notNull('password')->orderBy('id DESC')->find();

// atrast ierakstu pēc specifiska id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Atrast visus ierakstus tabulā, ko jūs norādāt.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Atgriež `true`, ja pašreizējais ieraksts ir hidratēts (iegūts no datubāzes).

```php
$user->find(1);
// ja ieraksts ir atrasts ar datiem...
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

##### Teksta bāzētas primārās atslēgas

Ja jums ir teksta bāzēta primārā atslēga (piemēram, UUID), jūs varat iestatīt primārās atslēgas vērtību pirms ievietošanas divos veidos.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // vai $user->save();
```

vai jūs varat ļaut primārajai atslēgai automātiski ģenerēties caur notikumiem.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// jūs varat iestatīt primaryKey arī šādi, nevis ar masīvu iepriekš.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // vai kā jūs ģenerējat savas unikālās id
	}
}
```

Ja jūs neiestatāt primāro atslēgu pirms ievietošanas, tā tiks iestatīta uz `rowid` un datubāze to ģenerēs jums, bet tā nepastāvēs, jo tas lauks var nepastāvēt jūsu tabulā. Tāpēc ieteicams izmantot notikumu, lai automātiski apstrādātu to.

#### `update(): boolean|ActiveRecord`

Atjaunina pašreizējo ierakstu datubāzē.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Ievieto vai atjaunina pašreizējo ierakstu datubāzē. Ja ierakstam ir id, tas atjauninās, citādi ievietos.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Piezīme:** Ja jums ir definētas attiecības klasē, tas rekursīvi saglabās tās attiecības, ja tās ir definētas, instance un ir netīri dati atjaunināšanai. (v0.4.0 un augstāk)

#### `delete(): boolean`

Dzēš pašreizējo ierakstu no datubāzes.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Jūs varat arī dzēst vairākus ierakstus, izpildot meklēšanu iepriekš.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Netīri dati attiecas uz datiem, kas ir mainīti ierakstā.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// līdz šim nekas nav "netīrs".

$user->email = 'test@example.com'; // tagad email tiek uzskatīts par "netīru", jo tas ir mainīts.
$user->update();
// tagad nav datu, kas ir netīri, jo tie ir atjaunināti un saglabāti datubāzē

$user->password = password_hash()'newpassword'); // tagad tas ir netīrs
$user->dirty(); // neko nepadojot, tas notīrīs visus netīros ierakstus.
$user->update(); // nekas netiks atjaunināts, jo nekas netika uztverts kā netīrs.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // gan name, gan password tiks atjaunināti.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Tas ir alias `dirty()` metodei. Tas ir nedaudz skaidrāks, ko jūs darāt.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // gan name, gan password tiks atjaunināti.
```

#### `isDirty(): boolean` (v0.4.0)

Atgriež `true`, ja pašreizējais ieraksts ir mainīts.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Atiestata pašreizējo ierakstu uz tā sākotnējo stāvokli. Tas ir patiešām labs lietošanai cilpas veida uzvedībās. Ja padojat `true`, tas arī atiestatīs vaicājuma datus, kas tika izmantoti, lai atrastu pašreizējo objektu (noklusējuma uzvedība).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // sākt ar tīru lapu
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Pēc tam, kad izpildāt `find()`, `findAll()`, `insert()`, `update()` vai `save()` metodi, jūs varat iegūt SQL, kas tika izveidots, un izmantot to atkļūdošanas nolūkos.

## SQL vaicājuma metodes
#### `select(string $field1 [, string $field2 ... ])`

Jūs varat atlasīt tikai dažas kolonnas tabulā, ja vēlaties (tas ir efektīvāks patiešām plašās tabulās ar daudzām kolonnām)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Jūs tehniski varat izvēlēties arī citu tabulu! Kāpēc gan ne?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Jūs pat varat pievienoties citai tabulai datubāzē.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Jūs varat iestatīt dažus pielāgotus where argumentus (jūs nevarat iestatīt parametrus šajā where paziņojumā)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Drošības piezīme** - Jūs varētu būt kārdināts darīt kaut ko līdzīgu `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Lūdzu, NEDARIET TO!!! Tas ir pakļauts tam, ko sauc par SQL injekcijas uzbrukumiem. Ir daudz rakstu tiešsaistē, lūdzu, meklējiet Google "sql injection attacks php" un atradīsit daudz rakstu par šo tēmu. Pareizais veids, kā apstrādāt to ar šo bibliotēku, ir, nevis izmantot šo `where()` metodi, bet gan kaut ko līdzīgu `$user->eq('id', $id)->eq('name', $name)->find();` Ja jums absolūti jāto dara, `PDO` bibliotēkai ir `$pdo->quote($var)`, lai aizbēgtu to jums. Tikai pēc `quote()` izmantošanas jūs varat izmantot to `where()` paziņojumā.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Grupēt jūsu rezultātus pēc noteikta nosacījuma.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Kārtot atgriezto vaicājumu noteiktā veidā.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Ierobežot atgriezto ierakstu skaitu. Ja dots otrais int, tas būs nobīde, ierobežojums tieši kā SQL.

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

### OR nosacījumi

Ir iespējams apvijināt jūsu nosacījumus OR paziņojumā. Tas tiek darīts ar `startWrap()` un `endWrap()` metodi vai aizpildot 3. parametru nosacījumā pēc lauka un vērtības.

```php
// Metode 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Tas tiks novērtēts kā `id = 1 AND (name = 'demo' OR name = 'test')`

// Metode 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Tas tiks novērtēts kā `id = 1 OR name = 'demo'`
```

## Attiecības
Jūs varat iestatīt vairākas attiecību veidus, izmantojot šo bibliotēku. Jūs varat iestatīt one->many un one->one attiecības starp tabulām. Tas prasa nedaudz papildu iestatījumu klasē iepriekš.

Iestatot `$relations` masīvu nav grūti, bet pareizās sintakses minēšana var būt mulsinoša.

```php
protected array $relations = [
	// jūs varat nosaukt atslēgu jebkā vēlaties. ActiveRecord nosaukums droši vien ir labs. Piem: user, contact, client
	'user' => [
		// obligāti
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // tas ir attiecību veids

		// obligāti
		'Some_Class', // tas ir "cits" ActiveRecord klase, uz kuru tas atsaucas

		// obligāti
		// atkarībā no attiecību veida
		// self::HAS_ONE = ārējā atslēga, kas atsaucas uz savienojumu
		// self::HAS_MANY = ārējā atslēga, kas atsaucas uz savienojumu
		// self::BELONGS_TO = lokālā atslēga, kas atsaucas uz savienojumu
		'local_or_foreign_key',
		// tikai FYI, tas arī pievienojas tikai uz "citas" modeļa primāro atslēgu

		// izvēles
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // papildu nosacījumi, ko vēlaties, pievienojoties attiecībai
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// izvēles
		'back_reference_name' // tas ir, ja vēlaties atpakaļatsauce uz šo attiecību atpakaļ uz sevi Piem: $user->contact->user;
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

Tagad mums ir atsauces iestatītas, lai mēs varētu izmantot tās ļoti viegli!

```php
$user = new User($pdo_connection);

// atrast jaunāko lietotāju.
$user->notNull('id')->orderBy('id desc')->find();

// iegūt kontaktus, izmantojot attiecību:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// vai mēs varam iet otru ceļu.
$contact = new Contact();

// atrast vienu kontaktu
$contact->find();

// iegūt lietotāju, izmantojot attiecību:
echo $contact->user->name; // tas ir lietotāja vārds
```

Diezgan forši, vai ne?

### Eager Loading

#### Pārskats
Eager loading atrisina N+1 vaicājuma problēmu, iepriekš ielādējot attiecības. Tā vietā, lai izpildītu atsevišķu vaicājumu katrai ieraksta attiecībai, eager loading iegūst visus saistītos datus tikai vienā papildu vaicājumā uz attiecību.

> **Piezīme:** Eager loading ir pieejams tikai v0.7.0 un augstāk.

#### Pamatlietošana
Izmantojiet `with()` metodi, lai norādītu, kuras attiecības eager ielādēt:
```php
// Ielādēt lietotājus ar viņu kontaktiem 2 vaicājumos, nevis N+1
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    foreach ($u->contacts as $contact) {
        echo $contact->email; // Nav papildu vaicājuma!
    }
}
```

#### Vairākas attiecības
Ielādēt vairākas attiecības uzreiz:
```php
$users = $user->with(['contacts', 'profile', 'settings'])->findAll();
```

#### Attiecību veidi

##### HAS_MANY
```php
// Eager ielādēt visus kontaktus katram lietotājam
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    // $u->contacts jau ir ielādēts kā masīvs
    foreach ($u->contacts as $contact) {
        echo $contact->email;
    }
}
```
##### HAS_ONE
```php
// Eager ielādēt vienu kontaktu katram lietotājam
$users = $user->with('contact')->findAll();
foreach ($users as $u) {
    // $u->contact jau ir ielādēts kā objekts
    echo $u->contact->email;
}
```

##### BELONGS_TO
```php
// Eager ielādēt vecāku lietotājus visiem kontaktiem
$contacts = $contact->with('user')->findAll();
foreach ($contacts as $c) {
    // $c->user jau ir ielādēts
    echo $c->user->name;
}
```
##### Ar find()
Eager loading darbojas ar 
findAll()
 un 
find()
:

```php
$user = $user->with('contacts')->find(1);
// Lietotājs un visi viņu kontakti ielādēti 2 vaicājumos
```
#### Veiktspējas priekšrocības
Bez eager loading (N+1 problēma):
```php
$users = $user->findAll(); // 1 vaicājums
foreach ($users as $u) {
    $contacts = $u->contacts; // N vaicājumi (viens uz lietotāju!)
}
// Kopā: 1 + N vaicājumi
```

Ar eager loading:

```php
$users = $user->with('contacts')->findAll(); // 2 vaicājumi kopā
foreach ($users as $u) {
    $contacts = $u->contacts; // 0 papildu vaicājumi!
}
// Kopā: 2 vaicājumi (1 lietotājiem + 1 visiem kontaktiem)
```
10 lietotājiem tas samazina vaicājumus no 11 līdz 2 - 82% samazinājums!

#### Svarīgas piezīmes
- Eager loading ir pilnīgi izvēles - lazy loading joprojām darbojas kā iepriekš
- Jau ielādētās attiecības automātiski tiek izlaistas
- Atpakaļatsauces darbojas ar eager loading
- Attiecību atsauces tiek ievērotas eager loading laikā

#### Ierobežojumi
- Ieslēgtas eager loading (piem., 
with(['contacts.addresses'])
) pašlaik netiek atbalstīts
- Eager load ierobežojumi caur aizvērumiem nav atbalstīti šajā versijā

## Pielāgota datu iestatīšana
Dažreiz jums var būt nepieciešams pievienot kaut ko unikālu jūsu ActiveRecord, piemēram, pielāgotu aprēķinu, kas varētu būt vieglāk pievienot objektam, kas tad tiks nodots, teiksim, šablonam.

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

Viens vairāk super lielisks elements par šo bibliotēku ir par notikumiem. Notikumi tiek izraisīti noteiktos laikos, balstoties uz noteiktām metodēm, ko jūs saucat. Tie ir ļoti noderīgi, lai automātiski iestatītu datus jums.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Tas ir patiešām noderīgi, ja jums ir nepieciešams iestatīt noklusējuma savienojumu vai kaut ko tādu.

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
		// vai to
		$self->transformAndPersistConnection(Flight::db());
		
		// Jūs varat arī iestatīt tabulas nosaukumu šādi.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Tas, visticamāk, ir noderīgi tikai tad, ja jums ir nepieciešama vaicājuma manipulācija katru reizi.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// vienmēr palaidiet id >= 0, ja tas ir jūsu stils
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Šis, visticamāk, ir noderīgāks, ja jums vienmēr ir jāpalaid kaut kāda loģika katru reizi, kad šis ieraksts tiek iegūts. Vai jums ir jāšifrē kaut kas? Vai jums ir jāpalaid pielāgots skaita vaicājums katru reizi (nav efektīvi, bet nu labi)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// šifrēšana kaut kā
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// varbūt saglabājot kaut ko pielāgotu kā vaicājumu???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Tas, visticamāk, ir noderīgi tikai tad, ja jums ir nepieciešama vaicājuma manipulācija katru reizi.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// vienmēr palaidiet id >= 0, ja tas ir jūsu stils
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Līdzīgs `afterFind()`, bet jūs varat to darīt visiem ierakstiem!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// dariet kaut ko foršu kā afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Patiešām noderīgi, ja jums ir nepieciešams iestatīt noklusējuma vērtības katru reizi.

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

Varbūt jums ir gadījums, kad mainīt datus pēc ievietošanas?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// dariet, ko vēlaties
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// vai ko citu....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Patiešām noderīgi, ja jums ir nepieciešams iestatīt noklusējuma vērtības katru reizi atjauninājumā.

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

Varbūt jums ir gadījums, kad mainīt datus pēc atjaunināšanas?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// dariet, ko vēlaties
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// vai ko citu....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Tas ir noderīgi, ja vēlaties, lai notikumi notiktu gan ievietošanas, gan atjauninājuma laikā. Es jūs saīsināšu no gara skaidrojuma, bet esmu pārliecināts, ka jūs varat uzminēt, kas tas ir.

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

Neesmu pārliecināts, ko jūs vēlētos darīt šeit, bet bez tiesājumiem! Dodieties!

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

## Datubāzes savienojuma pārvaldība

Izmantojot šo bibliotēku, jūs varat iestatīt datubāzes savienojumu dažādos veidos. Jūs varat iestatīt savienojumu konstruktorā, varat iestatīt to caur konfigurācijas mainīgo `$config['connection']` vai varat iestatīt to caur `setDatabaseConnection()` (v0.4.1). 

```php
$pdo_connection = new PDO('sqlite:test.db'); // piemēram
$user = new User($pdo_connection);
// vai
$user = new User(null, [ 'connection' => $pdo_connection ]);
// vai
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Ja vēlaties izvairīties no `$database_connection` iestatīšanas katru reizi, kad saucat active record, ir veidi ap to!

```php
// index.php vai bootstrap.php
// Iestatīt to kā reģistrētu klasi Flight
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// Un tagad nav nepieciešami argumenti!
$user = new User();
```

> **Piezīme:** Ja plānojat veikt unit testēšanu, darot to šādi, tas var pievienot dažus izaicinājumus unit testēšanai, bet kopumā, jo jūs varat injicēt savu savienojumu ar `setDatabaseConnection()` vai `$config['connection']`, tas nav pārāk slikti.

Ja jums ir jāatjauno datubāzes savienojums, piemēram, ja palaižat ilgu CLI skriptu un ir jāatjauno savienojums ik pa laikam, jūs varat atkārtoti iestatīt savienojumu ar `$your_record->setDatabaseConnection($pdo_connection)`.

## Iesaiste

Lūdzu, dariet. :D

### Iestatīšana

Kad jūs iesaistāties, pārliecinieties, ka palaižat `composer test-coverage`, lai uzturētu 100% testa pārklājumu (tas nav īsts unit testa pārklājums, vairāk kā integrācijas testēšana).

Arī pārliecinieties, ka palaižat `composer beautify` un `composer phpcs`, lai labotu jebkādas linting kļūdas.

## Licence

MIT